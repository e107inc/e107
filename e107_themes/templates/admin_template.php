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
|     $Revision: 1.12 $
|     $Date: 2005-02-02 10:13:01 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

$ADMIN_HEADER = "<div style='text-align:center'>
{ADMIN_LOGO}
<br />
{ADMIN_LOGGED}
{ADMIN_SEL_LAN}
{ADMIN_USERLAN}
</div>
<table style='width:100%' cellspacing='10' cellpadding='10'>
<tr>
<td style='width:17%; vertical-align: top;'>
{ADMIN_NAV}
{ADMIN_LANG}
{ADMIN_PWORD}
{ADMIN_STATUS=request}
{ADMIN_LATEST=request}
{ADMIN_LOG=request}
{ADMIN_HELP}
{ADMIN_MSG}
{ADMIN_PLUGINS}
</td>
<td style='width:62%; vertical-align: top;'>
";

$ADMIN_FOOTER = "</td>
<td style='width:17%; vertical-align:top'>
{ADMIN_MENU}
{ADMIN_PRESET}
{ADMIN_SITEINFO}
{ADMIN_DOCS}
</td>
</tr>
</table>";

?>