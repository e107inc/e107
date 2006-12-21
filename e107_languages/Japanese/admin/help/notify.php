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
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Japanese/admin/help/notify.php,v $
|     $Revision: 1.1 $
|     $Date: 2006-12-21 15:43:46 $
|     $Author: e107coders $
+-----Translation Updated by: difda on 2006/01/18---------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$text = "e107イベントが起こるとき,送信通知メールに通知してください.<br /><br />
たとえば、『スパム攻撃のために禁止されるIP』がセットされすると,サイトがスパム攻撃を受けたとき,ユーザークラス『Admin』と全ての管理人へ電子メールを送られます.<br /><br />
また,もう一つの例として,『管理人投稿のニュースアイテム』をユーザークラス『メンバー』にセットすることができます、そして、全てのユーザーはあなたが電子メールでサイトに掲示するニュースアイテムを送られます.<br /><br />
あなたは代替メールアドレスでメール通知を送りたいなら?『Email』オプションにチェックを入れ,テキストフィールドに電子メールアドレスで入れてください.";

$ns -> tablerender("通知 Help", $text);
?>