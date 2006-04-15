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

$caption = "Ajuda = Banir utilizadores do seu site";
$text = "Poderá banir utilizadores do seu site a partir deste ecrã.<br />
Poderá utilizar o endereço completo de IP ou um wildcard para banir um intervalo de endereços de IP. Poderá ainda inserir o endereço de email para impedir o registo de um deteminado utilizador no seu site.<br /><br />
<b>Banir por endereço de IP:</b><br />
Insira o endereço de IP sob a forma '123.123.123.123', desta forma impedirá o utilizador com este endereço de visitar o seu site.<br />
Se optar por inserir o endereço '123.123.123.*', então bloqueará o acesso a este intervalo de IPs.<br /><br />
<b>Banir por endereço de email</b><br />
Ao utilizar o endereço de email 'user@domain.com' irá impedir que alguem com este endereço de email, se registe no seu site como membro.<br />
Ao inserir o endereço de email '*@domain.com' irá impedir o registo no site a todos os utilizadores que usem este domínio de email.";
$ns -> tablerender($caption, $text);
?>