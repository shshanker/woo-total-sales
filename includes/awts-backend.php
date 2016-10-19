<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


/**
 * Woo Total Sales Backend
 *
 * Allows admin to set WooCommerce Total Sales of specific product.
 *
 * @class   Woo_Total_Sales_backend 
 */


class Woo_Total_Sales_backend extends Woo_Total_Sales{

	/**
	 * Init and hook in the integration.
	 *
	 * @return void
	 */


	public function __construct() {
		$this->id                 = 'Woo_Total_Sales_backend';
		$this->method_title       = __( 'WooCommerce Total Sales Backend', 'woo-total-sales' );
		$this->method_description = __( 'WooCommerce Total Sales Backend', 'woo-total-sales' );

	
		
		/**
		 * Create the section beneath the products tab
		 **/
		add_filter( 'woocommerce_get_sections_products', array( $this, 'awts_add_total_sales_section') );
		
		/**
		 * Add settings to the overview section we created before
		 */
		add_action('woocommerce_admin_field_awts_overview', array($this, 'awts_show_total_sales_overview'), 10, 1);
		
		/**
		 * Add settings to the specific section we created before
		 */
		add_filter( 'woocommerce_get_settings_products', array( $this, 'awts_add_total_sales_settings'), 10, 2 );

		/**
	     * Meta box initialization.
	     */
		add_action( 'add_meta_boxes', array( $this, 'awts_product_total_sales_metabox'  ) );
	}
	/**
     * Adds the meta box.
     */
    public function awts_product_total_sales_metabox() {
        add_meta_box(
            'render_awts_product_total_sales_metabox',
            __( 'Total Sales', 'woocommerce' ),
            array( $this, 'render_awts_product_total_sales_metabox' ),
            'product',
            'side',
            'high'
        );
 
    }

    /**
     * Renders the meta box.
     */
    public function render_awts_product_total_sales_metabox( $post ) {
        // Add nonce for security and authentication.
        //From admin setting
		$singular 	= get_option('woo_total_sales_singular');
		$plural 	= get_option('woo_total_sales_plural');
	
		$order_items = $this->awts_get_total_sales_per_product( $post->ID );
		$items_sold_count = (isset($order_items) ? absint($order_items->_qty) : 0);
		$items_sold_total = (isset($order_items) ? absint($order_items->_line_total) : 0);
		
	    $sold_texts  = ''; 

	  	/*if( $items_sold != 0 ){*/

		    $sold_texts .= '<table class="items-sold" ><tr><td><label for="items-sold-count" class="dashicons dashicons-chart-bar"></label></td><td class="misc-pub-section items-sold-count" >'; 
		    $sold_texts .= '<strong>';
		    $sold_texts .= sprintf( 
		    	esc_html( 
		    		_n( 
				    		(!empty($singular)) ? $singular : '%d item sold', 
				    		(!empty($plural)) ? $plural : '%d items sold', 
				    		$items_sold_count, 
				    		'woo-total-sales'  
				    		) 
			    		),

		    		$items_sold_count );
		    $sold_texts .= '</strong>';
		    $sold_texts .= '</td></tr>';
		    $sold_texts .= '<tr><td><label for="items-sold-count" class="dashicons dashicons-money"></label></td><td class="misc-pub-section items-sold-count">';
		    $sold_texts .= '<strong>';
		    $sold_texts .=  
		    	wc_price( 	$items_sold_total, 
				    		'woo-total-sales' 
				    		
			    		);
		    $sold_texts .= '</strong>';	
		    $sold_texts .= '</td></tr></table>';

		/*}*/
	    
	    echo $sold_texts;

    }


	public function awts_add_total_sales_section( $sections ) {
	
		$sections['awtstotalsales'] = __( 'Total Sales', 'woocommerce' );
		return $sections;
		
	}

	function awts_add_total_sales_settings( $settings, $current_section ) {
	/**
	 * Check the current section is what we want
	 **/
	if ( $current_section == 'awtstotalsales' ) {
		
		$awtstotalsales[] = array( 'name' => __( 'Total Sales Overview', 'woo-total-sales' ), 'type' => 'title', 'desc' =>'', 'id' => 'woo_total_sales_title_overview' );

		$awtstotalsales[] = array( 'type' => 'awts_overview', 'id' => 'woo_total_sales_title_overview_callback' );
		
		$awtstotalsales[] = array( 'type' => 'sectionend', 'id' => 'woo_total_sales_overview_sectionend');

		$awtstotalsales[] = array( 'name' => __( 'Total Sales Setting', 'woo-total-sales' ), 'type' => 'title', 'desc' =>'', 'id' => 'woo_total_sales_title' );
		
		$awtstotalsales[] = array(
			'title'    	=> __( 'Also include On-hold orders', 'woo-total-sales' ),
			'id'       	=> 'woo_total_sales_onhold',
			'type'     	=> 'checkbox',
			'desc_tip'  => __( 'If this option is checked, it also includes On-hold orders in a Total Sales. Otherwise it will count Completed and Processing orders only.', 'woo-total-sales' ),
			'css'       => 'min-width:350px;',
			'default'	=> '',
			'desc'     => __( 'Also Include On-hold order to Total Sales', 'woocommerce' ),					
		);


		$awtstotalsales[] = array(
			'title'    	=> __( 'Singular total sales text', 'woo-total-sales' ),
			'css'      => 'min-width:350px;',
			'id'       	=> 'woo_total_sales_singular',
			'desc'  	=> __( 'Please include %d at where you want to show the total sales number.,  e.g %d item sold', 'woo-total-sales' ),
			'type'     	=> 'text',
			'default'	=> '',
			'desc_tip'	=> true,
			'placeholder' => __( '%d item sold out', 'woo-total-sales' ),
		);

		$awtstotalsales[] = array(
			'title'    	=> __( 'Plural total sales text', 'woo-total-sales' ),
			'css'      => 'min-width:350px;',
			'id'       	=> 'woo_total_sales_plural',
			'desc'  	=> __( 'Please include %d at where you want to show the total sales number., e.g %d items sold', 'woo-total-sales' ),
			'type'     	=> 'text',
			'default'	=> '',
			'desc_tip'	=> true,
			'placeholder' => __( '%d items sold out', 'woo-total-sales' ),
		);

		$awtstotalsales[] = array(
			'title'    	=> __( 'Bar chart icon color', 'woo-total-sales' ),
			'css'      => 'min-width:55px;',
			'id'       	=> 'woo_total_sales_bar_color',
			'desc'  	=> __( 'Select/paste bar chart color', 'woo-total-sales' ),
			'type'     	=> 'color',
			'default'	=> '',
			'desc_tip'	=> true,
			'placeholder' => __( '#666666', 'woo-total-sales' ),
		);

		$awtstotalsales[] = array(
			'title'    	=> __( 'Total sales texts color', 'woo-total-sales' ),
			'css'      => 'min-width:55px;',
			'id'       	=> 'woo_total_sales_texts_color',
			'desc'  	=> __( 'Select/paste total sales texts color', 'woo-total-sales' ),
			'type'     	=> 'color',
			'default'	=> '',
			'desc_tip'	=> true,
			'placeholder' => __( '#47a106', 'woo-total-sales' ),
		);

		$awtstotalsales[] = array(
			'title'    	=> __( 'Visible only on single product page (frontend)', 'woo-total-sales' ),
			'id'       	=> 'woo_total_sales_single_product_only_fe',
			'type'     	=> 'checkbox',
			'desc_tip'  => __( 'If this option is checked, it only visible on single product page on frontend but not on shop page.', 'woo-total-sales' ),
			'css'       => 'min-width:350px;',
			'default'	=> '',
			'desc'     => __( 'Total sales only visible on frontend single product page', 'woocommerce' ),					
		);

		$awtstotalsales[] = array(
			'title'    	=> __( 'Visbile only on backend', 'woo-total-sales' ),
			'id'       	=> 'woo_total_sales_single_product_only_be',
			'type'     	=> 'checkbox',
			'desc_tip'  => __( 'If this option is checked, it will only visible in backend single product admin page. it helps shopmanager to track the sales of the product but will not display to the customer.', 'woo-total-sales' ),
			'css'       => 'min-width:350px;',
			'default'	=> '',
			'desc'     => __( 'Visbile only on backend, track them but do not disclose sales to the customers', 'woocommerce' ),					
		);
		
		

		$awtstotalsales[] = array( 'type' => 'sectionend', 'id' => 'woo_total_sales_sectionend');

		return $awtstotalsales;
	
	/**
	 * If not, return the standard settings
	 **/
	} else {
		return $settings;
	}
}

public function awts_show_total_sales_overview($value){

	$html = '';		
	$html .= '<table class="widefat">';
		$html .= '<thead>';
		$html .= '<tr>';
			$html .= '<th scope="row" class="titledesc">';
				$html .= __('Sold items','woocommerce');;
			$html .= '</th>';
			$html .= '<th scope="row" class="titledesc">';
				$html .= __('Total Sales','woocommerce');
			$html .= '</th>';
			$html .= '<th scope="row" class="titledesc">';
				$html .= __('Total Shipping Costs','woocommerce');
			$html .= '</th>';
			$html .= '<th scope="row" class="titledesc">';
				$html .= __('Total Discount Applied','woocommerce');
			$html .= '</th>';			
		$html .= '</tr>';
		$html .= '</thead>';

		$html .= '<tbody>';
		$html .= '<tr>';
			$html .= '<td scope="row" class="titledesc">';
				$html .= "<label for='awts_get_total_sales_items'>".$this->awts_get_total_sales_items()."</label>";
			$html .= '</td>';
			$html .= '<td scope="row" class="titledesc">';
				$html .= "<label for='awts_get_total_sales'>".wc_price($this->awts_get_total_sales())."</label>";
			$html .= '</td>';
			$html .= '<td scope="row" class="titledesc">';
				$html .= "<label for='awts_overview_shipping_total'>".wc_price($this->awts_overview_shipping_total())."</label>";
			$html .= '</td>';
			$html .= '<td scope="row" class="titledesc">';
				$html .= "<label for='awts_overview_discount_total'>".wc_price($this->awts_overview_discount_total())."</label>";
			$html .= '</td>';
		$html .= '</tr>';
		$html .= '</tbody>';
	$html .= '</table>';
	
	echo $html;
}



	
}

$awts_backend = new Woo_Total_Sales_backend();