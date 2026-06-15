<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CXCF_Order_Handler {

	/**
	 * @var CXCF_File_Manager
	 */
	private $file_manager;

	public function __construct( CXCF_File_Manager $file_manager ) {

		$this->file_manager = $file_manager;

		add_action(
			'woocommerce_checkout_create_order_line_item',
			[ $this, 'store_cart_item_key' ],
			10,
			4
		);

		add_action(
			'woocommerce_checkout_order_processed',
			[ $this, 'attach_files_to_order' ],
			99,
			3
		);

		add_action(
			'woocommerce_after_order_itemmeta',
			[ $this, 'render_admin_files' ],
			10,
			2
		);

		add_action(
			'before_delete_post',
			[ $this, 'delete_order_files' ]
		);

		add_action(
			'woocommerce_remove_cart_item',
			[ $this, 'delete_cart_files' ],
			20,
			2
		);

		add_action(
			'woocommerce_cart_item_removed',
			[ $this, 'delete_cart_files_legacy' ],
			10,
			1
		);
	}

	/**
	 * Store cart item key so we can access uploaded
	 * files once the order is created.
	 */
	public function store_cart_item_key(
		$item,
		$cart_item_key,
		$values,
		$order
	) {

		$item->add_meta_data(
			'_wc_cart_item_key',
			$cart_item_key,
			true
		);
	}

	/**
	 * Move uploaded files into the order folder
	 * and attach them to the order item.
	 */
	public function attach_files_to_order(
		$order_id,
		$post_data,
		$order
	) {

		foreach ( $order->get_items() as $item ) {

			$cart_item_key = $item->get_meta(
				'_wc_cart_item_key',
				true
			);

			if ( empty( $cart_item_key ) ) {
				continue;
			}

			$files = $this->file_manager->move_cart_files_to_order(
				$cart_item_key,
				$order_id
			);

			if ( empty( $files ) ) {
				continue;
			}

			$item->add_meta_data(
				'Uploaded Files',
				$files
			);

			$item->delete_meta_data(
				'_wc_cart_item_key'
			);

			$item->save();
		}
	}

	/**
	 * Show uploaded files inside the admin order screen.
	 */
	public function render_admin_files(
		$item_id,
		$item
	) {

		$files = $item->get_meta(
			'Uploaded Files',
			true
		);

		if ( empty( $files ) ) {
			return;
		}

		echo '<div class="cx-order-files">';
		echo '<strong>Hochgeladene Dateien:</strong><br>';

		foreach ( $files as $file ) {

			$url  = esc_url( $file['url'] );
			$name = esc_html( $file['name'] );

			echo sprintf(
				'<a href="%s" target="_blank">%s</a><br>',
				$url,
				$name
			);
		}

		echo '</div>';
	}

	/**
	 * Remove uploaded cart files when a cart item
	 * is deleted.
	 */
	public function delete_cart_files(
		$cart_item_key,
		$cart
	) {

		$this->file_manager->delete_cart_files(
			$cart_item_key
		);
	}

	/**
	 * Older WooCommerce versions use this hook.
	 */
	public function delete_cart_files_legacy(
		$cart_item_key
	) {

		$this->file_manager->delete_cart_files(
			$cart_item_key
		);
	}

	/**
	 * Remove order uploads when the order is deleted.
	 */
	public function delete_order_files(
		$post_id
	) {

		if ( get_post_type( $post_id ) !== 'shop_order' ) {
			return;
		}

		$this->file_manager->delete_order_files(
			$post_id
		);
	}
}