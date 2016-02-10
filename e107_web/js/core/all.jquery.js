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
	 * Behavior to attach a click event to links with .e-ajax class.
	 *
	 * @type {{attach: Function}}
	 */
	e107.behaviors.eAjaxLink = {
		attach: function (context, settings) {
			$(context).find('a.e-ajax').once('e-ajax-link').each(function () {
				$(this).click(function () {
					var id = $(this).attr("href");
					var target = $(this).attr("data-target"); // support for input buttons etc.
					var loading = $(this).attr('data-loading'); // image to show loading.
					var nav = $(this).attr('data-nav-inc');

					if (nav != null) {
						e107.callbacks.eNav(this, '.e-ajax');	//modify data-src value for next/prev. 'from='
					}

					var src = $(this).attr("data-src");

					if (target != null) {
						id = '#' + target;
					}

					if (loading != null) {
						$(id).html("<img src='" + loading + "' alt='' />");
					}

					if (src === null) // old way - href='myscript.php#id-to-target
					{
						var tmp = src.split('#');
						id = tmp[1];
						src = tmp[0];
					}
					//	var effect = $(this).attr("data-effect");
					//	alert(id);

					$(id).load(src, function () {
						// alert(src);
						//$(this).hide();
						// $(this).fadeIn();
					});

					return false;
				});
			});
		}
	};

	/**
	 * Behavior to attach a change event to selects with .e-ajax class.
	 *
	 * @type {{attach: Function}}
	 */
	e107.behaviors.eAjaxSelect = {
		attach: function (context, settings) {
			$(context).find('select.e-ajax').once('e-ajax-select').each(function () {
				$(this).on('change', function ()
				{
					var form = $(this).closest("form").attr('id');

					var target = $(this).attr("data-target"); // support for input buttons etc.
					var loading = $(this).attr('data-loading'); // image to show loading.
					var handler = $(this).attr('data-src');
					var method = $(this).attr('data-method');

					var data = $('#' + form).serialize();
					var $target = $("#" + target);

					if(loading != null)
					{
						$target.html("<img src='" + loading + "' alt='' />");
					}

					$.ajax({
						type: 'post',
						url: handler,
						data: data,
						success: function (data)
						{
							switch(method)
							{
								case 'replaceWith':
									$target.replaceWith($(data));
									break;

								case 'append':
									$target.append($(data));
									break;

								case 'prepend':
									$target.prepend($(data));
									break;

								case 'before':
									$target.before($(data));
									break;

								case 'after':
									$target.after($(data));
									break;

								case 'html':
								default:
									$target.html(data).hide().show("slow");
									break;
							}

							// Attach all registered behaviors to the new content.
							e107.attachBehaviors();
						}
					});

					return false;
				});
			});
		}
	};

	/**
	 * Dynamic next/prev.
	 *
	 * @param e object (eg. from selector)
	 * @param navid - class with data-src that needs 'from=' value updated. (often 2 of them eg. next/prev)
	 */
	e107.callbacks.eNav = function (e, navid) {
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

		if (add > tot) {
			add = amt;
			//	$(e).hide();
		}

		if (sub < 0) {
			sub = 0
		}

		if (dir == 'down') {
			newVal = 'from=' + sub;
		}
		else {
			newVal = 'from=' + add;
		}

		if (newVal) {
			src = src.replace(oldVal, newVal);
			$(navid).attr("data-src", src);
		}
	};

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
		$(".e-hideme").hide();
		$(".e-expandit").show();   	
	
    //	 $(".e-spinner").spinner(); //FIXME breaks tooltips
	 

    	 
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
		     		
    		
    	// default 'toggle'. 	
       	$(".e-expandit").click(function () {
       		
       		var href = ($(this).is("a")) ? $(this).attr("href") : '';
       		
       		if((href === "#" || href == "") && $(this).attr("data-target"))
       		{
       			select = $(this).attr("data-target").split(','); // support multiple targets (comma separated)
       			
       			$(select).each( function() {
       				
       				$('#'+ this).slideToggle("slow");
				});

                if($(this).attr("data-return")==='true')
                {
                    return true;
                }

       			
       			return false;
       		}
       	
			
						
			if(href === "#" || href == "") 
			{
				idt = $(this).nextAll("div");	
				$(idt).slideToggle("slow");
				 return true;			
			}
		
			      		    		
       		//var id = $(this).attr("href");   		
			$(href).slideToggle("slow");
			
			return false;
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
		$(".bbcode_buttons, a.e-tip").tooltip({placement: 'top',opacity: 1.0, fade: true,html: true, container:'body'});
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


    $(document).on("click", ".e-dialog-close", function(){
			parent.$('.modal').modal('hide');
            $('.modal').modal('hide');


         //   $('#modal').modal('hide');
			// parent.$.colorbox.close()	
	});
		
		
		
		
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
		
		
		
	
		
		
		
			
		
});


	/**
	 * dynamic next/prev  
	 * @param e object (eg. from selector)
	 * @param navid - class with data-src that needs 'from=' value updated. (often 2 of them eg. next/prev)
	 */
	function eNav(e,navid)
	{
			var src = $(e).attr("data-src");
			var inc = parseInt($(e).attr("data-nav-inc"));
			var dir = $(e).attr("data-nav-dir");
			var tot = parseInt($(e).attr("data-nav-total"));
			var val = src.match(/from=(\d+)/);
			var amt = parseInt(val[1]);
			
			var oldVal = 'from='+ amt;
		
			var sub = amt - inc;
			var add = amt + inc;
			
			$(e).show();	
			
			if(add > tot)
			{
				add = amt;	
			//	$(e).hide();
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
			
			src = src.replace(oldVal, newVal);
			$(navid).attr("data-src",src);
				
	}

// Legacy Stuff to be converted. 
// BC Expandit() function 

	var nowLocal = new Date();		/* time at very beginning of js execution */
	var localTime = Math.floor(nowLocal.getTime()/1000);	/* time, in ms -- recorded at top of jscript */

	
	function expandit(e) {



		//	var href = ($(e).is("a")) ? $(e).attr("href") : '';
			if($(e).is("a"))
			{
				var href = $(e).attr("href");	
						
			}
			else
            {
                var href = '';
            }

			if(href === "#" || e === null || href === undefined) 
			{
				idt = $(e).next("div");	
								
				$(idt).toggle("slow");
				return false;
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


