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
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Japanese/admin/help/content.php,v $
|     $Revision: 1.1 $
|     $Date: 2006-12-21 15:43:45 $
|     $Author: e107coders $
+-----Translation Updated by: difda on 2005/12/21----------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$text = "この機能を使って通常のページを追加できます. 新しいページへのリンクは、メインサイトナビゲーションボックスに作られます. たとえば、リンク名『テスト』で新しいページを作成すると、『テスト』と呼ばれるリンクは新しいページを送信した後にリンクボックスに現れます.<br />
あなたがあなたの内容ページがキャプションを持っていることを望むならば、ペイジHeadingボックスにそれを入れてください.";
$ns -> tablerender("コンテンツ Help", $text);
?>