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
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Japanese/admin/help/ugflag.php,v $
|     $Revision: 1.1 $
|     $Date: 2006-12-21 15:43:46 $
|     $Author: e107coders $
+-----Translation Updated by: difda on 2006/01/18---------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$text = "e107をアップグレードしているか,しばらくサイトを閉鎖する必要のあるとき,
メンテナンスのチェックボックスにチェックを入れると訪問者は閉鎖理由の説明をしているページにリダイレクトされます.閉鎖終了時はチェックボックスのチェックを外してください,標準に戻ります.";

$ns -> tablerender("メンテナンス", $text);
?>