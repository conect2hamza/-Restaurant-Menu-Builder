<?php
/**
 * Frontend controller.
 *
 * @package RestaurantMenuBuilder
 */

declare( strict_types=1 );

namespace RestaurantMenuBuilder\Frontend;

use RestaurantMenuBuilder\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers frontend assets and only enqueues them where a menu is present.
 */
class Frontend {

	public const STYLE_HANDLE  = 'rmb-frontend';
	public const SCRIPT_HANDLE = 'rmb-frontend';

	/**
	 * Whether assets have already been enqueued for this request.
	 *
	 * @var bool
	 */
	private static bool $enqueued = false;

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ), 5 );
		add_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue' ), 20 );
	}

	/**
	 * Register, but do not enqueue, the frontend assets.
	 *
	 * @return void
	 */
	public function register_assets(): void {
		wp_register_style(
			self::STYLE_HANDLE,
			RMB_URL . 'frontend/css/frontend.css',
			array(),
			RMB_VERSION
		);

		wp_register_script(
			self::SCRIPT_HANDLE,
			RMB_URL . 'frontend/js/frontend.js',
			array(),
			RMB_VERSION,
			true
		);
	}

	/**
	 * Enqueue assets when the current post contains a menu.
	 *
	 * @return void
	 */
	public function maybe_enqueue(): void {
		if ( self::$enqueued || ! $this->post_has_menu() ) {
			return;
		}

		self::enqueue();
	}

	/**
	 * Enqueue the frontend assets and the global custom CSS.
	 *
	 * Called again from the shortcode so menus rendered by page builders,
	 * widgets or template calls still get their styles.
	 *
	 * @return void
	 */
	public static function enqueue(): void {
		if ( self::$enqueued ) {
			return;
		}

		self::$enqueued = true;

		wp_enqueue_style( self::STYLE_HANDLE );
		wp_enqueue_script( self::SCRIPT_HANDLE );

		$custom_css = Settings::sanitize_css( (string) Settings::get( 'custom_css', '' ) );

		if ( '' !== $custom_css ) {
			wp_add_inline_style( self::STYLE_HANDLE, $custom_css );
		}
	}

	/**
	 * Detect a menu in the current post content.
	 *
	 * @return bool
	 */
	private function post_has_menu(): bool {
		if ( is_admin() ) {
			return false;
		}

		$post = get_post();

		if ( ! $post instanceof \WP_Post ) {
			return false;
		}

		$content = (string) $post->post_content;

		if ( has_shortcode( $content, 'restaurant_menu' ) ) {
			return true;
		}

		// Elementor and similar builders store their data in post meta.
		$builder_data = get_post_meta( $post->ID, '_elementor_data', true );

		if ( is_string( $builder_data ) && false !== strpos( $builder_data, 'restaurant_menu' ) ) {
			return true;
		}

		/**
		 * Filter whether frontend assets should load on the current request.
		 *
		 * @param bool     $needed Whether a menu is present.
		 * @param \WP_Post $post   Current post.
		 */
		return (bool) apply_filters( 'rmb_needs_frontend_assets', false, $post );
	}
}
