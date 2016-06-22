/* global Box, alert */
Box.Application.addModule('broadcasts-categories', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var $ = context.getGlobal('jQuery');
	var moduleEl;
	var list;
	var items;
	var height;

	function toggleCategories() {
		var heightCategories = list.outerHeight();

		if ( !$(moduleEl).is('.is-not-collapsing') ) {
			if ( $(moduleEl).is('.is-all-categories') ) {
				$(moduleEl)
					.removeClass('is-all-categories')
					.css('height', height+'px');
			} else {
				$(moduleEl)
					.addClass('is-all-categories')
					.css('height', heightCategories+'px');
			}
		}
	}

	function filterBroadcasts(category) {
		var broadcasts = $('.broadcasts-list .item');

		items.removeClass('active');
		$(moduleEl).find('.item[data-category="'+category+'"]').addClass('active');

		broadcasts.removeClass('is-hidden');

		if (category != 'all') {
			broadcasts.each(function(index, el) {
				var el = $(this);

				if ( el.data('category') != category ) {
					el.addClass('is-hidden');
				}
			});
		}
	}

	function state() {
		var heightCategories = list.outerHeight();

		if ( $(moduleEl).is('.is-all-categories') ) {
			$(moduleEl).css('height', heightCategories+'px');
		}

		if (heightCategories <= height) {
			$(moduleEl)
				.addClass('is-not-collapsing')
				.removeClass('is-all-categories')
				.css('height', height+'px');
		} else {
			$(moduleEl).removeClass('is-not-collapsing');
		}
	}

	$(window).resize(function(event) {
		state();
	});


	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		messages: ['categoryChanged'],

		init: function () {
			moduleEl = context.getElement();
			list = $(moduleEl).find('.items');
			items = $(moduleEl).find('.item');
			height = 60;

			state();
		},
		destroy: function () {
			moduleEl = null;
			list = null;
			items = null;
		},
		onclick: function (event, element, elementType) {
			var $item = $(event.target);
			var category = $item.data('category');

			if (elementType === 'more') {
				toggleCategories();
			} else if (elementType === 'item') {
				filterBroadcasts(category);
			}
		},

		onmessage: function(name, data) {
            if (name === 'categoryChanged') {
            	filterBroadcasts(data);
            }
        }
	};
});
