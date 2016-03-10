/* global Box */
Box.Application.addService('progressbar', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var $ = context.getGlobal('jQuery');
	var ProgressBar = context.getGlobal('ProgressBar');
	var circle;
	var progressbar;
	var obj;

	function Progressbar(target, options) {
		this.progress = options.progress || $(target).data('progress');
	}
	Progressbar.prototype.setProgress = function (value) {
		if (value >= 0.95) {
			this.set(1);
		} else {
			this.set(value);
		}
	};
	Progressbar.prototype.animateProgress = function (value, duration, callback) {
		if (typeof callback !== 'undefined') {
			callback = callback;
		} else {
			callback = null;
		}
		if (value >= 0.95) {
			this.animate(1, {
				duration: duration || 300
			}, callback);
		} else {
			this.animate(value, {
				duration: duration || 300
			}, callback);
		}
	};



	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		create: function (target, options) {
			progressbar = new Progressbar(target, options);
			circle = new ProgressBar.Circle(target, options);
			obj = $.extend(true, progressbar, circle);
			obj.setProgress(progressbar.progress);
			return obj;
		}

	};

});

