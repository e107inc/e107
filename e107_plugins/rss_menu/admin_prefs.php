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
 * $Source: /cvs_backup/e107_0.8/e107_plugins/rss_menu/admin_prefs.php,v $
 * $Revision: 1.8 $
 * $Date: 2009-11-18 02:03:35 $
 * $Author: marj_nl_fr $
 */

/*
Notes:
- array_flip method deprecated for delete item detection.
- using form handler is deprecated and present only for backwards compatibility.
- using generic terms like EDIT and DELETE in Language file is deprecated, use LAN_EDIT etc. instead.
- using terms like created, update, options etc..deprecated should use built in terms.
- generic admin icons used. ADMIN_ICON_EDIT etc.
- using $caption = "whatever", is unneccessary.
*/
require_once("../../class2.php");
if(!getperms("P") || !e107::isInstalled('rss_menu'))
{ 
	header("location:".e_BASE."index.php"); 
}

include_lan(e_PLUGIN."rss_menu/languages/".e_LANGUAGE."_admin_rss_menu.php");
require_once(e_ADMIN."auth.php");

$imagedir = e_IMAGE."admin_images/";
require_once(e_PLUGIN.'rss_menu/rss_shortcodes.php');
$rss = new rss;

global $tp;

// Delete entry
if(isset($_POST['delete']))
{
	$d_idt = array_keys($_POST['delete']);
	$message = ($sql -> db_Delete("rss", "rss_id=".intval($d_idt[0]))) ? LAN_DELETED : LAN_DELETED_FAILED;
	$admin_log->log_event('RSS_01','ID: '.intval($d_idt[0]).' - '.$message,E_LOG_INFORMATIVE,'');
    $e107cache->clear("rss");
}

// Create rss feed
if(isset($_POST['create_rss']))
{
	$message = $rss -> dbrss("create");
}

// Update rss feed
if(isset($_POST['update_rss']))
{
	$message = $rss -> dbrss("update");
}

// Import rss feed
if(isset($_POST['import_rss']))
{
	$message = $rss -> dbrssimport();
}

// Update_limit
if(isset($_POST['update_limit']))
{
	$message = $rss -> dbrsslimit();
}

// Update options
if(isset($_POST['updatesettings']))
{
	$message = $rss->dboptions();
}

// Config check
if($rss->file_check())
{
	$message = RSS_LAN_ERROR_2; // Space found in file.
}

// Render message
if(isset($message))
{
	$rss->show_message('', $message);
}

// Get template
if (is_readable(THEME."rss_template.php")) 
{
	require_once(THEME."rss_template.php");
} 
else 
{
	require_once(e_PLUGIN."rss_menu/rss_template.php");
}

// Listing
if(e_QUERY)
{
	$qs = explode(".", e_QUERY);
}
$action = varset($qs[0],'list');
$field = varset($qs[1], '');
$feedID = intval(varset($qs[2], 0));

switch ($action)
{
	case 'create' :
		if ($_POST)
		{	// List
			$rss -> rssadminlist();
		}
		else
		{	// Create
			$rss -> rssadmincreate($field, $feedID);
		}
		break;
	case 'import' :
		$rss -> rssadminimport();
		break;
	case 'options' :
		$rss -> rssadminoptions();
		break;
	case 'r3' :
		$rss->show_message('', RSS_LAN_ADMIN_31);	// Intentionally fall straight through after showing message
	case 'list' :
	default :
		$rss -> rssadminlist();
}

require_once(e_ADMIN."footer.php");

// ##### Display options --------------------------------------------------------------------------
function admin_prefs_adminmenu()
{
	global $sql, $qs;

	$act = varset($qs[0], 'list');

	$var['list']['text']		= RSS_LAN_ADMINMENU_2;
	$var['list']['link']		= e_SELF."?list";

	$var['create']['text']		= LAN_CREATE;
	$var['create']['link']		= e_SELF."?create";

	$var['import']['text']		= RSS_LAN_ADMINMENU_4;
	$var['import']['link']		= e_SELF."?import";

	$var['options']['text']		= LAN_OPTIONS;
	$var['options']['link']		= e_SELF."?options";

	show_admin_menu(RSS_LAN_ADMINMENU_1, $act, $var);

}
// ##### End --------------------------------------------------------------------------------------

class rss
{
	// Check for config
	function file_check()
	{
		$arrays = file_get_contents(e_BASE."e107_config.php");
		$arrays2 = file_get_contents(e_PLUGIN."rss_menu/languages/".e_LANGUAGE."_admin_rss_menu.php");
		if($arrays[0] != "<" || $arrays2[0] != "<")
		{
			return TRUE;
		}
		return FALSE;
	}

	// Admin : list : existing rss feeds
	function rssadminlist()
	{
		global $ns, $sql, $tp, $field, $sort, $rss_shortcodes, $row, $RSS_ADMIN_LIST_HEADER, $RSS_ADMIN_LIST_TABLE, $RSS_ADMIN_LIST_FOOTER;

        $fieldstag = array('id'=>'rss_id','path'=>'rss_path','name'=>'rss_name','url'=>'rss_url','limit'=>'rss_limit');
		$order = (isset($fieldstag[$field])) ? "ORDER BY ".$fieldstag[$field]." ".$sort : "ORDER BY rss_id";

        $query = "SELECT * FROM #rss ".$order;
		if(!$sql->db_Select_gen($query))
		{
			$this->show_message(LAN_ERROR, RSS_LAN_ERROR_3);
		}
		else
		{
			$text = $tp -> parseTemplate($RSS_ADMIN_LIST_HEADER, FALSE, $rss_shortcodes);
			while($row=$sql->db_Fetch())
			{
				$text .= $tp -> parseTemplate($RSS_ADMIN_LIST_TABLE, FALSE, $rss_shortcodes);
			}
			$text .= $tp -> parseTemplate($RSS_ADMIN_LIST_FOOTER, FALSE, $rss_shortcodes);
			$ns->tablerender(RSS_LAN_ADMIN_1, $text);
		}
	}

	// Create or edit - put up a form
	function rssadmincreate($action, $id=0)
	{
		global $ns, $sql, $tp, $rss_shortcodes, $row, $RSS_ADMIN_CREATE_TABLE;

		if (($action == "edit") && $id )
		{
			if(!$sql -> db_Select("rss", "*", "rss_id=".$id))
			{
				$this->show_message(LAN_ERROR, RSS_LAN_ERROR_5);
			}
			else
			{
				$row = $sql -> db_Fetch();
				$row['rss_name']	= $tp -> toForm($row['rss_name']);
				$row['rss_path']	= $tp -> toForm($row['rss_path']);
				$row['rss_url']		= $tp -> toForm($row['rss_url']);
				$row['rss_text']	= $tp -> toForm($row['rss_text']);
			}
		}
		$text = $tp -> parseTemplate($RSS_ADMIN_CREATE_TABLE, FALSE, $rss_shortcodes);
		$ns->tablerender(RSS_LAN_ADMIN_10, $text);
	}

	// Import - put up the list of possible feeds to import
	function rssadminimport()
	{
		global $sql, $ns, $i, $tp, $rss_shortcodes, $feed, $pref;
		global $RSS_ADMIN_IMPORT_HEADER, $RSS_ADMIN_IMPORT_TABLE, $RSS_ADMIN_IMPORT_FOOTER;

		$sqli = new db;
		$feedlist = array();

		// News
		$feed['name']		= ADLAN_0;
		$feed['url']		= 'news';	// The identifier for the rss feed url
		$feed['topic_id']	= '';		// The topic_id, empty on default (to select a certain category)
		$feed['path']		= 'news';	// This is the plugin path location
		$feed['text']		= RSS_PLUGIN_LAN_7;
		$feed['class']		= '0';
		$feed['limit']		= '9';
		$feedlist[]			= $feed;

		// News categories
		if($sqli -> db_Select("news_category", "*","category_id!='' ORDER BY category_name "))
		{
			while($rowi = $sqli -> db_Fetch())
			{
				$feed['name']		= ADLAN_0.' > '.$rowi['category_name'];
				$feed['url']		= 'news';
				$feed['topic_id']	= $rowi['category_id'];
				$feed['path']		= 'news';
				$feed['text']		= RSS_PLUGIN_LAN_10.' '.$rowi['category_name'];
				$feed['class']		= '0';
				$feed['limit']		= '9';
			//	$feed['exclude_class'] = '';
				$feedlist[]			= $feed;
			}
		}

/*		// Download
		$feed['name']		= ADLAN_24;
		$feed['url']		= 'download';
		$feed['topic_id']	= '';
		$feed['path']		= 'download';
		$feed['text']		= RSS_PLUGIN_LAN_8;
		$feed['class']		= '0';
		$feed['limit']		= '9';
		$feedlist[]			= $feed;

		// Download categories
		if($sqli -> db_Select("download_category", "*","download_category_id!='' ORDER BY download_category_order "))
		{
			while($rowi = $sqli -> db_Fetch())
			{
				$feed['name']		= ADLAN_24.' > '.$rowi['download_category_name'];
				$feed['url']		= 'download';
				$feed['topic_id']	= $rowi['download_category_id'];
				$feed['path']		= 'download';
				$feed['text']		= RSS_PLUGIN_LAN_11.' '.$rowi['download_category_name'];
				$feed['class']		= '0';
				$feed['limit']		= '9';
				$feedlist[]			= $feed;
			}
		}
*/
		// Comments
		$feed['name']		= RSS_PLUGIN_LAN_14;
		$feed['url']		= 'comments';
		$feed['topic_id']	= '';
		$feed['path']		= 'comments';
		$feed['text']		= RSS_PLUGIN_LAN_9;
		$feed['class']		= '0';
		$feed['limit']		= '9';
		$feedlist[]			= $feed;

		// Plugin rss feed, using e_rss.php in plugin folder
		$plugin_feedlist = array();
		foreach($pref['e_rss_list'] as $val)
		{
			if (is_readable(e_PLUGIN.$val."/e_rss.php")) 
			{
				require_once(e_PLUGIN.$val."/e_rss.php");
				$plugin_feedlist = $eplug_rss_feed;
			}
		}

		$feedlist = array_merge($feedlist, $plugin_feedlist);

		$render=FALSE;
		$i=0;
		$text = $RSS_ADMIN_IMPORT_HEADER;
		foreach($feedlist as $k=>$feed)
		{
			$feed['topic_id']		= $tp -> toDB($feed['topic_id']);
			$feed['url']			= $tp -> toDB($feed['url']);

			// Check if feed is not yet present
			if(!$sql -> db_Select("rss", "*", "rss_path='".$feed['path']."' AND rss_url='".$feed['url']."' AND rss_topicid='".$feed['topic_id']."' "))
			{
				$render=TRUE;
				$text .= $tp -> parseTemplate($RSS_ADMIN_IMPORT_TABLE, FALSE, $rss_shortcodes);
				$i++;
			}
		}
		$text .= $tp -> parseTemplate($RSS_ADMIN_IMPORT_FOOTER, FALSE, $rss_shortcodes);

		if(!$render)
		{
			$this->show_message(RSS_LAN_ADMIN_11, RSS_LAN_ERROR_6);
		}
		else
		{
			$ns->tablerender(RSS_LAN_ADMIN_11, $text);
		}
	}

	// Options - display form
	function rssadminoptions()
	{
		global $ns, $sql, $tp, $rss_shortcodes, $row, $RSS_ADMIN_OPTIONS_TABLE;

		$text = $tp -> parseTemplate($RSS_ADMIN_OPTIONS_TABLE, FALSE, $rss_shortcodes);
		$ns->tablerender(LAN_OPTIONS, $text);
		return;
	}

	// Render message
	function show_message($caption='', $text='')
	{
		global $ns;
		$ns -> tablerender($caption, "<div style='text-align:center'><b>$text</b></div>");
	}

	// Db:create/update
	function dbrss($mode='create')
	{
		global $sql, $ns, $tp, $e107cache, $admin_log;

		if($_POST['rss_name'] && $_POST['rss_url'] && $_POST['rss_path'])
		{
			$rssVals = array();
			$rssVals['rss_name']		= $tp -> toDB(trim($_POST['rss_name']));
			$rssVals['rss_url']			= $tp -> toDB($_POST['rss_url']);
			$rssVals['rss_topicid']		= $tp -> toDB($_POST['rss_topicid']);
			$rssVals['rss_path']		= $tp -> toDB($_POST['rss_path']);
			$rssVals['rss_text']		= $tp -> toDB($_POST['rss_text']);
			$rssVals['rss_class']		= (intval($_POST['rss_class']) ? intval($_POST['rss_class']) : '0');
			$rssVals['rss_limit']		= intval($_POST['rss_limit']);
		//	$rssVals['rss_exclude_class'] = intval($_POST['rss_exclude_class']);
			if(isset($_POST['rss_datestamp']) && $_POST['rss_datestamp']!='')
			{
				$rssVals['rss_datestamp'] = intval($_POST['rss_datestamp']);
			}
			else
			{
				$rssVals['rss_datestamp'] = time();
			}

			switch ($mode)
			{
				case 'create' :
					$message = ($sql -> db_Insert('rss',$rssVals)) ? LAN_CREATED : LAN_CREATED_FAILED;
					$admin_log->logArrayAll('RSS_02',$rssVals, $message);
					$e107cache->clear('rss');
					break;

				case  'update' :
					$message = ($sql -> db_UpdateArray('rss', $rssVals, " WHERE rss_id = ".intval($_POST['rss_id']))) ? LAN_UPDATED : LAN_UPDATED_FAILED;
					$admin_log->logArrayAll('RSS_03',$rssVals, $message);
					$e107cache->clear('rss');
					break;
			}
		}
		else
		{
			$message = RSS_LAN_ERROR_7;
		}
		return $message;
	}

	// Import rss feeds
	function dbrssimport()
	{
		global $sql, $tp, $admin_log;

		foreach($_POST['importid'] as $key=>$value)
		{
			$rssVals = array();
			$rssVals['rss_topicid']		= $tp -> toDB(varset($_POST['topic_id'][$key], ''));
			$rssVals['rss_url']			= $tp -> toDB(varset($_POST['url'][$key], ''));
			$rssVals['rss_path']		= $tp -> toDB(varset($_POST['path'][$key], ''));
			$rssVals['rss_name']		= $tp -> toDB(varset($_POST['name'][$key], ''));
			$rssVals['rss_text']		= $tp -> toDB(varset($_POST['text'][$key], ''));
			$rssVals['rss_datestamp']	= time();
			$rssVals['rss_class']		= intval(varset($_POST['class'][$key], '0'));
			$rssVals['rss_limit']		= intval(varset($_POST['limit'][$key], '0'));

			$sql -> db_Insert("rss", $rssVals);
			$admin_log->logArrayAll('RSS_04',$rssVals);
		}
		$message = count($_POST['importid'])." ".RSS_LAN_ADMIN_18;
		return $message;
	}

	function dbrsslimit()
	{
		global $sql, $tp, $admin_log;
		
		$limitVals = array();
		foreach($_POST['limit'] as $key=>$value)
		{
			$key = intval($key);
			$value = intval($value);
			$limitVals[$key] = $value;
			$sql -> db_Update("rss", "rss_limit = ".$value." WHERE rss_id = ".$key);
		}
		$admin_log->logArrayAll('RSS_05',$limitVals);
		header("location:".e_SELF."?r3");
	}

	// Update options
	function dboptions()
	{
		global $tp, $pref, $admin_log;

		$temp = array();
		$temp['rss_othernews'] = $_POST['rss_othernews'];
		$temp['rss_summarydiz'] = $_POST['rss_summarydiz'];
		$temp['rss_shownewsimage']	= $_POST['rss_shownewsimage'];
		if ($admin_log->logArrayDiffs($temp, $pref, 'RSS_06'))
		{
			save_prefs();		// Only save if changes
			return LAN_SAVED;
		}
		else
		{
			return RSS_LAN_ADMIN_28;
		}
	}
} // End class rss
?>