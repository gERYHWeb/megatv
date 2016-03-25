/* global Box, alert */
Box.Application.addModule('user-recorded-broadcasts-categories', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var $ = context.getGlobal('jQuery');
	var moduleEl;
	var list;
	var items;

	function toggleCategories() {
		var heightCategories = $(list).outerHeight();

		if ( $(moduleEl).hasClass('is-all-categories') ) {
			$(moduleEl)
				.removeClass('is-all-categories')
				.css('height', '60px');
		} else {
			$(moduleEl)
				.addClass('is-all-categories')
				.css('height', heightCategories+'px')
		}
	}

	function filterBroadcasts(category) {
		var allBroadcasts = $('.user-recorded-broadcasts .item');

		allBroadcasts.removeClass('is-hidden');

		if (category != 'all') {
			$('.user-recorded-broadcasts .item').each(function(index, el) {
				var el = $(this);

				if ( el.data('category') != category ) {
					el.addClass('is-hidden');
				}
			});
		}
	}


	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		init: function () {
			moduleEl = context.getElement();
			list = $(moduleEl).find('.items');
			items = $(moduleEl).find('.item');
		},
		destroy: function () {
			moduleEl = null;
			list = null;
			items = null;
		},
		onclick: function (event, element, elementType) {
			var $item = $(event.target);
			var broadcastCategory = $item.data('category');

			if (elementType === 'more') {
				toggleCategories();
			} else if (elementType === 'item') {
				items.removeClass('active');
				$(element).addClass('active');
				filterBroadcasts(broadcastCategory);
			}
		}
	};
});
