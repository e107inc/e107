<?php
/*
+---------------------------------------------------------------+
|        e107 website system
|        Easy Admin Page by Cameron. (www.e107coders.org)
|        a part of Your_plugin v3.0
|        Released under the terms and conditions of the
|        GNU General Public License (http://gnu.org).
|		 Suitable only for e107 v0.7
+---------------------------------------------------------------+
*/
require_once("../../class2.php");
if(!getperms("P")){ e107::redirect('admin');exit; }

e107::includeLan(e_PLUGIN."metaweblog/languages/".e_LANGUAGE.".php");
require_once(e_ADMIN."auth.php");

$text = "<div class='alert alert-block'>".XMLRPC_HELP_011."</div>";
$ns -> tablerender(XMLRPC_CONFIG_001, $text);
require_once(e_ADMIN."footer.php");

