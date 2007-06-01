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
$text = "Bu sayfa üzerinden Tekil yada çoðul Makale oluþturabilirsiniz.<br />
 Çok sayfalý makale kullanmak için her sayfayý bir BBCode ile ayýrmalýsýnýz. <br />
 Bunun için lütfen SADECE ! bu TAG kullanýn: [newpage], Örnek olarak: <br />
 <code>Test Sayfa 1 [newpage] Test sayfa 2</code><br /> Böyle bir yazý metni olan Ýki sayfalýk bir Makale yazmak  için 1. sayfaya:<br />
 'Test sayfa 1' ve devam eden 2. sayfa içeriði: <br />
 'Test sayfa 2'.
<br /><br />
Þayet HTML Taglar içeren  bir makaleyi kullanmak istiyorsanýz, o zaman bu Taglarý  bu Kodlarla çevrelemelisiniz: [html] [/html].<br />
Örnek olarak bu yazýyý verdik: '&lt;table>&lt;tr>&lt;td>Merhaba &lt;/td>&lt;/tr>&lt;/table>' Þimdi sizin makalenizde içinde Merhaba diye bir Tablo oluþur.<br />
Ama siz þimdi þöyle yazarsanýz '[html]&lt;table>&lt;tr>&lt;td>Merhaba  &lt;/td>&lt;/tr>&lt;/table>[/html]' , o zaman Kodlarla (code)üretilen tablo olarak deðilde, sizin yazdýðýnýz Kod (code)  gibi gözükür.";
$ns -> tablerender("Makale  Yardým", $text);
?>
