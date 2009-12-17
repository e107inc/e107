
/**
 * Global prefs
 */
e107Base.setPrefs('core-dialog', {
	id: null,
	theme : '',
	shadow: false,
	shadowTheme: '',
	top: null,
	left: null,
	width: 300,
	height: 200,
	minWidth: 200,
	minHeight: 100,
	maxHeight: null,
	maxWidth: null,
	gridX: 1,
	gridY: 1,
	wired: false,
	draggable : true,
	resizable : true,
	activeOnClick : true,
	show: Element.show,
	hide: Element.hide,
	maximizeEffects: true,
	dialogManager: null,
	positionningStrategyOffset: null,
	close: 'destroy', // e107Widgets.Dialog method for closing dialog or false to disable
	maximize: 'toggleMaximize', // e107Widgets.Dialog method for closing dialog or false to disable
});


e107Widgets.Dialog = Class.create(e107WidgetAbstract, {
	Version : '1.0',
	style : "position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;",
	wiredElement: null,
	events : null,
	element : null, // window element
	header : null, // window header element
	content : null, // window content element
	footer : null, // window footer element
	visible: false, // window visibility
	focused: false, // windows on focus
	modal: false, // modal window
	zIndex: 0,
	shadow: null,
	
	action: function(name) {
		var action = this.options[name];
		if (action)
			Object.isString(action) ? this[action]() : action.call(this, this);
	},
	
	initialize : function(options) {
		this.events = new e107EventManager(this);
		
		this.initMod('core-dialog', options);
		this.dialogManager = this.options.dialogManager || e107Widgets.DialogManagerDefault;
		if(this.options.id && this.dialogManager.getWindow($(this.options.id))) return;
		
		this.setMethodChains();

		this.create();
		this.id = this.element.id;

		this.dialogManager.register(this);
		this.render();
		
		if (this.options.activeOnClick)
			this.overlay.setStyle({
				zIndex: this.lastZIndex + 1
			}).show();
	},

	getTheme: function() {
		return this.options.theme || this.dialogManager.getTheme();
	},

	setTheme: function(theme, managerTheme) {
		this.element.removeClassName(this.getTheme()).addClassName(theme);
		// window has it's own theme
		if (!managerTheme)
			this.options.theme = theme;

		return this;
	},

	// shadows are under construction
	getShadowTheme: function() {
		return this.options.shadowTheme || this.dialogManager.getShadowTheme();
	},
	
	setMethodChains: function() {

		this.events.observe('create', this.createButtons)//bind create and createButtons
				.observe('destroy', this.destroyButtons); //bind destroy and destroyButtons

		
		if(Object.isUndefined(e107Widgets.Shadow))
			this.options.shadow = false;

		if(this.options.shadow) {
			this.events.observe('create', this.createShadow)
				.observe('addElements', this.addElementsShadow)
				.observe('setZIndex:pre', this.setZIndexShadow)
				.observe('setPosition', this.setPositionShadow)
				.observe('setSize', this.setSizeShadow)
				.observe('setBounds', this.setBoundsShadow);
		}		
	},

	create : function() {
		function createDiv(className, options) {
			return new Element('div', Object.extend( {
				className : className
			}, options));
		}

		// Main div FIXME - this.options.className should go to dialogManager.className
		this.element = createDiv(this.dialogManager.options.className + " " + this.getTheme(), {
			id: this.options.id,
			style: "top:-10000px; left:-10000px"
		});

		// Create window HTML code
		this.header = createDiv('n move_handle').enableDrag();
		this.content = createDiv('content').appendText(' ');
		this.footer = createDiv('s move_handle').enableDrag();

		var header = createDiv('nw').insert( createDiv('ne').insert(this.header));
		var content = createDiv('w').insert(createDiv('e', {
			style : "position:relative"
		}).insert(this.content));
		var footer = createDiv('sw').insert(createDiv('se' + (this.options.resizable ? " se_resize_handle" : "")).insert(this.footer));

		this.element.insert(header).insert(content).insert(footer);
		this.options.id = this.element.identify();
		this.header.observe('mousedown', this.activate.bind(this));

		this.setDraggable(this.options.draggable);
		this.setResizable(this.options.resizable);

		this.overlay = new Element('div', {
			style : this.style + "display: none"
		}).observe('mousedown', this.activate.bind(this));

		if (this.options.activeOnClick)
			this.content.insert( {
				before : this.overlay
			});
		this.events.notify('create');
	},

	createWiredElement: function() {
		this.wiredElement = this.wiredElement || new Element("div", {
			className: this.getTheme() + "_wired",
			style: "display: none; position: absolute; top: 0; left: 0"
		});
	},

	createResizeHandles: function() {
		$w("n  w  e  s  nw  ne  sw  se").each(function(id) {
			this.insert(new Element("div", {
				className: id + "_sizer resize_handle",
				drag_prefix: id
			}).enableDrag());
		}, this.element);
		this.createResizeHandles = Prototype.emptyFunction;
	},
	
	// First rendering, pre-compute window border size
	render: function() {
		this.addElements();
		
		this.computeBorderSize();
		this.updateButtonsOrder();
		this.element.hide().remove();
		
		// this.options contains top, left, width and height keys
		return this.setBounds(this.options);
	},

	show: function(modal) {
		if (this.visible)
			return this;

		this.fire('showing');
		this.effect('show');

		if (modal) {
			this.dialogManager.startModalSession(this);
			this.modalSession = true;
		}

		this.addElements();
		this.visible = true;
		
		new PeriodicalExecuter(function(executer) {
			if (!this.element.visible())
				return;
			this.fire('shown');
			executer.stop();
		}.bind(this), 0.1);

		return this;
	},
	
	/*
	 * Method: hide Hides the window, (removes it from the DOM)
	 * 
	 * Returns: this
	 */
	hide: function() {
		if (!this.visible)
			return this;

		this.fire('hiding');
		this.effect('hide');

		if (this.modalSession) {
			this.dialogManager.endModalSession(this);
			this.modalSession = false;
		}

		new PeriodicalExecuter(function(executer) {
			if (this.element.visible())
				return;
			this.visible = false;
			this.element.remove();
			this.fire('hidden');
			executer.stop();
		}.bind(this), 0.1);

		return this;
	},

	close: function() {
		return this.action('close');
	},
	
	activate: function() {
		return this.bringToFront().focus();
	},

	bringToFront: function() {
		return this.setAltitude('front');
	},

	sendToBack: function() {
		return this.setAltitude('back');
	},

	focus: function() {
		if (this.focused)
			return this;

		this.dialogManager.focus(this);
		// Hide the overlay that catch events
		this.overlay.hide();
		// Add focused class name
		this.element.addClassName(this.getTheme() + '_focused');

		this.focused = true;
		this.fire('focused');
		return this;
	},

	/*
	 * Method: blur Blurs the window (without changing windows order)
	 */
	blur: function() {
		if (!this.focused)
			return this;

		this.dialogManager.blur(this);
		this.element.removeClassName(this.getTheme() + '_focused');

		// Show the overlay to catch events
		if (this.options.activeOnClick)
			this.overlay.setStyle( {
				zIndex: this.lastZIndex + 1
			}).show();

		this.focused = false;
		this.fire('blurred');
		return this;
	},
	
	/*
	 * Method: maximize Maximizes window inside its viewport (managed by
	 * WindowManager) Makes window take full size of its viewport
	 */
	maximize: function() {
		if (this.maximized)
			return this;

		// Get bounds has to be before this.dialogManager.maximize for IE!!
		// this.dialogManager.maximize remove overflow
		// and it breaks this.getBounds()
		var bounds = this.getBounds();
		if (this.dialogManager.maximize(this)) {
			this.disableButton('minimize').setResizable(false).setDraggable(false);

			this.activate();
			this.maximized = true;
			this.savedArea = bounds;
			var newBounds = Object.extend(this.dialogManager.viewport.getDimensions(), {
				top: 0,
				left: 0
			});
			this[this.options.maximizeEffects && !e107API.Browser.IE ? "morph" : "setBounds"](newBounds);

			this.fire('maximized');
			return this;
		}
	},

	/*
	 * Function: restore Restores a maximized window to its initial size
	 */
	restore: function() {
		if (!this.maximized)
			return this;

		if (this.dialogManager.restore(this)) {
			this[this.options.maximizeEffects && !e107API.Browser.IE ? "morph" : "setBounds"](this.savedArea);
			this.enableButton("minimize").setResizable(true).setDraggable(true);

			this.maximized = false;

			this.fire('restored');
			return this;
		}
	},

	/*
	 * Function: toggleMaximize Maximizes/Restores window inside it's
	 * viewport (managed by DialogManager)
	 */
	toggleMaximize: function() {
		return this.maximized ? this.restore() : this.maximize();
	},

	/*
	 * Function: adapt Adapts window size to fit its content
	 * 
	 * Returns: this
	 */
	adapt: function() {
		var dimensions = this.content.getScrollDimensions();
		if (this.options.superflousEffects)
			this.morph(dimensions, true);
		else
			this.setSize(dimensions.width, dimensions.height, true);
		return this;
	},
	
	/*
	 * Method: destroy Destructor, closes window, cleans up DOM and memory
	 */
	destroy: function() {
		this.hide();
		if (this.centerOptions)
			Event.stopObserving(this.dialogManager.scrollContainer, "scroll", this.centerOptions.handler);
		
		this.dialogManager.unregister(this);
		this.events.notify('destroy');
		this.fire('destroyed');
	},
	
	setHeader: function(header) {
	    this.header.update(header);
	    return this;
	},
	
	setContent: function(content) {
	    this.content.update(content);
	    return this;
	},
	
	setAjaxContent: function(url, options) {
		if (!options)
			options = {};

		// bind all callbacks to the window
		Object.keys(options).each(function(name) {
			if (Object.isFunction(options[name]))
				options[name] = options[name].bind(this);
		}, this);

		var onComplete = options.onComplete;
		options.onComplete = (function(response, json) {
			this.setContent(response.responseText);
			if (Object.isFunction(onComplete))
				onComplete(response, json);
		}).bind(this);

		new e107Ajax.Request(url, options);
		return this;
	},
	
	setFooter : function(footer) {
		this.footer.update(footer);
		return this;
	},
	
	getPosition: function() {
		return {
			left: this.options.left,
			top: this.options.top
		};
	},

	setPosition: function(top, left) { 
		var pos = this.computePosition(top, left);
		this.options.top = pos.top;
		this.options.left = pos.left;

		var elementStyle = this.element.style;
		elementStyle.top = pos.top + 'px';
		elementStyle.left = pos.left + 'px';
		
		this.events.notify('setPosition', top, left);
		this.fire('position:changed');
		return this;
	},

	center: function(options) {
		var size = this.getSize(), dManager = this.dialogManager, viewport = dManager.viewport;
		viewportArea = viewport.getDimensions(), offset = viewport.getScrollOffset();
		
		if (options && options.auto) {
			this.centerOptions = Object.extend( {
				handler: this.recenter.bind(this)
			}, options);
			Event.observe(dManager.scrollContainer, "scroll", this.centerOptions.handler);
			Event.observe(window, "resize", this.centerOptions.handler);
		}

		options = Object.extend( {
			top: (viewportArea.height - size.height) / 2,
			left: (viewportArea.width - size.width) / 2
		}, options || {});
		
		return this.setPosition(options.top + offset.top, options.left + offset.left);
	},

	getSize: function(innerSize) {
		if (innerSize)
			return {
				width: this.options.width - this.borderSize.width,
				height: this.options.height - this.borderSize.height
			}
		else
			return {
				width: this.options.width,
				height: this.options.height
			};
	},

	/*
	 * innerSize: if true change set content size, else set window size
	 * (defaults to false)
	 */
	setSize: function(width, height, innerSize) {
		var size = this.computeSize(width, height, innerSize);
		var elementStyle = this.element.style, contentStyle = this.content.style;

		this.options.width = size.outerWidth;
		this.options.height = size.outerHeight;

		elementStyle.width = size.outerWidth + "px", elementStyle.height = size.outerHeight + "px";
		contentStyle.width = size.innerWidth + "px", contentStyle.height = size.innerHeight + "px";
		this.overlay.style.height = size.innerHeight + "px";

		this.events.notify('setSize', width, height, innerSize);
		this.fire('size:changed');
		return this;
	},

	/*
	 * innerSize: returns content size if true, window size
	 * otherwise
	 */
	getBounds: function(innerSize) {
		return Object.extend(this.getPosition(), this.getSize(innerSize));
	},

	/*
	 * Method: setBounds Sets window bounds (in pixels), fires
	 * position:changed and size:changed
	 * 
	 * Parameters bounds: Hash {top:, left:, width:, height:} where all
	 * values are optional 
	 * innerSize: sets content size if true, window size
	 * otherwise
	 * 
	 * Returns: Hash {top:, left:, width:, height:}
	 */
	setBounds: function(bounds, innerSize) {
		var ret = this.setPosition(bounds.top, bounds.left).setSize(bounds.width, bounds.height, innerSize);
		this.events.notify('setBounds', bounds, innerSize);
		return ret;
	},
	
	morph: function(bounds, innerSize) {
		bounds = Object.extend(this.getBounds(innerSize), bounds || {});
			
		if (this.centerOptions && this.centerOptions.auto)
			bounds = Object.extend(bounds, this.computeRecenter(bounds));

		if (innerSize) {
			bounds.width += this.borderSize.width;
			bounds.height += this.borderSize.height;
		}

		if (this.options.wired) {
			this.createWiredElement();
			if(this.shadow) this.shadow.hide();
			this.wiredElement.style.cssText = this.element.style.cssText;
			this.element.hide();
			this.savedElement = this.element;
			this.dialogManager.container.appendChild(this.wiredElement);
			this.element = this.wiredElement;
		}

		this.animating = true;

		new e107Widgets.Dialog.Effects.Morph(this, bounds, {
			duration: 0.3,
			afterFinish: function() {
				this.animating = false;
				if (this.options.wired) {
					this.savedElement.style.cssText = this.wiredElement.style.cssText;
					this.wiredElement.remove();
					this.element = this.savedElement;
					if(this.shadow) this.shadow.show();
					this.savedElement = false;
				}
			}.bind(this)
		});

		Object.extend(this.options, bounds);
		return this;
	},
	
	/*
	 * Method: getAltitude Returns window altitude, an integer between 0 and
	 * the number of windows, the higher the altitude number - the higher
	 * the window position.
	 */
	getAltitude: function() {
		return this.dialogManager.getAltitude(this);
	},

	/*
	 * Method: setAltitude Sets window altitude, fires 'altitude:changed' if
	 * altitude was changed
	 */
	setAltitude: function(altitude) {
		if (this.dialogManager.setAltitude(this, altitude))
			this.fire('altitude:changed');
		return this;
	},

	setDraggable : function(draggable) {
		var element = this.savedElement || this.element;
		this.options.draggable = draggable;
		element[(draggable ? 'add' : 'remove') + 'ClassName']('draggable');
		return this;
	},

	setResizable: function(resizable) {
		this.options.resizable = resizable;

		var toggleClassName = (resizable ? 'add' : 'remove') + 'ClassName',
			element = this.savedElement || this.element;

		element[toggleClassName]('resizable').select('div:[class*=_sizer]').invoke(resizable ? 'show' : 'hide');
		if (resizable)
			this.createResizeHandles();

		element.select('div.se').first()[toggleClassName]('se_resize_handle');
		return this;
	},

	fire: function(eventName, memo) {
		memo = memo || {};
		memo.window = this;
		return (this.savedElement || this.element).fire('edialog:' + eventName, memo);
	},

	observe: function(eventName, handler) {
		(this.savedElement || this.element).observe('edialog:' + eventName, handler.bind(this));
		return this;
	},
	
	addElements: function() {
		this.dialogManager.container.appendChild(this.element);
		this.events.notify('addElements');
	},
	
	effect: function(name, element, options) {
	    var effect = this.options[name] || Prototype.emptyFunction;
	    effect(element || this.element, options || {});
	},
	
	// Set z-index to all window elements
	setZIndex: function(zIndex) {
		if (this.zIndex != zIndex) {
			this.events.notify('setZIndex:pre', zIndex);
			this.zIndex = zIndex;
			[ this.element ].concat(this.element.childElements()).each(function(element) {
				element.style.zIndex = zIndex++;
			});
			this.lastZIndex = zIndex;
			this.events.notify('setZIndex', zIndex);
		}
		return this;
	},
	
	// re-compute window border size
	computeBorderSize: function() {
		if (this.element) {
			if (e107API.Browser.IE >= 7)
				this.content.style.width = "100%";
			
			var dim = this.element.getDimensions(), 
				pos = this.content.positionedOffset();
			
			this.borderSize = {
				top: pos[1],
				bottom: dim.height - pos[1] - this.content.getHeight(),
				left: pos[0],
				right: dim.width - pos[0] - this.content.getWidth()
			};
			this.borderSize.width = this.borderSize.left
					+ this.borderSize.right;
			this.borderSize.height = this.borderSize.top
					+ this.borderSize.bottom;
			if (e107API.Browser.IE >= 7)
				this.content.style.width = "auto";
		}
	},

	computeSize: function(width, height, innerSize) {
		var innerWidth, innerHeight, outerWidth, outerHeight;
		if (innerSize) {
			outerWidth = width + this.borderSize.width;
			outerHeight = height + this.borderSize.height;
		} else {
			outerWidth = width;
			outerHeight = height;
		}
		// Check grid value
		if (!this.animating) {
			outerWidth = outerWidth.snap(this.options.gridX);
			outerHeight = outerHeight.snap(this.options.gridY);

			// Check min size
			if (outerWidth < this.options.minWidth)
				outerWidth = this.options.minWidth;
			if (outerHeight < this.options.minHeight)
				outerHeight = this.options.minHeight;
			
			// Check max size
			if (this.options.maxWidth && outerWidth > this.options.maxWidth)
				outerWidth = this.options.maxWidth;

			if (this.options.maxHeight && outerHeight > this.options.maxHeight)
				outerHeight = this.options.maxHeight;
		}

		if (this.centerOptions && this.centerOptions.auto)
			this.recenter();

		innerWidth = outerWidth - this.borderSize.width;
		innerHeight = outerHeight - this.borderSize.height;
		return {
			innerWidth: innerWidth,
			innerHeight: innerHeight,
			outerWidth: outerWidth,
			outerHeight: outerHeight
		};
	},

	computePosition: function(top, left) {
		if (this.modal && this.centerOptions && this.centerOptions.auto)
			return this.computeRecenter(this.getSize());

		return {
			top: this.animating ? top : top.snap(this.options.gridY),
			left: this.animating ? left : left.snap(this.options.gridX)
		};
	},

	computeRecenter: function(size) {
		var viewport = this.dialogManager.viewport, area = viewport.getDimensions(), offset = viewport.getScrollOffset(), center = {
			top: Object.isUndefined(this.centerOptions.top) ? (area.height - size.height) / 2 : this.centerOptions.top,
			left: Object.isUndefined(this.centerOptions.left) ? (area.width - size.width) / 2 : this.centerOptions.left
		};

		return {
			top: parseInt(center.top + offset.top),
			left: parseInt(center.left + offset.left)
		};
	},

	recenter: function(event) {
		var pos = this.computeRecenter(this.getSize());
		this.setPosition(pos.top, pos.left);
	}
});

e107Widgets.URLDialog = Class.create(e107Widgets.Dialog, {

	setAjaxContent: function() {
		return this;
	},
	
	initialize: function($super, url, options) {
		$super(options);
		this.options.url = url || this.options.url;
		this.createIFrame();
	},
	
	destroy: function($super) {
		this.iframe.src = null;
		$super();
	},
	
	getUrl: function() {
		return this.iframe.src;
	},
	
	setUrl: function(url) {
		this.iframe.src = url;
		return this;
	},
	
	createIFrame: function($super) {
		this.iframe = new Element('iframe', {
			style: this.style,
			frameborder: 0,
			src: this.options.url,
			name: this.element.id + "_frame",
			id: this.element.id + "_frame"
		});
	
		this.content.insert(this.iframe);
	}
});

e107Base.setPrefs('core-confirm-dialog', {
	buttons: null, // default = ok/cancel button
	beforeSelect: function() {
		return true;
	},
	close: false,
	resizable: false,
	activeOnClick: false
});

e107Widgets.ConfirmDialog = Class.create(e107Widgets.Dialog, {
	
	initialize: function($super, options) {
		options = Object.extend(e107Base.getPrefs('core-confirm-dialog'), options || {});
		$super(options);
		
	},
	
	disableDialogButton: function(index) {
		var button = this.getDialogButton(index);
		if (button)
			button.addClassName("disabled");
		return this;
	},

	enableDialogButton: function(index) {
		var button = this.getDialogButton(index);
		if (button)
			button.removeClassName("disabled");
		return this;
	},

	getDialogButton: function(index) {
		var buttons = this.buttonContainer.childElements();
		if (index >= 0 && index < buttons.length)
			return buttons[index];
		else
			return null;
	},

	// Override create to add dialog buttons
	create: function($super) {
		$super();
	
		// Create buttons
		this.buttonContainer = this.createButtons();
		// Add a new content for dialog content
		this.dialogContent = new Element('div', {
			className: 'ui-dialog-confirm-content'
		});
	
		this.content.update(this.dialogContent);
		this.content.insert(this.buttonContainer);
	},

	addElements: function($super) {
		$super();
		// Pre compute buttons height
		this.buttonsHeight = this.buttonContainer.getHeight() || this.buttonsHeight;
		this.setDialogSize();
	},

	setContent: function(content, withoutButton) {
		this.dialogContent.update(content);
	
		// Remove button if need be
		if (withoutButton && this.buttonContainer.parentNode)
			this.buttonContainer.remove();
		else
			this.content.insert(this.buttonContainer);
	
		// Update dialog size
		this.setDialogSize();
		return this;
	},

	onSelect: function(e) {
		var element = e.element();
		
		if (element.callback && !element.hasClassName('disabled')) {
			if (this.options.beforeSelect(element))
				element.callback(this);
		}
	},

	createButtons: function(dialogButtons) {
		var buttonContainer = new Element('div', {
			className: 'ui-dialog-confirm-buttons'
		});
		
		(this.options.buttons || e107Widgets.ConfirmDialog.okCancelButtons).each(function(item) {
			var button;
			if (item.separator)
				button = new Element('span', {
					className: 'separator'
				});
			else
				button = new Element('button', {
					title: item.title,
					className: (item.className || '') + (item.disabled ? ' disabled' : '')
				}).observe('click', this.onSelect.bind(this)).update(item.title);
	
			buttonContainer.insert(button);
			button.callback = item.callback;
		}.bind(this));
		return buttonContainer;
	},

	setDialogSize: function() {
		// Do not compute dialog size if window is not completly ready
		if (!this.borderSize)
			return;
	
		this.buttonsHeight = this.buttonContainer.getHeight() || this.buttonsHeight;
		var style = this.dialogContent.style, size = this.getSize(true);
		style.width = size.width + "px", style.height = size.height - this.buttonsHeight + "px";
	},

	setSize: function($super, width, height, innerSize) {
		$super(width, height, innerSize);
		this.setDialogSize();
		return this;
	}
});

e107Widgets.ConfirmDialog =  Object.extend(e107Widgets.ConfirmDialog, {
	  okButton        : [{title: 'Ok',     className: 'ok',     callback: function(win) { win.destroy(); }}],
	  okCancelButtons : [{title: 'Ok',     className: 'ok',     callback: function(win) { win.destroy(); }},
	                     {title: 'Cancel', className: 'cancel', callback: function(win) { win.destroy(); }}]
});

if (!Object.isUndefined(window.Effect)) {
	e107Widgets.Dialog.Effects = e107Widgets.Dialog.Effects || {};
	e107Widgets.Dialog.Effects.Morph = Class.create(Effect.Base, {
		initialize: function(window, bounds) {
			this.window = window;
			var options = Object.extend( {
				fromBounds: this.window.getBounds(),
				toBounds: bounds,
				from: 0,
				to: 1
			}, arguments[2] || {});
			this.start(options);
		},

		update: function(position) {
			var t = this.options.fromBounds.top + (this.options.toBounds.top - this.options.fromBounds.top) * position;
			var l = this.options.fromBounds.left + (this.options.toBounds.left - this.options.fromBounds.left) * position;

			var ow = this.options.fromBounds.width + (this.options.toBounds.width - this.options.fromBounds.width) * position;
			var oh = this.options.fromBounds.height + (this.options.toBounds.height - this.options.fromBounds.height) * position;

			this.window.setBounds( {
				top: t,
				left: l,
				width: ow,
				height: oh
			})
		}
	});
}

e107Widgets.Dialog.addMethods( {

	startDrag: function(handle) {
		this.initBounds = this.getBounds();
		this.activate();
		
		if (this.options.wired) {
			this.createWiredElement();
			this.wiredElement.style.cssText = this.element.style.cssText;
			this.element.hide();
			if(this.shadow) this.shadow.hide();
			this.savedElement = this.element;
			this.dialogManager.container.appendChild(this.wiredElement);
			this.element = this.wiredElement;
		}

		handle.hasClassName('resize_handle') ? this.startResize(handle) : this.startMove();
	},

	endDrag: function() {
		this.element.hasClassName('resized') ? this.endResize() : this.endMove();

		if (this.options.wired) {
			this.savedElement.style.cssText = this.wiredElement.style.cssText;
			this.wiredElement.remove();
			this.element = this.savedElement;
			if(this.shadow) this.shadow.show();
			this.savedElement = false;
		}
	},

	startMove: function() {
		// method used to drag
		this.drag = this.moveDrag;
		this.element.addClassName('moved');
		this.fire('move:started');
	},

	endMove: function() {
		this.element.removeClassName('moved');
		this.fire('move:ended');
	},

	startResize: function(handle) {
		this.drag = this[handle.readAttribute('drag_prefix') + 'Drag'];
		this.element.addClassName('resized');
		this.fire('resize:started');
	},

	endResize: function() {
		this.element.removeClassName('resized');
		this.fire('resize:ended');
	},

	moveDrag: function(dx, dy) {
		var top = this.initBounds.top + dy, left = this.initBounds.left + dx;
		if(this.options.noPositionConstrain)
			this.setPosition(top, left);
		else 
			this.setPosition(top < 0 ? 0 : top, left < 0 ? 0 : left);
	},

	swDrag: function(dx, dy) {
		var initBounds = this.initBounds;
		this.setSize(initBounds.width - dx, initBounds.height + dy).setPosition(initBounds.top, initBounds.left + (initBounds.width - this.getSize().width));
	},

	seDrag: function(dx, dy) {
		this.setSize(this.initBounds.width + dx, this.initBounds.height + dy);
	},

	nwDrag: function(dx, dy) {
		var initBounds = this.initBounds;
		this.setSize(initBounds.width - dx, initBounds.height - dy).setPosition(initBounds.top + (initBounds.height - this.getSize().height), initBounds.left + (initBounds.width - this.getSize().width));
	},

	neDrag: function(dx, dy) {
		var initBounds = this.initBounds;
		this.setSize(initBounds.width + dx, initBounds.height - dy).setPosition(initBounds.top + (initBounds.height - this.getSize().height), initBounds.left);
	},

	wDrag: function(dx, dy) {
		var initBounds = this.initBounds;
		this.setSize(initBounds.width - dx, initBounds.height).setPosition(initBounds.top, initBounds.left + (initBounds.width - this.getSize().width));
	},

	eDrag: function(dx, dy) {
		this.setSize(this.initBounds.width + dx, this.initBounds.height);
	},

	nDrag: function(dx, dy) {
		var initBounds = this.initBounds;
		this.setSize(initBounds.width, initBounds.height - dy).setPosition(initBounds.top + (initBounds.height - this.getSize().height), initBounds.left);
	},

	sDrag: function(dx, dy) {
		this.setSize(this.initBounds.width, this.initBounds.height + dy);
	}
});

e107Widgets.Dialog.addMethods( {
	
	createButtons: function() {
		this.buttons = new Element("div", {
			className: "buttons"
		}).observe('click', this.onButtonsClick.bind(this))
		.observe('mouseover', this.onButtonsHover.bind(this))
		.observe('mouseout', this.onButtonsOut.bind(this));
	
		this.element.insert(this.buttons);
	
		this.defaultButtons.each(function(button) {
			if (this.options[button] !== false)
				this.addButton(button);
		}, this);
	},
	
	destroyButtons: function() {
		if(this.buttons)
			this.buttons.stopObserving();
	},
	
	defaultButtons: $w('maximize close'),
	
	getButtonElement: function(buttonName) {
		return this.buttons.down("." + buttonName);
	},
	
	// Controls close, minimize, maximize, etc.
	// action can be either a string or a function
	// if action is a string, it is the method name that will be called
	// else the function will take the window as first parameter.
	// if not given action will be taken in window's options
	addButton: function(buttonName, action) {
		this.buttons.insert(new Element("a", {
			className: buttonName,
			href: "#"
		}));
	
		if (action)
			this.options[buttonName] = action;
	
		return this;
	},
	
	removeButton: function(buttonName) {
		var b = this.getButtonElement(buttonName);
		if(b) b.remove();
		return this;
	},
	
	disableButton: function(buttonName) {
		var b = this.getButtonElement(buttonName);
		if(b) b.addClassName("disabled");
		return this;
	},
	
	enableButton: function(buttonName) {
		var b = this.getButtonElement(buttonName);
		if(b) b.removeClassName("disabled");
		return this;
	},
	
	onButtonsClick: function(event) {
		var element = event.findElement('a:not(.disabled)');
	
		if (element)
			this.action(element.className);
		event.stop();
	},
	
	onButtonsHover: function(event) {
		this.buttons.addClassName("over");
	},
	
	onButtonsOut: function(event) {
		this.buttons.removeClassName("over");
	},
	
	updateButtonsOrder: function() {
		if(!this.buttons) return;
		var buttons = this.buttons.childElements();
	
		buttons.inject(new Array(buttons.length), function(array, button) {
			array[parseInt(button.getStyle("padding-top"))] = button.setStyle("padding: 0");
			return array;
		}).each(function(button) {
			this.buttons.insert(button)
		}, this);
	}
});

e107Widgets.Dialog.addMethods( {
	
	showShadow: function() {
		if (this.shadow) {
			this.shadow.hide();
			this.effect('show', this.shadow.shadow);
		}
	},

	hideShadow: function() {
		if (this.shadow)
			this.effect('hide', this.shadow.shadow);
	},
	
	removeShadow: function() {
		if (this.shadow)
			this.shadow.remove();
	},
	
	focusShadow: function() {
		if (this.shadow)
			this.shadow.focus();
	},
	
	blurShadow: function() {
		if (this.shadow)
			this.shadow.blur();
	},
	
	// Private Functions
	createShadow: function() {
		if (!this.options.shadow || Object.isUndefined(e107Widgets.Shadow)) return;
		
		this.observe('showing', this.showShadow)
			.observe('hiding', this.hideShadow)
			.observe('hidden', this.removeShadow)
			.observe('focused', this.focusShadow)
			.observe('blurred', this.blurShadow);

		this.shadow = new e107Widgets.Shadow(this.element, {
			theme: this.getShadowTheme()
		});
	},
	
	addElementsShadow: function() {
		if (this.shadow) {
			this.shadow.setBounds(this.options).render();
		}
	},
	
	setZIndexShadow: function(zIndex) {
		if (this.zIndex != zIndex) {
			if (this.shadow)
				this.shadow.setZIndex(zIndex - 1);

			this.zIndex = zIndex;
		}
		return this;
	},
	
	setPositionShadow: function(top, left) {
		if (this.shadow) {
			var pos = this.getPosition();
			this.shadow.setPosition(pos.top, pos.left);
		}
		return this;
	},
	
	setSizeShadow: function(width, height, innerSize) {
		if (this.shadow) {
			var size = this.getSize();
			this.shadow.setSize(size.width, size.height);
		}
		return this;
	},
	
	setBoundsShadow: function(bounds, innerSize) {
		if (this.shadow)
			this.shadow.setBounds(this.getBounds());
	}
});


e107Base.setPrefs('core-dialogmanager', {
	className : 'e-dialog',
	container: null, // will default to document.body
	zIndex: 2000,
	theme: "e107",
	shadowTheme: "e107_shadow",
	showOverlay: Element.show,
	hideOverlay: Element.hide,
	positionningStrategyOffset: null,
	positionningStrategy: function(win, area, winoffset) {
		e107Widgets.DialogManager.DefPositionningStrategy(win, area, winoffset);
	}
});

e107Widgets.DialogManager = Class.create(e107WidgetAbstract, {

	initialize: function(options) {
		
		this.initMod('core-dialogmanager', options);

		this.container = $(this.options.container || document.body);

		if (this.container === $(document.body)) {
			this.viewport = document.viewport;
			this.scrollContainer = window;
		} else {
			this.viewport = this.scrollContainer = this.container;
		}

		this.container.observe('drag:started', this.onStartDrag.bind(this))
					.observe('drag:updated', this.onDrag.bind(this))
					.observe('drag:ended', this.onEndDrag.bind(this));

		this.stack = new e107Widgets.DialogManager.Stack();
		this.modalSessions = 0;

		this.createOverlays();
		this.resizeEvent = this.resize.bind(this);

		Event.observe(window, "resize", this.resizeEvent);
	},

	destroy: function() {
		this.windows().invoke('destroy');
		this.stack.destroy();
		Event.stopObserving(window, "resize", this.resizeEvent);
	},

	/*
	 * Method: setTheme Changes window manager's theme, all windows that
	 * don't have a own theme will have this new theme.
	 * 
	 * Parameters: theme - theme name
	 * 
	 * Example: e107Widgets.DialogManagerDefault.setTheme('bluelighting');
	 */
	setTheme: function(theme) {
		this.stack.windows.select(function(w) {
			return !w.options.theme;
		}).invoke('setTheme', theme, true);
		this.options.theme = theme;
		return this;
	},
	
	getTheme: function() {
		return this.options.theme;
	},
	
	//shadow under construction
	getShadowTheme: function() {
		return this.options.shadowTheme;
	},
	
	register: function(win) {
		if (this.getWindow(win.id))
			return;

		this.handlePosition(win);
		this.stack.add(win);
		this.restartZIndexes();
	},

	unregister: function(win) {
		this.stack.remove(win);

		if (win == this.focusedWindow)
			this.focusedWindow = null;
	},

	/*
	 * Method: getWindow Find the window containing a given element.
	 * 
	 * Example:  $$('.e-dialog a.close').invoke('observe', 'click',
	 * function() {  e107Widgets.DialogManagerDefault.getWindow(this).close();  });
	 * 
	 * Parameters: element - element or element identifier
	 * 
	 * Returns: containing window or null
	 */
	getWindow: function(element) {
		element = $(element);

		if (!element)
			return;

		if (!element.hasClassName(this.options.className))
			element = element.up(this.options.className);

		var id = element.id;
		return this.stack.windows.find(function(win) {
			return win.id == id
		});
	},

	/*
	 * Method: windows Returns an array of all windows handled by this
	 * window manager. First one is the back window, last one is the front
	 * window.
	 * 
	 * Example: UI.defaultWM.windows().invoke('destroy');
	 */
	windows: function() {
		return this.stack.windows.clone();
	},

	/*
	 * Method: getFocusedWindow Returns the focused window
	 */
	getFocusedWindow: function() {
		return this.focusedWindow;
	},

	// INTERNAL

	// Modal mode
	startModalSession: function(win) {
		if (!this.modalSessions) {
			this.removeOverflow();
			this.modalOverlay.className = win.getTheme() + "_overlay";
			this.container.appendChild(this.modalOverlay);

			if (!this.modalOverlay.opacity)
				this.modalOverlay.opacity = this.modalOverlay.getOpacity();
			this.modalOverlay.setStyle("height: " + this.viewport.getHeight() + "px");

			this.options.showOverlay(this.modalOverlay, {
				from: 0,
				to: this.modalOverlay.opacity
			});
		}
		this.modalOverlay.setStyle( {
			zIndex: win.zIndex - 1
		});
		this.modalSessions++;
	},

	endModalSession: function(win) {
		this.modalSessions--;
		if (this.modalSessions) {
			this.modalOverlay.setStyle( {
				zIndex: this.stack.getPreviousWindow(win).zIndex - 1
			});
		} else {
			this.resetOverflow();
			this.options.hideOverlay(this.modalOverlay, {
				from: this.modalOverlay.opacity,
				to: 0
			});
		}
	},
	
	// not sure this should stay so, too slow?
	moveHandleSelector: 	'.#{className}.draggable .move_handle',
	resizeHandleSelector: 	'.#{className}.resizable .resize_handle',

	onStartDrag: function(event) {
		var handle = event.element(), 
			isMoveHandle = handle.match(this.moveHandleSelector.interpolate(this.options)), 
			isResizeHandle = handle.match(this.resizeHandleSelector.interpolate(this.options));

		// ensure dragged element is a window handle !
		if (isResizeHandle || isMoveHandle) {
			event.stop();

			// find the corresponding window
			var win = this.getWindow(event.findElement('.' + this.options.className));

			// render drag overlay
			this.container.insert(this.dragOverlay.setStyle( {
				zIndex: this.getLastZIndex()
			}));

			win.startDrag(handle);
			this.draggedWindow = win;
		}
	},

	onDrag: function(event) {
		if (this.draggedWindow) {
			event.stop();
			this.draggedWindow.drag(event.memo.dx, event.memo.dy);
		}
	},

	onEndDrag: function(event) {
		if (this.draggedWindow) {
			event.stop();
			this.dragOverlay.remove();
			this.draggedWindow.endDrag();
			this.draggedWindow = null;
		}
	},

	maximize: function(win) {
		this.removeOverflow();
		this.maximizedWindow = win;
		return true;
	},

	restore: function(win) {
		if (this.maximizedWindow) {
			this.resetOverflow();
			this.maximizedWindow = false;
		}
		return true;
	},

	removeOverflow: function() {
		var container = this.container;
		// Remove overflow, save overflow and scrolloffset values to restore
		// them when restore window
		container.savedOverflow = container.style.overflow || "auto";
		container.savedOffset = this.viewport.getScrollOffset();
		container.style.overflow = "hidden";

		this.viewport.setScrollOffset( {
			top: 0,
			left: 0
		});

		if (this.container == document.body && Prototype.Browser.IE)
			this.cssRule = CSS.addRule("html { overflow: hidden }");
	},

	resetOverflow: function() {
		var container = this.container;
		// Restore overflow ans scrolloffset
		if (container.savedOverflow) {
			if (this.container == document.body && Prototype.Browser.IE)
				this.cssRule.remove();

			container.style.overflow = container.savedOverflow;
			this.viewport.setScrollOffset(container.savedOffset);

			container.savedOffset = container.savedOverflow = null;
		}
	},

	hide: function(win) {
		var previous = this.stack.getPreviousWindow(win);
		if (previous)
			previous.focus();
	},
	
	getZIndex: function() {
		return this.options.zIndex;
	},

	restartZIndexes: function() {
		// Reset zIndex
		var zIndex = this.getZIndex() + 1; // keep a zIndex free for
		// overlay divs
		this.stack.windows.each(function(w) {
			w.setZIndex(zIndex);
			zIndex = w.lastZIndex + 1;
		});
	},

	getLastZIndex: function() {
		return this.stack.getFrontWindow().lastZIndex + 1;
	},

	overlayStyle: "position: absolute; top: 0; left: 0; display: none; width: 100%;",

	createOverlays: function() {
		this.modalOverlay = new Element("div", {
			style: this.overlayStyle
		});
		this.dragOverlay = new Element("div", {
			style: this.overlayStyle + "height: 100%"
		});
	},

	focus: function(win) {
		// Blur the previous focused window
		if (this.focusedWindow)
			this.focusedWindow.blur();
		this.focusedWindow = win;
	},

	blur: function(win) {
		if (win == this.focusedWindow)
			this.focusedWindow = null;
	},

	setAltitude: function(win, altitude) {
		var stack = this.stack;

		if (altitude === "front") {
			if (stack.getFrontWindow() === win)
				return;
			stack.bringToFront(win);
		} else if (altitude === "back") {
			if (stack.getBackWindow() === win)
				return;
			stack.sendToBack(win);
		} else {
			if (stack.getPosition(win) == altitude)
				return;
			stack.setPosition(win, altitude);
		}

		this.restartZIndexes();
		return true;
	},

	getAltitude: function(win) {
		return this.stack.getPosition(win);
	},

	resize: function(event) {
		var area = this.viewport.getDimensions();

		if (this.maximizedWindow)
			this.maximizedWindow.setSize(area.width, area.height);

		if (this.modalOverlay.visible())
			this.modalOverlay.setStyle("height:" + area.height + "px");
	},

	handlePosition: function(win) {
		// window has its own position, nothing needs to be done
		if (Object.isNumber(win.options.top) && Object.isNumber(win.options.left))
			return;

		// default values
		//win.options.top = win.options.left = 0; 
		var strategy = this.options.positionningStrategy, 
			area = this.viewport.getDimensions(),
			winoffset = win.options.positionningStrategyOffset || win.options.positionningStrategyOffset === false ? win.options.positionningStrategyOffset : this.options.positionningStrategyOffset;

		Object.isFunction(strategy) ? strategy(win, area) : strategy.position(win, area, winoffset);
	}
});

e107Widgets.DialogManager.DefPositionningStrategy = function(win, area, winoffset) {

	var manager = win.dialogManager,
		last = manager.stack.getFrontWindow(),
		size = win.getSize(),
		offset = manager.viewport.getScrollOffset(),
		maxtop = area.height - size.height, 
		maxleft = area.width - size.width,
		poffset = winoffset === false ? 0 : (winoffset || 20),
		start = { left: offset[0] + poffset, top: offset[1] + poffset };
	
	if(last) {
		start = last.getPosition();
		start.left = (start.left < offset[0] ? offset[0] : start.left) + poffset;
		start.top = (start.top < offset[1] ? offset[1] : start.top) + poffset;
	}
	
	left = start.left < maxleft ? start.left : start.left - (poffset * 2);
	top = start.top < maxtop ? start.top : start.top - (poffset * 2);

	win.setPosition(top, left);
};

e107Widgets.DialogManager.Stack = Class.create(Enumerable, {
	initialize: function() {
		this.windows = [];
	},
	
	each: function(iterator) {
		this.windows.each(iterator);
	},
	
	add: function(win, position) {
		this.windows.splice(position || this.windows.length, 0, win);
	},
	
	remove: function(win) {
		this.windows = this.windows.without(win);
	},
	
	sendToBack: function(win) {
		this.remove(win);
		this.windows.unshift(win);
	},
	
	bringToFront: function(win) {
		this.remove(win);
		this.windows.push(win);
	},
	
	getPosition: function(win) {
		return this.windows.indexOf(win);
	},
	
	setPosition: function(win, position) {
		this.remove(win);
		this.windows.splice(position, 0, win);
	},
	
	getFrontWindow: function() {
		return this.windows.last();
	},
	
	getBackWindow: function() {
		return this.windows.first();
	},
	
	getPreviousWindow: function(win) {
		return (win == this.windows.first()) ? null : this.windows[this.windows.indexOf(win) - 1];
	}
});

/**
 * String extension
 */
Object.extend(Number.prototype, {
	// Snap a number to a grid
	snap: function(round) {
		return parseInt(round == 1 ? this : (this / round).floor() * round);
	}
});

document.observe('dom:loaded', function() {
	e107Widgets.DialogManagerDefault = new e107Widgets.DialogManager();
});
	
	