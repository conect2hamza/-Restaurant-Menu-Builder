<?php
/**
 * Currency definitions and price formatting.
 *
 * @package RestaurantMenuBuilder
 */

declare( strict_types=1 );

namespace RestaurantMenuBuilder;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Formats prices. No currency symbol is ever hard coded into a template.
 */
class Currency {

	/**
	 * Supported currencies.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	public static function all(): array {
		/**
		 * Filter the supported currency list.
		 *
		 * Add an entry to support an additional currency:
		 * 'JPY' => array( 'label' => 'Japanese yen', 'symbol' => '¥', 'decimals' => 0 )
		 *
		 * @param array<string,array<string,mixed>> $currencies Currency definitions.
		 */
		return apply_filters(
			'rmb_currencies',
			array(
				'USD' => array(
					'label'    => __( 'US dollar', 'restaurant-menu-builder' ),
					'symbol'   => '$',
					'decimals' => 2,
				),
				'EUR' => array(
					'label'    => __( 'Euro', 'restaurant-menu-builder' ),
					'symbol'   => '€',
					'decimals' => 2,
				),
				'GBP' => array(
					'label'    => __( 'Pound sterling', 'restaurant-menu-builder' ),
					'symbol'   => '£',
					'decimals' => 2,
				),
				'PKR' => array(
					'label'    => __( 'Pakistani rupee', 'restaurant-menu-builder' ),
					'symbol'   => 'Rs',
					'decimals' => 0,
				),
				'AED' => array(
					'label'    => __( 'UAE dirham', 'restaurant-menu-builder' ),
					'symbol'   => 'AED',
					'decimals' => 2,
				),
				'CAD' => array(
					'label'    => __( 'Canadian dollar', 'restaurant-menu-builder' ),
					'symbol'   => 'C$',
					'decimals' => 2,
				),
				'AUD' => array(
					'label'    => __( 'Australian dollar', 'restaurant-menu-builder' ),
					'symbol'   => 'A$',
					'decimals' => 2,
				),
				'INR' => array(
					'label'    => __( 'Indian rupee', 'restaurant-menu-builder' ),
					'symbol'   => '₹',
					'decimals' => 2,
				),
				'SAR' => array(
					'label'    => __( 'Saudi riyal', 'restaurant-menu-builder' ),
					'symbol'   => 'SR',
					'decimals' => 2,
				),
			)
		);
	}

	/**
	 * Whether a currency code is supported.
	 *
	 * @param string $code Currency code.
	 * @return bool
	 */
	public static function is_supported( string $code ): bool {
		return array_key_exists( strtoupper( $code ), self::all() );
	}

	/**
	 * Definition for a currency code, falling back to USD.
	 *
	 * @param string $code Currency code.
	 * @return array<string,mixed>
	 */
	public static function definition( string $code ): array {
		$all  = self::all();
		$code = strtoupper( $code );

		if ( isset( $all[ $code ] ) ) {
			return $all[ $code ];
		}

		return isset( $all['USD'] ) ? $all['USD'] : array(
			'label'    => $code,
			'symbol'   => '',
			'decimals' => 2,
		);
	}

	/**
	 * Format a price for display.
	 *
	 * @param float|null $amount   Price value.
	 * @param string     $code     Currency code. Defaults to the saved setting.
	 * @param string     $position Symbol position. Defaults to the saved setting.
	 * @return string Empty string when no amount was supplied.
	 */
	public static function format( ?float $amount, string $code = '', string $position = '' ): string {
		if ( null === $amount ) {
			return '';
		}

		$code     = '' !== $code ? $code : (string) Settings::get( 'currency', 'USD' );
		$position = '' !== $position ? $position : (string) Settings::get( 'currency_position', 'before' );

		$definition = self::definition( $code );
		$decimals   = isset( $definition['decimals'] ) ? (int) $definition['decimals'] : 2;
		$symbol     = isset( $definition['symbol'] ) ? (string) $definition['symbol'] : '';

		$number = number_format_i18n( $amount, $decimals );

		if ( 'after' === $position ) {
			$formatted = $number . ' ' . $symbol;
		} else {
			$separator = ( 1 === strlen( $symbol ) ) ? '' : ' ';
			$formatted = $symbol . $separator . $number;
		}

		/**
		 * Filter a formatted price string.
		 *
		 * @param string $formatted Formatted price.
		 * @param float  $amount    Raw amount.
		 * @param string $code      Currency code.
		 */
		return apply_filters( 'rmb_format_price', trim( $formatted ), $amount, $code );
	}
}
