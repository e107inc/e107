<?php
$text = "Bu Menü üzerinden,  Kendinize özel sayfalar ve Menüler  yapabilirsiniz.<br /><br />
<b>Önemli Bilgi!</b><br />
- Bu Ekstralarý kullanabilmek için  /e107_plugins/custom/ ve /e107_plugins/custompages/ dosyasý yetkisini  CHMOD  777 yapmalýsýnýz.
<br />
- Bunun için HTML Kodlamalarýnýda kullanabilirsiniz. Dikkat edeceðiniz önemli konu, ( " )  yerine ( ' ) kullanmanýzdýr !! . "/  kullandýðýnýz taktirde mizampajda hata oluþacaktýr!
<br /><br />
<i>Menü/Sayfa-Dosya ismi</i>: Yaptýðýnýz Menü ve sayfalar ".e_PLUGIN."custom/ Klasöründe  'Siteninismi.php' olarak kayýt olacaktýr.<br />
Sayfa kendisi ".e_PLUGIN."custompages/ Klasöründeki Siteninismi.php dosyasýna kayýt olacaktýr<br /><br />
<i>Menü/Sayfa Baþlýðý<i/>: Yazý Metni, Baþlýk olarak Menüler ve Sayfa baþlýðýnda gösterilecektir.<br /><br />
<i>Menü/Sayfa yazýsý(içeriði)</I>: Sizin girmiþ olduðunuz bilgiler, ya 'BODY' bölgesine yada normal yazý olarak verilecektir. çaðýrmak için her seferinde, class2.php,   HEADER ve FOOTERE, satýrlarý eklemenize gerek yoktur.Bu satýrlar otomatik olarak eklenecektir.<br /> Yinede sayfanýzda deðiþiklikler yapmak isterseniz, bunu CSS biçem dosyasý üzerinden sýnýflandýrarak sayfanýzýn deðiþik görünmesini saðlayabilirsiniz.";
$ns -> tablerender(CUSLAN_18, $text);
?>
