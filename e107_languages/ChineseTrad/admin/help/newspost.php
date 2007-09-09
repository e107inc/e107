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
|     $Source: /cvs_backup/e107_langpacks/e107_languages/ChineseTrad/admin/help/newspost.php,v $
|     $Revision: 1.1 $
|     $Date: 2007-09-09 07:18:48 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$caption = "新聞發表問題";
$text = "<b>一般狀況</b><br />
內文將會顯示於主要的頁面,延伸閱讀將需要點選 '閱讀更多'才會顯示.
<br />
<br />
<b>僅顯示標題</b>
<br />
開啟此像將會顯示新聞標題於網頁上, 需點選後才可以瀏覽全部文章.
<br /><br />
<b>日期設定</b>
<br />
如果您設定開始或是結束日期於您的新聞文章中，則此文章將會顯示於指定的日期中.
";
$ns -> tablerender($caption, $text);
?>