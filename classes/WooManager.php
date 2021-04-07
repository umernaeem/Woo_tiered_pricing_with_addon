<?php 

class WooManager {

	



	public function __construct() {
		remove_filter( 'woocommerce_stock_amount', 'intval' );
		add_filter( 'woocommerce_stock_amount', 'floatval' );
	
		add_filter( 'woocommerce_quantity_input_args', array( $this, 'woo_tiered_quantity_input_args' ), 10, 2 );
		
		add_filter( 'wc_get_template', array( $this, 'woo_tiered_quantity_input_template' ), 10, 2 );

		add_filter( 'woocommerce_add_cart_item_data', array( $this, 'woo_tiered_add_cart_item_data' ), 10, 3 );
		add_filter( 'woocommerce_get_item_data', array( $this, 'woo_tiered_get_item_data' ), 10, 2 );

		add_filter( 'woocommerce_add_to_cart_quantity', array( $this, 'woo_tiered_add_to_cart_quantity' ), 10, 2 );

		//add_action( 'woocommerce_add_to_cart', array( $this, 'woo_tiered_add_to_cart' ), 10, 6 );
		
		add_action( 'woocommerce_before_calculate_totals', [ $this, 'calculateTotals' ], 10, 3 );
		
		add_filter( 'woocommerce_cart_item_subtotal', [ $this, 'calculateItemPriceSidebar' ], 10, 2 );
		add_filter( 'woocommerce_cart_item_price', [ $this, 'calculateItemPrice' ], 10, 2 );

		add_action( 'woocommerce_after_calculate_totals', [ $this, 'calculateAfterTotals' ], 10, 3 );
		add_action( 'woocommerce_before_mini_cart_contents', [ $this, 'miniCartSubTotal' ], 10, 3 );


		add_action( 'woocommerce_add_order_item_meta', array( $this, 'woo_tiered_add_order_item_meta' ), 10, 2 );

		
		add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'woocommerce_checkout_create_order_line_item' ), 10, 4 );
		


	}
	public function woo_tiered_add_to_cart_quantity($quantity, $product_id)
	{
		$cart = wc()->cart;
		foreach ( $cart->get_cart() as $actual_cart_item_key => $cart_item ) 
		{
			
			if( $cart_item['product_id'] == $product_id ){
				$cart->remove_cart_item($actual_cart_item_key);
				$cart->calculate_totals();
			}
		}
		return $quantity;
	}
	public function woo_tiered_add_to_cart($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data)
	{
		$cart = wc()->cart;
		foreach ( $cart->get_cart() as $actual_cart_item_key => $cart_item ) 
		{
			
			if($cart_item_key!=$actual_cart_item_key && $cart_item['product_id'] == $product_id ){
				$cart->remove_cart_item($actual_cart_item_key);
				$cart->calculate_totals();
			}
		}
	}

	public function woo_tiered_quantity_input_template( $located, $template_name ) {
		if ( $template_name === 'global/quantity-input.php' ) {
			return WOO_TIERED_PLUGIN_PATH . 'classes/views/frontend/quantity-input.php';
		}

		return $located;
	}
	public function woo_tiered_quantity_input_args( $args, $product ) {
		if ( $product ) {
			if ( $product->is_type( 'variation' ) && $product->get_parent_id() ) {
				$product_id = $product->get_parent_id();
			} else {
				$product_id = $product->get_id();
			}

			$args['product_id'] = $product_id;
			$args['min_value']  = (float)0.5;
			$args['max_value']  = (float)10000000;
			$args['step']       = (float)0.5;

			if ( $args['input_name'] === 'quantity' ) {
				// check if isn't in the cart
				$args['input_value'] = (float)0.5;
			}
		}

		return $args;
	}
   	
	public function woocommerce_checkout_create_order_line_item( &$item, $cart_item_key, $values, $order ) {
		
		$woo_addon_data = '';

		if ( ! empty( $values ) ) {
			if ( array_key_exists( 'woo_addon_data', $values ) ) {
				$woo_addon_data = $values[ 'woo_addon_data' ];
			}
		}

		$product_id = $values['data']->get_id();

		$addon_prices = $this->get_addon_prices($product_id);
		$new_price = 0;
		if ( $woo_addon_data ) {

			foreach ( $woo_addon_data as $field_name => $quantity ) {

				$new_price += $addon_prices[$field_name]*$quantity;
			}
		}
		$item->set_props(
			array(
				'subtotal'     => $values['line_total'] + $new_price,
				'total'        => $values['line_total'] + $new_price,
			)
		);
	}
	
	public function adjustOrderItemPriceSave( $item, $item_id, \WC_Order $order ) {

		if ( $item instanceof WC_Order_Item_Product ) {
			$productId = $item->get_variation_id() ? $item->get_variation_id() : $item->get_product_id();
			$qty       = $item->get_quantity();
			$newPrice  = $this->get_woo_Prices( $qty, $productId );
			$addon_prices = $this->get_addon_prices($productId);




			if ( $newPrice) {
				foreach ( $order->get_items() as $_item ) {
					if ( $item->get_id() === $_item->get_id() && $_item instanceof WC_Order_Item_Product ) {

						$total_addon_to_add = 0;
						/*
						foreach()
						{

						}
						*/
						//$order_item_meta = wc_get_order_item_meta($_item->get_id(),)

						$_item->set_total( $newPrice * $qty );

						$_item->set_subtotal( $newPrice * $qty );
						
						$_item->get_product()->set_price( $newPrice );


						$_item->save();
					}
				}
			}
		}

		return $item;
	}

	public function adjustOrderItemPriceUpdate( \WC_Order_Item $item ) {

		if ( $item instanceof WC_Order_Item_Product ) {

			$productId = $item->get_variation_id() ? $item->get_variation_id() : $item->get_product_id();
			$qty       = $item->get_quantity();

			$newPrice = $this->get_woo_Prices( $qty, $productId );

			$newPrice = $newPrice ? $newPrice : $item->get_product()->get_price();

			$item->get_product()->set_price( $newPrice );
			$item->set_subtotal( $newPrice * $qty );

			if ( ! $this->isLineItemTotalManuallyChanged( $item->get_id() ) ) {
				$item->set_total( $newPrice * $qty );
			}

			$item->save();
		}

		return $item;
	}

	public function isLineItemTotalManuallyChanged( $itemId ) {

		$items = isset( $_REQUEST['items'] ) ? $_REQUEST['items'] : '';

		parse_str( wp_unslash( $items ), $items );

		if ( ! empty( $items ) ) {
			$total    = isset( $items['line_total'][ $itemId ] ) ? ( $items['line_total'][ $itemId ] ) : false;
			$subtotal = isset( $items['line_subtotal'][ $itemId ] ) ? ( $items['line_subtotal'][ $itemId ] ) : false;

			return $total !== $subtotal;
		}

		return false;
	}
	public function woo_tiered_add_order_item_meta( $item_id, $values ) {
		
		$woo_addon_data = '';

		if ( ! empty( $values ) ) {
			if ( array_key_exists( 'woo_addon_data', $values ) ) {
				$woo_addon_data = $values[ 'woo_addon_data' ];
			}
		}

		$product_id = $values['data']->get_id();

		$addon_prices = $this->get_addon_prices($product_id);

		if ( $woo_addon_data ) {

			foreach ( $woo_addon_data as $field_name => $quantity ) {

				$displayable_name = str_replace("_", " ", $field_name);
				$displayable_name .= " (<strong>".wc_price( $addon_prices[$field_name] )." x ".$quantity."</strong>)";

				wc_add_order_item_meta( $item_id, $displayable_name, wc_price($addon_prices[$field_name]*$quantity) );
			}
		}


	}

	public function calculateAfterTotals( $cart ) {
		$subtotal_new = 0;
		if ( ! empty( $cart->cart_contents ) ) {
			foreach ( $cart->cart_contents as $key => $cart_item ) {

				$needPriceRecalculation = true;

				if ( $cart_item['data'] instanceof WC_Product && $needPriceRecalculation ) {
					
					$new_price = $this->get_woo_Prices( $this->getTotalProductCount( $cart_item ), $cart_item['data']->get_id(), 'view', 'cart' );

					// To get real product price
					$product = wc_get_product( $cart_item['data']->get_id() );

					$product_quantity = $this->getTotalProductCount( $cart_item );
					
					$regular_price = wc_get_price_to_display( $product );
					$woo_addon_data = '';

					if ( ! empty( $cart_item ) ) {
						if ( array_key_exists( 'woo_addon_data', $cart_item ) ) {
							$woo_addon_data = $cart_item[ 'woo_addon_data' ];
						}
					}
					$addon_prices = $this->get_addon_prices($cart_item['data']->get_id());
					$total_addon_price = 0;
					if ( $woo_addon_data ) {

						foreach ( $woo_addon_data as $field_name => $quantity ) {

							$total_addon_price += $addon_prices[$field_name]*$quantity;
						}
					}

					if ( $new_price !== false ) {
							$subtotal_new+= ($new_price * $product_quantity) + $total_addon_price;
							//return '<del> ' . wc_price( $regular_price ) . ' </del> <ins> ' . wc_price( $new_price ) . ' </ins>';
					}
					else
					{
						$subtotal_new+= ($regular_price * $product_quantity) + $total_addon_price;
					}
					
					
				}
			}
		}
		

		$subtotal = $subtotal_new;
		$subtotal_tax = $cart->get_subtotal_tax();
		$shipping_total = $cart->get_shipping_total();
		$shipping_tax = $cart->get_shipping_tax();
		$shipping_taxes = $cart->get_shipping_taxes();
		$discount_total = $cart->get_total_discount();
		$discount_tax = $cart->get_discount_tax();
		$cart_contents_total = $cart->get_cart_contents_total();
		$cart_contents_tax = $cart->get_cart_contents_tax();
		$cart_contents_taxes = $cart->get_cart_contents_taxes();
		$fee_total = $cart->get_fee_total();
		$fee_tax = $cart->get_fee_tax();
		$fee_taxes = $cart->get_fee_taxes();
		$total = $subtotal + $subtotal_tax + $shipping_total + $shipping_tax + $discount_total + $discount_tax  + $fee_total + $fee_tax;
		
		
		$default_totals = array(
			'subtotal'            => $subtotal,
			'subtotal_tax'        => $subtotal_tax,
			'shipping_total'      => $shipping_total,
			'shipping_tax'        => $shipping_tax,
			'shipping_taxes'      => $shipping_taxes,
			'discount_total'      => $discount_total,
			'discount_tax'        => $discount_tax,
			'cart_contents_total' => $cart_contents_total,
			'cart_contents_tax'   => $cart_contents_tax,
			'cart_contents_taxes' => $cart_contents_taxes,
			'fee_total'           => $fee_total,
			'fee_tax'             => $fee_tax,
			'fee_taxes'           => $fee_taxes,
			'total'               => $total
		);
		
		$cart->set_totals($default_totals);
		$cart->set_subtotal($subtotal_new);


	}
	public function get_addon_prices( $product_id ) {

        $rules = get_post_meta( $product_id, '_woo_tiered_addon_prices', true );
        $parent_id = $product_id;
        $rules = ! empty( $rules ) ? $rules : array();
        ksort( $rules );
        return $rules;
    }
	public function woo_tiered_get_item_data( $cart_data, $cart_item = null ) {
		$meta_items = array();
		if ( ! empty( $cart_data ) ) {
			$meta_items = $cart_data;
		}
		if ( isset( $cart_item ) ) {
			$woo_addon_data = '';

			if ( ! empty( $cart_item ) ) {
				if ( array_key_exists( 'woo_addon_data', $cart_item ) ) {
					$woo_addon_data = $cart_item[ 'woo_addon_data' ];
				}
			}

			$product = wc_get_product( $cart_item['data']->get_id() );

			$product_id = $cart_item['data']->get_id();
			$product_quantity = $this->getTotalProductCount( $cart_item );
			$regular_price = wc_get_price_to_display( $product );

			$new_price = $this->get_woo_Prices( $this->getTotalProductCount( $cart_item ), $cart_item['data']->get_id(), 'view', 'cart' );
			

			$meta_items[] = array(
				'name'  => esc_attr( 'Total Quantity', 'woo_tiered_pricing_quantity_addon' ),
				'value' => '<strong>'.$product_quantity.'</strong>',
			);
			

			if ( $new_price !== false ) {
					$meta_items[] = array(
						'name'  => esc_attr( 'Price Per Item', 'woo_tiered_pricing_quantity_addon' ),
						'value' => '<strong>'.wc_price($new_price).'</strong>',
					);
					$meta_items[] = array(
						'name'  => '<strong>'. esc_attr( 'Total Quantity Price', 'woo_tiered_pricing_quantity_addon' ).'</strong>',
						'value' => wc_price($new_price * $product_quantity),
					);
			}
			else
			{
					$meta_items[] = array(
						'name'  => esc_attr( 'Price Per Item', 'woo_tiered_pricing_quantity_addon' ),
						'value' => '<strong>'.wc_price($regular_price).'</strong>',
					);
					$meta_items[] = array(
						'name'  => '<strong>'. esc_attr( 'Total Quantity Price', 'woo_tiered_pricing_quantity_addon' ).'</strong>',
						'value' => wc_price($regular_price * $product_quantity),
					);
			}

			

			$addon_prices = $this->get_addon_prices($product_id);

			$cart_item_addon_price = 0;
			if ( $woo_addon_data ) {

				foreach ( $woo_addon_data as $field_name => $quantity ) {
					if($cart_item_addon_price==0)
					{
						$meta_items[] = array(
							'name'  => '<strong>'. esc_attr( 'Addons', 'woo_tiered_pricing_quantity_addon' ).'</strong>',
							'value' => '',
						);
					}
					$displayable_name = str_replace("_", " ", $field_name);
					$displayable_name .= ' ('.wc_price( $addon_prices[$field_name] )." x ".$quantity.')'; 
					$meta_items[] = array(
						'name'  => $displayable_name,
						'value' => wc_price($addon_prices[$field_name]*$quantity),
					);
					$cart_item_addon_price += $addon_prices[$field_name]*$quantity;
				}
			}
			if($cart_item_addon_price!=0)
			{
				$meta_items[] = array(
					'name'  => '<strong>'. esc_attr( 'Addons Total', 'woo_tiered_pricing_quantity_addon' ).'</strong>',
					'value' => wc_price($cart_item_addon_price),
				);
			}
			

		}
		return $meta_items;
	}
	public function woo_tiered_add_cart_item_data( $cart_item, $product_id ) {
		if ( isset( $_POST ) && ! empty( $product_id ) ) {
			$post_data = $_POST;
		} else {
			return false;
		}
		$addon_field_name = '';
		$addon_field_quantity = '';
		foreach ( $post_data as $post_key => $post_value_data ) {
			if ( ! empty( $post_value_data ) ) 
			{
				if($post_key=='addon_field_name')
				{
					$addon_field_name = $post_value_data;
				}
				if($post_key=='addon_field_quantity')
				{
					$addon_field_quantity = $post_value_data;
				}
			}
		}
		$i = 0;
		foreach($addon_field_name as $field_name)
		{	
			if($addon_field_quantity[$i]>0)
			{
				$cart_item['woo_addon_data'][$field_name] = $addon_field_quantity[$i];
			}
			$i++;
		}
		return $cart_item;
	}



	public function calculateItemPriceSidebar( $price, $cart_item ) {

		$needPriceRecalculation = apply_filters( 'tier_pricing_table/cart/need_price_recalculation/item', true, $cart_item );

		if ( $cart_item['data'] instanceof WC_Product && $needPriceRecalculation ) {

			$new_price = $this->get_woo_Prices( $this->getTotalProductCount( $cart_item ), $cart_item['data']->get_id(), 'view', 'cart' );

			// To get real product price
			$product = wc_get_product( $cart_item['data']->get_id() );

			$product_quantity = $this->getTotalProductCount( $cart_item );
			
			$regular_price = wc_get_price_to_display( $product );
			$woo_addon_data = '';

			if ( ! empty( $cart_item ) ) {
				if ( array_key_exists( 'woo_addon_data', $cart_item ) ) {
					$woo_addon_data = $cart_item[ 'woo_addon_data' ];
				}
			}
			$addon_prices = $this->get_addon_prices($cart_item['data']->get_id());
			$total_addon_price = 0;
			if ( $woo_addon_data ) {

				foreach ( $woo_addon_data as $field_name => $quantity ) {

					$total_addon_price += $addon_prices[$field_name]*$quantity;
				}
			}

			if ( $new_price !== false ) {
					return wc_price( ($new_price * $product_quantity) + $total_addon_price);
					//return '<del> ' . wc_price( $regular_price ) . ' </del> <ins> ' . wc_price( $new_price ) . ' </ins>';
			}
			else
			{
				return wc_price(($regular_price * $product_quantity) + $total_addon_price);
			}
		}

		return $price;
	}
	public function get_woo_Prices( $quantity, $product_id, $context = 'view', $place = 'shop' ) {

		$rules = $this->get_pricing_table( $product_id);

		$type = 'fixed';

		if ( 'fixed' === $type ) {
			foreach ( array_reverse( $rules, true ) as $_amount => $price ) {
				if ( $_amount <= $quantity ) {

					$product_price = $price;

					if ( 'view' === $context ) {
						$product = wc_get_product( $product_id );

						$product_price = $this->get_Price_With_Taxes( $product_price, $product, $place );
					}

					break;
				}
			}
		}

		
		$product_price = isset( $product_price ) ? $product_price : false;

		return $product_price;
	}
	public function calculateTotals( $cart ) {

		if ( ! empty( $cart->cart_contents ) ) {
			foreach ( $cart->cart_contents as $key => $cart_item ) {

				$needPriceRecalculation = true;

				if ( $cart_item['data'] instanceof WC_Product && $needPriceRecalculation ) {
					
					$product_id = ! empty( $cart_item['variation_id'] ) ? $cart_item['variation_id'] : $cart_item['product_id'];
					$new_price  = $this->get_woo_Prices( $this->getTotalProductCount( $cart_item ), $product_id, 'calculation', 'cart' );
					
					if ( $new_price !== false ) {
						//$new_price = 150;
						$cart_item['data']->set_price( $new_price );
					}
					
					
				}
			}
		}
	}

	public function get_pricing_table( $product_id ) {

        $rules = get_post_meta( $product_id, '_woo_tiered_pricing_table', true );
        
        $rules = ! empty( $rules ) ? $rules : array();
        ksort( $rules );
        return $rules;
    }
    public function get_Price_With_Taxes( $price, $product, $place = 'shop' ) {

		if ( wc_tax_enabled() ) {

			if ( 'cart' === $place ) {
				$price = 'incl' === get_option( 'woocommerce_tax_display_cart' ) ?

					wc_get_price_including_tax( $product, array(
							'qty'   => 1,
							'price' => $price
						)
					) :

					wc_get_price_excluding_tax( $product, array(
							'qty'   => 1,
							'price' => $price
						)
					);
			} else {
				$price = wc_get_price_to_display( $product, array(
					'price' => $price,
					'qty'   => 1,
				) );
			}
		}

		return $price;
	}

	
	public function getTotalProductCount( $cart_item ) {

		$count = 0;

		foreach ( wc()->cart->cart_contents as $cart_content ) {
			if ( $cart_content['product_id'] == $cart_item['product_id'] ) {
				$count += $cart_content['quantity'];
			}
		}

		return apply_filters( 'tier_pricing_table/cart/total_product_count', $count );
	}


	public function miniCartSubTotal() {
		$cart = wc()->cart;
		$cart->calculate_totals();
	}
	
	
	public function calculateItemPrice( $price, $cart_item ) {

		$needPriceRecalculation = apply_filters( 'tier_pricing_table/cart/need_price_recalculation/item', true, $cart_item );

		if ( $cart_item['data'] instanceof WC_Product && $needPriceRecalculation ) {

			$new_price = $this->get_woo_Prices( $this->getTotalProductCount( $cart_item ), $cart_item['data']->get_id(), 'view', 'cart' );

			// To get real product price
			$product = wc_get_product( $cart_item['data']->get_id() );

			
			$regular_price = wc_get_price_to_display( $product );

			if ( $new_price !== false ) {
				
					return '<del> ' . wc_price( $regular_price ) . ' </del> <ins> ' . wc_price( $new_price ) . ' </ins>';
			}
		}

		return $price;
	}
	
}