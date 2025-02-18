<?php

namespace CatalogX\Quote;

class Ajax {
    /**
     * Ajax class constructor functions
     */
    public function __construct() {
        //quote
        add_action( 'wp_ajax_quote_added_in_list', [ $this, 'add_item_in_cart' ] );
        add_action( 'wp_ajax_nopriv_quote_added_in_list', [ $this, 'add_item_in_cart' ] );
    }

    /**
     * add item in quote cart
     */
    public function add_item_in_cart() {
        $return             = 'false';
        $message            = '';
        $product_id         = ( isset( $_POST['product_id'] ) && is_numeric( $_POST['product_id'] ) ) ? (int) $_POST['product_id'] : false;          
        $is_valid_variation = ( isset( $_POST['variation_id'] ) && ! empty( $_POST['variation_id'] ) ) ? is_numeric( $_POST['variation_id'] ) : true;
       
        $is_valid = apply_filters( 'catalog_add_item_in_cart_is_valid', $product_id && $is_valid_variation, $product_id );

        $postdata = $_POST;
        $postdata = apply_filters( 'catalog_add_item_in_cart_prepare', $postdata, $product_id );

        if ( ! $is_valid ) {
            $errors[] = __( 'Error occurred while adding product to Request a Quote list.', 'catalogx' );
        } else {
            $return = CatalogX()->quotecart->add_cart_item( $postdata );
        }

        if ( 'true' === $return ) {
            $message = __( 'Product added to quote list.', 'catalogx' );
        } elseif ( 'exists' === $return ) {
            $message = __( 'Product already in your quote list.', 'catalogx' );
        } else {
            $message = $errors;
        }
        wp_send_json(
            [
            'result'     => $return,
            'message'    => $message,
            'rqa_url'    => CatalogX()->quotecart->get_request_quote_page_url(),
            // 'variations' => implode( ',', $this->get_variations_list() ),
            ]
            
        );
    }
}