"use strict";jQuery.base64=(function($){var _PADCHAR="=",_ALPHA="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/",_VERSION="1.0";function _getbyte64(s,i){var idx=_ALPHA.indexOf(s.charAt(i));if(idx===-1){throw"Cannot decode base64"}return idx}function _decode(s){var pads=0,i,b10,imax=s.length,x=[];s=String(s);if(imax===0){return s}if(imax%4!==0){throw"Cannot decode base64"}if(s.charAt(imax-1)===_PADCHAR){pads=1;if(s.charAt(imax-2)===_PADCHAR){pads=2}imax-=4}for(i=0;i<imax;i+=4){b10=(_getbyte64(s,i)<<18)|(_getbyte64(s,i+1)<<12)|(_getbyte64(s,i+2)<<6)|_getbyte64(s,i+3);x.push(String.fromCharCode(b10>>16,(b10>>8)&255,b10&255))}switch(pads){case 1:b10=(_getbyte64(s,i)<<18)|(_getbyte64(s,i+1)<<12)|(_getbyte64(s,i+2)<<6);x.push(String.fromCharCode(b10>>16,(b10>>8)&255));break;case 2:b10=(_getbyte64(s,i)<<18)|(_getbyte64(s,i+1)<<12);x.push(String.fromCharCode(b10>>16));break}return x.join("")}function _getbyte(s,i){var x=s.charCodeAt(i);if(x>255){throw"INVALID_CHARACTER_ERR: DOM Exception 5"}return x}function _encode(s){if(arguments.length!==1){throw"SyntaxError: exactly one argument required"}s=String(s);var i,b10,x=[],imax=s.length-s.length%3;if(s.length===0){return s}for(i=0;i<imax;i+=3){b10=(_getbyte(s,i)<<16)|(_getbyte(s,i+1)<<8)|_getbyte(s,i+2);x.push(_ALPHA.charAt(b10>>18));x.push(_ALPHA.charAt((b10>>12)&63));x.push(_ALPHA.charAt((b10>>6)&63));x.push(_ALPHA.charAt(b10&63))}switch(s.length-imax){case 1:b10=_getbyte(s,i)<<16;x.push(_ALPHA.charAt(b10>>18)+_ALPHA.charAt((b10>>12)&63)+_PADCHAR+_PADCHAR);break;case 2:b10=(_getbyte(s,i)<<16)|(_getbyte(s,i+1)<<8);x.push(_ALPHA.charAt(b10>>18)+_ALPHA.charAt((b10>>12)&63)+_ALPHA.charAt((b10>>6)&63)+_PADCHAR);break}return x.join("")}return{decode:_decode,encode:_encode,VERSION:_VERSION}}(jQuery));
jQuery(document).ready(function($) {
    var form = $("#voucher-multistep-form").show();
form.steps({
    headerTag: "h3",
    bodyTag: "fieldset",
    transitionEffect: "slideLeft",
    onStepChanging: function (event, currentIndex, newIndex)
    {
        // Always allow previous action even if the current form is not valid!
        if (currentIndex > newIndex)
        {
            return true;
        }
        // Needed in some cases if the user went back (clean up)
        if (currentIndex < newIndex)
        {
            // To remove error styles
            form.find(".body:eq(" + newIndex + ") label.error").remove();
            form.find(".body:eq(" + newIndex + ") .error").removeClass("error");
        }
        form.validate().settings.ignore = ":disabled,:hidden";
        return form.valid();
    },
    onStepChanged: function (event, currentIndex, priorIndex)
    {
        if(currentIndex === 1) {
            var template_id = $('input[name=template_id]:checked').val();
            $.ajax({
                url: frontend_ajax_object.ajaxurl,
                type: "POST",
                data: "action=wpgv_doajax_front_template&template_id="+template_id,
                success: function(data) {
                    $(".cardImgTop img").attr('src', data.image); 
                    $('.voucherBottomDiv > h2').text(data.title);
                }
            });
        }
        if(currentIndex === 3) {
            $('.wizard>.actions a[href="#finish"]').hide();
            var link = $('.voucherPreviewButton a').data('src'),
            nonce = $('input[name=voucher_form_verify]').val(),
            templates_id = $.base64.encode($('input[name=template_id]:checked').val()),
            forName = $.base64.encode($('#voucherForName').val()),
            fromName = $.base64.encode($('#voucherFromName').val()),
            voucherValue = $.base64.encode($('#voucherAmount').val()),
            message = $.base64.encode($('#voucherMessage').val()),
            expiry = $.base64.encode($('.expiryCard').val()),
            code = $.base64.encode($('.codeCard').val()),
            fulllink = link+'?action=preview&nonce='+nonce+'&template='+templates_id+'&for='+forName+'&from='+fromName+'&value='+voucherValue+'&message='+message+'&expiry='+expiry+'&code='+code;
            $('.voucherPreviewButton a').attr('href', fulllink);
        }
    },
    onFinishing: function (event, currentIndex)
    {
        form.validate().settings.ignore = ":disabled";
        return form.valid();
    },
    onFinished: function (event, currentIndex)
    {
        alert(frontend_ajax_object.submitted);
    },
    labels: {
        finish: frontend_ajax_object.finish,
        next: frontend_ajax_object.next,
        previous: frontend_ajax_object.previous,
    }
    }).validate({
    errorPlacement: function errorPlacement(error, element) { element.before(error); },
    rules: {
        template_id: {
            required: true,
        },
        acceptVoucherTerms: {
            required: true,
        }
    },
    messages: {
        template_id: {
            required: frontend_ajax_object.select_template
        },
        acceptVoucherTerms: {
            required: frontend_ajax_object.accept_terms,
        }
    }
});
$('.sin-template label').click(function(){ $('.sin-template label').removeClass('selectImage'); $(this).addClass('selectImage'); });
$('#voucherForName').on('input blur', function() {
    var dInput = this.value;
    $(".forNameCard").val(dInput);
    $(".voucherReceiverInfo").text(dInput);
});
$('#voucherFromName').on('input blur', function() {
    var dInput = this.value;
    $(".fromNameCard").val(dInput);
});
$('#voucherAmount').on('input blur', function() {
    var dInput = this.value;
    $(".vaoucherValueCard").val(dInput);
    $(".voucherAmountInfo span").text(dInput);
});
$('#voucherMessage').on('input blur', function() {
    var dInput = this.value;
    $('.maxchar').text("Total Characters: " + (this.value.length));
    $(".personalMessageCard").val(dInput);
    $(".voucherMessageInfo").text(dInput);
});
$('#voucherFirstName').on('input blur', function() {
    var dInput = this.value;
    $(".voucherFirstNameInfo").text(dInput);
});
$('#voucherLastName').on('input blur', function() {
    var dInput = this.value;
    $(".voucherLastNameInfo").text(dInput);
});
$('#voucherEmail').on('input blur', function() {
    var dInput = this.value;
    $(".voucherEmailInfo").text(dInput);
});
$('#voucherAddress').on('input blur', function() {
    var dInput = this.value;
    $(".voucherAddressInfo").text(dInput);
});
$('#voucherPincode').on('input blur', function() {
    var dInput = this.value;
    $(".voucherPincodeInfo").text(dInput);
});
$('#voucherPayment').on('change', function() {
    var dInput = this.value;
    $(".voucherPaymentInfo").text(dInput);
});
$(".voucherPaymentInfo").text($('#voucherPayment').val());
$('.codeCard').val(Math.floor(100000000000000 + Math.random() * 900000000000000));

$('#voucherPaymentButton').on('click', function() {

    if(!$('input[name=acceptVoucherTerms]').is(':checked')) {
        alert(frontend_ajax_object.accept_terms);
        return false;
    }

    var nonce = $('input[name=voucher_form_verify]').val(),
    templates_id = $.base64.encode($('input[name=template_id]:checked').val()),
    forName = $.base64.encode($('#voucherForName').val()),
    fromName = $.base64.encode($('#voucherFromName').val()),
    voucherValue = $.base64.encode($('#voucherAmount').val()),
    message = $.base64.encode($('#voucherMessage').val()),
    firstName = $.base64.encode($('#voucherFirstName').val()),
    lastName = $.base64.encode($('#voucherLastName').val()),
    email = $.base64.encode($('#voucherEmail').val()),
    address = $.base64.encode($('#voucherAddress').val()),
    pincode = $.base64.encode($('#voucherPincode').val()),
    paymentMethod = $.base64.encode($('#voucherPayment').val()),
    expiry = $.base64.encode($('.expiryCard').val()),
    code = $.base64.encode($('.codeCard').val());
    $.ajax({
        url: frontend_ajax_object.ajaxurl,
        type: "POST",
        data: 'action=wpgv_doajax_pdf_save_func&nonce='+nonce+'&template='+templates_id+'&for='+forName+'&from='+fromName+'&value='+voucherValue+'&message='+message+'&expiry='+expiry+'&code='+code+'&firstname='+firstName+'&lastname='+lastName+'&email='+email+'&address='+address+'&pincode='+pincode+'&paymentmethod='+paymentMethod,
        success: function(a) {
            window.location.replace(a);
        },
        error: function() {
            alert(frontend_ajax_object.error_occur);
        }
    });
});
$('#voucher-multistep-form').ajaxStart(function () { $('.wizard>.content>.body.current').addClass('loading'); })
           .ajaxStop(function () { $('.wizard>.content>.body.current').removeClass('loading'); });
});