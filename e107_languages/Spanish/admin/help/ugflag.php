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
$text = "Si estás actualizando e107 o necesitas tu sitio
fuera de línea por un momento solo marca la casilla mantenimiento
y tus visitantes serán redirigidos a una página explicandoles que
el sitio está en reparación.
Cuando hayas finalizado desmarca la casilla para retornar al estado normal.";

$ns -> tablerender("Mantenimiento", $text);
?>