<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/admin/newspost.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("../class2.php");
if(!getperms("H")){ header("location:".e_BASE."index.php"); exit;}
require_once("auth.php");
require_once(e_HANDLER."userclass_class.php");
require_once(e_HANDLER."news_class.php");
require_once(e_HANDLER."ren_help.php");
require_once(e_HANDLER."form_handler.php");
$rs = new form;
$aj = new textparse;
$ix = new news;
$newspost = new newspost;

if(e_QUERY){
	$tmp = explode(".", e_QUERY);
	$action = $tmp[0];
	$sub_action = $tmp[1];
	$id = $tmp[2];
	$from = ($tmp[3] ? $tmp[3] : 0);
	unset($tmp);
}

$from = ($from ? $from : 0);
$amount = 50;

// ##### Main loop -----------------------------------------------------------------------------------------------------------------------

if($action == "main" && $sub_action == "confirm"){
	if($sql -> db_Delete("news", "news_id='$id' ")){
		$newspost -> show_message(NWSLAN_31." #".$id." ".NWSLAN_32);
	}
}

if($action == "cat" && $sub_action == "confirm"){
	if($sql -> db_Delete("news_category", "category_id='$id' ")){
		$newspost -> show_message(NWSLAN_33." #".$id." ".NWSLAN_32);
		unset($id);
	}
}

if($action == "sn" && $sub_action == "confirm"){
	if($sql -> db_Delete("submitnews", "submitnews_id='$id' ")){
		$newspost -> show_message(NWSLAN_34." #".$id." ".NWSLAN_32);
		unset($id);
	}
}

if(IsSet($_POST['preview'])){
	$newspost -> preview_item($id);
}

if(IsSet($_POST['submit'])){
	$newspost -> submit_item($sub_action, $id);
	$action = "main";
	unset($sub_action, $id);
}

if(IsSet($_POST['create_category'])){
	if($_POST['category_name']){
		$_POST['category_name'] = $aj -> formtpa($_POST['category_name'], "admin");
		$sql -> db_Insert("news_category", " '0', '".$_POST['category_name']."', '".$_POST['category_button']."'");
		$newspost -> show_message(NWSLAN_35);	
	}
}

if(IsSet($_POST['update_category'])){
	if($_POST['category_name']){
		$_POST['category_name'] = $aj -> formtpa($_POST['category_name'], "admin");
		$sql -> db_Update("news_category", "category_name='".$_POST['category_name']."', category_icon='".$_POST['category_button']."' WHERE category_id='".$_POST['category_id']."'");
		$newspost -> show_message(NWSLAN_36);
	}
}

if(!e_QUERY || $action == "main"){
	$newspost -> show_existing_items($action, $sub_action, $id, $from, $amount);
}

if($action == "create"){
	if($sub_action == "edit" && !$_POST['preview']  && !$_POST['submit']){
		if($sql -> db_Select("news", "*", "news_id='$id' ")){
			$row = $sql-> db_Fetch();
			extract($row);
			$_POST['news_title'] = $news_title;
			$_POST['data'] = $aj -> formtparev($news_body);
			$_POST['news_extended'] = $aj -> formtparev($news_extended);
			$_POST['news_allow_comments'] = $news_allow_comments;
			$_POST['news_class'] = $news_class;
			$_POST['cat_id'] = $news_category;
			if($news_start){$tmp = getdate($news_start);$_POST['startmonth'] = $tmp['mon'];$_POST['startday'] = $tmp['mday'];$_POST['startyear'] = $tmp['year'];}
			if($news_end){$tmp = getdate($news_end);$_POST['endmonth'] = $tmp['mon'];$_POST['endday'] = $tmp['mday'];$_POST['endyear'] = $tmp['year'];}
			$_POST['comment_total'] = $sql -> db_Count("comments", "(*)", " WHERE comment_item_id='$news_id' AND comment_type='0' ");
		}
	}
	$newspost -> create_item($sub_action, $id);
}

if($action == "cat"){
	$newspost -> show_categories($sub_action, $id);
}

if($action == "sn"){
	$newspost -> submitted_news($sub_action, $id);
}





// ##### End ---------------------------------------------------------------------------------------------------------------------------------------------------------------------

// ##### Display options --------------------------------------------------------------------------------------------------------------------------------------------------------

$newspost -> show_options($action);

// ##### End ---------------------------------------------------------------------------------------------------------------------------------------------------------------------

require_once("footer.php");
?>
<script type="text/javascript">
function addtext(str){
	document.dataform.data.value += str;
}
function addtext2(str){
	document.dataform.news_extended.value += str;
}
function addtext3(str){
	document.dataform.category_button.value = str;
}
function fclear(){
	document.dataform.data.value = "";
	document.dataform.news_extended.value = "";
}
function help(help){
	document.dataform.helpb.value = help;
}
</script>
<?php
echo "<script type=\"text/javascript\">
function confirm_(mode, news_id){
	if(mode == 'cat'){
		var x=confirm(\"".NWSLAN_37." [ID: \" + news_id + \"]\");
	}else if(mode == 'sn'){
		var x=confirm(\"".NWSLAN_38." [ID: \" + news_id + \"]\");
	}else{
		var x=confirm(\"".NWSLAN_39." [ID: \" + news_id + \"]\");
	}
if(x)
	if(mode == 'cat'){
		window.location='".e_SELF."?cat.confirm.' + news_id;
	}else if(mode == 'sn'){
		window.location='".e_SELF."?sn.confirm.' + news_id;
	}else{
		window.location='".e_SELF."?main.confirm.' + news_id;
	}
}
</script>";

exit;

class newspost{


	function show_existing_items($action, $sub_action, $id, $from, $amount){
		// ##### Display scrolling list of existing news items ---------------------------------------------------------------------------------------------------------
		global $sql, $rs, $ns, $aj;
		$text = "<div style='text-align:center'><div style='border : solid 1px #000; padding : 4px; width : auto; height : 200px; overflow : auto; '>";

		if(IsSet($_POST['searchquery'])){
			$query = "news_title REGEXP('".$_POST['searchquery']."') OR news_body REGEXP('".$_POST['searchquery']."') OR news_extended REGEXP('".$_POST['searchquery']."') ORDER BY news_datestamp DESC";
		}else{
			$query = "ORDER BY ".($sub_action ? $sub_action : "news_datestamp")." ".($id ? $id : "DESC")."  LIMIT $from, $amount";
		}

		if($sql -> db_Select("news", "*", $query, ($_POST['searchquery'] ? 0 : "nowhere"))){
			$text .= "<table class='fborder' style='width:100%'>
			<tr>

			<td style='width:5%' class='forumheader2'><a href='".e_SELF."?main.news_id.".($id == "desc" ? "asc" : "desc").".$from'>ID</a></td>
			<td style='width:5%' class='forumheader2'><a href='".e_SELF."?main.news_title.".($id == "desc" ? "asc" : "desc").".$from'>".NWSLAN_40."</a></td>
			<td style='width:45%' class='forumheader2'>".NWSLAN_41."</td>
			</tr>";
			while($row = $sql -> db_Fetch()){
				extract($row);
				$text .= "<tr>
				<td style='width:5%' class='forumheader3'>$news_id</td>
				<td style='width:75%' class='forumheader3'><a href='".e_BASE."comment.php?$news_id'>".($news_title ? $aj -> tpa($news_title) : "[".NWSLAN_42."]")."</a></td>
				<td style='width:20%; text-align:center' class='forumheader3'>
				".$rs -> form_button("submit", "main_edit", NWSLAN_7, "onClick=\"document.location='".e_SELF."?create.edit.$news_id'\"")."
				".$rs -> form_button("submit", "main_delete", NWSLAN_8, "onClick=\"confirm_('create', $news_id)\"")."
				</td>
				</tr>";
			}
			$text .= "</table>";
		}else{
			$text .= "<div style='text-align:center'>".NWSLAN_43."</div>";
		}
		$text .= "</div>";

		$newsposts = $sql -> db_Count("news");

		if($newsposts > $amount && !$_POST['searchquery']){
			$a = $newsposts/$amount;
			$r = explode(".", $a);
			if($r[1] != 0 ? $pages = ($r[0]+1) : $pages = $r[0]);
			if($pages){
				$current = ($from/$amount)+1;
				$text .= "<br />".NWSLAN_62." ";
				for($a=1; $a<=$pages; $a++){
					$text .= ($current == $a ? " <b>[$a]</b>" : " [<a href='".e_SELF."?".(e_QUERY ? "$action.$sub_action.$id." : "main.news_datestamp.desc.").(($a-1)*$amount)."'>$a</a>] ");
				}
				$text .= "<br />";
			}
		}

		$text .= "<br /><form method='post' action='".e_SELF."'>\n<p>\n<input class='tbox' type='text' name='searchquery' size='20' value='' maxlength='50' />\n<input class='button' type='submit' name='searchsubmit' value='".NWSLAN_63."' />\n</p>\n</form>\n</div>";



		$ns -> tablerender(NWSLAN_43, $text);
	}

	function show_options($action){
		// ##### Display options ---------------------------------------------------------------------------------------------------------
		global $sql, $rs, $ns;
		$text = "<div style='text-align:center'>";
		if(e_QUERY && $action != "main"){
			$text .= "<a href='".e_SELF."'><div class='border'><div class='forumheader'><img src='".e_IMAGE."generic/location.png' style='vertical-align:middle; border:0' alt='' /> ".NWSLAN_44."</div></div></a>";
		}
		if($action != "create"){
			$text .= "<a href='".e_SELF."?create'><div class='border'><div class='forumheader'><img src='".e_IMAGE."generic/location.png' style='vertical-align:middle; border:0' alt='' /> ".NWSLAN_45."</div></div></a>";
		}
		if($action != "cat" && getperms("7")){
			$text .= "<a href='".e_SELF."?cat'><div class='border'><div class='forumheader'><img src='".e_IMAGE."generic/location.png' style='vertical-align:middle; border:0' alt='' /> ".NWSLAN_46."</div></div></a>";
		}
		if($action != "sn" && getperms("N")){
			$text .= "<a href='".e_SELF."?sn'><div class='border'><div class='forumheader'><img src='".e_IMAGE."generic/location.png' style='vertical-align:middle; border:0' alt='' /> ".NWSLAN_47."</div></div></a>";
		}
		$text .= "</div>";
		$ns -> tablerender(NWSLAN_48, $text);
	}

	function create_item($sub_action, $id){
		// ##### Display creation form ---------------------------------------------------------------------------------------------------------
		global $sql, $rs, $ns;

		$handle=opendir(e_IMAGE."newspost_images");
		while ($file = readdir($handle)){
			if($file != "." && $file != ".." && $file != "/" && $file != "index.html" && $file != "null.txt"){
				$imagelist[] = $file;
			}
		}
		closedir($handle);

		if($sub_action == "sn" && !$_POST['preview']){
			if($sql -> db_Select("submitnews", "*", "submitnews_id=$id", TRUE)){
				list($id, $submitnews_name, $submitnews_email, $_POST['news_title'], $_POST['data']) = $sql-> db_Fetch();
				$_POST['data'] .= "\n[[b]".NWSLAN_49." ".$submitnews_name."[/b]]";
			}
		}

		$text = "<div style='text-align:center'>
		<form method='post' action='".e_SELF."?".e_QUERY."' name='dataform'>
		<table style='width:auto' class='fborder'>
		<tr>

		<td colspan='2' style='text-align:center' class='forumheader2'>
		<input class='button' type='button' onClick='openwindow()'  value='".NWSLAN_5."' />
		</td>
		</tr>

		<tr>
		<td style='width:20%' class='forumheader3'>".NWSLAN_6.": </td>
		<td style='width:80%' class='forumheader3'>";

		if(!$sql -> db_Select("news_category")){
			$text .= NWSLAN_10;
		}else{

			$text .= "
			<select name='cat_id' class='tbox'>";
			
			while(list($cat_id, $cat_name, $cat_icon) = $sql-> db_Fetch()){
				$text .= ($_POST['cat_id'] == $cat_id ? "<option value='$cat_id' selected>".$cat_name."</option>" : "<option value='$cat_id'>".$cat_name."</option>");
			}
			$text .= "</select>";
		}
		$text .= "</td>
		</tr>
		<tr> 
		<td style='width:20%' class='forumheader3'>".NWSLAN_12.":</td>
		<td style='width:80%' class='forumheader3'>
		<input class='tbox' type='text' name='news_title' size='80' value='".$_POST['news_title']."' maxlength='200' />
		</td>
		</tr>
		<tr> 
		<td style='width:20%' class='forumheader3'>".NWSLAN_13.":<br /></td>
		<td style='width:80%' class='forumheader3'>
		<textarea class='tbox' name='data' cols='80' rows='10'>".(strstr($_POST['data'], "[img]http") ? "" : str_replace("[img]../", "[img]", $_POST['data']))."</textarea>
		<br />
		<input class='helpbox' type='text' name='helpb' size='100' />
		<br />
		".ren_help("addtext", TRUE)."

		<select class='tbox' name='imageps' onChange=\"addtext('[img]' + this.form.imageps.options[this.form.imageps.selectedIndex].value + '[/img]');this.selectedIndex=0;\" onMouseOver=\"help('".NWSLAN_50."')\" onMouseOut=\"help('')\">
		<option>Insert image ...</option>\n";
		while(list($key, $image) = each($imagelist)){ 
			$text .= "<option value='e107_images/newspost_images/".$image."'>".$image."</option>\n";
		}
		$text .= "</select>

		</td>
		</tr>
		<tr> 
		<td style='width:20%' class='forumheader3'>".NWSLAN_14.":<br /></td>
		<td style='width:80%' class='forumheader3'>
		<textarea class='tbox' name='news_extended' cols='80' rows='10'>".$_POST['news_extended']."</textarea>
		<br />
		".ren_help("addtext2", TRUE)."
		<select class='tbox' name='imageps2' onChange=\"addtext2('[img]' + this.form.imageps2.options[this.form.imageps2.selectedIndex].value + '[/img]');this.selectedIndex=0;\" onMouseOver=\"help('".NWSLAN_50."')\" onMouseOut=\"help('')\">
		<option>Insert image ...</option>\n";
		reset($imagelist);
		while(list($key, $image) = each($imagelist)){ 
			$text .= "<option value='e107_images/newspost_images/".$image."'>".$image."</option>\n";
		}
		$text .= "</select>

		<tr> 
		<td style='width:20%' class='forumheader3'>".NWSLAN_15.":</td>
		<td style='width:80%' class='forumheader3'>".
		($_POST['news_allow_comments'] ? "<input name='news_allow_comments' type='radio' value='0'>".NWSLAN_16."&nbsp;&nbsp;<input name='news_allow_comments' type='radio' value='1' checked>".NWSLAN_17 : "<input name='news_allow_comments' type='radio' value='0' checked>".NWSLAN_16."&nbsp;&nbsp;<input name='news_allow_comments' type='radio' value='1'>".NWSLAN_17)."
		 <span class='smalltext'>( ".NWSLAN_18." )
		 </td>
		</tr>

		<tr> 
		<td style='width:20%' class='forumheader3'>".NWSLAN_30.":</td>
		<td style='width:80%' class='forumheader3'>
		".(substr($_POST['data'], 0, 6) == "&nbsp;" ? "<input name='titleonly' type='radio' value='1' checked>".NWSLAN_16."&nbsp;&nbsp;<input name='titleonly' type='radio' value='0'>".NWSLAN_17 : "<input name='titleonly' type='radio' value='1'>".NWSLAN_16."&nbsp;&nbsp;<input name='titleonly' type='radio' value='0' checked>".NWSLAN_17)."
		</td>
		</tr>


		<tr> 
		<td style='width:20%' class='forumheader3'>".NWSLAN_19.":<br /><span class='smalltext'>(".NWSLAN_20.")</span></td>
		<td style='width:80%' class='forumheader3'>";

		$text .= "<br />".NWSLAN_21.": <select name='startday' class='tbox'><option selected> </option>";
		for($a=1; $a<=31; $a++){
			$text .= ($a == $_POST['startday'] ? "<option selected>".$a."</option>" : "<option>".$a."</option>");
		}
		$text .= "</select> <select name='startmonth' class='tbox'><option selected> </option>";
		for($a=1; $a<=12; $a++){
			$text .= ($a == $_POST['startmonth'] ? "<option selected>".$a."</option>" : "<option>".$a."</option>");
		}
		$text .= "</select> <select name='startyear' class='tbox'><option selected> </option>";
		for($a=2003; $a<=2010; $a++){
			$text .= ($a == $_POST['startyear'] ? "<option selected>".$a."</option>" : "<option>".$a."</option>");
		}
		$text .= "</select> and <select name='endday' class='tbox'><option selected> </option>";
		for($a=1; $a<=31; $a++){
			$text .= ($a == $_POST['endday'] ? "<option selected>".$a."</option>" : "<option>".$a."</option>");
		}
		$text .= "</select> <select name='endmonth' class='tbox'><option selected> </option>";
		for($a=1; $a<=12; $a++){
			$text .= ($a == $_POST['endmonth'] ? "<option selected>".$a."</option>" : "<option>".$a."</option>");
		}
		$text .= "</select> <select name='endyear' class='tbox'><option selected> </option>";
		for($a=2003; $a<=2010; $a++){
			$text .= ($a == $_POST['endyear'] ? "<option selected>".$a."</option>" : "<option>".$a."</option>");
		}

		$text .= "</select>

		<tr>
		<td class='forumheader3'>
		".NWSLAN_22.":<br /><span class='smalltext'>(".NWSLAN_23.")</span>
		</td>
		<td class='forumheader3'>".r_userclass("news_class",$_POST['news_class'])."

		</td></tr>

		<tr style='vertical-align: top;'>
		<td colspan='2'  style='text-align:center' class='forumheader'>";
			
		if(IsSet($_POST['preview'])){
			$text .= "<input class='button' type='submit' name='preview' value='".NWSLAN_24."' /> ".($id && $sub_action != "sn" ? "<input class='button' type='submit' name='submit' value='".NWSLAN_25."' /> " : "<input class='button' type='submit' name='submit' value='".NWSLAN_26."' /> ");
		}else{
			$text .= "<input class='button' type='submit' name='preview' value='".NWSLAN_27."' />";
		}
		$text .= "\n</td>
		</tr>
		</table>
		<input type='hidden' name='news_id' value='$news_id'>
		</form>
		</div>";
		$ns -> tablerender("<div style='text-align:center'>".NWSLAN_29."</div>", $text);
	}


	function preview_item($id){
		// ##### Display news preview ---------------------------------------------------------------------------------------------------------
		global $aj, $sql, $ix;
		$_POST['news_id'] = $id;
		$_POST['active_start'] = (!$_POST['startmonth'] || !$_POST['startday'] || !$_POST['startyear'] ? 0 : mktime (0, 0, 0, $_POST['startmonth'], $_POST['startday'], $_POST['startyear']));
		$_POST['active_end'] = (!$_POST['endmonth'] || !$_POST['endday'] || !$_POST['endyear'] ? 0 : mktime (0, 0, 0, $_POST['endmonth'], $_POST['endday'], $_POST['endyear']));
		$sql -> db_Select("news_category", "*",  "category_id='".$_POST['cat_id']."' ");
		list($_POST['category_id'], $_POST['category_name'], $_POST['category_icon']) = $sql-> db_Fetch();
		$_POST['admin_id'] = USERID;
		$_POST['admin_name'] = USERNAME;
		$_POST['comment_total'] = $comment_total;
		$_POST['news_datestamp'] = time();

		$_POST['news_title'] = $aj -> formtpa($_POST['news_title']);
		$_POST['data'] = (strstr($_POST['data'], "[img]http") ? $_POST['data'] : str_replace("[img]", "[img]../", $_POST['data']));
		$_POST['data'] = $aj -> formtpa($_POST['data']);
		$_POST['news_extended'] = $aj -> formtpa($_POST['news_extended']);

		$ix -> render_newsitem($_POST);
		$_POST['news_title'] = $aj -> formtpa($_POST['news_title']);
		$_POST['data'] = str_replace("../", "", $aj -> formtparev($_POST['data']));
		$_POST['news_extended'] = $aj -> formtparev($_POST['news_extended']);
	}

	function submit_item($sub_action, $id){
		// ##### Format and submit item ---------------------------------------------------------------------------------------------------------
		global $aj, $ix;
		$_POST['active_start'] = (!$_POST['startmonth'] || !$_POST['startday'] || !$_POST['startyear'] ? 0 : mktime (0, 0, 0, $_POST['startmonth'], $_POST['startday'], $_POST['startyear']));
		$_POST['active_end'] = (!$_POST['endmonth'] || !$_POST['endday'] || !$_POST['endyear'] ? 0 : mktime (0, 0, 0, $_POST['endmonth'], $_POST['endday'], $_POST['endyear']));
		$_POST['admin_id'] = USERID;
		$_POST['admin_name'] = USERNAME;
		$_POST['news_datestamp'] = time();
		if($id && $sub_action != "sn"){ $_POST['news_id'] = $id; }
		if(!$_POST['cat_id']){ $_POST['cat_id'] = 1; }
		$this->show_message($ix -> submit_item($_POST));
		unset($_POST['news_title'], $_POST['cat_id'], $_POST['data'], $_POST['news_extended'], $_POST['news_allow_comments'], $_POST['startday'], $_POST['startmonth'], $_POST['startyear'], $_POST['endday'], $_POST['endmonth'], $_POST['endyear'], $_POST['news_id'], $_POST['news_class']);
		$rsd = new create_rss();
	}

	function show_message($message){
		// ##### Display comfort ---------------------------------------------------------------------------------------------------------
		global $ns;
		$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
	}

	function show_categories($sub_action, $id){
		// ##### Display scrolling list of existing news items ---------------------------------------------------------------------------------------------------------
		global $sql, $rs, $ns, $aj;
		$text = "<div style='border : solid 1px #000; padding : 4px; width :auto; height : 100px; overflow : auto; '>\n";
		if($category_total = $sql -> db_Select("news_category")){
			$text .= "<table class='fborder' style='width:100%'>
			<tr>
			<td style='width:5%' class='forumheader2'>&nbsp;</td>
			<td style='width:75%' class='forumheader2'>".NWSLAN_6."</td>
			<td style='width:20%; text-align:center' class='forumheader2'>".NWSLAN_41."</td>
			</tr>";
			while($row = $sql -> db_Fetch()){
				extract($row);

				if($category_icon){
					$icon = (strstr($category_icon, "images/") ? THEME."$category_icon" : e_IMAGE."newsicons/$category_icon");
				}

				$text .= "<tr>
				<td style='width:5%; text-align:center' class='forumheader3'><img src='$icon' alt='' style='vertical-align:middle' /></td>
				<td style='width:75%' class='forumheader3'>$category_name</td>
				<td style='width:20%; text-align:center' class='forumheader3'>
				".$rs -> form_button("submit", "category_edit", NWSLAN_7, "onClick=\"document.location='".e_SELF."?cat.edit.$category_id'\"")."
				".$rs -> form_button("submit", "category_delete", NWSLAN_8, "onClick=\"confirm_('cat', '$category_id');\"")."
				</td>
				</tr>\n";
			}
			$text .= "</table>";
		}else{
			$text .= "<div style='text-align:center'><div style='vertical-align:center'>".NWSLAN_10."</div>";
		}
		$text .= "</div>";
		$ns -> tablerender(NWSLAN_51, $text);

		$handle=opendir(e_IMAGE."newsicons");
		while ($file = readdir($handle)){
			if($file != "." && $file != ".." && $file != "/" && $file != "null.txt"){
				$iconlist[] = $file;
			}
		}
		closedir($handle);

		unset($category_name, $category_icon);

		if($sub_action == "edit"){
			if($sql -> db_Select("news_category", "*", "category_id='$id' ")){
				$row = $sql -> db_Fetch(); extract($row);
			}
		}

		$text = "<div style='text-align:center'>
		".$rs -> form_open("post", e_SELF."?cat", "dataform")."
		<table class='fborder' style='width:auto'>
		<tr>
		<td class='forumheader3' style='width:30%'><span class='defaulttext'>".NWSLAN_52."</span></td>
		<td class='forumheader3' style='width:70%'>".$rs -> form_text("category_name", 30, $category_name, 200)."</td>
		</tr>
		<tr>
		<td class='forumheader3' style='width:30%'><span class='defaulttext'>".NWSLAN_53."</span></td>
		<td class='forumheader3' style='width:70%'>
		".$rs -> form_text("category_button", 60, $category_icon, 100)."
		<br />
		<input class='button' type ='button' style=''width: 35px'; cursor:hand' size='30' value='".NWSLAN_54."' onClick='expandit(this)'>
		<div style='display:none'>";
		while(list($key, $icon) = each($iconlist)){
			$text .= "<a href='javascript:addtext3(\"$icon\")'><img src='".e_IMAGE."newsicons/".$icon."' style='border:0' alt='' /></a> ";
		}
		$text .= "</td>
		</tr>
		
		<tr><td colspan='2' style='text-align:center' class='forumheader'>";
		if($id){
			$text .= "<input class='button' type='submit' name='update_category' value='".NWSLAN_55."'> 
			".$rs -> form_button("submit", "category_clear", "Clear Form").
			$rs -> form_hidden("category_id", $id)."
			</td></tr>";
		}else{
			$text .= "<input class='button' type='submit' name='create_category' value='".NWSLAN_56."'></td></tr>";
		}
		$text .= "</table>
		".$rs -> form_close()."
		</div>";

		$ns -> tablerender(NWSLAN_56, $text);
	}
	


	function submitted_news($sub_action, $id){


		global $sql, $rs, $ns, $aj;
		$text = "<div style='border : solid 1px #000; padding : 4px; width :auto; height : 200px; overflow : auto; '>\n";
		if($category_total = $sql -> db_Select("submitnews")){
			$text .= "<table class='fborder' style='width:100%'>
			<tr>
			<td style='width:5%' class='forumheader2'>ID</td>
			<td style='width:75%' class='forumheader2'>".NWSLAN_57."</td>
			<td style='width:20%; text-align:center' class='forumheader2'>".NWSLAN_41."</td>
			</tr>";
			while($row = $sql -> db_Fetch()){
				extract($row);
				$text .= "<tr>
				<td style='width:5%; text-align:center; vertical-align:top' class='forumheader3'>$submitnews_id</td>
				<td style='width:75%' class='forumheader3'><b>".$aj -> tpa($submitnews_title)."</b> [ submitted by $submitnews_name ]<br />".$aj -> tpa($submitnews_item)."</td>
				<td style='width:20%; text-align:center; vertical-align:top' class='forumheader3'>
				".$rs -> form_button("submit", "category_edit", NWSLAN_58, "onClick=\"document.location='".e_SELF."?create.sn.$submitnews_id'\"")."
				".$rs -> form_button("submit", "category_delete", NWSLAN_8, "onClick=\"confirm_('sn', $submitnews_id);\"")."
				</td>
				</tr>\n";
			}
			$text .= "</table>";
		}else{
			$text .= "<div style='text-align:center'>".NWSLAN_59."</div>";
		}
		$text .= "</div>";
		$ns -> tablerender(NWSLAN_60, $text);

	}

}


class create_rss{
	function create_rss(){
		/*
		# rss create
		# - parameters		none
		# - return				null
		# - scope					public
		*/
		global $sql;
		$pubdate = strftime("%a, %d %b %Y %I:%M:00 GMT", time());

		$sitebutton = (strstr(SITEBUTTON, "http:") ? SITEBUTTON : SITEURL.str_replace("../", "", e_IMAGE).SITEBUTTON);
		$sitedisclaimer = ereg_replace("<br />|\n", "", SITEDISCLAIMER);

	$rss = "<?xml version=\"1.0\"?>
<rss version=\"2.0\">
<channel>
  <title>".SITENAME."</title> 
  <link>http://".$_SERVER['HTTP_HOST'].e_HTTP."index.php</link> 
  <description>".SITEDESCRIPTION."</description> 
  <language>en-gb</language> 
  <copyright>".$sitedisclaimer."</copyright> 
  <managingEditor>".SITEADMIN." - ".SITEADMINEMAIL."</managingEditor>
  <webMaster>".SITEADMINEMAIL."</webMaster> 
  <pubDate>$pubdate</pubDate>
  <lastBuildDate>$pubdate</lastBuildDate>
  <docs>http://backend.userland.com/rss</docs>
  <generator>e107 website system (http://e107.org)</generator>
  <ttl>60</ttl>

  <image>
    <title>".SITENAME."</title> 
    <url>".$sitebutton."</url> 
    <link>http://".$_SERVER['HTTP_HOST'].e_HTTP."index.php</link> 
    <width>88</width> 
    <height>31</height> 
    <description>".SITETAG."</description>
  </image>
   
  <textInput>
    <title>Search</title>
    <description>Search ".SITENAME."</description>
    <name>query</name>
    <link>".SITEURL.(substr(SITEURL, -1) == "/" ? "" : "/")."search.php</link>
  </textInput>
  ";

	$sql2 = new db;

	$sql -> db_Select("news", "*", "news_class=0 AND (news_start=0 || news_start < ".time().") AND (news_end=0 || news_end>".time().") ORDER BY news_datestamp DESC LIMIT 0, 10");
	while($row = $sql -> db_Fetch()){
		extract($row);
		$sql2 -> db_Select("news_category", "*",  "category_id='$news_category' ");
		$row = $sql2 -> db_Fetch(); extract($row);
		$sql2 -> db_Select("user", "user_name, user_email", "user_id=$news_author");
		$row = $sql2 -> db_Fetch(); extract($row);
		$tmp = explode(" ", $news_body);
		unset($nb);
		for($a=0; $a<=100; $a++){
			$nb .= $tmp[$a]." ";
		}
		if($tmp[($a-2)]){ $nb .= " [more ...]"; }
  		$nb = htmlentities($nb);
		$nb = str_replace('&pound', '&amp;#163;', $nb);
		$nb = str_replace('&copy;', 'c.', $nb);
		$wlog .= $news_title."\n".SITEURL."comment.php?".$news_id."\n\n";
		$itemdate = strftime("%a, %d %b %Y %I:%M:00 GMT", $news_datestamp);

  $rss .= "<item>
    <title>$news_title</title> 
    <link>http://".$_SERVER['HTTP_HOST'].e_HTTP."comment.php?".$news_id."</link> 
    <description>$nb</description>
    <category domain=\"".SITEURL."\">$category_name</category>
    <comments>http://".$_SERVER['HTTP_HOST'].e_HTTP."comment.php?".$news_id."</comments>
    <author>$user_name - $user_email</author>
    <pubDate>$itemdate</pubDate>
    <guid isPermaLink=\"true\">http://".$_SERVER['HTTP_HOST'].e_HTTP."comment.php?".$news_id."</guid>
  </item>
  ";
   
	}

   
	$rss .= "</channel>
</rss>";
	$rss = str_replace("&nbsp;", " ", $rss);
	$fp = fopen(e_FILE."backend/news.xml","w");
	@fwrite($fp, $rss);
	fclose($fp);
	$fp = fopen(e_FILE."backend/news.txt","w");
	@fwrite($fp, $wlog);
	fclose($fp);
	if(!fwrite){
		$text = "<div style='text-align:center'>".LAN_19."</div>";
		$ns -> tablerender("<div style='text-align:center'>".LAN_20."</div>", $text);
	}
}
}
?>

