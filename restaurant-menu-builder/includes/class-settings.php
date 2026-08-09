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
	 * Every customisable style setting, with its type, default and label.
	 *
	 * This schema is the single source of truth: the defaults, the sanitizer,
	 * the CSS custom properties and the admin controls are all derived from it,
	 * so a new setting only has to be described once.
	 *
	 * Types: color, size (CSS length), select, toggle, font.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	public static function style_schema(): array {
		$schema = array(

			/* ------------------------------------------------------- Colours */

			'color_primary'         => array(
				'type'    => 'color',
				'default' => '#1f2933',
				'group'   => 'colors',
				'label'   => __( 'Headings & item names', 'restaurant-menu-builder' ),
			),
			'color_accent'          => array(
				'type'    => 'color',
				'default' => '#a63d40',
				'group'   => 'colors',
				'label'   => __( 'Accent', 'restaurant-menu-builder' ),
				'help'    => __( 'Rules under section titles, the active category and focus outlines.', 'restaurant-menu-builder' ),
			),
			'color_text'            => array(
				'type'    => 'color',
				'default' => '#22282e',
				'group'   => 'colors',
				'label'   => __( 'Body text', 'restaurant-menu-builder' ),
			),
			'color_text_secondary'  => array(
				'type'    => 'color',
				'default' => '#5c6670',
				'group'   => 'colors',
				'label'   => __( 'Secondary text', 'restaurant-menu-builder' ),
				'help'    => __( 'Descriptions, size labels and inactive categories.', 'restaurant-menu-builder' ),
			),
			'color_background'      => array(
				'type'    => 'color',
				'default' => '#ffffff',
				'group'   => 'colors',
				'label'   => __( 'Background', 'restaurant-menu-builder' ),
			),
			'color_surface'         => array(
				'type'    => 'color',
				'default' => '#ffffff',
				'group'   => 'colors',
				'label'   => __( 'Card background', 'restaurant-menu-builder' ),
				'help'    => __( 'Used by the card layout and the category icon badges.', 'restaurant-menu-builder' ),
			),
			'color_border'          => array(
				'type'    => 'color',
				'default' => '#e3e1dc',
				'group'   => 'colors',
				'label'   => __( 'Borders', 'restaurant-menu-builder' ),
			),
			'color_category_active' => array(
				'type'    => 'color',
				'default' => '#1f2933',
				'group'   => 'colors',
				'label'   => __( 'Active category', 'restaurant-menu-builder' ),
			),
			'color_price'           => array(
				'type'    => 'color',
				'default' => '#1f2933',
				'group'   => 'colors',
				'label'   => __( 'Price', 'restaurant-menu-builder' ),
			),
			'color_sale'            => array(
				'type'    => 'color',
				'default' => '#a63d40',
				'group'   => 'colors',
				'label'   => __( 'Sale price', 'restaurant-menu-builder' ),
				'help'    => __( 'The discounted figure; the original is shown struck through.', 'restaurant-menu-builder' ),
			),
			'color_badge'           => array(
				'type'    => 'color',
				'default' => '#5c6670',
				'group'   => 'colors',
				'label'   => __( 'Badge text', 'restaurant-menu-builder' ),
			),

			/* ---------------------------------------------------- Typography */

			'font_family'           => array(
				'type'    => 'font',
				'default' => 'inherit',
				'group'   => 'typography',
				'label'   => __( 'Body font', 'restaurant-menu-builder' ),
			),
			'font_heading'          => array(
				'type'    => 'font',
				'default' => 'inherit',
				'group'   => 'typography',
				'label'   => __( 'Heading font', 'restaurant-menu-builder' ),
				'help'    => __( 'Section titles and item names.', 'restaurant-menu-builder' ),
			),
			'size_heading'          => array(
				'type'    => 'size',
				'default' => '30px',
				'group'   => 'typography',
				'label'   => __( 'Section title size', 'restaurant-menu-builder' ),
				'help'    => __( 'A CSS length such as 30px or 2rem.', 'restaurant-menu-builder' ),
			),
			'size_body'             => array(
				'type'    => 'size',
				'default' => '15px',
				'group'   => 'typography',
				'label'   => __( 'Body size', 'restaurant-menu-builder' ),
			),
			'size_category'         => array(
				'type'    => 'size',
				'default' => '14px',
				'group'   => 'typography',
				'label'   => __( 'Category navigation size', 'restaurant-menu-builder' ),
			),
			'size_price'            => array(
				'type'    => 'size',
				'default' => '16px',
				'group'   => 'typography',
				'label'   => __( 'Price size', 'restaurant-menu-builder' ),
			),
			'weight_heading'        => array(
				'type'    => 'select',
				'default' => '700',
				'group'   => 'typography',
				'label'   => __( 'Heading weight', 'restaurant-menu-builder' ),
				'choices' => array(
					'400' => __( 'Regular', 'restaurant-menu-builder' ),
					'500' => __( 'Medium', 'restaurant-menu-builder' ),
					'600' => __( 'Semi bold', 'restaurant-menu-builder' ),
					'700' => __( 'Bold', 'restaurant-menu-builder' ),
					'800' => __( 'Extra bold', 'restaurant-menu-builder' ),
				),
			),
			'transform_heading'     => array(
				'type'    => 'select',
				'default' => 'uppercase',
				'group'   => 'typography',
				'label'   => __( 'Section title case', 'restaurant-menu-builder' ),
				'choices' => array(
					'none'       => __( 'As typed', 'restaurant-menu-builder' ),
					'uppercase'  => __( 'UPPERCASE', 'restaurant-menu-builder' ),
					'capitalize' => __( 'Capitalised', 'restaurant-menu-builder' ),
				),
			),
			'letter_spacing'        => array(
				'type'    => 'size',
				'default' => '0.06em',
				'group'   => 'typography',
				'label'   => __( 'Section title letter spacing', 'restaurant-menu-builder' ),
				'help'    => __( 'Try 0.06em for a spaced, printed look, or 0 for none.', 'restaurant-menu-builder' ),
			),
			'line_height'           => array(
				'type'    => 'size',
				'default' => '1.55',
				'group'   => 'typography',
				'label'   => __( 'Line height', 'restaurant-menu-builder' ),
				'help'    => __( 'A plain number, such as 1.55.', 'restaurant-menu-builder' ),
			),
			'italic_description'    => array(
				'type'    => 'toggle',
				'default' => true,
				'group'   => 'typography',
				'label'   => __( 'Italic descriptions', 'restaurant-menu-builder' ),
			),

			/* -------------------------------------------------------- Layout */

			'max_width'             => array(
				'type'    => 'size',
				'default' => '100%',
				'group'   => 'layout',
				'label'   => __( 'Maximum width', 'restaurant-menu-builder' ),
				'help'    => __( 'Constrain the menu inside a wide theme, for example 1080px.', 'restaurant-menu-builder' ),
			),
			'radius'                => array(
				'type'    => 'size',
				'default' => '10px',
				'group'   => 'layout',
				'label'   => __( 'Corner rounding', 'restaurant-menu-builder' ),
			),
			'section_gap'           => array(
				'type'    => 'size',
				'default' => '28px',
				'group'   => 'layout',
				'label'   => __( 'Space between sections', 'restaurant-menu-builder' ),
			),
			'item_gap'              => array(
				'type'    => 'size',
				'default' => '22px',
				'group'   => 'layout',
				'label'   => __( 'Space between items', 'restaurant-menu-builder' ),
			),
			'align'                 => array(
				'type'    => 'select',
				'default' => 'center',
				'group'   => 'layout',
				'label'   => __( 'Section header alignment', 'restaurant-menu-builder' ),
				'choices' => array(
					'left'   => __( 'Left', 'restaurant-menu-builder' ),
					'center' => __( 'Centred', 'restaurant-menu-builder' ),
				),
			),
			'card_columns'          => array(
				'type'    => 'select',
				'default' => 'auto',
				'group'   => 'layout',
				'label'   => __( 'Card columns', 'restaurant-menu-builder' ),
				'help'    => __( 'Only affects the card layout. Automatic fits as many as the width allows.', 'restaurant-menu-builder' ),
				'choices' => array(
					'auto' => __( 'Automatic', 'restaurant-menu-builder' ),
					'2'    => __( 'Two', 'restaurant-menu-builder' ),
					'3'    => __( 'Three', 'restaurant-menu-builder' ),
					'4'    => __( 'Four', 'restaurant-menu-builder' ),
				),
			),
			'image_ratio'           => array(
				'type'    => 'select',
				'default' => 'square',
				'group'   => 'layout',
				'label'   => __( 'Image shape', 'restaurant-menu-builder' ),
				'choices' => array(
					'original' => __( 'Original proportions', 'restaurant-menu-builder' ),
					'square'   => __( 'Square', 'restaurant-menu-builder' ),
					'4-3'      => __( 'Landscape 4:3', 'restaurant-menu-builder' ),
					'16-9'     => __( 'Wide 16:9', 'restaurant-menu-builder' ),
					'circle'   => __( 'Circle', 'restaurant-menu-builder' ),
				),
			),
			'image_width'           => array(
				'type'    => 'size',
				'default' => '92px',
				'group'   => 'layout',
				'label'   => __( 'Thumbnail width', 'restaurant-menu-builder' ),
				'help'    => __( 'Used by the classic and two column layouts.', 'restaurant-menu-builder' ),
			),
			'image_position'        => array(
				'type'    => 'select',
				'default' => 'left',
				'group'   => 'layout',
				'label'   => __( 'Thumbnail position', 'restaurant-menu-builder' ),
				'choices' => array(
					'left'  => __( 'Before the text', 'restaurant-menu-builder' ),
					'right' => __( 'After the text', 'restaurant-menu-builder' ),
				),
			),
			'divider'               => array(
				'type'    => 'select',
				'default' => 'dots',
				'group'   => 'layout',
				'label'   => __( 'Price leader', 'restaurant-menu-builder' ),
				'help'    => __( 'The line that carries the eye from a dish to its price.', 'restaurant-menu-builder' ),
				'choices' => array(
					'dots' => __( 'Dotted', 'restaurant-menu-builder' ),
					'line' => __( 'Solid rule', 'restaurant-menu-builder' ),
					'none' => __( 'None', 'restaurant-menu-builder' ),
				),
			),
			'item_border'           => array(
				'type'    => 'toggle',
				'default' => false,
				'group'   => 'layout',
				'label'   => __( 'Separator line between items', 'restaurant-menu-builder' ),
			),

			/* ---------------------------------------------------- Navigation */

			'nav_style'             => array(
				'type'    => 'select',
				'default' => 'icons',
				'group'   => 'navigation',
				'label'   => __( 'Navigation style', 'restaurant-menu-builder' ),
				'choices' => array(
					'icons'     => __( 'Icon above the name', 'restaurant-menu-builder' ),
					'pills'     => __( 'Rounded pills', 'restaurant-menu-builder' ),
					'underline' => __( 'Underlined text', 'restaurant-menu-builder' ),
					'plain'     => __( 'Plain text', 'restaurant-menu-builder' ),
				),
			),
			'nav_align'             => array(
				'type'    => 'select',
				'default' => 'left',
				'group'   => 'navigation',
				'label'   => __( 'Navigation alignment', 'restaurant-menu-builder' ),
				'choices' => array(
					'left'   => __( 'Left', 'restaurant-menu-builder' ),
					'center' => __( 'Centred', 'restaurant-menu-builder' ),
					'right'  => __( 'Right', 'restaurant-menu-builder' ),
				),
			),
			'nav_sticky'            => array(
				'type'    => 'toggle',
				'default' => true,
				'group'   => 'navigation',
				'label'   => __( 'Keep the category bar visible while scrolling', 'restaurant-menu-builder' ),
			),
			'nav_icons'             => array(
				'type'    => 'toggle',
				'default' => true,
				'group'   => 'navigation',
				'label'   => __( 'Show category icons', 'restaurant-menu-builder' ),
			),
			'nav_border'            => array(
				'type'    => 'toggle',
				'default' => true,
				'group'   => 'navigation',
				'label'   => __( 'Rule under the category bar', 'restaurant-menu-builder' ),
			),
		);

		/**
		 * Filter the style schema.
		 *
		 * A new entry needs type, default, group and label. Anything added here
		 * is sanitized, saved, exposed as a CSS custom property or wrapper class
		 * and rendered in the Style editor automatically.
		 *
		 * @param array<string,array<string,mixed>> $schema Style schema.
		 */
		return apply_filters( 'rmb_style_schema', $schema );
	}

	/**
	 * Style editor groups, in the order they are shown.
	 *
	 * @return array<string,string>
	 */
	public static function style_groups(): array {
		return array(
			'colors'     => __( 'Colours', 'restaurant-menu-builder' ),
			'typography' => __( 'Typography', 'restaurant-menu-builder' ),
			'layout'     => __( 'Layout', 'restaurant-menu-builder' ),
			'navigation' => __( 'Navigation', 'restaurant-menu-builder' ),
		);
	}

	/**
	 * Ready made looks. Applying one fills the editor, which can then be tweaked.
	 *
	 * Each preset only lists what it changes; everything else stays at default.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	public static function presets(): array {
		$presets = array(
			'classic'  => array(
				'label' => __( 'Classic', 'restaurant-menu-builder' ),
				'style' => array(),
			),
			'modern'   => array(
				'label' => __( 'Modern', 'restaurant-menu-builder' ),
				'style' => array(
					'color_primary'         => '#1c1b22',
					'color_accent'          => '#8b5cf6',
					'color_text'            => '#2c2b33',
					'color_text_secondary'  => '#6b7280',
					'color_border'          => '#e6e8ee',
					'color_category_active' => '#8b5cf6',
					'color_price'           => '#8b5cf6',
					'color_sale'            => '#dc2626',
					'font_family'           => 'system',
					'font_heading'          => 'system',
					'transform_heading'     => 'none',
					'letter_spacing'        => '0',
					'weight_heading'        => '700',
					'radius'                => '14px',
					'divider'               => 'none',
					'nav_style'             => 'pills',
					'image_ratio'           => '4-3',
				),
			),
			'elegant'  => array(
				'label' => __( 'Elegant', 'restaurant-menu-builder' ),
				'style' => array(
					'color_primary'         => '#2b2118',
					'color_accent'          => '#b08d57',
					'color_text'            => '#3a2f26',
					'color_text_secondary'  => '#7a6a5b',
					'color_background'      => '#fdfaf5',
					'color_surface'         => '#fdfaf5',
					'color_border'          => '#e5d9c6',
					'color_category_active' => '#b08d57',
					'color_price'           => '#b08d57',
					'font_family'           => 'serif',
					'font_heading'          => 'serif',
					'transform_heading'     => 'uppercase',
					'letter_spacing'        => '0.12em',
					'weight_heading'        => '500',
					'radius'                => '2px',
					'divider'               => 'dots',
					'nav_style'             => 'underline',
					'image_ratio'           => 'circle',
				),
			),
			'minimal'  => array(
				'label' => __( 'Minimal', 'restaurant-menu-builder' ),
				'style' => array(
					'color_primary'         => '#111111',
					'color_accent'          => '#111111',
					'color_text'            => '#333333',
					'color_text_secondary'  => '#767676',
					'color_border'          => '#e5e5e5',
					'color_category_active' => '#111111',
					'color_price'           => '#111111',
					'font_heading'          => 'inherit',
					'transform_heading'     => 'none',
					'letter_spacing'        => '0',
					'weight_heading'        => '600',
					'size_heading'          => '22px',
					'radius'                => '0',
					'divider'               => 'none',
					'item_border'           => true,
					'nav_style'             => 'plain',
					'nav_border'            => false,
					'nav_icons'             => false,
					'align'                 => 'left',
				),
			),
			'bistro'   => array(
				'label' => __( 'Bistro', 'restaurant-menu-builder' ),
				'style' => array(
					'color_primary'         => '#1f2933',
					'color_accent'          => '#c1440e',
					'color_text'            => '#22282e',
					'color_text_secondary'  => '#5c6670',
					'color_background'      => '#fffdf8',
					'color_surface'         => '#ffffff',
					'color_border'          => '#e6ddcd',
					'color_category_active' => '#c1440e',
					'color_price'           => '#c1440e',
					'font_family'           => 'humanist',
					'font_heading'          => 'slab',
					'transform_heading'     => 'uppercase',
					'letter_spacing'        => '0.08em',
					'divider'               => 'dots',
					'nav_style'             => 'icons',
				),
			),
			'midnight' => array(
				'label' => __( 'Midnight', 'restaurant-menu-builder' ),
				'style' => array(
					'color_primary'         => '#f5f3ef',
					'color_accent'          => '#e0b973',
					'color_text'            => '#e8e6e1',
					'color_text_secondary'  => '#a8a29b',
					'color_background'      => '#16171b',
					'color_surface'         => '#1e2026',
					'color_border'          => '#33353d',
					'color_category_active' => '#e0b973',
					'color_price'           => '#e0b973',
					'color_sale'            => '#f0806c',
					'color_badge'           => '#a8a29b',
					'font_heading'          => 'serif',
					'letter_spacing'        => '0.1em',
					'radius'                => '8px',
					'nav_style'             => 'pills',
				),
			),
		);

		/**
		 * Filter the style presets offered in the Style editor.
		 *
		 * @param array<string,array<string,mixed>> $presets Preset definitions.
		 */
		return apply_filters( 'rmb_style_presets', $presets );
	}

	/**
	 * Resolve a preset key to a complete style array.
	 *
	 * @param string $key Preset key.
	 * @return array<string,mixed> Empty when the key is unknown.
	 */
	public static function preset_style( string $key ): array {
		$presets = self::presets();
		$key     = sanitize_key( $key );

		if ( ! isset( $presets[ $key ]['style'] ) || ! is_array( $presets[ $key ]['style'] ) ) {
			return array();
		}

		return self::sanitize_style( $presets[ $key ]['style'], self::default_style() );
	}

	/**
	 * Default style settings, derived from the schema.
	 *
	 * @return array<string,mixed>
	 */
	public static function default_style(): array {
		$defaults = array();

		foreach ( self::style_schema() as $key => $field ) {
			$defaults[ $key ] = $field['default'] ?? '';
		}

		return $defaults;
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
		$clean  = $current;
		$schema = self::style_schema();

		foreach ( $schema as $key => $field ) {
			$default = $field['default'] ?? '';

			// Keys the caller did not send keep whatever is already stored, and
			// keys stored before a schema change fall back to their default.
			if ( ! array_key_exists( $key, $input ) ) {
				if ( ! array_key_exists( $key, $clean ) ) {
					$clean[ $key ] = $default;
				}

				continue;
			}

			switch ( $field['type'] ?? 'size' ) {
				case 'color':
					$color         = sanitize_hex_color_value( $input[ $key ] );
					$clean[ $key ] = '' !== $color ? $color : $default;
					break;

				case 'font':
					$font          = sanitize_key( (string) $input[ $key ] );
					$clean[ $key ] = array_key_exists( $font, self::font_stacks() ) ? $font : (string) $default;
					break;

				case 'select':
					$choices       = is_array( $field['choices'] ?? null ) ? $field['choices'] : array();
					$choice        = sanitize_key( (string) $input[ $key ] );
					$clean[ $key ] = array_key_exists( $choice, $choices ) ? $choice : (string) $default;
					break;

				case 'toggle':
					$clean[ $key ] = self::to_bool( $input[ $key ] );
					break;

				case 'size':
				default:
					$clean[ $key ] = sanitize_css_length( $input[ $key ], (string) $default );
					break;
			}
		}

		// Drop anything that is no longer part of the schema.
		return array_intersect_key( $clean, $schema );
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
