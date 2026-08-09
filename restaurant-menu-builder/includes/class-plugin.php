<?php
/**
 * Plugin container.
 *
 * @package RestaurantMenuBuilder
 */

declare( strict_types=1 );

namespace RestaurantMenuBuilder;

use RestaurantMenuBuilder\Admin\Admin;
use RestaurantMenuBuilder\Frontend\Frontend;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wires the subsystems together. Nothing else touches the global namespace.
 */
final class Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var Plugin|null
	 */
	private static ?Plugin $instance = null;

	/**
	 * Whether boot() has already run.
	 *
	 * @var bool
	 */
	private bool $booted = false;

	/**
	 * Private constructor.
	 */
	private function __construct() {}

	/**
	 * Get the shared instance.
	 *
	 * @return Plugin
	 */
	public static function instance(): Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Register every hook the plugin needs.
	 *
	 * @return void
	 */
	public function boot(): void {
		if ( $this->booted ) {
			return;
		}

		$this->booted = true;

		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_action( 'admin_init', array( Installer::class, 'maybe_upgrade' ) );

		( new Shortcode() )->register();
		( new Rest_Api() )->register();
		( new Frontend() )->register();

		if ( is_admin() ) {
			( new Admin() )->register();
		}

		/**
		 * Fires once the plugin has registered its hooks.
		 *
		 * @param Plugin $plugin Plugin instance.
		 */
		do_action( 'rmb_loaded', $this );
	}

	/**
	 * Load translations.
	 *
	 * @return void
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain(
			'restaurant-menu-builder',
			false,
			dirname( RMB_BASENAME ) . '/languages'
		);
	}
}
