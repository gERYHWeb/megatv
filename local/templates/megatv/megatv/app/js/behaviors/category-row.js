/* global Box */
Box.Application.addBehavior('category-row', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var $ = context.getGlobal('jQuery');
	var moduleEl;
	var listItems;
	var categoryRow;
	var day;

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {
		init: function () {
			moduleEl = context.getElement();
			listItems = $(moduleEl).find('.categories-items');

			listItems.on('mouseenter', '.category-row', function (event) {
				day = $(event.target).closest('.day');

				if ($(this).length) {
					var index = listItems.find(day).find('.category-row').index($(this));
					$(moduleEl).find('.category-logo').eq(index).addClass('hover');
				}
			}).on('mouseleave', '.category-row', function () {
				$(moduleEl).find('.category-logo.hover').removeClass('hover');
			});
		},
		destroy: function () {
			moduleEl = null;
			listItems = null;
			categoryRow = null;
		}
	};

});
