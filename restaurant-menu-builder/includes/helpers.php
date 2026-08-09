<?php
/**
 * Shared helper functions.
 *
 * @package RestaurantMenuBuilder
 */

declare( strict_types=1 );

namespace RestaurantMenuBuilder;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Capability required to manage menus.
 *
 * @return string
 */
function manage_capability(): string {
	/**
	 * Filter the capability required to manage restaurant menus.
	 *
	 * @param string $capability Capability name.
	 */
	$capability = apply_filters( 'rmb_manage_capability', 'manage_options' );

	return is_string( $capability ) && '' !== $capability ? $capability : 'manage_options';
}

/**
 * Whether the current user may manage menus.
 *
 * @return bool
 */
function current_user_can_manage(): bool {
	return current_user_can( manage_capability() );
}

/**
 * Decode a JSON column into an array.
 *
 * @param mixed $value Raw column value.
 * @return array<string,mixed>
 */
function decode_json( $value ): array {
	if ( is_array( $value ) ) {
		return $value;
	}

	if ( ! is_string( $value ) || '' === $value ) {
		return array();
	}

	$decoded = json_decode( $value, true );

	return is_array( $decoded ) ? $decoded : array();
}

/**
 * Encode an array for storage in a JSON column.
 *
 * @param array<string,mixed> $value Value to encode.
 * @return string
 */
function encode_json( array $value ): string {
	$encoded = wp_json_encode( $value );

	return is_string( $encoded ) ? $encoded : '{}';
}

/**
 * Sanitize a status value.
 *
 * @param mixed $status Raw status.
 * @return string Either "active" or "draft".
 */
function sanitize_status( $status ): string {
	$status = is_string( $status ) ? sanitize_key( $status ) : '';

	return 'active' === $status ? 'active' : 'draft';
}

/**
 * Sanitize a hex colour, allowing empty values.
 *
 * @param mixed $color Raw colour.
 * @return string
 */
function sanitize_hex_color_value( $color ): string {
	if ( ! is_string( $color ) ) {
		return '';
	}

	$color = trim( $color );

	if ( '' === $color ) {
		return '';
	}

	if ( ! preg_match( '/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $color ) ) {
		return '';
	}

	return strtolower( $color );
}

/**
 * Sanitize a CSS length such as "18px" or "1.25rem".
 *
 * @param mixed  $value    Raw value.
 * @param string $fallback Value returned when the input is invalid.
 * @return string
 */
function sanitize_css_length( $value, string $fallback = '' ): string {
	if ( ! is_scalar( $value ) ) {
		return $fallback;
	}

	$value = trim( (string) $value );

	if ( '' === $value ) {
		return $fallback;
	}

	if ( preg_match( '/^-?\d+(\.\d+)?(px|em|rem|%|vw|vh|pt)?$/', $value ) ) {
		return $value;
	}

	return $fallback;
}

/**
 * Sanitize a price value.
 *
 * @param mixed $value Raw price.
 * @return float|null Null when no price was supplied.
 */
function sanitize_price( $value ): ?float {
	if ( null === $value || '' === $value || is_array( $value ) ) {
		return null;
	}

	$value = str_replace( array( ',', ' ' ), '', (string) $value );

	if ( ! is_numeric( $value ) ) {
		return null;
	}

	$price = round( (float) $value, 2 );

	if ( $price < 0 ) {
		return null;
	}

	return min( $price, 99999999.99 );
}

/**
 * Build a unique slug for a table column.
 *
 * @param string $slug   Desired slug.
 * @param string $table  Fully qualified table name.
 * @param int    $ignore Row ID to exclude from the check.
 * @return string
 */
function unique_slug( string $slug, string $table, int $ignore = 0 ): string {
	global $wpdb;

	$slug = sanitize_title( $slug );

	if ( '' === $slug ) {
		$slug = 'menu';
	}

	$slug      = substr( $slug, 0, 180 );
	$candidate = $slug;
	$suffix    = 2;

	// Table name is built from $wpdb->prefix and cannot be user supplied.
	while ( true ) {
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$exists = $wpdb->get_var(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT id FROM {$table} WHERE slug = %s AND id != %d LIMIT 1",
				$candidate,
				$ignore
			)
		);

		if ( null === $exists ) {
			return $candidate;
		}

		$candidate = $slug . '-' . $suffix;
		++$suffix;

		if ( $suffix > 500 ) {
			return $slug . '-' . wp_generate_password( 6, false, false );
		}
	}
}

/**
 * Return a label for a "copy" of an existing record.
 *
 * @param string $name Original name.
 * @return string
 */
function copy_name( string $name ): string {
	/* translators: %s: original record name. */
	return substr( sprintf( __( '%s (copy)', 'restaurant-menu-builder' ), $name ), 0, 191 );
}
