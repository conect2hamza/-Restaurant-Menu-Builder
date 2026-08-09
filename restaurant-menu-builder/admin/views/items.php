<?php
/**
 * Standalone items screen.
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
$rmb_menu_id = (int) $rmb['menu_id'];
?>
<div class="wrap rmb-wrap" id="rmb-app" data-screen="items" data-menu="<?php echo esc_attr( (string) $rmb_menu_id ); ?>">
	<?php
	Admin::header(
		__( 'Menu items', 'restaurant-menu-builder' ),
		__( 'Every dish, its photo, description and price.', 'restaurant-menu-builder' ),
		'item'
	);

	if ( 0 === $rmb_menu_id ) {
		Admin::no_menus_state();
		echo '</div>';
		return;
	}
	?>

	<div class="rmb-toolbar">
		<div class="rmb-toolbar-left">
			<?php Admin::menu_picker( $rmb_menu_id, 'rmb-items' ); ?>
			<label class="screen-reader-text" for="rmb-item-search"><?php esc_html_e( 'Search items', 'restaurant-menu-builder' ); ?></label>
			<span class="rmb-search">
				<?php echo Icons::render_ui( 'search', 'rmb-ui-icon', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed icon registry. ?>
				<input
					type="search"
					id="rmb-item-search"
					class="rmb-input"
					data-role="item-search"
					placeholder="<?php esc_attr_e( 'Search items', 'restaurant-menu-builder' ); ?>"
				/>
			</span>
		</div>
		<div class="rmb-toolbar-right">
			<button type="button" class="rmb-button rmb-button-primary" data-action="add-item">
				<?php echo Icons::render_ui( 'plus', 'rmb-ui-icon', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed icon registry. ?>
				<?php esc_html_e( 'Add item', 'restaurant-menu-builder' ); ?>
			</button>
		</div>
	</div>

	<div class="rmb-panel">
		<p class="rmb-hint"><?php esc_html_e( 'Drag an item to reorder it inside its category, or use the arrow buttons.', 'restaurant-menu-builder' ); ?></p>
		<div data-role="category-list" aria-live="polite">
			<div class="rmb-loading"><?php esc_html_e( 'Loading items…', 'restaurant-menu-builder' ); ?></div>
		</div>
	</div>
</div>
