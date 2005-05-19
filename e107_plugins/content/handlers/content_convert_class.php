<?php
/*
+---------------------------------------------------------------+
|        e107 website system
|        content_convert_class.php
|
|        ©Steve Dunstan 2001-2002
|        http://e107.org
|        jalist@e107.org
|
|        Released under the terms and conditions of the
|        GNU General Public License (http://gnu.org).
|
|		$Source: /cvs_backup/e107_0.7/e107_plugins/content/handlers/content_convert_class.php,v $
|		$Revision: 1.3 $
|		$Date: 2005-05-19 11:05:05 $
|		$Author: lisa_ $
+---------------------------------------------------------------+
*/


$plugintable = "pcontent";		//name of the table used in this plugin (never remove this, as it's being used throughout the plugin !!)

class content_convert{

		function show_main_intro(){
						global $sql, $ns, $rs, $type, $type_id, $action, $sub_action, $id, $plugintable;

						if(!is_object($sql)){ $sql = new db; }
						$newcontent = $sql -> db_Count($plugintable, "(*)", "");
						if($newcontent > 0){
							return false;
						}else{

							$text .= "
							<div style='text-align:center'>
							<div style='width:70%; text-align:left'>
							".$rs -> form_open("post", e_SELF, "dataform")."
							<table class='fborder'>";
							
							$oldcontent = $sql -> db_Count("content", "(*)", "");
							if($oldcontent > 0){
								$text .= "<tr><td class='forumheader3' colspan='2'>".CONTENT_ADMIN_MAIN_LAN_8." ".CONTENT_ADMIN_MAIN_LAN_9." ".CONTENT_ADMIN_MAIN_LAN_11."</td></tr>";

								$text .= "<tr><td style='height:20px; border:0;' colspan='2'></td></tr>";
								$text .= "<tr><td class='fcaption' colspan='2'>".CONTENT_ADMIN_MAIN_LAN_18."</td></tr>";
								$text .= "<tr><td class='forumheader3' colspan='2'>".CONTENT_ADMIN_MAIN_LAN_19."</td></tr>";
								$text .= "
								<tr>
									<td class='forumheader3' style='width:50%; white-space:nowrap;'>".CONTENT_ADMIN_CONVERSION_LAN_43."</td>
									<td class='forumheader3' style='width:50%; white-space:nowrap;'>".$rs -> form_button("submit", "convert_table", "convert table")."</td>
								</tr>";

								$text .= "<tr><td style='height:20px; border:0;' colspan='2'></td></tr>";
								$text .= "<tr><td class='fcaption' colspan='2'>".CONTENT_ADMIN_MAIN_LAN_22."</td></tr>";
								$text .= "<tr><td class='forumheader3' colspan='2'>".CONTENT_ADMIN_MAIN_LAN_23."</td></tr>";
								$text .= "
								<tr>
									<td class='forumheader3' style='width:50%; white-space:nowrap;'>".CONTENT_ADMIN_CONVERSION_LAN_54."</td>
									<td class='forumheader3' style='width:50%; white-space:nowrap;'>".$rs -> form_button("submit", "create_default", "create defaults")."</td>
								</tr>";

								$text .= "<tr><td style='height:20px; border:0;' colspan='2'></td></tr>";
								$text .= "<tr><td class='fcaption' colspan='2'>".CONTENT_ADMIN_MAIN_LAN_20."</td></tr>";
								$text .= "<tr><td class='forumheader3' colspan='2'>".CONTENT_ADMIN_MAIN_LAN_21."</td></tr>";
								$text .= "
								<tr>
									<td class='forumheader3' style='width:50%; white-space:nowrap;'>".CONTENT_ADMIN_CONVERSION_LAN_56."</td>
									<td class='forumheader3' style='width:50%; white-space:nowrap;'>".$rs -> form_button("button", "fresh", "create new category", "onclick=\"document.location='".e_PLUGIN."content/admin_content_config.php?type.0.cat.create'\"
								")."</td>
								</tr>";

							}else{
								$text .= "<tr><td class='fcaption' colspan='2'>".CONTENT_ADMIN_MAIN_LAN_8." ".CONTENT_ADMIN_MAIN_LAN_9." ".CONTENT_ADMIN_MAIN_LAN_24."</td></tr>";
								$text .= "<tr><td class='forumheader3' colspan='2'>".CONTENT_ADMIN_MAIN_LAN_25."</td></tr>";
								$text .= "
								<tr>
									<td class='forumheader3' style='width:50%; white-space:nowrap;'>".CONTENT_ADMIN_CONVERSION_LAN_54."</td>
									<td class='forumheader3' style='width:50%; white-space:nowrap;'>".$rs -> form_button("submit", "create_default", "create defaults")."</td>
								</tr>";
							}

							$text .= "</table>".$rs -> form_close()."
							</div>
							</div>";

							$ns -> tablerender(CONTENT_ADMIN_MAIN_LAN_7, $text);
							return true;
						}
		}

		//function to insert default preferences for a main parent
		function insert_default_prefs($id){
				global $sql, $aa, $plugintable;
				unset($content_pref, $tmp);

				$content_pref = $aa -> ContentDefaultPrefs($id);
				$tmp = addslashes(serialize($content_pref));
				$sql -> db_Update($plugintable, "content_pref='$tmp' WHERE content_id='$id' ");
		}


		//function to convert comments
		function convert_comments($oldid, $newid){
				global $plugintable;

				if(!is_object($sqlcc)){ $sqlcc = new db; }
				//check if comments present, if so, convert those to new content item id's
				$numc = $sqlcc -> db_Count("comments", "(*)", "WHERE comment_type = '1' AND comment_item_id = '".$oldid."' ");
				if($numc > 0){
					$sqlcc -> db_Update("comments", "comment_item_id = '".$newid."', comment_type = '".$plugintable."' WHERE comment_item_id = '".$oldid."' ");
				}
		}


		//function to convert rating
		function convert_rating($oldid, $newid){
				global $plugintable;

				if(!is_object($sqlcr)){ $sqlcr = new db; }
				//check if rating present, if so, convert those to new content item id's
				$numr = $sqlcr -> db_Count("rate", "(*)", "WHERE rate_itemid = '".$oldid."' AND (rate_table = 'content' || rate_table = 'article' || rate_table = 'review') ");
				if($numr > 0){
					$sqlcr -> db_Update("rate", "rate_table = '".$plugintable."', rate_itemid = '".$newid."' WHERE rate_itemid = '".$oldid."' ");
				}
		}


		//create main parent
		function create_mainparent($name, $tot, $order){
				global $sql, $aa, $plugintable, $maxcid;

				// ##### STAGE 4 : INSERT MAIN PARENT FOR ARTICLE ---------------------------------------------
				$checkinsert = FALSE;
				if($tot > "0"){
					//check if row with this name does not already exists
					if(!is_object($sql)){ $sql = new db; }					
					if(!$sql -> db_Select($plugintable, "content_heading", "content_heading = '".$name."' AND content_parent = '0' ")){
						
						//use global value for last row id, and add the $order number to it, else use order number as id
						$maxcid = ($maxcid ? $maxcid+$order : $order);

						$sql -> db_Insert($plugintable, "'".$maxcid."', '".$name."', '', '', '', '1', '', '', '', '0', '0', '0', '0', '', '".time()."', '0', '0', '', '".$order."' ");

						//check if row is present in the db (is it a valid insert)
						if(!is_object($sql2)){ $sql2 = new db; }
						if(!$sql2 -> db_Select($plugintable, "content_id", "content_heading = '".$name."' ")){
							$message = CONTENT_ADMIN_CONVERSION_LAN_45;
						}else{
							$message = $name." ".CONTENT_ADMIN_CONVERSION_LAN_7."<br />";
							$checkinsert = TRUE;

							//select main parent id
							if(!is_object($sql3)){ $sql3 = new db; }
							$sql3 -> db_Select($plugintable, "content_id", "content_heading = '".$name."' AND content_parent = '0' ");
							list($main_id) = $sql3 -> db_Fetch();

							//insert default preferences
							$this -> insert_default_prefs($main_id);

							//create menu
							$aa -> CreateParentMenu($main_id);

							$message .= $name." ".CONTENT_ADMIN_CONVERSION_LAN_8."<br />";
						}
					}else{
						$message = CONTENT_ADMIN_CONVERSION_LAN_9." ".$name." ".CONTENT_ADMIN_CONVERSION_LAN_10." : ".CONTENT_ADMIN_CONVERSION_LAN_53."<br />";
					}
				}else{
					$message = CONTENT_ADMIN_CONVERSION_LAN_9." ".$name." ".CONTENT_ADMIN_CONVERSION_LAN_10."<br />";
				}
				$create_mainparent = array($checkinsert, $message);		
				return $create_mainparent;
		}


		//analayse unknown rows
		function analyse_unknown(){
				global $sql;

				if(!is_object($sql)){ $sql = new db; }
				$totaloldrowsunknown = $sql -> db_Select("content", "*", " NOT ( (content_parent = '0' AND content_type = '1') || (content_parent = '0' AND content_type = '6') || (content_parent = '0' AND content_type = '10') || (content_type = '3' || content_type = '16') || (content_type = '0' || content_type = '15') ) ");

				while($row = $sql -> db_Fetch()){
					$unknown_bug[] = $row['content_id']." ".$row['content_heading']." - parent=".$row['content_parent']." - type=".$row['content_type'];
					$unknown_bug_id[] = $row['content_id'];
				}
				$analyse_unknown = array($unknown_bug, $unknown_bug_id);
				return $analyse_unknown;
		}


		//convert categories
		function convert_category($name, $query, $ordernr){
				global $sql, $plugintable, $tp;

				// ##### STAGE 7 : INSERT CATEGORY ----------------------------------------------------
				if(!is_object($sql)){ $sql = new db; }
				if(!$sql -> db_Select("content", "*", " ".$query." ORDER BY content_id " )){
					$cat_present = false;
				}else{
					$count = $ordernr;
					$cat_present = true;
					while($row = $sql -> db_Fetch()){

						//get max id value, new parent rows need id with added value
						//$sql -> db_select("content", "MAX(content_id) as maxcid", "content_id!='0' ");
						//list($maxcid) = $sql -> db_Fetch();

						//select main parent id
						if(!is_object($sql2)){ $sql2 = new db; }
						$sql2 -> db_Select($plugintable, "content_id", "content_heading = '".$name."' AND content_parent = '0' ");
						list($main_id) = $sql2 -> db_Fetch();

						//summary can contain link to image in e107_images/link_icons/".$summary." THIS STILL NEEDS TO BE CHECKED
						$newcontent_heading = $tp -> toDB($row['content_heading']);
						$newcontent_subheading = ($row['content_subheading'] ? $tp -> toDB($row['content_subheading']) : "");
						$newcontent_summary = ($row['content_summary'] ? $tp -> toDB($row['content_summary']) : "");
						$newcontent_text = $tp -> toDB($row['content_content']);
						$newcontent_author = (is_numeric($row['content_author']) ? $row['content_author'] : "0^".$row['content_author']);
						$newcontent_icon = "";
						$newcontent_attach = "";
						$newcontent_images = "";
						$newcontent_parent = "0.".$main_id;			//make each category a first level subcat of the main parent
						$newcontent_comment = $row['content_comment'];
						$newcontent_rate = "0";
						$newcontent_pe = $row['content_pe_icon'];
						$newcontent_refer = "0";
						$newcontent_starttime = $row['content_datestamp'];
						$newcontent_endtime = "0";
						$newcontent_class = $row['content_class'];
						$newcontent_pref = "";
						$newcontent_id = $row['content_id'];

						if(!is_object($sql3)){ $sql3 = new db; }
						$sql3 -> db_Insert($plugintable, "'".$newcontent_id."', '".$newcontent_heading."', '".$newcontent_subheading."', '".$newcontent_summary."', '".$newcontent_text."', '".$newcontent_author."', '".$newcontent_icon."', '".$newcontent_attach."', '".$newcontent_images."', '".$newcontent_parent."', '".$newcontent_comment."', '".$newcontent_rate."', '".$newcontent_pe."', '".$newcontent_refer."', '".$newcontent_starttime."', '".$newcontent_endtime."', '".$newcontent_class."', '".$newcontent_pref."', '".$count."' ");

						if(!$sql3 -> db_Select($plugintable, "content_id, content_heading", "content_heading = '".$newcontent_heading."' ")){
							$bug_cat_insert[] = $row['content_id']." ".$row['content_heading'];
						}else{
							$valid_cat_insert[] = $row['content_id']." ".$row['content_heading'];
							$count = $count + 1;
						}
					}
				}
				$convert_category = array($cat_present, $valid_cat_insert, $bug_cat_insert, $count);
				return $convert_category;
		}


		//convert rows
		function convert_row($name, $query, $startorder){
				global $sql, $tp, $plugintable;

				// ##### STAGE 8 : INSERT ROW -------------------------------------------------------------
				if(!is_object($sql)){ $sql = new db; }
				if(!$thiscount = $sql -> db_Select("content", "*", " ".$query." ORDER BY content_id " )){
					$check_present = false;
				}else{
					$count = $startorder;
					$check_present = true;
					while($row = $sql -> db_Fetch()){

						//select main parent id
						if(!is_object($sql2)){ $sql2 = new db; }
						$sql2 -> db_Select($plugintable, "content_id", "content_heading = '".$name."' AND content_parent = '0' ");
						list($main_id) = $sql2 -> db_Fetch();

						//item is in main cat
						if($row['content_parent'] == "0"){
							$newcontent_parent = $main_id.".".$main_id;

						//item is in sub cat
						}else{
							//select old review cat heading
							if(!is_object($sql3)){ $sql3 = new db; }
							if(!$sql3 -> db_Select("content", "content_id, content_heading", "content_id = '".$row['content_parent']."' ")){
								$bug_oldcat[] = $row['content_id']." ".$row['content_heading'];
								$newcontent_parent = $main_id.".".$main_id;
							}else{
								list($old_cat_id, $old_cat_heading) = $sql3 -> db_Fetch();

								//select new cat id from the cat with the old_cat_heading
								if(!is_object($sql4)){ $sql4 = new db; }
								if(!$sql4 -> db_Select($plugintable, "content_id", "content_heading = '".$old_cat_heading."' AND content_parent = '0.".$main_id."' ")){
									$bug_newcat[] = $row['content_id']." ".$row['content_heading'];
									$newcontent_parent = $main_id.".".$main_id;
								}else{
									list($new_cat_id) = $sql4 -> db_Fetch();
									$newcontent_parent = $main_id.".".$main_id.".".$new_cat_id;
								}
							}
						}
						
						if (strstr($row['content_content'], "{EMAILPRINT}")) {
							$row['content_content'] = str_replace("{EMAILPRINT}", "", $row['content_content']);
						}

						$newcontent_heading = $tp -> toDB($row['content_heading']);
						$newcontent_subheading = ($row['content_subheading'] ? $tp -> toDB($row['content_subheading']) : "");
						//summary can contain link to image in e107_images/link_icons/".$summary." THIS STILL NEEDS TO BE CHECKED
						$newcontent_summary = ($row['content_summary'] ? $tp -> toDB($row['content_summary']) : "");
						$newcontent_text = $tp -> toDB($row['content_content']);
						$newcontent_author = (is_numeric($row['content_author']) ? $row['content_author'] : "0^".$row['content_author']);
						$newcontent_icon = "";
						$newcontent_attach = "";
						$newcontent_images = "";
						$newcontent_comment = $row['content_comment'];
						$newcontent_rate = "0";
						$newcontent_pe = $row['content_pe_icon'];
						$newcontent_refer = ($row['content_type'] == "16" ? "sa" : "");
						$newcontent_starttime = $row['content_datestamp'];
						$newcontent_endtime = "0";
						$newcontent_class = $row['content_class'];
						$newcontent_id = $row['content_id'];
								
						if($content_review_score != "none" && $content_review_score){
							$custom["content_custom_score"] = $content_review_score;
							$newcontent_pref = addslashes(serialize($custom));
						}else{
							$newcontent_pref = "";
						}

						if(!is_object($sql5)){ $sql5 = new db; }
						$sql5 -> db_Insert($plugintable, "'".$newcontent_id."', '".$newcontent_heading."', '".$newcontent_subheading."', '".$newcontent_summary."', '".$newcontent_text."', '".$newcontent_author."', '".$newcontent_icon."', '".$newcontent_attach."', '".$newcontent_images."', '".$newcontent_parent."', '".$newcontent_comment."', '".$newcontent_rate."', '".$newcontent_pe."', '".$newcontent_refer."', '".$newcontent_starttime."', '".$newcontent_endtime."', '".$newcontent_class."', '".$newcontent_pref."', '1.".$count."' ");

						if(!is_object($sql6)){ $sql6 = new db; }
						if(!$sql6 -> db_Select($plugintable, "content_id, content_heading", "content_heading = '".$newcontent_heading."' ")){
							$bug_insert[] = $row['content_id']." ".$row['content_heading'];
						}else{
							$valid_insert[] = $row['content_id']." ".$row['content_heading'];
							$count = $count + 1;

							list($thenewcontent_id, $thenewcontent_heading) = $sql6 -> db_Fetch();
							$this -> convert_comments($row['content_id'], $thenewcontent_id);
							$this -> convert_rating($row['content_id'], $thenewcontent_id);
						}
					}
				}
				$convert_row = array($check_present, $count, $valid_insert, $bug_insert, $bug_oldcat, $bug_newcat);
				return $convert_row;
		}


		//show output of the category conversion
		function results_conversion_category($name, $array, $oldrows){

				//no pages present
				if($array[0] === false){
					if( !(count($array[1]) > 0 || count($array[2]) > 0) ){
						$text .= "<tr><td class='forumheader' colspan='2'>".CONTENT_ADMIN_CONVERSION_LAN_34." ".$name." ".CONTENT_ADMIN_CONVERSION_LAN_35."</td></tr>";
					}
				
				//pages present
				}else{
				
					//valid inserts
					if(count($array[1]) > 0 ){
						$text .= "
						<tr>
							<td class='forumheader3' style='width:5%; white-space:nowrap;'>".CONTENT_ADMIN_CONVERSION_LAN_3." : ".count($array[1])." ".CONTENT_ADMIN_CONVERSION_LAN_38."</td>
							<td class='forumheader3'><a style='cursor:pointer;' onclick=\"expandit('validcat_{$name}');\">".CONTENT_ADMIN_CONVERSION_LAN_48."</a>
								<div id='validcat_{$name}' style='display: none;'>
									<table style='width:100%; border:0;'>";
									for($i=0;$i<count($array[1]);$i++){
										$text .= "
										<tr>
											<td style='width:25%; white-space:nowrap;'>".CONTENT_ICON_OK." ".$array[1][$i]."</td>
											<td>".$name." ".CONTENT_ADMIN_CONVERSION_LAN_3." ".CONTENT_ADMIN_CONVERSION_LAN_26."</td>
										</tr>";
									}
									$text .= "
									</table>
								</div>
							</td>
						</tr>";
					}

					//bug inserts
					if(count($array[2]) > 0 ){
						$text .= "
						<tr>
							<td class='forumheader3' style='width:5%; white-space:nowrap;'>".CONTENT_ADMIN_CONVERSION_LAN_3." : ".count($array[2])." ".CONTENT_ADMIN_CONVERSION_LAN_39."</td>
							<td class='forumheader3'><a style='cursor:pointer;' onclick=\"expandit('failedcat_{$name}');\">".CONTENT_ADMIN_CONVERSION_LAN_48."</a>
								<div id='failedcat_{$name}' style='display: none;'>
									<table style='width:100%; border:0;'>";
									for($i=0;$i<count($array[2]);$i++){
										$text .= "
										<tr>
											<td style='width:25%; white-space:nowrap;'>".CONTENT_ICON_ERROR." ".$array[2][$i]."</td>
											<td>".CONTENT_ADMIN_CONVERSION_LAN_23."</td>
										</tr>";
									}
									$text .= "
									</table>
								</div>
							</td>
						</tr>";
					}
					$text .= "
					<tr>
						<td class='forumheader3' style='width:5%; white-space:nowrap;'>".CONTENT_ADMIN_CONVERSION_LAN_3." : ".CONTENT_ADMIN_CONVERSION_LAN_27."</td>
						<td class='forumheader3'>
							<a style='cursor: pointer; cursor: hand' onclick=\"expandit('analysecat_{$name}');\">".CONTENT_ADMIN_CONVERSION_LAN_48."</a>
							<div id='analysecat_{$name}' style='display: none;'>
								".$oldrows." ".CONTENT_ADMIN_CONVERSION_LAN_28."<br />
								".count($array[1])." ".CONTENT_ADMIN_CONVERSION_LAN_29."<br />
								".count($array[2])." ".CONTENT_ADMIN_CONVERSION_LAN_30."<br />
							</div>
						</td>
					</tr>";
				}
				return $text;
		}


		//show output of the item conversion
		function results_conversion_row($name, $array, $oldrows){

				//no rows present
				if($array[0] === false){
					$text .= "<tr><td class='forumheader' colspan='2'>".CONTENT_ADMIN_CONVERSION_LAN_34." ".$name." ".CONTENT_ADMIN_CONVERSION_LAN_36."</td></tr>";
				
				//rows present
				}else{
				
					//valid insert
					if(count($array[2]) > 0 ){
						$text .= "
						<tr>
							<td class='forumheader3' style='width:5%; white-space:nowrap;'>".CONTENT_ADMIN_CONVERSION_LAN_6." : ".count($array[2])." ".CONTENT_ADMIN_CONVERSION_LAN_38."</td>
							<td class='forumheader3'>
								<a style='cursor:pointer;' onclick=\"expandit('valid_{$name}');\">".CONTENT_ADMIN_CONVERSION_LAN_48."</a>
								<div id='valid_{$name}' style='display: none;'>
									<table style='width:100%; border:0;'>";
									for($i=0;$i<count($array[2]);$i++){
										$text .= "
										<tr>
											<td style='width:25%; white-space:nowrap;'>".CONTENT_ICON_OK." ".$array[2][$i]."</td>
											<td>".$name." ".CONTENT_ADMIN_CONVERSION_LAN_6." ".CONTENT_ADMIN_CONVERSION_LAN_26."</td>
										</tr>";
									}
									$text .= "
									</table>
								</div>
							</td>
						</tr>";
					}
					//bugs : old category
					if(count($array[4]) > 0 ){
						$text .= "
						<tr>
							<td class='forumheader3' style='width:5%; white-space:nowrap;'>".CONTENT_ADMIN_CONVERSION_LAN_6." : ".count($array[4])." ".CONTENT_ADMIN_CONVERSION_LAN_31."</td>
							<td class='forumheader3'>
								<a style='cursor:pointer;' onclick=\"expandit('oldcat_{$name}');\">".CONTENT_ADMIN_CONVERSION_LAN_48."</a>
								<div id='oldcat_{$name}' style='display: none;'>
									<table style='width:100%; border:0;'>";
									for($i=0;$i<count($array[4]);$i++){
										$text .= "
										<tr>
											<td style='width:25%; white-space:nowrap;'>".CONTENT_ICON_WARNING." ".$array[4][$i]."</td>
											<td>".CONTENT_ADMIN_CONVERSION_LAN_32."</td>
										</tr>";
									}
									$text .= "
									</table>
								</div>
							</td>
						</tr>";
					}
					//bugs : new category
					if(count($array[5]) > 0 ){
						$text .= "
						<tr>
							<td class='forumheader3' style='width:5%; white-space:nowrap;'>".CONTENT_ADMIN_CONVERSION_LAN_6." : ".count($array[5])." ".CONTENT_ADMIN_CONVERSION_LAN_31."</td>
							<td class='forumheader3'>
								<a style='cursor:pointer;' onclick=\"expandit('newcat_{$name}');\">".CONTENT_ADMIN_CONVERSION_LAN_48."</a>
								<div id='newcat_{$name}' style='display: none;'>
									<table style='width:100%; border:0;'>";
									for($i=0;$i<count($array[5]);$i++){
										$text .= "
										<tr>
											<td style='width:25%; white-space:nowrap;'>".CONTENT_ICON_WARNING." ".$array[5][$i]."</td>
											<td>".CONTENT_ADMIN_CONVERSION_LAN_33."</td>
										</tr>";
									}
									$text .= "
									</table>
								</div>
							</td>
						</tr>";
					}
					//bugs : insertion failed
					if(count($array[3]) > 0 ){
						$text .= "
						<tr>
							<td class='forumheader3' style='width:5%; white-space:nowrap;'>".CONTENT_ADMIN_CONVERSION_LAN_6." : ".count($array[3])." ".CONTENT_ADMIN_CONVERSION_LAN_39."</td>
							<td class='forumheader3'>
								<a style='cursor: pointer; cursor: hand' onclick=\"expandit('failed_{$name}');\">".CONTENT_ADMIN_CONVERSION_LAN_48."</a>
								<div id='failed_{$name}' style='display: none;'>
									<table style='width:100%; border:0;'>";
									for($i=0;$i<count($array[3]);$i++){
										$text .= "
										<tr>
											<td style='width:25%; white-space:nowrap;'>".CONTENT_ICON_ERROR." ".$array[3][$i]."</td>
											<td>".CONTENT_ADMIN_CONVERSION_LAN_23."</td>
										</tr>";
									}
									$text .= "
									</table>
								</div>
							</td>
						</tr>";
					}
					
					//analyses
					$text .= "
					<tr>
						<td class='forumheader3' style='width:5%; white-space:nowrap;'>".CONTENT_ADMIN_CONVERSION_LAN_6." : ".CONTENT_ADMIN_CONVERSION_LAN_27."</td>
						<td class='forumheader3'>
							<a style='cursor: pointer; cursor: hand' onclick=\"expandit('analyse_{$name}');\">".CONTENT_ADMIN_CONVERSION_LAN_48."</a>
							<div id='analyse_{$name}' style='display: none;'>
								".$oldrows." ".CONTENT_ADMIN_CONVERSION_LAN_28."<br />
								".count($array[2])." ".CONTENT_ADMIN_CONVERSION_LAN_29."<br />
								".count($array[3])." ".CONTENT_ADMIN_CONVERSION_LAN_30."<br />
								".count($array[4])." ".CONTENT_ADMIN_CONVERSION_LAN_31."<br />
								".count($array[5])." ".CONTENT_ADMIN_CONVERSION_LAN_31."<br />
							</div>
						</td>
					</tr>";
				}

				return $text;
		}


		//show output of the mainparent conversion
		function results_conversion_mainparent($content, $review, $article){
				$text = "<tr><td class='fcaption' colspan='2'>".CONTENT_ADMIN_CONVERSION_LAN_50."</td></tr>";
				$text .= "
				<tr>
					<td class='forumheader3' style='width:5%; white-space:nowrap;'>".($content[0] == "1" ? CONTENT_ICON_OK : CONTENT_ICON_ERROR)." ".CONTENT_ADMIN_CONVERSION_LAN_20."</td>
					<td class='forumheader3'>".$content[1]."</td>
				</tr>
				<tr>
					<td class='forumheader3' style='width:5%; white-space:nowrap;'>".($review[0] == "1" ? CONTENT_ICON_OK : CONTENT_ICON_ERROR)." ".CONTENT_ADMIN_CONVERSION_LAN_21."</td>
					<td class='forumheader3'>".$review[1]."</td>
				</tr>
				<tr>
					<td class='forumheader3' style='width:5%; white-space:nowrap;'>".($article[0] == "1" ? CONTENT_ICON_OK : CONTENT_ICON_ERROR)." ".CONTENT_ADMIN_CONVERSION_LAN_22."</td>
					<td class='forumheader3'>".$article[1]."</td>
				</tr>";

				return $text;
		}

}

?>