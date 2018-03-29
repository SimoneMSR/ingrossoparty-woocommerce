(function(){
	function enable_update_cart_button(){
		jQuery('button[name="update_cart"]' ).removeProp( 'disabled' );
	}
	setTimeout(enable_update_cart_button,1000);
	jQuery(document).ready(function(){
		jQuery( document.body ).on( 'updated_wc_div', enable_update_cart_button );
	});
})();
