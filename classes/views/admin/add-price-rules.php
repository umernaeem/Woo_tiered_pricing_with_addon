<?php if ( ! defined( 'WPINC' ) ) {
	die;
}
/**
 * @var bool $isFree
 */
?>

<script>
    jQuery(document).ready(function ($) {
        $('._woo_tiered_show_quantity').on('click', function () {
            if($(this).is(":checked")){
                $('.show_quantity_table').css("display","block");
            }
            else if($(this).is(":not(:checked)")){
                $('.show_quantity_table').css("display","none");
            }
        });
        $('._woo_tiered_show_addon').on('click', function () {
            if($(this).is(":checked")){
                $('.show_addon_product').css("display","block");
            }
            else if($(this).is(":not(:checked)")){
                $('.show_addon_product').css("display","none");
            }
        });
    });
</script>

<p class="form-field">
    <label for="woo_tiered_show_quantity_simple"><?php _e( "Want to add Addon on this product", 'woo_tiered_pricing_quantity_addon' ); ?></label>
    <input type="checkbox" name="_woo_tiered_show_addon" class="_woo_tiered_show_addon" value="yes" <?php if($woo_tiered_show_addon=='yes'){echo 'checked';} ?> >
    
</p>
<?php 
    
    if($woo_tiered_show_addon=='yes')
    {
        $hide_show_block = 'block';
    }
    else
    {
        $hide_show_block = 'none';
    } 
?>

<div class="show_addon_product" style="display: <?php echo $hide_show_block; ?>">
    <p class="form-field">
        <label for="woo_tiered_show_quantity_simple"><?php _e( "Addon Title", 'woo_tiered_pricing_quantity_addon' ); ?></label>
        <input type="text" name="_woo_tiered_addon_title" value="<?php echo $woo_tiered_addon_title; ?>" >
        
    </p>
    <p class="form-field" data-tiered-price-type-fixed
       data-tiered-price-type>
        <label><?php _e( "Addon for Product <br><b>(Make sure all the addon names are unique)</b>", 'woo_tiered_pricing_quantity_addon' ); ?></label>
        <span data-price-rules-wrapper>
            <?php if ( ! empty( $woo_tiered_addon_prices ) ): ?>
                <?php foreach ( $woo_tiered_addon_prices as $addon_name => $price ): ?>
                    <?php 

                        $addon_name = str_replace("_", " ", $addon_name); 
                    ?>
                    <span data-price-rules-container>
                        <span data-price-rules-input-wrapper>
                            <input type="text" value="<?php echo $addon_name; ?>"
                                   placeholder="<?php _e( 'Addon Name', 'woo_tiered_pricing_quantity_addon' ); ?>"
                                   class="price-quantity-rule price-quantity-rule--simple"
                                   name="woo_tiered_addon_name[]">
                            <input type="text" value="<?php echo wc_format_localized_price( $price ); ?>"
                                   placeholder="<?php _e( 'Price', 'woo_tiered_pricing_quantity_addon' ); ?>"
                                   class="wc_input_price price-quantity-rule--simple" name="woo_addon_price[]">
                        </span>
                        <span class="notice-dismiss remove-price-rule" data-remove-price-rule></span>
                        <br>
                        <br>
                    </span>

                <?php endforeach; ?>
            <?php endif; ?>

            <span data-price-rules-container>
                <span data-price-rules-input-wrapper>
                    <input type="text" placeholder="<?php _e( 'Addon Name', 'woo_tiered_pricing_quantity_addon' ); ?>"
                           class="price-quantity-rule price-quantity-rule--simple" name="woo_tiered_addon_name[]">
                    <input type="text" placeholder="<?php _e( 'Price', 'woo_tiered_pricing_quantity_addon' ); ?>"
                           class="wc_input_price  price-quantity-rule--simple" name="woo_addon_price[]">
                </span>
                <span class="notice-dismiss remove-price-rule" data-remove-price-rule></span>
                <br>
                <br>
            </span>
        <button data-add-new-price-rule class="button"><?php _e( 'Add New Addon', 'woo_tiered_pricing_quantity_addon' ); ?></button>
        </span>
    </p>
</div>




<p class="form-field">
    <label for="woo_tiered_show_quantity_simple"><?php _e( "Show Quantity Table", 'woo_tiered_pricing_quantity_addon' ); ?></label>
    <input type="checkbox" name="_woo_tiered_show_quantity" class="_woo_tiered_show_quantity" value="yes" <?php if($woo_tiered_show_quantity=='yes'){echo 'checked';} ?> >
    
</p>
<?php 
    
    if($woo_tiered_show_quantity=='yes')
    {
        $hide_show_block = 'block';
    }
    else
    {
        $hide_show_block = 'none';
    } 
?>

<div class="show_quantity_table" style="display: <?php echo $hide_show_block; ?>">
    <p class="form-field">
        <label for="woo_tiered_show_quantity_simple"><?php _e( "Quantity Table Title", 'woo_tiered_pricing_quantity_addon' ); ?></label>
        <input type="text" name="_woo_tiered_table_title" value="<?php echo $woo_tiered_table_title; ?>" >
        
    </p>
    <p class="form-field" data-tiered-price-type-fixed
       data-tiered-price-type>
        <label><?php _e( "Quantity Table Prices", 'woo_tiered_pricing_quantity_addon' ); ?></label>
        <span data-price-rules-wrapper>
            <?php if ( ! empty( $woo_tiered_pricing_table ) ): ?>
                <?php foreach ( $woo_tiered_pricing_table as $amount => $price ): ?>
                    <span data-price-rules-container>
                        <span data-price-rules-input-wrapper>
                            <input type="number" value="<?php echo $amount; ?>" min="2"
                                   placeholder="<?php _e( 'Quantity', 'woo_tiered_pricing_quantity_addon' ); ?>"
                                   class="price-quantity-rule price-quantity-rule--simple"
                                   name="woo_tiered_price_quantity[]">
                            <input type="text" value="<?php echo wc_format_localized_price( $price ); ?>"
                                   placeholder="<?php _e( 'Price', 'woo_tiered_pricing_quantity_addon' ); ?>"
                                   class="wc_input_price price-quantity-rule--simple" name="woo_tiered_price[]">
                        </span>
                        <span class="notice-dismiss remove-price-rule" data-remove-price-rule></span>
                        <br>
                        <br>
                    </span>

                <?php endforeach; ?>
            <?php endif; ?>

            <span data-price-rules-container>
                <span data-price-rules-input-wrapper>
                    <input type="number" min="2" placeholder="<?php _e( 'Quantity', 'woo_tiered_pricing_quantity_addon' ); ?>"
                           class="price-quantity-rule price-quantity-rule--simple" name="woo_tiered_price_quantity[]">
                    <input type="text" placeholder="<?php _e( 'Price', 'woo_tiered_pricing_quantity_addon' ); ?>"
                           class="wc_input_price  price-quantity-rule--simple" name="woo_tiered_price[]">
                </span>
                <span class="notice-dismiss remove-price-rule" data-remove-price-rule></span>
                <br>
                <br>
            </span>
        <button data-add-new-price-rule class="button"><?php _e( 'Add New Quantity Row', 'woo_tiered_pricing_quantity_addon' ); ?></button>
        </span>
    </p>
</div>


