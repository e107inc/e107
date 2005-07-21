<?php
$caption = "Forum Help";
$text = "<b>General</b><br />
Šiame lange jūs galite kurti arba redaguoti forumus.<br />
<br />
<b>Parents/Forums</b><br />
Parent -tai antraštė, po kuria išdėstomi forumai. Tai tiek pagerina forumo išvaizdą, tiek padeda lankytojams lengviau keliauti po forumą.
<br /><br />
<b>Accessability</b>
<br />
Galite padaryti forumus prieinamus tik tam tikriems lankytojams. Kai tik priskiriate lankytojui klasę, tai tik pažymėta klasė galės pasiekti forumus.Taip 
valdyti galite tiek parents, tiek individualius forumus.
<br /><br />
<b>Moderators</b>
<br />
Pažymėkite išvardintus administratorius - taip jiems bus suteiktas forumo moderatorių statusas.
Admintatorius privalo turėti forumo moderatorių leidimus, kad papultų į šį sąrašą.
<br /><br />
<b>Ranks</b>
<br />
Nustatykite lankytojųįvertį. Jei vaizdinio laukas pažymėtas, bus naudojami vaizdinia, o jei įrašysite įverčių pavadinimus - bus matomi įverčių pavadinimai. Tikneužmirškite,kadrokiu atveju atitinkamas vaizdinio laukelis turi likti nežynėtas.<br />
The threshold - tai taškų kiekis, kurį reikia pasiekti, kad lankytojo lygis pasikeistų.";
$ns -> tablerender($caption, $text);
unset($text);
?>