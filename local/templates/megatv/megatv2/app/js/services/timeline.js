/* global Box */
Box.Application.addService('timeline', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var $ = context.getGlobal('jQuery');

	function Timeline(elem) {
		var $elem = $(elem);
		var progress = $elem.data('progress');

		$elem.find('.progress-bg').css({
			width: progress + '%'
		});
	}

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {
		create: function (elem) {
			return new Timeline(elem);
		}
	};

});

