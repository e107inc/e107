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
Please use full paths to any links even if they are local or they may not be parsed correctly.
<br /><br />
<b>Status</b>
<br />
If you click the Disabled button the news item will not be displayed on your front page at all.
<br /><br />
<b>Activation</b>
<br />
If you set a start and/or end date your news item will only be displayed between these dates.
";
$ns -> tablerender($caption, $text);
?>