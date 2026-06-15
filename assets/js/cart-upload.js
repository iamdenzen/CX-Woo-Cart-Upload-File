jQuery(function($){

	// on file selection (multiple allowed)
	$(document).on('change', '.cx-file-input', function(){
		let key = $(this).data('key');
		let files = this.files;
		if (!files || files.length === 0) return;

		// iterate files and upload each separately (keeps index logic simple)
		for (let i = 0; i < files.length; i++) {
			let file = files[i];
			let formData = new FormData();
			formData.append('action', 'cx_upload_cart_file');
			formData.append('cart_key', key);
			formData.append('nonce', CXCF.nonce);
			formData.append('file', file);

			// optional: show uploading placeholder
			let placeholder = $('<div class="cx-file-item uploading"><span>Hochladen: '+file.name+'</span></div>');
			$(`.cx-cart-upload-wrapper[data-key="${key}"] .cx-uploaded-files`).append(placeholder);

			$.ajax({
				url: CXCF.ajax,
				type: 'POST',
				data: formData,
				contentType: false,
				processData: false,
				dataType: 'json'
			}).done(function(resp){
				placeholder.remove();
				if (resp && resp.success) {
					let item = `
						<div class="cx-file-item" data-index="${resp.data.index}">
							<img src="${resp.data.url}" class="cx-thumb">
							<span class="cx-file-name">${resp.data.name}</span>
							<button class="cx-remove-file" data-index="${resp.data.index}">X</button>
						</div>
					`;
					$(`.cx-cart-upload-wrapper[data-key="${key}"] .cx-uploaded-files`).append(item);
				} else {
					let msg = resp && resp.data ? resp.data : (resp && resp.message ? resp.message : 'Upload fehlgeschlagen');
					let err = $('<div class="cx-file-item error"><span>Fehler: '+msg+'</span></div>');
					$(`.cx-cart-upload-wrapper[data-key="${key}"] .cx-uploaded-files`).append(err);
				}
			}).fail(function(xhr){
				placeholder.remove();
				$(`.cx-cart-upload-wrapper[data-key="${key}"] .cx-uploaded-files`).append('<div class="cx-file-item error"><span>Upload Fehler</span></div>');
			});
		}

		// clear value so same file can be reselected if needed
		$(this).val('');
	});

	// remove file
	$(document).on('click', '.cx-remove-file', function(e){
		e.preventDefault();
		let index = $(this).data('index');
		let wrapper = $(this).closest('.cx-cart-upload-wrapper');
		let key = wrapper.data('key');

		$.post(CXCF.ajax, {
			action: 'cx_remove_cart_file',
			cart_key: key,
			index: index,
			nonce: CXCF.nonce
		}, function(resp){
			if (resp && resp.success) {
				wrapper.find(`.cx-file-item[data-index="${index}"]`).remove();
			} else {
				alert('Löschen fehlgeschlagen');
			}
		});
	});

});
