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

$caption = "Ajuda = Publicação de Notícias";
$text = "<b>Geral</b><br />
O conteúdo da notícia será mostrado na página principal, a extensão será mostrada ao clicar no link 'Ler mais'.
<br />
<br />
<b>Mostrar apenas título</b>
<br />
Permite mostar apenas o título na página principal com um link para a notícia completa.
<br /><br />
<b>Activação</b>
<br />
Se definir uma data de início e/ou fim a sua notícia será mostrada no site apenas durante este período.
";
$ns -> tablerender($caption, $text);
?>