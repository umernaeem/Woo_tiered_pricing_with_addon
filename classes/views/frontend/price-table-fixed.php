<?php if ( ! defined( 'WPINC' ) ) {
	die;
}

$minimum = 1;
?>
<div class="actual_price" data-actual_price="<?php echo $woo_real_price; ?>"></div>

<?php if ( ! empty( $woo_tiered_show_quantity ) ) : ?>
    <div class="clear"></div>
    <div class="price-rules-table-wrapper price_rule_quantity_div" style="display: block;">
		<?php if ( ! empty( $woo_tiered_table_title ) ) : ?>
            <h3 style="clear:both; margin: 20px 0;"><?php echo esc_attr( $woo_tiered_table_title ); ?></h3>
		<?php endif; ?>

		

        <table class="shop_table price-rules-table"
               data-price-rules-table
               data-product-id="<?php echo esc_attr( $woo_product_id ); ?>"
               data-price-rules="<?php echo esc_attr( htmlspecialchars( json_encode( $woo_tiered_pricing_table ) ) ); ?>"
               data-minimum="<?php echo esc_attr( $minimum ); ?>"
               data-product-name="<?php echo esc_attr( $woo_product_name ); ?>">

			    <thead>
	                <tr>
	                    <th>
	                        <span class="nobr"><?php _e( "Quantity", 'woo_tiered_pricing_quantity_addon' ); ?></span>
	                    </th>
	                    <th>
	                        <span class="nobr"><?php _e( "Price", 'woo_tiered_pricing_quantity_addon' ); ?></span>
	                    </th>
						
	                </tr>
                </thead>

            <tbody>
            <tr data-price-rules-amount="<?php echo esc_attr( $minimum ); ?>"
                data-price-rules-price="
				<?php
			    echo esc_attr( wc_get_price_to_display( wc_get_product( $woo_product_id ), array(
				    'price' => $woo_real_price,
			    ) ) );
			    ?>
				" data-price-rules-row>
                <td>
					<?php if ( 1 >= array_keys( $woo_tiered_pricing_table )[0] - $minimum ) : ?>
                        <span><?php echo esc_attr( number_format_i18n( $minimum ) ); ?></span>
					<?php else : ?>
                        <span><?php echo esc_attr( number_format_i18n( $minimum ) ); ?> - <?php echo esc_attr( number_format_i18n( array_keys( $woo_tiered_pricing_table )[0] - 1 ) ); ?></span>
					<?php endif; ?>
                </td>
                <td>
					<span data-price-rules-formated-price>
						<?php
						echo wp_kses_post( wc_price( wc_get_price_to_display( wc_get_product( $woo_product_id ),
							array(
								'price' => $woo_real_price,
							) ) ) );
						?>
					</span>
                </td>
            </tr>

			<?php $iterator = new ArrayIterator( $woo_tiered_pricing_table ); ?>

			<?php while ( $iterator->valid() ) : ?>
				<?php
				$current_price    = $iterator->current();
				$current_quantity = $iterator->key();

				$iterator->next();

				if ( $iterator->valid() ) {
					$quantity = $current_quantity;

					if ( intval( $iterator->key() - 1 != $current_quantity ) ) {
						$quantity = number_format_i18n( $quantity ) . ' - ' . number_format_i18n( intval( $iterator->key() - 1 ) );
					}
				} else {
					$quantity = number_format_i18n( $current_quantity ) . '+';
				}
				?>
                <tr data-price-rules-amount="<?php echo esc_attr( $current_quantity ); ?>"
                    data-price-rules-price="
					<?php
				    echo esc_attr( wc_get_price_to_display( wc_get_product( $woo_product_id ),
					    array(
						    'price' => $current_price,
					    ) ) );
				    ?>
						" data-price-rules-row>
                    <td>
                        <span><?php echo esc_attr( $quantity ); ?></span>
                    </td>
                    <td>
						<span data-price-rules-formated-price>
							<?php
							echo wp_kses_post( wc_price( wc_get_price_to_display( wc_get_product( $woo_product_id ),
								array(
									'price' => $current_price,
								) ) ) );
							?>
						</span>
                    </td>
                </tr>


			<?php endwhile; ?>

            </tbody>
        </table>

		<div class="woo_tiered_table_new_price_quantity" data-total-quantity-table-price="0">
			<span class="first"></span><strong>(<?php _e( "You Save ", 'woo_tiered_pricing_quantity_addon' ); ?><span class="second"></span>)</strong>
		</div>

    </div>

    <style>
        .price-rule-active td {
            background-color: #ff567d !important;
        }
    </style>
<?php endif; ?>

