<?php
/**
 * Global style screen.
 *
 * @package RestaurantMenuBuilder
 */

declare( strict_types=1 );

use RestaurantMenuBuilder\Admin\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** @var array<string,mixed> $rmb */
$rmb_menu_id = (int) $rmb['menu_id'];
?>
<div class="wrap rmb-wrap rmb-ui rmb-editor" id="rmb-app" data-screen="style" data-menu="<?php echo esc_attr( (string) $rmb_menu_id ); ?>">
	<?php
	Admin::header(
		__( 'Style', 'restaurant-menu-builder' ),
		__( 'These are the defaults for every menu. A single menu can override them in its own Style tab.', 'restaurant-menu-builder' ),
		'style'
	);
	?>

	<div class="rmb-toolbar">
		<div class="rmb-toolbar-left"></div>
		<div class="rmb-toolbar-right">
			<button type="button" class="rmb-button rmb-button-ghost" data-action="reset-global-style">
				<?php esc_html_e( 'Restore defaults', 'restaurant-menu-builder' ); ?>
			</button>
			<button type="button" class="rmb-button rmb-button-primary" data-action="save-global-style">
				<?php esc_html_e( 'Save style', 'restaurant-menu-builder' ); ?>
			</button>
		</div>
	</div>

	<div class="rmb-editor-grid">
		<aside class="rmb-editor-sidebar">
			<div class="rmb-tab-panel is-active">
				<div data-role="style-editor"></div>
			</div>
		</aside>

		<section class="rmb-editor-preview" data-role="preview-pane">
			<div class="rmb-preview-bar">
				<h2 class="rmb-preview-title"><?php esc_html_e( 'Live preview', 'restaurant-menu-builder' ); ?></h2>
				<div class="rmb-preview-sizes" role="group" aria-label="<?php esc_attr_e( 'Preview width', 'restaurant-menu-builder' ); ?>">
					<button type="button" class="rmb-size is-active" data-width="full"><?php esc_html_e( 'Desktop', 'restaurant-menu-builder' ); ?></button>
					<button type="button" class="rmb-size" data-width="768"><?php esc_html_e( 'Tablet', 'restaurant-menu-builder' ); ?></button>
					<button type="button" class="rmb-size" data-width="390"><?php esc_html_e( 'Mobile', 'restaurant-menu-builder' ); ?></button>
				</div>
			</div>
			<?php if ( 0 === $rmb_menu_id ) : ?>
				<p class="rmb-hint"><?php esc_html_e( 'Create a menu to see these colours applied to real content.', 'restaurant-menu-builder' ); ?></p>
			<?php else : ?>
				<p class="rmb-hint"><?php esc_html_e( 'Previewing your first menu with these colours.', 'restaurant-menu-builder' ); ?></p>
			<?php endif; ?>
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
