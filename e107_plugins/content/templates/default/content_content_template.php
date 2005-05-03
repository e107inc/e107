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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/content/templates/default/content_content_template.php,v $
|     $Revision: 1.5 $
|     $Date: 2005-05-03 21:43:24 $
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

if(!$CONTENT_CONTENT_TABLE){
				$CONTENT_CONTENT_TABLE .= "
				<table class='content_table'>
				<tr>";

					if($CONTENT_CONTENT_TABLE_ICON){
						$CONTENT_CONTENT_TABLE .= "
						<td class='content_icon'>
							{CONTENT_CONTENT_TABLE_ICON}
						</td>";
					}

					$CONTENT_CONTENT_TABLE .= "
					<td colspan='".($CONTENT_CONTENT_TABLE_IMAGES ? "3" : ($CONTENT_CONTENT_TABLE_ICON ? "2" : "3"))."' style='width:97%;'>
						<table style='width:100%;'>
						<tr>
							<td class='content_heading' ".($CONTENT_CONTENT_TABLE_REFER ? "" : "colspan='2'").">
								{CONTENT_CONTENT_TABLE_HEADING}
							</td>";

							if($CONTENT_CONTENT_TABLE_REFER){
								$CONTENT_CONTENT_TABLE .= "
								<td class='content_info' style='text-align:right;'>
									".CONTENT_LAN_44." {CONTENT_CONTENT_TABLE_REFER}
								</td>";
							}

						$CONTENT_CONTENT_TABLE .= "
						</tr>
						<tr>
							<td class='content_subheading' ".(isset($CONTENT_CONTENT_TABLE_COMMENT) ? "" : "colspan='2'")." >
								{CONTENT_CONTENT_TABLE_SUBHEADING}
							</td>";

							if(isset($CONTENT_CONTENT_TABLE_COMMENT)){
								$CONTENT_CONTENT_TABLE .= "
								<td class='content_info' style='text-align:right;'>
									".CONTENT_LAN_57." {CONTENT_CONTENT_TABLE_COMMENT}
								</td>";
							}
						
						$CONTENT_CONTENT_TABLE .= "
						</tr>
						<tr>
							<td class='content_info' colspan='2'>
								{CONTENT_CONTENT_TABLE_DATE} / {CONTENT_CONTENT_TABLE_AUTHORDETAILS} {CONTENT_CONTENT_TABLE_EPICONS}
							</td>
						</tr>";

						if($CONTENT_CONTENT_TABLE_RATING){
							$CONTENT_CONTENT_TABLE .= "
							<tr>							
								<td class='content_rate' colspan='2'>
									{CONTENT_CONTENT_TABLE_RATING}
								</td>
							</tr>";
						}
						if($CONTENT_CONTENT_TABLE_FILE || $CONTENT_CONTENT_TABLE_SCORE){
							$CONTENT_CONTENT_TABLE .= "
							<tr>
								<td class='content_info'>".($CONTENT_CONTENT_TABLE_FILE ? "{CONTENT_CONTENT_TABLE_FILE}" : "")."</td>
								<td class='content_info' style='text-align:right;'>".($CONTENT_CONTENT_TABLE_SCORE ? CONTENT_LAN_45." {CONTENT_CONTENT_TABLE_SCORE}" : "")."</td>
							</tr>";
						}
						$CONTENT_CONTENT_TABLE .= "
						</table>
					</td>
				</tr>
				<tr>
					<td colspan='".($CONTENT_CONTENT_TABLE_IMAGES ? ($CONTENT_CONTENT_TABLE_ICON ? "4" : "3") : "3")."'><br /></td>
				</tr>
				<tr>
					<td colspan='".($CONTENT_CONTENT_TABLE_IMAGES ? ($CONTENT_CONTENT_TABLE_ICON ? "4" : "3") : "3")."' style='border-top:1px solid #000;'><br /></td>
				</tr>
				<tr>
					<td class='content_text' colspan='".($CONTENT_CONTENT_TABLE_IMAGES ? ($CONTENT_CONTENT_TABLE_ICON ? "3" : "2") : "3")."' style='width:97%;'>
						".($CONTENT_CONTENT_TABLE_SUMMARY ? "<i>{CONTENT_CONTENT_TABLE_SUMMARY}</i><br /><br />" : "")."
						".($CONTENT_CONTENT_TABLE_TEXT ? "{CONTENT_CONTENT_TABLE_TEXT}<br />" : "")."
					</td>
					".($CONTENT_CONTENT_TABLE_IMAGES ? "<td class='content_image'>{CONTENT_CONTENT_TABLE_IMAGES}</td>" : "")."
				</tr>";

				$CONTENT_CONTENT_TABLE .= "{CONTENT_CONTENT_TABLE_CUSTOM_TAGS}";

				if($CONTENT_CONTENT_TABLE_PAGENAMES){
					$CONTENT_CONTENT_TABLE .= "
					<tr>
						<td colspan='".($CONTENT_CONTENT_TABLE_IMAGES ? ($CONTENT_CONTENT_TABLE_ICON ? "4" : "3") : "3")."' style='border-bottom:1px solid #000;'><br /></td>
					</tr>
					<tr>
						<td class='content_text' colspan='".($CONTENT_CONTENT_TABLE_IMAGES ? ($CONTENT_CONTENT_TABLE_ICON ? "4" : "3") : "3")."'>
							<div class='content_bold'>".CONTENT_LAN_46."</div>
							{CONTENT_CONTENT_TABLE_PAGENAMES}
						</td>
					</tr>";
				}

				$CONTENT_CONTENT_TABLE .= "
				</table>\n";
}
// ##### ----------------------------------------------------------------------


if(!$CONTENT_CONTENT_TABLE_CUSTOM_PRE){
	$CONTENT_CONTENT_TABLE_CUSTOM_PRE = "
	<tr>
		<td colspan='".($CONTENT_CONTENT_TABLE_IMAGES ? ($CONTENT_CONTENT_TABLE_ICON ? "4" : "3") : "3")."' style='border-bottom:1px solid #000;'><br /></td>
	</tr>";
}
if(!$CONTENT_CONTENT_TABLE_CUSTOM_PRE2){
	$CONTENT_CONTENT_TABLE_CUSTOM_PRE2 = "
	<tr>
		<td colspan='".($CONTENT_CONTENT_TABLE_IMAGES ? ($CONTENT_CONTENT_TABLE_ICON ? "4" : "3") : "3")."'><br /></td>
	</tr>";
}

if(!$CONTENT_CONTENT_TABLE_CUSTOM){
	$CONTENT_CONTENT_TABLE_CUSTOM = "
	<tr>
		<td class='content_bold' style='width:5%;'>
			{CONTENT_CONTENT_TABLE_CUSTOM_KEY}
		</td>
		<td class='content_text' colspan='".($CONTENT_CONTENT_TABLE_IMAGES ? ($CONTENT_CONTENT_TABLE_ICON ? "3" : "2") : "2")."' style='width:95%;'>
			{CONTENT_CONTENT_TABLE_CUSTOM_VALUE}
		</td>
	</tr>";
}

?>