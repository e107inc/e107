<?php
$text = "From this page you can either add normal content pages, or articles.<br /><br />
<b>Articles</b><br />
 For a multi-page article seperate each page with the text [newpage], ie <br /><code>Test1 [newline] Test2</code><br /> would create a two page article with 'Test1' on page 1 and 'Test2' on page 2.
";
$ns -> tablerender("Articles Help", $text);
?>