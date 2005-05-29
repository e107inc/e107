<?php
$text = "Von dieser Seite aus können Sie einseitige oder mehrseitige Artikel erstelen.<br />
 Für mehrseitige Artikel müssen Sie jede Seite mit einem BBCode Tag separieren. <br />
 Nutzen Sie dazu bitte NUR! folgendes Tag: [newpage], zum Beispiel: <br />
 <code>Testseite 1 [newpage] Testseite 2</code><br /> erstellt einen zweiseitigen Artikel mit folgendem Inhalt auf der ersten Seite:<br />
 'Testseite 1' und folgendem Inhalt auf Seite 2: <br />
 'Testseite 2'.
<br /><br />
Wenn Ihr Artikel HTML Tags enthät, die Sie erhaltenn möchten, dann müssen Sie diese Tag mit folgendem Code umschlie&szlig;en: [html] [/html].<br />
Zum Beispiel haben folgenden Text eingegeben: '&lt;table>&lt;tr>&lt;td>Hallo Text &lt;/td>&lt;/tr>&lt;/table>' In Ihrem Artikel erscheint dann eine Tabelle, mit den Worten Hallo Text.<br />
Wenn Sie jetzt aber eigeben '[html]&lt;table>&lt;tr>&lt;td>Hello Text &lt;/td>&lt;/tr>&lt;/table>[/html]' , dann erscheint der Code wie Sie ihn eingegeben haben und nicht die Tabelle die mit dem Code generiert wird.";
$ns -> tablerender("Artikel Hilfe", $text);
?>
