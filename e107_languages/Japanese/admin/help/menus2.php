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
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Japanese/admin/help/menus2.php,v $
|     $Revision: 1.1 $
|     $Date: 2006-12-21 15:43:46 $
|     $Author: e107coders $
+-----Translation Updated by: difda on 2006/01/21---------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$caption = "メニュー Help";
$text .= "ここからメニューアイテムを選び,どこに表示するかをアレンジできます. 
それらの配置を満足するまで,メニューのあちこちに動かすのに,矢印を使ってください.<br />
スクリーンの中央のメニュー項目で処理します,表示したい場所を選択することによってこれらを起動させることができます.
";

$ns -> tablerender("メニュー Help", $text);
?>