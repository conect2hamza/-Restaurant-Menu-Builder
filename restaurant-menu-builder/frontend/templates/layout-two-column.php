<?php
/**
 * two-column layout template.
 *
 * Available variable: $rmb with keys items, context, category.
 *
 * @package RestaurantMenuBuilder
 */

declare( strict_types=1 );

use RestaurantMenuBuilder\Frontend\Renderer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** @var array<string,mixed> $rmb */
$rmb_items   = $rmb['items'];
$rmb_context = $rmb['context'];
?>
<ul class="rmb-items rmb-items-two-column">
	<?php
	foreach ( $rmb_items as $rmb_item ) {
		echo Renderer::template( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Template escapes its own output.
			'item',
			array(
				'item'    => $rmb_item,
				'context' => $rmb_context,
				'layout'  => 'two-column',
			)
		);
	}
	?>
</ul>
