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
|     $Revision: 1.1 $
|     $Date: 2005-04-10 12:43:42 $
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

if($action == "del")
{
	$sql -> db_Delete("generic", "gen_id='$id' ");
	$message = FLALAN_3;
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

	$text = "<table class='fborder' style='width:99%;'>
	<tr>
	<td style='width: 20%;' class='forumheader'>Date</td>
	<td style='width: 50%;' class='forumheader'>Data</td>
	<td style='width: 20%;' class='forumheader'>IP / Host</td>
	<td style='width: 10%; text-align: center;' class='forumheader'>Options</td>
	</tr>
	";

	foreach($faArray as $fa)
	{
		extract($fa);
		$text .= "<tr>
		<td style='width: 20%;' class='forumheader3'>".$gen->convert_date($gen_datestamp, "forum")."</td>
		<td style='width: 50%;' class='forumheader3'>".str_replace(":::", "<br />", $gen_chardata)."</td>
		<td style='width: 20%;' class='forumheader'>".$fa['gen_ip']."<br />". gethostbyaddr(getenv($gen_ip))."</td>
		<td style='width: 10%; text-align: center;' class='forumheader3'><a href='".e_SELF."?del.$gen_id'>Delete</a> - <a href='".e_ADMIN."banlist.php?fla.$gen_id'>Ban</a></td>
		</tr>
		";
	}

	$text .= "</table>
	";
}

$ns->tablerender(FLALAN_1, $text);

require_once("footer.php");
?>