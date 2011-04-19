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

require_once("../class2.php");

if (!getperms("5")) { header("location:".e_BASE."index.php"); exit; }

$e_sub_cat = 'custom';
$e_wysiwyg = "data";
$page = new page;

if (e_QUERY) 
{
	$tmp        = explode(".", e_QUERY);
	$action     = $tmp[0]; // must be set before auth.php is loaded. 
	$sub_action = $tmp[1];
	$id         = $tmp[2];
	$from       = varset($tmp[3],0);
}

require_once("auth.php");
// require_once(e_HANDLER."ren_help.php");
require_once(e_HANDLER."userclass_class.php");



if(isset($_POST['submitPage']))
{
	$page -> submitPage();
}

if(isset($_POST['uploadfiles']))
{

	$page -> uploadPage();
	$id = $_POST['pe_id'];
	$sub_action = ($_POST['pe_id']) ? "edit" : "";
	$page -> createPage($_POST['mode']);
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
			$pages = $sql -> db_getList('ALL', FALSE, FALSE);
			$text .= "<form action='".e_SELF."' id='newsform' method='post'>
			<table style='".ADMIN_WIDTH."' class='fborder'>
			<tr>
			<td style='width:5%; text-align: center;' class='fcaption'>ID</td>
			<td style='width:60%' class='fcaption'>".CUSLAN_1."</td>
			<td style='width:15%; text-align: center;' class='fcaption'>".CUSLAN_2."</td>
			<td style='width:20%; text-align: center;' class='fcaption'>".CUSLAN_3."</td>
			</tr>";

			foreach($pages as $pge)
			{
			  $title_text = $pge['page_title'] ? $pge['page_title'] : ($pge['page_theme'] ? CUSLAN_43.$pge['page_theme'] : CUSLAN_44);
			  $text .= "
				<tr>
				<td style='width:5%; text-align: center;' class='forumheader3'>{$pge['page_id']}</td>
				<td style='width:60%' class='forumheader3'><a href='".($pge['page_theme'] ? e_ADMIN."menus.php" : e_BASE."page.php?{$pge['page_id']}" )."'>{$title_text}</a></td>
				<td style='width:15%; text-align: center;' class='forumheader3'>".($pge['page_theme'] ? "menu" : "page")."</td>
				<td style='width:20%; text-align: center;' class='forumheader3'>
				<a href='".e_SELF."?".($pge['page_theme'] ? "createm": "create").".edit.{$pge['page_id']}'>".ADMIN_EDIT_ICON."</a>
				<input type='image' title='".LAN_DELETE."' name='delete[{$pge['page_id']}]' src='".ADMIN_DELETE_ICON_PATH."' onclick=\"return jsconfirm('".CUSLAN_4." [ ID: $pge[page_id] ]')\"/>
				</td>
				</tr>";
			}

			$text .= "
			</table>
			</form>";
		}

		$text .= "
		</div>";

		$ns -> tablerender(CUSLAN_5, $text);
	}

	function createmPage()
	{
		$this -> createPage(TRUE);
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

		global $sql, $tp, $ns, $pref, $sub_action, $id;

		if ($sub_action == "edit" && !isset($_POST['preview']) && !isset($_POST['submit']))
		{
        	$query = "SELECT p.*,l.link_name,m.menu_name FROM #page AS p
			LEFT JOIN #links AS l ON l.link_url='page.php?".$id."'
			LEFT JOIN #menus AS m ON m.menu_path='$id' WHERE p.page_id ='$id' LIMIT 1";

            if ($sql->db_Select_gen($query))
			{
				$row                          = $sql->db_Fetch();
				$page_class                   = $row['page_class'];
				$page_password                = $row['page_password'];
				$page_title                   = $tp -> toFORM($row['page_title']);
				$page_rating_flag             = $row['page_rating_flag'];
				$page_comment_flag            = $row['page_comment_flag'];
				$page_display_authordate_flag = $row['page_author'];
				$page_link 					  = varset($row['link_name'],'');
				$data                         = $tp -> toFORM($row['page_text']);
				$edit                         = TRUE;
				$menu_name					  = $tp -> toFORM($row['menu_name']);
			}
		}

		$text = "<div style='text-align:center'>
		<form method='post' action='".e_SELF."' id='dataform' enctype='multipart/form-data'>
		<table style='".ADMIN_WIDTH."' class='fborder'>";

		if($mode)  // menu mode.
		{
			$text .= "<tr>
			<td style='width:25%' class='forumheader3'>".CUSLAN_7."</td>
			<td style='width:75%' class='forumheader3'>
           <input class='tbox' type='text' name='menu_name' size='30' value='".$menu_name."' maxlength='50' />
		   	</td>
			</tr>";
		}

		$text .= "<tr>
		<td style='width:25%' class='forumheader3'>".CUSLAN_8."</td>
		<td style='width:75%' class='forumheader3'><input class='tbox' type='text' name='page_title' size='50' value='".$page_title."' maxlength='250' /></td>
		</tr>

		<tr>
		<td style='width:25%' class='forumheader3'>".CUSLAN_9."</td>
		<td style='width:75%' class='forumheader3'>";

		require_once(e_HANDLER."ren_help.php");
		$insertjs = (!e_WYSIWYG)?"rows='15' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);' style='width:95%'": "rows='25' style='width:100%' ";
		$data = $tp->toForm($data,FALSE,TRUE);	// Make sure we convert HTML tags to entities
		$text .= "<textarea class='tbox' id='data' name='data' cols='80'   $insertjs>".(strstr($data, "[img]http") ? $data : str_replace("[img]../", "[img]", $data))."</textarea>";

		$text .= "<br />".display_help('',"cpage")."</td>
		</tr>

		<tr>
			<td style='width:25%' class='forumheader3'>".LAN_UPLOAD_IMAGES."</td>
			<td style='width:75%;' class='forumheader3'>".$tp->parseTemplate("{UPLOADFILE=".e_IMAGE."custom/}")."</td>
		</tr>";

		if(!$mode)
		{
			$text .= "


			<tr>
			<td style='width:25%' class='forumheader3'>".CUSLAN_10."</td>
			<td style='width:75%;' class='forumheader3'>
			<input type='radio' name='page_rating_flag' value='1'".($page_rating_flag ? " checked='checked'" : "")." /> ".CUSLAN_38."&nbsp;&nbsp;
			<input type='radio' name='page_rating_flag' value='0'".($page_rating_flag ? "" : " checked='checked'")." /> ".CUSLAN_39."
			</td>
			</tr>

			<tr>
			<td style='width:25%' class='forumheader3'>".CUSLAN_13."</td>
			<td style='width:75%;' class='forumheader3'>
			<input type='radio' name='page_comment_flag' value='1'".($page_comment_flag ? " checked='checked'" : "")." /> ".CUSLAN_38."&nbsp;&nbsp;
			<input type='radio' name='page_comment_flag' value='0'".($page_comment_flag ? "" : " checked='checked'")." /> ".CUSLAN_39."
			</td>
			</tr>

			<tr>
			<td style='width:25%' class='forumheader3'>".CUSLAN_41."</td>
			<td style='width:75%;' class='forumheader3'>
			<input type='radio' name='page_display_authordate_flag' value='1'".($page_display_authordate_flag ? " checked='checked'" : "")." /> ".CUSLAN_38."&nbsp;&nbsp;
			<input type='radio' name='page_display_authordate_flag' value='0'".($page_display_authordate_flag ? "" : " checked='checked'")." /> ".CUSLAN_39."
			</td>
			</tr>

			<tr>
			<td style='width:25%' class='forumheader3'>".CUSLAN_14."<br /><span class='smalltext'>".CUSLAN_15."</span></td>
			<td style='width:75%' class='forumheader3'><input class='tbox' type='text' name='page_password' size='20' value='".$page_password."' maxlength='50' /></td>
			</tr>

			<tr>
			<td style='width:25%' class='forumheader3'>".CUSLAN_16."<br /><span class='smalltext'>".CUSLAN_17."</span></td>
			<td style='width:75%' class='forumheader3'><input class='tbox' type='text' name='page_link' size='60' value='".$page_link."' maxlength='50' /></td>
			</tr>

			<tr>
			<td style='width:25%' class='forumheader3'>".CUSLAN_18."</td>
			<td style='width:75%' class='forumheader3'>".r_userclass("page_class", $page_class, "off", "public,guest,nobody,member,main,admin,classes")."</td>
			</tr>";
		}

		$text .= "<tr>
		<td colspan='2' style='text-align:center' class='forumheader'>".

		(!$mode ?
		($edit  ? "<input class='button' type='submit' name='updatePage' value='".CUSLAN_19."' /><input type='hidden' name='pe_id' value='$id' />" : "<input class='button' type='submit' name='submitPage' value='".CUSLAN_20."' />") :
		($edit  ? "<input class='button' type='submit' name='updateMenu' value='".CUSLAN_21."' /><input type='hidden' name='pe_id' value='$id' />" : "<input class='button' type='submit' name='submitMenu' value='".CUSLAN_22."' />"))

		."<input type='hidden' name='mode' value='$mode' />
		</td>
		</tr>

		</table>
		</form>
		</div>";

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
		{	// Saving existing page/menu after edit
			// Don't think $_POST['page_ip_restrict'] is ever set.
			$menuname = ($type ? ", page_theme = '".$tp -> toDB($_POST['menu_name'])."'" : "");
			$update = $sql -> db_Update("page", "page_title='{$page_title}', page_text='{$page_text}', page_datestamp='".time()."', page_author='{$pauthor}', page_rating_flag='".intval($_POST['page_rating_flag'])."', page_comment_flag='".intval($_POST['page_comment_flag'])."', page_password='".$_POST['page_password']."', page_class='".$_POST['page_class']."', page_ip_restrict='".varset($_POST['page_ip_restrict'],'')."'{$menuname} WHERE page_id='{$mode}'");
			$e107cache->clear("page_{$mode}");
			$e107cache->clear("page-t_{$mode}");

			if($type)  // it's a menu.
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
				else
				{
                  	$sql -> db_Insert('menus', "0, '$menu_name', '0', '0', '0', '', '".$mode."' ");
					if ($sql -> db_Insert('menus', $menuData))
					{
						$update++;
					}
				}
			}

			if ($_POST['page_link'])
			{
				if ($sql -> db_Select("links", "link_id", "link_url='page.php?".$mode."' && link_name!='".$tp -> toDB($_POST['page_link'])."'"))
				{
					$sql -> db_Update("links", "link_name='".$tp -> toDB($_POST['page_link'])."' WHERE link_url='page.php?".$mode."'");
					$update++;
					$e107cache->clear("sitelinks");
				}
				else if (!$sql -> db_Select("links", "link_id", "link_url='page.php?".$mode."'"))
				{
					$sql -> db_Insert("links", "0, '".$tp -> toDB($_POST['page_link'])."', 'page.php?".$mode."', '', '', 1, 0, 0, 0, ".$_POST['page_class']);
					$update++;
					$e107cache->clear("sitelinks");
				}
			} else {
				if ($sql -> db_Select("links", "link_id", "link_url='page.php?".$mode."'"))
				{
					$sql -> db_Delete("links", "link_url='page.php?".$mode."'");
					$update++;
					$e107cache->clear("sitelinks");
				}
			}
			admin_update($update, 'update', LAN_UPDATED);
		}
		else
		{	// New page/menu
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
		global $sql, $e107cache;
		admin_update($sql -> db_Delete("page", "page_id='$del_id' "), 'delete', CUSLAN_28);
		$sql -> db_Delete("menus", "menu_path='$del_id' ");
		if ($sql -> db_Select("links", "link_id", "link_url='page.php?".$del_id."'"))
		{
			$sql -> db_Delete("links", "link_url='page.php?".$del_id."'");
			$e107cache->clear("sitelinks");
		}
	}

	function optionsPage()
	{
		global $ns, $pref;

		if(!isset($pref['pageCookieExpire'])) $pref['pageCookieExpire'] = 84600;

		$text = "<div style='text-align: center; margin-left:auto; margin-right: auto;'>
		<form method='post' action='".e_SELF."'>
		<table style='".ADMIN_WIDTH."' class='fborder'>

		<tr>
		<td style='width:50%' class='forumheader3'>".CUSLAN_29."</td>
		<td style='width:50%; text-align: right;' class='forumheader3'>
		<input type='radio' name='listPages' value='1'".($pref['listPages'] ? " checked='checked'" : "")." /> ".CUSLAN_38."&nbsp;&nbsp;
		<input type='radio' name='listPages' value='0'".($pref['listPages'] ? "" : " checked='checked'")." /> ".CUSLAN_39."
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
		$this -> message = LAN_SETSAVED;
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
