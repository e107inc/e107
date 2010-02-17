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
|     $Source: /cvs_backup/e107_0.7/e107_admin/fla.php,v $
|     $Revision$
|     $Date$
|     $Author$
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
if (!getperms("4")) 
{
	header("location:".e_BASE."index.php");
	exit;
}

$e_sub_cat = 'failed_login';
require_once("auth.php");

$tmp = (e_QUERY) ? explode(".", e_QUERY) : "";
$from = intval(varset($tmp[0], 0));
$amount = intval(varset($tmp[1], 50));

/*
'generic' table:
  gen_id 		- unique identifier
  gen_type 		- 'auto_banned' is of interest here
  gen_datestamp	- date/time of ban
  gen_user_id 	- set to zero
  gen_ip 		- IP address of ban
  gen_intdata 	- user ID (where known)
  gen_chardata 	- ban detail as known

*/

function deleteBan($banID, $banIP = '')
{
	global $sql2;
	if ($banIP == '')
	{
		if($sql2->db_Select("generic", "gen_ip", "gen_id={$banID}"))
		{
			$at = $sql2->db_Fetch();
			$banIP = $at['gen_ip'];
		}
	}
	$sql2->db_Delete("generic", "gen_id='{$banID}' ");			// Delete from generic table regardless
	if ($banIP == '') return FALSE;
	$sql2->db_Delete("banlist", "banlist_ip='{$banIP}'");		// Delete from main banlist only if we've got an IP address
	return TRUE;
}


if(isset($_POST['delbanSubmit']))
{

	$delcount = 0;
	foreach($_POST['fladelete'] as $delete)
	{
		if (deleteBan($delete))
		{
			$delcount ++;
		}
	}
	$message = FLALAN_3.": ".$delcount;

	$bancount = 0;
	foreach($_POST['flaban'] as $ban)
	{
		if($sql -> db_Select("generic", "*", "gen_id={$ban}"))
		{
			$at = $sql -> db_Fetch();
			$banlist_ip = $at['gen_ip'];
			$sql->db_Insert("banlist", "'{$banlist_ip}', '".ADMINID."', '".FLALAN_4."' ");
			$sql -> db_Delete("generic", "gen_id='{$ban}' ");
			$bancount ++;
		}
	}
	$message .= ", ".FLALAN_5.": ".$bancount;
}


if(e_QUERY == "dabl")
{
	$sql -> db_Select("generic", 'gen_ip,gen_id',"gen_type='auto_banned' ");
	while ($row = $sql->db_Fetch())
	{
		if (deleteBan($row['gen_id'],$row['gen_ip']))
		{
			$delcount ++;
		}
	}
	$message = FLALAN_17;
}


// Now display any outstanding auto-banned IP addresses
if($sql -> db_Select("generic", "*", "gen_type='auto_banned' ORDER BY gen_datestamp DESC "))
{
	$abArray = $sql -> db_getList();
	$message = FLALAN_15;
	foreach($abArray as $ab)
	{
		$message .= " - ".$ab['gen_ip'];
	}

	$message .= "<div style='text-align: right;'>(<a href='".e_SELF."?dabl'>".FLALAN_16."</a>)</div>";

}

if (isset($message)) 
{
	$ns->tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

$gen = new convert;
$fla_total = $sql->db_Count("generic", "(*)", "WHERE gen_type='failed_login'");
if(!$sql -> db_Select("generic", "*", "gen_type='failed_login' ORDER BY gen_datestamp DESC LIMIT {$from},{$amount}"))
{
	$text = "<div style='text-align: center;'>".FLALAN_2."</div>";
}
else
{

	$faArray = $sql -> db_getList('ALL', FALSE, FALSE);

	$text = "
	<form method='post' action='".e_SELF."' id='flaform' >
	<table class='fborder' style='width:99%;'>
	<tr>
	<td style='width: 20%;' class='forumheader'>".FLALAN_6."</td>
	<td style='width: 50%;' class='forumheader'>".FLALAN_7."</td>
	<td style='width: 20%;' class='forumheader'>".FLALAN_8."</td>
	<td style='width: 10%; text-align: center;' class='forumheader'>".FLALAN_9."</td>
	</tr>
	";

	foreach($faArray as $fa)
	{
		extract($fa);

		$host = $e107->get_host_name(getenv($gen_ip));
		$text .= "<tr>
		<td style='width: 20%;' class='forumheader3'>".$gen->convert_date($gen_datestamp, "forum")."</td>
		<td style='width: 50%;' class='forumheader3'>".str_replace(":::", "<br />", htmlentities($gen_chardata, ENT_QUOTES, CHARSET))."</td>
		<td style='width: 20%;' class='forumheader'>".$fa['gen_ip']."<br />{$host}</td>
		<td style='width: 10%; text-align: left;' class='forumheader3'>
		<label><input type='checkbox' name='fladelete[]' value='{$gen_id}' /> ".LAN_DELETE."</label><br />
		<label><input type='checkbox' name='flaban[]' value='{$gen_id}' /> ".LAN_BAN."</label>
		</td>
		</tr>
		";
	}

	$text .= "
	<tr>
	<td colspan='4' class='forumheader' style='text-align: right;'>

	<a href='".e_SELF."?checkall=1' onclick=\"setCheckboxes('flaform', true, 'fladelete[]'); return false;\">".FLALAN_11."</a> -
	<a href='".e_SELF."' onclick=\"setCheckboxes('flaform', false, 'fladelete[]'); return false;\">".FLALAN_12."</a>
	<br />
	<a href='".e_SELF."?checkall=1' onclick=\"setCheckboxes('flaform', true, 'flaban[]'); return false;\">".FLALAN_13."</a> -
	<a href='".e_SELF."' onclick=\"setCheckboxes('flaform', false, 'flaban[]'); return false;\">".FLALAN_14."</a>

	</td>
	</tr>

	<tr>
	<td colspan='4' class='forumheader' style='text-align: center;'><input class='button' type='submit' name='delbanSubmit' value='".FLALAN_10."' /></td>
	</tr>
	</table>
	</form>
    <div style='text-align:center'><br />
	";

	$parms = $fla_total.",".$amount.",".$from.",".e_SELF.'?'."[FROM].".$amount;
	$text .= $tp->parseTemplate("{NEXTPREV={$parms}}");

    $text .= "</div>";



}

$ns->tablerender(FLALAN_1, $text);

require_once("footer.php");

?>
