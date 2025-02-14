jQuery( document ).ready( function ( $ ) {
    $( '#catalog-modal' ).hide();
    var $enquiryBtn = $('.woocommerce-catalog-enquiry-btn');
    if($('form.variations_form').length > 0){
        $enquiryBtn.hide();
    }
    $('form.variations_form').on('show_variation', function(event, variation) {
        $enquiryBtn.show();
    });

    $('form.variations_form').on('hide_variation', function(event) {
        $enquiryBtn.hide();
    });

    $('.modal-close-btn').on('click', function(event) {
        $( '#catalog-modal' ).hide();
    });
    
	$( '#woocommerce-catalog .woocommerce-catalog-enquiry-btn' ).on('click', function () {
			$( '#catalog-modal' ).slideToggle( 1000 );
		}
	);
	$( '#close-enquiry-popup' ).on('click', function (e) {
        e.preventDefault();
			$( '#catalog-modal' ).slideToggle( 1000 );
		}
	);

});