<?php

namespace CatalogX\Core;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * CatalogX QuoteCart class
 *
 * @class 		QuoteCart class
 * @version		6.0.0
 * @author 		MultivendorX
 */
class QuoteCart {
    
    public $session;
    public $quote_cart_content = array();
    public $errors = array();
    /**
     * Constructor
     *
     * @access public
     * @return void
     */
    public function __construct() {
        add_action( 'init', array( $this, 'quote_session_start' ));
        add_action( 'wp_loaded', array( $this, 'init_callback' ));
        add_action( 'wp', array( $this, 'maybe_set_cart_cookies' ), 99 ); 
        add_action( 'shutdown', array( $this, 'maybe_set_cart_cookies' ), 0 );
        add_action( 'quote_clean_cron', array( $this, 'clean_session'));
        add_action( 'wp_loaded', array( $this, 'add_to_quote_action' ), 30);
    }

    /**
     * Starts the php session data for the cart.
     */
    function quote_session_start(){
        if( ! isset( $_COOKIE['woocommerce_items_in_cart'] ) ) {
            do_action( 'woocommerce_set_cart_cookies', true );
        }
        $this->session = new Session();
        $this->set_session();
    }

    function init_callback() {
        $this->get_quote_cart_session();
        $this->session->set_customer_session_cookie(true);
        $this->quote_cron_schedule();
    }

    function get_quote_cart_session() {
        $this->quote_cart_content = $this->session->get( 'quote_cart', array() );
        return $this->quote_cart_content;
    }

    public function quote_cron_schedule(){     

        if ( !wp_next_scheduled( 'quote_clean_cron' ) ) {
            wp_schedule_event( time(), 'hourly', 'quote_clean_cron' );
        }
    }

    public function clean_session(){
        global $wpdb;
        $query = $wpdb->query("DELETE FROM ". $wpdb->prefix ."options  WHERE option_name LIKE '_catalogx_session_%'");
    }


    /**
     * Sets the php session data for the enquiry cart.
     */
    public function set_session($cart_session = array(), $can_be_empty = false) {

        if ( empty( $cart_session ) && !$can_be_empty) {
            $cart_session = $this->get_quote_cart_session();
        }
        // Set quote_cart  session data
        $this->session->set( 'quote_cart', $cart_session );
    }

    public function unset_session() {
        $this->session->__unset( 'quote_cart' );
    }

    function maybe_set_cart_cookies() {
        $set = true;

        if ( !headers_sent() ) {
            if ( sizeof( $this->quote_cart_content ) > 0 ) {
                $this->set_cart_cookies( true );
                $set = true;
            }
            elseif ( isset( $_COOKIE['quote_items_in_cart'] ) ) {
                $this->set_cart_cookies( false );
                $set = false;
            }
        }

        do_action( 'quote_set_cart_cookies', $set );
    }

    private function set_cart_cookies( $set = true ) {
        if ( $set ) {
            wc_setcookie( 'quote_items_in_cart', 1 );
            wc_setcookie( 'quote_hash', md5( json_encode( $this->quote_cart_content ) ) );
        }
        elseif ( isset( $_COOKIE['quote_items_in_cart'] ) ) {
            wc_setcookie( 'quote_items_in_cart', 0, time() - HOUR_IN_SECONDS );
            wc_setcookie( 'quote_hash', '', time() - HOUR_IN_SECONDS );
        }
    }

    public function add_to_quote_action() {
        $add_to_quote = filter_input( INPUT_GET, 'add-to-quote', FILTER_SANITIZE_NUMBER_INT );
        if ( ! $add_to_quote ) {
            return;
        }
    
        $product_id      = absint( $add_to_quote );
        $variation_id    = filter_input( INPUT_GET, 'variation_id', FILTER_SANITIZE_NUMBER_INT ) ?: '';
        $quantity        = filter_input( INPUT_GET, 'quantity', FILTER_SANITIZE_NUMBER_INT );
        $quantity        = empty( $quantity ) ? 1 : wc_stock_amount( intval( $quantity ) );
    
        $adding_to_quote = wc_get_product( $product_id );
    
        if ( ! $adding_to_quote ) {
            return;
        }
    
        $raq_data = array();
    
        if ( $adding_to_quote->is_type( 'variable' ) && $variation_id ) {
            $variation  = wc_get_product( $variation_id );
            $attributes = $variation->get_attributes();
    
            if ( ! empty( $attributes ) ) {
                foreach ( $attributes as $name => $value ) {
                    $raq_data[ 'attribute_' . $name ] = $value;
                }
            }
        }
    
        // Merge request data into array
        $raq_data = array_merge(
            array(
                'product_id'   => $product_id,
                'variation_id' => $variation_id,
                'quantity'     => $quantity,
            ),
            $raq_data
        );
    
        // Add item to quote cart
        $return = $this->add_cart_item( $raq_data );
    
        // Handle response messages
        if ( 'true' === $return ) {
            wc_add_notice( 'product_added', 'success' );
        } elseif ( 'exists' === $return ) {
            wc_add_notice( 'already_in_quote', 'notice' );
        }
    }
    

    public function add_cart_item( $cart_data ) {

        $cart_data['quantity'] = ( isset( $cart_data['quantity'] ) ) ? (int) $cart_data['quantity'] : 1;
        $return = '';
        
        do_action( 'catalogx_add_to_quote_cart', $cart_data );
        
        if ( !$this->exists_in_cart( $cart_data['product_id'] ) ) {
            $enquiry = array(
                'product_id'    => $cart_data['product_id'],
                'variation'     => $cart_data['variation'],
                'quantity'      => $cart_data['quantity']
            );

            $this->quote_cart_content[md5( $cart_data['product_id'] )] = $enquiry;
        }
        else {
            $return = 'exists';
        }

        if ( $return != 'exists' ) {
            $this->set_session( $this->quote_cart_content );
            $return = 'true';
            $this->set_cart_cookies( sizeof( $this->quote_cart_content ) > 0 );
        }
        return $return;
    }

    public function exists_in_cart( $product_id, $variation_id = false ) {
        if ( $variation_id ) {
            $key_to_find = md5( $product_id . $variation_id );
        } else {
            $key_to_find = md5( $product_id );
        }
        if ( array_key_exists( $key_to_find, $this->quote_cart_content ) ) {
            $this->errors[] = __( 'Product already in Cart.', 'catalogx' );
            return true;
        }
        return false;
    }

    public function get_cart_data() {
        return $this->quote_cart_content;
    }

    public function get_request_quote_page_url() {
        $catalogx_quote_page_id = get_option( 'catalogx_request_quote_page' );
        $base_url     = get_the_permalink( $catalogx_quote_page_id );

        return apply_filters( 'catalogx_request_quote_page_url', $base_url );
    }

    public function is_empty_cart() {
        return empty( $this->quote_cart_content );
    }

    public function remove_cart( $key ) {

        if ( isset( $this->quote_cart_content[$key] ) ) {
            unset( $this->quote_cart_content[$key] );
            $this->set_session( $this->quote_cart_content, true );
            return true;
        }
        else {
            return false;
        }
    }

    public function clear_cart() {
        $this->quote_cart_content = array();
        $this->set_session( $this->quote_cart_content, true );
    }

    public function update_cart( $key, $field = false, $value = '' ) {
        if ( $field && isset( $this->quote_cart_content[$key][$field] ) ) {
            $this->quote_cart_content[$key][$field] = $value;
            $this->set_session( $this->quote_cart_content );
        }
        elseif ( isset( $this->quote_cart_content[$key] ) ) {
            $this->quote_cart_content[$key] = $value;
            $this->set_session( $this->quote_cart_content );
        }
        else {
            return false;
        }
        $this->set_session( $this->quote_cart_content );
        return true;
    }

}