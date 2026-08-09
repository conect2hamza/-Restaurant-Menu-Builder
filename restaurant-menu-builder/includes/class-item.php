<?php
/**
 * Menu item model.
 *
 * @package RestaurantMenuBuilder
 */

declare( strict_types=1 );

namespace RestaurantMenuBuilder;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Create, read, update, delete, duplicate and reorder menu items.
 */
class Item {

	public const MAX_VARIATIONS = 8;

	/**
	 * Normalise a raw database row.
	 *
	 * @param array<string,mixed> $row Raw row.
	 * @return array<string,mixed>
	 */
	public static function prepare( array $row ): array {
		$settings   = decode_json( $row['settings'] ?? '' );
		$image_id   = (int) ( $row['image_id'] ?? 0 );
		$price_type = 'multiple' === ( $row['price_type'] ?? 'single' ) ? 'multiple' : 'single';
		$variations = self::prepare_variations( $settings['variations'] ?? array() );

		$price      = isset( $row['price'] ) && null !== $row['price'] ? (float) $row['price'] : null;
		$sale_price = isset( $row['sale_price'] ) && null !== $row['sale_price'] ? (float) $row['sale_price'] : null;

		return array(
			'id'          => (int) ( $row['id'] ?? 0 ),
			'menu_id'     => (int) ( $row['menu_id'] ?? 0 ),
			'category_id' => (int) ( $row['category_id'] ?? 0 ),
			'name'        => (string) ( $row['name'] ?? '' ),
			'description' => (string) ( $row['description'] ?? '' ),
			'image_id'    => $image_id,
			'image'       => $image_id > 0 ? wp_get_attachment_image_url( $image_id, 'thumbnail' ) : '',
			'price'       => $price,
			'sale_price'  => $sale_price,
			'price_type'  => $price_type,
			// A plain text summary in the site currency, so admin lists do not
			// have to render raw column values such as "11.99 (14.5)".
			'price_display' => self::price_summary( $price_type, $price, $sale_price, $variations ),
			'variations'  => $variations,
			'badge'       => isset( $settings['badge'] ) ? (string) $settings['badge'] : '',
			'sort_order'  => (int) ( $row['sort_order'] ?? 0 ),
			'status'      => sanitize_status( $row['status'] ?? 'active' ),
			'created_at'  => (string) ( $row['created_at'] ?? '' ),
			'updated_at'  => (string) ( $row['updated_at'] ?? '' ),
		);
	}

	/**
	 * Normalise stored price variations.
	 *
	 * @param mixed $raw Raw variation data.
	 * @return array<int,array<string,mixed>>
	 */
	private static function prepare_variations( $raw ): array {
		if ( ! is_array( $raw ) ) {
			return array();
		}

		$variations = array();

		foreach ( $raw as $variation ) {
			if ( ! is_array( $variation ) ) {
				continue;
			}

			$label = isset( $variation['label'] ) ? (string) $variation['label'] : '';
			$price = isset( $variation['price'] ) ? sanitize_price( $variation['price'] ) : null;

			if ( '' === $label && null === $price ) {
				continue;
			}

			$variations[] = array(
				'label'      => $label,
				'price'      => $price,
				'sale_price' => isset( $variation['sale_price'] ) ? sanitize_price( $variation['sale_price'] ) : null,
			);

			if ( count( $variations ) >= self::MAX_VARIATIONS ) {
				break;
			}
		}

		return $variations;
	}

	/**
	 * Human readable price for admin lists, in the configured currency.
	 *
	 * @param string                        $price_type single or multiple.
	 * @param float|null                    $price      Regular price.
	 * @param float|null                    $sale_price Sale price.
	 * @param array<int,array<string,mixed>> $variations Prepared variations.
	 * @return string
	 */
	private static function price_summary( string $price_type, ?float $price, ?float $sale_price, array $variations ): string {
		if ( 'multiple' === $price_type && ! empty( $variations ) ) {
			$parts = array();

			foreach ( $variations as $variation ) {
				$amount = null === $variation['sale_price'] ? $variation['price'] : $variation['sale_price'];
				$value  = Currency::format( $amount );

				if ( '' === $value ) {
					continue;
				}

				$label   = (string) $variation['label'];
				$parts[] = '' !== $label ? $label . ' ' . $value : $value;
			}

			return implode( ' · ', $parts );
		}

		if ( null !== $sale_price ) {
			$was = Currency::format( $price );
			$now = Currency::format( $sale_price );

			if ( '' !== $was ) {
				/* translators: 1: sale price, 2: original price. */
				return sprintf( __( '%1$s (was %2$s)', 'restaurant-menu-builder' ), $now, $was );
			}

			return $now;
		}

		return Currency::format( $price );
	}

	/**
	 * Find one item.
	 *
	 * @param int $id Item ID.
	 * @return array<string,mixed>|null
	 */
	public static function find( int $id ): ?array {
		$row = Database::find( Database::ITEMS, $id );

		return null === $row ? null : self::prepare( $row );
	}

	/**
	 * List items.
	 *
	 * @param array<string,mixed> $args Query arguments: menu_id, category_id, status, search.
	 * @return array<int,array<string,mixed>>
	 */
	public static function all( array $args = array() ): array {
		global $wpdb;

		$args = wp_parse_args(
			$args,
			array(
				'menu_id'     => 0,
				'category_id' => 0,
				'status'      => '',
				'search'      => '',
			)
		);

		$table  = Database::table( Database::ITEMS );
		$where  = array( '1=1' );
		$params = array();

		if ( (int) $args['category_id'] > 0 ) {
			$where[]  = 'category_id = %d';
			$params[] = (int) $args['category_id'];
		}

		if ( (int) $args['menu_id'] > 0 ) {
			$where[]  = 'menu_id = %d';
			$params[] = (int) $args['menu_id'];
		}

		if ( '' !== $args['status'] ) {
			$where[]  = 'status = %s';
			$params[] = sanitize_status( $args['status'] );
		}

		if ( '' !== $args['search'] ) {
			$where[]  = 'name LIKE %s';
			$params[] = '%' . $wpdb->esc_like( (string) $args['search'] ) . '%';
		}

		$clause = implode( ' AND ', $where );
		$sql    = "SELECT * FROM {$table} WHERE {$clause} ORDER BY sort_order ASC, id ASC";

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery
		$rows = $params ? $wpdb->get_results( $wpdb->prepare( $sql, $params ), ARRAY_A ) : $wpdb->get_results( $sql, ARRAY_A );
		// phpcs:enable

		$items = array();

		foreach ( (array) $rows as $row ) {
			$items[] = self::prepare( (array) $row );
		}

		return $items;
	}

	/**
	 * Load every item of a menu grouped by category in a single query.
	 *
	 * This is what keeps rendering free of N+1 queries.
	 *
	 * @param int    $menu_id Menu ID.
	 * @param string $status  Optional status filter.
	 * @return array<int,array<int,array<string,mixed>>> Category ID => items.
	 */
	public static function grouped_by_category( int $menu_id, string $status = '' ): array {
		$items   = self::all(
			array(
				'menu_id' => $menu_id,
				'status'  => $status,
			)
		);
		$grouped = array();

		foreach ( $items as $item ) {
			$grouped[ $item['category_id'] ][] = $item;
		}

		return $grouped;
	}

	/**
	 * Create an item.
	 *
	 * @param array<string,mixed> $input Raw input.
	 * @return int|\WP_Error
	 */
	public static function create( array $input ) {
		$category_id = absint( $input['category_id'] ?? 0 );
		$category    = Category::find( $category_id );

		if ( null === $category ) {
			return new \WP_Error( 'rmb_category_missing', __( 'Choose a category for this item.', 'restaurant-menu-builder' ), array( 'status' => 400 ) );
		}

		$name = sanitize_text_field( (string) ( $input['name'] ?? '' ) );

		if ( '' === $name ) {
			return new \WP_Error( 'rmb_item_name_required', __( 'Enter an item name.', 'restaurant-menu-builder' ), array( 'status' => 400 ) );
		}

		$pricing = self::sanitize_pricing( $input );

		$id = Database::insert(
			Database::ITEMS,
			array(
				'menu_id'     => (int) $category['menu_id'],
				'category_id' => $category_id,
				'name'        => substr( $name, 0, 191 ),
				'description' => sanitize_textarea_field( (string) ( $input['description'] ?? '' ) ),
				'image_id'    => Category::sanitize_image_id( $input['image_id'] ?? 0 ),
				'price'       => $pricing['price'],
				'sale_price'  => $pricing['sale_price'],
				'price_type'  => $pricing['price_type'],
				'sort_order'  => isset( $input['sort_order'] ) ? absint( $input['sort_order'] ) : Database::next_sort_order( Database::ITEMS, 'category_id', $category_id ),
				'status'      => isset( $input['status'] ) ? sanitize_status( $input['status'] ) : 'active',
				'settings'    => encode_json( $pricing['settings'] ),
			),
			array( '%d', '%d', '%s', '%s', '%d', '%s', '%s', '%s', '%d', '%s', '%s' )
		);

		if ( 0 === $id ) {
			return new \WP_Error( 'rmb_item_not_created', __( 'Unable to save the item. Please try again.', 'restaurant-menu-builder' ), array( 'status' => 500 ) );
		}

		Cache::flush();

		return $id;
	}

	/**
	 * Update an item.
	 *
	 * @param int                 $id    Item ID.
	 * @param array<string,mixed> $input Raw input.
	 * @return true|\WP_Error
	 */
	public static function update( int $id, array $input ) {
		$item = self::find( $id );

		if ( null === $item ) {
			return new \WP_Error( 'rmb_item_missing', __( 'That item no longer exists.', 'restaurant-menu-builder' ), array( 'status' => 404 ) );
		}

		$data    = array();
		$formats = array();

		if ( array_key_exists( 'category_id', $input ) ) {
			$category = Category::find( absint( $input['category_id'] ) );

			if ( null === $category ) {
				return new \WP_Error( 'rmb_category_missing', __( 'Choose a category for this item.', 'restaurant-menu-builder' ), array( 'status' => 400 ) );
			}

			if ( $category['id'] !== $item['category_id'] ) {
				$data['category_id']    = $category['id'];
				$formats['category_id'] = '%d';
				$data['menu_id']        = (int) $category['menu_id'];
				$formats['menu_id']     = '%d';
				$data['sort_order']     = Database::next_sort_order( Database::ITEMS, 'category_id', $category['id'] );
				$formats['sort_order']  = '%d';
			}
		}

		if ( array_key_exists( 'name', $input ) ) {
			$name = sanitize_text_field( (string) $input['name'] );

			if ( '' === $name ) {
				return new \WP_Error( 'rmb_item_name_required', __( 'Enter an item name.', 'restaurant-menu-builder' ), array( 'status' => 400 ) );
			}

			$data['name']    = substr( $name, 0, 191 );
			$formats['name'] = '%s';
		}

		if ( array_key_exists( 'description', $input ) ) {
			$data['description']    = sanitize_textarea_field( (string) $input['description'] );
			$formats['description'] = '%s';
		}

		if ( array_key_exists( 'image_id', $input ) ) {
			$data['image_id']    = Category::sanitize_image_id( $input['image_id'] );
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

		$touches_pricing = array_intersect( array( 'price', 'sale_price', 'price_type', 'variations', 'badge' ), array_keys( $input ) );

		if ( ! empty( $touches_pricing ) ) {
			$pricing = self::sanitize_pricing( $input, $item );

			$data['price']         = $pricing['price'];
			$formats['price']      = '%s';
			$data['sale_price']    = $pricing['sale_price'];
			$formats['sale_price'] = '%s';
			$data['price_type']    = $pricing['price_type'];
			$formats['price_type'] = '%s';
			$data['settings']      = encode_json( $pricing['settings'] );
			$formats['settings']   = '%s';
		}

		if ( empty( $data ) ) {
			return true;
		}

		// Formats are keyed by column and flattened here, so a column written by
		// more than one branch above cannot shift the remaining formats out of
		// step with $data — which previously stored a decimal price as %d.
		$format = array();

		foreach ( array_keys( $data ) as $column ) {
			$format[] = $formats[ $column ];
		}

		if ( ! Database::update( Database::ITEMS, $id, $data, $format ) ) {
			return new \WP_Error( 'rmb_item_not_saved', __( 'Unable to save the item. Please try again.', 'restaurant-menu-builder' ), array( 'status' => 500 ) );
		}

		Cache::flush();

		return true;
	}

	/**
	 * Delete an item.
	 *
	 * @param int $id Item ID.
	 * @return true|\WP_Error
	 */
	public static function delete( int $id ) {
		if ( null === self::find( $id ) ) {
			return new \WP_Error( 'rmb_item_missing', __( 'That item no longer exists.', 'restaurant-menu-builder' ), array( 'status' => 404 ) );
		}

		if ( ! Database::delete( Database::ITEMS, $id ) ) {
			return new \WP_Error( 'rmb_item_not_deleted', __( 'Unable to delete the item. Please try again.', 'restaurant-menu-builder' ), array( 'status' => 500 ) );
		}

		Cache::flush();

		return true;
	}

	/**
	 * Duplicate an item.
	 *
	 * @param int $id Item ID.
	 * @return int|\WP_Error New item ID.
	 */
	public static function duplicate( int $id ) {
		$item = self::find( $id );

		if ( null === $item ) {
			return new \WP_Error( 'rmb_item_missing', __( 'That item no longer exists.', 'restaurant-menu-builder' ), array( 'status' => 404 ) );
		}

		$new_id = self::clone_row( $item, $item['menu_id'], $item['category_id'], copy_name( $item['name'] ) );

		if ( 0 === $new_id ) {
			return new \WP_Error( 'rmb_item_not_duplicated', __( 'Unable to duplicate the item. Please try again.', 'restaurant-menu-builder' ), array( 'status' => 500 ) );
		}

		Cache::flush();

		return $new_id;
	}

	/**
	 * Insert a copy of a prepared item row.
	 *
	 * @param array<string,mixed> $item        Prepared item.
	 * @param int                 $menu_id     Target menu ID.
	 * @param int                 $category_id Target category ID.
	 * @param string              $name        Name for the copy.
	 * @return int
	 */
	public static function clone_row( array $item, int $menu_id, int $category_id, string $name ): int {
		return Database::insert(
			Database::ITEMS,
			array(
				'menu_id'     => $menu_id,
				'category_id' => $category_id,
				'name'        => substr( $name, 0, 191 ),
				'description' => (string) $item['description'],
				'image_id'    => (int) $item['image_id'],
				'price'       => null === $item['price'] ? null : number_format( (float) $item['price'], 2, '.', '' ),
				'sale_price'  => null === $item['sale_price'] ? null : number_format( (float) $item['sale_price'], 2, '.', '' ),
				'price_type'  => (string) $item['price_type'],
				'sort_order'  => Database::next_sort_order( Database::ITEMS, 'category_id', $category_id ),
				'status'      => (string) $item['status'],
				'settings'    => encode_json(
					array(
						'variations' => $item['variations'],
						'badge'      => $item['badge'],
					)
				),
			),
			array( '%d', '%d', '%s', '%s', '%d', '%s', '%s', '%s', '%d', '%s', '%s' )
		);
	}

	/**
	 * Persist a new item order inside a category.
	 *
	 * @param int   $category_id Category ID.
	 * @param int[] $ids         Ordered item IDs.
	 * @return true|\WP_Error
	 */
	public static function reorder( int $category_id, array $ids ) {
		$existing = wp_list_pluck( self::all( array( 'category_id' => $category_id ) ), 'id' );
		$ids      = array_values( array_map( 'absint', $ids ) );
		$filtered = array_values( array_intersect( $ids, $existing ) );

		if ( empty( $filtered ) ) {
			return new \WP_Error( 'rmb_reorder_invalid', __( 'Unable to save the new order. Please reload the page and try again.', 'restaurant-menu-builder' ), array( 'status' => 400 ) );
		}

		foreach ( $existing as $existing_id ) {
			if ( ! in_array( $existing_id, $filtered, true ) ) {
				$filtered[] = $existing_id;
			}
		}

		Database::apply_order( Database::ITEMS, $filtered );
		Cache::flush();

		return true;
	}

	/**
	 * Sanitize pricing input into storable columns.
	 *
	 * @param array<string,mixed>      $input   Raw input.
	 * @param array<string,mixed>|null $current Current prepared item.
	 * @return array{price:?string,sale_price:?string,price_type:string,settings:array<string,mixed>}
	 */
	private static function sanitize_pricing( array $input, ?array $current = null ): array {
		$price_type = $input['price_type'] ?? ( $current['price_type'] ?? 'single' );
		$price_type = 'multiple' === sanitize_key( (string) $price_type ) ? 'multiple' : 'single';

		$price      = array_key_exists( 'price', $input ) ? sanitize_price( $input['price'] ) : ( $current['price'] ?? null );
		$sale_price = array_key_exists( 'sale_price', $input ) ? sanitize_price( $input['sale_price'] ) : ( $current['sale_price'] ?? null );

		// A sale price is only meaningful when it undercuts the regular price.
		if ( null !== $sale_price && ( null === $price || $sale_price >= $price ) ) {
			$sale_price = null;
		}

		$variations = array();

		if ( array_key_exists( 'variations', $input ) ) {
			$raw = is_array( $input['variations'] ) ? $input['variations'] : array();

			foreach ( $raw as $variation ) {
				if ( ! is_array( $variation ) ) {
					continue;
				}

				$label           = sanitize_text_field( (string) ( $variation['label'] ?? '' ) );
				$variation_price = sanitize_price( $variation['price'] ?? null );
				$variation_sale  = sanitize_price( $variation['sale_price'] ?? null );

				if ( '' === $label && null === $variation_price ) {
					continue;
				}

				if ( null !== $variation_sale && ( null === $variation_price || $variation_sale >= $variation_price ) ) {
					$variation_sale = null;
				}

				$variations[] = array(
					'label'      => substr( $label, 0, 80 ),
					'price'      => $variation_price,
					'sale_price' => $variation_sale,
				);

				if ( count( $variations ) >= self::MAX_VARIATIONS ) {
					break;
				}
			}
		} elseif ( null !== $current ) {
			$variations = $current['variations'];
		}

		if ( 'multiple' === $price_type && empty( $variations ) ) {
			$price_type = 'single';
		}

		$badge = array_key_exists( 'badge', $input )
			? sanitize_text_field( (string) $input['badge'] )
			: (string) ( $current['badge'] ?? '' );

		return array(
			'price'      => null === $price ? null : number_format( $price, 2, '.', '' ),
			'sale_price' => null === $sale_price ? null : number_format( $sale_price, 2, '.', '' ),
			'price_type' => $price_type,
			'settings'   => array(
				'variations' => $variations,
				'badge'      => substr( $badge, 0, 40 ),
			),
		);
	}

	/**
	 * Count items for many menus in one query.
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

		$table        = Database::table( Database::ITEMS );
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
	 * Count every item across all menus.
	 *
	 * @return int
	 */
	public static function count_all(): int {
		global $wpdb;

		$table = Database::table( Database::ITEMS );

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
		// phpcs:enable
	}

	/**
	 * Count items in a menu.
	 *
	 * @param int $menu_id Menu ID.
	 * @return int
	 */
	public static function count( int $menu_id ): int {
		global $wpdb;

		$table = Database::table( Database::ITEMS );

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery
		return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE menu_id = %d", $menu_id ) );
		// phpcs:enable
	}
}
