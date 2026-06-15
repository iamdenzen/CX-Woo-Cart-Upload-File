<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CXCF_Assets {

	public function __construct() {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
	}

	/**
	 * Load frontend assets only where needed.
	 */
	public function enqueue_assets() {

		if ( ! is_cart() && ! is_checkout() ) {
			return;
		}

		wp_enqueue_script(
			'cx-cart-files',
			CXCF_PLUGIN_URL . 'assets/js/cart-upload.js',
			[ 'jquery' ],
			CXCF_VERSION,
			true
		);

		wp_localize_script(
			'cx-cart-files',
			'CXCF',
			[
				'ajax'  => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'cx_cart_files_nonce' ),
			]
		);

		wp_enqueue_style(
			'cx-cart-files-style',
			CXCF_PLUGIN_URL . 'assets/css/cart-upload.css',
			[],
			CXCF_VERSION
		);
	}
}