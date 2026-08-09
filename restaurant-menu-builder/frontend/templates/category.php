<?php
/**
 * Category section template.
 *
 * Available variable: $rmb with keys category, items, context, instance, layout.
 *
 * @package RestaurantMenuBuilder
 */

declare( strict_types=1 );

use RestaurantMenuBuilder\Frontend\Renderer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** @var array<string,mixed> $rmb */
$rmb_category = $rmb['category'];
$rmb_items    = $rmb['items'];
$rmb_context  = $rmb['context'];
$rmb_instance = $rmb['instance'];
$rmb_layout   = (string) $rmb['layout'];
$rmb_anchor   = $rmb_instance . '-' . $rmb_category['anchor'];
$rmb_image    = Renderer::image_html( (int) $rmb_category['image_id'], (string) $rmb_category['name'], $rmb_context, 'rmb-category-image' );
?>
<section
	id="<?php echo esc_attr( $rmb_anchor ); ?>"
	class="rmb-section"
	data-rmb-section="<?php echo esc_attr( $rmb_anchor ); ?>"
	aria-labelledby="<?php echo esc_attr( $rmb_anchor . '-title' ); ?>"
>
	<header class="rmb-section-header">
		<?php if ( '' !== $rmb_image ) : ?>
			<div class="rmb-section-media">
				<?php echo $rmb_image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image() output. ?>
			</div>
		<?php endif; ?>

		<h2 class="rmb-section-title" id="<?php echo esc_attr( $rmb_anchor . '-title' ); ?>">
			<?php echo esc_html( $rmb_category['name'] ); ?>
		</h2>

		<?php if ( '' !== (string) $rmb_category['description'] ) : ?>
			<p class="rmb-section-note"><?php echo esc_html( $rmb_category['description'] ); ?></p>
		<?php endif; ?>
	</header>

	<?php if ( empty( $rmb_items ) ) : ?>
		<p class="rmb-section-empty"><?php esc_html_e( 'No items in this category yet.', 'restaurant-menu-builder' ); ?></p>
	<?php else : ?>
		<?php
		echo Renderer::template( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Template escapes its own output.
			'layout-' . $rmb_layout,
			array(
				'items'    => $rmb_items,
				'context'  => $rmb_context,
				'category' => $rmb_category,
			)
		);
		?>
	<?php endif; ?>
</section>
