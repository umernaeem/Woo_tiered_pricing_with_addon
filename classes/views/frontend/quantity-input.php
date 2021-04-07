<?php
/**
 * Product quantity inputs
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/global/quantity-input.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 4.0.0
 */

defined( 'ABSPATH' ) || exit;

global $product;


?>
<style>
	
	
</style>
    <div class="quantity "
         data-min="<?php echo esc_attr( $min_value ); ?>" data-max="<?php echo esc_attr( $max_value ); ?>"
         data-step="<?php echo esc_attr( $step ); ?>">
		<?php do_action( 'woocommerce_before_quantity_input_field' ); ?>
        <label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>">
			<?php echo esc_attr( $label ); ?>
        </label>
		<div class="quantity_selector_button" data-addon-quantity="0" data-addon-price="<?php echo esc_attr( $current_price ); ?>">
			<div class="negative_button">-</div>
			<div class="selected_value" data-val="0">
				<input
			    type="number"
			    id="<?php echo esc_attr( $input_id ); ?>"
			    class="<?php echo esc_attr( join( ' ', (array) $classes ) ); ?>"
			    step="<?php echo esc_attr( $step ); ?>"
			    min="<?php echo esc_attr( $min_value ); ?>"
			    max="<?php echo esc_attr( 0 < $max_value ? $max_value : '' ); ?>"
			    name="<?php echo esc_attr( $input_name ); ?>"
			    value="<?php echo esc_attr( $input_value ); ?>"
			    title="<?php echo esc_attr_x( 'Qty', 'Product quantity input tooltip', 'wpc-product-quantity' ); ?>"
			    size="4"
			    placeholder="<?php echo esc_attr( $placeholder ); ?>"
			    inputmode="<?php echo esc_attr( $inputmode ); ?>"/>
			</div>
			<div class="positive_button">+</div>
		</div>	
			
		<?php do_action( 'woocommerce_after_quantity_input_field' ); ?>
    </div>
    
	<?php
