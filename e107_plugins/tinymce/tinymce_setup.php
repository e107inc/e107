<?php
/*
* e107 website system
*
* Copyright ( c ) 2001-2008 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
* Custom TinyMce install/uninstall/update routines
*
* $Source: /cvs_backup/e107_0.8/e107_plugins/tinymce/tinymce_setup.php,v $
* $Revision: 1.2 $
* $Date: 2009-10-21 12:53:00 $
* $Author: e107coders $
*
*/

class tinymce_setup
{
/*	
 	function install_pre($var)
	{
		// print_a($var);
		// echo "custom install 'pre' function<br /><br />";
	}
*/
	function install_post($var)
	{
		$sql = e107::getDb();
		$mes = eMessage::getInstance();
		
		$query = "INSERT INTO #tinymce (
		`tinymce_id`, `tinymce_name`, `tinymce_userclass`, `tinymce_plugins`, `tinymce_buttons1`, `tinymce_buttons2`, `tinymce_buttons3`, `tinymce_buttons4`, `tinymce_custom`, `tinymce_prefs`) VALUES 
		(1, 'Simple Users', '252', 'e107bbcode,emoticons', 'bold, italic, underline, undo, redo, link, unlink, image, forecolor, bullist, numlist, outdent, indent, emoticons', '', '', '', '', ''),
		(2, 'Members', '253', 'e107bbcode,emoticons,table', 'bold, italic, underline, undo, redo, link, unlink, image, forecolor, removeformat, table, bullist, numlist, outdent, indent, emoticons', '', '', '', '', ''),
		(3, 'Administrators', '254', 'contextmenu,e107bbcode,emoticons,ibrowser,iespell,paste,table,xhtmlxtras', 'bold, italic, underline, undo, redo, link, unlink, image, forecolor, removeformat, table, bullist, numlist, outdent, indent, cleanup, code, emoticons', '', '', '', '', ''),
		(4, 'Main Admin', '250', 'advhr,advlink,autoresize,compat2x,contextmenu,directionality,emoticons,ibrowser,paste,table,visualchars,wordcount,xhtmlxtras,zoom', 'bold, italic, underline, undo, redo, link, unlink, ibrowser, forecolor, removeformat, table, bullist, numlist, outdent, indent, cleanup, code, emoticons', '', '', '', '', ''
		);";
		
		if($sql->db_Select_gen($query))
		{
			$mes->add("Default data added to table.", E_MESSAGE_SUCCESS);
		}
		else
		{
			$mes->add("Failed to add default table data.", E_MESSAGE_ERROR);	
		}

	}
/*
	function uninstall_post($var)
	{
		// $sql = e107::getDb();
	}

	function upgrade_post($var)
	{
		// $sql = e107::getDb();
	}
*/	
}
?>