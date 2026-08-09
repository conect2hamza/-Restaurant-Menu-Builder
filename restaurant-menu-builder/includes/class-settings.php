<?php
/**
 * Global plugin settings and style defaults.
 *
 * @package RestaurantMenuBuilder
 */

declare( strict_types=1 );

namespace RestaurantMenuBuilder;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reads, writes and sanitizes the plugin option set.
 */
class Settings {

	public const OPTION_GENERAL = 'rmb_settings';
	public const OPTION_STYLE   = 'rmb_style';

	/**
	 * Runtime cache.
	 *
	 * @var array<string,array<string,mixed>>
	 */
	private static array $cache = array();

	/**
	 * Default general settings.
	 *
	 * @return array<string,mixed>
	 */
	public static function default_general(): array {
		return array(
			'currency'          => 'USD',
			'currency_position' => 'before',
			'default_menu'      => 0,
			'image_size'        => 'medium',
			'lazy_loading'      => true,
			'layout'            => 'classic',
			'show_navigation'   => true,
			'show_images'       => true,
			'show_descriptions' => true,
			'show_prices'       => true,
			'cache_enabled'     => true,
			'cache_duration'    => 12,
			'custom_css'        => '',
			'delete_on_uninstall' => false,
		);
	}

	/**
	 * Default style settings.
	 *
	 * @return array<string,mixed>
	 */
	public static function default_style(): array {
		return array(
			'color_primary'        => '#1f2933',
			'color_accent'         => '#a63d40',
			'color_text'           => '#22282e',
			'color_text_secondary' => '#5c6670',
			'color_background'     => '#ffffff',
			'color_border'         => '#e3e1dc',
			'color_category_active' => '#1f2933',
			'font_family'          => 'inherit',
			'size_heading'         => '30px',
			'size_body'            => '15px',
			'size_category'        => '14px',
			'size_price'           => '16px',
		);
	}

	/**
	 * Font stacks offered in the style editor. No external requests are made.
	 *
	 * @return array<string,string>
	 */
	public static function font_stacks(): array {
		return array(
			'inherit'   => 'inherit',
			'system'    => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif',
			'serif'     => 'Georgia, "Times New Roman", Times, serif',
			'slab'      => '"Rockwell", "Courier Bold", Georgia, serif',
			'humanist'  => '"Segoe UI", Candara, "Trebuchet MS", Verdana, sans-serif',
			'geometric' => '"Century Gothic", "Avenir Next", "Futura", sans-serif',
			'mono'      => 'ui-monospace, "SF Mono", "Cascadia Mono", Menlo, Consolas, monospace',
		);
	}

	/**
	 * Human readable font labels.
	 *
	 * @return array<string,string>
	 */
	public static function font_labels(): array {
		return array(
			'inherit'   => __( 'Inherit from theme', 'restaurant-menu-builder' ),
			'system'    => __( 'System sans-serif', 'restaurant-menu-builder' ),
			'serif'     => __( 'Classic serif', 'restaurant-menu-builder' ),
			'slab'      => __( 'Slab serif', 'restaurant-menu-builder' ),
			'humanist'  => __( 'Humanist sans-serif', 'restaurant-menu-builder' ),
			'geometric' => __( 'Geometric sans-serif', 'restaurant-menu-builder' ),
			'mono'      => __( 'Monospace', 'restaurant-menu-builder' ),
		);
	}

	/**
	 * Available frontend layouts.
	 *
	 * @return array<string,string>
	 */
	public static function layouts(): array {
		/**
		 * Filter the registered frontend layouts.
		 *
		 * A layout key must map to frontend/templates/layout-{key}.php or to a
		 * template provided through the rmb_template_path filter.
		 *
		 * @param array<string,string> $layouts Layout key => label.
		 */
		return apply_filters(
			'rmb_layouts',
			array(
				'classic'    => __( 'Classic', 'restaurant-menu-builder' ),
				'card'       => __( 'Card', 'restaurant-menu-builder' ),
				'two-column' => __( 'Two column', 'restaurant-menu-builder' ),
			)
		);
	}

	/**
	 * Get general settings merged with defaults.
	 *
	 * @return array<string,mixed>
	 */
	public static function general(): array {
		if ( isset( self::$cache[ self::OPTION_GENERAL ] ) ) {
			return self::$cache[ self::OPTION_GENERAL ];
		}

		$stored = get_option( self::OPTION_GENERAL, array() );
		$stored = is_array( $stored ) ? $stored : array();

		$merged = array_merge( self::default_general(), $stored );

		self::$cache[ self::OPTION_GENERAL ] = $merged;

		return $merged;
	}

	/**
	 * Get global style settings merged with defaults.
	 *
	 * @return array<string,mixed>
	 */
	public static function style(): array {
		if ( isset( self::$cache[ self::OPTION_STYLE ] ) ) {
			return self::$cache[ self::OPTION_STYLE ];
		}

		$stored = get_option( self::OPTION_STYLE, array() );
		$stored = is_array( $stored ) ? $stored : array();

		$merged = array_merge( self::default_style(), $stored );

		self::$cache[ self::OPTION_STYLE ] = $merged;

		return $merged;
	}

	/**
	 * Read a single general setting.
	 *
	 * @param string $key     Setting key.
	 * @param mixed  $default Fallback value.
	 * @return mixed
	 */
	public static function get( string $key, $default = null ) {
		$settings = self::general();

		return array_key_exists( $key, $settings ) ? $settings[ $key ] : $default;
	}

	/**
	 * Save general settings.
	 *
	 * @param array<string,mixed> $input Raw input.
	 * @return array<string,mixed> Saved settings.
	 */
	public static function save_general( array $input ): array {
		$clean = self::sanitize_general( $input, self::general() );

		update_option( self::OPTION_GENERAL, $clean, false );
		self::$cache[ self::OPTION_GENERAL ] = $clean;

		Cache::flush();

		return $clean;
	}

	/**
	 * Save global style settings.
	 *
	 * @param array<string,mixed> $input Raw input.
	 * @return array<string,mixed> Saved settings.
	 */
	public static function save_style( array $input ): array {
		$clean = self::sanitize_style( $input, self::style() );

		update_option( self::OPTION_STYLE, $clean, false );
		self::$cache[ self::OPTION_STYLE ] = $clean;

		Cache::flush();

		return $clean;
	}

	/**
	 * Restore defaults for both option groups.
	 *
	 * @return void
	 */
	public static function reset(): void {
		update_option( self::OPTION_GENERAL, self::default_general(), false );
		update_option( self::OPTION_STYLE, self::default_style(), false );

		self::$cache = array();

		Cache::flush();
	}

	/**
	 * Write defaults for keys that have never been stored.
	 *
	 * @return void
	 */
	public static function install_defaults(): void {
		$general = get_option( self::OPTION_GENERAL, null );

		if ( ! is_array( $general ) ) {
			add_option( self::OPTION_GENERAL, self::default_general(), '', false );
		}

		$style = get_option( self::OPTION_STYLE, null );

		if ( ! is_array( $style ) ) {
			add_option( self::OPTION_STYLE, self::default_style(), '', false );
		}

		self::$cache = array();
	}

	/**
	 * Sanitize general settings against the current values.
	 *
	 * @param array<string,mixed> $input   Raw input.
	 * @param array<string,mixed> $current Current values used as fallbacks.
	 * @return array<string,mixed>
	 */
	public static function sanitize_general( array $input, array $current ): array {
		$clean = $current;

		if ( isset( $input['currency'] ) ) {
			$code                = strtoupper( sanitize_key( (string) $input['currency'] ) );
			$clean['currency']   = Currency::is_supported( $code ) ? $code : $current['currency'];
		}

		if ( isset( $input['currency_position'] ) ) {
			$position                   = sanitize_key( (string) $input['currency_position'] );
			$clean['currency_position'] = in_array( $position, array( 'before', 'after' ), true ) ? $position : 'before';
		}

		if ( isset( $input['default_menu'] ) ) {
			$clean['default_menu'] = absint( $input['default_menu'] );
		}

		if ( isset( $input['image_size'] ) ) {
			$size                = sanitize_key( (string) $input['image_size'] );
			$sizes               = get_intermediate_image_sizes();
			$sizes[]             = 'full';
			$clean['image_size'] = in_array( $size, $sizes, true ) ? $size : 'medium';
		}

		foreach ( array( 'lazy_loading', 'show_navigation', 'show_images', 'show_descriptions', 'show_prices', 'cache_enabled', 'delete_on_uninstall' ) as $flag ) {
			if ( isset( $input[ $flag ] ) ) {
				$clean[ $flag ] = self::to_bool( $input[ $flag ] );
			}
		}

		if ( isset( $input['layout'] ) ) {
			$layout          = sanitize_key( (string) $input['layout'] );
			$clean['layout'] = array_key_exists( $layout, self::layouts() ) ? $layout : 'classic';
		}

		if ( isset( $input['cache_duration'] ) ) {
			$duration                = absint( $input['cache_duration'] );
			$clean['cache_duration'] = max( 1, min( 720, $duration ) );
		}

		if ( isset( $input['custom_css'] ) ) {
			$clean['custom_css'] = self::sanitize_css( (string) $input['custom_css'] );
		}

		return $clean;
	}

	/**
	 * Sanitize style settings against the current values.
	 *
	 * @param array<string,mixed> $input   Raw input.
	 * @param array<string,mixed> $current Current values used as fallbacks.
	 * @return array<string,mixed>
	 */
	public static function sanitize_style( array $input, array $current ): array {
		$clean    = $current;
		$defaults = self::default_style();

		foreach ( $defaults as $key => $default ) {
			if ( ! array_key_exists( $key, $input ) ) {
				continue;
			}

			if ( 0 === strpos( $key, 'color_' ) ) {
				$color         = sanitize_hex_color_value( $input[ $key ] );
				$clean[ $key ] = '' !== $color ? $color : $default;
				continue;
			}

			if ( 0 === strpos( $key, 'size_' ) ) {
				$clean[ $key ] = sanitize_css_length( $input[ $key ], (string) $default );
				continue;
			}

			if ( 'font_family' === $key ) {
				$font          = sanitize_key( (string) $input[ $key ] );
				$clean[ $key ] = array_key_exists( $font, self::font_stacks() ) ? $font : 'inherit';
			}
		}

		return $clean;
	}

	/**
	 * Strip anything that could break out of a style element.
	 *
	 * @param string $css Raw CSS.
	 * @return string
	 */
	public static function sanitize_css( string $css ): string {
		$css = wp_strip_all_tags( $css );
		$css = str_replace( array( '</style', '<style', '<!--', '-->' ), '', $css );
		$css = preg_replace( '/javascript\s*:/i', '', $css );
		$css = preg_replace( '/expression\s*\(/i', '', (string) $css );
		$css = preg_replace( '/@import/i', '', (string) $css );

		return trim( (string) $css );
	}

	/**
	 * Cast a mixed value to boolean.
	 *
	 * @param mixed $value Raw value.
	 * @return bool
	 */
	public static function to_bool( $value ): bool {
		if ( is_bool( $value ) ) {
			return $value;
		}

		if ( is_string( $value ) ) {
			return in_array( strtolower( $value ), array( '1', 'true', 'yes', 'on' ), true );
		}

		return (bool) $value;
	}

	/**
	 * Clear the runtime cache. Used after direct option writes in tests.
	 *
	 * @return void
	 */
	public static function clear_runtime_cache(): void {
		self::$cache = array();
	}
}
