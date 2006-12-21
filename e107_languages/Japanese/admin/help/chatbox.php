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
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Japanese/admin/help/chatbox.php,v $
|     $Revision: 1.1 $
|     $Date: 2006-12-21 15:43:45 $
|     $Author: e107coders $
+-----Translation Updated by: difda on 2005/12/16----------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$text = "ここで伝言板の基本設定をおこないます.<br />リンク置き換えにチェックを入れていると,入力されたリンクがテキストボックスに登録された文字列と入れ替え,長いリンクが引き起こす表示上の問題を解決します.ワードラップはここで指定される長さより長い文字列を自動的に折り返します.";

$ns -> tablerender("伝言板", $text);
?>