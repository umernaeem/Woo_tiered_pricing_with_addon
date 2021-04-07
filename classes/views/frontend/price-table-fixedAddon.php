<?php if ( ! empty( $woo_tiered_show_addon ) ) : ?>
	
    <div class="price-rules-table-wrapper" style="display: block;">
		<?php if ( ! empty( $woo_tiered_addon_title ) ) : ?>
            <h3 style="clear:both; margin: 20px 0;"><?php echo esc_attr( $woo_tiered_addon_title ); ?></h3>
		<?php endif; ?>

		<?php if ( ! empty( $woo_tiered_addon_prices ) ) : ?>	
			<div class="woo_tiered_addon_prices_container">
				
				<?php $iterator = new ArrayIterator( $woo_tiered_addon_prices ); ?>

				<?php while ( $iterator->valid() ) : ?>
					<?php
					$current_price    = $iterator->current();
					$current_name	  = $iterator->key();

					$iterator->next();

					$displayable_name = str_replace("_", " ", $current_name);

					?>
					<div class="woo_tiered_addon_prices_item">
						<div class="woo_tiered_addon_prices_name_price">
							<span><?php echo esc_attr( $displayable_name ); ?> (<strong><?php
								echo wp_kses_post( wc_price( wc_get_price_to_display( wc_get_product( $woo_product_id ),
									array(
										'price' => $current_price,
									) ) ) );
								?></strong>)
							</span>
						</div>
						<div class="woo_tiered_addon_prices_quantity_selector">
							<input type="hidden" name="addon_field_name[]" value="<?php echo esc_attr( $current_name ); ?>">
							<input type="hidden" name="addon_field_quantity[]" class="addon_field_quantity" value="0">
							<div class="quantity_selector_button" data-addon-quantity="0" data-addon-price="<?php echo esc_attr( $current_price ); ?>">
								<div class="negative_button">-</div>
								<div class="selected_value" data-val="0">0</div>
								<div class="positive_button">+</div>
							</div>
						</div>
					</div>


				<?php endwhile; ?>
			</div>
		<?php endif; ?>

		<div class="woo_tiered_addon_total_price" data-total-addon-price="0">
			<strong><?php _e( "Total Addon Price: ", 'woo_tiered_pricing_quantity_addon' ); ?></strong><span><?php
			echo wp_kses_post( wc_price( wc_get_price_to_display( wc_get_product( $woo_product_id ),
				array(
					'price' => 0,
				) ) ) );
			?></span>
		</div>


    </div>

    
<?php endif; ?>


<?php if ( ! empty( $woo_tiered_show_addon ) || ! empty( $woo_tiered_show_quantity ) ) : ?>
	<div class="total_product_price" style="margin-bottom: 20px;">
		<?php _e( "Total Price: ", 'woo_tiered_pricing_quantity_addon' ); ?><span><?php
			echo wp_kses_post( wc_price( wc_get_price_to_display( wc_get_product( $woo_product_id ),
				array(
					'price' => 0,
				) ) ) );
			?></span>
	</div>
	<div class="clear"></div>
<?php endif; ?>

<?php if ( ! empty( $woo_tiered_show_addon ) && ! empty( $woo_tiered_show_quantity ) ) : ?>
	<?php 


	
		
		$html = '<button type="submit" class="button alt">' . esc_html( $product->add_to_cart_text() ) . '</button>';
		

		echo $html;
		 ?>

<?php endif; ?>

	
