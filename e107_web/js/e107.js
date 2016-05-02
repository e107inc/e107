/*
 * e107 website system
 *
 * Copyright (C) 2008-2012 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * e107 Javascript API
 *
 * $URL$
 * $Id$
 *
*/

var e107API = {
	Version: '1.0.1',
	ServerVersion: '0.8.1'
}

/*
 * Old stuff
 * FIXME ASAP
 */
var nowLocal = new Date();		/* time at very beginning of js execution */
var localTime = Math.floor(nowLocal.getTime()/1000);	/* time, in ms -- recorded at top of jscript */
/**
 * NOTE: if serverDelta is needed for js functions, you must pull it from
 * the cookie (as calculated during a previous page load!)
 * The value calculated in SyncWithServerTime is not known until after the
 * entire page has been processed.
 */
function SyncWithServerTime(serverTime, path, domain)
{
	if (serverTime)
	{
	  	/* update time difference cookie */
		var serverDelta=Math.floor(localTime-serverTime);
		if(!path) path = '/';
		if(!domain) domain = '';
		else domain = '; domain=' + domain;
	  	document.cookie = 'e107_tdOffset='+serverDelta+'; path='+path+domain;
	  	document.cookie = 'e107_tdSetTime='+(localTime-serverDelta)+'; path='+path+domain; /* server time when set */
	}

	var tzCookie = 'e107_tzOffset=';
//	if (document.cookie.indexOf(tzCookie) < 0) {
		/* set if not already set */
		var timezoneOffset = nowLocal.getTimezoneOffset(); /* client-to-GMT in minutes */
		document.cookie = tzCookie + timezoneOffset+'; path='+path+domain;
//	}
}

// -------------------------------------------------------------------

/**
 * Prototype Xtensions
 * @author    Simon Martins
 * @copyright (c) 2008 Netatoo SARL <http://www.netatoo.fr>
 * @license   MIT License <http://www.prototypextensions.com/#main=license>
 *
 * @desc Retrieve the browser version
 */
(function() {
    var nav       = navigator,
    	userAgent = ua = navigator.userAgent,
    	v         = nav.appVersion,
    	version   = parseFloat(v);

    e107API.Browser = {
        IE      : (Prototype.Browser.IE)    ? parseFloat(v.split("MSIE ")[1]) || 0 : 0,
        Firefox : (Prototype.Browser.Gecko) ? parseFloat(ua.split("Firefox/")[1]) || 0 : 0,
        Camino  : (Prototype.Browser.Gecko) ? parseFloat(ua.split("Camino/")[1]) || 0 : 0,
        Flock   : (Prototype.Browser.Gecko) ? parseFloat(ua.split("Flock/")[1]) || 0 : 0,
        Opera   : (Prototype.Browser.Opera) ? version : 0,
        AIR     : (ua.indexOf("AdobeAIR") >= 0) ? 1 : 0,
        Mozilla : (Prototype.Browser.Gecko || !this.Khtml) ? version : 0,
        Khtml   : (v.indexOf("Konqueror") >= 0 && this.safari) ? version : 0,
        Safari  : (function() {
            var safari = Math.max(v.indexOf("WebKit"), v.indexOf("Safari"), 0);
            return (safari) ? (
                parseFloat(v.split("Version/")[1]) || ( ( parseFloat(v.substr(safari+7)) >= 419.3 ) ? 3 : 2 ) || 2
            ) : 0;
        })()
    };
})();

// -------------------------------------------------------------------

/**
 * Main registry object
 */
var e107Registry = {

    //System Path
    Path: e107Path,

    //Language Constants
    Lan: {},

    //Global Templates
    Template: {
    	Core: {
    		//e107Helper#duplicateHTML method
	        duplicateHTML:	'<div><div class="clear"><!-- --></div>' +
                           		'#{duplicateBody}' +
                           		'<a href="#" id="#{removeId}"><img src="#{e_IMAGE_PACK}admin_images/delete_16.png" class="icon action" style="vertical-align: middle" /></a>' +
                           	'</div>'
    	},

    	//e107Helper#LoadingStatus class
    	CoreLoading:   {
    		template: 		'<div id="loading-mask">' +
								'<p id="loading-mask-loader" class="loader">' +
									'<img src="#{e_IMAGE_PACK}generic/loading_32.gif" alt="#{JSLAN_CORE_LOADING_ALT}" />' +
									'<br /> <span class="loading-text">#{JSLAN_CORE_LOADING_TEXT}</span>' +
								'</p>' +
							'</div>'
    	}
    },

    //Cache
    Cache: new Hash,

    //Cached vars
    CachedVars: new Hash,

    //Global Preferences
    Pref: {
    	Core: {
    		zIndex: 5 //base system z-index
    	}
    }
}

// -------------------------------------------------------------------

/**
 * Global helpers - server side clonings
 */
var isset = function(varname) {
    return !Object.isUndefined(varname);
}

var varset = function(varname) {
    if(Object.isUndefined(varname)) {
        return (Object.isUndefined(arguments[1]) ? null : arguments[1]);
    }
    return varname;
}

var varsettrue = function(varname) {
    if(Object.isUndefined(varname) || !varname) {
        return (Object.isUndefined(arguments[1]) ? null : arguments[1]);
    }
    return varname;
}

var cachevars = function(varname, data) {
    e107Registry.CachedVars.set(data)
}

var getcachedvars = function(varname, destroy) {
	if(destroy)
		return clearcachedvars(varname);
    return e107Registry.CachedVars.get(varname);
}

var clearcachedvars = function(varname) {
    return e107Registry.CachedVars.unset(varname);
}

var echo = Prototype.emptyFunction, print_a = Prototype.emptyFunction, var_dump = Prototype.emptyFunction;

// -------------------------------------------------------------------


/**
 * e107 custom events
 */
var e107Event = {

    fire: function(eventName, memo, element) {
    	if ((!element || element == document) && !document.createEvent)
    	{
    		element = $(document.documentElement);
    	}
    	else
    		element = $(element) || document;
    	memo = memo || {};
    	return element.fire('e107:' + eventName, memo);
    },

    observe: function(eventName, handler, element) {
    	element = $(element) || document;
    	element.observe('e107:' + eventName, handler);
    	return this;
    },

    stopObserving: function(eventName, handler, element) {
    	element = $(element) || document;
    	element.stopObserving('e107:' + eventName, handler);
    	return this;
    },

    //Server side - e107_event aliases
    trigger: function(eventName, memo, element) {
    	this.fire(eventName, memo, element);
    },

    register: function(eventName, handler, element) {
    	this.observe(eventName, handler, element);
    },

    unregister:  function(eventName, handler, element) {
    	this.stopObserving(eventName, handler, element);
    }
}



/**
 * EventManager
 * Prototype Xtensions http://www.prototypextensions.com
 *
 * @desc Create custom events on your own class
 */
var e107EventManager = Class.create({

    /**
     * Initialize
     *
     * @desc Set scope and events hash
     */
    initialize: function(scope) {
        this.scope  = scope;
        this.events = new Hash();
    },

    /**
     * addListener
     *
     * @desc Add event observer
     */
    addObserver: function(name) {
        return this.events.set(name, new Hash());
    },

    /**
     * observe
     *
     * @desc Add a callback for listener 'name'
     */
    observe: function(name, callback) {
        var observers = this.events.get(name);

        if(!observers) observers = this.addObserver(name);

        if(!Object.isFunction(callback)) {
            //throw('e107EventManager.observe : callback must be an js function');
            //surpess error
            return this;
        }

        var i = this.events.get(name).keys().length;
        observers.set(i, callback.bind(this.scope));
        return this;
    },

    /**
     * stopObserving (class improvements)
     *
     * @desc Remove callback for listener 'name'
     */
    stopObserving: function(name, callback) {
        var observers = this.events.get(name);

        if(!observers) return this;
        observers.each( function(pair) {
        	if(pair.value == callback) {
        		observers.unset(pair.key);
        		$break;
        	}
        });
        return this;
    },

    /**
     * notify
     *
     * @desc Launch all callbacks for listener 'name'
     */
    notify: function(name) {
        var observers = this.events.get(name);
        if(observers) {
            var args = $A(arguments).slice(1);
            //Fix - preserve order
            observers.keys().sort().each( function(key) {
            	var callback = observers.get(key);
                if(Object.isFunction(callback)) {
                    callback.apply(this.scope, args);
                }
            });
        }
        return this;
    }

});

// -------------------------------------------------------------------


/**
 * Base e107 Object - interacts with the registry object
 */
var e107Base = {

    setPath: function(path_object) {
        e107Registry.Path = Object.extend( this.getPathVars(), path_object || {});
        return this;
    },

    addPath: function(path_var, path) {
    	//don't allow overwrite
        if(!e107Registry.Path[path_var]) e107Registry.Path[path_var] = path;
        return this;
    },

    getPathVars: function() {
        return e107Registry.Path;
    },

    getPath: function(path_name) {
        return varset(e107Registry.Path[path_name]);
    },

    _addLan: function(lan_name, lan_value) {
        e107Registry.Lan[lan_name] = lan_value;
        return this;
    },

    _getLan: function(lan_name) {
        return varsettrue(e107Registry.Lan[lan_name], lan_name);
    },

    setLan: function(lan_object) {
    	if(!arguments[1]) {
	        Object.keys(lan_object).each(function(key) {
	            this.addLan(key, lan_object[key]);
	        }, this);
	        return this
    	}
        Object.extend(e107Registry.Lan, (lan_object || {}));
        return this;
    },

    addLan: function(lan_name, lan_value) {
        this._addLan(this.toLanName(lan_name), lan_value);
        return this;
    },

    setModLan: function(mod, lan_object) {
    	Object.keys(lan_object).each( function(key) {
    		this.addModLan(mod, key, lan_object[key]);
    	}, this);
    	return this;
    },

    addModLan: function(mod, lan_name, lan_value) {
        return this._addLan(this.toModLanName(mod, lan_name), lan_value);
    },

    getLan: function(lan_name) {
        return this._getLan(this.toLanName(lan_name));
    },

    getModLan: function(mod, lan_name) {
    	return this._getLan(this.toModLanName(mod, lan_name));
    },

    getLanVars: function() {
        return e107Registry.Lan;
    },

    getModLanVars: function(mod) {
    	return this.getLanFilter(this.toModLanName(mod));
    },

    //Example e107.getLanRange('lan1 lan2 ...'); -- { LAN1: 'lan value1', LAN2: 'lan value2', ... }
    getLanRange: function(lan_keys) {
        var ret = {};
        $w(lan_keys).each( function(key) {
            this[key.toUpperCase()] = e107.getLan(key);
        }, ret);
        return ret;
    },

    //Example e107.getLanFilter('lan_myplug'); -- { LAN_MYPLUG_1: 'lan value1', LAN_MYPLUG_2: 'lan value2', ... }
    getLanFilter: function(filter) {
        var ret = {};
        filter = filter.toUpperCase();
        $H(e107Registry.Lan).keys().each( function(key) {
            if(key.startsWith(filter)) {
                this[key] = e107Registry.Lan[key];
            }
        }, ret);

        return ret;
    },

    setTemplate: function(mod, tmpl_object) {
        mod = this.toModName(mod);
        if(!varset(e107Registry.Template[mod])) {
            e107Registry.Template[mod] = {};
        }
        Object.extend(e107Registry.Template[mod], (tmpl_object || {}));

        return this;
    },

    addTemplate: function(mod, name, tmpl_string) {
        mod = this.toModName(mod);
        if(!varset(e107Registry.Template[mod])) {
            e107Registry.Template[mod] = {};
        }
        e107Registry.Template[mod][name] = tmpl_string;

        return this;
    },

    getTemplates: function(mod) {
        return varsettrue(e107Registry.Template[this.toModName(mod)], {});
    },

    getTemplate: function(mod, name) {
        mod = this.toModName(mod);

        if(varset(e107Registry.Template[mod])) {
            return varsettrue(e107Registry.Template[mod][name], '');
        }

        return '';
    },

    setPrefs: function(mod, pref_object) {
        mod = this.toModName(mod);
        if(!varset(e107Registry.Pref[mod])) {
            e107Registry.Pref[mod] = {};
        }
        Object.extend(e107Registry.Pref[mod], (pref_object || {}));

        return this;
    },

    addPref: function(mod, pref_name, pref_value) {
        mod = this.toModName(mod);
        if(!varset(e107Registry.Pref[mod])) {
            e107Registry.Pref[mod] = {};
        }
        e107Registry.Pref[mod][pref_name] = pref_value;

        return this;
    },

    getPrefs: function(mod) {
        return varsettrue(e107Registry.Pref[this.toModName(mod)], {});
    },

    getPref: function(mod, pref_name, def) {
        mod = this.toModName(mod);
        if(varset(e107Registry.Pref[mod])) {
            return varsettrue(e107Registry.Pref[mod][pref_name], varset(def, null));
        }
        return varset(def, null);
    },

    setCache: function(cache_str, cache_item) {
    	this.clearCache(cache_str);
        e107Registry.Cache['cache-' + cache_str] = cache_item;
        return this;
    },

    getCache: function(cache_str, def) {
        return varset(e107Registry.Cache['cache-' + cache_str], def);
    },

    clearCache: function(cache_str, nodestroy) {
    	var cached = this.getCache(cache_str);
    	if(!nodestroy && cached && Object.isFunction(cached['destroy'])) cached.destroy();
    	e107Registry.Cache['cache-' + cache_str] = null;
    	delete e107Registry.Cache['cache-' + cache_str];
    	return this;
    },

    parseTemplate: function(mod, name, data) {
        var cacheStr = mod + '_' + name;
        var cached = this.getCache(cacheStr);
        if(null === cached) {
            var tmp = this.getTemplate(mod, name);
            cached = new Template(tmp);
            this.setCache(cacheStr, cached);
        }

        if(varsettrue(arguments[3])) {
            data = this.getParseData(Object.clone(data || {}));
        }

        try{
           return cached.evaluate(data || {});
        } catch(e) {
            return '';
        }
    },

    getParseData: function (data) {
        data = Object.extend(data || {},
          Object.extend(this.getLanVars(), this.getPathVars())
        );

        return data;
    },

    parseLan: function(str) {
        return String(str).interpolate(this.getLanVars());
    },

    parsePath: function(str) {
        return String(str).interpolate(this.getPathVars());
    },

    toModName: function(mod, raw) {
    	return raw ? mod.dasherize() : mod.dasherize().camelize().ucfirst();
    },

    toLanName: function(lan) {
    	return 'JSLAN_' + lan.underscore().toUpperCase();
    },

    toModLanName: function(raw_mod, lan) {
    	return this.toLanName(raw_mod + '_' + varset(lan, ''));
    }
};

// -------------------------------------------------------------------

/**
 * String Extensions
 *
 * Methods used later in the core + e107base shorthands
 */
Object.extend(String.prototype, {

	//php like
    ucfirst: function() {
        return this.charAt(0).toUpperCase() + this.substring(1);
    },

	//Create element from string - Prototype UI
	createElement: function() {
	    var wrapper = new Element('div'); wrapper.innerHTML = this;
	    return wrapper.down();
	},

	parseToElement: function(data) {
		return this.parseTemplate(data).createElement();
	},

	parseTemplate: function(data) {
		return this.interpolate(e107Base.getParseData(data || {}));
	},

	parsePath: function() {
		return e107Base.parsePath(this);
	},

	parseLan: function() {
		return e107Base.parseLan(this);
	},

    addLan: function(lan_name) {
    	if(lan_name)
        	e107Base.addLan(lan_name, this);
        return e107Base.toLanName(lan_name);
    },

    addModLan: function(mod, lan_name) {
    	if(mod && lan_name)
        	e107Base.addModLan(mod, lan_name, this);
        return e107Base.toModLanName(mod, lan_name);
    },

    getLan: function() {
        return e107Base.getLan(this);
    },

    getModLan: function(mod) {
    	if(mod)
    		return e107Base.getModLan(mod, this);
    	return this;
    }
});

// -------------------------------------------------------------------

/**
 * e107WidgetAbstract Class
 */
var e107WidgetAbstract = Class.create(e107Base);
var e107WidgetAbstract = Class.create(e107WidgetAbstract, {

    initMod: function(modId, options, inherit) {

        this.mod = e107Base.toModName(modId, true);
        if(!this.mod) {
            throw 'Illegal Mod ID';
        }

		var methods = 'setTemplate addTemplate getTemplate parseTemplate setPrefs addPref getPref getPrefs getLan getLanVars addLan setLan';
		var that = this;

		//Some magic
		$w(methods).each(function(method){
			var mod_method = method.gsub(/^(set|get|add|parse)(.*)$/, function(match){
				return match[1] + 'Mod' + match[2];
			});
			var parent_method = !e107Base[mod_method] ? method : mod_method;
			this[mod_method] = e107Base[parent_method].bind(this, this.mod);
		}.bind(that));

		Object.extend(that, {
			getModName: function(raw) {
				return raw ? this.mod : e107Base.toModName(this.mod);
			},

		    parseModLan: function(str) {
		        return String(str).interpolate(e107Base.getModLan(this.mod));
		    },

		    setModCache: function(cache_str, cache_item) {
		    	e107Base.setCache(this.getModName(true) + varsettrue(cache_str, ''), cache_item);
		    	return this;
		    },

		    getModCache: function(cache_str) {
		    	return e107Base.getCache(this.getModName(true) + varsettrue(cache_str, ''));
		    },

		    clearModCache: function(cache_str) {
		    	e107Base.clearCache(this.getModName(true) + varsettrue(cache_str, ''));
		    	return this;
		    }
		});

        //Merge option object (recursive)
        this.setOptions(options, inherit);

        return this;
    },


    setOptions: function(options, inherit) {
        this.options = {};

        var c = this.constructor;

        if (c.superclass && inherit) {
            var chain = [], klass = c;

            while (klass = klass.superclass)
                chain.push(klass);

            chain = chain.reverse();
            for (var i = 0, len = chain.length; i < len; i++) {
                if(!chain[i].getModPrefs) chain[i].getModPrefs = Prototype.emptyFunction;
                //global options if available
                Object.extend(this.options, chain[i].getModPrefs() || {});
            }
        }

        //global options if available
        if(!this.getModPrefs) { this.getModPrefs = Prototype.emptyFunction; }

        Object.extend(this.options, this.getModPrefs() || {});
        return Object.extend(this.options, options || {});
    }

});

// -------------------------------------------------------------------

/**
 * Core - everything's widget!
 */
var e107Core = Class.create(e107WidgetAbstract, {
    initialize: function() {
        this.initMod('core');
    },

    /**
     * e107:loaded Event observer
     */
    runOnLoad: function(handler, element, reload) {
    	e107Event.register('loaded', handler, element || document);
    	if(reload)
    		this.runOnReload(handler, element);

    	return this;
    },

    /**
     * Ajax after update Event observer
     */
    runOnReload: function(handler, element) {
    	e107Event.register('ajax_update_after', handler, element || document);
    	return this;
    }

});

//e107Core instance
var e107 = new e107Core();

// -------------------------------------------------------------------

/*
 * Widgets namespace
 * @descr should contain only high-level classes
 */
 var e107Widgets = {};

/**
 * Utils namespace
 * @descr contains low-level classes and non-widget high-level classes/objects
 */
var e107Utils = {}

/**
 * Helper namespace
 * @descr includes all old e107 functions + some new helper methods/classes
 */
var e107Helper = {
    fxToggle: function(el, fx) {
    	var opt = Object.extend( { effect: 'blind' , options: {duration: 0.5} }, fx || {});
        Effect.toggle(el, opt.effect, opt.options);
    }
}

// -------------------------------------------------------------------

/*
 * Element extension
 */
Element.addMethods( {
	fxToggle: function(element, options) {
	    e107Helper.fxToggle(element, options);
	}
});

// -------------------------------------------------------------------

/**
 * Backward compatibility
 */
Object.extend(e107Helper, {

	toggle: function(el) {
		var eltoggle;
		/**
		 * (SecretR) Notice
		 *
		 * Logic mismatch!
		 * Passed element/string should be always the target element (which will be toggled)
		 *  OR
		 * anchor: <a href="#some-id"> where 'some-id' is the id of the target element
		 * This method will be rewritten after the core is cleaned up. After this point
		 * the target element will be auto-hidden (no need of class="e-hideme")
		 */

        if(false === Object.isString(el) || (
        	($(el) && $(el).nodeName.toLowerCase() == 'a' && $(el).readAttribute('href'))
        		||
        	($(el) && $(el).readAttribute('type') && $(el).readAttribute('type').toLowerCase() == 'input') /* deprecated */
        )) {
        	eltoggle = (function(el) {
	    		return Try.these(
	    		    function() { var ret = $(el.readAttribute('href').substr(1));  if(ret) { return ret; } throw 'Error';}, //This will be the only valid case in the near future
                    function() { var ret = el.next('.e-expandme'); if(ret) { return ret; } throw 'Error';},// maybe this too?
                    function() { var ret = el.next('div'); if(ret) { return ret; } throw 'Error'; }, //backward compatibality - DEPRECATED
                    function() { return null; } //break
	    		) || false;
        	})($(el));
        } else {
            var eltoggle = $(el);
        }

        if(!eltoggle) return false;

		var fx = varset(arguments[1], null);

		if(false !== fx)
		    this.fxToggle(eltoggle, fx || {});
		else
		    $(eltoggle).toggle();

		return true;
	},

    /**
     * added as Element method below
     * No toggle effects!
     */
    downToggle: function(element, selector) {
    	$(element).select(varsettrue(selector, '.e-expandme')).invoke('toggle');
    	return element;
    },

	/**
	 * Event listener - e107:loaded|e107:ajax_update_after
	 * @see e107Core#addOnLoad
	 */
    toggleObserver: function(event) {
    	var element = event.memo['element'] ? $(event.memo.element) : $$('body')[0];
        Element.select(element, '.e-expandit').invoke('observe', 'click', function(e) {
            var element = e.findElement('a');
            if(!element) element = e.element();
            if(this.toggle(element, {})) e.stop();
        }.bindAsEventListener(e107Helper));
    },

	/**
	 * Event listener - e107:loaded|e107:ajax_update_after
	 * Runs fxToggle against multiple elements. The trigger element is an anchor tag, IDs of the elements to be toggled are defined in
	 * the 'href' attribute separated by a hash character (including a leading hash), e.g. href='#id1#id2#id3'
	 * @see e107Core#addOnLoad
	 */
    toggleManyObserver: function(event) {
    	var element = event.memo['element'] ? $(event.memo.element) : $$('body')[0];
        Element.select(element, '.e-swapit').invoke('observe', 'click', function(e) {
            var element = e.findElement('a');
            var els = element.readAttribute('href').split('#').without('');
            els.each(function(el) {
               if ($(el)) {
                  $(el).fxToggle({
                     options: { duration: 0.5, queue: { position: 'end', scope: 'toggleManyObserver'} }
                  });
               }
            });
            e.stop();
        }.bindAsEventListener(e107Helper));
    },

    /**
     * Add fx scroll on click event
     * on all '<a href="#something" class="scroll-to"></a>' elements
     */
    scrollToObserver: function(event) {
    	var element = event.memo['element'] ? $(event.memo.element) : $$('body')[0];
		Element.select(element, 'a[href^=#].scroll-to:not([href=#])').invoke('observe', 'click', function(e) {
			new Effect.ScrollTo(e.findElement('a').hash.substr(1));
			e.stop();
		});
    },

    /**
     *
     *
     */
    executeAutoSubmit: function(event) {
    	var element = event.memo['element'] ? $(event.memo.element) : $$('body')[0];
		Element.select(element, 'select.e-autosubmit').invoke('observe', 'change', function(e) {
			e107Helper.selectAutoSubmit(e.element());
		});
    },

    selectAutoSubmit: function(el) {
		var frm = el.up('form');
		if (frm) {
			if(el.value == '___reset___') {
				frm.getInputs('text').each(function(r) { r.value = '' });
				frm.getInputs('password').each(function(r) { r.value = '' });
				el.value = '';
			}
			frm.submit();
		}
		if(el.hasClassName('reset')) el.selectedIndex = 0;
    },

    /**
     * added as Element method below
     */
    downHide: function(element, selector) {
    	$(element).select(varsettrue(selector, '.e-hideme')).invoke('hide');
    	return element;
    },

    /**
     * added as Element method below
     */
    downShow: function(element, selector) {
    	$(element).select(varsettrue(selector, '.e-hideme')).invoke('show');
    	return element;
    },

    //event listener
    autoHide: function(event) {
    	var hideunder = event.memo['element'] ? $(event.memo.element) : $$('body')[0];
        if(hideunder) hideunder.downHide();
    },

    /**
     * added as Element method below
     * autocomplete="off" - all major browsers except Opera(?!)
     */
    noHistory: function(element) {
        $(element).writeAttribute('autocomplete', 'off');
        return element;
    },

    /**
     * added as Element method below
     */
    downNoHistory: function(element, selector) {
    	$(element).select(varsettrue(selector, 'input.e-nohistory')).invoke('noHistory');
    	return element;
    },

    //event listener
    autoNoHistory: function(event) {
    	var down = event.memo['element'] ? $(event.memo.element) : $$('body')[0];
        if(down) down.downNoHistory();
    },

    /**
     * added as Element method below
     */
	externalLink: function (element) {
	    $(element).writeAttribute('target', '_blank');
	    return element;
	},

    /**
     * added as Element method below
     */
    downExternalLinks: function(element) {
    	$(element).select('a[rel~=external]').invoke('externalLink');
    	return element;
    },

    //event listener
	autoExternalLinks: function (event) {
		//event.element() works for IE now!
		//TODO - remove memo.element references
		//event.memo['element'] ? $(event.memo.element) : $$('body')[0];
		var down = event.element() != document ? event.element() : $$('body')[0];
	    if(down) down.downExternalLinks();
	},

	urlJump: function(url) {
	    top.window.location = url;
	},

	//TODO Widget - e107Window#confirm;
    confirm: function(thetext) {
    	return confirm(thetext);
    },

    autoConfirm: function(event) {

    },

	imagePreload: function(ejs_path, ejs_imageString) {
	    var ejs_imageArray = ejs_imageString.split(',');
	    for(var ejs_loadall = 0, len = ejs_imageArray.length; ejs_loadall < len; ejs_loadall++){
	        var ejs_LoadedImage = new Image();
	        ejs_LoadedImage.src=ejs_path + ejs_imageArray[ejs_loadall];
	    }
	},

	toggleChecked: function(form, state, selector, byId) {
		form = $(form); if(!form) { return; }
		if(byId) selector = 'id^=' + selector;
		$A(form.select('input[type=checkbox][' + selector + ']')).each(function(element) { if(!element.disabled) element.checked=state });
	},

	//This will be replaced later with upload_ui.php own JS method
	//and moved to a separate class
    __dupCounter: 1,
    __dupTmpTemplate: '',
	//FIXME
	duplicateHTML: function(copy, paste, baseid) {
        if(!$(copy) || !$(paste)) { return; }
        this.__dupCounter++;
        var source = $($(copy).cloneNode(true)), newentry, newid, containerId, clearB;

        source.writeAttribute('id', source.readAttribute('id') + this.__dupCounter);
        newid = (baseid || 'duplicated') + '-' + this.__dupCounter;

        var tmpl = this.getDuplicateTemplate();
        if(tmpl) {
        	var sourceInnerHTML = source.innerHTML;
        	source = source.update(tmpl.parseToElement({
                duplicateBody: sourceInnerHTML,
                removeId: 'remove-' + newid,
                baseId: baseid || '',
                newId: newid,
                counter: this.__dupCounter
            })).down().hide();
        	clearB = $(source.select('#remove-' + newid)[0]);
        } else {
        	//see clear, clearL and clearR CSS definitions
        	clearB = new Element('input', { 'class': 'button', 'value': 'x', 'type': 'button', 'id': 'remove-' + newid }); //backward compat. - subject of removal
        	source.insert({
        		top: new Element('div', {'class': 'clear'}),
        		bottom: clearB
        	}).hide();
        }
        if(baseid) {
            source.innerHTML = source.innerHTML.replace(new RegExp(baseid, 'g'), newid);
        }
        var containerId = source.identify();
        $(paste).insert(source);
        //Again - the EVIL IE6
        if(!clearB) { clearB = $('remove-' + newid); }

        clearB.observe('click', function(e) {
        	e.stop();
        	var el = e.element().up('#'+containerId);
	        el.fxToggle({
	            effect: 'appear',
	            options: {
	            	duration: 0.4,
	                afterFinish: function(o) { o.element.remove(); }
	            }
	        });
        }.bind(this));

        source.fxToggle({
        	effect: 'appear',
        	options: { duration: 0.5 }
        });
	},

    getDuplicateTemplate: function() {
    	if(this.__dupTmpTemplate) {
    		var tmpl = this.__dupTmpTemplate;
    		this.__dupTmpTemplate = '';
    		return tmpl;
    	}
    	return e107.getModTemplate('duplicateHTML');
    },

    setDuplicateTemplate: function(tmpl) {
        return this.__dupTmpTemplate = tmpl;
    },

	previewImage: function(src_val, img_path, not_found) {
	   $(src_val + '_prev').src = $(src_val).value ? img_path + $(src_val).value : not_found;
	    return;
	},

	insertText: function(str, tagid, display) {
	    $(tagid).value = str.escapeHTML();
	    if($(display)) {
	        $(display).fxToggle();
	    }
	},

	appendText: function(str, tagid, display) {
	    $(tagid).focus().value += str.escapeHTML();
	    if($(display)) {
	        $(display).fxToggle();
	    }
	},

	//by Lokesh Dhakar - http://www.lokeshdhakar.com
    getPageSize: function() {

	     var xScroll, yScroll;

		if (window.innerHeight && window.scrollMaxY) {
			xScroll = window.innerWidth + window.scrollMaxX;
			yScroll = window.innerHeight + window.scrollMaxY;
		} else if (document.body.scrollHeight > document.body.offsetHeight){ // all but Explorer Mac
			xScroll = document.body.scrollWidth;
			yScroll = document.body.scrollHeight;
		} else { // Explorer Mac...would also work in Explorer 6 Strict, Mozilla and Safari
			xScroll = document.body.offsetWidth;
			yScroll = document.body.offsetHeight;
		}

		var windowWidth, windowHeight;

		if (self.innerHeight) {	// all except Explorer
			if(document.documentElement.clientWidth){
				windowWidth = document.documentElement.clientWidth;
			} else {
				windowWidth = self.innerWidth;
			}
			windowHeight = self.innerHeight;
		} else if (document.documentElement && document.documentElement.clientHeight) { // Explorer 6 Strict Mode
			windowWidth = document.documentElement.clientWidth;
			windowHeight = document.documentElement.clientHeight;
		} else if (document.body) { // other Explorers
			windowWidth = document.body.clientWidth;
			windowHeight = document.body.clientHeight;
		}

		// for small pages with total height less then height of the viewport
		if(yScroll < windowHeight){
			pageHeight = windowHeight;
		} else {
			pageHeight = yScroll;
		}

		// for small pages with total width less then width of the viewport
		if(xScroll < windowWidth){
			pageWidth = xScroll;
		} else {
			pageWidth = windowWidth;
		}

		return [pageWidth,pageHeight];
	}
});


// -------------------------------------------------------------------

/*
 * Element extensions
 */
Element.addMethods( {
	downNoHistory: e107Helper.downNoHistory,
	downHide: e107Helper.downHide,
	downShow: e107Helper.downShow,
	downToggle: e107Helper.downToggle,
	downExternalLinks: e107Helper.downExternalLinks,

	// -- more useful extensions - taken from Prototype UI --
	getScrollDimensions: function(element) {
	    element = $(element);
	    return {
	      width:  element.scrollWidth,
	      height: element.scrollHeight
	    }
	},

	getScrollOffset: function(element) {
	    element = $(element);
	    return Element._returnOffset(element.scrollLeft, element.scrollTop);
	 },

	setScrollOffset: function(element, offset) {
	    element = $(element);
	    if (arguments.length == 3)
	      offset = { left: offset, top: arguments[2] };
	    element.scrollLeft = offset.left;
	    element.scrollTop  = offset.top;
	    return element;
	},

	// returns "clean" numerical style (without "px") or null if style can not be resolved
	// or is not numeric
	getNumStyle: function(element, style) {
	    var value = parseFloat($(element).getStyle(style));
	    return isNaN(value) ? null : value;
	},

	// (http://tobielangel.com/2007/5/22/prototype-quick-tip)
	appendText: function(element, text) {
	    element = $(element);
	    element.appendChild(document.createTextNode(String.interpret(text)));
	    return element;
	}
});

Object.extend(document.viewport, {
	// Alias this method for consistency
	getScrollOffset: document.viewport.getScrollOffsets,

	setScrollOffset: function(offset) {
		Element.setScrollOffset(Prototype.Browser.WebKit ? document.body : document.documentElement, offset);
	},

	getScrollDimensions: function() {
		return Element.getScrollDimensions(Prototype.Browser.WebKit ? document.body : document.documentElement);
	}
});

Element.addMethods('INPUT', {
	noHistory: e107Helper.noHistory
});

Element.addMethods('A', {
	externalLink: e107Helper.externalLink
});

Element.addMethods('FORM', {
	toggleChecked: e107Helper.toggleChecked
});

// -------------------------------------------------------------------

/**
 * e107BB helper
 */
e107Helper.BB = {

	__selectedInputArea: null,

	store: function(textAr){
	    this.__selectedInputArea = $(textAr);
	},

	/**
	 * New improved version - fixed scroll to top behaviour when inserting BBcodes
	 * @TODO - improve it further
	 */
	insert: function(text, emote) {
	    if (!this.__selectedInputArea) {
	    	return; //[SecretR] TODO - alert the user
	    }
	    var eField = this.__selectedInputArea,
	    	tags = this.parse(text, emote),
	    	scrollPos, sel, newStart, newEnd = '';
        if(this.insertIE(eField, text, tags)) return;

	    scrollPos = eField.scrollTop, sel = (eField.value).substring(eField.selectionStart, eField.selectionEnd);

	    newStart = eField.selectionStart + tags.start.length + sel.length + tags.end.length;
	    if(eField.selectionStart || (!eField.selectionStart && eField.selectionEnd != eField.textLength)) {
	    	newEnd = (eField.value).substring(eField.selectionEnd, eField.textLength);
	    }
	    eField.value = (eField.value).substring(0, eField.selectionStart) + tags.start + sel + tags.end + newEnd;
	    eField.focus(); eField.selectionStart = newStart; eField.selectionEnd = newStart; eField.scrollTop = scrollPos;
	    return;

	},

	insertIE: function(area, text, tags) {
        // IE fix
        if (!document.selection) return false;
        var eSelection = document.selection.createRange().text;
        area.focus();
        if (eSelection) {
            document.selection.createRange().text = tags.start + eSelection + tags.end;
        } else {
            document.selection.createRange().text = tags.start + tags.end;
        }
        eSelection = ''; area.blur(); area.focus();
        return true;
	},

	parse: function(text, isEmote) {
		var tOpen = text, tClose = '';
        if (isEmote != true) {  // Split if its a paired bbcode
            var tmp = text.split('][', 2);
            tOpen = varset(tmp[1]) ? tmp[0] + ']' : text;
            tClose = varset(tmp[1]) ? '[' + tmp[1] : '';
        }
        return { start: tOpen, end: tClose };
	},

	//TODO VERY BAD - make it right ASAP!
	help_old: function(help, tagid, nohtml){
		if(!tagid || !$(tagid)) return;
		if(nohtml) { help = help.escapeHTML(); }
		if($(tagid)) { $(tagid).value = help; }
		else if($('helpb')) {
			$('helpb').value = help;
		}
	},

	//FIXME - The new BB help system
	help: function(help, tagid, nohtml){
		if(nohtml) { help = help.escapeHTML(); }
		if(!tagid || !$(tagid)) return;
		if(help) {
			var wrapper = new Element('div', {'style': 'position: relative'}).update(help);
			$(tagid).update(wrapper).fxToggle();
		} else {
			$(tagid).update('').fxToggle();
		}
	}
};

//Iframe Shim - from PrototypeUI
e107Utils.IframeShim = Class.create({
	initialize: function() {
		this.element = new Element('iframe',{
			style: 'position:absolute;filter:progid:DXImageTransform.Microsoft.Alpha(opacity=0);display:none;',
			src: 'javascript:void(0);',
			frameborder: 0
		});
		$(document.body).insert(this.element);
	},
	hide: function() {
		this.element.hide();
		return this;
	},
	show: function() {
		this.element.show();
		return this;
	},
	positionUnder: function(element) {
		var element = $(element),
			offset = element.cumulativeOffset(),
			dimensions = element.getDimensions(),
			style = {
				left: offset[0] + 'px',
				top: offset[1] + 'px',
				width: dimensions.width + 'px',
				height: dimensions.height + 'px',
				zIndex: element.getStyle('zIndex') - 1
			};

		this.element.setStyle(style).show();
		return this;
	},
	setBounds: function(bounds) {
		for(prop in bounds)
			bounds[prop] += 'px';
		this.element.setStyle(bounds);
		return this;
	},
	setSize: function(width, height) {
		this.element.style.width = parseInt(width) + 'px';
		this.element.style.height = parseInt(height) + 'px';
		return this;
	},
	setPosition: function(top, left) {
		this.element.style.top = parseInt(top) + 'px';
		this.element.style.left = parseInt(left) + 'px';
		return this;
	},
	destroy: function() {
		if(this.element)
			this.element.remove();
		return this;
	}
});

// -------------------------------------------------------------------

/**
 * Show Page/Element loading status (during AJAX call)
 *
 * @class e107Utils.LoadingStatus
 * @widget: core-loading
 * @version 1.0
 * @author SecretR
 * @extends e107WidgetAbstract
 * @template: 'template'
 * @cache_string: 'instance-loading-status'
 */


('Loading')				   .addModLan('core-loading', 'alt');
('Loading, please wait...').addModLan('core-loading', 'text');

/**
 * Global Prefs
 */
e107Base.setPrefs('core-loading', {
	opacity: 0.8
	//TODO - more to come!
});

e107Utils.LoadingStatus = Class.create(e107WidgetAbstract, {

	initialize: function(dest_element, options) {
		this.initMod('core-loading', options);
		this.cacheStr = 'instance-loading-status';

		this.loading_mask_loader = false;
		this.loading_mask = $('loading-mask');
		this.iframeShim = this.getModCache(this.cacheStr + '-iframe');
		this.destElement = ($(dest_element) || $$('body')[0]);

		this.re_center = this.recenter.bindAsEventListener(this);

		this.create();
	    if(this.options.show_auto)
	    	this.show();
	},

	startObserving: function() {
		Event.observe(window,"resize", this.re_center);
    	if(e107API.Browser.IE && e107API.Browser.IE <= 7)
    		Event.observe(window,"scroll", this.re_center);
    	return this;
	},

	stopObserving:  function() {
		Event.stopObserving(window, "resize", this.re_center);
    	if(e107API.Browser.IE && e107API.Browser.IE <= 7)
    		Event.stopObserving(window, "scroll", this.re_center);
    	return this;
	},

	set_destination: function(dest_element) {
		this.destElement = $(dest_element) || $$('body')[0];
		return this;
	},

	create: function() {
		if(!this.loading_mask) {
			var objBody = $$('body')[0];
			this.loading_mask = this.getModTemplate('template').parseToElement().hide();

			objBody.insert({
				bottom: this.loading_mask
			});
		}
		this.loading_mask.setStyle( { 'opacity': this.options.opacity, zIndex: 9000 } );
		this.loading_mask_loader = this.loading_mask.down('#loading-mask-loader');
		this.loading_mask_loader.setStyle( { /*'position': 'fixed', */zIndex: 9100 } );
		//Create iframeShim if required
		this.createShim();
		return this;
 	},

	show: function () {
		if(this.loading_mask.visible()) return;
		this.startObserving();
		this.center();
		this.loading_mask.show();
		return this;
	},

	hide: function () {
		this.loading_mask.hide();
		this.stopObserving().positionShim(true);
		return this;
	},

	center: function() {
		//Evil IE6
		if(!this.iecenter()) {
			Element.clonePosition(this.loading_mask, this.destElement);
			this.fixBody().positionShim(false);
		}
		return this;

	},

	recenter: function() {
		if(!this.iecenter()) {
			Element.clonePosition(this.loading_mask, this.destElement);
			this.fixBody().positionShim(false);
		}
		return this;
	},

	iecenter: function() {
		//TODO - actually ie7 should work without this - investigate
		if(e107API.Browser.IE && e107API.Browser.IE <= 7) {
			//The 'freezing' problem solved (opacity = 1 ?!)
			this.loading_mask.show();
			var offset = document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop;
			var destdim = document.documentElement.clientHeight ? document.documentElement.clientHeight : document.body.clientHeight;

			if(!this.lmh) this.lmh = this.loading_mask_loader.getHeight();
			var eldim = this.lmh;
			var toph = parseInt(destdim/2 - eldim/2 + offset );
			this.loading_mask.setStyle({top: 0, left: 0, 'opacity': 1});
			this.fixBody(true);
			this.loading_mask_loader.setStyle( {
				'position': 'absolute',
				'top': toph + 'px',
				'opacity': 1
			});

			this.positionShim(false);
			return true;
		}
		return false;
	},

	fixBody: function(force) {
		if(force || this.destElement.nodeName.toLowerCase() == 'body') {
			var ps = e107Helper.getPageSize();
			this.loading_mask.setStyle({ 'width': parseInt(ps[0]) + 'px', 'height': parseInt(ps[1]) + 'px' });
		}
		return this;
	},

	createShim: function() {
		if(e107API.Browser.IE && e107API.Browser.IE <= 7 && !this.iframeShim) {
			this.iframeShim = new e107Utils.IframeShim().hide();
			this.setModCache(this.cacheStr +'-iframe', this.iframeShim);
		}

		return this;
	},

	positionShim: function(hide) {
		if(!e107API.Browser.IE || e107API.Browser.IE > 6) return this;
		if(hide) {
			this.iframeShim.hide(); return this;
		}
		this.iframeShim.positionUnder(this.loading_mask).show();
		return this;
	}
});

/**
 * Register page loading core events
 */
e107Event.register('ajax_loading_start', function(event) {
	var loadingObj = e107.getModCache('ajax-loader');
	if(!loadingObj) {
		loadingObj = new e107Utils.LoadingStatus(false, { show_auto: false });
		e107.setModCache('ajax-loader', loadingObj);
	}
	loadingObj.set_destination(event.memo.overlayPage).show();
});

e107Event.register('ajax_loading_end', function(event) {
	var loadingObj = e107.getModCache('ajax-loader');
	if(loadingObj) {
		window.setTimeout( function(){ loadingObj.hide() }, 200);
	}
});

/**
 * e107Utils.LoadingElement
 * based on Protoload by Andreas Kalsch
 */
e107Base.setPrefs('core-loading-element', {
	overlayDelay: 50,
	opacity: 0.8,
	zIndex: 10,
	className: 'element-loading-mask',
	backgroundImage: '#{e_IMAGE}generic/loading_32.gif'
});

e107Utils.LoadingElement = {
	startLoading: function(element, options) {
		if(!options) options = {};
		Object.extend(options, e107Base.getPrefs('core-loading-element') || {});
		element = $(element);

		var zindex = parseInt(e107.getModPref('zIndex')) + parseInt(options.zIndex);
		var cacheStr = 'core-loading-element-' + $(element).identify();
		element._waiting = true;
		//can't use element._eloading for storing objects because of IE6 memory leak
		var _eloading = e107Base.getCache(cacheStr);

		if (!_eloading) {
			_eloading = new Element('div', { 'class': options.className }).setStyle({
				position: 'absolute',
				opacity: options.opacity,
				zIndex: zindex
				//backgroundImage: 'url(' + options.backgroundImage.parsePath() + ')'
			});

			$$('body')[0].insert({ bottom: _eloading });
			var imgcheck = _eloading.getStyle('background-image');
			//console.log(options.backgroundImage.parsePath());
			if(!imgcheck || imgcheck == 'none') //only if not specified by CSS
				_eloading.setStyle( {backgroundImage: 'url(' + options.backgroundImage.parsePath() + ')'});
			e107Base.setCache(cacheStr, _eloading);
		}
		window.setTimeout(( function() {
			if (this._waiting) {
				Element.clonePosition(_eloading, this);
				_eloading.show();
			}
		}).bind(element), options.overlayDelay);

	},

	stopLoading: function(element) {
		if (element._waiting) {
			element._waiting = false;
			var cacheStr = 'core-loading-element-' + $(element).identify(), _eloading = e107Base.getCache(cacheStr);
			if($(_eloading)) $(_eloading).hide();//remove it or not?
			//e107Base.clearCache(cacheStr);
		}
	}
};

Element.addMethods(e107Utils.LoadingElement);

/**
 * Register element loading core events
 */
e107Event.register('ajax_loading_element_start', function(event) {
	var element = $(event.memo.overlayElement);
	if(element) element.startLoading();
});

e107Event.register('ajax_loading_element_end', function(event) {
	var element = $(event.memo.overlayElement);
	if(element)  window.setTimeout( function(){ element.stopLoading() }.bind(element), 50);
});

// -------------------------------------------------------------------

// ###### START DEPRECATED - subject of removal!!! ######

//@see e107Helper#toggle, e107Helper#autoToggle
var expandit = function(curobj, hide) {
	e107Helper.toggle(curobj, {});

    if(hide) { //don't use it - will be removed
        hide.replace(/[\s]?,[\s]?/, ' ').strip();
        $w(hide).each(function(h) {
            if(Object.isElement($(h))) { $(h).hide(); }
        });
    }
}

//Use Prototype JS instead: $(id).update(txt);
var setInner = function(id, txt) {
    $(id).update(txt);
}

//@see e107Helper#confirm TODO @see e107ModalConfirm#confirm
var jsconfirm = function(thetext){
        return e107Helper.confirm(thetext);
}

//Use Prototype JS instead e.g.: $(tagid).value = str; $(display).hide();
var insertext = function(str, tagid, display) {
    e107Helper.insertText(str, tagid, display);
}

//Use Prototype JS instead e.g.: $(tagid).focus().value += str; $(display).hide();
var appendtext = function(str, tagid, display) {
    e107Helper.appendText(str, tagid, display);
}

//TODO - e107Window class, e107Helper#openWindow proxy
var open_window = function(url, wth, hgt) {
    if('full' == wth){
        pwindow = window.open(url);
    } else {
    	mywidth = varset(wth, 600);
    	myheight = varset(wth, 400);
        pwindow = window.open(url,'Name', 'top=100,left=100,resizable=yes,width='+mywidth+',height='+myheight+',scrollbars=yes,menubar=yes')
    }
    pwindow.focus();
}

//TODO Window class
var closeWindow = function(form){
    if((window.opener!=null)&&(!window.opener.closed)){
        window.opener.location.reload();
    }
    if(window.opener!=null) {
        window.close();
    }else{setWinType(form);form.whatAction.value="Close";form.submit();}
}


//@see e107Helper#urljump
var urljump = function(url) {
    e107Helper.urlJump(url);
}

//@see e107Helper#imagePreload
var ejs_preload = function(ejs_path, ejs_imageString){
    e107Helper.imagePreload(ejs_path, ejs_imageString)
}

//Use Prototype JS e.g.: $(cntfield).value = $(field).value.length;
var textCounter = function(field,cntfield) {
    cntfield.value = field.value.length;
}

//Not used anymore - seek & remove
/*
function openwindow() {
    opener = window.open("htmlarea/index.php", "popup","top=50,left=100,resizable=no,width=670,height=520,scrollbars=no,menubar=no");
    opener.focus();
}
*/

//@see e107Helper#toggleChecked
var setCheckboxes = function(the_form, do_check, the_cb) { //backward compatibility
    e107Helper.toggleChecked(the_form, do_check, 'name^=' + the_cb.gsub(/[\[\]]/, ''), false);
}

//@see e107Helper.BB#storeCaret
var storeCaret = function(textAr) {
	e107Helper.BB.store(textAr); return;
}

//@see e107Helper.BB#insert
var addtext = function(text, emote) {
    e107Helper.BB.insert(text, emote); return;
}

// Prompt for user input value
var addinput = function(text) {

// quick fix to prevent JS errors - proper match was done only for latin words
	var rep = text.match(/\=([^\]]*)\]/);
	var val = rep ? prompt(rep[1]) : prompt('http://');

	if(!val)
	{
		return;
	}
	var newtext = text.replace(rep[1], val);
	emote = '';
    e107Helper.BB.insert(newtext, emote); return;
}

//@see e107Helper.BB#help
var help = function(help,tagid) {
    e107Helper.BB.help_old(help, tagid, true);
}

//Use Prototype JS e.g.: $(object).addClassName(over); $(object).removeClassName(over);
var eover = function(object, over) {
    $(object).writeAttribute('class', over);
}

//@see e107Helper#duplicateHTML
var duplicateHTML = function(copy, paste, baseid) {
    e107Helper.duplicateHTML(copy,paste,baseid);
}

var preview_image = function(src_val,img_path, not_found) {
    e107Helper.previewImage(src_val, img_path, not_found)
}

var externalLinks = function () {
    //already event listener
};
// ###### END DEPRECATED ######

// -------------------------------------------------------------------

/**
 * e107History
 *
 * Prototype Xtensions http://www.prototypextensions.com/
 */
var e107History = {
    __altered: false,
    __currentHash: null,
    __previousHash: null,
    __iframe: false,
    __title: false,

    /**
     * init()
     * @desc Initialize the hash. Call this method in first
     */
    init: function() {
        var inst  = this;
        var hash  = location.hash.substring(1);
        this.hash = $H(hash.toQueryParams());
        this.__currentHash  = hash;
        this.__previousHash = hash;

        this.__title = document.title;

        if(e107API.Browser.IE && e107API.Browser.IE < 8) {
            document.observe('dom:loaded', function(e) {
                if(!$('e107-px-historyframe')) {
                    e107History.__iframe = new Element('iframe', {
                        name   : 'e107-px-historyframe',
                        id     : 'e107-px-historyframe',
                        src    : '',
                        width  : '0',
                        height : '0',
                        style  : {
                            visibility: 'hidden'
                        }
                    });

                    document.body.appendChild(e107History.__iframe);

                    e107History.setHashOnIframe(inst.hash.toQueryString());
                }
            });
        }
    },

    /**
     * set( string name, string value )
     *
     * @desc Set new value value for parameter name
     */
    set: function(name, value) {
        this.__previousHash = this.hash.toQueryString();
        this.hash.set(name, value);
        this.apply();
    },

    /**
     * get( string $name )
     *
     * @desc Get value parameter $name
     */
    get: function(name) {
        return this.hash.get(name);
    },

    /**
     * unset( string $name )
     *
     * @desc Unset parameter $name
     */
    unset: function(name) {
        this.hash.unset(name);
        this.apply();
    },

    /**
     * update()
     *
     * @desc Updates this.hash with the current hash
     */
    update: function() {
        this.__previousHash = this.hash.toQueryString();
        var hash = window.location.hash.substring(1);

        // If IE, look in the iframe if the hash is updated
        if(e107API.Browser.IE && e107API.Browser.IE < 8 && this.__iframe) {
            var hashInFrame = this.getHashOnIframe();

            if(hashInFrame != hash) {
                hash = hashInFrame;
            }
        }

        this.hash = $H(hash.toQueryParams());
        this.__currentHash = hash;
    },

    /**
     * apply()
     *
     * @desc Apply this.hash to location.hash
     */
    apply: function() {
        var newHash = this.hash.toQueryString();

        // set new hash
        window.location.hash = newHash;

        // If IE, apply new hash to frame for history
        if(e107API.Browser.IE && e107API.Browser.IE < 8 && this.__iframe) {
            if(this.__currentHash != newHash)
            {
                this.setHashOnIframe(newHash);
            }
            else if(newHash != this.getHashOnIframe())
            {
                this.setHashOnIframe(newHash);
            }
        }
    },

    /**
     * isAltered()
     *
     * @desc Return true if current hash is different of previous hash.
     * this.__altered allows to force the dispatch.
     */
    isAltered: function() {
        if(this.__altered) {
            return true;
        }
        this.__altered = false;

        return (e107History.__currentHash != e107History.__previousHash);
    },

    /**
     * setHashOnIframe()
     *
     * @use  For IE compatibility
     * @desc Set hash value on iframe
     */
    setHashOnIframe: function(hash) {
        try {
            var doc = e107History.__iframe.contentWindow.document;
            doc.open();
            doc.write('<html><body id="history">' + hash + '</body></html>');
            doc.close();
        } catch(e) {}
    },

    /**
     * getHashOnIframe()
     *
     * @use  For IE compatibility
     * @desc Get hash value on iframe
     */
    getHashOnIframe: function() {
        var doc = this.__iframe.contentWindow.document;
        if (doc && doc.body.id == 'history') {
            return doc.body.innerText;
        } else {
            return this.hash.toQueryString();
        }
    },

    /**
     * setTitle()
     *
     * @desc Set a new title for window
     */
    setTitle: function(title) {
        if(document.title) {
            document.title = title;
        }
    },

    /**
     * getTitle()
     *
     * @desc Return current window title
     */
    getTitle: function() {
        return this.__title;
    }
};

e107History.init();

/**
 * History.Registry
 * Prototype Xtensions http://www.prototypextensions.com/
 *
 * @desc Used to register a callback for a parameter
 */
e107History.Registry =
{
    /**
     * @desc Hash
     */
    hash : new Hash(),

    /**
     * set( string $config )
     *
     * @desc Set new value historyId for parameter config
     */
    set: function(config) {

        if(typeof(config) != 'object') {
            throw('e107History.Registry.set : config must be an javascript object');
        }

        // id
        if(!config.id || !Object.isString(config.id)) {
            throw('e107History.Registry.set : config.id must be an string');
        }

        // onChange
        if(!config.onStateChange || !Object.isFunction(config.onStateChange)) {
            throw('e107History.Registry.set : config.onStateChange '
                + 'must be an javascript callback function');
        }

        // defaultValue
        if(!config.defaultValue || !Object.isString(config.defaultValue)) {
            config.defaultValue = '';
        }

        this.hash.set(config.id, config);
    },

    /**
     * flat version of set method
     *
     * @desc Register callback function for historyId
     */
    register: function(historyId, callback, defval) {
        var config = {
        	id: historyId,
        	onStateChange: callback,
        	defaultValue: defval
        };
        this.set(config);
    },

    /**
     * get( string $id )
     *
     * @desc Get value parameter $id
     */
    get: function(id) {
        return this.hash.get(id);
    },

    /**
     * unset( string $id )
     *
     * @desc Unset parameter $id
     */
    unset: function(id) {
        this.hash.unset(id);
    }
}

/**
 * History.Observer
 * Prototype Xtensions http://www.prototypextensions.com/
 *
 * @desc Used to perform actions defined in the registry,
 * according to the hash of the url.
 */
e107History.Observer = {

    /**
     * @desc Interval delay in seconds
     */
    delay : 0.4,

    /**
     * @desc Interval timer instance
     */
    interval : null,

    /**
     * @desc If interval is started : true, else false
     */
    started : false,

    /**
     * start()
     *
     * @desc Start a interval timer
     */
    start: function() {
        if(this.started) return;
        this.interval = new PeriodicalExecuter(e107History.Observer.dispatch, this.delay);
        this.started = true;
    },

    /**
     * stop()
     *
     * @desc Stop the interval timer
     */
    stop: function() {
        if(!this.started) return;
        this.interval.stop();
        this.started = false;
    },

    /**
     * dispatch()
     *
     * @desc This method is called each time interval,
     * the dispatch of the registry is implemented only if
     * the hash has been amended (optimisiation)
     */
    dispatch: function() {
        // Update the hash
        e107History.update();

        // Dispatch only if location.hash has been altered
        if(e107History.isAltered()) {
        	var oldstate = String(e107History.__previousHash).toQueryParams();
        	//FIXME - possible bugs/performance issues here - investigate further
            e107History.hash.each(function(pair)  {
                var registry = e107History.Registry.get(pair.key);
                //Bugfix - notify callbacks only when required
                if(registry && (e107History.__altered === pair.key || oldstate[pair.key] !== pair.value)) {
                   registry.onStateChange.bind(e107History)( pair.value );
                }
            });
        }
    }
};

// -------------------------------------------------------------------

/*
 * AJAX related
 */
var e107Ajax = {};

/**
 * Ajax.History
 * Prototype Xtensions http://www.prototypextensions.com/
 *
 * @desc Provides core methods to easily manage browsing history
 * with Ajax.History.Request / Updater.
 */
e107Ajax.History = {

    /**
     * @desc Allowed Ajax.History prefix (for validation)
     */
    types : ['Request', 'Updater'],

    cacheString: 'ajax-history-',

    /**
     * observe( string type, string id, string url, object options )
     *
     * @desc This method helps manage the browsing history
     */
    observe: function(type, id, url, options) {

        var getter         = e107.getModCache(this.cacheString + id);
        var currentVersion = 0;
        var output         = false;

        // Type validation
        if(this.types.indexOf(type) == -1) {
            throw('e107Ajax.History.observer: type ' + type + ' is invalid !');
        }

        // Registry management
        if(!getter) {
            currentVersion = (options.history.state) ? options.history.state : 0;
            var hash = new Hash();
            hash.set(currentVersion, options);
            e107.setModCache(this.cacheString + id, hash);
            //console.log(id,  e107.getModCache(this.cacheString + id));
        } else {
            currentVersion = (options.history.state)
                ? options.history.state : this.getCurrentVersion(id);
            getter.set(currentVersion, options);
        }

        // add handler on registry
        this.addCallback(type, id);

        return currentVersion;
    },

    /**
     * addCallback( string type, string id )
     *
     * @desc This method adds a state for request on History.Registry
     */
    addCallback: function(type, id) {

        e107History.Observer.start();
        // Set history altered state to true : force dispatch
        e107History.__altered = id;

        // Return void if registry is already set
        if(!Object.isUndefined(e107History.Registry.get(id))) return;

        // Add this id to history registry
        var cacheS = this.cacheString + id;
        e107History.Registry.set({
            id: id,
            onStateChange: function(state) {
                var options = e107.getModCache(cacheS).get(state.toString());
                var request = null;

                if(Object.isUndefined(options)) return;

                if(options.history.cache == true && options.history.__request) {
                    new Ajax.Cache(options.history.__request);
                } else {

                	//make a request
                    if(type == 'Request') {
                        request = new Ajax.Request(options.history.__url, options);
                    } else if(type == 'Updater') {
                        request = new Ajax.Updater(options.container, options.history.__url, options);
                    }
                    options.history.__request = request;
                }

                e107History.__altered = false;

                if (Object.isFunction(options.history.onStateChange)) {
                    options.history.onStateChange(state);
                }
            }
        });
    },

    /**
     * getCurrentVersion( string id )
     *
     * @desc This method returns the current state in history
     * (if the state is not defined)
     */
    getCurrentVersion: function(id) {
        var getter = e107.getModCache(this.cacheString + id);
        return Object.isUndefined(getter) ? 0 : getter.keys().length;
    }
};

e107Ajax.ObjectMap = {
    id              : null,    // set custom history value for this instance
    state           : false,   // set custom state value for this instance
    cache           : false,   // enable/disable history cache
    onStateChange   : null,    // handler called on history change
    __url           : null,
    __request       : null
};

/**
 * Ajax.Cache
 * Prototype Xtensions http://www.prototypextensions.com/
 *
 * @desc Ajax.Cache can "simulate" an Ajax request from an
 * Ajax.Request/Updater made beforehand.
 */
Ajax.Cache = Class.create(Ajax.Base, {
    _complete: false,
    initialize: function($super, request) {
        $super(request.options);
        request._complete = false;
        this.transport = request.transport;
        this.request(request.url);
        return this;
    },

    request: function(url) {
        this.url = url;
        this.method = this.options.method;
        var params = Object.clone(this.options.parameters);

        try {
            var response = new Ajax.Response(this);

            if (this.options.onCreate) this.options.onCreate(response);
            Ajax.Responders.dispatch('onCreate', this, response);

            if (this.options.asynchronous) this.respondToReadyState.bind(this).defer(1);

            this.onStateChange();
        }
        catch (e) {
            this.dispatchException(e);
        }
    }
});

Object.extend(Ajax.Cache.prototype, {
    respondToReadyState : Ajax.Request.prototype.respondToReadyState,
    onStateChange       : Ajax.Request.prototype.onStateChange,
    success             : Ajax.Request.prototype.getStatus,
    getStatus           : Ajax.Request.prototype.getStatus,
    isSameOrigin        : Ajax.Request.prototype.isSameOrigin,
    getHeader           : Ajax.Request.prototype.getHeader,
    evalResponse        : Ajax.Request.prototype.evalResponse,
    dispatchException   : Ajax.Request.prototype.dispatchException
});

/**
 * Ajax.Request Extended
 * Prototype Xtensions http://www.prototypextensions.com/
 *
 * @desc Just a small change: now Ajax.Request return self scope.
 * It is required by Ajax.Cache
 */
Ajax.Request = Class.create(Ajax.Request, {
    initialize: function($super, url, options) {
        $super(url, options);
        return this;
    }
});

Ajax.Request.Events =
  ['Uninitialized', 'Loading', 'Loaded', 'Interactive', 'Complete'];

/**
 * Ajax.Updater Extended
 * Prototype Xtensions http://www.prototypextensions.com/
 *
 * @desc Just a small change: now Ajax.Updater return self scope
 * It is required by Ajax.Cache
 */
Ajax.Updater = Class.create(Ajax.Updater, {
    initialize: function($super, container, url, options) {
        $super(container, url, options);
        return this;
    }
});



//Register Ajax Responder
(function() {

		var e_responder = {
				onCreate: function(request) {
					if(request.options['updateElement']) {
						request.options.element = request.options.updateElement;
						e107Event.trigger('ajax_update_before', request.options, request.options.updateElement);
					}
					if(request.options['overlayPage']){
						e107Event.trigger('ajax_loading_start', request.options, request.options.overlayPage);
					} else if(request.options['overlayElement']) {
						e107Event.trigger('ajax_loading_element_start', request.options, request.options.overlayElement);
					}
				},

				onComplete: function(request) {
					/*Ajax.activeRequestCount == 0 && */
					if(request.options['overlayPage']) {
						e107Event.trigger('ajax_loading_end', request.options, request.options.overlayPage);
					} else if(request.options['overlayElement']) {
						e107Event.trigger('ajax_loading_element_end', request.options, request.options.overlayElement);
					}

					if(request.options['updateElement']) {
						request.options.element = request.options.updateElement;
						e107Event.trigger('ajax_update_after', request.options, request.options.updateElement);
					}
				},

				onException: function(request, e) {
					//TODO handle exceptions
					//alert('e107Ajax Exception: ' + e);
					if(window.console) window.console.log('e107Ajax Exception: ' + e);
				}
		}

		Ajax.Responders.register(e_responder);
})();

/**
 * e107AjaxAbstract
 */
var e107AjaxAbstract = Class.create ({
	_processResponse: function(transport) {
		if(null !== transport.responseXML) {
			this._handleXMLResponse(transport.responseXML);
		} else if(null !== transport.responseJSON) {
			this._handleJSONResponse(transport.responseJSON);
		} else {
			this._handleTextResponse(transport.responseText);
		}

	},

	_handleXMLResponse: function (response) {
		var xfields = $A(response.getElementsByTagName('e107response')[0].childNodes);
		var parsed = {};
		xfields.each( function(el) {
			if (el.nodeType == 1 && el.nodeName == 'e107action' && el.getAttribute('name') && el.childNodes) {

				var action = el.getAttribute('name'), items = el.childNodes;
				if(!varsettrue(parsed[action])) {
					parsed[action] = {};
				}

				for(var i=0, len=items.length; i<len; i++) {
					var field = items[i];

					if(field.nodeType!=1)
						continue;

					if(field.getAttribute('name')) {
						var type = field.getAttribute('type'), //not used yet
							name = field.getAttribute('name'),
							eldata = field.firstChild
							val = eldata ? eldata.data : '';
						if(parsed[action][name] && Object.isArray(parsed[action][name]))
							parsed[action][name].push(val);
						else if(parsed[action][name] && Object.isString(parsed[action][name]))
							parsed[action][name] = [parsed[action][name], val];
						else
							parsed[action][name]= val;
					}

				}
			}
		}.bind(this));
		this._handleResponse(parsed);
	},

	_handleJSONResponse: function (response) {
		this._handleResponse(response);
	},

	_handleTextResponse: function (response) {
		this._handleResponse({ 'auto': response} );
	},

	_handleResponse: function(parsed) {

		Object.keys(parsed).each(function(method) {
			try{
				this['_processResponse' + ('-' + method).camelize()](parsed[method]);
			} catch(e) {
				//
			}
		}.bind(this));

	},

	_processResponseAuto: function(response) {
		//find by keys as IDs & update
		Object.keys(response).each(function(key) {
			this._updateElement(key, response[key]);
		}.bind(this));
	},

	/**
	 * Reset checked property of form elements by selector name attribute (checkbox, radio)
	 */
	_processResponseResetChecked: function(response) {
		Object.keys(response).each(function(key) {
			var checked = parseInt(response[key]) ? true : false;
			$$('input[name^=' + key + ']').each( function(felement) {
				var itype = String(felement.type);
				if(itype && 'checkbox radio'.include(itype.toLowerCase()))
					felement.checked = checked;
			});
		}.bind(this));
	},

	/**
	 * Invoke methods/set properties on element or element collections by id
	 *
	 * Examples:
	 * {'show': 'id1,id2,id3'} -> show elements with id id1,id2 and id3
	 * {'writeAttribute,rel,external': 'id1,id2,id3'} -> invoke writeAttribute('rel', 'external') on elements with id id1,id2 and id3
	 * {'disabled,true': 'button-el,other-button-el'} -> set disabled property of elements with id button-el,other-button-el to true
	 *
	 */
	_processResponseElementInvokeById: function(response) {
		//response.key is comma separated list representing method -> args to be invoked on every element
		Object.keys(response).each(function(key) {
			var tmp = $A(key.split(',')),
				method = tmp[0],
				args = tmp.slice(1);

			//search for boolean type
			$A(args).each( function(arg, i) {
				switch(arg) {
					case 'false': args[i] = false; break;
					case 'true': args[i] = true; break;
					case 'null': args[i] = null; break;
				}
			});
			//response.value is comma separated element id list
			$A(response[key].split(',')).each( function(el) {
				el = el ? $(el.strip()) : null;
				if(!el) return;

				if(Object.isFunction(el[method]))
					el[method].apply(el, args);
				else if(typeof el[method] !== 'undefined') {
					//XXX - should we allow adding values to undefined yet properties? At this time not allowed
					el[method] = varset(args[0], null);
				}
			});
		});
	},

	/**
	 * Update element by type
	 */
	_updateElement: function(el, data) {
		el = $(el); if(!el) return;
		var type = el.nodeName.toLowerCase(), itype = el.type;
        if(type == 'input' || type == 'textarea') {
        	if(itype) itype = itype.toLowerCase();
        	switch (itype) {
        		case 'checkbox':
        		case 'radio':
        			el.checked = (el.value == data);
        			break;
        		default:
        			el.value = data.unescapeHTML(); //browsers doesn't unescape entities on JS update, why?!
        			break;
        	}

        } else if(type == 'select') {
            if(el.options) {
                var opt = $A(el.options).find( function(op, ind) {
                    return op.value == data;
                });
                if(opt)
                	el.selectedIndex = opt.index;
            }
        } else if(type == 'img') {
        	el.writeAttribute('src', data).show(); //show if hidden
        }else if(el.nodeType == 1) {
        	el.update(data);
        }
	}
});

// -------------------------------------------------------------------

/**
 * e107Ajax.Request
 * Prototype Xtensions http://www.prototypextensions.com/
 *
 * @desc @desc e107Ajax.Update wrapper, used to execute an Ajax.Request by integrating
 * the management of browsing history
 */
e107Ajax.Request = Class.create({
    initialize: function(url, options) {

        this.options = {};
        Object.extend(this.options, options || {});
        if(!this.options['parameters'])
        	this.options['parameters'] = { 'ajax_used': 1 }
        else if(!this.options.parameters['ajax_used'])
        	this.options['parameters']['ajax_used'] = 1;

        // only if required
        if(this.options.history) {
            var tmpOpt = Object.clone(e107Ajax.ObjectMap);
            Object.extend(tmpOpt, this.options.history);
            this.options.history = tmpOpt;
            this.options.history.__url = url;

            // History id
            if(Object.isUndefined(options.history.id))
                throw('e107Ajax.Request error : you must define historyId');

            var id = this.options.history.id;

            // Enable history observer
            var version = e107Ajax.History.observe('Request', id, url, this.options);

            // Set current version value for container
            e107History.set(id, version);

        } else {
            return new Ajax.Request(url, this.options);
        }
    }
});

/**
 * e107Ajax.Updater
 *
 * @desc e107Ajax.Updater wrapper, used to execute an Ajax.Updater by integrating
 * the management of browsing history
 */
e107Ajax.Updater = Class.create({
    initialize: function(container, url, options) {

        this.options = {};

        Object.extend(this.options, options || {});
        if(!this.options['parameters'])
        	this.options['parameters'] = { 'ajax_used': 1 }
        else if(!this.options.parameters['ajax_used'])
        	this.options['parameters']['ajax_used'] = 1;

		//required for ajax_update event trigger
		this.options.updateElement = container;

        // only if required
        if(this.options.history) {
            var tmpOpt = Object.clone(e107Ajax.ObjectMap);
            Object.extend(tmpOpt, this.options.history);
            this.options.history = tmpOpt;
            this.options.history.__url = url;

            // History id
            if(Object.isUndefined(options.history.id)) {
                var id = (Object.isString(container)) ? container : container.identify();
                this.options.history.id = id;
            } else {
                var id = this.options.history.id;
            }
            // Add container to this.options
            this.options.container = container;

            // Enable history observer
            var version = e107Ajax.History.observe('Updater', id, url, this.options);

            // Set current version value for container
            e107History.set(id, version);

        } else {
            return new Ajax.Updater(container, url, this.options);
        }
    }
});

Object.extend(e107Ajax, {

	/**
	 * Ajax Submit Form method
	 *
	 * @descr e107 analog to Prototpye native Form.request method
	 */
	submitForm: function(form, container, options, handler) {
		var parm = $(form).serialize(true),
			opt = Object.clone(options || {}),
			url = !handler ? $(form).readAttribute('action') : String(handler).parsePath();

		if(!opt.parameters) opt.parameters = {};
		Object.extend(opt.parameters, parm || {});
		if ($(form).hasAttribute('method') && !opt.method) opt.method = $(form).method;
		if(!opt.method) opt.method = 'post';

		if(container)
			return new e107Ajax.Updater(container, url, opt);

		return new e107Ajax.Request(url, opt);
	},

	/**
	 * Ajax Submit Form method and auto-replace SC method
	 */
	submitFormSC: function(form, sc, scfile, container) {
		var handler = ('#{e_FILE}e_ajax.php'), parm = { 'ajax_sc': sc, 'ajax_scfile': scfile };
		return this.submitForm(form, varsettrue(container, sc), { parameters: parm, overlayElement: varsettrue(container, sc) }, handler);
	},

	toggleUpdate: function(toggle, container, url, cacheid, options) {
		container = $(container);
		toggle = $(toggle);
		opt = Object.clone(options || {});
		opt.method = 'post';

		if(!toggle) return;

		if(!toggle.visible())
		{

			if(cacheid && $(cacheid)) return toggle.fxToggle();

			opt.onComplete = function() { toggle.fxToggle() };
			if(url.startsWith('sc:'))
			{
				return e107Ajax.scUpdate(url.substring(3), container, opt);
			}
			return new e107Ajax.Updater(container, url, opt);
		}

		return toggle.fxToggle();
	},

	scUpdate: function(sc, container, options) {
		var handler = ('#{e_FILE}e_ajax.php').parsePath(), parm = { 'ajax_sc': sc };
		opt = Object.clone(options || {});
		opt.method = 'post';
		if(!opt.parameters) opt.parameters = {};
		Object.extend(opt.parameters, parm || {});
		return new e107Ajax.Updater(container, handler, opt);
	}
});

/**
 * e107Ajax.fillForm
 *
 * @desc
 */
e107Ajax.fillForm = Class.create(e107AjaxAbstract, {

	initialize: function(form, overlay_dest, options) {
		//TODO - options
		this.options = Object.extend({
			start: true
		}, options || {});

		this.form = $(form);
		if(!this.form) return;

		if(this.options['start'])
			this.start(overlay_dest);
	},

	start: function(overlay_dest) {
		e107Event.trigger("ajax_fillForm_start", {form: this.form});
		var destEl = $(overlay_dest) || false;
		var C = this;

		//Ajax history is NOT supported (and shouldn't be)
		var options = {
			overlayPage: destEl,

			history: false,

			onSuccess: function(transport) {
				try {
					this._processResponse(transport);
				} catch(e) {
					var err_obj = { message: 'Callback Error!', extended: e, code: -1 }
					e107Event.trigger("ajax_fillForm_error", {form: this.form, error: err_obj});
				}
			}.bind(C),

			onFailure: function(transport) {
				//We don't use transport.statusText only because of Safari!!!
				var err = transport.getHeader('e107ErrorMessage') || '';
				//TODO - move error messages to the ajax responder object, convert it to an 'error' object (message, extended, code)
				//Add Ajax option e.g. printErrors (true|false)
				var err_obj = { message: err, extended: transport.responseText, code: transport.status }
				e107Event.trigger("ajax_fillForm_error", {form: this.form, error: err_obj });
			}.bind(C)
		}
		Object.extend(options, this.options.request || {}); //update - allow passing request options

		this.form.submitForm(null, options, this.options.handler);
	},

	_processResponseFillForm: function(response) {
		if(!response || !this.form) return;
		var C = this, left_response = Object.clone(response);
		this.form.getElements().each(function(el) {
			var elid = el.identify(), elname = el.readAttribute('name'), data, elnameid = String(elname).gsub(/[\[\]\_]/, '-');

			if(isset(response[elname])) {
				data = response[elname];
				if(left_response[elname]) delete left_response[elname];
			} else if(isset(response[elnameid])) {
				data = response[elnameid];
				if(left_response[elnameid]) delete left_response[elnameid];
			} else if(isset(response[elid])) {
				data = response[elid];
				if(left_response[elid]) delete left_response[elid];
			} else {
				return;
			}
            this._updateElement(el, data);
		}.bind(C));

		if(left_response) { //update non-form elements (by id)
			Object.keys(left_response).each( function(el) {
				this._updateElement(el, left_response[el]);
			}.bind(C));
		}

		e107Event.trigger("ajax_fillForm_success", {form: this.form});
	}

});

Element.addMethods('FORM', {

	submitForm: e107Ajax.submitForm.bind(e107Ajax),

	submitFormSC: e107Ajax.submitFormSC.bind(e107Ajax),

	fillForm: function(form, overlay_element, options) {
		new e107Ajax.fillForm(form, overlay_element, options);
	}
});

// -------------------------------------------------------------------

//DEPRECATED!!! Use e107Ajax.submitFormSC() || form.submitFormSC() instead
function replaceSC(sc, form, container, scfile) {
		$(form).submitFormSC(sc, scfile, container);
}

//DEPRECATED!!! Use e107Ajax.submitForm() || form.submitForm() instead
function sendInfo(handler, container, form) {
	if(form)
		$(form).submitForm(container, null, handler);
	else
		new e107Ajax.Updater(container, handler);
}

// -------------------------------------------------------------------

/*
 * Core Auto-load
 */
$w('autoExternalLinks autoNoHistory autoHide toggleObserver toggleManyObserver scrollToObserver executeAutoSubmit').each( function(f) {
	// e107.runOnLoad(e107Helper[f], null, true);
});
