/* global Box */
Box.Application.addModule('reset-password-form', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var $ = context.getGlobal('jQuery');
	var moduleEl;
	var msbuttonService;
	var submitButton;
	var msSubmitButton;
	var maskedInputService;

	function sendForm () {
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: $(moduleEl).attr('action'),
			data: $(moduleEl).serialize(),
			beforeSend: function () {
				removeFormErrors();
				msSubmitButton.disable();
			},
			error: function () {
				msSubmitButton.temporalState('fail-network-state', {
					afterChange: function () {
						this.enable();
					}
				});
			},
			success: function (data) {
				if (data.status === 'error') {
					msSubmitButton.temporalState('fail-data-state', {
						afterChange: function () {
							this.enable();
						}
					});
					var errors = data.errors;
					$.each(errors, function (key, value) {
						$('<span class="form-control-message">' + value + '</span>')
							.insertAfter($(moduleEl).find('input[name="' + key + '"], textarea[name="' + key + '"]').closest('.form-group').addClass('has-error').find('.form-control'));
					});
				} else {
					$(moduleEl).find('.form-control').val('');
					msSubmitButton.temporalState('done-state').enable();
				}
			}
		});
	}

	function removeFormErrors() {
		$(moduleEl).find('.form-group').removeClass('has-error').end().find('.form-control-message').remove();
	}

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		init: function () {
			msbuttonService = context.getService('multistate-button');
			moduleEl = context.getElement();
			submitButton = $(moduleEl).find('button:submit[data-type="multistate-button"]');

			msSubmitButton = msbuttonService.create(submitButton, {
				states: ['default-state', 'done-state', 'fail-data-state', 'fail-network-state']
			});
		},
		destroy: function () {
			msbuttonService = null;
			maskedInputService = null;
			moduleEl = null;
			submitButton = null;
		},
		onsubmit: function (event) {
			event.preventDefault();
			sendForm();
		}

	};
});

