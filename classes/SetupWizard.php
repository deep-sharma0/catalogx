<?php

namespace CatalogX;

/**
 * Setup Wizard Class
 * 
 */
if (!defined('ABSPATH')) {
    exit;
}

class SetupWizard {

    public function __construct() {

        add_action( 'admin_menu', [$this, 'admin_menus'] );
        add_action( 'admin_enqueue_scripts', [ $this, 'admin_scripts'] );
    }

    /**
     * Add admin menus/screens.
     */
    public function admin_menus() {
        add_dashboard_page('', '', 'manage_options', 'catalog-setup', [$this, 'render_setup_wizard']);
    }
    
    function render_setup_wizard() {
        ?>
        <div id="catalog_setup_wizard">
        </div>
        <?php
    }
    
    function admin_scripts() {
        $current_screen = get_current_screen();

        if ( $current_screen->id === 'dashboard_page_catalog-setup' ) {
            wp_enqueue_script('setup_wizard_js', CatalogX()->plugin_url . 'build/blocks/setupWizard/index.js', [ 'jquery', 'jquery-blockui', 'wp-element', 'wp-i18n', 'react-jsx-runtime'  ], CatalogX()->version, true);
            wp_set_script_translations( 'setup_wizard_js', 'catalogx' );
            wp_enqueue_style('setup_wizard_css', CatalogX()->plugin_url . 'build/blocks/setupWizard/index.css');
            wp_localize_script(
                'setup_wizard_js', 'appLocalizer', [
                'apiurl' => untrailingslashit(get_rest_url()),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'restUrl' => 'catalog/v1',
                'redirect_url' => admin_url() . 'admin.php?page=catalogx#&tab=modules',
            ]);
        }
    }

}
