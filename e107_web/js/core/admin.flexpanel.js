var e107 = e107 || {'settings': {}, 'behaviors': {}};

(function ($)
{
	'use strict';

	e107.callbacks = e107.callbacks || {};

	e107.settings.flexPanel = {
		selector: '.draggable-panels',
		items: '> .panel'
	};

	/**
	 * Behavior to initialize draggable panels on the dashboard.
	 *
	 * @type {{attach: e107.behaviors.flexPanelDraggablePanels.attach}}
	 */
	e107.behaviors.flexPanelDraggablePanels = {
		attach: function (context, settings)
		{
			var selector = e107.settings.flexPanel.selector;
			var onceKey = 'admin-dashboard-draggable-panels';

			$(context).find(selector).once(onceKey).each(function ()
			{
				var $panel = $(this);

				$panel.sortable({
					connectWith: selector,
					items: e107.settings.flexPanel.items,
					handle: '.panel-heading',
					accept: e107.settings.flexPanel.selector,
					cursor: 'move',
					placeholder: 'draggable-placeholder',
					forcePlaceholderSize: true,
					helper: 'clone',
					forceHelperSize: true,
					opacity: 0.4,
					tolerance: 'pointer',
					start: function (event, ui)
					{
						var $placeholders = $('.draggable-placeholder');
						var $draggablePanels = $(e107.settings.flexPanel.selector);

						/*$placeholders.css('margin', '15px');*/
						$placeholders.css('background-color', '#337ab7');

						$draggablePanels.css('min-height', '80px');

						$draggablePanels.css('border', '1px dashed #CCCCCC');
						$draggablePanels.css('background-color', 'rgba(0,0,0,0.1)');
						$draggablePanels.css('margin-bottom', '30px');

						$panel.sortable("refreshPositions");
					},
					stop: function (event, ui)
					{
						var $draggablePanels = $(e107.settings.flexPanel.selector);
						$draggablePanels.css('min-height', '0');
						$draggablePanels.css('border', 'none');
						$draggablePanels.css('margin-bottom', '0');
						$draggablePanels.css('background-color', 'transparent');

						e107.callbacks.flexPanelSavePanelOrder();
                                                e107.callbacks.flexPanelEmptyPanels();
					}
				});
			});

                        e107.callbacks.flexPanelEmptyPanels();
		}
	};

	e107.callbacks.flexPanelSavePanelOrder = function ()
	{
		var selector = e107.settings.flexPanel.selector;
		var NewOrder = {};

		$(selector).each(function ()
		{
			var $this = $(this);
			var area = $this.attr('id');
			var weight = 0;

			if(area)
			{
				$('#' + area + ' ' + e107.settings.flexPanel.items).each(function ()
				{
					var $item = $(this);
					var panelID = $item.attr('id');

					NewOrder[panelID] = {
						area: area,
						weight: weight
					};

					weight++;
				});
			}
		});
		
		$.post(window.location.href, {'core-flexpanel-order': NewOrder});
	};

	e107.callbacks.flexPanelEmptyPanels = function ()
	{
		var selector = e107.settings.flexPanel.selector;

		$(selector).each(function ()
		{
			var $this = $(this);

			if($this.find('div').length > 0)
			{
				$this.removeClass('empty');
			}
			else
			{
				$this.addClass('empty');
			}
		});
	};

})(jQuery);
