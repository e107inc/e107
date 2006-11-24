<?php
/*
+-----------------------------------------------------------------------------+
|     e107 website system - Language File.
+-----------------------------------------------------------------------------+
|     Spolszczenie systemu e107 v0.7
|     Polskie wsparcie: http://e107.org.pl - http://e107poland.org
|
|     $Revision: 1.3 $
|     $Date: 2006-11-24 15:37:55 $
|     $Author: marcelis_pl $
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Polish/admin/help/forum.php,v $
+-----------------------------------------------------------------------------+
|     Zgodne z: /e107_languages/English/admin/help/forum.php rev. 1.3
+-----------------------------------------------------------------------------+
*/
 
if (!defined('e107_INIT')) { exit; }

$caption = "Forum";
$text = "<b>Ogólne</b><br />
Używaj tej strony do tworzenia lub edytowania swoich forów.<br />
<br />
<b>Działy i fora</b><br />
Dział jest nagłówkiem, pod którym są wyświetlane inne fora, co czyni układ strony prostszym i sprawia, że nawigacja na forach jest znacznie łatwiejsza dla odwiedzających.
<br /><br />
<b>Dostępność</b>
<br />
Tutaj możesz ustawić swoje fora tak, aby były dostępne tylko dla określonych odwiedzających. Gdy ustawisz 'grupę' odwiedzających możesz ją zaznaczyć, aby zezwolić tylko tym użytkownikom na dostęp do forum. Postępując w sposób analogiczny możesz nadać dostęp dla działów jak i pojedynczych forów.
<br /><br />
<b>Moderatorzy</b>
<br />
Zaznacz nazwy z listy administratorów, aby nadać im status moderatora tego forum. Administrator, aby zostać tutaj wyświetlonym, musi mieć nadane uprawnienia moderatora forum.
<br /><br />
<b>Ranga</b>
<br />
Tutaj możesz ustawić rangi dla użytkowników. Jeśli pola obrazków są wypełnione, zostaną użyte odpowiednie grafiki. Aby użyć nazwy rangi wpisz ją i upewnij się, że odpowiednie pole obrazka rangi jest puste.<br />Próg stanowi ilość punktów, które użytkownik musi zdobyć przed zmianą jego poziomu.";
$ns -> tablerender($caption, $text);
unset($text);

?>
