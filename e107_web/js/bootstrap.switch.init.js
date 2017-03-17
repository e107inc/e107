var e107 = e107 || {'settings': {}, 'behaviors': {}};

(function ($)
{

	/**
	 * @type {{attach: e107.behaviors.bootstrapSwitchInit.attach}}
	 */
	e107.behaviors.bootstrapSwitchInit = {
		attach: function (context, settings)
		{
			if(typeof $.fn.bootstrapSwitch === 'undefined')
			{
				return;
			}

			$('input[data-type="switch"]', context).once('bootstrap-switch-init').each(function ()
			{
				var $this = $(this);

				if($this.attr('type') != 'hidden')
				{
					$this.bootstrapSwitch({
						size: $this.data('size') || 'mini',
						onText: $this.data('on') || null,
						offText: $this.data('off') || null,
						wrapperClass: $this.data('wrapper') || null
					});

					$this.on('switchChange.bootstrapSwitch', function (event, state)
					{
						var name = $this.data('name');
						var checked = true;

						if(state === false)
						{
							checked = false;
						}

						var value = checked ? 1 : 0;

						if($this.data('inverse') == 1)
						{
							value = checked ? 0 : 1;
						}

						$('input[type="hidden"][name="' + name + '"]').val(value).trigger('change');

						return false;
					});
				}
			});
		}
	};

})(jQuery);
