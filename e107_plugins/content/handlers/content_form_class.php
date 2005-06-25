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
|		$Revision: 1.67 $
|		$Date: 2005-06-25 12:15:25 $
|		$Author: lisa_ $
+---------------------------------------------------------------+
*/

$plugindir		= e_PLUGIN."content/";
$plugintable	= "pcontent";		//name of the table used in this plugin (never remove this, as it's being used throughout the plugin !!)
$datequery		= " AND (content_datestamp=0 || content_datestamp < ".time().") AND (content_enddate=0 || content_enddate>".time().") ";

$months = array(CONTENT_ADMIN_DATE_LAN_0, CONTENT_ADMIN_DATE_LAN_1, CONTENT_ADMIN_DATE_LAN_2, CONTENT_ADMIN_DATE_LAN_3, CONTENT_ADMIN_DATE_LAN_4, CONTENT_ADMIN_DATE_LAN_5, CONTENT_ADMIN_DATE_LAN_6, CONTENT_ADMIN_DATE_LAN_7, CONTENT_ADMIN_DATE_LAN_8, CONTENT_ADMIN_DATE_LAN_9, CONTENT_ADMIN_DATE_LAN_10, CONTENT_ADMIN_DATE_LAN_11);

if (!defined('ADMIN_WIDTH')) { define("ADMIN_WIDTH", "width:98%;"); }

$stylespacer = "style='border:0; height:20px;'";

//only used in admin pages, for normal rows (+ in content_submit.php creation form)
$TOPIC_ROW_NOEXPAND = "
<tr>
	<td class='forumheader3' style='width:20%; white-space:nowrap; vertical-align:top;'>{TOPIC_TOPIC}</td>
	<td class='forumheader3'>{TOPIC_FIELD}</td>
</tr>
";
//only used in admin pages, for expanding rows (+ in content_submit.php creation form)
$TOPIC_ROW = "
<tr>
	<td class='forumheader3' style='width:20%; white-space:nowrap; vertical-align:top;'>{TOPIC_TOPIC}</td>
	<td class='forumheader3' style='vertical-align:top;'>
		<a style='cursor: pointer; cursor: hand' onclick='expandit(this);'>{TOPIC_HEADING}</a>
		<div style='display: none;'>
			<div class='smalltext'>{TOPIC_HELP}</div><br />
			{TOPIC_FIELD}
		</div>
	</td>
</tr>";

//only used in admin pages, for a spacer row
//$TOPIC_ROW_SPACER = "<tr><td $stylespacer colspan='2'></td></tr>";
$TOPIC_ROW_SPACER = "";

class contentform{

		function show_create_content($mode, $userid="", $username=""){
						global $qs, $sql, $ns, $rs, $aa, $fl, $tp, $plugintable, $plugindir, $pref, $eArrayStorage;
						global $message, $stylespacer, $TOPIC_ROW_SPACER, $TOPIC_ROW, $TOPIC_ROW_NOEXPAND;

						$months = array(CONTENT_ADMIN_DATE_LAN_0, CONTENT_ADMIN_DATE_LAN_1, CONTENT_ADMIN_DATE_LAN_2, CONTENT_ADMIN_DATE_LAN_3, CONTENT_ADMIN_DATE_LAN_4, CONTENT_ADMIN_DATE_LAN_5, CONTENT_ADMIN_DATE_LAN_6, CONTENT_ADMIN_DATE_LAN_7, CONTENT_ADMIN_DATE_LAN_8, CONTENT_ADMIN_DATE_LAN_9, CONTENT_ADMIN_DATE_LAN_10, CONTENT_ADMIN_DATE_LAN_11);

						//if create, first show category select (as preferences need to be loaded from the selected category)
						if( $qs[1] == "create" && !isset($qs[2]) ){
							$text = "
							<div style='text-align:center;'>
							".$rs -> form_open("post", e_SELF."?".e_QUERY."", "dataform", "", "enctype='multipart/form-data'")."
							<table style='".ADMIN_WIDTH."' class='fborder'>";

							$TOPIC_TOPIC = CONTENT_ADMIN_CAT_LAN_27;
							$TOPIC_FIELD = $aa -> ShowOptionCat().$rs->form_hidden("parent", "");
							$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW_NOEXPAND);
							$text .= "</table></form></div>";
							$caption = CONTENT_ADMIN_MAIN_LAN_2;
							$ns->tablerender($caption, $text);
							return;
						}

						if($mode == "submit"){
							$mainparent					= $aa -> getMainParent( $qs[2] );
							$array						= $aa -> getCategoryTree("", $mainparent, FALSE);
						}else{
							$array						= $aa -> getCategoryTree("", "", FALSE);
							$mainparent					= $aa -> getMainParent( (isset($qs[3]) && is_numeric($qs[3]) ? $qs[3] : $qs[2]) );
						}
						
						$content_pref					= $aa -> getContentPref($mainparent);
						$content_cat_icon_path_large	= $tp -> replaceConstants($content_pref["content_cat_icon_path_large_{$mainparent}"]);
						$content_cat_icon_path_small	= $tp -> replaceConstants($content_pref["content_cat_icon_path_small_{$mainparent}"]);
						$content_icon_path				= $tp -> replaceConstants($content_pref["content_icon_path_{$mainparent}"]);
						$content_image_path				= $tp -> replaceConstants($content_pref["content_image_path_{$mainparent}"]);
						$content_file_path				= $tp -> replaceConstants($content_pref["content_file_path_{$mainparent}"]);

						if(!is_object($sql)){ $sql = new db; }
						$sql2 = new db;
						if($mode == "contentmanager"){

							$personalmanagercheck = FALSE;
							if($sql -> db_Select($plugintable, "content_id, content_heading, content_parent, content_pref", " content_id='".$qs[2]."' ")){
								$rowpcm = $sql -> db_Fetch();
								
								if( isset($qs[1]) && $qs[1] == "edit" && is_numeric($qs[2]) ){
									$sql2 -> db_Select($plugintable, "content_id, content_heading, content_parent, content_pref", " content_id='".$rowpcm['content_parent']."' ");
									$rowpcm2 = $sql2 -> db_Fetch();
									$pcmcheckpref = $rowpcm2['content_pref'];
								
								}else{
									$pcmcheckpref = $rowpcm['content_pref'];
								}

								$pcmcontent_pref = $eArrayStorage->ReadArray($pcmcheckpref);

								//assign new preferences
								$pcm = explode(",", $pcmcontent_pref['content_manager_allowed']);
								if(in_array($userid, $pcm) || getperms("0")){
									$personalmanagercheck = TRUE;
								}
							}
							if($personalmanagercheck == TRUE){
								if($qs[1] == "edit"){
									//use user restriction (personal admin)
									if(isset($userid) && isset($username) ){
										$userquery = " AND (SUBSTRING_INDEX(content_author, '^', 1) = '".$userid."' OR SUBSTRING_INDEX(content_author, '^', 2) = '".$userid."^".$username."')";
									}else{
										$userquery = "";
									}
								}
								
							}else{
								header("location:".$plugindir."content_manager.php"); exit;
							}

						}else{
							$userquery = "";
						}

						//get preferences for submit page
						if($mode == "submit"){
							$checkicon			= (isset($content_pref["content_submit_icon_{$mainparent}"]) ? $content_pref["content_submit_icon_{$mainparent}"] : "");
							$checkattach		= (isset($content_pref["content_submit_attach_{$mainparent}"]) ? $content_pref["content_submit_attach_{$mainparent}"] : "");
							$checkattachnumber	= (isset($content_pref["content_submit_files_number_{$mainparent}"]) ? $content_pref["content_submit_files_number_{$mainparent}"] : "");
							$checkimages		= (isset($content_pref["content_submit_images_{$mainparent}"]) ? $content_pref["content_submit_images_{$mainparent}"] : "");
							$checkimagesnumber	= (isset($content_pref["content_submit_images_number_{$mainparent}"]) ? $content_pref["content_submit_images_number_{$mainparent}"] : "");
							$checkcomment		= (isset($content_pref["content_submit_comment_{$mainparent}"]) ? $content_pref["content_submit_comment_{$mainparent}"] : "");
							$checkrating		= (isset($content_pref["content_submit_rating_{$mainparent}"]) ? $content_pref["content_submit_rating_{$mainparent}"] : "");
							$checkscore			= (isset($content_pref["content_submit_score_{$mainparent}"]) ? $content_pref["content_submit_score_{$mainparent}"] : "");
							$checkpe			= (isset($content_pref["content_submit_pe_{$mainparent}"]) ? $content_pref["content_submit_pe_{$mainparent}"] : "");
							$checkvisibility	= (isset($content_pref["content_submit_visibility_{$mainparent}"]) ? $content_pref["content_submit_visibility_{$mainparent}"] : "");
							$checkmeta			= (isset($content_pref["content_submit_meta_{$mainparent}"]) ? $content_pref["content_submit_meta_{$mainparent}"] : "");
							$checkcustom		= (isset($content_pref["content_submit_customtags_{$mainparent}"]) ? $content_pref["content_submit_customtags_{$mainparent}"] : "");
							$checkcustomnumber	= (isset($content_pref["content_submit_custom_number_{$mainparent}"]) ? $content_pref["content_submit_custom_number_{$mainparent}"] : "");
							$checklayout		= (isset($content_pref["content_submit_layout_{$mainparent}"]) ? $content_pref["content_submit_layout_{$mainparent}"] : "");
							$checkpreset		= (isset($content_pref["content_submit_presettags_{$mainparent}"]) ? $content_pref["content_submit_presettags_{$mainparent}"] : "");
						
						//get preferences for admin area; posted submitted item.
						}elseif($mode == "sa"){

							//show all preferences from the submit options. if submit pref is not set, check if create prefs are set and use those
							$checkicon = (isset($content_pref["content_submit_icon_{$mainparent}"]) ? $content_pref["content_submit_icon_{$mainparent}"] : (isset($content_pref["content_admin_icon_{$mainparent}"]) ? $content_pref["content_admin_icon_{$mainparent}"] : ""));
							
							$checkattach = (isset($content_pref["content_submit_attach_{$mainparent}"]) ? $content_pref["content_submit_attach_{$mainparent}"] : (isset($content_pref["content_admin_attach_{$mainparent}"]) ? $content_pref["content_admin_attach_{$mainparent}"] : ""));
							
							$checkattachnumber = (isset($content_pref["content_submit_files_number_{$mainparent}"]) ? $content_pref["content_submit_files_number_{$mainparent}"] : (isset($content_pref["content_admin_files_number_{$mainparent}"]) ? $content_pref["content_admin_files_number_{$mainparent}"] : ""));
							
							$checkimages = (isset($content_pref["content_submit_images_{$mainparent}"]) ? $content_pref["content_submit_images_{$mainparent}"] : (isset($content_pref["content_admin_images_{$mainparent}"]) ? $content_pref["content_admin_images_{$mainparent}"] : ""));
							
							$checkimagesnumber = (isset($content_pref["content_submit_images_number_{$mainparent}"]) ? $content_pref["content_submit_images_number_{$mainparent}"] : (isset($content_pref["content_admin_images_number_{$mainparent}"]) ? $content_pref["content_admin_images_number_{$mainparent}"] : ""));
							
							$checkcomment = (isset($content_pref["content_submit_comment_{$mainparent}"]) ? $content_pref["content_submit_comment_{$mainparent}"] : (isset($content_pref["content_admin_comment_{$mainparent}"]) ? $content_pref["content_admin_comment_{$mainparent}"] : ""));
							
							$checkrating = (isset($content_pref["content_submit_rating_{$mainparent}"]) ? $content_pref["content_submit_rating_{$mainparent}"] : (isset($content_pref["content_admin_rating_{$mainparent}"]) ? $content_pref["content_admin_rating_{$mainparent}"] : ""));
							
							$checkscore = (isset($content_pref["content_submit_score_{$mainparent}"]) ? $content_pref["content_submit_score_{$mainparent}"] : (isset($content_pref["content_admin_score_{$mainparent}"]) ? $content_pref["content_admin_score_{$mainparent}"] : ""));
							
							$checkpe = (isset($content_pref["content_submit_pe_{$mainparent}"]) ? $content_pref["content_submit_pe_{$mainparent}"] : (isset($content_pref["content_admin_pe_{$mainparent}"]) ? $content_pref["content_admin_pe_{$mainparent}"] : ""));
							
							$checkvisibility = (isset($content_pref["content_submit_visibility_{$mainparent}"]) ? $content_pref["content_submit_visibility_{$mainparent}"] : (isset($content_pref["content_admin_visibility_{$mainparent}"]) ? $content_pref["content_admin_visibility_{$mainparent}"] : ""));
							
							$checkmeta = (isset($content_pref["content_submit_meta_{$mainparent}"]) ? $content_pref["content_submit_meta_{$mainparent}"] : (isset($content_pref["content_admin_meta_{$mainparent}"]) ? $content_pref["content_admin_meta_{$mainparent}"] : ""));
							
							$checkcustom = (isset($content_pref["content_submit_customtags_{$mainparent}"]) ? $content_pref["content_submit_customtags_{$mainparent}"] : (isset($content_pref["content_admin_customtags_{$mainparent}"]) ? $content_pref["content_admin_customtags_{$mainparent}"] : ""));
							
							$checkcustomnumber = (isset($content_pref["content_submit_custom_number_{$mainparent}"]) && $content_pref["content_submit_custom_number_{$mainparent}"] != "0" ? $content_pref["content_submit_custom_number_{$mainparent}"] : (isset($content_pref["content_admin_custom_number_{$mainparent}"]) ? $content_pref["content_admin_custom_number_{$mainparent}"] : ""));
							
							$checklayout = (isset($content_pref["content_submit_layout_{$mainparent}"]) ? $content_pref["content_submit_layout_{$mainparent}"] : (isset($content_pref["content_admin_layout_{$mainparent}"]) ? $content_pref["content_admin_layout_{$mainparent}"] : ""));

							$checkpreset = (isset($content_pref["content_submit_presettags_{$mainparent}"]) ? $content_pref["content_submit_presettags_{$mainparent}"] : (isset($content_pref["content_admin_presettags_{$mainparent}"]) ? $content_pref["content_admin_presettags_{$mainparent}"] : ""));

						//normal admin content create preferences
						}else{
							$checkicon = (isset($content_pref["content_admin_icon_{$mainparent}"]) ? $content_pref["content_admin_icon_{$mainparent}"] : "");
							$checkattach = (isset($content_pref["content_admin_attach_{$mainparent}"]) ? $content_pref["content_admin_attach_{$mainparent}"] : "");
							$checkattachnumber = (isset($content_pref["content_admin_files_number_{$mainparent}"]) ? $content_pref["content_admin_files_number_{$mainparent}"] : "");
							$checkimages = (isset($content_pref["content_admin_images_{$mainparent}"]) ? $content_pref["content_admin_images_{$mainparent}"] : "");
							$checkimagesnumber = (isset($content_pref["content_admin_images_number_{$mainparent}"]) ? $content_pref["content_admin_images_number_{$mainparent}"] : "");
							$checkcomment = (isset($content_pref["content_admin_comment_{$mainparent}"]) ? $content_pref["content_admin_comment_{$mainparent}"] : "");
							$checkrating = (isset($content_pref["content_admin_rating_{$mainparent}"]) ? $content_pref["content_admin_rating_{$mainparent}"] : "");
							$checkscore = (isset($content_pref["content_admin_score_{$mainparent}"]) ? $content_pref["content_admin_score_{$mainparent}"] : "");
							$checkpe = (isset($content_pref["content_admin_pe_{$mainparent}"]) ? $content_pref["content_admin_pe_{$mainparent}"] : "");
							$checkvisibility = (isset($content_pref["content_admin_visibility_{$mainparent}"]) ? $content_pref["content_admin_visibility_{$mainparent}"] : "");
							$checkmeta = (isset($content_pref["content_admin_meta_{$mainparent}"]) ? $content_pref["content_admin_meta_{$mainparent}"] : "");
							$checkcustom = (isset($content_pref["content_admin_customtags_{$mainparent}"]) ? $content_pref["content_admin_customtags_{$mainparent}"] : "");
							$checkcustomnumber = (isset($content_pref["content_admin_custom_number_{$mainparent}"]) ? $content_pref["content_admin_custom_number_{$mainparent}"] : "");
							$checklayout = (isset($content_pref["content_admin_layout_{$mainparent}"]) ? $content_pref["content_admin_layout_{$mainparent}"] : "");
							$checkpreset = (isset($content_pref["content_admin_presettags_{$mainparent}"]) ? $content_pref["content_admin_presettags_{$mainparent}"] : "");
						}

						if($mode == "contentmanager"){ // used in contentmanager
							$authordetails = $aa -> getAuthor(USERID);
						}
						if( !isset($authordetails) ){
							$authordetails = $aa -> getAuthor(USERID);
						}

						//$formurl = e_SELF."?".e_QUERY.".cc";

						if( ($qs[1] == "edit" || $qs[1] == "sa") && is_numeric($qs[2]) && !isset($_POST['preview_content']) && !isset($message)){
							if(!$sql -> db_Select($plugintable, "*", "content_id='".$qs[2]."' ")){
								header("location:".e_SELF."?content"); exit;
							}else{
								$row = $sql -> db_Fetch();
								$row['content_heading']		= $tp -> toForm($row['content_heading'], TRUE);
								$row['content_subheading']	= $tp -> toForm($row['content_subheading'], TRUE);
								$row['content_summary']		= $tp -> toForm($row['content_summary'], TRUE);
								$row['content_text']		= $tp -> toForm($row['content_text'], TRUE);
								//$row['content_text'] = $tp -> post_toHTML($row['content_text'], TRUE);
								$authordetails				= $aa -> getAuthor($row['content_author']);
							}
							//$formurl = e_SELF."?".e_QUERY.".cu";
						}
						
						
						//preview not yet working ok ........
						//if( ($qs[1] == "create" || $qs[1] == "edit" || $qs[1] == "sa") && is_numeric($qs[2]) && (isset($_POST['preview_content']) || isset($message)) ){
						if(isset($_POST['preview_content'])){
								/*
								$text = "
								<div style='text-align:center'>
								<table class='fborder' style='".ADMIN_WIDTH."' border='0'>
								<tr><td>".$_POST['content_heading']."</td></tr>
								<tr><td>".$_POST['content_subheading']."</td></tr>
								<tr><td>".$_POST['content_summary']."</td></tr>
								<tr><td>".$_POST['content_text']."</td></tr>
								</table>
								</div>";									  
								$ns -> tablerender($content_heading, $text);
								*/
						}
						if( isset($_POST['preview_content']) || isset($message) ){
								//$tp -> post_toHTML()
								$row['content_parent']				= $_POST['parent'];
								$row['content_heading']				= $_POST['content_heading'];
								$row['content_subheading']			= $_POST['content_subheading'];
								$row['content_summary']				= $_POST['content_summary'];
								$row['content_text']				= $_POST['content_text'];
								$authordetails[0]					= $_POST['content_author_id'];
								$authordetails[1]					= $_POST['content_author_name'];
								$authordetails[2]					= $_POST['content_author_email'];
								$ne_day								= $_POST['ne_day'];
								$ne_month							= $_POST['ne_month'];
								$ne_year							= $_POST['ne_year'];
								$end_day							= $_POST['end_day'];
								$end_month							= $_POST['end_month'];
								$end_year							= $_POST['end_year'];
								$row['content_comment']				= $_POST['content_comment'];
								$row['content_rate']				= $_POST['content_rate'];
								$row['content_pe']					= $_POST['content_pe'];
								$row['content_class']				= $_POST['content_class'];
								$row['content_refer']				= $_POST['content_refer'];
								$row['content_datestamp']			= $_POST['content_datestamp'];
								$custom['content_custom_score']		= $_POST['content_score'];
								$custom['content_custom_meta']		= $_POST['content_meta'];
								$custom['content_custom_template']	= $_POST['content_template'];

								//custom tags
								for($i=0;$i<$content_pref["content_admin_custom_number_{$mainparent}"];$i++){
									$keystring = $_POST["content_custom_key_{$i}"];
									$custom["content_custom_{$keystring}"] = $_POST["content_custom_value_{$i}"];
								}
								
								//custom preset tags
								for($i=0;$i<count($_POST['content_custom_preset_key']);$i++){
									$keystring = "content_custom_preset_".$_POST['content_custom_preset_key'][$i];
									$custom[$keystring] = $_POST['content_custom_preset_value'][$i];
								}
						}						

						$content_author_id		= (isset($authordetails[0]) && $authordetails[0] != "" ? $authordetails[0] : USERID);
						$content_author_name	= (isset($authordetails[1]) && $authordetails[1] != "" ? $authordetails[1] : USERNAME);
						$content_author_email	= (isset($authordetails[2]) && $authordetails[2] != "" ? $authordetails[2] : USEREMAIL);

						$formurl = e_SELF."?".e_QUERY;
						$text = "
						<div style='text-align:center;'>
						".$rs -> form_open("post", $formurl, "dataform", "", "enctype='multipart/form-data'")."
						<table style='".ADMIN_WIDTH."' class='fborder'>";

						$hidden = "";
						if($mode == "contentmanager"){
							if($qs[1] == "edit"){
								$hidden = $rs -> form_hidden("parent", $row['content_parent']);
							}else{
								$hidden = $rs -> form_hidden("parent", $qs[2]);
							}
						}else{
							if($mode == "submit"){
								$parent = "submit";
							}else{
								$parent = (isset($qs[3]) && is_numeric($qs[3]) ? $qs[3] : (isset($row['content_parent']) ? $row['content_parent'] : "") );
							}
							//category parent
							$TOPIC_TOPIC = CONTENT_ADMIN_CAT_LAN_27;							
							$TOPIC_FIELD = $aa -> ShowOptionCat($parent).$rs->form_hidden("parent", "");
							$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW_NOEXPAND);
							$text .= $TOPIC_ROW_SPACER;
						}

						//heading
						$row['content_heading'] = (isset($row['content_heading']) ? $row['content_heading'] : "");
						$TOPIC_TOPIC = CONTENT_ADMIN_ITEM_LAN_11;
						$TOPIC_FIELD = $rs -> form_text("content_heading", 74, $row['content_heading'], 250).$hidden;
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW_NOEXPAND);

						//subheading
						$row['content_subheading'] = (isset($row['content_subheading']) ? $row['content_subheading'] : "");
						$TOPIC_TOPIC = CONTENT_ADMIN_ITEM_LAN_16;
						$TOPIC_FIELD = $rs -> form_text("content_subheading", 74, $row['content_subheading'], 250);
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW_NOEXPAND);
						
						//summary
						$row['content_summary'] = (isset($row['content_summary']) ? $row['content_summary'] : "");
						$TOPIC_TOPIC = CONTENT_ADMIN_ITEM_LAN_17;
						$TOPIC_FIELD = $rs -> form_textarea("content_summary", 74, 5, $row['content_summary']);
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW_NOEXPAND);

						//text
						$row['content_text'] = (isset($row['content_text']) ? $row['content_text'] : "");
						require_once(e_HANDLER."ren_help.php");
						$TOPIC_TOPIC = CONTENT_ADMIN_ITEM_LAN_18;
						$insertjs = (!$pref['wysiwyg'] ? "onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'" : "");
						$TOPIC_FIELD = $rs -> form_textarea("content_text", 74, 20, $row['content_text'], $insertjs)."<br />";
						if (!$pref['wysiwyg']) { $TOPIC_FIELD .= $rs -> form_text("helpb", 90, '', '', "helpbox")."<br />". display_help("helpb"); }
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW_NOEXPAND);

						$text .= $TOPIC_ROW_SPACER;

						//author						
						$content_author_name_value = ($content_author_name ? $content_author_name : CONTENT_ADMIN_ITEM_LAN_14);
						$content_author_name_js = ($content_author_name ? "" : "onfocus=\"if(document.getElementById('dataform').content_author_name.value=='".CONTENT_ADMIN_ITEM_LAN_14."'){document.getElementById('dataform').content_author_name.value='';}\"");
						$content_author_email_value = ($content_author_email ? $content_author_email : CONTENT_ADMIN_ITEM_LAN_15);
						$content_author_email_js = ($content_author_email ? "" : "onfocus=\"if(document.getElementById('dataform').content_author_email.value=='".CONTENT_ADMIN_ITEM_LAN_15."'){document.getElementById('dataform').content_author_email.value='';}\"");

						$TOPIC_TOPIC = CONTENT_ADMIN_ITEM_LAN_51;
						$TOPIC_HEADING = CONTENT_ADMIN_ITEM_LAN_72." (".CONTENT_ADMIN_ITEM_LAN_71.")";
						$TOPIC_HELP = "";
						$TOPIC_FIELD = "
							<table style='width:100%; text-align:left;'>
							<tr><td>".CONTENT_ADMIN_ITEM_LAN_14."</td><td>".$rs -> form_text("content_author_name", 70, $content_author_name_value, 100, "tbox", "", "", $content_author_name_js )."</td></tr>
							<tr><td>".CONTENT_ADMIN_ITEM_LAN_15."</td><td>".$rs -> form_text("content_author_email", 70, $content_author_email_value, 100, "tbox", "", "", $content_author_email_js )."
							".$rs -> form_hidden("content_author_id", $content_author_id)."
							</td></tr></table>";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);


						if(isset($row['content_datestamp']) && $row['content_datestamp'] != "0" && $row['content_datestamp'] != ""){
							$startdate	= getdate($row['content_datestamp']);
							$ne_day		= $startdate['mday'];
							$ne_month	= $startdate['mon'];
							$ne_year	= $startdate['year'];
						}else{
							$ne_day		= (isset($ne_day) ? $ne_day : "0");
							$ne_month	= (isset($ne_month) ? $ne_month : "0");
							$ne_year	= (isset($ne_year) ? $ne_year : "0");
						}

						$smarray = getdate();
						$current_year = $smarray['year'];

						//start date
						$TOPIC_TOPIC = CONTENT_ADMIN_DATE_LAN_15;
						$TOPIC_HEADING = CONTENT_ADMIN_ITEM_LAN_73;
						$TOPIC_HELP = CONTENT_ADMIN_DATE_LAN_17;
						$TOPIC_FIELD = "
							".$rs -> form_select_open("ne_day")."
							".$rs -> form_option(CONTENT_ADMIN_DATE_LAN_12, 0, "none");
							for($count=1; $count<=31; $count++){
								$TOPIC_FIELD .= $rs -> form_option($count, (isset($ne_day) && $ne_day == $count ? "1" : "0"), $count);
							}
							$TOPIC_FIELD .= $rs -> form_select_close()."
							".$rs -> form_select_open("ne_month")."
							".$rs -> form_option(CONTENT_ADMIN_DATE_LAN_13, 0, "none");
							for($count=1; $count<=12; $count++){
								$TOPIC_FIELD .= $rs -> form_option($months[($count-1)], (isset($ne_month) && $ne_month == $count ? "1" : "0"), $count);
							}
							$TOPIC_FIELD .= $rs -> form_select_close()."
							".$rs -> form_select_open("ne_year")."
							".$rs -> form_option(CONTENT_ADMIN_DATE_LAN_14, 0, "none");
							for($count=($current_year-5); $count<=($current_year+1); $count++){
								$TOPIC_FIELD .= $rs -> form_option($count, (isset($ne_year) && $ne_year == $count ? "1" : "0"), $count);
							}
							$TOPIC_FIELD .= $rs -> form_select_close()."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);


						if(isset($row['content_enddate']) && $row['content_enddate'] != "0" && $row['content_enddate'] != ""){
							$enddate	= getdate($row['content_enddate']);
							$end_day	= $enddate['mday'];
							$end_month	= $enddate['mon'];
							$end_year	= $enddate['year'];
						}else{
							$end_day	= (isset($end_day) ? $end_day : "0");
							$end_month	= (isset($end_month) ? $end_month : "0");
							$end_year	= (isset($end_year) ? $end_year : "0");
						}

						//end date
						$TOPIC_TOPIC = CONTENT_ADMIN_DATE_LAN_16;
						$TOPIC_HEADING = CONTENT_ADMIN_ITEM_LAN_74;
						$TOPIC_HELP = CONTENT_ADMIN_DATE_LAN_18;
						$TOPIC_FIELD = "
							".$rs -> form_select_open("end_day")."
							".$rs -> form_option(CONTENT_ADMIN_DATE_LAN_12, 0, "none");
							for($count=1; $count<=31; $count++){
								$TOPIC_FIELD .= $rs -> form_option($count, (isset($end_day) && $end_day == $count ? "1" : "0"), $count);
							}
							$TOPIC_FIELD .= $rs -> form_select_close()."
							".$rs -> form_select_open("end_month")."
							".$rs -> form_option(CONTENT_ADMIN_DATE_LAN_13, 0, "none");
							for($count=1; $count<=12; $count++){
								$TOPIC_FIELD .= $rs -> form_option($months[($count-1)], (isset($end_month) && $end_month == $count ? "1" : "0"), $count);
							}
							$TOPIC_FIELD .= $rs -> form_select_close()."
							".$rs -> form_select_open("end_year")."
							".$rs -> form_option(CONTENT_ADMIN_DATE_LAN_14, 0, "none");
							for($count=($current_year-5); $count<=($current_year+1); $count++){
								$TOPIC_FIELD .= $rs -> form_option($count, (isset($end_year) && $end_year == $count ? "1" : "0"), $count);
							}
							$TOPIC_FIELD .= $rs -> form_select_close()."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//$text .= $TOPIC_ROW_SPACER;
						if( $checkicon || $checkattach || $checkimages ){
							$text .= $TOPIC_ROW_SPACER;
						}

						if($checkicon){
							//icon
							$TOPIC_TOPIC = CONTENT_ADMIN_ITEM_LAN_20;
							$TOPIC_HEADING = CONTENT_ADMIN_ITEM_LAN_75;
							$TOPIC_HELP = "";
							$TOPIC_FIELD = "
								".$rs -> form_hidden("uploadtype1", "icon");
								if (isset($row['content_icon']) && $row['content_icon']){
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
								if (isset($row['content_icon'])){
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
													<input class='tbox' type='file' name='file_userfile2[]' value='".$attachments[$i]."' size='50' />
													</div>
													";
												} else {
													$TOPIC_FIELD .= "<i>".CONTENT_ADMIN_ITEM_LAN_29."</i><br /><input class='tbox' name='file_userfile2[]' type='file' size='50' />";
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
													<input class='tbox' type='file' name='file_userfile3[]' value='".$imagesarray[$i]."' size='50' />
													</div>
													";
												} else {
													$TOPIC_FIELD .= "<input class='tbox' name='file_userfile3[]' type='file' size='50' />";
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

						if($checkcomment || $checkrating || $checkpe || $checkvisibility || $checkscore || $checkmeta || $checklayout ){
							$text .= $TOPIC_ROW_SPACER;
						}
						if($checkcomment){
							//comment
							$row['content_comment'] = (isset($row['content_comment']) ? $row['content_comment'] : "");
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
							$row['content_rate'] = (isset($row['content_rate']) ? $row['content_rate'] : "");
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
							$row['content_pe'] = (isset($row['content_pe']) ? $row['content_pe'] : "");
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
							$row['content_class'] = (isset($row['content_class']) ? $row['content_class'] : "");
							$TOPIC_TOPIC = CONTENT_ADMIN_ITEM_LAN_39;
							$TOPIC_HEADING = CONTENT_ADMIN_ITEM_LAN_81;
							$TOPIC_HELP = "";
							$TOPIC_FIELD = r_userclass("content_class",$row['content_class'], "CLASSES");
							$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);
						}

						
						if(!(isset($_POST['preview_content']) || isset($message))){
							$custom['content_custom_score']	= "";
							$custom['content_custom_meta']	= "";
							$custom['content_custom_template']	= "";
							if(isset($row['content_pref'])){
								$custom = $eArrayStorage->ReadArray($row['content_pref']);
							}
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

						if($checklayout){
							global $fl;

							if(!isset($content_pref["content_theme_{$mainparent}"])){
								$dir = $plugindir."templates/default";
							}else{
								if(file_exists($plugindir."templates/".$content_pref["content_theme_{$mainparent}"]."/content_content_template.php")){
									$dir = $plugindir."templates/".$content_pref["content_theme_{$mainparent}"];
								}else{
									$dir = $plugindir."templates/default";
								}
							}
							//get_files($path, $fmask = '', $omit='standard', $recurse_level = 0, $current_level = 0, $dirs_only = FALSE)
							$rejectlist = array('$.','$..','/','CVS','thumbs.db','Thumbs.db','*._$', 'index', 'null*', '.bak');
							$templatelist = $fl->get_files($dir,"content_content",$rejectlist);

							//template
							if(isset($custom['content_custom_template']) && $custom['content_custom_template'] != ""){
								$check = $custom['content_custom_template'];
							}else{
								if(isset($content_pref["content_layout_{$mainparent}"])){
									$check = $content_pref["content_layout_{$mainparent}"];
								}else{
									$check = "";
								}
							}
							$TOPIC_TOPIC = CONTENT_ADMIN_ITEM_LAN_92;
							$TOPIC_HEADING = CONTENT_ADMIN_ITEM_LAN_93;
							$TOPIC_HELP = "";
							$TOPIC_FIELD = "
							".$rs -> form_select_open("content_template")."
							".$rs -> form_option(CONTENT_ADMIN_ITEM_LAN_94, 0, "none");
							foreach($templatelist as $template){
								$templatename = substr($template['fname'], 25, -4);
								$templatename = ($template['fname'] == "content_content_template.php" ? "default" : $templatename);
								$TOPIC_FIELD .= $rs -> form_option($templatename, ($check == $template['fname'] ? "1" : "0"), $template['fname']);
							}
							$TOPIC_FIELD .= $rs -> form_select_close();
							$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);
						}

						if( $checkcustom && $checkcustomnumber ){
							$text .= $TOPIC_ROW_SPACER;
						}

						//custom data
						$existing_custom = "0";
						$TOPIC_TOPIC = CONTENT_ADMIN_ITEM_LAN_54;
						$TOPIC_HEADING = CONTENT_ADMIN_ITEM_LAN_84;
						$TOPIC_HELP = CONTENT_ADMIN_ITEM_LAN_68;
						$TOPIC_CHECK_VALID = FALSE;
						$TOPIC_FIELD = "";
						if($checkcustom && $checkcustomnumber){ $TOPIC_FIELD = "<table style='width:100%; border:0;'>"; }
						if(!empty($custom)){						
							foreach($custom as $k => $v){
								if(substr($k,0,22) != "content_custom_preset_" && $k != "content_custom_presettags" && $k != "content_custom_score" && $k != "content_custom_meta" && $k != "content_custom_template"){
									$key = substr($k,15);
									if($checkcustom && $checkcustomnumber){
										$TOPIC_FIELD .= "
										<tr>
											<td class='forumheader3' style='border:0;'>".$rs -> form_text("content_custom_key_".$existing_custom."", 20, $key, 100)."</td>
											<td class='forumheader3' style='border:0;'>".$rs -> form_text("content_custom_value_".$existing_custom."", 70, $v, 250)."</td>
										</tr>";
									}else{
										$TOPIC_FIELD .= "
										".$rs -> form_hidden("content_custom_key_".$existing_custom, $key)."
										".$rs -> form_hidden("content_custom_value_".$existing_custom, $v);
										$TOPIC_CHECK_VALID = TRUE;
									}
									$existing_custom = $existing_custom + 1;
								}
							}
						}

						if($checkcustom && $checkcustomnumber){
							$TOPIC_CHECK_VALID = TRUE;
							for($i=$existing_custom;$i<$checkcustomnumber;$i++){
									$TOPIC_FIELD .= "
									<tr>
										<td class='forumheader3' style='border:0;'>".$rs -> form_text("content_custom_key_".$i."", 20, "", 100)."</td>
										<td class='forumheader3' style='border:0;'>".$rs -> form_text("content_custom_value_".$i."", 70, "", 250)."</td>
									</tr>";
							}
						}
						if($checkcustom && $checkcustomnumber){ $TOPIC_FIELD .= "</table>"; }
						if($TOPIC_CHECK_VALID){ $text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW); }
						
						//preset custom data fields
						if(count($content_pref["content_custom_preset_key"]) > 0){
							$text .= $TOPIC_ROW_SPACER;
						}

						$hidden = "";
						for($i=0;$i<count($content_pref["content_custom_preset_key"]);$i++){
							$value = "";
							if($custom){
								$keystring = "content_custom_preset_".$content_pref["content_custom_preset_key"][$i];
								if(array_key_exists($keystring, $custom)){
									$value = $custom[$keystring];
								}
							}
							if(!empty($content_pref["content_custom_preset_key"][$i])){
								if($checkpreset){
									$text .= $this -> parseCustomPresetTag($content_pref["content_custom_preset_key"][$i], $custom['content_custom_presettags']);
								}else{
									$tmp = explode("^", $content_pref["content_custom_preset_key"][$i]);
									if(is_array($custom['content_custom_presettags'][$tmp[0]])){
										$hidden .= $rs -> form_hidden("content_custom_preset_key[$tmp[0]][day]", $custom['content_custom_presettags'][$tmp[0]][day]);
										$hidden .= $rs -> form_hidden("content_custom_preset_key[$tmp[0]][month]", $custom['content_custom_presettags'][$tmp[0]][month]);
										$hidden .= $rs -> form_hidden("content_custom_preset_key[$tmp[0]][year]", $custom['content_custom_presettags'][$tmp[0]][year]);
									}else{
										$hidden .= $rs -> form_hidden("content_custom_preset_key[$tmp[0]]", $custom['content_custom_presettags'][$tmp[0]]);
									}									
								}
							}
						}

						$text .= $TOPIC_ROW_SPACER."
						<tr>
							<td colspan='2' style='text-align:center' class='forumheader'>".($hidden ? $hidden : "");
							
							$js = "onclick=\"document.getElementById('parent').value = document.getElementById('parent1').options[document.getElementById('parent1').selectedIndex].label;\" ";
							if($qs[1] == "edit" || $qs[1] == "sa" || isset($_POST['editp']) ){
								$text .= $rs -> form_hidden("content_refer", $row['content_refer']);
								$text .= $rs -> form_hidden("content_datestamp", $row['content_datestamp']);
								//$text .= $rs -> form_button("submit", "preview_content", (isset($_POST['preview_content']) ? CONTENT_ADMIN_MAIN_LAN_27 : CONTENT_ADMIN_MAIN_LAN_26), $js);
								$text .= $rs -> form_button("submit", "update_content", ($qs[1] == "sa" ? CONTENT_ADMIN_ITEM_LAN_43 : CONTENT_ADMIN_ITEM_LAN_45), $js );
								$text .= $rs -> form_hidden("content_id", $qs[2]);
								$text .= $rs -> form_checkbox("update_datestamp", 1, 0)." ".CONTENT_ADMIN_ITEM_LAN_42;
							}else{
								//$text .= $rs -> form_button("submit", "preview_content", (isset($_POST['preview_content']) ? CONTENT_ADMIN_MAIN_LAN_27 : CONTENT_ADMIN_MAIN_LAN_26), $js);
								$text .= $rs -> form_button("submit", "create_content", CONTENT_ADMIN_ITEM_LAN_44, $js);								
							}
							$text .= "
							</td>
						</tr>

						</table>
						</form>
						</div>";

						$caption = ($qs[1] == "edit" ? CONTENT_ADMIN_ITEM_LAN_45 : CONTENT_ADMIN_ITEM_LAN_44);
						$ns -> tablerender($caption, $text);
		}


		function parseCustomPresetTag($tag, $values){
			global $rs, $TOPIC_ROW_NOEXPAND, $months;

			$tmp = explode("^", $tag);

			if($tmp[1] == "text"){
					$str = $rs -> form_text("content_custom_preset_key[{$tmp[0]}]", $tmp[2], $values[$tmp[0]], $tmp[3], "tbox", "", "", "");

			}elseif($tmp[1] == "area"){
					$str = $rs -> form_textarea("content_custom_preset_key[{$tmp[0]}]", $tmp[2], $tmp[3], $values[$tmp[0]], "", "", "", "", "");

			}elseif($tmp[1] == "select"){
					$str = $rs -> form_select_open("content_custom_preset_key[{$tmp[0]}]", "");
					$str .= $rs -> form_option($tmp[2], ($values[$tmp[0]] == $tmp[2] ? "1" : "0"), "", "");
					for($i=3;$i<count($tmp);$i++){
						$str .= $rs -> form_option($tmp[$i], ($values[$tmp[0]] == $tmp[$i] ? "1" : "0"), $tmp[$i], "");
					}				
					$str .= $rs -> form_select_close();
				
			}elseif($tmp[1] == "date"){
					$str = $rs -> form_select_open("content_custom_preset_key[{$tmp[0]}][day]", "")."
					".$rs -> form_option(CONTENT_ADMIN_DATE_LAN_12, "0", "");
					for($i=1;$i<=31;$i++){
						$str .= $rs -> form_option($i, ($values[$tmp[0]]['day'] == $i ? "1" : "0"), $i, "");
					}
					$str .= $rs -> form_select_close();

					$str .= $rs -> form_select_open("content_custom_preset_key[{$tmp[0]}][month]", "")."
					".$rs -> form_option(CONTENT_ADMIN_DATE_LAN_13, "0", "");
					for($i=1;$i<=12;$i++){
						$str .= $rs -> form_option($months[($i-1)], ($values[$tmp[0]]['month'] == $i ? "1" : "0"), $i, "");
					}
					$str .= $rs -> form_select_close();

					$str .= $rs -> form_select_open("content_custom_preset_key[{$tmp[0]}][year]", "")."
					".$rs -> form_option(CONTENT_ADMIN_DATE_LAN_14, "0", "");
					for($i=$tmp[2];$i<=$tmp[3];$i++){
						$str .= $rs -> form_option($i, ($values[$tmp[0]]['year'] == $i ? "1" : "0"), $i, "");
					}
					$str .= $rs -> form_select_close();
			
			}elseif($tmp[1] == "radio"){
					//secondradio^radio^opt1^1^opt2^2^opt3^3
					//form_radio($form_name, $form_value, $form_checked = 0, $form_tooltip = "", $form_js = "")
					$str = "";
					for($i=2;$i<count($tmp);$i++){
						$str .= $rs -> form_radio("content_custom_preset_key[{$tmp[0]}]", $tmp[$i], ($values[$tmp[0]] == $tmp[$i] ? "1" : "0"), "", "")." ".$tmp[$i];
						$i++;					
					}

			}elseif($tmp[1] == "checkbox"){
					//somecheckbox^checkbox^graduation
					//form_checkbox($form_name, $form_value, $form_checked = 0, $form_tooltip = "", $form_js = "")
					$str = $rs -> form_checkbox("content_custom_preset_key[{$tmp[0]}]", $tmp[2], ($values[$tmp[0]] == $tmp[2] ? "1" : "0"), "", "");
			}

			$TOPIC_TOPIC = $tmp[0];
			$TOPIC_HEADING = "";
			$TOPIC_HELP = "";
			$TOPIC_FIELD = $str;
			$text = preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW_NOEXPAND);

			return $text;
		}




		function show_manage_content($mode, $userid="", $username=""){
						global $qs, $sql, $ns, $rs, $aa, $plugintable, $plugindir, $tp, $eArrayStorage;

						if($mode != "contentmanager"){
							//category parent
							global $TOPIC_TOPIC, $TOPIC_FIELD, $TOPIC_ROW_NOEXPAND;
							$TOPIC_TOPIC = CONTENT_ADMIN_CAT_LAN_27;
							$parent = (is_numeric($qs[1]) ? $qs[1] : "");
							$TOPIC_FIELD = $aa -> ShowOptionCat($parent).$rs->form_hidden("parent", "");
							$text = "
							<div style='text-align:center'>								
							<table style='".ADMIN_WIDTH."' class='fborder'>
							<tr><td class='fcaption' colspan='2'>".CONTENT_ADMIN_MAIN_LAN_2."</td></tr>";
							$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW_NOEXPAND);
							$text .= "
							</table></div>";
							$caption = CONTENT_ADMIN_MAIN_LAN_2;
							$ns -> tablerender($caption, $text);
						}
						
						if(!isset($qs[1])){
							return;
						}

						$mainparent						= $aa -> getMainParent($qs[1]);
						$content_pref					= $aa -> getContentPref($mainparent);
						$content_cat_icon_path_large	= $tp -> replaceConstants($content_pref["content_cat_icon_path_large_{$mainparent}"]);
						$content_cat_icon_path_small	= $tp -> replaceConstants($content_pref["content_cat_icon_path_small_{$mainparent}"]);
						$content_icon_path				= $tp -> replaceConstants($content_pref["content_icon_path_{$mainparent}"]);
						$content_image_path				= $tp -> replaceConstants($content_pref["content_image_path_{$mainparent}"]);
						$content_file_path				= $tp -> replaceConstants($content_pref["content_file_path_{$mainparent}"]);

						if($mode == "contentmanager"){
							$personalmanagercheck = FALSE;
							if($sql -> db_Select($plugintable, "content_id, content_heading, content_pref", " content_id='".$qs[1]."' ")){
								$rowpcm = $sql -> db_Fetch();
								$pcmcontent_pref = $eArrayStorage->ReadArray($rowpcm['content_pref']);

								//assign new preferences
								$pcm = explode(",", $pcmcontent_pref['content_manager_allowed']);
								if(in_array($userid, $pcm) || getperms("0")){
									$personalmanagercheck = TRUE;
								}
							}
							if($personalmanagercheck == TRUE){
								$qryuser = "";
								if(getperms("0")){
									$userid = USERID;
									$username = USERNAME;
								}else{
									//use user restriction (personal admin)
									if(isset($userid) && isset($username) ){
										$qryuser = " AND (content_author = '".$userid."' OR SUBSTRING_INDEX(content_author, '^', 1) = '".$userid."' OR SUBSTRING_INDEX(content_author, '^', 2) = '".$userid."^".$username."')";
									}
								}
								$formtarget	= $plugindir."content_manager.php?content.".$qs[1];
								$qrycat		= " content_parent = '".$qs[1]."' ";
								$qryfirst	= " content_parent = '".$qs[1]."' ";
								$qryletter	= "";
								
							}else{
								header("location:".$plugindir."content_manager.php"); exit;
							}
						}else{
							$array			= $aa -> getCategoryTree("", $qs[1], TRUE);
							$validparent	= implode(",", array_keys($array));
							$qrycat			= " content_parent REGEXP '".$aa -> CONTENTREGEXP($validparent)."' ";
							$qryuser = "";
							if( isset($qs[2]) && is_numeric($qs[2]) ){
							//	$formtarget = e_SELF."?content.".$qs[1].".".$qs[2];
							//	$qryfirst = " content_parent='".$qs[2]."' ";							
							//	$qryletter = " AND ".$qryfirst;
							}else{
								$formtarget	= e_SELF."?content.".$qs[1];
								$qryfirst	= " ".$qrycat." ";							
								$qryletter	= "";
							}
						}
						
						$text = "";
						// -------- SHOW FIRST LETTERS FIRSTNAMES ------------------------------------
						if(!is_object($sql)){ $sql = new db; }
						$distinctfirstletter = $sql -> db_Select($plugintable, "DISTINCT(LEFT(content_heading,1)) as letter", "content_refer != 'sa' AND ".$qryfirst." ".$qryuser." ORDER BY content_heading ASC ");

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
						$letter=(isset($_POST['letter']) ? $_POST['letter'] : "");
						if ($letter != "" && $letter != "all" ) { $qryletter .= " AND content_heading LIKE '".$letter."%' "; }else{ $qryletter .= ""; }
						
						$qryitem = " ".$qrycat." AND content_refer != 'sa' ".$qryletter." ".$qryuser." ORDER BY content_datestamp DESC";
						// ---------------------------------------------------------------------------

						$sql2 = new db;						
						if(!$content_total = $sql2 -> db_Select($plugintable, "content_id, content_heading, content_subheading, content_author, content_icon", $qryitem)){
							$text .= "<div style='text-align:center'>".CONTENT_ADMIN_ITEM_LAN_4."</div>";
						}else{
							if($content_total < 50 || isset($letter)){
									//if($mode != "contentmanager"){
									//	$oText = str_replace("'", "\'", CONTENT_ADMIN_ITEM_LAN_67);
									//	$popuphelp = $aa -> popupHelp($oText, "", "", "");
									//}
									$text .= "
									<div style='text-align:center'>
									".$rs -> form_open("post", e_SELF."?".e_QUERY, "deletecontentform","","", "")."
									<table style='".ADMIN_WIDTH."' class='fborder'>
									<tr>
									<td class='fcaption' style='width:5%; text-align:center;'>".CONTENT_ADMIN_ITEM_LAN_8."</td>
									<td class='fcaption' style='width:5%; text-align:center;'>".CONTENT_ADMIN_ITEM_LAN_9."</td>
									<td class='fcaption' style='width:10%; text-align:left;'>".CONTENT_ADMIN_ITEM_LAN_10."</td>
									<td class='fcaption' style='width:70%; text-align:left;'>".CONTENT_ADMIN_ITEM_LAN_11."</td>
									<td class='fcaption' style='width:10%; text-align:center;'>".CONTENT_ADMIN_ITEM_LAN_12."</td>
									</tr>";
									while($row = $sql2 -> db_Fetch()){
											$delete_heading	= str_replace("&#39;", "\'", $row['content_heading']);
											$authordetails	= $aa -> getAuthor($row['content_author']);
											$caticon		= $content_icon_path.$row['content_icon'];
											$deleteicon		= CONTENT_ICON_DELETE;
											$cid			= $row['content_id'];
											$text .= "
											<tr>
												<td class='forumheader3' style='width:5%; text-align:center'>".$cid."</td>
												<td class='forumheader3' style='width:5%; text-align:center'>".($row['content_icon'] ? "<img src='".$caticon."' alt='' style='width:50px; vertical-align:middle' />" : "&nbsp;")."</td>
												<td class='forumheader3' style='width:10%; text-align:left'>".($authordetails[0] != "0" ? "<a href='".e_BASE."user.php?id.".$authordetails[0]."'>".CONTENT_ICON_USER."</a>" : "")." ".$authordetails[1]."</td>
												<td class='forumheader3' style='width:70%; text-align:left;'>
													<a href='".$plugindir."content.php?content.".$row['content_id']."'>".CONTENT_ICON_LINK."</a> 
													".$row['content_heading']." ".($row['content_subheading'] ? "[".$row['content_subheading']."]" : "")."</td>
												<td class='forumheader3' style='width:10%; text-align:center; white-space:nowrap; vertical-align:top;'>
													<a href='".e_SELF."?content.edit.".$cid."'>".CONTENT_ICON_EDIT."</a> 
													<input type='image' title='delete' name='delete[content_{$cid}]' src='".CONTENT_ICON_DELETE_BASE."' onclick=\"return jsconfirm('".$tp->toJS(CONTENT_ADMIN_JS_LAN_1."\\n\\n[".CONTENT_ADMIN_JS_LAN_6." ".$cid." : ".$delete_heading."]")."')\"/>
												</td>
											</tr>";

									}
									$text .= "</table>
									".$rs -> form_close()."
									</div>";
							} else {
									$text .= "<br /><div style='text-align:center'>".CONTENT_ADMIN_ITEM_LAN_7."</div>";
							}
						}
						$ns -> tablerender(CONTENT_ADMIN_ITEM_LAN_5, $text);
		}



		function show_submitted(){
						global $qs, $rs, $ns, $aa, $plugintable, $tp;

						if(!is_object($sql)){ $sql = new db; }
						if(!$content_total = $sql -> db_Select($plugintable, "content_id, content_heading, content_subheading, content_author, content_icon, content_parent", "content_refer = 'sa' ")){
							$text .= "<div style='text-align:center'>".CONTENT_ADMIN_ITEM_LAN_50."</div>";
						}else{
							$array = $aa -> getCategoryTree("", "", FALSE);

							$text = "
							<div style='text-align:center'>
							".$rs -> form_open("post", e_SELF, "submittedform","","", "")."
							<table style='".ADMIN_WIDTH."' class='fborder'>
							<tr>
							<td style='width:5%; text-align:center' class='fcaption'>".CONTENT_ADMIN_ITEM_LAN_8."</td>
							<td style='width:5%; text-align:center' class='fcaption'>".CONTENT_ADMIN_ITEM_LAN_9."</td>
							<td style='width:15%; text-align:left' class='fcaption'>".CONTENT_ADMIN_ITEM_LAN_48."</td>
							<td style='width:15%; text-align:left' class='fcaption'>".CONTENT_ADMIN_ITEM_LAN_10."</td>
							<td style='width:50%; text-align:left' class='fcaption'>".CONTENT_ADMIN_ITEM_LAN_11."</td>
							<td style='width:10%; text-align:center' class='fcaption'>".CONTENT_ADMIN_ITEM_LAN_12."</td>
							</tr>";
							while($row = $sql -> db_Fetch()){

								if(array_key_exists($row['content_parent'], $array)){
									$mainparent			= $array[$row['content_parent']][0];
									$mainparentheading	= $array[$row['content_parent']][1]." [".$array[$row['content_parent']][count($array[$row['content_parent']])-1]."]";
									$content_pref		= $aa -> getContentPref($mainparent);
									$iconpath			= ($content_pref["content_icon_path_{$mainparent}"] ? $content_pref["content_icon_path_{$mainparent}"] : "{e_PLUGIN}content/images/icon/" );
									$content_icon_path	= $tp -> replaceConstants($iconpath);
									$icon				= $content_icon_path.$row['content_icon'];
								}
								$delete_heading			= str_replace("&#39;", "\'", $row['content_heading']);										
								$authordetails			= $aa -> getAuthor($row['content_author']);
								$delid					= $row['content_id'];
									
								$text .= "
								<tr>
									<td class='forumheader3' style='width:5%; text-align:center'>".$delid."</td>
									<td class='forumheader3' style='width:5%; text-align:center'>
										".($row['content_icon'] ? "<img src='".$icon."' alt='' style='width:50px; vertical-align:middle' />" : "&nbsp;")."
									</td>
									<td class='forumheader3' style='width:15%; text-align:left'>".$mainparentheading."</td>
									<td class='forumheader3' style='width:15%; text-align:left'>
										".($authordetails[0] != "0" ? "<a href='".e_BASE."user.php?id.".$authordetails[0]."'>".CONTENT_ICON_USER."</a>" : "")."	".$authordetails[1]."
									</td>
									<td class='forumheader3' style='width:75%; text-align:left;'>
										".$row['content_heading']." ".($row['content_subheading'] ? "<br />[".$row['content_subheading']."]" : "")."
									</td>
									<td class='forumheader3' style='width:5%; text-align:center; white-space:nowrap;'>
										<a href='".e_SELF."?content.sa.".$delid."'>".CONTENT_ICON_EDIT."</a>
										<input type='image' title='delete' name='delete[submitted_{$delid}]' src='".CONTENT_ICON_DELETE_BASE."' onclick=\"return jsconfirm('".$tp->toJS(CONTENT_ADMIN_JS_LAN_10."\\n\\n[".CONTENT_ADMIN_JS_LAN_6." ".$delid." : ".$delete_heading."]")."')\"/>
									</td>
								</tr>";

							}
							$text .= "</table>
							".$rs -> form_close()."
							</div>";
						}

						$ns -> tablerender(CONTENT_ADMIN_ITEM_LAN_49, $text);
		}


		function show_manage($mode){
						global $qs, $sql, $ns, $rs, $aa, $plugintable, $plugindir, $tp, $stylespacer, $eArrayStorage;

						$catarray	= $aa -> getCategoryTree("", "", FALSE);
						$array		= array_keys($catarray);

						if(!is_array($array)){
							$text = "<div style='text-align:center;'>".CONTENT_ADMIN_CAT_LAN_9."</div>";
						}else{
							$text = "
							<div style='text-align:center'>
							".$rs -> form_open("post", e_SELF."?".$qs[0], "catform","","", "")."
							<table style='".ADMIN_WIDTH."' class='fborder'>
							<tr>
							<td class='fcaption' style='width:5%'>".CONTENT_ADMIN_CAT_LAN_24."</td>
							<td class='fcaption' style='width:5%'>".CONTENT_ADMIN_CAT_LAN_25."</td>
							<td class='fcaption' style='width:15%'>".CONTENT_ADMIN_CAT_LAN_18."</td>
							<td class='fcaption' style='width:65%'>".CONTENT_ADMIN_CAT_LAN_19."</td>
							<td class='fcaption' style='width:10%; text-align:center'>".CONTENT_ADMIN_CAT_LAN_20."</td>
							</tr>";

							if(!is_object($sql)){ $sql = new db; }
							foreach($array as $catid){
								if(!$category_total = $sql -> db_Select($plugintable, "*", "content_id='".$catid."' ")){
									$text .= "<div style='text-align:center;'>".CONTENT_ADMIN_CAT_LAN_9."</div>";
								}else{
									$row = $sql -> db_Fetch();

									$content_pref					= $aa -> getContentPref($catarray[$catid][0]);
									$content_cat_icon_path_large	= $tp -> replaceConstants($content_pref["content_cat_icon_path_large_{$catarray[$catid][0]}"]);
									$content_cat_icon_path_small	= $tp -> replaceConstants($content_pref["content_cat_icon_path_small_{$catarray[$catid][0]}"]);
									$delete_heading					= str_replace("&#39;", "\'", $row['content_heading']);
									$authordetails					= $aa -> getAuthor($row['content_author']);
									$caticon						= $content_cat_icon_path_large.$row['content_icon'];

									$pre = "";
									if($row['content_parent'] == "0"){		//main parent level
										$class			= "forumheader";
										$mainparentid	= $row['content_id'];
									}else{												//sub level
										$class = "forumheader3";
										for($b=0;$b<(count($catarray[$catid])/2)-1;$b++){
											$pre .= "_";
										}
									}

									$pcmusers = "";
									if($mode == "category"){
										$options = "<a href='".e_SELF."?cat.edit.".$catid."'>".CONTENT_ICON_EDIT."</a>
										<input type='image' title='delete' name='delete[cat_{$catid}]' src='".CONTENT_ICON_DELETE_BASE."' onclick=\"return jsconfirm('".$tp->toJS(CONTENT_ADMIN_JS_LAN_9."\\n\\n".CONTENT_ADMIN_JS_LAN_0."\\n\\n[".CONTENT_ADMIN_JS_LAN_6." ".$catid." : ".$delete_heading."]\\n\\n")."')\"/>";
									
									}elseif($mode == "manager"){
										$options = "<a href='".e_SELF."?manager.".$catid."'>".CONTENT_ICON_CONTENTMANAGER_SMALL."</a>";
										if(isset($row['content_pref'])){
											$pcmcontent_pref = $eArrayStorage->ReadArray($row['content_pref']);
										}
										if(isset($pcmcontent_pref["content_manager_allowed_{$catid}"])){
											$pcm		= explode(",", $pcmcontent_pref["content_manager_allowed_{$catid}"]);
											if($pcm[0]==""){ unset($pcm[0]); }
											$pcmusers	= count($pcm);
											$options	.= " ".($pcmusers ? "(".$pcmusers." ".($pcmusers == 1 ? CONTENT_ADMIN_CAT_LAN_54 : CONTENT_ADMIN_CAT_LAN_55).")" : "");
										}
									}

									$text .= "
									".($row['content_parent'] == 0 ? "<tr><td colspan='5' $stylespacer></td></tr>" : "")."
									<tr>
										<td class='".$class."' style='width:5%; text-align:left'>".$catid."</td>
										<td class='".$class."' style='width:5%; text-align:center'>".($row['content_icon'] ? "<img src='".$caticon."' alt='' style='vertical-align:middle' />" : "&nbsp;")."</td>
										<td class='".$class."' style='width:15%'>".($authordetails[0] != "0" ? "<a href='".e_BASE."user.php?id.".$authordetails[0]."'>".CONTENT_ICON_USER."</a>" : "")." ".$authordetails[1]."</td>
										<td class='".$class."' style='width:65%; white-space:nowrap;'>
											 <a href='".$plugindir."content.php?cat.".$row['content_id']."'>".CONTENT_ICON_LINK."</a> 
											".$pre.$row['content_heading']." ".($row['content_subheading'] ? "[".$row['content_subheading']."]" : "")."											
										</td>
										<td class='".$class."' style='width:10%; text-align:left; white-space:nowrap;'>
											".$options."
										</td>
									</tr>";
								}
							}
							$text .= "
							</table>
							".$rs -> form_close()."
							</div>";
						}
						$ns -> tablerender(CONTENT_ADMIN_CAT_LAN_10, $text);
						unset($row['content_id'], $row['content_heading'], $row['content_subheading'], $row['content_text'], $row['content_icon']);
		}

		function show_create_category(){
						global $qs, $plugintable, $plugindir, $sql, $ns, $rs, $aa, $fl, $pref, $tp;
						global $message, $content_parent, $content_heading, $content_subheading, $content_text, $content_icon, $content_comment, $content_rate, $content_pe, $content_class;
						global $stylespacer, $TOPIC_ROW_SPACER, $TOPIC_ROW, $TOPIC_ROW_NOEXPAND;

						$months = array(CONTENT_ADMIN_DATE_LAN_0, CONTENT_ADMIN_DATE_LAN_1, CONTENT_ADMIN_DATE_LAN_2, CONTENT_ADMIN_DATE_LAN_3, CONTENT_ADMIN_DATE_LAN_4, CONTENT_ADMIN_DATE_LAN_5, CONTENT_ADMIN_DATE_LAN_6, CONTENT_ADMIN_DATE_LAN_7, CONTENT_ADMIN_DATE_LAN_8, CONTENT_ADMIN_DATE_LAN_9, CONTENT_ADMIN_DATE_LAN_10, CONTENT_ADMIN_DATE_LAN_11);

						if(!is_object($sql)){ $sql = new db; }
						$formurl = e_SELF."?".e_QUERY;
						$array							= $aa -> getCategoryTree("", "", FALSE);
						$mainparent						= $aa -> getMainParent( (isset($qs[3]) && is_numeric($qs[3]) ? $qs[3] : (isset($qs[2]) && is_numeric($qs[2]) ? $qs[2] : "0") ) );						
						$content_pref					= $aa -> getContentPref($mainparent);
						$content_cat_icon_path_small	= $tp -> replaceConstants($content_pref["content_cat_icon_path_small_{$mainparent}"]);
						$content_cat_icon_path_large	= $tp -> replaceConstants($content_pref["content_cat_icon_path_large_{$mainparent}"]);

						if( $qs[0] == "cat" && $qs[1] == "create" && isset($qs[2]) && is_numeric($qs[2]) ){
							if(!$sql -> db_Select($plugintable, "*", "content_id='".$qs[2]."' ")){
								header("location:".e_SELF."?cat"); exit;
							}
							//$formurl = e_SELF."?".e_QUERY.".pc";
							$formurl = e_SELF."?".e_QUERY;
						}
						if( $qs[0] == "cat" && $qs[1] == "edit" && isset($qs[2]) && is_numeric($qs[2]) ){
							if(!$sql -> db_Select($plugintable, "*", "content_id='".$qs[2]."' ")){
								header("location:".e_SELF."?cat"); exit;
							}else{
								$row = $sql -> db_Fetch();
								if(substr($row['content_parent'],0,1) != "0"){
									header("location:".e_SELF."?cat"); exit;
								}
							}
							$formurl = e_SELF."?".e_QUERY;
						}
						
						if(isset($_POST['preview_category'])){
							$formurl = e_SELF."?".e_QUERY;

							$cat_heading			= $tp -> post_toHTML($_POST['cat_heading']);
							$cat_subheading			= $tp -> post_toHTML($_POST['cat_subheading']);
							$cat_text				= $tp -> post_toHTML($_POST['cat_text']);
							
							$text = "
							<div style='text-align:center'>
							<table class='fborder' style='".ADMIN_WIDTH."' border='0'>
							<tr>
								<td class='forumheader3' rowspan='3' style='width:5%; white-space:nowrap; vertical-align:top;'><img src='".$content_cat_icon_path_large.$_POST['cat_icon']."' style='border:0' alt='' /></td>
								<td class='fcaption'>".$cat_heading."</td>
							</tr>
							<tr><td class='forumheader3'>".$cat_subheading."</td></tr>
							<tr><td class='forumheader3'>".$cat_text."</td></tr>
							<tr><td colspan='2'>&nbsp;</td></tr>
							<tr><td class='forumheader3'>start date</td><td class='forumheader3'>
								".($_POST['ne_day'] != "none" ? $_POST['ne_day'] : "")." ".$months[($_POST['ne_month']-1)]." ".($_POST['ne_year'] != "none" ? $_POST['ne_year'] : "")."
							</td></tr>
							<tr><td class='forumheader3'>end date</td><td class='forumheader3'>
								".($_POST['end_day'] != "none" ? $_POST['end_day'] : "")." ".$months[($_POST['end_month']-1)]." ".($_POST['end_year'] != "none" ? $_POST['end_year'] : "")."
							</td></tr>
							<tr><td class='forumheader3'>class</td><td class='forumheader3'>".r_userclass_name($_POST['cat_class'])."</td></tr>
							<tr><td class='forumheader3'>comments</td><td class='forumheader3'>".($_POST['cat_comment'] == "1" ? "enabled" : "disabled")."</td></tr>
							<tr><td class='forumheader3'>rating</td><td class='forumheader3'>".($_POST['cat_rate'] == "1" ? "enabled" : "disabled")."</td></tr>
							<tr><td class='forumheader3'>print/email icons</td><td class='forumheader3'>".($_POST['cat_pe'] == "1" ? "enabled" : "disabled")."</td></tr>
							</table>
							</div>";
								  
							$ns -> tablerender($cat_heading, $text);
						}

						if( isset($_POST['preview_category']) || isset($message) || isset($_POST['uploadcaticon']) ){
							$row['content_heading']		= $tp -> post_toForm($_POST['cat_heading']);
							$row['content_subheading']	= $tp -> post_toForm($_POST['cat_subheading']);
							$row['content_text']		= $tp -> post_toForm($_POST['cat_text']);
							$ne_day						= $_POST['ne_day'];
							$ne_month					= $_POST['ne_month'];
							$ne_year					= $_POST['ne_year'];
							$end_day					= $_POST['end_day'];
							$end_month					= $_POST['end_month'];
							$end_year					= $_POST['end_year'];
							$row['content_icon']		= $_POST['cat_icon'];
							$row['content_comment']		= $_POST['cat_comment'];
							$row['content_rate']		= $_POST['cat_rate'];
							$row['content_pe']			= $_POST['cat_pe'];
							$row['content_class']		= $_POST['cat_class'];

						}

						$text = "
						<div style='text-align:center'>
						".$rs -> form_open("post", $formurl, "dataform", "", "enctype='multipart/form-data'")."
						<table class='fborder' style='".ADMIN_WIDTH."'>";
						
						//category parent
						$TOPIC_TOPIC = CONTENT_ADMIN_CAT_LAN_27;
						if($qs[1] == "create"){
							$parent = (isset($qs[3]) && is_numeric($qs[3]) ? $qs[3] : (isset($qs[2]) && is_numeric($qs[2]) ? $qs[2] : "0") );
						}elseif($qs[1] == "edit"){
							if(isset($qs[3]) && is_numeric($qs[3])){
								$parent = $qs[3];
							}else{
								$parent	= ( strpos($row['content_parent'], ".") ? substr($row['content_parent'],2) : "");
							}
						}
						$TOPIC_FIELD = $aa -> ShowOptionCat($parent).$rs->form_hidden("parent", "");
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW_NOEXPAND);
						$text .= $TOPIC_ROW_SPACER;

						//heading
						$row['content_heading'] = (isset($row['content_heading']) ? $row['content_heading'] : "");
						$TOPIC_TOPIC = CONTENT_ADMIN_CAT_LAN_2;
						$TOPIC_FIELD = $rs -> form_text("cat_heading", 90, $row['content_heading'], 250);
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW_NOEXPAND);

						//subheading
						$row['content_subheading'] = (isset($row['content_subheading']) ? $row['content_subheading'] : "");
						$TOPIC_TOPIC = CONTENT_ADMIN_CAT_LAN_3;
						$TOPIC_FIELD = $rs -> form_text("cat_subheading", 90, $row['content_subheading'], 250);
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW_NOEXPAND);

						//text
						$row['content_text'] = (isset($row['content_text']) ? $row['content_text'] : "");
						require_once(e_HANDLER."ren_help.php");
						$TOPIC_TOPIC = CONTENT_ADMIN_CAT_LAN_4;
						$insertjs = (!$pref['wysiwyg'] ? "onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'" : "");
						$TOPIC_FIELD = $rs -> form_textarea("cat_text", 80, 20, $row['content_text'], $insertjs)."<br />";
						if (!$pref['wysiwyg']) { $TOPIC_FIELD .= $rs -> form_text("helpb", 90, '', '', "helpbox")."<br />". display_help("helpb"); }
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW_NOEXPAND);


						if(isset($row['content_datestamp']) && $row['content_datestamp'] != "0"){
							$startdate = getdate($row['content_datestamp']);
							$ne_day = $startdate['mday'];
							$ne_month = $startdate['mon'];
							$ne_year = $startdate['year'];
						}else{
							$ne_day = (isset($ne_day) ? $ne_day : "");
							$ne_month = (isset($ne_month) ? $ne_month : "");
							$ne_year = (isset($ne_year) ? $ne_year : "");
						}
						if(isset($row['content_enddate']) && $row['content_enddate'] != "0"){
							$enddate = getdate($row['content_enddate']);
							$end_day = $enddate['mday'];
							$end_month = $enddate['mon'];
							$end_year = $enddate['year'];
						}else{
							$end_day = (isset($end_day) ? $end_day : "");
							$end_month = (isset($end_month) ? $end_month : "");
							$end_year = (isset($end_year) ? $end_year : "");
						}

						$smarray = getdate();
						$current_year = $smarray['year'];

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
							".$rs -> form_option(CONTENT_ADMIN_DATE_LAN_12, 1, "none");
							for($count=1; $count<=31; $count++){
								$TOPIC_FIELD .= $rs -> form_option($count, ($end_day == $count ? "1" : "0"), $count);
							}
							$TOPIC_FIELD .= $rs -> form_select_close()."
							".$rs -> form_select_open("end_month")."
							".$rs -> form_option(CONTENT_ADMIN_DATE_LAN_13, 1, "none");
							for($count=1; $count<=12; $count++){
								$TOPIC_FIELD .= $rs -> form_option($months[($count-1)], ($end_month == $count ? "1" : "0"), $count);
							}
							$TOPIC_FIELD .= $rs -> form_select_close()."
							".$rs -> form_select_open("end_year")."
							".$rs -> form_option(CONTENT_ADMIN_DATE_LAN_14, 1, "none");
							for($count=($current_year-5); $count<=$current_year; $count++){
								$TOPIC_FIELD .= $rs -> form_option($count, ($end_year == $count ? "1" : "0"), $count);
							}
							$TOPIC_FIELD .= $rs -> form_select_close()."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						$rejectlist = array('$.','$..','/','CVS','thumbs.db','Thumbs.db','*._$', 'index', 'null*');
						$iconlist = $fl->get_files($content_cat_icon_path_large,"",$rejectlist);

						//icon
						$row['content_icon'] = (isset($row['content_icon']) ? $row['content_icon'] : "");
						$TOPIC_TOPIC = CONTENT_ADMIN_CAT_LAN_5;
						$TOPIC_HEADING = CONTENT_ADMIN_CAT_LAN_49;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = "";

						//choose icon
						$TOPIC_FIELD .= "
						1 <a href='javascript:void(0);' onclick=\"expandit('diviconex')\">".CONTENT_ADMIN_CAT_LAN_60."</a><br />
						<div id='diviconex' style='display:none'>
							".$rs -> form_text("cat_icon", 60, $row['content_icon'], 100)."
							".$rs -> form_button("button", '', CONTENT_ADMIN_CAT_LAN_8, "onclick=\"expandit('divcaticon')\"")."
							<div id='divcaticon' style='{head}; display:none'>";
							foreach($iconlist as $icon){
								$TOPIC_FIELD .= "<a href=\"javascript:insertext('".$icon['fname']."','cat_icon','divcaticon')\"><img src='".$icon['path'].$icon['fname']."' style='border:0' alt='' /></a> ";
							}
							$TOPIC_FIELD .= "</div>";
						$TOPIC_FIELD .= "</div><br />";

						//upload icon
						$TOPIC_FIELD .= "
						2 <a href='javascript:void(0);' onclick=\"expandit('diviconnew')\">".CONTENT_ADMIN_CAT_LAN_61."</a><br />
						<div id='diviconnew' style='display:none'>";
							if(!FILE_UPLOADS){
								$TOPIC_FIELD .= "<b>".CONTENT_ADMIN_ITEM_LAN_21."</b>";
							}else{
								if(!is_writable($content_cat_icon_path_large)){
									$TOPIC_FIELD .= "<b>".CONTENT_ADMIN_ITEM_LAN_22." ".$content_cat_icon_path_large." ".CONTENT_ADMIN_ITEM_LAN_23."</b><br />";
								}
								$TOPIC_FIELD .= CONTENT_ADMIN_CAT_LAN_62."
								<input class='tbox' type='file' name='file_userfile[]'  size='58' /> 
								<input type='hidden' name='iconpathlarge' value='".$content_cat_icon_path_large."'>
								<input type='hidden' name='iconpathsmall' value='".$content_cat_icon_path_small."'>
								<input class='button' type='submit' name='uploadcaticon' value='".CONTENT_ADMIN_CAT_LAN_63."' />";
							}
						$TOPIC_FIELD .= "</div><br />";

						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);
						
						//comments
						$row['content_comment'] = (isset($row['content_comment']) ? $row['content_comment'] : "");
						$TOPIC_TOPIC = CONTENT_ADMIN_CAT_LAN_14;
						$TOPIC_HEADING = CONTENT_ADMIN_CAT_LAN_45;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = "
						".$rs -> form_radio("cat_comment", "1", ($row['content_comment'] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
						".$rs -> form_radio("cat_comment", "0", ($row['content_comment'] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//rating
						$row['content_rate'] = (isset($row['content_rate']) ? $row['content_rate'] : "");
						$TOPIC_TOPIC = CONTENT_ADMIN_CAT_LAN_15;
						$TOPIC_HEADING = CONTENT_ADMIN_CAT_LAN_46;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = "
						".$rs -> form_radio("cat_rate", "1", ($row['content_rate'] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
						".$rs -> form_radio("cat_rate", "0", ($row['content_rate'] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//print/email/pdf icons
						$row['content_pe'] = (isset($row['content_pe']) ? $row['content_pe'] : "");
						$TOPIC_TOPIC = CONTENT_ADMIN_CAT_LAN_16;
						$TOPIC_HEADING = CONTENT_ADMIN_CAT_LAN_47;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = "
						".$rs -> form_radio("cat_pe", "1", ($row['content_pe'] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
						".$rs -> form_radio("cat_pe", "0", ($row['content_pe'] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//userclass
						$row['content_class'] = (isset($row['content_class']) ? $row['content_class'] : "");
						$TOPIC_TOPIC = CONTENT_ADMIN_CAT_LAN_17;
						$TOPIC_HEADING = CONTENT_ADMIN_CAT_LAN_48;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = r_userclass("cat_class",$row['content_class'], "CLASSES");
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						$text .= $TOPIC_ROW_SPACER;
						$text .= "
						<tr>
							<td class='forumheader' style='text-align:center' colspan='2'>";
							if($qs[1] == "edit" && is_numeric($qs[2]) ){
								$js = "onclick=\"document.getElementById('parent').value = document.getElementById('parent1').options[document.getElementById('parent1').selectedIndex].label;\" ";
								$text .= $rs -> form_button("submit", "preview_category", (isset($_POST['preview_category']) ? CONTENT_ADMIN_MAIN_LAN_27 : CONTENT_ADMIN_MAIN_LAN_26), $js);
								$text .= $rs -> form_button("submit", "update_category", CONTENT_ADMIN_CAT_LAN_7, $js).$rs -> form_button("submit", "category_clear", CONTENT_ADMIN_CAT_LAN_21).$rs -> form_hidden("cat_id", $qs[2]).$rs -> form_hidden("id", $qs[2]);
								$caption = CONTENT_ADMIN_CAT_LAN_1;
							}else{
								$js = "onclick=\"document.getElementById('parent').value = document.getElementById('parent1').options[document.getElementById('parent1').selectedIndex].label;\" ";
								$text .= $rs -> form_button("submit", "preview_category", (isset($_POST['preview_category']) ? CONTENT_ADMIN_MAIN_LAN_27 : CONTENT_ADMIN_MAIN_LAN_26), $js);
								$text .= $rs -> form_button("submit", "create_category", CONTENT_ADMIN_CAT_LAN_6, $js);
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
						global $content_shortcodes, $tp, $sql, $ns, $rs, $plugintable, $plugindir, $aa, $eArrayStorage;
						global $CONTENT_CONTENTMANAGER_CATEGORY, $CONTENT_CONTENTMANAGER_TABLE, $CONTENT_CONTENTMANAGER_TABLE_START, $CONTENT_CONTENTMANAGER_TABLE_END;
						$personalmanagercheck = FALSE;

						if(!$CONTENT_CONTENTMANAGER_TABLE){
							require_once($plugindir."templates/content_manager_template.php");
						}
						$array		= $aa -> getCategoryTree("", "", TRUE);
						$catarray	= array_keys($array);
						$content_contentmanager_table_string = "";
						foreach($catarray as $catid){
							if($sql -> db_Select($plugintable, "content_id, content_heading, content_pref", " content_id='".$catid."' ")){
								$row = $sql -> db_Fetch();
								$content_pref = $eArrayStorage->ReadArray($row['content_pref']);

								if(isset($content_pref["content_manager_allowed_{$row['content_id']}"])){
									$pcm = explode(",", $content_pref["content_manager_allowed_{$row['content_id']}"]);
								}else{
									$pcm = array();
								}
								if(in_array($userid, $pcm) || getperms("0")){
									$personalmanagercheck = TRUE;
									$content_contentmanager_table_string .= $tp -> parseTemplate($CONTENT_CONTENTMANAGER_TABLE, FALSE, $content_shortcodes);
								}
							}
						}
						if($personalmanagercheck == TRUE){
							$text = $CONTENT_CONTENTMANAGER_TABLE_START.$content_contentmanager_table_string.$CONTENT_CONTENTMANAGER_TABLE_END;
							$ns -> tablerender(CONTENT_ADMIN_ITEM_LAN_56, $text);
						}else{
							header("location:".$plugindir."content.php"); exit;
						}
		}

		function show_order(){
						global $qs, $sql, $ns, $rs, $aa, $plugintable, $plugindir, $tp, $stylespacer;

						if(!getperms("0")){ header("location:".e_SELF); exit; }

						$catarray	= $aa -> getCategoryTree("", "", FALSE);
						$array		= array_keys($catarray);

						if(!is_array($array)){
							$text = "<div style='text-align:center;'>".CONTENT_ADMIN_CAT_LAN_9."</div>";
						}else{
							$text = "
							<div style='text-align:center'>
							".$rs -> form_open("post", e_SELF."?order", "orderform")."
							<table class='fborder' style='".ADMIN_WIDTH."'>							
							<tr>
								<td class='fcaption' style='width:5%'>".CONTENT_ADMIN_CAT_LAN_24."</td>
								<td class='fcaption' style='width:5%'>".CONTENT_ADMIN_CAT_LAN_25."</td>
								<td class='fcaption' style='width:15%'>".CONTENT_ADMIN_CAT_LAN_18."</td>
								<td class='fcaption' style='width:50%'>".CONTENT_ADMIN_CAT_LAN_19."</td>
								<td class='fcaption' style='width:5%; text-align:center; white-space:nowrap;'>".CONTENT_ADMIN_ITEM_LAN_58."</td>
								<td class='fcaption' style='width:5%; text-align:center; white-space:nowrap;'>".CONTENT_ADMIN_ITEM_LAN_59."</td>
								<td class='fcaption' style='width:5%; text-align:center; white-space:nowrap;'>".CONTENT_ADMIN_ITEM_LAN_60."</td>
							</tr>";

							if(!is_object($sql)){ $sql = new db; }
							foreach($array as $catid){
								if(!$category_total = $sql -> db_Select($plugintable, "*", "content_id='".$catid."' ")){
									$text .= "<div style='text-align:center;'>".CONTENT_ADMIN_CAT_LAN_9."</div>";
								}else{
									$row = $sql -> db_Fetch();

									$content_pref					= $aa -> getContentPref($catarray[$catid][0]);
									$content_cat_icon_path_large	= $tp -> replaceConstants($content_pref["content_cat_icon_path_large_{$catarray[$catid][0]}"]);
									$content_cat_icon_path_small	= $tp -> replaceConstants($content_pref["content_cat_icon_path_small_{$catarray[$catid][0]}"]);
									$authordetails					= $aa -> getAuthor($row['content_author']);
									$caticon						= $content_cat_icon_path_large.$row['content_icon'];

									$pre = "";
									if($row['content_parent'] == "0"){		//main parent level
										$class = "forumheader";
									}else{									//sub level
										$class = "forumheader3";
										for($b=0;$b<(count($catarray[$catid])/2)-1;$b++){
											$pre .= "_";
										}
									}

									if($row['content_parent'] == 0){
										$subs		= $aa -> getCategoryTree("", $row['content_id'], FALSE);
										$ordermax	= count($subs);
									}
									
									//count items in category
									$sqlc = new db;
									$n = $sqlc -> db_Count($plugintable, "(*)", "WHERE content_parent='".$catid."' AND content_refer != 'sa' ");
									if($n > 1){
										$ordercat = "<a href='".e_SELF."?order.".$catarray[$catid][0].".".$catid."'>".CONTENT_ICON_ORDERCAT."</a>";
										$ordercatall = ($row['content_parent'] == 0 ? "<a href='".e_SELF."?order.".$catid."'>".CONTENT_ICON_ORDERALL."</a>" : "");
									}else{
										$ordercat = "";
										$ordercatall = "";
									}
									$amount = "(".($n == 1 ? $n." ".CONTENT_ADMIN_CAT_LAN_56 : $n." ".CONTENT_ADMIN_CAT_LAN_57).")";
									
									$up = "";
									$down = "";
									$selectorder = "";
									if($ordermax > 1){
										//up arrow
										if($row['content_order'] != 1 && $row['content_order'] != 0){
											$up = "<a href='".e_SELF."?order.inc.".$catid."-".$row['content_order']."'>".CONTENT_ICON_ORDER_UP."</a> ";
										}else{
											$up = "&nbsp;&nbsp;&nbsp;";
										}
										//down arrow
										if($row['content_order'] != $ordermax){
											$down = "<a href='".e_SELF."?order.dec.".$catid."-".$row['content_order']."'>".CONTENT_ICON_ORDER_DOWN."</a>";
										}else{
											$down = "&nbsp;&nbsp;&nbsp;";
										}
										//select box
										if($ordermax > 1){
											$selectorder = "
											<select name='order[]' class='tbox'>";
											for($k=1;$k<=$ordermax;$k++){
												$selectorder .= $rs -> form_option($k, ($row['content_order'] == $k ? "1" : "0"), $catid.".".$k.".cat");
											}
											$selectorder .= "</select>";
										}else{
											$selectorder = "";
										}
									}

									$text .= "
									".($row['content_parent'] == 0 ? "<tr><td colspan='5' $stylespacer></td></tr>" : "")."
									<tr>
										<td class='".$class."' style='width:5%; text-align:left'>".$catid."</td>
										<td class='".$class."' style='width:5%; text-align:center'>".($row['content_icon'] ? "<img src='".$caticon."' alt='' style='vertical-align:middle' />" : "&nbsp;")."</td>
										<td class='".$class."' style='width:15%'>".($authordetails[0] != "0" ? "<a href='".e_BASE."user.php?id.".$authordetails[0]."'>".CONTENT_ICON_USER."</a>" : "")." ".$authordetails[1]."</td>
										<td class='".$class."' style='width:50%; white-space:nowrap;'>
											<a href='".$plugindir."content.php?cat.".$row['content_id']."'>".CONTENT_ICON_LINK."</a> 
											".$pre.$row['content_heading']." ".($row['content_subheading'] ? "[".$row['content_subheading']."]" : "")." ".$amount."
										</td>
										<td class='".$class."' style='width:5%; text-align:left; white-space:nowrap;'>
											".$ordercat."
											".$ordercatall."
										</td>
										<td class='".$class."' style='width:5%; text-align:center; white-space:nowrap;'>
											".$up." 
											".$down."
										</td>
										<td class='".$class."' style='width:5%; text-align:center; white-space:nowrap;'>
											".$selectorder."
										</td>
									</tr>";
								}
							}
							$text .= "
							<tr><td colspan='7' $stylespacer></td></tr>
							<tr>
								<td class='fcaption' colspan='5'>&nbsp;</td>
								<td class='fcaption' colspan='2' style='text-align:center'>
									".$rs -> form_button("submit", "update_order", CONTENT_ADMIN_ITEM_LAN_61)."
								</td>
							</tr>
							</table>
							".$rs -> form_close()."
							</div>";

						}
						$ns -> tablerender(CONTENT_ADMIN_ITEM_LAN_62, $text);

		}

		function show_content_order($mode){
						global $sql, $ns, $rs, $qs, $plugintable, $plugindir, $aa, $tp;

						$allcats = $aa -> getCategoryTree("", "", FALSE);
						if($mode == "ci"){
							$formtarget		= e_SELF."?order.".$qs[1].".".$qs[2];
							$qry			= "content_parent = '".$qs[2]."' ";
							$order			= "SUBSTRING_INDEX(content_order, '.', 1)+0";

						}elseif($mode == "ai"){
							$array			= $aa -> getCategoryTree("", $qs[1], FALSE);
							$validparent	= implode(",", array_keys($array));
							$qry			= " content_parent REGEXP '".$aa -> CONTENTREGEXP($validparent)."' ";
							$formtarget		= e_SELF."?order.".$qs[1];
							$order			= "SUBSTRING_INDEX(content_order, '.', -1)+0";
						}
						$content_pref		= $aa -> getContentPref($qs[1]);
						$content_icon_path	= $tp -> replaceConstants($content_pref["content_icon_path_{$qs[1]}"]);

						$sqlo = new db;
						if(!$content_total = $sqlo -> db_Select($plugintable, "content_id, content_heading, content_author, content_parent, content_order", "content_refer != 'sa' AND ".$qry." ORDER BY ".$order." ASC, content_heading DESC ")){
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

							while($row = $sqlo -> db_Fetch()){
									$delete_heading	= str_replace("&#39;", "\'", $row['content_heading']);
									$authordetails	= $aa -> getAuthor($row['content_author']);
									$caticon		= (isset($row['content_icon']) ? $content_icon_path.$row['content_icon'] : "");
									$deleteicon		= CONTENT_ICON_DELETE;

									$tmp = explode(".", $row['content_order']);
									if(!$tmp[1]){ $tmp[1] = "0"; }
									$row['content_order'] = $tmp[0]."-".$tmp[1];

									if($mode == "ci"){
										$ordercheck		= $tmp[0];
										$ordercheck2	= $tmp[1];
										$qrystring		= "order.".$qs[1].".".$qs[2];
									}elseif($mode == "ai"){
										$ordercheck		= $tmp[1];
										$ordercheck2	= $tmp[0];
										$qrystring		= "order.".$qs[1];
									}
									$cid	= $row['content_id'];
									$corder	= $row['content_order'];

									if(array_key_exists($row['content_parent'], $allcats)){
										$mainparentid = $allcats[$row['content_parent']][0];
									}
									//up arrow
									if($ordercheck != 1 && $ordercheck != 0){
										$up = "<a href='".e_SELF."?".$qrystring.".inc.".$cid."-".$corder."'>".CONTENT_ICON_ORDER_UP."</a> ";
									}else{
										$up = "&nbsp;&nbsp;&nbsp;";
									}
									//down arrow
									if($ordercheck != $content_total){
										$down = "<a href='".e_SELF."?".$qrystring.".dec.".$cid."-".$corder."'>".CONTENT_ICON_ORDER_DOWN."</a>";
									}else{
										$down = "&nbsp;&nbsp;&nbsp;";
									}

									$text .= "
									<tr>
										<td class='forumheader3' style='width:5%; text-align:center; white-space:nowrap;'>".$cid."</td>
										<td class='forumheader3' style='width:15%; text-align:left; white-space:nowrap;'>
											".($authordetails[0] != "0" ? "<a href='".e_BASE."user.php?id.".$authordetails[0]."'>".CONTENT_ICON_USER."</a>" : "")." ".$authordetails[1]."
										</td>
										<td class='forumheader3' style='width:70%; text-align:left;'>
											<a href='".$plugindir."content.php?content.".$row['content_id']."'>".CONTENT_ICON_LINK."</a> 
											".$row['content_heading']." (".$row['content_order'].")</td>
										<td class='forumheader3' style='width:5%; text-align:center; white-space:nowrap;'>
											".$up."
											".$down."
										</td>
										<td class='forumheader3' style='width:5%; text-align:center; white-space:nowrap;'>
											<select name='order[]' class='tbox'>";
											for($k=1;$k<=$content_total;$k++){
												$text .= $rs -> form_option($k, ($ordercheck == $k ? "1" : "0"), $cid.".".$k.".".$mode.".".$corder);
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
						global $sql, $ns, $rs, $qs, $plugintable, $plugindir;

						if(!is_object($sql)){ $sql = new db; }
						
						$newcontent = $sql -> db_Count($plugintable, "(*)", "");
						if($newcontent > 0){

							$upgrade = FALSE;
							if($thiscount = $sql -> db_Select("pcontent", "*", "ORDER BY content_id ", "mode=no_where" )){
								while($row = $sql -> db_Fetch()){
									if( strpos($row['content_parent'], ".") && substr($row['content_parent'],0,1) != "0"){
										//if item with old parent value exists, you need to upgrade
										$upgrade = TRUE;
									}
								}
							}
							if($upgrade === FALSE){
								return false;
							}

							$text .= "
							<div style='text-align:center'>
							".$rs -> form_open("post", $plugindir."admin_content_convert.php", "dataform")."
							<table class='fborder' style='".ADMIN_WIDTH."'>";
							
							$text .= "<tr><td class='fcaption' colspan='2'>UPGRADE CONTENT MANAGEMENT PLUGIN TABLE</td></tr>";
							$text .= "<tr><td class='forumheader3' colspan='2'>Please click the follwowing button to convert your content table used in the previous content management plugin, to the new improved version. You have to upgrade in order to keep using the content management plugin !</td></tr>";
							$text .= "
							<tr>
								<td class='forumheader3' style='width:50%; white-space:nowrap;'>UPGRADE</td>
								<td class='forumheader3' style='width:50%; white-space:nowrap;'>".$rs -> form_button("submit", "upgrade", "upgrade")."</td>
							</tr>";

							$text .= "</table>".$rs -> form_close()."
							</div>";

							$ns -> tablerender(CONTENT_ADMIN_MAIN_LAN_7, $text);
							return true;
						}else{

							$text .= "
							<div style='text-align:center'>
							".$rs -> form_open("post", $plugindir."admin_content_convert.php", "dataform")."
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
									<td class='forumheader3' style='width:50%; white-space:nowrap;'>".$rs -> form_button("button", "fresh", "create new category", "onclick=\"document.location='".$plugindir."admin_content_config.php?cat.create'\"
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

		function show_admin_contentmanager_category(){
						global $plugintable, $qs, $sql, $ns, $rs, $aa, $eArrayStorage;

						if(!getperms("0")){ header("location:".e_SELF); exit; }

						if(!is_object($sql)){ $sql = new db; }
						if(!$sql -> db_Select($plugintable, "content_id, content_heading, content_pref", "content_id='".$qs[1]."' ")){
							header("location:".e_SELF."?manager"); exit;
						}else{
							$row = $sql -> db_Fetch();
							$caption = CONTENT_ADMIN_CAT_LAN_30." : ".$row['content_heading'];
						}
						
						//get current preferences
						$mainparent			= $aa -> getMainParent( $qs[1] );						
						if($qs[1] == $mainparent){
							$content_pref	= $aa -> getContentPref($mainparent);
						}else{							
							$content_pref	= $eArrayStorage->ReadArray($row['content_pref']);
						}
						if(isset($content_pref["content_manager_allowed_{$qs[1]}"])){
							$pcm				= explode(",", $content_pref["content_manager_allowed_{$qs[1]}"]);
						}else{
							$pcm = array();
						}

						$sql2 = new db;
						$sql2->db_Select("user", "user_id, user_name, user_class, user_login", " user_perms != '0'");
						$c = 0;
						$d = 0;
						while ($row2 = $sql2->db_Fetch()) {
							if(isset($content_pref["content_manager_class_{$mainparent}"]) && check_class($content_pref["content_manager_class_{$mainparent}"])){
								if(in_array($row2['user_id'], $pcm)){
									$in_userid[$c]		= $row2['user_id'];
									$in_username[$c]	= $row2['user_name'];
									$in_userlogin[$c]	= $row2['user_login'] ? "(".$row2['user_login'].")" : "";
									$c++;
								} else {
									$out_userid[$d]		= $row2['user_id'];
									$out_username[$d]	= $row2['user_name'];
									$out_userlogin[$d]	= $row2['user_login'] ? "(".$row2['user_login'].")" : "";
									$d++;
								}
							}
						}
						
						$optionout = "";
						for ($a = 0; $a <= ($d-1); $a++) {
							$optionout .= "<option value=".$out_userid[$a].">".$out_username[$a]." ".$out_userlogin[$a]."</option>";
						}
						$optionin = "";
						$class_id = "";
						for($a = 0; $a <= ($c-1); $a++) {
							$optionin .= "<option value=".$in_userid[$a].">".$in_username[$a]." ".$in_userlogin[$a]."</option>";
							$class_id .= $in_userid[$a].".";
						}
						
						$text = "
						<div style='text-align:center'>
						".$rs -> form_open("post", e_SELF."?".e_QUERY, "managerform", "", "enctype='multipart/form-data'")."
						<table class='fborder' style='".ADMIN_WIDTH."'>
						<tr><td class='forumheader' style='text-align:center;'>".CONTENT_ADMIN_CAT_LAN_28."</td></tr>
						<tr><td class='forumheader3' style='text-align:center'>
					 		<table style='width:98%;'>
							<tr>
							<td style='width:50%; vertical-align:top'>".CONTENT_ADMIN_CAT_LAN_29."<br />
							
							<select class='tbox' id='assignclass1' name='assignclass1' size='10' style='width:220px' multiple='multiple' onchange='moveOver();'>
							".$optionout."				 
							</select>
							</td>
							<td style='width:50%; vertical-align:top'>".CONTENT_ADMIN_CAT_LAN_30."<br />
							<select class='tbox' id='assignclass2' name='assignclass2' size='10' style='width:220px' multiple='multiple'>
							".$optionin."
							</select><br /><br />
							<input class='button' type='button' value='".CONTENT_ADMIN_CAT_LAN_31."' onclick='removeMe();' />
							<input class='button' type='button' value='".CONTENT_ADMIN_CAT_LAN_32."' onclick='clearMe($qs[1]);' />
							<input type='hidden' name='class_id' value='".$class_id."' />
							</td>
							</tr>
							</table>
						</td></tr>
						<tr><td style='text-align:center' class='forumheader'>						
						".$rs -> form_button("button", "assign_admins", CONTENT_ADMIN_CAT_LAN_33, "onclick='saveMe($qs[1]);'")."
						".$rs -> form_hidden("cat_id", $qs[1])."
						</td>
						</tr>
						</table>
						".$rs -> form_close()."
						</div>";

						$oText = str_replace("'", "\'", CONTENT_ADMIN_CAT_LAN_41);
						$popuphelp = $aa -> popupHelp($oText, "", "", "");
						
						$ns -> tablerender($caption." ".$popuphelp, $text);
		}

		function show_options(){
						global $sql, $ns, $rs, $aa, $plugintable, $plugindir, $tp, $stylespacer;

						$sqlo = new db;
						if(!$category_total = $sqlo -> db_Select($plugintable, "*", "content_parent='0' ")){
							$text = "<div style='text-align:center;'>".CONTENT_ADMIN_CAT_LAN_9."</div>";
						}else{

							$text = "
							<div style='text-align:center'>
							".$rs -> form_open("post", e_SELF."?options", "optionsform","","", "")."
							<table style='".ADMIN_WIDTH."' class='fborder'>
							<tr>
							<td class='fcaption' style='width:5%'>".CONTENT_ADMIN_CAT_LAN_24."</td>
							<td class='fcaption' style='width:5%'>".CONTENT_ADMIN_CAT_LAN_25."</td>
							<td class='fcaption' style='width:15%'>".CONTENT_ADMIN_CAT_LAN_18."</td>
							<td class='fcaption' style='width:65%'>".CONTENT_ADMIN_CAT_LAN_19."</td>
							<td class='fcaption' style='width:10%; text-align:center'>".CONTENT_ADMIN_CAT_LAN_20."</td>
							</tr>";

							$content_pref					= $aa -> getContentPref(0);
							$content_cat_icon_path_large	= $tp -> replaceConstants($content_pref["content_cat_icon_path_large_0"]);
							$content_cat_icon_path_small	= $tp -> replaceConstants($content_pref["content_cat_icon_path_small_0"]);
							
							$text .= "
							<tr><td colspan='5' $stylespacer></td></tr>
							<tr>
								<td class='forumheader3' style='width:5%; text-align:left'></td>
								<td class='forumheader3' style='width:5%; text-align:center'></td>
								<td class='forumheader3' style='width:15%'></td>
								<td class='forumheader3' style='width:65%; white-space:nowrap;'>".CONTENT_ADMIN_OPT_LAN_218."</td>
								<td class='forumheader3' style='width:10%; text-align:center; white-space:nowrap;'>
									<a href='".e_SELF."?option.default'>".CONTENT_ICON_OPTIONS."</a>
								</td>
							</tr>
							<tr><td colspan='5' $stylespacer></td></tr>";

							while($row = $sqlo -> db_Fetch()){

								$content_pref					= $aa -> getContentPref($row['content_id']);
								$content_cat_icon_path_large	= $tp -> replaceConstants($content_pref["content_cat_icon_path_large_{$row['content_id']}"]);
								$content_cat_icon_path_small	= $tp -> replaceConstants($content_pref["content_cat_icon_path_small_{$row['content_id']}"]);
								$authordetails					= $aa -> getAuthor($row['content_author']);
								$caticon						= $content_cat_icon_path_large.$row['content_icon'];

								$text .= "								
								<tr>
									<td class='forumheader3' style='width:5%; text-align:left'>".$row['content_id']."</td>
									<td class='forumheader3' style='width:5%; text-align:center'>".($row['content_icon'] ? "<img src='".$caticon."' alt='' style='vertical-align:middle' />" : "&nbsp;")."</td>
									<td class='forumheader3' style='width:15%'>".($authordetails[0] != "0" ? "<a href='".e_BASE."user.php?id.".$authordetails[0]."'>".CONTENT_ICON_USER."</a>" : "")." ".$authordetails[1]."</td>
									<td class='forumheader3' style='width:65%; white-space:nowrap;'>
										<a href='".$plugindir."content.php?cat.".$row['content_id']."'>".CONTENT_ICON_LINK."</a> 
										".$row['content_heading']." ".($row['content_subheading'] ? "[".$row['content_subheading']."]" : "")."
									</td>
									<td class='forumheader3' style='width:10%; text-align:center; white-space:nowrap;'>
										<a href='".e_SELF."?option.".$row['content_id']."'>".CONTENT_ICON_OPTIONS."</a>
									</td>
								</tr>";
							}
							$text .= "
							</table>
							".$rs -> form_close()."
							</div>";
						}							

						$ns -> tablerender(CONTENT_ADMIN_MENU_LAN_6, $text);
		}

		function show_options_cat(){
						global $qs, $id, $sql, $ns, $rs, $aa, $content_pref, $pref, $content_cat_icon_path_large, $content_cat_icon_path_small, $plugintable, $plugindir;
						global $fl, $stylespacer, $TOPIC_ROW, $TOPIC_ROW_SPACER;

						if($qs[1] == "default"){
							$id = "0";
							$caption = CONTENT_ADMIN_OPT_LAN_0." : ".CONTENT_ADMIN_OPT_LAN_218;
						}elseif(is_numeric($qs[1])){
							$id = $qs[1];
							$sqlo = new db;
							if(!$sqlo -> db_Select($plugintable, "content_heading", "content_id='".$id."' AND content_parent = '0' ")){
								header("location:".e_SELF."?option"); exit;
							}else{
								while($rowo = $sqlo -> db_Fetch()){
									$caption = CONTENT_ADMIN_OPT_LAN_0." : ".$rowo['content_heading'];
								}
							}
						}else{
							header("location:".e_SELF."?option"); exit;
						}
						//check prefs two times to insure they are shown, if none present, the first inserts them, the second retrieves them						
						$content_pref		= $aa -> getContentPref($id);
						$content_pref		= $aa -> getContentPref($id);

						//define some variables
						$TOPIC_TABLE_END = $this->pref_submit()."</table></div>";

						$TOPIC_TITLE_ROW	= "<tr><td colspan='2' class='fcaption'>{TOPIC_CAPTION}</td></tr>";
						$TOPIC_HELP_ROW		= "<tr><td colspan='2' class='forumheader'>{TOPIC_HELP}</td></tr>";
						$TOPIC_TABLE_START	= "";

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

						function showhide(showid, arrhide){
							var i;
							for (i = 0; i < arrhide.length; i++){
								var hideit;
								hideit=arrhide[i];
								hide=document.getElementById(hideit).style;
								hide.display=\"none\";
							}
							show=document.getElementById(showid).style;
							show.display=\"\";							
						}

						function shows(showid){
							show1=document.getElementById(showid).style;
							show1.display=\"\";							
						}

						function hides(hideit){
							hide=document.getElementById(hideit).style;
							hide.display=\"none\";							
						}
						

						//-->
						</script>";


						$text .= "
						<div style='text-align:center'>
						<form method='post' name='optform' action='".e_SELF."?".e_QUERY."'>\n

						<div id='creation' style='text-align:center'>
						<table style='".ADMIN_WIDTH."' class='fborder'>";						
						
						$TOPIC_CAPTION = CONTENT_ADMIN_OPT_LAN_1;
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_TITLE_ROW);

						//content_admin_sections
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_2;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_3;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = "
						".$rs -> form_checkbox("content_admin_icon_{$id}", 1, (isset($content_pref["content_admin_icon_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_4."<br />
						".$rs -> form_checkbox("content_admin_attach_{$id}", 1, (isset($content_pref["content_admin_attach_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_5."<br />
						".$rs -> form_checkbox("content_admin_images_{$id}", 1, (isset($content_pref["content_admin_images_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_6."<br />
						".$rs -> form_checkbox("content_admin_comment_{$id}", 1, (isset($content_pref["content_admin_comment_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_7."<br />
						".$rs -> form_checkbox("content_admin_rating_{$id}", 1, (isset($content_pref["content_admin_rating_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_8."<br />
						".$rs -> form_checkbox("content_admin_score_{$id}", 1, (isset($content_pref["content_admin_score_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_9."<br />
						".$rs -> form_checkbox("content_admin_pe_{$id}", 1, (isset($content_pref["content_admin_pe_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_10."<br />
						".$rs -> form_checkbox("content_admin_visibility_{$id}", 1, (isset($content_pref["content_admin_visibility_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_11."<br />
						".$rs -> form_checkbox("content_admin_meta_{$id}", 1, (isset($content_pref["content_admin_meta_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_12."<br />
						".$rs -> form_checkbox("content_admin_layout_{$id}", 1, (isset($content_pref["content_admin_layout_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_205."<br />
						".$rs -> form_checkbox("content_admin_customtags_{$id}", 1, (isset($content_pref["content_admin_customtags_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_206."<br />
						".$rs -> form_checkbox("content_admin_presettags_{$id}", 1, (isset($content_pref["content_admin_presettags_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_221."<br />
						";
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

						//content_admin_custom_present_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_221;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_222;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_223;
						$TOPIC_FIELD = "";
						$i=0;
						$existing = 0;
						$TOPIC_FIELD .= "
						<div id='div_content_custom_preset' style='width:80%;'>";						
						for($i=0;$i<count($content_pref["content_custom_preset_key"]);$i++){
							if(!empty($content_pref["content_custom_preset_key"][$i])){
								//<span id='upline_ex' style='white-space:nowrap;'>
								$TOPIC_FIELD .= "
								<span style='white-space:nowrap;'>
									".$rs -> form_text("content_custom_preset_key[$existing]", 50, $content_pref["content_custom_preset_key"][$existing], 100)."
									".$rs -> form_button("button", "x", "x", "onclick=\"document.getElementById('content_custom_preset_key[$existing]').value='';\"", "", "")."
								</span>";
								$existing++;
							}
						}
						$url = e_PLUGIN."content/handlers/content_preset.php";
						$TOPIC_FIELD .= "
						<br />
						<span id='upline_new' style='white-space:nowrap;'></span><br />
						</div><br />
						<div id='upline_type' style='white-space:nowrap;'>
							".$rs -> form_select_open("type")."
							".$rs -> form_option(CONTENT_ADMIN_OPT_LAN_294, "1", "none", "")."
							".$rs -> form_option(CONTENT_ADMIN_OPT_LAN_295, "", "text", "onclick=\"open_window('".$url."?text' ,'400', '400');\"")."
							".$rs -> form_option(CONTENT_ADMIN_OPT_LAN_296, "", "area", "onclick=\"open_window('".$url."?area' ,'400', '400');\"")."
							".$rs -> form_option(CONTENT_ADMIN_OPT_LAN_297, "", "select", "onclick=\"open_window('".$url."?select' ,'400', '400');\"")."
							".$rs -> form_option(CONTENT_ADMIN_OPT_LAN_298, "", "date", "onclick=\"open_window('".$url."?date' ,'400', '400');\"")."
							".$rs -> form_option(CONTENT_ADMIN_OPT_LAN_299, "", "checkbox", "onclick=\"open_window('".$url."?checkbox' ,'400', '400');\"")."
							".$rs -> form_option(CONTENT_ADMIN_OPT_LAN_300, "", "radio", "onclick=\"open_window('".$url."?radio' ,'400', '400');\"")."
							".$rs -> form_select_close()."
						</div><br />";
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
						".$rs -> form_checkbox("content_submit_icon_{$id}", 1, (isset($content_pref["content_submit_icon_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_4."<br />
						".$rs -> form_checkbox("content_submit_attach_{$id}", 1, (isset($content_pref["content_submit_attach_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_5."<br />
						".$rs -> form_checkbox("content_submit_images_{$id}", 1, (isset($content_pref["content_submit_images_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_6."<br />
						".$rs -> form_checkbox("content_submit_comment_{$id}", 1, (isset($content_pref["content_submit_comment_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_7."<br />
						".$rs -> form_checkbox("content_submit_rating_{$id}", 1, (isset($content_pref["content_submit_rating_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_8."<br />
						".$rs -> form_checkbox("content_submit_score_{$id}", 1, (isset($content_pref["content_submit_score_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_9."<br />
						".$rs -> form_checkbox("content_submit_pe_{$id}", 1, (isset($content_pref["content_submit_pe_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_10."<br />
						".$rs -> form_checkbox("content_submit_visibility_{$id}", 1, (isset($content_pref["content_submit_visibility_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_11."<br />
						".$rs -> form_checkbox("content_submit_meta_{$id}", 1, (isset($content_pref["content_submit_meta_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_12."<br />
						".$rs -> form_checkbox("content_submit_layout_{$id}", 1, (isset($content_pref["content_submit_layout_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_205."<br />
						".$rs -> form_checkbox("content_submit_customtags_{$id}", 1, (isset($content_pref["content_submit_customtags_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_206."<br />
						".$rs -> form_checkbox("content_submit_presettags_{$id}", 1, (isset($content_pref["content_submit_presettags_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_221."<br />
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
						//$content_pref["content_submit_images_number_{$id}"] = ($content_pref["content_submit_images_number_{$id}"] ? $content_pref["content_submit_images_number_{$id}"] : "10");
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
						//$content_pref["content_submit_files_number_{$id}"] = ($content_pref["content_submit_files_number_{$id}"] ? $content_pref["content_submit_files_number_{$id}"] : "1");
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

						$text .= $TOPIC_ROW_SPACER;

						//content_theme_
						$dirlist = $fl->get_dirs($plugindir."templates/");
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


						
						//content_layoutscheme_
						if(!isset($content_pref["content_theme_{$id}"])){
							$dir = $plugindir."templates/default";
						}else{
							if(file_exists($plugindir."templates/".$content_pref["content_theme_{$id}"]."/content_content_template.php")){
								$dir = $plugindir."templates/".$content_pref["content_theme_{$id}"];
							}else{
								$dir = $plugindir."templates/default";
							}
						}
						//get_files($path, $fmask = '', $omit='standard', $recurse_level = 0, $current_level = 0, $dirs_only = FALSE)
						$rejectlist = array('$.','$..','/','CVS','thumbs.db','Thumbs.db','*._$', 'index', 'null*', '.bak');
						$templatelist = $fl->get_files($dir,"content_content_",$rejectlist);

						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_227;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_228;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = "
							".$rs -> form_select_open("content_layout_{$id}")."
							".$rs -> form_option(CONTENT_ADMIN_OPT_LAN_229, 0, "none");
							foreach($templatelist as $template){
								$templatename = substr($template['fname'], 25, -4);
								$templatename = ($template['fname'] == "content_content_template.php" ? "default" : $templatename);
								$TOPIC_FIELD .= $rs -> form_option($templatename, ($content_pref["content_layout_{$id}"] == $template['fname'] ? "1" : "0"), $template['fname']);
							}
							$TOPIC_FIELD .= $rs -> form_select_close()."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);
						

						
						/*
						//content_css_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_224;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_225;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_226;
						$TOPIC_FIELD = "
						<table style='width:90%; text-align:left;'>
							<tr>
								<td rowspan='2' style='width:5%; white-space:nowrap; vertical-align:top;'>
									".$rs -> form_radio("content_css_{$id}", "theme", ($content_pref["content_css_{$id}"] == "theme" ? "1" : "0"), "", "")."
								</td>
								<td><b>".CONTENT_ADMIN_OPT_LAN_230."</b></td>
							</tr>
							<tr><td>content_css.css in ".e_THEME."".$pref['sitetheme']."/<br /><br /></td></tr>
							<tr>
								<td rowspan='2' style='width:5%; white-space:nowrap; vertical-align:top;'>
									".$rs -> form_radio("content_css_{$id}", "ctemp", ($content_pref["content_css_{$id}"] == "ctemp" ? "1" : "0"), "", "")."
								</td>
								<td><b>".CONTENT_ADMIN_OPT_LAN_231."</b></td>
							</tr>
							<tr><td>content_css.css in ".$plugindir."templates/".$content_pref["content_theme_{$id}"]."/<br /><br /></td></tr>
							<tr>
								<td rowspan='2' style='width:5%; white-space:nowrap; vertical-align:top;'>
									".$rs -> form_radio("content_css_{$id}", "ctempdef", ($content_pref["content_css_{$id}"] == "ctempdef" ? "1" : "0"), "", "")."
								</td>
								<td><b>".CONTENT_ADMIN_OPT_LAN_232."</b></td>
							</tr>
							<tr><td>content_css.css in ".$plugindir."templates/default/<br /><br /></td></tr>
						</table>
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);
						*/


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
						//".$rs -> form_radio("content_breadcrumb_{$id}", "1", ($content_pref["content_breadcrumb_{$id}"] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
						//".$rs -> form_radio("content_breadcrumb_{$id}", "0", ($content_pref["content_breadcrumb_{$id}"] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
						$TOPIC_FIELD = "
						".$rs -> form_checkbox("content_breadcrumb_catall_{$id}", 1, ($content_pref["content_breadcrumb_catall_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_280."<br />
						".$rs -> form_checkbox("content_breadcrumb_cat_{$id}", 1, ($content_pref["content_breadcrumb_cat_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_281."<br />
						".$rs -> form_checkbox("content_breadcrumb_authorall_{$id}", 1, ($content_pref["content_breadcrumb_authorall_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_282."<br />
						".$rs -> form_checkbox("content_breadcrumb_author_{$id}", 1, ($content_pref["content_breadcrumb_author_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_283."<br />
						".$rs -> form_checkbox("content_breadcrumb_recent_{$id}", 1, ($content_pref["content_breadcrumb_recent_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_284."<br />
						".$rs -> form_checkbox("content_breadcrumb_item_{$id}", 1, ($content_pref["content_breadcrumb_item_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_285."<br />
						".$rs -> form_checkbox("content_breadcrumb_archive_{$id}", 1, ($content_pref["content_breadcrumb_archive_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_287."<br />
						".$rs -> form_checkbox("content_breadcrumb_top_{$id}", 1, ($content_pref["content_breadcrumb_top_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_286."<br />
						".$rs -> form_checkbox("content_breadcrumb_score_{$id}", 1, ($content_pref["content_breadcrumb_score_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_288."<br />
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

						/*
						//content_searchmenu_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_67;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_68;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_69;
						$TOPIC_FIELD = "
						".$rs -> form_radio("content_searchmenu_{$id}", "1", ($content_pref["content_searchmenu_{$id}"] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
						".$rs -> form_radio("content_searchmenu_{$id}", "0", ($content_pref["content_searchmenu_{$id}"] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);
						*/



						//content_navigator_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_273;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_274;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_275;
						$TOPIC_FIELD = "
						".$rs -> form_checkbox("content_navigator_catall_{$id}", 1, ($content_pref["content_navigator_catall_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_280."<br />
						".$rs -> form_checkbox("content_navigator_cat_{$id}", 1, ($content_pref["content_navigator_cat_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_281."<br />
						".$rs -> form_checkbox("content_navigator_authorall_{$id}", 1, ($content_pref["content_navigator_authorall_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_282."<br />
						".$rs -> form_checkbox("content_navigator_author_{$id}", 1, ($content_pref["content_navigator_author_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_283."<br />
						".$rs -> form_checkbox("content_navigator_recent_{$id}", 1, ($content_pref["content_navigator_recent_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_284."<br />
						".$rs -> form_checkbox("content_navigator_item_{$id}", 1, ($content_pref["content_navigator_item_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_285."<br />
						".$rs -> form_checkbox("content_navigator_archive_{$id}", 1, ($content_pref["content_navigator_archive_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_287."<br />
						".$rs -> form_checkbox("content_navigator_top_{$id}", 1, ($content_pref["content_navigator_top_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_286."<br />
						".$rs -> form_checkbox("content_navigator_score_{$id}", 1, ($content_pref["content_navigator_score_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_288."<br />
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_search_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_67;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_68;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_69;
						$TOPIC_FIELD = "
						".$rs -> form_checkbox("content_search_catall_{$id}", 1, ($content_pref["content_search_catall_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_280."<br />
						".$rs -> form_checkbox("content_search_cat_{$id}", 1, ($content_pref["content_search_cat_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_281."<br />
						".$rs -> form_checkbox("content_search_authorall_{$id}", 1, ($content_pref["content_search_authorall_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_282."<br />
						".$rs -> form_checkbox("content_search_author_{$id}", 1, ($content_pref["content_search_author_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_283."<br />
						".$rs -> form_checkbox("content_search_recent_{$id}", 1, ($content_pref["content_search_recent_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_284."<br />
						".$rs -> form_checkbox("content_search_item_{$id}", 1, ($content_pref["content_search_item_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_285."<br />
						".$rs -> form_checkbox("content_search_archive_{$id}", 1, ($content_pref["content_search_archive_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_287."<br />
						".$rs -> form_checkbox("content_search_top_{$id}", 1, ($content_pref["content_search_top_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_286."<br />
						".$rs -> form_checkbox("content_search_score_{$id}", 1, ($content_pref["content_search_score_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_288."<br />
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_ordering_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_276;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_277;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_278;
//".$rs -> form_checkbox("content_ordering_catall_{$id}", 1, ($content_pref["content_ordering_catall_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_280."<br />
//".$rs -> form_checkbox("content_ordering_top_{$id}", 1, ($content_pref["content_ordering_top_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_286."<br />
//".$rs -> form_checkbox("content_ordering_score_{$id}", 1, ($content_pref["content_ordering_score_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_288."<br />
						$TOPIC_FIELD = "						
						".$rs -> form_checkbox("content_ordering_cat_{$id}", 1, ($content_pref["content_ordering_cat_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_281."<br />
						".$rs -> form_checkbox("content_ordering_authorall_{$id}", 1, ($content_pref["content_ordering_authorall_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_282."<br />
						".$rs -> form_checkbox("content_ordering_author_{$id}", 1, ($content_pref["content_ordering_author_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_283."<br />
						".$rs -> form_checkbox("content_ordering_recent_{$id}", 1, ($content_pref["content_ordering_recent_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_284."<br />
						".$rs -> form_checkbox("content_ordering_item_{$id}", 1, ($content_pref["content_ordering_item_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_285."<br />
						".$rs -> form_checkbox("content_ordering_archive_{$id}", 1, ($content_pref["content_ordering_archive_{$id}"] ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_287."<br />						
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_searchmenu_rendertype_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_267;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_268;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_269;
						$TOPIC_FIELD = "
						".$rs -> form_select_open("content_searchmenu_rendertype_{$id}")."
						".$rs -> form_option(CONTENT_ADMIN_OPT_LAN_64, ($content_pref["content_searchmenu_rendertype_{$id}"] == "1" ? "1" : "0"), "1")."
						".$rs -> form_option(CONTENT_ADMIN_OPT_LAN_65, ($content_pref["content_searchmenu_rendertype_{$id}"] == "2" ? "1" : "0"), "2")."
						".$rs -> form_select_close();
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

						//content_defaultorder_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_106;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_107;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_108;
						$TOPIC_FIELD = "
						".$rs -> form_select_open("content_defaultorder_{$id}")."
						".$rs -> form_option(CONTENT_ORDER_LAN_1, ($content_pref["content_defaultorder_{$id}"] == "orderaheading" ? "1" : "0"), "orderaheading")."
						".$rs -> form_option(CONTENT_ORDER_LAN_2, ($content_pref["content_defaultorder_{$id}"] == "orderdheading" ? "1" : "0"), "orderdheading")."
						".$rs -> form_option(CONTENT_ORDER_LAN_3, ($content_pref["content_defaultorder_{$id}"] == "orderadate" ? "1" : "0"), "orderadate")."
						".$rs -> form_option(CONTENT_ORDER_LAN_4, ($content_pref["content_defaultorder_{$id}"] == "orderddate" ? "1" : "0"), "orderddate")."
						".$rs -> form_option(CONTENT_ORDER_LAN_5, ($content_pref["content_defaultorder_{$id}"] == "orderarefer" ? "1" : "0"), "orderarefer")."
						".$rs -> form_option(CONTENT_ORDER_LAN_6, ($content_pref["content_defaultorder_{$id}"] == "orderdrefer" ? "1" : "0"), "orderdrefer")."
						".$rs -> form_option(CONTENT_ORDER_LAN_7, ($content_pref["content_defaultorder_{$id}"] == "orderaparent" ? "1" : "0"), "orderaparent")."
						".$rs -> form_option(CONTENT_ORDER_LAN_8, ($content_pref["content_defaultorder_{$id}"] == "orderdparent" ? "1" : "0"), "orderdparent")."
						".$rs -> form_option(CONTENT_ORDER_LAN_9, ($content_pref["content_defaultorder_{$id}"] == "orderaorder" ? "1" : "0"), "orderaorder")."
						".$rs -> form_option(CONTENT_ORDER_LAN_10, ($content_pref["content_defaultorder_{$id}"] == "orderdorder" ? "1" : "0"), "orderdorder")."
						".$rs -> form_select_close()."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_upload_image_size_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_304;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_305;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_306;
						$TOPIC_FIELD = $rs -> form_text("content_upload_image_size_{$id}", 10, $content_pref["content_upload_image_size_{$id}"], 3)." px";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_upload_image_size_thumb_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_307;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_308;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_309;
						$TOPIC_FIELD = $rs -> form_text("content_upload_image_size_thumb_{$id}", 10, $content_pref["content_upload_image_size_thumb_{$id}"], 3)." px";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_upload_icon_size_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_310;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_311;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_312;
						$TOPIC_FIELD = $rs -> form_text("content_upload_icon_size_{$id}", 10, $content_pref["content_upload_icon_size_{$id}"], 3)." px";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						$text .= $TOPIC_TABLE_END;

						$text .= "
						<div id='recentpages' style='display:none; text-align:center'>
						<table style='".ADMIN_WIDTH."' class='fborder'>";

						$TOPIC_CAPTION = CONTENT_ADMIN_OPT_LAN_70;
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_TITLE_ROW);

						//content_list sections
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_2;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_71;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = "
						".$rs -> form_checkbox("content_list_icon_{$id}", 1, (isset($content_pref["content_list_icon_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_4."<br />
						".$rs -> form_checkbox("content_list_subheading_{$id}", 1, (isset($content_pref["content_list_subheading_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_72."<br />
						".$rs -> form_checkbox("content_list_summary_{$id}", 1, (isset($content_pref["content_list_summary_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_73."<br />
						".$rs -> form_checkbox("content_list_text_{$id}", 1, (isset($content_pref["content_list_text_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_241."<br />
						".$rs -> form_checkbox("content_list_date_{$id}", 1, (isset($content_pref["content_list_date_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_74."<br />
						".$rs -> form_checkbox("content_list_authorname_{$id}", 1, (isset($content_pref["content_list_authorname_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_75."<br />
						".$rs -> form_checkbox("content_list_authoremail_{$id}", 1, (isset($content_pref["content_list_authoremail_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_76."<br />
						".$rs -> form_checkbox("content_list_authorprofile_{$id}", 1, (isset($content_pref["content_list_authorprofile_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_118."<br />
						".$rs -> form_checkbox("content_list_authoricon_{$id}", 1, (isset($content_pref["content_list_authoricon_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_117."<br />
						".$rs -> form_checkbox("content_list_rating_{$id}", 1, (isset($content_pref["content_list_rating_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_77."<br />
						".$rs -> form_checkbox("content_list_peicon_{$id}", 1, (isset($content_pref["content_list_peicon_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_78."<br />
						".$rs -> form_checkbox("content_list_parent_{$id}", 1, (isset($content_pref["content_list_parent_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_79."<br />
						".$rs -> form_checkbox("content_list_refer_{$id}", 1, (isset($content_pref["content_list_refer_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_80."<br />
						".$rs -> form_checkbox("content_list_editicon_{$id}", 1, (isset($content_pref["content_list_editicon_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_204."<br />
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

						//content_list_text_char_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_236;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_237;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_238;
						$TOPIC_FIELD = $rs -> form_text("content_list_text_char_{$id}", 10, $content_pref["content_list_text_char_{$id}"], 3);
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_list_text_post_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_239;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_240;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = $rs -> form_text("content_list_text_post_{$id}", 10, $content_pref["content_list_text_post_{$id}"], 20);
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_list_text_link_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_233;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_234;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_235;
						$TOPIC_FIELD = "
						".$rs -> form_radio("content_list_text_link_{$id}", "1", ($content_pref["content_list_text_link_{$id}"] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
						".$rs -> form_radio("content_list_text_link_{$id}", "0", ($content_pref["content_list_text_link_{$id}"] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
						";
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
						
						//content_list_datestyle_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_214;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_215;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_216;
						$TOPIC_FIELD = $rs -> form_text("content_list_datestyle_{$id}", 15, $content_pref["content_list_datestyle_{$id}"], 50);
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						$text .= $TOPIC_TABLE_END;

						$text .= "
						<div id='catpages' style='display:none; text-align:center'>
						<table style='".ADMIN_WIDTH."' class='fborder'>";

						$TOPIC_CAPTION = CONTENT_ADMIN_OPT_LAN_119;
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_TITLE_ROW);

						$text .= $TOPIC_ROW_SPACER;

						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_113;
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_HELP_ROW);

						//content_cat_sections_allcats (view all categories)
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_2;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_110;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = "
						".$rs -> form_checkbox("content_catall_icon_{$id}", 1, (isset($content_pref["content_catall_icon_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_4."<br />
						".$rs -> form_checkbox("content_catall_subheading_{$id}", 1, (isset($content_pref["content_catall_subheading_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_72."<br />
						".$rs -> form_checkbox("content_catall_text_{$id}", 1, (isset($content_pref["content_catall_text_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_241."<br />
						".$rs -> form_checkbox("content_catall_date_{$id}", 1, (isset($content_pref["content_catall_date_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_74."<br />
						".$rs -> form_checkbox("content_catall_authorname_{$id}", 1, (isset($content_pref["content_catall_authorname_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_75."<br />
						".$rs -> form_checkbox("content_catall_authoremail_{$id}", 1, (isset($content_pref["content_catall_authoremail_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_76."<br />
						".$rs -> form_checkbox("content_catall_authorprofile_{$id}", 1, (isset($content_pref["content_catall_authorprofile_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_118."<br />
						".$rs -> form_checkbox("content_catall_authoricon_{$id}", 1, (isset($content_pref["content_catall_authoricon_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_117."<br />
						".$rs -> form_checkbox("content_catall_rating_{$id}", 1, (isset($content_pref["content_catall_rating_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_77."<br />
						".$rs -> form_checkbox("content_catall_peicon_{$id}", 1, (isset($content_pref["content_catall_peicon_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_78."<br />
						".$rs -> form_checkbox("content_catall_comment_{$id}", 1, (isset($content_pref["content_catall_comment_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_7."<br />
						".$rs -> form_checkbox("content_catall_amount_{$id}", 1, (isset($content_pref["content_catall_amount_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_109."<br />
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_catall_text_char_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_236;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_237;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_238;
						$TOPIC_FIELD = $rs -> form_text("content_catall_text_char_{$id}", 10, $content_pref["content_catall_text_char_{$id}"], 3);
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_catall_text_post_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_239;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_240;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = $rs -> form_text("content_catall_text_post_{$id}", 10, $content_pref["content_catall_text_post_{$id}"], 20);
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_catall_text_link_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_233;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_234;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_115;
						$TOPIC_FIELD = "
						".$rs -> form_radio("content_catall_text_link_{$id}", "1", ($content_pref["content_catall_text_link_{$id}"] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
						".$rs -> form_radio("content_catall_text_link_{$id}", "0", ($content_pref["content_catall_text_link_{$id}"] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);
						
						$text .= $TOPIC_ROW_SPACER;

						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_114;
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_HELP_ROW);

						//content_cat_sections (view category)
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_2;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_111;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = "
						".$rs -> form_checkbox("content_cat_icon_{$id}", 1, (isset($content_pref["content_cat_icon_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_4."<br />
						".$rs -> form_checkbox("content_cat_subheading_{$id}", 1, (isset($content_pref["content_cat_subheading_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_72."<br />
						".$rs -> form_checkbox("content_cat_text_{$id}", 1, (isset($content_pref["content_cat_text_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_241."<br />
						".$rs -> form_checkbox("content_cat_date_{$id}", 1, (isset($content_pref["content_cat_date_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_74."<br />
						".$rs -> form_checkbox("content_cat_authorname_{$id}", 1, (isset($content_pref["content_cat_authorname_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_75."<br />
						".$rs -> form_checkbox("content_cat_authoremail_{$id}", 1, (isset($content_pref["content_cat_authoremail_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_76."<br />
						".$rs -> form_checkbox("content_cat_authorprofile_{$id}", 1, (isset($content_pref["content_cat_authorprofile_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_118."<br />
						".$rs -> form_checkbox("content_cat_authoricon_{$id}", 1, (isset($content_pref["content_cat_authoricon_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_117."<br />
						".$rs -> form_checkbox("content_cat_rating_{$id}", 1, (isset($content_pref["content_cat_rating_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_77."<br />
						".$rs -> form_checkbox("content_cat_peicon_{$id}", 1, (isset($content_pref["content_cat_peicon_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_78."<br />
						".$rs -> form_checkbox("content_cat_comment_{$id}", 1, (isset($content_pref["content_cat_comment_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_7."<br />
						".$rs -> form_checkbox("content_cat_amount_{$id}", 1, (isset($content_pref["content_cat_amount_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_109."<br />
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_cat_sections_subcategory_list (view category)
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_2;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_112;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = "
						".$rs -> form_checkbox("content_catsub_icon_{$id}", 1, (isset($content_pref["content_catsub_icon_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_4."<br />
						".$rs -> form_checkbox("content_catsub_subheading_{$id}", 1, (isset($content_pref["content_catsub_subheading_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_72."<br />
						".$rs -> form_checkbox("content_catsub_amount_{$id}", 1, (isset($content_pref["content_catsub_amount_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_109."<br />
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_list_text_char_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_236;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_237;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_238;
						$TOPIC_FIELD = $rs -> form_text("content_cat_text_char_{$id}", 10, $content_pref["content_cat_text_char_{$id}"], 3);
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_list_text_post_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_239;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_240;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = $rs -> form_text("content_cat_text_post_{$id}", 10, $content_pref["content_cat_text_post_{$id}"], 20);
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_list_text_link_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_233;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_234;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_115;
						$TOPIC_FIELD = "
						".$rs -> form_radio("content_cat_text_link_{$id}", "1", ($content_pref["content_cat_text_link_{$id}"] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
						".$rs -> form_radio("content_cat_text_link_{$id}", "0", ($content_pref["content_cat_text_link_{$id}"] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_cat_authoremail_nonmember_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_91;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_92;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_93;
						$TOPIC_FIELD = "
						".$rs -> form_radio("content_cat_authoremail_nonmember_{$id}", "1", ($content_pref["content_cat_authoremail_nonmember_{$id}"] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
						".$rs -> form_radio("content_cat_authoremail_nonmember_{$id}", "0", ($content_pref["content_cat_authoremail_nonmember_{$id}"] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_cat_peicon_all_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_100;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_101;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_102;
						$TOPIC_FIELD = "
						".$rs -> form_radio("content_cat_peicon_all_{$id}", "1", ($content_pref["content_cat_peicon_all_{$id}"] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
						".$rs -> form_radio("content_cat_peicon_all_{$id}", "0", ($content_pref["content_cat_peicon_all_{$id}"] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_cat_rating_all_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_103;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_104;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_105;
						$TOPIC_FIELD = "
						".$rs -> form_radio("content_cat_rating_all_{$id}", "1", ($content_pref["content_cat_rating_all_{$id}"] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
						".$rs -> form_radio("content_cat_rating_all_{$id}", "0", ($content_pref["content_cat_rating_all_{$id}"] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

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
						".$rs -> form_checkbox("content_content_icon_{$id}", 1, (isset($content_pref["content_content_icon_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_4."<br />
						".$rs -> form_checkbox("content_content_subheading_{$id}", 1, (isset($content_pref["content_content_subheading_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_72."<br />
						".$rs -> form_checkbox("content_content_summary_{$id}", 1, (isset($content_pref["content_content_summary_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_73."<br />
						".$rs -> form_checkbox("content_content_date_{$id}", 1, (isset($content_pref["content_content_date_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_74."<br />
						".$rs -> form_checkbox("content_content_authorname_{$id}", 1, (isset($content_pref["content_content_authorname_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_75."<br />
						".$rs -> form_checkbox("content_content_authoremail_{$id}", 1, (isset($content_pref["content_content_authoremail_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_76."<br />
						".$rs -> form_checkbox("content_content_authorprofile_{$id}", 1, (isset($content_pref["content_content_authorprofile_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_118."<br />
						".$rs -> form_checkbox("content_content_authoricon_{$id}", 1, (isset($content_pref["content_content_authoricon_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_117."<br />						
						".$rs -> form_checkbox("content_content_parent_{$id}", 1, (isset($content_pref["content_content_parent_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_79."<br />
						".$rs -> form_checkbox("content_content_rating_{$id}", 1, (isset($content_pref["content_content_rating_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_77."<br />
						".$rs -> form_checkbox("content_content_peicon_{$id}", 1, (isset($content_pref["content_content_peicon_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_78."<br />
						".$rs -> form_checkbox("content_content_refer_{$id}", 1, (isset($content_pref["content_content_refer_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_80."<br />
						".$rs -> form_checkbox("content_content_comment_{$id}", 1, (isset($content_pref["content_content_comment_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_116."<br />
						".$rs -> form_checkbox("content_content_editicon_{$id}", 1, (isset($content_pref["content_content_editicon_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_204."<br />
						".$rs -> form_checkbox("content_content_customtags_{$id}", 1, (isset($content_pref["content_content_customtags_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_206."<br />
						".$rs -> form_checkbox("content_content_presettags_{$id}", 1, (isset($content_pref["content_content_presettags_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_221."<br />
						".$rs -> form_checkbox("content_content_attach_{$id}", 1, (isset($content_pref["content_content_attach_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_5."<br />
						".$rs -> form_checkbox("content_content_images_{$id}", 1, (isset($content_pref["content_content_images_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_6."<br />
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

						//content_content_pagenames_rendertype_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_301;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_302;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_303;
						$TOPIC_FIELD = "
						".$rs -> form_radio("content_content_pagenames_rendertype_{$id}", "0", ($content_pref["content_content_pagenames_rendertype_{$id}"] ? "0" : "1"), "", "").CONTENT_ADMIN_OPT_LAN_211."
						".$rs -> form_radio("content_content_pagenames_rendertype_{$id}", "1", ($content_pref["content_content_pagenames_rendertype_{$id}"] ? "1" : "0"), "", "").CONTENT_ADMIN_OPT_LAN_210."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						$text .= $TOPIC_TABLE_END;




						$text .= "
						<div id='authorpage' style='display:none; text-align:center'>
						<table style='".ADMIN_WIDTH."' class='fborder'>";

						$TOPIC_CAPTION = CONTENT_ADMIN_OPT_LAN_247;
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_TITLE_ROW);

						//content_author_sections
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_2;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_242;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = "
						".$rs -> form_checkbox("content_author_lastitem_{$id}", 1, (isset($content_pref["content_author_lastitem_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_243."<br />
						".$rs -> form_checkbox("content_author_amount_{$id}", 1, (isset($content_pref["content_author_amount_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_244."<br />
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);
						
						//content_author_nextprev_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_94;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_95;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_245;
						$TOPIC_FIELD = "
						".$rs -> form_radio("content_author_nextprev_{$id}", "1", ($content_pref["content_author_nextprev_{$id}"] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
						".$rs -> form_radio("content_author_nextprev_{$id}", "0", ($content_pref["content_author_nextprev_{$id}"] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_author_nextprev_number_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_97;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_246;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_99;
						$TOPIC_FIELD = $rs -> form_select_open("content_author_nextprev_number_{$id}");
						for($i=2;$i<63;$i++){
							$TOPIC_FIELD .= $rs -> form_option($i, ($content_pref["content_author_nextprev_number_{$id}"] == $i ? "1" : "0"), $i);
							$i++;
						}
						$TOPIC_FIELD .= $rs -> form_select_close();
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						$text .= $TOPIC_TABLE_END;








						$text .= "
						<div id='archivepage' style='display:none; text-align:center'>
						<table style='".ADMIN_WIDTH."' class='fborder'>";

						$TOPIC_CAPTION = CONTENT_ADMIN_OPT_LAN_217;
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_TITLE_ROW);

						//content_archive_sections
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_2;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_219;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = "
						".$rs -> form_checkbox("content_archive_date_{$id}", 1, (isset($content_pref["content_archive_date_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_74."<br />
						".$rs -> form_checkbox("content_archive_authorname_{$id}", 1, (isset($content_pref["content_archive_authorname_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_75."<br />
						".$rs -> form_checkbox("content_archive_authoremail_{$id}", 1, (isset($content_pref["content_archive_authoremail_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_76."<br />
						".$rs -> form_checkbox("content_archive_authorprofile_{$id}", 1, (isset($content_pref["content_archive_authorprofile_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_118."<br />
						".$rs -> form_checkbox("content_archive_authoricon_{$id}", 1, (isset($content_pref["content_archive_authoricon_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_117."<br />
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_archive_authoremail_nonmember_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_91;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_92;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_93;
						$TOPIC_FIELD = "
						".$rs -> form_radio("content_archive_authoremail_nonmember_{$id}", "1", ($content_pref["content_archive_authoremail_nonmember_{$id}"] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
						".$rs -> form_radio("content_archive_authoremail_nonmember_{$id}", "0", ($content_pref["content_archive_authoremail_nonmember_{$id}"] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);
						
						//content_archive_nextprev_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_94;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_95;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_96;
						$TOPIC_FIELD = "
						".$rs -> form_radio("content_archive_nextprev_{$id}", "1", ($content_pref["content_archive_nextprev_{$id}"] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
						".$rs -> form_radio("content_archive_nextprev_{$id}", "0", ($content_pref["content_archive_nextprev_{$id}"] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_nextprev_number_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_97;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_98;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_99;
						$TOPIC_FIELD = $rs -> form_select_open("content_archive_nextprev_number_{$id}");
						for($i=2;$i<63;$i++){
							$TOPIC_FIELD .= $rs -> form_option($i, ($content_pref["content_archive_nextprev_number_{$id}"] == $i ? "1" : "0"), $i);
							$i++;
						}
						$TOPIC_FIELD .= $rs -> form_select_close();
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_archive_letterindex_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_270;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_271;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_272;
						$TOPIC_FIELD = "
						".$rs -> form_radio("content_archive_letterindex_{$id}", "1", ($content_pref["content_archive_letterindex_{$id}"] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
						".$rs -> form_radio("content_archive_letterindex_{$id}", "0", ($content_pref["content_archive_letterindex_{$id}"] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_archive_datestyle_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_214;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_215;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_216;
						$TOPIC_FIELD = $rs -> form_text("content_archive_datestyle_{$id}", 15, $content_pref["content_archive_datestyle_{$id}"], 50);
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						$text .= $TOPIC_TABLE_END;




						$text .= "
						<div id='toppage' style='display:none; text-align:center'>
						<table style='".ADMIN_WIDTH."' class='fborder'>";

						$TOPIC_CAPTION = CONTENT_ADMIN_OPT_LAN_266;
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_TITLE_ROW);

						//content_top_sections
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_2;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_290;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = "
						".$rs -> form_checkbox("content_top_icon_{$id}", 1, (isset($content_pref["content_top_icon_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_4."<br />
						".$rs -> form_checkbox("content_top_authorname_{$id}", 1, (isset($content_pref["content_top_authorname_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_75."<br />
						".$rs -> form_checkbox("content_top_authoremail_{$id}", 1, (isset($content_pref["content_top_authoremail_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_76."<br />
						".$rs -> form_checkbox("content_top_authorprofile_{$id}", 1, (isset($content_pref["content_top_authorprofile_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_118."<br />
						".$rs -> form_checkbox("content_top_authoricon_{$id}", 1, (isset($content_pref["content_top_authoricon_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_117."<br />
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_top_authoremail_nonmember_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_91;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_92;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_93;
						$TOPIC_FIELD = "
						".$rs -> form_radio("content_top_authoremail_nonmember_{$id}", "1", ($content_pref["content_top_authoremail_nonmember_{$id}"] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
						".$rs -> form_radio("content_top_authoremail_nonmember_{$id}", "0", ($content_pref["content_top_authoremail_nonmember_{$id}"] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						$text .= $TOPIC_TABLE_END;



						$text .= "
						<div id='scorepage' style='display:none; text-align:center'>
						<table style='".ADMIN_WIDTH."' class='fborder'>";

						$TOPIC_CAPTION = CONTENT_ADMIN_OPT_LAN_279;
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_TITLE_ROW);

						//content_score_sections
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_2;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_291;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = "
						".$rs -> form_checkbox("content_score_icon_{$id}", 1, (isset($content_pref["content_score_icon_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_4."<br />
						".$rs -> form_checkbox("content_score_authorname_{$id}", 1, (isset($content_pref["content_score_authorname_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_75."<br />
						".$rs -> form_checkbox("content_score_authoremail_{$id}", 1, (isset($content_pref["content_score_authoremail_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_76."<br />
						".$rs -> form_checkbox("content_score_authorprofile_{$id}", 1, (isset($content_pref["content_score_authorprofile_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_118."<br />
						".$rs -> form_checkbox("content_score_authoricon_{$id}", 1, (isset($content_pref["content_score_authoricon_{$id}"]) ? "1" : "0"))." ".CONTENT_ADMIN_OPT_LAN_117."<br />
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_score_authoremail_nonmember_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_91;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_92;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_93;
						$TOPIC_FIELD = "
						".$rs -> form_radio("content_score_authoremail_nonmember_{$id}", "1", ($content_pref["content_score_authoremail_nonmember_{$id}"] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
						".$rs -> form_radio("content_score_authoremail_nonmember_{$id}", "0", ($content_pref["content_score_authoremail_nonmember_{$id}"] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						$text .= $TOPIC_TABLE_END;





						$text .= "
						<div id='contentmanager' style='display:none; text-align:center'>
						<table style='".ADMIN_WIDTH."' class='fborder'>";

						$TOPIC_CAPTION = CONTENT_ADMIN_OPT_LAN_248;
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_TITLE_ROW);
						
						//content_manager_class_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_249;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_250;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_251;
						$TOPIC_FIELD = r_userclass("content_manager_class_{$id}", $content_pref["content_manager_class_{$id}"], "CLASSES");
						$TOPIC_FIELD .= $rs -> form_hidden("content_manager_allowed_{$id}", $content_pref["content_manager_allowed_{$id}"]);
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

						//content_menu_links_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_252;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_253;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_254;
						$TOPIC_FIELD = "
						".$rs -> form_radio("content_menu_links_{$id}", "1", ($content_pref["content_menu_links_{$id}"] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
						".$rs -> form_radio("content_menu_links_{$id}", "0", ($content_pref["content_menu_links_{$id}"] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);
						
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

						//content_menu_viewallitems_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_212;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_213;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = "
						".$rs -> form_radio("content_menu_viewallitems_{$id}", "1", ($content_pref["content_menu_viewallitems_{$id}"] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
						".$rs -> form_radio("content_menu_viewallitems_{$id}", "0", ($content_pref["content_menu_viewallitems_{$id}"] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
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

						//content_menu_viewtopscore_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_292;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_293;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = "
						".$rs -> form_radio("content_menu_viewtopscore_{$id}", "1", ($content_pref["content_menu_viewtopscore_{$id}"] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
						".$rs -> form_radio("content_menu_viewtopscore_{$id}", "0", ($content_pref["content_menu_viewtopscore_{$id}"] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
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

						//content_menu_links_icon_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_260;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_159;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_160;
						$TOPIC_FIELD = "
						".$rs -> form_select_open("content_menu_links_icon_{$id}")."
						".$rs -> form_option(CONTENT_ADMIN_OPT_LAN_168, ($content_pref["content_menu_links_icon_{$id}"] == "0" ? "1" : "0"), 0)."
						".$rs -> form_option(CONTENT_ADMIN_OPT_LAN_169, ($content_pref["content_menu_links_icon_{$id}"] == "1" ? "1" : "0"), 1)."
						".$rs -> form_option(CONTENT_ADMIN_OPT_LAN_170, ($content_pref["content_menu_links_icon_{$id}"] == "2" ? "1" : "0"), 2)."
						".$rs -> form_option(CONTENT_ADMIN_OPT_LAN_171, ($content_pref["content_menu_links_icon_{$id}"] == "3" ? "1" : "0"), 3)."
						".$rs -> form_option(CONTENT_ADMIN_OPT_LAN_172, ($content_pref["content_menu_links_icon_{$id}"] == "4" ? "1" : "0"), 4)."
						".$rs -> form_select_close()."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_menu_links_dropdown_ (rendertype)
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_257;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_258;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_259;
						$TOPIC_FIELD = "
						".$rs -> form_radio("content_menu_links_dropdown_{$id}", "1", ($content_pref["content_menu_links_dropdown_{$id}"] ? "1" : "0"), "", "").CONTENT_ADMIN_OPT_LAN_210."
						".$rs -> form_radio("content_menu_links_dropdown_{$id}", "0", ($content_pref["content_menu_links_dropdown_{$id}"] ? "0" : "1"), "", "").CONTENT_ADMIN_OPT_LAN_211."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_menu_links_caption_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_261;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_262;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_263;
						$TOPIC_FIELD = $rs -> form_text("content_menu_links_caption_{$id}", 15, $content_pref["content_menu_links_caption_{$id}"], 50);
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						/*
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
						*/

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

						//content_menu_cat_main_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_255;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_256;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = "
						".$rs -> form_radio("content_menu_cat_main_{$id}", "1", ($content_pref["content_menu_cat_main_{$id}"] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
						".$rs -> form_radio("content_menu_cat_main_{$id}", "0", ($content_pref["content_menu_cat_main_{$id}"] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
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

						//content_menu_cat_icon_default_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_264;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_265;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_160;
						$TOPIC_FIELD = "
						".$rs -> form_select_open("content_menu_cat_icon_default_{$id}")."
						".$rs -> form_option(CONTENT_ADMIN_OPT_LAN_168, ($content_pref["content_menu_cat_icon_default_{$id}"] == "0" ? "1" : "0"), 0)."
						".$rs -> form_option(CONTENT_ADMIN_OPT_LAN_169, ($content_pref["content_menu_cat_icon_default_{$id}"] == "1" ? "1" : "0"), 1)."
						".$rs -> form_option(CONTENT_ADMIN_OPT_LAN_170, ($content_pref["content_menu_cat_icon_default_{$id}"] == "2" ? "1" : "0"), 2)."
						".$rs -> form_option(CONTENT_ADMIN_OPT_LAN_171, ($content_pref["content_menu_cat_icon_default_{$id}"] == "3" ? "1" : "0"), 3)."
						".$rs -> form_option(CONTENT_ADMIN_OPT_LAN_172, ($content_pref["content_menu_cat_icon_default_{$id}"] == "4" ? "1" : "0"), 4)."
						".$rs -> form_select_close()."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_menu_cat_dropdown_ (rendertype)
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_207;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_208;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_209;
						$TOPIC_FIELD = "
						".$rs -> form_radio("content_menu_cat_dropdown_{$id}", "1", ($content_pref["content_menu_cat_dropdown_{$id}"] ? "1" : "0"), "", "").CONTENT_ADMIN_OPT_LAN_210."
						".$rs -> form_radio("content_menu_cat_dropdown_{$id}", "0", ($content_pref["content_menu_cat_dropdown_{$id}"] ? "0" : "1"), "", "").CONTENT_ADMIN_OPT_LAN_211."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_menu_cat_caption_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_198;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_199;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_263;
						$TOPIC_FIELD = $rs -> form_text("content_menu_cat_caption_{$id}", 15, $content_pref["content_menu_cat_caption_{$id}"], 50);
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

						//content_menu_recent_date_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_181;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_182;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = "
						".$rs -> form_radio("content_menu_recent_date_{$id}", "1", ($content_pref["content_menu_recent_date_{$id}"] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85."
						".$rs -> form_radio("content_menu_recent_date_{$id}", "0", ($content_pref["content_menu_recent_date_{$id}"] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86."
						";
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						//content_menu_recent_datestyle_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_214;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_215;
						$TOPIC_HELP = CONTENT_ADMIN_OPT_LAN_216;
						$TOPIC_FIELD = $rs -> form_text("content_menu_recent_datestyle_{$id}", 15, $content_pref["content_menu_recent_datestyle_{$id}"], 50);
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

						//content_menu_recent_number_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_179;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_180;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = $rs -> form_select_open("content_menu_recent_number_{$id}");
						for($i=1;$i<16;$i++){
							$TOPIC_FIELD .= $rs -> form_option($i, ($content_pref["content_menu_recent_number_{$id}"] == $i ? "1" : "0"), $i);
						}
						$TOPIC_FIELD .= $rs -> form_select_close();
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

						//content_menu_recent_caption_
						$TOPIC_TOPIC = CONTENT_ADMIN_OPT_LAN_177;
						$TOPIC_HEADING = CONTENT_ADMIN_OPT_LAN_178;
						$TOPIC_HELP = "";
						$TOPIC_FIELD = $rs -> form_text("content_menu_recent_caption_{$id}", 15, $content_pref["content_menu_recent_caption_{$id}"], 50);
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

						$text .= $TOPIC_TABLE_END;

						$text .= "
						</form>
						</div>";

						$ns -> tablerender($caption, $text);
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