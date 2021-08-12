<?php
/*
Add image upload extra filed in payment method on checkout page
 */
add_filter( 'woocommerce_gateway_description', 'advance_bank_description_fields', 20, 2 );
function advance_bank_description_fields( $description, $payment_id ) {

    if ( 'advanced_bank_transfer' !== $payment_id ) {
        return $description;
    }
    
    ob_start();
    echo '<div style="display: block; width:300px; height:auto;">';
    echo '<div id="bank_receipt_progress"></div><div id="bank_receipt_preview"></div>';
    echo '<input type="file" required name="bank_receipt" id="bank_receipt" accept="image/*" >';   

    woocommerce_form_field(
        'payment_attachment_number',
        array(
            'type' => 'hidden',            
            'class' => array( 'form-row', 'form-row-wide' ),
            'required' => true,
        )
    );

    echo '</div>';
    $description .= ob_get_clean();
    return $description;
}

/*
Validatio of extra field of payment method on checkout page
 */
add_action( 'woocommerce_checkout_process', 'advance_bank_description_fields_validation' );
function advance_bank_description_fields_validation() {
    if( 'advanced_bank_transfer' !== $_POST['payment_method'])return;
    if( ! isset( $_POST['payment_attachment_number'] )  || empty( $_POST['payment_attachment_number'] ) ) {
        wc_add_notice( 'Please upload bank transfer receipt', 'error' );
    }
}

/*
save upload extra filed of payment method on checkout page
 */
add_action( 'woocommerce_checkout_update_order_meta', 'advance_bank_checkout_update_order_meta', 10, 1 );
function advance_bank_checkout_update_order_meta( $order_id ) {
    if( 'advanced_bank_transfer' !== $_POST['payment_method'])return;
    if( isset( $_POST['payment_attachment_number'] ) || ! empty( $_POST['payment_attachment_number'] ) ) {
       update_post_meta( $order_id, 'payment_attachment_number', $_POST['payment_attachment_number'] );
    }
}

/*
Show uploaded Receipt in edit order page in admin
 */
add_action( 'woocommerce_admin_order_data_after_order_details', 'advance_bank_order_data_after_general', 10, 1 );
function advance_bank_order_data_after_general( $order ) {
    $url=wp_get_attachment_image_url(get_post_meta( $order->get_id(), 'payment_attachment_number', true ),'full');
    $thumbnail=wp_get_attachment_image_url(get_post_meta( $order->get_id(), 'payment_attachment_number', true ));
    echo '<div style="margin-top:20px;display: inline-block;"><strong>' . __( 'Uploaded Slip:', 'advanced_bank_transfer' ) . '</strong><br><a target="_blank" href="' .$url. '"><img src="' .$thumbnail. '"/></a></div>';
}


/*
 adding advance bank transfer gatway to woocommerce default methods
 */
add_filter( 'woocommerce_payment_gateways', 'add_Advanced_Bank_Transfer_class' );
function add_Advanced_Bank_Transfer_class( $methods ) {
    $methods[] = 'WC_Gateway_Advanced_Bank_Transfer'; 
    return $methods;
}

/*
file upload function of receipt
 */
add_action('wp_ajax_nopriv_upload_file', 'upload_file_callback');
add_action( 'wp_ajax_upload_file', 'upload_file_callback' );
function upload_file_callback() {
    foreach( $_FILES as $file ){  
        if( is_array( $file ) ) {
            $attach_id =upload_user_file($file);  //Call function             
            echo json_encode(array('status'=>'success','attach_id' => $attach_id,'url'=>wp_get_attachment_image_url($attach_id) ));
            wp_die();
        }
        echo json_encode(array('status'=>'failed','attach_id' => 0,'url'=>'' ));
        wp_die();
    }
}


/******FILE UPLOAD*****************/
function upload_user_file( $file = array() ) {    
    require_once( ABSPATH . 'wp-admin/includes/admin.php' );
    $file_return = wp_handle_upload( $file, array('test_form' => false ) );
    if( isset( $file_return['error'] ) || isset( $file_return['upload_error_handler'] ) ) {
        return false;
    } else {
        $filename = $file_return['file'];
        $attachment = array(
            'post_mime_type' => $file_return['type'],
            'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
            'post_content' => '',
            'post_status' => 'inherit',
            'guid' => $file_return['url']
        );
        $attachment_id = wp_insert_attachment( $attachment, $file_return['url'] );
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attachment_data = wp_generate_attachment_metadata( $attachment_id, $filename );
        wp_update_attachment_metadata( $attachment_id, $attachment_data );
        if( 0 < intval( $attachment_id ) ) {
          return $attachment_id;
        }
    }
    return false;
}

/*
delete function of uploaded file on checkout page
 */
add_action('wp_ajax_nopriv_remove_receipt', 'remove_receipt_callback');
add_action( 'wp_ajax_remove_receipt', 'remove_receipt_callback' );
function remove_receipt_callback() {     
    if( wp_delete_attachment((int)$_POST['attach_id'],true) ) {                     
        echo json_encode(array('status'=>'success'));
        wp_die();
    }
    echo json_encode(array('status'=>'failed'));
    wp_die();    
}
