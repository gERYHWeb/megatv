/* global Box */
Box.Application.addService('tooltip', function () {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------

	function Tooltip(target, options) {
		this.element = target;
		$(target).tooltip(options);
	}
	Tooltip.prototype.show = function (target) {
		$(target || this.element).tooltip('show');
	};
	Tooltip.prototype.hide = function (target) {
		$(target || this.element).tooltip('hide');
	};
	Tooltip.prototype.destroy = function (target) {
		$(target || this.element).tooltip('destroy');
	};


	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		create: function (target, options) {
			return new Tooltip(target, options);
		}

	};

});

