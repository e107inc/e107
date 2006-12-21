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
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Japanese/admin/help/article.php,v $
|     $Revision: 1.1 $
|     $Date: 2006-12-21 15:43:45 $
|     $Author: e107coders $
+-----Translation Updated by: difda on 2005/12/16----------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$text = "このページから単ページや複数ページの記事を追加することができます.<br />
 複数ページの記事にするためには セパレータとして各々のページの間に [newpage] を入れてください, 例えば <br /><code>テスト1 [newpage] テスト２</code><br /> で'テスト1'が1ページと'テスト２'が2ページの２ページの記事が出来上がります.
<br /><br />
記事でHTMLタグを使いたい時,それを含む領域の前後に[html] [/html]を入れてください.例えば, 記事の中で '&lt;table>&lt;tr>&lt;td>Hello &lt;/td>&lt;/tr>&lt;/table>' のような文を入力したなら, a table would be shown containing the word hello. If you entered '[html]&lt;table>&lt;tr>&lt;td>Hello &lt;/td>&lt;/tr>&lt;/table>[/html]' the code as you entered it would be shown and not the table that the code generates.";
$ns -> tablerender("記事 Help", $text);
?>