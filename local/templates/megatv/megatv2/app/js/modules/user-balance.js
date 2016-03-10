/* global Box */
Box.Application.addModule('user-balance', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var $ = context.getGlobal('jQuery');
	var moduleEl;
	var modalService;
	var paymethodModal;
	var showSuccessModal;

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		init: function () {
			moduleEl = context.getElement();
			modalService = context.getService('modal');
			showSuccessModal = context.getConfig('showSuccessModal');
			paymethodModal = modalService.create($('#paymethod-modal'), {
				backdropClass: 'modal-backdrop paymethod-backdrop',
				container: $('.site-content')
			});

			if (typeof showSuccessModal !== 'undefined' && showSuccessModal === true) {
				setTimeout(function () {
					modalService.create($('#paysuccess-modal'), {
						backdropClass: 'modal-backdrop paysuccess-backdrop',
						container: $('.site-content')
					}).show();
				}, 500);
			}
		},
		destroy: function () {
			moduleEl = null;
		},
		onclick: function (event, element, elementType) {
			if (elementType === 'paymethod-modal-handler') {
				event.preventDefault();
				paymethodModal.show();
			}
		},
		onkeyup: function (event, element, elementType) {
			if (elementType === 'paymethod-field') {
				if (parseInt($(element).val(), 10) > 0) {
					$(moduleEl).find('[data-type="paymethod-submit"]').removeAttr('disabled');
				} else {
					$(moduleEl).find('[data-type="paymethod-submit"]').attr('disabled', true);
				}
			}
		}

	};
});
