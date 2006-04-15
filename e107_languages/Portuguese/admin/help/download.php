<?php
/*
+----------------------------------------------------------------------------+
|     e107 website system - PT Language File.
|
|     $Revision: 1.1 $
|     $Date: 2006-04-15 20:48:49 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }

$text = "Por favor transfira os seus ficheiros para a directoria ".e_FILE."downloads, as suas imagens para a directoria ".e_FILE."downloadimages e as imagens miniatura (previsão) para a directoria ".e_FILE."downloadthumbs.
<br /><br />
Para submeter um download deverá criar inicialmente um grupo, depois uma categoria dentro desse grupo e finalmente tornar os downloads disponiveis em cada categoria.";
$ns -> tablerender("Ajuda = Downloads", $text);
?>