<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Smart Variable Product Class.
 *
 * The WooCommerce product class handles individual product data.
 *
 * @version		3.0.0
 * @package		WooCommerce/Classes/Products
 * @category	Class
 * @author 		SimoneMSR
 */
class WC_Product_Variable_Smart extends WC_Product_Variable {


	protected $children = array();

	/**
	 * Array of visible children variation IDs. Determined by children.
	 *
	 * @var array
	 */
	protected $visible_children = array();

	/**
	 * Array of variation attributes IDs. Determined by children.
	 *
	 * @var array
	 */
	protected $variation_attributes = array();

	public function __construct( $product = 0 ) {
		parent::__construct( $product );
		if( is_a( $product, 'WC_Product_Variable_Smart' ) ){
			$this-> $children = $product->$children;
			$this-> $visible_children = $product->$variation_attributes;
		}
	}

	public function get_boxing_prices_html(){
		$variations = $this->get_available_variations();
		$variations_num = count($variations);
		$number_of_boxes = 0;
		$i = 0;
		$found_boxes = [];
		while( $i < $variations_num < && count($found_boxes) < $number_of_boxes){
			$attributes = $variations[$i]->attributes;
			$box_key = array_search( $attributes, 'attribute_confezione' );
			if( $box_key != null && array_key_exists( $found_boxes , $attributes[$box_key])){
				$found_boxes[ $attributes[$box_key] ] = $variation->price_html;
			}
			$i++;
		}
		asort($found_boxes);
		return $found_boxes;
	}
}
