<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2008 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Plugin Administration - PDF generator
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/pdf/admin_pdf_config.php,v $
 * $Revision: 1.2 $
 * $Date: 2008-12-21 12:03:28 $
 * $Author: e107steved $
 *
*/

require_once('../../class2.php');
if (!getperms("P") || !plugInstalled('pdf')) 
{
	header('location:'.e_BASE.'index.php');
	exit;
}
require_once(e_ADMIN.'auth.php');
require_once(e_HANDLER.'form_handler.php');
$rs = new form;
e107_require_once(e_HANDLER.'arraystorage_class.php');
$eArrayStorage = new ArrayData();
unset($text);

include_lan(e_PLUGIN.'pdf/languages/English_admin_pdf.php');

if(isset($_POST['update_pdf']))
{
	$message = updatePDFPrefs();
}


function updatePDFPrefs()
{
	global $sql, $eArrayStorage, $tp, $admin_log;
	while(list($key, $value) = each($_POST))
	{
		foreach($_POST as $k => $v)
		{
			if(strpos($k, 'pdf_') === 0)
			{
				$pdfpref[$k] = $tp->toDB($v);
			}
		}
	}
	//create new array of preferences
	$tmp = $eArrayStorage->WriteArray($pdfpref);
	$sql -> db_Update("core", "e107_value='{$tmp}' WHERE e107_name='pdf' ");
	$admin_log->logArrayAll('PDF_01',$pdfpref);
	$message = PDF_LAN_18;
	return $message;
}


function getDefaultPDFPrefs()
{
	$pdfpref['pdf_margin_left']				= '25';
	$pdfpref['pdf_margin_right']			= '15';
	$pdfpref['pdf_margin_top']				= '15';
	$pdfpref['pdf_font_family']				= 'arial';
	$pdfpref['pdf_font_size']				= '8';
	$pdfpref['pdf_font_size_sitename']		= '14';
	$pdfpref['pdf_font_size_page_url']		= '8';
	$pdfpref['pdf_font_size_page_number']	= '8';
	$pdfpref['pdf_show_logo']				= true;
	$pdfpref['pdf_show_sitename']			= false;
	$pdfpref['pdf_show_page_url']			= true;
	$pdfpref['pdf_show_page_number']		= true;
	$pdfpref['pdf_error_reporting']			= true;
	return $pdfpref;
}



function getPDFPrefs()
{
	global $sql, $eArrayStorage;

	if(!is_object($sql)){ $sql = new db; }
	$num_rows = $sql -> db_Select("core", "*", "e107_name='pdf' ");
	if($num_rows == 0)
	{
		$tmp = getDefaultPDFPrefs();
		$tmp2 = $eArrayStorage->WriteArray($tmp);
		$sql -> db_Insert("core", "'pdf', '".$tmp2."' ");
		$sql -> db_Select("core", "*", "e107_name='pdf' ");
	}
	$row = $sql -> db_Fetch();
	$pdfpref = $eArrayStorage->ReadArray($row['e107_value']);
	return $pdfpref;
}


if(isset($message))
{
	$caption = PDF_LAN_1;
	$ns -> tablerender($caption, $message);
}

$pdfpref = getPDFPrefs();

if(!is_object($sql)){ $sql = new db; }

$text = "
<div style='text-align:center'>
".$rs -> form_open("post", e_SELF, "pdfform", "", "enctype='multipart/form-data'")."
<table class='fborder' style='".ADMIN_WIDTH."'>

<tr>
	<td class='forumheader3' style='width:30%; white-space:nowrap;'>".PDF_LAN_5."</td>
	<td class='forumheader3' style='width:70%;'>".$rs -> form_text("pdf_margin_left", 10, $pdfpref['pdf_margin_left'], 10)."</td>
</tr>
<tr>
	<td class='forumheader3' style='width:30%; white-space:nowrap;'>".PDF_LAN_6."</td>
	<td class='forumheader3' style='width:70%;'>".$rs -> form_text("pdf_margin_right", 10, $pdfpref['pdf_margin_right'], 10)."</td>
</tr>
<tr>
	<td class='forumheader3' style='width:30%; white-space:nowrap;'>".PDF_LAN_7."</td>
	<td class='forumheader3' style='width:70%;'>".$rs -> form_text("pdf_margin_top", 10, $pdfpref['pdf_margin_top'], 10)."</td>
</tr>";

$fontlist=array("arial","times","courier","helvetica","symbol");
$text .= "
<tr>
	<td class='forumheader3' style='width:30%; white-space:nowrap;'>".PDF_LAN_8."</td>
	<td class='forumheader3' style='width:70%;'>
		".$rs -> form_select_open("pdf_font_family");
		foreach($fontlist as $font){
			$text .= $rs -> form_option($font, ($pdfpref['pdf_font_family'] == $font ? "1" : "0"), $font);
		}
		$text .= $rs -> form_select_close()."
	</td>
</tr>

<tr>
	<td class='forumheader3' style='width:30%; white-space:nowrap;'>".PDF_LAN_9."</td>
	<td class='forumheader3' style='width:70%;'>".$rs -> form_text("pdf_font_size", 10, $pdfpref['pdf_font_size'], 10)."</td>
</tr>
<tr>
	<td class='forumheader3' style='width:30%; white-space:nowrap;'>".PDF_LAN_10."</td>
	<td class='forumheader3' style='width:70%;'>".$rs -> form_text("pdf_font_size_sitename", 10, $pdfpref['pdf_font_size_sitename'], 10)."</td>
</tr>
<tr>
	<td class='forumheader3' style='width:30%; white-space:nowrap;'>".PDF_LAN_11."</td>
	<td class='forumheader3' style='width:70%;'>".$rs -> form_text("pdf_font_size_page_url", 10, $pdfpref['pdf_font_size_page_url'], 10)."</td>
</tr>
<tr>
	<td class='forumheader3' style='width:30%; white-space:nowrap;'>".PDF_LAN_12."</td>
	<td class='forumheader3' style='width:70%;'>".$rs -> form_text("pdf_font_size_page_number", 10, $pdfpref['pdf_font_size_page_number'], 10)."</td>
</tr>
<tr>
	<td class='forumheader3' style='width:30%; white-space:nowrap;'>".PDF_LAN_13."</td>
	<td class='forumheader3' style='width:70%;'>
		".$rs -> form_radio("pdf_show_logo", "1", ($pdfpref['pdf_show_logo'] ? "1" : "0"), "", "").PDF_LAN_3."
		".$rs -> form_radio("pdf_show_logo", "0", ($pdfpref['pdf_show_logo'] ? "0" : "1"), "", "").PDF_LAN_4."
	</td>
</tr>
<tr>
	<td class='forumheader3' style='width:30%; white-space:nowrap;'>".PDF_LAN_14."</td>
	<td class='forumheader3' style='width:70%;'>
		".$rs -> form_radio("pdf_show_sitename", "1", ($pdfpref['pdf_show_sitename'] ? "1" : "0"), "", "").PDF_LAN_3."
		".$rs -> form_radio("pdf_show_sitename", "0", ($pdfpref['pdf_show_sitename'] ? "0" : "1"), "", "").PDF_LAN_4."
	</td>
</tr>
<tr>
	<td class='forumheader3' style='width:30%; white-space:nowrap;'>".PDF_LAN_15."</td>
	<td class='forumheader3' style='width:70%;'>
		".$rs -> form_radio("pdf_show_page_url", "1", ($pdfpref['pdf_show_page_url'] ? "1" : "0"), "", "").PDF_LAN_3."
		".$rs -> form_radio("pdf_show_page_url", "0", ($pdfpref['pdf_show_page_url'] ? "0" : "1"), "", "").PDF_LAN_4."
	</td>
</tr>
<tr>
	<td class='forumheader3' style='width:30%; white-space:nowrap;'>".PDF_LAN_16."</td>
	<td class='forumheader3' style='width:70%;'>
		".$rs -> form_radio("pdf_show_page_number", "1", ($pdfpref['pdf_show_page_number'] ? "1" : "0"), "", "").PDF_LAN_3."
		".$rs -> form_radio("pdf_show_page_number", "0", ($pdfpref['pdf_show_page_number'] ? "0" : "1"), "", "").PDF_LAN_4."
	</td>
</tr>
<tr>
	<td class='forumheader3' style='width:30%; white-space:nowrap;'>".PDF_LAN_20."</td>
	<td class='forumheader3' style='width:70%;'>
		".$rs -> form_radio("pdf_error_reporting", "1", ($pdfpref['pdf_error_reporting'] ? "1" : "0"), "", "").PDF_LAN_3."
		".$rs -> form_radio("pdf_error_reporting", "0", ($pdfpref['pdf_error_reporting'] ? "0" : "1"), "", "").PDF_LAN_4."
	</td>
</tr>

<tr>
	<td style='text-align:center' class='forumheader' colspan='2'>".$rs -> form_button("submit", "update_pdf", PDF_LAN_17)."</td>
</tr>

</table>
".$rs -> form_close()."
</div>";

$ns -> tablerender(PDF_LAN_2, $text);

require_once(e_ADMIN."footer.php");

?>