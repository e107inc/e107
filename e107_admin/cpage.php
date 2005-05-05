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
|     $Revision: 1.9 $
|     $Date: 2005-05-05 14:12:21 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/

require_once("../class2.php");
if (!getperms("2")){
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
unset($message);


if (e_QUERY)
{
	$tmp = explode(".", e_QUERY);
	$action = $tmp[0];
	$sub_action = $tmp[1];
	$id = $tmp[2];
	$from = ($tmp[3] ? $tmp[3] : 0);
	unset($tmp);
}

if(IsSet($_POST['submitPage']))
{
	$page -> submitPage();
}

if(IsSet($_POST['submitMenu']))
{
	$page -> submitPage("", TRUE);
}

if(IsSet($_POST['updateMenu']))
{
	$page -> submitPage($_POST['pe_id'], TRUE);
}


if(IsSet($_POST['updatePage']))
{
	$page -> submitPage($_POST['pe_id']);
}

$delm = array_pop(array_flip($_POST));
if (preg_match("#delete_(.*?)_#", $delm, $match))
{
	$page -> delete_page($match[1]);
}

if (isset($_POST['saveOptions'])) {
	$page -> saveSettings();
}


if($page -> $message)
{
	$ns->tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
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

		$text = "<div style='text-align: center;'>
		";

		if(!$sql -> db_Select("page", "*", "ORDER BY page_datestamp DESC", "nowhere"))
		{
			$text .= "No pages defined yet";
		}
		else
		{
			$pages = $sql -> db_getList();
			$text .= "<form action='".e_SELF."' id='newsform' method='post'>
			<table style='".ADMIN_WIDTH."' class='fborder'>
			<tr>
			<td style='width:5%; text-align: center;' class='forumheader'>ID</td>
			<td style='width:60%' class='forumheader3'>Title</td>
			<td style='width:15%; text-align: center;' class='forumheader3'>Type</td>
			<td style='width:20%; text-align: center;' class='forumheader3'>Options</td>
			</tr>
			";

			foreach($pages as $pge)
			{
				extract($pge);
				$text .= "
				<tr>
				<td style='width:5%; text-align: center;' class='forumheader'>$page_id</td>
				<td style='width:60%' class='forumheader3'><a href='".e_BASE."page.php?$page_id'>$page_title</a></td>
				<td style='width:15%; text-align: center;' class='forumheader3'>".($page_theme ? "menu" : "page")."</td>
				<td style='width:20%; text-align: center;' class='forumheader3'>
				<a href='".e_SELF."?".($page_theme ? "createm": "create").".edit.{$page_id}'>".ADMIN_EDIT_ICON."</a>
				<input type='image' title='".LAN_DELETE."' name='delete_$page_id' src='".ADMIN_DELETE_ICON_PATH."' onclick=\"return jsconfirm('Delete this page? [ ID: $page_id ]')\"/>
				</td>
				</tr>
				";
			}
		
			$text .= "</form>
			</table>
			";
		}

		$text .= "</div>
		";

		$ns -> tablerender("Existing Pages", $text);

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
				extract($row);
				$page_title = $tp -> toFORM($page_title);
				$data = $tp -> toFORM($page_text);
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
			<td style='width:30%' class='forumheader3'>Menu Name</td>
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
		<td style='width:30%' class='forumheader3'>Title / Caption</td>
		<td style='width:70%' class='forumheader3'><input class='tbox' type='text' name='page_title' size='50' value='".$page_title."' maxlength='250' /></td>
		</tr>
		
		</td>
		</tr>
		
		<tr>
		<td style='width:30%' class='forumheader3'>Text</td>
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
			<td style='width:30%' class='forumheader3'>Allow page to be rated</td>
			<td style='width:70%;' class='forumheader3'>
			<input type='radio' name='page_rating_flag' value='1'".($page_rating_flag ? " checked='checked'" : "")." /> yes&nbsp;&nbsp;
			<input type='radio' name='page_rating_flag' value='0'".(!$page_rating_flag ? " checked='checked'" : "")." /> no
			</td>
			</tr>

			<tr>
			<td style='width:30%' class='forumheader3'>Allow comments</td>
			<td style='width:70%;' class='forumheader3'>
			<input type='radio' name='page_comment_flag' value='1'".($page_comment_flag ? " checked='checked'" : "")." /> yes&nbsp;&nbsp;
			<input type='radio' name='page_comment_flag' value='0'".(!$page_comment_flag ? " checked='checked'" : "")." /> no
			</td>
			</tr>

			<tr>
			<td style='width:30%' class='forumheader3'>Password protect page<br /><span class='smalltext'>enter password to protect page</span></td>
			<td style='width:70%' class='forumheader3'><input class='tbox' type='text' name='page_password' size='20' value='".$page_password."' maxlength='50' /></td>
			</tr>

			<tr>
			<td style='width:30%' class='forumheader3'>Create link in main menu<br /><span class='smalltext'>enter link name to create</span></td>
			<td style='width:70%' class='forumheader3'><input class='tbox' type='text' name='page_link' size='60' value='' maxlength='50' /></td>
			</tr>

			<tr>
			<td style='width:30%' class='forumheader3'>Page / link visible to</td>
			<td style='width:70%' class='forumheader3'>".r_userclass("page_class", $page_class)."</td>
			</tr>
			";
		}

		$text .= "<tr>
		<td colspan='2' style='text-align:center' class='forumheader'>". 

		(!$mode ? 

		($edit ? "<input class='button' type='submit' name='updatePage' value='Update Page' /><input type='hidden' name='pe_id' value='$id' />" : "<input class='button' type='submit' name='submitPage' value='Create Page' />") : 
		
		($edit ? "<input class='button' type='submit' name='updateMenu' value='Update Menu' /><input type='hidden' name='pe_id' value='$id' />" : "<input class='button' type='submit' name='submitMenu' value='Create Menu' />"))
		
		."
		</td>
		</tr>

		</table>
		</form>
		</div>
		";

		$caption =(!$mode ? ($edit ? "Edit page" : "Create new page") : ($edit ? "Edit menu" : "Create new menu"));
		$ns -> tablerender($caption, $text);
	}


	function submitPage($mode = FALSE, $type=FALSE)
	{
		global $sql, $tp, $e107cache;

		$page_title = $tp -> toDB($_POST['page_title']);
		$page_text = $tp -> toDB($_POST['data']);

		if($mode)
		{
			$sql -> db_Update("page", "page_title='$page_title', page_text='$page_text', page_rating_flag='".$_POST['page_rating_flag']."', page_comment_flag='".$_POST['page_comment_flag']."', page_password='".$_POST['page_password']."', page_class='".$_POST['page_class']."', page_ip_restrict='".$_POST['page_ip_restrict']."' WHERE page_id='$mode' ");
			$this -> message = "Page updated in database.";
		}
		else
		{

			$menuname = ($type ? $tp -> toDB($_POST['menu_name']) : "");

			$sql -> db_Insert("page", "0, '$page_title', '$page_text', '".USERID."', '".time()."', '".$_POST['page_rating_flag']."', '".$_POST['page_comment_flag']."', '".$_POST['page_password']."', '".$_POST['page_class']."', '', '".$menuname."' ");
			$this -> message = "Page saved to database.";

			if($type)
			{
				$sql -> db_Insert("menus", "0, '$menuname', '0', '0', '0', 'dbcustom', '".mysql_insert_id()."' ");
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
		$sql -> db_Delete("page", "page_id='$del_id' ");
		$sql -> db_Delete("menus", "menu_path='$del_id' ");
		$this -> message = "Page deleted";
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
		<td style='width:50%' class='forumheader3'>List pages if no page selected</td>
		<td style='width:50%; text-align: right;' class='forumheader3'>
		<input type='radio' name='listPages' value='1'".($pref['listPages'] ? " checked='checked'" : "")." /> ".ADSTAT_ON."&nbsp;&nbsp;
		<input type='radio' name='listPages' value='0'".(!$pref['listPages'] ? " checked='checked'" : "")." /> ".ADSTAT_OFF."
		</td>
		</tr>

		<tr>
		<td style='width:50%' class='forumheader3'>Expiry time for cookie (in seconds)</td>
		<td style='width:50%; text-align: right;' class='forumheader3'>
		<input class='tbox' type='text' name='pageCookieExpire' size='15' value='".$pref['pageCookieExpire']."' maxlength='10' />
		</td>
		</tr>

		<tr>
		<td colspan='2'  style='text-align:center' class='forumheader'>
		<input class='button' type='submit' name='saveOptions' value='".ADSTAT_L15."' />
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
		$var['main']['text'] = "Front page";
		$var['main']['link'] = e_SELF;

		$var['create']['text'] = "Create page";
		$var['create']['link'] = e_SELF."?create";

		$var['createm']['text'] = "Create menu";
		$var['createm']['link'] = e_SELF."?createm";

		$var['options']['text'] = LAN_OPTIONS;
		$var['options']['link'] = e_SELF."?options";

		require_once(e_HANDLER."file_class.php");
		$file = new e_file;
		$reject = array('$.','$..','/','CVS','thumbs.db','*._$', 'index', 'null*', 'Readme.txt');
		$cpages = $file -> get_files(e_PLUGIN."custompages", "", $reject);
		$cmenus = $file -> get_files(e_PLUGIN."custom", "", $reject);

		if(count($cpages) || count($cmenus))
		{
			$var['convert']['text'] = "Convert old pages/menus";
			$var['convert']['link'] = e_SELF."?convert";
		}

		show_admin_menu("Page Options", $action, $var);
	}


	function convertPage()
	{
		global $tp, $ns, $sql;
		require_once(e_HANDLER."file_class.php");
		$file = new e_file;
		$reject = array('$.','$..','/','CVS','thumbs.db','*._$', 'index', 'null*', 'Readme.txt');
		$cpages = $file -> get_files(e_PLUGIN."custompages", "", $reject);
		$cmenus = $file -> get_files(e_PLUGIN."custom", "", $reject);

		$customs = array_merge($cpages, $cmenus);

		$text = "<b>Beginning conversion ...</b><br /><br />";

		$count = 0;
		foreach($customs as $p)
		{
			$type = (strstr($p['path'], "custompages") ? "" : str_replace(".php", "", $p['fname']));
			$filename = $p['path'].$p['fname'];
			$handle = fopen ($filename, "r");
			$contents = fread ($handle, filesize ($filename));
			fclose ($handle);
			$contents = str_replace("'", "&#039;", $contents);
			preg_match('#\$caption = "(.*?)";#si', $contents, $match);
			$page_title = $tp -> toDB(trim(chop($match[1])));
			preg_match('#\$text = "(.*?)";#si', $contents, $match);
			$page_text = $tp -> toDB(trim(chop($match[1])));
			$filetime = filemtime($filename);

			if(!$sql -> db_Select("page", "*", "page_title='$page_title' "))
			{
				$sql -> db_Insert("page", "0, '$page_title', '$page_text', '".USERID."', '".$filetime."', '0', '0', '', '', '', '$type' ");
				$text .= "<b>Inserting: </b> '".$page_title."' <br />";
				$count ++;
			}

			if($type)
			{
				$iid = mysql_insert_id();
				if(!$sql -> db_Select("menus", "*", "menu_path='$iid' "))
				{
					$sql -> db_Insert("menus", "0, '$type', '0', '0', '0', 'dbcustom', '$iid' ");
					$type2 = "custom_".$type;
					$sql -> db_Delete("menus", "menu_name='$type2' ");
				}
			}

		}
		$text .= "<br />Finished custom page update - updated $count ".($count == 1 ? "file" : "files").".<br /> To set your preferences for each page, please return to front page and edit the pages.";
		$ns -> tablerender("Custom Page Update", $text);
	}








	


}

function cpage_adminmenu() {
	global $page;
	global $action;
	$page -> show_options($action);
}