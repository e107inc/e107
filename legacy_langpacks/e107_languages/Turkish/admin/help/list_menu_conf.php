<?php
$text = "Bu Bölümde 3 Menüyü ayarlayabilirsiniz.<br />
<b>Menü Yeni Makale </b> <br>
Lütfen bir rakam giriniz, mesela '5' rakamýný  birinci alana giriniz. Bunun neticesi olarak sadece ilk '5' Makalenin gösterilmesi olacaktýr. Alaný boþ býraktýðýnýz taktirde, tümü gözükecektir. Ýsterseniz baðlantýyý girip, Makalenin devamýna gidebilirsiniz. Ýkinci Alana, örnek olarak 'Tüm makaleler'. Alaný boþ býraktýðýnýz takdirde Baðlantý oluþturulmayacaktýr.<br />
<b>Forum Menüsünde Yorumlar</b><br />
standart ayar  #5 olarak yapýlmýþtýr. Gösterilecek harf adeti standart olarak  10.000 dir! uzun satýrlarý önlemek için  yapýlan bir  ayardýr. satýrýn kesiminin iyi gözükmesi için  '...' olarak bitirin. Nasýl gözüktüðünü görmek için orijinal metne bakýn.<br />";
$ns -> tablerender("Menü Ayar Yardým", $text);
?>
