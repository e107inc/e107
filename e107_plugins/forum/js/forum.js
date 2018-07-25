/**
 * @file
 * Forum JavaScript behaviors integration.
 */

var e107 = e107 || {'settings': {}, 'behaviors': {}};

(function ($)
{

	/**
	 * Behavior to bind click events on action buttons/links.
	 *
	 * @type {{attach: e107.behaviors.forumActions.attach}}
	 * @see "e107_web/js/core/all.jquery.js" file for more information.
	 */
	e107.behaviors.forumActions = {
		attach: function (context, settings)
		{
			$('a[data-forum-action], input[data-forum-action]', context).once('data-forum-action').each(function ()
			{
				$(this).on('click', function (e)
				{
					e.preventDefault();

					var $this = $(this);
					var action = $this.attr('data-forum-action');
					var thread = $this.attr('data-forum-thread');
					var post = $this.attr('data-forum-post');
					if (typeof tinymce == 'undefined')
					{
                        var text = $('#forum-quickreply-text').val();
					}
                    else
					{
                        var text = tinymce.get('forum-quickreply-text').getContent();
					}
					var insert = $this.attr('data-forum-insert');
					var token = $this.attr('data-token');
					var script = $this.attr("src");

					$.ajax({
						type: "POST",
						url: script,
						data: {thread: thread, action: action, post: post, text: text, insert: insert, e_token: token},
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
								if(d.html != false)
								{
									$('#' + insert).html(d.html);
									// Attach all registered behaviors to the new content.
									e107.attachBehaviors();
								}
							}

							if(action == 'quickreply' && d.status == 'ok')
							{
								if(d.html != false)
								{
									$(d.html).appendTo('#forum-viewtopic').hide().slideDown(1000);
									// Attach all registered behaviors to the new content.
									e107.attachBehaviors();
								}

                                if (typeof tinymce == 'undefined')
                                {
                                    $('#forum-quickreply-text').val('');
                                }
                                else
                                {
                                    tinymce.get('forum-quickreply-text').setContent('');
                                }
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
