<?php

if( !defined( 'ABSPATH' ) ) exit;  // Exit if accessed directly

// Add Voucher Shortcode
function wpgv_voucher_shortcode() 
{
	global $wp, $wpdb;
	$find = array( 'http://', 'https://' );
	$replace = '';
	$siteURL = str_replace( $find, $replace, get_site_url() );
	$voucher_table 	= $wpdb->prefix . 'giftvouchers_list';
	$setting_table 	= $wpdb->prefix . 'giftvouchers_setting';
	$template_table = $wpdb->prefix . 'giftvouchers_template';
    
    $order_count = $wpdb->get_var( "SELECT COUNT(*) FROM $voucher_table" );

    if($order_count > 10) {
        $html = sprintf('Voucher Orders are more than 10. Please Upgrade Wordpress Gift Voucher Plugin to <a href="%s" target="_blank">premium</a> for more features and unlimited orders.', 'http://www.codemenschen.at/wordpress-gift-voucher-plugin/');
        return $html;
    }

	$setting_options = $wpdb->get_row( "SELECT * FROM $setting_table WHERE id = 1" );
   	$template_options = $wpdb->get_results( "SELECT * FROM $template_table WHERE active = 1" );
   	$nonce = wp_create_nonce( 'voucher_form_verify' );

    $voucher_bgcolor = $setting_options->voucher_bgcolor;
    $voucher_color = $setting_options->voucher_color;

    $minVoucherValue = $setting_options->voucher_min_value ? $setting_options->voucher_min_value : 1;
    $minVoucherValueMsg = $setting_options->voucher_min_value ? '(Min Voucher Value '.$setting_options->currency.''.$setting_options->voucher_min_value.') ' : '';
    $maxVoucherValue = $setting_options->voucher_max_value ? $setting_options->voucher_max_value : 10000;

    $html = '<style type="text/css">
        #voucher-multistep-form.wizard>.steps .done a,
        #voucher-multistep-form.wizard>.steps .done a:hover,
        #voucher-multistep-form.wizard>.steps .done a:active,
        #voucher-multistep-form #secondRightDiv .cardDiv,
        #voucher-multistep-form.wizard>.actions a,
        #voucher-multistep-form.wizard>.actions a:hover,
        #voucher-multistep-form.wizard>.actions a:active,
        #voucher-multistep-form .voucherPreviewButton a,
        #voucher-multistep-form #voucherPaymentButton,
        #voucher-multistep-form .sin-template input[type="radio"]:checked:before {
            background-color: #'.$voucher_bgcolor.';
        }
        #voucher-multistep-form .voucherform .form-group input[type="text"],
        #voucher-multistep-form .form-group input[type="email"],
        #voucher-multistep-form .form-group input[type="tel"],
        #voucher-multistep-form .form-group input[type="number"],
        #voucher-multistep-form .form-group select,
        #voucher-multistep-form .form-group textarea,
        #voucher-multistep-form .sin-template label.selectImage {
            border-color: #'.$voucher_bgcolor.';
        }
        #voucher-multistep-form .paymentUserInfo .full,
        #voucher-multistep-form .paymentUserInfo .half {
            color: #'.$voucher_bgcolor.';
        }
        #voucher-multistep-form.wizard>.content>.body .voucherBottomDiv label {
            color:  #'.$voucher_color.';
        }
    </style>';
	$html .= '<form name="voucherform" id="voucher-multistep-form" action="'.home_url( $wp->request ).'" enctype="multipart/form-data">
		<input type="hidden" name="voucher_form_verify" value="'.$nonce.'">
		<h3>'.__('Select Templates', 'gift-voucher' ).'</h3>
		<fieldset>
			<legend>'.__('Select Templates', 'gift-voucher' ).'</legend><div class="voucher-row">';
	foreach ($template_options as $key => $options) {
		$image_attributes = wp_get_attachment_image_src( $options->image, 'voucher-thumb' );
		$image = ($image_attributes) ? $image_attributes[0] : WPGIFT__PLUGIN_URL.'/assets/img/demo.png';
		$html .= '<div class="vouchercol'.$setting_options->template_col.'"><div class="sin-template"><label for="template_id'.$options->id.'"><img src="'.$image.'" width="'.$image_attributes[1].'"/><span>'.$options->title.'</span></label><input type="radio" name="template_id" value="'.$options->id.'" id="template_id'.$options->id.'" class="required"></div></div>';
	}

    $paymenyGateway = __('Payment Gatway');
    if($setting_options->paypal || $setting_options->sofort){
        $paymenyGateway = '<select name="voucherPayment" id="voucherPayment">';
        $paymenyGateway .= $setting_options->paypal ? '<option value="Paypal">'.__('Paypal').'</option>' : '';
        $paymenyGateway .= $setting_options->sofort ? '<option value="Sofort">'.__('Sofort').'</option>' : '';
        $paymenyGateway .= '</select>';
    }

    $expiryCard = ($setting_options->voucher_expiry_type == 'days') ? date('d.m.Y',strtotime('+'.$setting_options->voucher_expiry.' days',time())) . PHP_EOL : $setting_options->voucher_expiry;
	$html .= '</div></fieldset>
 
	<h3>'.__('Personalize', 'gift-voucher' ).'</h3>
	<fieldset>
		<legend>'.__('Personalize', 'gift-voucher' ).'</legend><div class="voucher-row">
		<div class="voucherform secondLeft">
			<div class="form-group">
				<label for="voucherForName">'.__('Receiver of the voucher', 'gift-voucher' ).' <sup>*</sup></label>
				<input type="text" name="voucherForName" id="voucherForName" class="required">
			</div>
			<div class="form-group">
				<label for="voucherFromName">'.__('FROM (Exhibitor)', 'gift-voucher' ).' <sup>*</sup></label>
				<input type="text" name="voucherFromName" id="voucherFromName" class="required">
			</div>
			<div class="form-group">
				<label for="voucherAmount">'.__('Voucher Value', 'gift-voucher' ).' '.$minVoucherValueMsg.'<sup>*</sup></label>
				<span class="currencySymbol">'.$setting_options->currency.'</span>
    			<input type="number" name="voucherAmount" id="voucherAmount" class="required" min="'.$minVoucherValue.'" max="'.$maxVoucherValue.'">
    		</div>
    		<div class="form-group">
    			<label for="voucherMessage">'.__('Personal Message', 'gift-voucher' ).' (Max: 250 '.__('Characters', 'gift-voucher' ).') <sup>*</sup></label>
    			<textarea name="voucherMessage" id="voucherMessage" class="required" maxlength="250"></textarea>
    			<div class="maxchar"></div>
    		</div>
    	</div>
    	<div id="secondRightDiv" class="sideview secondRight">
    	<div class="cardDiv">
    		<div class="cardImgTop">
    			<img class="uk-thumbnail" src="'.WPGIFT__PLUGIN_URL.'/assets/img/demo.png">
    		</div>
    		<div class="voucherBottomDiv">
    			<h2>'.__('Gift Voucher', 'gift-voucher' ).'</h2>
    			<div class="uk-form-row">
    				<div class="nameFormLeft">
    					<label>'.__('For', 'gift-voucher' ).'</label>
    					<input type="text" name="forNameCard" class="forNameCard" readonly>
    				</div>
    				<div class="nameFormRight">
    					<label>'.__('From', 'gift-voucher' ).'</label>
    					<input type="text" name="fromNameCard" class="fromNameCard" readonly>
    				</div>
    				<div class="voucherValueForm">
    					<label>'.__('Voucher Value', 'gift-voucher' ).'</label>
    					<span class="currencySymbol">'.$setting_options->currency.'</span>
    					<input type="text" name="vaoucherValueCard" class="vaoucherValueCard" readonly>
    				</div>
    				<div class="messageForm">
    					<label>'.__('Personal Message', 'gift-voucher' ).'</label>
    					<textarea name="personalMessageCard" class="personalMessageCard" readonly></textarea>
    				</div>
    				<div class="expiryFormLeft">
    					<label>'.__('Date of Expiry', 'gift-voucher' ).'</label>
                        <input type="text" name="expiryCard" class="expiryCard" value="'.$expiryCard.'" readonly>
    				</div>
    				<div class="codeFormRight">
    					<label>'.__('Coupon Code', 'gift-voucher' ).'</label>
    					<input type="text" name="codeCard" class="codeCard" readonly>
    				</div>
    				<div class="clearfix"></div>
    				<div class="voucherSiteInfo"><a href="'.get_site_url() .'">'.$siteURL.'</a> | <a href="mailto:'.get_option('admin_email').'">'.get_option('admin_email').'</a></div>
    				<div class="termsCard">* '.__('Cash payment is not possible. The terms and conditions apply.', 'gift-voucher' ).'</div>
    			</div></div>
    	</div></div>
    </fieldset>
 
    <h3>'.__('Payment', 'gift-voucher' ).'</h3>
    <fieldset>
    	<legend>'.__('Payment', 'gift-voucher' ).'</legend><div class="voucher-row">
		<div class="voucherform secondLeft">
			<div class="form-group">
				<label for="voucherFirstName">'.__('First Name', 'gift-voucher' ).' <sup>*</sup></label>
				<input type="text" name="voucherFirstName" id="voucherFirstName" class="required">
			</div>
			<div class="form-group">
				<label for="voucherLastName">'.__('Last Name', 'gift-voucher' ).' <sup>*</sup></label>
				<input type="text" name="voucherLastName" id="voucherLastName" class="required">
			</div>
			<div class="form-group">
				<label for="voucherEmail">'.__('Email Address', 'gift-voucher' ).' <sup>*</sup></label>
				<input type="email" name="voucherEmail" id="voucherEmail" class="required">
			</div>
			<div class="form-group">
				<label for="voucherAddress">'.__('Street / House No', 'gift-voucher' ).' <sup>*</sup></label>
    			<input type="text" name="voucherAddress" id="voucherAddress" class="required">
    		</div>
    		<div class="form-group">
    			<label for="voucherPincode">'.__('Pincode', 'gift-voucher' ).' <sup>*</sup></label>
    			<input type="text" name="voucherPincode" id="voucherPincode" class="required">
    		</div>
    		<div class="form-group">
    			<label for="voucherPayment">'.__('Payment Gateway', 'gift-voucher' ).' <sup>*</sup></label>'.$paymenyGateway.'
    		</div>
    	</div>
    	<div id="secondRightDiv" class="sideview secondRight">
    	<div class="cardDiv">
    		<div class="cardImgTop">
    			<img class="uk-thumbnail" src="'.WPGIFT__PLUGIN_URL.'/assets/img/demo.png">
    		</div>
    		<div class="voucherBottomDiv">
    			<h2>'.__('Gift Voucher', 'gift-voucher' ).'</h2>
    			<div class="uk-form-row">
    				<div class="nameFormLeft">
    					<label>'.__('For', 'gift-voucher' ).'</label>
    					<input type="text" name="forNameCard" class="forNameCard" readonly>
    				</div>
    				<div class="nameFormRight">
    					<label>'.__('From', 'gift-voucher' ).'</label>
    					<input type="text" name="fromNameCard" class="fromNameCard" readonly>
    				</div>
    				<div class="voucherValueForm">
    					<label>'.__('Voucher Value', 'gift-voucher' ).'</label>
    					<span class="currencySymbol">'.$setting_options->currency.'</span>
    					<input type="text" name="vaoucherValueCard" class="vaoucherValueCard" readonly>
    				</div>
    				<div class="messageForm">
    					<label>'.__('Personal Message', 'gift-voucher' ).'</label>
    					<textarea name="personalMessageCard" class="personalMessageCard" readonly></textarea>
    				</div>
    				<div class="expiryFormLeft">
    					<label>'.__('Date of Expiry', 'gift-voucher' ).'</label>
                        <input type="text" name="expiryCard" class="expiryCard" value="'.$expiryCard.'" readonly>
    				</div>
    				<div class="codeFormRight">
    					<label>'.__('Coupon Code', 'gift-voucher' ).'</label>
    					<input type="text" name="codeCard" class="codeCard" readonly>
    				</div>
    				<div class="clearfix"></div>
    				<div class="voucherSiteInfo"><a href="'.get_site_url() .'">'.$siteURL.'</a> | <a href="mailto:'.get_option('admin_email').'">'.get_option('admin_email').'</a></div>
    				<div class="termsCard">* '.__('Cash payment is not possible. The terms and conditions apply.', 'gift-voucher' ).'</div>
    			</div></div>
    	</div></div>
    </fieldset>
 
    <h3>'.__('Overview', 'gift-voucher' ).'</h3>
    <fieldset>
    	<legend>'.__('Overview', 'gift-voucher' ).'</legend><div class="voucher-row">
		<div class="voucherform secondLeft">
			<div class="paymentUserInfo">
				<div class="half">
					<div class="labelInfo">'.__('First Name', 'gift-voucher' ).'</div>
					<div class="voucherFirstNameInfo"></div>
				</div>
				<div class="half">
					<div class="labelInfo">'.__('Last Name', 'gift-voucher' ).'</div>
					<div class="voucherLastNameInfo"></div>
				</div>
				<div class="full">
					<div class="labelInfo">'.__('Email Address', 'gift-voucher' ).'</div>
					<div class="voucherEmailInfo"></div>
				</div>
				<div class="full">
					<div class="labelInfo">'.__('Address', 'gift-voucher' ).'</div>
					<div class="voucherAddressInfo"></div>
				</div>
				<div class="full">
					<div class="labelInfo">'.__('Pincode', 'gift-voucher' ).'</div>
					<div class="voucherPincodeInfo"></div>
				</div>
				<div class="full">
					<div class="labelInfo">'.__('Payment Gateway', 'gift-voucher' ).'</div>
					<div class="voucherPaymentInfo"></div>
				</div><div class="clearfix"></div>
				<hr>
				<div class="full">
					<div class="labelInfo">'.__('Voucher Value', 'gift-voucher' ).'</div>
					<div class="voucherAmountInfo">'.$setting_options->currency.'<span></span></div>
				</div>
				<div class="full">
					<div class="labelInfo">'.__('Receiver of The Voucher', 'gift-voucher' ).'</div>
					<div class="voucherReceiverInfo"></div>
				</div>
				<div class="full">
					<div class="labelInfo">'.__('Personal Message', 'gift-voucher' ).'</div>
					<div class="voucherMessageInfo"></div>
				</div><div class="clearfix"></div>
				<hr>
				<div class="acceptVoucherTerms">
					<label><input type="checkbox" class="required" name="acceptVoucherTerms"> '.__('I hereby accept the terms and conditions, the revocation of the privacy policy and confirm that all information is correct.', 'gift-voucher' ).'</label>
				</div>
				<div class="checkEmailInfo">'.__('Please pay attention to the correct mail address, as the voucher will be sent there as a PDF.', 'gift-voucher' ).'</div>
				<div class="voucherNote">'.$setting_options->voucher_terms_note.'</div>
				<button type="button" id="voucherPaymentButton" name="finalPayment">'.__('Pay Now', 'gift-voucher' ).'</button>
			</div>
    	</div>
    	<div id="secondRightDiv" class="sideview secondRight">
    	<div class="cardDiv">
    		<div class="cardImgTop">
    			<img class="uk-thumbnail" src="'.WPGIFT__PLUGIN_URL.'/assets/img/demo.png">
    		</div>
    		<div class="voucherBottomDiv">
    			<h2>'.__('Gift Voucher', 'gift-voucher' ).'</h2>
    			<div class="uk-form-row">
    				<div class="nameFormLeft">
    					<label>'.__('For', 'gift-voucher' ).'</label>
    					<input type="text" name="forNameCard" class="forNameCard" readonly>
    				</div>
    				<div class="nameFormRight">
    					<label>'.__('From', 'gift-voucher' ).'</label>
    					<input type="text" name="fromNameCard" class="fromNameCard" readonly>
    				</div>
    				<div class="voucherValueForm">
    					<label>'.__('Voucher Value', 'gift-voucher' ).'</label>
    					<span class="currencySymbol">'.$setting_options->currency.'</span>
    					<input type="text" name="vaoucherValueCard" class="vaoucherValueCard" readonly>
    				</div>
    				<div class="messageForm">
    					<label>'.__('Personal Message', 'gift-voucher' ).'</label>
    					<textarea name="personalMessageCard" class="personalMessageCard" readonly></textarea>
    				</div>
    				<div class="expiryFormLeft">
    					<label>'.__('Date of Expiry', 'gift-voucher' ).'</label>
                        <input type="text" name="expiryCard" class="expiryCard" value="'.$expiryCard.'" readonly>
    				</div>
    				<div class="codeFormRight">
    					<label>'.__('Coupon Code', 'gift-voucher' ).'</label>
    					<input type="text" name="codeCard" class="codeCard" readonly>
    				</div>
    				<div class="clearfix"></div>
    				<div class="voucherSiteInfo"><a href="'.get_site_url() .'">'.$siteURL.'</a> | <a href="mailto:'.get_option('admin_email').'">'.get_option('admin_email').'</a></div>
    				<div class="termsCard">* '.__('Cash payment is not possible. The terms and conditions apply.', 'gift-voucher' ).'</div>
    			</div></div>
    		</div>
    		<div class="voucherPreviewButton"><a href="#" data-src="'.get_site_url() .'/voucher-pdf-preview" target="_blank">'.__('Show Preview', 'gift-voucher' ).'</a></div>
    	</div>
    </fieldset>
</form>';
	return $html;
}

function wpgv__doajax_front_template() {
	global $wpdb;
	$template_table = $wpdb->prefix . 'giftvouchers_template';
	$template_id = $_REQUEST['template_id'];
   	$template_options = $wpdb->get_row( "SELECT * FROM $template_table WHERE id = $template_id" );
	$image_attributes = wp_get_attachment_image_src( $template_options->image, 'voucher-medium' );
    $image_attributes = ($image_attributes) ? $image_attributes[0] : WPGIFT__PLUGIN_URL.'/assets/img/demo.png';
	echo wp_send_json(array('image' => $image_attributes, 'title' => $template_options->title));
	wp_die();
}

add_shortcode( 'wpgv_giftvoucher', 'wpgv_voucher_shortcode' );
add_action('wp_ajax_nopriv_wpgv_doajax_front_template', 'wpgv__doajax_front_template');
add_action('wp_ajax_wpgv_doajax_front_template', 'wpgv__doajax_front_template');