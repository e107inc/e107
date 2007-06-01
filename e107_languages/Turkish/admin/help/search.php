<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$text = "Þayet MySql server versiyonunuz bunu destekliyorsa, siz o zaman  
 daha hýzlý olan MySql sort(ayýklama) metodu ile   PHP sort(ayýklama) metodu arasýnda seçim yapabilirsiniz. Ayarlara bakýnýz.<br /><br />
Þayet sitenizde  Ideographic languages (dil) içerikli ise, yani Çince yada Japonca o zaman 
 PHP sort metodunu kullanmalýsýnýz ve   whole word matching off  seçmelisiniz.";
$ns -> tablerender("Arama  Zardým", $text);
?>
