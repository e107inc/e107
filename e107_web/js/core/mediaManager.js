var e107 = e107 || {'settings': {}, 'behaviors': {}};

(function ($)
{
	'use strict';

	e107.mediaManager = e107.mediaManager || {};

	/**
	 * Behavior to initialize Media Manager.
	 *
	 * @type {{attach: e107.behaviors.initMediaManager.attach}}
	 */
	e107.behaviors.initMediaManager = {
		attach: function (context, settings)
		{
			$(context).find('.e-media-attribute').once('media-manager-e-media-attribute').each(function ()
			{
				$(this).keyup(function ()
				{
					e107.mediaManager.eMediaAttribute(this);
				});
			});

			$(context).find('#float').once('media-manager-float').each(function ()
			{
				$(this).change(function ()
				{
					e107.mediaManager.eMediaAttribute(this);
				});
			});

			$(context).find('.e-media-select-file-none').once('media-manager-e-media-select-file-none').each(function ()
			{
				$(this).click(function ()
				{
					e107.mediaManager.eMediaSelectFileNone(this);
				});
			});
/*
			$(context).find('.e-media-select').once('media-manager-e-media-select').each(function ()
			{
				$(this).on('click', function ()
				{
					e107.mediaManager.eMediaSelect(this);
				});
			});
*/
            $(context).on('click', '.e-media-select', function (e)
            {
            	e.preventDefault();

            	var previewHTML = $(this).attr('data-preview-html');

            //	console.log(previewHTML);

            	if(previewHTML) // MediaPicker v2.1.9
				{
					 e107.mediaManager.eMediaSelectProcess(this);
				//	 e107.mediaManager.eMediaSelect(this);
				}
				else // imagepicker, iconpicker
				{
					 e107.mediaManager.eMediaSelect(this);
				}



            });

			// Must be defined after e-media-select.
			/*
			$(context).find('.e-dialog-save').once('media-manager-e-dialog-save').each(function ()
			{
				$(this).click(function ()
				{
					e107.mediaManager.eDialogSave(this);
				});
			});
			*/
            $(context).on('click', '.e-dialog-save', function ()
            {
                e107.mediaManager.eDialogSave(this);
            });


			// Must be defined after e-media-select.
			$(context).find('.e-media-nav').once('media-manager-e-media-nav').each(function ()
			{
				$(this).click(function ()
				{
					e107.mediaManager.mediaNav(this, '.e-media-nav');
				});
			});

			$(context).find('#media-search').once('media-manager-media-search').each(function ()
			{
				$(this).keyup(function ()
				{
					var that = this;

					e107.callbacks.waitForFinalEvent(function ()
					{
						e107.mediaManager.mediaNav(that, null);
					}, 300, "mediaSearch");
				});
			});

			// Ajax keyup search. Used by media-browser.
			$(context).find('.e-ajax-keyup').once('media-manager-e-ajax-keyup').each(function ()
			{


				$(this).keyup(function ()
				{

					var that = this;

					e107.callbacks.waitForFinalEvent(function ()
					{

						$(that).closest('div').find('.media-carousel-index').text('1'); // reset counter when searching.

						e107.mediaManager.eAjaxKeyUp(that);
					}, 300, "eAjaxKeyUp");
				});
			});

			$(context).find('body').once('media-manager-plupload').each(function ()
			{
				e107.mediaManager.initPlUpload();
			});
		}
	};

	/**
	 * @param {object} that
	 *  DOM element that was clicked, changed... etc.
	 * @param {string} bbcode
	 *  bbCode tag-name.
	 */
	e107.mediaManager.eMediaAttribute = function (that, bbcode)
	{
		var $this = $(that);
		var style = '';
		var css = '';
		var bb = '';

		var src = $('#src').attr('value'); // working old
		var path = $('#path').attr('value'); // working old
		var preview = $('#preview').attr('value'); // working old

		var width = $('#width').val();
		var height = $('#height').val();

		var margin_top = $('#margin-top').val();
		var margin_bottom = $('#margin-bottom').val();
		var margin_right = $('#margin-right').val();
		var margin_left = $('#margin-left').val();
		var _float = $('#float').val();
		var alt = $('#alt').val();



		var target = $this.attr('data-target');

		var $htmlHolder = $('#html_holder');
		var $bbcodeHolder = $('#bbcode_holder');

		if(margin_right != '' && margin_right !== undefined)
		{
			style = style + 'margin-right:' + margin_right + 'px;';
		}

		if(margin_left != '' && margin_left !== undefined)
		{
			style = style + 'margin-left:' + margin_left + 'px;';
		}

		if(margin_top != '' && margin_top !== undefined)
		{
			style = style + 'margin-top:' + margin_top + 'px;';
		}

		if(margin_bottom != '' && margin_bottom !== undefined)
		{
			style = style + 'margin-bottom:' + margin_bottom + 'px;';
		}

		if(_float == 'left' || _float == 'right')
		{
	//		style = style + 'float:' + _float + ';';
		}

		if(_float == 'left' || _float == 'right')
		{
			css = 'bbcode-img-' + _float;
		}

		if(width === undefined)
		{
			width = '';
		}

		if(height === undefined)
		{
			height = '';
		}

		// Set the Html / Wysiwyg Value.
		var $img = $('<img/>');

		if(css != '')
		{
			$img.attr('class', css);
		}

		if(style != '')
		{
			$img.attr('style', style);
		}

		$img.attr('src', src);

		if(alt != '')
		{
			$img.attr('alt', alt);
		}

		if(width > 0)
		{
			$img.attr('width', width);
		}

		if(height > 0)
		{
			$img.attr('height', height);
		}

		if($htmlHolder.length > 0)
		{
			$htmlHolder.val($img.prop('outerHTML'));
		}

		// prevent empty tags
		if (path == '') return;

		// Only Do width/height styling on bbcodes --
		if(width != '' && width != 0 && width !== undefined)
		{
			style = style + 'width:' + width + 'px;';
		}

		if(height != '' && height != 0 && height !== undefined)
		{
			style = style + 'height:' + height + 'px;';
		}

		if(bbcode != 'video')
		{
			bb = '[img';

			var bparm = {};

			if(css != '')
			{
				// bb = bb + '&class=' + css;
				bparm['class'] = css;
			}

			if(style != '')
			{
				// bb = bb + ' style=' + style;
				bparm['style'] = style;
			}

			if(alt != '')
			{
			// 	bb = bb + '&alt=' + alt;
				bparm['alt'] = alt;
			}

			var bbquery = decodeURIComponent(jQuery.param(bparm, true));
			console.log(bbquery);
			bb = bb + ' ' + bbquery;

			bb = bb + ']';
			bb = bb + path;
			bb = bb + '[/img]';
/*
			if(target && target.charAt(0) != "#" && target.charAt(0) != ".")
			{
				target = "#" + target;
			}

			var $target = $(target);

			if($target.length > 0)
			{
				$target.val($target.val() + bb);
			}
			else
			{
				var $parentTarget = parent.$(target); // From iframe.

				if($parentTarget.length > 0)
				{
					$parentTarget.val($parentTarget.val() + bb);
				}
				else
				{
					if($bbcodeHolder.length > 0)
					{
						$bbcodeHolder.val(bb); // Set the BBcode Value.
					}
				}
			}
*/
            // above code did directly write the tag to the target, without having clicked on "Save"
            if($bbcodeHolder.length > 0)
            {
                $bbcodeHolder.val(bb); // Set the BBcode Value.
            }

		}
	};

	/**
	 * @param {object} that
	 *  Element that was clicked.
	 */
	e107.mediaManager.eMediaSelectFileNone = function (that)
	{
		var $this = $(that);
		var target = $this.attr('data-target');
		var label = $this.attr('data-target-label');

		var $parentInput = parent.$('input#' + target);
		var $parentInputID = parent.$('input#' + target + '-id');
		var $parentInputPath = parent.$('input#' + target + '-path');
		var $parentInputName = parent.$('input#' + target + '-name');
		var $parentTarget = parent.$('#' + target + '_prev');

		if($parentInput.length > 0)
		{
			$parentInput.val('');
		}

		if($parentInputID.length > 0)
		{
			$parentInputID.val('');
		}

		if($parentInputPath.length > 0)
		{
			$parentInputPath.val('');
		}

		if($parentInputName.length > 0)
		{
			$parentInputName.val('');
		}

		if($parentTarget.length > 0)
		{
			$parentTarget.text(label);
		}

		console.log(label);
	};


	/**
	 * New in v2.1.9 to eventually replace the eMediaSelect
	 * Handle Media-Picker selection.
	 * @param {object} that
	 *  Element that was clicked.
	 *
	 * @returns {boolean}
	 */
	e107.mediaManager.eMediaSelectProcess = function (that)
	{

		var $this = $(that);

		var id 				= $this.attr('data-id'); 	// ID of the Media Manager Item.
		var target 			= $this.attr('data-target'); // Path of the Media Manager Item.
		var path 			= $this.attr('data-path');
		var preview 		= $this.attr('data-preview');
		var previewHTML 	= $this.attr('data-preview-html');
		var src 			= $this.attr('data-src');
		var bbcode 			= $this.attr('data-bbcode'); // TinyMce/Textarea insert mode-
		var name 			= $this.attr('data-name'); // Title of the Media Manager Item.
		var width 			= $this.attr('data-width');
		var height 			= ''; // Disable for now - will be updated by bb parser. $this.attr('data-height');
		var type 			= $this.attr('data-type');
		var alt 			= $this.attr('data-alt');

		var bbpath 			= '';

		// variable placeholders.
		var $bbcodeHolder 	= $('#bbcode_holder');
		var $htmlHolder 	= $('#html_holder');
		var $srcHolder 		= $('#src');
		var $pathHolder 	= $('#path');
		var $preview 		= $('img#preview');



		// Remove "selected" class from elements.
		$('.e-media-select').removeClass('media-select-active');

		// Add "selected" class to clicked element.
		$this.addClass('media-select-active');
		$this.closest('img').addClass('active');

		// convert base64 encoded HTML preview information to HTML.
		if(previewHTML)
		{
			var previewSrc = atob(previewHTML).trim();
		}
		
		if($htmlHolder.length > 0)
		{
			$htmlHolder.val(previewSrc);
		}

		if($pathHolder.length > 0)
		{
			$pathHolder.attr('value', path);
		}

		switch(type) {

			case 'file':
				bbpath = '[file=' + id + ']' + name + '[/file]';
				$bbcodeHolder.val(bbpath);
				break;

			case 'video':
			case 'glyph':
			case 'icon':
			case 'audio':
				$srcHolder.val('');
				bbpath = '[' + bbcode + ']' + path + '[/' + bbcode + ']';
				$bbcodeHolder.val(bbpath);
				break;


			case 'image':
				$srcHolder.attr('value', src);

				// Populate Attributes Tab with Info.
				
				$('#width').val(width);
				$('#height').val(height);
				$('#alt').val(alt);

				e107.mediaManager.eMediaAttribute($this, bbcode);
				$preview.attr('src',src);
			//	$preview.val(preview);
				$srcHolder.attr('value', src);

				break;

		}

		console.log("Mode: MediaPicker");
		console.log("Src:" + src);
		console.log("Bbpath: " + $bbcodeHolder.val());
		console.log("Preview: "+preview);
		console.log("Save Path: "+path);

		// Update mediapicker preview with html content.
		$('div#' + target + "_prev", window.top.document).html(previewSrc); // set new value
		$('span#' + target + "_prev", window.top.document).html(previewSrc); // set new value

		// Update hidden form targets with data that can be saved to the DB.
		if(target !== '')
		{
			if($('input#' + target) !== undefined)
			{
				$('input#' + target, window.top.document).attr('value', path); // set new value
			}

			// Array mode:
			var pathTarget = target + '-path';
			var nameTarget = target + '-name';
			var idTarget = target + '-id';

			if($('input#' + pathTarget) !== undefined)
			{
				$('input#' + pathTarget, window.top.document).attr('value', path); // set new value
			}

			if($('input#' + nameTarget) !== undefined)
			{
				$('input#' + nameTarget, window.top.document).attr('value', name); // set new value
			}

			if($('input#' + idTarget) !== undefined)
			{
				$('input#' + idTarget, window.top.document).attr('value', id); // set new value
			}
		}

		return false;

	};







	/**
	 * @deprecated - to eventually be replaced by the method above.
	 * @param {object} that
	 *  Element that was clicked.
	 *
	 * @returns {boolean}
	 */
	e107.mediaManager.eMediaSelect = function (that)
	{



		var $this = $(that);
		// ID of the Media Manager Item.
		var id = $this.attr('data-id');
		var target = $this.attr('data-target');
		// Path of the Media Manager Item.
		var path = $this.attr('data-path');
		var preview = $this.attr('data-preview');
		var previewHTML = $this.attr('data-preview-html');
		var src = $this.attr('data-src');
		// TinyMce/Textarea insert mode-
		var bbcode = $this.attr('data-bbcode');
		// Title of the Media Manager Item.
		var name = $this.attr('data-name');
		var width = $this.attr('data-width');
		// Disable for now - will be updated by bb parser.
		// var height = $this.attr('data-height');
		var height = '';
		var type = $this.attr('data-type');
		var alt = $this.attr('data-alt');
		var bbpath = '';

		var $bbcodeHolder = $('#bbcode_holder');
		var $htmlHolder = $('#html_holder');

		var $src = $('#src');
		var $preview = $('#preview');
		var $path = $('#path');




		// Remove "selected" class from elements.
		$('.e-media-select').removeClass('media-select-active');

		// Add "selected" class to clicked element.
		$this.addClass('media-select-active');
		$this.closest('img').addClass('active');


console.log('Bbcode: '+bbcode);

		

		if(bbcode === "file" && $bbcodeHolder.length > 0) // not needed for Tinymce
		{
			bbpath = '[file=' + id + ']' + name + '[/file]';
			$bbcodeHolder.val(bbpath);
			return;
		}

		if((bbcode === "video" || bbcode === "img") && $bbcodeHolder.length > 0)
		{
			e107.mediaManager.eMediaAttribute($this, bbcode);
			bbpath = '[' + bbcode + ']' + path + '[/' + bbcode + ']';
			$bbcodeHolder.val(bbpath);

		}

		if(bbcode === "glyph" && $bbcodeHolder.length > 0)
		{
			var $target = $('div#' + target + "_prev", window.top.document);
			
			// Only if  the triggering element is not an icon-picker.
			if($target.length === 0 || !$target.hasClass('image-selector'))
			{
				bbpath = '[' + bbcode + ']' + path + '[/' + bbcode + ']';
				$bbcodeHolder.val(bbpath);
			}
		}

		if($src.length > 0)
		{
			$src.attr('value', src); // working old
			$src.attr('src', src);	// working old
		}

		if($preview.length > 0)
		{
			$preview.attr('src', preview);	// working old
		}

		if($path.length > 0)
		{
			$path.attr('value', path); // working old
		}

		$('#width').val(width);
		$('#height').val(height);
		$('#alt').val(alt);

		$('img#' + target + "_prev", window.top.document).attr('src', preview); // set new value


		if(type === 'glyph')
		{
			preview = "<span class='" + src + "'>&nbsp;</span>";

			if($htmlHolder.length > 0)
			{
				$htmlHolder.val(preview);
			}

			if($path.length > 0)
			{
				$path.attr('value', path);
			}
		}
		else if(type === 'file')
		{
			preview = name;
		}
		else // image
		{
			e107.mediaManager.eMediaAttribute($this, bbcode);

			preview = '';

			if($htmlHolder.length > 0)
			{
				preview = $htmlHolder.val();
			}

			// issue #3051 Preview url is wrong when target page is a plugin
			var s = new RegExp('/' + e107_plugins_directory + '[\\w]+/', 'gmi');
			if (window.top.document.URL.match(s))
			{
				preview = preview.replace(e107_plugins_directory, '');
			}
			console.log("Mode: Image");
		}




		$('div#' + target + "_prev", window.top.document).html(preview); // set new value
		$('span#' + target + "_prev", window.top.document).html(preview); // set new value

		// @see $frm->filepicker()
		if(target !== '')
		{
			if($('input#' + target) !== undefined)
			{
				$('input#' + target, window.top.document).attr('value', path); // set new value
			}

			// Array mode:
			var pathTarget = target + '-path';
			var nameTarget = target + '-name';
			var idTarget = target + '-id';

			if($('input#' + pathTarget) !== undefined)
			{
				$('input#' + pathTarget, window.top.document).attr('value', path); // set new value
			}

			if($('input#' + nameTarget) !== undefined)
			{
				$('input#' + nameTarget, window.top.document).attr('value', name); // set new value
			}

			if($('input#' + idTarget) !== undefined)
			{
				$('input#' + idTarget, window.top.document).attr('value', id); // set new value
			}
		}

		return false;
	};

	/**
	 * @param {object} that
	 *  Element that was clicked.
	 *
	 * @returns {boolean}
	 */
	e107.mediaManager.eDialogSave = function (that)
	{
		var $this = $(that);
		// FIXME TODO missing caret , text selection overwrite etc.
		var newval = $('#bbcode_holder').val();
		var target = $this.attr('data-target');
		var bbcode = $this.attr('data-bbcode'); // TinyMce/Textarea insert mode
		var close = $this.attr('data-close');



		if(!target || !bbcode)
		{
			console.log('eDialogSave: no target or bbcode');
			console.log('eDialogSave (bbcode): ' + bbcode);
			console.log('eDialogSave (target): ' + target);
			return true;
		}



		var $target = $('#' + target, window.top.document);

		if ($target.length > 0)
		{
			var targetType = $target.attr('type');

			// The input element's type ('hidden') does not support selection.
			if (targetType == 'hidden')
			{
				return true;
			}

			// http://code.google.com/p/jquery-at-caret/wiki/GettingStarted
			$('#' + target, window.top.document).atCaret('insert', newval);
		}

		if(close == 'true')
		{
			parent.$('.modal').modal('hide');
		}
	};

	/**
	 * @param {object} e
	 *  Element that was clicked.
	 * @param {string} navid
	 *  Class with 'data-src' that needs 'from=' value updated. (often 2 of them eg. next/prev)
	 *
	 * @returns {boolean}
	 */
	e107.mediaManager.mediaNav = function (e, navid)
	{
		var id = $(e).attr("href");
		var target = $(e).attr("data-target"); // support for input buttons etc.
		var loading = $(e).attr('data-loading'); // image to show loading.
		var search = $('#media-search').val(); // image to show loading.
		var nav = $(e).attr('data-nav-inc');
		var dir = $(e).attr('data-nav-dir');
		var curTotal = $('#admin-ui-media-select-count-hidden').attr('data-media-select-current-limit');
		var total = $(e).attr('data-nav-total');
		var outDir;
		var inDir;
		var newVal;

		if(nav !== null && navid !== null)
		{
			e107.callbacks.eNav(e, navid);
		}

		if(dir == 'down' && curTotal == 20)
		{
			return false;
		}

		if(dir == 'up' && curTotal == total)
		{
			return false;
		}

		if(target !== null)
		{
			id = '#' + target;
		}

		if(loading != null)
		{
			$(id).html("<img src='" + loading + "' alt='' />");
		}

		var src = $(e).attr("data-src"); // heep here.
		if(src === null) // old way - href='myscript.php#id-to-target
		{
			var tmp = src.split('#');
			id = tmp[1];
			src = tmp[0];
		}

		if(search !== null)
		{
			src = src + '&search=' + search;
		}

		if(dir == 'down')
		{
			outDir = 'right';
			inDir = 'left';
		}
		else
		{
			outDir = 'left';
			inDir = 'right';
		}

		$('#e-modal-loading', window.parent.document).show();
		$('iframe', window.parent.document).attr('scrolling', 'no'); // hide the scrollbar.

		// TODO use loading screen instead?
		$(id).hide('slide', {direction: outDir}, 1500, function ()
		{
			$.get(src, function (data)
			{
				$(id).html(data);
				newVal = $('#admin-ui-media-select-count-hidden').text();
				$('#admin-ui-media-select-count').text(newVal).fadeIn();

				$(id).show('slide', {direction: inDir}, 500, function ()
				{
					$('#e-modal-loading', window.parent.document).hide();
				});

				// We need to attach behaviors to the newly loaded contents.
				e107.attachBehaviors();
			});
		});


		$('iframe', window.parent.document).attr('scrolling', 'auto');

		return false;
	};

	/**
	 * @param {object} that
	 *  Input element.
	 */
	e107.mediaManager.eAjaxKeyUp = function (that)
	{
		var $this = $(that);
		var id = $this.attr("data-target");
		var src = $this.attr("data-src");
		var search = $this.val();



		if(search !== null)
		{
			search = search.replace('https://', 'url:');
			search = search.replace('http://', 'url:');
			src = src + '&search=' + encodeURIComponent(search);
		}

		var $target = $('#' + id);

		$target.fadeOut('fast');

		$target.load(src, function ()
		{
			$target.fadeIn('fast'); // .slideLeft();
			e107.attachBehaviors();
		});
	};

	/**
	 * Initializes 'plupload' plugin.
	 */
	e107.mediaManager.initPlUpload = function ()
	{
		var $uploader = $("#uploader");
		var upath = $uploader.attr("rel");
		var extImg = $uploader.attr("extimg");
		var extArchive = $uploader.attr("extarch");
		var extDoc = $uploader.attr("extdoc");

		$uploader.pluploadQueue({
			// General settings
			runtimes: "html5,html4",
			url: upath,
			max_file_size: $uploader.attr("data-max-size"),
			chunk_size: "1mb",
			unique_names: false,

			// Resize images on clientside if we can
			// resize : {width : 320, height : 240, quality : 90},

			// Specify what files to browse for
			filters: [
				{title: "Image files", extensions: extImg || "jpg,gif,png,jpeg,svg"},
				{title: "Zip files", extensions: extArchive || "zip,gz,rar"},
				{title: "Document files", extensions: extDoc || "pdf,doc,docx,xls,xlsm,xml"},
				{title: "Media files", extensions: 'mp3,mp4,wav,ogg,webm,mid,midi'},
				{title: "Other files", extensions: 'torrent,txt'}
			],
			preinit: {
				Init: function (up, info)
				{
					//log('[Init]', 'Info:', info, 'Features:', up.features);
				}
			},
			init: {
				BeforeUpload: function(e,file)
				{


				},
				BeforeChunkUpload: function(uploader, file, post, currentBlob, currentOffset)
				{
					//console.log(file);
              		// currentBlob.name=file.name;
       			},
				FilesAdded: function (up, files)
				{

				},
				FileUploaded: function (up, file, info) // Called when a file has finished uploading
				{
				//	console.log(up);
				//	console.log(file);
				//	console.log(file);
					var result = JSON.parse(info.response);
					if(result.error)
					{
						alert(file.name + ': '+ result.error.message);
					//	$('.plupload_buttons').show();
						return false;
					}
				//	console.log(result);

				},
				UploadProgress: function (up, file)
				{
					// Called while a file is being uploaded
				},
				UploadComplete: function (up, files)
				{
					document.location.reload(); // refresh the page.
				},
				ChunkUploaded: function (up, file, info)
				{
					// Called when a file chunk has finished uploading
				},
				Error: function (up, args)
				{
					// Called when a error has occured
					alert('There was an error');
				}
			}
		});
	};

})(jQuery);

