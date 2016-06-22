/* global Box */
Box.Application.addModule('reset-overlay', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var $ = context.getGlobal('jQuery');
	var moduleEl;
	var pageModule;
	var adaptiveField;
	var codeField;
	var msbuttonService;
	var submitButton;
	var submitCodeButton;
	var msSubmitButton;
	var msSubmitCodeButton;
	var form;
	var steps;
	var passwordVisualizer;
	var passwordField;
	var adaptiveFieldHidden;

	function showOverlay() {
		$(moduleEl).addClass('is-visible');
		adaptiveField.trigger('focus');
		hideOtherOverlays();
	}
	function hideOverlay() {
		$(moduleEl).removeClass('is-visible');
	}
	function sendForm() {
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: form.attr('action'),
			data: form.serialize(),
			beforeSend: function () {
				removeFormErrors();
				if (form.data('type') !== 'reset-code-form') {
					if (adaptiveField.val() !== '') {
						adaptiveFieldHidden.val(adaptiveField.val());
					}
					msSubmitButton.disable();
				} else {
					msSubmitCodeButton.disable();
				}
			},
			error: function () {
				if (form.data('type') !== 'reset-code-form') {
					msSubmitButton.temporalState('fail-network-state', {
						afterChange: function () {
							this.enable();
						}
					});
				} else {
					msSubmitCodeButton.temporalState('fail-network-state', {
						afterChange: function () {
							this.enable();
						}
					});
				}
			},
			success: function (data) {
				if (data.status === 'error') {
					if (form.data('type') !== 'reset-code-form') {
						msSubmitButton.temporalState('fail-data-state', {
							afterChange: function () {
								this.enable();
							}
						});
					} else {
						msSubmitCodeButton.temporalState('fail-data-state', {
							afterChange: function () {
								this.enable();
							}
						});
					}
					var errors = data.errors;
					$.each(errors, function (key, value) {
						$('<span class="form-control-message">' + value + '</span>')
							.insertAfter($(moduleEl).find('input[name="' + key + '"]').closest('.form-group').addClass('has-error').find('.form-control'));
					});
				} else {
					if (form.data('type') !== 'reset-code-form') {
						msSubmitButton.changeState('default-state').enable();
						showNextStep();
					} else {
						msSubmitCodeButton.changeState('default-state').enable();
						hideOverlay();
						Box.Application.broadcast('showsuccessresetoverlay');
					}
				}
			}
		});
	}
	function removeFormErrors() {
		form.find('.form-group').removeClass('has-error').end().find('.form-control-message').remove();
	}
	function hideOtherOverlays() {
		Box.Application.broadcast('hidesigninoverlay');
		Box.Application.broadcast('hidesignupoverlay');
	}
	function showNextStep() {
		var activeItem = steps.find('.step.active');
		activeItem.removeClass('in');
		setTimeout(function () {
			activeItem.removeClass('active');
			activeItem.next('.step').addClass('active in');
			codeField.trigger('focus');
			reinit();
		}, 300);
	}
	function reinit() {
		form = steps.find('.step.active').find('form');
	}
	function resetOverlay() {
		adaptiveFieldHidden.val('');
		steps.find('.step').removeClass('in active').first().addClass('in active');
		form = steps.find('.step.active form');

		if (form.data('type') === 'reset-code-form') {
			adaptiveField.val('');
		}
	}

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		messages: ['showresetoverlay', 'hideresetoverlay'],

		init: function () {
			moduleEl = context.getElement();
			pageModule = $('[data-module="page"]').get(0);
			steps = $(moduleEl).find('.steps');
			form = steps.find('.step.active').find('form');
			msbuttonService = context.getService('multistate-button');
			adaptiveField = $(moduleEl).find('[data-type="adaptive-field"]');
			adaptiveFieldHidden = $(moduleEl).find('[data-type="adaptive-field-hidden"]');
			codeField = $(moduleEl).find('[data-type="code-field"]');
			passwordVisualizer = $(moduleEl).find('[data-type="password-visualizer"]');
			passwordField = $(moduleEl).find('[data-type="password-field"]');
			submitButton = $(moduleEl).find('form:not([data-type="reset-code-form"]) button:submit[data-type="multistate-button"]');
			submitCodeButton = $(moduleEl).find('form[data-type="reset-code-form"] button:submit[data-type="multistate-button"]');
			msSubmitButton = msbuttonService.create(submitButton, {
				states: ['default-state', 'fail-data-state', 'fail-network-state']
			});
			msSubmitCodeButton = msbuttonService.create(submitCodeButton, {
				states: ['default-state', 'done-state', 'fail-data-state', 'fail-network-state']
			});
		},
		destroy: function () {
			moduleEl = null;
			adaptiveField = null;
			submitButton = null;
			msbuttonService = null;
			msSubmitButton = null;
			msSubmitCodeButton = null;
			form = null;
			passwordVisualizer = null;
		},
		onmessage: function (name) {
			switch (name) {
				case 'showresetoverlay':
					showOverlay();
				break;
				case 'hideresetoverlay':
					hideOverlay();
				break;
			}
		},
		onclick: function (event, element, elementType) {
			if (elementType === 'password-visibility-toggle') {
				event.preventDefault();
				passwordVisualizer.toggleClass('is-visible');
				$(element).toggleClass('active');
			} else if (elementType === 'signin-handler-link') {
				event.preventDefault();
				hideOverlay();
				resetOverlay();
				Box.Application.broadcast('showsigninoverlay');
			} else if (elementType === 'reset-code-handler-link') {
				event.preventDefault();
				showNextStep();
			} else if  (elementType === 'reset-handler-link') {
				event.preventDefault();
				resetOverlay();
				adaptiveField.trigger('focus');
			}
		},
		onkeyup: function (event, element, elementType) {
			if (elementType === 'password-field') {
				passwordVisualizer.val($(element).val());
			}
			if (elementType === 'password-visualizer') {
				passwordField.val($(element).val());
			}
		},
		onsubmit: function (event) {
			event.preventDefault();
			sendForm();
		}
	};
});

