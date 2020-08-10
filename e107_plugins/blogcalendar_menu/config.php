<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Plugin Administration - Blog calendar menu
 *
*/
$eplug_admin = TRUE;
require_once("../../class2.php");
require_once(e_HANDLER."userclass_class.php");
	
e107::includeLan(e_PLUGIN."blogcalendar_menu/languages/".e_LANGUAGE.".php");
if (!getperms("1")) 
{
	e107::redirect('admin');
	 exit ;
}
require_once(e_ADMIN."auth.php");

$frm = e107::getForm();
$mes = e107::getMessage();

if(!empty($_POST['update_menu']))
{
	$cfg = e107::getConfig();
	$cfg->set('blogcal_mpr', intval($_POST['blogcal_mpr']));
	$cfg->set('blogcal_padding', intval($_POST['blogcal_padding']));
	$cfg->save(true,true,true);

}

$ns->tablerender($caption, $mes->render() . $text);

$text = "
	<form method='post' action='".e_SELF."?".e_QUERY."' name='menu_conf_form'>
	<table class='table adminform' >
	<colgroup span='2'>
    	<col class='col-label' />
    	<col class='col-control' />
    </colgroup>
	 
	<tr>
		<td>".BLOGCAL_CONF1.": </td>
		<td>
			<select class='tbox' name='blogcal_mpr'>";
	
			// if the nr of months per row is undefined, default to 3
			$months_per_row = $pref['blogcal_mpr']?$pref['blogcal_mpr']:
			"3";
			for($i = 1; $i <= 12; $i++) {
				$text .= "<option value='$i'";
				$text .= $months_per_row == $i?"selected":
				"";
				$text .= ">$i</option>";
			}
				
			$text .= "</select>
		</td>
	</tr>
	 
	<tr>
		<td>".BLOGCAL_CONF2.": </td>
		<td><input class='tbox' type='text' name='blogcal_padding' size='20' value='";
		// if the cellpadding isn't defined
		$padding = $pref['blogcal_padding']?$pref['blogcal_padding']:
		"2";
		$text .= $padding;
		$text .= "' maxlength='100' />
		</td>
	</tr>
	</table>
	
	<div class='buttons-bar center'>
		".$frm->admin_button('update_menu', LAN_UPDATE, 'update')." 

	</div>
	</form>
";

$ns->tablerender(BLOGCAL_CONF4, $text);
	
require_once(e_ADMIN."footer.php");
