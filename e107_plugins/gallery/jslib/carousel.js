/*
Copyright (c) 2009 Victor Stanciu - http://www.victorstanciu.ro

Permission is hereby granted, free of charge, to any person
obtaining a copy of this software and associated documentation
files (the "Software"), to deal in the Software without
restriction, including without limitation the rights to use,
copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following
conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.
*/

Carousel = Class.create(Abstract, {
	initialize: function (scroller, slides, controls, options) {
		this.scrolling	= false;
		this.scroller	= $(scroller);
		this.slides		= slides;
		this.controls	= controls;
		

		this.options    = Object.extend({
            duration:           1,
            auto:               false,
            frequency:          3,
            visibleSlides:      1,
            controlClassName:   'carousel-control',
            jumperClassName:    'carousel-jumper',
            disabledClassName:  'carousel-disabled',
            selectedClassName:  'carousel-selected',
            circular:           false,
            wheel:              true,
            effect:             'scroll',
            transition:         'sinoidal'
			
        }, options || {});
        
		
		
        if (this.options.effect == 'fade') {
            this.options.circular = true;
        }
		
		//scroll-circular fix by SecretR @ free-source.net
        if (this.options.effect == 'scroll' && this.options.circular && this.slides.length > 1) {
			var fixel = Element.clone(this.slides[0], true);
			fixel.identify();
			this.slides[this.slides.length] = fixel;
			Element.insert(this.slides[0].up(), {
				bottom: fixel
			});
        }

		this.slides.each(function(slide, index) {
			slide._index = index;
        });

		if (this.controls) {
            this.controls.invoke('observe', 'click', this.click.bind(this));
        }
        
        if (this.options.wheel) {            
            this.scroller.observe('mousewheel', this.wheel.bindAsEventListener(this)).observe('DOMMouseScroll', this.wheel.bindAsEventListener(this));;
        }

        if (this.options.auto) {
            this.start();
			// this.slides.invoke('observe', 'mouseover', this.pause.bind(this));
			// this.slides.invoke('observe', 'mouseout', this.resume.bind(this));
        }

		if (this.options.initial) {
			
			var initialIndex = this.slides.indexOf($(this.options.initial));
			
			if (initialIndex > (this.options.visibleSlides - 1) && this.options.visibleSlides > 1) {               
				if (initialIndex > this.slides.length - (this.options.visibleSlides + 1)) {
					initialIndex = this.slides.length - this.options.visibleSlides;
				}
			}
			
            this.moveTo(this.slides[initialIndex]);
		}
		
		if (this.options.container) {
			this.container = $(this.options.container);
			this.jumpers = this.container.select('a.'+this.options.jumperClassName);
		} else {
			this.jumpers = $$('a.'+ this.options.jumperClassName);
		}
		
		//this.current = this.slides[0];
	},

	click: function (event) {
		this.stop();

		var element = event.findElement('a');

		if (!element.hasClassName(this.options.disabledClassName)) {
			if (element.hasClassName(this.options.controlClassName)) {
				eval("this." + element.rel + "()");
            } else if (element.hasClassName(this.options.jumperClassName)) {
                this.moveTo(element.rel);
            }
        }

		this.deactivateControls();

		event.stop();
    },

	moveTo: function (element) {
		if (this.slides.length > 1) { 
			if (this.options.selectedClassName && this.jumpers) {
				this.jumpers.each(function(jump,b){
						if (jump.hasClassName(this.options.selectedClassName)) {
							jump.removeClassName(this.options.selectedClassName);
						}

						if (jump.rel == element || jump.rel == element.id ) {
							jump.addClassName(this.options.selectedClassName);
						}
						
				}.bind(this));
			}
			
			if (this.options.beforeMove && (typeof this.options.beforeMove == 'function')) {
				this.options.beforeMove();
			}
	
			this.previous = this.current ? this.current : this.slides[0];
			this.current  = $(element);
	
			var scrollerOffset = this.scroller.cumulativeOffset();
			var elementOffset  = this.current.cumulativeOffset();
	
			if (this.scrolling) {
				this.scrolling.cancel();
			}
	
			switch (this.options.effect) {
				case 'fade':               
					this.scrolling = new Effect.Opacity(this.scroller, {
						from:   1.0,
						to:     0,
						duration: this.options.duration,
						afterFinish: (function () {
							this.scroller.scrollLeft = elementOffset[0] - scrollerOffset[0];
							this.scroller.scrollTop  = elementOffset[1] - scrollerOffset[1];

							new Effect.Opacity(this.scroller, {
								from: 0,
								to: 1.0,
								duration: this.options.duration,
								afterFinish: (function () {
									if (this.controls) {
										this.activateControls();
									}
									if (this.options.afterMove && (typeof this.options.afterMove == 'function')) {
										this.options.afterMove();
									}
								}).bind(this)
							});
						}
					).bind(this)});
				break;
				case 'scroll':
				default:
					var transition;
					switch (this.options.transition) {
						case 'spring':
							transition = Effect.Transitions.spring;
							break;
						case 'sinoidal':
						default:
							transition = Effect.Transitions.sinoidal;
							break;
					}
	
					this.scrolling = new Effect.SmoothScroll(this.scroller, {
						duration: this.options.duration,
						x: (elementOffset[0] - scrollerOffset[0]),
						y: (elementOffset[1] - scrollerOffset[1]),
						transition: transition,
						afterFinish: (function () {
												
							if (this.controls) {
								this.activateControls();
							}
							if (this.options.afterMove && (typeof this.options.afterMove == 'function')) {
								this.options.afterMove();
							}                        
							this.scrolling = false;
						}).bind(this)});
				break;
			}
	
			return false;
		}
	},

	prev: function () {
		if (this.current) {
			var currentIndex = this.current._index;
			var prevIndex = (currentIndex == 0) ? (this.options.circular ? this.slides.length - 1 : 0) : currentIndex - 1;
        } else {
            var prevIndex = (this.options.circular ? this.slides.length - 1 : 0);
        }

		if (prevIndex == (this.slides.length - 1) && this.options.circular && this.options.effect != 'fade') {
			this.scroller.scrollLeft =  (this.slides.length - 1) * this.slides.first().getWidth();
			this.scroller.scrollTop =  (this.slides.length - 1) * this.slides.first().getHeight();
			prevIndex = this.slides.length - 2;
        }

		this.moveTo(this.slides[prevIndex]);
	},

	next: function () {
		if (this.current) {
			var currentIndex = this.current._index;
			var nextIndex = (this.slides.length - 1 == currentIndex) ? (this.options.circular ? 0 : currentIndex) : currentIndex + 1;
        } else {
            var nextIndex = 1;
        }
		
		if (nextIndex == 0 && this.options.circular && this.options.effect != 'fade') {
			this.scroller.scrollLeft = 0;
			this.scroller.scrollTop  = 0;
			nextIndex = 1;
        }

		if (nextIndex > this.slides.length - (this.options.visibleSlides + 1)) {
			nextIndex = this.slides.length - this.options.visibleSlides;
		}		

		this.moveTo(this.slides[nextIndex]);
	},

	first: function () {
		this.moveTo(this.slides[0]);
    },

	last: function () {
		this.moveTo(this.slides[this.slides.length - 1]);
    },

	toggle: function () {
		if (this.previous) {
			this.moveTo(this.slides[this.previous._index]);
        } else {
            return false;
        }
    },

	stop: function () {
		if (this.timer) {
			clearTimeout(this.timer);
		}
	},

	start: function () { 
        this.periodicallyUpdate();
    },

	pause: function (event) {
		this.stop();
		this.activateControls();
		this.scroller.addClassName('test-over')
    },

	resume: function (event) {
		if (event) {
			var related = event.relatedTarget || event.toElement;
			if (!related || (!this.slides.include(related) && !this.slides.any(function (slide) { return related.descendantOf(slide); }))) {
				this.start();
				this.scroller.removeClassName('test-over')
            }
        } else {
            this.start();
        }
    },

	periodicallyUpdate: function () {
		if (this.timer != null) {
			clearTimeout(this.timer);
			this.next();
        }
		this.timer = setTimeout(this.periodicallyUpdate.bind(this), this.options.frequency * 1000);
    },
	
    wheel: function (event) {
    /*
        event.cancelBubble = true;
        event.stop();
        
		var delta = 0;
		if (!event) {
            event = window.event;
        }
		if (event.wheelDelta) {
			delta = event.wheelDelta / 120; 
		} else if (event.detail) { 
            delta = -event.detail / 3;	
        }        
       
        if (!this.scrolling) {
            this.deactivateControls();
            if (delta > 0) {
                this.prev();
            } else {
                this.next();
            }            
        }
        
		return Math.round(delta); //Safari Round
*/
    },
	deactivateControls: function () {
		this.controls.invoke('addClassName', this.options.disabledClassName);
    },

	activateControls: function () {
		this.controls.invoke('removeClassName', this.options.disabledClassName);
    }
});


Effect.SmoothScroll = Class.create();
Object.extend(Object.extend(Effect.SmoothScroll.prototype, Effect.Base.prototype), {
	initialize: function (element) {
		this.element = $(element);
		var options = Object.extend({ x: 0, y: 0, mode: 'absolute' } , arguments[1] || {});
		this.start(options);
    },

	setup: function () {
		if (this.options.continuous && !this.element._ext) {
			this.element.cleanWhitespace();
			this.element._ext = true;
			this.element.appendChild(this.element.firstChild);
        }

		this.originalLeft = this.element.scrollLeft;
		this.originalTop  = this.element.scrollTop;

		if (this.options.mode == 'absolute') {
			this.options.x -= this.originalLeft;
			this.options.y -= this.originalTop;
        }
    },

	update: function (position) {
		this.element.scrollLeft = this.options.x * position + this.originalLeft;
		this.element.scrollTop  = this.options.y * position + this.originalTop;
    }
});

var FSfader = Class.create({
	initialize: function(wrapper,fader,items,options){
		this.wrapper = $(wrapper);
		this.fader = $(fader);
		this.items = $$(items);
		
		this.options =  Object.extend({
			layout: 'vertical',
			itemstyle: 'top',
			toShow: 4,
			jumperClass: 'jump',
			transition: Effect.Transitions.EaseTo
		}, arguments[3] || {});
		
		this.controls = $$( '#'+wrapper + ' a.'+ this.options.jumperClass);
		this.controls.invoke('observe', 'click', this.click.bind(this));
		
		this.current = 0;
		this.p = new Effect.Parallel([]);
		if (!this.options.itemWidth) { this.options.itemWidth = this.items[0].getWidth(); }
			
		this.space = Math.round((this.fader.getWidth() - this.options.itemWidth*this.options.toShow)/(this.options.toShow+1));
		
		var a=0, b=0;
		this.arrGroup = new Array;
		
		this.items.each(function(item,i){
			item.hide();
			
			if (a >= this.options.toShow) {
				a=0;
				b++;
			}
			
			if (typeof this.arrGroup[b] == 'undefined') this.arrGroup[b] = new Array;
			this.arrGroup[b].push(item);
			a++;
		}.bind(this));
		this.showGroup(this.arrGroup[0]);
	},
	
	showGroup: function(group) {
		group.each(function(item,i){
			item.setStyle({"left": (this.options.itemWidth*i + this.space*(i+1)) + "px"});
		}.bind(this));
		new Effect.multiple(group,Effect.Appear,{ speed: 0.1, duration: 0.4});
		
	},
	
	hideGroup: function(group) {
		this.p.cancel();
		group.each(function(item,i){
			var eff = new Effect.Fade(item, {duration: 0.3, from: 1, to: 0, delay: 0.1*i, sync: true});
			this.p.effects.push(eff);
		}.bind(this));
		this.p.start({
					 afterFinish: function () { 
						this.showGroup(this.arrGroup[this.toShow]);
						this.current=this.toShow;
					}.bind(this)
		});
	},
	
	
	next: function() {
		if (!this.toShow || this.toShow != this.current+1) {
			if (this.current != this.arrGroup.length-1 ) {
				this.toShow = this.current + 1;
			} else {
				this.toShow = 0;
			}
			this.hideGroup(this.arrGroup[this.current])
		}
	},
	
	prev: function() {
		if (!this.toShow || this.toShow != this.current-1) {
			if (this.current != 0 ) {
				this.toShow = this.current - 1;
			} else {
				this.toShow = this.arrGroup.length-1;
			}
			this.hideGroup(this.arrGroup[this.current])
		}
	},
	
	click: function (event) {
		event.stop();
		var element = event.findElement('a');
		if (!this.running) {
			eval("this." + element.rel + "()");
			}		
	}
});
