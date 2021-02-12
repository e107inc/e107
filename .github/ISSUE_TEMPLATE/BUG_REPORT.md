---
name: Bug report
about: Create a report for a problem that shouldn't be happening
title: ''
labels: 'type: bug'
assignees: ''

---

## Bug Description
A clear and concise description of what the bug is.

## How to Reproduce
Steps to reproduce the behavior:
1. Go to '...'
2. Click on '....'
3. Scroll down to '....'
4. See error

## Expected Behavior
A clear and concise description of what you expected to happen.

## Screenshots
If applicable, add screenshots to help explain your problem.

## Server Information

### PHP Operating System

```
Replace this code block with the "System" value
found towards the top of /e107_admin/phpinfo.php

e.g.
Linux localhost 2.4.21-0.13mdk #1 Fri Mar 14 15:08:06 EST 2003 i686
```

### PHP Version

```
Replace this code block with the PHP version shown
at the top of /e107_admin/phpinfo.php

e.g.
PHP Version 7.3.13
```

### PHP Modules

1. Go to /e107_admin/phpinfo.php
2. Change the page to "view source" mode by one of these methods:
   - Press [Ctrl]+[u]
   - Press [Option]+[Command]+[u]
   - Add `view-source:` at the beginning of the address bar
3. Copy all the source code
4. Go to https://sed.js.org/
5. Paste everything into the "STDIN:" box
6. In the "Command line (--help):" box, write:

       -rn 's|<h2><a id="module_[^"]+">([^<]+)</a></h2>|\1|p'

7. Copy everything from the "STDOUT | STDERR:" box.
8. Paste what you copied here, replacing this whole "PHP Modules" section.

### Client Information

1. Go to https://duckduckgo.com/?q=my+user+agent
2. Towards the top, copy your user agent from the line that begins with "Your user agent".
3. Replace this section with the clipboard contents.

   Example: `Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:71.0) Gecko/20100101 Firefox/71.0`

## Additional Information
Add any other context about the problem here.
