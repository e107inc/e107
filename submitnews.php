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
|     $Source: /cvs_backup/e107_0.8/submitnews.php,v $
|     $Revision: 1.5 $
|     $Date: 2007-11-13 07:54:30 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
require_once("class2.php");
require_once(HEADERF);

if (!isset($pref['subnews_class']))
{
    $pref['subnews_class'] = "0";
}
if (!check_class($pref['subnews_class']))
{
    $ns->tablerender(NWSLAN_12, NWSLAN_11);
    require_once(FOOTERF);
    exit;
}

$author_name = $tp->toDB($_POST['author_name']);
$author_email = $tp->toDB(check_email($_POST['author_email']));

if (isset($_POST['submit']))
{
    $user = (USER ? USERNAME : $author_name);
    $email = (USER ? USEREMAIL : $author_email);

    if ($user && $email)
    {
        $ip = $e107->getip();
        $fp = new floodprotect;
        if ($fp->flood("submitnews", "submitnews_datestamp") == false)
        {
            header("location:" . e_BASE . "index.php");
            exit;
        }
        $itemtitle = $tp->toDB($_POST['itemtitle']);
        $item = $tp->toDB($_POST['item']);
        $item = str_replace("src=&quot;e107_images", "src=&quot;" . SITEURL . "e107_images", $item);
        // Process File Upload    =================================================
        if ($_FILES['file_userfile'] && $pref['subnews_attach'] && $pref['upload_enabled'] && check_class($pref['upload_class']) && FILE_UPLOADS)
        {
            require_once(e_HANDLER . "upload_handler.php");
            $uploaded = file_upload(e_IMAGE . "newspost_images/");
            $file = $uploaded[0]['name'];
            $filetype = $uploaded[0]['type'];
            $filesize = $uploaded[0]['size'];
            $fileext = substr(strrchr($file, "."), 1);

            if (!$pref['upload_maxfilesize'])
            {
                $pref['upload_maxfilesize'] = ini_get('upload_max_filesize') * 1048576;
            }

            if ($uploaded && $fileext != "jpg" && $fileext != "gif" && $fileext != "png")
            {
                $message = SUBNEWSLAN_3;
                $error = true;
            }

            if ($filesize > $pref['upload_maxfilesize'])
            {
                $message = SUBNEWSLAN_4;
                $error = true;
            }

            if (!$error)
            {
                // $numberoffiles = count($uploaded);
                $today = getdate();
                $newname = USERID . "_" . $today[0] . "_" . str_replace(" ", "_", substr($itemtitle, 0, 6)) . "." . $fileext;
                if ($file && $pref['subnews_resize'])
                {
                    require_once(e_HANDLER . "resize_handler.php");
                    $rezwidth = $pref['subnews_resize'];
                    if (!resize_image(e_IMAGE . "newspost_images/" . $file, e_IMAGE . "newspost_images/" . $newname, $rezwidth))
                    {
                        rename(e_IMAGE . "newspost_images/" . $file, e_IMAGE . "newspost_images/" . $newname);
                    }
                } elseif ($file)
                {
                    rename(e_IMAGE . "newspost_images/" . $file, e_IMAGE . "newspost_images/" . $newname);
                }
            }
        }

        if ($error == false)
        {
            if (!file_exists(e_IMAGE . "newspost_images/" . $newname))
            {
                $newname = "";
            }
            $sql->db_Insert("submitnews", "0, '$user', '$email', '$itemtitle', '".intval($_POST['cat_id'])."','$item', '" . time() . "', '$ip', '0', '$newname' ");
            $edata_sn = array("user" => $user, "email" => $email, "itemtitle" => $itemtitle, "catid" => intval($_POST['cat_id']), "item" => $item, "ip" => $ip, "newname" => $newname);
            $e_event->trigger("subnews", $edata_sn);
            $ns->tablerender(LAN_133, "<div style='text-align:center'>" . LAN_134 . "</div>");
            require_once(FOOTERF);
            exit;
        }
        else
        {
            require_once(e_HANDLER . "message_handler.php");
            message_handler("P_ALERT", $message);
        }
    }
}

if (!defined("USER_WIDTH")){ define("USER_WIDTH","width:95%"); }

$text = "<div style='text-align:center'>
	<form id='dataform' method='post' action='" . e_SELF . "' enctype='multipart/form-data' onsubmit='return frmVerify()'>\n
	<table style='".USER_WIDTH."' class='fborder'>";
if (!USER)
{
    $text .= "<tr>\n<td style='width:20%' class='forumheader3'>" . LAN_7 . "</td>\n<td style='width:80%' class='forumheader3'>\n<input class='tbox' type='text' name='author_name' size='60' value='$author_name' maxlength='100' />\n</td>\n</tr>\n<tr>\n<td style='width:20%' class='forumheader3'>" . LAN_112 . "</td>\n<td style='width:80%' class='forumheader3'>\n<input class='tbox' type='text' name='author_email' size='60' value='$author_email' maxlength='100' />\n</td>\n</tr>";
}

if (!empty($pref['news_subheader']))
{
    $text .= " <tr>
	<td colspan='2' class='forumheader3'>" . $tp->toHTML($pref['news_subheader'], TRUE,'TITLE') . "</td>
	</tr>";
}

$text .= " <tr>
	<td style='width:20%' class='forumheader3'>" . NWSLAN_6 . ": </td>
	<td style='width:80%' class='forumheader3'>";
if (!$sql->db_Select("news_category"))
{
    $text .= NWSLAN_10;
}
else
{
    $text .= "
		<select name='cat_id' class='tbox'>";
    while (list($cat_id, $cat_name, $cat_icon) = $sql->db_Fetch())
    {
		$sel = ($_POST['cat_id'] == $cat_id) ? "selected='selected'" : "";
        $text .= "<option value='$cat_id' $sel>" . $tp->toHTML($cat_name,FALSE,"defs") . "</option>";
    }
    $text .= "</select>";
}
$text .= "</td>
	</tr><tr>
	<td style='width:20%' class='forumheader3'>" . LAN_62 . "</td>
	<td style='width:80%' class='forumheader3'>
	<input class='tbox' type='text' id='itemtitle' name='itemtitle' size='60' value='$itemtitle' maxlength='200' style='width:90%' />
	</td>
	</tr>";
if ($pref['subnews_htmlarea'])
{
    require_once(e_HANDLER . "tiny_mce/wysiwyg.php");
    echo wysiwyg("item");
}
else
{
require_once(e_HANDLER."ren_help.php");
}

$insertjs = (!$pref['subnews_htmlarea'])?"rows='15' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'" : "rows='25' ";
$text .= "

	<tr>
	<td style='width:20%' class='forumheader3'>" . LAN_135 . "</td>
	<td style='width:80%' class='forumheader3'>
	<textarea class='tbox' id='item' name='item'  cols='80'  style='max-width:95%' $insertjs></textarea><br />";

if (!$pref['subnews_htmlarea'])
{
  $text .= display_help("helpb","submitnews");
}
$text .= "	</td>
	</tr>\n";
if ($pref['subnews_attach'] && $pref['upload_enabled'] && check_class($pref['upload_class']) && FILE_UPLOADS)
{
    $text .= "
		<tr>
		<td style='width:20%' class='forumheader3'>" . SUBNEWSLAN_5 . "<br /><span class='smalltext'>" . SUBNEWSLAN_6 . "</span></td>
		<td style='width:80%' class='forumheader3'>
		<input class='tbox' type='file' name='file_userfile[]' style='width:90%' />
		</td>
		</tr>\n";
}

$text .= "
	<tr>
	<td colspan='2' style='text-align:center' class='forumheader'>
	<input class='button' type='submit' name='submit' value='" . LAN_136 . "' />
	</td>
	</tr>
	</table>
	</form>
	</div>";
$ns->tablerender(LAN_136, $text);
require_once(FOOTERF);
function headerjs()
{
    $script = "<script type=\"text/javascript\">
		function frmVerify()
		{
			if(document.getElementById('itemtitle').value == \"\")
			{
				alert('" . SUBNEWSLAN_1 . "');
				return false;
			}
			if(document.getElementById('item').value == \"\")
			{
				alert('" . SUBNEWSLAN_2 . "');
				return false;
			}
		}
		</script>";
    return $script;
}

?>