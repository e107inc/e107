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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/content/templates/default/content_submit_template.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-02-03 23:31:40 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/

// ##### CONTENT SUBMIT TYPE LIST --------------------------------------------------
if(!$CONTENT_SUBMIT_TYPE_TABLE_START){
				$CONTENT_SUBMIT_TYPE_TABLE_START = "
				<div style='text-align:center'>
				<table style='width:95%' border='0'>\n";
}
if(!$CONTENT_SUBMIT_TYPE_TABLE){
				$CONTENT_SUBMIT_TYPE_TABLE = "
				<tr>
					<td>
						<table style='width:100%' border='0'>
							<tr>
								<td style='vertical-align:top; white-space:nowrap; width:50px;'>{CONTENT_SUBMIT_TYPE_TABLE_ICON}</td>
								<td style='vertical-align:top; padding-left:10px;'>
									{CONTENT_SUBMIT_TYPE_TABLE_HEADING}<br />
									{CONTENT_SUBMIT_TYPE_TABLE_SUBHEADING}<br />
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr><td><div class='spacer'><br /></div></td></tr>\n";
}
if(!$CONTENT_SUBMIT_TYPE_TABLE_END){
				$CONTENT_SUBMIT_TYPE_TABLE_END = "
				</table>
				</div>";
}
// ##### ----------------------------------------------------------------------

// ##### CONTENT SUBMIT LIST --------------------------------------------------
if(!$CONTENT_SUBMIT_TABLE){
				$CONTENT_SUBMIT_TABLE = "
				<div style='text-align:center'>
				".$rs -> form_open("post", e_SELF."?".e_QUERY."", "dataform", "", "enctype='multipart/form-data'")."
				<table style='width:95%' class='fborder'>";

				if($CONTENT_SUBMIT_TABLE_PARENT){
					$CONTENT_SUBMIT_TABLE .= "
					<tr>
						<td class='forumheader3' style='width:20%; vertical-align:top'>".CONTENT_ADMIN_CAT_LAN_27.":</td>
						<td class='forumheader3' style='width:80%'>{CONTENT_SUBMIT_TABLE_PARENT}</td>
					</tr>";
				}

				if($CONTENT_SUBMIT_TABLE_AUTHOR){
					$CONTENT_SUBMIT_TABLE .= "					
					<tr>
						<td class='forumheader3' style='width:20%;'>".CONTENT_ADMIN_ITEM_LAN_51."</td>
						<td class='forumheader3' style='width:80%;'>{CONTENT_SUBMIT_TABLE_AUTHOR}</td>
					</tr>";
				}

				if($CONTENT_SUBMIT_TABLE_HEADING){
					$CONTENT_SUBMIT_TABLE .= "
					<tr>
						<td class='forumheader3' style='width:20%'>".CONTENT_ADMIN_ITEM_LAN_11."</td>
						<td class='forumheader3' style='width:80%'>{CONTENT_SUBMIT_TABLE_HEADING}</td>
					</tr>";
				}

				if($CONTENT_SUBMIT_TABLE_SUBHEADING){
					$CONTENT_SUBMIT_TABLE .= "
					<tr>
						<td class='forumheader3' style='width:20%'>".CONTENT_ADMIN_ITEM_LAN_16."</td>
						<td class='forumheader3' style='width:80%'>{CONTENT_SUBMIT_TABLE_SUBHEADING}</td>
					</tr>";
				}

				if($CONTENT_SUBMIT_TABLE_SUMMARY){
					$CONTENT_SUBMIT_TABLE .= "
					<tr>
						<td class='forumheader3' style='width:20%'>".CONTENT_ADMIN_ITEM_LAN_17."</td>
						<td class='forumheader3' style='width:80%'>{CONTENT_SUBMIT_TABLE_SUMMARY}</td>
					</tr>";
				}

				if($CONTENT_SUBMIT_TABLE_TEXT){
					$CONTENT_SUBMIT_TABLE .= "
					<tr>
						<td class='forumheader3' style='width:20%'>".CONTENT_ADMIN_ITEM_LAN_18."</td>
						<td class='forumheader3' style='width:80%'>{CONTENT_SUBMIT_TABLE_TEXT}</td>
					</tr>";
				}


				if($CONTENT_SUBMIT_TABLE_ICON){
					$CONTENT_SUBMIT_TABLE .= "
					<tr><td colspan='2' class='fcaption'>".CONTENT_ADMIN_ITEM_LAN_19."</td></tr>
					<tr>
						<td class='forumheader3' style='width:20%'>".CONTENT_ADMIN_ITEM_LAN_20."</td>
						<td class='forumheader3' style='width:80%'>{CONTENT_SUBMIT_TABLE_ICON}</td>
					</tr>";
				}
				if($CONTENT_SUBMIT_TABLE_ATTACH){
					$CONTENT_SUBMIT_TABLE .= "<tr><td colspan='2' class='fcaption'>".CONTENT_ADMIN_ITEM_LAN_24."</td></tr>";

					for($i=0;$i<count($CONTENT_SUBMIT_TABLE_ATTACH);$i++){
						$CONTENT_SUBMIT_TABLE .= "
						<tr>
							<td class='forumheader3' style='width:20%'>".CONTENT_ADMIN_ITEM_LAN_30." ".($i+1)."</td>
							<td class='forumheader3' style='width:80%'>{CONTENT_SUBMIT_TABLE_ATTACH[$i]}</td>
						</tr>";
					}
				}
				if($CONTENT_SUBMIT_TABLE_IMAGES){
					$CONTENT_SUBMIT_TABLE .= "<tr><td colspan='2' class='fcaption'>".CONTENT_ADMIN_ITEM_LAN_31."</td></tr>";
					for($i=0;$i<count($CONTENT_SUBMIT_TABLE_IMAGES);$i++){
						$CONTENT_SUBMIT_TABLE .= "
						<tr>
							<td class='forumheader3' style='width:20%'>".CONTENT_ADMIN_ITEM_LAN_34." ".($i+1)."</td>
							<td class='forumheader3' style='width:80%'>{CONTENT_SUBMIT_TABLE_IMAGES[$i]}</td>
						</tr>";
					}
				}


				if($CONTENT_SUBMIT_TABLE_PREF){
					$CONTENT_SUBMIT_TABLE .= "<tr><td colspan='2' class='fcaption'>{CONTENT_SUBMIT_TABLE_PREF}</td></tr>";
				}
				if($CONTENT_SUBMIT_TABLE_COMMENT){
					$CONTENT_SUBMIT_TABLE .= "
					<tr>
						<td class='forumheader3' style='width:20%'>".CONTENT_ADMIN_ITEM_LAN_36."</td>
						<td class='forumheader3' style='width:80%'>{CONTENT_SUBMIT_TABLE_COMMENT}</td>
					</tr>";
				}
				if($CONTENT_SUBMIT_TABLE_RATING){
					$CONTENT_SUBMIT_TABLE .= "
					<tr>
						<td class='forumheader3' style='width:20%'>".CONTENT_ADMIN_ITEM_LAN_37."</td>
						<td class='forumheader3' style='width:80%'>{CONTENT_SUBMIT_TABLE_RATING}</td>
					</tr>";
				}
				if($CONTENT_SUBMIT_TABLE_PE){
					$CONTENT_SUBMIT_TABLE .= "
					<tr>
						<td class='forumheader3' style='width:20%'>".CONTENT_ADMIN_ITEM_LAN_38."</td>
						<td class='forumheader3' style='width:80%'>{CONTENT_SUBMIT_TABLE_PE}</td>
					</tr>";
				}
				if($CONTENT_SUBMIT_TABLE_VISIBILITY){
					$CONTENT_SUBMIT_TABLE .= "
					<tr>
						<td class='forumheader3' style='width:20%'>".CONTENT_ADMIN_ITEM_LAN_39."</td>
						<td class='forumheader3' style='width:80%'>{CONTENT_SUBMIT_TABLE_VISIBILITY}</td>
					</tr>";
				}

				if($CONTENT_SUBMIT_TABLE_SCORE){
					$CONTENT_SUBMIT_TABLE .= "
					<tr>
						<td class='forumheader3' style='width:20%'>".CONTENT_ADMIN_ITEM_LAN_40."</td>
						<td class='forumheader3' style='width:80%'>{CONTENT_SUBMIT_TABLE_SCORE}</td></tr>";
				}

				if($CONTENT_SUBMIT_TABLE_META){
					$CONTENT_SUBMIT_TABLE .= "
					<tr>
						<td class='forumheader3' style='width:20%'>".CONTENT_ADMIN_ITEM_LAN_53."</td>
						<td class='forumheader3' style='width:80%'>{CONTENT_SUBMIT_TABLE_META}</td>
					</tr>";
				}
				
				if($CONTENT_SUBMIT_TABLE_CUSTOM_KEY){
					$CONTENT_SUBMIT_TABLE .= "<tr><td colspan='2' class='fcaption'>".CONTENT_ADMIN_ITEM_LAN_54."</td></tr>";

					for($i=0;$i<count($CONTENT_SUBMIT_TABLE_CUSTOM_KEY);$i++){
						$CONTENT_SUBMIT_TABLE .= "
						<tr>
							<td class='forumheader3' style='width:20%'>{CONTENT_SUBMIT_TABLE_CUSTOM_KEY[$i]}</td>
							<td class='forumheader3' style='width:80%'>{CONTENT_SUBMIT_TABLE_CUSTOM_VALUE[$i]}</td>
						</tr>";
					}
				}

				$CONTENT_SUBMIT_TABLE .= "
				<tr style='vertical-align:top'>
					<td colspan='2' style='text-align:center' class='forumheader'>{CONTENT_SUBMIT_TABLE_SUBMIT}</td>
				</tr>";

				$CONTENT_SUBMIT_TABLE .= "
				</table>
				</form>
				</div>";
}
// ##### ----------------------------------------------------------------------

?>