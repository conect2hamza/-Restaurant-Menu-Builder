<?php
/**
 * Turns style settings into scoped CSS.
 *
 * @package RestaurantMenuBuilder
 */

declare( strict_types=1 );

namespace RestaurantMenuBuilder;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Every visual setting is emitted as a CSS custom property on the menu wrapper,
 * so a stylesheet change never needs a rebuild and nothing leaks to the theme.
 */
class Style {

	/**
	 * Map a style array to CSS custom properties.
	 *
	 * @param array<string,mixed> $style Style settings.
	 * @return array<string,string>
	 */
	public static function variables( array $style ): array {
		$style  = array_merge( Settings::default_style(), $style );
		$stacks = Settings::font_stacks();
		$font   = (string) $style['font_family'];
		$stack  = $stacks[ $font ] ?? 'inherit';

		return array(
			'--rmb-primary'          => (string) $style['color_primary'],
			'--rmb-accent'           => (string) $style['color_accent'],
			'--rmb-text'             => (string) $style['color_text'],
			'--rmb-text-secondary'   => (string) $style['color_text_secondary'],
			'--rmb-background'       => (string) $style['color_background'],
			'--rmb-border'           => (string) $style['color_border'],
			'--rmb-category-active'  => (string) $style['color_category_active'],
			'--rmb-font'             => $stack,
			'--rmb-size-heading'     => (string) $style['size_heading'],
			'--rmb-size-body'        => (string) $style['size_body'],
			'--rmb-size-category'    => (string) $style['size_category'],
			'--rmb-size-price'       => (string) $style['size_price'],
		);
	}

	/**
	 * Build the inline style attribute value for a menu wrapper.
	 *
	 * @param array<string,mixed> $style Style settings.
	 * @return string
	 */
	public static function inline_style( array $style ): string {
		$pairs = array();

		foreach ( self::variables( $style ) as $property => $value ) {
			if ( '' === $value ) {
				continue;
			}

			$pairs[] = $property . ':' . $value;
		}

		return implode( ';', $pairs );
	}

	/**
	 * Build a CSS rule block for a specific menu instance.
	 *
	 * Used by the preview endpoint, which renders into an iframe.
	 *
	 * @param string              $selector CSS selector.
	 * @param array<string,mixed> $style    Style settings.
	 * @return string
	 */
	public static function css_block( string $selector, array $style ): string {
		$declarations = self::inline_style( $style );

		if ( '' === $declarations ) {
			return '';
		}

		return $selector . '{' . $declarations . '}';
	}

	/**
	 * Resolve the effective style for a menu.
	 *
	 * @param array<string,mixed> $menu Prepared menu.
	 * @return array<string,mixed>
	 */
	public static function for_menu( array $menu ): array {
		$settings = is_array( $menu['settings'] ?? null ) ? $menu['settings'] : array();

		if ( ! empty( $settings['use_global_style'] ) ) {
			return Settings::style();
		}

		$style = is_array( $settings['style'] ?? null ) ? $settings['style'] : array();

		return array_merge( Settings::default_style(), $style );
	}
}
