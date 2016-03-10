/* global Box */
Box.Application.addService('masked-input', function () {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------

	function MaskedInput(target, mask, options) {
		$(target).mask(mask, options);
	}
	// MaskedInput.prototype.show = function (target) {
	// ...
	// };


	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		create: function (target, mask, options) {
			return new MaskedInput(target, mask, options);
		}

	};

});

