<?php
/**

Plugin Name: Ingrossoparty discount
Plugin URI: https://github.com/SimoneMSR/ingrossoparty-woocommerce/plugins/ingrossoparty-discount
Description: Plugin per mostrare sconti
Version: 0.1
Author: SimoneMSR
Author URI: https://simonemsr.github.io
Fork of a Reigel Gallarde work: http://reigelgallarde.me/programming/show-product-price-times-selected-quantity-on-woocommecre-product-page/

*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_action('wp_enqueue_scripts', 'callback_for_setting_up_scripts');
function callback_for_setting_up_scripts() {
    wp_register_style( 'ingrossoparty', 'assets/css/style.css' );
    wp_enqueue_style( 'ingrossoparty' );
}
?>