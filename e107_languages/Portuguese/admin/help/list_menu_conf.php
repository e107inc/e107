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

$text = "Nesta secção pode configurar três menus distintos<br>
<b> Menu de Novos Artigos</b> <br>
Insira por exemplo o número '5' no primeiro campo para mostrar os 5 primeiros artigos, deixe em branco para mostrar tudo. No segundo campo pode configurar o título do link usado para visualizar os restantes artigos, se deixar o link em branco este não será criado (por exemplo: 'Todos os artigos')<br>
<b> Menu de Comentários/Fórum</b> <br>
O número de comentários por defeito é '5' e o número de caracteres é '10000'. A mensagem fixa é para as linhas que eventualmente sejam muito compridas, estas serão cortadas e colocado um grupo de caracteres no fim (por defeito '...'). Verifique as mensagens originais para visualizar a forma como são mostradas no resumo.<br>

";
$ns -> tablerender("Ajuda = Configuração de menus", $text);
?>
