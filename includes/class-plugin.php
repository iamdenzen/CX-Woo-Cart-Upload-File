<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CXCF_Plugin {

	private static $instance = null;

	private $file_manager;

	public static function instance() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		$this->load_files();
		$this->boot();
	}

	private function load_files() {

		require_once CXCF_PLUGIN_PATH . 'includes/class-assets.php';
		require_once CXCF_PLUGIN_PATH . 'includes/class-file-manager.php';
		require_once CXCF_PLUGIN_PATH . 'includes/class-cart-ui.php';
		require_once CXCF_PLUGIN_PATH . 'includes/class-upload-handler.php';
		require_once CXCF_PLUGIN_PATH . 'includes/class-order-handler.php';
		require_once CXCF_PLUGIN_PATH . 'includes/class-email-handler.php';
	}

	private function boot() {

		$this->file_manager = new CXCF_File_Manager();

		new CXCF_Assets();

		new CXCF_Cart_UI(
			$this->file_manager
		);

		new CXCF_Upload_Handler(
			$this->file_manager
		);

		new CXCF_Order_Handler(
			$this->file_manager
		);

		new CXCF_Email_Handler(
			$this->file_manager
		);
	}
}