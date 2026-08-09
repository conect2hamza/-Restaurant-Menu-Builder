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

		$body_font    = $stacks[ (string) $style['font_family'] ] ?? 'inherit';
		$heading_font = $stacks[ (string) $style['font_heading'] ] ?? 'inherit';

		// "inherit" for the heading means "whatever the body font resolved to",
		// so choosing only a body font still restyles the whole menu.
		if ( 'inherit' === $style['font_heading'] ) {
			$heading_font = $body_font;
		}

		$columns = (string) $style['card_columns'];
		$columns = 'auto' === $columns
			? 'repeat(auto-fill, minmax(230px, 1fr))'
			: 'repeat(' . (int) $columns . ', minmax(0, 1fr))';

		return array(
			'--rmb-primary'           => (string) $style['color_primary'],
			'--rmb-accent'            => (string) $style['color_accent'],
			'--rmb-text'              => (string) $style['color_text'],
			'--rmb-text-secondary'    => (string) $style['color_text_secondary'],
			'--rmb-background'        => (string) $style['color_background'],
			'--rmb-surface'           => (string) $style['color_surface'],
			'--rmb-border'            => (string) $style['color_border'],
			'--rmb-category-active'   => (string) $style['color_category_active'],
			'--rmb-price'             => (string) $style['color_price'],
			'--rmb-sale'              => (string) $style['color_sale'],
			'--rmb-badge'             => (string) $style['color_badge'],
			'--rmb-font'              => $body_font,
			'--rmb-font-heading'      => $heading_font,
			'--rmb-size-heading'      => (string) $style['size_heading'],
			'--rmb-size-body'         => (string) $style['size_body'],
			'--rmb-size-category'     => (string) $style['size_category'],
			'--rmb-size-price'        => (string) $style['size_price'],
			'--rmb-weight-heading'    => (string) $style['weight_heading'],
			'--rmb-transform-heading' => (string) $style['transform_heading'],
			'--rmb-letter-spacing'    => (string) $style['letter_spacing'],
			'--rmb-line-height'       => (string) $style['line_height'],
			'--rmb-max-width'         => (string) $style['max_width'],
			'--rmb-radius'            => (string) $style['radius'],
			'--rmb-gap'               => (string) $style['section_gap'],
			'--rmb-item-gap'          => (string) $style['item_gap'],
			'--rmb-image-width'       => (string) $style['image_width'],
			'--rmb-card-columns'      => $columns,
		);
	}

	/**
	 * Wrapper classes for the settings that switch layout rather than a value.
	 *
	 * Keeping these as classes rather than custom properties lets one choice
	 * change several declarations at once, which a variable cannot do.
	 *
	 * @param array<string,mixed> $style Style settings.
	 * @return string[]
	 */
	public static function wrapper_classes( array $style ): array {
		$style = array_merge( Settings::default_style(), $style );

		$classes = array(
			'rmb-align-' . sanitize_html_class( (string) $style['align'] ),
			'rmb-nav-' . sanitize_html_class( (string) $style['nav_style'] ),
			'rmb-nav-align-' . sanitize_html_class( (string) $style['nav_align'] ),
			'rmb-ratio-' . sanitize_html_class( (string) $style['image_ratio'] ),
			'rmb-media-' . sanitize_html_class( (string) $style['image_position'] ),
			'rmb-leader-' . sanitize_html_class( (string) $style['divider'] ),
		);

		$flags = array(
			'nav_wrap'           => 'has-nav-wrap',
			'nav_sticky'         => 'is-nav-sticky',
			'nav_icons'          => 'has-nav-icons',
			'nav_border'         => 'has-nav-border',
			'item_border'        => 'has-item-border',
			'italic_description' => 'is-italic',
		);

		foreach ( $flags as $key => $class ) {
			if ( Settings::to_bool( $style[ $key ] ) ) {
				$classes[] = $class;
			}
		}

		return $classes;
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
