<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/download/admin_download.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

$eplug_admin = true;
define('DOWNLOAD_DEBUG',FALSE);

require_once("../../class2.php");
if (!getperms("P") || !e107::isInstalled('download'))
{
	e107::redirect('admin');
	exit() ;
}


e107::lan('download','download'); // e_PLUGIN.'download/languages/'.e_LANGUAGE.'/download.php'
e107::lan('download', 'admin', true); // e_PLUGIN.'download/languages/'.e_LANGUAGE.'/admin_download.php'



// require_once(e_PLUGIN.'download/handlers/adminDownload_class.php');
require_once(e_PLUGIN.'download/handlers/download_class.php');
require_once(e_HANDLER.'upload_handler.php');
require_once(e_HANDLER.'xml_class.php');
require_once(e_HANDLER."form_handler.php");
require_once(e_HANDLER."ren_help.php");
//require_once(e_HANDLER."calendar/calendar_class.ph_");
//$cal = new DHTML_Calendar(true);
//$gen = new convert();



$e_sub_cat = 'download';
require_once(e_HANDLER."form_handler.php");
require_once(e_HANDLER."userclass_class.php");
require_once(e_HANDLER."file_class.php");

$fl = new e_file;
$pref = e107::getPref(); // legacy, remove all globals
$download = new download();
// $adminDownload = new adminDownload();


/*

$rs = new form;
$subAction = '';
if (e_QUERY)
{
	$tmp = explode(".", e_QUERY);
	$action = $tmp[0];
	$subAction = varset($tmp[1],'');
	$id = varset($tmp[2],'');
	$from = varset($tmp[3], 0);
	$maintPage = varset($tmp[4], '');
	unset($tmp);
}

// $adminDownload->observer();

require_once (e_HANDLER.'message_handler.php');
$emessage = &eMessage::getInstance();



$from = ($from ? $from : 0);
$amount = varset($pref['download_view'], 50);

if (isset($_POST))
{
	$e107cache->clear("download_cat");
}*/



/*
if (isset($_POST['submit_download']))
{
	$adminDownload->submit_download($subAction, $id);
	$action = "main";
	unset($subAction, $id);
}
*/

if (isset($_POST['update_catorder']))
{
	foreach($_POST['catorder'] as $key=>$order)
	{
		if (is_numeric($_POST['catorder'][$key]))
		{
			$sql -> db_Update("download_category", "download_category_order='".intval($order)."' WHERE download_category_id='".intval($key)."'");
		}
	}
	e107::getLog()->add('DOWNL_08',implode(',',array_keys($_POST['catorder'])),E_LOG_INFORMATIVE,'');
	$ns->tablerender("", "<div style='text-align:center'><b>".LAN_UPDATED."</b></div>");
}
/*

if (isset($_POST['updatedownlaodoptions']))
{
	unset($temp);
	$temp['download_php'] = $_POST['download_php'];
	$temp['download_view'] = $_POST['download_view'];
	$temp['download_sort'] = $_POST['download_sort'];
	$temp['download_order'] = $_POST['download_order'];
	$temp['mirror_order'] = $_POST['mirror_order'];
	$temp['recent_download_days'] = $_POST['recent_download_days'];
	$temp['agree_flag'] = $_POST['agree_flag'];
	$temp['download_email'] = $_POST['download_email'];
	$temp['agree_text'] = $tp->toDB($_POST['agree_text']);
	$temp['download_denied'] = $tp->toDB($_POST['download_denied']);
	$temp['download_reportbroken'] = $_POST['download_reportbroken'];
	if ($_POST['download_subsub']) $temp['download_subsub'] = '1'; else $temp['download_subsub'] = '0';
	if ($_POST['download_incinfo']) $temp['download_incinfo'] = '1'; else $temp['download_incinfo'] = '0';
	if ($admin_log->logArrayDiffs($temp, $pref, 'DOWNL_01'))
	{
		save_prefs();

		// e107::getMessage()->add(DOWLAN_65);

	}
	else
	{
		// e107::getMessage()->add(DOWLAN_8);
	}
}

*/

if (isset($_POST['updateuploadoptions']))
{
	unset($temp);
	$temp['upload_enabled'] = intval($_POST['upload_enabled']);
	$temp['upload_maxfilesize'] = $_POST['upload_maxfilesize'];
	$temp['upload_class'] = intval($_POST['upload_class']);
	if ($admin_log->logArrayDiffs($temp, $pref, 'DOWNL_02'))
	{
		save_prefs();
		$message = DOWLAN_65;
	}
	else
	{
		$message = DOWLAN_8;
	}
}

$targetFields = array('gen_datestamp', 'gen_user_id', 'gen_ip', 'gen_intdata', 'gen_chardata');		// Fields for download limits

if (!empty($_POST['addlimit']))
{
	if ($sql->select('generic','gen_id',"gen_type = 'download_limit' AND gen_datestamp = ".intval($_POST['newlimit_class'])))
	{
		$message = DOWLAN_116;
	}
	else
	{
		$vals = array();
		$vals['gen_type'] = 'download_limit';
		foreach(array('newlimit_class','new_bw_num','new_bw_days','new_count_num','new_count_days') as $k => $lName)
		{
			$vals[$targetFields[$k]] = intval($_POST[$lName]);
		}
		$valString = implode(',',$vals);
		if ($sql->insert('generic',$vals))
		{
			$message = DOWLAN_117;
			e107::getLog()->add('DOWNL_09',$valString,E_LOG_INFORMATIVE,'');
		}
		else
		{
			$message = DOWLAN_118;
		}
		unset($vals);
	}
}


if (isset($_POST['updatelimits']))
{
	
	//if ($pref['download_limits'] != $_POST['download_limits'])
	{
		$tmp = ($_POST['download_limits'] == 'on') ? 1 : 0;
		if ($pref['download_limits'] != $tmp)
		{
			$pref['download_limits'] = $tmp;
			e107::getConfig()->set('download_limits', $tmp)->save(false);
			$message .= DOWLAN_126."<br/>";
		}
	}
	foreach(array_keys($_POST['count_num']) as $idLim)
	{
		$idLim = intval($idLim);
		if (!$_POST['count_num'][$idLim] && !$_POST['count_days'][$idLim] && !$_POST['bw_num'][$idLim] && !$_POST['bw_days'][$idLim])
		{
			//All entries empty - Remove record
			if ($sql->db_Delete('generic',"gen_id = {$idLim}"))
			{
				$message .= $idLim." - ".DOWLAN_119."<br/>";
				e107::getLog()->add('DOWNL_11','ID: '.$idLim,E_LOG_INFORMATIVE,'');
			}
			else
			{
				$message .= $idLim." - ".DOWLAN_120."<br/>";
			}
		}
		else
		{
			$vals = array();
			foreach(array('bw_num','bw_days','count_num','count_days') as $k => $lName)
			{
				$vals[$targetFields[$k+1]] = intval($_POST[$lName][$idLim]);
			}
			$vals['WHERE'] = "gen_id = ".$idLim;

			$sql->update('generic',$vals);
			$valString = implode(',',$vals);
			e107::getLog()->add('DOWNL_10',$idLim.', '.$valString,E_LOG_INFORMATIVE,'');
			$message .= $idLim." - ".DOWLAN_121."<br/>";
			unset($vals);
		}
	}
}

new plugin_download_admin();
require_once(e_ADMIN."auth.php");
//download/includes/admin.php is auto-loaded. 
 e107::getAdminUI()->runPage();
require_once(e_ADMIN."footer.php");
exit;


