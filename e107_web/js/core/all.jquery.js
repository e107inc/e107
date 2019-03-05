var e107 = e107 || {'settings': {}, 'behaviors': {}};

// Allow other JavaScript libraries to use $.
// TODO: Use jQuery.noConflict(), but for this, need to rewrite all e107 javascript to use wrapper: (function ($) { ... })(jQuery);
// jQuery.noConflict();

(function ($) {

	e107.callbacks = e107.callbacks || {};

	/**
	 * Attach all registered behaviors to a page element.
	 *
	 * Behaviors are event-triggered actions that attach to page elements, enhancing
	 * default non-JavaScript UIs. Behaviors are registered in the e107.behaviors
	 * object using the method 'attach' and optionally also 'detach' as follows:
	 * @code
	 *    e107.behaviors.behaviorName = {
	 *      attach: function (context, settings) {
	 *        ...
	 *      },
	 *      detach: function (context, settings, trigger) {
	 *        ...
	 *      }
	 *    };
	 * @endcode
	 *
	 * e107.attachBehaviors is added below to the jQuery ready event and so
	 * runs on initial page load. Developers implementing Ajax in their
	 * solutions should also call this function after new page content has been
	 * loaded, feeding in an element to be processed, in order to attach all
	 * behaviors to the new content.
	 *
	 * Behaviors should use
	 * @code
	 *   $(selector).once('behavior-name', function () {
	 *     ...
	 *   });
	 * @endcode
	 * to ensure the behavior is attached only once to a given element. (Doing so
	 * enables the reprocessing of given elements, which may be needed on occasion
	 * despite the ability to limit behavior attachment to a particular element.)
	 *
	 * @param context
	 *   An element to attach behaviors to. If none is given, the document element
	 *   is used.
	 * @param settings
	 *   An object containing settings for the current context. If none given, the
	 *   global e107.settings object is used.
	 */
	e107.attachBehaviors = function (context, settings) {
		context = context || document;
		settings = settings || e107.settings;
		// Execute all of them.
		$.each(e107.behaviors, function () {
			if ($.isFunction(this.attach)) {
				this.attach(context, settings);
			}
		});
	};

	/**
	 * Detach registered behaviors from a page element.
	 *
	 * Developers implementing AHAH/Ajax in their solutions should call this
	 * function before page content is about to be removed, feeding in an element
	 * to be processed, in order to allow special behaviors to detach from the
	 * content.
	 *
	 * Such implementations should look for the class name that was added in their
	 * corresponding e107.behaviors.behaviorName.attach implementation, i.e.
	 * behaviorName-processed, to ensure the behavior is detached only from
	 * previously processed elements.
	 *
	 * @param context
	 *   An element to detach behaviors from. If none is given, the document element
	 *   is used.
	 * @param settings
	 *   An object containing settings for the current context. If none given, the
	 *   global e107.settings object is used.
	 * @param trigger
	 *   A string containing what's causing the behaviors to be detached. The
	 *   possible triggers are:
	 *   - unload: (default) The context element is being removed from the DOM.
	 *   - move: The element is about to be moved within the DOM (for example,
	 *     during a tabledrag row swap). After the move is completed,
	 *     e107.attachBehaviors() is called, so that the behavior can undo
	 *     whatever it did in response to the move. Many behaviors won't need to
	 *     do anything simply in response to the element being moved, but because
	 *     IFRAME elements reload their "src" when being moved within the DOM,
	 *     behaviors bound to IFRAME elements (like WYSIWYG editors) may need to
	 *     take some action.
	 *   - serialize: E.g. when an Ajax form is submitted, this is called with the
	 *     form as the context. This provides every behavior within the form an
	 *     opportunity to ensure that the field elements have correct content
	 *     in them before the form is serialized. The canonical use-case is so
	 *     that WYSIWYG editors can update the hidden textarea to which they are
	 *     bound.
	 *
	 * @see e107.attachBehaviors
	 */
	e107.detachBehaviors = function (context, settings, trigger) {
		context = context || document;
		settings = settings || e107.settings;
		trigger = trigger || 'unload';
		// Execute all of them.
		$.each(e107.behaviors, function () {
			if ($.isFunction(this.detach)) {
				this.detach(context, settings, trigger);
			}
		});
	};

	// Attach all behaviors.
	$(function () {
		e107.attachBehaviors(document, e107.settings);
	});

	/**
	 * Attaches the AJAX behavior to each AJAX form/page elements. E107 uses
	 * this behavior to enhance form/page elements with .e-ajax class.
	 */
	e107.behaviors.eAJAX = {
		attach: function (context, settings)
		{
			$(context).find('.e-ajax').once('e-ajax').each(function ()
			{
				var $this = $(this);
				var event = $this.attr('data-event') || e107.callbacks.getDefaultEventHandler($this);

				$this.on(event, function ()
				{
					var $element = $(this);

					var ajaxOptions = {
						// URL for Ajax request.
						url: $element.attr('data-src'),
						// Ajax type: POST or GET.
						type: $element.attr('data-ajax-type'),
						// Target container for result.
						target: $element.attr("data-target"),
						// Method: 'replaceWith', 'append', 'prepend', 'before', 'after', 'html' (default).
						method: $element.attr('data-method'),
						// Image to show loading.
						loading: $element.attr('data-loading'),
						// FontAwesome icon name.
						loadingIcon: $element.attr('data-loading-icon'),
						// ID or class of container to place loading-icon within. eg. #mycontainer or .mycontainer
						loadingTarget: $element.attr('data-loading-target'),
						// If this is a navigation controller, e.g. pager.
						nav: $element.attr('data-nav-inc'),
						// Old way - href='myscript.php#id-to-target.
						href: $element.attr("href"),
						// Wait for final event. Useful for keyUp, keyDown... etc.
						wait: $element.attr('data-event-wait'),
						// Optional confirmation message - requires user input before proceeding. 
						confirm: $element.attr('data-confirm'),
					};

					// If this is a navigation controller, e.g. pager.
					if(ajaxOptions.nav != null)
					{
						// Modify data-src value for next/prev. 'from='
						e107.callbacks.eNav(this, '.e-ajax');
						// Update URL for Ajax request.
						ajaxOptions.url = $element.attr('data-src');
						// Set Ajax type to "GET".
						ajaxOptions.type = 'GET';
					}

					if(ajaxOptions.wait != null)
					{
						e107.callbacks.waitForFinalEvent(function(){
							e107.callbacks.ajaxRequestHandler($element, ajaxOptions);
						}, parseInt(ajaxOptions.wait), event);
					}
					else
					{
						e107.callbacks.ajaxRequestHandler($element, ajaxOptions);
					}

					return false;
				});
			});
		}
	};

	/**
	 * Behavior to initialize tooltips on elements with data-toggle="tooltip" attribute.
	 *
	 * @type {{attach: e107.behaviors.bootstrapTooltip.attach}}
	 */
	e107.behaviors.bootstrapTooltip = {
		attach: function (context, settings)
		{
			if(typeof $.fn.tooltip !== 'undefined')
			{
				$(context).find('[data-toggle="tooltip"]').once('bootstrap-tooltip').each(function ()
				{
					$(this).tooltip();
				});
			}
		}
	};

	/**
	 * Behavior to attach a click event to elements with .e-expandit class.
	 *
	 * @type {{attach: Function}}
	 */
	e107.behaviors.eExpandIt = {
		attach: function (context, settings)
		{
			$(context).find('.e-expandit').once('e-expandit').each(function ()
			{
				$(this).show();

				// default 'toggle'.
				$(this).click(function ()
				{

					var $this = $(this);
					var href = ($this.is("a")) ? $this.attr("href") : '';
					var $button = $this.find('button');

					if($button.length > 0)
					{
						var textMore = $button.attr('data-text-more');
						var textLess = $button.attr('data-text-less');

						if(textLess && textMore)
						{
							if($button.html() == textMore)
							{
								$this.find('.e-expandit-ellipsis').hide();
								$button.html(textLess);
							}
							else
							{
								$this.find('.e-expandit-ellipsis').show();
								$button.html(textMore);
							}
						}
					}

					if((href === "#" || href == "") && $this.attr("data-target"))
					{
						var select = $this.attr("data-target").split(','); // support multiple targets (comma separated)

						$(select).each(function ()
						{
							$('#' + this).slideToggle("slow");
						});

						if($this.attr("data-return") === 'true')
						{
							return true;
						}

						return false;
					}


					if(href === "#" || href == "")
					{
						$(this).nextAll("div").slideToggle("slow");
						return true;
					}

					$(href).slideToggle('slow', function ()
					{
						if($(this).is(':visible'))
						{
							$this.addClass('open');
							if($this.hasClass('e-expandit-inline'))
							{
								$(this).css('display', 'initial');
							}
							else
							{
								$(this).css('display', 'block'); //XXX 'initial' broke the default behavior.
							}
						}
						else
						{
							$this.removeClass('open');
						}
					});

					return false;
				});
			});
		}
	};

	/**
	 * Behavior to initialize Modal closer elements.
	 *
	 * @type {{attach: e107.behaviors.eDialogClose.attach}}
	 */
	e107.behaviors.eDialogClose = {
		attach: function (context, settings)
		{
			//$(context).find('.e-dialog-close').once('e-dialog-close').each(function ()
			//{
			//	$(this).click(function ()
            $(context).on('click', '.e-dialog-close', function()
				{
					var $modal = $('.modal');
					var $parentModal = parent.$('.modal');
					var $parentDismiss = parent.$('[data-dismiss=modal]');

					if($modal.length > 0)
					{
						$modal.modal('hide');
					}

					if($parentModal.length > 0)
					{
						$parentModal.modal('hide');
					}

					if($parentDismiss.length > 0)
					{
						$parentDismiss.trigger({type: 'click'});
					}
				});
			//});
		}
	};

	/**
	 * Behavior to hide elements.
	 *
	 * @type {{attach: e107.behaviors.eHideMe.attach}}
	 */
	e107.behaviors.eHideMe = {
		attach: function (context, settings)
		{
			$(context).find('.e-hideme').once('e-hide-me').each(function ()
			{
				$(this).hide();
			});
		}
	};

	/**
	 * Behavior to initialize submit buttons.
	 *
	 * @type {{attach: e107.behaviors.buttonSubmit.attach}}
	 */
	e107.behaviors.buttonSubmit = {
		attach: function (context, settings)
		{
			$(context).find('button[type=submit]').once('button-submit').each(function ()
			{
				$(this).on('click', function ()
					{
						var $button = $(this);
						var $form = $button.closest('form');
						var form_submited = false;
						var type = $button.data('loading-icon');

						if(type === undefined || $form.length === 0)
						{
							return true;
						}

						$form.submit(function ()
						{
							if ($form.find('.has-error').length > 0) {
								return false;
							}

							if (form_submited) {
								return false;
							}
							
							var caption = "<i class='fa fa-spin " + type + " fa-fw'></i>";
							caption += "<span>" + $button.text() + "</span>";

							$button.html(caption);

							if($button.attr('data-disable') == 'true')
							{
								$button.addClass('disabled');
								form_submited = true;
							}
						});

						return true;
					}
				);
			});
		}
	};

	/**
	 * Check if the selector is valid.
	 *
	 * @param selector
	 * @returns {boolean}
	 */
	e107.callbacks.isValidSelector = function (selector)
	{
		try
		{
			var $element = $(selector);
		} catch(error)
		{
			return false;
		}
		return true;
	};

	/**
	 * Dynamic next/prev.
	 *
	 * @param e object (eg. from selector)
	 * @param navid - class with data-src that needs 'from=' value updated. (often 2 of them eg. next/prev)
	 */
	e107.callbacks.eNav = function (e, navid)
	{
		var src = $(e).attr("data-src");
		var inc = parseInt($(e).attr("data-nav-inc"));
		var dir = $(e).attr("data-nav-dir");
		var tot = parseInt($(e).attr("data-nav-total"));
		var val = src.match(/from=(\d+)/);
		var amt = parseInt(val[1]);

		var oldVal = 'from=' + amt;
		var newVal = null;

		var sub = amt - inc;
		var add = amt + inc;

		$(e).show();

		if(add > tot)
		{
			add = amt;
			// $(e).hide();
		}

		if(sub < 0)
		{
			sub = 0
		}

		if(dir == 'down')
		{
			newVal = 'from=' + sub;
		}
		else
		{
			newVal = 'from=' + add;
		}

		if(newVal)
		{
			src = src.replace(oldVal, newVal);
			$(navid).attr("data-src", src);
		}
	};

	/**
	 * Get a reasonable default event handler for a (jQuery) element.
	 *
	 * @param $element
	 *  JQuery element.
	 */
	e107.callbacks.getDefaultEventHandler = function ($element)
	{
		var event = 'click'; // Default event handler.
		var tag = $element.prop("tagName").toLowerCase();

		if(tag == 'input')
		{
			var type = $element.attr('type').toLowerCase();

			switch(type)
			{
				case 'submit':
				case 'button':
					// Pressing the ENTER key within a textfield triggers the click event of
					// the form's first submit button. Triggering Ajax in this situation
					// leads to problems, like breaking autocomplete textfields, so we bind
					// to mousedown instead of click.
					event = 'mousedown';
					break;

				case 'radio':
				case 'checkbox':
					event = 'change';
					break;

				// text, number, password, date, datetime, datetime-local, month, week, time,
				// email, search, tel, url, color, range
				default:
					event = 'blur';
					break;
			}
		}
		else
		{
			switch(tag)
			{
				case 'button':
					// Pressing the ENTER key within a textfield triggers the click event of
					// the form's first submit button. Triggering Ajax in this situation
					// leads to problems, like breaking autocomplete textfields, so we bind
					// to mousedown instead of click.
					event = 'mousedown';
					break;

				case 'select':
					event = 'change';
					break;

				case 'textarea':
					event = 'blur';
					break;
			}
		}

		return event;
	};

	/**
	 * Handler fo Ajax requests.
	 *
	 * @param $element
	 *  JQuery element which fired the event.
	 * @param options
	 *  An object with Ajax request options.
	 */
	e107.callbacks.ajaxRequestHandler = function ($element, options)
	{
		var $loadingImage = null;

		// Loading image.
		if(options.loading != null)
		{
			$loadingImage = $(options.loading);
			$element.after($loadingImage);
		}

		if(options.confirm != null)
		{
			answer = confirm(options.confirm);

			if(answer === false)
			{
				return null;
			}
		}

		if(options.loadingIcon != null && options.loadingTarget != null)
		{
			var loadHtml = '<i class="e-ajax-loading fa fa-spin '+ options.loadingIcon +'"></i>';
			$(options.loadingTarget).html(loadHtml);
		}

		// Old way - href='myscript.php#id-to-target.
		if(options.target == null || options.url == null)
		{
			if(options.href != null)
			{
				var tmp = options.href.split('#');
				var id = tmp[1];

				if(options.url == null)
				{
					options.url = tmp[0];
				}

				if(options.target == null)
				{
					options.target = id;
				}
			}
		}

		// BC.
		if(options.target && options.target.charAt(0) != "#" && options.target.charAt(0) != ".")
		{
			options.target = "#" + options.target;
		}

		var form = $element.closest("form");
		var data = form.serialize() || '';

		$.ajax({
			type: options.type || 'POST',
			url: options.url,
			data: data,
			complete: function ()
			{
				if(loadHtml)
				{
					$('.e-ajax-loading').hide();
				}

				if($loadingImage)
				{
					$loadingImage.remove();
				}
			},
			success: function (response)
			{
				var $target = $(options.target);
				var jsonObject = response;

				if(typeof response == 'string')
				{
					try
					{
						jsonObject = $.parseJSON(response);
					} catch(e)
					{
						// Not JSON.
					}
				}

				if(typeof jsonObject == 'object')
				{
					// If result is JSON.
					e107.callbacks.ajaxJsonResponseHandler($target, options, jsonObject);
				}
				else
				{
					// If result is a simple text/html.
					e107.callbacks.ajaxResponseHandler($target, options, response);
				}
			}
		});
	};

	/**
	 * Handler for JSON responses. Provides a series of commands that the server
	 * can request the client perform.
	 *
	 * @param $target
	 *  JQuery (target) object.
	 * @param options
	 *  Object with options for Ajax request.
	 * @param commands
	 *  JSON object with commands.
	 */
	e107.callbacks.ajaxJsonResponseHandler = function ($target, options, commands)
	{
		$(commands).each(function ()
		{
			var command = this;
			// Get target selector from the response. If it is not there, default to our presets.
			var $newtarget = command.target ? $(command.target) : $target;

			switch(command.command)
			{
				// Command to insert new content into the DOM.
				case 'insert':
					var newOptions = options;
					newOptions.method = command.method;
					e107.callbacks.ajaxResponseHandler($newtarget, newOptions, command.data);
					break;

				// Command to remove a chunk from the page.
				case 'remove':
					e107.detachBehaviors($(command.target));
					$(command.target).remove();
					break;

				// Command to provide an alert.
				case 'alert':
					alert(command.text, command.title);
					break;

				// Command to provide the jQuery css() function.
				case 'css':
					$(command.target).css(command.arguments);
					// Attach all registered behaviors to the new content.
					e107.attachBehaviors();
					break;

				// Command to set the settings that will be used for other commands in this response.
				case 'settings':
					if(typeof command.settings == 'object')
					{
						$.extend(true, e107.settings, command.settings);
					}
					break;

				// Command to attach data using jQuery's data API.
				case 'data':
					$(command.target).data(command.name, command.value);
					// Attach all registered behaviors to the new content.
					e107.attachBehaviors();
					break;

				// Command to apply a jQuery method.
				case 'invoke':
					var $element = $(command.target);
					$element[command.method].apply($element, command.arguments);
					// Attach all registered behaviors to the new content.
					e107.attachBehaviors();
					break;
			}
		});
	};

	/**
	 * Handler for text/html responses. Inserting new content into the DOM.
	 *
	 * @param $target
	 *  JQuery (target) object.
	 * @param options
	 *  An object with Ajax request options.
	 * @param data
	 *  Text/HTML content.
	 */
	e107.callbacks.ajaxResponseHandler = function ($target, options, data)
	{
		var html = null;

		// If removing content from the wrapper, detach behaviors first.
		switch(options.method)
		{
			case 'html':
			case 'replaceWith':
				e107.detachBehaviors($target);
				break;
		}

		// Inserting content.
		switch(options.method)
		{
			case 'replaceWith':
				html = $.parseHTML(data);
				$target.replaceWith(html);
				break;

			case 'append':
				html = $.parseHTML(data);
				$target.append(html);
				break;

			case 'prepend':
				html = $.parseHTML(data);
				$target.prepend(html);
				break;

			case 'before':
				html = $.parseHTML(data);
				$target.before(html);
				break;

			case 'after':
				html = $.parseHTML(data);
				$target.after(html);
				break;

			case 'html':
			default:
				$target.html(data); // .hide().show("slow"); //XXX this adds display:block by default which breaks loading content within inactive tabs.
				break;
		}

		// Attach all registered behaviors to the new content.
		e107.attachBehaviors();
	};

	/**
	 * Wait for final event. Useful when you need to call an event callback
	 * only once, but event is fired multiple times. For example:
	 * - resizing window manually
	 * - wait for User to stop typing
	 *
	 * Example usage:
	 * @code
	 *  $(window).resize(function () {
	 *      e107.callbacks.waitForFinalEvent(function(){
	 *          alert('Resize...');
	 *          //...
	 *      }, 500, "some unique string");
	 *  });
	 * @endcode
	 */
	e107.callbacks.waitForFinalEvent = (function ()
	{
		var timers = {};
		return function (callback, ms, uniqueId)
		{
			if(!uniqueId)
			{
				uniqueId = "Don't call this twice without a uniqueId";
			}
			if(timers[uniqueId])
			{
				clearTimeout(timers[uniqueId]);
			}
			timers[uniqueId] = setTimeout(callback, ms);
		};
	})();

})(jQuery);

$.ajaxSetup({
	dataFilter: function (data, type) {
		if (type != 'json' || !data) {
			return data;
		}
		return data.replace(/^\/\*-secure-([\s\S]*)\*\/\s*$/, '$1');
	},
	cache: false // Was Really NEeded!
});

$(document).ready(function()
{

		// Basic Delete Confirmation
		$('input.delete,button.delete,a[data-confirm]').click(function(){
  			answer = confirm($(this).attr("data-confirm"));
  			return answer; // answer is a boolean
		});

		$(".e-confirm").click(function(){
  			answer = confirm($(this).attr("title"));
  			return answer; // answer is a boolean
		});


		 //check all
		 $("#check-all").click(function(event){
		 		var val = $(this).val(), selector = '.field-spacer';
		 		event.preventDefault();
		 		// e.g. <button id="check-all" value="jstarget:perms"><span>Check All</span></button> - match all checkboxes with attribute 'name="perms[]"
		 		if(val && val.match(/^jstarget\:/))
		 		{
		 			selector = 'input:checkbox[name^=' + val.split(':')[1] + ']';
				    $(selector).each( function() {
						$(this).attr("checked",true);
					 });
					 return;
		 		}
		 		// checkboxes children of .field-spacer
		 		else 
		 		{
				    $(selector).each( function() {
						$(this).children(":checkbox").attr("checked",true);
					 });
		 		}

		 });
		 
		 $("#uncheck-all").click(function(event) {
		 		var val = $(this).val(), selector = '.field-spacer';
		 		event.preventDefault();
		 		// e.g. <button id="uncheck-all" value="jstarget:perms"><span>Uncheck All</span></button> - match all checkboxes with attribute 'name="perms[]"
		 		if(val && val.match(/^jstarget\:/))
		 		{
		 			selector = 'input:checkbox[name^=' + val.split(':')[1] + ']';
				    $(selector).each( function() {
						$(this).attr("checked",false);
					 });
		 		}
		 		// checkboxes children of .field-spacer
		 		else 
		 		{
				    $(".field-spacer").each( function() {
						$(this).children(":checkbox").attr("checked",false);
					});
				}
		 });

		// On 
		$(".e-expandit-on").click(function () {
       		
       		if($(this).is("input") && $(this).attr("type")=='radio')
       		{
       			if($(this).attr("data-target"))
				{
					idt = '#' + $(this).attr("data-target");	
				}
       			else
       			{
       				idt = $(this).parent().nextAll("div.e-expandit-container");		
       			}
       			
       			$(idt).show("slow");	
       			return true;
       		}
       		var href = ($(this).is("a")) ? $(this).attr("href") : '';
						
			if(href === "#" || href == "") 
			{
				idt = $(this).nextAll("div.e-expandit-container");	
				$(idt).show("slow");
				 return true;	// must be true or radio buttons etc. won't work 		
			}
			
			if($(this).attr("data-expandit"))
			{
				var id = $(this).attr("data-expandit");	
			}
			else
			{
				var id = $(this).attr("href");   	
			}
			      		    		     				
			$(id).show("slow");
			return false;
		}); 
		
		// Off. 
		$(".e-expandit-off").click(function () {
       		
			if($(this).is("input") && $(this).attr("type")=='radio')
       		{
       			if($(this).attr("data-target"))
				{
					idt = '#' + $(this).attr("data-target");	
				}
       			else
       			{
       				idt = $(this).parent().nextAll("div.e-expandit-container");	
       			}
       			
       			$(idt).hide("slow");	
       			return true;
       		}
       				
       		var href = ($(this).is("a")) ? $(this).attr("href") : '';
						
			if(href === "#" || href == "") 
			{
				idt = $(this).nextAll("div.e-expandit-container");	
				$(idt).hide("slow");
				 return true;	 // must be true or radio buttons etc. won't work 			
			}
			      		    		    					
			if($(this).attr("data-expandit"))
			{
				var id = $(this).attr("data-expandit");	
			}
			else
			{
				var id = $(this).attr("href");   	
			}
					
			$(id).hide("slow");
			return false;
		});
		
		// Dates --------------------------------------------------
		
			// https://github.com/smalot/bootstrap-datetimepicker
				

		
		
		/*	
			$("input.e-date").each(function() {
        		$(this).datepicker({
        			 dateFormat: $(this).attr("data-date-format"),
        			 ampm: $(this).attr("data-date-ampm"),
					 firstDay: $(this).attr("data-date-firstday"),
        			 showButtonPanel: true
        		 });    		 
    		});
    		
    		$("input.e-datetime").each(function() {
    		//	var name = $(this).attr("name");
    		//	var val = $(this).val();
    			
    		//	alert(name + ': ' + val);
    			
        		$(this).datetimepicker({
        			 dateFormat: $(this).attr("data-date-format"),
        			 timeFormat: $(this).attr("data-time-format"),
        			 defaultDate: $(this).val(),
        			 defaultValue: $(this).val(),
        			 setDate: $(this).val(),
        			 ampm: $(this).attr("data-date-ampm"),
				//	 firstDay: $(this).attr("data-date-firstday"),
        			 showButtonPanel: true
        		 });    		 
    		});
    	
    		// Inline versions 
    		$("div.e-date").each(function() {
    			var id = $(this).attr("id");
        		var newid = id.replace("inline-", "");
        		$(this).datepicker({
        			dateFormat: $(this).attr("data-date-format"),
        			ampm: $(this).attr("data-date-ampm"),
					firstDay: $(this).attr("data-date-firstday"),
        			defaultDate: $("#"+newid).val(),
        			onSelect: function(dateText, inst) {
				      $("#"+newid).val(dateText);
				   	}
				  
        		 });    		 
    		});
    		
    		$("div.e-datetime").each(function() {
    			var id = $(this).attr("id");
        		var newid = id.replace("inline-", "");
        		$(this).datetimepicker({
        			dateFormat: $(this).attr("data-date-format"),
        			ampm: $(this).attr("data-date-ampm"),
        			showButtonPanel: false,
           			onSelect: function(dateText, inst) {
				      $("#"+newid).val(dateText);
				   	}
        		 }); 
        		 $(this).datetimepicker('setDate', $("#"+newid).val());   		 
    		});
    		
	*/
		
		// Tabs -----------------------------------------------------
		
		/*
		$(function() {
			$( "#tab-container" ).tabs({cache: true});
		});	
		
		// Tabs
		$(function() {
			$( ".e-tabs" ).tabs();
		});	
		
		
		$('.e-tabs-add').on("click", function(){
			var url = $(this).attr('data-url');
			var ct = parseInt($("#e-tab-count").val());
			var count = ct + 1; 	
			// alert(count);
			//return false;
			if($("#tab-container").tabs("add",url +'&count='+count,"Page "+count))
			{
				$("#tab-container").tabs('select', ct);
				$("#e-tab-count").val(count);	
			}
			
			return false;
		});
		*/
		
		// --------------- Email ----------------------------------------
		
		$('.e-email').on('blur', function() {
			// alert('hello');
		  $(this).mailcheck({


		    suggested: function(element, suggestion) {

		    	var id = $(element).nextAll('div.e-email-hint');
             //   console.log("Hint obj", id);

                var hint = id.attr('data-hint');

		    	var mes = hint.replace('[x]',suggestion.full);

                id.html(mes);
                id.show('slow');
		    },
		    empty: function(element) {
		      $(element).nextAll('div.e-email-hint').hide('slow');
		    }
	  		});
		});
	
	
		// --------------- Passwords -----------------------------
	
		// front-end
		$('.e-password').on('keyup', function() {
			// var len = $(this).val().length;
			
			//data-minlength
		});
		

		
		// 	Tooltips for bbarea.

		$(".bbcode_buttons, a.e-tip").each(function() {

			var tip = $(this).attr('title');

			if(tip === undefined)
			{
				return;
			}

			var pos = $(this).attr('data-tooltip-position');

			if(pos === undefined)
			{
				pos = 'bottom';
			}
            if(typeof $.fn.tooltip !== 'undefined')
            {
                $(this).tooltip({opacity: 1.0, fade: true, placement: pos, container: 'body'});
            }
			// $(this).css( 'cursor', 'pointer' )
		});

		
	//	$(".bbcode_buttons, a.e-tip").tooltip({placement: 'top',opacity: 1.0, fade: true,html: true, container:'body'});
	//	$("a.e-tip").tipsy({gravity: 'w',opacity: 1.0, fade: true,html: true});
	//	var tabs = $('#tab-container').clone(true);
	//	$('#htmlEditor').append(tabs);

		$('e-clone').click(function(){
		
		
			var copy = $(this).attr('id');
		
			duplicateHTML(copy,paste,baseid);
				
		});
		
/*
		$("a.e-bb").click(function(){
			var add = $(this).attr('data-bbcode');
			var func = $(this).attr('data-function');
			var diz = $(this).attr('title');
			id = $(this).attr('href');
			var tmp = id.replace('#','');
			//alert(tmp);
			if(func == 'insert')
			{
				addtext(add,true);	
				return false;
			}
			if(func == 'input')
			{
				addinput(add,diz);
				return false;	
			}
			if(func == 'show')
			{
				$('#'+add).show('slow');
				// addinput(add,diz);
				return false;	
			}
			if(func == 'add')
			{
				addtext(add);	
				return false;	
			}


			return false;
		 	
		});
			
		
		$("select.e-bb").change(function(){
			var add = $(this).val();
		 	addtext(add);
		 	$(this).val('');
		 	return false;	
		});
		
		$(".e-bb").mouseover(function(){
			
			var id = $(this).attr('id');
			var diz = $(this).attr('title');
		//	alert(id);
			var tmp = id.split('--');
		//	alert('#'+tmp[0]);
		 	$('#'+tmp[0]).val(diz);
		 	return false;	
		});
		
		$(".e-bb").mouseout(function(){
			var id = $(this).attr('id');
			var tmp = id.split('--')			
		 	$('#'+tmp[0]).val('');
		 	return false;	
		});
		
	*/	
		
		
		
		
	//	$(".e-multiselect").chosen();
		
					
		// Character Counter
	//	$("textarea").before("<p class=\"remainingCharacters\" id=\"" + $("textarea").attr("name")+ "-remainingCharacters\">&nbsp;</p>");
		$("textarea").keyup(function(){
    		
    	//	var max=$(this).attr("maxlength");
			var max = 100;
			var el = "#" + $(this).attr("name") + "-remainingCharacters";
    		var valLen=$(this).val().length;
    		$(el).text( valLen + " characters");
		});
		

		
		// Dialog
		/*
		$("a.e-dialog").colorXXXXbox({
			
			iframe:true,
			width:"60%",
			height:"70%",
			preloading:false,
			speed:10,
			opacity: 0.7,
			fastIframe: false,
			onComplete: function() { 
				// $("iframe").contents().find("body").addClass("mediaBody");   
			}


		});
		*/

		
		
		
		
		/*
		$("input.e-dialog").live('click',function() {
			
			var link = $(this).attr("data-target");
				
		 	$(this).dialog({
	            modal: true,
	            open: function ()
	            {
	                $(this).load(link);
	            },         
	            height: 600,
	            iframe: true,
	            width: 700,
	            title: 'Dynamically Loaded Page'
        	});
        	return false;
		});
		*/
		
		
		// Modal Box - uses inline hidden content 
		/*
		$(".e-modal").click(function () {
			var id = $(this).attr("href");
			$(id).dialog({
				 minWidth: 800,
				 maxHeight: 700,
				 
				 modal: true
			 });
		});
		*/
		  
		
		
				

		
    	$('.e-rate').each(function() {
    		var path 		= $(this).attr("data-path");
			var script 		= $(this).attr("data-url");
			var score 		= $(this).attr("data-score");
			var readonly	= parseInt($(this).attr("data-readonly"));
			var tmp 		= $(this).attr('id');
			var hint		= $(this).attr("data-hint");
			var hintArray	= hint.split(',');
			var t	 		= tmp.split('-');
			var table 		= t[0];
			var id 			= t[1];
			var label 		= '#e-rate-'+ table + '-' + id;
			var styles		= { 0: ' ', 0.5: 'label-important', 1: 'label-important', 1.5: 'label-warning', 2: 'label-warning', 2.5: 'label-default', 3: 'label-default' , 3.5: 'label-info', 4: 'label-info', 4.5: 'label-success', 5: 'label-success'};
			
			if($('#e-rate-'+tmp).length == 0)
			{
				var target = undefined;	
			}
			else
			{
				var target 		= '#e-rate-'+tmp;	
			}
			
			
		
    		$('#'+tmp).raty({
    			path		: path,
    			half  		: true,
    			score    	: score,
    			readOnly	: readonly,
    			hints		: hintArray,
    		//	starOff		: 'star_off_16.png',
  			//	starOn		: 'star_on_16.png',
  			//	starHalf  	: 'star_half_16.png',
  			//	cancelOff 	: 'cancel-off-big.png',
  			//	cancelOn  	: 'cancel-on-big.png',			
  			//	size      	: 16,
  				target     	: target,	
  				targetFormat: '{score}',
  				targetKeep: true,
  			//	targetType : 'number',
  				targetText : $('#e-rate-'+tmp).text(),			
    		//	cancel		: true,
    		//	css			: 'e-rate-star',
    		
    			mouseover: function(score, evt) 
    			{
    		
    			//	alert(score + ' : '+ styles[score]);
    			
    				$(label).removeClass('label-success');	
					$(label).removeClass('label-info');
					$(label).removeClass('label-warning');
					$(label).removeClass('label-important');
					$(label).removeClass('label-default');
    			
    				$(label).show();
    				$(label).addClass('label');
    				
    				$(label).addClass(styles[score]);		
			    //	alert('ID: ' + $(this).attr('id') + "\nscore: " + score + "\nevent: " + evt);
			    	
			  	},
				mouseout: function(score, evt) 
				{
					$(label).removeClass('label-success');	
					$(label).removeClass('label-info');
					$(label).removeClass('label-warning');
					$(label).removeClass('label-important');
					$(label).removeClass('label-default');
					$(label).hide();
				},
			
    			click: function(score, evt) {
        				$(this).find('img').unbind('click');
						$(this).find('img').unbind();
					$.ajax({
					  type: "POST",
					  url: script + "?ajax_used=1",
					  data: { table: table, id: id, score: score }
					}).done(function( msg ) {
						alert(msg);
						bla = msg.split('|');
						$(label).addClass(styles[score]);	
											
						$('#e-rate-'+tmp).text(bla[0]);
						if(bla[1])
						{
							$('#e-rate-votes-'+tmp).text(bla[1]);	
						}
						
					});
				}
    		});
    	});
    
    	// $( ".field-help" ).tooltip();
	
		// Allow Tabs to be used inside textareas. 
		$( 'textarea' ).keypress( function( e ) {
	    if ( e.keyCode == 9 ) {
	        e.preventDefault();
	        $( this ).val( $( this ).val() + '\t' );
	    }
		});

		// Text-area AutoGrow
	//	$("textarea.e-autoheight").elastic();
	
		// ajax next/prev mechanism - updates url from value. 
		$(".e-nav").click(function(){ // should be run before ajax. 
			/*
			var src = $(this).attr("data-src");
			var inc = parseInt($(this).attr("data-nav-inc"));
			var dir = $(this).attr("data-nav-dir");
			var tot = parseInt($(this).attr("data-nav-total"));
			var val = src.match(/from=(\d+)/);
			var amt = parseInt(val[1]);
			
			var oldVal = 'from='+ amt;
		
			var sub = amt - inc;
			var add = amt + inc;
			
			$(this).show();	
			
			if(add > tot)
			{
				add = amt;	
			}
				
			if(sub < 0)
			{
				sub = 0
			}
			
			if(dir == 'down')
			{
				var newVal = 'from='+ sub;
			}
			else
			{
				var newVal = 'from='+ add;	
			}
			
			alert('nav');
			src = src.replace(oldVal, newVal);
			$(".e-nav").attr("data-src",src);
		*/
		});

		
	



		// Does the same as externalLinks(); 
		$('a').each(function() {
			var href = $(this).attr("href");
			var rel = $(this).attr("rel");
			if(href && rel == 'external')
			{
				$(this).attr("target",'_blank');	
			}					
		});





	// Store selected textarea.
	$('.tbox.bbarea').click(function() {
		storeCaret(this);
	});
		
			
		
});


// Legacy Stuff to be converted. 
// BC Expandit() function 

	var nowLocal = new Date();		/* time at very beginning of js execution */
	var localTime = Math.floor(nowLocal.getTime()/1000);	/* time, in ms -- recorded at top of jscript */

	
	function expandit(e) {

		if(typeof e === 'object')
		{

			if($(e).is("a"))
			{
				var href = $(e).attr("href");						
			}

			if(href === "#" || e === null || href === undefined)
			{
				idt = $(e).next("div");
								
				$(idt).toggle("slow");
				return false;
			}
		}


		var id = "#" + e;

		$(id).toggle("slow");

		return false;
	}






	var addinput = function(text,rep) {
	
	// quick fix to prevent JS errors - proper match was done only for latin words
		// var rep = text.match(/\=([^\]]*)\]/);
		// var rep = '';
		var val = rep ? prompt(rep) : prompt('http://');
	
		if(!val)
		{
			return;
		}
		var newtext = text.replace('*', val);
		emote = '';
	    addtext(newtext, emote);
	     return;
	};

		
		
		
function SyncWithServerTime(serverTime, path, domain)
{
	if (serverTime) 
	{
	  	/* update time difference cookie */
		var serverDelta=Math.floor(localTime-serverTime);
		if(!path) path = '/';
		if(!domain) domain = '';
		else domain = '; domain=' + domain;
	  	document.cookie = 'e107_tdOffset='+serverDelta+'; path='+path+domain;
	  	document.cookie = 'e107_tdSetTime='+(localTime-serverDelta)+'; path='+path+domain; /* server time when set */
	}

	var tzCookie = 'e107_tzOffset=';
//	if (document.cookie.indexOf(tzCookie) < 0) {
		/* set if not already set */
		var timezoneOffset = nowLocal.getTimezoneOffset(); /* client-to-GMT in minutes */
		document.cookie = tzCookie + timezoneOffset+'; path='+path+domain;
//	}
}
	
	
	function urljump(url){
		top.window.location = url;
	}
	
	function setInner(id, txt) {
		document.getElementById(id).innerHTML = txt;
	}
	
	function jsconfirm(thetext){
			return confirm(thetext);
	}
	
	function insertext(str,tagid,display){
		document.getElementById(tagid).value = str;
		if(display){
			document.getElementById(display).style.display='none';
		}
	}
	
	function appendtext(str,tagid,display){
		document.getElementById(tagid).value += str;
		document.getElementById(tagid).focus();
		if(display){
			document.getElementById(display).style.display='none';
		}
	}

	function open_window(url,wth,hgt) {
		if('full' == wth){
			pwindow = window.open(url);
		} else {
			if (wth) {
				mywidth=wth;
			} else {
				mywidth=600;
			}
	
			if (hgt) {
				myheight=hgt;
			} else {
				myheight=400;
			}
	
			pwindow = window.open(url,'Name', 'top=100,left=100,resizable=yes,width='+mywidth+',height='+myheight+',scrollbars=yes,menubar=yes');
		}
		pwindow.focus();
	}

	function ejs_preload(ejs_path, ejs_imageString){
		var ejs_imageArray = ejs_imageString.split(',');
		for(ejs_loadall=0; ejs_loadall<ejs_imageArray.length; ejs_loadall++){
			var ejs_LoadedImage=new Image();
			ejs_LoadedImage.src=ejs_path + ejs_imageArray[ejs_loadall];
		}
	}
	
	function textCounter(field,cntfield) {
		cntfield.value = field.value.length;
	}
	
	function openwindow() {
		opener = window.open("htmlarea/index.php", "popup","top=50,left=100,resizable=no,width=670,height=520,scrollbars=no,menubar=no");
		opener.focus();
	}
	
	function setCheckboxes(the_form, do_check, the_cb){
		var elts = (typeof(document.forms[the_form].elements[the_cb]) != 'undefined') ? document.forms[the_form].elements[the_cb] : document.forms[the_form].elements[the_cb];
		if(document.getElementById(the_form))
		{
			if(the_cb)
			{
				var elts =(typeof(document.getElementById(the_form).elements[the_cb]) != 'undefined') ? document.getElementById(the_form).elements[the_cb] : document.getElementById(the_form).elements[the_cb];
			}
			else
			{
	        	var elts = document.getElementById(the_form);
			}
		}
	
		var elts_cnt  = (typeof(elts.length) != 'undefined') ? elts.length : 0;
		if(elts_cnt){
			for(var i = 0; i < elts_cnt; i++){
				elts[i].checked = do_check;
			}
		}else{
			elts.checked        = do_check;
			}
		return true;
	}

	var ref=""+escape(top.document.referrer);
	var colord = window.screen.colorDepth;
	var res = window.screen.width + "x" + window.screen.height;
	var eself = document.location;

var e107_selectedInputArea;

/* TODO: @SecretR - Object of removal
// From http://phpbb.com
var clientPC = navigator.userAgent.toLowerCase();
var clientVer = parseInt(navigator.appVersion);
var is_ie = ((clientPC.indexOf("msie") != -1) && (clientPC.indexOf("opera") == -1));
var is_nav = ((clientPC.indexOf('mozilla')!=-1) && (clientPC.indexOf('spoofer')==-1) && (clientPC.indexOf('compatible') == -1) && (clientPC.indexOf('opera')==-1) && (clientPC.indexOf('webtv')==-1) && (clientPC.indexOf('hotjava')==-1));
var is_moz = 0;
var is_win = ((clientPC.indexOf("win")!=-1) || (clientPC.indexOf("16bit") != -1));
var is_mac = (clientPC.indexOf("mac")!=-1);
var e107_selectedInputArea;
var e107_selectedRange;


// From http://www.massless.org/mozedit/
function mozWrap(txtarea, open, close){
	var selLength = txtarea.textLength;
	var selStart = txtarea.selectionStart;
	var selEnd = txtarea.selectionEnd;
	if (selEnd == 1 || selEnd == 2) selEnd = selLength;
	var s1 = (txtarea.value).substring(0,selStart);
	var s2 = (txtarea.value).substring(selStart, selEnd)
	var s3 = (txtarea.value).substring(selEnd, selLength);
	txtarea.value = s1 + open + s2 + close + s3;
	return;
}

function mozSwap(txtarea, newtext){
	var selLength = txtarea.textLength;
	var selStart = txtarea.selectionStart;
	var selEnd = txtarea.selectionEnd;
	if (selEnd == 1 || selEnd == 2) selEnd = selLength;
	var s1 = (txtarea.value).substring(0,selStart);
	var s3 = (txtarea.value).substring(selEnd, selLength);
	txtarea.value = s1 + newtext + s3;
	return;
}
*/

	function storeCaret (textAr){
		e107_selectedInputArea = textAr;
		/* TODO: @SecretR - Object of removal - not needed anymore
		if (textAr.createTextRange){
			e107_selectedRange = document.selection.createRange().duplicate();
		}*/
	}

/**
 * New improved version - fixed scroll to top behaviour when inserting BBcodes
 * @TODO - improve it more (0.8) 
 */
	function addtext(text, emote) {
	
	if (!window.e107_selectedInputArea) {
		return; //[SecretR] TODO - alert the user 
	}

	var eField = e107_selectedInputArea;	
	var eSelection 	= false;  
	var tagOpen = '';
	var tagClose = '';
	
	if (emote != true) {  // Split if its a paired bbcode
		var tmp = text.split('][', 2);
		if (tmp[0] == text) {
			tagOpen = text;
		} else {
			tagOpen = tmp[0] + ']';
			tagClose = '[' + tmp[1];
		}
	} else { //Insert Emote
		tagOpen = text;
	}
		

	// Windows user  
	if (document.selection) {
	
		eSelection = document.selection.createRange().text;
		eField.focus();
		if (eSelection) {
			document.selection.createRange().text = tagOpen + eSelection + tagClose;
		} else {
			document.selection.createRange().text = tagOpen + tagClose;
		}
		
		eSelection = '';
		
		eField.blur();
		eField.focus();
		
		return;
	} 
	
	var scrollPos = eField.scrollTop;
	var selLength = eField.textLength;
	var selStart = eField.selectionStart;
	var selEnd = eField.selectionEnd; 
	
	if (selEnd <= 2 && typeof(selLength) != 'undefined' && (selStart != selEnd)) {
		selEnd = selLength;
	}
	
	var sel1 = (eField.value).substring(0,selStart);
	var sel2 = (eField.value).substring(selStart, selEnd);
	var sel3 = (eField.value).substring(selEnd, selLength);

	var newStart = selStart + tagOpen.length + sel2.length + tagClose.length;
	eField.value = sel1 + tagOpen + sel2 + tagClose + sel3;

	eField.focus();
	eField.selectionStart = newStart;
	eField.selectionEnd = newStart;
	eField.scrollTop = scrollPos;
	return;

}

	function help(helpstr,tagid){
		if(tagid){
			document.getElementById(tagid).value = helpstr;
		} else if(document.getElementById('dataform')) {
			document.getElementById('dataform').helpb.value = helpstr;
		}
	}
	function externalLinks() {
		if (!document.getElementsByTagName) return;
		var anchors = document.getElementsByTagName("a");
		for (var i=0; i<anchors.length; i++) {
		var anchor = anchors[i];
		if (anchor.getAttribute("href") &&
			anchor.getAttribute("rel") == "external")
			anchor.target = "_blank";
		}
	}
	
	function eover(object, over) {
		object.className = over;
	}

var e107_dupCounter = 1;
function duplicateHTML(copy,paste,baseid){
	
		if(document.getElementById(copy)){

			e107_dupCounter++;
			var type = document.getElementById(copy).nodeName; // get the tag name of the source copy.

			var but = document.createElement('button');
			var br = document.createElement('br');

			but.type = 'button';
			but.innerHTML = 'x';
			but.value = 'x';
			but.className = 'btn btn-default button';
			but.onclick = function(){ this.parentNode.parentNode.removeChild(this.parentNode); };

			var destination = document.getElementById(paste);
			var source      = document.getElementById(copy).cloneNode(true);

			var newentry = document.createElement(type);

			newentry.appendChild(source);
			newentry.value='';
			newentry.className = 'form-inline';
			newentry.appendChild(but);
			newentry.appendChild(br);
			if(baseid)
			{
				newid = baseid+e107_dupCounter;
				newentry.innerHTML = newentry.innerHTML.replace(new RegExp(baseid, 'g'), newid);
				newentry.id=newid;
			}

			destination.appendChild(newentry);
		}
}

function preview_image(src_val,img_path, not_found)
{
	var ta;
	var desti = src_val + '_prev';

	ta = document.getElementById(src_val).value;
	if(ta){
		document.getElementById(desti).src = img_path + ta;
	}else{
		document.getElementById(desti).src = not_found;
	}
	return;
}

	// BC Ajax function 
function sendInfo(handler, container, form) 
{
	var data 	= $(form).serialize();
	
	$.ajax({
		type: 'post',
		 url: handler,
		 data: data,
		 success: function(data) 
		 {
		// 	console.log(data);
			$("#"+container).html(data).hide().show("slow");
		 }
	});
	
	
			
	return false;

	
		//$(container).load(handler,function() {
  				// alert(src);
  				//$(this).hide();
    			// $(this).fadeIn();
		//	});
			//if(form)
			//	$(form).submitForm(container, null, handler);
			//else
			//	new e107Ajax.Updater(container, handler);
}


