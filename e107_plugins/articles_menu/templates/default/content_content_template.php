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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/articles_menu/templates/default/content_content_template.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-02-03 23:20:38 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/

// ##### CONTENT CONTENT ------------------------------------------------------
$CONTENT_CONTENT_TABLE_START = "";
$CONTENT_CONTENT_TABLE = "";
$CONTENT_CONTENT_TABLE_END = "";

if(!$CONTENT_CONTENT_TABLE){
				$CONTENT_CONTENT_TABLE .= "
				<div style='text-align:center'>
				<table style='width:95%;' border='0'>
				<tr>";

					if($CONTENT_CONTENT_TABLE_ICON){
					$CONTENT_CONTENT_TABLE .= "
					<td style='width:5%; white-space:nowrap; vertical-align:top; padding:0; '>
						{CONTENT_CONTENT_TABLE_ICON}
					</td>";
					}

					$CONTENT_CONTENT_TABLE .= "
					<td colspan='".($CONTENT_CONTENT_TABLE_IMAGES ? "3" : ($CONTENT_CONTENT_TABLE_ICON ? "2" : "3"))."' style='vertical-align:top; padding-top:2px; padding:0;'>
						<table style='width:100%; vertical-align:top;' border='0'>
						<tr>
							<td ".($CONTENT_CONTENT_TABLE_REFER ? "" : "colspan='2'")." style='text-align:left; padding:2px; vertical-align:top; border:0;'>
								{CONTENT_CONTENT_TABLE_HEADING}
							</td>";

							if($CONTENT_CONTENT_TABLE_REFER){
							$CONTENT_CONTENT_TABLE .= "
							<td style='text-align:right; padding:2px; vertical-align:top; border:0;'>
								<span class='smalltext'>".CONTENT_LAN_44." {CONTENT_CONTENT_TABLE_REFER}</span>
							</td>";
							}

						$CONTENT_CONTENT_TABLE .= "
						</tr>
						<tr>
							<td ".(isset($CONTENT_CONTENT_TABLE_COMMENT) ? "" : "colspan='2'")." style='text-align:left; padding:2px; vertical-align:top; border:0;'>
								<i>{CONTENT_CONTENT_TABLE_SUBHEADING}</i>
							</td>";

							if(isset($CONTENT_CONTENT_TABLE_COMMENT)){
							$CONTENT_CONTENT_TABLE .= "
							<td style='text-align:right; padding:2px; vertical-align:top; border:0;'>
								<span class='smalltext'>".CONTENT_LAN_57." {CONTENT_CONTENT_TABLE_COMMENT}</span>
							</td>";
							}
						
						$CONTENT_CONTENT_TABLE .= "
						</tr>
						<tr>
							<td colspan='2' style='text-align:left; padding:2px; vertical-align:top; border:0;'>
								<span class='smalltext'>{CONTENT_CONTENT_TABLE_DATE} / {CONTENT_CONTENT_TABLE_AUTHORDETAILS} {CONTENT_CONTENT_TABLE_EPICONS}</span>
							</td>
						</tr>";

						if($CONTENT_CONTENT_TABLE_RATING){
						$CONTENT_CONTENT_TABLE .= "
						<tr>							
							<td colspan='2' style='padding:2px; vertical-align:top; border:0;'>
								<span class='smalltext'>{CONTENT_CONTENT_TABLE_RATING}</span>
							</td>
						</tr>";
						}
						
						if($CONTENT_CONTENT_TABLE_FILE){
						$CONTENT_CONTENT_TABLE .= "
						<tr>
							<td colspan='2' style='padding:2px; vertical-align:top; border:0;'>
								<span class='smalltext'>".($CONTENT_CONTENT_TABLE_FILE ? "{CONTENT_CONTENT_TABLE_FILE}" : "")."</span>
							</td>
						</tr>";
						}

						if($CONTENT_CONTENT_TABLE_SCORE){
						$CONTENT_CONTENT_TABLE .= "
						<tr>
							<td colspan='2' style='padding:2px; vertical-align:top; border:0;'>
								<span class='smalltext'>".CONTENT_LAN_45." {CONTENT_CONTENT_TABLE_SCORE}</span>
							</td>
						</tr>";
						}

						$CONTENT_CONTENT_TABLE .= "
						</table>
					</td>
				</tr>
				<tr>
					<td colspan='".($CONTENT_CONTENT_TABLE_IMAGES ? ($CONTENT_CONTENT_TABLE_ICON ? "4" : "3") : "3")."' style='border-top:1px solid #000; vertical-align:top;'><br /></td>
				</tr>
				<tr>
					<td colspan='".($CONTENT_CONTENT_TABLE_IMAGES ? ($CONTENT_CONTENT_TABLE_ICON ? "3" : "2") : "3")."' style='vertical-align:top;'>
						".($CONTENT_CONTENT_TABLE_SUMMARY ? "<i>{CONTENT_CONTENT_TABLE_SUMMARY}</i><br /><br />" : "")."
						".($CONTENT_CONTENT_TABLE_TEXT ? "{CONTENT_CONTENT_TABLE_TEXT}<br />" : "")."
					</td>
					".($CONTENT_CONTENT_TABLE_IMAGES ? "<td style='width:5%; white-space:nowrap; vertical-align:top; padding-left:20px;'>{CONTENT_CONTENT_TABLE_IMAGES}</td>" : "")."
				</tr>";

				if($CONTENT_CONTENT_TABLE_PAGENAMES){
					$CONTENT_CONTENT_TABLE .= "
					<tr>
						<td colspan='".($CONTENT_CONTENT_TABLE_IMAGES ? ($CONTENT_CONTENT_TABLE_ICON ? "4" : "3") : "3")."' style='border-bottom:1px solid #000; vertical-align:top;'><br /></td>
					</tr>
					<tr>
						<td colspan='".($CONTENT_CONTENT_TABLE_IMAGES ? ($CONTENT_CONTENT_TABLE_ICON ? "3" : "2") : "3")."' style='vertical-align:top;'>
							<div style='font-weight:bold;'>".CONTENT_LAN_46."</div>
							{CONTENT_CONTENT_TABLE_PAGENAMES}
						</td>
					</tr>";
				}

				if($CONTENT_CONTENT_TABLE_CUSTOM_KEY){
					$CONTENT_CONTENT_TABLE .= "
					<tr>
						<td colspan='".($CONTENT_CONTENT_TABLE_IMAGES ? ($CONTENT_CONTENT_TABLE_ICON ? "4" : "3") : "3")."' style='border-bottom:1px solid #000; vertical-align:top;'><br /></td>
					</tr>";

					for($i=0;$i<count($CONTENT_CONTENT_TABLE_CUSTOM_KEY);$i++){
					
					$CONTENT_CONTENT_TABLE .= "
					<tr>
						<td colspan='2' style='width:20%; white-space:nowrap; vertical-align:top;'>
							<span class='smalltext'>{CONTENT_CONTENT_TABLE_CUSTOM_KEY[$i]}</span>
						</td>
						<td colspan='".($CONTENT_CONTENT_TABLE_IMAGES ? ($CONTENT_CONTENT_TABLE_ICON ? "3" : "2") : "2")."' style='vertical-align:top;'>
							<span class='smalltext'>{CONTENT_CONTENT_TABLE_CUSTOM_VALUE[$i]}</span>
						</td>
					</tr>";
					}
				}


				$CONTENT_CONTENT_TABLE .= "
				</table>
				</div>\n";
}
// ##### ----------------------------------------------------------------------

?>