<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_themes/templates/admin_template.php,v $
|     $Revision: 1.4 $
|     $Date: 2004-11-18 03:12:44 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

$ADMIN_HEADER = "<div style='text-align:center'>
{ADMIN_LOGO}
<br />
{ADMIN_LOGGED}";

if($_COOKIE['userlan']){
$ADMIN_HEADER .= "<br /><b>Language:</b> ".$_COOKIE['userlan'];
}

$ADMIN_HEADER .= "
<div>
".(e_MLANG == 1 ? "{ADMIN_MULTILANG}" : "" )."
<table style='width:100%' cellspacing='10' cellpadding='10'>
<tr>
<td style='width:15%; vertical-align: top;'>
{ADMIN_NAV}
{ADMIN_PWORD}
{ADMIN_HELP}
{ADMIN_MSG}
{ADMIN_PLUGINS}
</td>
<td style='width:60%; vertical-align: top;'>
";

$ADMIN_FOOTER = "</td>
<td style='width:20%; vertical-align:top'>
{ADMIN_MENU}
{ADMIN_SITEINFO}
{ADMIN_DOCS}
</td>
</tr>
</table>
</div>
</div>
<br /><br />";
?>