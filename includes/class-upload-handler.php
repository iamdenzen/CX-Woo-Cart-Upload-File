<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CXCF_Upload_Handler {

	/**
	 * @var CXCF_File_Manager
	 */
	private $file_manager;

	public function __construct( CXCF_File_Manager $file_manager ) {

		$this->file_manager = $file_manager;

		add_action( 'wp_ajax_cx_upload_cart_file', [ $this, 'handle_upload' ] );
		add_action( 'wp_ajax_nopriv_cx_upload_cart_file', [ $this, 'handle_upload' ] );

		add_action( 'wp_ajax_cx_remove_cart_file', [ $this, 'remove_file' ] );
		add_action( 'wp_ajax_nopriv_cx_remove_cart_file', [ $this, 'remove_file' ] );
	}

	/**
	 * Upload a file for a cart item.
	 */
	public function handle_upload() {

		check_ajax_referer( 'cx_cart_files_nonce', 'nonce' );

		if ( empty( $_POST['cart_key'] ) ) {
			wp_send_json_error( 'Fehlender Warenkorb-Schlüssel' );
		}

		if ( empty( $_FILES['file'] ) ) {
			wp_send_json_error( 'Keine Datei übergeben' );
		}

		$cart_key = sanitize_text_field(
			wp_unslash( $_POST['cart_key'] )
		);

		$file = $_FILES['file'];

		// Keep the same upload limit as before.
		$max_size = 5 * 1024 * 1024;

		if ( $file['size'] > $max_size ) {
			wp_send_json_error( 'Datei zu groß (max 5MB).' );
		}

		$next_index = $this->file_manager->get_next_file_index(
			$cart_key
		);

		$extension = pathinfo(
			$file['name'],
			PATHINFO_EXTENSION
		);

		$filename = $this->file_manager->generate_filename(
			$cart_key,
			$next_index,
			$extension
		);

		$destination = $this->file_manager->get_cart_upload_dir() . $filename;

		if ( ! move_uploaded_file( $file['tmp_name'], $destination ) ) {
			wp_send_json_error(
				'Upload fehlgeschlagen (move_uploaded_file).'
			);
		}

		wp_send_json_success(
			[
				'index' => $next_index,
				'name'  => $filename,
				'url'   => $this->file_manager->get_cart_upload_url() . $filename,
			]
		);
	}

	/**
	 * Remove one uploaded file.
	 */
	public function remove_file() {

		check_ajax_referer( 'cx_cart_files_nonce', 'nonce' );

		if (
			empty( $_POST['cart_key'] ) ||
			! isset( $_POST['index'] )
		) {
			wp_send_json_error( 'Fehlende Parameter' );
		}

		$cart_key = sanitize_text_field(
			wp_unslash( $_POST['cart_key'] )
		);

		$index = absint(
			wp_unslash( $_POST['index'] )
		);

		$deleted = $this->file_manager->delete_cart_file(
			$cart_key,
			$index
		);

		wp_send_json_success(
			[
				'deleted' => $deleted,
			]
		);
	}

}