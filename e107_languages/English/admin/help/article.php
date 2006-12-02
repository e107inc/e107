<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_languages/English/admin/help/article.php,v $
|     $Revision: 1.1.1.1 $
|     $Date: 2006-12-02 04:34:42 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$text = "From this page you can add single or multi-page articles.<br />
 For a multi-page article separate each page with the text [newpage], i.e. <br /><code>Test1 [newpage] Test2</code><br /> would create a two page article with 'Test1' on page 1 and 'Test2' on page 2.
<br /><br />
If your article contains HTML tags that you wish to preserve, enclose the code with [html] [/html]. For example, if you entered the text '&lt;table>&lt;tr>&lt;td>Hello &lt;/td>&lt;/tr>&lt;/table>' in your article, a table would be shown containing the word hello. If you entered '[html]&lt;table>&lt;tr>&lt;td>Hello &lt;/td>&lt;/tr>&lt;/table>[/html]' the code as you entered it would be shown and not the table that the code generates.";
$ns -> tablerender("Article Help", $text);
?>