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
if(!getperms("R")){ header("location:".e_BASE."index.php"); exit; }
require_once("auth.php");
require_once(e_HANDLER."form_handler.php");
require_once(e_HANDLER."userclass_class.php");
$rs = new form;
$aj = new textparse;
$download = new download;

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

$e_file = str_replace("../", "", e_FILE);

if($file_array = getfiles($e_file."downloads/")){ sort($file_array); } unset($t_array);
if($sql -> db_Select("rbinary")){
	while($row = $sql -> db_Fetch()){
		extract($row);
		$file_array[] = "Binary ".$binary_id."/".$binary_name;
	}
}



if($image_array = getfiles($e_file."downloadimages/")){ sort($image_array); } unset($t_array);
if($thumb_array = getfiles($e_file."downloadthumbs/")){ sort($thumb_array); } unset($t_array);

if(IsSet($_POST['add_category'])){
	$download -> create_category($sub_action, $id);
}

if(IsSet($_POST['submit_download'])){
	$download -> submit_download($sub_action, $id);
	$action = "main";
	unset($sub_action, $id);
}


if(IsSet($_POST['updateoptions'])){
	$pref['download_view'] = $_POST['download_view'];
	$pref['download_sort'] = $_POST['download_sort'];
	$pref['download_order'] = $_POST['download_order'];
	save_prefs();
	$message = DOWLAN_65;
}

if($action == "dlm"){
	$action = "create";
	$id = $sub_action;
	$sub_action = "dlm";
}

if($action == "create"){
	$download -> create_download($sub_action, $id);
}

if($action == "cat"){
	if($sub_action == "confirm"){
		if($sql -> db_Delete("download_category", "download_category_id='$id' ")){
			$download -> show_message(DOWLAN_49." #".$id." ".DOWLAN_36);
			$download -> show_categories($sub_action, $id);
		}
	}else{
		$download -> show_categories($sub_action, $id);
	}
}

if($action == "main" && $sub_action == "confirm"){
	if($sql -> db_Delete("download", "download_id='$id' ")){
		$download -> show_message(DOWLAN_35." #".$id." ".DOWLAN_36);
	}
	unset($sub_action, $id);
}


if(IsSet($message)){
        $ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

if(!e_QUERY || $action == "main"){
	$download -> show_existing_items($action, $sub_action, $id, $from, $amount);
}


if($action == "opt"){
	global $pref, $ns;
	$text = "<div style='text-align:center'>
	<form method='post' action='".e_SELF."?".e_QUERY."'>\n
	<table style='width:auto' class='fborder'>
	<tr>
	
	<td style='width:70%' class='forumheader3'>
	".DOWLAN_55."
	</td>
	<td style='width:30%' class='forumheader3' style='text-align:center'>
	<select name='download_view' class='tbox'>".
	($pref['download_view'] == 5 ? "<option selected>5</option>" : "<option>5</option>").
	($pref['download_view'] == 10 ? "<option selected>10</option>" : "<option>10</option>").
	($pref['download_view'] == 15 ? "<option selected>15</option>" : "<option>15</option>").
	($pref['download_view'] == 20 ? "<option selected>20</option>" : "<option>20</option>").
    ($pref['download_view'] == 50 ? "<option selected>50</option>" : "<option>50</option>")."
	</select>
	</td>
	</tr>

	<td style='width:70%' class='forumheader3'>
	".DOWLAN_56."
	</td>
	<td style='width:30%' class='forumheader3' style='text-align:center'>
	
	<select name='download_order' class='tbox'>".
	($pref['download_order'] == "download_datestamp" ? "<option value='download_datestamp' selected>".DOWLAN_58."</option>" : "<option value='download_datestamp'>".DOWLAN_58."</option>").
	($pref['download_order'] == "download_requested" ? "<option value='download_requested' selected>".DOWLAN_57."</option>" : "<option value='download_requested'>".DOWLAN_57."</option>").
	($pref['download_order'] == "download_name" ? "<option value='download_name' selected>".DOWLAN_59."</option>" : "<option value='download_name'>".DOWLAN_59."</option>").
	($pref['download_order'] == "download_author" ? "<option value='download_author' selected>".DOWLAN_60."</option>" : "<option value='download_author'>".DOWLAN_60."</option>")."
	</select>
	</td>
	</tr>
	<td style='width:70%' class='forumheader3'>
	".DOWLAN_61."
	</td>
	<td style='width:30%' class='forumheader3' style='text-align:center'>
	<select name='download_sort' class='tbox'>".
	($pref['download_sort'] == "ASC" ? "<option value='ASC' selected>".DOWLAN_62."</option>" : "<option value='ASC'>".DOWLAN_62."</option>").
	($pref['download_sort'] == "DESC" ? "<option value='DESC' selected>".DOWLAN_63."</option>" : "<option value='DESC'>".DOWLAN_63."</option>")."
	</select>
	</td>
	</tr>

	<tr style='vertical-align:top'> 
	<td colspan='2'  style='text-align:center' class='forumheader'>
	<input class='button' type='submit' name='updateoptions' value='".DOWLAN_64."' />
	</td>
	</tr>

	</table>
	</form>
	</div>";
	$ns -> tablerender(DOWLAN_54, $text);
}

$download -> show_options($action);

require_once("footer.php");

echo "<script type=\"text/javascript\">

function addtext2(str){
	document.dlform.download_category_icon.value = str;
}


function confirm_(mode, download_id){
	if(mode == 'cat'){
		var x=confirm(\"".DOWLAN_34." [ID: \" + download_id + \"]\");
	}else{
		var x=confirm(\"".DOWLAN_33." [ID: \" + download_id + \"]\");
	}
if(x)
	if(mode == 'cat'){
		window.location='".e_SELF."?cat.confirm.' + download_id;
	}else{
		window.location='".e_SELF."?main.confirm.' + download_id;
	}
}
</script>";

exit;

class download{

	function show_existing_items($action, $sub_action, $id, $from, $amount){
		global $sql, $rs, $ns, $aj;
		$text = "<div style='text-align:center'><div style='border : solid 1px #000; padding : 4px; width : auto; height : 200px; overflow : auto; '>";

		if(IsSet($_POST['searchquery'])){
			$query = "download_name REGEXP('".$_POST['searchquery']."') OR download_url REGEXP('".$_POST['searchquery']."') OR download_author REGEXP('".$_POST['searchquery']."') OR download_description  REGEXP('".$_POST['searchquery']."') ORDER BY download_datestamp DESC";
		}else{
			$query = "ORDER BY ".($sub_action ? $sub_action : "download_datestamp")." ".($id ? $id : "DESC")."  LIMIT $from, $amount";
		}

		if($sql -> db_Select("download", "*", $query, ($_POST['searchquery'] ? 0 : "nowhere"))){
			$text .= "<table class='fborder' style='width:100%'>
			<tr>
			<td style='width:5%' class='forumheader2'>ID</td>
			<td style='width:50%' class='forumheader2'>".DOWLAN_27."</td>
			<td style='width:45%' class='forumheader2'>".DOWLAN_28."</td>
			</tr>";
			while($row = $sql -> db_Fetch()){
				extract($row);
				$text .= "<tr>
				<td style='width:5%' class='forumheader3'>$download_id</td>
				<td style='width:75%' class='forumheader3'>$download_name</a></td>
				<td style='width:20%; text-align:center' class='forumheader3'>
				".$rs -> form_button("submit", "main_edit", DOWLAN_8, "onClick=\"document.location='".e_SELF."?create.edit.$download_id'\"")."
				".$rs -> form_button("submit", "main_delete", DOWLAN_9, "onClick=\"confirm_('create', $download_id)\"")."
				</td>
				</tr>";
			}
			$text .= "</table>";
		}else{
			$text .= "<div style='text-align:center'>".DOWLAN_6."</div>";
		}
		$text .= "</div>";

		$downloads = $sql -> db_Count("download");

		if($downloads > $amount && !$_POST['searchquery']){
			$a = $downloads/$amount;
			$r = explode(".", $a);
			if($r[1] != 0 ? $pages = ($r[0]+1) : $pages = $r[0]);
			if($pages){
				$current = ($from/$amount)+1;
				$text .= "<br />".DOWLAN_50." ";
				for($a=1; $a<=$pages; $a++){
					$text .= ($current == $a ? " <b>[$a]</b>" : " [<a href='".e_SELF."?".(e_QUERY ? "$action.$sub_action.$id." : "main.download_datestamp.desc.").(($a-1)*$amount)."'>$a</a>] ");
				}
				$text .= "<br />";
			}
		}

		$text .= "<br /><form method='post' action='".e_SELF."'>\n<p>\n<input class='tbox' type='text' name='searchquery' size='20' value='' maxlength='50' />\n<input class='button' type='submit' name='searchsubmit' value='".DOWLAN_51."' />\n</p>\n</form>\n</div>";


		$ns -> tablerender(DOWLAN_7, $text);
	}

	function show_options($action){
		global $sql, $rs, $ns;
		$text = "<div style='text-align:center'>";
		if(e_QUERY && $action != "main"){
			$text .= "<a href='".e_SELF."'><div class='border'><div class='forumheader'><img src='".e_IMAGE."generic/location.png' style='vertical-align:middle; border:0' alt='' /> ".DOWLAN_29."</div></div></a>";
		}
		if($action != "opt"){
			$text .= "<a href='".e_SELF."?opt'><div class='border'><div class='forumheader'><img src='".e_IMAGE."generic/location.png' style='vertical-align:middle; border:0' alt='' /> ".DOWLAN_28."</div></div></a>";
		}
		if($action != "create"){
			$text .= "<a href='".e_SELF."?create'><div class='border'><div class='forumheader'><img src='".e_IMAGE."generic/location.png' style='vertical-align:middle; border:0' alt='' /> ".DOWLAN_30."</div></div></a>";
		}
		if($action != "cat" && getperms("Q")){
			$text .= "<a href='".e_SELF."?cat'><div class='border'><div class='forumheader'><img src='".e_IMAGE."generic/location.png' style='vertical-align:middle; border:0' alt='' /> ".DOWLAN_31."</div></div></a>";
		}
		$text .= "</div>";
		$ns -> tablerender(DOWLAN_32, $text);
	}

	function create_download($sub_action, $id){
		global $sql, $rs, $ns, $file_array, $image_array, $thumb_array;

		if(!$sql -> db_Select("download_category")){
			$ns -> tablerender(DOWLAN_26, "<div style='text-align:center'>".DOWLAN_5."</div>");
			return;
		}


		if($sub_action == "edit" && !$_POST['submit']){
			if($sql -> db_Select("download", "*", "download_id='$id' ")){
				$row = $sql-> db_Fetch();
				extract($row);
			}
		}

		if($sub_action == "dlm" && !$_POST['submit']){
			if($sql -> db_Select("upload", "*", "upload_id='$id' ")){
				$row = $sql-> db_Fetch();
				extract($row);
				$download_name = $upload_name.($upload_version ? " - " . $upload_version : "");
				$download_url = $upload_file;
				$download_author_email = $upload_email;
				$download_author_website = $upload_website;
				$download_description = $upload_description;
				$download_image = $upload_ss;
				$download_filesize = $upload_filesize;
				$file_array[] = $download_url;
				$image_array[] = $upload_ss;
				$download_author = substr($upload_poster, (strpos($upload_poster, ".")+1));
			}
		}

		$text = "
		<div style='text-align:center'>
		<form method='post' action='".e_SELF."?".e_QUERY."' name='myform'>
		<table style='width:85%' class='fborder'>
		<tr>
		<td style='width:20%' class='forumheader3'>".DOWLAN_11.":</td>
		<td style='width:80%' class='forumheader3'>";

		$sql -> db_Select("download_category", "*", "download_category_parent !=0");
		$text .= "<select name='download_category' class='tbox'>\n";
		while($row = $sql -> db_Fetch()){
			extract($row);
			if($download_category_id == $download_category){
				$text .= "<option value='$download_category_id' selected>".$download_category_name."</option>\n";
			}else{
				$text .= "<option value='$download_category_id'>".$download_category_name."</option>\n";
			}
		}
		$text .= "</select>
		</td>
		</tr>

		<tr>
		<td style='width:20%; vertical-align:top' class='forumheader3'><u>".DOWLAN_12."</u>:</td>
		<td style='width:80%' class='forumheader3'>
		<input class='tbox' type='text' name='download_name' size='60' value='$download_name' maxlength='200' />
		</td>
		</tr>

		<td style='width:20%; vertical-align:top' class='forumheader3'><u>".DOWLAN_13."</u>:</td>
		<td style='width:80%' class='forumheader3'>
		<select name='download_url' class='tbox'>
		<option></option>
		";

		$counter = 0;
		while(IsSet($file_array[$counter])){
			if($file_array[$counter] == $download_url){
				$text .= "<option selected>".$file_array[$counter]."</option>\n";
			}else{
				$text .= "<option>".$file_array[$counter]."</option>\n";
			}
			$counter++;
		}
		$text .= "</select>
		<br />
		<span class='smalltext'> ".DOWLAN_14.": ";


		if(ereg("http", $download_url) || ereg("ftp", $download_url) ){
			$download_url_external = $download_url;
		}

		$text .= "<input class='tbox' type='text' name='download_url_external' size='40' value='$download_url_external' maxlength='100' />
		&nbsp;&nbsp;filesize: 
		<input class='tbox' type='text' name='download_filesize_external' size='8' value='$download_filesize' maxlength='10' />
		</span>

		</td>
		</tr>

		<tr>
		<td style='width:20%' class='forumheader3'>".DOWLAN_15.":</td>
		<td style='width:80%' class='forumheader3'>
		<input class='tbox' type='text' name='download_author' size='60' value='$download_author' maxlength='100' />
		</td>
		</tr>

		<tr>
		<td style='width:20%' class='forumheader3'>".DOWLAN_16.":</td>
		<td style='width:80%' class='forumheader3'>
		<input class='tbox' type='text' name='download_author_email' size='60' value='$download_author_email' maxlength='100' />
		</td>
		</tr>

		<tr>
		<td style='width:20%' class='forumheader3'>".DOWLAN_17.":</td>
		<td style='width:80%' class='forumheader3'>
		<input class='tbox' type='text' name='download_author_website' size='60' value='$download_author_website' maxlength='100' />
		</td>
		</tr>

		<tr>
		<td style='width:20%' class='forumheader3'><u>".DOWLAN_18."</u>: </td>
		<td style='width:80%' class='forumheader3'>
		<textarea class='tbox' name='download_description' cols='70' rows='5'>$download_description</textarea>
		</td>
		</tr>

		<tr>
		<td style='width:20%' class='forumheader3'>".DOWLAN_19.":</td>
		<td style='width:80%' class='forumheader3'>
		<select name='download_image' class='tbox'>
		<option></option>
		";
		$counter = 0;
		while(IsSet($image_array[$counter])){
			if($image_array[$counter] == $download_image){
				$text .= "<option selected>".$image_array[$counter]."</option>\n";
			}else{
				$text .= "<option>".$image_array[$counter]."</option>\n";
			}
			$counter++;
		}
		$text .= "</select>
		</td>
		</tr>

		<tr>
		<td style='width:20%' class='forumheader3'>".DOWLAN_20.":</td>
		<td style='width:80%' class='forumheader3'>
		<select name='download_thumb' class='tbox'>
		<option></option>
		";
		$counter = 0;
		while(IsSet($thumb_array[$counter])){
			if($thumb_array[$counter] == $download_thumb){
				$text .= "<option selected>".$thumb_array[$counter]."</option>\n";
			}else{
				$text .= "<option>".$thumb_array[$counter]."</option>\n";
			}
			$counter++;
		}
		$text .= "</select>
		</td>
		</tr>

		<tr>
		<td style='width:20%' class='forumheader3'>".DOWLAN_21.":</td>
		<td style='width:80%' class='forumheader3'>";


		if($download_active == "0"){
			$text .= DOWLAN_22.": <input type='radio' name='download_active' value='1'>
			".DOWLAN_23.": <input type='radio' name='download_active' value='0' checked>";
		}else{
			$text .= DOWLAN_22.": <input type='radio' name='download_active' value='1' checked>
			".DOWLAN_23.": <input type='radio' name='download_active' value='0'>";
		}

		$text .= "</td>
		</tr>
		<tr style='vertical-align:top'>
		<td colspan='2' style='text-align:center' class='forumheader'>";


		if($id && $sub_action == "edit"){
			$text .= "<input class='button' type='submit' name='submit_download' value='".DOWLAN_24."' /> ";
		}else{
			$text .= "<input class='button' type='submit' name='submit_download' value='".DOWLAN_25."' />";
		}

		$text .= "</td>
		</tr>
		</table>
		</form>
		</div>";


		$ns -> tablerender("<div style='text-align:center'>".DOWLAN_26."</div>", $text);
	}

	function show_message($message){
		global $ns;
		$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
	}

	function submit_download($sub_action, $id){
		global $aj, $sql;

		if($_POST['download_url_external']){
			$durl = $_POST['download_url_external'];
			$filesize = $_POST['download_filesize_external'];
		}else{
			$durl = $_POST['download_url'];
			$filesize = ($_POST['download_filesize_external'] ? $_POST['download_filesize_external'] : filesize(e_FILE."downloads/".$_POST['download_url']));
		}

		if(!$filesize){
			$sql -> db_Select("upload", "upload_filesize", "upload_file='$durl'");
			$row = $sql -> db_Fetch(); extract($row);
			$filesize = $upload_filesize;
		}

		$_POST['download_description'] = $aj -> formtpa($_POST['download_description'], "admin");

		if($id){
			$sql -> db_Update("download", "download_name='".$_POST['download_name']."', download_url='".$durl."', download_author='".$_POST['download_author']."', download_author_email='".$_POST['download_author_email']."', download_author_website='".$_POST['download_author_website']."', download_description='".$_POST['download_description']."', download_filesize='".$filesize."', download_category='".$_POST['download_category']."', download_active='".$_POST['download_active']."', download_datestamp='".time()."', download_thumb='".$_POST['download_thumb']."', download_image='".$_POST['download_image']."' WHERE download_id=$id");
			$this->show_message(DOWLAN_2);
		}else{

			$sql -> db_Insert("download", "0, '".$_POST['download_name']."', '".$durl."', '".$_POST['download_author']."', '".$_POST['download_author_email']."', '".$_POST['download_author_website']."', '".$_POST['download_description']."', '".$filesize."', '0', '".$_POST['download_category']."', '".$_POST['download_active']."', '".time()."', '".$_POST['download_thumb']."', '".$_POST['download_image']."' ");
			$this->show_message(DOWLAN_1);
		}
	}

	function show_categories($sub_action, $id){
		global $sql, $rs, $ns, $sql2, $sql3;
		if(!is_object($sql2)){
			$sql2 = new db;
		}
		if(!is_object($sql3)){
			$sql3 = new db; $sql4 = new db;
		}
		$text = "<div style='border : solid 1px #000; padding : 4px; width : auto; height : 200px; overflow : auto; '>";
		if($download_total = $sql -> db_Select("download_category", "*", "download_category_parent=0")){
			$text .= "<table class='fborder' style='width:100%'>

			<tr>
			<td style='width:5%; text-align:center' class='fcaption'>&nbsp;</td>
			<td style='width:70%; text-align:center' class='fcaption'>".DOWLAN_11."</td>
			<td style='width:5%; text-align:center' class='fcaption'>".DOWLAN_52."</td>
			<td style='width:25%; text-align:center' class='fcaption'>".DOWLAN_28."</td>
			</tr>";

			while($row = $sql -> db_Fetch()){
				extract($row);
				$text .= "<tr>
				<td style='width:5%; text-align:center' class='forumheader'>".($download_category_icon ? "<img src='".e_IMAGE."download_icons/$download_category_icon' style='vertical-align:middle; border:0' alt='' />" : "&nbsp;")."</td>
				<td colspan='2' style='width:70%' class='forumheader'><b>$download_category_name</b></a></td>
				
				<td style='width:20%; text-align:center' class='forumheader'>
				".$rs -> form_button("submit", "main_edit", DOWLAN_8, "onClick=\"document.location='".e_SELF."?cat.edit.$download_category_id'\"")."
				".$rs -> form_button("submit", "main_delete", DOWLAN_9, "onClick=\"confirm_('cat', $download_category_id)\"")."
				</td>
				</tr>";
				$parent_id = $download_category_id;
				if($sql2 -> db_Select("download_category", "*", "download_category_parent=$parent_id")){
					while($row = $sql2-> db_Fetch()){
						extract($row);

						$files = $sql4 -> db_Count("download", "(*)", "WHERE download_category='".$download_category_id."'");


						$text .= "<tr>
						<td style='width:5%; text-align:center' class='forumheader3'>".($download_category_icon ? "<img src='".e_IMAGE."download_icons/$download_category_icon' style='vertical-align:middle; border:0' alt='' />" : "&nbsp;")."</td>
						<td style='width:70%' class='forumheader3'>$download_category_name<br /><span class='smalltext'>$download_category_description</span></td>
						<td style='width:5%; text-align:center' class='forumheader3'>$files</td>
						<td style='width:20%; text-align:center' class='forumheader3'>
						".$rs -> form_button("submit", "main_edit", DOWLAN_8, "onClick=\"document.location='".e_SELF."?cat.edit.$download_category_id'\"")."
						".$rs -> form_button("submit", "main_delete", DOWLAN_9, "onClick=\"confirm_('cat', $download_category_id)\"")."
						</td>
						</tr>";
						$sub_parent_id = $download_category_id;
						if($sql3 -> db_Select("download_category", "*", "download_category_parent=$sub_parent_id")){
							while($row = $sql3-> db_Fetch()){
								extract($row);
								$files = $sql4 -> db_Count("download", "(*)", "WHERE download_category='".$download_category_id."'");
								$text .= "<tr>
								<td style='width:5%; text-align:center' class='forumheader3'>".($download_category_icon ? "<img src='".e_IMAGE."download_icons/$download_category_icon' style='vertical-align:middle; border:0' alt='' />" : "&nbsp;")."</td>
								<td style='width:70%' class='forumheader3'>&nbsp;&nbsp;&nbsp;&nbsp;".DOWLAN_53.": $download_category_name<br />&nbsp;&nbsp;&nbsp;&nbsp;<span class='smalltext'>$download_category_description</span></td>
								<td style='width:5%; text-align:center' class='forumheader3'>$files</td>
								<td style='width:20%; text-align:center' class='forumheader3'>
								".$rs -> form_button("submit", "main_edit", DOWLAN_8, "onClick=\"document.location='".e_SELF."?cat.edit.$download_category_id'\"")."
								".$rs -> form_button("submit", "main_delete", DOWLAN_9, "onClick=\"confirm_('cat', $download_category_id)\"")."
								</td>
								</tr>";
							}
						}
					}
				}
			}
			$text .= "</table>";
		}else{
			$text .= "<div style='text-align:center'>".DOWLAN_38."</div>";
		}
		$text .= "</div>";
		$ns -> tablerender(DOWLAN_37, $text);

		unset($download_category_id, $download_category_name, $download_category_description, $download_category_parent, $download_category_icon, $download_category_class);



		$handle=opendir(e_IMAGE."download_icons");
		while ($file = readdir($handle)){
			if($file != "." && $file != ".." && $file != "/"){
				$iconlist[] = $file;
			}
		}
		closedir($handle);

		if($sub_action == "edit" && !$_POST['add_category']){
			if($sql -> db_Select("download_category", "*", "download_category_id=$id")){
				$row = $sql -> db_Fetch(); extract($row);
				$main_category_parent = $download_category_parent;
			}
		}

		$text = "<div style='text-align:center'>
		<form method='post' action='".e_SELF."?".e_QUERY."' name='dlform'>
		<table style='width:auto' class='fborder'>
		<tr>
		<td style='width:30%' class='forumheader3'>".DOWLAN_37.": </td>
		<td style='width:70%' class='forumheader3'>";

		if(!$download_cats = $sql -> db_Select("download_category")){
			$text .= "
			<select name='download_category_parent' class='tbox'>
			<option>".DOWLAN_40."</option>
			</select>\n";
		}else{
			$text .= "
			<select name='download_category_parent' class='tbox'>
			<option>".DOWLAN_40."</option>";

			while(list($cat_id, $cat_name, $null, $null, $cat_parent) = $sql-> db_Fetch()){
				$sql2 -> db_Select("download_category", "download_category_parent", "download_category_id='$cat_parent'", TRUE);
				$row = $sql2 -> db_Fetch(); extract($row);
				if(!$download_category_parent || !$cat_parent){
					$text .= ($main_category_parent == $cat_id ? "<option value='$cat_id' selected>".$cat_name."</option>" : "<option value='$cat_id'>".$cat_name."</option>");
				}
			}
			$text .= "</select>";
		}
		$text .= "<tr>
		<td style='width:30%' class='forumheader3'>".DOWLAN_12.": </td>
		<td style='width:70%' class='forumheader3'>
		<input class='tbox' type='text' name='download_category_name' size='40' value='$download_category_name' maxlength='100' />
		</td>
		</tr>

		<tr>
		<td style='width:30%' class='forumheader3'>".DOWLAN_18.": </td>
		<td style='width:70%' class='forumheader3'>
		<textarea class='tbox' name='download_category_description' cols='59' rows='3'>$download_category_description</textarea>
		</td>
		</tr>

		<tr>
		<td style='width:30%' class='forumheader3'>".DOWLAN_41.": </td>
		<td style='width:70%' class='forumheader3'>
		<input class='tbox' type='text' name='download_category_icon' size='60' value='$download_category_icon' maxlength='100' />

		<br />
		<input class='button' type ='button' style=''width: 35px'; cursor:hand' size='30' value='".DOWLAN_42."' onClick='expandit(this)'>
		<div style='display:none' style=&{head};>";



		while(list($key, $icon) = each($iconlist)){
			$text .= "<a href='javascript:addtext2(\"$icon\")'><img src='".e_IMAGE."download_icons/".$icon."' style='border:0' alt='' /></a> ";
		}

		$text .= "
		</td>
		</tr>

		<tr>
		<td style='width:30%' class='forumheader3'>".DOWLAN_43.":<br /><span class='smalltext'>(".DOWLAN_44.")</span></td>
		<td style='width:70%' class='forumheader3'>".r_userclass("download_category_class",$download_category_class)."

		</td></tr>


		<tr style='vertical-align:top'>
		<td colspan='2' style='text-align:center' class='forumheader'>";
		if($id && $sub_action == "edit"){
			$text .= "<input class='button' type='submit' name='add_category' value='".DOWLAN_46."' /> ";
		}else{
			$text .= "<input class='button' type='submit' name='add_category' value='".DOWLAN_45."' />";
		}
		$text .= "</td>
		</tr>
		</table>
		</form>
		</div>";
		$ns -> tablerender(DOWLAN_39, $text);



	}

	function create_category($sub_action, $id){
		global $aj, $sql;
		$download_category_name = $aj -> formtpa($_POST['download_category_name'], "admin");
		$download_category_description = $aj -> formtpa($_POST['download_category_description'], "admin");
		$download_category_icon = $aj -> formtpa($_POST['download_category_icon'], "admin");

		if($id){
			$sql -> db_Update("download_category", "download_category_name='$download_category_name', download_category_description='$download_category_description', download_category_icon ='$download_category_icon', download_category_parent= '".$_POST['download_category_parent']."', download_category_class='".$_POST['download_category_class']."' WHERE download_category_id='$id'");
			$this->show_message(DOWLAN_48);
		}else{
			$sql -> db_Insert("download_category", "0, '$download_category_name', '$download_category_description', '$download_category_icon', '".$_POST['download_category_parent']."', '".$_POST['download_category_class']."' ");
			$this->show_message(DOWLAN_47);
		}
		if($sub_action == "sn"){
			$sql -> db_Delete("tmp", "tmp_time='$id' ");
		}
	}
	






}

function getfiles($dir){
	global $t_array, $FILES_DIRECTORY;
	$pathdir = e_BASE.$dir;
	$dh = opendir($pathdir);
	$size = 0;
	$search = array("../", $FILES_DIRECTORY, "downloads/", "downloadimages/", "downloadthumbs/");
	$replace = array("", "", "", "", "");
	while($file = readdir($dh)){
		if($file != "." and $file != ".." && $file != "index.html" && $file != "null.txt"){
			if(is_file($pathdir.$file)){
				$t_array[] = str_replace($search, $replace, $pathdir.$file);
			}else{
				getfiles(str_replace("../", "", $pathdir.$file)."/");
			}
		}
	}
	closedir($dh);
	return $t_array;
}
?>