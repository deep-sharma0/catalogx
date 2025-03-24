<?php

namespace CatalogX;

defined( 'ABSPATH' ) || exit;

/**
 * CatalogX Block class
 *
 * @class 		Block class
 * @version		6.0.0
 * @author 		MultivendorX
 */
class Block {
    private $blocks;

    public function __construct() {
        $this->blocks = $this->initialize_blocks();
        // Register block category
        add_filter( 'block_categories_all', [$this, 'register_block_category'] );
        // Register the block
        add_action( 'init', [$this, 'register_blocks'] );
        // Localize the script for block
        add_action( 'enqueue_block_assets', [ $this,'enqueue_all_block_assets'] );

    }
    
    public function initialize_blocks() {
        $blocks = [];
        $current_user = wp_get_current_user();

        if (CatalogX()->modules->is_active('enquiry')) {
            $blocks[] = [
                'name' => 'enquiry-button', // block name
                'textdomain' => 'catalogx',
                'block_path' => CatalogX()->plugin_path . 'build/blocks/',
                // src link is generated (which is append from block name) within the function
                'react_dependencies'   => ['wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n'], // the react dependencies which required in js
                'localize' => [
                    'object_name' => 'enquiryButton', // the localized variable name
                    // all the data that is required in index.js
                    'data' => [
                        'apiUrl'  => '', // this set blank because in scope the get_rest_url() is not defined
                        'restUrl' => CatalogX()->rest_namespace,
                        'nonce'   => wp_create_nonce( 'catalog-security-nonce' )
                    ],
                ],
            ];

            //this path is set for load the translation   
            CatalogX()->block_paths += ['blocks/enquiry-button' => 'build/blocks/enquiry-button/index.js'];
        }

        if (CatalogX()->modules->is_active('quote')) {
            $blocks[] = [
                'name' => 'quote-button', // block name
                'textdomain' => 'catalogx',
                'block_path' => CatalogX()->plugin_path . 'build/blocks/',
                // src link is generated (which is append from block name) within the function
                'react_dependencies'   => ['wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n'],
                'localize' => [
                    'object_name' => 'quoteButton',
                    'data' => [
                        'apiUrl'  => '', // this set blank because in scope the get_rest_url() is not defined
                        'restUrl' => CatalogX()->rest_namespace,
                        'nonce'   => wp_create_nonce( 'catalog-security-nonce' )
                    ],
                ],
            ];

            $blocks[] =  [
                'name' => 'quote-cart', // block name
                'textdomain' => 'catalogx',
                'block_path' => CatalogX()->plugin_path . 'build/blocks/',
                // src link is generated (which is append from block name) within the function
                'react_dependencies'   => ['wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n'],
                'localize' => [
                    'object_name' => 'quoteCart',
                    'data' => [
                        'apiUrl' => '',
                        'restUrl' => CatalogX()->rest_namespace,
                        'nonce'  => wp_create_nonce('wp_rest'),
                        'name'  => $current_user->display_name,
                        'email' => $current_user->user_email,
                        'quote_my_account_url'  => site_url('/my-account/all-quotes/'),
                        'khali_dabba'           => Utill::is_khali_dabba(),
                    ],
                ],
            ];

            //this path is set for load the translation
            CatalogX()->block_paths += [
                'blocks/quote-cart' => 'build/blocks/quote-cart/index.js',
                'blocks/quote-button' => 'build/blocks/quote-button/index.js',
            ];            
        }

        return apply_filters('catalogx_initialize_blocks', $blocks);
    }

    public function enqueue_all_block_assets() {
        foreach ($this->blocks as $block_script) {
            wp_set_script_translations( $block_script['name'], $block_script['textdomain'] );
            if (isset($block_script['localize']) && !empty($block_script['localize'])) {
                // apiUrl re-initialize here beacuse in array the url is not define
                $block_script['localize']['data']['apiUrl'] = untrailingslashit( get_rest_url() );
                wp_localize_script($block_script['textdomain'] . '-' . $block_script['name'] . '-editor-script', $block_script['localize']['object_name'], $block_script['localize']['data']);
                wp_localize_script($block_script['textdomain'] . '-' . $block_script['name'] . '-script', $block_script['localize']['object_name'], $block_script['localize']['data']);
            }
        }
    }

    public function register_block_category($categories) {
        // Adding a new category.
        $categories[] = [
            'slug'  => 'catalogx',
            'title' => 'CatalogX'
        ];
        return $categories;
    }
    
    public function register_blocks() {
        foreach ($this->blocks as $block) {
            register_block_type( $block['block_path'] . $block['name']);
        }
    }
    
}