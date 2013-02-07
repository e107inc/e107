<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Plugin administration - newsfeeds
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/newsfeed/admin_config.php,v $
 * $Revision$
 * $Date$
 * $Author$
 *
*/
require_once("../../class2.php");
if (!getperms("P") || !plugInstalled('newsfeed')) 
{
	header("location:".e_BASE."index.php");
	exit;
}

require_once(e_ADMIN."auth.php");

define('NEWSFEED_LIST_CACHE_TAG', 'nomd5_newsfeeds');

$frm = e107::getForm();
$mes = e107::getMessage();

if (e_QUERY) 
{
	list($action, $id) = explode(".", e_QUERY);
	$id = intval($id);
}
else
{
	$action = FALSE;
	$id = FALSE;
}

if (isset($_POST['createFeed']) || isset($_POST['updateFeed']))
{
	if ($_POST['newsfeed_url'] && $_POST['newsfeed_name']) 
	{
		$feed['newsfeed_name'] = $tp -> toDB($_POST['newsfeed_name']);
		$feed['newsfeed_description'] = $tp -> toDB($_POST['newsfeed_description']);
		$feed['newsfeed_image'] = $tp->toDB($_POST['newsfeed_image'])."::".intval($_POST['newsfeed_showmenu'])."::".intval($_POST['newsfeed_showmain']);
		$feed['newsfeed_url'] = $tp->toDB($_POST['newsfeed_url']);
		$feed['newsfeed_active'] = intval($_POST['newsfeed_active']);
		$feed['newsfeed_updateint'] = intval($_POST['newsfeed_updateint']);
		$feed['newsfeed_data'] = '';		// Start with blank data feed
		$feed['newsfeed_timestamp'] = 0;	// This should force an immediate update
		if (isset($_POST['createFeed']))
		{
			if ($sql->db_Insert('newsfeed',$feed))
			{
				$admin_log->logArrayAll('NEWSFD_01', $feed);
				$message = NFLAN_23;
				$mes->addSuccess(LAN_CREATED);
			}
			else
			{
				//$message = NFLAN_50.$sql->mySQLerror;
				$mes->addError(LAN_CREATED_FAILED.': '.$sql->mySQLerror); 
			}
		}
		elseif (isset($_POST['updateFeed']))
		{
			if ($sql->db_UpdateArray('newsfeed',$feed, " WHERE newsfeed_id=".intval($_POST['newsfeed_id'])))
			{
				$admin_log->logArrayAll('NEWSFD_02', $feed);
				//$message = NFLAN_25;
				$mes->addSuccess(LAN_UPDATED);
			}
			else
			{
				//$message = NFLAN_50.$sql->mySQLerror;
				$mes->addInfo(LAN_NO_CHANGE.': '.$sql->mySQLerror); 
			}
		}
		$e107->ecache->clear(NEWSFEED_LIST_CACHE_TAG);		// This should actually clear all the newsfeed data in one go
	} 
	else 
	{
		//$message = NFLAN_24;
		$mes->addError(LAN_REQUIRED_BLANK);
	}
}

$ns->tablerender($caption, $mes->render() . $text);

if($action == "delete") 
{
	$sql->db_Delete('newsfeed', 'newsfeed_id='.$id);
	$admin_log->log_event('NEWSFD_03','ID: '.$id,E_LOG_INFORMATIVE,'');
	//$message = NFLAN_40;
	$mes->addSuccess(LAN_DELETED);
}

/*
if (isset($message)) 
{
	$mes->addInfo($message);
	// $ns->tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}
*/
$ns->tablerender($caption, $mes->render() . $text);


if($headline_total = $sql->db_Select("newsfeed"))
{
	$nfArray = $sql -> db_getList();

	$text = "
	<table class='table adminlist'>
	<colgroup>
		<col style='width: 5%; text-align: center;' />
		<col style='width: 50%;' />
		<col style='width: 10%; text-align: center;' />
		<col style='width: 25%; text-align: center;' />
		<col style='width: 10%; text-align: center;' />
	</colgroup>
	
	<tr>
		<td>".LAN_ID."</td>
		<td>".LAN_NAME."</td>
		<td>".NFLAN_26."</td>
		<td>".NFLAN_12."</td>
		<td>".LAN_OPTIONS."</td>
	</tr>\n";

	$active = array(NFLAN_13,NFLAN_14,NFLAN_20,NFLAN_21);

	foreach($nfArray as $newsfeed)
	{
		extract($newsfeed);

		$text .= "
		<tr>
			<td>$newsfeed_id</td>
			<td><a href='$newsfeed_url' rel='external'>$newsfeed_name</a></td>
			<td>".($newsfeed_updateint ? $newsfeed_updateint : "3600")."</td>
			<td>".$active[$newsfeed_active]."</td>
			<td><a href='".e_SELF."?edit.".$newsfeed_id."'>".ADMIN_EDIT_ICON."</a>&nbsp;<a href='".e_SELF."?delete.".$newsfeed_id."'>".ADMIN_DELETE_ICON."</a></td>
		</tr>\n";
	}

	$text .= "</table>";
}
else
{
	$mes->addInfo(NFLAN_41);
	// $text = NFLAN_41;
}
$ns->tablerender(NFLAN_07, $mes->render(). $text);

if($action == "edit")
{
	if($sql->db_Select("newsfeed", "*", "newsfeed_id=$id"))
	{
		$row = $sql->db_Fetch();
		extract($row); // FIX
		list($newsfeed_image, $newsfeed_showmenu, $newsfeed_showmain) = explode("::", $newsfeed_image);
	}
}
else
{
	unset($newsfeed_showmenu, $newsfeed_showmain, $newsfeed_name, $newsfeed_url, $newsfeed_image, $newsfeed_description, $newsfeed_updateint, $newsfeed_active);
}

$text = "
<form method='post' action='".e_SELF."'>\n
<table class='table adminform'>
<colgroup>
	<col class='col-label' />
	<col style='col-control' />
</colgroup>

<tr>
	<td>".LAN_NAME."</td>
	<td><input class='tbox' type='text' name='newsfeed_name' size='80' value='$newsfeed_name' maxlength='200' /></td>
</tr>

<tr>
	<td>".LAN_URL."</td>
	<td><input class='tbox' type='text' name='newsfeed_url' size='80' value='$newsfeed_url' maxlength='250' /><span class='field-help'>".NFLAN_10."</span></td>
</tr>

<tr>
	<td>".NFLAN_11."</td>
	<td><input class='tbox' type='text' name='newsfeed_image' size='80' value='$newsfeed_image' maxlength='200' /><span class='field-help'>".NFLAN_17."</span></td>
</tr>

<tr>
	<td>".LAN_DESCRIPTION."</td>
	<td><input class='tbox' type='text' name='newsfeed_description' size='80' value='$newsfeed_description' maxlength='200' /><span class='field-help'>".NFLAN_37."</span></td>
</tr>

<tr>
	<td>".NFLAN_18."</td>
	<td>".$frm->number('newsfeed_updateint',($newsfeed_updateint ? $newsfeed_updateint : 3600),5)."<span class='field-help'>".NFLAN_19."</span></td>
</tr>

<tr>
	<td>".NFLAN_12."</td>
	<td>"; 
	$array = array(NFLAN_13,NFLAN_14,NFLAN_20,NFLAN_21);

	$text .= 
	$frm->radio_multi('newsfeed_active', $array, ($newsfeeed_active ? $newsfeeed_active : 0), true, NFLAN_22)."
	</td>
</tr>

<tr>
	<td>".NFLAN_45."</td>
	<td>".$frm->number('newsfeed_showmenu', $newsfeed_showmenu ,5)."<span class='field-help'>".NFLAN_47."</span></td>
</tr>

<tr>
	<td>".NFLAN_46."</td>
	<td>".$frm->number('newsfeed_showmain', $newsfeed_showmain ,5)."<span class='field-help'>".NFLAN_47."</span></td>
</tr>

</table>

<div class='buttons-bar center'>
	".$frm->admin_button(($action == "edit" ? "updateFeed" : "createFeed"),($action == "edit" ? LAN_UPDATE : LAN_CREATE),'update')."
</div>
	".($action == "edit" ? "<input type='hidden' name='newsfeed_id' value='$newsfeed_id' />" : "")."
</form>
";

$ns->tablerender(NFLAN_09, $mes->render() . $text);

require_once(e_ADMIN."footer.php");
?>