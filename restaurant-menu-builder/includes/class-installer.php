<?php
/**
 * Installation, schema and migrations.
 *
 * @package RestaurantMenuBuilder
 */

declare( strict_types=1 );

namespace RestaurantMenuBuilder;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates and upgrades the plugin database schema.
 */
class Installer {

	public const DB_VERSION_OPTION     = 'rmb_db_version';
	public const PLUGIN_VERSION_OPTION = 'rmb_version';

	/**
	 * Activation callback.
	 *
	 * @return void
	 */
	public static function activate(): void {
		if ( version_compare( PHP_VERSION, RMB_MIN_PHP, '<' ) ) {
			deactivate_plugins( RMB_BASENAME );
			wp_die(
				esc_html(
					sprintf(
						/* translators: 1: required PHP version, 2: current PHP version. */
						__( 'Restaurant Menu Builder needs PHP %1$s or newer. This server runs PHP %2$s.', 'restaurant-menu-builder' ),
						RMB_MIN_PHP,
						PHP_VERSION
					)
				),
				esc_html__( 'Plugin activation stopped', 'restaurant-menu-builder' ),
				array( 'back_link' => true )
			);
		}

		self::install();
	}

	/**
	 * Deactivation callback. User data is never removed here.
	 *
	 * @return void
	 */
	public static function deactivate(): void {
		Cache::flush();
	}

	/**
	 * Create tables, run migrations and store versions.
	 *
	 * @return void
	 */
	public static function install(): void {
		self::create_tables();
		self::migrate();

		Settings::install_defaults();

		update_option( self::DB_VERSION_OPTION, RMB_DB_VERSION, false );
		update_option( self::PLUGIN_VERSION_OPTION, RMB_VERSION, false );
	}

	/**
	 * Run the installer when the stored version is behind the code version.
	 *
	 * @return void
	 */
	public static function maybe_upgrade(): void {
		$stored = (string) get_option( self::DB_VERSION_OPTION, '' );

		if ( RMB_DB_VERSION !== $stored ) {
			self::install();

			return;
		}

		// A release can ship without a schema change. The plugin version is still
		// recorded so upgrade routines and support have an accurate value.
		if ( RMB_VERSION !== (string) get_option( self::PLUGIN_VERSION_OPTION, '' ) ) {
			update_option( self::PLUGIN_VERSION_OPTION, RMB_VERSION, false );
		}
	}

	/**
	 * Create or update the schema with dbDelta().
	 *
	 * @return void
	 */
	private static function create_tables(): void {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset = $wpdb->get_charset_collate();

		$menus      = Database::table( Database::MENUS );
		$categories = Database::table( Database::CATEGORIES );
		$items      = Database::table( Database::ITEMS );

		$sql = array();

		$sql[] = "CREATE TABLE {$menus} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			name varchar(191) NOT NULL DEFAULT '',
			slug varchar(191) NOT NULL DEFAULT '',
			status varchar(20) NOT NULL DEFAULT 'draft',
			settings longtext NULL,
			created_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			updated_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id),
			UNIQUE KEY slug (slug),
			KEY status (status)
		) {$charset};";

		$sql[] = "CREATE TABLE {$categories} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			menu_id bigint(20) unsigned NOT NULL DEFAULT 0,
			name varchar(191) NOT NULL DEFAULT '',
			description text NULL,
			icon varchar(64) NOT NULL DEFAULT '',
			image_id bigint(20) unsigned NOT NULL DEFAULT 0,
			sort_order int(11) NOT NULL DEFAULT 0,
			status varchar(20) NOT NULL DEFAULT 'active',
			created_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			updated_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id),
			KEY menu_order (menu_id,status,sort_order),
			KEY menu_id (menu_id)
		) {$charset};";

		$sql[] = "CREATE TABLE {$items} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			menu_id bigint(20) unsigned NOT NULL DEFAULT 0,
			category_id bigint(20) unsigned NOT NULL DEFAULT 0,
			name varchar(191) NOT NULL DEFAULT '',
			description text NULL,
			image_id bigint(20) unsigned NOT NULL DEFAULT 0,
			price decimal(12,2) NULL DEFAULT NULL,
			sale_price decimal(12,2) NULL DEFAULT NULL,
			price_type varchar(20) NOT NULL DEFAULT 'single',
			sort_order int(11) NOT NULL DEFAULT 0,
			status varchar(20) NOT NULL DEFAULT 'active',
			settings longtext NULL,
			created_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			updated_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id),
			KEY category_order (category_id,status,sort_order),
			KEY menu_id (menu_id,status),
			KEY category_id (category_id)
		) {$charset};";

		foreach ( $sql as $statement ) {
			dbDelta( $statement );
		}
	}

	/**
	 * Version aware migrations.
	 *
	 * Each migration is guarded by the stored database version so it runs once.
	 * Version 1.0.0 is the initial schema and needs no data migration.
	 *
	 * @return void
	 */
	private static function migrate(): void {
		$stored = (string) get_option( self::DB_VERSION_OPTION, '' );

		if ( '' === $stored ) {
			// Fresh install; create_tables() already produced the current schema.
			return;
		}

		/**
		 * Fires after the schema has been synchronised, for future migrations.
		 *
		 * @param string $stored Previously stored database version.
		 */
		do_action( 'rmb_migrate', $stored );
	}

	/**
	 * Whether all plugin tables exist.
	 *
	 * @return bool
	 */
	public static function tables_exist(): bool {
		global $wpdb;

		foreach ( Database::tables() as $table ) {
			// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery
			$found = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
			// phpcs:enable

			if ( $found !== $table ) {
				return false;
			}
		}

		return true;
	}
}
