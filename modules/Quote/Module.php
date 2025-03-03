<?php

namespace CatalogX\Quote;

use CatalogX\Utill;

class Module {
    /**
     * Container contain all helper class
     * @var array
     */
    private $container = [];

    /**
     * Contain reference of the class
     * @var 
     */
    private static $instance = null;

    /**
     * Catalog class constructor function
     */
    public function __construct() {

        // Init helper classes
        $this->init_classes();

        if ( Utill::is_khali_dabba() ) {
            new \CatalogXPro\Quote\Module();
        }

        if (CatalogX()->modules->is_active('quote')) {
            $this->create_page_for_quote();
            $this->create_page_for_quote_thank_you();
        }
    
    }

    /**
     * Init helper classes
     * @return void
     */
    public function init_classes() {
        $this->container[ 'admin' ]     = new Admin();
        $this->container[ 'ajax' ]      = new Ajax();
        $this->container[ 'frontend' ]  = new Frontend();
        $this->container[ 'rest' ]      = new Rest();
		$this->container[ 'util' ]      = new Util();
	}

    /**
     * Create page for quote
     * @return void
     */
    public function create_page_for_quote() {
        // quote page
        $option_value = get_option('request_quote_page');
        if ($option_value > 0 && get_post($option_value)) {
            return;
        }

        $page_found = get_posts([
            'name' => 'request-quote',
            'post_status' => 'publish',
            'post_type' => 'page',
            'fields' => 'ids',
            'numberposts' => 1
        ]);
        if ($page_found) {
            if (!$option_value) {
                update_option('request_quote_page', $page_found[0]);
            }
            return;
        }
        $page_data = [
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_author' => 1,
            'post_name' => 'request-quote',
            'post_title' => __('Request Quote', 'catalogx'),
            'post_content' => '[request_quote]',
            'comment_status' => 'closed'
        ];
        $page_id = wp_insert_post($page_data);
        update_option('request_quote_page', $page_id);
    }

    /**
     * Create page for quote thakyou
     * @return void
     */
    function create_page_for_quote_thank_you() {
        // quote thank you page
        $option_value = get_option('request_quote_thank_you_page');
        if ($option_value > 0 && get_post($option_value)) {
            return;
        }

        $page_found = get_posts([
            'name' => 'request-quote-thank-you',
            'post_status' => 'publish',
            'post_type' => 'page',
            'fields' => 'ids',
            'numberposts' => 1
        ]);
        if ($page_found) {
            if (!$option_value) {
                update_option('request_quote_thank_you_page', $page_found[0]);
            }
            return;
        }
        $page_data = [
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_author' => 1,
            'post_name' => 'request-quote-thank-you',
            'post_title' => __('Quotation Confirmation', 'catalogx'),
            'post_content' => '[request_quote_thank_you]',
            'comment_status' => 'closed'
        ];
        $page_id = wp_insert_post($page_data);
        update_option('request_quote_thank_you_page', $page_id);
    }

    /**
     * Magic getter function to get the reference of class.
     * Accept class name, If valid return reference, else Wp_Error. 
     * @param   mixed $class
     * @return  object | \WP_Error
     */
    public function __get( $class ) {
        if ( array_key_exists( $class, $this->container ) ) {
            return $this->container[ $class ];
        }
        return new \WP_Error( sprintf('Call to unknown class %s.', $class ) );
    }

	/**
     * Initializes Catalog class.
     * Checks for an existing instance
     * And if it doesn't find one, create it.
     * @param mixed $file
     * @return object | null
     */
	public static function init() {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}