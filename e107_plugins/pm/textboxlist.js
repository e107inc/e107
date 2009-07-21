/*
  Proto!MultiSelect 0.2
  - Prototype version required: 6.0
  
  Credits:
  - Idea: Facebook + Apple Mail
  - Caret position method: Diego Perini <http://javascript.nwbox.com/cursor_position/cursor.js>
  - Guillermo Rauch: Original MooTools script
  - Ran Grushkowsky/InteRiders Inc. : Porting into Prototype and further development
  
  Changelog:
  - 0.1: translation of MooTools script
  - 0.2: renamed from Proto!TextboxList to Proto!MultiSelect, added new features/bug fixes
        added feature: support to fetch list on-the-fly using AJAX    Credit: Cheeseroll
        added feature: support for value/caption
        added feature: maximum results to display, when greater displays a scrollbar   Credit: Marcel
        added feature: filter by the beginning of word only or everywhere in the word   Credit: Kiliman
        added feature: shows hand cursor when going over options
        bug fix: the click event stopped working
        bug fix: the cursor does not 'travel' when going up/down the list   Credit: Marcel
*/

/* Copyright: InteRiders <http://interiders.com/> - Distributed under MIT - Keep this message! */

var ResizableTextbox = Class.create({
  
  options: $H({
    min: 5,
    max: 500,
    step: 7
  }),
  
  initialize: function(element, options) {
    var that = this;
    this.options.update(options);
    this.el = $(element);
    this.width = this.el.offsetWidth;
    this.el.observe(
      'keyup', function() {
        var newsize = that.options.get('step') * $F(this).length;
        if(newsize <= that.options.get('min')) newsize = that.width;
        if(! ($F(this).length == this.retrieveData('rt-value') || newsize <= that.options.min || newsize >= that.options.max))
          this.setStyle({'width': newsize});
      }).observe('keydown', function() {
        this.cacheData('rt-value', $F(this).length);
      });
  }
});

var TextboxList = Class.create({ 
  
  options: $H({/*
    onFocus: $empty,
    onBlur: $empty,
    onInputFocus: $empty,
    onInputBlur: $empty,
    onBoxFocus: $empty,
    onBoxBlur: $empty,
    onBoxDispose: $empty,*/
    resizable: {},
    className: 'bit',
    separator: '###',
    extrainputs: true,
    startinput: true,
    hideempty: true,
    fetchFile: undefined,
    results: 10,
    wordMatch: false
  }),
  
  initialize: function(element, options) {
    this.options.update(options);
    this.element = $(element).hide();    
    this.bits = new Hash();
    this.events = new Hash();
    this.count = 0;
    this.current = false;
    this.maininput = this.createInput({'class': 'maininput'});
    this.holder = new Element('ul', {
      'class': 'holder'
    }).insert(this.maininput);
    this.element.insert({'before':this.holder});
    this.holder.observe('click', function(event){
          event.stop();
          if(this.maininput != this.current) this.focus(this.maininput);     
    }.bind(this));
    this.makeResizable(this.maininput);
    this.setEvents();
  },
  
  setEvents: function() {
    document.observe(Prototype.Browser.IE ? 'keydown' : 'keypress', function(e) {      
      if(! this.current) return;
      if(this.current.retrieveData('type') == 'box' && e.keyCode == Event.KEY_BACKSPACE) e.stop();
    }.bind(this));      
         
    document.observe(
      'keyup', function(e) {
        e.stop();
        if(! this.current) return;
        switch(e.keyCode){
          case Event.KEY_LEFT: return this.move('left');
          case Event.KEY_RIGHT: return this.move('right');
          case Event.KEY_DELETE:
          case Event.KEY_BACKSPACE: return this.moveDispose();
        }
      }.bind(this)).observe(  
      'click', function() { document.fire('blur'); }.bindAsEventListener(this)
    );
  },
  
  update: function() {
    this.element.value = this.bits.values().join(this.options.get('separator'));
    return this;
  },
  
  add: function(text, html) {
    var id = this.options.get('className') + '-' + this.count++;
    var el = this.createBox($pick(html, text), {'id': id});
    (this.current || this.maininput).insert({'before':el});
    el.observe('click', function(e) {
      e.stop();
      this.focus(el);
    }.bind(this));
    this.bits.set(id, text.value);    
    if(this.options.get('extrainputs') && (this.options.get('startinput') || el.previous())) this.addSmallInput(el,'before');
    return el;
  },
  
  addSmallInput: function(el, where) {
    var input = this.createInput({'class': 'smallinput'});
    el.insert({}[where] = input);
    input.cacheData('small', true);
    this.makeResizable(input);
    if(this.options.get('hideempty')) input.hide();
    return input;
  },
  
  dispose: function(el) {
    this.bits.unset(el.id);
    if(el.previous() && el.previous().retrieveData('small')) el.previous().remove();
    if(this.current == el) this.focus(el.next());
    if(el.retrieveData('type') == 'box') el.onBoxDispose(this);
    el.remove();    
    return this;
  },
  
  focus: function(el, nofocus) {
    if(! this.current) el.fire('focus');
    else if(this.current == el) return this;
    this.blur();
    el.addClassName(this.options.get('className') + '-' + el.retrieveData('type') + '-focus');
    if(el.retrieveData('small')) el.setStyle({'display': 'block'});
    if(el.retrieveData('type') == 'input') {
      el.onInputFocus(this);      
      if(! nofocus) this.callEvent(el.retrieveData('input'), 'focus');
    }
    else el.fire('onBoxFocus');
    this.current = el;    
    return this;
  },
  
  blur: function(noblur) {
    if(! this.current) return this;
    if(this.current.retrieveData('type') == 'input') {
      var input = this.current.retrieveData('input');
      if(! noblur) this.callEvent(input, 'blur');   
      input.onInputBlur(this);
    }
    else this.current.fire('onBoxBlur');
    if(this.current.retrieveData('small') && ! input.get('value') && this.options.get('hideempty')) 
      this.current.hide();
    this.current.removeClassName(this.options.get('className') + '-' + this.current.retrieveData('type') + '-focus');
    this.current = false;
    return this;
  },
  
  createBox: function(text, options) {
    return new Element('li', options).addClassName(this.options.get('className') + '-box').update(text.caption).cacheData('type', 'box');
  },
  
  createInput: function(options) {
    var li = new Element('li', {'class': this.options.get('className') + '-input'});
    var el = new Element('input', Object.extend(options,{'type': 'text'}));
    el.observe('click', function(e) { e.stop(); }).observe('focus', function(e) { if(! this.isSelfEvent('focus')) this.focus(li, true); }.bind(this)).observe('blur', function() { if(! this.isSelfEvent('blur')) this.blur(true); }.bind(this)).observe('keydown', function(e) { this.cacheData('lastvalue', this.value).cacheData('lastcaret', this.getCaretPosition()); });
    var tmp = li.cacheData('type', 'input').cacheData('input', el).insert(el);
    return tmp;
  },
  
  callEvent: function(el, type) {
    this.events.set(type, el);
    el[type]();
  },
  
  isSelfEvent: function(type) {
    return (this.events.get(type)) ? !! this.events.unset(type) : false;
  },
  
  makeResizable: function(li) {
    var el = li.retrieveData('input');
    el.cacheData('resizable', new ResizableTextbox(el, Object.extend(this.options.get('resizable'),{min: el.offsetWidth, max: (this.element.getWidth()?this.element.getWidth():0)})));
    return this;
  },
  
  checkInput: function() {
    var input = this.current.retrieveData('input');
    return (! input.retrieveData('lastvalue') || (input.getCaretPosition() === 0 && input.retrieveData('lastcaret') === 0));
  },
  
  move: function(direction) {
    var el = this.current[(direction == 'left' ? 'previous' : 'next')]();
    if(el && (! this.current.retrieveData('input') || ((this.checkInput() || direction == 'right')))) this.focus(el);
    return this;
  },
  
  moveDispose: function() {
    if(this.current.retrieveData('type') == 'box') return this.dispose(this.current);
    if(this.checkInput() && this.bits.keys().length && this.current.previous()) return this.focus(this.current.previous());
  }
  
});

//helper functions 
Element.addMethods({
  getCaretPosition: function() {
    if (this.createTextRange) {
      var r = document.selection.createRange().duplicate();
        r.moveEnd('character', this.value.length);
        if (r.text === '') return this.value.length;
        return this.value.lastIndexOf(r.text);
    } else return this.selectionStart;
  },
  cacheData: function(element, key, value) { 
    if (Object.isUndefined(this[$(element).identify()]) || !Object.isHash(this[$(element).identify()]))
        this[$(element).identify()] = $H();
    this[$(element).identify()].set(key,value);
    return element;
  },
  retrieveData: function(element,key) {
    return this[$(element).identify()].get(key);
  }  
});

function $pick(){for(var B=0,A=arguments.length;B<A;B++){if(!Object.isUndefined(arguments[B])){return arguments[B];}}return null;}