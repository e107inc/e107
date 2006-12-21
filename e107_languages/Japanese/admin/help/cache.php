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
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Japanese/admin/help/cache.php,v $
|     $Revision: 1.1 $
|     $Date: 2006-12-21 15:43:45 $
|     $Author: e107coders $
+-----Translation Updated by: difda on 2005/12/16----------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$caption = "キャッシュ";
$text = "キャッシュにチェックを入れておくと,それは非常にサイトでの速度を改善して,sqlデータベースへの呼び出しの量を最小にします.<br /><br /><b>重要！自分でテーマを作っているならば,あなたが作る少しの変化も反映されないので,キャッシングをオフにしてください.</b>";
$ns -> tablerender($caption, $text);
?>