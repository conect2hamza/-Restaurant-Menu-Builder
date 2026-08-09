<?php
/**
 * Plugin Name:       Restaurant Menu Builder
 * Plugin URI:        https://example.com/restaurant-menu-builder
 * Description:       Build, style and publish responsive restaurant menus from the WordPress dashboard. Menus, categories, items, images, multiple prices and a live preview — published with a shortcode.
 * Version:           1.2.0
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            Hamza Dezinr
 * Author URI:        https://hamzadezinr.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       restaurant-menu-builder
 * Domain Path:       /languages
 *
 * @package RestaurantMenuBuilder
 */

declare( strict_types=1 );

namespace RestaurantMenuBuilder;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( defined( 'RMB_VERSION' ) ) {
	return;
}

define( 'RMB_VERSION', '1.2.0' );
define( 'RMB_DB_VERSION', '1.0.0' );
define( 'RMB_FILE', __FILE__ );
define( 'RMB_PATH', plugin_dir_path( __FILE__ ) );
define( 'RMB_URL', plugin_dir_url( __FILE__ ) );
define( 'RMB_BASENAME', plugin_basename( __FILE__ ) );
define( 'RMB_MIN_PHP', '8.0' );
define( 'RMB_MIN_WP', '6.0' );

/**
 * Class autoloader.
 *
 * Maps RestaurantMenuBuilder\Sub\Class_Name to the matching
 * class-class-name.php file inside the plugin directory.
 *
 * @param string $class_name Fully qualified class name.
 * @return void
 */
function autoload( string $class_name ): void {
	if ( 0 !== strpos( $class_name, __NAMESPACE__ . '\\' ) ) {
		return;
	}

	$relative = substr( $class_name, strlen( __NAMESPACE__ ) + 1 );
	$parts    = explode( '\\', $relative );
	$class    = array_pop( $parts );
	$sub      = strtolower( implode( '/', $parts ) );

	$file = 'class-' . str_replace( '_', '-', strtolower( $class ) ) . '.php';

	$base = RMB_PATH;
	if ( '' !== $sub ) {
		$base .= $sub . '/';
	} else {
		$base .= 'includes/';
	}

	$path = $base . $file;

	if ( is_readable( $path ) ) {
		require_once $path;
	}
}

spl_autoload_register( __NAMESPACE__ . '\\autoload' );

require_once RMB_PATH . 'includes/helpers.php';

/**
 * Check that the server environment can run the plugin.
 *
 * @return string Empty string when supported, otherwise an error message.
 */
function environment_error(): string {
	global $wp_version;

	if ( version_compare( PHP_VERSION, RMB_MIN_PHP, '<' ) ) {
		/* translators: 1: required PHP version, 2: current PHP version. */
		return sprintf( __( 'Restaurant Menu Builder needs PHP %1$s or newer. This server runs PHP %2$s.', 'restaurant-menu-builder' ), RMB_MIN_PHP, PHP_VERSION );
	}

	if ( isset( $wp_version ) && version_compare( $wp_version, RMB_MIN_WP, '<' ) ) {
		/* translators: 1: required WordPress version, 2: current WordPress version. */
		return sprintf( __( 'Restaurant Menu Builder needs WordPress %1$s or newer. This site runs WordPress %2$s.', 'restaurant-menu-builder' ), RMB_MIN_WP, $wp_version );
	}

	return '';
}

register_activation_hook( __FILE__, array( Installer::class, 'activate' ) );
register_deactivation_hook( __FILE__, array( Installer::class, 'deactivate' ) );

/**
 * Boot the plugin once WordPress has loaded.
 *
 * @return void
 */
function boot(): void {
	$error = environment_error();

	if ( '' !== $error ) {
		add_action(
			'admin_notices',
			static function () use ( $error ): void {
				if ( ! current_user_can( 'activate_plugins' ) ) {
					return;
				}
				printf( '<div class="notice notice-error"><p>%s</p></div>', esc_html( $error ) );
			}
		);

		return;
	}

	Plugin::instance()->boot();
}

add_action( 'plugins_loaded', __NAMESPACE__ . '\\boot', 5 );
