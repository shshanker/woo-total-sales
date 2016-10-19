<?php
/*
 * Plugin Name:       Woo Total Sales 
 * Plugin URI:        https://github.com/shshanker/woo-total-sales
 * Description:       This plugin will fetch total sales of specific woocommerce product, and then show it into the shop-archive page and the single product page with the price.
 * Version:           2.0.0
 * Author:            shivashankerbhatta
 * Author URI:        https://github.com/shshanker
 * Text Domain:       woo-total-sales
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.html
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );  // prevent direct access

if ( ! class_exists( 'Woo_Total_Sales' ) ) :
	
	class Woo_Total_Sales {


		/**
		 * Plugin version.
		 *
		 * @var string
		 */
		const VERSION = '2.5.0';

		/**
		 * Instance of this class.
		 *
		 * @var object
		 */
		protected static $instance = null;


		/**
		 * Initialize the plugin.
		 */
		public function __construct(){

				
				/**
				 * Check if WooCommerce is active
				 **/
				if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

			   		include_once 'includes/awts-frontend.php';
			   		include_once 'includes/awts-backend.php';				
					
					add_filter( 'awts_include_order_statuses', array( $this, 'control_awts_include_order_statuses' ), 10, 1 );
					
				} else {
					
					add_action( 'admin_init', array( $this, 'awts_plugin_deactivate') );
					add_action( 'admin_notices', array( $this, 'awts_woocommerce_missing_notice' ) );

				}

			} // end of contructor




		/**
		 * Return an instance of this class.
		 *
		 * @return object A single instance of this class.
		 */
		public static function get_instance() {
			
			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;
		}

		/**
		 * WooCommerce fallback notice.
		 *
		 * @return string
		 */
		public function awts_woocommerce_missing_notice() {
			echo '<div class="error"><p>' . sprintf( __( 'Woocommerce Total Sales says "There must be active install of %s to take a flight!"', 'woo-total-sales' ), '<a href="http://www.woothemes.com/woocommerce/" target="_blank">' . __( 'WooCommerce', 'woo-total-sales' ) . '</a>' ) . '</p></div>';
			if ( isset( $_GET['activate'] ) )
                 unset( $_GET['activate'] );	
		}

		

		public function awts_get_total_sales_per_product($product_id ='') { 
			global $wpdb;

			//$post_status = array( 'wc-completed', 'wc-processing', 'wc-on-hold' );
			$post_status = array('wc-completed', 'wc-processing');	
			 
			$order_items = $wpdb->get_row( $wpdb->prepare(" SELECT SUM( order_item_meta.meta_value ) as _qty, SUM( order_item_meta_3.meta_value ) as _line_total FROM {$wpdb->prefix}woocommerce_order_items as order_items

			LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
			LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta_2 ON order_items.order_item_id = order_item_meta_2.order_item_id
			LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta_3 ON order_items.order_item_id = order_item_meta_3.order_item_id
			LEFT JOIN {$wpdb->posts} AS posts ON order_items.order_id = posts.ID

			WHERE posts.post_type = 'shop_order'			
			AND posts.post_status IN ( '".implode( "','", apply_filters( 'awts_include_order_statuses', $post_status ) )."' )
			AND order_items.order_item_type = 'line_item'
			AND order_item_meta.meta_key = '_qty'
			AND order_item_meta_2.meta_key = '_product_id'
			AND order_item_meta_2.meta_value = %s
			AND order_item_meta_3.meta_key = '_line_total'

			GROUP BY order_item_meta_2.meta_value

			", $product_id));
			
			return $order_items;

			}

		public function awts_get_total_sales_items(){
					global $wpdb;
					$post_status = array('wc-completed', 'wc-processing');	
					
					$order_items = apply_filters( 'woocommerce_reports_sales_overview_order_items', absint( $wpdb->get_var( "
					SELECT SUM( order_item_meta.meta_value )
					FROM {$wpdb->prefix}woocommerce_order_items as order_items
					LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
					LEFT JOIN {$wpdb->posts} AS posts ON order_items.order_id = posts.ID
					
					WHERE 	order_items.order_item_type = 'line_item'
					AND posts.post_status IN ( '".implode( "','", apply_filters( 'awts_include_order_statuses', $post_status ) )."' )
					AND 	order_item_meta.meta_key = '_qty'
				" ) ) );

					return $order_items;

				}


		public function awts_get_total_sales() {

				global $wpdb;

				$post_status = array('wc-completed', 'wc-processing');	

				$order_totals =  $wpdb->get_row( "
				 
				SELECT SUM(meta.meta_value) AS total_sales, COUNT(posts.ID) AS total_orders FROM {$wpdb->posts} AS posts
				 
				LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id					 
				WHERE meta.meta_key = '_order_total'					 
				AND posts.post_type = 'shop_order'					 
				AND posts.post_status IN ( '".implode( "','", apply_filters( 'awts_include_order_statuses', $post_status ))."' )					 
				" );
				 
				return absint( $order_totals->total_sales);
				 
				}

		public function awts_overview_shipping_total(){
					global $wpdb;
					$post_status = array('wc-completed', 'wc-processing');	

					$shipping_total = apply_filters( 'woocommerce_reports_sales_overview_shipping_total', $wpdb->get_var( "
					SELECT SUM(meta.meta_value) AS total_sales FROM {$wpdb->posts} AS posts
				 
					LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id						
				 
					WHERE 	meta.meta_key 		= '_order_shipping'
					AND posts.post_type 	= 'shop_order'						
					AND posts.post_status IN ( '".implode( "','", apply_filters( 'awts_include_order_statuses', $post_status ))."' )
				" ) );

				return $shipping_total;	

				}

		public function awts_overview_discount_total(){
					global $wpdb;
					$post_status = array('wc-completed', 'wc-processing');

					$discount_total = apply_filters( 'woocommerce_reports_sales_overview_discount_total', $wpdb->get_var( "
						SELECT SUM(meta.meta_value) AS total_sales FROM {$wpdb->posts} AS posts
					 
						LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id							
					 
						WHERE 	meta.meta_key 		IN ('_order_discount', '_cart_discount')
						AND posts.post_type 	= 'shop_order'
						AND posts.post_status IN ( '".implode( "','", apply_filters( 'awts_include_order_statuses', $post_status ))."' )
					" ) );

				return $discount_total;

				}

		public function control_awts_include_order_statuses($post_status){
			
			$total_sales_onhold = get_option('woo_total_sales_onhold');

			if( isset($total_sales_onhold) && $total_sales_onhold == 'yes' ){
				$post_status[] = 'wc-on-hold';
			}

			return $post_status;
		}
				

		/**
		 * WooCommerce fallback notice.
		 *
		 * @return string
		 */
		public function awts_plugin_deactivate() {

			deactivate_plugins( plugin_basename( __FILE__ ) );

		}
		

	}// end of the class

add_action( 'plugins_loaded', array( 'Woo_Total_Sales', 'get_instance' ), 0 );

endif;