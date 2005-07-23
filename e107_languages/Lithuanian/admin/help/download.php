<?php
$text = "Prašome savo bylas siųsti į ".e_FILE."downloads direktoriją, vaizdinius - ".e_FILE."downloadimages direktoriją ir thumbnail vaizdinius - į ".e_FILE."downloadthumbs direktoriją.
<br /><br />
Norėdami sukurti siuntinį, pirmiausiai sukurkite parent, tada sukurkite kategoriją šiame parent. Tada Jūs jau galėsite aktyvuoti siuntinius parsisiuntimui.";
$ns -> tablerender("Download Help", $text);
?>