<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2001-2002 Steve Dunstan (jalist@e107.org)
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $URL$
|     $Revision$
|     $Id$
|     $Author$
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$MEMBERSONLY_BEGIN = "<div style='width:75%;margin-right:auto;margin-left:auto'><br /><br />";

$MEMBERSONLY_CAPTION = "<div style='text-align:center'>".LAN_MEMBERS_0."</div>";

$MEMBERSONLY_TABLE = "
<div style='text-align:center'>
<table class='fborder' style='width:75%;margin-right:auto;margin-left:auto'>
<tr>
	<td class='forumheader3' style='text-align:center'><br />".LAN_MEMBERS_1." ".LAN_MEMBERS_2;
			if ($pref['user_reg'])
			{
				$MEMBERSONLY_TABLE .= " ".LAN_MEMBERS_3." ";
			}
			$MEMBERSONLY_TABLE .= "<br /><br /><a href='".e_BASE."index.php'>".LAN_MEMBERS_4."</a>
	</td>
</tr>
</table>
</div>
";

$MEMBERSONLY_END = "</div>";
?>