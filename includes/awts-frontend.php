<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


/**
 * Woo Total Sales Frontend
 *
 * Allows user to get WooCommerce Total Sales of specific product.
 *
 * @class   Woo_Total_Sales_Frontend 
 */


class Woo_Total_Sales_Frontend extends Woo_Total_Sales{

	/**
	 * Init and hook in the integration.
	 *
	 * @return void
	 */


	public function __construct() {
		$this->id                 = 'Woo_Total_Sales_Frontend';
		$this->method_title       = __( 'WooCommerce Total Sales Frontend', 'woo-total-sales' );
		$this->method_description = __( 'WooCommerce Total Sales Frontend', 'woo-total-sales' );

	
		
		// Scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'awts_scripts' ));
		add_action( 'wp_footer', array( $this, 'awts_footer_style' ));
		
		// Filters
		// Add saved price note
		add_filter( 'woocommerce_get_price_html', array( $this, 'awts_display_total_sales') , 101, 2 );
		
	}

	
	/**
	 * Loading scripts.
	 *
	 * @return void
	 */

	public function awts_scripts(){
		
		// loading plugin custom css file
		wp_register_style( 'awts-style', plugins_url( 'woo-total-sales/assets/css/awts-style.css' ) );
		wp_enqueue_style( 'awts-style' );
		
	
	} // end of awts_scripts



	

	/**
	 * Loading  functionality to user to get WooCommerce Total Sales information of the specific product.
	 *
	 * @return void
	 */


	public function awts_display_total_sales( $price='', $product='' ){  

		//From admin setting
		$singular 	= get_option('woo_total_sales_singular');
		$plural 	= get_option('woo_total_sales_plural');
	
		$items_sold = $this->awts_get_total_sales_per_product( $product->id );
		$items_sold = (isset($items_sold) ? absint($items_sold->_qty) : 0);

	    $price_texts  = ''; 
	    $price_texts  .= $price; 

	  	if( $items_sold != 0 ){

		    $price_texts .= '<div class="items-sold" ><span class="items-sold-texts" >'; 
		    $price_texts .= sprintf( 

		    	esc_html( 
		    		_n( 
				    		(!empty($singular)) ? $singular : '%d item sold', 
				    		(!empty($plural)) ? $plural : '%d items sold', 
				    		$items_sold, 
				    		'woo-total-sales'  
				    		) 
			    		),

		    		$items_sold );
		    $price_texts .= '</span></div>';

		}
	    
	    return $price_texts;
	}



	
	/**
	 * Loading footer css.
	 *
	 * @return void
	 */
	public function awts_footer_style(){

			$barcolor 	= get_option('woo_total_sales_bar_color');
			$textcolor 	= get_option('woo_total_sales_texts_color');
			
			// check if bar-chart or texts color to start 'style' tag.
			if( !empty($barcolor) || !empty($textcolor) ){
			    	echo '<style type="text/css">';
			    }

			// check if bar-chart color.
			if(!empty($barcolor)){ echo '.items-sold span:before{color:'.$barcolor.'}'; }

			// check if total sales color.
			if(!empty($barcolor)){ echo '.items-sold span{color:'.$textcolor.'}'; }

			// check if bar-chart or texts color to end 'style' tag.
		    if( !empty($barcolor) || !empty($textcolor) ){
		    	echo '</style>';
		    }


		
	}

}

$awts_frontend = new Woo_Total_Sales_Frontend();