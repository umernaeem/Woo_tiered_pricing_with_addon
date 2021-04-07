<?php
 /*
  * Plugin Name: Woocommerce Tiered Pricing Table with Products Addon.
  * Description: Customization for better Woocommerce
  * Author: Umer Naeem
  * Author URI: https://www.freelancer.com/u/umernaeemucp
  * Version: 1.0
  */

require_once('classes/WooManager.php');

require_once('classes/BackendManager.php');

require_once('classes/FrontendManager.php');


define('WOO_TIERED_PLUGIN_URL', plugin_dir_url(__FILE__));

define( 'WOO_TIERED_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

add_action( 'plugins_loaded', 'Woo_tiered_pricing_with_addon_function', 11 );
function Woo_tiered_pricing_with_addon_function(){
		class Woo_tiered_pricing_with_addon {

		public function __construct() {
			add_action( 'admin_init', [ $this, 'checkRequirePlugins' ] );
			new WooManager();
			if ( is_admin() ) {
				$this->backend_things();
			} else {
				$this->frontend_things();
			}
		}
		public function backend_things()
		{
			new BackendManager();
		}
		public function frontend_things()
		{
			new FrontendManager();
		}
		private function validateRequiredPlugins() {
			$plugins = [];

			if ( ! function_exists( 'is_plugin_active' ) ) {
				include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			}

			/**
			 * Check if WooCommerce is active
			 **/
			if ( ! ( is_plugin_active( 'woocommerce/woocommerce.php' ) || is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ) ) {
				$plugins[] = '<a target="_blank" href="https://wordpress.org/plugins/woocommerce/">WooCommerce</a>';
			}

			return $plugins;
		}

		public function checkRequirePlugins() {
			$message = __( 'The %s plugin requires %s plugin to be active!', 'tier-pricing-table' );

			$plugins = $this->validateRequiredPlugins();

			if ( count( $plugins ) ) {
				foreach ( $plugins as $plugin ) {
					$error = sprintf( $message, 'Woocommerce Tiered Pricing Table with Products Addon', $plugin );
					$dismissible = '';
					$type = 'error';
					add_action('admin_notices', function() use ($error, $type, $dismissible){
						echo "<div class='notice notice-{$type} {$dismissible}'><p>{$error}</p></div>";
					});
				}
			}
		}
	}

	$Woo_tiered_pricing_with_addon = new Woo_tiered_pricing_with_addon();	
}
