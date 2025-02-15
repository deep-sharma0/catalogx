<?php

namespace Catalogx\Quote;

class Admin {
    /**
     * Admin class constructor functions
     */
    public function __construct() {
        add_action( 'init', [$this, 'register_custom_order_status'] );
        add_filter( 'wc_order_statuses', [$this, 'add_custom_order_status_to_order_statuses'] );
        add_filter( 'wc_order_is_editable', [ $this, 'order_is_editable' ], 10, 2 );
    }

    /**
     * register the custom status
     */
    function register_custom_order_status() {
        register_post_status(
            'wc-quote-new',
            [
                'label'                     => _x( 'New Quote Request',  'Order status', 'catalogx' ),
                'public'                    => true,
                'exclude_from_search'       => false,
                'show_in_admin_all_list'    => true,
                'show_in_admin_status_list' => true,
                'label_count'               => _n_noop( 'New Quote Request <span class="count">(%s)</span>', 'New Quote Requests <span class="count">(%s)</span>', 'catalogx' ),
            ]
        );
    
        register_post_status(
            'wc-quote-pending',
            [
                'label'                     => _x( 'Pending Quote',  'Order status', 'catalogx' ),
                'public'                    => true,
                'exclude_from_search'       => false,
                'show_in_admin_all_list'    => true,
                'show_in_admin_status_list' => true,
                'label_count'               => _n_noop( 'Pending Quote <span class="count">(%s)</span>', 'Pending Quote <span class="count">(%s)</span>', 'catalogx' ),
            ]
        );
    
        register_post_status(
            'wc-quote-expired',
            [
                'label'                     => _x( 'Expired Quote',  'Order status', 'catalogx' ),
                'public'                    => true,
                'exclude_from_search'       => false,
                'show_in_admin_all_list'    => true,
                'show_in_admin_status_list' => true,
                'label_count'               => _n_noop( 'Expired Quote <span class="count">(%s)</span>', 'Expired Quotes <span class="count">(%s)</span>', 'catalogx' ),
            ]
        );
    
        register_post_status(
            'wc-quote-accepted',
            [
                'label'                     => _x( 'Accepted Quote',  'Order status', 'catalogx' ),
                'public'                    => true,
                'exclude_from_search'       => false,
                'show_in_admin_all_list'    => true,
                'show_in_admin_status_list' => true,
                'label_count'               => _n_noop( 'Accepted Quote <span class="count">(%s)</span>', 'Accepted Quote <span class="count">(%s)</span>', 'catalogx' ),
            ]
        );
    
        register_post_status(
            'wc-quote-rejected',
            [
                'label'                     => _x( 'Rejected Quote',  'Order status', 'catalogx'  ),
                'public'                    => true,
                'exclude_from_search'       => false,
                'show_in_admin_all_list'    => true,
                'show_in_admin_status_list' => true,
                'label_count'               => _n_noop( 'Rejected Quote <span class="count">(%s)</span>', 'Rejected Quote <span class="count">(%s)</span>', 'catalogx' ),
            ]
        );
    }

    /**
     * merge new status and old status
     */
    function add_custom_order_status_to_order_statuses( $order_statuses_old ) {
        $order_statuses['wc-quote-new']      = _x( 'New Quote Request', 'Order status', 'catalogx' );
        $order_statuses['wc-quote-pending']  = _x( 'Pending Quote', 'Order status', 'catalogx' );
        $order_statuses['wc-quote-expired']  = _x( 'Expired Quote', 'Order status', 'catalogx' );
        $order_statuses['wc-quote-accepted'] = _x( 'Accepted Quote', 'Order status', 'catalogx' );
        $order_statuses['wc-quote-rejected'] = _x( 'Rejected Quote', 'Order status', 'catalogx' );
        // return $order_statuses;

        $request = $_REQUEST; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( (isset( $request['new_quote'] ) && $request['new_quote'] && ( isset($request['page']) && 'wc-orders' === $request['page'])) ||
		 (isset( $request['new_quote'] ) && $request['new_quote'] && isset($request['post_type']) && 'shop_order' === $request['post_type']) ) {
			$new_status = array_merge( $order_statuses, $order_statuses_old );
		} else {
			$new_status = array_merge( $order_statuses_old, $order_statuses );
		}

		return $new_status;
    }

    /**
     * make the order is editable in order-edit page
     * @return void
     */
    public function order_is_editable( $editable, $order ) {
        $accepted_statuses = [
                'quote-new',
                'quote-accepted',
                'quote-pending',
                'quote-expired',
                'quote-rejected',
            ];

        if ( in_array( $order->get_status(), $accepted_statuses, true ) ) {
            return true;
        }

        return $editable;
    }
}