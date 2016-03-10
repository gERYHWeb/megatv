/* global Box */
Box.Application.addModule('success-reset-overlay', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var $ = context.getGlobal('jQuery');
	var moduleEl;

	function showOverlay() {
		$(moduleEl).addClass('is-visible');
	}
	function hideOverlay() {
		$(moduleEl).removeClass('is-visible');
	}

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		messages: ['showsuccessresetoverlay', 'hidesuccessresetoverlay'],

		init: function () {
			moduleEl = context.getElement();
		},
		destroy: function () {
			moduleEl = null;
		},
		onclick: function (event, element, elementType) {
			if (elementType === 'signin-handler-link') {
				event.preventDefault();
				hideOverlay();
				Box.Application.broadcast('showsigninoverlay');
			}
		},
		onmessage: function (name) {
			switch (name) {
				case 'showsuccessresetoverlay':
					showOverlay();
				break;
				case 'hidesuccessresetoverlay':
					hideOverlay();
				break;
			}
		}
	};
});

