<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div
	class="cx-cart-upload-wrapper"
	data-key="<?php echo esc_attr( $cart_item_key ); ?>"
>

	<label class="cx-label">
		<strong>Logo hochladen</strong>
	</label>

	<input
		type="file"
		class="cx-file-input"
		data-key="<?php echo esc_attr( $cart_item_key ); ?>"
		multiple
	>

	<div class="cx-uploaded-files">

		<?php if ( ! empty( $files ) ) : ?>

			<?php foreach ( $files as $file ) : ?>

				<div
					class="cx-file-item"
					data-index="<?php echo esc_attr( $file['index'] ); ?>"
				>

					<?php if ( $file['is_image'] ) : ?>

						<img
							src="<?php echo esc_url( $file['url'] ); ?>"
							class="cx-thumb"
							alt=""
						>

					<?php endif; ?>

					<span class="cx-file-name">
						<?php echo esc_html( $file['name'] ); ?>
					</span>

					<button
						type="button"
						class="cx-remove-file"
						data-index="<?php echo esc_attr( $file['index'] ); ?>"
					>
						X
					</button>

				</div>

			<?php endforeach; ?>

		<?php endif; ?>

	</div>

</div>