jQuery( document ).ready( function ( $ ) {
    $( '#catalogx-modal' ).hide();
    var $enquiryBtn = $('.catalogx-enquiry-btn');
    if($('form.variations_form').length > 0){
        $enquiryBtn.hide();
    }
    $('form.variations_form').on('show_variation', function(event, variation) {
        $enquiryBtn.show();
    });

    $('form.variations_form').on('hide_variation', function(event) {
        $enquiryBtn.hide();
    });

    $('.catalogx-modal-close-btn').on('click', function(event) {
        $( '#catalogx-modal' ).hide();
    });
    
	$( '#catalogx-enquiry .catalogx-enquiry-btn' ).on('click', function () {
        $( '#catalogx-modal' ).slideToggle( 1000 );
    }
	);
	$( '#catalogx-close-enquiry-popup' ).on('click', function (e) {
        e.preventDefault();
        $( '#catalogx-modal' ).slideToggle( 1000 );
    }
	);
});