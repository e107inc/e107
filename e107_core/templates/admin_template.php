<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_themes/templates/admin_template.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

if (!defined('e107_INIT')) { exit; }

$ADMIN_HEADER = "<div style='text-align:center'>
{ADMIN_LOGO}
<br />
{ADMIN_LOGGED}
{ADMIN_SEL_LAN}
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
{ADMIN_UPDATE}
{ADMIN_SITEINFO}
{ADMIN_DOCS}
</td>
</tr>
</table>
{ADMIN_CREDITS}
";


