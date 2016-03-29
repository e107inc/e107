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
			$(context).find("a[data-gal^='prettyPhoto']").once('gallery-prettyPhoto').each(function ()
			{
				$(this).prettyPhoto(
					{
						hook: 'data-gal',
						theme: 'pp_default', /* pp_default , light_rounded , dark_rounded , light_square , dark_square ,facebook */
						overlay_gallery: false,
						deeplinking: false
					}
				);
			});
		}
	};

})(jQuery);
