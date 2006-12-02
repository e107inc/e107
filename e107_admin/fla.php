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
|     $Source: /cvs_backup/e107_0.8/e107_admin/fla.php,v $
|     $Revision: 1.1.1.1 $
|     $Date: 2006-12-02 04:33:22 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
if (!getperms("4")) {
	header("location:".e_BASE."index.php");
	exit;
}

$e_sub_cat = 'failed_login';
require_once("auth.php");

$tmp = (e_QUERY) ? explode(".", e_QUERY) : "";
$from = (!$tmp[0]) ? 0 : intval($tmp[0]);
$amount = (!$tmp[1]) ? 50 : intval($tmp[1]);


if(isset($_POST['delbanSubmit']))
{

	$delcount = 0;
	foreach($_POST['fladelete'] as $delete)
	{
		$delcount ++;
		$sql -> db_Delete("generic", "gen_id='$delete' ");
	}
	$message = FLALAN_3.": ".$delcount;

	$bancount = 0;
	foreach($_POST['flaban'] as $ban)
	{
		if($sql -> db_Select("generic", "*", "gen_id=$ban"))
		{
			$at = $sql -> db_Fetch();
			$banlist_ip = $at['gen_ip'];
			$sql->db_Insert("banlist", "'$banlist_ip', '".ADMINID."', '".FLALAN_4."' ");
			$sql -> db_Delete("generic", "gen_id='$ban' ");
			$bancount ++;
		}
	}
	$message .= ", ".FLALAN_5.": ".$bancount;
}


if(e_QUERY == "dabl")
{
	$sql -> db_Delete("generic", "gen_type='auto_banned' ");
	$message = FLALAN_17;
}


if($sql -> db_Select("generic", "*", "gen_type='auto_banned' ORDER BY gen_datestamp DESC "))
{
	$abArray = $sql -> db_getList();
	$message = FLALAN_15;
	foreach($abArray as $ab)
	{
		$message .= " - ".$ab['gen_ip'];
	}

	$message .= "<div style='text-align: right;'>( <a href='".e_SELF."?dabl'>".FLALAN_16."</a> )</div>";

}

if (isset($message)) {
	$ns->tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

$gen = new convert;
$fla_total = $sql->db_Count("generic", "(*)", "WHERE gen_type='failed_login'");
if(!$sql -> db_Select("generic", "*", "gen_type='failed_login' ORDER BY gen_datestamp DESC LIMIT $from,$amount"))
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
		<input type='checkbox' name='fladelete[]' value='$gen_id' /> ".LAN_DELETE."<br />
		<input type='checkbox' name='flaban[]' value='$gen_id' /> ".LAN_BAN."
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
