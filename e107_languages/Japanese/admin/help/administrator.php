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
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Japanese/admin/help/administrator.php,v $
|     $Revision: 1.1 $
|     $Date: 2006-12-21 15:43:45 $
|     $Author: e107coders $
+-----Translation Updated by: difda on 2006/01/12----------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$caption = "サイト管理人 Help";
$text = "サイト管理人の基本設定を編集や削除するのにこのページを使ってください.管理人には,チェックされた機能にのみアクセスする許可があるます.
新しい管理人をつくるためには、ユーザー設定ページへ行って,既存のユーザーのオプションで管理権限の取得を選んでください.";
$ns -> tablerender($caption, $text);
?>