<?php
/**
 * Uninstall handler.
 *
 * Data is only removed when the administrator has explicitly opted in from
 * Restaurant Menu → Settings → Advanced. That option is off by default.
 *
 * @package RestaurantMenuBuilder
 */

declare( strict_types=1 );

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Remove the plugin's tables and options for a single site.
 *
 * @return void
 */
function rmb_uninstall_site(): void {
	global $wpdb;

	$settings = get_option( 'rmb_settings', array() );

	$opted_in = is_array( $settings )
		&& isset( $settings['delete_on_uninstall'] )
		&& in_array( $settings['delete_on_uninstall'], array( true, 1, '1', 'yes', 'on', 'true' ), true );

	if ( ! $opted_in ) {
		return;
	}

	$tables = array(
		$wpdb->prefix . 'rmb_items',
		$wpdb->prefix . 'rmb_categories',
		$wpdb->prefix . 'rmb_menus',
	);

	foreach ( $tables as $table ) {
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery
		$wpdb->query( "DROP TABLE IF EXISTS {$table}" );
		// phpcs:enable
	}

	$index = get_option( 'rmb_cache_index', array() );

	if ( is_array( $index ) ) {
		foreach ( $index as $key ) {
			if ( is_string( $key ) ) {
				delete_transient( $key );
			}
		}
	}

	foreach ( array( 'rmb_settings', 'rmb_style', 'rmb_db_version', 'rmb_version', 'rmb_cache_index', 'rmb_cache_generation' ) as $option ) {
		delete_option( $option );
	}
}

if ( is_multisite() ) {
	$rmb_sites = get_sites( array( 'number' => 0 ) );

	foreach ( $rmb_sites as $rmb_site ) {
		switch_to_blog( (int) $rmb_site->blog_id );
		rmb_uninstall_site();
		restore_current_blog();
	}
} else {
	rmb_uninstall_site();
}
