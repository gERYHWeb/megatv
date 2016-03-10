/* global Box */
Box.Application.addModule('channel-card', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var $ = context.getGlobal('jQuery');
	var moduleEl;
	var listItems;
	var kineticService;
	var timelineElements;
	var timelineService;
	var items;

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		behaviors: ['recording-broadcast', 'play-recorded-broadcasts'],

		init: function () {
			kineticService = context.getService('kinetic');
			timelineService = context.getService('timeline');
			moduleEl = context.getElement();
			listItems = $(moduleEl).find('.channel-broadcasts');
			timelineElements = $(moduleEl).find('.timeline');
			items = $(moduleEl).find('.item');

			$(moduleEl).find('[data-type="broadcast"]').data('status-flag', false).data('play-flag', false);

			kineticService.create(listItems, {
				y: false,
				triggerHardware: true,
				movingClass: {},
				deceleratingClass: {},
				filterTarget: function (target) {
					if ($(target).closest('.item-status-icon').length || $(target).closest('.details-trigger').length) {
						return false;
					}
				}
			});
			timelineElements.each(function () {
				timelineService.create(this);
			});
			$(moduleEl).on('mouseenter', '.item', function () {
				if (!$(this).hasClass('show-descr')) {
					$(this).addClass('hover');
				}
			}).on('mouseleave', '.item', function () {
				$(this).removeClass('hover');
			});
		},
		destroy: function () {
			kineticService = null;
			moduleEl = null;
			listItems = null;
			timelineElements = null;
			timelineService = null;
			items = null;
		},
		onclick: function (event, element, elementType) {
			var $item = $(event.target).closest('.item');
			if (elementType === 'descr-trigger') {
				if (!$item.hasClass('show-descr')) {
					$item.removeClass('hover').addClass('show-descr');
				} else {
					$item.removeClass('show-descr').addClass('hover');
				}
			}
		}

	};
});
