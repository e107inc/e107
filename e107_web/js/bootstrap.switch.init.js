var e107 = e107 || {'settings': {}, 'behaviors': {}};

(function ($)
{

	/**
	 * @type {{attach: e107.behaviors.bootstrapSwitchInit.attach}}
	 */
	e107.behaviors.bootstrapSwitchInit = {
		attach: function (context, settings)
		{
			if(typeof settings.bsSwitch === 'undefined' || settings.bsSwitch.length == 0)
			{
				return;
			}

			$.each(settings.bsSwitch, function (name, options)
			{
				$('input[name="' + name + '"]', context).once('bootstrap-switch-init').each(function ()
				{
					$(this).bootstrapSwitch({
						size: options.size || 'mini',
						onText: options.onText || null,
						offText: options.offText || null,
						wrapperClass: options.wrapperClass || null
					});
				});
			});
		}
	};

})(jQuery);
