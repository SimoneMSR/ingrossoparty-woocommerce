<?php

/**

 * Single Product Price

 *

 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/price.php.

 *

 * HOWEVER, on occasion WooCommerce will need to update template files and you

 * (the theme developer) will need to copy the new files to your theme to

 * maintain compatibility. We try to do this as little as possible, but it does

 * happen. When this occurs the version of the template file will be bumped and

 * the readme will list any important changes.

 *

 * @see     https://docs.woocommerce.com/document/template-structure/

 * @author  WooThemes

 * @package WooCommerce/Templates

 * @version 3.0.0

 */



if ( ! defined( 'ABSPATH' ) ) {

	exit; // Exit if accessed directly

}



global $product;



?>



<?php if( $product->is_type( 'single' )){ ?>

	<p class="price"><?php echo $product->get_price_html(); ?></p>

<?php } ?>



<?php if( $product->is_type( 'variable' )){ ?>

	<div class="woocommerce-variation-price under-title"></div>

	<script>
		setTimeout(function(){
		jQuery('form.variations_form').change(function(){
			var cad_price = jQuery('.woocommerce-variation-description p').text();
			var var_price = jQuery('.woocommerce-variation-price .woocommerce-Price-amount.amount').text();
			if( cad_price === "")
				jQuery('.woocommerce-variation-price.under-title').text(var_price);
			else
				jQuery('.woocommerce-variation-price.under-title').text(cad_price);
		});
		},0);
	</script>



<?php } ?>