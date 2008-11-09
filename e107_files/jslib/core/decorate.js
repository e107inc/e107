/* 
 * DECORATE HTML ELEMENTS
 * Inspired by Magento' decorate JS functions (www.magentocommerce.com) 
*/

var e107Decorate = {
	
	/**
	 * Decorate table rows and cells, tbody etc
	 * @see eDecorate()
	 */
	 table: function(table) {
	    var table = $(table);
	    if (!table) return;
	    
	    //default options
	    this._options = {
	        'tbody': false,
	        'tbody tr': 'odd,even,first,last',
	        'thead tr': 'first,last',
	        'tfoot tr': 'first,last',
	        'tr td': 'last'
	    };
	    
	    // overload options
	    Object.extend(this._options, (arguments[1] || {}));
	    
	    // decorate
	    if (this._options['tbody']) {
	        this._decorate(table.select('tbody'), this._options['tbody']);
	    }
	    if (this._options['tbody_tr']) {
	        this._decorate(table.select('tbody tr'), this._options['tbody tr']);
	    }
	    if (this._options['thead_tr']) {
	        this._decorate(table.select('thead tr'), this._options['thead tr']);
	    }
	    if (this._options['tfoot_tr']) {
	        this._decorate(table.select('tfoot tr'), this._options['tfoot tr']);
	    }
	    if (this._options['tr_td']) {
	        table.select('tr').each( function(tr) {
	            this._decorate(tr.select('td'), this._options['tr td']);
	        }.bind(this));
	    }
	},
	
	/**
	 * Decorate list (ul)
	 * Default decorate CSS classes for list items are "odd", "even" and "last" 
	 * 
	 * Examples: 
	 *  eDecorateList('mylist'); //default decorate options over element with id 'mylist'
	 *  eDecorateList('mylist', 'odd,even'); //decorate options odd and even only over element with id 'mylist'
	 * 
	 * @param list - id/DOM object of list element (ul) to be decorated
	 * [@param options] - string|array decorate options - @see eDecorate()
	 * [@param recursive] - boolean decorate all childs if present
	 */
	list: function(list) {
	    list = $(list);
	    if (list) {
	        if (typeof(arguments[2]) == 'undefined') {
	            var items = list.select('li')
	        } else {
	            var items = list.childElements();
	        }
	        this._decorate(items, (arguments[1] || 'odd,even,last'));
	    }
	},
	
	/**
	 * Set "odd", "even" and "last" CSS classes for list items
	 * 
	 * Examples: 
	 *  eDecorateDataList('mydatalist'); //default decorate options over element with id 'mydatalist'
	 *  eDecorateDataList('mydatalist', 'odd,even'); //decorate options odd and even for dt elements, default for dd elements
	 * 
	 * [@param dt_options] - string|array dt element decorate options - @see eDecorate()
	 * [@param dd_options] - string|array dd element decorate options - @see eDecorate()
	 */
	dataList: function(list) {
	    list = $(list);
	    if (list) {
	        this._decorate(list.select('dt'), (arguments[1] || 'odd,even,last'));
	        this._decorate(list.select('dd'), (arguments[2] || 'odd,even,last'));
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
	    var decorateAllParams = ['odd', 'even', 'first', 'last'];
	    this.decorateParams = [];
	    this.params = {};
	    
	    if (!elements.length)  return;
	    
	    if(!varset(arguments[1])) {
	        this.decorateParams = decorateAllParams;
	    } else if(typeof(arguments[1]) == 'string') {
	        this.decorateParams = arguments[1].replace(/[\s]/, '').split(',');
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
	    //elements.select(_eDecorateIsEven).invoke('addClassName', 'even');
	    var selections = elements.partition(this._isEven);
	    // decorate even
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
     * @see eDecorate()
     */
    _isEven: function(dummy, i) {
        return ((i+1) % 2 == 0);
    }
}