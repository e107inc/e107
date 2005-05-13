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
|		$Revision: 1.33 $
|		$Date: 2005-05-13 11:16:40 $
|		$Author: lisa_ $
+---------------------------------------------------------------+
*/

//$plugintable = "pcontent";		//name of the table used in this plugin

if (!defined('ADMIN_WIDTH')) { define("ADMIN_WIDTH", "width:98%;"); }

$stylespacer = "style='border:0; height:20px;'";
//$stylehelp = "style='border:0; font-style:italic; color:#0087E5;'";
$stylehelp = "class='smalltext'";
$td1 = "style='width:20%; white-space:nowrap; vertical-align:top;'";

//only used in admin pages, for normal rows (+ in content_submit.php creation form)
$TOPIC_ROW_NOEXPAND = "
<tr>
	<td class='forumheader3' $td1>{TOPIC_TOPIC}</td>
	<td class='forumheader3'>{TOPIC_FIELD}</td>
</tr>
";
//only used in admin pages, for expanding rows (+ in content_submit.php creation form)
$TOPIC_ROW = "
<tr>
	<td class='forumheader3' $td1>{TOPIC_TOPIC}</td>
	<td class='forumheader3' style='vertical-align:top;'>
		<a style='cursor: pointer; cursor: hand' onclick='expandit(this);'>{TOPIC_HEADING}</a>
		<div style='display: none;'>
			<div $stylehelp>{TOPIC_HELP}</div><br />
			{TOPIC_FIELD}
		</div>
	</td>
</tr>";


//only used in admin pages, for a spacer row
$TOPIC_ROW_SPACER = "<tr><td $stylespacer colspan='2'></td></tr>";

class contentform{

		function show_content_create($mode, $userid="", $username=""){
						global $sql, $ns, $rs, $aa, $tp, $plugintable, $pref;
						global $type, $type_id, $action, $sub_action, $id;
						global $message, $stylespacer, $td1, $TOPIC_ROW_SPACER, $TOPIC_ROW, $TOPIC_ROW_NOEXPAND;

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
							if($mode == "admin"){
								require_once(e_ADMIN."footer.php");
							}else{
								require_once(FOOTERF);
							}
							exit;
						}

						if(!$sub_action){
							$authordetails = $aa -> getAuthor(USERID);
						}
						if($sub_action == "edit" && !$_POST['preview'] && !isset($message)){
							if(is_numeric($id)){
								if($sql -> db_Select($plugintable, "content_id, content_heading, content_subheading, content_summary, content_text, content_author, content_icon, content_file, content_image, content_parent, content_comment, content_rate, content_pe, content_refer, content_datestamp, content_enddate, content_class, content_pref as contentprefvalue", "content_id='$id' ")){
									$row = $sql -> db_Fetch();
									//$row['content_text'] = $tp -> toForm($row['content_text'], TRUE);
									$row['content_text'] = $tp -> post_toHTML($row['content_text'], TRUE);
									$authordetails = $aa -> getAuthor($row['content_author']);
								}
							}else{
								header("location:".e_SELF."?create"); exit;
							}
						}

						if($sub_action == "sa" && is_numeric($id) && !$_POST['preview'] && !isset($message)){
							if(is_numeric($id)){
								if($sql -> db_Select($plugintable, "content_id, content_heading, content_subheading, content_summary, content_text, content_author, content_icon, content_file, content_image, content_parent, content_comment, content_rate, content_pe, content_refer, content_datestamp, content_enddate, content_class, content_pref as contentprefvalue", "content_id=$id")){
									$row = $sql -> db_Fetch();
									//$row['content_text'] = $tp -> toForm($row['content_text'], TRUE);
									$row['content_text'] = $tp -> post_toHTML($row['content_text'], TRUE);
									$authordetails = $aa -> getAuthor($row['content_author']);
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

						$authordetails[0] = ($content_author_id ? $content_author_id : ($authordetails[0] ? $authordetails[0] : USERID) );
						$authordetails[1] = ($content_author_name ? $content_author_name : ($authordetails[1] ? $authordetails[1] : USERNAME) );
						$authordetails[2] = ($content_author_email ? $content_author_email : ($authordetails[2] ? $authordetails[2] : USEREMAIL) );

						$text = "
						<div style='text-align:center;'>
						".$rs -> form_open("post", e_SELF."?".e_QUERY."", "dataform", "", "enctype='multipart/form-data'")."
						<table style='".ADMIN_WIDTH."' class='fborder'>";

						if($mode == "contentmanager"){
							if($sub_action == "edit"){
								$text .= $rs -> form_hidden("parent", $row['content_parent']);
							}else{
								$cat = str_replace("-", ".", $sub_action);
								$text .= $rs -> form_hidden("parent", $cat);
							}
						}else{
							//category parent
							$TOPIC_TOPIC = CONTENT_ADMIN_CAT_LAN_27;
							$TOPIC_FIELD = "
								".$rs -> form_select_open("parent")."
								".$rs -> form_option("** ".CONTENT_ADMIN_ITEM_LAN_13." **", 0, "none")."
								".$aa -> printParent($parentdetails, "0", $row['content_parent'], "optioncontent")."
								".$rs -> form_select_close()."
							";
							$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW_NOEXPAND);
						}

						//heading
						$TOPIC_TOPIC = CONTENT_ADMIN_ITEM_LAN_11;
						$TOPIC_FIELD = $rs -> form_text("content_heading", 80, $row['content_heading'], 250);
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW_NOEXPAND);

						//subheading
						$TOPIC_TOPIC = CONTENT_ADMIN_ITEM_LAN_16;
						$TOPIC_FIELD = $rs -> form_text("content_subheading", 80, $row['content_subheading'], 250);
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW_NOEXPAND);
						
						//summary
						$TOPIC_TOPIC = CONTENT_ADMIN_ITEM_LAN_17;
						$TOPIC_FIELD = $rs -> form_textarea("content_summary", 77, 5, $row['content_summary']);
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW_NOEXPAND);

						if($mode == "submit"){
							if($pref['wysiwyg'])
							{
								require_once(e_HANDLER."tiny_mce/wysiwyg.php");
								echo wysiwyg("content_text");
							}
						}
						//text
						$insertjs = " onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);' ";
						require_once(e_HANDLER."ren_help.php");
						$TOPIC_TOPIC = CONTENT_ADMIN_ITEM_LAN_18;
						//".$rs -> form_textarea("content_text", 77, 20, $row['content_text'], ($pref['wysiwyg'] ? $insertjs : ""))."<br />
						$TOPIC_FIELD = "
							".$rs -> form_textarea("content_text", 80, 20, $row['content_text'], $insertjs)."<br />
							".$rs -> form_text("helpb", 90, '', '', "helpbox")."<br />
							".display_help()."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW_NOEXPAND);

						$text .= $TOPIC_ROW_SPACER;

						//author
						$TOPIC_TOPIC = CONTENT_ADMIN_ITEM_LAN_51;
						$TOPIC_HEADING = CONTENT_ADMIN_ITEM_LAN_72." (".CONTENT_ADMIN_ITEM_LAN_71.")";
						$TOPIC_HELP = "";
						$TOPIC_FIELD = "
							<table style='width:100%; text-align:left;'>
							<tr><td>".CONTENT_ADMIN_ITEM_LAN_14."</td><td>".$rs -> form_text("content_author_name", 70, ($authordetails[1] ? $authordetails[1] : CONTENT_ADMIN_ITEM_LAN_14), 100, "tbox", "", "", ($authordetails[1] ? "" : "onfocus=\"if(document.getElementById('dataform').content_author_name.value=='".CONTENT_ADMIN_ITEM_LAN_14."'){document.getElementById('dataform').content_author_name.value='';}\"") )."</td></tr>
							<tr><td>".CONTENT_ADMIN_ITEM_LAN_15."</td><td>".$rs -> form_text("content_author_email", 70, ($authordetails[2] ? $authordetails[2] : CONTENT_ADMIN_ITEM_LAN_15), 100, "tbox", "", "", ($authordetails[2] ? "" : "onfocus=\"if(document.getElementById('dataform').content_author_email.value=='".CONTENT_ADMIN_ITEM_LAN_15."'){document.getElementById('dataform').content_author_email.value='';}\"") )."
							".$rs -> form_hidden("content_author_id", $authordetails[0])."
							</td></tr></table>
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						$enddate = getdate($row['content_enddate']);
						$end_day = $enddate['mday'];
						$end_month = $enddate['mon'];
						$end_year = $enddate['year'];

						$startdate = getdate($row['content_datestamp']);
						$ne_day = $startdate['mday'];
						$ne_month = $startdate['mon'];
						$ne_year = $startdate['year'];

						$smarray = getdate();
						$current_year = $smarray['year'];

						$months = array(CONTENT_ADMIN_DATE_LAN_0, CONTENT_ADMIN_DATE_LAN_1, CONTENT_ADMIN_DATE_LAN_2, CONTENT_ADMIN_DATE_LAN_3, CONTENT_ADMIN_DATE_LAN_4, CONTENT_ADMIN_DATE_LAN_5, CONTENT_ADMIN_DATE_LAN_6, CONTENT_ADMIN_DATE_LAN_7, CONTENT_ADMIN_DATE_LAN_8, CONTENT_ADMIN_DATE_LAN_9, CONTENT_ADMIN_DATE_LAN_10, CONTENT_ADMIN_DATE_LAN_11);

						//start date
						$TOPIC_TOPIC = CONTENT_ADMIN_DATE_LAN_15;
						$TOPIC_HEADING = CONTENT_ADMIN_ITEM_LAN_73;
						$TOPIC_HELP = CONTENT_ADMIN_DATE_LAN_17;
						$TOPIC_FIELD = "
							".$rs -> form_select_open("ne_day")."
							".$rs -> form_option(CONTENT_ADMIN_DATE_LAN_12, 0, "none");
							for($count=1; $count<=31; $count++){
								$TOPIC_FIELD .= $rs -> form_option($count, ($ne_day == $count ? "1" : "0"), $count);
							}
							$TOPIC_FIELD .= $rs -> form_select_close()."
							".$rs -> form_select_open("ne_month")."
							".$rs -> form_option(CONTENT_ADMIN_DATE_LAN_13, 0, "none");
							for($count=1; $count<=12; $count++){
								$TOPIC_FIELD .= $rs -> form_option($months[($count-1)], ($ne_month == $count ? "1" : "0"), $count);
							}
							$TOPIC_FIELD .= $rs -> form_select_close()."
							".$rs -> form_select_open("ne_year")."
							".$rs -> form_option(CONTENT_ADMIN_DATE_LAN_14, 0, "none");
							for($count=($current_year-5); $count<=($current_year+1); $count++){
								$TOPIC_FIELD .= $rs -> form_option($count, ($ne_year == $count ? "1" : "0"), $count);
							}
							$TOPIC_FIELD .= $rs -> form_select_close()."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//end date
						$TOPIC_TOPIC = CONTENT_ADMIN_DATE_LAN_16;
						$TOPIC_HEADING = CONTENT_ADMIN_ITEM_LAN_74;
						$TOPIC_HELP = CONTENT_ADMIN_DATE_LAN_18;
						$TOPIC_FIELD = "
							".$rs -> form_select_open("end_day")."
							".$rs -> form_option(CONTENT_ADMIN_DATE_LAN_12, 0, "none");
							for($count=1; $count<=31; $count++){
								$TOPIC_FIELD .= $rs -> form_option($count, ($end_day == $count ? "1" : "0"), $count);
							}
							$TOPIC_FIELD .= $rs -> form_select_close()."
							".$rs -> form_select_open("end_month")."
							".$rs -> form_option(CONTENT_ADMIN_DATE_LAN_13, 0, "none");
							for($count=1; $count<=12; $count++){
								$TOPIC_FIELD .= $rs -> form_option($months[($count-1)], ($end_month == $count ? "1" : "0"), $count);
							}
							$TOPIC_FIELD .= $rs -> form_select_close()."
							".$rs -> form_select_open("end_year")."
							".$rs -> form_option(CONTENT_ADMIN_DATE_LAN_14, 0, "none");
							for($count=($current_year-5); $count<=($current_year+1); $count++){
								$TOPIC_FIELD .= $rs -> form_option($count, ($end_year == $count ? "1" : "0"), $count);
							}
							$TOPIC_FIELD .= $rs -> form_select_close()."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						$text .= $TOPIC_ROW_SPACER;

						if($checkicon){
							//icon
							$TOPIC_TOPIC = CONTENT_ADMIN_ITEM_LAN_20;
							$TOPIC_HEADING = CONTENT_ADMIN_ITEM_LAN_75;
							$TOPIC_HELP = "";
							$TOPIC_FIELD = "
								".$rs -> form_hidden("uploadtype1", "icon");
								if ($row['content_icon']){
									$TOPIC_FIELD .= "
									01 ".$rs -> form_text("content_icon", 50, $row['content_icon'], 100, "tbox", TRUE)."
									".$rs -> form_button("button", "removeicon", CONTENT_ADMIN_ITEM_LAN_26, "onClick=\"confirm2_('icon', '', '".$row['content_icon']."');\"").$rs -> form_button("button", "newicon", CONTENT_ADMIN_ITEM_LAN_25, "onClick='expandit(this)'")."
									<div style='display:none;'>";
								}
								if(!FILE_UPLOADS){
									$TOPIC_FIELD .= "<b>".CONTENT_ADMIN_ITEM_LAN_21."</b>";
								}else{
									if(!is_writable($content_icon_path)){
										$TOPIC_FIELD .= "<b>".CONTENT_ADMIN_ITEM_LAN_22." ".$content_icon_path." ".CONTENT_ADMIN_ITEM_LAN_23."</b><br />";
									}
									$TOPIC_FIELD .= "01 <input class='tbox' type='file' name='file_userfile1[]'  size='50' />";
								}
								if ($row['content_icon']){
									$TOPIC_FIELD .= "</div>";
								}
							
							$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						}

						if($checkattach){
							//attachments
							$TOPIC_TOPIC = CONTENT_ADMIN_ITEM_LAN_24;
							$TOPIC_HEADING = CONTENT_ADMIN_ITEM_LAN_76;
							$TOPIC_HELP = "";
							$TOPIC_FIELD = "
									<table style='width:100%; text-align:left;'>";
									$filetmp = explode("[file]", $row['content_file']);
									foreach($filetmp as $key => $value) { 
										if($value == "") { 
											unset($filetmp[$key]); 
										} 
									} 
									$attachments = array_values($filetmp);
									for($i=0;$i<count($attachments);$i++){
										$k=$i+1;
										$num = (strlen($k) == 1 ? "0".$k : $k);
										$TOPIC_FIELD .= "
										<tr>
											<td>".$num." ".$rs -> form_hidden("uploadtype2", "file");
												if ($attachments[$i]){
													$TOPIC_FIELD .= "
													".$rs -> form_text("content_files".$i."", 50, $attachments[$i], 100, "tbox", TRUE)."
													".$rs -> form_button("button", "removefile".$i."", CONTENT_ADMIN_ITEM_LAN_26, "onClick=\"confirm2_('file', '$i', '$attachments[$i]');\"").$rs -> form_button("button", "newfile".$i."", CONTENT_ADMIN_ITEM_LAN_28, "onClick='expandit(this)'")."
													<div style='display:none; &{head};'>
													<input class='tbox' type='file' name='file_userfile2[]' value='".$attachments[$i]."' size='50'>
													</div>
													";
												} else {
													$TOPIC_FIELD .= "<i>".CONTENT_ADMIN_ITEM_LAN_29."</i><br /><input class='tbox' name='file_userfile2[]' type='file' size='50'>";
												}
											$TOPIC_FIELD .= "
											</td>
										</tr>";
									}

									if(count($attachments) < $checkattachnumber){
										for($i=0;$i<$checkattachnumber-count($attachments);$i++){
											$num = (strlen($i+1+count($attachments)) == 1 ? "0".($i+1+count($attachments)) : ($i+1+count($attachments)));
											$TOPIC_FIELD .= "
											<tr>
												<td>".$num." ".$rs -> form_hidden("uploadtype2", "file");
												if(!FILE_UPLOADS){
													$TOPIC_FIELD .= "<b>".CONTENT_ADMIN_ITEM_LAN_21."</b>";
												}else{
													if(!is_writable($content_file_path)){
														$TOPIC_FIELD .= "<b>".CONTENT_ADMIN_ITEM_LAN_22." ".$content_file_path." ".CONTENT_ADMIN_ITEM_LAN_23."</b><br />";
													}
													$TOPIC_FIELD .= "<input class='tbox' type='file' name='file_userfile2[]' size='50' />";
												}
												$TOPIC_FIELD .= "
												</td>
											</tr>";
										}
									}
									$TOPIC_FIELD .= "
									</table>
							";
							$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);
						}

						if($checkimages){
							//images
							$TOPIC_TOPIC = CONTENT_ADMIN_ITEM_LAN_31;
							$TOPIC_HEADING = CONTENT_ADMIN_ITEM_LAN_77;
							$TOPIC_HELP = "";
							$TOPIC_FIELD = "
									<table style='width:100%; text-align:left;'>";
									$imagestmp = explode("[img]", $row['content_image']);
									foreach($imagestmp as $key => $value) { 
										if($value == "") { 
											unset($imagestmp[$key]); 
										} 
									} 
									$imagesarray = array_values($imagestmp);
									for($i=0;$i<count($imagesarray);$i++){
										$k=$i+1;
										$num = (strlen($k) == 1 ? "0".$k : $k);
										$TOPIC_FIELD .= "
										<tr>
											<td>".$num." ".$rs -> form_hidden("uploadtype3", "image");
												if ($imagesarray[$i]){
													$TOPIC_FIELD .= "
													".$rs -> form_text("content_images".$i."", 50, $imagesarray[$i], 100, "tbox", TRUE)."
													".$rs -> form_button("button", "removeimage".$i."", CONTENT_ADMIN_ITEM_LAN_26, "onClick=\"confirm2_('image', '$i', '$imagesarray[$i]');\"").$rs -> form_button("button", "newimage".$i."", CONTENT_ADMIN_ITEM_LAN_33, "onClick='expandit(this)'")."
													<div style='display:none; &{head};'>
													<input class='tbox' type='file' name='file_userfile3[]' value='".$imagesarray[$i]."' size='50'>									</div>
													";
												} else {
													$TOPIC_FIELD .= "<input class='tbox' name='file_userfile3[]' type='file' size='50'>";
												}
											$TOPIC_FIELD .= "
											</td>
										</tr>";
									}
									if(count($imagesarray) < $checkimagesnumber){
										for($i=0;$i<$checkimagesnumber-count($imagesarray);$i++){
											$num = (strlen($i+1+count($imagesarray)) == 1 ? "0".($i+1+count($imagesarray)) : ($i+1+count($imagesarray)));
											$TOPIC_FIELD .= "
											<tr>
												<td>".$num." ".$rs -> form_hidden("uploadtype3", "image");
												if(!FILE_UPLOADS){
													$TOPIC_FIELD .= "<b>".CONTENT_ADMIN_ITEM_LAN_21."</b>";
												}else{
													if(!is_writable($content_image_path)){
														$TOPIC_FIELD .= "<b>".CONTENT_ADMIN_ITEM_LAN_22." ".$content_image_path." ".CONTENT_ADMIN_ITEM_LAN_23."</b><br />";
													}
													$TOPIC_FIELD .= "<input class='tbox' type='file' name='file_userfile3[]' size='50' />";
												}
												$TOPIC_FIELD .= "
												</td>
											</tr>";
										}
									}
									$TOPIC_FIELD .= "
									</table>
							";
							$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);
						}

						if($checkcomment || $checkrating || $checkscore || $checkpe || $checkvisibility || $checkmeta ){
							$text .= $TOPIC_ROW_SPACER;
						}
						if($checkcomment){
							//comment
							$TOPIC_TOPIC = CONTENT_ADMIN_ITEM_LAN_36;
							$TOPIC_HEADING = CONTENT_ADMIN_ITEM_LAN_78;
							$TOPIC_HELP = "";
							$TOPIC_FIELD = "
							".$rs -> form_radio("content_comment", "1", ($row['content_comment'] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
							".$rs -> form_radio("content_comment", "0", ($row['content_comment'] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
							";
							$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);
						}

						if($checkrating){
							//rating
							$TOPIC_TOPIC = CONTENT_ADMIN_ITEM_LAN_37;
							$TOPIC_HEADING = CONTENT_ADMIN_ITEM_LAN_79;
							$TOPIC_HELP = "";
							$TOPIC_FIELD = "
							".$rs -> form_radio("content_rate", "1", ($row['content_rate'] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
							".$rs -> form_radio("content_rate", "0", ($row['content_rate'] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
							";
							$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);
						}

						if($checkpe){
							//print/email/pdf icons
							$TOPIC_TOPIC = CONTENT_ADMIN_ITEM_LAN_38;
							$TOPIC_HEADING = CONTENT_ADMIN_ITEM_LAN_80;
							$TOPIC_HELP = "";
							$TOPIC_FIELD = "
							".$rs -> form_radio("content_pe", "1", ($row['content_pe'] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
							".$rs -> form_radio("content_pe", "0", ($row['content_pe'] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
							";
							$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);
						}

						if($checkvisibility){
							//userclass
							$TOPIC_TOPIC = CONTENT_ADMIN_ITEM_LAN_39;
							$TOPIC_HEADING = CONTENT_ADMIN_ITEM_LAN_81;
							$TOPIC_HELP = "";
							$TOPIC_FIELD = r_userclass("content_class",$row['content_class'], "CLASSES");
							$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);
						}

						if(!($_POST['preview'] || isset($message))){
							$custom = unserialize(stripslashes($row['contentprefvalue']));
						}

						if($checkscore){
							//score
							$TOPIC_TOPIC = CONTENT_ADMIN_ITEM_LAN_40;
							$TOPIC_HEADING = CONTENT_ADMIN_ITEM_LAN_82;
							$TOPIC_HELP = "";
							$TOPIC_FIELD = "
								".$rs -> form_select_open("content_score")."
								".$rs -> form_option(CONTENT_ADMIN_ITEM_LAN_41, 0, "none");
								for($a=1; $a<=100; $a++){
									$TOPIC_FIELD .= $rs -> form_option($a, ($custom['content_custom_score'] == $a ? "1" : "0"), $a);
								}
								$TOPIC_FIELD .= $rs -> form_select_close()."
							";
							$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);
						}

						if($checkmeta){
							//meta
							$TOPIC_TOPIC = CONTENT_ADMIN_ITEM_LAN_53;
							$TOPIC_HEADING = CONTENT_ADMIN_ITEM_LAN_83;
							$TOPIC_HELP = CONTENT_ADMIN_ITEM_LAN_70;
							$TOPIC_FIELD = $rs -> form_text("content_meta", 80, $custom['content_custom_meta'], 250);
							$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);
						}
						
						if($checkcustomnumber){
							$existing_custom = "0";

							//custom data
							$TOPIC_TOPIC = CONTENT_ADMIN_ITEM_LAN_54;
							$TOPIC_HEADING = CONTENT_ADMIN_ITEM_LAN_84;
							$TOPIC_HELP = CONTENT_ADMIN_ITEM_LAN_68;
							$TOPIC_FIELD = "
								<table style='width:100%; border:0;'>";
								if(!empty($custom)){						
									foreach($custom as $k => $v){
										if(!($k == "content_custom_score" || $k == "content_custom_meta")){
											$key = substr($k,15);
											if($checkcustomnumber){
												$TOPIC_FIELD .= "
												<tr>
													<td class='forumheader3' style='border:0;'>".$rs -> form_text("content_custom_key_".$existing_custom."", 20, $key, 100)."</td>
													<td class='forumheader3' style='border:0;'>".$rs -> form_text("content_custom_value_".$existing_custom."", 70, $v, 250)."</td>
												</tr>";
											}else{
												$TOPIC_FIELD .= "
												".$rs -> form_hidden("content_custom_key_".$existing_custom, $key)."
												".$rs -> form_hidden("content_custom_value_".$existing_custom, $v);
											}
											$existing_custom = $existing_custom + 1;
										}
									}
								}
								for($i=$existing_custom;$i<$checkcustomnumber;$i++){
										$TOPIC_FIELD .= "
										<tr>
											<td class='forumheader3' style='border:0;'>".$rs -> form_text("content_custom_key_".$i."", 20, "", 100)."</td>
											<td class='forumheader3' style='border:0;'>".$rs -> form_text("content_custom_value_".$i."", 70, "", 250)."</td>
										</tr>";
								}
								$TOPIC_FIELD .= "</table>
							";
							$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);
						}

						$checktemplate = TRUE;
						if($checktemplate){
							global $fl;

							if(!$content_pref["content_theme_{$type_id}"]){
								$dir = e_PLUGIN."content/templates/default";
							}else{
								if(file_exists(e_PLUGIN."content/templates/".$content_pref["content_theme_{$type_id}"]."/content_content_template.php")){
									$dir = e_PLUGIN."content/templates/".$content_pref["content_theme_{$type_id}"];
								}else{
									$dir = e_PLUGIN."content/templates/default";
								}
							}
							//get_files($path, $fmask = '', $omit='standard', $recurse_level = 0, $current_level = 0, $dirs_only = FALSE)
							$rejectlist = array('$.','$..','/','CVS','thumbs.db','Thumbs.db','*._$', 'index', 'null*');
							$templatelist = $fl->get_files($dir,"content_content",$rejectlist);

							//template
							$TOPIC_TOPIC = CONTENT_ADMIN_ITEM_LAN_92;
							$TOPIC_HEADING = CONTENT_ADMIN_ITEM_LAN_93;
							$TOPIC_HELP = "";
							$TOPIC_FIELD = "
								".$rs -> form_select_open("content_template")."
								".$rs -> form_option(CONTENT_ADMIN_ITEM_LAN_94, 0, "none");
								foreach($templatelist as $template){
									$templatename = substr($template['fname'], 25, -4);
									$templatename = ($template['fname'] == "content_content_template.php" ? "default" : $templatename);
									$TOPIC_FIELD .= $rs -> form_option($templatename, ($custom['content_custom_template'] == $template['fname'] ? "1" : "0"), $template['fname']);
								}
								$TOPIC_FIELD .= $rs -> form_select_close()."
							";
							$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);
						}
						

						$text .= $TOPIC_ROW_SPACER."
						<tr>
							<td colspan='2' style='text-align:center' class='forumheader'>";
							if($sub_action == "edit" || $sub_action == "sa" || $_POST['editp']){
								$text .= $rs -> form_hidden("content_refer", $row['content_refer']);
								$text .= $rs -> form_hidden("content_datestamp", $row['content_datestamp']);
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
								//."?".$type.".".$type_id
								$text .= "
								".$rs -> form_open("post", e_SELF, "deletesubmittedform","","", "")."
								<table style='".ADMIN_WIDTH."' class='fborder'>
								<tr>
								<td style='width:5%; text-align:center' class='fcaption'>".CONTENT_ADMIN_ITEM_LAN_8."</td>
								<td style='width:5%; text-align:center' class='fcaption'>".CONTENT_ADMIN_ITEM_LAN_9."</td>
								<td style='width:15%; text-align:left' class='fcaption'>".CONTENT_ADMIN_ITEM_LAN_48."</td>
								<td style='width:70%; text-align:left' class='fcaption'>".CONTENT_ADMIN_ITEM_LAN_11."</td>
								<td style='width:10%; text-align:center' class='fcaption'>".CONTENT_ADMIN_ITEM_LAN_12."</td>
								</tr>";
								while($row = $sql -> db_Fetch()){

										unset($row['content_pref']);
										$type_id_parent = substr($row['content_parent'],0,1);
										if(!is_object($sql2)){ $sql2 = new db; }
										$sql2 -> db_Select($plugintable, "content_id, content_heading", "content_id = '".$type_id_parent."' ");
										list($parent_id, $parent_heading) = $sql2 -> db_Fetch();
										$delete_heading = str_replace("&#39;", "\'", $row['content_heading']);
										$authordetails = $aa -> getAuthor($row['content_author']);
										$content_pref = $aa -> getContentPref($row['content_id']);
										$content_pref["content_icon_path_{$type_id_parent}"] = ($content_pref["content_icon_path_{$type_id_parent}"] ? $content_pref["content_icon_path_{$type_id_parent}"] : "{e_PLUGIN}content/images/icon/" );
										$content_icon_path = $aa -> parseContentPathVars($content_pref["content_icon_path_{$type_id_parent}"]);
										$caticon = $content_icon_path.$row['content_icon'];
										$cid = $row['content_id'];
										$text .= "
										<tr>
											<td class='forumheader3' style='width:5%; text-align:center'>".$cid."</td>
											<td class='forumheader3' style='width:5%; text-align:center'>".($row['content_icon'] ? "<img src='".$caticon."' alt='' style='width:50px; vertical-align:middle' />" : "&nbsp;")."</td>
											<td class='forumheader3' style='width:15%; text-align:left'>".$parent_heading."</td>
											<td class='forumheader3' style='width:75%; text-align:left; white-space:nowrap;'><b>".$row['content_heading']."</b> [".$row['content_subheading']."]<br />
											".($authordetails[0] == "0" ? $authordetails[1] : "<a href='".e_BASE."user.php?id.".$authordetails[0]."'>".$authordetails[1]."</a>")."	
											(".$authordetails[2].")</td>
											<td class='forumheader3' style='width:5%; text-align:center; white-space:nowrap;'>

											<a href='".e_SELF."?".$type.".".$type_id_parent.".create.sa.".$cid."'>".CONTENT_ICON_EDIT."</a>
									
											<input type='image' value='{$cid}' title='delete' name='delete_submitted' src='".CONTENT_ICON_DELETE_BASE."' onclick=\"return jsconfirm('".$tp->toJS(CONTENT_ADMIN_JS_LAN_10."\\n\\n[".CONTENT_ADMIN_JS_LAN_6." ".$cid." : ".$delete_heading."]")."')\"/>

											</td>
										</tr>";
											/*
											".$rs -> form_open("post", e_SELF."?".$type.".".$type_id_parent, "myform_{$cid}","","", "")."
											<a href='".e_SELF."?".$type.".".$type_id_parent.".create.sa.".$cid."'>".CONTENT_ICON_EDIT."</a> 
											<a onclick=\"if(jsconfirm('".$tp->toJS(CONTENT_ADMIN_JS_LAN_10."\\n\\n[".CONTENT_ADMIN_JS_LAN_6." ".$cid." : ".$delete_heading."]")."')){document.forms['myform_{$cid}'].submit();}\" >".CONTENT_ICON_DELETE."</a>
											".$rs -> form_hidden("content_delete_{$row['content_id']}", "delete")."
											".$rs -> form_close()."
											*/
								}
								$text .= "</table>
								".$rs -> form_close();
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

								$oText = str_replace("'", "\'", CONTENT_ADMIN_ITEM_LAN_66);
								$popuphelp = $aa -> popupHelp($oText, "", "", "");
								
								$text .= "
								<div style='text-align:center'>
								<form method='post' action='".$formtarget."'>
								<table class='fborder' style='".ADMIN_WIDTH."'>
								<tr><td colspan='2' class='fcaption'>".CONTENT_ADMIN_ITEM_LAN_6." ".$popuphelp."</td></tr>
								<tr><td colspan='2' class='forumheader3'>";
								while($row = $sql -> db_Fetch()){
									if($row['letter'] != ""){
										$text .= "<input class='button' style='width:20' type='submit' name='letter' value='".strtoupper($row['letter'])."' />";
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
									$oText = str_replace("'", "\'", CONTENT_ADMIN_ITEM_LAN_67);
									$popuphelp = $aa -> popupHelp($oText, "", "", "");
									$text .= "
									".$rs -> form_open("post", e_SELF."?".$type.".".$type_id, "deletecontentform","","", "")."
									<table style='".ADMIN_WIDTH."' class='fborder'>
									<tr>
									<td class='fcaption' style='width:5%; text-align:center;'>".CONTENT_ADMIN_ITEM_LAN_8."</td>
									<td class='fcaption' style='width:5%; text-align:center;'>".CONTENT_ADMIN_ITEM_LAN_9."</td>
									<td class='fcaption' style='width:10%; text-align:left;'>".CONTENT_ADMIN_ITEM_LAN_10."</td>
									<td class='fcaption' style='width:70%; text-align:left;'>".CONTENT_ADMIN_ITEM_LAN_11."</td>
									<td class='fcaption' style='width:10%; text-align:center;'>".CONTENT_ADMIN_ITEM_LAN_12." ".$popuphelp."</td>
									</tr>";
									while($row = $sql2 -> db_Fetch()){
											$delete_heading = str_replace("&#39;", "\'", $row['content_heading']);
											$authordetails = $aa -> getAuthor($row['content_author']);
											$caticon = $content_icon_path.$row['content_icon'];
											$deleteicon = CONTENT_ICON_DELETE;
											$cid = $row['content_id'];
											$text .= "
											<tr>
												<td class='forumheader3' style='width:5%; text-align:center'>".$cid."</td>
												<td class='forumheader3' style='width:5%; text-align:center'>".($row['content_icon'] ? "<img src='".$caticon."' alt='' style='width:50px; vertical-align:middle' />" : "&nbsp;")."</td>
												<td class='forumheader3' style='width:10%; text-align:left'>[".$authordetails[0]."] ".$authordetails[1]."</td>
												<td class='forumheader3' style='width:70%; text-align:left;'>".$row['content_heading']." [".$row['content_subheading']."]</td>
												<td class='forumheader3' style='width:10%; text-align:center; white-space:nowrap; vertical-align:top;'>

												<a href='".e_SELF."?".$type.".".$type_id.".create.edit.".$cid."'>".CONTENT_ICON_EDIT."</a> 
												<input type='image' value='{$cid}' title='delete' name='delete_content' src='".CONTENT_ICON_DELETE_BASE."' onclick=\"return jsconfirm('".$tp->toJS(CONTENT_ADMIN_JS_LAN_1."\\n\\n[".CONTENT_ADMIN_JS_LAN_6." ".$cid." : ".$delete_heading."]")."')\"/>

												</td>
											</tr>";
									}
									$text .= "</table>
									".$rs -> form_close();
												/*
												".$rs -> form_open("post", e_SELF."?".$type.".".$type_id, "myform_{$content_id}","","", "")."
												<a href='".e_SELF."?".$type.".".$type_id.".create.edit.".$content_id."'>".CONTENT_ICON_EDIT."</a> 
												<a onclick=\"if(jsconfirm('".$tp->toJS(CONTENT_ADMIN_JS_LAN_1."\\n\\n[".CONTENT_ADMIN_JS_LAN_6." ".$content_id." : ".$delete_heading."]")."')){document.forms['myform_{$content_id}'].submit();}\" >".CONTENT_ICON_DELETE."</a>
												".$rs -> form_hidden("content_delete_{$content_id}", "delete")."
												".$rs -> form_close()."
												*/
							} else {
									$text .= "<br /><div style='text-align:center'>".CONTENT_ADMIN_ITEM_LAN_7."</div>";
							}
						}
						$text .= "</div>";
						$ns -> tablerender(CONTENT_ADMIN_ITEM_LAN_5, $text);
		}


		function show_cat_create(){
						global $plugintable, $sql, $ns, $rs, $aa, $fl;
						global $type, $type_id, $action, $sub_action, $id;
						global $content_parent, $content_heading, $content_subheading, $content_text, $content_icon, $content_comment, $content_rate, $content_pe, $content_class;
						global $stylespacer, $td1, $TOPIC_ROW_SPACER, $TOPIC_ROW, $TOPIC_ROW_NOEXPAND;

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
								$row = $sql -> db_Fetch();
								if(substr($row['content_parent'],0,1) != "0"){
									unset($id); header("location:".e_SELF."?".$type.".".$type_id.".cat"); exit;
								}
							}else{
								header("location:".e_SELF."?".$type.".".$type_id.".cat"); exit;
							}
						}

						$text = "
						<div style='text-align:center'>
						".$rs -> form_open("post", e_SELF."?".$type.".".$type_id.".cat.create", "dataform")."
						<table class='fborder' style='".ADMIN_WIDTH."'>";

						//category parent
						$TOPIC_TOPIC = CONTENT_ADMIN_CAT_LAN_27;
						$TOPIC_FIELD = "
							".$rs -> form_select_open("parent")."
							".$rs -> form_option("** ".CONTENT_ADMIN_CAT_LAN_26." **", "0", "none")."
							".$aa -> printParent($parentdetails, "0", $row['content_parent'], "optioncat")."
							".$rs -> form_select_close()."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW_NOEXPAND);

						//heading
						$TOPIC_TOPIC = CONTENT_ADMIN_CAT_LAN_2;
						$TOPIC_FIELD = $rs -> form_text("cat_heading", 90, $row['content_heading'], 250);
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW_NOEXPAND);

						//subheading
						$TOPIC_TOPIC = CONTENT_ADMIN_CAT_LAN_3;
						$TOPIC_FIELD = $rs -> form_text("cat_subheading", 90, $row['content_subheading'], 250);
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW_NOEXPAND);

						//text
						$insertjs = " onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);' ";
						require_once(e_HANDLER."ren_help.php");
						$TOPIC_TOPIC = CONTENT_ADMIN_CAT_LAN_4;
						//".$rs -> form_textarea("cat_text", 87, 20, $row['content_text'], ($pref['wysiwyg'] ? $insertjs : ""))."<br />
						$TOPIC_FIELD = "
							".$rs -> form_textarea("cat_text", 80, 20, $row['content_text'], $insertjs )."<br />
							".$rs -> form_text("helpb", 90, '', '', "helpbox")."<br />
							".display_help()."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW_NOEXPAND);

						if($row['content_datestamp']){
							$startdate = getdate($row['content_datestamp']);
							$ne_day = $startdate['mday'];
							$ne_month = $startdate['mon'];
							$ne_year = $startdate['year'];
						}else{
							$ne_day = "";
							$ne_month = "";
							$ne_year = "";
						}
						if($row['content_enddate']){
							$enddate = getdate($row['content_enddate']);
							$end_day = $enddate['mday'];
							$end_month = $enddate['mon'];
							$end_year = $enddate['year'];
						}else{
							$end_day = "";
							$end_month = "";
							$end_year = "";
						}

						$smarray = getdate();
						$current_year = $smarray['year'];

						$months = array(CONTENT_ADMIN_DATE_LAN_0, CONTENT_ADMIN_DATE_LAN_1, CONTENT_ADMIN_DATE_LAN_2, CONTENT_ADMIN_DATE_LAN_3, CONTENT_ADMIN_DATE_LAN_4, CONTENT_ADMIN_DATE_LAN_5, CONTENT_ADMIN_DATE_LAN_6, CONTENT_ADMIN_DATE_LAN_7, CONTENT_ADMIN_DATE_LAN_8, CONTENT_ADMIN_DATE_LAN_9, CONTENT_ADMIN_DATE_LAN_10, CONTENT_ADMIN_DATE_LAN_11);
						
						$text .= $TOPIC_ROW_SPACER;

						//start date
						$TOPIC_TOPIC = CONTENT_ADMIN_DATE_LAN_15;
						$TOPIC_HEADING = CONTENT_ADMIN_ITEM_LAN_73;
						$TOPIC_HELP = CONTENT_ADMIN_DATE_LAN_17;
						$TOPIC_FIELD = "
							".$rs -> form_select_open("ne_day")."
							".$rs -> form_option(CONTENT_ADMIN_DATE_LAN_12, 0, "none");
							for($count=1; $count<=31; $count++){
								$TOPIC_FIELD .= $rs -> form_option($count, ($ne_day == $count ? "1" : "0"), $count);
							}
							$TOPIC_FIELD .= $rs -> form_select_close()."
							".$rs -> form_select_open("ne_month")."
							".$rs -> form_option(CONTENT_ADMIN_DATE_LAN_13, 0, "none");
							for($count=1; $count<=12; $count++){
								$TOPIC_FIELD .= $rs -> form_option($months[($count-1)], ($ne_month == $count ? "1" : "0"), $count);
							}
							$TOPIC_FIELD .= $rs -> form_select_close()."
							".$rs -> form_select_open("ne_year")."
							".$rs -> form_option(CONTENT_ADMIN_DATE_LAN_14, 0, "none");
							for($count=($current_year-5); $count<=$current_year; $count++){
								$TOPIC_FIELD .= $rs -> form_option($count, ($ne_year == $count ? "1" : "0"), $count);
							}
							$TOPIC_FIELD .= $rs -> form_select_close()."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//end date
						$TOPIC_TOPIC = CONTENT_ADMIN_DATE_LAN_16;
						$TOPIC_HEADING = CONTENT_ADMIN_ITEM_LAN_74;
						$TOPIC_HELP = CONTENT_ADMIN_DATE_LAN_18;
						$TOPIC_FIELD = "
							".$rs -> form_select_open("end_day")."
							".$rs -> form_option(CONTENT_ADMIN_DATE_LAN_12, 0, "none");
							for($count=1; $count<=31; $count++){
								$TOPIC_FIELD .= $rs -> form_option($count, ($end_day == $count ? "1" : "0"), $count);
							}
							$TOPIC_FIELD .= $rs -> form_select_close()."
							".$rs -> form_select_open("end_month")."
							".$rs -> form_option(CONTENT_ADMIN_DATE_LAN_13, 0, "none");
							for($count=1; $count<=12; $count++){
								$TOPIC_FIELD .= $rs -> form_option($months[($count-1)], ($end_month == $count ? "1" : "0"), $count);
							}
							$TOPIC_FIELD .= $rs -> form_select_close()."
							".$rs -> form_select_open("end_year")."
							".$rs -> form_option(CONTENT_ADMIN_DATE_LAN_14, 0, "none");
							for($count=($current_year-5); $count<=$current_year; $count++){
								$TOPIC_FIELD .= $rs -> form_option($count, ($end_year == $count ? "1" : "0"), $count);
							}
							$TOPIC_FIELD .= $rs -> form_select_close()."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						$rejectlist = array('$.','$..','/','CVS','thumbs.db','Thumbs.db','*._$', 'index', 'null*');
						$iconlist = $fl->get_files($content_cat_icon_path_large,"",$rejectlist);

						//icon
						$TOPIC_TOPIC = CONTENT_ADMIN_CAT_LAN_5;
						$TOPIC_HEADING = CONTENT_ADMIN_CAT_LAN_49;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = "
						".$rs -> form_text("cat_icon", 60, $row['content_icon'], 100)."
						".$rs -> form_button("button", '', CONTENT_ADMIN_CAT_LAN_8, "onclick='expandit(this)'")."
						<div id='divcaticon' style='{head}; display:none'>";
						foreach($iconlist as $icon){
							$TOPIC_FIELD .= "<a href=\"javascript:insertext('".$icon['fname']."','cat_icon','divcaticon')\"><img src='".$icon['path'].$icon['fname']."' style='border:0' alt='' /></a> ";
						}
						$TOPIC_FIELD .= "</div>
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);
						
						//comments
						$TOPIC_TOPIC = CONTENT_ADMIN_CAT_LAN_14;
						$TOPIC_HEADING = CONTENT_ADMIN_CAT_LAN_45;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = "
						".$rs -> form_radio("cat_comment", "1", ($row['content_comment'] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
						".$rs -> form_radio("cat_comment", "0", ($row['content_comment'] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//rating
						$TOPIC_TOPIC = CONTENT_ADMIN_CAT_LAN_15;
						$TOPIC_HEADING = CONTENT_ADMIN_CAT_LAN_46;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = "
						".$rs -> form_radio("cat_rate", "1", ($row['content_rate'] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
						".$rs -> form_radio("cat_rate", "0", ($row['content_rate'] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//print/email/pdf icons
						$TOPIC_TOPIC = CONTENT_ADMIN_CAT_LAN_16;
						$TOPIC_HEADING = CONTENT_ADMIN_CAT_LAN_47;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = "
						".$rs -> form_radio("cat_pe", "1", ($row['content_pe'] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
						".$rs -> form_radio("cat_pe", "0", ($row['content_pe'] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//userclass
						$TOPIC_TOPIC = CONTENT_ADMIN_CAT_LAN_17;
						$TOPIC_HEADING = CONTENT_ADMIN_CAT_LAN_48;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = r_userclass("cat_class",$row['content_class'], "CLASSES");
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						$text .= "
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
							require_once(e_PLUGIN."content/templates/content_manager_template.php");
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
											$personalmanagercheck = TRUE;
										
											$parentheading = $aa -> drawBreadcrumb($prefetchbreadcrumb, $row['content_id'], "nobase", "nolink");
											for($i=0; $i<count($prefetchbreadcrumb); $i++){
												if($row['content_id'] == $prefetchbreadcrumb[$i][0]){
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


		function show_cat_order($mode){
						global $sql, $ns, $rs, $type, $type_id, $action, $sub_action, $id, $plugintable, $aa, $tp, $stylespacer;

						$parentdetails = $aa -> getParent("","","", "", false);	//use all categories

						$text = "
						<div style='text-align:center'>
						".$rs -> form_open("post", e_SELF."?".$type.".".$type_id.".order.cat", "orderform")."
						<table class='fborder' style='".ADMIN_WIDTH."'>
						<tr><td class='fcaption' colspan='5'>".CONTENT_ADMIN_MAIN_LAN_2."</td></tr>
						<tr>
							<td class='fcaption' style='width:5%; text-align:center; white-space:nowrap;'>".CONTENT_ADMIN_ITEM_LAN_8."</td>
							<td class='fcaption' style='width:80%; text-align:left;'>".CONTENT_ADMIN_ITEM_LAN_57."</td>
							<td class='fcaption' style='width:5%; text-align:center; white-space:nowrap;'>".CONTENT_ADMIN_ITEM_LAN_58."</td>
							<td class='fcaption' style='width:5%; text-align:center; white-space:nowrap;'>".CONTENT_ADMIN_ITEM_LAN_59."</td>
							<td class='fcaption' style='width:5%; text-align:center; white-space:nowrap;'>".CONTENT_ADMIN_ITEM_LAN_60."</td>
						</tr>";
						
						$subcatarray = $aa -> countSubCat();		//create array of amount of subcats for each main parent

						for($i=0;$i<count($parentdetails);$i++){

							if($parentdetails[$i][9] == "0"){		//if main parent
								foreach ($subcatarray as $key => $value){
									if($parentdetails[$i][0] == $key){
										$ordermax = $value+1;		//ordermax equals the amount of subcats + 1 (for the main parent)
										break;
									}
								}
							}							

							if(strpos($parentdetails[$i][9], ".")){
								$tmp1 = explode(".", $parentdetails[$i][9]);
								$id = $tmp1[1].".".$parentdetails[$i][0];
								$type_id = $tmp1[1];
							}else{
								$id = $parentdetails[$i][0].".".$parentdetails[$i][0];
								$type_id = $parentdetails[$i][0];
							}
							$id = str_replace(".", "-", $id);

							if($parentdetails[$i][9] == "0"){
								$catitemid = $type_id.".".$type_id;
							}else{
								$catitemid = $type_id.".".substr($parentdetails[$i][9],2).".".$parentdetails[$i][0];
							}
							$catitemid = str_replace(".", "-", $catitemid);

							$class = ($parentdetails[$i][9] == 0 ? "forumheader" : "forumheader3");
							$fontweight = ($parentdetails[$i][9] == 0 ? "bold" : "normal");
							$text .= ($parentdetails[$i][9] == 0 ? "<tr><td colspan='5' $stylespacer></td></tr>" : "");
							$text .= "
							<tr>
								<td class='".$class."' style='width:5%; font-weight:".$fontweight."; text-align:center; white-space:nowrap;'>".$parentdetails[$i][0]."</td>
								<td class='".$class."' style='width:80%; font-weight:".$fontweight."; text-align:left;'>".$parentdetails[$i][1]."</td>
								<td class='".$class."' style='width:5%; text-align:left; white-space:nowrap;'>
									<a href='".e_SELF."?".$type.".".$type_id.".order.item.".$catitemid."'>".CONTENT_ICON_ORDERCAT."</a>
									".($parentdetails[$i][9] == 0 ? "<a href='".e_SELF."?".$type.".".$type_id.".order.all'>".CONTENT_ICON_ORDERALL."</a>" : "")."
								</td>
								<td class='".$class."' style='width:5%; text-align:center; white-space:nowrap;'>
									<a href='".e_SELF."?".$type.".".$type_id.".order.cat.inc-".$parentdetails[$i][0]."-".$parentdetails[$i][16]."'><img src='".e_IMAGE."admin_images/up.png' alt='".CONTENT_ADMIN_ITEM_LAN_63."' style='border:0;' /></a>
									<a href='".e_SELF."?".$type.".".$type_id.".order.cat.dec-".$parentdetails[$i][0]."-".$parentdetails[$i][16]."'><img src='".e_IMAGE."admin_images/down.png' alt='".CONTENT_ADMIN_ITEM_LAN_64."' style='border:0;' /></a>
								</td>
								<td class='".$class."' style='width:5%; text-align:center; white-space:nowrap;'>
									<select name='order[]' class='tbox'>";
									for($k=1;$k<=$ordermax;$k++){
										$text .= $rs -> form_option($k, ($parentdetails[$i][16] == $k ? "1" : "0"), $parentdetails[$i][0].".".$k.".cat");
									}
									$text .= "</select>
								</td>
							</tr>";
						}

						$text .= "
						<tr><td colspan='5' $stylespacer></td></tr>
						<tr>
							<td class='fcaption' colspan='3'>&nbsp;</td>
							<td class='fcaption' colspan='2' style='text-align:center'>
								".$rs -> form_button("submit", "update_order", CONTENT_ADMIN_ITEM_LAN_61)."
							</td>
						</tr>
						</table>
						".$rs -> form_close()."
						</div>";

						$oText = str_replace("'", "\'", CONTENT_ADMIN_HELP_LAN_18);
						$popuphelp = $aa -> popupHelp($oText, "", "", "");

						$ns -> tablerender(CONTENT_ADMIN_ITEM_LAN_62." ".$popuphelp, $text);

						return;
		}


		function show_content_order($mode, $style){
						global $sql, $ns, $rs, $type, $type_id, $action, $sub_action, $id, $plugintable, $aa, $tp;

						if($style == "catitem"){
							$cat = str_replace("-", ".", $id);
							$formtarget = e_SELF."?".$type.".".$type_id.".order.item.".$id;
							$query = "content_parent = '".$cat."' ";
							$order = "SUBSTRING_INDEX(content_order, '.', 1)+0";

						}elseif($style == "allitem"){
							$formtarget = e_SELF."?".$type.".".$type_id.".order.all";
							$query = "LEFT(content_parent, ".strlen($type_id)." ) = '".$type_id."' ";
							$order = "SUBSTRING_INDEX(content_order, '.', -1)+0";
						}

						if(!is_object($sql)){ $sql = new db; }
						if(!$content_total = $sql -> db_Select($plugintable, "content_id, content_heading, content_author, content_order", "content_refer != 'sa' AND ".$query." ORDER BY ".$order." ASC, content_heading DESC ")){
							$text = "<div style='text-align:center'>".CONTENT_ADMIN_ITEM_LAN_4."</div>";
						}else{
							
							$text = "
							<div style='text-align:center'>
							".$rs -> form_open("post", $formtarget, "orderform")."
							<table class='fborder' style='".ADMIN_WIDTH."'>
							<tr><td class='fcaption' colspan='5'>".CONTENT_ADMIN_MAIN_LAN_2."</td></tr>
							<tr>
								<td class='forumheader' style='width:5%; text-align:center; white-space:nowrap;'>".CONTENT_ADMIN_ITEM_LAN_8."</td>
								<td class='forumheader' style='width:15%; text-align:left;'>".CONTENT_ADMIN_ITEM_LAN_10."</td>
								<td class='forumheader' style='width:70%; text-align:center; white-space:nowrap;'>".CONTENT_ADMIN_ITEM_LAN_11."</td>
								<td class='forumheader' style='width:5%; text-align:center; white-space:nowrap;'>".CONTENT_ADMIN_ITEM_LAN_59."</td>
								<td class='forumheader' style='width:5%; text-align:center; white-space:nowrap;'>".CONTENT_ADMIN_ITEM_LAN_60."</td>
							</tr>";

							while($row = $sql -> db_Fetch()){
									$delete_heading = str_replace("&#39;", "\'", $row['content_heading']);
									$authordetails = $aa -> getAuthor($row['content_author']);
									$caticon = $content_icon_path.$row['content_icon'];
									$deleteicon = CONTENT_ICON_DELETE;

									$tmp = explode(".", $row['content_order']);
									if(!$tmp[1]){ $tmp[1] = "0"; }
									$row['content_order'] = $tmp[0]."-".$tmp[1];

									if($style == "catitem"){
										$ordercheck = $tmp[0];
										$ordercheck2 = $tmp[1];
										$addid = ".".$id;
									}elseif($style == "allitem"){
										$ordercheck = $tmp[1];
										$ordercheck2 = $tmp[0];
										$addid = "";
									}
									$cid = $row['content_id'];
									$corder = $row['content_order'];

									$text .= "
									<tr>
										<td class='forumheader3' style='width:5%; text-align:center; white-space:nowrap;'>".$cid."</td>
										<td class='forumheader3' style='width:15%; text-align:left; white-space:nowrap;'>
											[".$authordetails[0]."] ".$authordetails[1]."
										</td>
										<td class='forumheader3' style='width:70%; text-align:left;'>".$row['content_heading']."</td>
										<td class='forumheader3' style='width:5%; text-align:center; white-space:nowrap;'>
											<a href='".e_SELF."?".$type.".".$type_id.".order.".$sub_action."".$addid.".inc-".$cid."-".$corder."'><img src='".e_IMAGE."admin_images/up.png' alt='".CONTENT_ADMIN_ITEM_LAN_63."' style='border:0;' /></a>
											<a href='".e_SELF."?".$type.".".$type_id.".order.".$sub_action."".$addid.".dec-".$cid."-".$corder."'><img src='".e_IMAGE."admin_images/down.png' alt='".CONTENT_ADMIN_ITEM_LAN_64."' style='border:0;' /></a>
										</td>
										<td class='forumheader3' style='width:5%; text-align:center; white-space:nowrap;'>
											<select name='order[]' class='tbox'>";
											for($k=1;$k<=$content_total;$k++){
												$text .= $rs -> form_option($k, ($ordercheck == $k ? "1" : "0"), $cid.".".$k.".".$style.".".$corder);
											}
											$text .= "</select>
										</td>
									</tr>";
							}
							$text .= "
							<tr>
								<td class='fcaption' colspan='3'>&nbsp;</td>
								<td class='fcaption' colspan='2' style='text-align:center'>
									".$rs -> form_button("submit", "update_order", CONTENT_ADMIN_ITEM_LAN_61)."
								</td>
							</tr>
							</table>
							".$rs -> form_close()."
							</div>";

						}

						$ns -> tablerender(CONTENT_ADMIN_ITEM_LAN_65, $text);

						return;
		}

		function show_main_intro(){
						global $sql, $ns, $rs, $type, $type_id, $action, $sub_action, $id, $plugintable;

						if(!is_object($sql)){ $sql = new db; }
						$newcontent = $sql -> db_Count($plugintable, "(*)", "");
						if($newcontent > 0){
							return false;
						}else{

							$text .= "
							<div style='text-align:center'>
							".$rs -> form_open("post", e_PLUGIN."content/admin_content_convert.php", "dataform")."
							<table class='fborder' style='".ADMIN_WIDTH."'>";
							
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
							</div>";

							$ns -> tablerender(CONTENT_ADMIN_MAIN_LAN_7, $text);
							return true;
						}
		}


		function show_main_parent($mode){
						global $aa, $sql, $ns, $rs, $type, $type_id, $action, $sub_action, $id, $plugintable;

						if(!is_object($sql)){ $sql = new db; }
						if(!$sql -> db_Select($plugintable, "content_id, content_heading", "content_parent='0' ")){
								$text = "
								<div style='text-align:center'>".CONTENT_ADMIN_MAIN_LAN_1."<br />";

								if($mode == "create"){ $text .= CONTENT_ADMIN_MAIN_LAN_17; }
								if($mode == "edit"){ $text .= ""; }
								if($mode == "order"){ $text .= CONTENT_ADMIN_MAIN_LAN_17; }
								if($mode == "editcat"){ $text .= CONTENT_ADMIN_MAIN_LAN_17; }
								if($mode == "createcat"){ $text .= ""; }

								$text .= "</div>";
						}else{
								if($mode == "create"){ $help = CONTENT_ADMIN_MAIN_LAN_13; }
								if($mode == "edit"){ $help = CONTENT_ADMIN_MAIN_LAN_10; }
								if($mode == "order"){ $help = CONTENT_ADMIN_MAIN_LAN_14; }
								if($mode == "editcat"){ $help = CONTENT_ADMIN_MAIN_LAN_15; }
								if($mode == "createcat"){ $help = CONTENT_ADMIN_MAIN_LAN_16; }

								$oText = str_replace("'", "\'", $help);
								$popuphelp = $aa -> popupHelp($oText, "", "", "");

								$text = "
								<div style='text-align:center'>								
								<table style='".ADMIN_WIDTH."' class='fborder'>
								<tr><td class='fcaption'>".CONTENT_ADMIN_MAIN_LAN_2." ".$popuphelp."</td></tr>
								<tr><td class='forumheader3'>";
								while($row = $sql -> db_Fetch()){
										if($mode == "create"){ $urllocation = e_SELF."?".$type.".".$row['content_id'].".create"; }
										if($mode == "edit"){ $urllocation = e_SELF."?".$type.".".$row['content_id'].""; }
										if($mode == "order"){ $urllocation = e_SELF."?".$type.".".$row['content_id'].".order.cat"; }
										if($mode == "editcat"){ $urllocation = e_SELF."?".$type.".".$row['content_id'].".cat.manage"; }
										if($mode == "createcat"){ $urllocation = e_SELF."?".$type.".".$row['content_id'].".cat.create"; }
										$text .= $rs -> form_button("button", "typeselect_{$row['content_id']}", $row['content_heading'], "onclick=\"document.location='".$urllocation."'\"")." ";
									}
								$text .= "</table></div>";
						}
						$ns -> tablerender(CONTENT_ADMIN_MAIN_LAN_0, $text);
						return;
		}

		/*
		function show_cat_manage(){
						global $sql, $ns, $rs, $aa, $plugintable;
						global $type, $type_id, $action, $sub_action, $id;

						$parentdetails = $aa -> getParent("","",$type_id, "", false);	//use only categories from selected type_id
						//$parentdetails = $aa -> getParent("","","", "", false);	//use all categories
						//usage: getParent($id, $level="", $mode="", $classcheck="", $date="")
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
								if($category_total = $sql -> db_Select($plugintable, "content_id, content_heading, content_subheading, content_text, content_author, content_icon", "content_id='".$type_id."' ORDER BY content_parent")){
									$row = $sql -> db_Fetch();
										$text .= "
										".$rs -> form_open("post", e_SELF."?".$type.".".$type_id.".cat.manage", "deletecatform","","", "")."
										<table style='".ADMIN_WIDTH."' class='fborder'>
										<tr>
										<td class='fcaption' style='width:5%'>".CONTENT_ADMIN_CAT_LAN_24."</td>
										<td class='fcaption' style='width:5%'>".CONTENT_ADMIN_CAT_LAN_25."</td>
										<td class='fcaption' style='width:15%'>".CONTENT_ADMIN_CAT_LAN_18."</td>
										<td class='fcaption' style='width:65%'>".CONTENT_ADMIN_CAT_LAN_19."</td>
										<td class='fcaption' style='width:10%; text-align:center'>".CONTENT_ADMIN_CAT_LAN_20."</td>
										</tr>
										".$aa -> printParent($parentdetails, "0", $row['content_id'], "table")."
										</table>
										".$rs -> form_close();
								}else{
									$text .= "<div style='text-align:center'>".CONTENT_ADMIN_CAT_LAN_9."</div>";
								}
								$text .= "</div>";
								$oText = str_replace("'", "\'", CONTENT_ADMIN_CAT_LAN_40);
								$popuphelp = $aa -> popupHelp($oText, "", "", "");
								$ns -> tablerender(CONTENT_ADMIN_CAT_LAN_10." ".$popuphelp, $text);
								unset($row['content_id'], $row['content_heading'], $row['content_subheading'], $row['content_text'], $row['content_icon']);
						}
		}
		*/
		function show_cat_manage(){
						global $sql, $ns, $rs, $aa, $plugintable;
						global $type, $type_id, $action, $sub_action, $id;

						$parentdetails = $aa -> getParent("","","", "", false);	//use all categories

						// ##### MANAGE CATEGORIES ------------------
						//if(!$sub_action || $sub_action == "manage"){
						//if(!$sub_action){
								$text = "<div style='text-align:center'>\n";
								if(!is_object($sql)){ $sql = new db; }
								if($category_total = $sql -> db_Select($plugintable, "content_id, content_heading, content_subheading, content_text, content_author, content_icon, content_parent", "ORDER BY content_parent", "mode=no_where")){
									$row = $sql -> db_Fetch();

										$content_pref = $aa -> getContentPref($type_id);
										$content_cat_icon_path_large = $aa -> parseContentPathVars($content_pref["content_cat_icon_path_large_{$type_id}"]);
										$content_cat_icon_path_small = $aa -> parseContentPathVars($content_pref["content_cat_icon_path_small_{$type_id}"]);
										//$content_icon_path = $aa -> parseContentPathVars($content_pref["content_icon_path_{$type_id}"]);
										//$content_image_path = $aa -> parseContentPathVars($content_pref["content_image_path_{$type_id}"]);
										//$content_file_path = $aa -> parseContentPathVars($content_pref["content_file_path_{$type_id}"]);

										$text .= "
										".$rs -> form_open("post", e_SELF."?".$type.".".$type_id.".cat.manage", "deletecatform","","", "")."
										<table style='".ADMIN_WIDTH."' class='fborder'>
										<tr>
										<td class='fcaption' style='width:5%'>".CONTENT_ADMIN_CAT_LAN_24."</td>
										<td class='fcaption' style='width:5%'>".CONTENT_ADMIN_CAT_LAN_25."</td>
										<td class='fcaption' style='width:15%'>".CONTENT_ADMIN_CAT_LAN_18."</td>
										<td class='fcaption' style='width:65%'>".CONTENT_ADMIN_CAT_LAN_19."</td>
										<td class='fcaption' style='width:10%; text-align:center'>".CONTENT_ADMIN_CAT_LAN_20."</td>
										</tr>
										".$aa -> printParent($parentdetails, "0", $row['content_id'], "table")."
										</table>
										".$rs -> form_close();
								}else{
									$text .= "<div style='text-align:center'>".CONTENT_ADMIN_CAT_LAN_9."</div>";
								}
								$text .= "</div>";

								//$oText = str_replace("'", "\'", CONTENT_ADMIN_CAT_LAN_40);
								$oText = CONTENT_ADMIN_HELP_LAN_10.(getperms("0") ? CONTENT_ADMIN_HELP_LAN_15 : "");
								$oText = str_replace("'", "\'", $oText);
								$popuphelp = $aa -> popupHelp($oText, "", "", "");
								$ns -> tablerender(CONTENT_ADMIN_CAT_LAN_10." ".$popuphelp, $text);
								unset($row['content_id'], $row['content_heading'], $row['content_subheading'], $row['content_text'], $row['content_icon']);
						//}
		}


		function show_admin_contentmanager(){
						global $plugintable;
						global $sql, $ns, $rs, $aa;
						global $type, $type_id, $action, $sub_action, $id;

						if(!getperms("0")){ header("location:".e_SELF); exit; }

						if(!is_object($sql)){ $sql = new db; }
						if($sql -> db_Select($plugintable, "content_id, content_pref as contentprefvalue", "content_id='$id' ")){
							$row = $sql -> db_Fetch();
						}else{
							header("location:".e_SELF."?".$type.".".$type_id.".cat.".$id); exit;
						}

						$personalcontentusers = explode(",", $row['contentprefvalue']);
						for($i=0;$i<count($personalcontentusers);$i++){
							if(empty($personalcontentusers[$i])){ unset($personalcontentusers[$i]); }
						}

						if(!is_object($sql2)){ $sql2 = new db; }
						$sql2->db_Select("user", "*", "user_admin='1' AND user_perms != '0' ");
						$c = 0;
						$d = 0;
						while ($row = $sql2->db_Fetch()) {
								if(in_array($row['user_id'], $personalcontentusers)){
									$in_userid[$c] = $row['user_id'];
									$in_username[$c] = $row['user_name'];
									$in_userlogin[$c] = $row['user_login'] ? "(".$row['user_login'].")" :
									 "";
									$c++;
								}else{
									$out_userid[$d] = $row['user_id'];
									$out_username[$d] = $row['user_name'];
									$out_userlogin[$d] = $row['user_login'] ? "(".$row['user_login'].")" :
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

						$oText = str_replace("'", "\'", CONTENT_ADMIN_CAT_LAN_41);
						$popuphelp = $aa -> popupHelp($oText, "", "", "");
						$caption = CONTENT_ADMIN_CAT_LAN_30;
						$ns -> tablerender($caption." ".$popuphelp, $text);
		}


		function show_cat_options(){
						global $sql, $ns, $rs, $aa, $content_pref, $content_cat_icon_path_large, $content_cat_icon_path_small, $plugintable;
						global $type, $type_id, $action, $sub_action, $id, $fl, $stylespacer, $td1;
						global $TOPIC_ROW;

						$TOPIC_TABLE_END = "
						".$this->pref_submit()."
						</table>
						</div>
						";

						$TOPIC_TITLE_ROW = "<tr><td colspan='2' class='fcaption'>{TOPIC_CAPTION}</td></tr>";
						$TOPIC_HELP_ROW = "<tr><td colspan='2' class='forumheader'>{TOPIC_HELP}</td></tr>";
						$TOPIC_TABLE_START = "";


						if(!is_object($sql)){ $sql = new db; }
						$parentdetails = $aa -> getParent();

						if(!is_numeric($id) || $type_id != $id){
							header("location:".e_SELF."?".$type.".".$type_id.".cat"); exit;
						}

						if(!$sql -> db_Select($plugintable, "content_heading", "content_id='".$id."' AND content_parent = '0' ")){
								header("location:".e_SELF."?".$type.".".$type_id.".cat"); exit;
						}else{
								while($row = $sql -> db_Fetch()){
									$caption = CONTENT_ADMIN_OPT_LAN_0." : ".$row['content_heading'];
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
						var hideidcolor=\"creationdiv\";
						function setactive(showidcolor){
							if (hideidcolor!=showidcolor){
								document.getElementById(showidcolor).src = \"".e_IMAGE."admin_images/arrow_over_16.png\";
								document.getElementById(hideidcolor).src = \"".e_IMAGE."admin_images/arrow_16.png\";
								hideidcolor = showidcolor;						
							}
						}
						//-->
						</script>";


						$text .= "
						<div style='text-align:center'>
						<form method='post' action='".e_SELF."?".e_QUERY."'>\n

						<div id='creation' style='text-align:center'>
						<table style='".ADMIN_WIDTH."' class='fborder'>";						
						
						$TOPIC_CAPTION = CONTENT_ADMIN_OPT_LAN_1;
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_TITLE_ROW);

						//content_admin_sections
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_2;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_3;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = "
						".$rs -> form_checkbox("content_admin_icon_{$id}", 1, ($content_pref["content_admin_icon_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_4."<br />
						".$rs -> form_checkbox("content_admin_attach_{$id}", 1, ($content_pref["content_admin_attach_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_5."<br />
						".$rs -> form_checkbox("content_admin_images_{$id}", 1, ($content_pref["content_admin_images_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_6."<br />
						".$rs -> form_checkbox("content_admin_comment_{$id}", 1, ($content_pref["content_admin_comment_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_7."<br />
						".$rs -> form_checkbox("content_admin_rating_{$id}", 1, ($content_pref["content_admin_rating_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_8."<br />
						".$rs -> form_checkbox("content_admin_score_{$id}", 1, ($content_pref["content_admin_score_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_9."<br />
						".$rs -> form_checkbox("content_admin_pe_{$id}", 1, ($content_pref["content_admin_pe_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_10."<br />
						".$rs -> form_checkbox("content_admin_visibility_{$id}", 1, ($content_pref["content_admin_visibility_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_11."<br />
						".$rs -> form_checkbox("content_admin_meta_{$id}", 1, ($content_pref["content_admin_meta_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_12."<br />
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_admin_custom_number_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_13;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_14;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_15;
						$TOPIC_FIELD = "
						".$rs -> form_select_open("content_admin_custom_number_{$id}");
						for($i=0;$i<11;$i++){
							$TOPIC_FIELD .= $rs -> form_option($i, ($content_pref["content_admin_custom_number_{$id}"] == $i ? "1" : "0"), $i);
						}
						$TOPIC_FIELD .= $rs -> form_select_close();
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_admin_images_number_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_16;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_17;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_18;
						$TOPIC_FIELD = "
						".$rs -> form_select_open("content_admin_images_number_{$id}");
						$content_pref["content_admin_images_number_{$id}"] = ($content_pref["content_admin_images_number_{$id}"] ? $content_pref["content_admin_images_number_{$id}"] : "10");
						for($i=1;$i<16;$i++){
							$k=$i*2;
							$TOPIC_FIELD .= $rs -> form_option($k, ($content_pref["content_admin_images_number_{$id}"] == $k ? "1" : "0"), $k);
						}
						$TOPIC_FIELD .= $rs -> form_select_close();
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_admin_files_number_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_19;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_20;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_21;
						$TOPIC_FIELD = "
						".$rs -> form_select_open("content_admin_files_number_{$id}");
						$content_pref["content_admin_files_number_{$id}"] = ($content_pref["content_admin_files_number_{$id}"] ? $content_pref["content_admin_files_number_{$id}"] : "1");
						for($i=1;$i<6;$i++){
							$TOPIC_FIELD .= $rs -> form_option($i, ($content_pref["content_admin_files_number_{$id}"] == $i ? "1" : "0"), $i);
						}
						$TOPIC_FIELD .= $rs -> form_select_close();
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						$text .= $TOPIC_TABLE_END;

						$text .= "
						<div id='submission' style='display:none; text-align:center'>
						<table style='".ADMIN_WIDTH."' class='fborder'>";

						$TOPIC_CAPTION = CONTENT_ADMIN_OPT_LAN_22;
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_TITLE_ROW);
						
						//content_submit_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_23;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_24;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_25;
						$TOPIC_FIELD = "
						".$rs -> form_radio("content_submit_{$id}", "1", ($content_pref["content_submit_{$id}"] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
						".$rs -> form_radio("content_submit_{$id}", "0", ($content_pref["content_submit_{$id}"] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_submit_class_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_26;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_27;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = r_userclass("content_submit_class_{$id}", $content_pref["content_submit_class_{$id}"], "CLASSES");
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_submit_directpost_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_28;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_29;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_30;
						$TOPIC_FIELD = "
						".$rs -> form_radio("content_submit_directpost_{$id}", "1", ($content_pref["content_submit_directpost_{$id}"] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
						".$rs -> form_radio("content_submit_directpost_{$id}", "0", ($content_pref["content_submit_directpost_{$id}"] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_submit_sections
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_2;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_31;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = "
						".$rs -> form_checkbox("content_submit_icon_{$id}", 1, ($content_pref["content_submit_icon_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_4."<br />
						".$rs -> form_checkbox("content_submit_attach_{$id}", 1, ($content_pref["content_submit_attach_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_5."<br />
						".$rs -> form_checkbox("content_submit_images_{$id}", 1, ($content_pref["content_submit_images_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_6."<br />
						".$rs -> form_checkbox("content_submit_comment_{$id}", 1, ($content_pref["content_submit_comment_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_7."<br />
						".$rs -> form_checkbox("content_submit_rating_{$id}", 1, ($content_pref["content_submit_rating_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_8."<br />
						".$rs -> form_checkbox("content_submit_score_{$id}", 1, ($content_pref["content_submit_score_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_9."<br />
						".$rs -> form_checkbox("content_submit_pe_{$id}", 1, ($content_pref["content_submit_pe_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_10."<br />
						".$rs -> form_checkbox("content_submit_visibility_{$id}", 1, ($content_pref["content_submit_visibility_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_11."<br />
						".$rs -> form_checkbox("content_submit_meta_{$id}", 1, ($content_pref["content_submit_meta_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_12."<br />
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_submit_custom_number_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_13;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_14;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_15;
						$TOPIC_FIELD = "
						".$rs -> form_select_open("content_submit_custom_number_{$id}");
						for($i=0;$i<11;$i++){
							$TOPIC_FIELD .= $rs -> form_option($i, ($content_pref["content_submit_custom_number_{$id}"] == $i ? "1" : "0"), $i);
						}
						$TOPIC_FIELD .= $rs -> form_select_close();
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_submit_images_number_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_16;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_17;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_18;
						$TOPIC_FIELD = "
						".$rs -> form_select_open("content_submit_images_number_{$id}");
						$content_pref["content_submit_images_number_{$id}"] = ($content_pref["content_submit_images_number_{$id}"] ? $content_pref["content_submit_images_number_{$id}"] : "10");
						for($i=1;$i<16;$i++){
							$k=$i*2;
							$TOPIC_FIELD .= $rs -> form_option($k, ($content_pref["content_submit_images_number_{$id}"] == $k ? "1" : "0"), $k);
						}
						$TOPIC_FIELD .= $rs -> form_select_close();
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_submit_files_number_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_19;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_20;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_21;
						$TOPIC_FIELD = "
						".$rs -> form_select_open("content_submit_files_number_{$id}");
						$content_pref["content_submit_files_number_{$id}"] = ($content_pref["content_submit_files_number_{$id}"] ? $content_pref["content_submit_files_number_{$id}"] : "1");
						for($i=1;$i<6;$i++){
							$TOPIC_FIELD .= $rs -> form_option($i, ($content_pref["content_submit_files_number_{$id}"] == $i ? "1" : "0"), $i);
						}
						$TOPIC_FIELD .= $rs -> form_select_close();
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						$text .= $TOPIC_TABLE_END;

						$text .= "
						<div id='paththeme' style='display:none; text-align:center'>
						<table style='".ADMIN_WIDTH."' class='fborder'>";

						$TOPIC_CAPTION = CONTENT_ADMIN_OPT_LAN_33;
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_TITLE_ROW);

						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_34;
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_HELP_ROW);

						//content_cat_icon_path_large_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_35;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_36;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = $rs -> form_text("content_cat_icon_path_large_{$id}", 60, $content_pref["content_cat_icon_path_large_{$id}"], 100);
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_cat_icon_path_small_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_37;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_38;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = $rs -> form_text("content_cat_icon_path_small_{$id}", 60, $content_pref["content_cat_icon_path_small_{$id}"], 100);
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_icon_path_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_39;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_40;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = $rs -> form_text("content_icon_path_{$id}", 60, $content_pref["content_icon_path_{$id}"], 100);
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_image_path_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_41;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_42;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = $rs -> form_text("content_image_path_{$id}", 60, $content_pref["content_image_path_{$id}"], 100);
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_file_path_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_43;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_44;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = $rs -> form_text("content_file_path_{$id}", 60, $content_pref["content_file_path_{$id}"], 100);
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_theme_
						$dirlist = $fl->get_dirs(e_PLUGIN."content/templates/");
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_45;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_46;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_47;
						$TOPIC_FIELD = "
						".$rs -> form_select_open("content_theme_{$id}");
						$counter = 0;
						foreach($dirlist as $themedir){
							$TOPIC_FIELD .= $rs -> form_option($themedir, ($themedir == $content_pref["content_theme_{$id}"] ? "1" : "0"), $themedir);
							$counter++;
						}
						$TOPIC_FIELD .= $rs -> form_select_close();
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						$text .= $TOPIC_TABLE_END;

						$text .= "
						<div id='general' style='display:none; text-align:center'>
						<table style='".ADMIN_WIDTH."' class='fborder'>";

						$TOPIC_CAPTION = CONTENT_ADMIN_OPT_LAN_48;
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_TITLE_ROW);

						//content_log_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_49;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_50;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_51;
						$TOPIC_FIELD = "
						".$rs -> form_radio("content_log_{$id}", "1", ($content_pref["content_log_{$id}"] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
						".$rs -> form_radio("content_log_{$id}", "0", ($content_pref["content_log_{$id}"] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_blank_icon_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_52;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_53;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = "
						".$rs -> form_radio("content_blank_icon_{$id}", "1", ($content_pref["content_blank_icon_{$id}"] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
						".$rs -> form_radio("content_blank_icon_{$id}", "0", ($content_pref["content_blank_icon_{$id}"] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_blank_caticon_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_54;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_55;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = "
						".$rs -> form_radio("content_blank_caticon_{$id}", "1", ($content_pref["content_blank_caticon_{$id}"] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
						".$rs -> form_radio("content_blank_caticon_{$id}", "0", ($content_pref["content_blank_caticon_{$id}"] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_breadcrumb_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_56;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_57;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = "
						".$rs -> form_radio("content_breadcrumb_{$id}", "1", ($content_pref["content_breadcrumb_{$id}"] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
						".$rs -> form_radio("content_breadcrumb_{$id}", "0", ($content_pref["content_breadcrumb_{$id}"] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_breadcrumb_seperator
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_58;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_59;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_60;
						$TOPIC_FIELD = $rs -> form_text("content_breadcrumb_seperator{$id}", 10, $content_pref["content_breadcrumb_seperator{$id}"], 3);
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_breadcrumb_rendertype_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_61;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_62;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_63;
						$TOPIC_FIELD = "
						".$rs -> form_select_open("content_breadcrumb_rendertype_{$id}")."
						".$rs -> form_option(CONTENT_ADMIN_OPT_LAN_64, ($content_pref["content_breadcrumb_rendertype_{$id}"] == "1" ? "1" : "0"), "1")."
						".$rs -> form_option(CONTENT_ADMIN_OPT_LAN_65, ($content_pref["content_breadcrumb_rendertype_{$id}"] == "2" ? "1" : "0"), "2")."
						".$rs -> form_option(CONTENT_ADMIN_OPT_LAN_66, ($content_pref["content_breadcrumb_rendertype_{$id}"] == "3" ? "1" : "0"), "3")."
						".$rs -> form_select_close();
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_searchmenu_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_67;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_68;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_69;
						$TOPIC_FIELD = "
						".$rs -> form_radio("content_searchmenu_{$id}", "1", ($content_pref["content_searchmenu_{$id}"] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
						".$rs -> form_radio("content_searchmenu_{$id}", "0", ($content_pref["content_searchmenu_{$id}"] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);
						
						$text .= $TOPIC_TABLE_END;

						$text .= "
						<div id='listpages' style='display:none; text-align:center'>
						<table style='".ADMIN_WIDTH."' class='fborder'>";

						$TOPIC_CAPTION = CONTENT_ADMIN_OPT_LAN_70;
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_TITLE_ROW);

						//content_list sections
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_2;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_71;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = "
						".$rs -> form_checkbox("content_list_subheading_{$id}", 1, ($content_pref["content_list_subheading_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_72."<br />
						".$rs -> form_checkbox("content_list_summary_{$id}", 1, ($content_pref["content_list_summary_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_73."<br />
						".$rs -> form_checkbox("content_list_date_{$id}", 1, ($content_pref["content_list_date_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_74."<br />
						".$rs -> form_checkbox("content_list_authorname_{$id}", 1, ($content_pref["content_list_authorname_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_75."<br />
						".$rs -> form_checkbox("content_list_authoremail_{$id}", 1, ($content_pref["content_list_authoremail_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_76."<br />
						".$rs -> form_checkbox("content_list_rating_{$id}", 1, ($content_pref["content_list_rating_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_77."<br />
						".$rs -> form_checkbox("content_list_peicon_{$id}", 1, ($content_pref["content_list_peicon_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_78."<br />
						".$rs -> form_checkbox("content_list_parent_{$id}", 1, ($content_pref["content_list_parent_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_79."<br />
						".$rs -> form_checkbox("content_list_refer_{$id}", 1, ($content_pref["content_list_refer_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_80."<br />
						".$rs -> form_checkbox("content_list_editicon_{$id}", 1, ($content_pref["content_list_editicon_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_204."<br />
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_list_subheading_char_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_81;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_82;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_83;
						$TOPIC_FIELD = $rs -> form_text("content_list_subheading_char_{$id}", 10, $content_pref["content_list_subheading_char_{$id}"], 3);
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_list_subheading_post_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_84;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_85;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = $rs -> form_text("content_list_subheading_post_{$id}", 10, $content_pref["content_list_subheading_post_{$id}"], 20);
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_list_summary_char_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_86;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_87;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_88;
						$TOPIC_FIELD = $rs -> form_text("content_list_summary_char_{$id}", 10, $content_pref["content_list_summary_char_{$id}"], 3);
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_list_summary_post_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_89;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_90;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = $rs -> form_text("content_list_summary_post_{$id}", 10, $content_pref["content_list_summary_post_{$id}"], 20);
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_list_authoremail_nonmember_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_91;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_92;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_93;
						$TOPIC_FIELD = "
						".$rs -> form_radio("content_list_authoremail_nonmember_{$id}", "1", ($content_pref["content_list_authoremail_nonmember_{$id}"] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
						".$rs -> form_radio("content_list_authoremail_nonmember_{$id}", "0", ($content_pref["content_list_authoremail_nonmember_{$id}"] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_nextprev_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_94;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_95;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_96;
						$TOPIC_FIELD = "
						".$rs -> form_radio("content_nextprev_{$id}", "1", ($content_pref["content_nextprev_{$id}"] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
						".$rs -> form_radio("content_nextprev_{$id}", "0", ($content_pref["content_nextprev_{$id}"] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_nextprev_number_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_97;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_98;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_99;
						$TOPIC_FIELD = $rs -> form_select_open("content_nextprev_number_{$id}");
						for($i=1;$i<21;$i++){
							$TOPIC_FIELD .= $rs -> form_option($i, ($content_pref["content_nextprev_number_{$id}"] == $i ? "1" : "0"), $i);
						}
						$TOPIC_FIELD .= $rs -> form_select_close();
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_list_peicon_all_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_100;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_101;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_102;
						$TOPIC_FIELD = "
						".$rs -> form_radio("content_list_peicon_all_{$id}", "1", ($content_pref["content_list_peicon_all_{$id}"] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
						".$rs -> form_radio("content_list_peicon_all_{$id}", "0", ($content_pref["content_list_peicon_all_{$id}"] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_list_rating_all_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_103;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_104;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_105;
						$TOPIC_FIELD = "
						".$rs -> form_radio("content_list_rating_all_{$id}", "1", ($content_pref["content_list_rating_all_{$id}"] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
						".$rs -> form_radio("content_list_rating_all_{$id}", "0", ($content_pref["content_list_rating_all_{$id}"] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_defaultorder_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_106;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_107;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_108;
						$TOPIC_FIELD = "
						".$rs -> form_select_open("content_defaultorder_{$id}")."
						".$rs -> form_option("heading_ASC", ($content_pref["content_defaultorder_{$id}"] == "orderaheading" ? "1" : "0"), "orderaheading")."
						".$rs -> form_option("heading_DESC", ($content_pref["content_defaultorder_{$id}"] == "orderdheading" ? "1" : "0"), "orderdheading")."
						".$rs -> form_option("date_ASC", ($content_pref["content_defaultorder_{$id}"] == "orderadate" ? "1" : "0"), "orderadate")."
						".$rs -> form_option("date_DESC", ($content_pref["content_defaultorder_{$id}"] == "orderddate" ? "1" : "0"), "orderddate")."
						".$rs -> form_option("refer_ASC", ($content_pref["content_defaultorder_{$id}"] == "orderarefer" ? "1" : "0"), "orderarefer")."
						".$rs -> form_option("refer_DESC", ($content_pref["content_defaultorder_{$id}"] == "orderdrefer" ? "1" : "0"), "orderdrefer")."
						".$rs -> form_option("parent_ASC", ($content_pref["content_defaultorder_{$id}"] == "orderaparent" ? "1" : "0"), "orderaparent")."
						".$rs -> form_option("parent_DESC", ($content_pref["content_defaultorder_{$id}"] == "orderdparent" ? "1" : "0"), "orderdparent")."
						".$rs -> form_option("order_ASC", ($content_pref["content_defaultorder_{$id}"] == "orderaorder" ? "1" : "0"), "orderaorder")."
						".$rs -> form_option("order_DESC", ($content_pref["content_defaultorder_{$id}"] == "orderdorder" ? "1" : "0"), "orderdorder")."
						".$rs -> form_select_close()."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						$text .= $TOPIC_TABLE_END;

						$text .= "
						<div id='catpages' style='display:none; text-align:center'>
						<table style='".ADMIN_WIDTH."' class='fborder'>";

						$TOPIC_CAPTION = CONTENT_ADMIN_OPT_LAN_119;
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_TITLE_ROW);

						//content_cat_showparent_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_120;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_121;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = "
						".$rs -> form_radio("content_cat_showparent_{$id}", "1", ($content_pref["content_cat_showparent_{$id}"] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
						".$rs -> form_radio("content_cat_showparent_{$id}", "0", ($content_pref["content_cat_showparent_{$id}"] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_cat_showparentsub_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_122;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_123;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_124;
						$TOPIC_FIELD = "
						".$rs -> form_radio("content_cat_showparentsub_{$id}", "1", ($content_pref["content_cat_showparentsub_{$id}"] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
						".$rs -> form_radio("content_cat_showparentsub_{$id}", "0", ($content_pref["content_cat_showparentsub_{$id}"] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_cat_listtype_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_125;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_126;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_127;
						$TOPIC_FIELD = "
						".$rs -> form_radio("content_cat_listtype_{$id}", "1", ($content_pref["content_cat_listtype_{$id}"] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
						".$rs -> form_radio("content_cat_listtype_{$id}", "0", ($content_pref["content_cat_listtype_{$id}"] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_cat_menuorder_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_128;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_129;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_130;
						$TOPIC_FIELD = "
						".$rs -> form_select_open("content_cat_menuorder_{$id}")."
						".$rs -> form_option(CONTENT_ADMIN_OPT_LAN_131, ($content_pref["content_cat_menuorder_{$id}"] == "1" ? "1" : "0"), "1")."
						".$rs -> form_option(CONTENT_ADMIN_OPT_LAN_132, ($content_pref["content_cat_menuorder_{$id}"] == "2" ? "1" : "0"), "2")."
						".$rs -> form_select_close()."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_cat_rendertype_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_133;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_134;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_135;
						$TOPIC_FIELD = "
						".$rs -> form_select_open("content_cat_rendertype_{$id}")."
						".$rs -> form_option(CONTENT_ADMIN_OPT_LAN_136, ($content_pref["content_cat_rendertype_{$id}"] == "1" ? "1" : "0"), "1")."
						".$rs -> form_option(CONTENT_ADMIN_OPT_LAN_137, ($content_pref["content_cat_rendertype_{$id}"] == "2" ? "1" : "0"), "2")."
						".$rs -> form_select_close()."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						$text .= $TOPIC_TABLE_END;

						$text .= "
						<div id='contentpages' style='display:none; text-align:center'>
						<table style='".ADMIN_WIDTH."' class='fborder'>";

						$TOPIC_CAPTION = CONTENT_ADMIN_OPT_LAN_138;
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_TITLE_ROW);

						//content_content_sections
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_2;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_139;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = "
						".$rs -> form_checkbox("content_content_subheading_{$id}", 1, ($content_pref["content_content_subheading_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_72."<br />
						".$rs -> form_checkbox("content_content_summary_{$id}", 1, ($content_pref["content_content_summary_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_73."<br />
						".$rs -> form_checkbox("content_content_date_{$id}", 1, ($content_pref["content_content_date_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_74."<br />
						".$rs -> form_checkbox("content_content_authorname_{$id}", 1, ($content_pref["content_content_authorname_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_75."<br />
						".$rs -> form_checkbox("content_content_authoremail_{$id}", 1, ($content_pref["content_content_authoremail_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_76."<br />
						".$rs -> form_checkbox("content_content_rating_{$id}", 1, ($content_pref["content_content_rating_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_77."<br />
						".$rs -> form_checkbox("content_content_peicon_{$id}", 1, ($content_pref["content_content_peicon_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_78."<br />
						".$rs -> form_checkbox("content_content_refer_{$id}", 1, ($content_pref["content_content_refer_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_80."<br />
						".$rs -> form_checkbox("content_content_editicon_{$id}", 1, ($content_pref["content_content_editicon_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_204."<br />
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_content_authoremail_nonmember_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_91;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_92;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_93;
						$TOPIC_FIELD = "
						".$rs -> form_radio("content_content_authoremail_nonmember_{$id}", "1", ($content_pref["content_content_authoremail_nonmember_{$id}"] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
						".$rs -> form_radio("content_content_authoremail_nonmember_{$id}", "0", ($content_pref["content_content_authoremail_nonmember_{$id}"] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_content_peicon_all_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_100;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_101;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_102;
						$TOPIC_FIELD = "
						".$rs -> form_radio("content_content_peicon_all_{$id}", "1", ($content_pref["content_content_peicon_all_{$id}"] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
						".$rs -> form_radio("content_content_peicon_all_{$id}", "0", ($content_pref["content_content_peicon_all_{$id}"] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_content_rating_all_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_103;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_104;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_105;
						$TOPIC_FIELD = "
						".$rs -> form_radio("content_content_rating_all_{$id}", "1", ($content_pref["content_content_rating_all_{$id}"] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
						".$rs -> form_radio("content_content_rating_all_{$id}", "0", ($content_pref["content_content_rating_all_{$id}"] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_content_comment_all_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_201;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_202;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_203;
						$TOPIC_FIELD = "
						".$rs -> form_radio("content_content_comment_all_{$id}", "1", ($content_pref["content_content_comment_all_{$id}"] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
						".$rs -> form_radio("content_content_comment_all_{$id}", "0", ($content_pref["content_content_comment_all_{$id}"] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						$text .= $TOPIC_TABLE_END;

						$text .= "
						<div id='menu' style='display:none; text-align:center'>
						<table style='".ADMIN_WIDTH."' class='fborder'>";

						$TOPIC_CAPTION = CONTENT_ADMIN_OPT_LAN_140;
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_TITLE_ROW);

						//content_menu_caption_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_141;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_142;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = $rs -> form_text("content_menu_caption_{$id}", 15, $content_pref["content_menu_caption_{$id}"], 50);
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_menu_search_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_143;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_144;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = "
						".$rs -> form_radio("content_menu_search_{$id}", "1", ($content_pref["content_menu_search_{$id}"] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
						".$rs -> form_radio("content_menu_search_{$id}", "0", ($content_pref["content_menu_search_{$id}"] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_menu_sort_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_145;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_146;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = "
						".$rs -> form_radio("content_menu_sort_{$id}", "1", ($content_pref["content_menu_sort_{$id}"] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
						".$rs -> form_radio("content_menu_sort_{$id}", "0", ($content_pref["content_menu_sort_{$id}"] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						$text .= $TOPIC_ROW_SPACER;

						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_147;
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_HELP_ROW);

						//content_menu_viewallcat_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_148;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_149;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = "
						".$rs -> form_radio("content_menu_viewallcat_{$id}", "1", ($content_pref["content_menu_viewallcat_{$id}"] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
						".$rs -> form_radio("content_menu_viewallcat_{$id}", "0", ($content_pref["content_menu_viewallcat_{$id}"] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_menu_viewallauthor_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_150;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_151;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = "
						".$rs -> form_radio("content_menu_viewallauthor_{$id}", "1", ($content_pref["content_menu_viewallauthor_{$id}"] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
						".$rs -> form_radio("content_menu_viewallauthor_{$id}", "0", ($content_pref["content_menu_viewallauthor_{$id}"] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_menu_viewtoprated_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_152;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_153;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = "
						".$rs -> form_radio("content_menu_viewtoprated_{$id}", "1", ($content_pref["content_menu_viewtoprated_{$id}"] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
						".$rs -> form_radio("content_menu_viewtoprated_{$id}", "0", ($content_pref["content_menu_viewtoprated_{$id}"] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_menu_viewrecent_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_154;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_155;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = "
						".$rs -> form_radio("content_menu_viewrecent_{$id}", "1", ($content_pref["content_menu_viewrecent_{$id}"] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
						".$rs -> form_radio("content_menu_viewrecent_{$id}", "0", ($content_pref["content_menu_viewrecent_{$id}"] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_menu_viewsubmit_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_156;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_157;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = "
						".$rs -> form_radio("content_menu_viewsubmit_{$id}", "1", ($content_pref["content_menu_viewsubmit_{$id}"] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
						".$rs -> form_radio("content_menu_viewsubmit_{$id}", "0", ($content_pref["content_menu_viewsubmit_{$id}"] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_menu_viewicon_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_158;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_159;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_160;
						$TOPIC_FIELD = $rs -> form_select_open("content_menu_viewicon_{$id}")."
						".$rs -> form_option("none", ($content_pref["content_menu_viewicon_{$id}"] == "0" ? "1" : "0"), 0)."
						".$rs -> form_option("bullet", ($content_pref["content_menu_viewicon_{$id}"] == "1" ? "1" : "0"), 1)."
						".$rs -> form_option("middot", ($content_pref["content_menu_viewicon_{$id}"] == "2" ? "1" : "0"), 2)."
						".$rs -> form_option("white bullet", ($content_pref["content_menu_viewicon_{$id}"] == "3" ? "1" : "0"), 3)."
						".$rs -> form_option("arrow", ($content_pref["content_menu_viewicon_{$id}"] == "4" ? "1" : "0"), 4)."
						".$rs -> form_select_close();
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						$text .= $TOPIC_ROW_SPACER;

						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_161;
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_HELP_ROW);

						//content_menu_cat_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_162;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_163;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = "
						".$rs -> form_radio("content_menu_cat_{$id}", "1", ($content_pref["content_menu_cat_{$id}"] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
						".$rs -> form_radio("content_menu_cat_{$id}", "0", ($content_pref["content_menu_cat_{$id}"] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_menu_cat_number_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_164;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_165;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = "
						".$rs -> form_radio("content_menu_cat_number_{$id}", "1", ($content_pref["content_menu_cat_number_{$id}"] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
						".$rs -> form_radio("content_menu_cat_number_{$id}", "0", ($content_pref["content_menu_cat_number_{$id}"] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_menu_cat_icon_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_166;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_159;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_167;
						$TOPIC_FIELD = "
						".$rs -> form_select_open("content_menu_cat_icon_{$id}")."
						".$rs -> form_option(CONTENT_ADMIN_OPT_LAN_168, ($content_pref["content_menu_cat_icon_{$id}"] == "0" ? "1" : "0"), 0)."
						".$rs -> form_option(CONTENT_ADMIN_OPT_LAN_169, ($content_pref["content_menu_cat_icon_{$id}"] == "1" ? "1" : "0"), 1)."
						".$rs -> form_option(CONTENT_ADMIN_OPT_LAN_170, ($content_pref["content_menu_cat_icon_{$id}"] == "2" ? "1" : "0"), 2)."
						".$rs -> form_option(CONTENT_ADMIN_OPT_LAN_171, ($content_pref["content_menu_cat_icon_{$id}"] == "3" ? "1" : "0"), 3)."
						".$rs -> form_option(CONTENT_ADMIN_OPT_LAN_172, ($content_pref["content_menu_cat_icon_{$id}"] == "4" ? "1" : "0"), 4)."
						".$rs -> form_option(CONTENT_ADMIN_OPT_LAN_173, ($content_pref["content_menu_cat_icon_{$id}"] == "5" ? "1" : "0"), 5)."
						".$rs -> form_select_close()."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);						
					
						$text .= $TOPIC_ROW_SPACER;

						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_174;
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_HELP_ROW);

						//content_menu_recent_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_175;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_176;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = "
						".$rs -> form_radio("content_menu_recent_{$id}", "1", ($content_pref["content_menu_recent_{$id}"] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
						".$rs -> form_radio("content_menu_recent_{$id}", "0", ($content_pref["content_menu_recent_{$id}"] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_menu_recent_caption_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_177;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_178;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = $rs -> form_text("content_menu_recent_caption_{$id}", 15, $content_pref["content_menu_recent_caption_{$id}"], 50);
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_menu_recent_number_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_179;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_180;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = $rs -> form_select_open("content_menu_recent_number_{$id}");
						for($i=1;$i<15;$i++){
							$TOPIC_FIELD .= $rs -> form_option($i, ($content_pref["content_menu_recent_number_{$id}"] == $i ? "1" : "0"), $i);
						}
						$TOPIC_FIELD .= $rs -> form_select_close();
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_menu_recent_date_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_181;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_182;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = "
						".$rs -> form_radio("content_menu_recent_date_{$id}", "1", ($content_pref["content_menu_recent_date_{$id}"] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
						".$rs -> form_radio("content_menu_recent_date_{$id}", "0", ($content_pref["content_menu_recent_date_{$id}"] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_menu_recent_author_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_183;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_184;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = "
						".$rs -> form_radio("content_menu_recent_author_{$id}", "1", ($content_pref["content_menu_recent_author_{$id}"] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
						".$rs -> form_radio("content_menu_recent_author_{$id}", "0", ($content_pref["content_menu_recent_author_{$id}"] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_menu_recent_subheading_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_185;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_186;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = "
						".$rs -> form_radio("content_menu_recent_subheading_{$id}", "1", ($content_pref["content_menu_recent_subheading_{$id}"] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
						".$rs -> form_radio("content_menu_recent_subheading_{$id}", "0", ($content_pref["content_menu_recent_subheading_{$id}"] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_menu_recent_subheading_char_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_187;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_188;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_189;
						$TOPIC_FIELD = $rs -> form_text("content_menu_recent_subheading_char_{$id}", 10, $content_pref["content_menu_recent_subheading_char_{$id}"], 3);
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_menu_recent_subheading_post_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_190;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_191;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = $rs -> form_text("content_menu_recent_subheading_post_{$id}", 10, $content_pref["content_menu_recent_subheading_post_{$id}"], 30);
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_menu_recent_icon_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_192;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_159;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_193;
						$TOPIC_FIELD = "
						".$rs -> form_select_open("content_menu_recent_icon_{$id}")."
						".$rs -> form_option(CONTENT_ADMIN_OPT_LAN_168, ($content_pref["content_menu_recent_icon_{$id}"] == "0" ? "1" : "0"), 0)."
						".$rs -> form_option(CONTENT_ADMIN_OPT_LAN_169, ($content_pref["content_menu_recent_icon_{$id}"] == "1" ? "1" : "0"), 1)."
						".$rs -> form_option(CONTENT_ADMIN_OPT_LAN_170, ($content_pref["content_menu_recent_icon_{$id}"] == "2" ? "1" : "0"), 2)."
						".$rs -> form_option(CONTENT_ADMIN_OPT_LAN_171, ($content_pref["content_menu_recent_icon_{$id}"] == "3" ? "1" : "0"), 3)."
						".$rs -> form_option(CONTENT_ADMIN_OPT_LAN_172, ($content_pref["content_menu_recent_icon_{$id}"] == "4" ? "1" : "0"), 4)."
						".$rs -> form_option(CONTENT_ADMIN_OPT_LAN_194, ($content_pref["content_menu_recent_icon_{$id}"] == "5" ? "1" : "0"), 5)."
						".$rs -> form_select_close()."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_menu_recent_icon_width_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_195;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_196;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_197;
						$TOPIC_FIELD = $rs -> form_text("content_menu_recent_icon_width_{$id}", 10, $content_pref["content_menu_recent_icon_width_{$id}"], 3);
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						$text .= $TOPIC_TABLE_END;

						$text .= "
						</form>
						</div>";

						$oText = str_replace("'", "\'", CONTENT_ADMIN_HELP_LAN_12);
						//popupHelp($text, $image="", $width="500", $title="")
						$popuphelp = $aa -> popupHelp($oText, "", "", "");
						$ns -> tablerender($caption." ".$popuphelp, $text);
		}

		function pref_submit() {
			global $id;
			$text = "
			<tr>
			<td colspan='2' style='text-align:center' class='forumheader'>
				<input class='button' type='submit' name='updateoptions' value='".CONTENT_ADMIN_OPT_LAN_200."' /> <input type='hidden' name='options_type' value='".$id."' />
			</td>
			</tr>";

			return $text;
		}

}

?>