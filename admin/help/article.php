<?php
$text = "From this page you can either add normal content pages, or articles.<br /><br />
<b>Articles</b><br />
 For a multi-page article seperate each page with the text [newpage], ie <br /><code>Test1 [newline] Test2</code><br /> would create a two page article with 'Test1' on page 1 and 'Test2' on page 2.
<br /><br />
<b>Normal content pages</b><br />
You can add a normal page to your site using this feature. In the heading box, enter what you want the link to the page to be called, and tick the Normal Content box. For example, to create a tutorial page explaining how to make themes for e107, enter 'theme tutorial' in the heading box, tick the normal content box, and enter the text to be displayed on the tutorial page.If you've chosen the Normal Content with link option a link will be created for you in the main navigation menu.<br />If you want your content page to have a caption, enter it in the subheading box.
<br /><br />
<b>Reviews</b><br />
Reviews are like single page articles but will be displayed in their own menu box.";

$ns -> tablerender("Articles/Content Help", $text);
?>