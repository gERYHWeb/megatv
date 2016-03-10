/* global Box, alert */
Box.Application.addModule('broadcast-user-comments-form', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var $ = context.getGlobal('jQuery');
	var moduleEl;
	var form;

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		init: function () {
			moduleEl = context.getElement();
			form = $(moduleEl).find('form');
		},
		destroy: function () {
			moduleEl = null;
			form = null;
		},
		onsubmit: function (event) {
			event.preventDefault();

			$(moduleEl).find('.form-group').removeClass('has-error');

			$.ajax({
				type: 'POST',
				dataType: 'json',
				url: form.attr('action'),
				data: form.serialize(),
				error: function () {
					alert('Что-то пошло не так. Повторите попытку позднее!');
				},
				success: function (data) {
					if (data.status === false) {
						var errors = data.errors;
						$.each(errors, function (key, value) {
							$('<span class="form-control-message">' + value + '</span>')
								.insertAfter(form.find('input[name="' + key + '"], textarea[name="' + key + '"]').closest('.form-group').addClass('has-error').find('.form-control'));
						});
					} else {
						console.log(form);
						form.find('.form-control').val('');
					}
				}
			});
		}

	};
});
