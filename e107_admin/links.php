<?php
/*
+---------------------------------------------------------------+
|        e107 website system
|        /admin/links.php
|
|        ©Steve Dunstan 2001-2002
|        http://e107.org
|        jalist@e107.org
|
|        Released under the terms and conditions of the
|        GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("../class2.php");
if(!getperms("I")){ header("location:".e_BASE."index.php"); }
require_once("auth.php");
require_once(e_HANDLER."userclass_class.php");
$aj = new textparse;

if(e_QUERY){
	$qs = explode(".", e_QUERY);
	$action = $qs[0];
	$linkid = $qs[1];
	$link_order = $qs[2];
	$location = $qs[3];
}


if($action == "dec"){
	$sql -> db_Update("links", "link_order=link_order-1 WHERE link_order='".($link_order+1)."' AND link_category='$location' ");
	$sql -> db_Update("links", "link_order=link_order+1 WHERE link_id='$linkid' AND link_category='$location' ");
	header("location: ".e_SELF);
}

if($action == "inc"){
	$sql -> db_Update("links", "link_order=link_order+1 WHERE link_order='".($link_order-1)."' AND link_category='$location' ");
	$sql -> db_Update("links", "link_order=link_order-1 WHERE link_id='$linkid' AND link_category='$location' ");
	header("location: ".e_SELF);
}

if(IsSet($_POST['updateoptions'])){
	$pref['linkpage_categories'] = $_POST['linkpage_categories'];
	$pref['link_submit'] = $_POST['link_submit'];
	$pref['link_submit_class'] = $_POST['link_submit_class'];
	save_prefs();
	$message = LCLAN_1;
}

$handle=opendir(e_IMAGE."link_icons");
while ($file = readdir($handle)){
	if($file != "." && $file != ".." && $file != "/"){
		$iconlist[] = $file;
	}
}
closedir($handle);


if($_POST['add_link'] != ""){
        $sql -> db_Select("link_category", "*", "link_category_name='".$_POST['cat_name']."' ");
        $row = $sql -> db_Fetch();
        $link_cat_id = $row['link_category_id'];
		$_POST['link_name'] = $aj -> formtpa($_POST['link_name'], "admin");
		$_POST['link_url'] = $aj -> formtpa($_POST['link_url'], "admin");
		$_POST['link_description'] = $aj -> formtpa($_POST['link_description'], "admin");
		$_POST['link_button'] = $aj -> formtpa($_POST['link_button'], "admin");

        $sql -> db_Insert("links", "0, '".$_POST['link_name']."', '".$_POST['link_url']."', '".$_POST['link_description']."', '".$_POST['link_button']."', '$link_cat_id', '0', '0', '".$_POST['linkopentype']."', '".$_POST['link_class']."' ");
        $message = LCLAN_2;
        unset ($link_id, $link_name, $link_url, $link_description, $link_button, $link_main);
}

if(IsSet($_POST['update_link'])){
        $sql -> db_Select("link_category", "*", "link_category_name='".$_POST['cat_name']."' ");
        $row = $sql -> db_Fetch();
        $link_cat_id = $row['link_category_id'];
		$_POST['link_name'] = $aj -> formtpa($_POST['link_name'], "admin");
		$_POST['link_url'] = $aj -> formtpa($_POST['link_url'], "admin");
		$_POST['link_description'] = $aj -> formtpa($_POST['link_description'], "admin");
		$_POST['link_button'] = $aj -> formtpa($_POST['link_button'], "admin");
        $sql -> db_Update("links", "link_name='".$_POST['link_name']."', link_url='".$_POST['link_url']."', link_description='".$_POST['link_description']."', link_button= '".$_POST['link_button']."', link_category='$link_cat_id', link_open='".$_POST['linkopentype']."', link_class='".$_POST['link_class']."' WHERE link_id='".$_POST['link_id']."' ");
        $message = LCLAN_3;
        unset ($link_id, $link_name, $link_url, $link_description, $link_button, $link_main);
}

if(IsSet($_POST['edit']) || $action == "edit"){
        if($action == "edit"){
                $sql -> db_Select("links", "*", "link_id='".$linkid."' ");
        }else{
                $sql -> db_Select("links", "*", "link_id='".$_POST['existing']."' ");
        }
        list($link_id, $link_name, $link_url, $link_description, $link_button, $link_category, $link_order, $link_refer, $link_open, $link_class) = $sql-> db_Fetch();
}

if($action == "delete"){
	$sql -> db_Delete("links", "link_id='$linkid'");
	$message = LCLAN_4;
}

if(IsSet($_POST['delete'])){
    if($_POST['confirm']){
		$sql -> db_Delete("links", "link_id='".$_POST['existing']."' ");
        $message = LCLAN_4;
	}else{
		$message = LCLAN_5;
	}
}


if(IsSet($_POST['update_order'])){

        extract($_POST);

		while(list($key, $id) = each($link_order)){
			$tmp = explode(".", $id);
			$sql -> db_Update("links", "link_order=".$tmp[1]." WHERE link_id=".$tmp[0]);
		}
		
        $message = LCLAN_6;

}

if($sql -> db_Select("tmp", "*", "tmp_ip='submitted_link' ")){
	$gen = new convert; 
	$text = "<div style='text-align:center'>
	<table style='width:85%' class='fborder'>\n";
	while($row = $sql -> db_Fetch()){
		extract($row);
		$submitted = explode("^", $tmp_info);
		$datestamp = $gen->convert_date($tmp_time, "short");
		if(!strstr($submitted[2], "http")){
			$submitted[2] = "http://".$submitted[2];
		}
		$text .= "<tr><td class='forumheader3'>
		<table>
		<tr><td style='width:40%'>".LCLAN_45."</td><td style='width:60%'><b>".$submitted[5]."</b></td></tr>
		<tr><td style='width:40%'>".LCLAN_12."</td><td style='width:60%'>".$submitted[0]."</td></tr>
		<tr><td style='width:40%'>".LCLAN_15."</td><td style='width:60%'>".$submitted[1]."</td></tr>
		<tr><td style='width:40%'>".LCLAN_16."</td><td style='width:60%'><a href='".$submitted[2]."'>".$submitted[2]."</a></td></tr>
		<tr><td style='width:40%'>".LCLAN_17."</td><td style='width:60%'>".$submitted[3]."</td></tr>
		<tr><td style='width:40%'>".LCLAN_18."</td><td style='width:60%'>".$submitted[4]."</td></tr>
		<tr><td style='width:40%'>&nbsp;</td><td style='width:60%'>[ <a href='".e_SELF."?sval.$tmp_time'>".LCLAN_46."</a> ] [ <a href='".e_SELF."?sdel.$tmp_time'>".LCLAN_50."</a> ]</td></tr>
		</table>
		</td>
		</tr>";
	}
	$text .= "</table>
	</div>";
	$ns -> tablerender(LCLAN_47, $text);
}

if($action == "sval"){
	$sql -> db_Select("tmp", "*", "tmp_time='$linkid' ");
	$row = $sql -> db_Fetch(); extract($row);
	$submitted = explode("^", $tmp_info);
	$sql -> db_Select("link_category", "*", "link_category_name='".$submitted[0]."' ");
	$row = $sql -> db_Fetch(); extract($row);
	$link_category = $link_category_id;
	$link_name = $submitted[1];
	$link_url = $submitted[2];
	$link_description = $submitted[3]."\n<br /><i>".LCLAN_45." ".$submitted[5]."</i>";
	$link_button = $submitted[4];
}

if($action == "sdel"){
	$sql -> db_Delete("tmp", "tmp_time='$linkid' ");
	$message = LCLAN_49;
}

if(IsSet($message)){
        $ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

$link_total = $sql -> db_Select("links");

$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."' name='linkform'>
<table style='width:85%' class='fborder'>
<tr>
<td colspan='2' class='forumheader' style='text-align:center'>";

if(!$link_total){
	$text .= "<span class='defaulttext'>".LCLAN_7."</span>";
}else{
$text .= "<span class='defaulttext'>".LCLAN_8.":</span>
	<select name='existing' class='tbox'>";
	while(list($link_id_, $link_name_) = $sql-> db_Fetch()){
		$text .= "<option value='$link_id_'>".$link_name_."</option>";
	}
	$text .= "</select>
	<input class='button' type='submit' name='edit' value='".LCLAN_9."' />
	<input class='button' type='submit' name='delete' value='".LCLAN_10."' />
	<input type='checkbox' name='confirm' value='1'><span class='smalltext'> ".LCLAN_11."</span>";
}

$text .= "
</td>
</tr>
<tr>
<td style='width:30%' class='forumheader3'>".LCLAN_12.": </td>
<td style='width:70%' class='forumheader3'>";

if(!$link_cats = $sql -> db_Select("link_category")){
	$text .= "<div class='twelvept'>".LCLAN_13."</div><br />";
}else{

        $text .= "
        <select name='cat_name' class='tbox'>";

        while(list($cat_id, $cat_name, $cat_description) = $sql-> db_Fetch()){
                if($link_category == $cat_id || ($cat_id == $linkid && $action == "add")){
                        $text .= "<option selected>".$cat_name."</option>";
                }else{
                        $text .= "<option>".$cat_name."</option>";
                }
        }
        $text .= "</select>";
}
$text .= "<span class='twelvept'> [ <a href='link_category.php'>".LCLAN_14."</a> ]</span>

<tr>
<td style='width:30%' class='forumheader3'>".LCLAN_15.": </td>
<td style='width:70%' class='forumheader3'>
<input class='tbox' type='text' name='link_name' size='60' value='$link_name' maxlength='100' />
</td>
</tr>

<tr>
<td style='width:30%' class='forumheader3'>".LCLAN_16.": </td>
<td style='width:70%' class='forumheader3'>
<input class='tbox' type='text' name='link_url' size='60' value='$link_url' maxlength='200' />
</td>
</tr>

<tr>
<td style='width:30%' class='forumheader3'>".LCLAN_17.": </td>
<td style='width:70%' class='forumheader3'>
<textarea class='tbox' name='link_description' cols='59' rows='3'>$link_description</textarea>
</td>
</tr>

<tr>
<td style='width:30%' class='forumheader3'>".LCLAN_18.": </td>
<td style='width:70%' class='forumheader3'>
<input class='tbox' type='text' name='link_button' size='60' value='$link_button' maxlength='100' />

<br />
<input class='button' type ='button' style=''width: 35px'; cursor:hand' size='30' value='".LCLAN_39."' onClick='expandit(this)'>
<div style='display:none' style=&{head};>";



while(list($key, $icon) = each($iconlist)){
	$text .= "<a href='javascript:addtext(\"$icon\")'><img src='".e_IMAGE."link_icons/".$icon."' style='border:0' alt='' /></a> ";
}










$text .= "</td>
</tr>
<tr>
<td style='width:30%' class='forumheader3'>".LCLAN_19.": </td>
<td style='width:70%' class='forumheader3'>
<select name='linkopentype' class='tbox'>".
($link_open == 0 ? "<option value='0' selected>".LCLAN_20."</option>" : "<option value='0'>".LCLAN_20."</option>").

($link_open == 1 ? "<option value='1' selected>".LCLAN_21."</option>" : "<option value='1'>".LCLAN_21."</option>").
($link_open == 2 ? "<option value='2' selected>".LCLAN_22."</option>" : "<option value='2'>".LCLAN_22."</option>").
($link_open == 3 ? "<option value='3' selected>".LCLAN_23."</option>" : "<option value='3'>".LCLAN_23."</option>").
($link_open == 4 ? "<option value='4' selected>".LCLAN_24."</option>" : "<option value='4'>".LCLAN_24."</option>")."
</select>
</td>
</tr>

<tr>
<td style='width:30%' class='forumheader3'>".LCLAN_25.":<br /><span class='smalltext'>(".LCLAN_26.")</span></td>
<td style='width:70%' class='forumheader3'>".r_userclass("link_class",$link_class)."

</td></tr>


<tr style='vertical-align:top'>
<td colspan='2' style='text-align:center' class='forumheader'>";
if(IsSet($_POST['edit']) || $action == "edit"){
        $text .= "<input class='button' type='submit' name='update_link' value='".LCLAN_27."' />
<input type='hidden' name='link_id' value='$link_id'>";
}else{
        $text .= "<input class='button' type='submit' name='add_link' value='".LCLAN_28."' />";
}
$text .= "</td>
</tr>
</table>
</form>
</div>";
$ns -> tablerender("<div style='text-align:center'>".LCLAN_29."</div>", $text);

/*
$sql2 = new db;
for($a=1; $a<=$link_cats; $a++){
	if($sql -> db_Select("links", "*",  "link_category='$a' ORDER BY link_order ASC")){
		$c=1;
		while($row = $sql -> db_Fetch()){
			extract($row);
			$sql2 -> db_Update("links", "link_order='$c' WHERE link_id='$link_id' ");
			$c++;
		}
	}
}
*/

$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."'>
<table style='width:85%' class='fborder'>";

$sql -> db_Select("link_category");
$sql2 = new db;
while(list($link_category_id, $link_category_name, $link_category_description) = $sql-> db_Fetch()){
        if($lamount = $sql2 -> db_Select("links", "*", "link_category ='$link_category_id' ORDER BY link_order ASC ")){
                $text .= "<tr><td colspan='3' class='forumheader'>$link_category_name</td></tr>";
                while(list($link_id, $link_name, $link_url, $link_description, $link_button, $link_category, $link_order, $link_refer) = $sql2-> db_Fetch()){
					$text .= "<tr>\n<td style='width:30%' class='forumheader3'>".$link_order." - ".$link_name."</td>\n<td style='width:20%' class='forumheader3'>\n<select name='link_order[]' class='tbox'>";
					for($a=1; $a<= $lamount; $a++){
						$text .= ($link_order == $a ? "<option value='$link_id.$a' selected>$a</option>\n" : "<option value='$link_id.$a'>$a</option>\n");
					}



/*
<select name='activate' onChange='urljump(this.options[selectedIndex].value)' class='tbox'>
<option value='links.php' selected></option>
<option value='links.php?inc.".$link_id.".".$link_order.".".$link_category."'>".LCLAN_30."</option>
<option value='links.php?dec.".$link_id.".".$link_order.".".$link_category."'>".LCLAN_31."</option>
</select>
*/
					$text .= "</td>
					<td style='width:50%' class='forumheader3'>&nbsp;".$link_description."</td>
					</tr>";
                }
                
        }
}
$text .= "
<tr>
<td colspan='3' style='text-align:center' class='forumheader'>
<input class='button' type='submit' name='update_order' value='".LCLAN_32."' />
</td>
</tr>
</table>
</form>
</div>";

$ns -> tablerender("<div style='text-align:center'>".LCLAN_33."</div>", $text);

$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."'>\n
<table style='width:85%' class='fborder'>
<tr>
<td style='width:70%' class='forumheader3'>
".LCLAN_40."<br />
<span class='smalltext'>".LCLAN_34."</span>
</td>
<td style='width:30%' class='forumheader2' style='text-align:center'>".
($pref['linkpage_categories'] ? "<input type='checkbox' name='linkpage_categories' value='1' checked>" : "<input type='checkbox' name='linkpage_categories' value='1'>")."
</td>
</tr>

<tr>
<td style='width:70%' class='forumheader3'>
".LCLAN_41."<br />
<span class='smalltext'>".LCLAN_42."</span>
</td>
<td style='width:30%' class='forumheader2' style='text-align:center'>".
($pref['link_submit'] ? "<input type='checkbox' name='link_submit' value='1' checked>" : "<input type='checkbox' name='link_submit' value='1'>")."
</td>
</tr>

<tr>
<td style='width:70%' class='forumheader3'>
".LCLAN_43."<br />
<span class='smalltext'>".LCLAN_44."</span>
</td>
<td style='width:30%' class='forumheader2' style='text-align:center'>".r_userclass("link_submit_class", $pref['link_submit_class'])."</td>
</tr>





<tr style='vertical-align:top'> 
<td colspan='2'  style='text-align:center' class='forumheader'>
<input class='button' type='submit' name='updateoptions' value='".LCLAN_35."' />
</td>
</tr>

</table>
</form>
</div>";
$ns -> tablerender(LCLAN_36, $text);
require_once("footer.php");
?>
<script type="text/javascript">
function addtext(sc){
	document.linkform.link_button.value = sc;
}
</script>