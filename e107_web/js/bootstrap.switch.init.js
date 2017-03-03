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
					if($(this).attr('type') != 'hidden')
					{
						$(this).bootstrapSwitch({
							size: options.size || 'mini',
							onText: options.onText || null,
							offText: options.offText || null,
							wrapperClass: options.wrapperClass || null,
						//	disabled: false
						//	state: $('input[type="hidden"][name="' + name + '"]').data('on')
							// inverse: options.inverse // this is 'reverse' - default values but reversed order.
						});



						$(this).on('switchChange.bootstrapSwitch', function (event, state) {

							var tmp = $(this).attr('name').split('__');

							var name = tmp[0]; // $(this).attr('name');
							var checked = true;

							if(state === false)
							{
								checked = false;
							}

							var value = checked ? 1 : 0;

							if(options.inverse)
							{
								 value = checked ? 0 : 1;
							}

							$('input[type="hidden"][name="' + name + '"]').val(value);

						//	event.preventDefault();
						});
					}
				});
			});
		}
	};

})(jQuery);
