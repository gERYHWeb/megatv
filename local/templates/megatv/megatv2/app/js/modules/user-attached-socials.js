/* global Box */
Box.Application.addModule('user-attached-socials', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var $ = context.getGlobal('jQuery');
	var moduleEl;
	var popoverService;
	var popoverParents;
	var popoverContent;

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		init: function () {
			popoverService = context.getService('popover');
			moduleEl = context.getElement();
			popoverParents = $(moduleEl).find('li:not(.active) a');
			popoverContent = context.getConfig('popoverContent');

			popoverService.create(popoverParents, {
				content: popoverContent,
				title: '',
				trigger: 'manual',
				placement: 'bottom',
				html: true
			});
			popoverParents.on('mouseenter', function () {
				$(this).popover('show');
			}).on('mouseleave', function () {
				$(this).popover('hide');
			});
		},
		destroy: function () {
			popoverService = null;
			moduleEl = null;
			popoverParents = null;
			popoverContent = null;
		}

	};
});

