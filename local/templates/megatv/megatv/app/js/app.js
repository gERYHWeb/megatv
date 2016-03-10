/* global Box */
'use strict';

jQuery.extend(jQuery.lazyLoadXT, {
  edgeX: 800,
  throttle: 50
});

// add easing for animation
jQuery.easing.jswing = jQuery.easing.swing;

jQuery.extend(jQuery.easing, {
	def: 'easeOutQuad',
	swing: function (x, t, b, c, d) {
		return jQuery.easing[jQuery.easing.def](x, t, b, c, d);
	},
	easeOutQuad: function (x, t, b, c, d) {
		return -c *(t/=d)*(t-2) + b;
	},
	easeInOutCubic: function (x, t, b, c, d) {
		if ((t/=d/2) < 1) {
			return c/2*t*t*t + b;
		}
		return c/2*((t-=2)*t*t + 2) + b;
	}
});


Box.Application.init({ debug: true });

$(function () {
	$('.recommend-form-wrap select').select2({
		theme: 'default'
	});
});
