<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2001-2002 Steve Dunstan (jalist@e107.org)
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $URL$
|     $Revision$
|     $Id$
|     $Author$
+----------------------------------------------------------------------------+
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
if(!getperms("P")){ header("location:".e_BASE."index.php"); exit(); }

include_lan(e_PLUGIN.'rss_menu/languages/'.e_LANGUAGE.'.php');

require_once(e_ADMIN."auth.php");

$imagedir = e_IMAGE."admin_images/";

require_once(e_PLUGIN.'rss_menu/rss_shortcodes.php');
$rss = new rss;

global $tp;

//delete entry
if(isset($_POST['delete'])){
	$d_idt = array_keys($_POST['delete']);
	$message = ($sql -> db_Delete("rss", "rss_id='".$d_idt[0]."'")) ? LAN_DELETED : LAN_DELETED_FAILED;
    $e107cache->clear("rss");
}

//create rss feed
if(isset($_POST['create_rss'])){
	$message = $rss -> dbrss("create");
}

//update rss feed
if(isset($_POST['update_rss'])){
	$message = $rss -> dbrss("update");
}

//import rss feed
if(isset($_POST['import_rss'])){
	$message = $rss -> dbrssimport();
}

//update_limit
if(isset($_POST['update_limit'])){
	$message = $rss -> dbrsslimit();
}
//update options
if(isset($_POST['updatesettings'])){
	$message = $rss->dboptions();
}

//config check
if($rss->file_check()){
	$message = RSS_LAN_ERROR_2; // space found in file.
}


//render message
if(isset($message)){
	$rss->show_message('', $message);
}

//get template
if (is_readable(THEME."rss_template.php")) {
	require_once(THEME."rss_template.php");
	} else {
	require_once(e_PLUGIN."rss_menu/rss_template.php");
}

//listing
if(e_QUERY){
	$qs = explode(".", e_QUERY);
	$field = (isset($qs[1])) ? $qs[1] : "";
	$sort = (isset($qs[2])) ? $qs[2] : "";
}

	unset($_POST['sitelanguage']); // quick bug fix for multi-language.

	//create
	if(isset($qs[0]) && ($qs[0] == 'create') && !$_POST)
	{
		$rss -> rssadmincreate();

	//import
	}elseif(isset($qs[0]) && $qs[0] == 'import'){
		$rss -> rssadminimport();

	//options
	}elseif(isset($qs[0]) && $qs[0] == 'options'){
		$rss -> rssadminoptions();

	//list
	}else{

		$rss -> rssadminlist();
	}

require_once(e_ADMIN."footer.php");

// ##### Display options --------------------------------------------------------------------------
function admin_prefs_adminmenu(){
	global $sql, $qs;

	$act = $qs[0];
	if($act==""){$act="list";}

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






class rss{

	//check for config
	function file_check(){
		$arrays = file_get_contents(e_BASE."e107_config.php");
		$arrays2 = file_get_contents(e_PLUGIN."rss_menu/languages/".e_LANGUAGE.".php");
		if($arrays[0] != "<" || $arrays2[0] != "<"){
			return TRUE;
		}
	}


	//admin : list : existing rss feeds
	function rssadminlist(){
		global $qs, $ns, $sql, $rs, $tp, $field, $sort, $rss_shortcodes, $row, $RSS_ADMIN_LIST_HEADER, $RSS_ADMIN_LIST_TABLE, $RSS_ADMIN_LIST_FOOTER;

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

	//create
	function rssadmincreate(){
		global $ns, $qs, $rs, $sql, $tp, $rss_shortcodes, $row, $RSS_ADMIN_CREATE_TABLE;

		if( isset($qs[1]) && $qs[1] == "edit" && isset($qs[2]) && is_numeric($qs[2]) ){
			if(!$sql -> db_Select("rss", "*", "rss_id='".intval($qs[2])."' ")){
				$this->show_message(LAN_ERROR, RSS_LAN_ERROR_5);
			}else{
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

	//import
	function rssadminimport(){
		global $sql, $ns, $i, $qs, $rs, $tp, $rss_shortcodes, $feed, $pref;
		global $RSS_ADMIN_IMPORT_HEADER, $RSS_ADMIN_IMPORT_TABLE, $RSS_ADMIN_IMPORT_FOOTER;

		$sqli = new db;
		$feedlist = array();

		//news
		$feed['name']		= ADLAN_0;
		$feed['url']		= 'news';	//the identifier for the rss feed url
		$feed['topic_id']	= '';		//the topic_id, empty on default (to select a certain category)
		$feed['path']		= 'news';	//this is the plugin path location
		$feed['text']		= RSS_PLUGIN_LAN_7;
		$feed['class']		= '0';
		$feed['limit']		= '9';
		$feedlist[]			= $feed;

		//news categories
		if($sqli -> db_Select("news_category", "*","category_id!='' ORDER BY category_name ")){
			while($rowi = $sqli -> db_Fetch()){
				$feed['name']		= ADLAN_0.' > '.$rowi['category_name'];
				$feed['url']		= 'news';
				$feed['topic_id']	= $rowi['category_id'];
				$feed['path']		= 'news';
				$feed['text']		= RSS_PLUGIN_LAN_10.' '.$rowi['category_name'];
				$feed['class']		= '0';
				$feed['limit']		= '9';
				$feedlist[]			= $feed;
			}
		}

		//download
		$feed['name']		= ADLAN_24;
		$feed['url']		= 'download';
		$feed['topic_id']	= '';
		$feed['path']		= 'download';
		$feed['text']		= RSS_PLUGIN_LAN_8;
		$feed['class']		= '0';
		$feed['limit']		= '9';
		$feedlist[]			= $feed;

		//download categories
		if($sqli -> db_Select("download_category", "*","download_category_id!='' ORDER BY download_category_order ")){
			while($rowi = $sqli -> db_Fetch()){
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

		//comments
		$feed['name']		= RSS_PLUGIN_LAN_14;
		$feed['url']		= 'comments';
		$feed['topic_id']	= '';
		$feed['path']		= 'comments';
		$feed['text']		= RSS_PLUGIN_LAN_9;
		$feed['class']		= '0';
		$feed['limit']		= '9';
		$feedlist[]			= $feed;

		//plugin rss feed, using e_rss.php in plugin folder
		$plugin_feedlist = array();
		foreach($pref['e_rss_list'] as $val)
		{
			if (is_readable(e_PLUGIN.$val."/e_rss.php")) {
				require_once(e_PLUGIN.$val."/e_rss.php");
				$plugin_feedlist = $eplug_rss_feed;
			}
		}

/*        if($sqli -> db_Select("plugin","plugin_path","plugin_installflag = '1' ORDER BY plugin_path ")){
            while($rowi = $sqli -> db_Fetch()){
                if (is_readable(e_PLUGIN.$rowi['plugin_path']."/e_rss.php")) {
                    require_once(e_PLUGIN.$rowi['plugin_path']."/e_rss.php");
                    $plugin_feedlist = $eplug_rss_feed;
                }
            }
        }*/

		$feedlist = array_merge($feedlist, $plugin_feedlist);

		$render=FALSE;
		$i=0;
		$text = $RSS_ADMIN_IMPORT_HEADER;
		foreach($feedlist as $k=>$feed){
			$feed['topic_id']		= $tp -> toDB($feed['topic_id']);
			$feed['url']			= $tp -> toDB($feed['url']);

			//check if feed is not yet present
			if(!$sql -> db_Select("rss", "*", "rss_path='".$feed['path']."' AND rss_url='".$feed['url']."' AND rss_topicid='".$feed['topic_id']."' "))
			{
				$render=TRUE;
				$text .= $tp -> parseTemplate($RSS_ADMIN_IMPORT_TABLE, FALSE, $rss_shortcodes);
				$i++;
			}
		}
		$text .= $tp -> parseTemplate($RSS_ADMIN_IMPORT_FOOTER, FALSE, $rss_shortcodes);

		if(!$render){
			$this->show_message(RSS_LAN_ADMIN_11, RSS_LAN_ERROR_6);
		}else{
			$ns->tablerender(RSS_LAN_ADMIN_11, $text);
		}
	}

	//options
	function rssadminoptions(){
		global $ns, $qs, $rs, $sql, $tp, $rss_shortcodes, $row, $RSS_ADMIN_OPTIONS_TABLE;

		$text = $tp -> parseTemplate($RSS_ADMIN_OPTIONS_TABLE, FALSE, $rss_shortcodes);
		$ns->tablerender(LAN_OPTIONS, $text);
		return;
	}

	//render message
	function show_message($caption='', $text=''){
		global $ns;
		$ns -> tablerender($caption, "<div style='text-align:center'><b>$text</b></div>");
	}

	//db:create/update
	function dbrss($mode='create'){
		global $qs, $sql, $ns, $rs, $tp, $e107cache;

		if($_POST['rss_name'] && $_POST['rss_url'] && $_POST['rss_path']){

			$_POST['rss_name']		= $tp -> toDB(trim($_POST['rss_name']));
			$_POST['rss_url']		= $tp -> toDB($_POST['rss_url']);
			$_POST['rss_topicid']	= $tp -> toDB($_POST['rss_topicid']);
			$_POST['rss_path']		= $tp -> toDB($_POST['rss_path']);
			$_POST['rss_text']		= $tp -> toDB($_POST['rss_text']);
			$_POST['rss_class']		= (intval($_POST['rss_class']) ? intval($_POST['rss_class']) : '0');
			$_POST['rss_limit']		= intval($_POST['rss_limit']);

			if(isset($_POST['rss_datestamp']) && $_POST['rss_datestamp']!=''){
				$datestamp = intval($_POST['rss_datestamp']);
			}else{
				$datestamp = time();
			}

			if($mode == 'create'){
				$message = ($sql -> db_Insert("rss", "'0', '".$_POST['rss_name']."', '".$_POST['rss_url']."', '".$_POST['rss_topicid']."', '".$_POST['rss_path']."', '".$_POST['rss_text']."', '".$datestamp."', '".$_POST['rss_class']."', '".$_POST['rss_limit']."' ")) ? LAN_CREATED : LAN_CREATED_FAILED;
				$e107cache->clear("rss");

			}elseif($mode == 'update'){
				$message = ($sql -> db_Update("rss", "rss_name = '".$_POST['rss_name']."', rss_url = '".$_POST['rss_url']."', rss_topicid = '".$_POST['rss_topicid']."', rss_path = '".$_POST['rss_path']."', rss_text = '".$_POST['rss_text']."', rss_datestamp = '".$datestamp."', rss_class = '".$_POST['rss_class']."', rss_limit = '".$_POST['rss_limit']."' WHERE rss_id = '".intval($_POST['rss_id'])."' ")) ? LAN_UPDATED : LAN_UPDATED_FAILED;
				$e107cache->clear("rss");
			}
		}else{
			$message = RSS_LAN_ERROR_7;
		}
		return $message;
	}

	//import rss feeds
	function dbrssimport(){
		global $sql, $tp;

		foreach($_POST['importid'] as $key=>$value)
		{
			$rss_topcid		= ($_POST['topic_id'][$key] ? $tp -> toDB($_POST['topic_id'][$key]) : '');
			$rss_url		= ($_POST['url'][$key] ? $tp -> toDB($_POST['url'][$key]) : '');
			$rss_path		= ($_POST['path'][$key] ? $tp -> toDB($_POST['path'][$key]) : '');
			$rss_name		= ($_POST['name'][$key] ? $tp -> toDB($_POST['name'][$key]) : '');
			$rss_text		= ($_POST['text'][$key] ? $tp -> toDB($_POST['text'][$key]) : '');
			$rss_datestamp	= time();
			$rss_class		= ($_POST['class'][$key] ? intval($_POST['class'][$key]) : '0');
			$rss_limit		= ($_POST['limit'][$key] ? intval($_POST['limit'][$key]) : '0');

			$sql -> db_Insert("rss", "'0', '".$rss_name."', '".$rss_url."', '".$rss_topcid."', '".$rss_path."', '".$rss_text."', '".$rss_datestamp."', '".$rss_class."', '".$rss_limit."' ");
		}
		$message = count($_POST['importid'])." ".RSS_LAN_ADMIN_18;
		return $message;

	}

	function dbrsslimit(){
		global $sql, $tp;
		foreach($_POST['limit'] as $key=>$value)
		{
			$sql -> db_Update("rss", "rss_limit = '".intval($value)."' WHERE rss_id = '".intval($key)."' ");
		}
		header("location:".e_SELF."?r3");
	}

	//update options
	function dboptions(){
		global $tp, $pref;

		$pref['rss_othernews'] = $_POST['rss_othernews'];

		save_prefs();
		return LAN_SAVED;
	}


} //end class


?>
