/* global Box */
Box.Application.addService('kinetic', function () {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	$.Kinetic.prototype.animateScrollTo = function (movingPos) {
		var $scroller = this._getScroller();
		$scroller.addClass('kinetic-moving');
		if (typeof movingPos === 'number') {
			$scroller.stop().animate({
				scrollLeft: movingPos
			}, 800, 'easeInOutCubic');
			this.settings.scrollLeft = movingPos;
			setTimeout(function () {
				$(window).trigger('scroll'); // hack for jquery.lazyLoadXT
				setTimeout(function() {
					$scroller.removeClass('kinetic-moving');
				}, 100);
			}, 800);
		}
	};
	$.Kinetic.prototype.animateScroll = function (movingOptions) {
		var $scroller = this._getScroller();
		var scrollLeft = this.scrollLeft();
		$scroller.addClass('kinetic-moving');
		if (typeof movingOptions.movingDistance === 'number' && typeof movingOptions.direction === 'string') {
			if (movingOptions.direction === 'left') {
				scrollLeft = scrollLeft - movingOptions.movingDistance;
			} else if (movingOptions.direction === 'right') {
				scrollLeft = scrollLeft + movingOptions.movingDistance;
			}
			$scroller.stop().animate({
				scrollLeft: scrollLeft
			}, 800, 'easeInOutCubic');
			this.settings.scrollLeft = scrollLeft;
			setTimeout(function () {
				$(window).trigger('scroll'); // hack for jquery.lazyLoadXT
				$scroller.removeClass('kinetic-moving');
			}, 800);
		}
	};

	function Kinetic(target, options) {
		this.target = target;
		this.options = options;
		$(target).kinetic(options);
	}
	Kinetic.prototype.moveTo = function (movingPos, callback) {
		$(this.target).kinetic('animateScrollTo', movingPos);
		if (typeof callback === 'function') {
			setTimeout(callback, 900);
		}
	};
	Kinetic.prototype.move = function (direction, movingDistance) {
		$(this.target).kinetic('animateScroll', {
			direction: direction,
			movingDistance: movingDistance
		});
	};

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {
		create: function (target, options) {
			return new Kinetic(target, options);
		}
	};

});

