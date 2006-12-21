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
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Japanese/admin/help/news_category.php,v $
|     $Revision: 1.1 $
|     $Date: 2006-12-21 15:43:46 $
|     $Author: e107coders $
+-----Translation Updated by: difda on 2006/01/19---------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$text = "異なるカテゴリーにニュースアイテムを分けることができて,訪問者がそれらのカテゴリーのニュースアイテムだけを表示するのを許します. <br /><br /> ".e_THEME."-yourtheme-/images/ or themes/shared/newsicons/のどちらかのニュースアイコンイメージをアップロードしてください.";
$ns -> tablerender("ニュースカテゴリー Help", $text);
?>