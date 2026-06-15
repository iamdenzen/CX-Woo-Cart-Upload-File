<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CXCF_File_Manager {

	private $cart_upload_dir;
	private $cart_upload_url;

	private $order_upload_dir;
	private $order_upload_url;

	public function __construct() {

		$uploads = wp_upload_dir();

		$this->cart_upload_dir = trailingslashit( $uploads['basedir'] ) . 'cart-files/';
		$this->cart_upload_url = trailingslashit( $uploads['baseurl'] ) . 'cart-files/';

		$this->order_upload_dir = trailingslashit( $uploads['basedir'] ) . 'order-files/';
		$this->order_upload_url = trailingslashit( $uploads['baseurl'] ) . 'order-files/';

		$this->ensure_directories_exist();
	}

	/**
	 * Create upload folders if they don't exist yet.
	 */
	private function ensure_directories_exist() {

		if ( ! is_dir( $this->cart_upload_dir ) ) {
			wp_mkdir_p( $this->cart_upload_dir );
		}

		if ( ! is_dir( $this->order_upload_dir ) ) {
			wp_mkdir_p( $this->order_upload_dir );
		}
	}

	/**
	 * Cart upload directory path.
	 */
	public function get_cart_upload_dir() {
		return $this->cart_upload_dir;
	}

	/**
	 * Cart upload base URL.
	 */
	public function get_cart_upload_url() {
		return $this->cart_upload_url;
	}

	/**
	 * Order upload directory path.
	 */
	public function get_order_upload_dir() {
		return $this->order_upload_dir;
	}

	/**
	 * Order upload base URL.
	 */
	public function get_order_upload_url() {
		return $this->order_upload_url;
	}

	/**
	 * Get all uploaded files for a cart item.
	 *
	 * Returns:
	 * [
	 *     1 => '/path/file.jpg',
	 *     2 => '/path/file.png'
	 * ]
	 */
	public function get_cart_files( $cart_key ) {

		$files = [];

		$pattern = $this->cart_upload_dir . $cart_key . '-*';

		foreach ( glob( $pattern ) as $file ) {

			$basename = basename( $file );

			if (
				preg_match(
					'/^' . preg_quote( $cart_key, '/' ) . '-([0-9]+)\./',
					$basename,
					$matches
				)
			) {
				$files[ (int) $matches[1] ] = $file;
			}
		}

		ksort( $files, SORT_NUMERIC );

		return $files;
	}

	/**
	 * Find the next available file index.
	 */
	public function get_next_file_index( $cart_key ) {

		$files = $this->get_cart_files( $cart_key );

		if ( empty( $files ) ) {
			return 1;
		}

		return max( array_keys( $files ) ) + 1;
	}

	/**
	 * Build a filename using the cart key and index.
	 */
	public function generate_filename( $cart_key, $index, $extension ) {

		$extension = strtolower( $extension );
		$extension = preg_replace( '/[^a-z0-9]/', '', $extension );

		return sprintf(
			'%s-%d.%s',
			$cart_key,
			$index,
			$extension ?: 'dat'
		);
	}

	/**
	 * Delete a single uploaded cart file.
	 */
	public function delete_cart_file( $cart_key, $index ) {

		$deleted = 0;

		$pattern = $this->cart_upload_dir . $cart_key . '-' . $index . '.*';

		foreach ( glob( $pattern ) as $file ) {

			if ( @unlink( $file ) ) {
				$deleted++;
			}
		}

		return $deleted;
	}

	/**
	 * Delete all files belonging to a cart item.
	 */
	public function delete_cart_files( $cart_key ) {

		$files = $this->get_cart_files( $cart_key );

		foreach ( $files as $file ) {
			@unlink( $file );
		}
	}

	/**
	 * Move cart files into the order folder.
	 */
	public function move_cart_files_to_order( $cart_key, $order_id ) {

		$files = $this->get_cart_files( $cart_key );

		if ( empty( $files ) ) {
			return [];
		}

		$order_folder = trailingslashit(
			$this->order_upload_dir . $order_id
		);

		wp_mkdir_p( $order_folder );

		$moved_files = [];

		foreach ( $files as $index => $source_path ) {

			if ( ! file_exists( $source_path ) ) {
				continue;
			}

			$filename = basename( $source_path );
			$destination = $order_folder . $filename;

			if ( rename( $source_path, $destination ) ) {

				$moved_files[] = [
					'index' => $index,
					'name'  => $filename,
					'path'  => $destination,
					'url'   => $this->order_upload_url . $order_id . '/' . $filename,
				];
			}
		}

		return $moved_files;
	}

	/**
	 * Delete all files stored for an order.
	 */
	public function delete_order_files( $order_id ) {

		$order_folder = trailingslashit(
			$this->order_upload_dir . $order_id
		);

		if ( ! is_dir( $order_folder ) ) {
			return;
		}

		foreach ( glob( $order_folder . '*.*' ) as $file ) {
			@unlink( $file );
		}

		@rmdir( $order_folder );
	}

	public function get_order_folder( $order_id ) {

        return trailingslashit(
            $this->order_upload_dir . $order_id
        );
    }

    public function get_order_files( $order_id ) {

        $files = [];

        $order_folder = $this->get_order_folder( $order_id );

        if ( ! is_dir( $order_folder ) ) {
            return $files;
        }

        foreach ( glob( $order_folder . '*.*' ) as $file ) {
            $files[] = $file;
        }

        return $files;
    }
}