<?php
/**
 * Single Product tabs
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Filter tabs and allow third parties to add their own
 *
 * Each tab is an array containing title, callback and priority.
 * @see woocommerce_default_product_tabs()
 */
$tabs = apply_filters( 'woocommerce_product_tabs', array() );

if ( ! empty( $tabs ) ) : ?>

	<div class="wb-woocommerce-tabs wc-tabs-wrapper">
        <ul class="nav nav-tabs wc-tabs" role="tablist">
			<?php foreach ( $tabs as $key => $tab ) : ?>
				<li class="<?php echo esc_attr( $key ); ?>_tab">
					<a role="tab" data-toggle="tab" aria-controls="tab-<?php echo $key ?>" href="#tab-<?php echo esc_attr( $key ); ?>"><?php echo apply_filters( 'woocommerce_product_' . $key . '_tab_title', esc_html( $tab['title'] ), $key ) ?></a>
				</li>
			<?php endforeach; ?>
		</ul>
        <div class="tab-content">
		<?php foreach ( $tabs as $key => $tab ) : ?>
            <div role="tabpanel" class="tab-pane wc-tab" id="tab-<?php echo esc_attr( $key ); ?>">
				<?php call_user_func( $tab['callback'], $key, $tab ) ?>
			</div>
		<?php endforeach; ?>
        </div>
	</div>

<?php endif; ?>
