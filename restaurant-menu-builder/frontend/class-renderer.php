<?php
/**
 * Frontend renderer.
 *
 * @package RestaurantMenuBuilder
 */

declare( strict_types=1 );

namespace RestaurantMenuBuilder\Frontend;

use RestaurantMenuBuilder\Cache;
use RestaurantMenuBuilder\Category;
use RestaurantMenuBuilder\Currency;
use RestaurantMenuBuilder\Icons;
use RestaurantMenuBuilder\Item;
use RestaurantMenuBuilder\Menu;
use RestaurantMenuBuilder\Settings;
use RestaurantMenuBuilder\Style;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders a menu into HTML using the template files.
 */
class Renderer {

	/**
	 * Instance counter so each rendered menu gets a unique DOM id.
	 *
	 * @var int
	 */
	private static int $instance = 0;

	/**
	 * Build the effective rendering context for a menu.
	 *
	 * @param array<string,mixed> $menu      Prepared menu.
	 * @param array<string,mixed> $overrides Shortcode overrides.
	 * @return array<string,mixed>
	 */
	public static function context( array $menu, array $overrides = array() ): array {
		$settings = $menu['settings'];

		$layout = (string) $settings['layout'];

		if ( isset( $overrides['layout'] ) && array_key_exists( (string) $overrides['layout'], Settings::layouts() ) ) {
			$layout = (string) $overrides['layout'];
		}

		$context = array(
			'menu_id'           => (int) $menu['id'],
			'layout'            => $layout,
			'show_navigation'   => (bool) $settings['show_navigation'],
			'show_images'       => (bool) $settings['show_images'],
			'show_descriptions' => (bool) $settings['show_descriptions'],
			'show_prices'       => (bool) $settings['show_prices'],
			'style'             => Style::for_menu( $menu ),
			'custom_css'        => (string) $settings['custom_css'],
			'currency'          => (string) Settings::get( 'currency', 'USD' ),
			'currency_position' => (string) Settings::get( 'currency_position', 'before' ),
			'image_size'        => (string) Settings::get( 'image_size', 'medium' ),
			'lazy_loading'      => Settings::to_bool( Settings::get( 'lazy_loading', true ) ),
		);

		foreach ( array( 'show_navigation', 'show_images', 'show_descriptions', 'show_prices' ) as $flag ) {
			if ( array_key_exists( $flag, $overrides ) ) {
				$context[ $flag ] = Settings::to_bool( $overrides[ $flag ] );
			}
		}

		/**
		 * Filter the rendering context before a menu is rendered.
		 *
		 * @param array<string,mixed> $context Render context.
		 * @param array<string,mixed> $menu    Prepared menu.
		 */
		return apply_filters( 'rmb_render_context', $context, $menu );
	}

	/**
	 * Render a menu by ID.
	 *
	 * @param int                      $menu_id   Menu ID.
	 * @param array<string,mixed>      $overrides Shortcode overrides.
	 * @param bool                     $preview   Render draft content and skip the cache.
	 * @param array<string,mixed>|null $menu_data Pre-loaded menu, used to preview unsaved settings.
	 * @return string
	 */
	public static function render( int $menu_id, array $overrides = array(), bool $preview = false, ?array $menu_data = null ): string {
		$menu = null !== $menu_data ? $menu_data : Menu::find( $menu_id );

		if ( null === $menu ) {
			return self::notice( __( 'This menu is no longer available.', 'restaurant-menu-builder' ), $preview );
		}

		if ( 'active' !== $menu['status'] && ! $preview ) {
			return self::notice( __( 'This menu is still a draft. Set it to Active to publish it.', 'restaurant-menu-builder' ), false );
		}

		$context = self::context( $menu, $overrides );

		if ( ! $preview ) {
			$key    = Cache::key( $menu_id, $context );
			$cached = Cache::get( $key );

			if ( null !== $cached ) {
				return $cached;
			}
		}

		$status     = $preview ? '' : 'active';
		$categories = Category::all(
			array(
				'menu_id' => $menu_id,
				'status'  => $status,
			)
		);
		$grouped    = Item::grouped_by_category( $menu_id, $status );

		if ( empty( $categories ) ) {
			return self::notice( __( 'This menu has no categories yet.', 'restaurant-menu-builder' ), $preview );
		}

		++self::$instance;

		$data = array(
			'menu'       => $menu,
			'context'    => $context,
			'categories' => $categories,
			'items'      => $grouped,
			'instance'   => 'rmb-menu-' . $menu_id . '-' . self::$instance,
		);

		$html = self::template( 'menu', $data );

		if ( ! $preview ) {
			Cache::set( Cache::key( $menu_id, $context ), $html );
		}

		return $html;
	}

	/**
	 * Load a template file and return its output.
	 *
	 * Themes can override any template by placing a file at
	 * restaurant-menu-builder/{name}.php in the theme directory.
	 *
	 * @param string              $name Template name without extension.
	 * @param array<string,mixed> $data Variables exposed to the template.
	 * @return string
	 */
	public static function template( string $name, array $data ): string {
		$name = sanitize_file_name( $name );
		$file = locate_template( array( 'restaurant-menu-builder/' . $name . '.php' ) );

		if ( '' === $file ) {
			$file = RMB_PATH . 'frontend/templates/' . $name . '.php';
		}

		/**
		 * Filter the resolved template path.
		 *
		 * @param string              $file Absolute template path.
		 * @param string              $name Template name.
		 * @param array<string,mixed> $data Template data.
		 */
		$file = (string) apply_filters( 'rmb_template_path', $file, $name, $data );

		if ( ! is_readable( $file ) ) {
			return '';
		}

		ob_start();

		// Templates read from $rmb.
		$rmb = $data;
		include $file;

		return (string) ob_get_clean();
	}

	/**
	 * Render an item price block.
	 *
	 * @param array<string,mixed> $item    Prepared item.
	 * @param array<string,mixed> $context Render context.
	 * @return string
	 */
	public static function price_html( array $item, array $context ): string {
		if ( empty( $context['show_prices'] ) ) {
			return '';
		}

		$currency = (string) $context['currency'];
		$position = (string) $context['currency_position'];

		if ( 'multiple' === $item['price_type'] && ! empty( $item['variations'] ) ) {
			$parts = array();

			foreach ( $item['variations'] as $variation ) {
				$amount = null === $variation['sale_price'] ? $variation['price'] : $variation['sale_price'];
				$value  = Currency::format( $amount, $currency, $position );

				if ( '' === $value && '' === (string) $variation['label'] ) {
					continue;
				}

				$label = (string) $variation['label'];
				$was   = null === $variation['sale_price'] ? '' : Currency::format( $variation['price'], $currency, $position );

				$parts[] = sprintf(
					'<li class="rmb-price-variation%1$s"><span class="rmb-price-label">%2$s</span><span class="rmb-price-value">%3$s%4$s</span></li>',
					'' === $was ? '' : ' is-sale',
					esc_html( $label ),
					'' === $was ? '' : '<s class="rmb-price-was">' . esc_html( $was ) . '</s> ',
					esc_html( $value )
				);
			}

			if ( empty( $parts ) ) {
				return '';
			}

			return '<ul class="rmb-prices">' . implode( '', $parts ) . '</ul>';
		}

		if ( null === $item['price'] && null === $item['sale_price'] ) {
			return '';
		}

		$amount = null === $item['sale_price'] ? $item['price'] : $item['sale_price'];
		$value  = Currency::format( $amount, $currency, $position );

		if ( '' === $value ) {
			return '';
		}

		$html = '';
		$sale = '';

		if ( null !== $item['sale_price'] ) {
			$was = Currency::format( $item['price'], $currency, $position );

			if ( '' !== $was ) {
				// A class rather than a :has() selector, so the discounted colour
				// works in every browser the plugin supports.
				$sale  = ' is-sale';
				$html .= '<s class="rmb-price-was">' . esc_html( $was ) . '</s> ';
			}
		}

		$html .= '<span class="rmb-price-value">' . esc_html( $value ) . '</span>';

		return '<p class="rmb-price' . $sale . '">' . $html . '</p>';
	}

	/**
	 * Render an item or category image.
	 *
	 * @param int                 $image_id Attachment ID.
	 * @param string              $alt      Alt text.
	 * @param array<string,mixed> $context  Render context.
	 * @param string              $class    CSS class.
	 * @return string
	 */
	public static function image_html( int $image_id, string $alt, array $context, string $class = 'rmb-image' ): string {
		if ( $image_id <= 0 || empty( $context['show_images'] ) ) {
			return '';
		}

		$attributes = array(
			'class' => $class,
			'alt'   => $alt,
		);

		$attributes['loading'] = ! empty( $context['lazy_loading'] ) ? 'lazy' : 'eager';

		if ( ! empty( $context['lazy_loading'] ) ) {
			$attributes['decoding'] = 'async';
		}

		$image = wp_get_attachment_image( $image_id, (string) $context['image_size'], false, $attributes );

		return is_string( $image ) ? $image : '';
	}

	/**
	 * Render a category icon.
	 *
	 * @param string $icon Icon key.
	 * @return string
	 */
	public static function icon_html( string $icon ): string {
		return Icons::render( $icon );
	}

	/**
	 * Render an informational notice.
	 *
	 * Front of house visitors never see internal messages; only editors do.
	 *
	 * @param string $message Message text.
	 * @param bool   $force   Always show, used by the admin preview.
	 * @return string
	 */
	private static function notice( string $message, bool $force ): string {
		if ( ! $force && ! current_user_can( 'edit_posts' ) ) {
			return '';
		}

		return '<div class="rmb-notice">' . esc_html( $message ) . '</div>';
	}

	/**
	 * Reset the instance counter. Used by the preview endpoint.
	 *
	 * @return void
	 */
	public static function reset_instance(): void {
		self::$instance = 0;
	}
}
