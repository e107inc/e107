<?php
$text = "Desde esta página puede añadir artículos simples o multi-página.<br />
 Para artículo multi-página separar cada página con el texto [newpage], ej <br /><code>Test1 [newpage] Test2</code><br /> 
 creará un artíclo de dos páginas con 'Test1' en página 1 y 'Test2' en página 2.
<br /><br />
Si su artículo contiene etiquetas HTML que desea preservar, encierre el código con [preserve] [/preserve]. 
Por ejemplo, si ha introducido el texto '&lt;table>&lt;tr>&lt;td>Hola &lt;/td>&lt;/tr>&lt;/table>' 
en su artículo, debe aparecer una tabla conteniendo la palabra Hola. 
Si ha introducido '[preserve]&lt;table>&lt;tr>&lt;td>Hola &lt;/td>&lt;/tr>&lt;/table>[/preserve]' 
el debe aparecer el código tal como lo introdujo y no la tabla que genera el código.";
$ns -> tablerender("Ayuda Artículo", $text);
?>