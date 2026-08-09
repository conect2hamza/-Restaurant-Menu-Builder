<?php
/**
 * Menu editor screen.
 *
 * @package RestaurantMenuBuilder
 */

declare( strict_types=1 );

use RestaurantMenuBuilder\Admin\Admin;
use RestaurantMenuBuilder\Icons;
use RestaurantMenuBuilder\Menu;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** @var array<string,mixed> $rmb */
$rmb_menu_id = (int) $rmb['menu_id'];
$rmb_menu    = Menu::find( $rmb_menu_id );

if ( null === $rmb_menu ) {
	return;
}
?>
<div class="wrap rmb-wrap rmb-ui rmb-editor" id="rmb-app" data-screen="editor" data-menu="<?php echo esc_attr( (string) $rmb_menu_id ); ?>">
	<div class="rmb-editor-bar">
		<div class="rmb-editor-identity">
			<a class="rmb-back" href="<?php echo esc_url( admin_url( 'admin.php?page=' . Admin::MENUS_SLUG ) ); ?>">
				<?php echo Icons::render_ui( 'arrow_left', 'rmb-ui-icon', 15 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed icon registry. ?>
				<?php esc_html_e( 'All menus', 'restaurant-menu-builder' ); ?>
			</a>
			<h1 class="rmb-editor-title" data-role="menu-title"><?php echo esc_html( $rmb_menu['name'] ); ?></h1>
			<span class="rmb-status-chip" data-role="menu-status" data-status="<?php echo esc_attr( $rmb_menu['status'] ); ?>">
				<?php echo esc_html( 'active' === $rmb_menu['status'] ? __( 'Active', 'restaurant-menu-builder' ) : __( 'Draft', 'restaurant-menu-builder' ) ); ?>
			</span>
		</div>
		<div class="rmb-editor-actions">
			<button type="button" class="rmb-button rmb-button-ghost" data-action="copy-shortcode" data-shortcode="<?php echo esc_attr( $rmb_menu['shortcode'] ); ?>">
				<?php echo esc_html( $rmb_menu['shortcode'] ); ?>
			</button>
			<button type="button" class="rmb-button rmb-button-secondary" data-action="toggle-preview" aria-pressed="true">
				<?php esc_html_e( 'Hide preview', 'restaurant-menu-builder' ); ?>
			</button>
			<button type="button" class="rmb-button rmb-button-primary" data-action="save-menu">
				<?php esc_html_e( 'Save changes', 'restaurant-menu-builder' ); ?>
			</button>
		</div>
	</div>

	<div class="rmb-editor-grid">
		<aside class="rmb-editor-sidebar">
			<nav class="rmb-tabs" role="tablist" aria-label="<?php esc_attr_e( 'Menu editor sections', 'restaurant-menu-builder' ); ?>">
				<button type="button" class="rmb-tab is-active" role="tab" aria-selected="true" data-tab="content"><?php esc_html_e( 'Categories & items', 'restaurant-menu-builder' ); ?></button>
				<button type="button" class="rmb-tab" role="tab" aria-selected="false" data-tab="style"><?php esc_html_e( 'Style', 'restaurant-menu-builder' ); ?></button>
				<button type="button" class="rmb-tab" role="tab" aria-selected="false" data-tab="settings"><?php esc_html_e( 'Menu settings', 'restaurant-menu-builder' ); ?></button>
			</nav>

			<div class="rmb-tab-panel is-active" data-panel="content">
				<div class="rmb-panel-head">
					<h2><?php esc_html_e( 'Categories', 'restaurant-menu-builder' ); ?></h2>
					<button type="button" class="rmb-button rmb-button-secondary rmb-button-small" data-action="add-category">
						<?php echo Icons::render_ui( 'plus', 'rmb-ui-icon', 14 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed icon registry. ?>
						<?php esc_html_e( 'Add category', 'restaurant-menu-builder' ); ?>
					</button>
				</div>
				<p class="rmb-hint"><?php esc_html_e( 'Drag a category to reorder it, or use the arrow buttons.', 'restaurant-menu-builder' ); ?></p>
				<div data-role="category-list" aria-live="polite">
					<div class="rmb-loading"><?php esc_html_e( 'Loading categories…', 'restaurant-menu-builder' ); ?></div>
				</div>
			</div>

			<div class="rmb-tab-panel" data-panel="style">
				<div class="rmb-panel-head">
					<h2><?php esc_html_e( 'Style', 'restaurant-menu-builder' ); ?></h2>
					<button type="button" class="rmb-button rmb-button-ghost rmb-button-small" data-action="reset-menu-style">
						<?php esc_html_e( 'Reset', 'restaurant-menu-builder' ); ?>
					</button>
				</div>
				<div data-role="style-editor"></div>
			</div>

			<div class="rmb-tab-panel" data-panel="settings">
				<div class="rmb-panel-head">
					<h2><?php esc_html_e( 'Menu settings', 'restaurant-menu-builder' ); ?></h2>
				</div>
				<div data-role="menu-settings"></div>
			</div>
		</aside>

		<section class="rmb-editor-preview" data-role="preview-pane">
			<div class="rmb-preview-bar">
				<h2 class="rmb-preview-title">
					<?php echo Icons::render_ui( 'eye', 'rmb-ui-icon', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed icon registry. ?>
					<?php esc_html_e( 'Live preview', 'restaurant-menu-builder' ); ?>
				</h2>
				<div class="rmb-preview-sizes" role="group" aria-label="<?php esc_attr_e( 'Preview width', 'restaurant-menu-builder' ); ?>">
					<button type="button" class="rmb-size is-active" data-width="full"><?php esc_html_e( 'Desktop', 'restaurant-menu-builder' ); ?></button>
					<button type="button" class="rmb-size" data-width="768"><?php esc_html_e( 'Tablet', 'restaurant-menu-builder' ); ?></button>
					<button type="button" class="rmb-size" data-width="390"><?php esc_html_e( 'Mobile', 'restaurant-menu-builder' ); ?></button>
				</div>
			</div>
			<p class="rmb-hint"><?php esc_html_e( 'Draft categories and items are included here so you can check them before publishing.', 'restaurant-menu-builder' ); ?></p>
			<div class="rmb-preview-frame-wrap">
				<iframe
					class="rmb-preview-frame"
					data-role="preview-frame"
					title="<?php esc_attr_e( 'Menu preview', 'restaurant-menu-builder' ); ?>"
				></iframe>
			</div>
		</section>
	</div>
</div>
