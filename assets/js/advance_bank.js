
jQuery( document ).ready(function() {
    //function to upload file
    jQuery(document).on('change', '#bank_receipt' ,function(){        
        if(typeof jQuery('#bank_receipt')[0].files[0] != 'undefined'){            
            jQuery('#place_order').prop('disabled', true);
            jQuery('#bank_receipt_progress').html('<span style="color:#96588a">Please wait. File is uploading...</span>');
            //Append here your necessary data
    	    var fd = new FormData();
            fd.append( "action", 'upload_file');      
            fd.append( "main_image", jQuery('#bank_receipt')[0].files[0]);
            jQuery.ajax({
                type: 'POST',
                url: myAjax.ajaxurl,
                data: fd, 
                processData: false,
                contentType: false,
                success: function(data) {
                    data=JSON.parse(data);
                    if(data.status=="success"){
                        jQuery('#place_order').prop('disabled', false);
                        jQuery('#bank_receipt_progress').html('');
                        jQuery('#bank_receipt_preview').html('<img src="'+data.url+'"><span id="removeReceipt" title="remove">X</span>');
                        jQuery('#payment_attachment_number').attr("value",data.attach_id);
                        jQuery('#bank_receipt').hide();
                    }else{
                        jQuery('#place_order').prop('disabled', false);
                        jQuery('#bank_receipt_progress').html('');
                    }                    
                },
                error: function(errorThrown) {
                    jQuery('#bank_receipt_progress').html('');
                    alert(errorThrown);
                }
            });
        }
    });

    // function to delete the uploaded file on checkout page
    jQuery(document).on('click', '#removeReceipt' ,function(){
        jQuery('#place_order').prop('disabled', true);
        jQuery('#bank_receipt_progress').html('<span style="color:red">Please wait. File is deleting...</span>');
        jQuery.ajax({
            type: 'POST',
            url: myAjax.ajaxurl+'?action=remove_receipt',
            data: {attach_id:jQuery('#payment_attachment_number').val()}, 
            success: function(data) {
                data=JSON.parse(data);
                if(data.status=="success"){
                    jQuery('#place_order').prop('disabled', false);
                    jQuery('#bank_receipt_progress').html('');
                    jQuery('#bank_receipt_preview').html('');
                    jQuery('#payment_attachment_number').removeAttr("value");
                    jQuery('#bank_receipt').val('');
                    jQuery('#bank_receipt').show();
                }else{
                    jQuery('#place_order').prop('disabled', false);
                    jQuery('#bank_receipt_progress').html('');
                }            
            },
            error: function(errorThrown) {
                jQuery('#place_order').prop('disabled', false);
                jQuery('#bank_receipt_progress').html('');
                alert(errorThrown);
            }

        });
    });
});