/*
 * Prototype UI http://prototype-ui.com/
 * 
 * Group: Drag UI provides Element#enableDrag method that allow elements to fire
 * drag-related events.
 * 
 * Events fired: - drag:started : fired when a drag is started (mousedown then
 * mousemove) - drag:updated : fired when a drag is updated (mousemove) -
 * drag:ended : fired when a drag is ended (mouseup)
 * 
 * Notice it doesn't actually move anything, drag behavior has to be implemented
 * by attaching handlers to drag events.
 * 
 * Drag-related informations: event.memo contains useful information about the
 * drag occuring: - dx : difference between pointer x position when drag started
 * and actual x position - dy : difference between pointer y position when drag
 * started and actual y position - mouseEvent : the original mouse event, useful
 * to know pointer absolute position, or if key were pressed.
 * 
 * Example, with event handling for a specific element: > // Now "resizable"
 * will fire drag-related events > $('resizable').enableDrag(); > > // Let's
 * observe them > $('resizable').observe('drag:started', function(event) { >
 * this._dimensions = this.getDimensions(); > }).observe('drag:updated',
 * function(event) { > var drag = event.memo; > > this.setStyle({ > width:
 * this._dimensions.width + drag.dx + 'px', > height: this._dimensions.height +
 * drag.dy + 'px' > }); > });
 * 
 * Example, with event delegating on the whole document: > // All elements in
 * the having the "draggable" class name will fire drag events. >
 * $$('.draggable').invoke('enableDrag'); > > document.observe('drag:started',
 * function(event) { > UI.logger.info('trying to drag ' + event.element().id); >
 * }):
 */
Element.addMethods( {
	enableDrag : function(element) {
		return $(element).writeAttribute('draggable');
	},

	disableDrag : function(element) {
		return $(element).writeAttribute('draggable', null);
	},

	isDraggable : function(element) {
		return $(element).hasAttribute('draggable');
	}
});

(function() {
	var initPointer, draggedElement;

	document.observe('mousedown', function(event) { 
		if (draggedElement = findDraggable(event.element())) {
			// prevent default browser action to avoid selecting text for
			// instance
			event.preventDefault();
			initPointer = event.pointer();
			
			document.observe('mousemove', startDrag);
			document.observe('mouseup', cancelDrag);
		}
	});

	function findDraggable(element) {
		while (element && element !== document) {
			if (element.hasAttribute('draggable'))
				return element;
			element = $(element.parentNode);
		}
	}

	function startDrag(event) {
		document.stopObserving('mousemove', startDrag).stopObserving('mouseup', cancelDrag).observe('mousemove', drag).observe('mouseup', endDrag);
		fire('drag:started', event);
	}

	function cancelDrag(event) {
		document.stopObserving('mousemove', startDrag).stopObserving('mouseup', cancelDrag);
	}

	function drag(event) {
		fire('drag:updated', event);
	}

	function endDrag(event) {
		document.stopObserving('mousemove', drag).stopObserving('mouseup', endDrag);

		fire('drag:ended', event);
	}

	function fire(eventName, event) {
		var pointer = event.pointer();

		draggedElement.fire(eventName, {
			dx : pointer.x - initPointer.x,
			dy : pointer.y - initPointer.y,
			mouseEvent : event
		});
	}
})();