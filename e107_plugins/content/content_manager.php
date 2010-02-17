<?php
/*
+---------------------------------------------------------------+
|        e107 website system
|        /admin/review.php
|
|        (C)Steve Dunstan 2001-2002
|        http://e107.org
|        jalist@e107.org
|
|        Released under the terms and conditions of the
|        GNU General Public License (http://gnu.org).
|
|		$Source: /cvs_backup/e107_0.7/e107_plugins/content/content_manager.php,v $
|		$Revision$
|		$Date$
|		$Author$
+---------------------------------------------------------------+
*/

require_once("../../class2.php");

$plugindir = e_PLUGIN."content/";
require_once($plugindir."content_shortcodes.php");

global $tp;
require_once(e_HANDLER."userclass_class.php");
require_once(e_HANDLER."form_handler.php");
$rs = new form;
e107_require_once(e_HANDLER.'arraystorage_class.php');
$eArrayStorage = new ArrayData();
require_once(e_HANDLER."file_class.php");
$fl = new e_file;
require_once($plugindir."handlers/content_class.php");
$aa = new content;
require_once($plugindir."handlers/content_db_class.php");
$adb = new contentdb;
require_once($plugindir."handlers/content_form_class.php");
$aform = new contentform;

include_lan($plugindir.'languages/'.e_LANGUAGE.'/lan_content_admin.php');

include_lan($plugindir.'languages/'.e_LANGUAGE.'/lan_content.php');

$deltest = array_flip($_POST);

if(e_QUERY)
{
	$qs = explode(".", e_QUERY);
}


if (!USER)
{	// non-user can never manage content
	header("location:".$plugindir."content.php"); 
	exit;
}


// define e_pagetitle
$aa -> setPageTitle();


if(isset($_POST['delete']))
{
	$tmp = array_pop(array_flip($_POST['delete']));
	list($delete, $del_id) = explode("_", $tmp);
}

//these have to be set for the tinymce wysiwyg
$e_wysiwyg	= "content_text";

//include js
function headerjs()
{
	echo "<script type='text/javascript' src='".e_FILE."popup.js'></script>\n";
}
// ##### DB ---------------------------------------------------------------------------------------



require_once(HEADERF);

if(isset($_POST['create_content']))
{
	if($_POST['content_text'] && $_POST['content_heading'] && $_POST['parent'] != "none")
	{
		$adb -> dbContent("create", "contentmanager");
	}
	else
	{
		$message = CONTENT_ADMIN_ITEM_LAN_0;
	}
}


if(isset($_POST['update_content']))
{
	if($_POST['content_text'] && $_POST['content_heading'] && $_POST['parent'] != "none")
	{
		$adb -> dbContent("update", "contentmanager");
	}
	else
	{
		$message = CONTENT_ADMIN_ITEM_LAN_0;
	}
}

if($delete == 'content' && is_numeric($del_id))
{
	if($sql -> db_Delete($plugintable, "content_id='{$del_id}' "))
	{
		$message = CONTENT_ADMIN_ITEM_LAN_3;
		$e107cache->clear("content");
	}
}

if(isset($message))
{
	$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

if(!e_QUERY)
{
	$aform -> show_contentmanager("edit", USERID, USERNAME);
	require_once(FOOTERF);
	exit;
}


	if($qs[0] == "c")
	{
		$message = CONTENT_ADMIN_ITEM_LAN_1."<br /><br />".CONTENT_ADMIN_ITEM_LAN_55;
		$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
		require_once(FOOTERF);
		exit;

	}
	elseif($qs[0] == "u")
	{
		$message = CONTENT_ADMIN_ITEM_LAN_2."<br /><br />".CONTENT_ADMIN_ITEM_LAN_55;
		$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
		require_once(FOOTERF);
		exit;

	//show list of items in this category
	}
	elseif($qs[0] == "content" && is_numeric($qs[1]))
	{
		$aform -> show_manage_content("contentmanager", USERID, USERNAME);

	//create new item
	}
	elseif($qs[0] == "content" && $qs[1] == "create" && is_numeric($qs[2]))
	{
		$aform -> show_create_content("contentmanager", USERID, USERNAME);

	//edit item
	}
	elseif($qs[0] == "content" && $qs[1] == "edit" && is_numeric($qs[2]))
	{
		$aform -> show_create_content("contentmanager", USERID, USERNAME);

	//manage submitted
	}
	elseif($qs[0] == "content" && $qs[1] == "submitted" && is_numeric($qs[2]))
	{
		//$aform -> show_submitted("contentmanager", USERID, USERNAME, $qs[2]);
		$aform -> show_submitted($qs[2]);

		//post submitted content item
	}
	elseif($qs[0] == "content" && $qs[1] == "sa" && is_numeric($qs[2]) )
	{
		$newqs = array_reverse($qs);
		if($newqs[0] == "cu")
		{										//item; submit post / update redirect
			$mainparent = $aa -> getMainParent($qs[2]);
			$message = CONTENT_ADMIN_ITEM_LAN_117."<br /><br />";
			$message .= CONTENT_ADMIN_ITEM_LAN_88." <a href='".e_SELF."?content.create.".$mainparent."'>".CONTENT_ADMIN_ITEM_LAN_90."</a><br />";
			$message .= CONTENT_ADMIN_ITEM_LAN_89." <a href='".e_SELF."?content.".$mainparent."'>".CONTENT_ADMIN_ITEM_LAN_90."</a><br />";
			$message .= CONTENT_ADMIN_ITEM_LAN_91." <a href='".e_SELF."?content.edit.".$qs[2]."'>".CONTENT_ADMIN_ITEM_LAN_90."</a><br />";
			$message .= CONTENT_ADMIN_ITEM_LAN_124." <a href='".e_PLUGIN."content/content.php?content.".$qs[2]."'>".CONTENT_ADMIN_ITEM_LAN_90."</a>";
			$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
			require_once(e_ADMIN."footer.php");
			exit;
		}
		$aform -> show_create_content("sa", USERID, USERNAME);

	}
	else
	{
		header("location:".e_SELF); exit;
	}



require_once(FOOTERF);



?>