<?php
$text = "Puteţi recepţiona sau prelucra ştirile altor site-uri şi le puteţi afişa pe propriul site de aici.<br />Introduceţi URL-ul complet către sistemul de ştiri (ex. http://e107.org/news.xml). Puteţi adăuga calea către o imagine dacă nu vă place cea implicită, sau dacă nu aţi definit-o. Puteţi activa si dezactiva sistemul de ştiri dacă site-ul dispare.<br /><br />Pentru a vedea titlurile pe site-ul dumneavoastră, asiguraţi-vă ca aţi activat headlines_menu de pe pagina meniurilor.";

$ns -> tablerender("Titluri", $text);
?>