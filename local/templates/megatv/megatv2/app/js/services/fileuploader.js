/* global Box */
Box.Application.addService('fileuploader', function () {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------

	function Fileuploader(target, options) {
		$(target).fileupload(options);
	}
	// Fileuploader.prototype.show = function (target) {
	// ...
	// };


	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		create: function (target, options) {
			return new Fileuploader(target, options);
		}

	};

});

