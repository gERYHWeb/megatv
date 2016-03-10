/* global Box */
Box.Application.addModule('signin-overlay', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var $ = context.getGlobal('jQuery');
	var moduleEl;
	var pageModule;
	var passwordVisualizer;
	var adaptiveField;
	var msbuttonService;
	var submitButton;
	var msSubmitButton;
	var form;

	function showOverlay() {
		$(moduleEl).addClass('is-visible');
		adaptiveField.trigger('focus');
		hideOtherOverlays();
	}
	function hideOverlay() {
		$(moduleEl).removeClass('is-visible');
	}
	function toggleOverlay() {
		$(moduleEl).toggleClass('is-visible');

		if ($(moduleEl).hasClass('is-visible') === true) {
			adaptiveField.trigger('focus');
			hideOtherOverlays();
		}
	}
	function sendForm() {
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: form.attr('action'),
			data: form.serialize(),
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
							.insertAfter($(moduleEl).find('input[name="' + key + '"]').closest('.form-group').addClass('has-error').find('.form-control'));
					});
				} else {
					msSubmitButton.changeState('done-state').enable();
					window.location.href = form.data('redirect');
				}
			}
		});
	}
	function removeFormErrors() {
		form.find('.form-group').removeClass('has-error').end().find('.form-control-message').remove();
	}
	function hideOtherOverlays() {
		Box.Application.broadcast('hideresetoverlay');
		Box.Application.broadcast('hidesignupoverlay');
	}

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		messages: ['showsigninoverlay', 'hidesigninoverlay', 'togglesigninoverlay'],

		init: function () {
			moduleEl = context.getElement();
			pageModule = $('[data-module="page"]').get(0);
			form = $(moduleEl).find('form');
			msbuttonService = context.getService('multistate-button');
			adaptiveField = $(moduleEl).find('[data-type="adaptive-field"]');
			passwordVisualizer = $(moduleEl).find('[data-type="password-visualizer"]');
			submitButton = $(moduleEl).find('button:submit[data-type="multistate-button"]');
			msSubmitButton = msbuttonService.create(submitButton, {
				states: ['default-state', 'done-state', 'fail-data-state', 'fail-network-state']
			});
		},
		destroy: function () {
			moduleEl = null;
			passwordVisualizer = null;
			adaptiveField = null;
			submitButton = null;
			msbuttonService = null;
			msSubmitButton = null;
			form = null;
		},
		onmessage: function (name) {
			switch (name) {
				case 'showsigninoverlay':
					showOverlay();
				break;
				case 'hidesigninoverlay':
					hideOverlay();
				break;
				case 'togglesigninoverlay':
					toggleOverlay();
				break;
			}
		},
		onclick: function (event, element, elementType) {
			if (elementType === 'password-visibility-toggle') {
				event.preventDefault();
				passwordVisualizer.toggleClass('is-visible');
				$(element).toggleClass('active');
			} else if (elementType === 'social-signin-link') {
				msSubmitButton.changeState('done-state');
			} else if (elementType === 'reset-handler-link') {
				event.preventDefault();
				hideOverlay();
				Box.Application.broadcast('showresetoverlay');
			}
		},
		onkeyup: function (event, element, elementType) {
			if (elementType === 'password-field') {
				$(moduleEl).find('[data-type="password-visualizer"]').val($(element).val());
			}
			if (elementType === 'password-visualizer') {
				$(moduleEl).find('[data-type="password-field"]').val($(element).val());
			}
		},
		onsubmit: function (event) {
			event.preventDefault();
			sendForm();
		}
	};
});

