<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2008 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Plugin Administration - gsitemap
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/tinymce/admin_config.php,v $
 * $Revision: 1.1 $
 * $Date: 2009-07-01 02:52:08 $
 * $Author: e107coders $
 *
*/
require_once("../../class2.php");
if(!getperms("P") || !plugInstalled('tinymce'))
{
	header("location:".e_BASE."index.php");
	exit();
}

$e_wysiwyg = 'content';
require_once(e_ADMIN."auth.php");
require_once (e_HANDLER.'message_handler.php');
$emessage = &eMessage::getInstance();

if($_POST['save_settings'])
{
    $pref['tinymce']['customjs'] = $_POST['customjs'];
    $pref['tinymce']['theme_advanced_buttons1'] = $_POST['theme_advanced_buttons1'];
    $pref['tinymce']['theme_advanced_buttons2'] = $_POST['theme_advanced_buttons2'];
	$pref['tinymce']['theme_advanced_buttons3'] = $_POST['theme_advanced_buttons3'];
	$pref['tinymce']['theme_advanced_buttons4'] = $_POST['theme_advanced_buttons4'];
	$pref['tinymce']['plugins'] = $_POST['mce_plugins'];

	save_prefs();

    $emessage->add(LAN_UPDATED, E_MESSAGE_SUCCESS);
	$e107->ns->tablerender(LAN_UPDATED, $emessage->render());

}

    require_once(e_HANDLER."file_class.php");
    $fl = new e_file;

    if($plug_array = $fl->get_dirs(e_PLUGIN."tinymce/plugins/"))
    {
    	sort($plug_array);
    }

 	if(!$pref['tinymce']['theme_advanced_buttons1'])
	{
    	$pref['tinymce']['theme_advanced_buttons1'] = "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect";
	}

	if(!$pref['tinymce']['theme_advanced_buttons2'])
	{
    	$pref['tinymce']['theme_advanced_buttons2'] = "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor";
	}

	if(!$pref['tinymce']['theme_advanced_buttons3'])
	{
		$pref['tinymce']['theme_advanced_buttons3'] = "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen";
	}

	if(!$pref['tinymce']['theme_advanced_buttons4'])
	{
		$pref['tinymce']['theme_advanced_buttons4'] = "insertlayer,moveforward,movebackward,absolute,|,styleprops,spellchecker,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,blockquote,pagebreak,|,insertfile,insertimage";
	}



 $text = "<div style='text-align:center'>
    <form method='post' action='".e_SELF."' id='linkform'>
    <table style='".ADMIN_WIDTH."' class='fborder'>

    <tr>
    <td style='width:20%' class='forumheader3'>Preview</td>
    <td style='width:80%' class='forumheader3'>
    <textarea id='content' rows='10' cols='10' name='name3' class='tbox' style='width:80%'>     </textarea>
    </td>
    </tr>

    <tr>
    <td style='width:20%' class='forumheader3'>Installed Plugins</td>
    <td style='width:80%' class='forumheader3'><div style='width:80%'>
    ";

    foreach($plug_array as $mce_plg)
	{
		$checked = (in_array($mce_plg,$pref['tinymce']['plugins'])) ? "checked='checked'" : "";
    	$text .= "<div style='width:25%;float:left'><input type='checkbox' name='mce_plugins[]' value='".$mce_plg."' $checked /> $mce_plg </div>";
	}



	$text .= "</div>
    </td>
    </tr>

	<tr>
    <td class='forumheader3' style='width:20%' >Button Layout</td>
    <td style='width:80%' class='forumheader3'>";
    for ($i=1; $i<=4; $i++)
	{
		$rowNm = "theme_advanced_buttons".$i;
    	$text .= "\t<input class='tbox' style='width:97%' type='text' name='".$rowNm."' value='".$pref['tinymce'][$rowNm]."' />\n";
    }

	$text .= "
	</td>
	</tr>

	<tr>
    <td style='width:20%' class='forumheader3'>Custom TinyMce Javascript</td>
    <td style='width:80%' class='forumheader3'>
    <textarea rows='5' cols='10' name='customjs' class='tbox' style='width:80%'>".$pref['tinymce']['customjs']."</textarea>
    </td>
    </tr>



    <tr style='vertical-align:top'>
    <td colspan='2' style='text-align:center' class='forumheader'>";
    $text .= "<input class='button' type='submit' name='save_settings' value='".LAN_SAVE."' />";
    $text .= "</td>
    </tr>
    </table>
    </form>
    </div>";

    $ns -> tablerender("TinyMCE Configuration", $text);





require_once(e_ADMIN."footer.php");


function headerjs()
{
	require_once(e_HANDLER.'js_helper.php');
	//FIXME - how exactly to auto-call JS lan? This and more should be solved in Stage II.
	$ret = "
		<script type='text/javascript'>
			//add required core lan - delete confirm message
		</script>
		<script type='text/javascript' src='".e_FILE_ABS."jslib/core/admin.js'></script>
	";

   //	return $ret;
}

?>