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
if(!getperms("I")){ header("location:".e_HTTP."index.php"); }
require_once("auth.php");

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



if($_POST['add_link'] != ""){
        $sql -> db_Select("link_category", "*", "link_category_name='".$_POST['cat_name']."' ");
        $row = $sql -> db_Fetch();
        $link_cat_id = $row['link_category_id'];
        $sql -> db_Insert("links", "0, '".$_POST['link_name']."', '".$_POST['link_url']."', '".$_POST['link_description']."', '".$_POST['link_button']."', '$link_cat_id', '0', '0', '".$_POST['linkopentype']."' ");
        $message = "Link added to database.";
        unset ($link_id, $link_name, $link_url, $link_description, $link_button, $link_main);
}

if(IsSet($_POST['update_link'])){
        $sql -> db_Select("link_category", "*", "link_category_name='".$_POST['cat_name']."' ");
        $row = $sql -> db_Fetch();
        $link_cat_id = $row['link_category_id'];
        $sql -> db_Update("links", "link_name='".$_POST['link_name']."', link_url='".$_POST['link_url']."', link_description='".$_POST['link_description']."', link_button= '".$_POST['link_button']."', link_category='$link_cat_id', link_open='".$_POST['linkopentype']."' WHERE link_id='".$_POST['link_id']."' ");
        $message = "Link updated in database.";
        unset ($link_id, $link_name, $link_url, $link_description, $link_button, $link_main);
}

if(IsSet($_POST['edit']) || $action == "edit"){
        if($action == "edit"){
                $sql -> db_Select("links", "*", "link_id='".$linkid."' ");
        }else{
                $sql -> db_Select("links", "*", "link_id='".$_POST['existing']."' ");
        }
        list($link_id, $link_name, $link_url, $link_description, $link_button, $link_category, $link_refer) = $sql-> db_Fetch();
}

if(IsSet($_POST['confirm'])){
        $sql -> db_Delete("links", "link_id='".$_POST['existing']."' ");
        $message = "Link deleted.";
}

if(IsSet($_POST['delete']) || $action == "delete"){
        if($action == "delete"){
                $sql -> db_Select("links", "*", "link_id='".$linkid."' ");
        }else{
                $sql -> db_Select("links", "*", "link_id='".$_POST['existing']."' ");
        }
        list($link_id, $link_name) = $sql-> db_Fetch();

        $text = "<div style='text-align:center'>
        <b>Please confirm you wish to delete the '$link_name' link - once deleted it cannot be retrieved</b>
<br /><br />
<form method='post' action='".e_SELF."'>
<input class='button' type='submit' name='cancel' value='Cancel' />
<input class='button' type='submit' name='confirm' value='Confirm Delete' />";
if($action == "delete"){
$text .= "<input type='hidden' name='existing' value='".$linkid."'>";
 }else{
$text .= "<input type='hidden' name='existing' value='".$_POST['existing']."'>";

}
$text .= "</form>
</div>";
$ns -> tablerender("Confirm Delete Link", $text);

        require_once("footer.php");
        exit;
}
if(IsSet($_POST['cancel'])){
        $message = "Delete cancelled.";
}

if(IsSet($_POST['update_order'])){
        extract($_POST);
        $sql -> db_Select("links");
        $sql2 = new db;
        while(list($link_id) = $sql-> db_Fetch()){
                $sql2 -> db_Update("links", "link_order='".$link_order[$link_id]."' WHERE link_id='$link_id' ");
        }

        $message = "Order updated.";

}

if(IsSet($message)){
        $ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

$link_total = $sql -> db_Select("links");

$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."'>
<table style='width:85%' class='fborder'>
<tr>
<td colspan='2' class='forumheader' style='text-align:center'>";

if(!$link_total){
	$text .= "<span class='defaulttext'>No links set yet.</span>";
}else{
$text .= "<span class='defaulttext'>Existing Links:</span>
	<select name='existing' class='tbox'>";
	while(list($link_id_, $link_name_) = $sql-> db_Fetch()){
		$text .= "<option value='$link_id_'>".$link_name_."</option>";
	}
	$text .= "</select>
	<input class='button' type='submit' name='edit' value='Edit' />
	<input class='button' type='submit' name='delete' value='Delete' />";
}

$text .= "
</td>
</tr>
<tr>
<td style='width:30%' class='forumheader3'>Link Category: </td>
<td style='width:70%' class='forumheader3'>";

if(!$link_cats = $sql -> db_Select("link_category")){
	$text .= "<div class='twelvept'>No categories set yet.</div><br />";
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
$text .= "<span class='twelvept'> [ <a href='link_category.php'>Add/Edit Categories</a> ]</span>

<tr>
<td style='width:30%' class='forumheader3'>Link Name: </td>
<td style='width:70%' class='forumheader3'>
<input class='tbox' type='text' name='link_name' size='60' value='$link_name' maxlength='100' />
</td>
</tr>

<tr>
<td style='width:30%' class='forumheader3'>Link URL: </td>
<td style='width:70%' class='forumheader3'>
<input class='tbox' type='text' name='link_url' size='60' value='$link_url' maxlength='200' />
</td>
</tr>

<tr>
<td style='width:30%' class='forumheader3'>Link Description: </td>
<td style='width:70%' class='forumheader3'>
<textarea class='tbox' name='link_description' cols='59' rows='3'>$link_description</textarea>
</td>
</tr>

<tr>
<td style='width:30%' class='forumheader3'>Link Button: </td>
<td style='width:70%' class='forumheader3'>
<input class='tbox' type='text' name='link_button' size='60' value='$link_button' maxlength='100' />

</td>
</tr>
<tr>
<td style='width:30%' class='forumheader3'>Link Open Type: </td>
<td style='width:70%' class='forumheader3'>
<select name='linkopentype' class='tbox'>
<option value='0' selected>opens in same window</option>
<option value='1'>_target=blank</option>
<option value='2'>_target=parent</option>
<option value='3'>_target=top</option>
<option value='4'>open in 600x400 miniwindow</option>
</select>

</td>
</tr>
<tr style='vertical-align:top'>
<td colspan='2' style='text-align:center' class='forumheader'>";
if(IsSet($_POST['edit']) || $action == "edit"){
        $text .= "<input class='button' type='submit' name='update_link' value='Update Link' />
<input type='hidden' name='link_id' value='$link_id'>";
}else{
        $text .= "<input class='button' type='submit' name='add_link' value='Add link' />";
}
$text .= "</td>
</tr>
</table>
</form>
</div>";
$ns -> tablerender("<div style='text-align:center'>Links</div>", $text);





// -------------------------------------------------------------------------------------------------------------------------------------------------------

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


$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."'>
<table style='width:85%' class='fborder'>";

$sql -> db_Select("link_category");
$sql2 = new db;
while(list($link_category_id, $link_category_name, $link_category_description) = $sql-> db_Fetch()){
        if($sql2 -> db_Select("links", "*", "link_category ='$link_category_id' ORDER BY link_order ASC ")){
                $text .= "<tr><td colspan='3' class='forumheader'>$link_category_name</td></tr>";
                while(list($link_id, $link_name, $link_url, $link_description, $link_button, $link_category, $link_order, $link_refer) = $sql2-> db_Fetch()){

                        $text .= "<tr>
<td style='width:30%' class='forumheader3'>".$link_order." - ".$link_name."</td>
<td style='width:20%' class='forumheader3'>
<select name='activate' onChange='urljump(this.options[selectedIndex].value)' class='tbox'>
<option value='links.php' selected></option>
<option value='links.php?inc.".$link_id.".".$link_order.".".$link_category."'>move up</option>
<option value='links.php?dec.".$link_id.".".$link_order.".".$link_category."'>move down</option>
</select>
</td>
<td style='width:50%' class='forumheader3'>&nbsp;".$link_description."</td>
</tr>";
                }
                
        }
}
$text .= "
<tr>
<td colspan='3' style='text-align:center' class='forumheader'>
<input class='button' type='submit' name='update_order' value='Update Order' />
</td>
</tr>
</table>
</form>
</div>";

$ns -> tablerender("<div style='text-align:center'>Link Order</div>", $text);

require_once("footer.php");
?>