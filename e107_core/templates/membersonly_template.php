<?php

if (!defined('e107_INIT')) { exit; }
/*
$MEMBERSONLY_BEGIN = "<div style='width:75%;margin-right:auto;margin-left:auto'><br /><br />";

$MEMBERSONLY_CAPTION = "<div style='text-align:center'>".LAN_MEMBERS_0."</div>";

$MEMBERSONLY_TABLE = "
<div style='text-align:center'>
<table class='table fborder' style='width:75%;margin-right:auto;margin-left:auto'>
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

$MEMBERSONLY_END = "<div>";
*/

	// e107 v2.x

	$MEMBERSONLY_TEMPLATE['default']['caption']	= LAN_MEMBERS_0;
	$MEMBERSONLY_TEMPLATE['default']['header']	= "<div class='container text-center' style='margin-right:auto;margin-left:auto'><br /><br />";
	$MEMBERSONLY_TEMPLATE['default']['body']	= "<div class='alert alert-block text-danger'>
														{MEMBERSONLY_RESTRICTED_AREA} {MEMBERSONLY_LOGIN}
														{MEMBERSONLY_SIGNUP}<br /><br />{MEMBERSONLY_RETURNTOHOME}

													</div>
													";

	$MEMBERSONLY_TEMPLATE['default']['footer'] = "</div>";



	$MEMBERSONLY_TEMPLATE['signup']['header'] = "<div class='container'><div class='text-center'>{LOGO=login}</div>";
	$MEMBERSONLY_TEMPLATE['signup']['footer'] = "</div>";



