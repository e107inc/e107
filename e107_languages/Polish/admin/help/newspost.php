<?php
/*
+-----------------------------------------------------------------------------+
|     e107 website system - Language File.
+-----------------------------------------------------------------------------+
|     Spolszczenie systemu e107 v0.7
|     Polskie wsparcie: http://e107.org.pl - http://e107poland.org
|
|     $Revision: 1.8 $
|     $Date: 2009-09-13 10:26:27 $
|     $Author: marcelis_pl $
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Polish/admin/help/newspost.php,v $
+-----------------------------------------------------------------------------+
|     Zgodne z: /e107_languages/English/admin/help/newspost.php rev. 1.3
+-----------------------------------------------------------------------------+
*/
 
if (!defined('e107_INIT')) { exit; }

$caption = "Aktualności";
$text = "<b>Ogólnie</b><br />
Treść będzie wyświetlana na stronie głównej, rozszerzenie będzie możliwe do przeczytania po kliknięciu w link <i>Czytaj więcej</i>.
<br />
<br />
<b>Pokazuj tylko tytuł</b>
<br />
Aktywacja tego spowoduje wyświetlenie tylko tytułu aktualności na stronie głównej wraz z linkiem do pełnej wiadomości.
<br /><br />
<b>Aktywacja</b>
<br />
Jeśli ustawisz datę początkową oraz/lub końcową dana pozycja aktualności będzie wyświetlana tylko pomiędzy wskazanymi datami.
";
$ns -> tablerender($caption, $text);

?>
