<?php


class BackendManager 
{
    public function __construct() 
    {
        add_action( 'woocommerce_product_options_pricing', array( $this, 'get_backend_prices' ) );
        
        add_action( 'woocommerce_process_product_meta', array( $this, 'save_backend_prices' ) );

        add_action( 'admin_enqueue_scripts', [ $this, 'enqueueAssets' ] );
    }

    public function enqueueAssets( $page )
    {
        global  $post, $page ;
        
        if ( ($page == 'post.php' || ($page = 'post-new.php')) && $post && $post->post_type == 'product' ) {
            wp_enqueue_script(
                'woo-tiered-admin-js',
                WOO_TIERED_PLUGIN_URL.'assets/backend/main.js',
                [ 'jquery' ],
                rand(10,100)
            );
            wp_enqueue_style(
                'woo-tiered-admin-css',
                WOO_TIERED_PLUGIN_URL.'assets/backend/style.css',
                null,
                rand(10,100)
            );
        }
    
    }
    public function save_backend_prices( $product_id )
    {
        $data = $_POST;
        
        $amounts = ( isset( $data['woo_tiered_price_quantity'] ) ? (array) $data['woo_tiered_price_quantity'] : array() );
        $show_quantity = ( isset( $data['_woo_tiered_show_quantity'] ) ? $data['_woo_tiered_show_quantity'] : false );
        $prices = ( !empty($data['woo_tiered_price']) ? (array) $data['woo_tiered_price'] : array() );

        $table_title = ( isset( $data['_woo_tiered_table_title'] ) ? $data['_woo_tiered_table_title'] : '' );


        $show_addon  = ( isset( $data['_woo_tiered_show_addon'] ) ? $data['_woo_tiered_show_addon'] : false );
        $addon_title = ( isset( $data['_woo_tiered_addon_title'] ) ? $data['_woo_tiered_addon_title'] : '' );
        $addon_names = ( isset( $data['woo_tiered_addon_name'] ) ? (array) $data['woo_tiered_addon_name'] : array() );
        $addon_prices = ( isset( $data['woo_addon_price'] ) ? (array) $data['woo_addon_price'] : array() );


        $this->save_prices_now( $amounts, $prices, $show_quantity, $table_title, $product_id, $show_addon, $addon_title, $addon_names, $addon_prices );
    }
    
    public function save_prices_now( $amounts, $prices,$show_quantity,$table_title, $product_id, $show_addon, $addon_title, $addon_names, $addon_prices ) {
        $rules = array();

        foreach ( $amounts as $key => $amount ) {
            if ( ! empty( $amount ) && ! empty( $prices[ $key ] ) && ! key_exists( $amount, $rules ) ) {
                $rules[ $amount ] = wc_format_decimal( $prices[ $key ] );
            }
        }
        if($show_quantity==false)
        {
            $rules = array();
        }
        update_post_meta( $product_id, '_woo_tiered_pricing_table', $rules );
        update_post_meta( $product_id, '_woo_tiered_show_quantity', $show_quantity );
        update_post_meta( $product_id, '_woo_tiered_table_title', $table_title );


        $addons = array();

        foreach ( $addon_names as $key => $addon_n ) {
            if ( ! empty( $addon_n ) && ! empty( $addon_prices[ $key ] ) ) 
            {
                
                $addon_n = str_replace(" ", "_", $addon_n);
                if(! key_exists( $addon_n, $addons ))
                {
                    $addons[ $addon_n ] = wc_format_decimal( $addon_prices[ $key ] );    
                }
                
            }
        }
        if($show_addon==false)
        {
            $addons = array();
        }
        update_post_meta( $product_id, '_woo_tiered_addon_prices', $addons );
        update_post_meta( $product_id, '_woo_tiered_show_addon', $show_addon );
        update_post_meta( $product_id, '_woo_tiered_addon_title', $addon_title );


    }
    public function get_backend_prices()
    {
        global  $product_object ;
        
        if ( $product_object instanceof WC_Product ) {
            

            $values_var = array(
                'woo_tiered_pricing_table'      => $this->get_pricing_table( $product_object->get_id() ),
                'woo_tiered_show_quantity'      => $this->want_to_show_pricing_table( $product_object->get_id()),
                'woo_tiered_table_title'      => $this->get_pricing_table_title( $product_object->get_id()),

                'woo_tiered_addon_prices'      => $this->get_addon_prices( $product_object->get_id() ),
                'woo_tiered_show_addon'      => $this->want_to_show_addon( $product_object->get_id()),
                'woo_tiered_addon_title'      => $this->get_addon_title( $product_object->get_id())
            );
            extract($values_var);
            include 'views/admin/add-price-rules.php';
        }
    
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

    public function get_pricing_table( $product_id ) {

        $rules = get_post_meta( $product_id, '_woo_tiered_pricing_table', true );
        $parent_id = $product_id;
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



}


 ?>