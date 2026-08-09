<?php
/**
 * All menus screen.
 *
 * @package RestaurantMenuBuilder
 */

declare( strict_types=1 );

use RestaurantMenuBuilder\Admin\Admin;
use RestaurantMenuBuilder\Icons;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** @var array<string,mixed> $rmb */
$rmb_open_create = ! empty( $rmb['open_create'] );
?>
<div class="wrap rmb-wrap rmb-ui" id="rmb-app" data-screen="menus" data-open-create="<?php echo $rmb_open_create ? '1' : '0'; ?>">
	<?php
	Admin::header(
		__( 'Restaurant menus', 'restaurant-menu-builder' ),
		__( 'Build a menu, fill it with categories and dishes, then publish it with a shortcode.', 'restaurant-menu-builder' ),
		'menus'
	);
	?>

	<div class="rmb-toolbar">
		<div class="rmb-toolbar-left">
			<label class="screen-reader-text" for="rmb-menu-search"><?php esc_html_e( 'Search menus', 'restaurant-menu-builder' ); ?></label>
			<span class="rmb-search">
				<?php echo Icons::render_ui( 'search', 'rmb-ui-icon', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed icon registry. ?>
				<input
					type="search"
					id="rmb-menu-search"
					class="rmb-input"
					data-role="search"
					placeholder="<?php esc_attr_e( 'Search menus', 'restaurant-menu-builder' ); ?>"
				/>
			</span>
			<select class="rmb-select" data-role="status-filter" aria-label="<?php esc_attr_e( 'Filter by status', 'restaurant-menu-builder' ); ?>">
				<option value=""><?php esc_html_e( 'All statuses', 'restaurant-menu-builder' ); ?></option>
				<option value="active"><?php esc_html_e( 'Active', 'restaurant-menu-builder' ); ?></option>
				<option value="draft"><?php esc_html_e( 'Draft', 'restaurant-menu-builder' ); ?></option>
			</select>
		</div>
		<div class="rmb-toolbar-right">
			<button type="button" class="rmb-button rmb-button-primary" data-action="create-menu">
				<?php echo Icons::render_ui( 'plus', 'rmb-ui-icon', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed icon registry. ?>
				<?php esc_html_e( 'Add new menu', 'restaurant-menu-builder' ); ?>
			</button>
		</div>
	</div>

	<div class="rmb-panel" data-role="menu-list" aria-live="polite">
		<div class="rmb-loading"><?php esc_html_e( 'Loading menus…', 'restaurant-menu-builder' ); ?></div>
	</div>
</div>
