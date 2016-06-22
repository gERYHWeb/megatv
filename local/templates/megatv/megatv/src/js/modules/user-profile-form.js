/* global Box */
Box.Application.addModule('user-profile-form', function (context) {
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
	var dateInput;
	var dateMask;
	var phoneInput;
	var phoneMask;

	function sendForm () {
		removeFormErrors();

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
			maskedInputService = context.getService('masked-input');
			moduleEl = context.getElement();
			submitButton = $(moduleEl).find('button:submit[data-type="multistate-button"]');
			dateInput = $(moduleEl).find('input[data-type="masked-birthdate-input"]');
			phoneInput = $(moduleEl).find('input[data-type="masked-phone-input"]');
			dateMask = context.getConfig('dateMask') || '99.99.9999';
			phoneMask = context.getConfig('phoneMask') || '+7 (999) 999-99-99';

			msSubmitButton = msbuttonService.create(submitButton, {
				states: ['default-state', 'done-state', 'fail-data-state', 'fail-network-state']
			});
			maskedInputService.create(dateInput, dateMask);
			maskedInputService.create(phoneInput, phoneMask);
		},
		destroy: function () {
			msbuttonService = null;
			maskedInputService = null;
			moduleEl = null;
			submitButton = null;
			dateInput = null;
			dateMask = null;
			phoneInput = null;
			phoneMask = null;
		},
		onsubmit: function (event) {
			event.preventDefault();
			sendForm();
		}

	};
});

