<?php
/**
 * Dashboard overview screen.
 *
 * Every figure on this screen is queried live, so nothing here can drift away
 * from what is actually stored.
 *
 * @package RestaurantMenuBuilder
 */

declare( strict_types=1 );

use RestaurantMenuBuilder\Admin\Admin;
use RestaurantMenuBuilder\Category;
use RestaurantMenuBuilder\Icons;
use RestaurantMenuBuilder\Item;
use RestaurantMenuBuilder\Menu;
use RestaurantMenuBuilder\Settings;
use RestaurantMenuBuilder\Shortcode;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rmb_recent = Menu::all( array( 'per_page' => 5 ) );
$rmb_ids    = wp_list_pluck( $rmb_recent['items'], 'id' );

$rmb_category_counts = Category::counts_for_menus( $rmb_ids );
$rmb_item_counts     = Item::counts_for_menus( $rmb_ids );

$rmb_stats = array(
	array(
		'label' => __( 'Total menus', 'restaurant-menu-builder' ),
		'value' => Menu::count(),
		'icon'  => 'menus',
		'note'  => __( 'Across every status', 'restaurant-menu-builder' ),
		'tone'  => 'brand',
	),
	array(
		'label' => __( 'Total categories', 'restaurant-menu-builder' ),
		'value' => Category::count_all(),
		'icon'  => 'category',
		'note'  => __( 'Sections diners can jump to', 'restaurant-menu-builder' ),
		'tone'  => 'brand',
	),
	array(
		'label' => __( 'Total items', 'restaurant-menu-builder' ),
		'value' => Item::count_all(),
		'icon'  => 'item',
		'note'  => __( 'Dishes and drinks on the menus', 'restaurant-menu-builder' ),
		'tone'  => 'brand',
	),
	array(
		'label' => __( 'Shortcode in use', 'restaurant-menu-builder' ),
		'value' => Shortcode::usage_count(),
		'icon'  => 'code',
		'note'  => __( 'Posts and pages embedding a menu', 'restaurant-menu-builder' ),
		'tone'  => 'accent',
	),
);

// The shortcode card shows the default menu when one is set, otherwise the most
// recently updated menu, so it always points at something real.
$rmb_featured   = null;
$rmb_default_id = absint( Settings::get( 'default_menu', 0 ) );

if ( $rmb_default_id > 0 ) {
	$rmb_featured = Menu::find( $rmb_default_id );
}

if ( null === $rmb_featured && ! empty( $rmb_recent['items'] ) ) {
	$rmb_featured = $rmb_recent['items'][0];
}
?>
<div class="wrap rmb-wrap rmb-ui" id="rmb-app" data-screen="dashboard">
	<?php
	Admin::header(
		__( 'Restaurant Menu Builder', 'restaurant-menu-builder' ),
		__( 'An overview of your menus, and the quickest way back into the work.', 'restaurant-menu-builder' ),
		'dashboard'
	);
	?>

	<div class="rmb-stats">
		<?php foreach ( $rmb_stats as $rmb_stat ) : ?>
			<div class="rmb-stat" data-tone="<?php echo esc_attr( $rmb_stat['tone'] ); ?>">
				<p class="rmb-stat-head">
					<span class="rmb-stat-icon">
						<?php
						// Built from the fixed interface icon registry.
						echo Icons::render_ui( (string) $rmb_stat['icon'], 'rmb-ui-icon', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						?>
					</span>
					<?php echo esc_html( $rmb_stat['label'] ); ?>
				</p>
				<p class="rmb-stat-value"><?php echo esc_html( number_format_i18n( (int) $rmb_stat['value'] ) ); ?></p>
				<p class="rmb-stat-note"><?php echo esc_html( $rmb_stat['note'] ); ?></p>
			</div>
		<?php endforeach; ?>
	</div>

	<div class="rmb-dashboard-grid">
		<section class="rmb-panel">
			<div class="rmb-panel-head">
				<h2><?php esc_html_e( 'Recently updated menus', 'restaurant-menu-builder' ); ?></h2>
				<a class="rmb-button rmb-button-ghost rmb-button-small" href="<?php echo esc_url( admin_url( 'admin.php?page=' . Admin::MENUS_SLUG ) ); ?>">
					<?php esc_html_e( 'View all', 'restaurant-menu-builder' ); ?>
				</a>
			</div>

			<?php if ( empty( $rmb_recent['items'] ) ) : ?>
				<p class="rmb-hint"><?php esc_html_e( 'No menus yet. Create your first one to get started.', 'restaurant-menu-builder' ); ?></p>
				<button type="button" class="rmb-button rmb-button-primary" data-action="create-menu">
					<?php esc_html_e( 'Create your first menu', 'restaurant-menu-builder' ); ?>
				</button>
			<?php else : ?>
				<ul class="rmb-recent">
					<?php foreach ( $rmb_recent['items'] as $rmb_menu ) : ?>
						<li>
							<a class="rmb-recent-name" href="<?php echo esc_url( Admin::editor_url( (int) $rmb_menu['id'] ) ); ?>">
								<?php echo esc_html( $rmb_menu['name'] ); ?>
								<span class="rmb-status-chip" data-status="<?php echo esc_attr( $rmb_menu['status'] ); ?>">
									<?php echo esc_html( 'active' === $rmb_menu['status'] ? __( 'Active', 'restaurant-menu-builder' ) : __( 'Draft', 'restaurant-menu-builder' ) ); ?>
								</span>
							</a>
							<div class="rmb-recent-meta">
								<?php
								printf(
									/* translators: 1: category count, 2: item count, 3: human readable time difference. */
									esc_html__( '%1$d categories · %2$d items · updated %3$s ago', 'restaurant-menu-builder' ),
									(int) ( $rmb_category_counts[ (int) $rmb_menu['id'] ] ?? 0 ),
									(int) ( $rmb_item_counts[ (int) $rmb_menu['id'] ] ?? 0 ),
									// Timestamps are stored in site local time, and so is current_time( 'timestamp' ).
									esc_html( human_time_diff( (int) strtotime( (string) $rmb_menu['updated_at'] ), (int) current_time( 'timestamp' ) ) )
								);
								?>
							</div>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</section>

		<section class="rmb-panel">
			<h2><?php esc_html_e( 'Quick actions', 'restaurant-menu-builder' ); ?></h2>
			<div class="rmb-quick-actions">
				<button type="button" class="rmb-button rmb-button-primary" data-action="create-menu">
					<?php esc_html_e( 'Create new menu', 'restaurant-menu-builder' ); ?>
				</button>
				<a class="rmb-button" href="<?php echo esc_url( admin_url( 'admin.php?page=rmb-categories' ) ); ?>">
					<?php esc_html_e( 'Manage categories', 'restaurant-menu-builder' ); ?>
				</a>
				<a class="rmb-button" href="<?php echo esc_url( admin_url( 'admin.php?page=rmb-items' ) ); ?>">
					<?php esc_html_e( 'Manage items', 'restaurant-menu-builder' ); ?>
				</a>
				<a class="rmb-button" href="<?php echo esc_url( admin_url( 'admin.php?page=rmb-style' ) ); ?>">
					<?php esc_html_e( 'Open the style editor', 'restaurant-menu-builder' ); ?>
				</a>
			</div>
		</section>

		<section class="rmb-panel">
			<h2><?php esc_html_e( 'Publish a menu', 'restaurant-menu-builder' ); ?></h2>

			<?php if ( null === $rmb_featured ) : ?>
				<p class="rmb-hint"><?php esc_html_e( 'Once you have a menu, its shortcode appears here ready to copy.', 'restaurant-menu-builder' ); ?></p>
			<?php else : ?>
				<p class="rmb-hint">
					<?php
					printf(
						/* translators: %s: menu name. */
						esc_html__( 'Paste this anywhere to show %s.', 'restaurant-menu-builder' ),
						'<strong>' . esc_html( $rmb_featured['name'] ) . '</strong>'
					);
					?>
				</p>
				<div class="rmb-shortcode-block">
					<button
						type="button"
						class="rmb-shortcode"
						data-action="copy-shortcode"
						data-shortcode="<?php echo esc_attr( $rmb_featured['shortcode'] ); ?>"
						title="<?php esc_attr_e( 'Copy shortcode', 'restaurant-menu-builder' ); ?>"
					><?php echo esc_html( $rmb_featured['shortcode'] ); ?></button>
				</div>
				<p class="rmb-field-help">
					<?php esc_html_e( 'It works in the block editor, the classic editor, Elementor and theme widgets.', 'restaurant-menu-builder' ); ?>
				</p>
			<?php endif; ?>

			<p style="margin-bottom:0">
				<a class="rmb-button rmb-button-ghost rmb-button-small" href="<?php echo esc_url( admin_url( 'admin.php?page=rmb-help' ) ); ?>">
					<?php esc_html_e( 'Read the shortcode guide', 'restaurant-menu-builder' ); ?>
				</a>
			</p>
		</section>
	</div>
</div>
