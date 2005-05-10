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
|     $Revision: 1.4 $
|     $Date: 2005-05-10 19:18:03 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
if (!getperms("4")) {
	header("location:".e_BASE."index.php");
	exit;
}

$e_sub_cat = 'failed_login';
require_once("auth.php");

if(e_QUERY)
{
	list($action, $id) = explode(".", e_QUERY);
}

if(IsSet($_POST['delbanSubmit']))
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


if (isset($message)) {
	$ns->tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

$gen = new convert;
if(!$sql -> db_Select("generic", "*", "gen_type='failed_login' ORDER BY gen_datestamp DESC"))
{
	$text = "<div style='text-align: center;'>".FLALAN_2."</div>";
}
else
{

	$faArray = $sql -> db_getList();

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
		$text .= "<tr>
		<td style='width: 20%;' class='forumheader3'>".$gen->convert_date($gen_datestamp, "forum")."</td>
		<td style='width: 50%;' class='forumheader3'>".str_replace(":::", "<br />", $gen_chardata)."</td>
		<td style='width: 20%;' class='forumheader'>".$fa['gen_ip']."<br />". gethostbyaddr(getenv($gen_ip))."</td>
		<td style='width: 10%; text-align: center;' class='forumheader3'>
		<input type='checkbox' name='fladelete[]' value='$gen_id' /> delete<br />
		<input type='checkbox' name='flaban[]' value='$gen_id' /> ban
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

	<script type=\"text/javascript\">
	
	</script>


	";
}

$ns->tablerender(FLALAN_1, $text);

require_once("footer.php");

/*
<a href='".e_SELF."?checkall=1' onclick=\"setCheckboxes('flaform', true); return false;\">".FLALAN_11."</a> -
	<a href='".e_SELF."' onclick=\"setCheckboxes('flaform', false); return false;\">".FLALAN_12."</a>
*/

?>