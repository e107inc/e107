<?php
$caption = "Newspost Help";
$text = "<b>General</b><br />
Body will be displayed on the main page, extended will be readable by clicking a 'Read More' link. Source and URL is where you got the story from.
<br />
<br />
<b>Shortcuts</b><br />
You can use these shortcuts instead of typing the whole tag out, on posting the news item the shortcuts will be converted to xhtml compliant code.
<br /><br />
<b>Links</b>
<br />
Remember if adding a local link (ie a link to another page on your site), you must add ../ to move up a directory or the link will point to your admin directory, ie<br />
<code>&lt;a href=\"blah.php\"&gt;Foo&lt;/a&gt;</code>
<br />should be entered as<br />
<code>&lt;a href=\"../blah.php\"&gt;Foo&lt;/a&gt;</code>
";
$ns -> tablerender($caption, $text);
?>