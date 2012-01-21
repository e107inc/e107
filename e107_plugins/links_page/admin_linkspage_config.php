<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2012 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *	Links plugin
 *
 * $URL$
 * $Revision$
 * $Date$
 * $Author$
 */

/**
 *	e107 Links plugin
 *
 *	@package	e107_plugins
 *	@subpackage	links
 *	@version 	$Id$;
 */

require_once('../../class2.php');
if (!getperms('P') || !plugInstalled('links_page')) 
{
	header('location:'.e_BASE.'index.php');
	exit();
}
require_once(e_PLUGIN.'links_page/link_shortcodes.php');
require_once(e_PLUGIN.'links_page/link_defines.php');
require_once(e_ADMIN.'auth.php');
require_once(e_HANDLER.'userclass_class.php');
require_once(e_HANDLER.'form_handler.php');
$rs = new form;
require_once(e_HANDLER.'file_class.php');
$fl = new e_file;
e107_require_once(e_HANDLER.'arraystorage_class.php');
$eArrayStorage = new ArrayData();
require_once(e_PLUGIN.'links_page/link_class.php');
$lc = new linkclass;

include_lan(e_PLUGIN.'links_page/languages/'.e_LANGUAGE.'.php');

$linkspage_pref = $lc->getLinksPagePref();

//$deltest = array_flip($_POST);			// Not used any more


if(e_QUERY)
{
	$qs = explode('.', e_QUERY);

	if(is_numeric($qs[0]))
	{
		$from = array_shift($qs);
	}
	else
	{
		$from = '0';
	}
}





$incdec_action = '';
foreach($_POST as $k => $v)
{
	if ((preg_match("#^(inc|dec)".URL_SEPARATOR."(\d+)".URL_SEPARATOR."(\d+)".URL_SEPARATOR."(\d+){0,1}_[x|y]#", $k, $matches)))
	{
		$incdec_action = $matches[1];			// (inc|dec)
		$linkid = intval($matches[2]);
		$link_order = intval($matches[3]);
		$link_location = intval(varset($matches[4], ''));
		break;
	}
}

switch ($incdec_action)
{
	case 'inc' :
		$lc->dbOrderUpdateInc($linkid, $link_order, $link_location);
		break;
	case 'dec' :
		$lc->dbOrderUpdateDec($linkid, $link_order, $link_location);
		break;
}


if (isset($_POST['delete']))
{
	$tmp = array_pop($tmp = array_flip($_POST['delete']));
	list($delete, $del_id) = explode("_", $tmp);
	$del_id = intval($del_id);
}
elseif (isset($_POST['create_category'])) 
{
	$lc -> dbCategoryCreate();
}
elseif (isset($_POST['update_category'])) 
{
	$lc -> dbCategoryUpdate();
}
elseif (isset($_POST['updateoptions'])) 
{
	$linkspage_pref = $lc -> UpdateLinksPagePref();
	$lc -> show_message(LCLAN_ADMIN_6);
}
elseif (isset($_POST['add_link'])) 
{
	$lc -> dbLinkCreate();
}
//upload link icon
elseif(isset($_POST['uploadlinkicon']))
{
	$lc -> uploadLinkIcon();
}
//upload category icon
elseif(isset($_POST['uploadcatlinkicon']))
{
	$lc -> uploadCatLinkIcon();
}
//update link order
elseif (isset($_POST['update_order'])) 
{
	$lc -> dbOrderUpdate($_POST['link_order']);
}
//update link category order
elseif (isset($_POST['update_category_order'])) 
{
	$lc -> dbOrderCatUpdate($_POST['link_category_order']);
}

//delete link
if (isset($delete) && $del_id)
{
	switch ($delete)
	{
		case 'main' :		// Delete link
			$sql->db_Select('links_page', 'link_order', 'link_id='.$del_id);
			$row = $sql->db_Fetch();
			$sql2 = e107::getDb('sql2');
			$sql->db_Select("links_page", "link_id", "link_order>'".$row['link_order']."' && link_category='".$id."'");
			while ($row = $sql->db_Fetch()) {
				$sql2->db_Update('links_page', "link_order=link_order-1 WHERE link_id='".$row['link_id']."'");
			}
			if ($sql->db_Delete('links_page', 'link_id='.$del_id)) 
			{
				$msg = LCLAN_ADMIN_10." #".$del_id." ".LCLAN_ADMIN_11;
				$data = array('method'=>'delete', 'table'=>'links_page', 'id'=>$del_id, 'plugin'=>'links_page', 'function'=>'delete');
				$msg .= $e_event->triggerHook($data);
				$admin_log->log_event('LINKS_02','ID: '.$del_id,E_LOG_INFORMATIVE,'');
				$lc->show_message($msg);
			}
			break;

		case 'category' :	//delete category
			//check if links are present for this category
			if($sql->db_Select('links_page', '*', 'link_category='.$del_id)) 
			{
				$lc->show_message(LCLAN_ADMIN_12." #".$del_id." ".LAN_DELETED_FAILED."<br />".LCLAN_ADMIN_15);
			//no? then we can safely remove this category
			}
			else
			{
				if ($sql->db_Delete('links_page_cat', 'link_category_id='.$del_id)) 
				{
					$admin_log->log_event('LINKS_03','ID: '.$del_id,E_LOG_INFORMATIVE,'');
					$lc->show_message(LCLAN_ADMIN_12." #".$del_id." ".LCLAN_ADMIN_11);
					unset($id);
				}
			}
			break;

		case 'sn' :		//delete submitted link
			if ($sql->db_Delete('tmp', 'tmp_time='.$del_id)) 
			{
				$admin_log->log_event('LINKS_04','ID: '.$del_id,E_LOG_INFORMATIVE,'');
				$lc->show_message(LCLAN_ADMIN_13);
			}
	}
}



//show link categories (cat edit)
if (!e_QUERY) 
{
	$lc->show_categories('cat');
}
elseif (isset($qs[0]))
{
	switch ($qs[0])
	{
		case 'cat' :
			if (isset($qs[1]))
			{
				//show cat edit form
				if (($qs[1] == 'edit') && isset($qs[2]) && is_numeric($qs[2])) 
				{
					$lc->show_cat_create();
				}
				//show cat create form
				elseif (($qs[1] == 'create') && !isset($qs[2]) ) 
				{
					$lc->show_cat_create();
				}
			}
			break;
		case 'link' :
			if (isset($qs[1]))
			{
				switch ($qs[1])
				{
					case 'view' :
						if (isset($qs[2]) && (is_numeric($qs[2]) || $qs[2] == "all") ) 
						{
							$lc->show_links();
						}
						break;
					case 'edit' :		// Edit link
						if (isset($qs[2]) && is_numeric($qs[2])) 
						{
							$lc->show_link_create();
						}
						break;
					case 'create' :		// Create link
						if (!isset($qs[2]) ) 
						{
							$lc->show_link_create();
						}
						break;
					case 'sn' :			// Post submitted
						if (isset($qs[2]) && is_numeric($qs[2]) ) 
						{
							$lc->show_link_create();
						}
						break;
				}
			}
			else
			{
				$lc->show_categories('link');	//view categories (link select cat)
			}
			break;
		case 'sn' :		//view submitted links
			$lc->show_submitted();
			break;
		case 'opt' :	// Options
			$lc->show_pref_options();
			break;
	}
}


// ##### Display options --------------------------------------------------------------------------
function admin_linkspage_config_adminmenu()
{
	global $qs, $sql;
	$act = varset($qs[0],'cat');
	if($act == 'cat' && isset($qs[1]))
	{
		if($qs[1] == 'create')
		{
			$act .= '.create';
		}
		elseif ($qs[1] == 'edit')
		{
			$act .= '';
		}
		elseif ($qs[1] == 'view')
		{
			$act .= '';
		}
	}

	$var['cat']['text'] = LCLAN_ADMINMENU_2;
	$var['cat']['link'] = e_SELF;

	$var['cat.create']['text'] = LCLAN_ADMINMENU_3;
	$var['cat.create']['link'] = e_SELF."?cat.create";

	$var['link']['text'] = LCLAN_ADMINMENU_4;
	$var['link']['link'] = e_SELF."?link";

	$var['link.create']['text'] = LCLAN_ADMINMENU_5;
	$var['link.create']['link'] = e_SELF."?link.create";
		
	if ($tot = $sql->db_Select("tmp", "*", "tmp_ip='submitted_link' ")) 
	{
		$var['sn']['text'] = LCLAN_ADMINMENU_7." (".$tot.")";
		$var['sn']['link'] = e_SELF."?sn";
	}
		
	$var['opt']['text'] = LCLAN_ADMINMENU_6;
	$var['opt']['link'] = e_SELF."?opt";
		
	show_admin_menu(LCLAN_ADMINMENU_1, $act, $var);
		
	if($qs[0] != 'opt')
	{
		unset($var);
		$var=array();
		if ($sql->db_Select("links_page_cat", "*")) 
		{
			while ($row = $sql->db_Fetch()) {
				$var[$row['link_category_id']]['text'] = $row['link_category_name'];
				$var[$row['link_category_id']]['link'] = e_SELF."?link.view.".$row['link_category_id'];
			}
			$active = ($qs[0] == 'link') ? $id : FALSE;
			show_admin_menu(LCLAN_ADMINMENU_8, $active, $var);
		}
	}
	if(isset($qs[0]) && $qs[0] == "opt")
	{
		unset($var);
		$var=array();
		$var['optgeneral']['text']	= LCLAN_OPT_MENU_1;
		$var['optmanager']['text']	= LCLAN_OPT_MENU_2;
		$var['optcategory']['text']	= LCLAN_OPT_MENU_3;
		$var['optlinks']['text']	= LCLAN_OPT_MENU_4;
		$var['optrefer']['text']	= LCLAN_OPT_MENU_5;
		$var['optrating']['text']	= LCLAN_OPT_MENU_6;
		$var['optmenu']['text']		= LCLAN_OPT_MENU_7;
		show_admin_menu(LCLAN_ADMINMENU_6, $qs[0], $var, TRUE);
	}
}

require_once(e_ADMIN.'footer.php');
exit;

// End ---------------------------------------------------------------------------------------------------------

?>