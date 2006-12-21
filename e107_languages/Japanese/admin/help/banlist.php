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
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Japanese/admin/help/banlist.php,v $
|     $Revision: 1.1 $
|     $Date: 2006-12-21 15:43:45 $
|     $Author: e107coders $
+-----Translation Updated by: difda on 2005/12/16----------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$caption = "サイトからのユーザー締出し";
$text = "この画面でサイトからユーザーを締出すことが出来ます.<br />
きっちりとしたIPアドレスかワイルドカードを使って締出したいIPアドレスの範囲を指定するかかどちらを入力してください. また、ユーザーがサイトでメンバーとして登録するのを止めるために.電子メールアドレスを入力することができます.<br /><br />
<b>IPアドレスによる締出し:</b><br />
IPアドレス123.123.123.123を登録すると,そのアドレスでユーザーがあなたのサイトを訪問するのを締出します.<br />
IPアドレス123.123.123.*を登録すると,そのIP範囲で訪ねてきた訪問者を締出します.<br /><br />
<b>メールアドレスによる締出し</b><br />
電子メールアドレスfoo@bar.comを登録すると,その電子メールアドレスを使っている誰もをサイトのメンバー登録をさせません.<br />
メールアドレス*@bar.comを登録すると,そのメールアドレスのドメインを使っている誰もをサイトのメンバー登録をさせません.";
$ns -> tablerender($caption, $text);
?>