<?php
/**
 * Category model.
 *
 * @package RestaurantMenuBuilder
 */

declare( strict_types=1 );

namespace RestaurantMenuBuilder;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Create, read, update, delete, duplicate and reorder categories.
 */
class Category {

	/**
	 * Normalise a raw database row.
	 *
	 * @param array<string,mixed> $row Raw row.
	 * @return array<string,mixed>
	 */
	public static function prepare( array $row ): array {
		$image_id = (int) ( $row['image_id'] ?? 0 );

		return array(
			'id'          => (int) ( $row['id'] ?? 0 ),
			'menu_id'     => (int) ( $row['menu_id'] ?? 0 ),
			'name'        => (string) ( $row['name'] ?? '' ),
			'description' => (string) ( $row['description'] ?? '' ),
			'icon'        => (string) ( $row['icon'] ?? '' ),
			'image_id'    => $image_id,
			'image'       => $image_id > 0 ? wp_get_attachment_image_url( $image_id, 'thumbnail' ) : '',
			'sort_order'  => (int) ( $row['sort_order'] ?? 0 ),
			'status'      => sanitize_status( $row['status'] ?? 'active' ),
			'anchor'      => 'rmb-cat-' . (int) ( $row['id'] ?? 0 ),
			'created_at'  => (string) ( $row['created_at'] ?? '' ),
			'updated_at'  => (string) ( $row['updated_at'] ?? '' ),
		);
	}

	/**
	 * Find one category.
	 *
	 * @param int $id Category ID.
	 * @return array<string,mixed>|null
	 */
	public static function find( int $id ): ?array {
		$row = Database::find( Database::CATEGORIES, $id );

		return null === $row ? null : self::prepare( $row );
	}

	/**
	 * List categories.
	 *
	 * @param array<string,mixed> $args Query arguments: menu_id, status.
	 * @return array<int,array<string,mixed>>
	 */
	public static function all( array $args = array() ): array {
		global $wpdb;

		$args = wp_parse_args(
			$args,
			array(
				'menu_id' => 0,
				'status'  => '',
			)
		);

		$table  = Database::table( Database::CATEGORIES );
		$where  = array( '1=1' );
		$params = array();

		if ( (int) $args['menu_id'] > 0 ) {
			$where[]  = 'menu_id = %d';
			$params[] = (int) $args['menu_id'];
		}

		if ( '' !== $args['status'] ) {
			$where[]  = 'status = %s';
			$params[] = sanitize_status( $args['status'] );
		}

		$clause = implode( ' AND ', $where );
		$sql    = "SELECT * FROM {$table} WHERE {$clause} ORDER BY sort_order ASC, id ASC";

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery
		$rows = $params ? $wpdb->get_results( $wpdb->prepare( $sql, $params ), ARRAY_A ) : $wpdb->get_results( $sql, ARRAY_A );
		// phpcs:enable

		$categories = array();

		foreach ( (array) $rows as $row ) {
			$categories[] = self::prepare( (array) $row );
		}

		return $categories;
	}

	/**
	 * Create a category.
	 *
	 * @param array<string,mixed> $input Raw input.
	 * @return int|\WP_Error
	 */
	public static function create( array $input ) {
		$menu_id = absint( $input['menu_id'] ?? 0 );

		if ( null === Menu::find( $menu_id ) ) {
			return new \WP_Error( 'rmb_menu_missing', __( 'Choose a menu for this category.', 'restaurant-menu-builder' ), array( 'status' => 400 ) );
		}

		$name = sanitize_text_field( (string) ( $input['name'] ?? '' ) );

		if ( '' === $name ) {
			return new \WP_Error( 'rmb_category_name_required', __( 'Enter a category name.', 'restaurant-menu-builder' ), array( 'status' => 400 ) );
		}

		$id = Database::insert(
			Database::CATEGORIES,
			array(
				'menu_id'     => $menu_id,
				'name'        => substr( $name, 0, 191 ),
				'description' => sanitize_textarea_field( (string) ( $input['description'] ?? '' ) ),
				'icon'        => Icons::sanitize( (string) ( $input['icon'] ?? '' ) ),
				'image_id'    => self::sanitize_image_id( $input['image_id'] ?? 0 ),
				'sort_order'  => isset( $input['sort_order'] ) ? absint( $input['sort_order'] ) : Database::next_sort_order( Database::CATEGORIES, 'menu_id', $menu_id ),
				'status'      => isset( $input['status'] ) ? sanitize_status( $input['status'] ) : 'active',
			),
			array( '%d', '%s', '%s', '%s', '%d', '%d', '%s' )
		);

		if ( 0 === $id ) {
			return new \WP_Error( 'rmb_category_not_created', __( 'Unable to save the category. Please try again.', 'restaurant-menu-builder' ), array( 'status' => 500 ) );
		}

		Cache::flush();

		return $id;
	}

	/**
	 * Update a category.
	 *
	 * @param int                 $id    Category ID.
	 * @param array<string,mixed> $input Raw input.
	 * @return true|\WP_Error
	 */
	public static function update( int $id, array $input ) {
		$category = self::find( $id );

		if ( null === $category ) {
			return new \WP_Error( 'rmb_category_missing', __( 'That category no longer exists.', 'restaurant-menu-builder' ), array( 'status' => 404 ) );
		}

		$data    = array();
		$formats = array();

		if ( array_key_exists( 'name', $input ) ) {
			$name = sanitize_text_field( (string) $input['name'] );

			if ( '' === $name ) {
				return new \WP_Error( 'rmb_category_name_required', __( 'Enter a category name.', 'restaurant-menu-builder' ), array( 'status' => 400 ) );
			}

			$data['name']    = substr( $name, 0, 191 );
			$formats['name'] = '%s';
		}

		if ( array_key_exists( 'description', $input ) ) {
			$data['description']    = sanitize_textarea_field( (string) $input['description'] );
			$formats['description'] = '%s';
		}

		if ( array_key_exists( 'icon', $input ) ) {
			$data['icon']    = Icons::sanitize( (string) $input['icon'] );
			$formats['icon'] = '%s';
		}

		if ( array_key_exists( 'image_id', $input ) ) {
			$data['image_id']    = self::sanitize_image_id( $input['image_id'] );
			$formats['image_id'] = '%d';
		}

		if ( array_key_exists( 'status', $input ) ) {
			$data['status']    = sanitize_status( $input['status'] );
			$formats['status'] = '%s';
		}

		if ( array_key_exists( 'sort_order', $input ) ) {
			$data['sort_order']    = absint( $input['sort_order'] );
			$formats['sort_order'] = '%d';
		}

		if ( empty( $data ) ) {
			return true;
		}

		// Formats are keyed by column and only flattened here, so a column set
		// by more than one branch can never shift the remaining formats.
		$format = array();

		foreach ( array_keys( $data ) as $column ) {
			$format[] = $formats[ $column ];
		}

		if ( ! Database::update( Database::CATEGORIES, $id, $data, $format ) ) {
			return new \WP_Error( 'rmb_category_not_saved', __( 'Unable to save the category. Please try again.', 'restaurant-menu-builder' ), array( 'status' => 500 ) );
		}

		Cache::flush();

		return true;
	}

	/**
	 * Delete a category and its items.
	 *
	 * @param int $id Category ID.
	 * @return true|\WP_Error
	 */
	public static function delete( int $id ) {
		global $wpdb;

		if ( null === self::find( $id ) ) {
			return new \WP_Error( 'rmb_category_missing', __( 'That category no longer exists.', 'restaurant-menu-builder' ), array( 'status' => 404 ) );
		}

		$items = Database::table( Database::ITEMS );

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$items} WHERE category_id = %d", $id ) );
		// phpcs:enable

		if ( ! Database::delete( Database::CATEGORIES, $id ) ) {
			return new \WP_Error( 'rmb_category_not_deleted', __( 'Unable to delete the category. Please try again.', 'restaurant-menu-builder' ), array( 'status' => 500 ) );
		}

		Cache::flush();

		return true;
	}

	/**
	 * Duplicate a category and its items.
	 *
	 * @param int $id Category ID.
	 * @return int|\WP_Error New category ID.
	 */
	public static function duplicate( int $id ) {
		$category = self::find( $id );

		if ( null === $category ) {
			return new \WP_Error( 'rmb_category_missing', __( 'That category no longer exists.', 'restaurant-menu-builder' ), array( 'status' => 404 ) );
		}

		$new_id = self::clone_row( $category, $category['menu_id'], copy_name( $category['name'] ) );

		if ( 0 === $new_id ) {
			return new \WP_Error( 'rmb_category_not_duplicated', __( 'Unable to duplicate the category. Please try again.', 'restaurant-menu-builder' ), array( 'status' => 500 ) );
		}

		foreach ( Item::all( array( 'category_id' => $id ) ) as $item ) {
			Item::clone_row( $item, $category['menu_id'], $new_id, $item['name'] );
		}

		Cache::flush();

		return $new_id;
	}

	/**
	 * Insert a copy of a prepared category row.
	 *
	 * @param array<string,mixed> $category Prepared category.
	 * @param int                 $menu_id  Target menu ID.
	 * @param string              $name     Name for the copy.
	 * @return int
	 */
	public static function clone_row( array $category, int $menu_id, string $name ): int {
		return Database::insert(
			Database::CATEGORIES,
			array(
				'menu_id'     => $menu_id,
				'name'        => substr( $name, 0, 191 ),
				'description' => (string) $category['description'],
				'icon'        => (string) $category['icon'],
				'image_id'    => (int) $category['image_id'],
				'sort_order'  => Database::next_sort_order( Database::CATEGORIES, 'menu_id', $menu_id ),
				'status'      => (string) $category['status'],
			),
			array( '%d', '%s', '%s', '%s', '%d', '%d', '%s' )
		);
	}

	/**
	 * Persist a new category order inside a menu.
	 *
	 * @param int   $menu_id Menu ID.
	 * @param int[] $ids     Ordered category IDs.
	 * @return true|\WP_Error
	 */
	public static function reorder( int $menu_id, array $ids ) {
		$existing = wp_list_pluck( self::all( array( 'menu_id' => $menu_id ) ), 'id' );
		$ids      = array_values( array_map( 'absint', $ids ) );
		$filtered = array_values( array_intersect( $ids, $existing ) );

		if ( empty( $filtered ) ) {
			return new \WP_Error( 'rmb_reorder_invalid', __( 'Unable to save the new order. Please reload the page and try again.', 'restaurant-menu-builder' ), array( 'status' => 400 ) );
		}

		// Append any category the client did not send so nothing loses its position.
		foreach ( $existing as $existing_id ) {
			if ( ! in_array( $existing_id, $filtered, true ) ) {
				$filtered[] = $existing_id;
			}
		}

		Database::apply_order( Database::CATEGORIES, $filtered );
		Cache::flush();

		return true;
	}

	/**
	 * Validate an attachment ID.
	 *
	 * @param mixed $value Raw value.
	 * @return int
	 */
	public static function sanitize_image_id( $value ): int {
		$id = absint( $value );

		if ( $id <= 0 ) {
			return 0;
		}

		return wp_attachment_is_image( $id ) ? $id : 0;
	}

	/**
	 * Count categories for many menus in one query.
	 *
	 * @param int[] $menu_ids Menu IDs.
	 * @return array<int,int> Menu ID => count.
	 */
	public static function counts_for_menus( array $menu_ids ): array {
		global $wpdb;

		$menu_ids = array_values( array_filter( array_map( 'absint', $menu_ids ) ) );

		if ( empty( $menu_ids ) ) {
			return array();
		}

		$table        = Database::table( Database::CATEGORIES );
		$placeholders = implode( ',', array_fill( 0, count( $menu_ids ), '%d' ) );

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT menu_id, COUNT(*) AS total FROM {$table} WHERE menu_id IN ({$placeholders}) GROUP BY menu_id",
				$menu_ids
			),
			ARRAY_A
		);
		// phpcs:enable

		$counts = array();

		foreach ( (array) $rows as $row ) {
			$counts[ (int) $row['menu_id'] ] = (int) $row['total'];
		}

		return $counts;
	}

	/**
	 * Count every category across all menus.
	 *
	 * @return int
	 */
	public static function count_all(): int {
		global $wpdb;

		$table = Database::table( Database::CATEGORIES );

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
		// phpcs:enable
	}

	/**
	 * Count categories in a menu.
	 *
	 * @param int $menu_id Menu ID.
	 * @return int
	 */
	public static function count( int $menu_id ): int {
		global $wpdb;

		$table = Database::table( Database::CATEGORIES );

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery
		return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE menu_id = %d", $menu_id ) );
		// phpcs:enable
	}
}
