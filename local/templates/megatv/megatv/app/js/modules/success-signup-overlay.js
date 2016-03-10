/* global Box */
Box.Application.addModule('success-signup-overlay', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var $ = context.getGlobal('jQuery');
	var moduleEl;

	function showOverlay() {
		$(moduleEl).addClass('is-visible');
	}

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		messages: ['showsuccesssignupoverlay'],

		init: function () {
			moduleEl = context.getElement();
		},
		destroy: function () {
			moduleEl = null;
		},
		onmessage: function (name) {
			switch (name) {
				case 'showsuccesssignupoverlay':
					showOverlay();
					setTimeout(function () {
						window.location = '/';
					}, 3000);
				break;
			}
		}
	};
});

