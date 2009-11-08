<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2008 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Manage/View failed login attempts
 *
 * $Source: /cvs_backup/e107_0.8/e107_admin/fla.php,v $
 * $Revision: 1.9 $
 * $Date: 2009-11-08 09:14:22 $
 * $Author: e107coders $
 *
*/
require_once("../class2.php");
if (!getperms("4"))
{
	header("location:".e_BASE."index.php");
	exit;
}

$e_sub_cat = 'failed_login';
require_once("auth.php");

require_once(e_HANDLER."form_handler.php");
$frm = new e_form();

require_once(e_HANDLER."message_handler.php");
$emessage = &eMessage::getInstance();

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
	$sql2->db_Delete("generic", "gen_id='{$banID}' ");			// Delete from generic table
	if ($banIP == '') return FALSE;
	$sql2->db_Delete("banlist", "banlist_ip='{$banIP}'");		// Delete from main banlist
	return TRUE;
}

/*
 * FIXME - refine messages (strange messages on delete all & reload)
 */
if(isset($_POST['delbanSubmit']))
{
	$message = '';
	$delcount = 0;
	$spacer = '';
	foreach($_POST['fladelete'] as $delete)
	{
		$delcount++;
		$sql->db_Delete("generic", "gen_id='{$delete}' ");
	}
	if ($delcount)
	{
		$emessage->add(FLALAN_3.": ".$delcount, E_MESSAGE_SUCCESS);
	}

	$bancount = 0;
	foreach($_POST['flaban'] as $ban)
	{
		if($sql->db_Select("generic", "*", "gen_id={$ban}"))
		{
			$at = $sql->db_Fetch();
			if (!$e107->add_ban(4, FLALAN_4, $at['gen_ip'], ADMINID))
			{  // IP on whitelist (although possibly we shouldn't get to this stage, but check anyway
				$emessage->add(str_replace(FLALAN_18,'--IP--',$at['gen_ip']), E_MESSAGE_WARNING);
			}
			else $bancount++;
			$banlist_ip = $at['gen_ip'];
			//XXX - why inserting it twice?
			//$sql->db_Insert("banlist", "'$banlist_ip', '".ADMINID."', '".FLALAN_4."' ");
			$sql->db_Delete("generic", "gen_id='{$ban}' ");
		}
	}
	$emessage->add(FLALAN_5.": ".$bancount, $bancount ? E_MESSAGE_SUCCESS : E_MESSAGE_INFO);
}


if(e_QUERY == "dabl")
{
	$sql->db_Select("generic", 'gen_ip,gen_id',"gen_type='auto_banned' ");
	while ($row = $sql->db_Fetch())
	{
		if (deleteBan($row['gen_id'],$row['gen_ip']))
		{
			$delcount++;
		}
	}
	//XXX - add delcount to the message
	$emessage->add(FLALAN_17, E_MESSAGE_SUCCESS);
}


// Now display any outstanding auto-banned IP addresses
if($sql->db_Select("generic", "*", "gen_type='auto_banned' ORDER BY gen_datestamp DESC "))
{
	$abArray = $sql->db_getList();
	$message = FLALAN_15;
	foreach($abArray as $ab)
	{
		$message .= " - ".$ab['gen_ip'];
	}

	$message .= "<div class='right'>(<a href='".e_SELF."?dabl'>".FLALAN_16."</a>)</div>";
	$emessage->add($message);

}

$gen = new convert;
$fla_total = $sql->db_Count("generic", "(*)", "WHERE gen_type='failed_login'");
if(!$sql->db_Select("generic", "*", "gen_type='failed_login' ORDER BY gen_datestamp DESC LIMIT {$from},{$amount}"))
{
	$text = $emessage->render()."<div class='center'>".FLALAN_2."</div>";
}
else
{

	$faArray = $sql->db_getList('ALL', FALSE, FALSE);

	$text = "
		<form method='post' action='".e_SELF."' id='flaform' >
			<fieldset id='core-fla'>
				<legend class='e-hideme'>".ADLAN_146."</legend>
				<table cellpadding='0' cellspacing='0' class='adminlist'>
					<colgroup span='5'>
						<col style='width: 20%'></col>
						<col style='width: 40%'></col>
						<col style='width: 20%'></col>
						<col style='width: 10%'></col>
						<col style='width: 10%'></col>
					</colgroup>
					<thead>
						<tr>
							<th>".LAN_DATE."</th>
							<th>".FLALAN_7."</th>
							<th>".FLALAN_8."</th>
							<th class='center last'>
								".LAN_DELETE."<br/>
								".$frm->checkbox('check_all_del', 'jstarget:fladelete', false, array('id'=>false,'class'=>'checkbox toggle-all'))."
							</th>
							<th class='center last'>
								".LAN_BAN."<br/>
								".$frm->checkbox_toggle('check-all-ban', 'flaban')."
							</th>
						</tr>
					</thead>
					<tbody>
	";

	foreach($faArray as $fa)
	{
		extract($fa);//FIXME kill extract()
		
		$gen_chardata = str_replace(":::", "<br />", $e107->tp->toHTML($gen_chardata));
		$host = $e107->get_host_name(getenv($gen_ip));
		$text .= "
						<tr>
							<td>".$gen->convert_date($gen_datestamp, "forum")."</td>
							<td>".$gen_chardata."</td>
							<td>".$e107->ipDecode($fa['gen_ip'])."<br />{$host}</td>
							<td class='center middle autocheck e-pointer'>
								".$frm->checkbox('fladelete[]', $gen_id)."
							</td>
							<td class='center middle autocheck e-pointer'>
								".$frm->checkbox('flaban[]', $gen_id)."
							</td>
						</tr>
		";
	}

	$text .= "
					</tbody>
				</table>
				<div class='buttons-bar center'>
					".$frm->admin_button('delbanSubmit', FLALAN_10, 'delete', FLALAN_10, 'title=')."
				</div>
			</fieldset>
		</form>
	";

	$parms = $fla_total.",".$amount.",".$from.",".e_SELF.'?'."[FROM].".$amount;
	$nextprev = $tp->parseTemplate("{NEXTPREV={$parms}}");
	if ($nextprev)
		$text .= "<div class='nextprev-bar'>".$nextprev."</div>";




}

$e107->ns->tablerender(ADLAN_146, $emessage->render().$text);

require_once("footer.php");
/**
 * Handle page DOM within the page header
 *
 * @return string JS source
 */
function headerjs()
{
	require_once(e_HANDLER.'js_helper.php');
	$ret = "
		<script type='text/javascript'>
			if(typeof e107Admin == 'undefined') var e107Admin = {}

			/**
			 * OnLoad Init Control
			 */
			e107Admin.initRules = {
				'Helper': true,
				'AdminMenu': false
			}
		</script>
		<script type='text/javascript' src='".e_FILE_ABS."jslib/core/admin.js'></script>
	";

	return $ret;
}
