var e107 = e107 || {'settings': {}, 'behaviors': {}};

(function ($)
{

	/**
	 * Behavior to initialize gallery slideshow.
	 *
	 * @type {{attach: Function}}
	 */
	e107.behaviors.galleryCycle = {
		attach: function (context, settings)
		{
			$(context).find("#gallery-slideshow-content").one('gallery-slideshow-content').each(function ()
			{
				$(this).cycle({
					fx: settings.gallery.fx,
					next: '.gal-next',
					prev: '.gal-prev',
					speed: settings.gallery.speed,  // speed of the transition (any valid fx speed value)
					timeout: settings.gallery.timeout,
					slideExpr: '.slide',
                    cleartypeNoBg:    true, 
					pause: 1, // pause on hover - TODO pref
					activePagerClass: '.gallery-slide-jumper-selected',
					before: function (currSlideElement, nextSlideElement, options, forwardFlag)
					{
						var nx = $(nextSlideElement).attr('id').split('item-');
						var th = $(currSlideElement).attr('id').split('item-');
						$('#gallery-jumper-' + th[1]).removeClass('gallery-slide-jumper-selected');
						$('#gallery-jumper-' + nx[1]).addClass('gallery-slide-jumper-selected');
					}
				});
			});

			$(context).find(".gallery-slide-jumper").one('gallery-slide-jumper').each(function ()
			{
				$(this).click(function ()
				{
					var nid = $(this).attr('id');
					var id = nid.split('-jumper-');
					var go = parseInt(id[1]) - 1;
					$('#gallery-slideshow-content').cycle(go);
					return false;
				});
			});
		}
	};

})(jQuery);
