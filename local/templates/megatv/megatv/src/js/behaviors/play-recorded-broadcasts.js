/* global Box */
Box.Application.addBehavior('play-recorded-broadcasts', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------

	var $ = context.getGlobal('jQuery');
	var moduleEl;
	var pageModule;
	var authentication;
	var modalService;

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		init: function () {
			moduleEl = context.getElement();
			pageModule = $('[data-module="page"]').get(0);
			authentication = Box.Application.getModuleConfig(pageModule, 'authentication');
			modalService = context.getService('modal');
		},
		destroy: function () {
			moduleEl = null;
			pageModule = null;
			authentication = null;
			modalService = null;
		},
		onclick: function (event, element, elementType) {
			if (elementType === 'broadcast' && $(event.target).closest('.icon-recorded').length > 0) {
				event.preventDefault();
				if (authentication === true) {
					if ($(element).data('play-flag') === false) {
						var broadcast = $(moduleEl).find($(event.target).closest('.item'));
						var broadcastID = broadcast.data('broadcast-id');
						if (broadcastID !== '' && typeof broadcastID !== 'undefined' && broadcast.hasClass('status-recorded')) {
							// send message
							Box.Application.broadcast('playbroadcast', {
								broadcastID: broadcastID,
								record: false,
								element: element
							});
						}
					}
				} else {
					Box.Application.broadcast('showsigninoverlay');
				}
			}
		}
	};

});
