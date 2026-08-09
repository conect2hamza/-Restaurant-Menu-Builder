<?php
/**
 * Single item template.
 *
 * Available variable: $rmb with keys item, context, layout.
 *
 * @package RestaurantMenuBuilder
 */

declare( strict_types=1 );

use RestaurantMenuBuilder\Frontend\Renderer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** @var array<string,mixed> $rmb */
$rmb_item    = $rmb['item'];
$rmb_context = $rmb['context'];
$rmb_layout  = (string) ( $rmb['layout'] ?? 'classic' );
$rmb_image   = Renderer::image_html( (int) $rmb_item['image_id'], (string) $rmb_item['name'], $rmb_context, 'rmb-item-image' );
$rmb_price   = Renderer::price_html( $rmb_item, $rmb_context );
$rmb_multi   = 'multiple' === $rmb_item['price_type'] && ! empty( $rmb_item['variations'] );
$rmb_desc    = ! empty( $rmb_context['show_descriptions'] ) ? (string) $rmb_item['description'] : '';
$rmb_badge   = (string) $rmb_item['badge'];
$rmb_classes = array( 'rmb-item' );

if ( '' !== $rmb_image ) {
	$rmb_classes[] = 'has-image';
}

if ( $rmb_multi ) {
	$rmb_classes[] = 'has-variations';
}
?>
<li class="<?php echo esc_attr( implode( ' ', $rmb_classes ) ); ?>">
	<?php if ( '' !== $rmb_image ) : ?>
		<div class="rmb-item-media">
			<?php echo $rmb_image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image() output. ?>
		</div>
	<?php endif; ?>

	<div class="rmb-item-body">
		<div class="rmb-item-head">
			<h3 class="rmb-item-name">
				<?php echo esc_html( $rmb_item['name'] ); ?>
				<?php if ( '' !== $rmb_badge ) : ?>
					<span class="rmb-badge"><?php echo esc_html( $rmb_badge ); ?></span>
				<?php endif; ?>
			</h3>

			<?php if ( ! $rmb_multi && '' !== $rmb_price ) : ?>
				<span class="rmb-leader" aria-hidden="true"></span>
				<?php echo $rmb_price; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in Renderer::price_html(). ?>
			<?php endif; ?>
		</div>

		<?php if ( '' !== $rmb_desc ) : ?>
			<p class="rmb-item-description"><?php echo esc_html( $rmb_desc ); ?></p>
		<?php endif; ?>

		<?php if ( $rmb_multi && '' !== $rmb_price ) : ?>
			<?php echo $rmb_price; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in Renderer::price_html(). ?>
		<?php endif; ?>
	</div>
</li>
