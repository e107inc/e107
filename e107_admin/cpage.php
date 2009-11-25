<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Custom Menus/Pages Administration
 *
 * $Source: /cvs_backup/e107_0.8/e107_admin/cpage.php,v $
 * $Revision: 1.29 $
 * $Date: 2009-11-25 11:54:53 $
 * $Author: e107coders $
 *
*/

require_once("../class2.php");

if (!getperms("5|J")) { header("location:".e_BASE."index.php"); exit; }

include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_'.e_PAGE);

$e_sub_cat = 'custom';

require_once("auth.php");
require_once(e_HANDLER."userclass_class.php");
require_once(e_HANDLER."message_handler.php");
require_once(e_HANDLER."form_handler.php");
$frm = new e_form(true);
$emessage = &eMessage::getInstance();
$page = new page;

if (e_QUERY)
{
	$tmp        = explode(".", e_QUERY);
	$action     = $tmp[0];
	$sub_action = varset($tmp[1]);
	$id         = intval(varset($tmp[2], 0));
	$from       = intval(varset($tmp[3], 0));
}

if(isset($_POST['submitPage']))
{
	$page->submitPage();
}

if(isset($_POST['uploadfiles']))
{

	$page->uploadPage();
	$id = intval(varset($_POST['pe_id'], 0));
	$sub_action = ($_POST['pe_id']) ? "edit" : "";
	$page->createPage($_POST['mode']);
}

if(isset($_POST['submitMenu']))
{
	$page->submitPage("", TRUE);
}

if(isset($_POST['updateMenu']))
{
	$page->submitPage($_POST['pe_id'], TRUE);
}

if(isset($_POST['updatePage']))
{
	$page->submitPage($_POST['pe_id']);
}

if(isset($_POST['delete']) || varset($_POST['etrigger_delete']))
{
	if($_POST['etrigger_delete'])
	{
		$delArray = array_keys($_POST['etrigger_delete']);	
	}
	else
	{
		$delArray = array_keys($_POST['delete']);
	}
	
	foreach($delArray as $pid)
	{
		$page->delete_page($pid);
	}
}

if (isset($_POST['saveOptions']))
{
	$page->saveSettings();
}

if(!e_QUERY)
{
	if(getperms("5"))
	{
		$page->showExistingPages();
	}
	else
	{
    	$page->showExistingPages('menus');
	}

}
elseif($_GET['action']=='edit')
{
	$action = 'create';
	$sub_action = 'edit';
	$id = intval($_GET['id']);

	$mod 	= (vartrue($_GET['menus'])) ? 'menus' : "";
	$page->createPage($mod);		
}
elseif(vartrue($_GET['menus']))
{
	$page->menusPage();	
}
else
{
	$function = $action."Page";
	$page->$function();
}

require_once(e_ADMIN."footer.php");

class page
{
	var $fields;


	function page()
	{

 		global $pref,$user_pref, $admin_log;
		if(isset($_POST['etrigger_ecolumns']))
		{
			$user_pref['admin_cpage_columns'] = $_POST['e-columns'];
			save_prefs('user');
		}
        $this->fieldpref = (varset($user_pref['admin_cpage_columns'])) ? $user_pref['admin_cpage_columns'] : array("page_id","page_title","page_theme"); ;

    	$this->fields = array(
			'page_id'			=> array('title'=> ID, 					'width'=>'5%', 'forced'=> TRUE),
            'page_title'	   	=> array('title'=> CUSLAN_1, 		'type' => 'text', 'width'=>'auto'),
			'page_theme' 		=> array('title'=> CUSLAN_2, 		'type' => 'text', 'width' => 'auto','nolist'=>true),
			'page_template' 	=> array('title'=> 'Template', 		'type' => 'text', 'width' => 'auto'),
         	'page_author' 		=> array('title'=> LAN_AUTHOR, 		'type' => 'text', 'width' => 'auto', 'thclass' => 'left'), 
			'page_datestamp' 	=> array('title'=> LAN_DATE, 		'type' => 'datestamp', 'width' => 'auto'),	
            'page_class' 		=> array('title'=> LAN_USERCLASS, 	'type' => 'userclass', 'width' => 'auto'),	 	
			'page_rating_flag' 	=> array('title'=> LAN_RATING, 		'type' => 'boolean', 'width' => '10%', 'thclass' => 'center', 'class' => 'center' ),	 
			'page_comment_flag' => array('title'=> ADLAN_114,		'type' => 'boolean', 'width' => '10%', 'thclass' => 'center', 'class' => 'center' ),	
		//	'page_password' 	=> array('title'=> LAN_USER_05, 	'type' => 'text', 'width' => 'auto'),

	   //	'page_ip_restrict' 		=> array('title'=> LAN_USER_07, 'type' => 'text', 'width' => 'auto'),	 // Avatar

			'options' 	=> array('title'=> LAN_OPTIONS, 'forced'=>TRUE, 'width' => '10%', 'thclass' => 'center last', 'class' => 'center')
		);

       // $this->fieldpref = array("page_id","page_title","page_author","page_class");
	}


// --------------------------------------------------------------------------

	function menusPage()
	{
		if(!getperms("J")){ return; }
    	return $this->showExistingPages('menus');
	}

// --------------------------------------------------------------------------

	function showExistingPages($mode=FALSE)
	{
		global $sql, $e107, $emessage, $frm, $pref;

        $text = "<form method='post' action='".e_SELF."?".e_QUERY."'>
                        <fieldset id='core-cpage-list'>
						<legend class='e-hideme'>".CUSLAN_5."</legend>
						<table cellpadding='0' cellspacing='0' class='adminlist'>".
							$frm->colGroup($this->fields,$this->fieldpref).
							$frm->thead($this->fields,$this->fieldpref).

							"<tbody>";

        if($mode=='menus')
		{
			$qry = "page_theme !='' ";
        	$caption = CUSLAN_50;
		}
		else
		{
			if(!getperms("5")){ return; }
        	$qry = "page_theme ='' ";
			$caption = CUSLAN_5;
		}

		if(!$sql->db_Select("page", "*", $qry." ORDER BY page_datestamp DESC"))
		{
			$text .= "
							<tr>
								<td colspan='4' class='center middle'>
									".CUSLAN_42."
								</td>
							</tr>
			";
		}
		else
		{
			$pages = $sql->db_getList('ALL', FALSE, FALSE);

			foreach($pages as $pge)
			{
				$pge['page_title'] = $pge['page_title'] ? $pge['page_title'] : ($pge['page_theme'] ? CUSLAN_43.$pge['page_theme'] : CUSLAN_44);
				$authorData = get_user_data($pge['page_author']);
				$pge['page_author'] = $authorData['user_name'];

				$text .= $frm->renderTableRow($this->fields,$this->fieldpref,$pge,'page_id');
			}
		}

		$text .= "
						</tbody>
					</table>
				</fieldset>
			</form>
		";

		$e107->ns->tablerender($caption, $emessage->render().$text);
	}


	function createmPage()
	{
		$this->createPage(TRUE);
	}


	function uploadPage()
	{
		global $pref;
		$pref['upload_storagetype'] = "1";
		require_once(e_HANDLER."upload_handler.php");
		$uploaded = file_upload(e_IMAGE."custom/");
	}


	function createPage($mode=FALSE)
	{
		/* mode: FALSE == page, mode: TRUE == menu */

		global $e107, $sub_action, $id, $e_userclass, $e_event;
		
		$frm = e107::getForm();
		$sql = e107::getDb();
		$tp = e107::getParser();
		

		$edit = ($sub_action == 'edit');
		$caption =(!$mode ? ($edit ? CUSLAN_23 : CUSLAN_24) : ($edit ? CUSLAN_25 : CUSLAN_26));

		if ($sub_action == "edit" && !isset($_POST['preview']) && !isset($_POST['submit']))
		{
        	$query = "SELECT p.*,l.link_name,m.menu_name FROM #page AS p
			LEFT JOIN #links AS l ON l.link_url='page.php?".$id."'
			LEFT JOIN #menus AS m ON m.menu_path='{$id}' WHERE p.page_id ='{$id}' LIMIT 1";

            if ($sql->db_Select_gen($query))
			{
				$row                          = $sql->db_Fetch();
				$page_class                   = $row['page_class'];
				$page_password                = $row['page_password'];
				$page_title                   = $tp->toForm($row['page_title']);
				$page_rating_flag             = $row['page_rating_flag'];
				$page_comment_flag            = $row['page_comment_flag'];
				$page_display_authordate_flag = $row['page_author'];
				$page_link 					  = varset($row['link_name'],'');
				$data                         = $tp->toForm($row['page_text']);
				$edit                         = TRUE;
				$menu_name					  = $tp->toForm($row['menu_name']);
			}
		}

		$e_qry = ($mode) ? "menus=1" : "";

		$text = "
			<form method='post' action='".e_SELF."?".$e_qry."' id='dataform' enctype='multipart/form-data'>
				<fieldset id='core-cpage-create-general'>
					<legend".($mode ? " class='e-hideme'" : "").">".CUSLAN_47."</legend>
					<table cellpadding='0' cellspacing='0' class='adminedit'>
						<colgroup span='2'>
							<col class='col-label' />
							<col class='col-control' />
						</colgroup>
						<tbody>
		";

		if($mode)  // menu mode.
		{
			$text .= "
				<tr>
					<td>".CUSLAN_7."</td>
					<td>".$frm->text('menu_name', $menu_name, 50)."</td>
				</tr>
			";
		}
		else
		{		
			$templates = array();
			$tmp = e107::getTemplate('page', 'page');
			foreach($tmp as $key=>$val)
			{
				$templates[$key] = $key; //TODO add LANS?
			}
					
			$text .= "
				<tr>
					<td>Template</td>
					<td>". $frm->selectbox('page_template',$templates,$row['page_template'])  ."</td>
				</tr>
			";	
		}
		$text .= "
				<tr>
					<td>".CUSLAN_8."</td>
					<td>".$frm->text('page_title', $page_title, 250)."</td>
				</tr>
				<tr>
					<td>".CUSLAN_9."</td>
					<td>
		";

	//	require_once(e_HANDLER."ren_help.php");
		
	//	$insertjs = (!e_WYSIWYG)? " rows='15' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);' style='width:95%'": "rows='25' style='width:100%' ";
		
		$data = $tp->toForm($data,FALSE,TRUE);	// Make sure we convert HTML tags to entities
		
		$textareaValue = (strstr($data, "[img]http") ? $data : str_replace("[img]../", "[img]", $data));
	//	$text .= $frm->textarea('data', $textareaValue);
		$text .= $frm->bbarea('data', $textareaValue, 'data', 'cpage-help');
		
		
		
	//	$text .= "<textarea class='e-wysiwyg tbox' tabindex='".$frm->getNext()."' id='data' name='data' cols='80'{$insertjs}>".(strstr($data, "[img]http") ? $data : str_replace("[img]../", "[img]", $data))."</textarea>";
	//			<br />".display_help('cpage-help', 'cpage')."
	
	
				$text .= "</td>
							</tr>
							<tr>
								<td class='label'>".LAN_UPLOAD_IMAGES."</td>
								<td class='control'>".$tp->parseTemplate("{UPLOADFILE=".e_IMAGE."custom/}")."</td>
							</tr>


		";

		if(!$mode)
		{
			$text .= "
						</tbody>
					</table>
				</fieldset>
				<fieldset id='core-cpage-create-options'>
					<legend>".LAN_OPTIONS."</legend>
					<table cellpadding='0' cellspacing='0' class='adminedit options'>
						<colgroup span='2'>
							<col class='col-label' />
							<col class='col-control' />
						</colgroup>
						<tbody>
							<tr>
								<td class='label'>".CUSLAN_10."</td>
								<td class='control'>
									".$frm->radio_switch('page_rating_flag', $page_rating_flag)."
								</td>
							</tr>
							<tr>
								<td class='label'>".CUSLAN_13."</td>
								<td class='control'>
									".$frm->radio_switch('page_comment_flag', $page_comment_flag)."
								</td>
							</tr>
							<tr>
								<td class='label'>".CUSLAN_41."</td>
								<td class='control'>
									".$frm->radio_switch('page_display_authordate_flag', $page_display_authordate_flag)."
								</td>
							</tr>
							<tr>
								<td class='label'>".CUSLAN_14."</td>
								<td class='control'>
									".$frm->text('page_password', $page_password, 50)."
									<div class='field-help'>".CUSLAN_15."</div>
								</td>
							</tr>
							<tr>
								<td class='label'>".CUSLAN_16."</td>
								<td class='control'>
									".$frm->text('page_link', $page_link, 50)."
									<div class='field-help'>".CUSLAN_17."</div>
								</td>
							</tr>
							<tr>
								<td class='label'>".CUSLAN_18."</td>

								<td class='control'>
									".$e_userclass->uc_dropdown('page_class', $page_class, 'public,guest,nobody,member,main,admin,classes', "tabindex='".$frm->getNext()."'")."
								</td>
							</tr>
			";

			//triggerHook
			$data = array('method'=>'form', 'table'=>'page', 'id'=>$id, 'plugin'=>'page', 'function'=>'createPage');
			$hooks = $e_event->triggerHook($data);
			if(!empty($hooks))
			{
				$text .= "
				</tbody>
					</table>
				</fieldset>
				<fieldset id='core-cpage-create-hooks'>
					<legend>".LAN_HOOKS."</legend>
					<table cellpadding='0' cellspacing='0' class='adminedit options'>
						<colgroup span='2'>
							<col class='col-label' />
							<col class='col-control' />
						</colgroup>
						<tbody>";
				foreach($hooks as $hook)
				{
					if(!empty($hook))
					{
						$text .= "
						<tr>
							<td class='label'>".$hook['caption']."</td>
							<td class='control'>".$hook['text']."</td>
						</tr>";
					}
				}
			}
			
		}

		$text .= "
						</tbody>
					</table>
					<div class='buttons-bar center'>
						".
						(!$mode ?
						($edit  ? $frm->admin_button('updatePage', CUSLAN_19, 'update')."<input type='hidden' name='pe_id' value='{$id}' />" : $frm->admin_button('submitPage', CUSLAN_20, 'create')) :
						($edit  ? $frm->admin_button('updateMenu', CUSLAN_21, 'update')."<input type='hidden' name='pe_id' value='{$id}' />" : $frm->admin_button('submitMenu', CUSLAN_22, 'create')))
						."
						<input type='hidden' name='mode' value='{$mode}' />
					</div>
				</fieldset>
			</form>
		";

		$e107->ns->tablerender($caption, $text);
	}


	// Write new/edited page/menu to the DB
	// $mode - zero for new page, page id for existing
	// $type = FALSE for page, TRUE for menu
	function submitPage($mode = FALSE, $type=FALSE)
	{
		global $sql, $tp, $e107cache, $admin_log, $e_event;

		$page_title = $tp->toDB($_POST['page_title']);
		$page_text = $tp->toDB($_POST['data']);
	//	$pauthor = ($_POST['page_display_authordate_flag'] ? USERID : 0); // this check should be done in the front-end. 
		$pauthor = USERID;
	

		if($mode)
		{	// Saving existing page/menu after edit
			// Don't think $_POST['page_ip_restrict'] is ever set.
			
			$menuname = ($type && vartrue($_POST['menu_name']) ? ", page_theme = '".$tp -> toDB($_POST['menu_name'])."'" : "");
			$status = $sql -> db_Update("page", "page_title='{$page_title}', page_text='{$page_text}', page_datestamp='".time()."', page_author='{$pauthor}', page_rating_flag='".intval($_POST['page_rating_flag'])."', page_comment_flag='".intval($_POST['page_comment_flag'])."', page_password='".$_POST['page_password']."', page_class='".$_POST['page_class']."', page_ip_restrict='".varset($_POST['page_ip_restrict'],'')."', page_template='".$_POST['page_template']."' {$menuname} WHERE page_id='{$mode}'") ?  E_MESSAGE_SUCCESS : E_MESSAGE_ERROR;
			
			$mes = e107::getMessage();
			$mes->add($message, $status);
			
			$admin_log->log_event('CPAGE_02',$mode.'[!br!]'.$page_title.'[!br!]'.$pauthor,E_LOG_INFORMATIVE,'');
			$e107cache->clear("page_{$mode}");
			$e107cache->clear("page-t_{$mode}");

			$data = array('method'=>'update', 'table'=>'page', 'id'=>$mode, 'plugin'=>'page', 'function'=>'submitPage');
			$this->message = $e_event->triggerHook($data);

			if($type)
			{
				$menu_name = $tp -> toDB($_POST['menu_name']); // not to be confused with menu-caption.
				if($sql -> db_Update('menus', "menu_name='{$menu_name}' WHERE menu_path='{$mode}' ") !== FALSE)
				{
				  	$update++;
				}
				else
				{
                  	$sql -> db_Insert("menus", "0, '$menu_name', '0', '0', '0', '', '".$mode."' ");
				}
			}

			if ($_POST['page_link'])
			{
				if ($sql->db_Select("links", "link_id", "link_url='page.php?".$mode."' && link_name!='".$tp->toDB($_POST['page_link'])."'"))
				{
					$sql->db_Update("links", "link_name='".$tp->toDB($_POST['page_link'])."' WHERE link_url='page.php?".$mode."'");
					$update++;
					$e107cache->clear("sitelinks");
				}
				else if (!$sql->db_Select("links", "link_id", "link_url='page.php?".$mode."'"))
				{
					$sql->db_Insert("links", "0, '".$tp->toDB($_POST['page_link'])."', 'page.php?".$mode."', '', '', 1, 0, 0, 0, ".$_POST['page_class']);
					$update++;
					$e107cache->clear("sitelinks");
				}
			} else {
				if ($sql->db_Select("links", "link_id", "link_url='page.php?".$mode."'"))
				{
					$sql->db_Delete("links", "link_url='page.php?".$mode."'");
					$update++;
					$e107cache->clear("sitelinks");
				}
			}
			admin_update($update, 'update', LAN_UPDATED, false, false);
		}
		else
		{	// New page/menu
			$menuname = ($type ? $tp->toDB($_POST['menu_name']) : "");

			$pid = admin_update($sql->db_Insert("page", "0, '{$page_title}', '{$page_text}', '{$pauthor}', '".time()."', '".intval($_POST['page_rating_flag'])."', '".intval($_POST['page_comment_flag'])."', '".$_POST['page_password']."', '".$_POST['page_class']."', '', '".$menuname."', '".$_POST['page_template']."'"), 'insert', CUSLAN_27, LAN_CREATED_FAILED, false);
			$admin_log->log_event('CPAGE_01',$menuname.'[!br!]'.$page_title.'[!br!]'.$pauthor,E_LOG_INFORMATIVE,'');

			if($type)
			{
				$sql->db_Insert("menus", "0, '{$menuname}', '0', '0', '0', '', '".$pid."' ");
			}

			if($_POST['page_link'])
			{
				$link = "page.php?".$pid;
				if (!$sql->db_Select("links", "link_id", "link_name='".$tp->toDB($_POST['page_link'])."'"))
				{
					$linkname = $tp->toDB($_POST['page_link']);
					$sql->db_Insert("links", "0, '{$linkname}', '{$link}', '', '', 1, 0, 0, 0, ".$_POST['page_class']);
					$e107cache->clear("sitelinks");
				}
			}

			$data = array('method'=>'create', 'table'=>'page', 'id'=>$pid, 'plugin'=>'page', 'function'=>'submitPage');
			$this->message = $e_event->triggerHook($data);
		}
	}

	function delete_page($del_id)
	{
		global $sql, $e107cache, $admin_log, $e_event;
		admin_update($sql->db_Delete("page", "page_id='{$del_id}' "), 'delete', CUSLAN_28, false, false);
		$sql->db_Delete("menus", "menu_path='$del_id' ");
		$admin_log->log_event('CPAGE_03','ID: '.$del_id,E_LOG_INFORMATIVE,'');
		if ($sql->db_Select("links", "link_id", "link_url='page.php?".$del_id."'"))
		{
			$sql->db_Delete("links", "link_url='page.php?".$del_id."'");
			$e107cache->clear("sitelinks");
		}

		$data = array('method'=>'delete', 'table'=>'page', 'id'=>$del_id, 'plugin'=>'page', 'function'=>'delete_page');
		$this->message = $e_event->triggerHook($data);
	}

	function optionsPage()
	{
		global $e107, $pref, $frm, $emessage;

		if(!isset($pref['pageCookieExpire'])) $pref['pageCookieExpire'] = 84600;

		//XXX Lan - Options
		$text = "
			<form method='post' action='".e_SELF."?".e_QUERY."'>
				<fieldset id='core-cpage-options'>
					<legend class='e-hideme'>".LAN_OPTIONS."</legend>
					<table cellpadding='0' cellspacing='0' class='adminform'>
						<colgroup span='2'>
							<col class='col-label' />
							<col class='col-control' />
						</colgroup>
						<tbody>
							<tr>
								<td class='label'>".CUSLAN_29."</td>
								<td class='control'>
									".$frm->radio_switch('listPages', $pref['listPages'])."
								</td>
							</tr>

							<tr>
								<td class='label'>".CUSLAN_30."</td>
								<td class='control'>
									".$frm->text('pageCookieExpire', $pref['pageCookieExpire'], 10)."
								</td>
							</tr>
						</tbody>
					</table>
					<div class='buttons-bar center'>
						".$frm->admin_button('saveOptions', CUSLAN_40, 'submit')."
					</div>
				</fieldset>
			</form>
		";

		$e107->ns->tablerender(LAN_OPTIONS, $emessage->render().$text);
	}


	function saveSettings()
	{
		global $pref, $admin_log, $emessage;
		$temp['listPages'] = $_POST['listPages'];
		$temp['pageCookieExpire'] = $_POST['pageCookieExpire'];
		if ($admin_log->logArrayDiffs($temp, $pref, 'CPAGE_04'))
		{
			save_prefs();		// Only save if changes
			$emessage->add(CUSLAN_45, E_MESSAGE_SUCCESS);
		}
		else
		{
			$emessage->add(CUSLAN_46);
		}
	}


	function show_options($action)
	{
		if ($action == "")
		{
			$action = (getperms('5')) ? "pages" : "menus";
		}
		
		if(vartrue($_GET['menus']))
		{
			$action = "menus";
		}
		
		$var['pages']['text'] = CUSLAN_48;
		$var['pages']['link'] = e_SELF;
		$var['pages']['perm'] = 5;

        $var['menus']['text'] = CUSLAN_49;
		$var['menus']['link'] = e_SELF."?menus=1";
		$var['menus']['perm'] = "J";

		$var['create']['text'] = CUSLAN_12;
		$var['create']['link'] = e_SELF."?create";
		$var['create']['perm'] = 5;

		$var['createm']['text'] = CUSLAN_31;
		$var['createm']['link'] = e_SELF."?createm";
		$var['createm']['perm'] = "J";

		$var['options']['text'] = LAN_OPTIONS;
		$var['options']['link'] = e_SELF."?options";
		$var['options']['perm'] = "0";

		e_admin_menu(CUSLAN_33, $action, $var);
	}
}

function cpage_adminmenu()
{
	global $page;
	global $action;
	$page->show_options($action);
}

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
?>