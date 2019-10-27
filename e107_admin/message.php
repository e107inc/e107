<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

// FILE IS DEPRECATED - UP FOR REMOVAL IN THE FUTURE

require_once("../class2.php");

e107::coreLan('message', true);

$e_sub_cat = 'message';
require_once("auth.php");
$gen = new convert;
$mes = e107::getMessage();

// DO NOT TRANSLATE - warning for deprecated file. 
e107::getMessage()->addWarning("This area is no longer in use and will be removed in the future. For reported broken downloads, see the Downloads Admin Area.");

$ns->tablerender("Received Messages", $mes->render());


/*

$messageTypes = array("Broken Download", "Dev Team Message");
$queryString = "";
foreach($messageTypes as $types) {
	$queryString .= " gen_type='$types' OR";
}
$queryString = substr($queryString, 0, -3);

if(isset($_POST['delete_message']))
{
	if(preg_match("/\s[0-9]+/si", $_POST['delete_message'], $match))
	{
		$id = $match[0];
		$sql->db_Delete("generic", "gen_id=$id");
		$message = MESSLAN_3;
	}
}

if(isset($_POST['delete_all']) && isset($_POST['deleteconfirm']))
{
	$sql->db_Delete("generic", $queryString);
	$message = MESSLAN_6;
}


if (isset($message)) {
	$ns->tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}


if($amount = $sql -> db_Select("generic", "*", $queryString))
{


	$text = "<table style='width: 98%;' class='fborder'>\n<form method='post' action='".e_SELF."'>\n";
	$messages = $sql -> db_getList();

	foreach($messages as $message)
	{
		extract($message);

		$sql -> db_Select("user", "user_name", "user_id=$gen_user_id");
		$user = $sql -> db_Fetch();
		$user = "<a href='".e_BASE."user.php?id.$gen_user_id'>".$user['user_name']."</a>";

		switch($gen_type)
		{
			case "Broken Download":
				$link = "<a href='".e_BASE."download.php?view.$gen_intdata' rel='external' title='".MESSLAN_11."'>$gen_ip</a>";
                $link .= " [<a href='".e_ADMIN."download.php?create.edit.".$gen_intdata."'>".LAN_EDIT."</a>]";
			break;
			case "Dev Team Message":
				$link = "";
			break;
		}


		$text .= "<tr>
<td style='width: 100%;' class='forumheader3'><b>".MESSLAN_8."</b>: $gen_type<br />
<b>".MESSLAN_9."</b>: ".$gen->convert_date($gen_datestamp, 'long')."<br />
<b>".MESSLAN_10."</b>: $user<br />
<b>".MESSLAN_13."</b>: $link ".
($gen_chardata ? "<br /><b>".MESSLAN_12."</b>: $gen_chardata" : "")."<br /><input class='btn btn-default btn-secondary button' type='submit' name='delete_message' value='".MESSLAN_2." $gen_id' />
</td>\n</tr>\n";
	}

$text .= "
<tr>
<td><br /><input class='btn btn-default btn-secondary button' type='submit' name='delete_all' value='".MESSLAN_4."' />
<input type='checkbox' name='deleteconfirm' value='1' /> ".MESSLAN_5."
</td>
</tr>


</form></table>";
}
else
{
	$text = MESSLAN_7;
}
$ns->tablerender(MESSLAN_1, $text);

*/
require_once("footer.php");