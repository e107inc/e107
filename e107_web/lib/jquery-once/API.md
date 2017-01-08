## Functions

<dl>
<dt><a href="#once">once([id])</a> ⇒</dt>
<dd><p>Filter elements that have yet to be processed by the given data ID.</p>
</dd>
<dt><a href="#removeOnce">removeOnce([id])</a> ⇒</dt>
<dd><p>Removes the once data from elements, based on the given ID.</p>
</dd>
<dt><a href="#findOnce">findOnce([id])</a> ⇒</dt>
<dd><p>Filters elements that have already been processed once.</p>
</dd>
</dl>

<a name="once"></a>

## once([id]) ⇒
Filter elements that have yet to be processed by the given data ID.

**Kind**: global function  
**Returns**: jQuery collection of elements that have now run once by
  the given ID.  
**this**: <code>jQuery</code>  
**Access:** public  
**See**

- removeOnce
- findOnce


| Param | Type | Default | Description |
| --- | --- | --- | --- |
| [id] | <code>string</code> | <code>&quot;once&quot;</code> | The data ID used to determine whether the given elements have already   been processed or not. Defaults to `'once'`. |

**Example**  
``` javascript
// The following will change the color of each paragraph to red, just once
// for the 'changecolor' key.
$('p').once('changecolor').css('color', 'red');

// .once() will return a set of elements that yet to have the once ID
// associated with them. You can return to the original collection set by
// using .end().
$('p')
  .once('changecolorblue')
    .css('color', 'blue')
  .end()
  .css('color', 'red');

// To execute a function on the once set, you can use jQuery's each().
$('div.calendar').once().each(function () {
  // Since there is no once ID provided here, the key will be 'once'.
});
```
<a name="removeOnce"></a>

## removeOnce([id]) ⇒
Removes the once data from elements, based on the given ID.

**Kind**: global function  
**Returns**: jQuery collection of elements that were acted upon to remove their
   once data.  
**this**: <code>jQuery</code>  
**Access:** public  
**See**: once  

| Param | Type | Default | Description |
| --- | --- | --- | --- |
| [id] | <code>string</code> | <code>&quot;once&quot;</code> | A string representing the name of the data ID which should be used when   filtering the elements. This only filters elements that have already been   processed by the once function. The ID should be the same ID that was   originally passed to the once() function. Defaults to `'once'`. |

**Example**  
``` javascript
// Remove once data with the 'changecolor' ID. The result set is the
// elements that had their once data removed.
$('p').removeOnce('changecolor').css('color', '');

// Any jQuery function can be performed on the result set.
$('div.calendar').removeOnce().each(function () {
  // Remove the calendar behavior.
});
```
<a name="findOnce"></a>

## findOnce([id]) ⇒
Filters elements that have already been processed once.

**Kind**: global function  
**Returns**: jQuery collection of elements that have been run once.  
**this**: <code>jQuery</code>  
**Access:** public  
**See**: once  

| Param | Type | Default | Description |
| --- | --- | --- | --- |
| [id] | <code>string</code> | <code>&quot;once&quot;</code> | A string representing the name of the data id which should be used when   filtering the elements. This only filters elements that have already   been processed by the once function. The id should be the same id that   was originally passed to the once() function. Defaults to 'once'. |

**Example**  
``` javascript
// Find all elements that have been changecolor'ed once.
$('p').findOnce('changecolor').each(function () {
  // This function is called for all elements that has already once'd.
});

// Find all elements that have been acted on with the default 'once' key.
$('p').findOnce().each(function () {
  // This function is called for all elements that have been acted on with
  // a 'once' action.
});
```
