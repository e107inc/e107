<?php
$text = "Über dieses Menü können Sie individuelle Menüs bzw. Seiten erstellen, die Sie ebenfalls mit eigenen Inhalten versehen können.<br /><br />
<b>Wichtiger Hinweis!</b><br />
- um dieses Extra zu nutzen, müssen Sie den Ordner /e107_plugins/custom/ und /e107_plugins/custompages/ per CHMOD auf 777 setzen.
<br />
- Sie können natürlich HTML Code nutzen. Achten Sie aber darauf, dass Sie für die Attribute nur das Teichen ' nutzen und NICHT! die klassischen Anführungszeichen \". Letzteres wird Ihre Seite im Layout zerstören!
<br /><br />
<i>Menü/Seiten-Dateiname</i>: Der Name Ihres Menüs bzw. Ihre Seite wird gespeichert als 'Seitenname.php' in dem ".e_PLUGIN."custom/ Verzeichnis<br />
Die Seite ansich wird als Seitenname.php im ".e_PLUGIN."custompages/ Verzeichnis<br /><br />
<i>Menü/Seitenüberschrift<i/>: Der Text wird als Seitenüberschrift des Menüs bzw. im Seitentitel angezeigt.<br /><br />
<i>Menü/ Seitentext</I>: Die von Ihnen eingegebenen Daten in den Bereich 'BODY' oder als normaler Text eingegeben. Es ist nicht notwendig, dass Sie die Zeilen einfügen, um die class2.php aufzurufen bzw. die Zeilen HEADER oder FOOTER. Diese Zeilen werden automtisch hinzugefügt.<br /> Wenn Sie dennoch Ihre Seite etwas anpassen wollen, haben Sie die Möglichkeit über das CSS Stylesheet, classen zu vergeben, die dann Ihre Seite anders aussehen lässt.";
$ns -> tablerender(CUSLAN_18, $text);
?>
