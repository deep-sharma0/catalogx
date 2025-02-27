<?php

namespace CatalogX;

defined( 'ABSPATH' ) || exit;

class Utill {
    /**
     * Constent holds table name
     * @var array
     */
    const TABLES = [
        'enquiry' => 'catalogx_enquiry',
        'rule'    => 'catalogx_rules',
        'message' => 'catalogx_messages',
    ];

    /**
     * Function to console and debug errors.
     */
    public static function log( $str ) {
        $file = CatalogX()->plugin_path . 'log/catalog.txt';

        if ( file_exists( $file ) ) {
            // Open the file to get existing content
            $str = var_export( $str, true );

            // Wp_remote_gate replacement required
            $current = file_get_contents( $file );

            if ( $current ) {
                // Append a new content to the file
                $current .= "$str" . "\r\n";
                $current .= "-------------------------------------\r\n";
            } else {
                $current = "$str" . "\r\n";
                $current .= "-------------------------------------\r\n";
            }
            
            // Write the contents back to the file
            file_put_contents( $file, $current );
        }
    }

    /**
	 * Check is Catalog Pro is active or not.
	 * @return bool
	 */
	public static function is_khali_dabba() {
		if ( defined( 'CATALOG_ENQUIRY_PRO_PLUGIN_TOKEN' ) ) {
			return CatalogX_Pro()->license->is_active();
		}
	}

    /**
     * Get other templates ( e.g. product attributes ) passing attributes and including the file.
     *
     * @access public
     * @param mixed $template_name
     * @param array $args ( default: array() )
     * @return void
     */
    public static function get_template( $template_name, $args = [] ) {

        if ( $args && is_array( $args ) )
            extract( $args );

        $located = CatalogX()->plugin_path.'templates/'.$template_name;
        
        load_template( $located, false, $args );
    }

    /**
     * Create atachment from array of fiels.
     * @param mixed $files_array
     * @return int|\WP_Error
     */
    public static function create_attachment_from_files_array($files_array) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
   
        // Handle the file upload
        $upload = wp_handle_upload($files_array, array('test_form' => false));
    
        
        // Prepare the attachment
        $file_path = $upload['file'];
        $file_name = basename($file_path);
        $file_type = wp_check_filetype($file_name, null);

        // Create attachment post
        $attachment = array(
            'guid' => $upload['url'],
            'post_mime_type' => $file_type['type'],
            'post_title' => preg_replace('/\.[^.]+$/', '', $file_name),
            'post_content' => '',
            'post_status' => 'inherit'
        );

        // Insert attachment into the media library
        $attachment_id = wp_insert_attachment($attachment, $file_path);

        if (!is_wp_error($attachment_id)) {
            // Generate metadata for the attachment, and update the attachment
            $attachment_data = wp_generate_attachment_metadata($attachment_id, $file_path);
            wp_update_attachment_metadata($attachment_id, $attachment_data);

            return $attachment_id; // Return the attachment ID
        }

        return 0;
    }

    /**
     * Check mvx is active or not
     * @return bool
     */
    public static function is_active_plugin($name = '') {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        
        if ($name == 'stock') {
            return is_plugin_active('woocommerce-product-stock-alert/product_stock_alert.php');
        }
        
        if ($name == 'mvx') {
            return is_plugin_active('dc-woocommerce-multi-vendor/dc_product_vendor.php');
        }
        
        return false;
    }

    /**
     * WPML support for language translation
     * @param mixed $context
     * @param mixed $name
     * @param mixed $default
     * @return mixed
     */
    public static function get_translated_string($context, $name, $default ) {
        if ( function_exists( 'icl_t' ) ) {
            return icl_t( $context, $name, $default );
        } else {
            return __( $default, $context );
        }
    }
} 