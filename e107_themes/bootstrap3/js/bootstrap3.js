var e107 = e107 || {'settings': {}, 'behaviors': {}};

(function ($)
{
	'use strict';

	/**
	 * Initializes draggable panels on the dashboard.
	 *
	 * @type {{attach: e107.behaviors.adminDashboardDraggablePanels.attach}}
	 */
	e107.behaviors.adminDashboardDraggablePanels = {
		attach: function (context, settings)
		{
			var selector = '.draggable-panels';
			var onceKey = 'admin-dashboard-draggable-panels';

			$(context).find(selector).once(onceKey).each(function ()
			{
				var $panel = $(this);

				$panel.sortable({
					connectWith: selector,
					handle: '.panel-heading',
					cursor: 'move',
					placeholder: 'placeholder',
					forcePlaceholderSize: true,
					opacity: 0.4,
					stop: function (event, ui)
					{
						var SortOrder = "SortOrder:\n";
						var i = 0;

						$(selector + " .panel-title").each(function ()
						{
							i++;
							var $this = $(this);
							var title = $this.text();
							SortOrder += i + " - " + title + "\n";
						});

						console.log(SortOrder);
					}
				});

				$panel.disableSelection();

			});
		}
	};

})(jQuery);
