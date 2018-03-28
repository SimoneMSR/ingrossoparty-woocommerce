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

// we are going to hook this on priority 31, so that it would display below add to cart button.
add_action( 'woocommerce_single_product_summary', 'woocommerce_total_product_price', 31 );
function woocommerce_total_product_price() {
    global $woocommerce, $product;


    // let's setup our divs
    echo sprintf('<div id="product_total_price" style="margin-bottom:20px;">%s %s</div>',__('Totale prodotto:','woocommerce'),'<span class="price">'.$product->get_price().'</span>');
    //echo sprintf('<div id="cart_total_price" style="margin-bottom:20px;display:none">%s %s</div>',__('Totale carrello:','woocommerce'),'<span class="price">'.$product->get_price().'</span>');
    if( $product->is_type( 'variable' )){
        $variable_product = new WC_Product_Variable_Smart($product);
        $prices = $variable_product->get_boxing_prices_html();

        echo "<table class='ingrossoparty-boxing-table'>";

            foreach ($prices as $boxing_type => $boxing_price) {
                echo "<tr><td>$boxing_type</td><td>boxing_price</td></tr>";
            }

        echo "</table>";
    }
    ?>
        <script>
            jQuery(function($){
                var current_cart_total = <?php echo $woocommerce->cart->cart_contents_total; ?>;
                var currency = '<?php echo get_woocommerce_currency_symbol(); ?>';
                var isProductVariable = '<?php echo $product->is_type( 'simple' ); ?>'
                var price = isProductVariable ? jQuery('.woocommerce-variation-price .woocommerce-Price-amount.amount').text().replace(currency,'') : <?php echo $product->get_price(); ?>;
 
                $('[name=quantity]').change(function(){
                    if ( this.value > 0 ) {
                        var product_total = parseFloat(price * this.value),
                        cart_total = parseFloat(product_total + current_cart_total);
 
                        $('#product_total_price .price').html(product_total.toFixed(2)+currency);
                        //$('#cart_total_price .price').html( currency + cart_total.toFixed(2));
                    } 
                });
            });
        </script>
    <?php
}
?>