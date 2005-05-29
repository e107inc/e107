<?php
$text = "In dieser Sektion können Sie drei Menüs konfigrieren.<br />
<b>Menü Neue Artikel</b> <br>
Geben Sie eine Zahl, zB die Zahl '5' in das erste Feld ein. Das führt dazu, dass die ersten '5' Artikel angezeigt werden. Lassen Sie das Feld leer, werden alle angezeigt. Sie können auch den Link bennen, der zu dem rest der Artikel führt, im zweiten Feld, zB 'Alle Artikel'. Lassen Sie das Feld leer, wird kein Link erstellt.<br />
<b>Kommentare im Menü Forum</b><br />
Die Standardeinstellung ist auf #5 gesetzt. Die Anzahl der Zeichen standardmäszlig;ig auf 10.000! Der Zeichenschluss ist für zu lange Zeilen. Er schneidet sie am ende ab. Eine gute Wahl den Zeichenschluss anzuzeigen ist '...'. Schauen Sie sich Originalbeiträge an, um zu sehen wie es aussieht.<br />";
$ns -> tablerender("Menü Konfiguration Hilfe", $text);
?>
