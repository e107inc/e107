<?php
$text = "Reviews are similar to articles but they will be listed in their own menu item.<br />
 For a multi-page review seperate each page with the text [newpage], ie <br /><code>Test1 [newpage] Test2</code><br /> would create a two page review with 'Test1' on page 1 and 'Test2' on page 2.";
$ns -> tablerender("Review Help", $text);
?>