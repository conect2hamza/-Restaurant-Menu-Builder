<?php
/**
 * Menu wrapper template.
 *
 * Available variable: $rmb with keys menu, context, categories, items, instance.
 *
 * @package RestaurantMenuBuilder
 */

declare( strict_types=1 );

use RestaurantMenuBuilder\Frontend\Renderer;
use RestaurantMenuBuilder\Style;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** @var array<string,mixed> $rmb */
$rmb_menu       = $rmb['menu'];
$rmb_context    = $rmb['context'];
$rmb_categories = $rmb['categories'];
$rmb_items      = $rmb['items'];
$rmb_instance   = $rmb['instance'];
$rmb_layout     = (string) $rmb_context['layout'];
$rmb_inline     = Style::inline_style( $rmb_context['style'] );
$rmb_custom_css = (string) $rmb_context['custom_css'];
?>
<div
	id="<?php echo esc_attr( $rmb_instance ); ?>"
	class="rmb-menu rmb-layout-<?php echo esc_attr( $rmb_layout ); ?>"
	style="<?php echo esc_attr( $rmb_inline ); ?>"
	data-rmb-menu="<?php echo esc_attr( (string) $rmb_menu['id'] ); ?>"
>
	<?php if ( '' !== $rmb_custom_css ) : ?>
		<style>
			<?php
			// Sanitized on save by Settings::sanitize_css(): tags, @import, javascript: and
			// expression() are already stripped, so it cannot break out of this element.
			echo wp_strip_all_tags( $rmb_custom_css ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?>
		</style>
	<?php endif; ?>

	<?php if ( ! empty( $rmb_context['show_navigation'] ) && count( $rmb_categories ) > 1 ) : ?>
		<nav class="rmb-nav" aria-label="<?php esc_attr_e( 'Menu categories', 'restaurant-menu-builder' ); ?>">
			<ul class="rmb-nav-list">
				<?php foreach ( $rmb_categories as $rmb_index => $rmb_category ) : ?>
					<li class="rmb-nav-item">
						<a
							class="rmb-nav-link<?php echo 0 === $rmb_index ? ' is-active' : ''; ?>"
							href="#<?php echo esc_attr( $rmb_instance . '-' . $rmb_category['anchor'] ); ?>"
							data-rmb-target="<?php echo esc_attr( $rmb_instance . '-' . $rmb_category['anchor'] ); ?>"
							<?php echo 0 === $rmb_index ? 'aria-current="true"' : ''; ?>
						>
							<?php
							$rmb_icon = Renderer::icon_html( (string) $rmb_category['icon'] );

							if ( '' !== $rmb_icon ) {
								echo $rmb_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built from a fixed icon registry.
							}
							?>
							<span class="rmb-nav-label"><?php echo esc_html( $rmb_category['name'] ); ?></span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</nav>
	<?php endif; ?>

	<div class="rmb-sections">
		<?php
		foreach ( $rmb_categories as $rmb_category ) {
			$rmb_category_items = $rmb_items[ $rmb_category['id'] ] ?? array();

			echo Renderer::template( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Template escapes its own output.
				'category',
				array(
					'category' => $rmb_category,
					'items'    => $rmb_category_items,
					'context'  => $rmb_context,
					'instance' => $rmb_instance,
					'layout'   => $rmb_layout,
				)
			);
		}
		?>
	</div>
</div>
