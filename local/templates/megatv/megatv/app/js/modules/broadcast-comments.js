/* global Box, alert */
Box.Application.addModule('broadcast-comments', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var $ = context.getGlobal('jQuery');
	var iconLoaderService;
	var moduleEl;
	var form;
	var formBlock;
	var formCollapseTrigger;
	var commentsBlock;
	var commentsList;
	var formSubmit;
	var formTextarea;
	var submitFlag = false;

	function changeCollapseTriggerState(state) {
		switch (state) {
			case 'active':
				formCollapseTrigger.html('<span>Скрыть</span>');
			break;
			case 'passive':
				formCollapseTrigger.html('<span data-icon="icon-paper-airplane"></span><span>Оставить отзыв</span>');
				// Box.Application.renderIcons(context);
				iconLoaderService.renderIcons(context);
			break;
		}
	}

	function showCollapseTrigger() {
		formCollapseTrigger.removeClass('hidden');
	}

	function showCommentsBlock() {
		commentsBlock.collapse('show');
	}

	function addComment(data) {
		var commentHTML = '';
		// console.log( data.user_avatar );
		if (typeof data !== 'undefined') {
			if (data.user_avatar != null) {
				var avatar =	'<div class="user-avatar">' +
								'<img src="' + data.user_avatar + '" alt="' + data.username + '">' +
							'</div>';
			} else {
				var avatar =	'<div class="user-avatar is-empty"></div>';
			}
			commentHTML += '<li>' +
							avatar +
							'<div class="comment-holder">' +
								'<div class="comment-title">' + data.username + ' | ' + data.publish_date + '</div>' +
								'<div class="comment-text">' + data.comment_text + '</div>' +
							'</div>' +
							'</li>';
			commentsList.prepend(commentHTML);
		} else {
			console.warn('Объект с данными для нового комментария пуст');
			return;
		}
	}

	function sendComment(dataObj) {
		$(moduleEl).find('.form-group').removeClass('has-error');

		$.ajax({
			type: 'POST', // GET
			dataType: 'json',
			url: form.attr('action'),
			data: dataObj,
			error: function (xhr, ajaxOptions, thrownError) {
				// console.log( xhr );
				// console.log( ajaxOptions );
				// console.log( thrownError );
				alert('Что-то пошло не так. Повторите попытку позднее!');
				formSubmit.removeClass('is-submit-progress').trigger('blur');
			},
			success: function (data) {
				if (data.status === 'success') {

					// Очищаем поле ввода комментария после отправки комментария
					form.find('.form-control').val('');

					// Добавляем комментарий
					addComment(data);

					// Сворачивани
					if (formCollapseTrigger.hasClass('hidden') === true) {
						changeCollapseTriggerState('active');
						showCollapseTrigger();
						formCollapseTrigger.addClass('active');
					}
					if (commentsBlock.hasClass('in') === false || commentsBlock.hasClass('collapsing') === false) {
						showCommentsBlock();
					}
					formSubmit.removeClass('is-submit-progress');
					submitFlag = false;
				} else if (data.status === 'fail') {
					var errors = data.errors;
					$.each(errors, function (key, value) {
						$('<span class="form-control-message">' + value + '</span>')
							.insertAfter(form.find('input[name="' + key + '"], textarea[name="' + key + '"]').closest('.form-group').addClass('has-error').find('.form-control'));
					});
					formSubmit.removeClass('is-submit-progress');
					submitFlag = false;
				} else {
					alert('Что-то пошло не так. Повторите попытку позднее!');
					formSubmit.removeClass('is-submit-progress');
					submitFlag = false;
				}
			}
		});
	}

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		init: function () {
			moduleEl = context.getElement();
			iconLoaderService = context.getService('icon-loader');
			formBlock = $(moduleEl).find('.broadcast-user-comments-form');
			formCollapseTrigger = $(moduleEl).find('.comment-form-trigger-link');
			form = $(moduleEl).find('form');
			commentsBlock = $(moduleEl).find('.broadcast-user-comments');
			commentsList = commentsBlock.find('.comments-list');
			formSubmit = form.find('button:submit');
			formTextarea = form.find('textarea');
		},
		destroy: function () {
			moduleEl = null;
			iconLoaderService = null;
			form = null;
			formBlock = null;
			formCollapseTrigger = null;
			commentsBlock = null;
			commentsList = null;
			formSubmit = null;
			formTextarea = null;
		},
		onclick: function (event, element, elementType) {
			if (elementType === 'comment-form-trigger') {
				if ($(element).hasClass('active')) {
					formBlock.collapse('hide');
					$(element).removeClass('active');
					changeCollapseTriggerState('passive');
				} else {
					formBlock.collapse('show');
					$(element).addClass('active');
					changeCollapseTriggerState('active');
				}
			}
		},
		onsubmit: function (event) {
			event.preventDefault();
			var textareaValue = formTextarea.val();
			if (submitFlag === false && $.trim(textareaValue).length > 0) {
				formSubmit.addClass('is-submit-progress');
				sendComment(form.serialize());
			}
		}

	};
});
