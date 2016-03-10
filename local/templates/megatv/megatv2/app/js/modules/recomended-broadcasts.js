/* global Box */
Box.Application.addModule('recomended-broadcasts', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var $ = context.getGlobal('jQuery');
	var moduleEl;
	var listItems;
	var kineticService;

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		behaviors: ['recording-broadcast', 'play-recorded-broadcasts'],

		init: function () {
			kineticService = context.getService('kinetic');
			moduleEl = context.getElement();
			listItems = $(moduleEl).find('.block-body');
			$(moduleEl).find('[data-type="broadcast"]').data('status-flag', false).data('play-flag', false);
			kineticService.create(listItems, {
				y: false,
				triggerHardware: true,
				movingClass: {},
				deceleratingClass: {},
				filterTarget: function (target) {
					if ($(target).closest('.item-status-icon').length) {
						return false;
					}
				}
			});
		},
		destroy: function () {
			kineticService = null;
			moduleEl = null;
			listItems = null;
		}

	};
});

