<?php

class FrontendManager
{
    
    public function __construct()
    {

        add_action( 'woocommerce_before_add_to_cart_button', [ $this, 'displayPricingTable' ], -999 );
        add_action( 'woocommerce_after_add_to_cart_button', [ $this, 'displayPricingTableAddon' ], -999 );
        // Wrap price
        add_action(
            'woocommerce_get_price_html',
            [ $this, 'wrapPrice' ],
            10,
            2
        );
        // Get table for variation
        add_action(
            'wc_ajax_get_price_table',
            [ $this, 'getPriceTableVariation' ],
            10,
            1
        );
        // Enqueue frontend assets
        add_action(
            'wp_enqueue_scripts',
            [ $this, 'enqueueAssets' ],
            10,
            1
        );
        
    }
    
    public function renderSummary()
    {
        global  $post ;
        if ( !$post ) {
            return;
        }
        $product = wc_get_product( $post->ID );
        if ( !$product ) {
            return;
        }
        $type = $this->settings->get( 'summary_type', 'table' );
        $this->fileManager->includeTemplate( 'frontend/summary-' . $type . '.php', array(
            'needHide'   => $product->is_type( 'variable' ),
            'totalLabel' => $this->settings->get( 'summary_total_label', 'Total:' ),
            'eachLabel'  => $this->settings->get( 'summary_each_label', 'Each:' ),
            'title'      => $this->settings->get( 'summary_title', '' ),
        ) );
    }
    
    /**
     *  Display table at frontend
     */
    public function displayPricingTable()
    {
        global  $post ;
        if ( !$post ) {
            return;
        }
        $product = wc_get_product( $post->ID );
        if ( $product ) {
            
            if ( $product->is_type( 'simple' ) ) {
                $this->renderPricingTable( $product->get_id() );
            } elseif ( $product->is_type( 'variable' ) ) {
                echo  '<div data-variation-price-rules-table></div>' ;
            }
        
        }
    }
    
    public function displayPricingTableAddon()
    {
        global  $post ;
        if ( !$post ) {
            return;
        }
        $product = wc_get_product( $post->ID );
        if ( $product ) {
            
            if ( $product->is_type( 'simple' ) ) {
                $this->renderPricingTableAddon( $product->get_id() );
            } elseif ( $product->is_type( 'variable' ) ) {
                echo  '<div data-variation-price-rules-table></div>' ;
            }
        
        }
    }
    

    /**
     * Wrap product price for managing it by JS
     *
     * @param string $price_html
     * @param WC_Product $product
     *
     * @return string
     */
    public function wrapPrice( $price_html, $product )
    {
        if ( is_single() && ($product->is_type( 'simple' ) || $product->is_type( 'variation' )) ) {
            return '<span data-tiered-price-wrapper>' . $price_html . '</span>';
        }
        return $price_html;
    }
    
    /**
     * Render tooltip near product price if selected display type is "tooltip"
     *
     * @param string $price
     * @param WC_Product $_product
     *
     * @return string
     */
    public function renderTooltip( $price, $_product )
    {
        
        if ( is_product() ) {
            $page_product_id = get_queried_object_id();
            
            if ( $_product->is_type( 'variation' ) && $_product->get_parent_id() === $page_product_id || is_product() && $_product->is_type( 'simple' ) && $page_product_id === $_product->get_id() ) {
                $rules = PriceManager::getPriceRules( $_product->get_id() );
                if ( !empty($rules) ) {
                    return $price . $this->fileManager->renderTemplate( 'frontend/tooltip.php', [
                        'color' => $this->settings->get( 'tooltip_color', '#cc99c2' ),
                        'size'  => $this->settings->get( 'tooltip_size', 15 ) . 'px',
                    ] );
                }
            }
        
        }
        
        return $price;
    }
    
    /**
     * Enqueue assets at simple product and variation product page.
     *
     * @global WP_Post $post .
     */
    public function enqueueAssets()
    {
        global  $post ;
        wp_enqueue_style(
                'woo-tiered-front-css',
                WOO_TIERED_PLUGIN_URL.'assets/frontend/main.css',
                null,
                rand(10,10000)
            );
        if ( is_product() ) {
            $product = wc_get_product( $post->ID );
            wp_enqueue_script( 'jquery' );
            wp_enqueue_script( 'jquery-ui-tooltip' );
            
            wp_enqueue_script(
                'woo-tiered-front-js',
                WOO_TIERED_PLUGIN_URL.'assets/frontend/product-tier-pricing-table.js',
                'jquery',
                rand(10,10000)
            );
            
            
            wp_localize_script( 'woo-tiered-front-js', 'tieredPricingTable', [
                'product_type'     => $product->get_type(),
                'is_premium'       => 'no',
                'currency_options' => [
                'currency_symbol'    => get_woocommerce_currency_symbol(),
                'decimal_separator'  => wc_get_price_decimal_separator(),
                'thousand_separator' => wc_get_price_thousand_separator(),
                'decimals'           => wc_get_price_decimals(),
                'price_format'       => get_woocommerce_price_format(),
                'price_suffix'       => $product->get_price_suffix(),
            ],
            ] );
            
        }
        wp_enqueue_script(
            'woo-tiered-cart-js',
            WOO_TIERED_PLUGIN_URL.'assets/frontend/cart.js',
            'jquery',
            rand(10,10000)
        );
        

    
    }
    
    
    public function getPriceTableVariation()
    {
        $product_id = ( isset( $_POST['variation_id'] ) ? $_POST['variation_id'] : false );
        $product = wc_get_product( $product_id );
        if ( $product && $product->is_type( 'variation' ) ) {
            $this->renderPricingTable( $product->get_parent_id(), $product->get_id() );
        }
    }
    public function get_pricing_table( $product_id ) {

        $rules = get_post_meta( $product_id, '_woo_tiered_pricing_table', true );
        
        $rules = ! empty( $rules ) ? $rules : array();
        ksort( $rules );
        return $rules;
    }
    public function want_to_show_pricing_table( $product_id ) {

        $rules = get_post_meta( $product_id, '_woo_tiered_show_quantity', true );
        
        return $rules;
    }
    public function get_pricing_table_title( $product_id ) {

        $rules = get_post_meta( $product_id, '_woo_tiered_table_title', true );
        
        return $rules;
    }
    public function get_addon_prices( $product_id ) {

        $rules = get_post_meta( $product_id, '_woo_tiered_addon_prices', true );
        $parent_id = $product_id;
        $rules = ! empty( $rules ) ? $rules : array();
        ksort( $rules );
        return $rules;
    }
    public function want_to_show_addon( $product_id ) {

        $rules = get_post_meta( $product_id, '_woo_tiered_show_addon', true );
        
        return $rules;
    }
    public function get_addon_title( $product_id ) {

        $rules = get_post_meta( $product_id, '_woo_tiered_addon_title', true );
        
        return $rules;
    }
    public function renderPricingTable( $product_id, $variation_id = null )
    {
        $product = wc_get_product( $product_id );
        $product_id = ( !is_null( $variation_id ) ? $variation_id : $product->get_id() );
        
        if ( !$product || !($product->is_type( 'simple' ) || $product->is_type( 'variable' )) ) {
            return;
        }
        $rules = $this->get_pricing_table( $product_id );
        $real_price = ( !is_null( $variation_id ) ? wc_get_product( $variation_id )->get_price() : $product->get_price() );
        $product_name = ( !is_null( $variation_id ) ? wc_get_product( $variation_id )->get_name() : $product->get_name() );
        
            $values_var = array(
                'woo_tiered_pricing_table'      => $rules,
                'woo_real_price'   => $real_price,
                'woo_product_name' => $product_name,
                'woo_product_id'   => $product_id,
                'woo_tiered_show_quantity'      => $this->want_to_show_pricing_table( $product_id),
                'woo_tiered_table_title'      => $this->get_pricing_table_title( $product_id),

                
                'woo_tiered_addon_prices'      => $this->get_addon_prices( $product_id ),
                'woo_tiered_show_addon'      => $this->want_to_show_addon( $product_id),
                'woo_tiered_addon_title'      => $this->get_addon_title( $product_id)
            );
            extract($values_var);
            include 'views/frontend/price-table-fixed.php';
        
    
    }
    public function renderPricingTableAddon( $product_id, $variation_id = null )
    {
        $product = wc_get_product( $product_id );
        $product_id = ( !is_null( $variation_id ) ? $variation_id : $product->get_id() );
        
        if ( !$product || !($product->is_type( 'simple' ) || $product->is_type( 'variable' )) ) {
            return;
        }
        $rules = $this->get_pricing_table( $product_id );
        $real_price = ( !is_null( $variation_id ) ? wc_get_product( $variation_id )->get_price() : $product->get_price() );
        $product_name = ( !is_null( $variation_id ) ? wc_get_product( $variation_id )->get_name() : $product->get_name() );
        
            $values_var = array(
                'woo_tiered_pricing_table'      => $rules,
                'woo_real_price'   => $real_price,
                'woo_product_name' => $product_name,
                'woo_product_id'   => $product_id,
                'woo_tiered_show_quantity'      => $this->want_to_show_pricing_table( $product_id),
                'woo_tiered_table_title'      => $this->get_pricing_table_title( $product_id),
                
                'woo_tiered_addon_prices'      => $this->get_addon_prices( $product_id ),
                'woo_tiered_show_addon'      => $this->want_to_show_addon( $product_id),
                'woo_tiered_addon_title'      => $this->get_addon_title( $product_id)
            );
            extract($values_var);
            include 'views/frontend/price-table-fixedAddon.php';
        
    
    }

}