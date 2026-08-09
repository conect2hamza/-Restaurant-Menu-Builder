<?php
/**
 * Shortcode handler.
 *
 * @package RestaurantMenuBuilder
 */

declare( strict_types=1 );

namespace RestaurantMenuBuilder;

use RestaurantMenuBuilder\Frontend\Frontend;
use RestaurantMenuBuilder\Frontend\Renderer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers [restaurant_menu].
 */
class Shortcode {

	public const TAG = 'restaurant_menu';

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		add_shortcode( self::TAG, array( $this, 'render' ) );
	}

	/**
	 * Render the shortcode.
	 *
	 * @param array<string,mixed>|string $atts Shortcode attributes.
	 * @return string
	 */
	public function render( $atts ): string {
		$atts = shortcode_atts(
			array(
				'id'           => 0,
				'slug'         => '',
				'layout'       => '',
				'navigation'   => '',
				'images'       => '',
				'descriptions' => '',
				'prices'       => '',
			),
			is_array( $atts ) ? $atts : array(),
			self::TAG
		);

		$menu_id = absint( $atts['id'] );

		if ( 0 === $menu_id && '' !== (string) $atts['slug'] ) {
			$menu = Menu::find_by_slug( (string) $atts['slug'] );

			if ( null !== $menu ) {
				$menu_id = (int) $menu['id'];
			}
		}

		if ( 0 === $menu_id ) {
			$menu_id = absint( Settings::get( 'default_menu', 0 ) );
		}

		if ( 0 === $menu_id ) {
			if ( current_user_can( 'edit_posts' ) ) {
				return '<div class="rmb-notice">' . esc_html__( 'Add a menu ID to the shortcode, for example [restaurant_menu id="1"].', 'restaurant-menu-builder' ) . '</div>';
			}

			return '';
		}

		$overrides = array();

		if ( '' !== (string) $atts['layout'] ) {
			$overrides['layout'] = sanitize_key( (string) $atts['layout'] );
		}

		$flags = array(
			'navigation'   => 'show_navigation',
			'images'       => 'show_images',
			'descriptions' => 'show_descriptions',
			'prices'       => 'show_prices',
		);

		foreach ( $flags as $attribute => $key ) {
			if ( '' !== (string) $atts[ $attribute ] ) {
				$overrides[ $key ] = Settings::to_bool( $atts[ $attribute ] );
			}
		}

		Frontend::enqueue();

		return Renderer::render( $menu_id, $overrides );
	}

	/**
	 * Count the published posts and pages that embed a menu.
	 *
	 * Used by the dashboard so the "in use" figure reflects reality rather than
	 * a stored counter that could drift. The result is cached for a few minutes
	 * because it is a LIKE scan over post content.
	 *
	 * @return int
	 */
	public static function usage_count(): int {
		global $wpdb;

		$cached = get_transient( 'rmb_shortcode_usage' );

		if ( false !== $cached ) {
			return (int) $cached;
		}

		$like = '%' . $wpdb->esc_like( '[' . self::TAG ) . '%';

		// phpcs:disable WordPress.DB.DirectDatabaseQuery
		$count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status IN ( 'publish', 'draft', 'private' ) AND post_type NOT IN ( 'revision', 'attachment' ) AND post_content LIKE %s",
				$like
			)
		);
		// phpcs:enable

		set_transient( 'rmb_shortcode_usage', $count, 5 * MINUTE_IN_SECONDS );

		return $count;
	}
}
