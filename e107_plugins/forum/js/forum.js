/**
 * @file
 * Forum JavaScript behaviors integration.
 */

var e107 = e107 || {'settings': {}, 'behaviors': {}};

(function ($)
{

	var QUICKREPLY = 'forum-quickreply-text';

	/**
	 * The editor behind the quick-reply box, or null when there is none.
	 *
	 * Every action on a forum page ran through this, moderator links on the
	 * forum listing included, where no quick-reply box exists. It asked
	 * tinymce for the editor whenever TinyMCE was loaded for anything at all
	 * on the page and called getContent() on the answer, so a page carrying
	 * TinyMCE for some other field threw before the request was ever made:
	 * a click that did nothing, said nothing and logged nothing.
	 *
	 * @returns {object|null}
	 */
	function quickReplyEditor()
	{
		if (typeof tinymce === 'undefined' || !tinymce.get)
		{
			return null;
		}

		return tinymce.get(QUICKREPLY) || null;
	}

	/**
	 * @returns {string} what is in the quick-reply box, or '' if there is none
	 */
	function quickReplyText()
	{
		var editor = quickReplyEditor();

		if (editor)
		{
			return editor.getContent();
		}

		var $field = $('#' + QUICKREPLY);

		return $field.length ? $field.val() : '';
	}

	/**
	 * Hands what is in the quick-reply box to the full reply form at url, or follows url as it stands when there is nothing to carry.
	 */
	function openReplyForm(url, text)
	{
		if (!text)
		{
			window.location = url;

			return;
		}

		var token = e107.security.csrfToken();
		var $form = $('<form/>', {method: 'post', action: url})
			.append($('<input/>', {type: 'hidden', name: 'post', value: text}));

		// Empty where the site does not work in tokens at all, and a token
		// input carrying nothing is refused by the modes that do.
		if (token)
		{
			$form.append($('<input/>', {type: 'hidden', name: 'e-token', value: token}));
		}

		$form.appendTo('body').trigger('submit');
	}

	function clearQuickReply()
	{
		var editor = quickReplyEditor();

		if (editor)
		{
			editor.setContent('');

			return;
		}

		$('#' + QUICKREPLY).val('');
	}

	/**
	 * Behavior to bind click events on action buttons/links.
	 *
	 * @type {{attach: e107.behaviors.forumActions.attach}}
	 * @see "e107_web/js/core/all.jquery.js" file for more information.
	 */
	e107.behaviors.forumActions = {
		attach: function (context, settings)
		{
			// once(), not one(). jQuery's one() binds a handler that fires a
			// single time; given a name and no handler it returns the set
			// untouched, so nothing was marked and nothing filtered. Every
			// successful track and quick reply calls attachBehaviors() again
			// with the default document context, so each one bound another
			// click handler to every action on the page: two clicks on the bell
			// sent two requests, then four, and forum_track has no unique key
			// to stop the duplicate rows or the duplicate notification mail.
			$('a[data-forum-action], input[data-forum-action]', context).once('data-forum-action').each(function ()
			{
				$(this).on('click', function (e)
				{
					var $this = $(this);
					var action = $this.attr('data-forum-action');

					// Post Reply is the one action here whose href goes anywhere
					// else, the rest pointing back at the page they are on, so a
					// modified click on it means "open that page in its own tab"
					// and belongs to the browser. The new tab would be starting
					// from an empty quick reply box in any case.
					if (action === 'postreply' && (e.which > 1 || e.ctrlKey || e.metaKey || e.shiftKey || e.altKey))
					{
						return;
					}

					e.preventDefault();

					var thread = $this.attr('data-forum-thread');
					var post = $this.attr('data-forum-post');

					// Ask before destroying anything, and make sure nothing else
					// asks or acts afterwards.
					//
					// Core binds its own confirm to a[data-confirm], and returning
					// the answer from it only gets jQuery as far as
					// stopPropagation(), which does not stop a second handler on
					// the same element. This handler is bound first, because
					// attachBehaviors() is registered ahead of that ready block,
					// so a moderator who clicked Cancel on "delete this thread?"
					// deleted the thread anyway.
					var confirmText = $this.attr('data-confirm');

					if (confirmText)
					{
						// Immediately, and on both answers: core's handler is
						// bound to this same element, and letting it run would
						// either ask the very same question a second time or,
						// on Cancel, act anyway.
						e.stopImmediatePropagation();

						if (!window.confirm(confirmText))
						{
							return false;
						}
					}

					var text = quickReplyText();
					var insert = $this.attr('data-forum-insert');
					var token = $this.attr('data-token');
					var script = $this.attr("src");

					if (action === 'postreply')
					{
						openReplyForm($this.attr('href'), text);

						return false;
					}

					$.ajax({
						type: "POST",
						url: script,
						data: {thread: thread, action: action, post: post, text: text, insert: insert, e_token: token},
						error: function (xhr, status, error)
						{
							// Without this a refused or broken request produced
							// no message and no trace, which is indistinguishable
							// from a click that never fired.
							if (window.console && console.error)
							{
								console.error('e107 forum: ' + action + ' failed (' + xhr.status + ' ' + status + ')', error);
							}
						},
						success: function (data)
						{
							try
							{
								var d = $.parseJSON(data);
							} catch(e)
							{
								// Not JSON.
								return;
							}

							// Update e_token value on quick-reply form for the next Ajax request.
							if(d.e_token)
							{
								$this.attr('data-token', d.e_token);
							}

							// Show pup-up message.
							if(d.msg)
							{
								var alertType = 'info';

								if(d.status == 'ok')
								{
									alertType = 'success';
								}

								if(d.status == 'error')
								{
									alertType = 'danger';
								}

								if(jQuery().notify)
								{
									$('#uiAlert').notify({
										type: alertType,
										message: {text: d.msg},
										fadeOut: {enabled: true, delay: 3000}
									}).show();
								}
								else
								{
									alert(d.msg);
									location.reload();
									return;
								}
							}

							if(action == 'stick' || action == 'unstick' || action == 'lock' || action == 'unlock')
							{
								location.reload();
								return;
							}

							if(action == 'track')
							{
								// A failed untrack returns no html at all, and
								// "!= false" is true for undefined. It only ever
								// worked by accident: $().html(undefined) is a
								// getter rather than a setter.
								if(d.html)
								{
									$('#' + insert).html(d.html);
									// Attach all registered behaviors to the new content.
									e107.attachBehaviors();
								}
							}

							if(action == 'quickreply' && d.status == 'ok')
							{
								if(d.html)
								{
									$(d.html).appendTo('#forum-viewtopic').hide().slideDown(1000);
									// Attach all registered behaviors to the new content.
									e107.attachBehaviors();
								}

								clearQuickReply();
								return;
							}

							if(d.hide)
							{
								var t = '#thread-' + thread;
								var p = '#post-' + post;

								$(t).hide('slow');
								$(p).hide('slow').slideUp(800);
							}
						}
					});
				});
			});
		}
	};

})(jQuery);
