<?php
/**
 * Plugin Name: CX Cart File Uploads
 * Description: Allows users to upload files per cart item and keeps them attached to the order.
 * Version: 2.0.0
 * Author: Creatricx
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CXCF_VERSION', '2.0.0' );
define( 'CXCF_PLUGIN_FILE', __FILE__ );
define( 'CXCF_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'CXCF_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once CXCF_PLUGIN_PATH . 'includes/class-plugin.php';

CXCF_Plugin::instance();