<?php
/**
 * Plugin Name: Advanced Bank Transfer
 * Plugin URI: http://example.com
 * Description: Custom payment mthode for woocommerce.
 * Version: 1.0.0
 * Author: Mohit Singh
 * Author URI: http://example.com
 * text-domain: advanced_bank_transfer
 */

defined( 'ABSPATH' ) or die;

/*
* stop plugin if woocommerce plugin not active
 */
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ){
	return;
} 

add_action( 'plugins_loaded', 'init_Advanced_Bank_Transfer' );

/** Create new payement method on pluign activation **/
function init_Advanced_Bank_Transfer() {
    class WC_Gateway_Advanced_Bank_Transfer extends WC_Payment_Gateway {
    	public function __construct() {
            $this->id   = 'advanced_bank_transfer';
            //$this->icon = apply_filters( 'woocommerce_abt_icon', plugins_url('/assets/icon.png', __FILE__ ) );
            $this->has_fields = false;
            $this->method_title = __( 'Advanced Bank Transfer', 'advanced_bank_transfer');
            $this->method_description = __( 'Advanced Bank Transfer payment systems.', 'advanced_bank_transfer');

            $this->title = $this->get_option( 'title' );
            $this->description = $this->get_option( 'description' );
            $this->instructions = $this->get_option( 'instructions', $this->description );

            $this->init_form_fields();
            $this->init_settings();

            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
            
        }

        public function init_form_fields() {
		    $this->form_fields = apply_filters( 'woo_abt_pay_fields', array(
		        'enabled' => array(
		            'title' => __( 'Enable/Disable', 'advanced_bank_transfer'),
		            'type' => 'checkbox',
		            'label' => __( 'Enable or Disable Advanced Bank Transfer', 'advanced_bank_transfer'),
		            'default' => 'no'
		        ),
		        'title' => array(
		            'title' => __( 'Advanced Bank Transfer', 'advanced_bank_transfer'),
		            'type' => 'text',
		            'default' => __( 'Advanced Bank Transfer', 'advanced_bank_transfer'),
		            'desc_tip' => true,
		            'description' => __( 'Add a new title for the Advanced Bank Transfer that customers will see when they are in the checkout page.', 'advanced_bank_transfer')
		        ),
		        'description' => array(
		            'title' => __( 'Advanced Bank Transfer Description', 'advanced_bank_transfer'),
		            'type' => 'textarea',
		            'default' => __( 'Please upload bank payment receipt', 'advanced_bank_transfer'),
		            'desc_tip' => true,
		            'description' => __( 'Add a description for the Advanced Bank Transfer that customers will see when they are in the checkout page.', 'advanced_bank_transfer')
		        ),
		        'instructions' => array(
		            'title' => __( 'Instructions', 'advanced_bank_transfer'),
		            'type' => 'textarea',
		            'default' => __( 'Default instructions', 'advanced_bank_transfer'),
		            'desc_tip' => true,
		            'description' => __( 'Instructions that will be added to the thank you page and odrer email', 'advanced_bank_transfer')
		        )
		    ));
		}

		public function process_payment( $order_id ) {                
            $order = wc_get_order( $order_id );
            if ( $order->get_total() > 0 ) {
            // Mark as on-hold (we're awaiting the payment).
                $order->update_status( apply_filters( 'woocommerce_bacs_process_payment_order_status1', 'on-hold', $order ), __( 'Verify uploaded receipt with bank statemnet', 'woocommerce' ) );
            } else {
                $order->payment_complete();
            }

            // Remove cart.
            WC()->cart->empty_cart();

            // Return thankyou redirect.
            return array(
                'result'   => 'success',
                'redirect' => $this->get_return_url( $order ),
            );
        }

    }

    if( class_exists( 'WC_Payment_Gateway' ) ) {
		require_once plugin_dir_path( __FILE__ ) . '/includes/checkout-description-fields.php';
	}
}

/**
 * Enqueue scripts and styles.
 */
function enqueue_bank_scripts() {
    wp_enqueue_style( 'deals-style',plugin_dir_url( __FILE__) .'assets/css/style.css',false, '1.0.0');
    wp_enqueue_script( 'advance-bank-transfer', plugin_dir_url( __FILE__) .'assets/js/advance_bank.js', array('jquery'), '1.0.0', true );
    wp_localize_script( 'advance-bank-transfer', 'myAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));
}
add_action( 'wp_enqueue_scripts', 'enqueue_bank_scripts' );
?>