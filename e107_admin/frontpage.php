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
|     $Source: /cvs_backup/e107_0.7/e107_admin/frontpage.php,v $
|     $Revision: 1.1 $
|     $Date: 2004-09-21 19:10:20 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
if(!getperms("G")){ header("location:".e_BASE."index.php"); exit; }
$aj = new textparse;
if(IsSet($_POST['updatesettings'])){
        if($_POST['frontpage'] == "other"){
                $_POST['frontpage'] = ($_POST['frontpage_url'] ? $_POST['frontpage_url'] : "news");
        }
        $_POST['frontpage_url'] = $aj -> formtpa($_POST['frontpage_url'], "admin");
        $pref['frontpage'] = $_POST['frontpage'];
        $pref['frontpage_type'] = $_POST['frontpage_type'];
        save_prefs();

        if($pref['frontpage'] != "news"){
                if(!$sql -> db_Select("links", "*", "link_url='news.php' ")){
                        $sql -> db_Insert("links", "0, 'News', 'news.php', '', '', 1, 0, 0, 0, 0");
                }
        }else{
                $sql -> db_Delete("links", "link_url='news.php'");
        }
}

require_once("auth.php");

$frontpage_re = ($pref['frontpage'] ? $pref['frontpage'] : "news");
$frontpage_type = ($pref['frontpage_type'] ? $pref['frontpage_type'] : "constant");


if(e_QUERY == "u"){
        $ns -> tablerender("", "<div style='text-align:center'><b>".FRTLAN_1."</b></div>");
}

$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."'>
<table style='width:95%' class='fborder'>
<tr>

<td style='width:30%' class='forumheader3'>".FRTLAN_2.": </td>
<td style='width:70%' class='forumheader3'>


<input name='frontpage' type='radio' value='news'";
if($frontpage_re == "news"){
        $text .= "checked='checked'";
        $flag = TRUE;
}
$text .= " />".FRTLAN_3."<br />
<input name='frontpage' type='radio' value='forum'";
if($frontpage_re == "forum"){
        $text .= "checked='checked'";
        $flag = TRUE;
}
$text .= " />".FRTLAN_4."<br />
<input name='frontpage' type='radio' value='download'";
if($frontpage_re == "download"){
        $text .= "checked='checked'";
        $flag = TRUE;
}
$text .= " />".FRTLAN_5."<br />
<input name='frontpage' type='radio' value='links'";
if($frontpage_re == "links"){
        $text .= "checked='checked'";
        $flag = TRUE;
}
$text .= " />".FRTLAN_6."<br />";

if($sql -> db_Select("content", "*", "content_type='1'")){
        while($row = $sql -> db_Fetch()){
                extract($row);
                $text .= "<input name='frontpage' type='radio' value='".$content_id."'";
                if($frontpage_re == $content_id){
                        $text .= "checked='checked'";
                        $flag = TRUE;
                }
                $text .= " />".FRTLAN_7.": ".$content_heading."/".$content_subheading."<br />";
        }
}

$text .= "
<input name='frontpage' type='radio' value='other'";
if($flag != TRUE){
        $text .= "checked='checked'";
}

$text .= " />".FRTLAN_8."
<input class='tbox' type='text' name='frontpage_url' size='50' value='";
if($flag != TRUE){
        $text .= $pref['frontpage'];
}

$text .= "' maxlength='100' /> ".FRTLAN_14."
</td>
</tr>

<tr>
<td style='width:30%' class='forumheader3'>".FRTLAN_9.": </td>
<td style='width:70%' class='forumheader3'>

<input name='frontpage_type' type='radio' value='constant'";
if($frontpage_type == "constant"){
        $text .= "checked='checked'";
}
$text .= " />".FRTLAN_10."<br />
<input name='frontpage_type' type='radio' value='splash'";
if($frontpage_type == "splash"){
        $text .= "checked='checked'";
}
$text .= " />".FRTLAN_11."<br />


</td>
</tr>

<tr style='vertical-align:top'>
<td colspan='2'  style='text-align:center'  class='forumheader'>
<input class='button' type='submit' name='updatesettings' value='".FRTLAN_12."' />
</td>
</tr>
</table>
</form>
</div>";

$ns -> tablerender("<div style='text-align:center'>".FRTLAN_13."</div>", $text);
require_once("footer.php");

?>