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

			$(context).find("a[" + pPhook + "^='prettyPhoto']").one('gallery-prettyPhoto').prettyPhoto(
				{
					hook: pPhook,
					animation_speed: pPhoto.animation_speed, // fast/slow/normal
					slideshow: pPhoto.slideshow, // false OR interval time in ms
					autoplay_slideshow: pPhoto.autoplay_slideshow, // true/false
					opacity: pPhoto.opacity, // Value between 0 and 1
					show_title: pPhoto.show_title, // true/false
					allow_resize: pPhoto.allow_resize, // Resize the photos bigger than viewport. true/false
					default_width: pPhoto.default_width,
					default_height: pPhoto.default_height,
					counter_separator_label: pPhoto.counter_separator_label, // The separator for the gallery counter 1 "of" 2
					theme: pPhoto.theme, // light_rounded / dark_rounded / light_square / dark_square / facebook
					horizontal_padding: pPhoto.horizontal_padding, // The padding on each side of the picture
					hideflash: pPhoto.hideflash, // Hides all the flash object on a page, set to TRUE if flash appears over prettyPhoto
					wmode: pPhoto.wmode, // Set the flash wmode attribute
					autoplay: pPhoto.autoplay, // Automatically start videos: true/false
					modal: pPhoto.modal, // If set to true, only the close button will close the window
					deeplinking: pPhoto.deeplinking, // Allow prettyPhoto to update the url to enable deeplinking.
					overlay_gallery: pPhoto.overlay_gallery, // If set to true, a gallery will overlay the fullscreen image on mouse over
					keyboard_shortcuts: pPhoto.keyboard_shortcuts, // Set to false if you open forms inside prettyPhoto
					ie6_fallback: pPhoto.ie6_fallback, // true/false
					markup: pPhoto.markup,
					gallery_markup: pPhoto.gallery_markup,
					image_markup: pPhoto.image_markup,
					flash_markup: pPhoto.flash_markup,
					quicktime_markup: pPhoto.quicktime_markup,
					iframe_markup: pPhoto.iframe_markup,
					inline_markup: pPhoto.inline_markup,
					custom_markup: pPhoto.custom_markup,
					social_tools: pPhoto.social_tools,
					changepicturecallback: function ()
					{
						var $ppContent = $(".pp_content");
						$ppContent.css("height", $ppContent.height() + jQuery(".download-btn").outerHeight() + 10);
					}
				}
			);
		}
	};

})(jQuery);
