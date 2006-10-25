<?php
/*
+ ----------------------------------------------------------------------------+
|     Russian Language Pack for e107 0.7
|     $Revision: 1.2 $
|     $Date: 2006-10-25 15:23:26 $
|     $Author: yarodin $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

//Not translated yet: obsolete?
$text = "Обзоры похожи на статьи, но  are similar to articles but they will be listed in their own menu item.<br />
 For a multi-page review separate each page with the text [newpage], i.e. <br /><code>Test1 [newpage] Test2</code><br /> would create a two page review with 'Test1' on page 1 and 'Test2' on page 2.";
$ns -> tablerender("Помощь по разделу Обзоры", $text);

?>