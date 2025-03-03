<?php

namespace CatalogX;
use CatalogX\Enquiry\Module;

defined( 'ABSPATH' ) || exit;

class Block {
    private $blocks;

    public function __construct() {
        // Register block category
        add_filter( 'block_categories_all', [$this, 'register_block_category'] );
        // Register the block
        add_action( 'init', [$this, 'register_blocks'] );
        // Localize the script for block
        add_action( 'enqueue_block_assets', [ $this,'enqueue_all_block_assets'] );

        $this->blocks = $this->initialize_blocks();
    }
    
    public function initialize_blocks() {
        $blocks = [];
        $current_user = wp_get_current_user();

        if (CatalogX()->modules->is_active('enquiry')) {
            $blocks[] = [
                'name' => 'enquiry-button', // block name
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
            $block_paths = CatalogX()->get_block_paths();
            $block_paths['blocks/enquiry-button'] = 'build/blocks/enquiry-button/index.js';
            CatalogX()->set_block_paths($block_paths);
        }

        if (CatalogX()->modules->is_active('quote')) {
            $blocks[] = [
                'name' => 'quote-button', // block name
                // src link is generated (which is append from block name) within the function
				'react_dependencies'   => ['wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n'],
                'localize' => [
					'object_name' => 'quote_button',
					'data' => [
                        'ajaxurl' => admin_url('admin-ajax.php'),
					],
				],
            ];

            $blocks[] =  [
                'name' => 'quote-cart', // block name
                // src link is generated (which is append from block name) within the function
				'react_dependencies'   => ['wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n'],
                'localize' => [
					'object_name' => 'quote_cart',
					'data' => [
                        'apiUrl' => '',
						'restUrl' => CatalogX()->rest_namespace,
						'nonce'  => wp_create_nonce('wp_rest'),
                        'name'  => $current_user->display_name,
                        'email' => $current_user->user_email
					],
				],
            ];

            $blocks[] = [
                'name' => 'quote-thank-you', // block name
                // src link is generated (which is append from block name) within the function
				'react_dependencies'   => ['wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n'],
                'localize' => [
                    'object_name' => 'quote_thank_you',
                    'data' => [
                        'quote_my_account_url'  => site_url('/my-account/all-quotes/'),
                        'khali_dabba'           => Utill::is_khali_dabba(),
                    ]
                ],
            ];

            $block_paths = CatalogX()->get_block_paths();
            $block_paths['blocks/quote-cart'] = 'build/blocks/quote-cart/index.js';
            $block_paths['blocks/quote-button'] = 'build/blocks/quote-button/index.js';
            $block_paths['blocks/quote-thank-you'] = 'build/blocks/quote-thank-you/index.js';
            CatalogX()->set_block_paths($block_paths);
        }

        return $blocks;
    }

    public function enqueue_all_block_assets() {
        global $post;
        foreach ($this->blocks as $block_script) {
            wp_set_script_translations( $block_script['name'], 'catalogx' );
            if (isset($block_script['localize']) && !empty($block_script['localize'])) {
                $block_script['localize']['data']['apiUrl'] = untrailingslashit( get_rest_url() );
                wp_localize_script('catalogx-' . $block_script['name'] . '-editor-script', $block_script['localize']['object_name'], $block_script['localize']['data']);
                wp_localize_script('catalogx-' . $block_script['name'] . '-script', $block_script['localize']['object_name'], $block_script['localize']['data']);
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
            register_block_type( CatalogX()->plugin_path . 'build/blocks/' . $block['name']);
        }
    }
    
}