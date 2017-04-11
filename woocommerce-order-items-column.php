<?php
/* Plugin Name: WooCommerce Order Items Column
 * Plugin URI: http://grabania.pl.
 * Description: Displays Order Items on Orders admin page for WooCommerce > 3.0
 * Version: 1.0.1
 * Author: Krzysztof Grabania
 * Author URI: http://grabania.pl
 */

if ( ! class_exists( 'WC_Order_Items_Column' ) ) {
	class WC_Order_Items_Column {
		public function __construct() {
			add_action( 'woocommerce_loaded', array( $this, 'woocommerce_loaded' ) );
		}

		public function woocommerce_loaded() {
			if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '3.0.0', '>=' ) ) {
				add_filter( 'manage_shop_order_posts_columns', array( $this, 'manage_shop_order_posts_columns' ), 20 );
				add_action( 'manage_shop_order_posts_custom_column', array( $this, 'render_shop_order_columns' ), 20 );
			}
		}

		public function manage_shop_order_posts_columns( $columns ) {
			$pos = array_search( 'order_title', array_keys( $columns ) );

			if ( ! $pos ) {
				return $columns;
			}

			$pos += 1;

			return array_slice( $columns, 0, $pos ) +
			array( 'order_items' => __( 'Purchased', 'woocommerce' ) ) +
			array_slice( $columns, $pos );
		}

		public function render_shop_order_columns( $column ) {
			if ( 'order_items' == $column ) {
				global $the_order;

				$items = $the_order->get_items();

				echo '<a href="#" class="show_order_items">' . apply_filters( 'woocommerce_admin_order_item_count', sprintf( _n( '%d item', '%d items', count( $items ), 'woocommerce' ), count( $items ) ), $the_order ) . '</a>';

				if ( count( $items ) > 0 ) {
					echo '<table class="order_items" cellspacing="0">';

					foreach ( $items as $item ) {
						$product        = apply_filters( 'woocommerce_order_item_product', $item->get_product(), $item );
						$item_meta      = new WC_Order_Item_Meta( $item, $product );
						$item_meta_html = $item_meta->display( true, true );
						?>
						<tr class="<?php echo apply_filters( 'woocommerce_admin_order_item_class', '', $item, $the_order ); ?>">
							<td class="qty"><?php echo esc_html( $item->get_quantity() ); ?></td>
							<td class="name">
								<?php if ( $product ): ?>
									<?php echo ( wc_product_sku_enabled() && $product->get_sku() ) ? $product->get_sku() . ' - ' : ''; ?><a href="<?php echo get_edit_post_link( $product->get_id() ); ?>"><?php echo apply_filters( 'woocommerce_order_item_name', $item->get_name(), $item, false ); ?></a>
								<?php else: ?>
									<?php echo apply_filters( 'woocommerce_order_item_name', $item->get_name(), $item, false ); ?>
								<?php endif;?>

								<?php if ( ! empty( $item_meta_html ) ): ?>
									<?php echo wc_help_tip( $item_meta_html ); ?>
								<?php endif;?>
							</td>
						</tr>
					<?php }
					echo '</table>';
				} else {
					echo '&ndash;';
				}
			}
		}
	}

	new WC_Order_Items_Column();
}