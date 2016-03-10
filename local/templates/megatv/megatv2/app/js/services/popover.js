/* global Box */
Box.Application.addService('popover', function () {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------

	function Popover(target, options) {
		this.element = target;
		$(target).popover(options);
	}
	Popover.prototype.show = function (target) {
		$(target || this.element).popover('show');
	};
	Popover.prototype.hide = function (target) {
		$(target || this.element).popover('hide');
	};


	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		create: function (target, options) {
			return new Popover(target, options);
		}

	};

});

