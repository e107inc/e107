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
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Japanese/admin/help/download.php,v $
|     $Revision: 1.1 $
|     $Date: 2006-12-21 15:43:45 $
|     $Author: e107coders $
+-----Translation Updated by: difda on 2005/12/16----------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$text = "ファイルを".e_FILE."downloads フォルダに,画像を".e_FILE."downloadimages フォルダに,サムネイルを".e_FILE."downloadthumbs フォルダにアップロードしてください .
<br /><br />
ダウンロード使うには,最初に上位カテゴリを作り,その上位カテゴリーの下でカテゴリーを作ってください.それで,ダウンロードを利用できるようにすることができます.";
$ns -> tablerender("ダウンロード Help", $text);
?>