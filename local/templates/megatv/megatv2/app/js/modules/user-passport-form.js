/* global Box */
Box.Application.addModule('user-passport-form', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var $ = context.getGlobal('jQuery');
	var moduleEl;
	var msbuttonService;
	var submitButton;
	var msSubmitButton;
	var maskedService;
	var dateInput;
	var dateMask;
	var passportSerialInput;
	var passportSerialMask;
	var passportNumberInput;
	var passportNumberMask;
	var passportCodeInput;
	var passportCodeMask;
	var maskedServicesArr = [];

	function pushMaskedService(input, mask) {
		maskedServicesArr.push(maskedService.create(input, mask));
	}

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
			maskedService = context.getService('masked-input');
			moduleEl = context.getElement();
			submitButton = $(moduleEl).find('button:submit[data-type="multistate-button"]');

			passportSerialInput = $(moduleEl).find('input[data-type="masked-passport-serial-input"]');
			passportSerialMask = context.getConfig('passportSerialMask') || '99 99';

			passportNumberInput = $(moduleEl).find('input[data-type="masked-passport-number-input"]');
			passportNumberMask = context.getConfig('passportNumberMask') || '999999';

			dateInput = $(moduleEl).find('input[data-type="masked-date-input"]');
			dateMask = context.getConfig('dateMask') || '99.99.9999';

			passportCodeInput = $(moduleEl).find('input[data-type="masked-passport-code-input"]');
			passportCodeMask = context.getConfig('passportCodeMask') || '999-999';

			pushMaskedService(passportSerialInput, passportSerialMask);
			pushMaskedService(dateInput, dateMask);
			pushMaskedService(passportNumberInput, passportNumberMask);
			pushMaskedService(passportCodeInput, passportCodeMask);

			msSubmitButton = msbuttonService.create(submitButton, {
				states: ['default-state', 'done-state', 'fail-data-state', 'fail-network-state']
			});
		},
		destroy: function () {
			msbuttonService = null;
			maskedService = null;
			moduleEl = null;
			submitButton = null;
			dateInput = null;
			dateMask = null;
			passportSerialInput = null;
			passportSerialMask = null;
			passportNumberInput = null;
			passportNumberMask = null;
			passportCodeInput = null;
			passportCodeMask = null;
			maskedServicesArr = null;
		},
		onsubmit: function (event) {
			event.preventDefault();
			sendForm();
		}

	};
});

