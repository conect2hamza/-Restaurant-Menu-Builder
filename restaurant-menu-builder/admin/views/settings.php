<?php
/**
 * Settings screen.
 *
 * @package RestaurantMenuBuilder
 */

declare( strict_types=1 );

use RestaurantMenuBuilder\Admin\Admin;
use RestaurantMenuBuilder\Currency;
use RestaurantMenuBuilder\Menu;
use RestaurantMenuBuilder\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rmb_settings = Settings::general();
$rmb_menus    = Menu::all( array( 'per_page' => 200 ) );
$rmb_sizes    = get_intermediate_image_sizes();
$rmb_sizes[]  = 'full';
?>
<div class="wrap rmb-wrap" id="rmb-app" data-screen="settings">
	<?php
	Admin::header(
		__( 'Settings', 'restaurant-menu-builder' ),
		__( 'Currency, default display options and housekeeping.', 'restaurant-menu-builder' ),
		'settings'
	);
	?>

	<div class="rmb-settings-grid">
		<section class="rmb-panel">
			<h2><?php esc_html_e( 'General', 'restaurant-menu-builder' ); ?></h2>

			<div class="rmb-field">
				<label class="rmb-field-label" for="rmb-currency"><?php esc_html_e( 'Currency', 'restaurant-menu-builder' ); ?></label>
				<select id="rmb-currency" class="rmb-select" data-setting="currency">
					<?php foreach ( Currency::all() as $rmb_code => $rmb_currency ) : ?>
						<option value="<?php echo esc_attr( $rmb_code ); ?>" <?php selected( $rmb_settings['currency'], $rmb_code ); ?>>
							<?php echo esc_html( $rmb_currency['label'] . ' (' . $rmb_currency['symbol'] . ')' ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>

			<div class="rmb-field">
				<label class="rmb-field-label" for="rmb-currency-position"><?php esc_html_e( 'Symbol position', 'restaurant-menu-builder' ); ?></label>
				<select id="rmb-currency-position" class="rmb-select" data-setting="currency_position">
					<option value="before" <?php selected( $rmb_settings['currency_position'], 'before' ); ?>><?php esc_html_e( 'Before the amount', 'restaurant-menu-builder' ); ?></option>
					<option value="after" <?php selected( $rmb_settings['currency_position'], 'after' ); ?>><?php esc_html_e( 'After the amount', 'restaurant-menu-builder' ); ?></option>
				</select>
			</div>

			<div class="rmb-field">
				<label class="rmb-field-label" for="rmb-default-menu"><?php esc_html_e( 'Default menu', 'restaurant-menu-builder' ); ?></label>
				<select id="rmb-default-menu" class="rmb-select" data-setting="default_menu">
					<option value="0"><?php esc_html_e( 'None', 'restaurant-menu-builder' ); ?></option>
					<?php foreach ( $rmb_menus['items'] as $rmb_menu ) : ?>
						<option value="<?php echo esc_attr( (string) $rmb_menu['id'] ); ?>" <?php selected( (int) $rmb_settings['default_menu'], (int) $rmb_menu['id'] ); ?>>
							<?php echo esc_html( $rmb_menu['name'] ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<p class="rmb-field-help"><?php esc_html_e( 'Used when the shortcode is added without an ID.', 'restaurant-menu-builder' ); ?></p>
			</div>

			<div class="rmb-field">
				<label class="rmb-field-label" for="rmb-image-size"><?php esc_html_e( 'Image size', 'restaurant-menu-builder' ); ?></label>
				<select id="rmb-image-size" class="rmb-select" data-setting="image_size">
					<?php foreach ( array_unique( $rmb_sizes ) as $rmb_size ) : ?>
						<option value="<?php echo esc_attr( $rmb_size ); ?>" <?php selected( $rmb_settings['image_size'], $rmb_size ); ?>>
							<?php echo esc_html( $rmb_size ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<p class="rmb-field-help"><?php esc_html_e( 'Photos are served from the media library at this size. Nothing is duplicated on the server.', 'restaurant-menu-builder' ); ?></p>
			</div>

			<label class="rmb-switch">
				<input type="checkbox" data-setting="lazy_loading" <?php checked( Settings::to_bool( $rmb_settings['lazy_loading'] ) ); ?> />
				<span class="rmb-switch-track" aria-hidden="true"></span>
				<span><?php esc_html_e( 'Load images only as they scroll into view', 'restaurant-menu-builder' ); ?></span>
			</label>
		</section>

		<section class="rmb-panel">
			<h2><?php esc_html_e( 'Display defaults', 'restaurant-menu-builder' ); ?></h2>
			<p class="rmb-field-help"><?php esc_html_e( 'New menus start with these options. Each menu can change them later.', 'restaurant-menu-builder' ); ?></p>

			<div class="rmb-field">
				<label class="rmb-field-label" for="rmb-layout"><?php esc_html_e( 'Layout', 'restaurant-menu-builder' ); ?></label>
				<select id="rmb-layout" class="rmb-select" data-setting="layout">
					<?php foreach ( Settings::layouts() as $rmb_key => $rmb_label ) : ?>
						<option value="<?php echo esc_attr( $rmb_key ); ?>" <?php selected( $rmb_settings['layout'], $rmb_key ); ?>>
							<?php echo esc_html( $rmb_label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>

			<?php
			$rmb_flags = array(
				'show_navigation'   => __( 'Show category navigation', 'restaurant-menu-builder' ),
				'show_images'       => __( 'Show images', 'restaurant-menu-builder' ),
				'show_descriptions' => __( 'Show descriptions', 'restaurant-menu-builder' ),
				'show_prices'       => __( 'Show prices', 'restaurant-menu-builder' ),
			);

			foreach ( $rmb_flags as $rmb_flag => $rmb_label ) :
				?>
				<label class="rmb-switch">
					<input type="checkbox" data-setting="<?php echo esc_attr( $rmb_flag ); ?>" <?php checked( Settings::to_bool( $rmb_settings[ $rmb_flag ] ) ); ?> />
					<span class="rmb-switch-track" aria-hidden="true"></span>
					<span><?php echo esc_html( $rmb_label ); ?></span>
				</label>
			<?php endforeach; ?>
		</section>

		<section class="rmb-panel">
			<h2><?php esc_html_e( 'Performance', 'restaurant-menu-builder' ); ?></h2>

			<label class="rmb-switch">
				<input type="checkbox" data-setting="cache_enabled" <?php checked( Settings::to_bool( $rmb_settings['cache_enabled'] ) ); ?> />
				<span class="rmb-switch-track" aria-hidden="true"></span>
				<span><?php esc_html_e( 'Cache rendered menus', 'restaurant-menu-builder' ); ?></span>
			</label>
			<p class="rmb-field-help"><?php esc_html_e( 'The cache clears itself whenever you change a menu, category, item or style.', 'restaurant-menu-builder' ); ?></p>

			<div class="rmb-field">
				<label class="rmb-field-label" for="rmb-cache-duration"><?php esc_html_e( 'Cache duration (hours)', 'restaurant-menu-builder' ); ?></label>
				<input
					type="number"
					id="rmb-cache-duration"
					class="rmb-input rmb-input-small"
					min="1"
					max="720"
					value="<?php echo esc_attr( (string) $rmb_settings['cache_duration'] ); ?>"
					data-setting="cache_duration"
				/>
			</div>

			<button type="button" class="rmb-button rmb-button-secondary" data-action="flush-cache">
				<?php esc_html_e( 'Clear menu cache now', 'restaurant-menu-builder' ); ?>
			</button>
		</section>

		<section class="rmb-panel">
			<h2><?php esc_html_e( 'Advanced', 'restaurant-menu-builder' ); ?></h2>

			<div class="rmb-field">
				<label class="rmb-field-label" for="rmb-custom-css"><?php esc_html_e( 'Custom CSS', 'restaurant-menu-builder' ); ?></label>
				<textarea
					id="rmb-custom-css"
					class="rmb-textarea rmb-code"
					rows="8"
					spellcheck="false"
					data-setting="custom_css"
					placeholder=".rmb-menu .rmb-item-name { letter-spacing: 0.04em; }"
				><?php echo esc_textarea( (string) $rmb_settings['custom_css'] ); ?></textarea>
				<p class="rmb-field-help"><?php esc_html_e( 'Prefix your selectors with .rmb-menu so the rules stay inside the menu.', 'restaurant-menu-builder' ); ?></p>
			</div>

			<label class="rmb-checkbox rmb-checkbox-danger">
				<input type="checkbox" data-setting="delete_on_uninstall" <?php checked( Settings::to_bool( $rmb_settings['delete_on_uninstall'] ) ); ?> />
				<span><?php esc_html_e( 'Delete all menus, categories, items and settings when the plugin is uninstalled', 'restaurant-menu-builder' ); ?></span>
			</label>
			<p class="rmb-field-help"><?php esc_html_e( 'Off by default. Deactivating the plugin never removes data.', 'restaurant-menu-builder' ); ?></p>

			<button type="button" class="rmb-button rmb-button-ghost" data-action="reset-settings">
				<?php esc_html_e( 'Restore default settings', 'restaurant-menu-builder' ); ?>
			</button>
		</section>
	</div>

	<div class="rmb-sticky-actions">
		<button type="button" class="rmb-button rmb-button-primary" data-action="save-settings">
			<?php esc_html_e( 'Save settings', 'restaurant-menu-builder' ); ?>
		</button>
	</div>
</div>
