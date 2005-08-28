<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/legacy_langpacks/e107_languages/Spanish/admin/help/article.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-08-28 09:26:19 $
|     $Author: natxocc $
+----------------------------------------------------------------------------+
*/
$text = "Desde esta página puede añadir artículos simples o multi-página.<br />
 Para un artículo multi-página debe separar cada página con el texto [newpage], ej <br /><code>Test1 [newpage] Test2</code><br />
 Creará un artíclo de dos páginas con 'Test1' en la página 1 y 'Test2' en la página 2.
<br /><br />
Si su artículo contiene etiquetas HTML que desea preservar, encierre el código con [preserve] [/preserve].
Por ejemplo, si ha introducido el texto '<table><tr><td>Hola </td></tr></table>'
en su artículo, debe aparecer una tabla conteniendo la palabra Hola.
Si ha introducido '[preserve]<table><tr><td>Hola </td></tr></table>[/preserve]'
el debe aparecer el código tal como lo introdujo y no la tabla que genera el código.";
$ns -> tablerender("Ayuda Artículo", $text);
?>