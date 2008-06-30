<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$text = "Notify/hatýrlatýcý 107 de girilmiþ olan olaylarý hatýrlatmak için size email yollar.<br /><br />
Örnek olarak:, þayet  'IP adresi flood yaptýðý için banlandý. ' SEL/ FLOOD Güvelik ayarý açýkken, sitenize Flood / çoðul  saldýrý olursa,'Admin' ve tüm  Yönetici sýnýfýndaki üyelere email gönderilecektir.<br /><br />
Baþka bir örnek olarak  'News item posted by admin= admin tarafýndan haber yollandý' ayarýný 'üyelerinize' ve kullanýcýlarýnýza açarsanýz, o zaman haber yolladýðýnýz taktirde, onlara otomatik olarak email yollanacaktýr.<br /><br />
Þayet email Notifcation/Hatýrlatmayý  alternatif olarak baþka bir email adresine yollamak istiyorsanýz – o zaman 'Email' opsiyonunu seçip yollamak istediðiniz email adresini giriniz.";

$ns -> tablerender("Notify/Hatýrlatýcý Yardým", $text);
?>
