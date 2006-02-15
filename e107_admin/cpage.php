<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_admin/cpage.php,v $
|     $Revision: 1.25 $
|     $Date: 2006-02-15 15:46:07 $
|     $Author: whoisrich $
+----------------------------------------------------------------------------+
*/

require_once("../class2.php");
if (!getperms("5")){
	header("location:".e_BASE."index.php");
	exit;
}

$e_sub_cat = 'custom';
$e_wysiwyg = "data";

$page = new page;

require_once("auth.php");
require_once(e_HANDLER."ren_help.php");
require_once(e_HANDLER."userclass_class.php");
$custpage_lang = ($sql->mySQLlanguage) ? $sql->mySQLlanguage : $pref['sitelanguage'];


if (e_QUERY)
{
	$tmp = explode(".", e_QUERY);
	$action = $tmp[0];
	$sub_action = $tmp[1];
	$id = $tmp[2];
	$from = ($tmp[3] ? $tmp[3] : 0);
	unset($tmp);
}

if(isset($_POST['submitPage']))
{
	$page -> submitPage();
}

if(isset($_POST['submitMenu']))
{
	$page -> submitPage("", TRUE);
}

if(isset($_POST['updateMenu']))
{
	$page -> submitPage($_POST['pe_id'], TRUE);
}


if(isset($_POST['updatePage']))
{
	$page -> submitPage($_POST['pe_id']);
}

if(isset($_POST['delete']))
{
	foreach(array_keys($_POST['delete']) as $pid)
	{
		$page -> delete_page($pid);
	}
}
		
if (isset($_POST['saveOptions'])) {
	$page -> saveSettings();
}

if($page->message)
{
	$ns->tablerender("", "<div style='text-align:center'><b>".$page->message."</b></div>");
}

if(!e_QUERY)
{
	$page -> showExistingPages();
}
else
{
	$function = $action."Page";
	$page -> $function();
}

require_once(e_ADMIN."footer.php");

class page
{

	var $message;

	function showExistingPages()
	{
		global $sql, $tp, $ns;

		$text = "<div style='text-align: center;'>";

		if(!$sql -> db_Select("page", "*", "ORDER BY page_datestamp DESC", "nowhere"))
		{
			$text .= CUSLAN_42;
		}
		else
		{
			$pages = $sql -> db_getList();
			$text .= "<form action='".e_SELF."' id='newsform' method='post'>
			<table style='".ADMIN_WIDTH."' class='fborder'>
			<tr>
			<td style='width:5%; text-align: center;' class='fcaption'>ID</td>
			<td style='width:60%' class='fcaption'>".CUSLAN_1."</td>
			<td style='width:15%; text-align: center;' class='fcaption'>".CUSLAN_2."</td>
			<td style='width:20%; text-align: center;' class='fcaption'>".CUSLAN_3."</td>
			</tr>
			";

			foreach($pages as $pge)
			{
				$text .= "
				<tr>
				<td style='width:5%; text-align: center;' class='forumheader3'>$pge[page_id]</td>
				<td style='width:60%' class='forumheader3'><a href='".($pge[page_theme] ? e_ADMIN."menus.php" : e_BASE."page.php?{$pge[page_id]}" )."'>$pge[page_title]</a></td>
				<td style='width:15%; text-align: center;' class='forumheader3'>".($pge[page_theme] ? "menu" : "page")."</td>
				<td style='width:20%; text-align: center;' class='forumheader3'>
				<a href='".e_SELF."?".($pge[page_theme] ? "createm": "create").".edit.{$pge[page_id]}'>".ADMIN_EDIT_ICON."</a>
				<input type='image' title='".LAN_DELETE."' name='delete[{$pge[page_id]}]' src='".ADMIN_DELETE_ICON_PATH."' onclick=\"return jsconfirm('".CUSLAN_4." [ ID: $pge[page_id] ]')\"/>
				</td>
				</tr>
				";
			}
		
			$text .= "
			</table>
			</form>
			";
		}

		$text .= "</div>
		";

		$ns -> tablerender(CUSLAN_5, $text);

	}

	function createmPage()
	{
		$this -> createPage(TRUE);
	}

	function createPage($mode=FALSE)
	{
		/* mode: FALSE == page, mode: TRUE == menu */

		global $sql, $tp, $ns, $pref, $sub_action, $id;

		if ($sub_action == "edit" && !$_POST['preview'] && !$_POST['submit'])
		{
			if ($sql->db_Select("page", "*", "page_id='$id' "))
			{
				$row = $sql->db_Fetch();
				$page_title = $tp -> toFORM($row[page_title]);
				$data = $tp -> toFORM($row[page_text]);
				$page_display_authordate_flag = ($row[page_author]);
				$edit = TRUE;
			}
		}

		$text = "<div style='text-align:center'>
		<form method='post' action='".e_SELF."' id='dataform'>
		<table style='".ADMIN_WIDTH."' class='fborder'>
		";

		if($mode)
		{
			$text .= "<tr>
			<td style='width:30%' class='forumheader3'>".CUSLAN_7."</td>
			<td style='width:70%' class='forumheader3'>
			";
			if($edit)
			{
				$text .= $page_theme;
			}
			else
			{
				$text .= "<input class='tbox' type='text' name='menu_name' size='30' value='".$menu_name."' maxlength='50' />
				";
			}
			$text .= "</td>
			</tr>
			";
		}

		$text .= "<tr>
		<td style='width:30%' class='forumheader3'>".CUSLAN_8."</td>
		<td style='width:70%' class='forumheader3'><input class='tbox' type='text' name='page_title' size='50' value='".$page_title."' maxlength='250' /></td>
		</tr>
		
		</td>
		</tr>
		
		<tr>
		<td style='width:30%' class='forumheader3'>".CUSLAN_9."</td>
		<td style='width:70%' class='forumheader3'>
		";

		$insertjs = (!$pref['wysiwyg'])?"rows='15' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'":
		"rows='25' style='width:100%' ";
		$data = $tp->toForm($data);
		$text .= "<textarea class='tbox' id='data' name='data' cols='80'  style='width:95%' $insertjs>".(strstr($data, "[img]http") ? $data : str_replace("[img]../", "[img]", $data))."</textarea>
		";

		if (!$pref['wysiwyg']) {
			$text .= "<input id='helpb' class='helpbox' type='text' name='helpb' size='100' style='width:95%'/>
			<br />". display_help("helpb");
		}

		$text .= "
		</td>
		</tr>
		";
	
		if(!$mode)
		{
			$text .= "<tr>
			<td style='width:30%' class='forumheader3'>".CUSLAN_10."</td>
			<td style='width:70%;' class='forumheader3'>
			<input type='radio' name='page_rating_flag' value='1'".($page_rating_flag ? " checked='checked'" : "")." /> ".CUSLAN_38."&nbsp;&nbsp;
			<input type='radio' name='page_rating_flag' value='0'".(!$page_rating_flag ? " checked='checked'" : "")." /> ".CUSLAN_39."
			</td>
			</tr>

			<tr>
			<td style='width:30%' class='forumheader3'>".CUSLAN_13."</td>
			<td style='width:70%;' class='forumheader3'>
			<input type='radio' name='page_comment_flag' value='1'".($page_comment_flag ? " checked='checked'" : "")." /> ".CUSLAN_38."&nbsp;&nbsp;
			<input type='radio' name='page_comment_flag' value='0'".(!$page_comment_flag ? " checked='checked'" : "")." /> ".CUSLAN_39."
			</td>
			</tr>

			<tr>
			<td style='width:30%' class='forumheader3'>".CUSLAN_41."</td>
			<td style='width:70%;' class='forumheader3'>
			<input type='radio' name='page_display_authordate_flag' value='1'".($page_display_authordate_flag ? " checked='checked'" : "")." /> ".CUSLAN_38."&nbsp;&nbsp;
			<input type='radio' name='page_display_authordate_flag' value='0'".(!$page_display_authordate_flag ? " checked='checked'" : "")." /> ".CUSLAN_39."
			</td>
			</tr>

			<tr>
			<td style='width:30%' class='forumheader3'>".CUSLAN_14."<br /><span class='smalltext'>".CUSLAN_15."</span></td>
			<td style='width:70%' class='forumheader3'><input class='tbox' type='text' name='page_password' size='20' value='".$page_password."' maxlength='50' /></td>
			</tr>

			<tr>
			<td style='width:30%' class='forumheader3'>".CUSLAN_16."<br /><span class='smalltext'>".CUSLAN_17."</span></td>
			<td style='width:70%' class='forumheader3'><input class='tbox' type='text' name='page_link' size='60' value='' maxlength='50' /></td>
			</tr>

			<tr>
			<td style='width:30%' class='forumheader3'>".CUSLAN_18."</td>
			<td style='width:70%' class='forumheader3'>".r_userclass("page_class", $page_class)."</td>
			</tr>
			";
		}

		$text .= "<tr>
		<td colspan='2' style='text-align:center' class='forumheader'>". 

		(!$mode ? 

		($edit ? "<input class='button' type='submit' name='updatePage' value='".CUSLAN_19."' /><input type='hidden' name='pe_id' value='$id' />" : "<input class='button' type='submit' name='submitPage' value='".CUSLAN_20."' />") : 
		
		($edit ? "<input class='button' type='submit' name='updateMenu' value='".CUSLAN_21."' /><input type='hidden' name='pe_id' value='$id' />" : "<input class='button' type='submit' name='submitMenu' value='".CUSLAN_22."' />"))
		
		."
		</td>
		</tr>

		</table>
		</form>
		</div>
		";

		$caption =(!$mode ? ($edit ? CUSLAN_23 : CUSLAN_24) : ($edit ? CUSLAN_25 : CUSLAN_26));
		$ns -> tablerender($caption, $text);
	}


	function submitPage($mode = FALSE, $type=FALSE)
	{
		global $sql, $tp, $e107cache;

		$page_title = $tp -> toDB($_POST['page_title']);
		$page_text = $tp -> toDB($_POST['data']);
		$pauthor = ($_POST['page_display_authordate_flag'] ? USERID : 0);

		if($mode)
		{
			admin_update($sql -> db_Update("page", "page_title='$page_title', page_text='$page_text', page_author='$pauthor', page_rating_flag='".intval($_POST['page_rating_flag'])."', page_comment_flag='".intval($_POST['page_comment_flag'])."', page_password='".$_POST['page_password']."', page_class='".$_POST['page_class']."', page_ip_restrict='".$_POST['page_ip_restrict']."' WHERE page_id='$mode'"), 'update', "Page updated in database.");
		}
		else
		{

			$menuname = ($type ? $tp -> toDB($_POST['menu_name']) : "");

			admin_update($sql -> db_Insert("page", "0, '$page_title', '$page_text', '$pauthor', '".time()."', '".intval($_POST['page_rating_flag'])."', '".intval($_POST['page_comment_flag'])."', '".$_POST['page_password']."', '".$_POST['page_class']."', '', '".$menuname."'"), 'insert', CUSLAN_27);

			if($type)
			{
				$sql -> db_Insert("menus", "0, '$menuname', '0', '0', '0', '', '".mysql_insert_id()."' ");
			}

			if($_POST['page_link'])
			{
				$link = "page.php?".mysql_insert_id();
				if (!$sql->db_Select("links", "link_id", "link_name='".$tp -> toDB($_POST['page_link'])."'"))
				{
					$linkname = $tp -> toDB($_POST['page_link']);
					$sql->db_Insert("links", "0, '$linkname', '$link', '', '', 1, 0, 0, 0, ".$_POST['page_class']);
					$e107cache->clear("sitelinks");
				}
			}
		}
	}

	function delete_page($del_id)
	{
		global $sql;
		admin_update($sql -> db_Delete("page", "page_id='$del_id' "), 'delete', CUSLAN_28);
		$sql -> db_Delete("menus", "menu_path='$del_id' ");
	}

	function optionsPage()
	{
		global $ns, $pref;

		if(!isset($pref['listPages'])) $pref['listPages'] = TRUE;
		if(!isset($pref['pageCookieExpire'])) $pref['pageCookieExpire'] = 84600;
	

		$text = "<div style='text-align: center; margin-left:auto; margin-right: auto;'>
		<form method='post' action='".e_SELF."'>
		<table style='".ADMIN_WIDTH."' class='fborder'>

		<tr>
		<td style='width:50%' class='forumheader3'>".CUSLAN_29."</td>
		<td style='width:50%; text-align: right;' class='forumheader3'>
		<input type='radio' name='listPages' value='1'".($pref['listPages'] ? " checked='checked'" : "")." /> ".CUSLAN_38."&nbsp;&nbsp;
		<input type='radio' name='listPages' value='0'".(!$pref['listPages'] ? " checked='checked'" : "")." /> ".CUSLAN_39."
		</td>
		</tr>

		<tr>
		<td style='width:50%' class='forumheader3'>".CUSLAN_30."</td>
		<td style='width:50%; text-align: right;' class='forumheader3'>
		<input class='tbox' type='text' name='pageCookieExpire' size='15' value='".$pref['pageCookieExpire']."' maxlength='10' />
		</td>
		</tr>

		<tr>
		<td colspan='2'  style='text-align:center' class='forumheader'>
		<input class='button' type='submit' name='saveOptions' value='".CUSLAN_40."' />
		</td>
		</tr>
		</table>
		</form>
		</div>";

		$ns->tablerender("Options", $text);
	}

	function saveSettings()
	{
		global $pref;
		$pref['listPages'] = $_POST['listPages'];
		$pref['pageCookieExpire'] = $_POST['pageCookieExpire'];
		save_prefs();
		$this -> message = "Settings saved.";
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

		show_admin_menu(CUSLAN_33, $action, $var);
	}
}

function cpage_adminmenu() {
	global $page;
	global $action;
	$page -> show_options($action);
}

?>