print_a = function(){
	if(window.console)
		$A(arguments).each( function(a) {console.log(a) });
};

var_dump = print_a;

var e107Debug = {
	init: function() {
		//always on top!
		this.debugE = new Element('div', { 'id': 'e-debug-console', 'style': 'z-index: 9991' } ).update(this._console_header);
		this.cont = new Element('div', { 'id': 'e-debug-console-cont', 'style': 'z-index: 9990' } ).insert(this.debugE); 
		this.input = new Element('input', { 'id': 'e-debug-console-input', 'type': 'text' } );
		this.controlC = new Element('a', { 'id': 'e-debug-console-controls-close', 'href': '#' } ).update(' X');
		this.controlE = new Element('a', { 'id': 'e-debug-console-controls-eval', 'href': '#' } ).update(' Run ');
		//var controlCC =  new Element('div', { 'float': 'right' }).insert(this.controlC);
		this.cont.insert( new Element('div', { 'id': 'e-debug-console-controls' }).update('&gt;&gt; ').insert(this.input).insert(this.controlE).insert(this.controlC) );
		

		this.commands = new Array('');
		this.clen = this.commands.length;
		this.cindex = 0;
		
		var C = this;
		(function() { 
			C.controlC.observe('click', function(e) {
				e.stop();
				C.hide();
			});
			
			C.controlE.observe('click', function(e) {
				e.stop();
				C.evalInput(); C.setFocus();
			});
			C.input.observe('keydown', function(e) { //supported in all modern browsers
		        var keycode = e.keyCode; 
		        var enterKey, escapeKey, keyUp, keyDown;
		        if (e.DOM_VK_RETURN) {  // mozilla
		            enterKey = e.DOM_VK_RETURN;
		            escapeKey = e.DOM_VK_ESCAPE;
		            keyUp = e.DOM_VK_UP;
		            keyDown = e.DOM_VK_DOWN;
		        } else { // ie && friends
		            escapeKey = 27;
		            enterKey = 13;
		            keyUp = 38;
		            keyDown = 40;
		        }
		        switch (keycode) {
		        	case enterKey:
		        		C.evalInput();
		        		break;
		        	case keyUp:
		        		C.chistory(-1);
		        		break;
		        	case keyDown:
		        		C.chistory(1);
		        		break;
		        	case escapeKey:
		        		C.input.value = ''; C.input.blur(); C.input.focus();
		        		break;
		        }

		    });
			//TODO - destroy method, console commands (e.g. \run -help), better key navigation (e.g. Ctrl + Shift + Key)
		}).defer();
		
		document.observe('dom:loaded', function() {
			$(document.body).insert(this.cont.hide());
			if(Prototype.Browser.IE6) this.iecenter();
			else this.center();
		}.bind(this));
		
		this.keyboardNav = this.keyboardNav.bindAsEventListener(this);
		this.re_center = this.center.bindAsEventListener(this);
		this.re_iecenter = this.iecenter.bindAsEventListener(this);
		this.startKeyboardNav();
	},
	
	show: function() {
		if(!this.visible()) {
			this.startPosObserve();
			this._toggle();
		}
	},
	
	hide: function() {
		if(this.visible()) {
			this.stopPosObserve();
			this._toggle();
		}
	},
	
	_toggle: function() {
		var C = this;
		Effect.toggle(this.cont, 'blind', { 
			duration: 0.4,
			beforeStart: this.setFocus.bind(C),
			afterFinish: this.setFocus.bind(C)
		});
		this.cindex = 0; //reset commands index 
	},
	
	visible: function() {
		return this.cont.visible();
	},
	
	center: function() {
		var w = document.viewport.getWidth(), cw = this.cont.getWidth();
		var pos = parseInt(w/2 - cw/2); 
		this.cont.setStyle({ 'left':  pos + 'px'});
		
	},
	
	
	iecenter: function() {
		var offset = document.body.scrollTop;
		var w = document.body.clientWidth;
		if(!this.cd) this.cd = this.cont.getWidth();
		var left = parseInt(w/2 - this.cd/2);
		if(left < 0) { //ie6 - sick of it
			left = 0;
		}
		this.cont.setStyle( { 
			'position': 'absolute', 
			'top': offset + 'px',
			'left': left + 'px'
		});
	},
	
	setFocus: function() {
        	if(this.visible()) { this.input.blur(); this.input.focus(); this.scrollDown(); }
        	else { this.input.value = ''; this.scrollDown(); this.input.blur(); } 
	},
	
	scrollDown: function() {
		this.debugE.scrollTop = this.debugE.scrollHeight;
	},
	
	log: function(d) {
		this.show();
		this.debugE.insert( new Element('div', { 'class': 'console-output' }).update(d) ); //TODO check the type
		this.scrollDown();
	},
	
	syslog: function(msg, error) {
		var logcol = '#333300';
		if(error) logcol = '#cc3300'; 
		this.log('<span style="color: ' + logcol + '">&gt;&gt; ' + msg + '</span>');
	},

	clearLog: function(d) {
		this.debugE.update('');
	},
	
	evalInput: function() {
		var src = this.input.value;
		if(!src.length) return;
		
		this.syslog(src);
		this.input.value = '';
		try {
			var ret = eval.call(window, src);

			if(ret) this.log(ret);
			//setTimeout(src, 0); - Safari only! Not implemented anyway
			this.clen = this.commands.push(src);
			this.cindex = 0;
		} catch(e) {
			this.syslog(e, true);
			this.clen = this.commands.push(src);
			this.cindex = 0;
		} 
		this.setFocus();
	},

	startKeyboardNav: function() {
		document.observe('keydown', this.keyboardNav); 
		return this;
	},
	

	startPosObserve: function() {
    	if(Prototype.Browser.IE6) {
    		Event.observe(window,"resize", this.re_iecenter);
    		Event.observe(window,"scroll", this.re_iecenter);
    		return this;
    	}
    	Event.observe(window,"resize", this.re_center);
    	return this;
	},
	
	stopPosObserve: function() {
		if(Prototype.Browser.IE6) {
			Event.stopObserving(window,"scroll", this.re_iecenter);
			Event.stopObserving(window,"resize", this.re_iecenter);
			return this;
		}
		Event.stopObserving(window,"resize", this.re_center);
		return this;
	},
	 
	keyboardNav: function(event) {
		//TODO - find out what kind of shortcuts are safe to be used (Ctrl + Alt + Shift brings me too much irritation)
        var keycode = event.keyCode;
        var key = String.fromCharCode(keycode).toLowerCase();
		var isShifthPressed = event.shiftKey || (event.keyIdentifier && event.keyIdentifier.toLowerCase() == 'shift'); //ie & friends
		var isCtrlPressed = event.ctrlKey  || (event.keyIdentifier && event.keyIdentifier.toLowerCase() == 'control'); //ie & friends
		var isAltPressed = event.altKey || (event.keyIdentifier && event.keyIdentifier.toLowerCase() == 'alt'); //ie & friends
        if(isShifthPressed && isCtrlPressed && isAltPressed && key.match(/c|l/) /* && event.element() != this.input */ ) {
        	if(this.visible()) this.stopPosObserve()._toggle();
        	else this.startPosObserve()._toggle();
        }
	},
	
	chistory: function(index) {
		var ci = this.clen + this.cindex + index; 
		if(this.commands[ci] || ci === 0 || ci === this.clen) {
			this.input.value = this.commands[ci] || '';
			this.cindex += index;
		}
	},

	_console_header: '<span class="smallblacktext">--- <strong>e107 Debug Console v1.0.0:</strong> session started ---</span><br />'
	
}

e107Debug.init();
echo = function() {
	$A(arguments).each( function(a) { e107Debug.log(a) });
}