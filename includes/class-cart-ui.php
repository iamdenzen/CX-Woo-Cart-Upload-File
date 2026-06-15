<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CXCF_Cart_UI {

	/**
	 * @var CXCF_File_Manager
	 */
	private $file_manager;

	public function __construct( CXCF_File_Manager $file_manager ) {

		$this->file_manager = $file_manager;

		add_action(
			'cx_cart_upload_field_here',
			[ $this, 'render_upload_field' ],
			20,
			2
		);

		// Alternative WooCommerce hook if needed later.
		// add_action(
		//     'woocommerce_after_cart_item_name',
		//     [ $this, 'render_upload_field' ],
		//     20,
		//     2
		// );
	}

	/**
	 * Render upload field for a cart item.
	 */
	public function render_upload_field( $cart_item, $cart_item_key ) {

		$files = $this->prepare_files(
			$this->file_manager->get_cart_files( $cart_item_key )
		);

		include CXCF_PLUGIN_PATH . 'templates/upload-field.php';
	}

	/**
	 * Prepare file data for the template.
	 */
	private function prepare_files( array $files ) {

		$prepared = [];

		foreach ( $files as $index => $file_path ) {

			$file_name = basename( $file_path );

			$prepared[] = [
				'index'     => $index,
				'name'      => $file_name,
				'url'       => $this->file_manager->get_cart_upload_url() . $file_name,
				'is_image'  => (bool) preg_match(
					'/\.(jpe?g|png|gif|webp)$/i',
					$file_name
				),
			];
		}

		return $prepared;
	}
}