<?php
/*
+ ----------------------------------------------------------------------------+
|     $Sitio web e107 - Archivos del lenguaje $
|     $Versión: 0.7.16 $
|     $Date: 2009/09/16 17:51:27 $
|     $Author: E107 <www.e107.org> $
|     $Traductor: Josico <www.e107.es> $
+----------------------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }
$caption = "Ayuda Página de Inicio";
$text = "Desde esta pantalla puede elegir qué mostrar en la página inicial de su sitio,
la predeterminada es noticias.
Puede asimismo usar esta página para configurar 'splashscreen',
una página que solo aparecerá cuando el visitante entra por primera vez a su sitio.";
$ns -> tablerender($caption, $text);
?>