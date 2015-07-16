<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Plugin administration - newsfeeds
 *
 *
*/
require_once("../../class2.php");
if (!getperms("P") || !e107::isInstalled('newsfeed')) 
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
		$feed['newsfeed_name'] 			= $tp->toDB($_POST['newsfeed_name']);
		$feed['newsfeed_description'] 	= $tp->toDB($_POST['newsfeed_description']);
		$feed['newsfeed_image'] 		= $tp->toDB($_POST['newsfeed_image'])."::".intval($_POST['newsfeed_showmenu'])."::".intval($_POST['newsfeed_showmain']);
		$feed['newsfeed_url'] 			= $tp->toDB($_POST['newsfeed_url']);
		$feed['newsfeed_active'] 		= intval($_POST['newsfeed_active']);
		$feed['newsfeed_updateint'] 	= intval($_POST['newsfeed_updateint']);
		$feed['newsfeed_data'] 			= ''; 	// Start with blank data feed
		$feed['newsfeed_timestamp'] 	= 0;	// This should force an immediate update
		
		if (isset($_POST['createFeed']))
		{
			if ($sql->insert('newsfeed',$feed))
			{
				$admin_log->logArrayAll('NEWSFD_01', $feed);
				$mes->addSuccess(LAN_CREATED);
			}
			else
			{
				$mes->addError(LAN_CREATED_FAILED.': '.$sql->mySQLerror); 
			}
		}
		elseif (isset($_POST['updateFeed']))
		{
			$feed['WHERE'] = "newsfeed_id=".intval($_POST['newsfeed_id']);
			
			if($sql->update('newsfeed',$feed))
			{
				$admin_log->logArrayAll('NEWSFD_02', $feed);
				$mes->addSuccess(LAN_UPDATED);
			}
			else
			{
				$mes->addInfo(LAN_NO_CHANGE.': '.$sql->mySQLerror); 
			}
		}
		e107::getCache()->clear(NEWSFEED_LIST_CACHE_TAG);		// This should actually clear all the newsfeed data in one go
	} 
	else 
	{
		$mes->addError(LAN_REQUIRED_BLANK);
	}
}

$ns->tablerender($caption, $mes->render() . $text);

if($action == "delete") 
{
	$sql->db_Delete('newsfeed', 'newsfeed_id='.$id);
	e107::getLog()->add('NEWSFD_03','ID: '.$id,E_LOG_INFORMATIVE,'');
	$mes->addSuccess(LAN_DELETED);
}

$ns->tablerender($caption, $mes->render() . $text);


if($headline_total = $sql->db_Select("newsfeed"))
{
	$nfArray = $sql->rows();

	$text = "
	<table class='table table-striped'>
	<colgroup>
		<col style='width: 5%; text-align: center;' />
		<col style='width: 40%;' />
		<col style='width: 10%; text-align: center;' />
		<col style='width: 25%; text-align: center;' />
		<col style='width: 10%; text-align: center;' />
	</colgroup>
	<thead>
	<tr>
		<th>".LAN_ID."</th>
		<th>".LAN_NAME."</th>
		<th>".NFLAN_26."</th>
		<th>".NFLAN_12."</th>
		<th class='center options'>".LAN_OPTIONS."</th>
	</tr>
	</thead>\n";

	$active = array(NFLAN_13,NFLAN_14,NFLAN_20,NFLAN_21);

	foreach($nfArray as $newsfeed)
	{
		extract($newsfeed); // FIXME

		$text .= "
		<tr>
			<td>$newsfeed_id</td>
			<td><a href='$newsfeed_url' rel='external'>$newsfeed_name</a></td>
			<td>".($newsfeed_updateint ? $newsfeed_updateint : "3600")."</td>
			<td>".$active[$newsfeed_active]."</td>
			<td>
				<a class='btn btn-default btn-large' href='".e_SELF."?edit.".$newsfeed_id."'>".ADMIN_EDIT_ICON."</a>
	            <a class='btn btn-default btn-large action delete' href='".e_SELF."?delete.".$newsfeed_id."' rel='no-confirm' title='".LAN_CONFDELETE."'>".ADMIN_DELETE_ICON."</a>
			</td>
		</tr>";
	}

	$text .= "</table>";
}
else
{
	$mes->addInfo(NFLAN_41);
}

$ns->tablerender(NFLAN_07, $mes->render(). $text);

if($action == "edit")
{
	if($sql->select("newsfeed", "*", "newsfeed_id=$id"))
	{
		$row = $sql->fetch();
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
	<table class='table'>
	<colgroup>
		<col class='col-label' />
		<col style='col-control' />
	</colgroup>
	<tr>
		<td>".LAN_NAME."</td>
		<td>".$frm->text('newsfeed_name', $newsfeed_name, '200')."</td>
	</tr>
	
	<tr>
		<td>".LAN_URL."</td>
		<td>".$frm->text('newsfeed_url', $newsfeed_url, '250', 'size=xxlarge')."<span class='field-help'>".NFLAN_10."</span></td>
	</tr>
	<tr>
		<td>".NFLAN_11."</td>
		<td>".$frm->text('newsfeed_image', $newsfeed_image, '200') /* TODO imagepicker? */."<span class='field-help'>".NFLAN_17."</span></td>
	</tr>
	<tr>
		<td>".LAN_DESCRIPTION."</td>
		<td>".$frm->text('newsfeed_description', $newsfeed_description, '200')."<span class='field-help'>".NFLAN_37."</span></td>
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
		$frm->radio('newsfeed_active', $array, ($newsfeed_active ? $newsfeed_active : 0), true, NFLAN_22)."
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