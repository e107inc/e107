<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2008 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Custom Menus/Pages Administration
 *
 * $Source: /cvs_backup/e107_0.8/e107_admin/cpage.php,v $
 * $Revision: 1.15 $
 * $Date: 2009-07-10 14:25:21 $
 * $Author: e107coders $
 *
*/

require_once("../class2.php");

if (!getperms("5")) { header("location:".e_BASE."index.php"); exit; }

$e_sub_cat = 'custom';
$e_wysiwyg = "data";

require_once("auth.php");
require_once(e_HANDLER."userclass_class.php");
require_once(e_HANDLER."message_handler.php");
require_once(e_HANDLER."form_handler.php");
$frm = new e_form(true);
$emessage = &eMessage::getInstance();
$page = new page;

$custpage_lang = ($sql->mySQLlanguage) ? $sql->mySQLlanguage : $pref['sitelanguage'];

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

if(isset($_POST['delete']))
{
	foreach(array_keys($_POST['delete']) as $pid)
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
	$page->showExistingPages();
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
		if(isset($_POST['submit-e-columns']))
		{
			$user_pref['admin_cpage_columns'] = $_POST['e-columns'];
			save_prefs('user');
		}
        $this->fieldpref = (varset($user_pref['admin_cpage_columns'])) ? $user_pref['admin_cpage_columns'] : array("page_id","page_title","page_theme"); ;

    	$this->fields = array(
			'page_id'			=> array('title'=> ID, 'width'=>'5%', 'forced'=> TRUE),
            'page_title'	   	=> array('title'=> CUSLAN_1, 'width'=>'auto'),
			'page_theme' 		=> array('title'=> CUSLAN_2, 'type' => 'text', 'width' => 'auto'),
         	'page_author' 		=> array('title'=> LAN_AUTHOR, 'type' => 'text', 'width' => 'auto', 'thclass' => 'left first'), // Display name
			'page_datestamp' 	=> array('title'=> LAN_DATE, 'type' => 'text', 'width' => 'auto'),	// User name
            'page_class' 		=> array('title'=> LAN_USERCLASS, 'type' => 'text', 'width' => 'auto'),	 	// Photo
			'page_rating_flag' 		=> array('title'=> LAN_RATING, 'type' => 'text', 'width' => '10%', 'thclass' => 'center' ),	 // Real name (no real vetting)
			'page_comment_flag' 	=> array('title'=> ADLAN_114, 'type' => 'text', 'width' => '10%', 'thclass' => 'center' ),	 // No real vetting
		//	'page_password' 	=> array('title'=> LAN_USER_05, 'type' => 'text', 'width' => 'auto'),

	   //	'page_ip_restrict' 		=> array('title'=> LAN_USER_07, 'type' => 'text', 'width' => 'auto'),	 // Avatar

			'options' 	=> array('title'=> LAN_OPTIONS, 'forced'=>TRUE, 'width' => '10%', 'thclass' => 'center last')
		);

       // $this->fieldpref = array("page_id","page_title","page_author","page_class");
	}


// --------------------------------------------------------------------------

	function showExistingPages()
	{
		global $sql, $e107, $emessage, $frm, $pref;

/*		$text = "
			<form action='".e_SELF."' id='newsform' method='post'>
				<fieldset id='core-cpage-list'>
					<legend class='e-hideme'>".CUSLAN_5."</legend>
					<table cellpadding='0' cellspacing='0' class='adminlist'>
						<colgroup span='4'>
							<col style='width: 	5%'></col>
							<col style='width: 60%'></col>
							<col style='width: 15%'></col>
							<col style='width: 20%'></col>
						</colgroup>
						<thead>
							<tr>
								<th>".ID."</th>
								<th>".CUSLAN_1."</th>
								<th>".CUSLAN_2."</th>
								<th class='center last'>".CUSLAN_3."</th>
							</tr>
						</thead>
						<tbody>
		";*/

        $text .= "<form method='post' action='".e_SELF."?".e_QUERY."'>
                        <fieldset id='core-cpage-list'>
						<legend class='e-hideme'>".CUSLAN_5."</legend>
						<table cellpadding='0' cellspacing='0' class='adminlist'>
							<colgroup span='".count($this->fieldpref)."'>".$frm->colGroup($this->fields,$this->fieldpref)."</colgroup>
							<thead>
								<tr>".$frm->thead($this->fields,$this->fieldpref)."</tr>
							</thead>
							<tbody>";

		if(!$sql->db_Select("page", "*", "ORDER BY page_datestamp DESC", "nowhere"))
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
				//title='".LAN_DELETE."'
				$title_text = $pge['page_title'] ? $pge['page_title'] : ($pge['page_theme'] ? CUSLAN_43.$pge['page_theme'] : CUSLAN_44);
				$author = get_user_data($pge['page_author']);


				$text .= "
						<tr>
							<td>{$pge['page_id']}</td>";

				$text .= (in_array("page_title",$this->fieldpref)) ? "<td><a href='".($pge['page_theme'] ? e_ADMIN."menus.php" : e_BASE."page.php?{$pge['page_id']}" )."'>{$title_text}</a></td>" : "";
                $text .= (in_array("page_theme",$this->fieldpref)) ? "<td>".($pge['page_theme'] ? "menu" : "page")."</td>" : "";
                $text .= (in_array("page_author",$this->fieldpref)) ? "<td>".($author['user_name'])."</td>" : "";
				$text .= (in_array("page_datestamp",$this->fieldpref)) ? "<td>".strftime($pref['shortdate'],$pge['page_datestamp'])."</td>" : "";
				$text .= (in_array("page_class",$this->fieldpref)) ? "<td>".(r_userclass_name($pge['page_class']))."</td>" : "";
				$text .= (in_array("page_rating_flag",$this->fieldpref)) ? "<td class='center'>".($pge['page_rating_flag'] ? ADMIN_TRUE_ICON : "&nbsp;")."</td>" : "";
				$text .= (in_array("page_comment_flag",$this->fieldpref)) ? "<td class='center'>".($pge['page_comment_flag'] ? ADMIN_TRUE_ICON : "&nbsp;")."</td>" : "";

				$text .= "<td class='center'>
								<a class='action edit' href='".e_SELF."?".($pge['page_theme'] ? "createm": "create").".edit.{$pge['page_id']}'>".ADMIN_EDIT_ICON."</a>
								<input type='image' class='action delete' name='delete[{$pge['page_id']}]' src='".ADMIN_DELETE_ICON_PATH."' title='".CUSLAN_4." [ ID: {$pge['page_id']} ]' />
							</td>
						</tr>
				";
			}
		}

		$text .= "
						</tbody>
					</table>
				</fieldset>
			</form>
		";

		$e107->ns->tablerender(CUSLAN_5, $emessage->render().$text);
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

		global $sql, $tp, $e107, $sub_action, $id, $frm, $e_userclass, $e_event;

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

		$text = "
			<form method='post' action='".e_SELF."' id='dataform' enctype='multipart/form-data'>
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
							<td class='label'>".CUSLAN_7."</td>
							<td class='control'>
								".$frm->text('menu_name', $menu_name, 50)."
							</td>
						</tr>
			";
		}

		$text .= "
						<tr>
							<td class='label'>".CUSLAN_8."</td>
							<td class='control'>
								".$frm->text('page_title', $page_title, 250)."
							</td>
						</tr>
						<tr>
							<td class='label'>".CUSLAN_9."</td>
							<td class='control'>
		";

		require_once(e_HANDLER."ren_help.php");
		$insertjs = (!e_WYSIWYG)? " rows='15' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);' style='width:95%'": "rows='25' style='width:100%' ";
		$data = $tp->toForm($data,FALSE,TRUE);	// Make sure we convert HTML tags to entities
		$text .= "<textarea class='tbox' tabindex='".$frm->getNext()."' id='data' name='data' cols='80'{$insertjs}>".(strstr($data, "[img]http") ? $data : str_replace("[img]../", "[img]", $data))."</textarea>";

		$text .= "
								<br />".display_help('cpage-help', 'cpage')."
							</td>
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
									".$e_userclass->uc_dropdown('page_class', $page_class, '', "tabindex='".$frm->getNext()."'")."
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
		$pauthor = ($_POST['page_display_authordate_flag'] ? USERID : 0);


		if ($mode)
		{	// Don't think $_POST['page_ip_restrict'] is ever set.
			$update = $sql->db_Update("page", "page_title='{$page_title}', page_datestamp='".time()."', page_text='{$page_text}', page_author='{$pauthor}', page_rating_flag='".intval($_POST['page_rating_flag'])."', page_comment_flag='".intval($_POST['page_comment_flag'])."', page_password='".$_POST['page_password']."', page_class='".$_POST['page_class']."', page_ip_restrict='".varset($_POST['page_ip_restrict'],'')."' WHERE page_id='{$mode}'");
			$admin_log->log_event('CPAGE_02',$mode.'[!br!]'.$page_title.'[!br!]'.$pauthor,E_LOG_INFORMATIVE,'');
			$e107cache->clear("page_{$mode}");
			$e107cache->clear("page-t_{$mode}");

			$data = array('method'=>'update', 'table'=>'page', 'id'=>$mode, 'plugin'=>'page', 'function'=>'submitPage');
			$this->message = $e_event->triggerHook($data);

			if($type)
			{
				$menu_name = $tp -> toDB($_POST['menu_name']); // not to be confused with menu-caption.

				if($sql -> db_Update("menus", "menu_name='{$menu_name}' WHERE menu_path='{$mode}' "))
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

			$pid = admin_update($sql->db_Insert("page", "0, '{$page_title}', '{$page_text}', '{$pauthor}', '".time()."', '".intval($_POST['page_rating_flag'])."', '".intval($_POST['page_comment_flag'])."', '".$_POST['page_password']."', '".$_POST['page_class']."', '', '".$menuname."'"), 'insert', CUSLAN_27, LAN_CREATED_FAILED, false);
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
			$action = "main";
		}
		$var['main']['text'] = CUSLAN_11;
		$var['main']['link'] = e_SELF;

		$var['create']['text'] = CUSLAN_12;
		$var['create']['link'] = e_SELF."?create";

		$var['createm']['text'] = CUSLAN_31;
		$var['createm']['link'] = e_SELF."?createm";

		$var['options']['text'] = LAN_OPTIONS;
		$var['options']['link'] = e_SELF."?options";

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