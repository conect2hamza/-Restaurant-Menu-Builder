<?php
/**
 * Rendered menu cache.
 *
 * @package RestaurantMenuBuilder
 */

declare( strict_types=1 );

namespace RestaurantMenuBuilder;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stores rendered menu markup in transients and invalidates it on every write.
 */
class Cache {

	private const PREFIX        = 'rmb_render_';
	private const INDEX_OPTION  = 'rmb_cache_index';
	private const GENERATION    = 'rmb_cache_generation';

	/**
	 * Current cache generation. Bumping it invalidates every stored entry.
	 *
	 * @return int
	 */
	public static function generation(): int {
		return (int) get_option( self::GENERATION, 1 );
	}

	/**
	 * Build a cache key from a rendering context.
	 *
	 * @param int                 $menu_id Menu ID.
	 * @param array<string,mixed> $context Effective render context.
	 * @return string
	 */
	public static function key( int $menu_id, array $context ): string {
		$hash = md5( wp_json_encode( $context ) . '|' . self::generation() . '|' . RMB_VERSION );

		return self::PREFIX . $menu_id . '_' . substr( $hash, 0, 16 );
	}

	/**
	 * Read a cached render.
	 *
	 * @param string $key Cache key.
	 * @return string|null
	 */
	public static function get( string $key ): ?string {
		if ( ! Settings::to_bool( Settings::get( 'cache_enabled', true ) ) ) {
			return null;
		}

		$value = get_transient( $key );

		return is_string( $value ) ? $value : null;
	}

	/**
	 * Store a rendered menu.
	 *
	 * @param string $key  Cache key.
	 * @param string $html Rendered markup.
	 * @return void
	 */
	public static function set( string $key, string $html ): void {
		if ( ! Settings::to_bool( Settings::get( 'cache_enabled', true ) ) ) {
			return;
		}

		$hours = (int) Settings::get( 'cache_duration', 12 );
		$hours = max( 1, min( 720, $hours ) );

		set_transient( $key, $html, $hours * HOUR_IN_SECONDS );

		$index = get_option( self::INDEX_OPTION, array() );
		$index = is_array( $index ) ? $index : array();

		if ( ! in_array( $key, $index, true ) ) {
			$index[] = $key;

			// Keep the index bounded; the generation counter is the real invalidator.
			if ( count( $index ) > 500 ) {
				$index = array_slice( $index, -500 );
			}

			update_option( self::INDEX_OPTION, $index, false );
		}
	}

	/**
	 * Invalidate every cached render.
	 *
	 * @return void
	 */
	public static function flush(): void {
		$index = get_option( self::INDEX_OPTION, array() );

		if ( is_array( $index ) ) {
			foreach ( $index as $key ) {
				if ( is_string( $key ) ) {
					delete_transient( $key );
				}
			}
		}

		update_option( self::INDEX_OPTION, array(), false );
		update_option( self::GENERATION, self::generation() + 1, false );
	}
}
