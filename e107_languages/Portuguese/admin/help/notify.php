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

$text = "As notificações enviam emails de aviso quando ocorrem eventos no e107.<br /><br />
Por exemplo, se definir 'IP banido por flooding no site' para a classe 'Administrador', todos os admins receberão um email quando o seu site sofrer um flood.<br /><br />
Poderá ainda a título de exemplo, definir 'Notícias publicadas por admin' para a classe 'Membros' e todos os seu utilizadores registados irão receber um mail com as notícias que publicar no seu site.<br /><br />
Se desejar enviar as notificações por email para um endereço alternativo, deverá seleccionar a opção 'Email' e inserir o novo endereço no campo correspondente.";

$ns -> tablerender("Ajuda = Notificações", $text);
?>