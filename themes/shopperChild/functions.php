<?php
/**
 * Shopper functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Shopper
 */

/**
 * Assign the shopper version to a var
 */
$shopper_theme              = wp_get_theme( 'shopper' );
$shopper_version = $shopper_theme['Version'];

/**
 * Set the content width based on the theme's design and stylesheet.
 */
if ( ! isset( $content_width ) ) {
	
	$content_width = 980; /* pixels */
}

$shopper = (object) array(
	'version' => $shopper_version,

	/**
	 * Initialize all the things.
	 */
	'main'       => require 'inc/class-shopper.php',
	'customizer' => require 'inc/customizer/class-shopper-customizer.php',
);


require 'inc/shopper-functions.php';
require 'inc/shopper-template-hooks.php';
require 'inc/shopper-template-functions.php';

/**
 * All for WooCommerce functions
 */
if ( shopper_is_woocommerce_activated() ) {
	
	$shopper->woocommerce = require 'inc/woocommerce/class-shopper-woocommerce.php';

	require 'inc/woocommerce/shopper-wc-template-hooks.php';
	require 'inc/woocommerce/shopper-wc-template-functions.php';
}

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
                $validation_errors->add( $key.'_error', __( 'Perfavore inserisci una città.', 'woocommerce' ) );
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


/* Controllo e validazione dei field PIVA e CF nel form di registrazione */
add_action('woocommerce_register_post','antonino_validations', 10, 3 );
function antonino_validations( $username, $email, $validation_errors ){
    foreach ($_POST as $key => $field) :
        /*Validazione partita iva*/
        if( strpos( $key, 'billing_' ) !== false ){
            if($key == 'billing_piva'  ){
            	$piva_without_numbers = preg_replace('/[0-9]+/', '', $field);
            	if( empty($field) || $field->length !== 11  || $piva_without_numbers->length > 0 ){
            			$validation_errors->add( $key.'_error', __( 'Perfavore inserisci una partita iva composta da esattemente 11 cifre.', 'woocommerce' ) );
          		  }
            }
        }
        /*Validazione codice fiscale */
        if( strpos( $key, 'billing_' ) !== false ){
            if($key == 'billing_cf' && $_POST['billing_invoice_type'] == 'professionist_invoice' ){
            	if( empty($field) || $field->length !== 16 ){
            			$validation_errors->add( $key.'_error', __( 'Perfavore inserisci una codice fiscale composto da esattemente 16 cifre.', 'woocommerce' ) );
          		  }
            }
        }
       endforeach;
}