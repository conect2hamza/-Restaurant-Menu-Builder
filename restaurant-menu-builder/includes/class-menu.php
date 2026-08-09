<?php
/**
 * Menu model.
 *
 * @package RestaurantMenuBuilder
 */

declare( strict_types=1 );

namespace RestaurantMenuBuilder;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Create, read, update, delete and duplicate menus.
 */
class Menu {

	/**
	 * Menu setting keys that may be overridden per menu.
	 *
	 * @return array<string,mixed>
	 */
	public static function default_settings(): array {
		$general = Settings::general();

		return array(
			'layout'            => $general['layout'],
			'show_navigation'   => Settings::to_bool( $general['show_navigation'] ),
			'show_images'       => Settings::to_bool( $general['show_images'] ),
			'show_descriptions' => Settings::to_bool( $general['show_descriptions'] ),
			'show_prices'       => Settings::to_bool( $general['show_prices'] ),
			'use_global_style'  => true,
			'style'             => Settings::style(),
			'custom_css'        => '',
		);
	}

	/**
	 * Normalise a raw database row.
	 *
	 * @param array<string,mixed> $row Raw row.
	 * @return array<string,mixed>
	 */
	public static function prepare( array $row ): array {
		$settings = self::merge_settings( decode_json( $row['settings'] ?? '' ) );

		return array(
			'id'         => (int) ( $row['id'] ?? 0 ),
			'name'       => (string) ( $row['name'] ?? '' ),
			'slug'       => (string) ( $row['slug'] ?? '' ),
			'status'     => sanitize_status( $row['status'] ?? 'draft' ),
			'settings'   => $settings,
			'shortcode'  => '[restaurant_menu id="' . (int) ( $row['id'] ?? 0 ) . '"]',
			'created_at' => (string) ( $row['created_at'] ?? '' ),
			'updated_at' => (string) ( $row['updated_at'] ?? '' ),
		);
	}

	/**
	 * Merge stored settings over the defaults.
	 *
	 * @param array<string,mixed> $stored Stored settings.
	 * @return array<string,mixed>
	 */
	public static function merge_settings( array $stored ): array {
		$defaults = self::default_settings();
		$merged   = array_merge( $defaults, $stored );

		$style           = is_array( $merged['style'] ?? null ) ? $merged['style'] : array();
		$merged['style'] = array_merge( Settings::default_style(), $style );

		$merged['use_global_style'] = Settings::to_bool( $merged['use_global_style'] );

		foreach ( array( 'show_navigation', 'show_images', 'show_descriptions', 'show_prices' ) as $flag ) {
			$merged[ $flag ] = Settings::to_bool( $merged[ $flag ] );
		}

		if ( ! array_key_exists( (string) $merged['layout'], Settings::layouts() ) ) {
			$merged['layout'] = 'classic';
		}

		$merged['custom_css'] = Settings::sanitize_css( (string) ( $merged['custom_css'] ?? '' ) );

		return $merged;
	}

	/**
	 * Find one menu.
	 *
	 * @param int $id Menu ID.
	 * @return array<string,mixed>|null
	 */
	public static function find( int $id ): ?array {
		$row = Database::find( Database::MENUS, $id );

		return null === $row ? null : self::prepare( $row );
	}

	/**
	 * Find a menu by slug.
	 *
	 * @param string $slug Menu slug.
	 * @return array<string,mixed>|null
	 */
	public static function find_by_slug( string $slug ): ?array {
		global $wpdb;

		$slug = sanitize_title( $slug );

		if ( '' === $slug ) {
			return null;
		}

		$table = Database::table( Database::MENUS );

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery
		$row = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE slug = %s LIMIT 1", $slug ),
			ARRAY_A
		);
		// phpcs:enable

		return is_array( $row ) ? self::prepare( $row ) : null;
	}

	/**
	 * List menus.
	 *
	 * @param array<string,mixed> $args Query arguments: status, search, per_page, page.
	 * @return array{items:array<int,array<string,mixed>>,total:int}
	 */
	public static function all( array $args = array() ): array {
		global $wpdb;

		$args = wp_parse_args(
			$args,
			array(
				'status'   => '',
				'search'   => '',
				'per_page' => 50,
				'page'     => 1,
			)
		);

		$table  = Database::table( Database::MENUS );
		$where  = array( '1=1' );
		$params = array();

		if ( '' !== $args['status'] ) {
			$where[]  = 'status = %s';
			$params[] = sanitize_status( $args['status'] );
		}

		if ( '' !== $args['search'] ) {
			$where[]  = 'name LIKE %s';
			$params[] = '%' . $wpdb->esc_like( (string) $args['search'] ) . '%';
		}

		$per_page = max( 1, min( 200, (int) $args['per_page'] ) );
		$page     = max( 1, (int) $args['page'] );
		$offset   = ( $page - 1 ) * $per_page;

		$clause = implode( ' AND ', $where );

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery
		$count_sql = "SELECT COUNT(*) FROM {$table} WHERE {$clause}";
		$total     = (int) ( $params ? $wpdb->get_var( $wpdb->prepare( $count_sql, $params ) ) : $wpdb->get_var( $count_sql ) );

		$sql  = "SELECT * FROM {$table} WHERE {$clause} ORDER BY updated_at DESC, id DESC LIMIT %d OFFSET %d";
		$rows = $wpdb->get_results( $wpdb->prepare( $sql, array_merge( $params, array( $per_page, $offset ) ) ), ARRAY_A );
		// phpcs:enable

		$items = array();

		foreach ( (array) $rows as $row ) {
			$items[] = self::prepare( (array) $row );
		}

		return array(
			'items' => $items,
			'total' => $total,
		);
	}

	/**
	 * Create a menu.
	 *
	 * @param array<string,mixed> $input Raw input.
	 * @return int|\WP_Error Menu ID or error.
	 */
	public static function create( array $input ) {
		$name = isset( $input['name'] ) ? sanitize_text_field( (string) $input['name'] ) : '';

		if ( '' === $name ) {
			return new \WP_Error( 'rmb_menu_name_required', __( 'Enter a menu name.', 'restaurant-menu-builder' ), array( 'status' => 400 ) );
		}

		$slug     = isset( $input['slug'] ) && '' !== $input['slug'] ? (string) $input['slug'] : $name;
		$slug     = unique_slug( $slug, Database::table( Database::MENUS ) );
		$settings = self::merge_settings( self::sanitize_settings( is_array( $input['settings'] ?? null ) ? $input['settings'] : array(), self::default_settings() ) );

		$id = Database::insert(
			Database::MENUS,
			array(
				'name'     => substr( $name, 0, 191 ),
				'slug'     => $slug,
				'status'   => sanitize_status( $input['status'] ?? 'draft' ),
				'settings' => encode_json( $settings ),
			),
			array( '%s', '%s', '%s', '%s' )
		);

		if ( 0 === $id ) {
			return new \WP_Error( 'rmb_menu_not_created', __( 'Unable to save the menu. Please try again.', 'restaurant-menu-builder' ), array( 'status' => 500 ) );
		}

		Cache::flush();

		/**
		 * Fires after a menu is created.
		 *
		 * @param int $id Menu ID.
		 */
		do_action( 'rmb_menu_created', $id );

		return $id;
	}

	/**
	 * Update a menu.
	 *
	 * @param int                 $id    Menu ID.
	 * @param array<string,mixed> $input Raw input.
	 * @return true|\WP_Error
	 */
	public static function update( int $id, array $input ) {
		$menu = self::find( $id );

		if ( null === $menu ) {
			return new \WP_Error( 'rmb_menu_missing', __( 'That menu no longer exists.', 'restaurant-menu-builder' ), array( 'status' => 404 ) );
		}

		$data   = array();
		$format = array();

		if ( array_key_exists( 'name', $input ) ) {
			$name = sanitize_text_field( (string) $input['name'] );

			if ( '' === $name ) {
				return new \WP_Error( 'rmb_menu_name_required', __( 'Enter a menu name.', 'restaurant-menu-builder' ), array( 'status' => 400 ) );
			}

			$data['name'] = substr( $name, 0, 191 );
			$format[]     = '%s';
		}

		if ( array_key_exists( 'slug', $input ) ) {
			$slug         = '' !== (string) $input['slug'] ? (string) $input['slug'] : ( $data['name'] ?? $menu['name'] );
			$data['slug'] = unique_slug( $slug, Database::table( Database::MENUS ), $id );
			$format[]     = '%s';
		}

		if ( array_key_exists( 'status', $input ) ) {
			$data['status'] = sanitize_status( $input['status'] );
			$format[]       = '%s';
		}

		if ( array_key_exists( 'settings', $input ) && is_array( $input['settings'] ) ) {
			$settings         = self::merge_settings( self::sanitize_settings( $input['settings'], $menu['settings'] ) );
			$data['settings'] = encode_json( $settings );
			$format[]         = '%s';
		}

		if ( empty( $data ) ) {
			return true;
		}

		$updated = Database::update( Database::MENUS, $id, $data, $format );

		if ( ! $updated ) {
			return new \WP_Error( 'rmb_menu_not_saved', __( 'Unable to save the menu. Please try again.', 'restaurant-menu-builder' ), array( 'status' => 500 ) );
		}

		Cache::flush();

		/**
		 * Fires after a menu is updated.
		 *
		 * @param int $id Menu ID.
		 */
		do_action( 'rmb_menu_updated', $id );

		return true;
	}

	/**
	 * Delete a menu together with its categories and items.
	 *
	 * @param int $id Menu ID.
	 * @return true|\WP_Error
	 */
	public static function delete( int $id ) {
		global $wpdb;

		$menu = self::find( $id );

		if ( null === $menu ) {
			return new \WP_Error( 'rmb_menu_missing', __( 'That menu no longer exists.', 'restaurant-menu-builder' ), array( 'status' => 404 ) );
		}

		$items      = Database::table( Database::ITEMS );
		$categories = Database::table( Database::CATEGORIES );

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$items} WHERE menu_id = %d", $id ) );
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$categories} WHERE menu_id = %d", $id ) );
		// phpcs:enable

		if ( ! Database::delete( Database::MENUS, $id ) ) {
			return new \WP_Error( 'rmb_menu_not_deleted', __( 'Unable to delete the menu. Please try again.', 'restaurant-menu-builder' ), array( 'status' => 500 ) );
		}

		if ( (int) Settings::get( 'default_menu', 0 ) === $id ) {
			Settings::save_general( array( 'default_menu' => 0 ) );
		}

		Cache::flush();

		/**
		 * Fires after a menu and its contents are deleted.
		 *
		 * @param int $id Menu ID.
		 */
		do_action( 'rmb_menu_deleted', $id );

		return true;
	}

	/**
	 * Duplicate a menu including every category and item.
	 *
	 * @param int $id Menu ID.
	 * @return int|\WP_Error New menu ID.
	 */
	public static function duplicate( int $id ) {
		$menu = self::find( $id );

		if ( null === $menu ) {
			return new \WP_Error( 'rmb_menu_missing', __( 'That menu no longer exists.', 'restaurant-menu-builder' ), array( 'status' => 404 ) );
		}

		$new_id = Database::insert(
			Database::MENUS,
			array(
				'name'     => copy_name( $menu['name'] ),
				'slug'     => unique_slug( $menu['slug'] . '-copy', Database::table( Database::MENUS ) ),
				'status'   => 'draft',
				'settings' => encode_json( $menu['settings'] ),
			),
			array( '%s', '%s', '%s', '%s' )
		);

		if ( 0 === $new_id ) {
			return new \WP_Error( 'rmb_menu_not_duplicated', __( 'Unable to duplicate the menu. Please try again.', 'restaurant-menu-builder' ), array( 'status' => 500 ) );
		}

		$categories = Category::all( array( 'menu_id' => $id ) );

		foreach ( $categories as $category ) {
			$new_category_id = Category::clone_row( $category, $new_id, $category['name'] );

			if ( 0 === $new_category_id ) {
				continue;
			}

			foreach ( Item::all( array( 'category_id' => $category['id'] ) ) as $item ) {
				Item::clone_row( $item, $new_id, $new_category_id, $item['name'] );
			}
		}

		Cache::flush();

		return $new_id;
	}

	/**
	 * Sanitize per-menu settings.
	 *
	 * @param array<string,mixed> $input   Raw settings.
	 * @param array<string,mixed> $current Current settings.
	 * @return array<string,mixed>
	 */
	public static function sanitize_settings( array $input, array $current ): array {
		$clean = $current;

		if ( isset( $input['layout'] ) ) {
			$layout          = sanitize_key( (string) $input['layout'] );
			$clean['layout'] = array_key_exists( $layout, Settings::layouts() ) ? $layout : ( $current['layout'] ?? 'classic' );
		}

		foreach ( array( 'show_navigation', 'show_images', 'show_descriptions', 'show_prices', 'use_global_style' ) as $flag ) {
			if ( array_key_exists( $flag, $input ) ) {
				$clean[ $flag ] = Settings::to_bool( $input[ $flag ] );
			}
		}

		if ( isset( $input['style'] ) && is_array( $input['style'] ) ) {
			$base           = is_array( $current['style'] ?? null ) ? $current['style'] : Settings::default_style();
			$clean['style'] = Settings::sanitize_style( $input['style'], array_merge( Settings::default_style(), $base ) );
		}

		if ( array_key_exists( 'custom_css', $input ) ) {
			$clean['custom_css'] = Settings::sanitize_css( (string) $input['custom_css'] );
		}

		return $clean;
	}

	/**
	 * Count menus.
	 *
	 * @return int
	 */
	public static function count(): int {
		global $wpdb;

		$table = Database::table( Database::MENUS );

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
		// phpcs:enable
	}
}
