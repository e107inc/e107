<?php
$text = "Baþka web  Site Veri Bankalarý 'RSS' haber giriþlerini sorgulayabilirsiniz, ve sitenizde gösterebilirsiniz. <br /> bunun için databank/Veri Bankasý baðlantýsýný(URL/Link) giriniz , örnek: http://e107.org/news.xml. Orijinal resimden hoþlanmazsanýz, bir resim içinde Baðlantý girebilirsiniz. Þayet bakým yapmanýz gerekiyorsa  databank/Veri Bankasý (backend) kapatma imkanýnýzda bulunmaktadýr.<br /><br /> Baþlýklarýn gözükmesi için, Admin sayfasýndaki baþlýklar gözüksün (headlines_menu) aktif olmasý gerekmektedir.";

$ns -> tablerender("Baþlýklar", $text);
?>
