/* global Box */
Box.Application.addService('select', function () {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------

	function Select(target, options) {
		this.element = target;
		$(target).select2(options);
	}
	Select.prototype.open = function (target) {
		$(target || this.element).select2('open');
	};
	Select.prototype.close = function (target) {
		$(target || this.element).select2('close');
	};

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		create: function (target, options) {
			return new Select(target, options);
		}

	};

});

