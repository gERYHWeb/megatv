/* global Box */
Box.Application.addService('icon-loader', function () {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var moduleEl;
	var contextIcons;

	function icon(name, options) {
		var optionsz = options || {};
		var size    = optionsz.size ? 'is-' + optionsz.size : '';
		var klass   = 'icon ' + name + ' ' + size + '' + (optionsz.class || '');

		var iconz   =	'<svg class="icon__cnt">' +
						'<use xlink:href="#' + name + '" />' +
						'</svg>';

		var html =  '<div class="' + klass + '">' +
						wrapSpinner(iconz, klass) +
					'</div>';

		return html;
	}
	function wrapSpinner(html, klass) {
		if (klass.indexOf('spinner') > -1) {
			return '<div class="icon__spinner">' + html + '</div>';
		} else {
			return html;
		}
	}

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		renderIcons: function (inst) {
			if (inst) {
				moduleEl = inst.getElement();
				contextIcons = moduleEl.querySelectorAll('[data-icon]');
			}
			var icons = inst ? contextIcons : document.querySelectorAll('[data-icon]');

			for (var i = 0; i < icons.length; i++) {
				var currentIcon = icons[i];
				var name        = currentIcon.getAttribute('data-icon');
				var options = {
					class:  currentIcon.className,
					size:   currentIcon.getAttribute('data-size')
				};

				currentIcon.insertAdjacentHTML('beforebegin', icon(name, options));
				currentIcon.parentNode.removeChild(currentIcon);
			}
		},
		renderSprite: function (path) {
			var file = (path !== '' && typeof path !== 'undefined') ? path : '/img/sprites/svg_sprite.svg';
			var revision = 1460990441;
			if (!document.createElementNS || !document.createElementNS('http://www.w3.org/2000/svg', 'svg').createSVGRect) {
				document.createElement('svg');
				document.createElement('use');
			}
			var isLocalStorage = 'localStorage' in window && window.localStorage !== null;
			var	request;
			var	data;
			var	insertIT = function () {
				var sprite = '<div style="display: none">' + data + '</div>';
				document.body.insertAdjacentHTML('afterbegin', sprite);
			};
			var insert = function () {
				if (document.body) {
					insertIT();
				} else {
					document.addEventListener('DOMContentLoaded', insertIT);
				}
			};

			if (isLocalStorage && parseInt(localStorage.getItem('inlineSVGrev'), 10) === revision) {
				data = localStorage.getItem('inlineSVGdata');
				if (data) {
					insert();
					return true;
				}
			}
			try {
				request = new XMLHttpRequest();
				request.open('GET', file, true);
				request.onload = function () {
					if (request.status >= 200 && request.status < 400) {
						data = request.responseText;
						insert();
						if (isLocalStorage) {
							localStorage.setItem('inlineSVGdata', data);
							localStorage.setItem('inlineSVGrev', revision);
						}
					}
				};
				request.send();
			} catch (e) {}
		}

	};
});

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


/* global Box */
Box.Application.addService('cookies', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var Cookies = context.getGlobal('Cookies');

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		set: function (name, value, options) {
			return Cookies.set(name, value, options);
		},
		get: function (name) {
			return Cookies.get(name);
		},
		getJSON: function (name) {
			return Cookies.getJSON(name);
		},
		remove: function (name) {
			return Cookies.remove(name);
		}

	};
});

/* global Box */
/*jshint -W030 */
Box.Application.addService('modal', function () {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------

	//
	// Based on Bootstrap Modals v.3.3.5
	//

	var Modal = function (element, options) {
		this.options             = options;
		this.$body               = $(document.body);
		this.$element            = $(element);
		this.$dialog             = this.$element.find('.modal-dialog');
		this.$backdrop           = null;
		this.isShown             = null;
		this.originalBodyPad     = null;
		this.scrollbarWidth      = 0;
		this.ignoreBackdropClick = false;
	};

	Modal.TRANSITION_DURATION = 300;
	Modal.BACKDROP_TRANSITION_DURATION = 150;

	Modal.DEFAULTS = {
		backdrop: true,
		keyboard: true,
		show: true,
		backdropClass: 'modal-backdrop',
		container: $(document.body)
	};

	Modal.prototype.toggle = function (_relatedTarget) {
		return this.isShown ? this.hide() : this.show(_relatedTarget);
	};

	Modal.prototype.show = function (_relatedTarget) {
		var that = this;
		var e = $.Event('show.bs.modal', { relatedTarget: _relatedTarget });

		this.$element.trigger(e);

		if (this.isShown || e.isDefaultPrevented()) {
			return;
		}

		this.isShown = true;

		this.checkScrollbar();
		this.setScrollbar();
		this.options.container.addClass('modal-open');

		this.escape();
		this.resize();

		this.$element.on('click.dismiss.bs.modal', '[data-dismiss="modal"]', $.proxy(this.hide, this));

		this.$dialog.on('mousedown.dismiss.bs.modal', function () {
			that.$element.one('mouseup.dismiss.bs.modal', function (e) {
				if ($(e.target).is(that.$element)) {
					that.ignoreBackdropClick = true;
				}
			});
		});

		this.backdrop(function () {
			var transition = $.support.transition && that.$element.hasClass('fade');

			if (!that.$element.parent().length) {
				that.$element.appendTo(that.$body); // don't move modals dom position
			}

			that.$element
				.show()
				.scrollTop(0);

			that.adjustDialog();

			if (transition) {
				that.$element[0].offsetWidth; // force reflow
			}

			that.$element.addClass('in');

			that.enforceFocus();

			var e = $.Event('shown.bs.modal', { relatedTarget: _relatedTarget });

			transition ?
			that.$dialog // wait for modal to slide in
			.one('bsTransitionEnd', function () {
				that.$element.trigger('focus').trigger(e);
			})
			.emulateTransitionEnd(Modal.TRANSITION_DURATION) : that.$element.trigger('focus').trigger(e);
		});
	};

	Modal.prototype.hide = function (e) {
		if (e) {
			e.preventDefault();
		}

		e = $.Event('hide.bs.modal');

		this.$element.trigger(e);

		if (!this.isShown || e.isDefaultPrevented()) {
			return;
		}

		this.isShown = false;

		this.escape();
		this.resize();

		$(document).off('focusin.bs.modal');

		this.$element
			.removeClass('in')
			.off('click.dismiss.bs.modal')
			.off('mouseup.dismiss.bs.modal');

		this.$dialog.off('mousedown.dismiss.bs.modal');

		$.support.transition && this.$element.hasClass('fade') ?
			this.$element
			.one('bsTransitionEnd', $.proxy(this.hideModal, this))
			.emulateTransitionEnd(Modal.TRANSITION_DURATION) :
			this.hideModal();
	};

	Modal.prototype.enforceFocus = function () {
		$(document)
			.off('focusin.bs.modal') // guard against infinite focus loop
			.on('focusin.bs.modal', $.proxy(function (e) {
				if (this.$element[0] !== e.target && !this.$element.has(e.target).length) {
					this.$element.trigger('focus');
				}
			}, this));
	};

	Modal.prototype.escape = function () {
		if (this.isShown && this.options.keyboard) {
			$(document).on('keydown.dismiss.bs.modal', this.$element, $.proxy(function (e) {
				e.which === 27 && this.hide();
			}, this));
		} else if (!this.isShown) {
			$(document).off('keydown.dismiss.bs.modal', this.$element);
		}
	};

	Modal.prototype.resize = function () {
		if (this.isShown) {
			$(window).on('resize.bs.modal', $.proxy(this.handleUpdate, this));
		} else {
			$(window).off('resize.bs.modal');
		}
	};

	Modal.prototype.hideModal = function () {
		var that = this;
		this.$element.hide();
		this.backdrop(function () {
			that.$body.removeClass('modal-open');
			that.resetAdjustments();
			that.resetScrollbar();
			that.$element.trigger('hidden.bs.modal');
		});
	};

	Modal.prototype.removeBackdrop = function () {
		this.$backdrop && this.$backdrop.remove();
		this.$backdrop = null;
	};

	Modal.prototype.backdrop = function (callback) {
		var that = this;
		var animate = this.$element.hasClass('fade') ? 'fade' : '';

		if (this.isShown && this.options.backdrop) {
			var doAnimate = $.support.transition && animate;

			this.$backdrop = $(document.createElement('div'))
				.addClass(this.options.backdropClass + ' ' + animate)
				.appendTo(this.options.container);

			this.$element.on('click.dismiss.bs.modal', $.proxy(function (e) {
				if (this.ignoreBackdropClick) {
					this.ignoreBackdropClick = false;
					return;
				}
				if (e.target !== e.currentTarget) {
					return;
				}
				this.options.backdrop === 'static' ? this.$element[0].focus() : this.hide();
			}, this));

		if (doAnimate) {
			this.$backdrop[0].offsetWidth; // force reflow
		}

		this.$backdrop.addClass('in');

		if (!callback) {
			return;
		}

		doAnimate ?
			this.$backdrop
			.one('bsTransitionEnd', callback)
			.emulateTransitionEnd(Modal.BACKDROP_TRANSITION_DURATION) :
			callback();

		} else if (!this.isShown && this.$backdrop) {
			this.$backdrop.removeClass('in');

			var callbackRemove = function () {
				that.removeBackdrop();
				callback && callback();
			};
			$.support.transition && this.$element.hasClass('fade') ?
				this.$backdrop
				.one('bsTransitionEnd', callbackRemove)
				.emulateTransitionEnd(Modal.BACKDROP_TRANSITION_DURATION) :
				callbackRemove();

		} else if (callback) {
			callback();
		}
	};

	// these following methods are used to handle overflowing modals

	Modal.prototype.handleUpdate = function () {
		this.adjustDialog();
	};

	Modal.prototype.adjustDialog = function () {
		var modalIsOverflowing = this.$element[0].scrollHeight > document.documentElement.clientHeight;

		this.$element.css({
			paddingLeft:  !this.bodyIsOverflowing && modalIsOverflowing ? this.scrollbarWidth : '',
			paddingRight: this.bodyIsOverflowing && !modalIsOverflowing ? this.scrollbarWidth : ''
		});
	};

	Modal.prototype.resetAdjustments = function () {
		this.$element.css({
			paddingLeft: '',
			paddingRight: ''
		});
	};

	Modal.prototype.checkScrollbar = function () {
		var fullWindowWidth = window.innerWidth;
		if (!fullWindowWidth) { // workaround for missing window.innerWidth in IE8
			var documentElementRect = document.documentElement.getBoundingClientRect();
			fullWindowWidth = documentElementRect.right - Math.abs(documentElementRect.left);
		}
		this.bodyIsOverflowing = document.body.clientWidth < fullWindowWidth;
		this.scrollbarWidth = this.measureScrollbar();
	};

	Modal.prototype.setScrollbar = function () {
		var bodyPad = parseInt((this.options.container.css('padding-right') || 0), 10);
		this.originalBodyPad = document.body.style.paddingRight || '';
		if (this.bodyIsOverflowing) {
			this.options.container.css('padding-right', bodyPad + this.scrollbarWidth);
		}
	};

	Modal.prototype.resetScrollbar = function () {
		this.options.container.css('padding-right', this.originalBodyPad);
	};

	Modal.prototype.measureScrollbar = function () { // thx walsh
		var scrollDiv = document.createElement('div');
		scrollDiv.className = 'modal-scrollbar-measure';
		this.options.container.append(scrollDiv);
		var scrollbarWidth = scrollDiv.offsetWidth - scrollDiv.clientWidth;
		this.options.container[0].removeChild(scrollDiv);
		return scrollbarWidth;
	};


	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		create: function (target, option) {
			var $this = $(target);
			var data = $this.data('modal');
			var options = $.extend({}, Modal.DEFAULTS, $this.data(), typeof option === 'object' && option);
			if (!data) {
				$this.data('modal', (data = new Modal(target, options)));
			} else {
				data.options = options;
			}
			return data;
		}

	};

});


/* global Box */
Box.Application.addService('advertizing', function (aplication) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------

	var $ = aplication.getGlobal('jQuery');
	var cookieService = aplication.getService('cookies');
	var DATA_KEY = 'advertizing_hidden_banners';

	function checkBannerVisibility(target) {
		var elementID = $(target).attr('id');

		return $.inArray(elementID, cookieService.getJSON(DATA_KEY)) === -1 ? false : true;
	}
	function setHiddenInCookies(target, bannersHideTime) {
		var elementID = $(target).attr('id');
		var array = cookieService.getJSON(DATA_KEY) || [];

		if ($.inArray(elementID, array) === -1) {
			array.push(elementID);
			cookieService.set(DATA_KEY, array, { expires: bannersHideTime });
		}
	}

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		show: function (target) {
			$(target).removeClass('hide');
		},
		hide: function (target, bannersHideTime) {
			$(target).addClass('hide');
			setHiddenInCookies(target, bannersHideTime);
		},
		isHidden: function (target) {
			return checkBannerVisibility(target);
		}

	};

});

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


/* global Box */
Box.Application.addBehavior('banner-close', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var $ = context.getGlobal('jQuery');
	var advertService;
	var bannersHideTime;
	var banner;

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {
		init: function () {
			advertService = context.getService('advertizing');
			bannersHideTime = context.getConfig('bannersHideTime');
		},
		destroy: function () {
			advertService = null;
			bannersHideTime = null;
			banner = null;
		},
		onclick: function (event, element, elementType) {
			if (elementType === 'hide-banner-link') {
				event.preventDefault();
				banner = $(element).closest('[data-type="advertizing"]');
				advertService.hide(banner, bannersHideTime);
			}
		}
	};

});

/* global Box, alert, jwplayer */
Box.Application.addBehavior('load-broadcast-player', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var $ = context.getGlobal('jQuery');
	var playerURL;
	var modalService;
	var modal;
	var playerModalContainer;
	var playerLastPositionURL;
	var player;
	var playerPos;
	var playerDur;
	var percentPos;
	var progressPosition;
	var broadcastID;
	var record;
	var iconLoaderService;

	function loadPlayer(broadcastID, element) {
		addPlayerModal();

		$.ajax({
			type: 'GET',
			dataType: 'html',
			url: playerURL,
			data: {
				broadcastID: broadcastID,
				record: record
			},
			error: function () {
				alert('Что-то пошло не так. Повторите попытку позднее!');
				$(element).data('play-flag', false);
			},
			success: function (data) {
				$(element).data('play-flag', false);
				playerModalContainer = $('.player-modal');
				playerModalContainer.find('.modal-content').html(data);
				iconLoaderService.renderIcons();
				Box.Application.startAll(playerModalContainer.get(0));
				modal = modalService.create(playerModalContainer, {
					backdropClass: 'modal-backdrop player-backdrop'
				});
				modal.show();
			}
		});
	}
	function addPlayerModal() {
		var modalHTML = $('<div class="modal player-modal fade" id="player-modal"><div class="modal-dialog"><div class="modal-content"></div></div></div>');
		if ($('#player-modal').length === 0) {
			$('body').prepend(modalHTML);
		}
	}
	function savePlayerPosition() {
		player = jwplayer('player');
		playerPos = player.getPosition();
		playerDur = player.getDuration();
		percentPos = Math.round((playerPos / playerDur) * 100);

		if (percentPos === 0) {
			percentPos = 0;
		} else if (percentPos <= 85 && percentPos > 0) {
			progressPosition = Math.floor(percentPos / 25);
		} else {
			progressPosition = 4;
		}

		if (playerPos !== 0) {
			$.get(playerLastPositionURL, {
				broadcastID: broadcastID,
				record: record,
				progressInSeconds: playerPos,
				progressPosition: progressPosition
			}, 'json').fail(function () {
				alert('Что-то пошло не так. Повторите попытку позднее!');
			});
		}
	}
	function play() {
		jwplayer('player').play();
	}

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		messages: ['playbroadcast'],

		init: function () {
			playerURL = context.getConfig('playerURL');
			playerLastPositionURL = context.getConfig('playerLastPositionURL');
			modalService = context.getService('modal');
			iconLoaderService = context.getService('icon-loader');

			$(document).on('hidden.bs.modal', playerModalContainer, function (event) {
				var modal = $(event.target).closest('.modal');

				if (modal.hasClass('player-modal')) {
					// remove class 'player-modal-open' with core class 'modal-open'
					$('body').removeClass('player-modal-open');
					savePlayerPosition();
					Box.Application.stopAll(playerModalContainer.get(0));
				}
			});

			// When modal show
			$(document).on('shown.bs.modal', function (event) {
				var modal = $(event.target).closest('.modal');
				// and this modal is player-modal
				if (modal.hasClass('player-modal')) {
					// add class for body, that remove padding-right for fullscreen player
					$('body').addClass('player-modal-open');
				}

				// Autoplay when show modal
				play();
			});
		},
		destroy: function () {
			playerURL = null;
			modal = null;
		},
		onmessage: function (name, data) {
			switch (name) {
				case 'playbroadcast':
					if (typeof data.broadcastID !== 'undefined') {
						broadcastID = data.broadcastID;
						record = data.record || false;
						loadPlayer(broadcastID, record, data.element);
					}
					break;
			}
		}
	};

});

/* global Box */
Box.Application.addModule('calendar-carousel', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var $ = context.getGlobal('jQuery');
	var moment = context.getGlobal('moment');
	var moduleEl;
	var carousel;
	var currentDay;
	var slidesHTML = '';
	var prevTrigger;
	var nextTrigger;
	var minDate;
	var maxDate;
	var today;
	var weekPointer;

	function updateNextPrevTriggers() {
		var carouselElements = $(carousel.flickity('getCellElements'));
		if ($(carouselElements[0]).hasClass('is-selected')) {
			prevTrigger.addClass('disabled');
		} else {
			prevTrigger.removeClass('disabled');
		}
		if ($(carouselElements.slice(-1)).hasClass('is-selected')) {
			nextTrigger.addClass('disabled');
		} else {
			nextTrigger.removeClass('disabled');
		}
	}

	function updateDateTriggersState(element) {
		var $parent = element.closest('li');
		var dateGroupIndex = $parent.closest('.date-group').index();
		if ($parent.hasClass('disabled') === false) {
			carousel.find('li').removeClass('current').end().find($parent).addClass('current');
			carousel.flickity('select', dateGroupIndex);

			return true;
		} else {
			return false;
		}
	}

	function getWeekHTML(dayPointer, minDate, maxDate) {
		var weekStart = dayPointer.startOf('week');
		var weekArr = [];
		var weekHTML = '';

		for (var i = 0; i <= 6; i = i + 1) {
			if (i === 0) {
				today = moment(weekStart);
				weekArr[i] = {
					day: moment(today),
					dayNum: moment(today).format('e'),
					dayChar: moment(today).format('dd')
				};
			} else {
				today = moment(weekStart).add(i, 'days');
				weekArr[i] = {
					day: moment(today),
					dayNum: moment(today).format('e'),
					dayChar: moment(today).format('dd')
				};
			}
		}

		weekHTML = '<ul class="date-group">';
		weekArr.forEach(function (currentValue, index) {
			var liClass;
			if (moment(weekArr[index].day).format('DD.MM.YYYY') === moment(currentDay).format('DD.MM.YYYY')) {
				liClass = ' class="current"';
			} else if (moment(weekArr[index].day).isBetween(minDate.subtract(1, 'seconds'), maxDate.add(1, 'seconds')) === false) {
				liClass = ' class="disabled"';
			} else {
				liClass = '';
			}
			weekHTML += '<li' + liClass + ' data-date="' + moment(weekArr[index].day).format('DD.MM.YYYY') + '"><a href="#" data-type="day-trigger"><span class="day-char">' + weekArr[index].dayChar +
											'</span><span class="day-number">' + moment(weekArr[index].day).format('DD') + '</span></a></li>';
		});
		weekHTML += '</ul>';

		return weekHTML;
	}

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		messages: ['datechanged'],

		init: function () {
			moduleEl = context.getElement();
			today = moment().startOf('day');
			currentDay = moment(context.getConfig('currentDate'), 'DD.MM.YYYY');
			minDate = context.getConfig('minDate') ? moment(today).subtract(context.getConfig('minDate'), 'days') : null;
			maxDate = context.getConfig('maxDate') ? moment(today).add(context.getConfig('maxDate'), 'days') : null;
			carousel = $(moduleEl).find('[data-type="dates-carousel"]');
			prevTrigger = $(moduleEl).find('[data-type="prev-trigger"]');
			nextTrigger = $(moduleEl).find('[data-type="next-trigger"]');

			weekPointer = moment(minDate);
			slidesHTML += getWeekHTML(moment(minDate), moment(minDate), moment(maxDate)); // minDate

			while (moment(weekPointer).endOf('week').format() !== moment(maxDate).endOf('week').format()) {
				slidesHTML += getWeekHTML(weekPointer.add(1, 'week'), moment(minDate), moment(maxDate));
			}

			if ($(slidesHTML).find('li.current').closest('.date-group').index() !== -1) {
				carousel.html(slidesHTML).flickity({
					prevNextButtons: false,
					pageDots: false,
					initialIndex: $(slidesHTML).find('li.current').closest('.date-group').index()
				});
			} else {
				console.warn('Промежуток дат задан не корректно');
			}
		},
		destroy: function () {
			moduleEl = null;
			carousel = null;
		},
		onmessage: function (name, data) {
			switch (name) {
				case 'datechanged':
					if (data.newDate !== currentDay) {
						var element = carousel.find('[data-date="' + data.newDate + '"]');
						updateDateTriggersState(element);
					}
				break;
			}
		},
		onclick: function (event, element, elementType) {
			if (elementType === 'prev-trigger') {
				event.preventDefault();
				carousel.flickity('previous');
				updateNextPrevTriggers();
			} else if (elementType === 'next-trigger') {
				event.preventDefault();
				carousel.flickity('next');
				updateNextPrevTriggers();
			} else if (elementType === 'day-trigger') {
				event.preventDefault();
				if (updateDateTriggersState($(event.target))) {
					// send message
					Box.Application.broadcast('datechanged', {
						newDate: $(event.target).closest('li').data('date') // DD.MM.YYYY
					});
					window.location.search = 'date=' + moment($(event.target).closest('li').data('date'), 'DD.MM.YYYY').format('DD-MM-YYYY');
				}
			}
		}
	};
});


/* global Box, clearTimeout */
Box.Application.addModule('city-select', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var $ = context.getGlobal('jQuery');
	var moduleEl;
	var select;
	var popover;
	var selectService;
	var popoverService;
	var popoverContent;
	var citiesArr;
	var isCitySelected = false;
	var cookieService;
	var DATA_KEY = 'city_select_data';
	var remoteURL;
	var showCityRequestPopover;
	var writedCookies;
	var citySelect;
	var popoverShowTimer;

	function updateSelectionHTML(state) {
		if (!state.id) {
			return state.text;
		}
		var $state = $('<span>' + state.text + '</span>');
		return $state;
	}

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		init: function () {

			// services
			selectService = context.getService('select');
			popoverService = context.getService('popover');
			cookieService = context.getService('cookies');
			// elements
			moduleEl = context.getElement();
			select = $(moduleEl).find('select');
			popoverContent = '<p>Мы угадали Ваш город?</p><ul><li><a href="#" data-type="select-trigger">Выбрать другой</a></li><li><a href="#" data-type="popover-trigger">Да, угадали</a></li></ul>';
			// configs
			remoteURL = context.getConfig('url');

			citiesArr = context.getConfig('cities');
			showCityRequestPopover = context.getConfig('showCityRequestPopover') || false;
			writedCookies = cookieService.get(DATA_KEY);

			citySelect = selectService.create(select, {
				data: citiesArr,
				templateSelection: updateSelectionHTML,
				theme: 'city-select'
			});

			select.on('select2:opening', function () {
				popover.hide();
				clearTimeout(popoverShowTimer);
			}).on('select2:select', function (event) {
				cookieService.set(DATA_KEY, event.params.data.text, {
					expires: 365
				});
				isCitySelected = true;

				// send form to server
				$('#city-select-value').val(event.params.data.id);
				$('#city-select-form').submit();
			}).on('select2:close', function () {
				if (!isCitySelected) {
					popoverShowTimer = setTimeout(function () {
						popover.show();
					}, 300);
				}
			});
			popover = popoverService.create(select, {
				content: popoverContent,
				trigger: 'manual',
				placement: 'bottom',
				html: true
			});
			if (typeof cookieService.get(DATA_KEY) === 'string' || showCityRequestPopover === false) {
				isCitySelected = true;
			} else {
				popoverShowTimer = setTimeout(function () {
					popover.show();
				}, 3000);
			}
		},
		destroy: function () {
			moduleEl = null;
			select = null;
			selectService = null;
			popover = null;
			showCityRequestPopover = null;
			popoverService = null;
			popoverContent = null;
			citiesArr = null;
			cookieService = null;
			remoteURL = null;
			writedCookies = null;
			citySelect = null;
			clearTimeout(popoverShowTimer);
		},
		onclick: function (event, element, elementType) {
			if (elementType === 'select-trigger') {
				event.preventDefault();
				popover.hide();
				citySelect.open();
			} else if (elementType === 'popover-trigger') {
				event.preventDefault();
				var selectedCity = select.find('option[value="' + select.val() + '"]').text();
				if (typeof selectedCity === 'string' && typeof writedCookies !== 'string') {
					cookieService.set(DATA_KEY, selectedCity, {
						expires: 365
					});
					clearTimeout(popoverShowTimer);
				}
				popover.hide();
			}
		}

	};
});

/* global Box, clearTimeout */
Box.Application.addModule('lang-select', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var $ = context.getGlobal('jQuery');
	var moduleEl;
	var select;
	var popover;
	var selectService;
	var popoverService;
	var popoverContent;
	var languagesArr;
	var isLangSelected = false;
	var remoteURL;
	var langSelect;

	function updateSelectionHTML(state) {
		if (!state.id) {
			return state.text;
		}
		var $state = $('<span>' + state.text + '</span>');
		return $state;
	}

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		init: function () {

			// services
			selectService = context.getService('select');

			// elements
			moduleEl = context.getElement();
			select = $(moduleEl).find('select');

			// configs
			remoteURL = context.getConfig('url');
			languagesArr = context.getConfig('languages');

			langSelect = selectService.create(select, {
				data: languagesArr,
				templateSelection: updateSelectionHTML,
				theme: 'lang-select'
			});

			select.on('select2:select', function (event) {
				isLangSelected = true;

				// send form to server
				$('#lang-select-value').val(event.params.data.id);
				$('#lang-select-form').submit();
			});
		},
		destroy: function () {
			moduleEl = null;
			select = null;
			selectService = null;
			popover = null;
			popoverService = null;
			popoverContent = null;
			languagesArr = null;
			remoteURL = null;
			langSelect = null;
		}
	};
});

/* global Box */
Box.Application.addModule('user-attached-socials', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var $ = context.getGlobal('jQuery');
	var moduleEl;
	var popoverService;
	var popoverParents;
	var popoverContent;

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		init: function () {
			popoverService = context.getService('popover');
			moduleEl = context.getElement();
			popoverParents = $(moduleEl).find('li:not(.active) a');
			popoverContent = context.getConfig('popoverContent');

			popoverService.create(popoverParents, {
				content: popoverContent,
				title: '',
				trigger: 'manual',
				placement: 'bottom',
				html: true
			});
			popoverParents.on('mouseenter', function () {
				$(this).popover('show');
			}).on('mouseleave', function () {
				$(this).popover('hide');
			});
		},
		destroy: function () {
			popoverService = null;
			moduleEl = null;
			popoverParents = null;
			popoverContent = null;
		}

	};
});


/* global Box, alert */
Box.Application.addModule('avatar-loader', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var $ = context.getGlobal('jQuery');
	var moduleEl;
	var progressbarService;
	var fileuploadService;
	var progressbarHolder;
	var progressbar;
	var fileInput;
	var remoteUrl;
	var textHolder;
	var imagePath;

	function imageIsLoaded(img) {
		// During the onload event, IE correctly identifies any images that
		// weren’t downloaded as not complete. Others should too. Gecko-based
		// browsers act like NS4 in that they report this incorrectly.
		if (!img.complete) {
			return false;
		}

		// However, they do have two very useful properties: naturalWidth and
		// naturalHeight. These give the true size of the image. If it failed
		// to load, either of these should be zero.

		if (typeof img.naturalWidth !== 'undefined' && img.naturalWidth === 0) {
			return false;
		}

		// No other way of checking: assume it’s ok.
		return true;
	}

	function updateModuleStateToLoaded() {
		progressbar.setProgress(1, 300);
		$(moduleEl).removeClass('is-loading is-empty');
		textHolder.text('Обновить аватар');

		// send message
		Box.Application.broadcast('useravatarupdated', {
			thumbnail: imagePath
		});
	}

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		init: function () {
			moduleEl = context.getElement();
			progressbarService = context.getService('progressbar');
			fileuploadService = context.getService('fileuploader');
			progressbarHolder = $(moduleEl).find('.progressbar-holder').get(0);
			fileInput = $(moduleEl).find('[data-type="file-input"]');
			remoteUrl = context.getConfig('url');
			textHolder = $(moduleEl).find('.load-avatar-text-holder');

			progressbar = progressbarService.create(progressbarHolder, {
				strokeWidth: 0.657894736842,
				color: '#ffba00',
				trailColor: '#dfdfe0'
			});
			fileuploadService.create(fileInput, {
				url: remoteUrl,
				dataType: 'json',
				add: function (event, data) {
					progressbar.setProgress(0);
					$(moduleEl).addClass('is-empty is-loading');
					data.submit().error(function () {
						alert('Что-то пошло не так. Повторите попытку позднее!');
					});
				},
				done: function (event, data) {
					imagePath = decodeURIComponent(data.result.files[0].thumbnailUrl);
					$(progressbarHolder).find('img').remove().end().append($('<img src="' + imagePath + '" width="246" height="246" />'));

					progressbar.animateProgress(0.8, 800, function () {
						if (imageIsLoaded($(progressbarHolder).find('img').get(0))) {
							updateModuleStateToLoaded();
						} else {
							$(progressbarHolder).find('img').on('load', 'img', updateModuleStateToLoaded);
						}
					});
				}
			});
		},
		destroy: function () {
			moduleEl = null;
			progressbarService = null;
			fileuploadService = null;
		}

	};
});

/* global Box */
Box.Application.addModule('user-profile-form', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var $ = context.getGlobal('jQuery');
	var moduleEl;
	var msbuttonService;
	var submitButton;
	var msSubmitButton;
	var maskedInputService;
	var dateInput;
	var dateMask;
	var phoneInput;
	var phoneMask;

	function sendForm () {
		removeFormErrors();

		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: $(moduleEl).attr('action'),
			data: $(moduleEl).serialize(),
			beforeSend: function () {
				removeFormErrors();
				msSubmitButton.disable();
			},
			error: function () {
				msSubmitButton.temporalState('fail-network-state', {
					afterChange: function () {
						this.enable();
					}
				});
			},
			success: function (data) {
				if (data.status === 'error') {
					msSubmitButton.temporalState('fail-data-state', {
						afterChange: function () {
							this.enable();
						}
					});
					var errors = data.errors;
					$.each(errors, function (key, value) {
						$('<span class="form-control-message">' + value + '</span>')
							.insertAfter($(moduleEl).find('input[name="' + key + '"], textarea[name="' + key + '"]').closest('.form-group').addClass('has-error').find('.form-control'));
					});
				} else {
					msSubmitButton.temporalState('done-state').enable();
				}
			}
		});
	}

	function removeFormErrors() {
		$(moduleEl).find('.form-group').removeClass('has-error').end().find('.form-control-message').remove();
	}

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		init: function () {
			msbuttonService = context.getService('multistate-button');
			maskedInputService = context.getService('masked-input');
			moduleEl = context.getElement();
			submitButton = $(moduleEl).find('button:submit[data-type="multistate-button"]');
			dateInput = $(moduleEl).find('input[data-type="masked-birthdate-input"]');
			phoneInput = $(moduleEl).find('input[data-type="masked-phone-input"]');
			dateMask = context.getConfig('dateMask') || '99.99.9999';
			phoneMask = context.getConfig('phoneMask') || '+7 (999) 999-99-99';

			msSubmitButton = msbuttonService.create(submitButton, {
				states: ['default-state', 'done-state', 'fail-data-state', 'fail-network-state']
			});
			maskedInputService.create(dateInput, dateMask);
			maskedInputService.create(phoneInput, phoneMask);
		},
		destroy: function () {
			msbuttonService = null;
			maskedInputService = null;
			moduleEl = null;
			submitButton = null;
			dateInput = null;
			dateMask = null;
			phoneInput = null;
			phoneMask = null;
		},
		onsubmit: function (event) {
			event.preventDefault();
			sendForm();
		}

	};
});


/* global Box */
Box.Application.addModule('user-passport-form', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var $ = context.getGlobal('jQuery');
	var moduleEl;
	var msbuttonService;
	var submitButton;
	var msSubmitButton;
	var maskedService;
	var dateInput;
	var dateMask;
	var passportSerialInput;
	var passportSerialMask;
	var passportNumberInput;
	var passportNumberMask;
	var passportCodeInput;
	var passportCodeMask;
	var maskedServicesArr = [];

	function pushMaskedService(input, mask) {
		maskedServicesArr.push(maskedService.create(input, mask));
	}

	function sendForm () {
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: $(moduleEl).attr('action'),
			data: $(moduleEl).serialize(),
			beforeSend: function () {
				removeFormErrors();
				msSubmitButton.disable();
			},
			error: function () {
				msSubmitButton.temporalState('fail-network-state', {
					afterChange: function () {
						this.enable();
					}
				});
			},
			success: function (data) {
				if (data.status === 'error') {
					msSubmitButton.temporalState('fail-data-state', {
						afterChange: function () {
							this.enable();
						}
					});
					var errors = data.errors;
					$.each(errors, function (key, value) {
						$('<span class="form-control-message">' + value + '</span>')
							.insertAfter($(moduleEl).find('input[name="' + key + '"], textarea[name="' + key + '"]').closest('.form-group').addClass('has-error').find('.form-control'));
					});
				} else {
					msSubmitButton.temporalState('done-state').enable();
				}
			}
		});
	}

	function removeFormErrors() {
		$(moduleEl).find('.form-group').removeClass('has-error').end().find('.form-control-message').remove();
	}

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		init: function () {
			msbuttonService = context.getService('multistate-button');
			maskedService = context.getService('masked-input');
			moduleEl = context.getElement();
			submitButton = $(moduleEl).find('button:submit[data-type="multistate-button"]');

			passportSerialInput = $(moduleEl).find('input[data-type="masked-passport-serial-input"]');
			passportSerialMask = context.getConfig('passportSerialMask') || '99 99';

			passportNumberInput = $(moduleEl).find('input[data-type="masked-passport-number-input"]');
			passportNumberMask = context.getConfig('passportNumberMask') || '999999';

			dateInput = $(moduleEl).find('input[data-type="masked-date-input"]');
			dateMask = context.getConfig('dateMask') || '99.99.9999';

			passportCodeInput = $(moduleEl).find('input[data-type="masked-passport-code-input"]');
			passportCodeMask = context.getConfig('passportCodeMask') || '999-999';

			pushMaskedService(passportSerialInput, passportSerialMask);
			pushMaskedService(dateInput, dateMask);
			pushMaskedService(passportNumberInput, passportNumberMask);
			pushMaskedService(passportCodeInput, passportCodeMask);

			msSubmitButton = msbuttonService.create(submitButton, {
				states: ['default-state', 'done-state', 'fail-data-state', 'fail-network-state']
			});
		},
		destroy: function () {
			msbuttonService = null;
			maskedService = null;
			moduleEl = null;
			submitButton = null;
			dateInput = null;
			dateMask = null;
			passportSerialInput = null;
			passportSerialMask = null;
			passportNumberInput = null;
			passportNumberMask = null;
			passportCodeInput = null;
			passportCodeMask = null;
			maskedServicesArr = null;
		},
		onsubmit: function (event) {
			event.preventDefault();
			sendForm();
		}

	};
});


/* global Box */
Box.Application.addModule('reset-password-form', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var $ = context.getGlobal('jQuery');
	var moduleEl;
	var msbuttonService;
	var submitButton;
	var msSubmitButton;
	var maskedInputService;

	function sendForm () {
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: $(moduleEl).attr('action'),
			data: $(moduleEl).serialize(),
			beforeSend: function () {
				removeFormErrors();
				msSubmitButton.disable();
			},
			error: function () {
				msSubmitButton.temporalState('fail-network-state', {
					afterChange: function () {
						this.enable();
					}
				});
			},
			success: function (data) {
				if (data.status === 'error') {
					msSubmitButton.temporalState('fail-data-state', {
						afterChange: function () {
							this.enable();
						}
					});
					var errors = data.errors;
					$.each(errors, function (key, value) {
						$('<span class="form-control-message">' + value + '</span>')
							.insertAfter($(moduleEl).find('input[name="' + key + '"], textarea[name="' + key + '"]').closest('.form-group').addClass('has-error').find('.form-control'));
					});
				} else {
					$(moduleEl).find('.form-control').val('');
					msSubmitButton.temporalState('done-state').enable();
				}
			}
		});
	}

	function removeFormErrors() {
		$(moduleEl).find('.form-group').removeClass('has-error').end().find('.form-control-message').remove();
	}

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		init: function () {
			msbuttonService = context.getService('multistate-button');
			moduleEl = context.getElement();
			submitButton = $(moduleEl).find('button:submit[data-type="multistate-button"]');

			msSubmitButton = msbuttonService.create(submitButton, {
				states: ['default-state', 'done-state', 'fail-data-state', 'fail-network-state']
			});
		},
		destroy: function () {
			msbuttonService = null;
			maskedInputService = null;
			moduleEl = null;
			submitButton = null;
		},
		onsubmit: function (event) {
			event.preventDefault();
			sendForm();
		}

	};
});


/* global Box */
Box.Application.addModule('user-navigation', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var $ = context.getGlobal('jQuery');
	var moduleEl;
	var avatarHolder;
	var recordingCount;

	function updateRecordingCount(element) {
		var countValue = parseInt(element.text(), 10) + 1;
		element.text(countValue);
	}
	function updateUserAvatar(element, thumbnail) {
		if (element.hasClass('is-empty')) {
			element.find('img').remove().end().append($('<img src="' + thumbnail + '" width="50" height="50" />')).removeClass('is-empty');
		} else {
			element.find('img').attr('src', thumbnail);
		}
	}

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		messages: ['useravatarupdated', 'updatebroadcaststatus'],

		init: function () {
			moduleEl = context.getElement();
			avatarHolder = $(moduleEl).find('[data-type="avatar-holder"]');
			recordingCount = $(moduleEl).find('[data-type="recording-count"]');
		},
		destroy: function () {
			moduleEl = null;
			avatarHolder = null;
			recordingCount = null;
		},
		onmessage: function (name, data) {
			switch (name) {
				case 'useravatarupdated':
					updateUserAvatar(avatarHolder, data.thumbnail);
					break;
				case 'updatebroadcaststatus':
					updateRecordingCount(recordingCount);
					break;
			}
		}

	};
});

/* global Box */
Box.Application.addModule('user-info-subscriptions', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	// var $ = context.getGlobal('jQuery');
	var moduleEl;
	var remoteUrl;

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		init: function () {
			moduleEl = context.getElement();
			remoteUrl = context.getConfig('url');
		},
		destroy: function () {
			moduleEl = null;
			remoteUrl = null;
		},
		onclick: function (event, element, elementType) {
			if (elementType === 'info-subscription-item') {
				event.preventDefault();
				// var subscriptionID = $(event.target).closest('li').data('subscription-id');
				// var status = $(event.target).closest('li').hasClass('status-active') ? 'disable' : 'enable';

				// $.get(remoteUrl, {
				// 	subscriptionID: subscriptionID,
				// 	status: status
				// }, function (data) {
				// 	var element = $(event.target).closest('li');
				// 	if (data.status === 'enable') {
				// 		element.addClass('status-active');
				// 	} else if (data.status === 'disable') {
				// 		element.removeClass('status-active');
				// 	}
				// }, 'json').fail(function () {
				// 	alert('Что-то пошло не так. Повторите попытку позднее!');
				// });
			}
		}

	};
});

/* global Box */
Box.Application.addModule('page', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------

	var $ = context.getGlobal('jQuery');
	var moduleEl;
	var advertService;
	var adverts;
	var bannersHideTime;
	var fillDiskSpace;
	var progressbarService;
	var modalService;
	var pathToSVGSprite;
	var iconLoaderService;

	function updateBroadcastsStatus(broadcastID) {
		var broadcast = $(moduleEl).find('.item.status-recordable[data-type="broadcast"]').filter('[data-broadcast-id="' + broadcastID + '"]');
		broadcast.removeClass('status-recordable').addClass('status-recording');
		broadcast.find('.icon:not(.icon-recording)').remove().end().find('.item-status-icon').prepend($('<span data-icon="icon-recording" />'));
		broadcast.find('.status-desc').text('В записи');
		iconLoaderService.renderIcons();
	}

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		messages: ['updatebroadcaststatus'],

		behaviors: ['banner-close', 'load-broadcast-player'],

		init: function () {
			moduleEl = context.getElement();
			advertService = context.getService('advertizing');
			progressbarService = context.getService('progressbar');
			modalService = context.getService('modal');
			iconLoaderService = context.getService('icon-loader');
			bannersHideTime = context.getConfig('bannersHideTime');
			pathToSVGSprite = context.getConfig('pathToSVGSprite');
			adverts = $(moduleEl).find('[data-type="advertizing"]');
			fillDiskSpace = $(moduleEl).find('[data-type="fill-disk-space"]');

			// Скрываем баннеры записанные в cookies
			adverts.each(function () {
				if (advertService.isHidden(this)) {
					advertService.hide(this);
				}
			});

			// Render icons on the page
			document.addEventListener('DOMContentLoaded', function () {
				iconLoaderService.renderSprite(pathToSVGSprite);
				iconLoaderService.renderIcons();
			});

			if (fillDiskSpace.length) {
				progressbarService.create(fillDiskSpace.find('.progress-holder').get(0), {
					strokeWidth: 11.1,
					color: '#216ed8',
					trailColor: '#d0d0d0'
				});
			}
		},
		destroy: function () {
			advertService = null;
			moduleEl = null;
		},
		onclick: function (event, element, elementType) {
			if (elementType === 'signin-overlay-toggle') {
				event.preventDefault();
				Box.Application.broadcast('togglesigninoverlay');
			} else {
				if ($(event.target).closest('[data-module="signin-overlay"]').length === 0 &&
					$(event.target).closest('.icon-recordit').length === 0 &&
					$(event.target).closest('.icon-recorded').length === 0 &&
					$(event.target).closest('[data-type="signin-handler-link"]').length === 0) {
					Box.Application.broadcast('hidesigninoverlay');
				}
			}
			if (elementType === 'signup-overlay-toggle') {
				event.preventDefault();
				Box.Application.broadcast('togglesignupoverlay');
			} else {
				if ($(event.target).closest('[data-module="signup-overlay"]').length === 0) {
					Box.Application.broadcast('hidesignupoverlay');
				}
			}

			if ($(event.target).closest('[data-module="reset-overlay"]').length === 0 &&
				$(event.target).closest('[data-type="reset-handler-link"]').length === 0 &&
				$(event.target).closest('[data-type="signin-handler-link"]').length === 0) {
				Box.Application.broadcast('hideresetoverlay');
			}
			if ($(event.target).closest('[data-module="success-reset-overlay"]').length === 0 &&
				$(event.target).closest('[data-type="signin-handler-link"]').length === 0) {
				Box.Application.broadcast('hidesuccessresetoverlay');
			}
		},
		onmessage: function (name, data) {
			if (name === 'updatebroadcaststatus' && typeof data.broadcastID !== 'undefined' && data.status === 'recording') {
				updateBroadcastsStatus(data.broadcastID);
			}
		}

	};
});

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
