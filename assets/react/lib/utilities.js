window.jQuery(document).ready(function($) {
	const { __ } = wp.i18n;

	// Copy text
	$(document).on('click', '.tutor-copy-text', function(e) {
		// Prevent default action
		e.stopImmediatePropagation();
		e.preventDefault();

		// Get the text
		let text = $(this).data('text');

		// Create input to place texts in
		var $temp = $('<input>');
		$('body').append($temp);

		$temp.val(text).select();

		document.execCommand('copy');
		$temp.remove();

		tutor_toast(__('Copied!', 'tutor'), text, 'success');
	});

	/**
	 * Tutor Default Tab - see more
	 */
	// const seeMoreAttr = 'data-seemore-target';
	//  if (e.target.hasAttribute(seeMoreAttr)) {
	// 	 const id = e.target.getAttribute(seeMoreAttr);
	// 	 document.getElementById(`${id}`).closest('.tab-header-item-seemore').classList.toggle('is-active');
	// } else {
	// 	document.querySelectorAll('.tab-header-item-seemore').forEach((item) => {
	// 		if (item.classList.contains('is-active')) {
	// 			item.classList.remove('is-active');
	// 		}
	// 	});
	// }

	// Ajax action
	$(document).on('click', '.tutor-list-ajax-action', function(e) {
		if (!e.detail || e.detail == 1) {
			e.preventDefault();

			let $that = $(this);
			let buttonContent = $that.html();
			let prompt = $(this).data('prompt');
			let del = $(this).data('delete_element_id');
			let redirect = $(this).data('redirect_to');
			var data = $(this).data('request_data') || {};
			typeof data == 'string' ? (data = JSON.parse(data)) : 0;

			if (prompt && !window.confirm(prompt)) {
				return;
			}

			$.ajax({
				url: _tutorobject.ajaxurl,
				type: 'POST',
				data: data,
				beforeSend: function() {
					$that
						.text(__('Deleting...', 'tutor'))
						.attr('disabled', 'disabled')
						.addClass('is-loading');
				},
				success: function(data) {
					if (data.success) {
						if (del) {
							$('#' + del).fadeOut(function() {
								$(this).remove();
							});
						}

						if (redirect) {
							window.location.assign(redirect);
						}
						return;
					}

					let { message = __('Something Went Wrong!', 'tutor') } = data.data || {};
					tutor_toast('Error!', message, 'error');
				},
				error: function() {
					tutor_toast('Error!', __('Something Went Wrong!', 'tutor'), 'error');
				},
				complete: function() {
					$that
						.html(buttonContent)
						.removeAttr('disabled')
						.removeClass('is-loading');
				},
			});
		}
	});

	// Textarea auto height
	$(document).on('input', '.tutor-form-control-auto-height', function() {
		this.style.height = 'auto';
		this.style.height = this.scrollHeight + 'px';
	});
	$('.tutor-form-control-auto-height').trigger('input');

	// Prevent number input out of range
	$(document).on(
		'input',
		'input.tutor-form-control[type="number"], input.tutor-form-number-verify[type="number"]',
		function() {
			if ($(this).val() == '') {
				$(this).val('');
				return;
			}

			let min = $(this).attr('min');
			let max = $(this).attr('max');

			let val = $(this)
				.val()
				.toString();
			/\D/.test(val) ? (val = '') : 0;
			val = parseInt(val || 0);

			$(this).val(Math.abs($(this).val()));
		},
	);

	// Open location on dropdoqn change
	$(document).on('change', '.tutor-select-redirector', function() {
		let url = $(this).val();
		window.location.assign(url);
	});
});
