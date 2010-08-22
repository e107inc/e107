<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2001-2002 Steve Dunstan (jalist@e107.org)
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $URL$
|     $Revision$
|     $Id$
|     $Author$
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
if (!getperms("A")) {
	header("location:".e_BASE."index.php");
	exit;
}
$e_sub_cat = 'image';
require_once("auth.php");
require_once(e_HANDLER."form_handler.php");
require_once(e_HANDLER."userclass_class.php");
$rs = new form;

if (isset($_POST['delete'])) {
	$image = $_POST['filename'];
	@unlink(e_FILE."public/avatars/".$image);
	$sql->db_Update("user", "user_image='' WHERE user_image='-upload-$image'");
	$sql->db_Update("user", "user_sess='' WHERE user_sess='$image'");
	$message = $image." ".IMALAN_28;
}

if (isset($_POST['deleteall'])) {
	$handle = opendir(e_FILE."public/avatars/");
	while ($file = readdir($handle)) {
		if ($file != '.' && $file != '..' && $file != "index.html" && $file != "null.txt" && $file != '/' && $file != 'CVS' && $file != 'Thumbs.db') {
			$dirlist[] = $file;
		}
	}
	closedir($handle);
	$count = 0;
	while (list($key, $image_name) = each($dirlist)) {
		if (!$sql->db_Select("user", "*", "user_image='-upload-$image_name' OR user_sess='$image_name'")) {
			unlink(e_FILE."public/avatars/".$image_name);
			$count ++;
		}
	}
	$message = $count." ".IMALAN_26;
}

if (isset($_POST['avdelete'])) {
	require_once(e_HANDLER."avatar_handler.php");
	foreach($_POST['avdelete'] as $key => $val)
	{
		$key = $tp->toDB($key); // We only need the key
		if ($sql->db_Select("user", "*", "user_id='$key'")) {
			$row = $sql->db_Fetch();
			extract($row);
			$avname=avatar($user_image);
			if (strpos($avname,"http://")===FALSE)
			{ // Internal file, so unlink it
				@unlink($avname);
			}
			$sql->db_Update("user","user_image='' WHERE user_id='$key'");
			$message = IMALAN_51.$user_name." ".IMALAN_28;
		}
	}
	$_POST['check_avatar_sizes'] = TRUE;	// Force size recheck after doing one or more deletes
}

if (isset($_POST['update_options'])) {
	$pref['image_post'] = intval($_POST['image_post']);
	$pref['resize_method'] = $_POST['resize_method'];
	$pref['im_path'] = trim($tp->toDB($_POST['im_path']));
	$pref['image_post_class'] = intval($_POST['image_post_class']);
	$pref['image_post_disabled_method'] = intval($_POST['image_post_disabled_method']);
	$pref['enable_png_image_fix'] = intval($_POST['enable_png_image_fix']);

	save_prefs();
	$message = IMALAN_9;
}

if (isset($message)) {
	$ns->tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}


if (isset($_POST['show_avatars'])) 
{
	$handle = opendir(e_FILE."public/avatars/");
	while ($file = readdir($handle)) {
		if ($file != '.' && $file != '..' && $file != "index.html" && $file != "null.txt" && $file != '/' && $file != 'CVS' && $file != 'Thumbs.db' && !is_dir($file)) {
			$dirlist[] = $file;
		}
	}
	closedir($handle);

	$text = "<div style='text-align:center'>\n";

	if (!is_array($dirlist)) {
		$text .= IMALAN_29;
	} else {



		while (list($key, $image_name) = each($dirlist)) {
			$users = IMALAN_21." | ";
			if ($sql->db_Select("user", "*", "user_image='-upload-$image_name' OR user_sess='$image_name'")) {
				while ($row = $sql->db_Fetch()) {
					extract($row);
					$users .= "<a href='".e_BASE."user.php?id.$user_id'>$user_name</a> <span class='smalltext'>(".($user_sess == $image_name ? IMALAN_24 : IMALAN_23).")</span> | ";
				}
			} else {
				$users = IMALAN_22;
			}

			$text .= "<div class='spacer'>
				<form method='post' action='".e_SELF."'>
				<table style='".ADMIN_WIDTH."' class='fborder'>
				<tr>
				<td class='fcaption'>$image_name</td>
				</tr>
				<tr>
				<td class='forumheader3'><img src='".e_FILE."public/avatars/".$image_name."' alt='' /><br />
				<input type='hidden' name='filename' value='".$image_name."' />
				<input class='button' type='submit' name='delete' value='".LAN_DELETE."' />
				</td>
				</tr>
				<tr>
				<td class='forumheader3'>$users</td>
				</tr>
				</table>
				</form>
				</div>";
		}

		$text .= "<div class='spacer'>
			<form method='post' action='".e_SELF."'>
			<table style='".ADMIN_WIDTH."' class='fborder'>
			<tr>
			<td class='forumheader'>
			<input class='button' type='submit' name='deleteall' value='".IMALAN_25."' />
			</td>
			</tr>
			</table>
			</form>
			</div>";

	}

	$text .= "</div>";

	$ns->tablerender(IMALAN_18, $text);
}

if (isset($_POST['check_avatar_sizes'])) {
	//
	// Set up to track what we've done
	//
	$iUserCount  = 0;
	$iAVinternal = 0;
	$iAVexternal = 0;
	$iAVnotfound = 0;
	$iAVtoobig   = 0;
	require_once(e_HANDLER."avatar_handler.php");
	$text = "<div style='text-align:center'>\n";
	$text .= "<div class='spacer'>
		<form method='post' action='".e_SELF."'>
		<table style='".ADMIN_WIDTH."' class='fborder'>
		<tr>
		<td class='forumheader3'>".$pref['im_width']."</td>
		<td class='forumheader3'>".IMALAN_38."</td>
		</tr>
		<tr>
		<td class='forumheader3'>".$pref['im_height']."</td>
		<td class='forumheader3'>".IMALAN_39."</td>
		</tr>";

	//
	// Loop through avatar field for every user
	//
	$iUserCount = $sql->db_Count("user");
	if ($sql->db_Select("user", "*", "user_image!=''")) {
		while ($row = $sql->db_Fetch()) {
			extract($row);

	//
	// Check size
	//
			$avname=avatar($user_image);
			if (strpos($avname,"http://")!==FALSE)
			{
				$iAVexternal++;
				$bAVext=TRUE;
			} else {
				$iAVinternal++;
				$bAVext=FALSE;
			}
			$image_stats = getimagesize($avname);
			$sBadImage="";
			if (!$image_stats)
			{
				$iAVnotfound++;
				// allow delete
				$sBadImage=IMALAN_42;
			} else {
				$imageWidth = $image_stats[0];
				$imageHeight = $image_stats[1];
				if ( ($imageHeight > $pref['im_height']) || ($imageWidth>$pref['im_width']) )
				{ // Too tall or too wide
					$iAVtoobig++;
					if ($imageWidth > $pref['im_width']) {
						$sBadImage = IMALAN_40." ($imageWidth)";
					}
					if ($imageHeight > $pref['im_height']) {
						if (strlen($sBadImage))
						{
							$sBadImage .= ", ";
						}
						$sBadImage .= IMALAN_41." ($imageHeight)";
					}
				}
			}

	//
	// If not found or too big, allow delete
	//
			if (strlen($sBadImage))
			{
				$sBadImage .=" [".$avname."]"; // Show all files that have a problem
				$text .= "
				<tr>
				<td class='forumheader3'>
				<input class='button' type='submit' name='avdelete[$user_id]' value='".($bAVext ? IMALAN_44 : IMALAN_43)."' />
				</td>
				<td class='forumheader3'>".IMALAN_51."<a href='".e_BASE."user.php?id.".$user_id."'>".$user_name."</a> ".$sBadImage."</td>
				</tr>";
			}
		}
	}
	//
	// Done, so show stats
	//
	$text .= "
		<tr>
		<td class='forumheader3'>".$iAVnotfound."</td>
		<td class='forumheader3'>".IMALAN_45."</td>
		</tr>
		<tr>
		<td class='forumheader3'>".$iAVtoobig."</td>
		<td class='forumheader3'>".IMALAN_46."</td>
		</tr>
		<tr>
		<td class='forumheader3'>".$iAVinternal."</td>
		<td class='forumheader3'>".IMALAN_47."</td>
		</tr>
		<tr>
		<td class='forumheader3'>".$iAVexternal."</td>
		<td class='forumheader3'>".IMALAN_48."</td>
		</tr>
		<tr>
		<td class='forumheader3'>".($iAVexternal+$iAVinternal)." (".(int)(100.0*(($iAVexternal+$iAVinternal)/$iUserCount)).'%, '.$iUserCount." ".IMALAN_50.")</td>
		<td class='forumheader3'>".IMALAN_49."</td>
		</tr>
		</table>
		</form>
		</div>";

	$text .= "</div>";

	$ns->tablerender(IMALAN_37, $text);
}

if(function_exists('gd_info'))
{
	$gd_info = gd_info();
	$gd_version = $gd_info['GD Version'];
}
else
{
	$gd_version = "<span style='color:red'> ".IMALAN_55."</span>";
}

$IM_NOTE = "";
if($pref['im_path'] != "")
{
  $im_file = $pref['im_path'].'convert';
	if(!file_exists($im_file))
	{
		$IM_NOTE = "<br /><span style='color:red'>".IMALAN_52."</span>";
	}
	else
	{
		$cmd = "{$im_file} -version";
		$tmp = `$cmd`;
		if(strpos($tmp, "ImageMagick") === FALSE)
		{
			$IM_NOTE = "<br /><span style='color:red'>".IMALAN_53."</span>";
		}
	}
}

$text = "<div style='text-align:center'>
	<form method='post' action='".e_SELF."'>
	<table style='".ADMIN_WIDTH."' class='fborder'>

	<tr>
	<td style='width:75%' class='forumheader3'>
	".IMALAN_1."<br />
	<span class='smalltext'>".IMALAN_2."</span>
	</td>
	<td style='width:25%;text-align:center' class='forumheader3' >". ($pref['image_post'] ? "<input type='checkbox' name='image_post' value='1' checked='checked' />" : "<input type='checkbox' name='image_post' value='1' />")."
	</td>
	</tr>


	<tr>
	<td style='width:75%' class='forumheader3'>
	".IMALAN_10."<br />
	<span class='smalltext'>".IMALAN_11."</span>
	</td>
	<td style='width:25%;text-align:center' class='forumheader3' >".r_userclass('image_post_class',$pref['image_post_class'],"off","public,guest,nobody,member,admin,main,classes")."</td>
	</tr>

	<tr>
	<td style='width:75%' class='forumheader3'>
	".IMALAN_12."<br />
	<span class='smalltext'>".IMALAN_13."</span>
	</td>
	<td style='width:25%;text-align:center' class='forumheader3' >
	<select name='image_post_disabled_method' class='tbox'>". 
	($pref['image_post_disabled_method'] == "0" ? "<option value='0' selected='selected'>".IMALAN_14."</option>" : "<option value='0'>".IMALAN_14."</option>"). 
	($pref['image_post_disabled_method'] == "1" ? "<option value='1' selected='selected'>".IMALAN_19."</option>" : "<option value='1'>".IMALAN_19."</option>").
	($pref['image_post_disabled_method'] == "2" ? "<option value='2' selected='selected'>".IMALAN_15."</option>" : "<option value='2'>".IMALAN_15."</option>")."
	</select></td>
	</tr>

	<tr>
	<td style='width:75%' class='forumheader3'>".IMALAN_3."<br /><span class='smalltext'>".IMALAN_4."</span><br />".IMALAN_54." {$gd_version}</td>
	<td style='width:25%;text-align:center' class='forumheader3' >
	<select name='resize_method' class='tbox'>". ($pref['resize_method'] == "gd1" ? "<option selected='selected'>gd1</option>" : "<option>gd1</option>"). ($pref['resize_method'] == "gd2" ? "<option selected='selected'>gd2</option>" : "<option>gd2</option>"). ($pref['resize_method'] == "ImageMagick" ? "<option selected='selected'>ImageMagick</option>" : "<option>ImageMagick</option>")."
	</select>
	</td>
	</tr>

	<tr>
	<td style='width:75%' class='forumheader3'>".IMALAN_5."<br /><span class='smalltext'>".IMALAN_6."</span></td>
	<td style='width:25%;text-align:center' class='forumheader3' >
	<input class='tbox' type='text' name='im_path' size='40' value=\"".$pref['im_path']."\" maxlength='200' />
	{$IM_NOTE}
	</td></tr>

	<tr>
	<td style='width:75%' class='forumheader3'>".IMALAN_34."<br />
	<span class='smalltext'>".IMALAN_35."</span>
	</td>
	<td style='width:25%;text-align:center' class='forumheader3' >".($pref['enable_png_image_fix'] ? "<input type='checkbox' name='enable_png_image_fix' value='1' checked='checked' />" : "<input type='checkbox' name='enable_png_image_fix' value='1' />")."
	</td>
	</tr>

	<tr>
	<td style='width:75%' class='forumheader3'>".IMALAN_16."</td>
	<td style='width:25%;text-align:center' class='forumheader3'  >
	<input class='button' type='submit' name='show_avatars' value='".IMALAN_17."' />
	</td></tr>

	<tr>
	<td style='width:75%' class='forumheader3'>".IMALAN_36."</td>
	<td style='width:25%;text-align:center' class='forumheader3'  >
	<input class='button' type='submit' name='check_avatar_sizes' value='".IMALAN_17."' />
	</td></tr>

	<tr>
	<td colspan='2' style='text-align:center' class='forumheader'>
	<input class='button' type='submit' name='update_options' value='".IMALAN_8."' />
	</td>
	</tr>




	</table></form></div>";
$ns->tablerender(IMALAN_7, $text);


require_once("footer.php");



$pref['resize_method'] = $_POST['resize_method'];
$pref['im_path'] = $_POST['im_path'];


?>
