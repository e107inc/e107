<?php
/*
+---------------------------------------------------------------+
|        e107 website system
|        /admin/review.php
|
|        ©Steve Dunstan 2001-2002
|        http://e107.org
|        jalist@e107.org
|
|        Released under the terms and conditions of the
|        GNU General Public License (http://gnu.org).
|
|		$Source: /cvs_backup/e107_0.7/e107_plugins/content/handlers/content_form_class.php,v $
|		$Revision: 1.4 $
|		$Date: 2005-02-08 14:36:55 $
|		$Author: lisa_ $
+---------------------------------------------------------------+
*/

//$plugintable = "pcontent";		//name of the table used in this plugin

if (!defined('ADMIN_WIDTH')) { define("ADMIN_WIDTH", "width:98%;"); }

class contentform{

		function show_content_create($mode, $userid="", $username=""){
						global $sql, $ns, $rs, $aa, $tp, $plugintable;
						global $type, $type_id, $action, $sub_action, $id;
						global $message;

						$content_pref = $aa -> getContentPref($type_id);
						$content_cat_icon_path_large = $aa -> parseContentPathVars($content_pref["content_cat_icon_path_large_{$type_id}"]);
						$content_cat_icon_path_small = $aa -> parseContentPathVars($content_pref["content_cat_icon_path_small_{$type_id}"]);
						$content_icon_path = $aa -> parseContentPathVars($content_pref["content_icon_path_{$type_id}"]);
						$content_image_path = $aa -> parseContentPathVars($content_pref["content_image_path_{$type_id}"]);
						$content_file_path = $aa -> parseContentPathVars($content_pref["content_file_path_{$type_id}"]);

						if(!is_object($sql)){ $sql = new db; }

						if($mode == "contentmanager"){
							//use user restriction (personal admin)
							if($userid != "" && $username != ""){
								$userquery = " AND (SUBSTRING_INDEX(content_author, '^', 1) = '".$userid."' OR SUBSTRING_INDEX(content_author, '^', 2) = '".$userid."^".$username."')";
							}else{
								$userquery = "";
							}
							$parentdetails = $aa -> getParent("", "", $type_id, "1");

							if($sub_action != "edit"){
								$aa -> ContentManagerValidCategoryCheck($sub_action);
							}
						}elseif($mode == "admin"){
							$userquery = "";
							$parentdetails = $aa -> getParent("", "", $type_id);
						}elseif($mode == "submit"){
							$userquery = "";
							$parentdetails = $aa -> getParent("", "", $type_id, "1");
						}

						if($mode == "submit"){
							$checkicon = $content_pref["content_submit_icon_{$type_id}"];
							$checkattach = $content_pref["content_submit_attach_{$type_id}"];
							$checkattachnumber = $content_pref["content_submit_files_number_{$type_id}"];
							$checkimages = $content_pref["content_submit_images_{$type_id}"];
							$checkimagesnumber = $content_pref["content_submit_images_number_{$type_id}"];
							$checkcomment = $content_pref["content_submit_comment_{$type_id}"];
							$checkrating = $content_pref["content_submit_rating_{$type_id}"];
							$checkscore = $content_pref["content_submit_score_{$type_id}"];
							$checkpe = $content_pref["content_submit_pe_{$type_id}"];
							$checkvisibility = $content_pref["content_submit_visibility_{$type_id}"];
							$checkmeta = $content_pref["content_submit_meta_{$type_id}"];
							$checkcustomnumber = $content_pref["content_submit_custom_number_{$type_id}"];

						}else{
							$checkicon = $content_pref["content_admin_icon_{$type_id}"];
							$checkattach = $content_pref["content_admin_attach_{$type_id}"];
							$checkattachnumber = $content_pref["content_admin_files_number_{$type_id}"];
							$checkimages = $content_pref["content_admin_images_{$type_id}"];
							$checkimagesnumber = $content_pref["content_admin_images_number_{$type_id}"];
							$checkcomment = $content_pref["content_admin_comment_{$type_id}"];
							$checkrating = $content_pref["content_admin_rating_{$type_id}"];
							$checkscore = $content_pref["content_admin_score_{$type_id}"];
							$checkpe = $content_pref["content_admin_pe_{$type_id}"];
							$checkvisibility = $content_pref["content_admin_visibility_{$type_id}"];
							$checkmeta = $content_pref["content_admin_meta_{$type_id}"];
							$checkcustomnumber = $content_pref["content_admin_custom_number_{$type_id}"];
						}

						if($parentdetails == FALSE){
							$text = "<div style='text-align:center'>".CONTENT_ADMIN_MAIN_LAN_1."</div>";
							$ns -> tablerender(CONTENT_ADMIN_MAIN_LAN_2, $text);
							require_once(FOOTERF);
							exit;
						}

						if(!$sub_action){
							$authordetails = $aa -> getAuthor(USERID);
						}
						if($sub_action == "edit" && !$_POST['preview'] && !isset($message)){
							if(is_numeric($id)){
								if($sql -> db_Select($plugintable, "content_id, content_heading, content_subheading, content_summary, content_text, content_author, content_icon, content_file, content_image, content_parent, content_comment, content_rate, content_pe, content_refer, content_datestamp, content_class, content_pref as contentprefvalue", "content_id='$id' ")){
									$row = $sql -> db_Fetch(); extract($row);
									$content_text = str_replace("<br />", "", $tp -> post_toForm($content_text));
									$authordetails = $aa -> getAuthor($content_author);
								}
							}else{
								header("location:".e_SELF."?create"); exit;
							}
						}

						if($sub_action == "sa" && is_numeric($id) && !$_POST['preview'] && !isset($message)){
							if(is_numeric($id)){
								if($sql -> db_Select($plugintable, "content_id, content_heading, content_subheading, content_summary, content_text, content_author, content_icon, content_file, content_image, content_parent, content_comment, content_rate, content_pe, content_refer, content_datestamp, content_class, content_pref as contentprefvalue", "content_id=$id")){
									$row = $sql -> db_Fetch(); extract($row);
									$content_text = str_replace("<br />", "", $tp -> post_toForm($content_text));
									$authordetails = $aa -> getAuthor($content_author);
								}
							}else{
								header("location:".e_SELF."?create"); exit;
							}
						}elseif($_POST['preview'] || isset($message)){
							global $custom, $_POST, $ne_day, $ne_month, $ne_year, $content_heading, $content_authorname, $content_authoremail, $content_parent, $content_subheading, $content_summary, $content_text, $content_icon, $content_comment, $content_rate, $content_pe, $content_class, $custom, $content_images, $content_files;

							for($i=0;$i<$checkcustomnumber;$i++){
								$keystring = $_POST["content_custom_key_{$i}"];
								$custom["content_custom_{$keystring}"] = $_POST["content_custom_value_{$i}"];
							}
						}
						if($mode == "contentmanager"){ // used in contentmanager
							$authordetails = $aa -> getAuthor(USERID);
						}

						$authordetails[0] = ($content_author_id ? $content_author_id : $authordetails[0]);
						$authordetails[1] = ($content_author_name ? $content_author_name : $authordetails[1]);
						$authordetails[2] = ($content_author_email ? $content_author_email : $authordetails[2]);

						$text = "
						<div style='text-align:center'>
						".$rs -> form_open("post", e_SELF."?".e_QUERY."", "dataform", "", "enctype='multipart/form-data'")."
						<table style='".ADMIN_WIDTH."' class='fborder'>";

						$smarray = getdate();
						//$ne_day = $smarray['mday'];
						//$ne_month = $smarray['mon'];
						$current_year = $smarray['year'];

						$months = array(CONTENT_ADMIN_DATE_LAN_0, CONTENT_ADMIN_DATE_LAN_1, CONTENT_ADMIN_DATE_LAN_2, CONTENT_ADMIN_DATE_LAN_3, CONTENT_ADMIN_DATE_LAN_4, CONTENT_ADMIN_DATE_LAN_5, CONTENT_ADMIN_DATE_LAN_6, CONTENT_ADMIN_DATE_LAN_7, CONTENT_ADMIN_DATE_LAN_8, CONTENT_ADMIN_DATE_LAN_9, CONTENT_ADMIN_DATE_LAN_10, CONTENT_ADMIN_DATE_LAN_11);
						
						//if($sub_action == "edit" || $sub_action == "sa" || $_POST['editp']){
						//}else{
							$text .= "
							<tr>
								<td class='forumheader3' style='width:30%; vertical-align:top'>".CONTENT_ADMIN_DATE_LAN_15."<br />".CONTENT_ADMIN_DATE_LAN_17."</td>
								<td class='forumheader3' style='width:70%'>

									".$rs -> form_select_open("ne_day")."
									".$rs -> form_option(CONTENT_ADMIN_DATE_LAN_12, 0, "none");
									for($count=1; $count<=31; $count++){
										$text .= $rs -> form_option($count, ($ne_day == $count ? "1" : "0"), $count);
									}
									$text .= $rs -> form_select_close()."

									".$rs -> form_select_open("ne_month")."
									".$rs -> form_option(CONTENT_ADMIN_DATE_LAN_13, 0, "none");
									for($count=1; $count<=12; $count++){
										$text .= $rs -> form_option($months[($count-1)], ($ne_month == $count ? "1" : "0"), $count);
										
									}
									$text .= $rs -> form_select_close()."

									".$rs -> form_select_open("ne_year")."
									".$rs -> form_option(CONTENT_ADMIN_DATE_LAN_14, 0, "none");
									for($count=($current_year-5); $count<=$current_year; $count++){
										$text .= $rs -> form_option($count, ($ne_year == $count ? "1" : "0"), $count);
									}
									$text .= $rs -> form_select_close()."

								</td>
							</tr>";
						//}

						$text .= "
						<tr>
							<td class='forumheader3' style='width:30%; vertical-align:top'>".CONTENT_ADMIN_DATE_LAN_16."<br />".CONTENT_ADMIN_DATE_LAN_18."</td>
							<td class='forumheader3' style='width:70%'>

								".$rs -> form_select_open("end_day")."
								".$rs -> form_option(CONTENT_ADMIN_DATE_LAN_12, 0, "none");
								for($count=1; $count<=31; $count++){
									$text .= $rs -> form_option($count, ($end_day == $count ? "1" : "0"), $count);
								}
								$text .= $rs -> form_select_close()."

								".$rs -> form_select_open("end_month")."
								".$rs -> form_option(CONTENT_ADMIN_DATE_LAN_13, 0, "none");
								for($count=1; $count<=12; $count++){
									$text .= $rs -> form_option($months[($count-1)], ($end_month == $count ? "1" : "0"), $count);
									
								}
								$text .= $rs -> form_select_close()."

								".$rs -> form_select_open("end_year")."
								".$rs -> form_option(CONTENT_ADMIN_DATE_LAN_14, 0, "none");
								for($count=($current_year-5); $count<=$current_year; $count++){
									$text .= $rs -> form_option($count, ($end_year == $count ? "1" : "0"), $count);
								}
								$text .= $rs -> form_select_close()."

							</td>
						</tr>";

						if($mode == "contentmanager"){
							if($sub_action == "edit"){
								$text .= $rs -> form_hidden("parent", $content_parent);
							}else{
								$cat = str_replace("-", ".", $sub_action);
								$text .= $rs -> form_hidden("parent", $cat);
							}
						}else{
							$text .= "
							<tr>
								<td class='forumheader3' style='width:30%; vertical-align:top'>".CONTENT_ADMIN_CAT_LAN_27.":</td>
								<td class='forumheader3' style='width:70%'>
									".$rs -> form_select_open("parent")."
									".$rs -> form_option("** ".CONTENT_ADMIN_ITEM_LAN_13." **", 0, "none")."
									".$aa -> printParent($parentdetails, "0", $content_parent, "optioncontent")."
									".$rs -> form_select_close()."
								</td>
							</tr>";
						}
							
						$text .= "
						<tr>
							<td class='forumheader3' style='width:30%; vertical-align:top'>".CONTENT_ADMIN_ITEM_LAN_51."</td>
							<td class='forumheader3' style='width:70%; vertical-align:top'>
								".$rs -> form_text("content_author_name", 60, ($authordetails[1] ? $authordetails[1] : CONTENT_ADMIN_ITEM_LAN_14), 100, "tbox", "", "", ($authordetails[1] ? "" : "onfocus=\"if(document.getElementById('dataform').content_author_name.value=='".CONTENT_ADMIN_ITEM_LAN_14."'){document.getElementById('dataform').content_author_name.value='';}\"") )."<br />
								".$rs -> form_text("content_author_email", 60, ($authordetails[2] ? $authordetails[2] : CONTENT_ADMIN_ITEM_LAN_15), 100, "tbox", "", "", ($authordetails[2] ? "" : "onfocus=\"if(document.getElementById('dataform').content_author_email.value=='".CONTENT_ADMIN_ITEM_LAN_15."'){document.getElementById('dataform').content_author_email.value='';}\"") )."
								".$rs -> form_hidden("content_author_id", $authordetails[0])."
							</td>
						</tr>
						<tr>
							<td class='forumheader3' style='width:30%'>".CONTENT_ADMIN_ITEM_LAN_11."</td>
							<td class='forumheader3' style='width:70%'>".$rs -> form_text("content_heading", 100, $content_heading, 250)."</td>
						</tr>
						<tr>
							<td class='forumheader3' style='width:30%'>".CONTENT_ADMIN_ITEM_LAN_16."</td>
							<td class='forumheader3' style='width:70%'>".$rs -> form_text("content_subheading", 100, $content_subheading, 250)."</td>
						</tr>
						<tr>
							<td class='forumheader3' style='width:30%'>".CONTENT_ADMIN_ITEM_LAN_17."</td>
							<td class='forumheader3' style='width:70%'>".$rs -> form_textarea("content_summary", 102, 5, $content_summary)."</td>
						</tr>
						<tr>
							<td class='forumheader3' style='width:30%'>".CONTENT_ADMIN_ITEM_LAN_18."</td>
							<td class='forumheader3' style='width:70%'>".$rs -> form_textarea("content_text", 102, 30, $content_text, "onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'")."
								<br />".$rs -> form_text("helpb", 100, '', '', "helpbox")."<br />";
								require_once(e_HANDLER."ren_help.php");
								$text .= ren_help()."
							</td>
						</tr>";

						if($checkicon){
							$text .= "
							<tr><td colspan='2' class='fcaption'>".CONTENT_ADMIN_ITEM_LAN_19."</td></tr>
							<tr>
								<td class='forumheader3' style='width:30%'>".CONTENT_ADMIN_ITEM_LAN_20."</td>
								<td class='forumheader3' style='width:70%'>".$rs -> form_hidden("uploadtype1", "icon");
								if ($content_icon){
										$text .= "
										".$rs -> form_text("content_icon", 50, $content_icon, 100, "tbox", TRUE)."
										".$rs -> form_button("button", "removeicon", CONTENT_ADMIN_ITEM_LAN_26, "onClick=\"confirm2_('icon', '', '$content_icon');\"").$rs -> form_button("button", "newicon", CONTENT_ADMIN_ITEM_LAN_25, "onClick='expandit(this)'")."
										<div style='display:none' style=&{head};>";
								}
								if(!FILE_UPLOADS){
									$text .= "<b>".CONTENT_ADMIN_ITEM_LAN_21."</b>";
								}else{
									if(!is_writable($content_icon_path)){
										$text .= "<b>".CONTENT_ADMIN_ITEM_LAN_22." ".$content_icon_path." ".CONTENT_ADMIN_ITEM_LAN_23."</b><br />";
									}
									$text .= "<input class='tbox' type='file' name='file_userfile1[]'  size='50' />";
								}
								if ($content_icon){
										$text .= "</div>";
								}

								$text .= "
								</td>
							</tr>";
						}

						if($checkattach){
							$text .= "<tr><td colspan='2' class='fcaption'>".CONTENT_ADMIN_ITEM_LAN_24."</td></tr>";
							$filetmp = explode("[file]", $content_file);
							foreach($filetmp as $key => $value) { 
								if($value == "") { 
									unset($filetmp[$key]); 
								} 
							} 
							$attachments = array_values($filetmp);
							for($i=0;$i<count($attachments);$i++){
								$k=$i+1;
								$text .= "
								<tr>
									<td class='forumheader3' style='width:30%'>".CONTENT_ADMIN_ITEM_LAN_27." ".$k."</td>
									<td class='forumheader3' style='width:70%'>".$rs -> form_hidden("uploadtype2", "file");
										if ($attachments[$i]){
											$text .= "
											".$rs -> form_text("content_files".$i."", 50, $attachments[$i], 100, "tbox", TRUE)."
											".$rs -> form_button("button", "removefile".$i."", CONTENT_ADMIN_ITEM_LAN_26, "onClick=\"confirm2_('file', '$i', '$attachments[$i]');\"").$rs -> form_button("button", "newfile".$i."", CONTENT_ADMIN_ITEM_LAN_28, "onClick='expandit(this)'")."
											<div style='display:none; &{head};'>
											<input class='tbox' type='file' name='file_userfile2[]' value='".$attachments[$i]."' size='50'>
											</div>
											";
										} else {
											$text .= "<i>".CONTENT_ADMIN_ITEM_LAN_29."</i><br /><input class='tbox' name='file_userfile2[]' type='file' size='50'>";
										}
									$text .= "
									</td>
								</tr>";
							}

							if(count($attachments) < $checkattachnumber){
								for($i=0;$i<$checkattachnumber-count($attachments);$i++){
									$text .= "
									<tr>
										<td class='forumheader3' style='width:30%'>".CONTENT_ADMIN_ITEM_LAN_30." ".($i+1+count($attachments))."</td>
										<td class='forumheader3' style='width:70%'>".$rs -> form_hidden("uploadtype2", "file");
										if(!FILE_UPLOADS){
											$text .= "<b>".CONTENT_ADMIN_ITEM_LAN_21."</b>";
										}else{
											if(!is_writable($content_file_path)){
												$text .= "<b>".CONTENT_ADMIN_ITEM_LAN_22." ".$content_file_path." ".CONTENT_ADMIN_ITEM_LAN_23."</b><br />";
											}
											$text .= "<input class='tbox' type='file' name='file_userfile2[]' size='50' />";
										}
										$text .= "
										</td>
									</tr>";
								}
							}
						}

						if($checkimages){
							$text .= "<tr><td colspan='2' class='fcaption'>".CONTENT_ADMIN_ITEM_LAN_31."</td></tr>";
							$imagestmp = explode("[img]", $content_image);
							foreach($imagestmp as $key => $value) { 
								if($value == "") { 
									unset($imagestmp[$key]); 
								} 
							} 
							$imagesarray = array_values($imagestmp);
							for($i=0;$i<count($imagesarray);$i++){
								$k=$i+1;
								$text .= "
								<tr>
									<td class='forumheader3' style='width:30%'>".CONTENT_ADMIN_ITEM_LAN_32." ".$k."</td>
									<td class='forumheader3' style='width:70%'>".$rs -> form_hidden("uploadtype3", "image");
										if ($imagesarray[$i]){
											$text .= "
											".$rs -> form_text("content_images".$i."", 50, $imagesarray[$i], 100, "tbox", TRUE)."
											".$rs -> form_button("button", "removeimage".$i."", CONTENT_ADMIN_ITEM_LAN_26, "onClick=\"confirm2_('image', '$i', '$imagesarray[$i]');\"").$rs -> form_button("button", "newimage".$i."", CONTENT_ADMIN_ITEM_LAN_33, "onClick='expandit(this)'")."
											<div style='display:none; &{head};'>
											<input class='tbox' type='file' name='file_userfile3[]' value='".$imagesarray[$i]."' size='50'>
											</div>
											";
										} else {
											$text .= "<i>no image yet</i><br /><input class='tbox' name='file_userfile3[]' type='file' size='50'>";
										}
									$text .= "
									</td>
								</tr>";
							}
							if(count($imagesarray) < $checkimagesnumber){
								for($i=0;$i<$checkimagesnumber-count($imagesarray);$i++){
									$text .= "
									<tr>
										<td class='forumheader3' style='width:30%'>".CONTENT_ADMIN_ITEM_LAN_34." ".($i+1+count($imagesarray))."</td>
										<td class='forumheader3' style='width:70%'>".$rs -> form_hidden("uploadtype3", "image");
										if(!FILE_UPLOADS){
											$text .= "<b>".CONTENT_ADMIN_ITEM_LAN_21."</b>";
										}else{
											if(!is_writable($content_image_path)){
												$text .= "<b>".CONTENT_ADMIN_ITEM_LAN_22." ".$content_image_path." ".CONTENT_ADMIN_ITEM_LAN_23."</b><br />";
											}
											$text .= "<input class='tbox' type='file' name='file_userfile3[]' size='50' />";
										}
										$text .= "
										</td>
									</tr>";
								}
							}
						}

						if($checkcomment || $checkrating || $checkscore || $checkpe || $checkvisibility || $checkmeta ){
							$text .= "<tr><td colspan='2' class='fcaption'>".CONTENT_ADMIN_ITEM_LAN_35."</td></tr>";
						}
						if($checkcomment){
							$text .= "
							<tr>
								<td class='forumheader3' style='width:30%'>".CONTENT_ADMIN_ITEM_LAN_36."</td>
								<td class='forumheader3' style='width:70%'>".$rs -> form_checkbox("content_comment", 1, ($content_comment ? "1" : "0"))."</td>
							</tr>";
						}

						if($checkrating){
							$text .= "
							<tr>
								<td class='forumheader3' style='width:30%'>".CONTENT_ADMIN_ITEM_LAN_37."</td>
								<td class='forumheader3' style='width:70%'>".$rs -> form_checkbox("content_rate", 1, ($content_rate ? "1" : "0"))."</td>
							</tr>";
						}

						if($checkpe){
							$text .= "
							<tr>
								<td class='forumheader3' style='width:30%'>".CONTENT_ADMIN_ITEM_LAN_38."</td>
								<td class='forumheader3' style='width:70%'>".$rs -> form_checkbox("content_pe", 1, ($content_pe ? "1" : "0"))."</td>
							</tr>";
						}

						if($checkvisibility){
							$text .= "
							<tr>
								<td class='forumheader3' style='width:30%'>".CONTENT_ADMIN_ITEM_LAN_39."</td>
								<td class='forumheader3' style='width:70%'>".r_userclass("content_class",$content_class, "CLASSES")."</td>
							</tr>";
						}

						if(!($_POST['preview'] || isset($message))){
							$custom = unserialize(stripslashes($contentprefvalue));
						}

						if($checkscore){
							$text .= "
							<tr>
								<td class='forumheader3' style='width:30%'>".CONTENT_ADMIN_ITEM_LAN_40."</td>
								<td class='forumheader3' style='width:70%'>
								".$rs -> form_select_open("content_score")."
								".$rs -> form_option(CONTENT_ADMIN_ITEM_LAN_41, 0, "none");
								for($a=1; $a<=100; $a++){
										$text .= $rs -> form_option($a, ($custom['content_custom_score'] == $a ? "1" : "0"), $a);
								}
								$text .= $rs -> form_select_close()."
								</td>
							</tr>";
						}

						if($checkmeta){
							$text .= "
							<tr>
								<td class='forumheader3' style='width:30%'>".CONTENT_ADMIN_ITEM_LAN_53."</td>
								<td class='forumheader3' style='width:70%'>".$rs -> form_text("content_meta", 100, $custom['content_custom_meta'], 250)."</td>
							</tr>";
						}
						
						if($checkcustomnumber){
							$text .= "<tr><td colspan='2' class='fcaption'>".CONTENT_ADMIN_ITEM_LAN_54."</td></tr>";
						}
						$existing_custom = "0";

						if(!empty($custom)){
							foreach($custom as $k => $v){
								if(!($k == "content_custom_score" || $k == "content_custom_meta")){
									$key = substr($k,15);
									if($checkcustomnumber){
										$text .= "
										<tr>
											<td class='forumheader3' style='width:30%'>".$rs -> form_text("content_custom_key_".$existing_custom."", 30, $key, 100)."</td>
											<td class='forumheader3' style='width:70%'>".$rs -> form_text("content_custom_value_".$existing_custom."", 100, $v, 250)."</td>
										</tr>";
									}else{
										$text .= $rs -> form_hidden("content_custom_key_".$existing_custom, $key);
										$text .= $rs -> form_hidden("content_custom_value_".$existing_custom, $v);
									}
									$existing_custom = $existing_custom + 1;
								}
							}
						}
						for($i=$existing_custom;$i<$checkcustomnumber;$i++){
								$text .= "
								<tr>
									<td class='forumheader3' style='width:30%'>".$rs -> form_text("content_custom_key_".$i."", 30, "", 100)."</td>
									<td class='forumheader3' style='width:70%'>".$rs -> form_text("content_custom_value_".$i."", 100, "", 250)."</td>
								</tr>";
						}

						$text .= "
						<tr style='vertical-align:top'>
							<td colspan='2' style='text-align:center' class='forumheader'>";
							if($sub_action == "edit" || $sub_action == "sa" || $_POST['editp']){
								$text .= $rs -> form_hidden("content_refer", $content_refer);
								$text .= $rs -> form_hidden("content_datestamp", $content_datestamp);
								$text .= $rs -> form_button("submit", "update_content", ($sub_action == "sa" ? CONTENT_ADMIN_ITEM_LAN_43 : CONTENT_ADMIN_ITEM_LAN_45) );
								$text .= $rs -> form_hidden("content_id", $id);
								$text .= $rs -> form_checkbox("update_datestamp", 1, 0)." ".CONTENT_ADMIN_ITEM_LAN_42;
							}else{
								$text .= $rs -> form_button("submit", "create_content", CONTENT_ADMIN_ITEM_LAN_44);
							}
							$text .= "
							</td>
						</tr>

						</table>
						</form>
						</div>";

						$caption = ($sub_action == "edit" ? CONTENT_ADMIN_ITEM_LAN_45 : CONTENT_ADMIN_ITEM_LAN_44);
						$ns -> tablerender($caption, $text);
		}


		function show_content_submitted($mode){
						global $rs, $ns, $aa, $plugintable, $tp;
						global $type, $type_id;
						if(!is_object($sql)){ $sql = new db; }
						$text = "<div style='text-align:center'>\n";
						if($content_total = $sql -> db_Select($plugintable, "content_id, content_heading, content_subheading, content_author, content_icon, content_parent", "content_refer = 'sa' ")){
								$text .= "<table style='".ADMIN_WIDTH."' class='fborder'>
								<tr>
								<td style='width:5%; text-align:center' class='fcaption'>".CONTENT_ADMIN_ITEM_LAN_8."</td>
								<td style='width:5%; text-align:center' class='fcaption'>".CONTENT_ADMIN_ITEM_LAN_9."</td>
								<td style='width:15%; text-align:left' class='fcaption'>".CONTENT_ADMIN_ITEM_LAN_48."</td>
								<td style='width:70%; text-align:left' class='fcaption'>".CONTENT_ADMIN_ITEM_LAN_11."</td>
								<td style='width:10%; text-align:center' class='fcaption'>".CONTENT_ADMIN_ITEM_LAN_12."</td>
								</tr>";
								while($row = $sql -> db_Fetch()){
								extract($row);
										unset($content_pref);
										$type_id_parent = substr($content_parent,0,1);
										if(!is_object($sql2)){ $sql2 = new db; }
										$sql2 -> db_Select($plugintable, "content_id, content_heading", "content_id = '".$type_id_parent."' ");
										list($parent_id, $parent_heading) = $sql2 -> db_Fetch();
										$delete_heading = str_replace("&#39;", "\'", $content_heading);
										$authordetails = $aa -> getAuthor($content_author);
										$content_pref = $aa -> getContentPref($content_id);
										$content_pref["content_icon_path_{$type_id_parent}"] = ($content_pref["content_icon_path_{$type_id_parent}"] ? $content_pref["content_icon_path_{$type_id_parent}"] : "{e_PLUGIN}content/images/icon/" );
										$content_icon_path = $aa -> parseContentPathVars($content_pref["content_icon_path_{$type_id_parent}"]);
										$caticon = $content_icon_path.$content_icon;
										$text .= "
										<tr>
											<td class='forumheader3' style='width:5%; text-align:center'>".$content_id."</td>
											<td class='forumheader3' style='width:5%; text-align:center'>".($content_icon ? "<img src='".$caticon."' alt='' style='width:50px; vertical-align:middle' />" : "&nbsp;")."</td>
											<td class='forumheader3' style='width:15%; text-align:left'>".$parent_heading."</td>
											<td class='forumheader3' style='width:75%; text-align:left; white-space:nowrap;'><b>".$content_heading."</b> [".$content_subheading."]<br />
											".($authordetails[0] == "0" ? $authordetails[1] : "<a href='".e_BASE."user.php?id.".$authordetails[0]."'>".$authordetails[1]."</a>")."	
											(".$authordetails[2].")</td>
											<td class='forumheader3' style='width:5%; text-align:center; white-space:nowrap;'>
											".$rs -> form_open("post", e_SELF."?".$type.".".$type_id_parent, "myform_{$content_id}","","", "")."
											<a href='".e_SELF."?".$type.".".$type_id_parent.".create.sa.".$content_id."'>".CONTENT_ICON_EDIT."</a> 
											<a onclick=\"if(jsconfirm('".$tp->toJS(CONTENT_ADMIN_JS_LAN_10."\\n\\n[".CONTENT_ADMIN_JS_LAN_6." ".$content_id." : ".$delete_heading."]")."')){document.forms['myform_{$content_id}'].submit();}\" >".CONTENT_ICON_DELETE."</a>
											".$rs -> form_hidden("content_delete_{$content_id}", "delete")."
											".$rs -> form_close()."
											</td>
										</tr>";
								}
								$text .= "</table>";
						}else{
							$text .= "<div style='text-align:center'>".CONTENT_ADMIN_ITEM_LAN_50."</div>";
						}
						$text .= "</div>";
						$ns -> tablerender(CONTENT_ADMIN_ITEM_LAN_49, $text);
		}


		function show_content_manage($mode, $userid="", $username=""){
						global $sql, $ns, $rs, $aa, $plugintable, $tp;
						global $type, $type_id, $action, $sub_action, $id;

						$content_pref = $aa -> getContentPref($type_id);
						$content_cat_icon_path_large = $aa -> parseContentPathVars($content_pref["content_cat_icon_path_large_{$type_id}"]);
						$content_cat_icon_path_small = $aa -> parseContentPathVars($content_pref["content_cat_icon_path_small_{$type_id}"]);
						$content_icon_path = $aa -> parseContentPathVars($content_pref["content_icon_path_{$type_id}"]);
						$content_image_path = $aa -> parseContentPathVars($content_pref["content_image_path_{$type_id}"]);
						$content_file_path = $aa -> parseContentPathVars($content_pref["content_file_path_{$type_id}"]);

						if($mode == "contentmanager"){
							if($action != "c"){
								header("location:".e_SELF); exit;
							}
							$aa -> ContentManagerValidCategoryCheck($sub_action);

							//if(!is_object($sql)){ $sql = new db; }
							//$sql -> db_Select($plugintable, "content_parent", "content_id = '".$action."' ");
							//list($parentparent) = $sql -> db_Fetch();

							//use user restriction (personal admin)
							if(getperms("0")){
								$userid = USERID;
								$username = USERNAME;
							}
							if($userid != "" && $username != ""){
								$userquery = " AND (content_author = '".$userid."' OR SUBSTRING_INDEX(content_author, '^', 1) = '".$userid."' OR SUBSTRING_INDEX(content_author, '^', 2) = '".$userid."^".$username."')";
							}else{
								$userquery = "";
							}
							
							$cat = str_replace("-", ".", $sub_action);
							$parent = $cat;
							$query = " content_parent = '".$cat."' ";
							$formtarget = e_SELF."?".$type.".".$type_id.".c.".$sub_action;
						}else{
							if($action == "c"){
									$cat = str_replace("-", ".", $sub_action);
									$query = " content_parent = '".$cat."' ";
									$formtarget = e_SELF."?".$type.".".$type_id.".c.".$sub_action;
							} else {
									$query = "LEFT(content_parent,".strlen($type_id).") = '".$type_id."' ";
									$formtarget = e_SELF."?".$type.".".$type_id;
							}
							$userquery = "";
						}

						$text = "";
						// -------- SHOW FIRST LETTERS FIRSTNAMES ------------------------------------
						if(!is_object($sql)){ $sql = new db; }
						$distinctfirstletter = $sql -> db_Select($plugintable, "DISTINCT(LEFT(content_heading,1)) as letter", "content_refer != 'sa' AND ".$query." ".$userquery." ORDER BY content_heading ASC ");
						if ($distinctfirstletter == 0){
								$text .= "<div style='text-align:center'>".CONTENT_ADMIN_ITEM_LAN_4."</div>";
								$ns -> tablerender(CONTENT_ADMIN_ITEM_LAN_5, $text);
								return;

						}elseif ($distinctfirstletter != 1){
								$text .= "
								<div style='text-align:center'>
								<form method='post' action='".$formtarget."'>
								<table class='fborder' style='".ADMIN_WIDTH."'>
								<tr><td colspan='2' class='fcaption'>".CONTENT_ADMIN_ITEM_LAN_6."</td></tr>
								<tr><td colspan='2' class='forumheader3'>";
								while($row = $sql-> db_Fetch()){
								extract($row);
									if($letter != ""){
										$text .= "<input class='button' style='width:20' type='submit' name='letter' value='".strtoupper($letter)."' />";
									}
								}
								$text .= "
								<input class='button' style='width:20' type='submit' name='letter' value='all' />
								</td>
								</tr>
								</table>
								</form>
								</div>";
						}
						// ---------------------------------------------------------------------------

						// -------- CHECK FOR FIRST LETTER SUBMISSION --------------------------------
						$letter=$_POST['letter'];
						if($mode == "contentmanager"){
							$cat = str_replace("-", ".", $sub_action);
							if ($letter != "" && $letter != "all" ) { $letterquery = " AND content_heading LIKE '".$letter."%' "; }else{ $letterquery = ""; }
							$query = "content_refer != 'sa' AND content_parent = '".$cat."' ".$letterquery." ".$userquery." ORDER BY content_datestamp DESC";
						}else{
							if($action == "c"){
									$cat = str_replace("-", ".", $sub_action);
									if ($letter != "" && $letter != "all" ) { $letterquery = " AND content_heading LIKE '".$letter."%' "; }else{ $letterquery = ""; }
									$query = "content_refer != 'sa' AND content_parent = '".$cat."' ".$letterquery." ORDER BY content_datestamp DESC";
							} else {
									if ($letter != "" && $letter != "all" ) { $letterquery = " AND content_heading LIKE '".$letter."%' "; }else{ $letterquery = ""; }
									$query = "content_refer != 'sa' AND LEFT(content_parent,".strlen($type_id).") = '".$type_id."' ".$letterquery." ORDER BY content_datestamp DESC";
							}
						}
						// ---------------------------------------------------------------------------

						if(!is_object($sql2)){ $sql2 = new db; }
						$text .= "<div style='text-align:center'>";
						if(!$content_total = $sql2 -> db_Select($plugintable, "content_id, content_heading, content_subheading, content_author, content_icon", $query)){
							$text .= "<div style='text-align:center'>".CONTENT_ADMIN_ITEM_LAN_4."</div>";
						}else{
							if($content_total < 50 || $letter || $cat){
									$text .= "<table style='".ADMIN_WIDTH."' class='fborder'>
									<tr>
									<td class='fcaption' style='width:5%; text-align:center;'>".CONTENT_ADMIN_ITEM_LAN_8."</td>
									<td class='fcaption' style='width:5%; text-align:center;'>".CONTENT_ADMIN_ITEM_LAN_9."</td>
									<td class='fcaption' style='width:10%; text-align:left;'>".CONTENT_ADMIN_ITEM_LAN_10."</td>
									<td class='fcaption' style='width:70%; text-align:left;'>".CONTENT_ADMIN_ITEM_LAN_11."</td>
									<td class='fcaption' style='width:10%; text-align:center;'>".CONTENT_ADMIN_ITEM_LAN_12."</td>
									</tr>";
									while($row = $sql2 -> db_Fetch()){
									extract($row);
											$delete_heading = str_replace("&#39;", "\'", $content_heading);
											$authordetails = $aa -> getAuthor($content_author);
											$caticon = $content_icon_path.$content_icon;
											$deleteicon = CONTENT_ICON_DELETE;
											$text .= "
											<tr>
												<td class='forumheader3' style='width:5%; text-align:center'>".$content_id."</td>
												<td class='forumheader3' style='width:5%; text-align:center'>".($content_icon ? "<img src='".$caticon."' alt='' style='width:50px; vertical-align:middle' />" : "&nbsp;")."</td>
												<td class='forumheader3' style='width:10%; text-align:left'>[".$authordetails[0]."] ".$authordetails[1]."</td>
												<td class='forumheader3' style='width:70%; text-align:left; white-space:nowrap;'>".$content_heading." [".content_subheading."]</td>
												<td class='forumheader3' style='width:10%; text-align:center; white-space:nowrap;'>
												".$rs -> form_open("post", e_SELF."?".$type.".".$type_id, "myform_{$content_id}","","", "")."
												<a href='".e_SELF."?".$type.".".$type_id.".create.edit.".$content_id."'>".CONTENT_ICON_EDIT."</a> 
												<a onclick=\"if(jsconfirm('".$tp->toJS(CONTENT_ADMIN_JS_LAN_1."\\n\\n[".CONTENT_ADMIN_JS_LAN_6." ".$content_id." : ".$delete_heading."]")."')){document.forms['myform_{$content_id}'].submit();}\" >".CONTENT_ICON_DELETE."</a>
												".$rs -> form_hidden("content_delete_{$content_id}", "delete")."
												".$rs -> form_close()."
												</td>
											</tr>";
									}
									$text .= "</table>";
							} else {
									$text .= "<br /><div style='text-align:center'>".CONTENT_ADMIN_ITEM_LAN_7."</div>";
							}
						}
						$text .= "</div>";
						$ns -> tablerender(CONTENT_ADMIN_ITEM_LAN_5, $text);
		}


		function show_cat_create(){
						global $plugintable, $sql, $ns, $rs, $aa;
						global $type, $type_id, $action, $sub_action, $id;
						global $content_parent, $content_heading, $content_subheading, $content_text, $content_icon, $content_comment, $content_rate, $content_pe, $content_class;

						if(!is_object($sql)){ $sql = new db; }
						if($type_id != "0"){ $parentdetails = $aa -> getParent("", "", $type_id); }

						$content_pref = $aa -> getContentPref(($type_id != "0" ? $type_id : "0"));
						$content_cat_icon_path_large = $aa -> parseContentPathVars($content_pref["content_cat_icon_path_large_{$type_id}"]);
						$content_cat_icon_path_small = $aa -> parseContentPathVars($content_pref["content_cat_icon_path_small_{$type_id}"]);
						$content_icon_path = $aa -> parseContentPathVars($content_pref["content_icon_path_{$type_id}"]);
						$content_image_path = $aa -> parseContentPathVars($content_pref["content_image_path_{$type_id}"]);
						$content_file_path = $aa -> parseContentPathVars($content_pref["content_file_path_{$type_id}"]);

						if($sub_action == "edit" && is_numeric($id)){
							if($sql -> db_Select($plugintable, "content_id, content_heading, content_subheading, content_summary, content_text, content_author, content_icon, content_file, content_image, content_parent, content_comment, content_rate, content_pe, content_refer, content_datestamp, content_class, content_pref as contentprefvalue", "content_id='$id' ")){
								$row = $sql -> db_Fetch(); extract($row);
								if(substr($content_parent,0,1) != "0"){
									unset($id); header("location:".e_SELF."?".$type.".".$type_id.".cat"); exit;
								}
							}else{
								header("location:".e_SELF."?".$type.".".$type_id.".cat"); exit;
							}
						}

						$handle=opendir($content_cat_icon_path_small);
						while ($file = readdir($handle)){
							if($file != "." && $file != ".." && $file != "/" && strtolower($file) != "thumbs.db"){
								$iconlist[] = $file;
							}
						}
						closedir($handle);

						$text = "
						<div style='text-align:center'>
						".$rs -> form_open("post", e_SELF."?".$type.".".$type_id.".cat.create", "dataform")."
						<table class='fborder' style='".ADMIN_WIDTH."'>";

						$smarray = getdate();
						//$ne_day = $smarray['mday'];
						//$ne_month = $smarray['mon'];
						$current_year = $smarray['year'];

						$months = array(CONTENT_ADMIN_DATE_LAN_0, CONTENT_ADMIN_DATE_LAN_1, CONTENT_ADMIN_DATE_LAN_2, CONTENT_ADMIN_DATE_LAN_3, CONTENT_ADMIN_DATE_LAN_4, CONTENT_ADMIN_DATE_LAN_5, CONTENT_ADMIN_DATE_LAN_6, CONTENT_ADMIN_DATE_LAN_7, CONTENT_ADMIN_DATE_LAN_8, CONTENT_ADMIN_DATE_LAN_9, CONTENT_ADMIN_DATE_LAN_10, CONTENT_ADMIN_DATE_LAN_11);
						
						$text .= "
						<tr>
							<td class='forumheader3' style='width:30%; vertical-align:top'>".CONTENT_ADMIN_DATE_LAN_15."<br />".CONTENT_ADMIN_DATE_LAN_17."</td>
							<td class='forumheader3' style='width:70%'>

								".$rs -> form_select_open("ne_day")."
								".$rs -> form_option(CONTENT_ADMIN_DATE_LAN_12, 0, "none");
								for($count=1; $count<=31; $count++){
									$text .= $rs -> form_option($count, ($ne_day == $count ? "1" : "0"), $count);
								}
								$text .= $rs -> form_select_close()."

								".$rs -> form_select_open("ne_month")."
								".$rs -> form_option(CONTENT_ADMIN_DATE_LAN_13, 0, "none");
								for($count=1; $count<=12; $count++){
									$text .= $rs -> form_option($months[($count-1)], ($ne_month == $count ? "1" : "0"), $count);
									
								}
								$text .= $rs -> form_select_close()."

								".$rs -> form_select_open("ne_year")."
								".$rs -> form_option(CONTENT_ADMIN_DATE_LAN_14, 0, "none");
								for($count=($current_year-5); $count<=$current_year; $count++){
									$text .= $rs -> form_option($count, ($ne_year == $count ? "1" : "0"), $count);
								}
								$text .= $rs -> form_select_close()."

							</td>
						</tr>";

						$text .= "
						<tr>
							<td class='forumheader3' style='width:30%; vertical-align:top'>".CONTENT_ADMIN_DATE_LAN_16."<br />".CONTENT_ADMIN_DATE_LAN_18."</td>
							<td class='forumheader3' style='width:70%'>

								".$rs -> form_select_open("end_day")."
								".$rs -> form_option(CONTENT_ADMIN_DATE_LAN_12, 0, "none");
								for($count=1; $count<=31; $count++){
									$text .= $rs -> form_option($count, ($ne_day == $count ? "1" : "0"), $count);
								}
								$text .= $rs -> form_select_close()."

								".$rs -> form_select_open("end_month")."
								".$rs -> form_option(CONTENT_ADMIN_DATE_LAN_13, 0, "none");
								for($count=1; $count<=12; $count++){
									$text .= $rs -> form_option($months[($count-1)], ($ne_month == $count ? "1" : "0"), $count);
									
								}
								$text .= $rs -> form_select_close()."

								".$rs -> form_select_open("end_year")."
								".$rs -> form_option(CONTENT_ADMIN_DATE_LAN_14, 0, "none");
								for($count=($current_year-5); $count<=$current_year; $count++){
									$text .= $rs -> form_option($count, ($ne_year == $count ? "1" : "0"), $count);
								}
								$text .= $rs -> form_select_close()."

							</td>
						</tr>";
						
						$text .= "
						<tr>
							<td class='forumheader3' style='width:30%; vertical-align:top'>".CONTENT_ADMIN_CAT_LAN_27.":</td>
							<td class='forumheader3' style='width:70%'>
								".$rs -> form_select_open("parent")."
								".$rs -> form_option("** ".CONTENT_ADMIN_CAT_LAN_26." **", "0", "none")."
								".$aa -> printParent($parentdetails, "0", $content_parent, "optioncat")."
								".$rs -> form_select_close()."
							</td>
						</tr>
						<tr>
							<td class='forumheader3' style='width:30%'>".CONTENT_ADMIN_CAT_LAN_2."</td>
							<td class='forumheader3' style='width:70%'>".$rs -> form_text("cat_heading", 100, $content_heading, 250)."</td>
						</tr>
						<tr>
							<td class='forumheader3' style='width:30%'>".CONTENT_ADMIN_CAT_LAN_3."</td>
							<td class='forumheader3' style='width:70%'>".$rs -> form_text("cat_subheading", 100, $content_subheading, 250)."</td>
						</tr>
						<tr>
							<td class='forumheader3' style='width:30%'>".CONTENT_ADMIN_CAT_LAN_4."</td>
							<td class='forumheader3' style='width:70%'>".$rs -> form_textarea("cat_text", 102, 20, $content_text, "onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'")."
								<br />".$rs -> form_text("helpb", 100, '', '', "helpbox")."<br />";
								require_once(e_HANDLER."ren_help.php");
								$text .= ren_help()."
							</td>
						</tr>
						<tr>
							<td class='forumheader3' style='width:30%'>".CONTENT_ADMIN_CAT_LAN_5."</td>
							<td class='forumheader3' style='width:70%'>".$rs -> form_text("cat_icon", 60, $content_icon, 100)."
								".$rs -> form_button("button", '', CONTENT_ADMIN_CAT_LAN_8, "onclick='expandit(this)'")."
								<div style='{head}; display:none'>";
								while(list($key, $icon) = each($iconlist)){
									$text .= "<a href='javascript:addtext2(\"$icon\")'><img src='".$content_cat_icon_path_large.$icon."' style='border:0' alt='' /></a> ";
								}
								$text .= "</div>
							</td>
						</tr>
						<tr>
							<td class='forumheader3' style='width:30%'>".CONTENT_ADMIN_CAT_LAN_14."</td>
							<td class='forumheader3' style='width:70%'>".$rs -> form_checkbox("cat_comment", 1, ($content_comment ? "1" : "0"))."</td>
						</tr>
						<tr>
							<td class='forumheader3' style='width:30%'>".CONTENT_ADMIN_CAT_LAN_15."</td>
							<td class='forumheader3' style='width:70%'>".$rs -> form_checkbox("cat_rate", 1, ($content_rate ? "1" : "0"))."</td>
						</tr>
						<tr>
							<td class='forumheader3' style='width:30%'>".CONTENT_ADMIN_CAT_LAN_16."</td>
							<td class='forumheader3' style='width:70%'>".$rs -> form_checkbox("cat_pe", 1, ($content_pe ? "1" : "0"))."</td>
						</tr>
						<tr>
							<td class='forumheader3' style='width:30%'>".CONTENT_ADMIN_CAT_LAN_17."</td>
							<td class='forumheader3' style='width:70%'>".r_userclass("cat_class",$content_class, "CLASSES")."</td>
						</tr>
						<tr>
							<td class='forumheader' style='text-align:center' colspan='2'>";
							if($id){
								$text .= $rs -> form_button("submit", "update_category", CONTENT_ADMIN_CAT_LAN_7).$rs -> form_button("submit", "category_clear", CONTENT_ADMIN_CAT_LAN_21).$rs -> form_hidden("cat_id", $id).$rs -> form_hidden("id", $id);
								$caption = CONTENT_ADMIN_CAT_LAN_1;
							}else{
								$text .= $rs -> form_button("submit", "create_category", CONTENT_ADMIN_CAT_LAN_6);
								$caption = CONTENT_ADMIN_CAT_LAN_0;
							}
							$text .= "
							</td>
						</tr>
						</table>
						".$rs -> form_close()."
						</div>";

						$ns -> tablerender($caption, $text);
		}


		function show_contentmanager($mode, $userid="", $username=""){
						global $sql, $ns, $rs, $type, $type_id, $plugintable, $aa;
						$personalmanagercheck = FALSE;

						if(!$CONTENT_CONTENTMANAGER_TABLE){
							if(!$content_pref["content_theme_{$type_id}"]){
								require_once(e_PLUGIN."content/templates/default/content_manager_template.php");
							}else{
								if(file_exists(e_PLUGIN."content/templates/".$content_pref["content_theme_{$type_id}"]."/content_manager_template.php")){
									require_once(e_PLUGIN."content/templates/".$content_pref["content_theme_{$type_id}"]."/content_manager_template.php");
								}else{
									require_once(e_PLUGIN."content/templates/default/content_manager_template.php");
								}
							}
						}

						if(!is_object($sql2)){ $sql2 = new db; }
						if($sql2 -> db_Select($plugintable, "content_id", " content_parent='0' ORDER BY content_heading")){
							while(list($parent_id) = $sql2 -> db_Fetch()){
								$type_id = $parent_id;
								$prefetchbreadcrumb = $aa -> prefetchBreadCrumb( $type_id );

								$checkquery = (getperms("0") ? "" : " AND FIND_IN_SET('".$userid."',content_pref) ");
								if(!is_object($sql)){ $sql = new db; }
								if(!$sql -> db_Select($plugintable, "content_id", "LEFT(content_parent, ".(strlen($type_id)+2).") = '0.".$type_id."' ".$checkquery." ORDER BY content_parent")){
									//	header("location:".e_PLUGIN."content/content.php"); exit;
								}else{
									while($row = $sql -> db_Fetch()){
										extract($row);
											$personalmanagercheck = TRUE;
										
											$parentheading = $aa -> drawBreadcrumb($prefetchbreadcrumb, $content_id, "nobase", "nolink");
											for($i=0; $i<count($prefetchbreadcrumb); $i++){
												if($content_id == $prefetchbreadcrumb[$i][0]){
													$catidstring = $prefetchbreadcrumb[$i][3];
												}
											}
											$catidstring = str_replace(".","-",$catidstring);
											$CONTENT_CONTENTMANAGER_ICONEDIT = "<a href='".e_SELF."?".$type.".".$type_id.".c.".$catidstring."'>".CONTENT_ICON_EDIT."</a>";
											$CONTENT_CONTENTMANAGER_ICONNEW = "<a href='".e_SELF."?".$type.".".$type_id.".create.".$catidstring."'>".CONTENT_ICON_NEW."</a>";


											$CONTENT_CONTENTMANAGER_CATEGORY = $parentheading;
											$content_contentmanager_table_string .= preg_replace("/\{(.*?)\}/e", '$\1', $CONTENT_CONTENTMANAGER_TABLE);
									}
								}
							}
							if($personalmanagercheck == TRUE){
								$content_contentmanager_table_start = preg_replace("/\{(.*?)\}/e", '$\1', $CONTENT_CONTENTMANAGER_TABLE_START);
								$content_contentmanager_table_end = preg_replace("/\{(.*?)\}/e", '$\1', $CONTENT_CONTENTMANAGER_TABLE_END);
								$text = $content_contentmanager_table_start.$content_contentmanager_table_string.$content_contentmanager_table_end;
								$ns -> tablerender(CONTENT_ADMIN_ITEM_LAN_56, $text);
							}else{
								header("location:".e_PLUGIN."content/content.php"); exit;
							}
						}
		}


		function show_main_parent($mode){
						global $sql, $ns, $rs, $type, $type_id, $action, $sub_action, $id, $plugintable;

						if(!is_object($sql)){ $sql = new db; }
						if(!$sql -> db_Select($plugintable, "content_id, content_heading", "content_parent='0' ")){
								$text .= "<div style='text-align:center'>".CONTENT_ADMIN_MAIN_LAN_1."</div>";
								$ns -> tablerender(CONTENT_ADMIN_MAIN_LAN_0, $text);
								return;
						}else{
								$text .= "
								<div style='text-align:center'>
								<table class='fborder' style='".ADMIN_WIDTH."'>
								<tr><td class='fcaption'>".CONTENT_ADMIN_MAIN_LAN_2."</td></tr><tr><td class='forumheader3'>";
								while($row = $sql -> db_Fetch()){
									extract($row);
										if($mode == "create"){ $urllocation = "".e_SELF."?".$type.".".$content_id.".create"; }
										if($mode == "edit"){ $urllocation = "".e_SELF."?".$type.".".$content_id.""; }
										if($mode == "editcat"){ $urllocation = "".e_SELF."?".$type.".".$content_id.".cat.manage"; }
										if($mode == "createcat"){ $urllocation = "".e_SELF."?".$type.".".$content_id.".cat.create"; }
										$text .= $rs -> form_button("submit", "typeselect", $content_heading, "onclick=\"document.location='".$urllocation."'\"")." ";
									}
								$text .= "</table></div>";
								$ns -> tablerender(CONTENT_ADMIN_MAIN_LAN_0, $text);
						}
						return;
		}


		function show_cat_manage(){
						global $sql, $ns, $rs, $aa, $plugintable;
						global $type, $type_id, $action, $sub_action, $id;

						$parentdetails = $aa -> getParent("","",$type_id, "");
						$content_pref = $aa -> getContentPref($type_id);
						$content_cat_icon_path_large = $aa -> parseContentPathVars($content_pref["content_cat_icon_path_large_{$type_id}"]);
						$content_cat_icon_path_small = $aa -> parseContentPathVars($content_pref["content_cat_icon_path_small_{$type_id}"]);
						$content_icon_path = $aa -> parseContentPathVars($content_pref["content_icon_path_{$type_id}"]);
						$content_image_path = $aa -> parseContentPathVars($content_pref["content_image_path_{$type_id}"]);
						$content_file_path = $aa -> parseContentPathVars($content_pref["content_file_path_{$type_id}"]);

						// ##### MANAGE CATEGORIES ------------------
						if(!$sub_action || $sub_action == "manage"){
								$text = "<div style='text-align:center'>\n";
								if(!is_object($sql)){ $sql = new db; }
								if($category_total = $sql -> db_Select($plugintable, "content_id, content_heading, content_subheading, content_text, content_author, content_icon", "content_id='".$type_id."' ")){
										$text .= "
										<table style='".ADMIN_WIDTH."' class='fborder'>
										<tr>
										<td class='fcaption' style='width:5%'>".CONTENT_ADMIN_CAT_LAN_24."</td>
										<td class='fcaption' style='width:5%'>".CONTENT_ADMIN_CAT_LAN_25."</td>
										<td class='fcaption' style='width:10%'>".CONTENT_ADMIN_CAT_LAN_18."</td>
										<td class='fcaption' style='width:70%'>".CONTENT_ADMIN_CAT_LAN_19."</td>
										<td class='fcaption' style='width:10%; text-align:center'>".CONTENT_ADMIN_CAT_LAN_20."</td>
										</tr>";
										$text .= $aa -> printParent($parentdetails, "0", $content_id, "table");
										$text .= "</table>";
								}else{
									$text .= "<div style='text-align:center'>".CONTENT_ADMIN_CAT_LAN_9."</div>";
								}
								$text .= "</div>";
								$ns -> tablerender(CONTENT_ADMIN_CAT_LAN_10, $text);
								unset($content_id, $content_heading, $content_subheading, $content_text, $content_icon);
						}
		}


		function show_admin_contentmanager(){
						global $plugintable;
						global $sql, $ns, $rs, $aa;
						global $type, $type_id, $action, $sub_action, $id;

						if(!getperms("0")){ header("location:".e_SELF); exit; }

						if(!is_object($sql)){ $sql = new db; }
						if($sql -> db_Select($plugintable, "content_id, content_pref as contentprefvalue", "content_id='$id' ")){
							$row = $sql -> db_Fetch(); extract($row);
						}else{
							header("location:".e_SELF."?".$type.".".$type_id.".cat.".$id); exit;
						}

						$personalcontentusers = explode(",", $contentprefvalue);
						for($i=0;$i<count($personalcontentusers);$i++){
							if(empty($personalcontentusers[$i])){ unset($personalcontentusers[$i]); }
						}

						if(!is_object($sql2)){ $sql2 = new db; }
						$sql2->db_Select("user", "*", "user_admin='1' AND user_perms != '0' ");
						$c = 0;
						$d = 0;
						while ($row = $sql2->db_Fetch()) {
							extract($row);
								if(in_array($user_id, $personalcontentusers)){
									$in_userid[$c] = $user_id;
									$in_username[$c] = $user_name;
									$in_userlogin[$c] = $user_login ? "(".$user_login.")" :
									 "";
									$c++;
								}else{
									$out_userid[$d] = $user_id;
									$out_username[$d] = $user_name;
									$out_userlogin[$d] = $user_login ? "(".$user_login.")" :
									 "";
									$d++;
								}
						}

						$text = "
						<div style='text-align:center'>
						".$rs -> form_open("post", e_SELF."?".$type.".".$type_id.".cat.personalmanager.".$id, "dataform")."
						<table class='fborder' style='".ADMIN_WIDTH."'>
						<tr><td class='forumheader' style='text-align:center;'>".CONTENT_ADMIN_CAT_LAN_28."</td></tr>
						<tr><td class='forumheader3' style='text-align:center'>
					 		<table style='width:98%;'>
							<tr>
							<td style='width:50%; vertical-align:top'>".CONTENT_ADMIN_CAT_LAN_29."<br />
							<select class='tbox' id='assignclass1' name='assignclass1' size='10' style='width:220px' multiple='multiple' onchange='moveOver();'>";
				 
							for ($a = 0; $a <= ($d-1); $a++) {
								$text .= "<option value=".$out_userid[$a].">".$out_username[$a]." ".$out_userlogin[$a]."</option>";
							}
				 
							$text .= "
							</select>
							</td>
							<td style='width:50%; vertical-align:top'>".CONTENT_ADMIN_CAT_LAN_30."<br />
							<select class='tbox' id='assignclass2' name='assignclass2' size='10' style='width:220px' multiple='multiple'>";
							for($a = 0; $a <= ($c-1); $a++) {
								$text .= "<option value=".$in_userid[$a].">".$in_username[$a]." ".$in_userlogin[$a]."</option>";
								$class_id .= $in_userid[$a].".";
							}
							$text .= "
							</select><br /><br />
							<input class='button' type='button' value='".CONTENT_ADMIN_CAT_LAN_31."' onclick='removeMe();' />
							<input type='hidden' name='class_id' value='".$class_id."'>
							</td>
							</tr>
							</table>
						</td></tr>
						<tr><td style='text-align:center' class='forumheader'>
						".$rs -> form_button("submit", "assign_admins", CONTENT_ADMIN_CAT_LAN_33)."
						".$rs -> form_hidden("cat_id", $id)."
						</td>
						</tr>
						</table>
						".$rs -> form_close()."
						</div>";

						$ns -> tablerender($caption, $text);
		}


		function show_cat_options(){
						global $sql, $ns, $rs, $aa, $content_pref, $content_cat_icon_path_large, $content_cat_icon_path_small, $plugintable;
						global $type, $type_id, $action, $sub_action, $id;
						if(!is_object($sql)){ $sql = new db; }
						$parentdetails = $aa -> getParent();

						if(!is_numeric($id) || $type_id != $id){
							header("location:".e_SELF."?".$type.".".$type_id.".cat"); exit;
						}

						if(!$sql -> db_Select($plugintable, "content_heading", "content_id='".$id."' AND content_parent = '0' ")){
								header("location:".e_SELF."?".$type.".".$type_id.".cat"); exit;
						}else{
								while($row = $sql -> db_Fetch()){
								extract($row);
									$caption = CONTENT_ADMIN_OPT_LAN_0." : ".$content_heading;
								}
						}

						//check prefs two times to insure they are shown, if none present, the first inserts them, the second retrieves them
						$content_pref = $aa -> getContentPref($id);
						$content_pref = $aa -> getContentPref($id);

						$text = "
						<script type=\"text/javascript\">
						<!--
						var hideid=\"creation\";
						function showhideit(showid){
							if (hideid!=showid){
								show=document.getElementById(showid).style;
								hide=document.getElementById(hideid).style;
								show.display=\"\";
								hide.display=\"none\";
								hideid = showid;
							}
						}
						//-->
						</script>";

						$text .= "
						<div style='text-align:center'>
						<form method='post' action='".e_SELF."?".e_QUERY."'>\n

						<div id='creation' style='text-align:center'>
						<table style='".ADMIN_WIDTH."' class='fborder'>
						<tr><td colspan='6' class='fcaption'>".CONTENT_ADMIN_OPT_LAN_1."</td></tr>
						<tr>
							<td class='forumheader3' style='text-align:right'>".CONTENT_ADMIN_OPT_LAN_60."</td>
							<td class='forumheader3' style='text-align:center; width:5%;'>".$rs -> form_checkbox("content_admin_icon_{$id}", 1, ($content_pref["content_admin_icon_{$id}"] ? "1" : "0"))."</td>
							<td class='forumheader3' style='text-align:right'>".CONTENT_ADMIN_OPT_LAN_61."</td>
							<td class='forumheader3' style='text-align:center; width:5%;'>".$rs -> form_checkbox("content_admin_attach_{$id}", 1, ($content_pref["content_admin_attach_{$id}"] ? "1" : "0"))."</td>
							<td class='forumheader3' style='text-align:right'>".CONTENT_ADMIN_OPT_LAN_62."</td>
							<td class='forumheader3' style='text-align:center; width:5%;'>".$rs -> form_checkbox("content_admin_images_{$id}", 1, ($content_pref["content_admin_images_{$id}"] ? "1" : "0"))."</td>
						</tr>
						<tr>
							<td class='forumheader3' style='text-align:right'>".CONTENT_ADMIN_OPT_LAN_58."</td>
							<td class='forumheader3' style='text-align:center; width:5%;'>".$rs -> form_checkbox("content_admin_comment_{$id}", 1, ($content_pref["content_admin_comment_{$id}"] ? "1" : "0"))."</td>
							<td class='forumheader3' style='text-align:right'>".CONTENT_ADMIN_OPT_LAN_30."</td>
							<td class='forumheader3' style='text-align:center; width:5%;'>".$rs -> form_checkbox("content_admin_rating_{$id}", 1, ($content_pref["content_admin_rating_{$id}"] ? "1" : "0"))."</td>
							<td class='forumheader3' style='text-align:right'>".CONTENT_ADMIN_OPT_LAN_59."</td>
							<td class='forumheader3' style='text-align:center; width:5%;'>".$rs -> form_checkbox("content_admin_score_{$id}", 1, ($content_pref["content_admin_score_{$id}"] ? "1" : "0"))."</td>
						</tr>
						<tr>
							<td class='forumheader3' style='text-align:right'>".CONTENT_ADMIN_OPT_LAN_31."</td>
							<td class='forumheader3' style='text-align:center; width:5%;'>".$rs -> form_checkbox("content_admin_pe_{$id}", 1, ($content_pref["content_admin_pe_{$id}"] ? "1" : "0"))."</td>
							<td class='forumheader3' style='text-align:right'>".CONTENT_ADMIN_OPT_LAN_57."</td>
							<td class='forumheader3' style='text-align:center; width:5%;'>".$rs -> form_checkbox("content_admin_visibility_{$id}", 1, ($content_pref["content_admin_visibility_{$id}"] ? "1" : "0"))."</td>
							<td class='forumheader3' style='text-align:right'>".CONTENT_ADMIN_OPT_LAN_64."</td>
							<td class='forumheader3' style='text-align:center; width:5%;'>".$rs -> form_checkbox("content_admin_meta_{$id}", 1, ($content_pref["content_admin_meta_{$id}"] ? "1" : "0"))."</td>
						</tr>
						<tr>
							<td colspan='4' class='forumheader3'>".CONTENT_ADMIN_OPT_LAN_65."</td>
							<td colspan='2' class='forumheader3' style='text-align:center'>
								".$rs -> form_select_open("content_admin_custom_number_{$id}");
								for($i=0;$i<11;$i++){
									$text .= $rs -> form_option($i, ($content_pref["content_admin_custom_number_{$id}"] == $i ? "1" : "0"), $i);
								}
								$text .= $rs -> form_select_close()."
							</td>
						</tr>
						<tr>
							<td colspan='4' class='forumheader3' style='width:70%'>".CONTENT_ADMIN_OPT_LAN_2."</td>
							<td colspan='2' class='forumheader3' style='width:30%; text-align:center'>
								".$rs -> form_select_open("content_admin_images_number_{$id}");
								$content_pref["content_admin_images_number_{$id}"] = ($content_pref["content_admin_images_number_{$id}"] ? $content_pref["content_admin_images_number_{$id}"] : "10");
								for($i=1;$i<16;$i++){
									$k=$i*2;
									$text .= $rs -> form_option($k, ($content_pref["content_admin_images_number_{$id}"] == $k ? "1" : "0"), $k);
								}
								$text .= $rs -> form_select_close()."
							</td>
						</tr>
						<tr>
							<td colspan='4' class='forumheader3' style='width:70%'>".CONTENT_ADMIN_OPT_LAN_3."</td>
							<td colspan='2' class='forumheader3' style='width:30%; text-align:center'>
								".$rs -> form_select_open("content_admin_files_number_{$id}");
								$content_pref["content_admin_files_number_{$id}"] = ($content_pref["content_admin_files_number_{$id}"] ? $content_pref["content_admin_files_number_{$id}"] : "1");
								for($i=1;$i<6;$i++){
									$text .= $rs -> form_option($i, ($content_pref["content_admin_files_number_{$id}"] == $i ? "1" : "0"), $i);
								}
								$text .= $rs -> form_select_close()."
							</td>
						</tr>
						</table>
						</div>

						<div id='submission' style='display:none; text-align:center'>
						<table style='".ADMIN_WIDTH."' class='fborder'>
						<tr><td colspan='6' class='fcaption'>".CONTENT_ADMIN_OPT_LAN_5."</td></tr>
						<tr>
							<td colspan='4' class='forumheader3' style='width:70%'>".CONTENT_ADMIN_OPT_LAN_6."<br />".CONTENT_ADMIN_OPT_LAN_7."</td>
							<td colspan='2' class='forumheader3' style='width:30%; text-align:center'>".$rs -> form_checkbox("content_submit_{$id}", 1, ($content_pref["content_submit_{$id}"] ? "1" : "0"))."</td>
						</tr>

						<tr>
							<td colspan='4' class='forumheader3' style='width:70%'>".CONTENT_ADMIN_OPT_LAN_8."<br />".CONTENT_ADMIN_OPT_LAN_9."</td>
							<td colspan='2' class='forumheader3' style='width:30%; text-align:center'>".r_userclass("content_submit_class_{$id}", $content_pref["content_submit_class_{$id}"], "CLASSES")."</td>
						</tr>
						<tr>
							<td colspan='4' class='forumheader3' style='width:70%'>".CONTENT_ADMIN_OPT_LAN_63."</td>
							<td colspan='2' class='forumheader3' style='width:30%; text-align:center'>".$rs -> form_checkbox("content_submit_directpost_{$id}", 1, ($content_pref["content_submit_directpost_{$id}"] ? "1" : "0"))."</td>
						</tr>
						<tr><td colspan='6' class='forumheader3'>choose which sections should be available for a user to submit</td></tr>
						<tr>
							<td class='forumheader3' style='text-align:right'>".CONTENT_ADMIN_OPT_LAN_60."</td>
							<td class='forumheader3' style='text-align:center; width:5%;'>".$rs -> form_checkbox("content_submit_icon_{$id}", 1, ($content_pref["content_submit_icon_{$id}"] ? "1" : "0"))."</td>
							<td class='forumheader3' style='text-align:right'>".CONTENT_ADMIN_OPT_LAN_61."</td>
							<td class='forumheader3' style='text-align:center; width:5%;'>".$rs -> form_checkbox("content_submit_attach_{$id}", 1, ($content_pref["content_submit_attach_{$id}"] ? "1" : "0"))."</td>
							<td class='forumheader3' style='text-align:right'>".CONTENT_ADMIN_OPT_LAN_62."</td>
							<td class='forumheader3' style='text-align:center; width:5%;'>".$rs -> form_checkbox("content_submit_images_{$id}", 1, ($content_pref["content_submit_images_{$id}"] ? "1" : "0"))."</td>
						</tr>
						<tr>
							<td class='forumheader3' style='text-align:right'>".CONTENT_ADMIN_OPT_LAN_58."</td>
							<td class='forumheader3' style='text-align:center; width:5%;'>".$rs -> form_checkbox("content_submit_comment_{$id}", 1, ($content_pref["content_submit_comment_{$id}"] ? "1" : "0"))."</td>
							<td class='forumheader3' style='text-align:right'>".CONTENT_ADMIN_OPT_LAN_30."</td>
							<td class='forumheader3' style='text-align:center; width:5%;'>".$rs -> form_checkbox("content_submit_rating_{$id}", 1, ($content_pref["content_submit_rating_{$id}"] ? "1" : "0"))."</td>
							<td class='forumheader3' style='text-align:right'>".CONTENT_ADMIN_OPT_LAN_59."</td>
							<td class='forumheader3' style='text-align:center; width:5%;'>".$rs -> form_checkbox("content_submit_score_{$id}", 1, ($content_pref["content_submit_score_{$id}"] ? "1" : "0"))."</td>
						</tr>
						<tr>
							<td class='forumheader3' style='text-align:right'>".CONTENT_ADMIN_OPT_LAN_31."</td>
							<td class='forumheader3' style='text-align:center; width:5%;'>".$rs -> form_checkbox("content_submit_pe_{$id}", 1, ($content_pref["content_submit_pe_{$id}"] ? "1" : "0"))."</td>
							<td class='forumheader3' style='text-align:right'>".CONTENT_ADMIN_OPT_LAN_57."</td>
							<td class='forumheader3' style='text-align:center; width:5%;'>".$rs -> form_checkbox("content_submit_visibility_{$id}", 1, ($content_pref["content_submit_visibility_{$id}"] ? "1" : "0"))."</td>
							<td class='forumheader3' style='text-align:right'>".CONTENT_ADMIN_OPT_LAN_64."</td>
							<td class='forumheader3' style='text-align:center; width:5%;'>".$rs -> form_checkbox("content_submit_meta_{$id}", 1, ($content_pref["content_submit_meta_{$id}"] ? "1" : "0"))."</td>
						</tr>
						<tr>
							<td colspan='4' class='forumheader3'>".CONTENT_ADMIN_OPT_LAN_65."</td>
							<td colspan='2' class='forumheader3' style='text-align:center'>
								".$rs -> form_select_open("content_submit_custom_number_{$id}");
								for($i=0;$i<11;$i++){
									$text .= $rs -> form_option($i, ($content_pref["content_submit_custom_number_{$id}"] == $i ? "1" : "0"), $i);
								}
								$text .= $rs -> form_select_close()."
							</td>
						</tr>
						<tr>
							<td colspan='4' class='forumheader3' style='width:70%'>".CONTENT_ADMIN_OPT_LAN_2."</td>
							<td colspan='2' class='forumheader3' style='width:30%; text-align:center'>
								".$rs -> form_select_open("content_submit_images_number_{$id}");
								$content_pref["content_submit_images_number_{$id}"] = ($content_pref["content_submit_images_number_{$id}"] ? $content_pref["content_submit_images_number_{$id}"] : "10");
								for($i=1;$i<16;$i++){
									$k=$i*2;
									$text .= $rs -> form_option($k, ($content_pref["content_submit_images_number_{$id}"] == $k ? "1" : "0"), $k);
								}
								$text .= $rs -> form_select_close()."
							</td>
						</tr>
						<tr>
							<td colspan='4' class='forumheader3' style='width:70%'>".CONTENT_ADMIN_OPT_LAN_3."</td>
							<td colspan='2' class='forumheader3' style='width:30%; text-align:center'>
								".$rs -> form_select_open("content_submit_files_number_{$id}");
								$content_pref["content_submit_files_number_{$id}"] = ($content_pref["content_submit_files_number_{$id}"] ? $content_pref["content_submit_files_number_{$id}"] : "1");
								for($i=1;$i<6;$i++){
									$text .= $rs -> form_option($i, ($content_pref["content_submit_files_number_{$id}"] == $i ? "1" : "0"), $i);
								}
								$text .= $rs -> form_select_close()."
							</td>
						</tr>
						</table>
						</div>

						<div id='paththeme' style='display:none; text-align:center'>
						<table style='".ADMIN_WIDTH."' class='fborder'>
						<tr><td colspan='6' class='fcaption'>".CONTENT_ADMIN_OPT_LAN_10."</td></tr>
						<tr>
							<td colspan='2' class='forumheader3' style='width:70%'>".CONTENT_ADMIN_OPT_LAN_11." (".CONTENT_ADMIN_OPT_LAN_55.")</td>
							<td colspan='4' class='forumheader3' style='width:30%; text-align:center'>".$rs -> form_text("content_cat_icon_path_large_{$id}", 60, $content_pref["content_cat_icon_path_large_{$id}"], 100)."</td>
						</tr>
						<tr>
							<td colspan='2' class='forumheader3' style='width:70%'>".CONTENT_ADMIN_OPT_LAN_11." (".CONTENT_ADMIN_OPT_LAN_56.")</td>
							<td colspan='4' class='forumheader3' style='width:30%; text-align:center'>".$rs -> form_text("content_cat_icon_path_small_{$id}", 60, $content_pref["content_cat_icon_path_small_{$id}"], 100)."</td>
						</tr>
						<tr>
							<td colspan='2' class='forumheader3' style='width:70%'>".CONTENT_ADMIN_OPT_LAN_12."</td>
							<td colspan='4' class='forumheader3' style='width:30%; text-align:center'>".$rs -> form_text("content_icon_path_{$id}", 60, $content_pref["content_icon_path_{$id}"], 100)."</td>
						</tr>
						<tr>
							<td colspan='2' class='forumheader3' style='width:70%'>".CONTENT_ADMIN_OPT_LAN_13."</td>
							<td colspan='4' class='forumheader3' style='width:30%; text-align:center'>".$rs -> form_text("content_image_path_{$id}", 60, $content_pref["content_image_path_{$id}"], 100)."</td>
						</tr>
						<tr>
							<td colspan='2' class='forumheader3' style='width:70%'>".CONTENT_ADMIN_OPT_LAN_14."</td>
							<td colspan='4' class='forumheader3' style='width:30%; text-align:center'>".$rs -> form_text("content_file_path_{$id}", 60, $content_pref["content_file_path_{$id}"], 100)."</td>
						</tr>";

						$handle=opendir(e_PLUGIN."content/templates/");
						while ($file = readdir($handle)){
							if($file != "." && $file != ".." && $file != "templates" && $file != "/"){
								$dirlist[] = $file;
							}
						}
						closedir($handle);

						$text .= "
						<tr>
							<td colspan='2' class='forumheader3' style='width:70%'>".CONTENT_ADMIN_OPT_LAN_66."</td>
							<td colspan='4' class='forumheader3' style='width:30%; text-align:center'>
								".$rs -> form_select_open("content_theme_{$id}");
								$counter = 0;
								while(isset($dirlist[$counter])){
									$text .= $rs -> form_option($dirlist[$counter], ($dirlist[$counter] == $content_pref["content_theme_{$id}"] ? "1" : "0"), $dirlist[$counter]);
									$counter++;
								}
								$text .= $rs -> form_select_close()."
							</td>
						</tr>
						</table>
						</div>

						<div id='general' style='display:none; text-align:center'>
						<table style='".ADMIN_WIDTH."' class='fborder'>
						<tr><td colspan='6' class='fcaption'>".CONTENT_ADMIN_OPT_LAN_15."</td></tr>
						<tr>
							<td colspan='4' class='forumheader3' style='width:70%'>".CONTENT_ADMIN_OPT_LAN_16."</td>
							<td colspan='2' class='forumheader3' style='width:30%; text-align:center'>".$rs -> form_checkbox("content_log_{$id}", 1, ($content_pref["content_log_{$id}"] ? "1" : "0"))."</td>
						</tr>
						<tr>
							<td colspan='4' class='forumheader3' style='width:70%'>".CONTENT_ADMIN_OPT_LAN_17."</td>
							<td colspan='2' class='forumheader3' style='width:30%; text-align:center'>".$rs -> form_checkbox("content_blank_icon_{$id}", 1, ($content_pref["content_blank_icon_{$id}"] ? "1" : "0"))."</td>
						</tr>
						<tr>
							<td colspan='4' class='forumheader3' style='width:70%'>".CONTENT_ADMIN_OPT_LAN_54."</td>
							<td colspan='2' class='forumheader3' style='width:30%; text-align:center'>".$rs -> form_checkbox("content_blank_caticon_{$id}", 1, ($content_pref["content_blank_caticon_{$id}"] ? "1" : "0"))."</td>
						</tr>
						<tr>
							<td colspan='4' class='forumheader3' style='width:70%'>".CONTENT_ADMIN_OPT_LAN_18."</td>
							<td colspan='2' class='forumheader3' style='width:30%; text-align:center'>".$rs -> form_checkbox("content_breadcrumb_{$id}", 1, ($content_pref["content_breadcrumb_{$id}"] ? "1" : "0"))."</td>
						</tr>
						<tr>
							<td colspan='4' class='forumheader3' style='width:70%'>".CONTENT_ADMIN_OPT_LAN_83."</td>
							<td colspan='2' class='forumheader3' style='width:30%; text-align:center'>".$rs -> form_text("content_breadcrumb_seperator{$id}", 1, $content_pref["content_breadcrumb_seperator{$id}"], 3)."</td>
						</tr>
						<tr>
							<td colspan='4' class='forumheader3'>".CONTENT_ADMIN_OPT_LAN_19."</td>
							<td colspan='2' class='forumheader3' style='text-align:center'>
								".$rs -> form_select_open("content_breadcrumb_rendertype_{$id}")."
								".$rs -> form_option(CONTENT_ADMIN_OPT_LAN_20, ($content_pref["content_breadcrumb_rendertype_{$id}"] == "1" ? "1" : "0"), "1")."
								".$rs -> form_option(CONTENT_ADMIN_OPT_LAN_21, ($content_pref["content_breadcrumb_rendertype_{$id}"] == "2" ? "1" : "0"), "2")."
								".$rs -> form_option(CONTENT_ADMIN_OPT_LAN_22, ($content_pref["content_breadcrumb_rendertype_{$id}"] == "3" ? "1" : "0"), "3")."
								".$rs -> form_select_close()."
							</td>
						</tr>
						<tr>
							<td colspan='4' class='forumheader3' style='width:70%'>".CONTENT_ADMIN_OPT_LAN_23."</td>
							<td colspan='2' class='forumheader3' style='width:30%; text-align:center'>".$rs -> form_checkbox("content_searchmenu_{$id}", 1, ($content_pref["content_searchmenu_{$id}"] ? "1" : "0"))."</td>
						</tr>
						</table>
						</div>

						<div id='listpages' style='display:none; text-align:center'>
						<table style='".ADMIN_WIDTH."' class='fborder'>
						<tr><td colspan='6' class='fcaption'>".CONTENT_ADMIN_OPT_LAN_24."</td></tr>
						<tr>
							<td class='forumheader3' style='text-align:right'>".CONTENT_ADMIN_OPT_LAN_25."</td>
							<td class='forumheader3' style='text-align:center; width:5%;'>".$rs -> form_checkbox("content_list_subheading_{$id}", 1, ($content_pref["content_list_subheading_{$id}"] ? "1" : "0"))."</td>
							<td class='forumheader3' style='text-align:right'>".CONTENT_ADMIN_OPT_LAN_26."</td>
							<td class='forumheader3' style='text-align:center; width:5%;'>".$rs -> form_checkbox("content_list_summary_{$id}", 1, ($content_pref["content_list_summary_{$id}"] ? "1" : "0"))."</td>
							<td class='forumheader3' style='text-align:right'>".CONTENT_ADMIN_OPT_LAN_27."</td>
							<td class='forumheader3' style='text-align:center; width:5%;'>".$rs -> form_checkbox("content_list_date_{$id}", 1, ($content_pref["content_list_date_{$id}"] ? "1" : "0"))."</td>
						</tr>
						<tr>
							<td class='forumheader3' style='text-align:right'>".CONTENT_ADMIN_OPT_LAN_28."</td>
							<td class='forumheader3' style='text-align:center; width:5%;'>".$rs -> form_checkbox("content_list_authorname_{$id}", 1, ($content_pref["content_list_authorname_{$id}"] ? "1" : "0"))."</td>
							<td class='forumheader3' style='text-align:right'>".CONTENT_ADMIN_OPT_LAN_29."</td>
							<td class='forumheader3' style='text-align:center; width:5%;'>".$rs -> form_checkbox("content_list_authoremail_{$id}", 1, ($content_pref["content_list_authoremail_{$id}"] ? "1" : "0"))."</td>
							<td class='forumheader3' style='text-align:right'>".CONTENT_ADMIN_OPT_LAN_30."</td>
							<td class='forumheader3' style='text-align:center; width:5%;'>".$rs -> form_checkbox("content_list_rating_{$id}", 1, ($content_pref["content_list_rating_{$id}"] ? "1" : "0"))."</td>
						</tr>
						<tr>
							<td class='forumheader3' style='text-align:right'>".CONTENT_ADMIN_OPT_LAN_31."</td>
							<td class='forumheader3' style='text-align:center; width:5%;'>".$rs -> form_checkbox("content_list_peicon_{$id}", 1, ($content_pref["content_list_peicon_{$id}"] ? "1" : "0"))."</td>
							<td class='forumheader3' style='text-align:right'>".CONTENT_ADMIN_OPT_LAN_32."</td>
							<td class='forumheader3' style='text-align:center; width:5%;'>".$rs -> form_checkbox("content_list_parent_{$id}", 1, ($content_pref["content_list_parent_{$id}"] ? "1" : "0"))."</td>
							<td class='forumheader3' style='text-align:right'>".CONTENT_ADMIN_OPT_LAN_33."</td>
							<td class='forumheader3' style='text-align:center; width:5%;'>".$rs -> form_checkbox("content_list_refer_{$id}", 1, ($content_pref["content_list_refer_{$id}"] ? "1" : "0"))."</td>
						</tr>
						<tr>
							<td colspan='4' class='forumheader3'>".CONTENT_ADMIN_OPT_LAN_34."</td>
							<td colspan='2' class='forumheader3' style='text-align:center'>".$rs -> form_text("content_list_subheading_char_{$id}", 1, $content_pref["content_list_subheading_char_{$id}"], 3)."</td>
						</tr>
						<tr>
							<td colspan='4' class='forumheader3'>".CONTENT_ADMIN_OPT_LAN_35."</td>
							<td colspan='2' class='forumheader3' style='text-align:center'>".$rs -> form_text("content_list_subheading_post_{$id}", 10, $content_pref["content_list_subheading_post_{$id}"], 30)."</td>
						</tr>
						<tr>
							<td colspan='4' class='forumheader3'>".CONTENT_ADMIN_OPT_LAN_36."</td>
							<td colspan='2' class='forumheader3' style='text-align:center'>".$rs -> form_text("content_list_summary_char_{$id}", 1, $content_pref["content_list_summary_char_{$id}"], 3)."</td>
						</tr>
						<tr>
							<td colspan='4' class='forumheader3'>".CONTENT_ADMIN_OPT_LAN_37."</td>
							<td colspan='2' class='forumheader3' style='text-align:center'>".$rs -> form_text("content_list_summary_post_{$id}", 10, $content_pref["content_list_summary_post_{$id}"], 30)."</td>
						</tr>
						<tr>
							<td colspan='4' class='forumheader3'>".CONTENT_ADMIN_OPT_LAN_38."</td>
							<td colspan='2' class='forumheader3' style='text-align:center'>".$rs -> form_checkbox("content_list_authoremail_nonmember_{$id}", 1, ($content_pref["content_list_authoremail_nonmember_{$id}"] ? "1" : "0"))."</td>
						</tr>
						<tr>
							<td colspan='4' class='forumheader3'>".CONTENT_ADMIN_OPT_LAN_39."</td>
							<td colspan='2' class='forumheader3' style='text-align:center'>".$rs -> form_checkbox("content_nextprev_{$id}", 1, ($content_pref["content_nextprev_{$id}"] ? "1" : "0"))."</td>
						</tr>
						<tr>
							<td colspan='4' class='forumheader3'>".CONTENT_ADMIN_OPT_LAN_40."</td>
							<td colspan='2' class='forumheader3' style='text-align:center'>
								".$rs -> form_select_open("content_nextprev_number_{$id}");
								for($i=1;$i<21;$i++){
									$text .= $rs -> form_option($i, ($content_pref["content_nextprev_number_{$id}"] == $i ? "1" : "0"), $i);
								}
								$text .= $rs -> form_select_close()."
							</td>
						</tr>
						<tr>
							<td colspan='4' class='forumheader3' style='width:70%'>".CONTENT_ADMIN_OPT_LAN_41."</td>
							<td colspan='2' class='forumheader3' style='width:30%; text-align:center'>".$rs -> form_checkbox("content_list_peicon_all_{$id}", 1, ($content_pref["content_list_peicon_all_{$id}"] ? "1" : "0"))."</td>
						</tr>
						<tr>
							<td colspan='4' class='forumheader3' style='width:70%'>".CONTENT_ADMIN_OPT_LAN_42."</td>
							<td colspan='2' class='forumheader3' style='width:30%; text-align:center'>".$rs -> form_checkbox("content_list_rating_all_{$id}", 1, ($content_pref["content_list_rating_all_{$id}"] ? "1" : "0"))."</td>
						</tr>
						<tr>
							<td colspan='4' class='forumheader3'>default order</td>
							<td colspan='2' class='forumheader3' style='text-align:center'>
								".$rs -> form_select_open("content_defaultorder_{$id}")."
								".$rs -> form_option("heading_ASC", ($content_pref["content_defaultorder_{$id}"] == "orderaheading" ? "1" : "0"), "orderaheading")."
								".$rs -> form_option("heading_DESC", ($content_pref["content_defaultorder_{$id}"] == "orderdheading" ? "1" : "0"), "orderdheading")."
								".$rs -> form_option("date_ASC", ($content_pref["content_defaultorder_{$id}"] == "orderadate" ? "1" : "0"), "orderadate")."
								".$rs -> form_option("date_DESC", ($content_pref["content_defaultorder_{$id}"] == "orderddate" ? "1" : "0"), "orderddate")."
								".$rs -> form_option("refer_ASC", ($content_pref["content_defaultorder_{$id}"] == "orderarefer" ? "1" : "0"), "orderarefer")."
								".$rs -> form_option("refer_DESC", ($content_pref["content_defaultorder_{$id}"] == "orderdrefer" ? "1" : "0"), "orderdrefer")."
								".$rs -> form_option("parent_ASC", ($content_pref["content_defaultorder_{$id}"] == "orderaparent" ? "1" : "0"), "orderaparent")."
								".$rs -> form_option("parent_DESC", ($content_pref["content_defaultorder_{$id}"] == "orderdparent" ? "1" : "0"), "orderdparent")."
								".$rs -> form_select_close()."
							</td>
						</tr>

						</table>
						</div>

						<div id='catpages' style='display:none; text-align:center'>
						<table style='".ADMIN_WIDTH."' class='fborder'>
						<tr><td colspan='6' class='fcaption'>".CONTENT_ADMIN_OPT_LAN_43."</td></tr>
						<tr>
							<td colspan='4' class='forumheader3'>".CONTENT_ADMIN_OPT_LAN_44."</td>
							<td colspan='2' class='forumheader3' style='text-align:center'>".$rs -> form_checkbox("content_cat_showparent_{$id}", 1, ($content_pref["content_cat_showparent_{$id}"] ? "1" : "0"))."</td>
						</tr>
						<tr>
							<td colspan='4' class='forumheader3'>".CONTENT_ADMIN_OPT_LAN_45."</td>
							<td colspan='2' class='forumheader3' style='text-align:center'>".$rs -> form_checkbox("content_cat_showparentsub_{$id}", 1, ($content_pref["content_cat_showparentsub_{$id}"] ? "1" : "0"))."</td>
						</tr>
						<tr>
							<td colspan='4' class='forumheader3'>".CONTENT_ADMIN_OPT_LAN_46."</td>
							<td colspan='2' class='forumheader3' style='text-align:center'>".$rs -> form_checkbox("content_cat_listtype_{$id}", 1, ($content_pref["content_cat_listtype_{$id}"] ? "1" : "0"))."</td>
						</tr>
						<tr>
							<td colspan='4' class='forumheader3'>".CONTENT_ADMIN_OPT_LAN_47."</td>
							<td colspan='2' class='forumheader3' style='text-align:center'>
								".$rs -> form_select_open("content_cat_menuorder_{$id}")."
								".$rs -> form_option(CONTENT_ADMIN_OPT_LAN_48, ($content_pref["content_cat_menuorder_{$id}"] == "1" ? "1" : "0"), "1")."
								".$rs -> form_option(CONTENT_ADMIN_OPT_LAN_49, ($content_pref["content_cat_menuorder_{$id}"] == "2" ? "1" : "0"), "2")."
								".$rs -> form_select_close()."
							</td>
						</tr>
						<tr>
							<td colspan='4' class='forumheader3'>".CONTENT_ADMIN_OPT_LAN_78."</td>
							<td colspan='2' class='forumheader3' style='text-align:center'>
								".$rs -> form_select_open("content_cat_rendertype_{$id}")."
								".$rs -> form_option(CONTENT_ADMIN_OPT_LAN_79, ($content_pref["content_cat_rendertype_{$id}"] == "1" ? "1" : "0"), "1")."
								".$rs -> form_option(CONTENT_ADMIN_OPT_LAN_80, ($content_pref["content_cat_rendertype_{$id}"] == "2" ? "1" : "0"), "2")."
								".$rs -> form_select_close()."
							</td>
						</tr>
						</table>
						</div>

						<div id='contentpages' style='display:none; text-align:center'>
						<table style='".ADMIN_WIDTH."' class='fborder'>
						<tr><td colspan='6' class='fcaption'>".CONTENT_ADMIN_OPT_LAN_50."</td></tr>
						<tr>
							<td class='forumheader3' style='text-align:right'>".CONTENT_ADMIN_OPT_LAN_25."</td>
							<td class='forumheader3' style='text-align:center; width:5%;'>".$rs -> form_checkbox("content_content_subheading_{$id}", 1, ($content_pref["content_content_subheading_{$id}"] ? "1" : "0"))."</td>
							<td class='forumheader3' style='text-align:right'>".CONTENT_ADMIN_OPT_LAN_26."</td>
							<td class='forumheader3' style='text-align:center; width:5%;'>".$rs -> form_checkbox("content_content_summary_{$id}", 1, ($content_pref["content_content_summary_{$id}"] ? "1" : "0"))."</td>
							<td class='forumheader3' style='text-align:right'>".CONTENT_ADMIN_OPT_LAN_27."</td>
							<td class='forumheader3' style='text-align:center; width:5%;'>".$rs -> form_checkbox("content_content_date_{$id}", 1, ($content_pref["content_content_date_{$id}"] ? "1" : "0"))."</td>
						</tr>
						<tr>
							<td class='forumheader3' style='text-align:right'>".CONTENT_ADMIN_OPT_LAN_28."</td>
							<td class='forumheader3' style='text-align:center; width:5%;'>".$rs -> form_checkbox("content_content_authorname_{$id}", 1, ($content_pref["content_content_authorname_{$id}"] ? "1" : "0"))."</td>
							<td class='forumheader3' style='text-align:right'>".CONTENT_ADMIN_OPT_LAN_29."</td>
							<td class='forumheader3' style='text-align:center; width:5%;'>".$rs -> form_checkbox("content_content_authoremail_{$id}", 1, ($content_pref["content_content_authoremail_{$id}"] ? "1" : "0"))."</td>
							<td class='forumheader3' style='text-align:right'>".CONTENT_ADMIN_OPT_LAN_30."</td>
							<td class='forumheader3' style='text-align:center; width:5%;'>".$rs -> form_checkbox("content_content_rating_{$id}", 1, ($content_pref["content_content_rating_{$id}"] ? "1" : "0"))."</td>
						</tr>
						<tr>
							<td class='forumheader3' style='text-align:right'>".CONTENT_ADMIN_OPT_LAN_31."</td>
							<td class='forumheader3' style='text-align:center; width:5%;'>".$rs -> form_checkbox("content_content_peicon_{$id}", 1, ($content_pref["content_content_peicon_{$id}"] ? "1" : "0"))."</td>
							<td class='forumheader3' style='text-align:right'>".CONTENT_ADMIN_OPT_LAN_33."</td>
							<td class='forumheader3' style='text-align:center; width:5%;'>".$rs -> form_checkbox("content_content_refer_{$id}", 1, ($content_pref["content_content_refer_{$id}"] ? "1" : "0"))."</td>
							<td class='forumheader3' style='text-align:right'>&nbsp;</td>
							<td class='forumheader3' style='text-align:center; width:5%;'>&nbsp;</td>
						</tr>
						<tr>
							<td colspan='4' class='forumheader3'>".CONTENT_ADMIN_OPT_LAN_38."</td>
							<td colspan='2' class='forumheader3' style='text-align:center'>".$rs -> form_checkbox("content_content_authoremail_nonmember_{$id}", 1, ($content_pref["content_content_authoremail_nonmember_{$id}"] ? "1" : "0"))."</td>
						</tr>
						<tr>
							<td colspan='4' class='forumheader3' style='width:70%'>".CONTENT_ADMIN_OPT_LAN_41."</td>
							<td colspan='2' class='forumheader3' style='width:30%; text-align:center'>".$rs -> form_checkbox("content_content_peicon_all_{$id}", 1, ($content_pref["content_content_peicon_all_{$id}"] ? "1" : "0"))."</td>
						</tr>
						<tr>
							<td colspan='4' class='forumheader3' style='width:70%'>".CONTENT_ADMIN_OPT_LAN_42."</td>
							<td colspan='2' class='forumheader3' style='width:30%; text-align:center'>".$rs -> form_checkbox("content_content_rating_all_{$id}", 1, ($content_pref["content_content_rating_all_{$id}"] ? "1" : "0"))."</td>
						</tr>
						</table>
						</div>

						<div id='menu' style='display:none; text-align:center'>
						<table style='".ADMIN_WIDTH."' class='fborder'>
						<tr><td colspan='6' class='fcaption'>".CONTENT_ADMIN_OPT_LAN_67."</td></tr>
						<tr>
							<td colspan='4' class='forumheader3'>".CONTENT_ADMIN_OPT_LAN_68."</td>
							<td colspan='2' class='forumheader3' style='text-align:center'>".$rs -> form_text("content_menu_caption_{$id}", 15, $content_pref["content_menu_caption_{$id}"], 50)."</td>
						</tr>
						<tr>
							<td colspan='4' class='forumheader3' style='width:70%'>".CONTENT_ADMIN_OPT_LAN_69."</td>
							<td colspan='2' class='forumheader3' style='width:30%; text-align:center'>".$rs -> form_checkbox("content_menu_cat_{$id}", 1, ($content_pref["content_menu_cat_{$id}"] ? "1" : "0"))."</td>
						</tr>
						<tr>
							<td colspan='4' class='forumheader3' style='width:70%'>".CONTENT_ADMIN_OPT_LAN_70."</td>
							<td colspan='2' class='forumheader3' style='width:30%; text-align:center'>".$rs -> form_checkbox("content_menu_cat_number_{$id}", 1, ($content_pref["content_menu_cat_number_{$id}"] ? "1" : "0"))."</td>
						</tr>
						<tr>
							<td colspan='4' class='forumheader3' style='width:70%'>".CONTENT_ADMIN_OPT_LAN_71."</td>
							<td colspan='2' class='forumheader3' style='width:30%; text-align:center'>".$rs -> form_checkbox("content_menu_recent_{$id}", 1, ($content_pref["content_menu_recent_{$id}"] ? "1" : "0"))."</td>
						</tr>
						<tr>
							<td colspan='4' class='forumheader3'>".CONTENT_ADMIN_OPT_LAN_72."</td>
							<td colspan='2' class='forumheader3' style='text-align:center'>".$rs -> form_text("content_menu_recent_caption_{$id}", 15, $content_pref["content_menu_recent_caption_{$id}"], 50)."</td>
						</tr>
						<tr>
							<td colspan='4' class='forumheader3' style='width:70%'>".CONTENT_ADMIN_OPT_LAN_73."</td>
							<td colspan='2' class='forumheader3' style='width:30%; text-align:center'>".$rs -> form_checkbox("content_menu_search_{$id}", 1, ($content_pref["content_menu_search_{$id}"] ? "1" : "0"))."</td>
						</tr>
						<tr>
							<td colspan='4' class='forumheader3' style='width:70%'>".CONTENT_ADMIN_OPT_LAN_77."</td>
							<td colspan='2' class='forumheader3' style='width:30%; text-align:center'>".$rs -> form_checkbox("content_menu_sort_{$id}", 1, ($content_pref["content_menu_sort_{$id}"] ? "1" : "0"))."</td>
						</tr>
						<tr>
							<td colspan='4' class='forumheader3' style='width:70%'>".CONTENT_ADMIN_OPT_LAN_74."</td>
							<td colspan='2' class='forumheader3' style='width:30%; text-align:center'>".$rs -> form_checkbox("content_menu_viewallcat_{$id}", 1, ($content_pref["content_menu_viewallcat_{$id}"] ? "1" : "0"))."</td>
						</tr>
						<tr>
							<td colspan='4' class='forumheader3' style='width:70%'>".CONTENT_ADMIN_OPT_LAN_75."</td>
							<td colspan='2' class='forumheader3' style='width:30%; text-align:center'>".$rs -> form_checkbox("content_menu_viewallauthor_{$id}", 1, ($content_pref["content_menu_viewallauthor_{$id}"] ? "1" : "0"))."</td>
						</tr>
						<tr>
							<td colspan='4' class='forumheader3' style='width:70%'>".CONTENT_ADMIN_OPT_LAN_76."</td>
							<td colspan='2' class='forumheader3' style='width:30%; text-align:center'>".$rs -> form_checkbox("content_menu_viewtoprated_{$id}", 1, ($content_pref["content_menu_viewtoprated_{$id}"] ? "1" : "0"))."</td>
						</tr>
						<tr>
							<td colspan='4' class='forumheader3' style='width:70%'>".CONTENT_ADMIN_OPT_LAN_76."</td>
							<td colspan='2' class='forumheader3' style='width:30%; text-align:center'>".$rs -> form_checkbox("content_menu_viewrecent_{$id}", 1, ($content_pref["content_menu_viewrecent_{$id}"] ? "1" : "0"))."</td>
						</tr>
						</table>
						</div>";

						$text .= "
						<br /><table style='".ADMIN_WIDTH."' class='fborder'>
						<tr style='vertical-align:top'>
							<td class='forumheader' colspan='6' style='text-align:center'>
								<input class='button' type='submit' name='updateoptions' value='".CONTENT_ADMIN_OPT_LAN_51."' /> <input type='hidden' name='options_type' value='".$id."' />
							</td>
						</tr>
						</table>
						</form>
						</div>";

						$ns -> tablerender($caption, $text);
		}

}

?>