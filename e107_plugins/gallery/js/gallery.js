var e107 = e107 || {'settings': {}, 'behaviors': {}};

(function ($)
{

	/**
	 * Behavior to initialize prettyPhoto on gallery elements.
	 *
	 * @type {{attach: Function}}
	 */
	e107.behaviors.gallery = {
		attach: function (context, settings)
		{
			var pPhoto = settings.gallery.prettyphoto || {};
			var pPhook = pPhoto.hook || 'data-gal';

			$(context).find("a[" + pPhook + "^='prettyPhoto']").once('gallery-prettyPhoto').each(function ()
			{
				$(this).prettyPhoto(
					{
						hook: pPhook,
						animation_speed: pPhoto.animation_speed || 'fast', // fast/slow/normal
						slideshow: pPhoto.slideshow || 5000, // false OR interval time in ms
						autoplay_slideshow: pPhoto.autoplay_slideshow || false, // true/false
						opacity: pPhoto.opacity || 0.80, // Value between 0 and 1
						show_title: pPhoto.show_title || true, // true/false
						allow_resize: pPhoto.allow_resize || true, // Resize the photos bigger than viewport. true/false
						default_width: pPhoto.default_width || 500,
						default_height: pPhoto.default_height || 344,
						counter_separator_label: pPhoto.counter_separator_label || '/', // The separator for the gallery counter 1 "of" 2
						theme: pPhoto.theme || 'pp_default', // light_rounded / dark_rounded / light_square / dark_square / facebook
						horizontal_padding: pPhoto.horizontal_padding || 20, // The padding on each side of the picture
						hideflash: pPhoto.hideflash || false, // Hides all the flash object on a page, set to TRUE if flash appears over prettyPhoto
						wmode: pPhoto.wmode || 'opaque', // Set the flash wmode attribute
						autoplay: pPhoto.autoplay || true, // Automatically start videos: true/false
						modal: pPhoto.modal || false, // If set to true, only the close button will close the window
						deeplinking: pPhoto.deeplinking || false, // Allow prettyPhoto to update the url to enable deeplinking.
						overlay_gallery: pPhoto.overlay_gallery || true, // If set to true, a gallery will overlay the fullscreen image on mouse over
						keyboard_shortcuts: pPhoto.keyboard_shortcuts || true, // Set to false if you open forms inside prettyPhoto
						ie6_fallback: pPhoto.ie6_fallback || true, // true/false
						markup: pPhoto.markup || null,
						gallery_markup: pPhoto.gallery_markup || null,
						image_markup: pPhoto.image_markup || null,
						flash_markup: pPhoto.flash_markup || null,
						quicktime_markup: pPhoto.quicktime_markup || null,
						iframe_markup: pPhoto.iframe_markup || null,
						inline_markup: pPhoto.inline_markup || null,
						custom_markup: pPhoto.custom_markup || null,
						social_tools: pPhoto.social_tools || null,
						changepicturecallback: function ()
						{
							var $ppContent = $(".pp_content");
							$ppContent.css("height", $ppContent.height() + jQuery(".download-btn").outerHeight() + 10);
						}
					}
				);
			});
		}
	};

})(jQuery);
