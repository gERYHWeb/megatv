/* global Box */
Box.Application.addModule('broadcast-card', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	// var $ = context.getGlobal('jQuery');
	var moduleEl;

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		behaviors: ['recording-broadcast', 'play-recorded-broadcasts'],

		init: function () {
			moduleEl = context.getElement();
			$(moduleEl).find('[data-type="broadcast"]').data('status-flag', false).data('play-flag', false);
		},
		destroy: function () {
			moduleEl = null;
		}

	};
});
