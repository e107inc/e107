bootstrap-suggest
============================
A bootstrap plugin for your mention needs.

![demo](demo.png "demo")

## V2
The version 2 of this plugin now supports bootstrap 4 and `contenteditable` that uses `jquery.caret` (optional).

## Install
Several quick start options are available:

- [download](https://github.com/lodev09/bootstrap-suggest/archive/v2.0.2.zip) latest release
- [npm](https://www.npmjs.com/package/bootstrap-suggest): `npm install --save bootstrap-suggest`
- [bower](https://bower.io): `bower install bootstrap-suggest`

** Make sure to link `bootstrap-suggest.js` and `bootstrap-suggest.css` to your project

## Usage

### Markup
```html
<div class="form-group">
   <label for="comment">start typing with @</label>
   <textarea class="form-control" rows="5" id="comment"></textarea>
</div>
```

### Data
```
var users = [
  {username: 'lodev09', fullname: 'Jovanni Lo'},
  {username: 'foo', fullname: 'Foo User'},
  {username: 'bar', fullname: 'Bar User'},
  {username: 'twbs', fullname: 'Twitter Bootstrap'},
  {username: 'john', fullname: 'John Doe'},
  {username: 'jane', fullname: 'Jane Doe'},
];
```

### Init
```javascript
$('#comment').suggest('@', {
  data: users,
  map: function(user) {
    return {
      value: user.username,
      text: '<strong>'+user.username+'</strong> <small>'+user.fullname+'</small>'
    }
  }
})
```

## API
http://lodev09.github.io/bootstrap-suggest/#api

## Feedback
All bugs, feature requests, pull requests, feedback, etc., are welcome. Visit my site at [www.lodev09.com](http://www.lodev09.com "www.lodev09.com").

[![LICENSE MIT](https://img.shields.io/badge/Mail%20me%20at-lodev09%40gmail.com-green.svg)](mailto:lodev09@gmail.com)

## Credits
&copy; 2018 - Coded by Jovanni Lo / [@lodev09](http://twitter.com/lodev09)

## License

Released under the MIT license. See [LICENSE](LICENSE) file.

[![LICENSE MIT](https://img.shields.io/badge/license-MIT-red.svg)](http://opensource.org/licenses/MIT)
