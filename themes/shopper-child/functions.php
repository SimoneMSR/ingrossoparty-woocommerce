<?php
/* Funzione per far visionare il form di fatturazione in fase di registrazione */
add_action('woocommerce_register_form_start','zk_add_billing_form_to_registration');
function zk_add_billing_form_to_registration(){
    $checkout = WC()->checkout;
    foreach ( $checkout->get_checkout_fields( 'billing' ) as $key => $field ) :
        if($key!='billing_email')
            woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
    endforeach;
}

/* Funzione per salvare i dati di fatturazione sull'utente registrato */
add_action('woocommerce_created_customer','zk_save_billing_address');
function zk_save_billing_address($user_id){
    $address = $_POST;
    foreach ($address as $key => $field){
        // Only billing fields values
        if( strpos( $key, 'billing_' ) !== false ){
            // Condition to add firstname and last name to user meta table
            if($key == 'billing_first_name' || $key == 'billing_last_name'){
                $new_key = str_replace( 'billing_', '', $key );
                update_user_meta( $user_id, $new_key, $_POST[$key] );
            }
            update_user_meta( $user_id, $key, $_POST[$key] );
        }
    }
}

/* Controllo e validazione dei field compilati nel form di registrazione */
add_action('woocommerce_register_post','zk_validation_billing_address', 10, 3 );
function zk_validation_billing_address( $username, $email, $validation_errors ){
    foreach ($_POST as $key => $field) :
        /*Validazione dei campi richiesti*/
        if( strpos( $key, 'billing_' ) !== false ){
            if($key == 'billing_country' && empty($field) ){
                $validation_errors->add( $key.'_error',  __( 'Perfavore seleziona uno stato.', 'woocommerce' ));
            }
            if($key == 'billing_first_name' && empty($field) ){
                $validation_errors->add( $key.'_error', __( 'Perfavore inserisci un nome.', 'woocommerce' ) );
            }
            if($key == 'billing_last_name' && empty($field) ){
                $validation_errors->add( $key.'_error', __( 'Perfavore inserisci un cognome.', 'woocommerce' ) );
            }
            if($key == 'billing_address_1' && empty($field) ){
                $validation_errors->add( $key.'_error', __( 'Perfavore inserisci una via.', 'woocommerce' ) );
            }
            if($key == 'billing_city' && empty($field) ){
                $validation_errors->add( $key.'_error', __( 'Perfavore inserisci una cittÃ .', 'woocommerce' ) );
            }
            if($key == 'billing_state' && empty($field) ){
                if(count( WC()->countries->get_states($_POST['billing_country']) ) > 0)
                    $validation_errors->add( $key.'_error', __( 'Perfavore inserisci una provincia.', 'woocommerce' ) );
            }
            if($key == 'billing_postcode' && empty($field) ){
                $validation_errors->add( $key.'_error', __( 'Perfavore inserisci un cap.', 'woocommerce' ) );
            }
            /*
            if($key == 'billing_email' && empty($field) ){
                $validation_errors->add( $key.'_error', __( 'Please enter billing email address.', 'woocommerce' ) );
            }
            */
            if($key == 'billing_phone' && empty($field) ){
                $validation_errors->add( $key.'_error', __( 'Perfavore inserisci un numero di telefono.', 'woocommerce' ) );
            }

        }
    endforeach;
}

add_filter( 'woocommerce_billing_fields', 'sv_required_billing_fields' );
function sv_required_billing_fields( $fields ) {
    $fields['billing_phone']['required'] = true;
    $fields['billing_city']['required'] = true;
    $fields['billing_country']['required'] = true;
    $fields['billing_address_1']['required'] = true;
    $fields['billing_piva']['required'] = true;
    return $fields;
}

/* Modifica posizione field form woocommerce*/

add_filter( 'woocommerce_checkout_fields', 'custom_move_checkout_fields' );

function custom_move_checkout_fields( $fields ) {

    /* Posizione field */
    $billing_order = array(
        "billing_first_name", 
        "billing_last_name",
        "billing_invoice_type",
        "billing_company",
        "billing_piva",
        "billing_cf",
        "billing_country",
        "billing_address_1", 
        "billing_address_2",
        "billing_city",
        "billing_state",
        "billing_postcode", 
        "billing_phone",
        "billing_email"
    );

    /* Controllo Field */
    foreach($billing_order as $billing_field) {
        $billing_fields[$billing_field] = $fields["billing"][$billing_field];
    }

    $fields["billing"] = $billing_fields;

    /* Riordino campi di spedizione */
    $shipping_order = array(
        "shipping_first_name", 
        "shipping_last_name", 
        "shipping_company", 
        "shipping_address_1", 
        "shipping_address_2", 
        "shipping_postcode", 
        "shipping_country",
        "shipping_city",
        "shipping_state"
    );

    /* Controllo Field */
    foreach($shipping_order as $shipping_field) {
        $shipping_fields[$shipping_field] = $fields["shipping"][$shipping_field];
    }

    $fields["shipping"] = $shipping_fields;

    return $fields;
}


/* Controllo e validazione dei field PIVA nel form di registrazione */
add_action('woocommerce_register_post','antonino_validations', 10, 3 );
function antonino_validations( $username, $email, $validation_errors ){
    foreach ($_POST as $key => $field) :
        /*Validazione dei campi richiesti*/
        if( strpos( $key, 'billing_' ) !== false ){
            if($key == 'billing_piva'  ){
                $piva_without_numbers = preg_replace('/[0-9]+/', '', $field);
                if( empty($field) || strlen($field) !== 11  || strlen($piva_without_numbers) > 0 ){
                        $validation_errors->add( $key.'_error', __( 'Perfavore inserisci una partita iva composta da esattemente 11 cifre.', 'woocommerce' ) );
                  }
            }
        }
       /*validazione codice fiscale */
       if( strpos( $key, 'billing_' ) !== false ){
            if($key == 'billing_cf' && $_POST['billing_invoice_type'] == 'professionist_invoice' ){
                $cf_without_numbers_and_letter = preg_replace('/[0-9]+/', '', $field);
                $cf_without_numbers_and_letter = preg_replace('/[^a-zA-Z]+/','', $cf_without_numbers_and_letter);
                if( empty($field) || strlen($cf_without_numbers_and_letter) !== 16 ){
                        $validation_errors->add( $key.'_error', __( 'Perfavore inserisci un codice fiscale composto da esattemente 16 cifre.', 'woocommerce' ) );
                  }
            }
        }
       endforeach;
}


function reorder_price(){
    remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
    add_action( 'woocommerce_single_product_summary', 'woocommerce_ingrossoparty_template_single_price', 10 );
}
add_action( 'woocommerce_before_single_product_summary', 'reorder_price' );

if ( ! function_exists( 'woocommerce_ingrossoparty_template_single_price' ) ) {

    /**
     * Output the product price.
     */
    function woocommerce_ingrossoparty_template_single_price() {
        wc_get_template( 'single-product/price.php' );
    }
}

function remove_class_action ($action,$class,$method) {
    global $wp_filter ;
    if (isset($wp_filter[$action])) {
        $len = strlen($method) ;
        foreach ($wp_filter[$action] as $pri => $actions) {
            foreach ($actions as $name => $def) {
                if (substr($name,-$len) == $method) {
                    if (is_array($def['function'])) {
                        if (get_class($def['function'][0]) == $class) {
                error_log("hook found");
                            if (is_object($wp_filter[$action]) && isset($wp_filter[$action]->callbacks)) {
                                unset($wp_filter[$action]->callbacks[$pri][$name]) ;
                            } else {
                                unset($wp_filter[$action][$pri][$name]) ;
                            }
                        }
                    }
                }
            }
        }
    }
}

function loop_columns() {
            return apply_filters( 'shopper_loop_columns', 4 ); // 4 products per row
}


add_action( 'after_setup_theme', function() {
  remove_class_action('loop_shop_columns','Shopper_WooCommerce','loop_columns');
    add_filter('loop_shop_columns','loop_columns' );
} );


$minimum_order_error = 'E\' necessario un importo minimo di Euro %s al fine di ordinare.';
$minimum = 50;

add_action('woocommerce_before_checkout_form','show_minimum_checkout');

function show_minimum_checkout(){
global $minimum_order_error;
global $minimum;
    $total = WC()->cart->get_total('edit');

    if( $total < $minimum){
        wc_add_notice( sprintf( $minimum_order_error , wc_price( $minimum ) ), 'error' );
    }
}

add_action( 'woocommerce_checkout_process', 'wc_minimum_order_amount' );
 
function wc_minimum_order_amount() {
global $minimum_order_error;
global $minimum;
    // Set this variable to specify a minimum order value

    if ( WC()->cart->total < $minimum ) {

        if( is_cart() ) {

            wc_print_notice( sprintf( $minimum_order_error ,  wc_price( $minimum ) ), 'error' );

        } else {

            wc_add_notice( sprintf( $minimum_order_error , wc_price( $minimum ) ), 'error' );

        }
    }

}

function enable_update_cart_button(){
if ( ! wp_script_is( 'jquery', 'done' ) ) {
     wp_enqueue_script( 'jquery' );
   }
    wp_enqueue_script( 'enable_update_cart_button_script','/wp-content/themes/shopper-child/assets/js/custom.js', array(), NULL);

}

add_action( 'wp_enqueue_scripts', 'enable_update_cart_button' );