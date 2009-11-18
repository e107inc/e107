/*
 * e107 website system
 * 
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://gnu.org).
 * 
 * e107Widget.Tabs Class
 * 
 * Create tabs, supports ajax/inline content, browser history & bookmarks
 * (unobtrusive Javascript)
 * 
 * $Source: /cvs_backup/e107_0.8/e107_files/jslib/core/tabs.js,v $
 * $Revision: 1.4 $
 * $Date: 2009-11-18 01:49:18 $
 * $Author: marj_nl_fr $
 * 
*/

/**
 * Global prefs
 */
e107Base.setPrefs('core-tabs', {
	tabsClassName: 'e-tabs',
	bookmarkFix: true,
	historyNavigation: false,
	pageOverlay: false,
	elementOverlay: true,
	ajaxCache: true
});
 
e107Widgets.Tabs = Class.create(e107WidgetAbstract, {
	
	initialize: function(container, options) {
		this.Version = '1.0';
		
		this.events = new e107EventManager(this);
		var optHandlers = {
			show: this.show,
			hide: this.hide
		}
		Object.extend(optHandlers , options || {});

		this.global = this;
		this.initMod('core-tabs', optHandlers).__initTabData(container);
		
	},	
	
	__initTabData: function(container) {
		var cstring, celement = $(container);
		if(null === celement)
			throw('e107Widgets.Tabs: invalid value for container'); //TODO Lan
			
		if(Object.isString(container)) {
			cstring = container; 
		} else if(Object.isElement(container)) {
			cstring = celement.identify();
		}
		
		this.histotyHash = ('etab-' + cstring).camelize(); 
		if(!this.getModCache('-data')) {
			this.setModCache('-data', {});
		}
		
		this.tabData = this.getModCache('-data')['ref-' + cstring];
		this._observer = this.observer.bindAsEventListener(this); //click observer
 
		if(!this.tabData && !this.___initialized) {
			if(this.options.bookmarkFix || this.options.historyNavigation) this.options.history = true;
			
			this.___methods = $w('show hide select tabSelect ajaxSelect visible getPanelId getMenuId startObserve stopObserve getPanelId getPanel getMenuId getMenu');
			this.tabData = {
				container: celement,
				list: $A()
			}

			if(celement.nodeName.toLowerCase != 'ul') 
				var celements = celement.select('ul.' + this.options.tabsClassName + ' > li');
			else 
				var celements = $$('body')[0].select(cstring + ' > li');
			
			celements.inject(this.tabData.list, function(arr, elitem, i) {
				var mid = elitem.identify(),
					a = elitem.select('a')[0],
					act = a.hash.substr(1),
					cid = $(act);
				
				var that = this;
				arr[i] = { Index: i, menuId: mid, menu: elitem, menuAction: act, actionItem: a, panel: cid, panelId: cid.id, ajaxUrl: a.readAttribute('rel'), global: that, exec: that._exec.bind(that, i) };
				this._extendTab(arr[i]);
				
				return arr;
			}.bind(this));
			
			this.exec_recursive('hide').getDefault().select();
			this.startEvents();
			this.___initialized = true;
			this.getModCache('-data')['ref-' + cstring] = this.tabData;
		}
	},
	
	_extendTab: function(data) {
		this.___methods.inject(data, function(obj, method) {
			obj[method] = this[method].bind(this, obj);
			return obj;
		}.bind(this));
		data.events = new e107EventManager(this);
		data.options = this.options;
		data.histotyHash = this.histotyHash;

		return this._detectLoad(data);
	},
	
	_detectLoad: function(tab) {
		if(tab.ajaxUrl) {
			var lopts = $w(tab.ajaxUrl).detect(function (values) {
				return values.startsWith('ajax-tab');
			});
			if(lopts) { 
				var link = tab.actionItem.readAttribute('href').split('#')[0]; //link url
				tab.ajaxUrl = link ? link : document.location.href.split('#')[0]; //self url
			}
			return tab;
		}
		tab.ajaxUrl = false;
		return tab;
	},
	
	_exec: function(index, method, options) {
		if(!this.___methods.include(method) || !this.tabData.list[index]) {
			throw('e107Widgets.Tabs._exec: wrong method or object not found!');
		}
		this.tabData.list[index][method](options);
		return this.tabData.list[index];
	},
	
	/**
	 * Available only in instance' global scope
	 */
	exec: function(index, method, options) {
		this.tabData.list[index].exec(method, options || {});
		return this;
	},
	
	/**
	 * Available only in instance' global scope
	 */
	exec_recursive: function(method, except, options) {
		if(except)
			this.tabData.list.without(except).invoke('exec', method, options || {});
		else 
			this.tabData.list.invoke('exec', method, options || {});
		return this;
	},
	
	_getTabByIdnex: function(index) {
		return this.tabData.list[index] || null;
	},
	
	_getTabByPanelId: function(name) {
		return this.tabData.list.find(function(tab_obj) { return tab_obj.getPanelId() == name }) || null;
	},
	
	/**
	 * Available only in instance' global scope
	 */
	get: function(tab) {
		if(Object.isNumber(tab))
			return this._getTabByIdnex(tab);
		else if(Object.isString(tab))
			return this._getTabByPanelId(tab);
		return tab;
	},
	
	getPanelId: function(tab_obj) {
		return tab_obj.panelId;
	},
	
	getPanel: function(tab_obj) {
		return tab_obj.panel;
	},
	
	getMenuId: function(tab_obj) {
		return tab_obj.menuId;
	},
	
	getMenu: function(tab_obj) {
		return tab_obj.menu;
	},
	
	/**
	 * Available only in instance' global scope
	 */
	getDefault: function() {
		var current = e107History.get(this.histotyHash);
		if(current) {
			var tab = this.get(current) || this.tabData.list[0];
			this._active = tab.Index;
			return tab;
		}
		
		this._active = 0;
		return this.tabData.list[0];
	},
	
	getActive: function() {
		if(!this.global._active) {
			var _active = this.tabData.list.find(function(tab_obj) { return tab_obj.visible(); }) || null;
			if(_active) {
				this.global._active = _active.Index;
			}
		}
		return this.get(this.global._active);
	},
	
	visible: function(tab) {
		return tab.getPanel().visible();
	},

	show: function(tab) {
		tab.getMenu().addClassName('active');
		tab.getPanel().addClassName('active').show(); 
		if(tab.global.options.history)
			e107History.set(tab.histotyHash, tab.getPanelId());
		return tab;
	},
	
	hide: function(tab) {
		tab.getMenu().removeClassName('active');
		tab.getPanel().removeClassName('active').hide();
		
		return tab;
	},

	select: function(tab) {
		if(!tab.visible()) {
			if(tab.ajaxUrl) 
				return tab.ajaxSelect();
			return tab.tabSelect();
		}
		return tab;
	},
	
	ajaxSelect: function(tab) {
		if(!tab.ajaxUrl || (this.global.options.ajaxCache && tab.options['ajaxCached'])) 
			return tab.tabSelect();
		var ovel = this.global.options.overlayElement === true ? tab.getPanel() : $(this.global.options.overlayElement);
		tab.getMenu().addClassName('active'); 
		new e107Ajax.Updater(tab.getPanel(), tab.ajaxUrl, {
			overlayPage: this.options.pageOverlay ? tab.getPanel() : false,
			overlayElement: ovel || false,
			onComplete: function() { tab.options.ajaxCached = this.global.options.ajaxCache; tab.tabSelect(); }.bind(this)
		});
		
		return tab;
	},
	
	tabSelect: function(tab) {
		
		this.global.events.notify('hideActive', this.global.getActive()); //global trigger
		tab.events.notify('hide', this.global.getActive()); // tab object trigger
		this.options.hide(this.global.getActive());
		
		this.global.events.notify('showSelected', tab); //global trigger
		tab.events.notify('show', tab); // tab object trigger
		this.options.show(tab);
		
		this.global._active = tab.Index;
		
		return tab;
	},

	startEvents: function() {
		this.exec_recursive('startObserve'); 
		this._startHistory();
		return this;
	},
	
	stopEvents: function() {
		this.exec_recursive('stopObserve'); 
		this._stopHistory();
		return this;
	},
	
	startObserve: function(tab) {
		tab.actionItem.observe('click', this._observer); return this;
	},
	
	stopObserve: function(tab) {
		tab.actionItem.stopObserving('click', this._observer); return this;
	},
	
	observer: function(event) {
		var el = event.findElement('a');
		if(el) {
			event.stop();
			
			this.get(el.hash.substr(1)).select();
		}
	},
	
	eventObserve: function(method, callback) {
		this.events.observe(method, callback); return this;
	},
	
	eventStopObserve: function() {
		this.events.stopObserving(method, callback); return this;
	},
	
	_startHistory: function() {
		if(this.options.historyNavigation) {
            e107History.Observer.start();
            var that = this;
            // set handler for this instance
            e107History.Registry.set({
                id: this.histotyHash,
                onStateChange: function(tab) { 
                    that.get(String(tab)).select();
                }
            });
		}
	},
	
	_stopHistory: function() {
        e107History.Observer.stop();
        e107History.Registry.unset(this.histotyHash);
	}
});
