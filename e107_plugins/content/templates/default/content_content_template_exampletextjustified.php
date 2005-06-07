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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/content/templates/default/content_content_template_exampletextjustified.php,v $
|     $Revision: 1.5 $
|     $Date: 2005-06-07 19:37:24 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/

// ##### CONTENT CONTENT ------------------------------------------------------
$CONTENT_CONTENT_TABLE_START = "";
$CONTENT_CONTENT_TABLE = "";
$CONTENT_CONTENT_TABLE_END = "";
$CONTENT_CONTENT_TABLE_CUSTOM = "";
$CONTENT_CONTENT_TABLE_CUSTOM_PRE = "";
$CONTENT_CONTENT_TABLE_CUSTOM_PRE2 = "";
global $sc_style, $content_shortcodes, $qs, $row, $content_pref, $gen, $tp, $sql, $plugintable, $rater, $aa, $content_image_path, $content_icon_path, $content_file_path, $custom;

$sc_style['CONTENT_CONTENT_TABLE_REFER']['pre'] = CONTENT_LAN_44." ";
$sc_style['CONTENT_CONTENT_TABLE_REFER']['post'] = "";

$sc_style['CONTENT_CONTENT_TABLE_COMMENT']['pre'] = CONTENT_LAN_57." ";
$sc_style['CONTENT_CONTENT_TABLE_COMMENT']['post'] = "";

$sc_style['CONTENT_CONTENT_TABLE_SCORE']['pre'] = CONTENT_LAN_45." ";
$sc_style['CONTENT_CONTENT_TABLE_SCORE']['post'] = "";

$sc_style['CONTENT_CONTENT_TABLE_RATING']['pre'] = "<tr><td class='forumheader3' colspan='2'>";
$sc_style['CONTENT_CONTENT_TABLE_RATING']['post'] = "</td></tr>";

$sc_style['CONTENT_CONTENT_TABLE_AUTHORDETAILS']['pre'] = " / ";
$sc_style['CONTENT_CONTENT_TABLE_AUTHORDETAILS']['post'] = "";

$sc_style['CONTENT_CONTENT_TABLE_ICON']['pre'] = "<td class='forumheader3'>";
$sc_style['CONTENT_CONTENT_TABLE_ICON']['post'] = "</td>";

$sc_style['CONTENT_CONTENT_TABLE_PAGENAMES']['pre'] = "<tr><td class='forumheader3' colspan='4' style='border-top:1px solid #000;'><br /><div class='forumheader3'>".CONTENT_LAN_46."</div>";
$sc_style['CONTENT_CONTENT_TABLE_PAGENAMES']['post'] = "</td></tr>";

$sc_style['CONTENT_CONTENT_TABLE_CUSTOM_TAGS']['pre'] = "<tr><td colspan='4' style='border-top:1px solid #000;'><br /></td></tr>";
$sc_style['CONTENT_CONTENT_TABLE_CUSTOM_TAGS']['post'] = "<tr><td colspan='4'><br /></td></tr>";

$sc_style['CONTENT_CONTENT_TABLE_SUMMARY']['pre'] = "<i>";
$sc_style['CONTENT_CONTENT_TABLE_SUMMARY']['post'] = "</i>";

if(!$CONTENT_CONTENT_TABLE){
				$CONTENT_CONTENT_TABLE .= "
				<table class='fborder'>
				<tr>
					{CONTENT_CONTENT_TABLE_ICON}
					<td colspan='3' style='width:97%;'>
						<table style='width:100%;' cellpadding='0' cellspacing='0'>
						<tr>
							<td class='fcaption'>{CONTENT_CONTENT_TABLE_HEADING}</td>
							<td class='forumheader3' style='text-align:right;'>{CONTENT_CONTENT_TABLE_REFER}</td>
						</tr>
						<tr>
							<td class='forumheader'>{CONTENT_CONTENT_TABLE_SUBHEADING}</td>
							<td class='forumheader3' style='text-align:right;'>{CONTENT_CONTENT_TABLE_COMMENT}</td>
						</tr>
						<tr>
							<td class='forumheader3' colspan='2'>
								{CONTENT_CONTENT_TABLE_DATE} {CONTENT_CONTENT_TABLE_AUTHORDETAILS} {CONTENT_CONTENT_TABLE_EPICONS} {CONTENT_CONTENT_TABLE_EDITICON}
							</td>
						</tr>
						{CONTENT_CONTENT_TABLE_RATING}
						<tr>
							<td class='forumheader3'>{CONTENT_CONTENT_TABLE_FILE}</td>
							<td class='forumheader3' style='text-align:right;'>{CONTENT_CONTENT_TABLE_SCORE}</td>
						</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td colspan='4'><br /></td>
				</tr>
				<tr>
					<td colspan='4' style='border-top:1px solid #000;'><br /></td>
				</tr>
				<tr>
					
					<td class='forumheader3' colspan='3' style='width:97%; text-align:justified;'>
						{CONTENT_CONTENT_TABLE_SUMMARY}<br /><br />
						{CONTENT_CONTENT_TABLE_TEXT}<br />
					</td>
					<td class='forumheader3'>{CONTENT_CONTENT_TABLE_IMAGES}</td>
				</tr>
				{CONTENT_CONTENT_TABLE_CUSTOM_TAGS}
				{CONTENT_CONTENT_TABLE_PAGENAMES}
				</table>\n";
}
// ##### ----------------------------------------------------------------------

if(!$CONTENT_CONTENT_TABLE_CUSTOM){
	$CONTENT_CONTENT_TABLE_CUSTOM = "
	<tr>
		<td class='forumheader3' style='width:5%;'>
			{CONTENT_CONTENT_TABLE_CUSTOM_KEY}
		</td>
		<td class='forumheader3' colspan='2' style='width:95%;'>
			{CONTENT_CONTENT_TABLE_CUSTOM_VALUE}
		</td>
	</tr>";
}

?>