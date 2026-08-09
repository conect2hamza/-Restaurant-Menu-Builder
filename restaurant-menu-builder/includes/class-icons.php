<?php
/**
 * Plugin controlled SVG icon set.
 *
 * @package RestaurantMenuBuilder
 */

declare( strict_types=1 );

namespace RestaurantMenuBuilder;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hand drawn line-art icon pack for menu categories, plus a small set of
 * interface icons for the admin screens.
 *
 * Icons are stored as keys, never as raw markup, so no arbitrary SVG can be
 * injected through a category record. Geometry is drawn on a 24x24 viewBox with
 * round caps, no fill and a single stroke weight, which keeps the whole pack
 * visually consistent at any size.
 */
class Icons {

	/**
	 * Icon groups used to organise the picker.
	 *
	 * @return array<string,string>
	 */
	public static function groups(): array {
		return array(
			'mains'    => __( 'Mains & dishes', 'restaurant-menu-builder' ),
			'seafood'  => __( 'Seafood', 'restaurant-menu-builder' ),
			'produce'  => __( 'Fruit & vegetables', 'restaurant-menu-builder' ),
			'desserts' => __( 'Desserts', 'restaurant-menu-builder' ),
			'drinks'   => __( 'Drinks', 'restaurant-menu-builder' ),
			'pantry'   => __( 'Pantry & tools', 'restaurant-menu-builder' ),
			'service'  => __( 'Service', 'restaurant-menu-builder' ),
			'labels'   => __( 'Labels', 'restaurant-menu-builder' ),
		);
	}

	/**
	 * Registered icons: key => array( label, group, paths, circles ).
	 *
	 * Keys are permanent. Renaming one would silently drop the icon from every
	 * category already using it, so artwork may be improved but keys stay put.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	public static function all(): array {
		/**
		 * Filter the registered category icons.
		 *
		 * Path data and circles are sanitized before rendering, so a filter can
		 * only ever add geometry, never markup.
		 *
		 * @param array<string,array<string,mixed>> $icons Icon definitions.
		 */
		return apply_filters( 'rmb_icons', array_merge( self::mains(), self::seafood(), self::produce(), self::desserts(), self::drinks(), self::pantry(), self::service(), self::labels() ) );
	}

	/* ------------------------------------------------------------------ Mains */

	/**
	 * Main dishes and everyday plates.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	private static function mains(): array {
		return array(
			'pizza'        => array(
				'label'   => __( 'Pizza slice', 'restaurant-menu-builder' ),
				'group'   => 'mains',
				'paths'   => array(
					'M12 3.4 4.3 19a1.1 1.1 0 0 0 1.4 1.5 21 21 0 0 1 12.6 0 1.1 1.1 0 0 0 1.4-1.5Z',
					'M6.7 15.4a15.5 15.5 0 0 1 10.6 0',
				),
				'circles' => array( '10.4 12.6 1', '13.8 15.6 1', '11 17.8 0.9' ),
			),
			'pizza_whole'  => array(
				'label'   => __( 'Whole pizza', 'restaurant-menu-builder' ),
				'group'   => 'mains',
				'paths'   => array(
					'M12 3.2a8.8 8.8 0 1 0 0 17.6 8.8 8.8 0 0 0 0-17.6Z',
					'M12 6a6 6 0 1 0 0 12 6 6 0 0 0 0-12Z',
					'M12 6v12M6 12h12',
				),
				'circles' => array( '9.6 9.6 0.7', '14.4 14.4 0.7' ),
			),
			'burger'       => array(
				'label'   => __( 'Burger', 'restaurant-menu-builder' ),
				'group'   => 'mains',
				'paths'   => array(
					'M4 10.4a8 8 0 0 1 16 0Z',
					'M4.4 12.8c1.3-1.2 2.7 1.2 4 0s2.7 1.2 4 0 2.7 1.2 4 0 2.4 1 3.2.4',
					'M5.2 15.4h13.6',
					'M4.8 17h14.4a2.4 2.4 0 0 1-2.4 3.6H7.2a2.4 2.4 0 0 1-2.4-3.6Z',
				),
				'circles' => array( '9 8.2 0.55', '12.4 7.1 0.55', '15.6 8.4 0.55' ),
			),
			'hot_dog'      => array(
				'label' => __( 'Hot dog', 'restaurant-menu-builder' ),
				'group' => 'mains',
				'paths' => array(
					'M4 15.2a3.2 3.2 0 0 1 3.2-3.2h9.6a3.2 3.2 0 0 1 0 6.4H7.2A3.2 3.2 0 0 1 4 15.2Z',
					'M7.4 13.8h9.2a1.5 1.5 0 0 1 0 3H7.4a1.5 1.5 0 0 1 0-3Z',
					'M8.2 15.3c1-1 1.7 1 2.7 0s1.7 1 2.7 0 1.3.7 1.9.4',
				),
			),
			'sandwich'     => array(
				'label' => __( 'Sandwich', 'restaurant-menu-builder' ),
				'group' => 'mains',
				'paths' => array(
					'M4.4 7.2h15.2a1.4 1.4 0 0 1 0 2.8H4.4a1.4 1.4 0 0 1 0-2.8Z',
					'M5.2 11.8c1.3-1.2 2.7 1.2 4 0s2.7 1.2 4 0 2.7 1.2 4 0 1.1.5 1.5.5',
					'M4.6 13.8h14.8l-1.6 5.2a1.6 1.6 0 0 1-1.5 1.1H7.7a1.6 1.6 0 0 1-1.5-1.1Z',
				),
			),
			'taco'         => array(
				'label' => __( 'Taco', 'restaurant-menu-builder' ),
				'group' => 'mains',
				'paths' => array(
					'M3.4 17.6a8.6 8.6 0 0 1 17.2 0Z',
					'M6 14.6c1.4-1 2.8 1 4.2 0s2.8 1 4.2 0 2.2.8 3 .4',
					'M3.4 17.6h17.2',
				),
			),
			'steak'        => array(
				'label' => __( 'Steak', 'restaurant-menu-builder' ),
				'group' => 'mains',
				'paths' => array(
					'M5.4 12.8c0-4 3.4-6.8 7.6-6.8 3.8 0 6.4 2.2 6.4 5.2 0 4.6-4 7.8-8.2 7.8-3.4 0-5.8-2.2-5.8-6.2Z',
					'M8.6 10.6c1.6-1.4 3.6-2 5.8-1.8M9.4 15.4c1.2-.6 2.4-.6 3.6 0M15.4 13c.6.6 1 1.4 1.2 2.2',
				),
			),
			'ham'          => array(
				'label' => __( 'Ham', 'restaurant-menu-builder' ),
				'group' => 'mains',
				'paths' => array(
					'M6 13.4c0-3.7 3-6.6 6.8-6.6 3.4 0 5.8 2.2 5.8 5.2 0 4-3.4 7.2-7.4 7.2-3.2 0-5.2-2.2-5.2-5.8Z',
					'M14.8 6.8 17 4.6a1.5 1.5 0 0 1 2.2 2l-1.8 2.2',
				),
			),
			'chicken_leg'  => array(
				'label'   => __( 'Chicken leg', 'restaurant-menu-builder' ),
				'group'   => 'mains',
				'paths'   => array(
					'M19.4 5.6a5 5 0 0 1-6.2 7.7l-2 2-2.5-2.5 2-2a5 5 0 0 1 7.7-6.2 5 5 0 0 1 1 1Z',
					'M10.4 15.4 8.8 17',
				),
				'circles' => array( '7.2 17.4 1.5', '9 19.2 1.5' ),
			),
			'roast_turkey' => array(
				'label' => __( 'Roast', 'restaurant-menu-builder' ),
				'group' => 'mains',
				'paths' => array(
					'M3.4 18.8h17.2',
					'M4.8 16.8a7.2 7.2 0 0 1 14.4 0Z',
					'M9.2 9 10.8 11.6M14.8 9 13.2 11.6',
				),
			),
			'sausage'      => array(
				'label' => __( 'Sausages', 'restaurant-menu-builder' ),
				'group' => 'mains',
				'paths' => array(
					'M4.6 14.6a5 5 0 0 1 5-5h4.8a5 5 0 0 1 0 10H9.6a5 5 0 0 1-5-5Z',
					'M12 9.6v10',
					'M4.6 12.6H3M4.6 16.6H3M19.4 12.6H21M19.4 16.6H21',
				),
			),
			'skewer'       => array(
				'label' => __( 'Skewer', 'restaurant-menu-builder' ),
				'group' => 'mains',
				'paths' => array(
					'M4 20 20 4',
					'M9.4 12.7 11.3 14.6 9.4 16.5 7.5 14.6Z',
					'M12 10.1 13.9 12 12 13.9 10.1 12Z',
					'M14.6 7.5 16.5 9.4 14.6 11.3 12.7 9.4Z',
				),
			),
			'pasta'        => array(
				'label'   => __( 'Pasta', 'restaurant-menu-builder' ),
				'group'   => 'mains',
				'paths'   => array(
					'M3.4 13.4h17.2a8.6 8.6 0 0 1-17.2 0Z',
					'M7.6 13.4a4.4 4.4 0 0 1 8.8 0',
					'M9.6 13.4a2.4 2.4 0 0 1 4.8 0',
				),
				'circles' => array( '12 10.6 1.1' ),
			),
			'noodles'      => array(
				'label' => __( 'Noodles', 'restaurant-menu-builder' ),
				'group' => 'mains',
				'paths' => array(
					'M3.4 12.6h17.2a8.6 8.6 0 0 1-17.2 0Z',
					'M6.6 12.6c1.2-1 2.4 1 3.6 0s2.4 1 3.6 0 2.4 1 3.6 0',
					'M14.6 3.6 8.8 11.2M17.4 4.8 11.8 12',
				),
			),
			'rice'         => array(
				'label'   => __( 'Rice bowl', 'restaurant-menu-builder' ),
				'group'   => 'mains',
				'paths'   => array(
					'M3.6 12.4h16.8a8.4 8.4 0 0 1-16.8 0Z',
					'M7 12.4a5 5 0 0 1 10 0',
				),
				'circles' => array( '10.4 9.8 0.6', '13.4 10.4 0.6' ),
			),
			'soup'         => array(
				'label' => __( 'Soup', 'restaurant-menu-builder' ),
				'group' => 'mains',
				'paths' => array(
					'M4.4 12.8h15.2a7.6 7.6 0 0 1-15.2 0Z',
					'M2.8 12.8h18.4',
					'M9.6 8.6c0-1.2 1.2-1.2 1.2-2.4s-1.2-1.2-1.2-2.4M14.4 8.6c0-1.2 1.2-1.2 1.2-2.4s-1.2-1.2-1.2-2.4',
				),
			),
			'salad'        => array(
				'label'   => __( 'Salad', 'restaurant-menu-builder' ),
				'group'   => 'mains',
				'paths'   => array(
					'M3.4 13h17.2a8.6 8.6 0 0 1-17.2 0Z',
					'M8.8 13c-1.8-2.6-.8-5.8 2.2-6.8',
					'M13.2 13c2-1.8 2.4-4.2 1.4-6.2',
				),
				'circles' => array( '15.8 10.8 1.2' ),
			),
			'fried_egg'    => array(
				'label'   => __( 'Fried egg', 'restaurant-menu-builder' ),
				'group'   => 'mains',
				'paths'   => array(
					'M12 5.4c1.9 0 3.3 1 4 2.3 1.8-.6 3.7.3 4.3 2 .6 1.7-.2 3.6-1.9 4.4.3 1.8-.9 3.5-2.7 3.9-1.4.3-2.7-.2-3.5-1.3-1.1 1.4-3 1.8-4.5 1-1.5-.8-2.2-2.4-1.8-4-1.9-.6-3-2.5-2.6-4.4.4-1.8 2-3 3.8-3 .5-1.7 2.1-2.9 4-2.9Z',
				),
				'circles' => array( '12.2 12 2.4' ),
			),
			'grill'        => array(
				'label' => __( 'Grill', 'restaurant-menu-builder' ),
				'group' => 'mains',
				'paths' => array(
					'M3.6 9.6h16.8',
					'M5 9.6h14l-1.6 6.4a2 2 0 0 1-2 1.6H8.6a2 2 0 0 1-2-1.6Z',
					'M8 17.6 6.4 21M16 17.6 17.6 21',
					'M9.4 6.6c0-1.2 1.2-1.2 1.2-2.4M13.4 6.6c0-1.2 1.2-1.2 1.2-2.4',
				),
			),
			'sides'        => array(
				'label' => __( 'Fries & sides', 'restaurant-menu-builder' ),
				'group' => 'mains',
				'paths' => array(
					'M8.2 11h7.6l-1 8.8a1.5 1.5 0 0 1-1.5 1.4h-2.6a1.5 1.5 0 0 1-1.5-1.4Z',
					'M9.6 11V6.6M12 11V5M14.4 11V6.6',
					'M8.6 14h6.8',
				),
			),
			'starter'      => array(
				'label' => __( 'Starter', 'restaurant-menu-builder' ),
				'group' => 'mains',
				'paths' => array(
					'M4.6 12.8h14.8a7.4 7.4 0 0 1-14.8 0Z',
					'M3.4 12.8h17.2',
					'M12 12.8c0-2.6 1.8-4.7 4.4-5.1-.2 2.9-1.9 4.8-4.4 5.1Z',
				),
			),
			'cheese'       => array(
				'label'   => __( 'Cheese', 'restaurant-menu-builder' ),
				'group'   => 'mains',
				'paths'   => array(
					'M3.6 15.4v-1.8L11.4 8h8a1 1 0 0 1 1 1v5.4a1 1 0 0 1-1 1Z',
					'M3.6 13.6h16.8',
				),
				'circles' => array( '8.6 12.8 1', '13.6 11.6 0.9', '16.8 13.4 0.8' ),
			),
			'bread'        => array(
				'label' => __( 'Bread', 'restaurant-menu-builder' ),
				'group' => 'mains',
				'paths' => array(
					'M4.6 13.2c0-3.4 3.3-6 7.4-6s7.4 2.6 7.4 6v3.4a1.8 1.8 0 0 1-1.8 1.8H6.4a1.8 1.8 0 0 1-1.8-1.8Z',
					'M4.6 13.2h14.8',
					'M8.6 9.6c.8 1 .8 2.2 0 3.2M12 9c.8 1.2.8 2.6 0 3.8M15.4 9.6c.8 1 .8 2.2 0 3.2',
				),
			),
			'baguette'     => array(
				'label' => __( 'Baguette', 'restaurant-menu-builder' ),
				'group' => 'mains',
				'paths' => array(
					'M6.4 17.6a3.2 3.2 0 0 1 0-4.6l6.6-6.6a3.2 3.2 0 0 1 4.6 4.6l-6.6 6.6a3.2 3.2 0 0 1-4.6 0Z',
					'M9.2 12.8 10.8 14.4M11.4 10.6 13 12.2M13.6 8.4 15.2 10',
				),
			),
			'croissant'    => array(
				'label' => __( 'Croissant', 'restaurant-menu-builder' ),
				'group' => 'mains',
				'paths' => array(
					'M3.6 16.6c0-4.6 3.8-8.4 8.4-8.4s8.4 3.8 8.4 8.4c-2 .8-3.6-.6-4.8-1.4-1.6-1-2.6-1.4-3.6-1.4s-2 .4-3.6 1.4c-1.2.8-2.8 2.2-4.8 1.4Z',
					'M8.6 13.4 7 17M15.4 13.4 17 17M12 12.6v4.4',
				),
			),
			'breakfast'    => array(
				'label'   => __( 'Breakfast', 'restaurant-menu-builder' ),
				'group'   => 'mains',
				'paths'   => array(
					'M4 12h11.4v1.8a5.7 5.7 0 1 1-11.4 0Z',
					'M15.4 13.4h3.4a1.7 1.7 0 0 1 0 3.4H17',
					'M8.4 8.6c0-1.2 1.2-1.2 1.2-2.4M12 8.6c0-1.2 1.2-1.2 1.2-2.4',
				),
				'circles' => array( '9.7 15.4 1.6' ),
			),
			'sauce'        => array(
				'label' => __( 'Sauces', 'restaurant-menu-builder' ),
				'group' => 'mains',
				'paths' => array(
					'M10.2 3.6h3.6v2.6l2.2 2.6v10.6a1.6 1.6 0 0 1-1.6 1.6H9.6A1.6 1.6 0 0 1 8 19.4V8.8l2.2-2.6Z',
					'M8 12.4h8',
				),
			),
		);
	}

	/* ---------------------------------------------------------------- Seafood */

	/**
	 * Fish and shellfish.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	private static function seafood(): array {
		return array(
			'seafood'     => array(
				'label'   => __( 'Fish', 'restaurant-menu-builder' ),
				'group'   => 'seafood',
				'paths'   => array(
					'M2.6 12.4c2.8-4 7.4-6 11.4-4.6 2.2.8 3.6 2.4 4.2 4.6-.6 2.2-2 3.8-4.2 4.6-4 1.4-8.6-.6-11.4-4.6Z',
					'M18.2 12.4 21.6 8.8v7.2Z',
					'M10.2 8.4c.6-1.7 1.9-2.7 3.8-3-.2 1.5-.7 2.5-1.4 3.3',
				),
				'circles' => array( '6.8 11.6 0.75' ),
			),
			'salmon'      => array(
				'label' => __( 'Salmon fillet', 'restaurant-menu-builder' ),
				'group' => 'seafood',
				'paths' => array(
					'M4.8 14.2c0-3.5 3.2-6.3 7.2-6.3s7.2 2.8 7.2 6.3-3.2 6.3-7.2 6.3-7.2-2.8-7.2-6.3Z',
					'M8.4 10.2c1.2 2.6 1.2 5.4 0 8M11.8 8.4c1.4 3.2 1.4 6.8 0 10M15.2 10.2c1.2 2.6 1.2 5.4 0 8',
				),
			),
			'shrimp'      => array(
				'label'   => __( 'Shrimp', 'restaurant-menu-builder' ),
				'group'   => 'seafood',
				'paths'   => array(
					'M18.4 7.2c-3.6 0-6.8 1.2-9 3.4-2.2 2.2-3.2 4.8-3 7h1.8c3.6 0 6.8-1.2 9-3.4',
					'M12.6 10.2c.8 1.4 1.7 2.6 3 3.6M10 12.6c.8 1.4 1.7 2.4 3 3.4',
					'M7.4 17.6 4.4 20.6M7.4 17.6 8 21',
					'M18.4 7.2 20.8 4.8M18.4 7.2l2.8.6',
				),
				'circles' => array( '17 9 0.7' ),
			),
			'sushi'       => array(
				'label'   => __( 'Sushi', 'restaurant-menu-builder' ),
				'group'   => 'seafood',
				'paths'   => array(
					'M4.6 10.6h6.6a1.4 1.4 0 0 1 1.4 1.4v5.2a1.4 1.4 0 0 1-1.4 1.4H4.6a1.4 1.4 0 0 1-1.4-1.4V12a1.4 1.4 0 0 1 1.4-1.4Z',
					'M13.8 8.6h5.6a1.4 1.4 0 0 1 1.4 1.4v7.2a1.4 1.4 0 0 1-1.4 1.4h-5.6a1.4 1.4 0 0 1-1.4-1.4V10a1.4 1.4 0 0 1 1.4-1.4Z',
				),
				'circles' => array( '7.9 14.6 1.5', '16.6 13.6 1.6' ),
			),
			'canned_fish' => array(
				'label' => __( 'Canned fish', 'restaurant-menu-builder' ),
				'group' => 'seafood',
				'paths' => array(
					'M4.4 9.6h15.2a1 1 0 0 1 1 1v7.4a1 1 0 0 1-1 1H4.4a1 1 0 0 1-1-1v-7.4a1 1 0 0 1 1-1Z',
					'M17.6 9.6 20 7.2a1.3 1.3 0 0 1 1.8 1.8l-1.2 1.2',
					'M7 14.4c1.6-2 4.4-2 6 0-1.6 2-4.4 2-6 0Z',
					'M13 14.4 15.2 12.8v3.2Z',
				),
			),
		);
	}

	/* ---------------------------------------------------------------- Produce */

	/**
	 * Fruit and vegetables.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	private static function produce(): array {
		return array(
			'carrot'      => array(
				'label' => __( 'Carrot', 'restaurant-menu-builder' ),
				'group' => 'produce',
				'paths' => array(
					'M15.2 6.8 19 10.6 8.6 21a1.6 1.6 0 0 1-2.6-1.6Z',
					'M16.4 5.6c0-1.9 1.3-3.2 3.2-3.4.2 1.9-1.1 3.3-3.2 3.4Z',
					'M14.6 6c-1.6-.6-2.4-1.9-2.4-3.8 1.9.3 2.9 1.5 3 3.4',
					'M12.8 12.6 14.6 14.4M10.4 15l1.8 1.8',
				),
			),
			'bell_pepper' => array(
				'label' => __( 'Pepper', 'restaurant-menu-builder' ),
				'group' => 'produce',
				'paths' => array(
					'M8.4 8.8c-2.2 1-3.6 3.2-3.6 5.8 0 3.4 2.3 6.2 5.2 6.2.8 0 1.4-.4 2-.4s1.2.4 2 .4c2.9 0 5.2-2.8 5.2-6.2 0-2.6-1.4-4.8-3.6-5.8',
					'M12 8.6V5.8',
					'M12 5.8c1.6-1 3-.8 4 .4-1 1.2-2.6 1.4-4-.4Z',
				),
			),
			'mushroom'    => array(
				'label'   => __( 'Mushroom', 'restaurant-menu-builder' ),
				'group'   => 'produce',
				'paths'   => array(
					'M4.6 12.4a7.4 7.4 0 0 1 14.8 0Z',
					'M10 12.4v5.2a2 2 0 0 0 4 0v-5.2',
				),
				'circles' => array( '9 9.6 1', '14.4 10.2 0.8' ),
			),
			'strawberry'  => array(
				'label'   => __( 'Strawberry', 'restaurant-menu-builder' ),
				'group'   => 'produce',
				'paths'   => array(
					'M12 20.6c-3.5 0-6.4-3-6.4-6.6 0-2.8 2.6-5 6.4-5s6.4 2.2 6.4 5c0 3.6-2.9 6.6-6.4 6.6Z',
					'M12 9V6.4',
					'M8.6 7.4c1.4-.8 2.6-.6 3.4.6.8-1.2 2-1.4 3.4-.6-1 1.4-2.2 2-3.4 2s-2.4-.6-3.4-2Z',
				),
				'circles' => array( '10 13.4 0.5', '14 13.4 0.5', '12 16.4 0.5' ),
			),
			'banana'      => array(
				'label' => __( 'Banana', 'restaurant-menu-builder' ),
				'group' => 'produce',
				'paths' => array(
					'M4.6 9.4c0 6.2 4.6 10.8 10.6 10.8 2 0 3.6-.6 4.4-1.6-4.6 0-8.4-1.4-10.4-4.2-1.4-2-2-4.2-2-5.4 0-.6-1.6-.6-2.6.4Z',
					'M4.6 9.4 4 6.6',
				),
			),
			'lemon'       => array(
				'label' => __( 'Citrus', 'restaurant-menu-builder' ),
				'group' => 'produce',
				'paths' => array(
					'M12 4.4a7.6 7.6 0 1 0 0 15.2 7.6 7.6 0 0 0 0-15.2Z',
					'M12 6.6a5.4 5.4 0 1 0 0 10.8 5.4 5.4 0 0 0 0-10.8Z',
					'M12 6.6v10.8M6.6 12h10.8M8.2 8.2l7.6 7.6M15.8 8.2 8.2 15.8',
				),
			),
		);
	}

	/* --------------------------------------------------------------- Desserts */

	/**
	 * Sweet courses.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	private static function desserts(): array {
		return array(
			'dessert'         => array(
				'label'   => __( 'Dessert', 'restaurant-menu-builder' ),
				'group'   => 'desserts',
				'paths'   => array(
					'M6.4 11h11.2l-1.6 8.8a1.8 1.8 0 0 1-1.8 1.4H9.8A1.8 1.8 0 0 1 8 19.8Z',
					'M9 11a3 3 0 0 1 6 0',
					'M12 6.6v1.6',
				),
				'circles' => array( '12 5.4 1.1' ),
			),
			'cupcake'         => array(
				'label'   => __( 'Cupcake', 'restaurant-menu-builder' ),
				'group'   => 'desserts',
				'paths'   => array(
					'M6.4 12.8h11.2l-1.2 7.4a1.6 1.6 0 0 1-1.6 1.4H9.2a1.6 1.6 0 0 1-1.6-1.4Z',
					'M9.4 14.2v6M12 14.2v6.8M14.6 14.2v6',
					'M6.4 12.8c0-2 1.4-3.4 3.2-3.6.6-1.8 2.4-2.8 4-2 1.6-.6 3.4.4 3.8 2.2 1.4.6 2 1.8 1.8 3.4Z',
				),
				'circles' => array( '12 5.4 0.9' ),
			),
			'cake'            => array(
				'label' => __( 'Cake', 'restaurant-menu-builder' ),
				'group' => 'desserts',
				'paths' => array(
					'M4.6 13.4h14.8a1.4 1.4 0 0 1 1.4 1.4v4.6a1.4 1.4 0 0 1-1.4 1.4H4.6a1.4 1.4 0 0 1-1.4-1.4v-4.6a1.4 1.4 0 0 1 1.4-1.4Z',
					'M3.2 16.8h17.6',
					'M8 13.4v-3.2M12 13.4v-3.6M16 13.4v-3.2',
					'M8 8.4c0-.8.8-1 .8-1.8M12 8c0-.8.8-1 .8-1.8M16 8.4c0-.8.8-1 .8-1.8',
				),
			),
			'cake_slice'      => array(
				'label' => __( 'Cake slice', 'restaurant-menu-builder' ),
				'group' => 'desserts',
				'paths' => array(
					'M4.4 18.8 12 6.4l7.6 12.4Z',
					'M7.4 13.8h9.2M5.8 16.4h12.4',
				),
			),
			'ice_cream_cone'  => array(
				'label' => __( 'Ice cream cone', 'restaurant-menu-builder' ),
				'group' => 'desserts',
				'paths' => array(
					'M8 12.2h8L12 21Z',
					'M8.4 12.2a3.6 3.6 0 0 1 7.2 0',
					'M9.6 9.4a2.6 2.6 0 0 1 4.8 0',
					'M9.4 14.8 12.6 12M10.6 17.4 14.4 13.8',
				),
			),
			'sundae'          => array(
				'label'   => __( 'Sundae', 'restaurant-menu-builder' ),
				'group'   => 'desserts',
				'paths'   => array(
					'M7 10.6h10l-1.4 5.4a2 2 0 0 1-2 1.6h-3.2a2 2 0 0 1-2-1.6Z',
					'M12 17.6v2.4M9 20.4h6',
				),
				'circles' => array( '9.8 8.6 1.9', '14.2 8.6 1.9', '12 6.8 1.9' ),
			),
			'popsicle'        => array(
				'label' => __( 'Ice lolly', 'restaurant-menu-builder' ),
				'group' => 'desserts',
				'paths' => array(
					'M7.8 3.6h8.4a1.6 1.6 0 0 1 1.6 1.6v7.6a5.8 5.8 0 0 1-5.8 5.8h-.6a5.8 5.8 0 0 1-5.8-5.8V5.2a1.6 1.6 0 0 1 1.6-1.6Z',
					'M12 18.6v2.8',
					'M9.4 6.4v6M12 6.4v6M14.6 6.4v6',
				),
			),
			'donut'           => array(
				'label' => __( 'Donut', 'restaurant-menu-builder' ),
				'group' => 'desserts',
				'paths' => array(
					'M12 4a8 8 0 1 0 0 16 8 8 0 0 0 0-16Z',
					'M12 9.6a2.4 2.4 0 1 0 0 4.8 2.4 2.4 0 0 0 0-4.8Z',
					'M8.6 8 9.6 9.2M15 8.4 14 9.6M8 14.4l1.4.8M15.8 14.8l-1.4.8M12 5.8v1.4',
				),
			),
			'cookie'          => array(
				'label'   => __( 'Cookie', 'restaurant-menu-builder' ),
				'group'   => 'desserts',
				'paths'   => array(
					'M12 4.2a7.8 7.8 0 1 0 0 15.6 7.8 7.8 0 0 0 0-15.6Z',
				),
				'circles' => array( '9.6 9.6 0.8', '14.2 10.4 0.8', '11.4 13.6 0.8', '15 14.6 0.7' ),
			),
			'candy'           => array(
				'label' => __( 'Sweets', 'restaurant-menu-builder' ),
				'group' => 'desserts',
				'paths' => array(
					'M8.4 9.6h7.2a2.4 2.4 0 0 1 0 4.8H8.4a2.4 2.4 0 0 1 0-4.8Z',
					'M6 12 2.8 9.2v5.6ZM18 12l3.2-2.8v5.6Z',
					'M10.4 10.8 13.6 13.2',
				),
			),
			'chocolate'       => array(
				'label' => __( 'Chocolate', 'restaurant-menu-builder' ),
				'group' => 'desserts',
				'paths' => array(
					'M6 6.6h12v10.8H6Z',
					'M10 6.6v10.8M14 6.6v10.8M6 10.2h12M6 13.8h12',
					'M6 17.4 4.4 19h12l1.6-1.6M18 6.6l1.6-1.6v12.4L18 17.4',
				),
			),
		);
	}

	/* ----------------------------------------------------------------- Drinks */

	/**
	 * Hot and cold drinks.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	private static function drinks(): array {
		return array(
			'coffee'        => array(
				'label' => __( 'Coffee', 'restaurant-menu-builder' ),
				'group' => 'drinks',
				'paths' => array(
					'M4 9.6h12v5.4a5 5 0 0 1-5 5H9a5 5 0 0 1-5-5Z',
					'M16 11.2h2.2a2.6 2.6 0 0 1 0 5.2H16',
					'M7.6 6.6c0-1.2 1.2-1.2 1.2-2.4M11.6 6.6c0-1.2 1.2-1.2 1.2-2.4',
				),
			),
			'coffee_togo'   => array(
				'label' => __( 'Coffee to go', 'restaurant-menu-builder' ),
				'group' => 'drinks',
				'paths' => array(
					'M7 9h10l-1 10.8a1.6 1.6 0 0 1-1.6 1.4H9.6A1.6 1.6 0 0 1 8 19.8Z',
					'M5.8 6h12.4a.8.8 0 0 1 .8.8v1.4a.8.8 0 0 1-.8.8H5.8a.8.8 0 0 1-.8-.8V6.8A.8.8 0 0 1 5.8 6Z',
					'M10.6 3.6h2.8V6h-2.8Z',
					'M7.6 13h8.8',
				),
			),
			'espresso'      => array(
				'label' => __( 'Espresso', 'restaurant-menu-builder' ),
				'group' => 'drinks',
				'paths' => array(
					'M6.4 10.6h8.2v3.4a4.1 4.1 0 0 1-8.2 0Z',
					'M14.6 11.6h1.8a2 2 0 0 1 0 4h-1.8',
					'M4 19h13',
				),
			),
			'tea'           => array(
				'label' => __( 'Tea', 'restaurant-menu-builder' ),
				'group' => 'drinks',
				'paths' => array(
					'M5.6 10.2h9.6v3.8a4.8 4.8 0 0 1-9.6 0Z',
					'M15.2 11.6H17a2.2 2.2 0 0 1 0 4.4h-1.8',
					'M11.6 10.2 13.6 6',
					'M12.8 3.6h2.8v2.6h-2.8Z',
				),
			),
			'teapot'        => array(
				'label' => __( 'Teapot', 'restaurant-menu-builder' ),
				'group' => 'drinks',
				'paths' => array(
					'M5.4 11.8h10.4V16a5.2 5.2 0 0 1-10.4 0Z',
					'M15.8 13.4c1.9 0 3.5-1.2 4.2-3',
					'M5.4 12.8H3.8a2.2 2.2 0 0 0 0 4.4h1',
					'M8.4 11.8a2.2 2.2 0 0 1 4.4 0',
					'M10.6 8.6V7.2',
				),
			),
			'beer'          => array(
				'label' => __( 'Beer', 'restaurant-menu-builder' ),
				'group' => 'drinks',
				'paths' => array(
					'M5.4 9h10v10.8a1.6 1.6 0 0 1-1.6 1.6H7a1.6 1.6 0 0 1-1.6-1.6Z',
					'M15.4 11h2.2a2.6 2.6 0 0 1 0 5.2h-2.2',
					'M5.4 9c1.2-1.6 2.4 1 3.6-.6s2.4 1 3.6-.6 2.4 1 2.8.2',
					'M8.4 12.6v5.4M12 12.6v5.4',
				),
			),
			'wine'          => array(
				'label' => __( 'Wine', 'restaurant-menu-builder' ),
				'group' => 'drinks',
				'paths' => array(
					'M7.2 3.6h9.6l-1 7a3.9 3.9 0 0 1-7.6 0Z',
					'M12 14.6v5.8M8.6 20.4h6.8',
					'M7.8 8h8.4',
				),
			),
			'wine_bottle'   => array(
				'label' => __( 'Wine bottle', 'restaurant-menu-builder' ),
				'group' => 'drinks',
				'paths' => array(
					'M10 3.6h4v4.4l2 3v9.4a1.8 1.8 0 0 1-1.8 1.8H9.8A1.8 1.8 0 0 1 8 20.4V11l2-3Z',
					'M8 13.6h8M8 17.6h8',
				),
			),
			'cocktail'      => array(
				'label' => __( 'Cocktail', 'restaurant-menu-builder' ),
				'group' => 'drinks',
				'paths' => array(
					'M6.4 8.6h11.2l-1.2 10.8a1.8 1.8 0 0 1-1.8 1.6H9.4a1.8 1.8 0 0 1-1.8-1.6Z',
					'M13.6 8.6 16.6 4',
					'M7.2 12.6h9.6',
				),
			),
			'martini'       => array(
				'label' => __( 'Martini', 'restaurant-menu-builder' ),
				'group' => 'drinks',
				'paths' => array(
					'M4.6 7h14.8L12 15.2Z',
					'M12 15.2v5.2M8.6 20.4h6.8',
					'M15.6 9.6 18.6 4.6',
				),
			),
			'drink'         => array(
				'label' => __( 'Cold drink', 'restaurant-menu-builder' ),
				'group' => 'drinks',
				'paths' => array(
					'M6.6 7h10.8l-1.4 13a1.8 1.8 0 0 1-1.8 1.6H9.8A1.8 1.8 0 0 1 8 20Z',
					'M7.2 11.6h9.6',
					'M13.4 7 15.6 3.4',
				),
			),
			'milk'          => array(
				'label' => __( 'Milk', 'restaurant-menu-builder' ),
				'group' => 'drinks',
				'paths' => array(
					'M7.4 9.4h9.2v10.4a1.4 1.4 0 0 1-1.4 1.4H8.8a1.4 1.4 0 0 1-1.4-1.4Z',
					'M7.4 9.4 12 4.6l4.6 4.8M12 4.6v4.8',
					'M9.6 13.6h4.8M9.6 16.4h4.8',
				),
			),
			'water_bottle'  => array(
				'label' => __( 'Water', 'restaurant-menu-builder' ),
				'group' => 'drinks',
				'paths' => array(
					'M9.4 7.4h5.2v12a1.8 1.8 0 0 1-1.8 1.8h-1.6a1.8 1.8 0 0 1-1.8-1.8Z',
					'M10.6 4.6h2.8v2.8h-2.8Z',
					'M10.2 2.8h3.6v1.8h-3.6Z',
					'M9.4 11.6h5.2',
				),
			),
			'smoothie'      => array(
				'label'   => __( 'Smoothie', 'restaurant-menu-builder' ),
				'group'   => 'drinks',
				'paths'   => array(
					'M7 9.6h10l-1.2 10.2a1.8 1.8 0 0 1-1.8 1.6h-4a1.8 1.8 0 0 1-1.8-1.6Z',
					'M13.8 9.6 16.2 5',
					'M7.6 13.4h8.8',
				),
				'circles' => array( '9.4 6.8 1.6' ),
			),
		);
	}

	/* ----------------------------------------------------------------- Pantry */

	/**
	 * Pantry staples and kitchen tools.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	private static function pantry(): array {
		return array(
			'olive_oil'     => array(
				'label' => __( 'Oil', 'restaurant-menu-builder' ),
				'group' => 'pantry',
				'paths' => array(
					'M11 3.6h2.4v3l2.2 2.6v9.6a2 2 0 0 1-2 2h-2.8a2 2 0 0 1-2-2V9.2l2.2-2.6Z',
					'M9 13.4h6.6',
					'M19.4 12.8c.9 1.1 1.3 1.8 1.3 2.4a1.3 1.3 0 0 1-2.6 0c0-.6.4-1.3 1.3-2.4Z',
				),
			),
			'salt'          => array(
				'label'   => __( 'Salt', 'restaurant-menu-builder' ),
				'group'   => 'pantry',
				'paths'   => array(
					'M8.4 9.8h7.2l1 9.4a1.6 1.6 0 0 1-1.6 1.8H9a1.6 1.6 0 0 1-1.6-1.8Z',
					'M8.8 9.8a3.2 3.2 0 0 1 6.4 0',
					'M7.8 14.4h8.4',
				),
				'circles' => array( '10.8 7.4 0.45', '13.2 7.4 0.45', '12 6.2 0.45' ),
			),
			'pepper_mill'   => array(
				'label'   => __( 'Pepper mill', 'restaurant-menu-builder' ),
				'group'   => 'pantry',
				'paths'   => array(
					'M9.4 20.6h5.2l.8-8.6H8.6Z',
					'M9.6 12h4.8V8.6a2.4 2.4 0 0 0-4.8 0Z',
					'M12 6.2V4.8',
				),
				'circles' => array( '12 3.9 0.9' ),
			),
			'honey'         => array(
				'label' => __( 'Honey', 'restaurant-menu-builder' ),
				'group' => 'pantry',
				'paths' => array(
					'M12 3.6v6.6',
					'M8.8 10.2h6.4M8.6 12.6h6.8M9 15h6M9.8 17.4h4.4M11 19.8h2',
					'M8.8 10.2c-.6 3.4.4 6.8 3.2 9.6 2.8-2.8 3.8-6.2 3.2-9.6',
				),
			),
			'jam_jar'       => array(
				'label' => __( 'Preserves', 'restaurant-menu-builder' ),
				'group' => 'pantry',
				'paths' => array(
					'M7.4 9.6h9.2v9.4a1.6 1.6 0 0 1-1.6 1.6H9a1.6 1.6 0 0 1-1.6-1.6Z',
					'M6.6 6.4h10.8v3.2H6.6Z',
					'M9.6 13.6h4.8M9.6 16.4h4.8',
				),
			),
			'cutting_board' => array(
				'label'   => __( 'Cutting board', 'restaurant-menu-builder' ),
				'group'   => 'pantry',
				'paths'   => array(
					'M4.6 4.6h11.2a1.6 1.6 0 0 1 1.6 1.6v11.2a1.6 1.6 0 0 1-1.6 1.6H4.6A1.6 1.6 0 0 1 3 17.4V6.2a1.6 1.6 0 0 1 1.6-1.6Z',
					'M17.4 8.6h2.4a1.2 1.2 0 0 1 0 2.4h-2.4',
					'M6.6 12h7.2M6.6 15h7.2',
				),
				'circles' => array( '10.2 8.4 1.8' ),
			),
			'whisk'         => array(
				'label' => __( 'Whisk', 'restaurant-menu-builder' ),
				'group' => 'pantry',
				'paths' => array(
					'M12 3.6v3.8M10.8 3.6h2.4',
					'M12 7.4c-3.2 1.4-5.2 5-4.6 8.6.5 2.8 2.6 4.4 4.6 4.4s4.1-1.6 4.6-4.4c.6-3.6-1.4-7.2-4.6-8.6Z',
					'M12 7.4v13M9.4 8.8c-.8 3.8-.8 8 0 11.2M14.6 8.8c.8 3.8.8 8 0 11.2',
				),
			),
			'colander'      => array(
				'label'   => __( 'Colander', 'restaurant-menu-builder' ),
				'group'   => 'pantry',
				'paths'   => array(
					'M3.6 12.4h16.8a8.4 8.4 0 0 1-16.8 0Z',
					'M20.4 12.4h2.2M3.6 12.4H1.4',
				),
				'circles' => array( '9 15 0.55', '12 16.4 0.55', '15 15 0.55', '10.6 17.8 0.55', '13.4 17.8 0.55' ),
			),
			'scale'         => array(
				'label'   => __( 'Kitchen scale', 'restaurant-menu-builder' ),
				'group'   => 'pantry',
				'paths'   => array(
					'M6 12.8h12a1.6 1.6 0 0 1 1.6 1.6v4.6a1.6 1.6 0 0 1-1.6 1.6H6a1.6 1.6 0 0 1-1.6-1.6v-4.6A1.6 1.6 0 0 1 6 12.8Z',
					'M7 9.8h10a1 1 0 0 1 0 2H7a1 1 0 0 1 0-2Z',
					'M12 15.4v1.4',
				),
				'circles' => array( '12 16.8 1.8' ),
			),
			'grinder'       => array(
				'label'   => __( 'Coffee grinder', 'restaurant-menu-builder' ),
				'group'   => 'pantry',
				'paths'   => array(
					'M7.4 11.4h9.2l-.8 8a1.6 1.6 0 0 1-1.6 1.4H9.8a1.6 1.6 0 0 1-1.6-1.4Z',
					'M8 11.4V8.4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v3',
					'M12 7.4V5.4M12 5.4h2.6',
					'M9.6 16.4h4.8',
				),
				'circles' => array( '15.4 5.4 1' ),
			),
		);
	}

	/* ---------------------------------------------------------------- Service */

	/**
	 * Front of house and service icons.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	private static function service(): array {
		return array(
			'chef_hat'         => array(
				'label' => __( 'Chef special', 'restaurant-menu-builder' ),
				'group' => 'service',
				'paths' => array(
					'M7.4 17.6h9.2v3.2H7.4Z',
					'M7.4 17.6c-2.2 0-3.9-1.9-3.9-4.2 0-1.8 1.2-3.4 2.8-3.9C6.5 6.8 8.8 4.6 12 4.6s5.5 2.2 5.7 4.9c1.6.5 2.8 2.1 2.8 3.9 0 2.3-1.7 4.2-3.9 4.2',
					'M10 17.6v-3.4M14 17.6v-3.4',
				),
			),
			'cloche'           => array(
				'label'   => __( 'Served dish', 'restaurant-menu-builder' ),
				'group'   => 'service',
				'paths'   => array(
					'M3.4 18.8h17.2',
					'M5.4 18.8a6.6 6.6 0 0 1 13.2 0Z',
					'M12 12.2v-1.6',
				),
				'circles' => array( '12 9.6 1' ),
			),
			'cutlery'          => array(
				'label' => __( 'Cutlery', 'restaurant-menu-builder' ),
				'group' => 'service',
				'paths' => array(
					'M7.4 3.6v5.2a2 2 0 0 0 4 0V3.6M9.4 3.6v5.2M9.4 10.8v9.6',
					'M16.6 3.6c1.4 1.4 2 3.4 2 5.6 0 1.9-.8 3.1-2 3.5v7.7',
				),
			),
			'cutlery_crossed'  => array(
				'label' => __( 'Restaurant', 'restaurant-menu-builder' ),
				'group' => 'service',
				'paths' => array(
					'M6.6 3.6v4.6a2 2 0 0 0 2 2h.4l7 10.2M8.6 3.6v4.6',
					'M17.4 3.6c1.2 1.4 1.8 3.2 1.8 5 0 1.6-.6 2.6-1.6 3.2L7.8 20.4',
				),
			),
			'menu_book'        => array(
				'label' => __( 'Menu card', 'restaurant-menu-builder' ),
				'group' => 'service',
				'paths' => array(
					'M5 4.6h14a1.4 1.4 0 0 1 1.4 1.4v13a1.4 1.4 0 0 1-1.4 1.4H5A1.4 1.4 0 0 1 3.6 19V6A1.4 1.4 0 0 1 5 4.6Z',
					'M8.4 8.6h7.2M8.4 12h7.2M8.4 15.4h4.4',
				),
			),
			'table'            => array(
				'label' => __( 'Dining table', 'restaurant-menu-builder' ),
				'group' => 'service',
				'paths' => array(
					'M7 9.6h10',
					'M12 9.6v8M9.4 20.4h5.2',
					'M3.4 12.4a2.2 2.2 0 0 1 4.4 0V15H3.4Z',
					'M4 15v3.6M7.2 15v3.6',
					'M16.2 12.4a2.2 2.2 0 0 1 4.4 0V15h-4.4Z',
					'M16.8 15v3.6M20 15v3.6',
				),
			),
			'takeout'          => array(
				'label' => __( 'Takeaway', 'restaurant-menu-builder' ),
				'group' => 'service',
				'paths' => array(
					'M5.6 8.8h12.8l-1.2 10.2a1.8 1.8 0 0 1-1.8 1.6H8.6a1.8 1.8 0 0 1-1.8-1.6Z',
					'M5.6 8.8 12 5.2l6.4 3.6',
					'M9.4 6.8c1.8-1.6 3.4-1.6 5.2 0',
				),
			),
			'bell'             => array(
				'label'   => __( 'Service bell', 'restaurant-menu-builder' ),
				'group'   => 'service',
				'paths'   => array(
					'M3.8 19h16.4a1 1 0 0 1 0 2H3.8a1 1 0 0 1 0-2Z',
					'M6.4 19a5.6 5.6 0 0 1 11.2 0Z',
					'M12 13.4v-2',
				),
				'circles' => array( '12 10.2 1.1' ),
			),
		);
	}

	/* ----------------------------------------------------------------- Labels */

	/**
	 * Dietary and promotional labels.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	private static function labels(): array {
		return array(
			'special' => array(
				'label' => __( 'Chef special', 'restaurant-menu-builder' ),
				'group' => 'labels',
				'paths' => array(
					'm12 3 2.6 5.4 5.9.8-4.2 4.2 1 5.8L12 16.5 6.7 19.2l1-5.8-4.2-4.2 5.9-.8L12 3Z',
				),
			),
			'spicy'   => array(
				'label' => __( 'Spicy', 'restaurant-menu-builder' ),
				'group' => 'labels',
				'paths' => array(
					'M12.4 19.6c-4.8 0-8.6-2.9-8.6-6.7 3.8 0 5.8-2 6.8-4 3.6 1.2 6.6 4.3 6.6 7.5 0 2-2.1 3.2-4.8 3.2Z',
					'M10.6 8.9c0-2 1-3.4 3-3.7M13.6 5.2c.8-.8 1.8-1.1 3-1.1',
				),
			),
			'vegan'   => array(
				'label' => __( 'Vegan', 'restaurant-menu-builder' ),
				'group' => 'labels',
				'paths' => array(
					'M20 4c0 9-5 14-13 14C7 9 12 4 20 4Z',
					'M4 20c2-4 5-7 9-9',
				),
			),
			'kids'    => array(
				'label' => __( 'Kids', 'restaurant-menu-builder' ),
				'group' => 'labels',
				'paths' => array(
					'M12 3.4a4 4 0 1 1 0 8 4 4 0 0 1 0-8Z',
					'M5 21c0-4 3-6 7-6s7 2 7 6',
				),
			),
		);
	}

	/* --------------------------------------------------------- Interface set */

	/**
	 * Small interface icons used by the admin chrome.
	 *
	 * These are never stored against a record; they only exist so the admin
	 * screens can draw real icons without pulling in an external icon font.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	public static function ui(): array {
		return array(
			'dashboard'  => array( 'paths' => array( 'M4 4.8h6v6H4ZM14 4.8h6v4h-6ZM14 12.8h6v6.4h-6ZM4 14.8h6v4.4H4Z' ) ),
			'menus'      => array( 'paths' => array( 'M4.6 5.4h14.8M4.6 12h14.8M4.6 18.6h9.4' ) ),
			'category'   => array( 'paths' => array( 'M4.6 5.4h6v6h-6ZM13.4 5.4h6v6h-6ZM4.6 13.6h6v6h-6ZM13.4 13.6h6v6h-6Z' ) ),
			'item'       => array( 'paths' => array( 'M8.6 6.6h11.4M8.6 12h11.4M8.6 17.4h11.4' ), 'circles' => array( '4.6 6.6 1', '4.6 12 1', '4.6 17.4 1' ) ),
			'style'      => array( 'paths' => array( 'M12 3.4a8.6 8.6 0 1 0 0 17.2c1.2 0 2-.8 2-1.8 0-1.6-1.4-1.8-1.4-3 0-.9.8-1.6 1.8-1.6h1.6a4.6 4.6 0 0 0 4.6-4.6c0-3.6-3.8-6.2-8.6-6.2Z' ), 'circles' => array( '8.2 9.6 1.1', '12 7.4 1.1', '15.6 9.8 1.1' ) ),
			'settings'   => array( 'paths' => array( 'M12 8.6a3.4 3.4 0 1 0 0 6.8 3.4 3.4 0 0 0 0-6.8Z', 'M19.2 14.2a1.5 1.5 0 0 0 .3 1.7l.1.1a1.8 1.8 0 1 1-2.6 2.6l-.1-.1a1.5 1.5 0 0 0-2.6 1.1v.3a1.8 1.8 0 1 1-3.6 0v-.2a1.5 1.5 0 0 0-2.6-1.1l-.1.1a1.8 1.8 0 1 1-2.6-2.6l.1-.1a1.5 1.5 0 0 0-1.1-2.6h-.3a1.8 1.8 0 1 1 0-3.6h.2a1.5 1.5 0 0 0 1.1-2.6l-.1-.1a1.8 1.8 0 1 1 2.6-2.6l.1.1a1.5 1.5 0 0 0 2.6-1.1V3a1.8 1.8 0 1 1 3.6 0v.2a1.5 1.5 0 0 0 2.6 1.1l.1-.1a1.8 1.8 0 1 1 2.6 2.6l-.1.1a1.5 1.5 0 0 0 1.1 2.6h.3a1.8 1.8 0 1 1 0 3.6h-.2a1.5 1.5 0 0 0-1.4.9Z' ) ),
			'help'       => array( 'paths' => array( 'M12 3.4a8.6 8.6 0 1 0 0 17.2 8.6 8.6 0 0 0 0-17.2Z', 'M9.6 9.6a2.5 2.5 0 0 1 4.9.6c0 1.7-2.5 2.5-2.5 2.5' ), 'circles' => array( '12 16.4 0.6' ) ),
			'plus'       => array( 'paths' => array( 'M12 5.4v13.2M5.4 12h13.2' ) ),
			'edit'       => array( 'paths' => array( 'M16.5 3.9a2.1 2.1 0 0 1 3 3L8.4 18h-3v-3Z', 'M14.4 6l3.6 3.6' ) ),
			'trash'      => array( 'paths' => array( 'M4.6 6.6h14.8M9.4 6.6V4.8a1.2 1.2 0 0 1 1.2-1.2h2.8a1.2 1.2 0 0 1 1.2 1.2v1.8', 'M6.6 6.6 7.6 19a1.6 1.6 0 0 0 1.6 1.4h5.6a1.6 1.6 0 0 0 1.6-1.4l1-12.4', 'M10.4 10.4v6M13.6 10.4v6' ) ),
			'copy'       => array( 'paths' => array( 'M9 9h9.4a1.6 1.6 0 0 1 1.6 1.6V20a1.6 1.6 0 0 1-1.6 1.6H9A1.6 1.6 0 0 1 7.4 20v-9.4A1.6 1.6 0 0 1 9 9Z', 'M4.6 15.4a1.6 1.6 0 0 1-1.6-1.6V4.4a1.6 1.6 0 0 1 1.6-1.6H14a1.6 1.6 0 0 1 1.6 1.6' ) ),
			'eye'        => array( 'paths' => array( 'M2.4 12s3.6-6.4 9.6-6.4S21.6 12 21.6 12s-3.6 6.4-9.6 6.4S2.4 12 2.4 12Z', 'M12 9.4a2.6 2.6 0 1 0 0 5.2 2.6 2.6 0 0 0 0-5.2Z' ) ),
			'search'     => array( 'paths' => array( 'M11 4.4a6.6 6.6 0 1 0 0 13.2 6.6 6.6 0 0 0 0-13.2Z', 'M15.8 15.8 20 20' ) ),
			'code'       => array( 'paths' => array( 'M8.6 8 4.4 12l4.2 4M15.4 8l4.2 4-4.2 4M13.4 5.4 10.6 18.6' ) ),
			'check'      => array( 'paths' => array( 'M4.6 12.6 9.4 17.4 19.4 6.6' ) ),
			'close'      => array( 'paths' => array( 'M6 6l12 12M18 6 6 18' ) ),
			'arrow_left' => array( 'paths' => array( 'M19 12H5M11 6l-6 6 6 6' ) ),
			'chevron_up'   => array( 'paths' => array( 'M6 14.6 12 8.6l6 6' ) ),
			'chevron_down' => array( 'paths' => array( 'M6 9.4 12 15.4l6-6' ) ),
			'grip'       => array( 'circles' => array( '9 6 1.1', '15 6 1.1', '9 12 1.1', '15 12 1.1', '9 18 1.1', '15 18 1.1' ) ),
			'image'      => array( 'paths' => array( 'M5 4.6h14a1.4 1.4 0 0 1 1.4 1.4v12a1.4 1.4 0 0 1-1.4 1.4H5A1.4 1.4 0 0 1 3.6 18V6A1.4 1.4 0 0 1 5 4.6Z', 'm3.6 15.6 4.6-4.2 4 3.4 3.6-3 4.6 4' ), 'circles' => array( '8.6 8.6 1.3' ) ),
			'sparkle'    => array( 'paths' => array( 'M12 3.6 13.6 9 19 10.6 13.6 12.2 12 17.6 10.4 12.2 5 10.6 10.4 9Z', 'M18 16.4l.7 2.1 2.1.7-2.1.7-.7 2.1-.7-2.1-2.1-.7 2.1-.7Z' ) ),
		);
	}

	/* -------------------------------------------------------------- Rendering */

	/**
	 * Validate a category icon key.
	 *
	 * @param string $key Raw key.
	 * @return string Empty string when the key is unknown.
	 */
	public static function sanitize( string $key ): string {
		$key = sanitize_key( $key );

		return array_key_exists( $key, self::all() ) ? $key : '';
	}

	/**
	 * Render a category icon as inline SVG.
	 *
	 * @param string $key       Icon key.
	 * @param string $css_class Optional extra class.
	 * @param int    $size      Pixel size.
	 * @return string SVG markup, or an empty string when the key is unknown.
	 */
	public static function render( string $key, string $css_class = 'rmb-icon', int $size = 20 ): string {
		$key = self::sanitize( $key );

		if ( '' === $key ) {
			return '';
		}

		$icons = self::all();

		return self::svg( $icons[ $key ], $css_class, $size );
	}

	/**
	 * Render an interface icon as inline SVG.
	 *
	 * @param string $key       Icon key.
	 * @param string $css_class Optional extra class.
	 * @param int    $size      Pixel size.
	 * @return string SVG markup, or an empty string when the key is unknown.
	 */
	public static function render_ui( string $key, string $css_class = 'rmb-ui-icon', int $size = 18 ): string {
		$icons = self::ui();
		$key   = sanitize_key( $key );

		if ( ! isset( $icons[ $key ] ) ) {
			return '';
		}

		return self::svg( $icons[ $key ], $css_class, $size );
	}

	/**
	 * Build the SVG element for one icon definition.
	 *
	 * @param array<string,mixed> $icon      Icon definition.
	 * @param string              $css_class CSS class.
	 * @param int                 $size      Pixel size.
	 * @return string
	 */
	private static function svg( array $icon, string $css_class, int $size ): string {
		$size  = max( 8, min( 128, $size ) );
		$shapes = '';

		foreach ( self::paths_of( $icon ) as $path ) {
			$shapes .= '<path d="' . esc_attr( $path ) . '"/>';
		}

		foreach ( self::circles_of( $icon ) as $circle ) {
			$shapes .= sprintf(
				'<circle cx="%s" cy="%s" r="%s"/>',
				esc_attr( (string) $circle[0] ),
				esc_attr( (string) $circle[1] ),
				esc_attr( (string) $circle[2] )
			);
		}

		if ( '' === $shapes ) {
			return '';
		}

		return sprintf(
			'<svg class="%1$s" viewBox="0 0 24 24" width="%2$d" height="%2$d" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%3$s</svg>',
			esc_attr( $css_class ),
			$size,
			$shapes
		);
	}

	/**
	 * Path data for an icon, with anything that is not path syntax removed.
	 *
	 * @param array<string,mixed> $icon Icon definition.
	 * @return string[]
	 */
	public static function paths_of( array $icon ): array {
		$raw = array();

		if ( isset( $icon['paths'] ) && is_array( $icon['paths'] ) ) {
			$raw = $icon['paths'];
		} elseif ( isset( $icon['path'] ) ) {
			// Supported for third party filters written against the 1.0 format.
			$raw = array( $icon['path'] );
		}

		$paths = array();

		foreach ( $raw as $path ) {
			$path = preg_replace( '/[^0-9eE.,\-\s MmLlHhVvCcSsQqTtAaZz]/', '', (string) $path );
			$path = trim( (string) $path );

			if ( '' !== $path ) {
				$paths[] = $path;
			}
		}

		return $paths;
	}

	/**
	 * Circles for an icon as numeric triples.
	 *
	 * @param array<string,mixed> $icon Icon definition.
	 * @return array<int,array{0:float,1:float,2:float}>
	 */
	public static function circles_of( array $icon ): array {
		if ( ! isset( $icon['circles'] ) || ! is_array( $icon['circles'] ) ) {
			return array();
		}

		$circles = array();

		foreach ( $icon['circles'] as $circle ) {
			$parts = preg_split( '/\s+/', trim( (string) $circle ) );

			if ( ! is_array( $parts ) || 3 !== count( $parts ) ) {
				continue;
			}

			// Anything that is not plainly numeric is dropped rather than cast,
			// so a malformed entry never becomes a silently truncated circle.
			if ( count( array_filter( $parts, 'is_numeric' ) ) !== 3 ) {
				continue;
			}

			$circles[] = array( (float) $parts[0], (float) $parts[1], (float) $parts[2] );
		}

		return $circles;
	}

	/**
	 * Icon definitions for the admin picker.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	public static function for_admin(): array {
		$groups = self::groups();
		$list   = array();

		foreach ( self::all() as $key => $icon ) {
			$group = (string) ( $icon['group'] ?? 'mains' );

			$list[] = array(
				'key'     => (string) $key,
				'label'   => (string) ( $icon['label'] ?? $key ),
				'group'   => $group,
				'groupLabel' => (string) ( $groups[ $group ] ?? $group ),
				'paths'   => self::paths_of( $icon ),
				'circles' => self::circles_of( $icon ),
			);
		}

		return $list;
	}

	/**
	 * Interface icon definitions for the admin scripts.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	public static function ui_for_admin(): array {
		$list = array();

		foreach ( self::ui() as $key => $icon ) {
			$list[ $key ] = array(
				'paths'   => self::paths_of( $icon ),
				'circles' => self::circles_of( $icon ),
			);
		}

		return $list;
	}
}
