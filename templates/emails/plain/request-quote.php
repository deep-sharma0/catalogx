<?php
/**
 * Catalog Enquiry Email Request quote (Plain Text)
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly

echo "= " . sprintf( __( 'Dear %s', 'catalogx' ), $admin ) . " =\n\n";
echo __( 'You have received a new quote request from a customer for the following product:', 'catalogx' ) . "\n\n";

// Products Table
foreach ( $products as $item ) {
    $_product = wc_get_product( $item['product_id'] );
    echo sprintf( __( 'Product: %s', 'catalogx' ), $_product->get_title() ) . "\n";
    echo sprintf( __( 'Qty: %s', 'catalogx' ), $item['quantity'] ) . "\n\n";
    echo sprintf( __( 'Price: %s', 'catalogx' ), $_product->get_regular_price() ) . "\n\n";
}

echo "\n" . __( 'Customer Details:', 'catalogx' ) . "\n";
echo __( 'Customer Name:', 'catalogx' ) . ' ' . $customer_data['name'] . "\n";
echo __( 'Email:', 'catalogx' ) . ' ' . $customer_data['email'] . "\n\n";

if ( ! empty( $customer_data['details'] ) ) {
    echo __( 'Additional Details:', 'catalogx' ) . "\n";
    echo $customer_data['details'] . "\n";
}

do_action( 'woocommerce_email_footer', $email );
