<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ・teve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Japanese/admin/help/newsfeed.php,v $
|     $Revision: 1.1 $
|     $Date: 2006-12-21 15:43:46 $
|     $Author: e107coders $
+-----Translation Updated by: difda on 2006/01/18---------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$text = "あなたは,他のサイトのバックエンドRSSニュースフィードを取り戻すことができ、解析することができ、ここからあなた自身のサイトでそれらを示すことができます.<br />バックエンド のURLをフルパスで入力して下さい(例えば http://e107.org/news.xmlのように). あなたがデフォルト1が好きでないならば、あなたは経路をイメージに加えることができます、あるいは、それは定められません。 サイトがたとえば下がるならば、あなたはバックエンドを起動させることができて、処理することができます。 <br /><br />あなたのサイトで見出しを見るために、headlines_menuがあなたのメニューページから起動することを確認してください。 ";

$ns -> tablerender("ヘッドライン", $text);
?>