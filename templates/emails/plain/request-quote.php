<?php
/**
 * CatalogX Email Request quote (Plain Text)
 * 
 * @author 	MultiVendorX
 * @version  6.0.0
 */
defined( 'ABSPATH' ) || exit; // Exit if accessed directly
/* translators: %s: Show the admin name. */
echo "= " . sprintf( __( 'Dear %s', 'catalogx' ), $admin ) . " =\n\n";
echo __( 'You have received a new quote request from a customer for the following product:', 'catalogx' ) . "\n\n";

// Products Table
foreach ( $products as $item ) {
    $_product = wc_get_product( $item['product_id'] );
    /* translators: %s: Show the product name. */
    echo sprintf( __( 'Product: %s', 'catalogx' ), $_product->get_title() ) . "\n";
    /* translators: %s: Show the quantity of the product. */
    echo sprintf( __( 'Qty: %s', 'catalogx' ), $item['quantity'] ) . "\n\n";
    /* translators: %s: Show the price of the product. */
    echo sprintf( __( 'Price: %s', 'catalogx' ), $_product->get_regular_price() ) . "\n\n";
}

echo "\n" . __( 'Customer Details:', 'catalogx' ) . "\n";
echo __( 'Customer Name:', 'catalogx' ) . ' ' . $customer_data['name'] . "\n";
echo __( 'Email:', 'catalogx' ) . ' ' . $customer_data['email'] . "\n\n";

if ( ! empty( $customer_data['details'] ) ) {
    echo __( 'Additional Details:', 'catalogx' ) . "\n";
    echo $customer_data['details'] . "\n";
}

do_action( 'catalogx_email_footer', $email );
