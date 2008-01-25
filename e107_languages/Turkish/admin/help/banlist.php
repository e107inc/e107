<?php

/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: 
|     $Revision: 
|     $Date: 
|     $Author: 
|			
+----------------------------------------------------------------------------+
*/
$caption = "Kullanýcýyý Sitenizden uzaklaþtýrma (BANlama)";
$text = "Bu Menü üzerinden kullanýcýlarý sitenizden uzaklaþtýrabilirsiniz (BAN).<br />
Ya bir kiþi için IP adresiniz girerek , yada birçok kiþiyi (wildcard) rastgele BANlayabilirsiniz. Yada belli E.Mail adreslerini girerek istemediðiniz kiþilerin sitenize kayýt  olmasýný engelleyebilirsiniz.<br /><br />
<b> IP-Adresi Üzeri BANlama:</b><br />
Örnek olarak mesela 123.123.123.123 IP-Adresini verirseniz , bu IP kullanan kiþiler yasaklanýr (BANlanýr).<br />
Ama siz þimdi  123.123.123.* IP-Adresini  verirseniz, o zaman sonu 000 - 999 ile biten tüm  IP adresi kullanýcýlarý BANlanýr. Yaný bir kaç deðiþik kullanýcý yasaklanmýþ olacak.<br /><br />
<b>E-Mail Adresi  üzeri BANlama:</b><br />
Mesela  isim@email.com adresini verirseniz, ozaman bu E-Mail adresini kullanan kiþi sitenize kayýt olmasý engellenmiþ olur. Bu genellikle bir kiþi yada bir gruba ait kayýt yapmaya çalýþan ekip olabilir.<br />
Þayet þimdi  *@email.com adresini girerseniz, o zaman bu domaine ait emaili olan KÝMSE kayýt olamaz! O yüzden yasaklama iþleminde dikkatli olunuz!.";
$ns -> tablerender($caption, $text);
?>
