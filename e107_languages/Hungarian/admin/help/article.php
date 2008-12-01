<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ŠSteve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Hungarian/admin/help/article.php,v $
|     $Revision: 1.8 $
|     $Date: 2008-12-01 16:42:39 $
|     $Author: e107hun-lac $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$text = "Itt egy/több oldalas cikkeket írhatsz.<br />
  Az oldalakat a [newpage] kóddal tudod elválasztani, Például <br /><code>Test1 [newpage] Test2</code><br /> készíthetsz kétoldalas cikkeket, ahol a 'Test1' az első oldal, a 'Test2' a második oldal.
<br /><br />
Ha a szövegben olyan HTML kód van, amit meg szeretnél jeleníteni, akkor használd a [html] [/html] lehetőséget. Például, ha beírod a következő szöveget: '&lt;table>&lt;tr>&lt;td>Hello &lt;/td>&lt;/tr>&lt;/table>' a cikkbe, akkor egy táblában kiírja a hello szöveget. Ha viszont kódként írod be a '[html]&lt;table>&lt;tr>&lt;td>Hello &lt;/td>&lt;/tr>&lt;/table>[/html]' szöveget, akkor az fog megjelenni, amit beírtál.";
$ns -> tablerender("Cikk Súgó", $text);
?>
