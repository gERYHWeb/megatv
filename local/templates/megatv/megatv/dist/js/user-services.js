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
			var revision = 1459513283;
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
					window.location.search = 'cur_date=' + moment($(event.target).closest('li').data('date'), 'DD.MM.YYYY').format('DD-MM-YYYY');
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
Box.Application.addModule('search-form', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var $ = context.getGlobal('jQuery');
	var Bloodhound = context.getGlobal('Bloodhound');
	var moduleEl;
	var searchField;
	var bloodhoundObj;
	var remoteUrl;

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		init: function () {
			moduleEl = context.getElement();
			searchField = $(moduleEl).find('[data-type="search-field"]');
			remoteUrl = context.getConfig('url');

			bloodhoundObj = new Bloodhound({
				datumTokenizer: Bloodhound.tokenizers.obj.whitespace('title'),
				queryTokenizer: Bloodhound.tokenizers.whitespace,
				remote: {
					url: remoteUrl,
					wildcard: '%QUERY'
				}
			});

			searchField.typeahead({
				minLength: 3
			}, {
				name: 'search-form',
				source: bloodhoundObj,
				templates: {
					suggestion: function (data) {
						var resultHTML = '';

						resultHTML += '<a class="search-result" href="' + data.link + '">';

						if (data.thumbnail === null) {
							resultHTML += '<span class="image-holder is-empty"></span>';
						} else {
							resultHTML += '<span class="image-holder"><img alt="' + data.title + '" width="60" height="60" src="' + data.thumbnail + '"></span>';
						}

						resultHTML += '<span class="info-col"><span class="publish-date">' + data.date + '</span><h5 class="result-title">' + data.title + '</h5></span></a>';

						return resultHTML;
					}
				}
			});
			$(moduleEl).on('typeahead:render typeahead:open', searchField, function () {
				var serchResults = searchField.closest('.form-group').find('.search-result').length;
				if (searchField.val() !== '' && serchResults > 0) {
					$(this).addClass('is-show-results');
				}
				if (serchResults < 1) {
					$(this).removeClass('is-show-results');
				}
			}).on('typeahead:close', searchField, function () {
				$(this).removeClass('is-show-results');
			});
		},
		destroy: function () {
			moduleEl = null;
			searchField = null;
			bloodhoundObj = null;
			remoteUrl = null;
		},
		onkeyup: function () {
			if (searchField.val().length < 3) {
				$(moduleEl).removeClass('is-show-results');
			}
		}
	};
});


/* global Box, alert */
Box.Application.addModule('subscription-services', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var $ = context.getGlobal('jQuery');
	var moduleEl;
	var progressbarService;
	var progressbarHolder;
	var totalSpaceHolder;
	var filledDiskSpace;
	var remoteUrl;
	var progressbar;
	var usedSpaceHolder;

	function updateServiceStatus(serviceID, status) {
		$.get(remoteUrl, {
			serviceID: serviceID,
			status: status
		}, function (data) {
			var element = $(event.target).closest('.item');
			switch (data.status) {
				case 'enabled':
					element.addClass('status-active');
					break;
				case 'disabled':
					element.removeClass('status-active');
					break;
				case 'temporal':
					element.addClass('status-active');
					setTimeout(function () {
						if (typeof data.updatedSpace === 'number') {
							updateDiskSpace(data.updatedSpace);
						}
						element.removeClass('status-active');
					}, 500);
					break;
			}
			if (data.status === 'enable') {
				element.addClass('status-active');
			} else if (data.status === 'disable') {
				element.removeClass('status-active');
			}
		}, 'json').fail(function () {
			alert('Что-то пошло не так. Повторите попытку позднее!');
		});
	}

	function updateDiskSpace(newDiskSpace) {
		var usedDiskSpace = parseInt(usedSpaceHolder.text(), 10);
		totalSpaceHolder.text(newDiskSpace + ' ГБ');
		progressbar.animateProgress(usedDiskSpace / newDiskSpace, 500);
	}

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		init: function () {
			moduleEl = context.getElement();
			progressbarService = context.getService('progressbar');
			filledDiskSpace = context.getConfig('filledDiskSpace');
			remoteUrl = context.getConfig('url');
			totalSpaceHolder = $(moduleEl).find('.total-space strong');
			usedSpaceHolder = $(moduleEl).find('.used-space strong');
			progressbarHolder = $(moduleEl).find('.progressbar-holder').get(0);

			progressbar = progressbarService.create(progressbarHolder, {
				progress: filledDiskSpace,
				strokeWidth: 13.147410358566,
				color: '#216ed8',
				trailColor: '#e9e9e9'
			});
		},
		destroy: function () {
			progressbarService = null;
			moduleEl = null;
			progressbarHolder = null;
			progressbar = null;
		},
		onclick: function (event, element, elementType) {
			if (elementType === 'service-item') {
				var serviceID = $(event.target).closest('.item').data('service-id');
				var status = $(event.target).closest('.item').hasClass('status-active') ? 'disable' : 'enable';

				updateServiceStatus(serviceID, status);
			}
		}

	};
});


/* global Box, alert */
Box.Application.addModule('user-subscription-channels', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var $ = context.getGlobal('jQuery');
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
			if (elementType === 'channel-item') {
				var channelID = $(event.target).closest('.item').data('channel-id');
				var status = $(event.target).closest('.item').hasClass('status-active') ? 'disable' : 'enable';

				$.get(remoteUrl, {
					channelID: channelID,
					status: status
				}, function (data) {
					var element = $(event.target).closest('.item');
					if (data.status === 'enable') {
						element.addClass('status-active');
					} else if (data.status === 'disable') {
						element.removeClass('status-active');
					}
				}, 'json').fail(function () {
					alert('Что-то пошло не так. Повторите попытку позднее!');
				});
			}
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
Box.Application.addModule('user-balance', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var $ = context.getGlobal('jQuery');
	var moduleEl;
	var modalService;
	var paymethodModal;
	var showSuccessModal;

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		init: function () {
			moduleEl = context.getElement();
			modalService = context.getService('modal');
			showSuccessModal = context.getConfig('showSuccessModal');
			paymethodModal = modalService.create($('#paymethod-modal'), {
				backdropClass: 'modal-backdrop paymethod-backdrop',
				container: $('.site-content')
			});

			if (typeof showSuccessModal !== 'undefined' && showSuccessModal === true) {
				setTimeout(function () {
					modalService.create($('#paysuccess-modal'), {
						backdropClass: 'modal-backdrop paysuccess-backdrop',
						container: $('.site-content')
					}).show();
				}, 500);
			}
		},
		destroy: function () {
			moduleEl = null;
		},
		onclick: function (event, element, elementType) {
			if (elementType === 'paymethod-modal-handler') {
				event.preventDefault();
				paymethodModal.show();
			}
		},
		onkeyup: function (event, element, elementType) {
			if (elementType === 'paymethod-field') {
				if (parseInt($(element).val(), 10) > 0) {
					$(moduleEl).find('[data-type="paymethod-submit"]').removeAttr('disabled');
				} else {
					$(moduleEl).find('[data-type="paymethod-submit"]').attr('disabled', true);
				}
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
