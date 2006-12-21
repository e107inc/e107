<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ｩSteve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Japanese/admin/help/review.php,v $
|     $Revision: 1.1 $
|     $Date: 2006-12-21 15:43:46 $
|     $Author: e107coders $
+-----Translation Updated by: difda on 2006/01/18---------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$text = "レビューは記事と類似しています、しかし、それらそれら自身のメニューアイテムにリストされます。<br />
 複数ページにわたるときは各々のページの境に[newpage]を埋め込んでください, 例えば <br /><code>Test1 [newpage] Test2</code><br /> のようにすると1ページ目が'Test1',2ページ目が'Test2'として作成されます.";
$ns -> tablerender("レビュー Help", $text);
?>