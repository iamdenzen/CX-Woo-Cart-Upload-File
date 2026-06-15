<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CXCF_Email_Handler {

	/**
	 * @var CXCF_File_Manager
	 */
	private $file_manager;

	/**
	 * Email IDs that should receive attachments.
	 */
	private $allowed_emails = [
		'new_order',
		'customer_processing_order',
	];

	public function __construct( CXCF_File_Manager $file_manager ) {

		$this->file_manager = $file_manager;

		add_filter(
			'woocommerce_email_attachments',
			[ $this, 'attach_files' ],
			10,
			3
		);
	}

	/**
	 * Attach uploaded files to selected WooCommerce emails.
	 */
	public function attach_files(
		$attachments,
		$email_id,
		$order
	) {

		if ( ! $order instanceof WC_Order ) {
			return $attachments;
		}

		if ( ! in_array( $email_id, $this->allowed_emails, true ) ) {
			return $attachments;
		}

		foreach (
            $this->file_manager->get_order_files( $order->get_id() )
            as $file
        ) {
            $attachments[] = $file;
        }

		return $attachments;
	}
}