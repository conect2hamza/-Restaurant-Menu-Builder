<?php
/**
 * Database access layer.
 *
 * @package RestaurantMenuBuilder
 */

declare( strict_types=1 );

namespace RestaurantMenuBuilder;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provides table names and low level query primitives.
 *
 * Every table name is derived from $wpdb->prefix, never hard coded.
 */
class Database {

	public const MENUS      = 'rmb_menus';
	public const CATEGORIES = 'rmb_categories';
	public const ITEMS      = 'rmb_items';

	/**
	 * Fully qualified table name.
	 *
	 * @param string $table One of the class constants.
	 * @return string
	 */
	public static function table( string $table ): string {
		global $wpdb;

		return $wpdb->prefix . $table;
	}

	/**
	 * All table names managed by the plugin.
	 *
	 * @return string[]
	 */
	public static function tables(): array {
		return array(
			self::table( self::MENUS ),
			self::table( self::CATEGORIES ),
			self::table( self::ITEMS ),
		);
	}

	/**
	 * Fetch a single row by primary key.
	 *
	 * @param string $table Table constant.
	 * @param int    $id    Row ID.
	 * @return array<string,mixed>|null
	 */
	public static function find( string $table, int $id ): ?array {
		global $wpdb;

		if ( $id <= 0 ) {
			return null;
		}

		$name = self::table( $table );

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery
		$row = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$name} WHERE id = %d", $id ),
			ARRAY_A
		);
		// phpcs:enable

		return is_array( $row ) ? $row : null;
	}

	/**
	 * Insert a row.
	 *
	 * @param string              $table  Table constant.
	 * @param array<string,mixed> $data   Column data.
	 * @param string[]            $format Column formats.
	 * @return int Inserted ID, or 0 on failure.
	 */
	public static function insert( string $table, array $data, array $format ): int {
		global $wpdb;

		$now = current_time( 'mysql' );

		$data['created_at'] = $now;
		$data['updated_at'] = $now;
		$format[]           = '%s';
		$format[]           = '%s';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$result = $wpdb->insert( self::table( $table ), $data, $format );

		if ( false === $result ) {
			return 0;
		}

		return (int) $wpdb->insert_id;
	}

	/**
	 * Update a row by primary key.
	 *
	 * @param string              $table  Table constant.
	 * @param int                 $id     Row ID.
	 * @param array<string,mixed> $data   Column data.
	 * @param string[]            $format Column formats.
	 * @return bool
	 */
	public static function update( string $table, int $id, array $data, array $format ): bool {
		global $wpdb;

		if ( $id <= 0 ) {
			return false;
		}

		$data['updated_at'] = current_time( 'mysql' );
		$format[]           = '%s';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$result = $wpdb->update( self::table( $table ), $data, array( 'id' => $id ), $format, array( '%d' ) );

		return false !== $result;
	}

	/**
	 * Delete a row by primary key.
	 *
	 * @param string $table Table constant.
	 * @param int    $id    Row ID.
	 * @return bool
	 */
	public static function delete( string $table, int $id ): bool {
		global $wpdb;

		if ( $id <= 0 ) {
			return false;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$result = $wpdb->delete( self::table( $table ), array( 'id' => $id ), array( '%d' ) );

		return (bool) $result;
	}

	/**
	 * Next sort order value for a scoped column.
	 *
	 * @param string $table  Table constant.
	 * @param string $column Scope column (menu_id or category_id).
	 * @param int    $value  Scope value.
	 * @return int
	 */
	public static function next_sort_order( string $table, string $column, int $value ): int {
		global $wpdb;

		$name   = self::table( $table );
		$column = in_array( $column, array( 'menu_id', 'category_id' ), true ) ? $column : 'menu_id';

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery
		$max = $wpdb->get_var(
			$wpdb->prepare( "SELECT MAX(sort_order) FROM {$name} WHERE {$column} = %d", $value )
		);
		// phpcs:enable

		return null === $max ? 0 : (int) $max + 1;
	}

	/**
	 * Persist a new sort order for a list of IDs.
	 *
	 * @param string $table Table constant.
	 * @param int[]  $ids   Ordered IDs.
	 * @return int Number of rows updated.
	 */
	public static function apply_order( string $table, array $ids ): int {
		global $wpdb;

		$name    = self::table( $table );
		$updated = 0;

		foreach ( array_values( $ids ) as $position => $id ) {
			$id = (int) $id;

			if ( $id <= 0 ) {
				continue;
			}

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$result = $wpdb->update(
				$name,
				array(
					'sort_order' => $position,
					'updated_at' => current_time( 'mysql' ),
				),
				array( 'id' => $id ),
				array( '%d', '%s' ),
				array( '%d' )
			);

			if ( false !== $result ) {
				++$updated;
			}
		}

		return $updated;
	}
}
