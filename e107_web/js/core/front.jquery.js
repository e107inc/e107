var e107 = e107 || {'settings': {}, 'behaviors': {}};

(function ($)
{
	// In case the page was opened with a hash, prevent jumping to it.
	// http://stackoverflow.com/questions/3659072/how-to-disable-anchor-jump-when-loading-a-page
	if(window.location.hash)
	{
		$('html, body').stop().animate({scrollTop: 0});
	}

	/**
	 * Behavior to initialize Smooth Scrolling on document, if URL has a fragment.
	 *
	 * @type {{attach: Function}}
	 */
	e107.behaviors.initializeSmoothScrolling = {
		attach: function (context, settings)
		{
			if(window.location.hash)
			{
				$(context).find('body').once('initialize-smooth-scrolling').each(function ()
				{
					if($(window.location.hash).length !== 0)
					{
						$('html, body').stop().animate({
							scrollTop: $(window.location.hash).offset().top
						}, 2000);

						return false;
					}
				});
			}
		}
	};

	/**
	 * Behavior to initialize Bootstrap Tooltip on elements.
	 *
	 * @type {{attach: Function}}
	 */
	e107.behaviors.initializeTooltip = {
		attach: function (context, settings)
		{
			$(context).find(":input").once('initialize-tooltip').each(function ()
			{
				$(this).tooltip();
			});
		}
	};

	/**
	 * Behavior to initialize click event on "Comment Submit" elements, and handle Ajax request.
	 *
	 * @type {{attach: Function}}
	 */
	e107.behaviors.eCommentSubmit = {
		attach: function (context, settings)
		{
			$(context).find(".e-comment-submit").once('e-comment-submit').each(function ()
			{
				$(this).on("click", function ()
				{
					var url = $(this).attr("data-target");
					var sort = $(this).attr("data-sort");
					var pid = parseInt($(this).attr("data-pid"));
					var formid = (pid != '0') ? "#e-comment-form-reply" : "#e-comment-form";
					var data = $('form' + formid).serialize();
					var total = parseInt($("#e-comment-total").text());

					$.ajax({
						type: 'POST',
						url: url + '?ajax_used=1&mode=submit',
						data: data,
						success: function (data)
						{
							try
							{
								var a = $.parseJSON(data);
							} catch(e)
							{
								// Not JSON.
								return;
							}

							var $commentsContainer = $('#comments-container');

							$("#comment").val('');

							if(a.html)
							{
								if(pid != 0)
								{
									$('#comment-' + pid).after(a.html).hide().slideDown(800);
									// Attach all registered behaviors to the new content.
									e107.attachBehaviors();
								}
								else
								{
									if(sort == 'desc')
									{
										$commentsContainer.prepend(a.html).hide().slideDown(800);
										// Attach all registered behaviors to the new content.
										e107.attachBehaviors();
									}
									else
									{
										$commentsContainer.append(a.html).hide().slideDown(800);
										// Attach all registered behaviors to the new content.
										e107.attachBehaviors();

										// Possibly needed as the submission may go unnoticed by the user.
										// TODO lan.
										// TODO: use Bootstrap Notify instead?
										alert('Thank you for commenting');
									}
								}
							}

							if(!a.error)
							{
								$("#e-comment-total").text(total + 1);

								if(pid != '0')
								{
									$(formid).hide();
								}
							}
							else
							{
								// TODO: use Bootstrap Notify instead?
								alert(a.msg);
							}

							return false;
						}
					});

					return false;

				});
			});
		}
	};

	/**
	 * Behavior to initialize click event on "Comment Reply" elements, and handle Ajax request.
	 *
	 * @type {{attach: Function}}
	 */
	e107.behaviors.eCommentReply = {
		attach: function (context, settings)
		{
			$(context).find(".e-comment-reply").once('e-comment-reply').each(function ()
			{
				$(this).on("click", function ()
				{
					var url = $(this).attr("data-target");
					var table = $(this).attr("data-type");
					var sp = $(this).attr('id').split("-");
					var id = "#comment-" + sp[3];

					$(".e-comment-reply").hide();

					// Prevent creating save button twice.
					if($('.e-comment-edit-save').length !== 0 || $('#e-comment-form-reply').length !== 0)
					{
						return false;
					}

					$.ajax({
						type: 'POST',
						url: url + '?ajax_used=1&mode=reply',
						data: {itemid: sp[3], table: table},
						success: function (data)
						{
							try
							{
								var a = $.parseJSON(data);
							} catch(e)
							{
								// Not JSON.
								return;
							}

							if(!a.error)
							{
								$(id).after(a.html).hide().slideDown(800);
								// Attach all registered behaviors to the new content.
								e107.attachBehaviors();
							}
						}
					});

					return false;
				});
			});
		}
	};

	/**
	 * Behavior to initialize click event on "Comment Edit" elements.
	 *
	 * @type {{attach: Function}}
	 */
	e107.behaviors.eCommentEdit = {
		attach: function (context, settings)
		{
			$(context).find(".e-comment-edit").once('e-comment-edit').each(function ()
			{
				$(this).on("click", function ()
				{
					var url = $(this).attr("data-target");
					var sp = $(this).attr('id').split("-");
					var id = "#comment-" + sp[3] + "-edit";

					// Prevent creating save button twice.
					if($('.e-comment-edit-save').length != 0)
					{
						return false;
					}

					$(id).attr('contentEditable', true);
					$(id).after("<div class='e-comment-edit-save'><input data-target='" + url + "' id='e-comment-edit-save-" + sp[3] + "' class='button btn btn-success e-comment-edit-save' type='button' value='Save' /></div>");
					$('div.e-comment-edit-save').hide().fadeIn(800);
					$(id).addClass("e-comment-edit-active");
					$(id).focus();

					return false;
				});
			});
		}
	};

	/**
	 * Behavior to initialize click event on "Comment Edit Save" elements, and handle Ajax request.
	 *
	 * @type {{attach: Function}}
	 */
	e107.behaviors.eCommentEditSave = {
		attach: function (context, settings)
		{
			$(context).find("input.e-comment-edit-save").once('input-e-comment-edit-save').each(function ()
			{
				$(this).on("click", function ()
				{
					var url = $(this).attr("data-target");
					var sp = $(this).attr('id').split("-");
					var id = "#comment-" + sp[4] + "-edit";
					var comment = $(id).text();

					$(id).attr('contentEditable', false);

					$.ajax({
						url: url + '?ajax_used=1&mode=edit',
						type: 'POST',
						data: {
							comment: comment,
							itemid: sp[4]
						},
						success: function (data)
						{
							try
							{
								var a = $.parseJSON(data);
							} catch(e)
							{
								// Not JSON.
								return;
							}

							if(!a.error)
							{
								$("div.e-comment-edit-save")
										.hide()
										.addClass("e-comment-edit-success")
										.html(a.msg)
										.fadeIn('slow')
										.delay(1000)
										.fadeOut('slow');

								// Attach all registered behaviors to the new content.
								e107.attachBehaviors();
							}
							else
							{
								$("div.e-comment-edit-save")
										.addClass("e-comment-edit-error")
										.html(a.msg)
										.fadeIn('slow')
										.delay(1000)
										.fadeOut('slow');
							}
							$(id).removeClass("e-comment-edit-active");

							setTimeout(function ()
							{
								$('div.e-comment-edit-save').remove();
							}, 2000);
						}
					});
				});
			});
		}
	};

	/**
	 * Behavior to initialize click event on "Comment Delete" elements, and handle Ajax request.
	 *
	 * @type {{attach: Function}}
	 */
	e107.behaviors.eCommentDelete = {
		attach: function (context, settings)
		{
			$(context).find(".e-comment-delete").once('e-comment-delete').each(function ()
			{
				$(this).on("click", function ()
				{
					var url = $(this).attr("data-target");
					var sp = $(this).attr('id').split("-");
					var id = "#comment-" + sp[3];
					var total = parseInt($("#e-comment-total").text());

					$.ajax({
						type: 'POST',
						url: url + '?ajax_used=1&mode=delete',
						data: {itemid: sp[3]},
						success: function (data)
						{
							try
							{
								var a = $.parseJSON(data);
							} catch(e)
							{
								// Not JSON.
								return;
							}

							if(!a.error)
							{
								$(id).hide('slow');
								$("#e-comment-total").text(total - 1);
							}

						}
					});

					return false;
				});
			});
		}
	};

	/**
	 * Behavior to initialize click event on "Comment Approve" elements, and handle Ajax request.
	 *
	 * @type {{attach: Function}}
	 */
	e107.behaviors.eCommentApprove = {
		attach: function (context, settings)
		{
			$(context).find(".e-comment-approve").once('e-comment-approve').each(function ()
			{
				$(this).on("click", function ()
				{
					var url = $(this).attr("data-target");
					var sp = $(this).attr('id').split("-");
					var id = "#comment-status-" + sp[3];

					$.ajax({
						type: 'POST',
						url: url + '?ajax_used=1&mode=approve',
						data: {itemid: sp[3]},
						success: function (data)
						{
							try
							{
								var a = $.parseJSON(data);
							} catch(e)
							{
								// Not JSON.
								return;
							}

							if(!a.error)
							{
								//TODO modify status of html on page
								$(id).text(a.html)
										.fadeIn('slow')
										.addClass('e-comment-edit-success'); //TODO another class?

								$('#e-comment-approve-' + sp[3]).hide('slow');
							}
							else
							{
								// TODO: use Bootstrap Notify instead?
								alert(a.msg);
							}
						}
					});

					return false;
				});
			});
		}
	};

	/**
	 * Behavior to initialize click event on "Vote Up/Down" elements, and handle Ajax request.
	 *
	 * @type {{attach: Function}}
	 */
	e107.behaviors.eRateThumb = {
		attach: function (context, settings)
		{
			$(context).find(".e-rate-thumb").once('e-rate-thumb').each(function ()
			{
				$(this).on("click", function ()
				{
					var src = $(this).attr("href");
					var thumb = $(this);
					var tmp = src.split('#');
					var id = tmp[1];
					src = tmp[0];

					$.ajax({
						type: "POST",
						url: src,
						data: {ajax_used: 1, mode: 'thumb'},
						dataType: "html",
						success: function (html)
						{
							if(html === '')
							{
								return false;
							}

							var tmp = html.split('|');
							var up = tmp[0];
							var down = tmp[1];

							$('#' + id + '-up').text(up);
							$('#' + id + '-down').text(down);
							// TODO lan.
							thumb.attr('title', 'Thanks for voting');
						}
					});

					return false;
				});
			});
		}
	};

	/**
	 * Behavior to switch to Tab containing invalid form field.
	 *
	 * @type {{attach: Function}}
	 */
	e107.behaviors.switchToTabContainingInvalidFormField = {
		attach: function (context, settings)
		{
			$(context).find('input[type=submit],button[type=submit]').once('switch-to-tab').each(function ()
			{
				$(this).on("click", function ()
				{
					var id = $(this).closest('form').attr('id'), found = false;

					$('#' + id).find(':invalid').each(function (index, node)
					{
						var tab = $('#' + node.id).closest('.tab-pane').attr('id');

						if(tab && (found === false))
						{
							$('a[href="#' + tab + '"]').tab('show');
							found = true;
						}
					});

					return true;
				});
			});
		}
	};

})(jQuery);
