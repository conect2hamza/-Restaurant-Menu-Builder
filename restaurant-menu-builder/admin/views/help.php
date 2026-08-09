<?php
/**
 * Help screen.
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

$rmb_menus = Menu::all( array( 'per_page' => 20 ) );
?>
<div class="wrap rmb-wrap" id="rmb-app" data-screen="help">
	<?php
	Admin::header(
		__( 'Help', 'restaurant-menu-builder' ),
		__( 'How to publish a menu and what each shortcode option does.', 'restaurant-menu-builder' ),
		'help'
	);
	?>

	<div class="rmb-settings-grid">
		<section class="rmb-panel">
			<h2><?php esc_html_e( 'Publish a menu in four steps', 'restaurant-menu-builder' ); ?></h2>
			<ol class="rmb-steps">
				<li><?php esc_html_e( 'Create a menu under All Menus.', 'restaurant-menu-builder' ); ?></li>
				<li><?php esc_html_e( 'Add categories such as Starters, Mains and Drinks.', 'restaurant-menu-builder' ); ?></li>
				<li><?php esc_html_e( 'Add dishes to each category with a photo, description and price.', 'restaurant-menu-builder' ); ?></li>
				<li><?php esc_html_e( 'Set the menu to Active, copy its shortcode and paste it into a page.', 'restaurant-menu-builder' ); ?></li>
			</ol>
			<p class="rmb-field-help"><?php esc_html_e( 'A draft menu shows nothing to visitors, so you can build it in the open.', 'restaurant-menu-builder' ); ?></p>
		</section>

		<section class="rmb-panel">
			<h2><?php esc_html_e( 'Your shortcodes', 'restaurant-menu-builder' ); ?></h2>
			<?php if ( empty( $rmb_menus['items'] ) ) : ?>
				<p class="rmb-field-help"><?php esc_html_e( 'Shortcodes appear here once you create a menu.', 'restaurant-menu-builder' ); ?></p>
			<?php else : ?>
				<ul class="rmb-shortcode-list">
					<?php foreach ( $rmb_menus['items'] as $rmb_menu ) : ?>
						<li>
							<strong><?php echo esc_html( $rmb_menu['name'] ); ?></strong>
							<code><?php echo esc_html( $rmb_menu['shortcode'] ); ?></code>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</section>

		<section class="rmb-panel">
			<h2><?php esc_html_e( 'Shortcode options', 'restaurant-menu-builder' ); ?></h2>
			<table class="rmb-doc-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Option', 'restaurant-menu-builder' ); ?></th>
						<th><?php esc_html_e( 'What it does', 'restaurant-menu-builder' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr><td><code>id</code></td><td><?php esc_html_e( 'The menu to show. Falls back to the default menu in Settings.', 'restaurant-menu-builder' ); ?></td></tr>
					<tr><td><code>slug</code></td><td><?php esc_html_e( 'Use the menu slug instead of its ID.', 'restaurant-menu-builder' ); ?></td></tr>
					<tr><td><code>layout</code></td><td><?php esc_html_e( 'classic, card or two-column. Overrides the menu setting for this placement only.', 'restaurant-menu-builder' ); ?></td></tr>
					<tr><td><code>navigation</code></td><td><?php esc_html_e( 'yes or no. Show the category bar.', 'restaurant-menu-builder' ); ?></td></tr>
					<tr><td><code>images</code></td><td><?php esc_html_e( 'yes or no. Show photos.', 'restaurant-menu-builder' ); ?></td></tr>
					<tr><td><code>descriptions</code></td><td><?php esc_html_e( 'yes or no. Show dish descriptions.', 'restaurant-menu-builder' ); ?></td></tr>
					<tr><td><code>prices</code></td><td><?php esc_html_e( 'yes or no. Show prices.', 'restaurant-menu-builder' ); ?></td></tr>
				</tbody>
			</table>
			<p class="rmb-field-help">
				<?php esc_html_e( 'Example:', 'restaurant-menu-builder' ); ?>
				<code>[restaurant_menu id="1" layout="two-column" images="no"]</code>
			</p>
		</section>

		<section class="rmb-panel" style="grid-column:1/-1">
			<h2><?php esc_html_e( 'Category icon pack', 'restaurant-menu-builder' ); ?></h2>
			<p class="rmb-field-help">
				<?php
				printf(
					/* translators: %d: number of icons in the pack. */
					esc_html__( '%d line-art icons ship with the plugin. Pick one for a category and it appears in the menu navigation. They are drawn inline as SVG, so they inherit your colours and stay sharp at any size.', 'restaurant-menu-builder' ),
					count( Icons::all() )
				);
				?>
			</p>

			<?php foreach ( Icons::groups() as $rmb_group_key => $rmb_group_label ) : ?>
				<?php
				$rmb_group_icons = array_filter(
					Icons::all(),
					static function ( $icon ) use ( $rmb_group_key ) {
						return ( $icon['group'] ?? '' ) === $rmb_group_key;
					}
				);

				if ( empty( $rmb_group_icons ) ) {
					continue;
				}
				?>
				<p class="rmb-icon-group-label"><?php echo esc_html( $rmb_group_label ); ?></p>
				<div class="rmb-icon-gallery">
					<?php foreach ( $rmb_group_icons as $rmb_icon_key => $rmb_icon ) : ?>
						<div class="rmb-icon-card">
							<?php
							// Built from the fixed icon registry, with path data sanitized on render.
							echo Icons::render( (string) $rmb_icon_key, 'rmb-icon', 26 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							?>
							<span><?php echo esc_html( (string) $rmb_icon['label'] ); ?></span>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endforeach; ?>
		</section>

		<section class="rmb-panel">
			<h2><?php esc_html_e( 'For developers', 'restaurant-menu-builder' ); ?></h2>
			<p class="rmb-field-help"><?php esc_html_e( 'Templates can be overridden from your theme at restaurant-menu-builder/menu.php, category.php, item.php or layout-{name}.php.', 'restaurant-menu-builder' ); ?></p>
			<table class="rmb-doc-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Hook', 'restaurant-menu-builder' ); ?></th>
						<th><?php esc_html_e( 'Purpose', 'restaurant-menu-builder' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr><td><code>rmb_layouts</code></td><td><?php esc_html_e( 'Register another layout.', 'restaurant-menu-builder' ); ?></td></tr>
					<tr><td><code>rmb_currencies</code></td><td><?php esc_html_e( 'Add a currency.', 'restaurant-menu-builder' ); ?></td></tr>
					<tr><td><code>rmb_icons</code></td><td><?php esc_html_e( 'Add a category icon. Supply label, group and a paths array of SVG path data drawn on a 24x24 grid.', 'restaurant-menu-builder' ); ?></td></tr>
					<tr><td><code>rmb_render_context</code></td><td><?php esc_html_e( 'Change display options before a menu renders.', 'restaurant-menu-builder' ); ?></td></tr>
					<tr><td><code>rmb_manage_capability</code></td><td><?php esc_html_e( 'Change who can manage menus.', 'restaurant-menu-builder' ); ?></td></tr>
					<tr><td><code>rmb_needs_frontend_assets</code></td><td><?php esc_html_e( 'Force the menu CSS and JS to load on a template you render yourself.', 'restaurant-menu-builder' ); ?></td></tr>
				</tbody>
			</table>
		</section>
	</div>
</div>
