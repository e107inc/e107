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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/poll/admin_config.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-03-03 18:36:13 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/
require_once("../../class2.php");
if (!is_object($tp)) $tp = new e_parse;
if (!getperms("U")) {
	header("location:".e_BASE."index.php");
	 exit;
}
$e_sub_cat = 'poll';

require_once(e_ADMIN."auth.php");
require_once(e_PLUGIN."poll/poll_class.php");
require_once(e_HANDLER."form_handler.php");
require_once(e_HANDLER."userclass_class.php");

@include_once(e_PLUGIN."poll/languages/admin/".e_LANGUAGE.".php");
@include_once(e_PLUGIN."poll/languages/admin/English.php");

$rs = new form;
$poll = new poll;

if (isset($_POST['reset']))
{
	unset($poll_id, $_POST['poll_title'], $_POST['poll_option'], $_POST['activate'], $_POST['multipleChoice'], $_POST['showResults'], $_POST['startday'], $_POST['startmonth'], $_POST['startyear'], $_POST['endday'], $_POST['endmonth'], $_POST['endyear']);
	define("RESET", TRUE);
} else if (e_QUERY)
{
	list($action, $id) = explode(".", e_QUERY);
	define("POLLACTION", $action);
	define("POLLID", $id);
}
else
{
	define("POLLACTION", FALSE);
	define("POLLID", FALSE);
}

if ($action == "delete" && $_POST['del_poll_confirm'] == 1) {
	$message = $poll->delete_poll($sub_action);
	unset($poll_id, $_POST['poll_title'], $_POST['poll_option'], $_POST['activate']);
}

if (isset($_POST['submit']))
{

	if($_POST['poll_title'])
	{
		$message = $poll -> submit_poll();
		unset($_POST['poll_title'], $_POST['poll_option'], $_POST['activate'], $_POST['poll_comment']);
	}
	else
	{
		$message = "Field(s) left blank";
	}
}

if (POLLACTION == "edit" && !$_POST['preview'] && !$_POST['submit'])
{

	if ($sql->db_Select("poll", "*", "poll_id=".POLLID)) {
		$row = $sql->db_Fetch();
		extract($row);

		$tmpArray = explode(chr(1), $poll_options);

		foreach($tmpArray as $option)
		{
			$_POST['poll_option'][] = $option;
		}

		$_POST['activate'] = $poll_active;
		$_POST['option_count'] = count($_POST['poll_option']);
		$_POST['poll_title'] = $poll_title;
		$_POST['poll_comment'] = $poll_comment;

		if ($poll_start_datestamp)
		{
			$tmp = getdate($poll_start_datestamp);
			$_POST['startmonth'] = $tmp['mon'];
			$_POST['startday'] = $tmp['mday'];
			$_POST['startyear'] = $tmp['year'];
		}
		if ($poll_end_datestamp)
		{
			$tmp = getdate($poll_end_datestamp);
			$_POST['endmonth'] = $tmp['mon'];
			$_POST['endday'] = $tmp['mday'];
			$_POST['endyear'] = $tmp['year'];
		}

		$_POST['multipleChoice'] = $poll_allow_multiple;
		$_POST['showResults'] = $poll_result_type;
		$_POST['pollUserclass'] = $poll_vote_userclass;
		$_POST['storageMethod'] = $poll_storage_method;
	}
}

if (isset($_POST['preview']))
{
	$poll->render_poll($_POST, "preview");
	
}

if (isset($message))
{
	$ns->tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

$text = "<div style='text-align:center'><div style='padding : 1px; ".ADMIN_WIDTH."; height : 200px; overflow : auto; margin-left: auto; margin-right: auto;'>
	<form action=\"".e_SELF."\" method=\"post\" id=\"del_poll\" >";

if ($poll_total = $sql->db_Select("poll")) {
	$text .= "<table class='fborder' style='width:99%'>
		<tr>
		<td style='width:5%' class='fcaption'>ID
		<input type=\"hidden\" name=\"del_poll_confirm\" id=\"del_poll_confirm\" value=\"1\" />
		</td>
		<td style='width:75%' class='fcaption'>".POLLAN_7."</td>
		<td style='width:20%' class='fcaption'>".POLLAN_20."</td>
		</tr>";
	while ($row = $sql->db_Fetch()) {
		extract($row);
		$text .= "<tr>
			<td style='width:5%' class='forumheader3'>$poll_id</td>
			<td style='width:75%' class='forumheader3'>$poll_title</td>
			<td style='width:20%; text-align:center' class='forumheader3'><div>". $rs->form_button("button", "main_edit_{$poll_id}", POLLAN_4, "onclick=\"document.location='".e_SELF."?edit.$poll_id'\""). $rs->form_button("submit", "main_delete_{$poll_id}", POLLAN_5, "onclick=\"confirm_($poll_id)\"")."
			</div></td>
			</tr>";
	}
	$text .= "</table>";
} else {
	$text .= "<div style='text-align:center'>".POLLAN_22."</div>";
}
$text .= "</form></div></div>";
$ns->tablerender(POLLAN_3, $text);

$poll_total = $sql->db_Select("poll");

$text = $poll -> renderPollForm();

$ns->tablerender(POLLAN_19, $text);
require_once("footer.php");
function headerjs() {
	global $tp;
	$headerjs = "<script type=\"text/javascript\">
		function confirm_(poll_id){
		var x=confirm(\"".$tp->toJS(POLLAN_21)." [ID: \" + poll_id + \"]\");
		if (x){
		document.getElementById('del_poll').action='".e_SELF."?delete.' + poll_id;
		document.getElementById('del_poll').submit();
		}
		}
		</script>";
	return $headerjs;
}
?>