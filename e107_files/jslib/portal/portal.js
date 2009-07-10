/**
	iGoogle-style Drag&Drop Portal v1

	Author: M.D.A. (Michel) Hiemstra
			mhiemstra@php.net
			www.michelhiemstra.nl

	Inspired by 'Drag/Drop Portal Interface with Drupal'

	Required: Prototype (1.6 and higher)
			  Script.aculo.us (with Effects and DragDrop) v1.8.2

	Recoded and optimized for Prototype 1.6.0.3

	Licenced CC BY-SA - http://creativecommons.org/licenses/by-sa/3.0/

	Usage:

		<script type="text/javascript">
			var settings = { 'portal-column-1':['block-test'] };
			var options = { editorEnabled : true, 'saveurl' : '/path/to/script' };
			var data = { };

			var portal;

			Event.observe(window, 'load', function() {
				portal = new Portal(settings, options, data);
			}, false);

		</script>

		<div id="content">
			<!-- These are destinations of 'blocks' -->
			<div id="portal">
				<div class="portal-column dir-horizontal" id="portal-column-0"></div>
				<div class="portal-column dir-vertical" id="portal-column-1"></div>
			</div>

			<!-- These are the blocks you can choose from -->
			<div class="portal-column" id="portal-column-block-list">
				<h2 class="block-list-handle">Block List</h2>

				<!-- Block: testblock -->
				<div class="block block-test" id="block-test">
					<h3 class="handle"><div class="block-controls" style="display: none;"><a class="block-remove"><span>x</span></a> <a class="block-config"><span>e</span></a></div>Testblock</h3>

					<div class="config" style="display: none;">
						<div>config-params</div>
						<div align="right">
							<a href="#" class="cancel-button">cancel</a>
							<a href="#" class="save-button">cancel</a>
						</div>
					</div>

					<div class="content">
						<div id="block-test-content">
							test
						</div>
					</div>
				</div>
				<!-- End: testblock -->

			</div>
		</div>
**/



var Portal = Class.create();

Portal.prototype = {

	initialize : function (settings, options, data) {
		// set options
		this.setOptions(options);

		// set blocks to their positions
		this.applySettings(settings);

		// load data to blocks
		this.loadData(data);

		// set styles of blocks
		this.set_styles();

		// if editor is enabled we proceed
		if (!this.options.editorEnabled) return;

		// get all available columns
		var columns = $(this.options.portal).getElementsByClassName(this.options.column);

		// loop trough columns array
		$A(columns).each(function(column) {

			// create sortable
			Sortable.create(column, {
				containment : $A(columns),
				constraint  : false,
				ghosting	: true,
				tag 		: 'div',
				only 		: this.options.block,
				dropOnEmpty : true,
				handle 		: this.options.handle,
				hoverclass 	: this.options.hoverclass,

				onUpdate 	: function (container) {

					// if we dont have a save url we dont update
					if (!this.options.saveurl) return;

					// if we are in the same container we do nothing
					if (container.id == this.options.blocklist) return;

					// get blocks in this container
					var blocks = container.getElementsByClassName(this.options.block);

					// serialize all blocks in this container
					var postBody = container.id + ':';
					postBody += $A(blocks).pluck('id').join(',');
					postBody = 'value=' + escape(postBody);

					// save it to the database
					//new Ajax.Request(this.options.saveurl, { method: 'post', postBody: postBody });

					/* DEMO ONLY, REMOVE THIS */
					new Ajax.Updater('debug', this.options.saveurl, {
						method: 'post', postBody: postBody,
						insertion: Insertion.Top
					});


					// reset styles
					this.set_styles();

				}.bind(this)
			});

		}.bind(this));


		//-----------//

		// get all blocks
		var blocks = $(this.options.portal).getElementsByClassName(this.options.block);

		// loop trough blocks
		$A(blocks).each(function(block) {

			// enable controls if available
			if (typeof(block.getElementsByClassName('block-controls').item(0)) == 'object') {
				block.getElementsByClassName('block-controls').item(0).setStyle({'display' : 'block'});
			}

			// detail, set cursor style to move when in admin modus
			if (typeof(block.getElementsByClassName('handle').item(0)) == 'object') {
			  //	block.getElementsByClassName('handle').item(0).setStyle({'cursor' : 'move'});
			}

			// toggle configuration element
			if (typeof(block.getElementsByClassName(this.options.config).item(0)) == 'object') {
				Event.observe(block.getElementsByClassName(this.options.config).item(0), 'click', function () {
					block.getElementsByClassName(this.options.configElement).item(0).toggle();
				}.bind(this));
			}

			// observe save button
			if (typeof(block.getElementsByClassName(this.options.configSave).item(0)) == 'object') {
				Event.observe(block.getElementsByClassName(this.options.configSave).item(0), 'click', function (e) {
					alert('save');
				}.bind(this));
			}

			// observe cancel button
			if (typeof(block.getElementsByClassName(this.options.configCancel).item(0)) == 'object') {
				Event.observe(block.getElementsByClassName(this.options.configCancel).item(0), 'click', function (e) {
					block.getElementsByClassName(this.options.configElement).item(0).toggle();
				}.bind(this));
			}

			// observe delete block button
			if (typeof(block.getElementsByClassName(this.options.remove).item(0)) == 'object') {
				Event.observe(block.getElementsByClassName(this.options.remove).item(0), 'click', function (e) {
					if (confirm('Are you sure you wish to delete this block?')) {
					  //	alert(this.options.saveurl + '?delete')
						new Ajax.Request(this.options.saveurl + 'delete', { method: 'post', postBody: 'block='+block.id }); $(block.id).hide();
					}
				}.bind(this));
			}

		}.bind(this));

	},

	set_styles : function () {

		// get all blocks
		var blocks = $(this.options.portal).getElementsByClassName(this.options.block);

		// loop trough blocks
		$A(blocks).each(function(block) {
			if (block.up().hasClassName('dir-vertical')) {

				block.removeClassName('block-horizontal').addClassName('block-vertical');
			} else {
				block.removeClassName('block-vertical').addClassName('block-horizontal');
			}
		}.bind(this));

	},

	applySettings : function (settings) {
		// apply settings to the array
		for (var container in settings) {
			settings[container].each(function (block) { $(container).appendChild($(block)); });
		}
	},

	setOptions : function (options) {
		// set options
		this.options = {
			editorEnabled 	: false,
			portal			: 'portal',
			column			: 'portal-column',
			block			: 'block',
			content			: 'content',
			configElement	: 'config',
			configSave		: 'save-button',
			configCancel	: 'cancel-button',
			handle			: 'handle',
			hoverclass		: false,
			remove			: 'block-remove',
			config			: 'block-config',
			blocklist		: 'portal-column-block-list',
			blocklistlink	: 'portal-block-list-link',
			blocklisthandle : 'block-list-handle',
			saveurl			: false
		}

		Object.extend(this.options, options || {});
	},

	loadData : function (data) {
		// load data for each block
		for (var type in data) {
			data[type].each(function(block) {
				for (var blockname in block) {
					// your code to load data here
					new Ajax.Updater(blockname + '-content', '/url?'+type+'?data='+block[blockname]);
				}
			});
		}
	}
};