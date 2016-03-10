/* global Box */
Box.Application.addService('multistate-button', function () {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------

	function MSButton(target, options) {
		this.options = $.extend({}, this.options, options);
		this.target = target;
		this.states = $.map(this.options.states, function (val) {
			return 'is-' + val;
		});
		if (typeof this.options.initContainerClass === 'string' && this.options.initContainerClass.length !== 0) {
			$(target).addClass(this.options.initContainerClass);
		} else {
			$(target).addClass('is-inited');
		}

	}
	MSButton.prototype.changeState = function (state) {
		$(this.target).removeClass(this.states.join(' ')).addClass('is-' + state)
				.find('[class*="state"]').removeClass('init-state active').end().find('.' + state).addClass('active');
		return this;
	};
	MSButton.prototype.getState = function () {
		var stateClasses = $(this.target).find('.active, .init-state').first().attr('class').split(' ');
		for (var i = stateClasses.length; i--;) {
			if (stateClasses[i] === 'init-state' || stateClasses[i] === 'active') {
				stateClasses.splice(i, 1);
			}
		}
		return stateClasses[0];
	};
	MSButton.prototype.temporalState = function (temporalState, options) {
		var $this = this;
		var temporalOptions = $.extend({
			timer: 2500,
			oldState: $this.getState(),
			afterChange: null
		}, options);

		if (typeof temporalState === 'string') {
			$this.changeState(temporalState);
			setTimeout($.proxy(function() {
				this.changeState(temporalOptions.oldState);
				temporalOptions.afterChange.call(this);
			}, $this), temporalOptions.timer);
		} else {
			console.warn('Не задан класс для временного состояния кнопки');
		}
		return this;
	};
	MSButton.prototype.disable = function () {
		$(this.target).prop('disabled', true);
		return this;
	};
	MSButton.prototype.enable = function () {
		$(this.target).prop('disabled', false);
		return this;
	};


	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		create: function (target, options) {
			return new MSButton(target, options);
		}

	};

});

