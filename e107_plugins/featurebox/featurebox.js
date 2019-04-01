/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Featurebox Javascript Class
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/featurebox/featurebox.js,v $
 * $Revision$
 * $Date$
 * $Author$
 *
*/

// Temporary solution, will be moved to e107Widgets
var Featurebox = Class.create({
	
	initialize: function(container, options) {
		this._container = $(container);
		if(!this._container) return;
		
		this.options = Object.extend({
			'ajax_container': null,
			'ajax_navbar': null,
			'ajax_nav_selector': 'a.featurebox-nav-link',
			'ajax_loader': null,
			'ajax_hide_onload': false,
			'continuous': false,
			'ajax_url': '#{e_JS}'.parsePath() + 'e_ajax.php'  
		}, options || {});
		
		this._ajax_container = this.options.ajax_container && $(this.options.ajax_container) ? $(this.options.ajax_container) : this._container.down('.body');
		this._ajax_navbar = this.options.ajax_navbar && $(this.options.ajax_navbar) ? $(this.options.ajax_navbar) : this._container.down('.featurebox-nav');
		this._ajax_loader = this.options.ajax_loader && $(this.options.ajax_loader) ? $(this.options.ajax_loader) : this._container.down('.featurebox-loader');
		this._current = $A();
		
		if(!this._ajax_container || !this._ajax_navbar) return;
		this._ajax_nav = this._ajax_navbar.select(this.options.ajax_nav_selector);
		
		this.clickObserverHandler = this.clickObserver.bindAsEventListener(this);
		this.nextObserverHandler = this.nextObserver.bindAsEventListener(this);
		this.prevObserverHandler = this.prevObserver.bindAsEventListener(this);
		
		this.startObserve();
	},
	
	clickObserver: function(event) {
		var element = event.element('a');
		event.stop();
		this.run(element);
	},
	
	nextObserver: function(event) {
		event.stop();
		var current = this._current[1] ? parseInt(this._current[1]) : 1, 
			next = current >= this._ajax_nav.length ? 0 : current;
		if(!this.options.continuous && next == 0) return;
		this.run(this._ajax_nav[next]);
	},
	
	prevObserver: function(event) {
		event.stop();
		var current = this._current[1] ? parseInt(this._current[1]) : 1, 
			prev = (current - 2) < 0 ? this._ajax_nav.length - 1 : current - 2;
		if(!this.options.continuous && prev == this._ajax_nav.length - 1) return;
		this.run(this._ajax_nav[prev]);
	},
	
	run: function(element) {
		var options = element.href.split('#',2)[1].split('.'), that;
		this._current = options;
		
		if(element.hasClassName('active')) return;
		
		this._ajax_navbar.select('.active').invoke('removeClassName', 'active');
		element.addClassName('active');
		if(element.up('li')) {
			element.up('li').addClassName('active'); // only li support at this time
		}
		
		this.showLoader();
		that = this;
		new e107Ajax.Request(this.options.ajax_url, {
			parameters: { 
				'ajax_sc': 'featurebox_items|' + varset(options[0], '') + '=from=' + varset(options[1], 0) + '&cols=' + varset(options[2], 0) + '&no_fill_empty=' + varset(options[3], 0)
			},
			method: 'post',
			onComplete: function(transport) {
				that.hideLoader(transport.responseText);
			}
		});
	},
	
	startObserve: function() {
		this._ajax_navbar.select('a.featurebox-nav-link').invoke('observe', 'click', this.clickObserverHandler);
		this._ajax_navbar.select('a.featurebox-nav-next').invoke('observe', 'click', this.nextObserverHandler);
		this._ajax_navbar.select('a.featurebox-nav-prev').invoke('observe', 'click', this.prevObserverHandler);
	},
	
	stopObserve: function() {
		this._ajax_navbar.select('a.featurebox-nav-link').invoke('stopObserving', 'click', this.clickObserverHandler);
		this._ajax_navbar.select('a.featurebox-nav-next').invoke('stopObserving', 'click', this.nextObserverHandler);
		this._ajax_navbar.select('a.featurebox-nav-prev').invoke('stopObserving', 'click', this.prevObserverHandler);
	},
	
	showLoader: function() {
		if(this._ajax_loader) {
			if(this.options.ajax_hide_onload) {
				this._ajax_container.hide();
			}
			this._ajax_loader.show();
		}
		this.stopObserve();
	},
	
	hideLoader: function(text) {
		if(this._ajax_loader) {
			this._ajax_loader.hide();
			if(this.options.ajax_hide_onload) {
				this._ajax_container.show();
			}
		}
		this._ajax_container.update(text);
		this.startObserve();
	}
});