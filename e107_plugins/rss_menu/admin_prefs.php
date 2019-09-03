<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2016 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
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
	e107::redirect('admin');
	exit;
}


e107::includeLan(e_PLUGIN."rss_menu/languages/".e_LANGUAGE."_admin_rss_menu.php");


class rss_admin extends e_admin_dispatcher
{

	protected $modes = array(	
	
		'main'	=> array(
			'controller' 	=> 'rss_ui',
			'path' 			=> null,
			'ui' 			=> 'rss_form_ui',
			'uipath' 		=> null
		),

	);	
	
	
	protected $adminMenu = array(

		'main/list'			=> array('caption'=> LAN_MANAGE, 'perm' => 'P'),
		'main/import'		=> array('caption'=> LAN_IMPORT, 'perm' => 'P'),

		'main/prefs' 		=> array('caption'=> LAN_PREFS, 'perm' => 'P'),
		/*
		'main/custom'		=> array('caption'=> 'Custom Page', 'perm' => 'P')
	*/	

	);

	protected $adminMenuAliases = array(
		'main/edit'	=> 'main/list'				
	);	
	
	protected $menuTitle = 'RSS';


	function init()
	{
		if(E107_DEBUG_LEVEL > 0)
		{
			$this->adminMenu['main/create'] = array('caption'=> LAN_CREATE, 'perm' => 'P');
		}
	}
}


//TODO - Use this .. .				
class rss_ui extends e_admin_ui
{
			
		protected $pluginTitle		= 'RSS';
		protected $pluginName		= 'core';
		protected $table			= 'rss';
		protected $pid				= 'rss_id';
		protected $perPage 			= 10; 
			
		protected $fields 		= array (
		  'checkboxes'      =>   array ( 'title' => '',             'type' => null, 'data' => false, 'width' => '5%', 'thclass' => 'center', 'forced' => '1', 'class' => 'center', 'toggle' => 'e-multiselect',  ),
		  'rss_id'          =>   array ( 'title' => LAN_ID,         'data' => 'int', 'width' => '5%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'rss_name'        =>   array ( 'title' => LAN_TITLE,      'type' => 'text', 'data' => 'str', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'rss_path'        =>   array ( 'title' => LAN_PLUGIN_FOLDER,'type' => 'text', 'data' => 'str', 'readonly'=>1, 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'rss_url'         =>   array ( 'title' => LAN_URL,        'type' => 'method', 'data' => 'str', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'rss_topicid'     =>   array ( 'title' => RSS_LAN_ADMIN_12,'type' => 'text', 'data' => 'int', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),

		  'rss_text'        =>   array ( 'title' => LAN_DESCRIPTION,'type' => 'textarea', 'data' => 'str', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
		  'rss_datestamp'   =>   array ( 'title' => LAN_DATESTAMP,  'type' => 'datestamp', 'data' => 'int', 'readonly'=>true, 'width' => 'auto', 'filter' => true, 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'rss_class'       =>   array ( 'title' => LAN_VISIBILITY, 'type' => 'dropdown', 'data' => 'int', 'width' => 'auto', 'batch' => true, 'filter' => true, 'help' => '', 'readParms' => '', 'writeParms' => array('optArray'=> array(RSS_LAN_ADMIN_21,RSS_LAN_ADMIN_22,RSS_LAN_ADMIN_23),'size'=>'xlarge'), 'class' => 'left', 'thclass' => 'left',  ),
		  'rss_limit'       =>   array ( 'title' => LAN_LIMIT,      'type' => 'number', 'data' => 'int', 'inline'=>true, 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'options'         =>   array ( 'title' => LAN_OPTIONS,    'type' => null, 'data' => '', 'width' => '10%', 'thclass' => 'center last', 'class' => 'center last', 'forced' => '1',  ),
		);
		
		protected $fieldpref = array('checkboxes', 'rss_name','rss_url', 'rss_topicid', 'rss_limit', 'rss_class', 'options');


		protected $prefs = array(
			'rss_othernews'	   		=> array('title'=> RSS_LAN_ADMIN_13, 'type' => 'boolean', 'data' => 'int'),
			'rss_summarydiz' 		=> array('title'=> RSS_LAN_ADMIN_19, 'type' => 'boolean', 'data' => 'integer'),
			'rss_shownewsimage' 	=> array('title'=> RSS_LAN_ADMIN_33, 'type' => 'boolean', 'data' => 'int')
		);


	// optional
	public function init()
	{

		if(!empty($_POST['importid']))
		{
			$this->dbrssImport();
		}
	}


	function dbrssImport()
	{
		$sql = e107::getDb();
		$tp = e107::getParser();



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

			$sql->insert("rss", $rssVals);
			e107::getLog()->logArrayAll('RSS_04',$rssVals);
		}
		$message = count($_POST['importid'])." ".RSS_LAN_ADMIN_18;
		return $message;
	}


	public function importPage()
	{
		// Import - put up the list of possible feeds to import


			$sql = e107::getDb();
			$ns = e107::getRender();
			$mes = e107::getMessage();
			$tp = e107::getParser();
			$frm = e107::getForm();

		global $i,$rss_shortcodes, $feed, $pref;

		require_once(e_PLUGIN.'rss_menu/rss_shortcodes.php');

		if(!isset($RSS_ADMIN_IMPORT_HEADER))
		{
			$RSS_ADMIN_IMPORT_HEADER = "
			<form action='".e_SELF."' id='imlistform' method='post' >
			<table class='table table-striped adminlist'>
			<thead>
			<tr>
				<th class='center' style='width:5%'>".LAN_SELECT."</td>
				<th>".LAN_NAME."</td>
				<th>".LAN_PLUGIN_FOLDER."</td>

				<th>".LAN_URL."</td>
				<th>".RSS_LAN_ADMIN_12."</td>
			</tr>
			</thead><tbody>";
				}
				if(!isset($RSS_ADMIN_IMPORT_TABLE))
				{
					$RSS_ADMIN_IMPORT_TABLE = "
			<tr>
				<td class='first center'>{RSS_ADMIN_IMPORT_CHECK}</td>
					<td>{RSS_ADMIN_IMPORT_NAME} - {RSS_ADMIN_IMPORT_TEXT}</td>
				<td>{RSS_ADMIN_IMPORT_PATH}</td>

				<td>{RSS_ADMIN_IMPORT_URL}</td>
				<td>{RSS_ADMIN_IMPORT_TOPICID}</td>
			</tr>";
				}

				if(!isset($RSS_ADMIN_IMPORT_FOOTER))
				{
					$RSS_ADMIN_IMPORT_FOOTER = "</tbody>
			</table>
			<div class='buttons-bar center'>
				".$frm->admin_button('import_rss',LAN_ADD,'submit')."
			</div>
			</form>
			";
		}




		//	global $RSS_ADMIN_IMPORT_HEADER, $RSS_ADMIN_IMPORT_TABLE, $RSS_ADMIN_IMPORT_FOOTER;

			$sqli = new db;
			$feedlist = array();

			// @see e107_plugins/news/e_rss.php
			/*
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
			}*/

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


			//
			// Comments
			$feed['name']		= LAN_COMMENTS;
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
				$eplug_rss_feed = array();
				if (is_readable(e_PLUGIN.$val."/e_rss.php"))
				{
					require_once(e_PLUGIN.$val."/e_rss.php");

					$className = $val."_rss";
					$data = false;

					if(!$data = e107::callMethod($className,'config'))
					{
						$data = $eplug_rss_feed;
					}

					foreach($data as $v)
					{
						$v['path'] = $val;
						array_push($plugin_feedlist,$v);
					}

				}
			}

			$feedlist = array_merge($feedlist, $plugin_feedlist);

//		print_a($feedlist);

			$render=FALSE;
			$i=0;
			$text = $RSS_ADMIN_IMPORT_HEADER;
			foreach($feedlist as $k=>$feed)
			{
				$feed['topic_id']		= $tp -> toDB($feed['topic_id']);
				$feed['url']			= $tp -> toDB($feed['url']);

				// Check if feed is not yet present
				if(!$sql->select("rss", "*", "rss_path='".$feed['path']."' AND rss_url='".$feed['url']."' AND rss_topicid='".$feed['topic_id']."' "))
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
		//		$ns->tablerender(RSS_LAN_ADMIN_11, $mes->render(). $text);

				return $text;
			}
		}

			
}
				


class rss_form_ui extends e_admin_form_ui
{

	
	// Custom Method/Function 
	function rss_url($curVal,$mode)
	{



		switch($mode)
		{
			case 'read': // List Page

				$type = $this->getController()->getListModel()->get('rss_type');
				$topic = $this->getController()->getListModel()->get('rss_topicid');

				$link = e107::url('rss_menu', 'rss', array('rss_type'=>$type, 'rss_url'=>$curVal, 'rss_topicid'=>$topic));
				return "<a href='".$link."'>".$curVal."</a>";
			break;
			
			case 'write': // Edit Page
				$link = SITEURL."feed/"; // e107::url('rss_menu','index').'/';
				return "<div class='form-inline'>".$link.e107::getForm()->text('rss_url', $curVal,255, 'size=small')."/rss/{Topic id}</div>";
			break;
			
			case 'filter':
			case 'batch':
				return  null;
			break;
		}
	}

}		
		

	new rss_admin();

	require_once(e_ADMIN."auth.php");
	e107::getAdminUI()->runPage();

	require_once(e_ADMIN."footer.php");
	exit;










/// ------------------------------- Legacy Code -------------------------------








require_once(e_ADMIN."auth.php");

$imagedir = e_IMAGE."admin_images/";
require_once(e_PLUGIN.'rss_menu/rss_shortcodes.php');
$rss = new rss;

global $tp;
$mes = e107::getMessage();


// Delete entry
if(isset($_POST['delete']))
{
	$d_idt = array_keys($_POST['delete']);
	$message = ($sql -> db_Delete("rss", "rss_id=".intval($d_idt[0]))) ? LAN_DELETED : LAN_DELETED_FAILED;
	e107::getLog()->add('RSS_01','ID: '.intval($d_idt[0]).' - '.$message,E_LOG_INFORMATIVE,'');
    e107::getCache()->clear("rss");
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
	$mes->add($message);
	// $rss->show_message('', $message);
}

// Get template
/*
if (is_readable(THEME."rss_template.php")) 
{
	require_once(THEME."rss_template.php");
} 
else 
{
	require_once(e_PLUGIN."rss_menu/rss_template.php");
}*/

$frm = e107::getForm();

// Admin : rss listing
if(!isset($RSS_ADMIN_LIST_HEADER))
{
		
    $RSS_ADMIN_LIST_HEADER = "
    <div style='text-align:center;'>
    <form action='".e_SELF.(e_QUERY ? "?".e_QUERY : "")."' id='dataform' method='post' >
    <table class='table adminlist'>
	<thead>
    <tr>
        <th style='white-space:nowrap;'>{RSS_ADMIN_CAPTION=id,LAN_ID}</th>
        <th style='white-space:nowrap;'>{RSS_ADMIN_CAPTION=name,LAN_NAME}</th>
        <th style='white-space:nowrap;'>{RSS_ADMIN_CAPTION=path,LAN_PLUGIN_FOLDER}</th>
        <th style='white-space:nowrap;'>{RSS_ADMIN_CAPTION=url,LAN_URL}</th>
        <th style='white-space:nowrap;'>".RSS_LAN_ADMIN_12."</th>
        <th style='white-space:nowrap;'>{RSS_ADMIN_CAPTION=limit,LAN_LIMIT}</th>
        <th style='white-space:nowrap;'>".LAN_OPTIONS."</th>
    </tr>
	</thead>
	<tbody>";
}
if(!isset($RSS_ADMIN_LIST_TABLE))
{
	$RSS_ADMIN_LIST_TABLE = "
	<tr>
		<td>{RSS_ADMIN_ID}</td>
		<td>{RSS_ADMIN_NAME}</td>
		<td>{RSS_ADMIN_PATH}</td>
		<td>{RSS_ADMIN_URL}</td>
		<td>{RSS_ADMIN_TOPICID}</td>
		<td>{RSS_ADMIN_LIMIT}</td>
		<td class='center'>{RSS_ADMIN_OPTIONS}</td>
	</tr>";
}
if(!isset($RSS_ADMIN_LIST_FOOTER))
{
	$RSS_ADMIN_LIST_FOOTER = "
	<tr>
		<td class='buttons-bar center' colspan='7'>
			{RSS_ADMIN_LIMITBUTTON}
		</td>
	</tr>
	</tbody>
	</table>
	</form>
	</div>";
}

// Admin : rss create/edit
if(!isset($RSS_ADMIN_CREATE_TABLE))
{
	$RSS_ADMIN_CREATE_TABLE = "
	<form action='".e_SELF.(e_QUERY ? "?".e_QUERY : "")."' id='dataform' method='post' >
	<table class='table adminform'>
	<tr>
		<td style='width:12%'>".LAN_NAME."</td>
		<td>{RSS_ADMIN_FORM_NAME}</td>
	</tr>
	<tr>
		<td>".LAN_URL."</td>
		<td>{RSS_ADMIN_FORM_URL}</td>
	</tr>
	<tr>
		<td>".RSS_LAN_ADMIN_12."</td>
		<td>{RSS_ADMIN_FORM_TOPICID}</td>
	</tr>
	<tr>
		<td>".LAN_PLUGIN_FOLDER."</td>
		<td>{RSS_ADMIN_FORM_PATH}</td>
	</tr>
	<tr>
		<td>".LAN_DESCRIPTION."</td>
		<td>{RSS_ADMIN_FORM_TEXT}</td>
	</tr>
	<tr>
		<td>".LAN_LIMIT."</td>
		<td>{RSS_ADMIN_FORM_LIMIT}</td>
	</tr>
	<tr>
		<td>".LAN_VISIBILITY."</td>
		<td>{RSS_ADMIN_FORM_CLASS}</td>
	</tr>

	<tr>
		<td colspan='2' style='text-align:center;'>{RSS_ADMIN_FORM_CREATEBUTTON}</td>
	</tr>
	</table>
	</form>
	</div>";
}

// Admin : rss options
if(!isset($RSS_ADMIN_OPTIONS_TABLE))
{
	$RSS_ADMIN_OPTIONS_TABLE = "
	<form action='".e_SELF.(e_QUERY ? "?".e_QUERY : "")."' id='dataform' method='post' >
	<table class='table adminform'>
	<tr>
		<td>".RSS_LAN_ADMIN_13."</td>
		<td>
			<input type='checkbox' name='rss_othernews' value='1' ".(vartrue($pref['rss_othernews']) == 1 ? " checked='checked' " : "")." />
		</td>
	</tr>
	<tr>
		<td>".RSS_LAN_ADMIN_19."</td>
		<td>
			<input type='checkbox' name='rss_summarydiz' value='1' ".(vartrue($pref['rss_summarydiz']) == 1 ? " checked='checked' " : "")." />
		</td>
	</tr>
	<tr>
		<td>".RSS_LAN_ADMIN_33."</td>
		<td>
			<input type='checkbox' name='rss_shownewsimage' value='1' ".(vartrue($pref['rss_shownewsimage']) == 1 ? " checked='checked' " : "")." />
		</td>
	</tr>
	</table>
	<div class='buttons-bar center'>
		".$frm->admin_button('updatesettings', LAN_UPDATE, 'update')."
	</div>
	</form>";
}

// Admin : rss import
if(!isset($RSS_ADMIN_IMPORT_HEADER))
{
	$RSS_ADMIN_IMPORT_HEADER = "
	<form action='".e_SELF."' id='imlistform' method='post' >
	<table class='table adminform'>
	<tr>
		<th>".RSS_LAN_ADMIN_16."</td>
		<th>".LAN_PLUGIN_FOLDER."</td>
		<th>".LAN_NAME."</td>
		<th>".LAN_URL."</td>
		<th>".RSS_LAN_ADMIN_12."</td>
	</tr>";
}
if(!isset($RSS_ADMIN_IMPORT_TABLE))
{
	$RSS_ADMIN_IMPORT_TABLE = "
	<tr>
		<td>{RSS_ADMIN_IMPORT_CHECK}</td>
		<td>{RSS_ADMIN_IMPORT_PATH}</td>
		<td><b>{RSS_ADMIN_IMPORT_NAME}</b><br />{RSS_ADMIN_IMPORT_TEXT}</td>
		<td>{RSS_ADMIN_IMPORT_URL}</td>
		<td>{RSS_ADMIN_IMPORT_TOPICID}</td>
	</tr>";
}

if(!isset($RSS_ADMIN_IMPORT_FOOTER))
{
	$RSS_ADMIN_IMPORT_FOOTER = "
	</table>
	<div class='buttons-bar center'>
		".$frm->admin_button('import_rss',RSS_LAN_ADMIN_17,'submit')."
	</div>
	</form>
	";
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
	global $sql;
	$qs = explode(".",e_QUERY);

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
		$tp = e107::getParser();
		$mes = e107::getMessage();
		$ns = e107::getRender();
		$sql = e107::getDb();
		
		global $field, $sort, $rss_shortcodes, $row, $RSS_ADMIN_LIST_HEADER, $RSS_ADMIN_LIST_TABLE, $RSS_ADMIN_LIST_FOOTER;

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
			$ns->tablerender(RSS_LAN_ADMIN_1,$mes->render(). $text);
		}
	}

	// Create or edit - put up a form
	function rssadmincreate($action, $id=0)
	{
		$mes = e107::getMessage();
		$ns = e107::getRender();
		$tp = e107::getParser();
		$sql = e107::getDb();
		
		global $rss_shortcodes, $row, $RSS_ADMIN_CREATE_TABLE;

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
		
		$text = $tp->parseTemplate($RSS_ADMIN_CREATE_TABLE, FALSE, $rss_shortcodes);
		
		$ns->tablerender(RSS_LAN_ADMIN_10, $mes->render().$text);
	}

	// Import - put up the list of possible feeds to import
	function rssadminimport()
	{
		$sql = e107::getDb();
		$ns = e107::getRender();
		$mes = e107::getMessage();
		$tp = e107::getParser();
		
		global $i,$rss_shortcodes, $feed, $pref;
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
		$feed['name']		= LAN_COMMENTS;
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
			$eplug_rss_feed = array();
			if (is_readable(e_PLUGIN.$val."/e_rss.php")) 
			{
				require_once(e_PLUGIN.$val."/e_rss.php");
				
				$className = $val."_rss";
				$data = false;
				
				if(!$data = e107::callMethod($className,'config'))
				{
					$data = $eplug_rss_feed;	
				}
				
				foreach($data as $v)
				{
					$v['path'] = $val;
					array_push($plugin_feedlist,$v);
				}
				
			}
		}

		$feedlist = array_merge($feedlist, $plugin_feedlist);
		
//		print_a($feedlist);

		$render=FALSE;
		$i=0;
		$text = $RSS_ADMIN_IMPORT_HEADER;
		foreach($feedlist as $k=>$feed)
		{
			$feed['topic_id']		= $tp -> toDB($feed['topic_id']);
			$feed['url']			= $tp -> toDB($feed['url']);

			// Check if feed is not yet present
			if(!$sql->select("rss", "*", "rss_path='".$feed['path']."' AND rss_url='".$feed['url']."' AND rss_topicid='".$feed['topic_id']."' "))
			{
				$render=TRUE;
				$text .= $tp -> parseTemplate($RSS_ADMIN_IMPORT_TABLE, FALSE, $rss_shortcodes);
				$i++;
			}
		}
		$text .= $tp -> parseTemplate($RSS_ADMIN_IMPORT_FOOTER, FALSE, $rss_shortcodes);

		if(!$render)
		{
			$this->show_message(RSS_LAN_ADMIN_11, LAN_DESCRIPTION);
		}
		else
		{
			$ns->tablerender(RSS_LAN_ADMIN_11, $mes->render(). $text);
		}
	}

	// Options - display form
	function rssadminoptions()
	{
		$mes = e107::getMessage();
		$ns = e107::getRender();
		$tp = e107::getParser();

		global $rss_shortcodes, $row, $RSS_ADMIN_OPTIONS_TABLE;

		$text = $tp -> parseTemplate($RSS_ADMIN_OPTIONS_TABLE, FALSE, $rss_shortcodes);
		$ns->tablerender(LAN_OPTIONS, $mes->render(). $text);
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
		$sql = e107::getDb();
		$tp = e107::getParser();
		$cache = e107::getCache();
		$log = e107::getLog();


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
			$rssVals['rss_datestamp'] = !empty($_POST['rss_datestamp']) ? (int) $_POST['rss_datestamp'] : time();
			$rssVals['WHERE']           = " rss_id = ".intval($_POST['rss_id']);

			switch ($mode)
			{
				case 'create' :
					$message = ($sql ->insert('rss',$rssVals)) ? LAN_CREATED : LAN_CREATED_FAILED;
					$log->logArrayAll('RSS_02',$rssVals, $message);
					$cache->clear('rss');
					break;

				case  'update' :
					$message = ($sql ->update('rss', $rssVals)) ? LAN_UPDATED : LAN_UPDATED_FAILED;
					$log->logArrayAll('RSS_03',$rssVals, $message);
					$cache->clear('rss');
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
		global $tp, $pref;

		$admin_log = e107::getLog();

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
			return LAN_NOCHANGE_NOTSAVED;
		}
	}
} // End class rss
?>
