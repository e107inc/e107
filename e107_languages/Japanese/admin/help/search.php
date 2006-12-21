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
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Japanese/admin/help/search.php,v $
|     $Revision: 1.1 $
|     $Date: 2006-12-21 15:43:46 $
|     $Author: e107coders $
+-----Translation Updated by: difda on 2006/01/18----------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$text = "MySqlサーバーのバージョンがサーポートしているなら,PHPで検索するより、
速く処理いできるMySqlの検索方法に変えることができます. 選択肢を見てください.<br /><br />
あなたのサイトが中国語や日本語のような表意文字言語を含むならば,
PHPによる検索方法を使用し完全一致のスイッチをオフにしてください.
";
$ns -> tablerender("Search Help", $text);
?>