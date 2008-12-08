/*
 * e107 website system
 * 
 * Copyright (c) 2001-2008 e107 Developers (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://gnu.org).
 * 
 * e107 Tooltip Widget
 * Create static/ajax tooltips (unobtrusive Javascript)
 * 
 * $Source: /cvs_backup/e107_0.8/e107_files/jslib/core/tooltip.js,v $
 * $Revision: 1.1 $
 * $Date: 2008-12-08 23:40:18 $
 * $Author: secretr $
 * 
*/

/**
 * Global prefs
 */
e107Base.setPrefs('core-tooltip', {
	backgroundColor: '', // background color (used if set)
	borderColor: '', // Default border color (used if set)
	textColor: '', // Default text color (used if set)
	textShadowColor: '', // Default text shadow color (used if set)
	align: "left", // left (default) | right
	maxWidth: 250,	// Max tooltip width
	delay: 250, // Default delay before tooltip appears in ms
	mouseFollow: true, // Tooltips follows the mouse moving
	opacity: .75, // Default tooltips opacity
	appearDuration: .25, // Default appear duration in sec
	hideDuration: .25 // Default disappear duration in sec
});

/**
 * e107Widget.Tooltip Class
 * 
 * Inspired by CoolTip by Andrey Okonetchnikov 
 * (http://www.wildbit.com/labs/cooltips)
 */
e107Widgets.Tooltip = Class.create(e107WidgetAbstract, {
	
	Version: '1.0',
	
	initialize: function(element, options) {
		this.events = new e107EventManager(this);
		this.ttinit = false;
		this.initMod('core-tooltip', optHandlers).__initTabData(container);
		this.attachListeners();
	},	
	
	show: function(e) {

	},
	
	hide: function(e) {

	},
	
	update: function(e){

	},
	
	create: function() {
		
	},
		
	attachListeners: function() {
		
	},
	
	_clearTimeout: function(timer) {
		clearTimeout(timer);
		clearInterval(timer);
		return null;
	}
});