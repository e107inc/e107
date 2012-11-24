/*
 * e107 website system
 * 
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://gnu.org).
 * 
 * DECORATE HTML LIST ELEMENTS
 * Inspired by Magento' decorate JS functions (www.magentocommerce.com) 
 * 
 * $Source: /cvs_backup/e107_0.8/e107_files/jslib/core/decorate.js,v $
 * $Revision$
 * $Date$
 * $Author$
 * 
*/

e107Utils.Decorate = {
	
	/**
	 * Decorate table rows and cells, tbody etc
	 * @see e107Utils.Decorate._decorate()
	 */
	 table: function(table) {
	    var table = $(table);
	    if (!table) return;
	    
	    //default options
	    this._options = {
	        'tbody': false,
	        'tbody_tr': 'odd even first last',
	        'thead_tr': 'first last',
	        'tfoot_tr': 'first last',
	        'tr_td': false
	    };
	    
	    // overload options
	    Object.extend(this._options, (arguments[1] || {}));
    	
	    // decorate
	    if (this._options['tbody']) {
	        this._decorate(table.select('tbody'), this._options['tbody']);
	    }
	    if (this._options['tbody_tr']) {
	        this._decorate(table.select('tbody tr:not(.no-decorate)'), this._options['tbody_tr']);
	    }
	    if (this._options['thead_tr']) {
	        this._decorate(table.select('thead tr:not(.no-decorate)'), this._options['thead_tr']);
	    }
	    if (this._options['tfoot_tr']) {
	        this._decorate(table.select('tfoot tr:not(.no-decorate)'), this._options['tfoot_tr']);
	    }
	    if (this._options['tr_td']) {
	        table.select('tr:not(.no-decorate)').each( function(tr) {
	            this._decorate(tr.select('td:not(.no-decorate)'), this._options['tr_td']);
	        }.bind(this));
	    }
	},
	
	/**
	 * Decorate list (ul)
	 * Default decorate CSS classes for list items are "odd", "even" and "last" 
	 * 
	 * Examples: 
	 *  e107Utils.Decorate.list('mylist'); //default decorate options over element with id 'mylist'
	 *  e107Utils.Decorate.list('mylist', 'odd even'); //decorate options odd and even only over element with id 'mylist'
	 * 
	 * @param list - id/DOM object of list element (ul) to be decorated
	 * [@param options] - string|array decorate options - @see e107Utils.Decorate._decorate()
	 * [@param recursive] - boolean decorate all childs if present
	 */
	list: function(list) {
	    list = $(list);
	    if (list) {
	        if (!varset(arguments[2])) {
	            var items = list.select('li:not(.no-decorate)');
	        } else {
	            var items = list.childElements();
	        }
	        this._decorate(items, (arguments[1] || 'odd even last'));
	    }
	},
	
	/**
	 * Set "odd", "even" and "last" CSS classes for list items
	 * 
	 * Examples: 
	 *  e107Utils.Decorate.dataList('mydatalist'); //default decorate options over element with id 'mydatalist'
	 *  e107Utils.Decorate.dataList('mydatalist', 'odd even'); //decorate options odd and even for dt elements, default for dd elements
	 * 
	 * [@param dt_options] - string|array dt element decorate options - @see e107Utils.Decorate._decorate()
	 * [@param dd_options] - string|array dd element decorate options - @see e107Utils.Decorate._decorate()
	 */
	dataList: function(list) {
	    list = $(list);
	    if (list) {
	        this._decorate(list.select('dt:not(.no-decorate)'), (arguments[1] || 'odd even last'));
	        this._decorate(list.select('dd:not(.no-decorate)'), (arguments[2] || 'odd even last'));
	    }
	},
	
	/**
	 * Add classes to specified elements.
	 * Supported classes are: 'odd', 'even', 'first', 'last'
	 *
	 * @param elements - array of elements to be decorated
	 * [@param decorateParams] - array of classes to be set. If omitted or empty, all available will be used
	 */
	_decorate: function(elements) {
	    var decorateAllParams = $w('odd even first last');
	    this.decorateParams = $A();
	    this.params = {};
	    
	    if (!elements.length)  return;
	    
	    if(!varset(arguments[1])) {
	        this.decorateParams = decorateAllParams;
	    } else if(typeof(arguments[1]) == 'string') {
	        this.decorateParams = $w(arguments[1]);
	    } else {
	        this.decorateParams = arguments[1];
	    }
	    
	    decorateAllParams.each( function(v) {
	        this.params[v] = this.decorateParams.include(v);
	    }.bind(this));

	    // decorate first
	    if(this.params.first) {
	        Element.addClassName(elements[0], 'first');
	    }
	    // decorate last
	    if(this.params.last) {
	        Element.addClassName(elements[elements.length-1], 'last');
	    }
	    
	    if(!this.params.even && !this.params.odd) {
	        return;
	    }

	    var selections = elements.partition(this._isEven);

	    if(this.params.even) {
	        selections[0].invoke('addClassName', 'even');
	    }
	    if(this.params.odd) {
	        selections[1].invoke('addClassName', 'odd');
	    }
	},
	
    /**
     * Select/Reject/Partition callback function
     * 
     * @see e107Utils.Decorate._decorate()
     */
    _isEven: function(dummy, i) {
        return ((i+1) % 2 == 0);
    }
}