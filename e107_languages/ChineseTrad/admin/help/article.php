<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     咎teve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_langpacks/e107_languages/ChineseTrad/admin/help/article.php,v $
|     $Revision: 1.1 $
|     $Date: 2007-09-09 07:18:42 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$text = "您可以增加一個或是多頁面的文章.<br />
 如果是多頁面的文章可以使用 [newpage], 例如： <br /><code>Test1 [newpage] Test2</code><br /> 將會產身兩個頁面，一個是 'Test1' 於頁面 1 和 'Test2' 於頁面 2.
<br /><br />
如果您的文章要顯示 HTML 標籤符號, 請使用 [html] [/html]編碼. 例如： 輸入 '&lt;table>&lt;tr>&lt;td>Hello &lt;/td>&lt;/tr>&lt;/table>' 於您的文章,他將會顯示 hello. 如果您輸入 '[html]&lt;table>&lt;tr>&lt;td>Hello &lt;/td>&lt;/tr>&lt;/table>[/html]' 該編碼內的字元將會忠實的表達出來.";
$ns -> tablerender("文章問題", $text);
?>