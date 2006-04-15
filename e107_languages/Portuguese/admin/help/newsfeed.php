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

$text = "Poderá publicar no seu sites os cabeçalhos de notícias provenientes de outros sites através do RSS.<br />Insira o link completo do backend (p.exo. http://e107.org/news.xml). Pode ainda adicionar uma imagem a cada site/conjunto de títulos (introduzir o link do botão/imagem) ou se preferir deixar em branco (escrever 'none'). Pode ainda seleccionar exactamente os títulos que deseja mostrar no seu site, ou desactivar o backend no caso de o site estar desligado.<br /><br />Para ver os cabeçalhos de notícias no seu site, certifique-se que o feed de notícias está activado na sua página de menus.";

$ns -> tablerender("Ajuda = Feeds de notícias (RSS)", $text);
?>