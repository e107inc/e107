<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2010 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Custom Menus/Pages Administration
 *
 * $URL$
 * $Id$
 *
*/

/**
 *
 * @package     e107
 * @subpackage	admin
 * @version     $Revision$
 * @author      $Author$

 *	Admin-related functions for custom page and menu creation
*/

require_once('../class2.php');

if (!getperms("5|J")) { header('location:'.e_BASE.'index.php'); exit; }

include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_'.e_PAGE);

$e_sub_cat = 'custom';

require_once('auth.php');
require_once(e_HANDLER.'userclass_class.php');
require_once(e_HANDLER.'message_handler.php');
require_once(e_HANDLER.'form_handler.php');
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
	if(getperms('5'))
	{
		$page->showExistingPages();
	}
	else
	{
    	$page->showExistingPages('menus');
	}

}
elseif(varset($_GET['action'],'')=='edit')
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
	$function = $action.'Page';
	$page->$function();
}

require_once(e_ADMIN.'footer.php');

// FIXME - add page link to sitelinks is completely disabled as current implementation is not reliable (+ is obsolete and generates sql error)
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
			'page_id'			=> array('title'=> 'ID',			'width'=>'5%', 'forced'=> TRUE),
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
				$title_text = $pge['page_title'] ? $pge['page_title'] : ($pge['page_theme'] ? CUSLAN_43.$pge['page_theme'] : CUSLAN_44);
				$pge['page_title'] = "<a href='".($pge['page_theme'] ? e_ADMIN."menus.php" : e107::getUrl()->create('page/view', $pge, 'allow=page_id,page_sef'))."'>{$title_text}</a>";
				$authorData = get_user_data($pge['page_author']);
				$pge['page_author'] = varset($authorData['user_name'], '?');

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
		$ns = e107::getRender();


		$edit = ($sub_action == 'edit');
		$caption =(!$mode ? ($edit ? CUSLAN_23 : CUSLAN_24) : ($edit ? CUSLAN_25 : CUSLAN_26));

		if ($sub_action == "edit" && !isset($_POST['preview']) && !isset($_POST['submit']))
		{
			
			//$url = e107::getUrl()->sc('page/view', $row, 'allow=page_id,page_title,page_sef');
        	//$query = "SELECT p.*,l.link_name,m.menu_name FROM #page AS p
        	$query = "SELECT p.* FROM #page AS p
			LEFT JOIN #menus AS m ON m.menu_path='{$id}' WHERE p.page_id ='{$id}' LIMIT 1";
			// FIXME - extremely bad
			//LEFT JOIN #links AS l ON l.link_url='".$url."'
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
//				$menu_name					  = $tp->toForm($row['menu_name']);
				$menu_name					  = $tp->toForm($row['page_theme']);
				
			}
		}
		else
		{
			$menu_name = '';
			$page_title = '';
			$data = '';
		}

		$e_qry = ($mode) ? 'menus=1' : '';

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
			// fixed - last parameter (allinfo) should be false as getLayout method is returning non-usable formatted array
			$templates = e107::getLayouts('', 'page', 'front', '', false, false); 

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
		";
		
		if(!$mode)
		{
			$text .= "
					<tr>
						<td>".CUSLAN_3."</td>
						<td>".$frm->text('page_sef', $row['page_sef'], 250)."</td>
					</tr>
			";
			
			$text .= "
					<tr>
						<td>".CUSLAN_32."</td>
						<td>".$frm->text('page_metakeys', $row['page_metakeys'], 250)."</td>
					</tr>
			";
			
			$text .= "
					<tr>
						<td>".CUSLAN_11."</td>
						<td>".$frm->textarea('page_metadscr', $row['page_metadscr'], 10, 80, array(), 200)."</td>
					</tr>
			";
		}

		$text .= "
					<tr>
						<td>".CUSLAN_9."</td>
						<td>
		";

	//	require_once(e_HANDLER."ren_help.php");

	//	$insertjs = (!e_WYSIWYG)? " rows='15' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);' style='width:95%'": "rows='25' style='width:100%' ";

		$data = $tp->toForm($data,FALSE,TRUE);	// Make sure we convert HTML tags to entities

		$textareaValue = (strstr($data, "[img]http") ? $data : str_replace("[img]../", "[img]", $data));
	//	$text .= $frm->textarea('data', $textareaValue);
		$text .= $frm->bbarea('data', $textareaValue, 'page','help','large');



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
								<td class='label'>".CUSLAN_18."</td>

								<td class='control'>
									".$e_userclass->uc_dropdown('page_class', $page_class, 'public,guest,nobody,member,main,admin,classes', "tabindex='".$frm->getNext()."'")."
								</td>
							</tr>
			";

							/*
							<tr>
								<td class='label'>".CUSLAN_16."</td>
								<td class='control'>
									".$frm->text('page_link', $page_link, 50)."
									<div class='field-help'>".CUSLAN_17."</div>
								</td>
							</tr>
							 **/
							
			
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

		$ns->tablerender($caption, $text);
	}


	// Write new/edited page/menu to the DB
	// $mode - zero for new page, page id for existing
	// $type = FALSE for page, TRUE for menu
	function submitPage($mode = FALSE, $type=FALSE)
	{
		global $sql, $tp, $e107cache, $admin_log, $e_event;

		$page_title = $tp->toDB($_POST['page_title']);
		$page_text = $tp->toDB($_POST['data']);
		$pauthor = ($_POST['page_display_authordate_flag'] ? USERID : 0); // Ideally, this check should be done in the front-end.
		$update = 0;			// Make sure some updates happen
		
		$page_sef = '';
		$page_metad = '';
		$page_metak = '';
		if(!$type)
		{
			
			if(!empty($_POST['page_sef']))
			{
				$page_sef = eHelper::secureSef($_POST['page_sef']);
			}
			
			if(empty($page_sef))
			{
				$page_sef = eHelper::title2sef($_POST['page_title']);
			}
			
			if(!empty($_POST['page_metadscr']))
			{
				$page_metad = $tp->toDB(eHelper::formatMetaDescription($_POST['page_metadscr']));
			}

			if(!empty($_POST['page_metakeys']))
			{
				$page_metak = eHelper::formatMetaKeys($_POST['page_metakeys']);
			}
			
		}

		if(!$type && (!$page_title || !$page_sef))
		{
			e107::getMessage()->addError(CUSLAN_34, 'default', true);
			e107::getRedirect()->redirect(e_ADMIN_ABS.'cpage.php');
		}
		
		if(!$type && $sql->db_Count('page', '(page_id)', ($mode ? "page_id<>{$mode} AND " : '')."page_sef!={$page_sef}"))
		{
			e107::getMessage()->addError(CUSLAN_34, 'default', true);
			e107::getRedirect()->redirect(e_ADMIN_ABS.'cpage.php');
		}
		
		if($type && empty($_POST['menu_name']))
		{
			e107::getMessage()->addError(CUSLAN_36, 'default', true);
			e107::getRedirect()->redirect(e_ADMIN_ABS.'cpage.php');
		}

		if($mode)
		{	// Saving existing page/menu after edit
			// Don't think $_POST['page_ip_restrict'] is ever set.
			
			$menuname = ($type && vartrue($_POST['menu_name']) ? ", page_theme = '".$tp -> toDB($_POST['menu_name'])."'" : "");
			
			$status = $sql -> db_Update("page", "page_title='{$page_title}', page_sef='{$page_sef}', page_metakeys='{$page_metak}', page_metadscr='{$page_metad}', page_text='{$page_text}', page_datestamp='".time()."', page_author='{$pauthor}', page_rating_flag='".intval($_POST['page_rating_flag'])."', page_comment_flag='".intval($_POST['page_comment_flag'])."', page_password='".$_POST['page_password']."', page_class='".$_POST['page_class']."', page_ip_restrict='".varset($_POST['page_ip_restrict'],'')."', page_template='".$_POST['page_template']."' {$menuname} WHERE page_id='{$mode}'") ?  E_MESSAGE_SUCCESS : E_MESSAGE_ERROR;
			if ($status == E_MESSAGE_SUCCESS) $update++;

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
				// Need to check whether menu already in table, else we can't distinguish between a failed update and no update needed
				if ($sql->db_Select('menus', 'menu_name', "`menu_path` = '{$mode}'"))
				{		// Updating existing entry
					if($sql -> db_Update('menus', "menu_name='{$menu_name}' WHERE menu_path='{$mode}' ") !== FALSE)
					{
						$update++;
					}
				}
			}

			//$url = e107::getUrl()->sc('page/view', array('name' => $tp->post_toForm($_POST['page_title']), 'id' => $mode));
			/*
			if ($_POST['page_link'])
			{
				// FIXME extremely ugly, just join on created link ID by new field page_link 
				if ($sql->db_Select("links", "link_id", "link_url='".$url."' && link_name!='".$tp->toDB($_POST['page_link'])."'"))
				{
					$sql->db_Update("links", "link_name='".$tp->toDB($_POST['page_link'])."' WHERE link_url='".$url."'");
					$update++;
					$e107cache->clear("sitelinks");
				}
				else if (!$sql->db_Select("links", "link_id", "link_url='".$url."'"))
				{
					$sql->db_Insert("links", "0, '".$tp->toDB($_POST['page_link'])."', '".$url."', '', '', 1, 0, 0, 0, ".$_POST['page_class']);
					$update++;
					$e107cache->clear("sitelinks");
				}
			} else {
				if ($sql->db_Select("links", "link_id", "link_url='".$url."'"))
				{
					$sql->db_Delete("links", "link_url='".$url."'");
					$update++;
					$e107cache->clear("sitelinks");
				}
			}*/
			admin_update($update, 'update', LAN_UPDATED, false, false);		// Display result of update
		}
		else
		{	// New page/menu
			$menuname = ($type ? $tp->toDB($_POST['menu_name']) : "");
			$addMsg = ($type ? CUSLAN_51 : CUSLAN_27);
			
			$info = array(
				'page_title' => $page_title,
				'page_sef' => $page_sef,
				'page_metakeys' => $page_metak,
				'page_metadscr' => $page_metad,
				'page_text' => $page_text,
				'page_author' => $pauthor,
				'page_datestamp' => time(),
				'page_rating_flag' => varset($_POST['page_rating_flag'],0),
				'page_comment_flag' => varset($_POST['page_comment_flag'], ''),
				'page_password' => varset($_POST['page_password'], ''),
				'page_class' => varset($_POST['page_class'],e_UC_PUBLIC),
				'page_ip_restrict' => '',
				'page_theme' => $menuname,
				'page_template' => varset($_POST['page_template'],'')
				);
			$pid = admin_update($sql->db_Insert('page', $info), 'insert', $addMsg, LAN_CREATED_FAILED, false);
			$admin_log->log_event('CPAGE_01',$menuname.'[!br!]'.$page_title.'[!br!]'.$pauthor,E_LOG_INFORMATIVE,'');

			if($type)
			{
				$info = array(
					'menu_name' => $menuname,
					'menu_location' => 0,
					'menu_order' => 0,
					'menu_class' => '0',
					'menu_pages' => '',
					'menu_path' => $pid,
				);
				admin_update($sql->db_Insert('menus', $info), 'insert', CUSLAN_52, LAN_CREATED_FAILED, false);
			}

			/*if(vartrue($_POST['page_link']))
			{
				//$link = 'page.php?'.$pid;
				$url = e107::getUrl()->sc('page/view', array('name' => $tp->post_toForm($_POST['page_title']), 'id' => $pid));
				if (!$sql->db_Select("links", "link_id", "link_name='".$tp->toDB($_POST['page_link'])."'"))
				{
					$linkname = $tp->toDB($_POST['page_link']);
					$sql->db_Insert("links", "0, '{$linkname}', '{$url}', '', '', 1, 0, 0, 0, ".$_POST['page_class']);
					$e107cache->clear("sitelinks");
				}
			}*/

			$data = array('method'=>'create', 'table'=>'page', 'id'=>$pid, 'plugin'=>'page', 'function'=>'submitPage');
			$this->message = $e_event->triggerHook($data);
		}
	}

	function delete_page($del_id)
	{
		global $sql, $e107cache, $admin_log, $e_event;
		//if(!$sql->db_Select('page', '*', "page_id={$del_id}")) return;
		//$row = $sql->db_Fetch();
		
		admin_update($sql->db_Delete("page", "page_id='{$del_id}' "), 'delete', CUSLAN_28, false, false);
		$sql->db_Delete('menus', "menu_path='$del_id'");
		$e107cache->clear_sys('menus_');
		$admin_log->log_event('CPAGE_03','ID: '.$del_id,E_LOG_INFORMATIVE,'');
		
		/*$url = e107::getUrl()->sc('page/view', $row, 'allow=page_id,page_title,page_sef');
		if ($row['page_theme'] && $sql->db_Select('links', 'link_id', "link_url='".$url."'"))
		{
			$tmp = $sql->db_Fetch();
			$sql->db_Delete('links', "link_id=".$tmp['link_id']);
			$e107cache->clear('sitelinks');
		}
		*/
		$data = array('method'=>'delete', 'table'=>'page', 'id'=>$del_id, 'plugin'=>'page', 'function'=>'delete_page');
		$this->message = $e_event->triggerHook($data);
	}

	function optionsPage()
	{
		global $e107, $pref, $frm, $emessage;
		
		$ns = e107::getRender();

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

		$ns->tablerender(LAN_OPTIONS, $emessage->render().$text);
	}


	function saveSettings()
	{
		global $pref, $admin_log, $emessage;
		$temp['listPages'] = $_POST['listPages'];
		$temp['pageCookieExpire'] = $_POST['pageCookieExpire'];
		if ($admin_log->logArrayDiffs($temp, $pref, 'CPAGE_04'))
		{
			save_prefs();		// Only save if changes
			$emessage->add(LAN_SETSAVED, E_MESSAGE_SUCCESS);
		}
		else
		{
			$emessage->add(LAN_NOCHANGE_NOTSAVED);
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