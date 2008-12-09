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
|     $Source: /cvs_backup/e107_0.8/e107_admin/image.php,v $
|     $Revision: 1.9 $
|     $Date: 2008-12-09 17:49:59 $
|     $Author: secretr $
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

if (isset($_POST['delete']))
{
	$image = $tp->toDB($_POST['filename']);
	@unlink(e_FILE."public/avatars/".$image);
	$sql->db_Update("user", "user_image='' WHERE user_image='-upload-{$image}'");
	$sql->db_Update("user", "user_sess='' WHERE user_sess='{$image}'");
	$admin_log->log_event('IMALAN_01',$image,E_LOG_INFORMATIVE,'');
	$message = $image." ".IMALAN_28;
}


if (isset($_POST['deleteall']))
{
	$handle = opendir(e_FILE."public/avatars/");
	while ($file = readdir($handle)) {
		if ($file != '.' && $file != '..' && $file != "index.html" && $file != "null.txt" && $file != '/' && $file != 'CVS' && $file != 'Thumbs.db') {
			$dirlist[] = $file;
		}
	}
	closedir($handle);
	$imgList = '';
	$count = 0;
	while (list($key, $image_name) = each($dirlist))
	{
		if (!$sql->db_Select("user", "*", "user_image='-upload-$image_name' OR user_sess='$image_name'")) {
			unlink(e_FILE."public/avatars/".$image_name);
			$count++;
			$imgList .= '[!br!]'.$image_name;
		}
	}
	$message = $count." ".IMALAN_26;
	$admin_log->log_event('IMALAN_02',$message.$imgList,E_LOG_INFORMATIVE,'');
	unset($imgList);
}


if (isset($_POST['avdelete']))
{
	require_once(e_HANDLER."avatar_handler.php");
	$avList = array();
	foreach($_POST['avdelete'] as $key => $val)
	{
		$key = intval($key); // We only need the key
		if ($sql->db_Select("user", 'user_id, user_name, user_image', "user_id='{$key}'"))
		{
			$row = $sql->db_Fetch();
			$avname=avatar($row['user_image']);
			if (strpos($avname,"http://")===FALSE)
			{ // Internal file, so unlink it
				@unlink($avname);
			}
			$sql->db_Update("user","user_image='' WHERE user_id='{$key}'");
			$message = IMALAN_51.$row['user_name']." ".IMALAN_28;
			$avList[] = $key.':'.$row['user_name'].':'.$row['user_image'];
		}
	}
	$admin_log->log_event('IMALAN_03',implode('[!br!]',$avList),E_LOG_INFORMATIVE,'');
	unset($avList);
	$_POST['check_avatar_sizes'] = TRUE;	// Force size recheck after doing one or more deletes
}

if (isset($_POST['update_options']))
{
	unset($temp);
	$temp['image_post'] = intval($_POST['image_post']);
	$temp['resize_method'] = $_POST['resize_method'];
	$temp['im_path'] = trim($tp->toDB($_POST['im_path']));
	$temp['image_post_class'] = intval($_POST['image_post_class']);
	$temp['image_post_disabled_method'] = intval($_POST['image_post_disabled_method']);
	$temp['enable_png_image_fix'] = intval($_POST['enable_png_image_fix']);

	if ($admin_log->logArrayDiffs($temp, $pref, 'IMALAN_04'))
	{
		save_prefs();		// Only save if changes
		$message = IMALAN_9;
	}
	else
	{
		$message = IMALAN_20;
	}
}

//FIXME - better message handler, no tablerender for sys-messages anymore
if (isset($message))
{
	$ns->tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}


if (isset($_POST['show_avatars']))
{
	$handle = opendir(e_FILE."public/avatars/");
	while ($file = readdir($handle))
	{
		if ($file != '.' && $file != '..' && $file != "index.html" && $file != "null.txt" && $file != '/' && $file != 'CVS' && $file != 'Thumbs.db' && !is_dir($file))
		{
			$dirlist[] = $file;
		}
	}
	closedir($handle);

	$text = '';

	if (!is_array($dirlist))
	{
		$text .= IMALAN_29;
	}
	else
	{
		$text = "
			<form method='post' action='".e_SELF."' id='form-show-avatars'>
		";

		$count = 0;
		while (list($key, $image_name) = each($dirlist))
		{
			$users = IMALAN_21." | ";
			if ($sql->db_Select("user", "*", "user_image='-upload-$image_name' OR user_sess='$image_name'"))
			{
				/*
				//Is it possible?! I don't think so
				while ($row = $sql->db_Fetch())
				{
					extract($row); //FIXME - kill this!!!
					$users .= "<a href='".e_BASE."user.php?id.$user_id'>$user_name</a> <span class='smalltext'>(".($user_sess == $image_name ? IMALAN_24 : IMALAN_23).")</span> | ";
				}*/
				$row = $sql->db_Fetch();
				$users .= "<a href='".e_BASE."user.php?id.{$row['user_id']}'>{$row['user_name']}</a> <span class='smalltext'>(".($row['user_sess'] == $image_name ? IMALAN_24 : IMALAN_23).")</span>";
			} else {
				$users = '<span class="warning">'.IMALAN_22.'</span>';
			}

			//File info
			$users = "<a class='e-tooltip' href='#' title='".IMALAN_66.": {$image_name}'><img src='".e_IMAGE_ABS."admin_images/docs_16.png' alt='".IMALAN_66.": {$image_name}' /></a> ".$users;

			// Control over the image size (design)
			$image_size = getimagesize(e_FILE."public/avatars/".$image_name);

			//Friendly UI - click text to select a form element
			$img_src = "<label for='image-action-{$count}' title='".IMALAN_56."'><img src='".e_FILE_ABS."public/avatars/{$image_name}' alt='{$image_name}' /></label>";
			if ($image_size[0] > $pref['im_width'] || $image_size[1] > $pref['im_height'])
			{
				$img_src = "<a class='image-preview' href='".e_FILE_ABS."public/avatars/".rawurlencode($image_name)."'>".IMALAN_57."</a>";
			}

			$text .= "
			<div class='image-box f-left center' style='width: ".(intval($pref['im_width'])+40)."px; height: ".(intval($pref['im_height'])+100)."px;'>
				<div class='spacer'>
				<div class='image-users'>{$users}</div>
				<div class='image-preview'>{$img_src}</div>
				<div class='image-delete options'>
					<input type='checkbox' class='checkbox' id='image-action-{$count}' name='multiaction[]' value='{$image_name}' />
				</div>

				</div>
			</div>

			";

			$count++;
		}

		//FIXME add multi delete for better user experience (not working yet), make check/uncheck-all work
		$text .= "
			<div class='spacer clear'>
			<div class='buttons-bar'>
				<button class='delete' type='submit' name='deleteall'><span>".IMALAN_25."</span></button>
				<button class='delete' type='submit' name='delete_multi'><span>".IMALAN_58."</span></button>
				<button class='action' type='button' name='check_all'><span>".IMALAN_59."</span></button>
				<button class='action' type='button' name='uncheck_all'><span>".IMALAN_60."</span></button>
			</div>
			</div>
			</form>
		";

	}


	$ns->tablerender(IMALAN_18, $text);
}

if (isset($_POST['check_avatar_sizes']))
{
	// Set up to track what we've done
	//
	$iUserCount  = 0;
	$iAVinternal = 0;
	$iAVexternal = 0;
	$iAVnotfound = 0;
	$iAVtoobig   = 0;
	require_once(e_HANDLER."avatar_handler.php");

	$text = "
	<form method='post' action='".e_SELF."'>
		<fieldset id='bad-avatar-table'>
			<legend class='e-hideme'>".CACLAN_3."</legend>
			<table cellpadding='0' cellspacing='0' class='adminlist'>
				<colgroup span='3'>
					<col style='width:10%'></col>
					<col style='width:20%'></col>
					<col style='width:25%'></col>
					<col style='width:45%'></col>
				</colgroup>
				<thead>
					<tr>
						<th class='center'>".IMALAN_61."</th>
						<th class='center'>".IMALAN_64."</th>
						<th class='center'>".IMALAN_62."</th>
						<th class='center last'>".IMALAN_63."</th>
					</tr>
				</thead>
				<tbody>
	";


	//
	// Loop through avatar field for every user
	//
	$iUserCount = $sql->db_Count("user");
	if ($sql->db_Select("user", "*", "user_image!=''")) {
		while ($row = $sql->db_Fetch())
		{
			extract($row); //FIXME - kill this!!!

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
				if ( ($imageHeight > $pref['im_height']) || ($imageWidth > $pref['im_width']) )
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
				//$sBadImage .=" [".$avname."]"; // Show all files that have a problem
				//FIXME <button class='delete' type='submit' name='avdelete[$user_id]'><span>".($bAVext ? IMALAN_44 : IMALAN_43)."</span></button>
				$text .= "
				<tr>
					<td class='options center'>
						<input class='checkbox' type='checkbox' name='multiaction[]' id='avdelete-{$user_id}' value='{$user_id}' />
					</td>
					<td>
						<label for='avdelete-{$user_id}' title='".IMALAN_56."'>".IMALAN_51."</label><a href='".e_BASE."user.php?id.".$user_id."'>".$user_name."</a>
					</td>
					<td>".$sBadImage."</td>
					<td>".$avname."</td>
				</tr>";
			}
			else
			{
				//Nothing found
				$text .="
				<tr>
					<td colspan='4' class='center'>".IMALAN_65."</td>
				</tr>";

			}
		}
	}
	//
	// Done, so show stats
	//
	$text .= "
				</tbody>
			</table>
			<div class='buttons-bar'>
				<button class='action' type='button' name='check_all'><span>".IMALAN_59."</span></button>
				<button class='action' type='button' name='uncheck_all'><span>".IMALAN_60."</span></button>
				<button class='delete' type='submit' name='avdelete_multi'><span>".IMALAN_58."</span></button>
			</div>
		</fieldset>
	</form>

	<table cellpadding='0' cellspacing='0' class='admininfo'>
	<colgroup span='2'>
		<col style='width:20%'></col>
		<col style='width:80%'></col>
	</colgroup>
	<tbody>
	<tr>
	<td class='label'>".IMALAN_38."</td>
	<td class='control'>".$pref['im_width']."</td>
	</tr>
	<tr>
	<td class='label'>".IMALAN_39."</td>
	<td class='control'>".$pref['im_height']."</td>
	</tr>

	<tr>
	<td class='label'>".IMALAN_45."</td>
	<td class='control'>".$iAVnotfound."</td>
	</tr>
	<tr>
	<td class='label'>".IMALAN_46."</td>
	<td>".$iAVtoobig."</td>
	</tr>
	<tr class='control'>
	<td class='label'>".IMALAN_47."</td>
	<td>".$iAVinternal."</td>
	</tr>
	<tr>
	<td class='label'>".IMALAN_48."</td>
	<td class='control'>".$iAVexternal."</td>
	</tr>
	<tr>
	<td class='label'>".IMALAN_49."</td>
	<td class='control'>".($iAVexternal+$iAVinternal)." (".(int)(100.0*(($iAVexternal+$iAVinternal)/$iUserCount)).'%, '.$iUserCount." ".IMALAN_50.")</td>
	</tr>
	</tbody>
	</table>
	";

	$ns->tablerender(IMALAN_37, $text);
}

if(function_exists('gd_info'))
{
	$gd_info = gd_info();
	$gd_version = $gd_info['GD Version'];
}
else
{
	$gd_version = "<span class='error'> ".IMALAN_55."</span>";
}

$IM_NOTE = "";
if($pref['im_path'] != "")
{
  $im_file = $pref['im_path'].'convert';
	if(!file_exists($im_file))
	{
		$IM_NOTE = "<span class='error'>".IMALAN_52."</span>";
	}
	else
	{
		$cmd = "{$im_file} -version";
		$tmp = `$cmd`;
		if(strpos($tmp, "ImageMagick") === FALSE)
		{
			$IM_NOTE = "<span class='error'>".IMALAN_53."</span>";
		}
	}
}

$text = "
	<form method='post' action='".e_SELF."'>
		<fieldset id='image-settings-form'>
			<legend class='e-hideme'>".IMALAN_7."</legend>
			<table cellpadding='0' cellspacing='0' class='adminform'>
				<colgroup span='2'>
					<col style='width:250px'></col>
					<col></col>
				</colgroup>
				<tbody>
					<tr>
						<td class='label'>
							".IMALAN_1."
						</td>
						<td class='control'>". ($pref['image_post'] ? "<input class='checkbox' type='checkbox' name='image_post' value='1' checked='checked' />" : "<input type='checkbox' name='image_post' value='1' />")."
							<div class='smalltext field-help'>".IMALAN_2."</div>
						</td>
					</tr>
					<tr>
						<td class='label'>
							".IMALAN_10."
						</td>
						<td class='control'>".r_userclass('image_post_class',$pref['image_post_class'],"off","public,guest,nobody,member,admin,main,classes")."
							<div class='smalltext field-help'>".IMALAN_11."</div>
						</td>
					</tr>

					<tr>
						<td class='label'>
							".IMALAN_12."
						</td>
						<td class='control'>
							<select name='image_post_disabled_method' class='tbox select'>". ($pref['image_post_disabled_method'] == "0" ? "<option value='1' selected='selected'>".IMALAN_14."</option>" : "<option value='0'>".IMALAN_14."</option>"). ($pref['image_post_disabled_method'] == "1" ? "<option value='1' selected='selected'>".IMALAN_15."</option>" : "<option value='1'>".IMALAN_15."</option>")."
							</select>
							<div class='smalltext field-help'>".IMALAN_13."</div>
						</td>
					</tr>

					<tr>
						<td class='label'>".IMALAN_3."<div class='label-note'>".IMALAN_54." {$gd_version}</div></td>
						<td class='control'>
							<select name='resize_method' class='tbox'>". ($pref['resize_method'] == "gd1" ? "<option selected='selected'>gd1</option>" : "<option>gd1</option>"). ($pref['resize_method'] == "gd2" ? "<option selected='selected'>gd2</option>" : "<option>gd2</option>"). ($pref['resize_method'] == "ImageMagick" ? "<option selected='selected'>ImageMagick</option>" : "<option>ImageMagick</option>")."
							</select>
							<div class='smalltext field-help'>".IMALAN_4."</div>
						</td>
					</tr>

					<tr>
						<td class='label'>".IMALAN_5."<div class='label-note'>{$IM_NOTE}</div></td>
						<td class='control'>
							<input class='tbox input-text' type='text' name='im_path' size='40' value=\"".$pref['im_path']."\" maxlength='200' />
							<div class='smalltext field-help'>".IMALAN_6."</div>
						</td>
					</tr>

					<tr>
						<td class='label'>".IMALAN_34."
						</td>
						<td class='control'>".($pref['enable_png_image_fix'] ? "<input type='checkbox' name='enable_png_image_fix' value='1' checked='checked' />" : "<input type='checkbox' name='enable_png_image_fix' value='1' />")."
							<div class='smalltext field-help'>".IMALAN_35."</div>
						</td>
					</tr>

					<tr>
						<td class='label'>".IMALAN_16."</td>
						<td class='control'>
							<button class='action' type='submit' name='show_avatars'><span>".IMALAN_17."</span></button>
						</td>
					</tr>

					<tr>
						<td class='label'>".IMALAN_36."</td>
						<td class='control'>
							<button class='action' type='submit' name='check_avatar_sizes'><span>".IMALAN_17."</span></button>
						</td>
					</tr>
				</tbody>
			</table>
			<div class='buttons-bar center'>
				<button class='update' type='submit' name='update_options'><span>".IMALAN_8."</span></button>
			</div>
		</fieldset>
	</form>";
$ns->tablerender(IMALAN_7, $text);


require_once("footer.php");

function headerjs()
{
	require_once(e_HANDLER.'js_helper.php');
	$ret = "
	<script type='text/javascript'>
		//add required core lan
		(".e_jshelper::toString(IMALAN_67).").addModLan('core', 'delete_confirm');

		/**
		 * Admin Image JS Handler
		 */
		var eCoreImage = {

			init: function() {
				this.tCheckEventHandler = this.tCheckHandler.bindAsEventListener(this);
				this.allCheckEventHandler = this.allCheckHandler.bindAsEventListener(this);
				this.allUnCheckEventHandler = this.allUnCheckHandler.bindAsEventListener(this);

				\$\$('.options').invoke('observe', 'click', this.tCheckEventHandler);
				\$\$('button.action[name=check_all]').invoke('observe', 'click', this.allCheckEventHandler);
				\$\$('button.action[name=uncheck_all]').invoke('observe', 'click', this.allUnCheckHandler);
				\$\$('button.delete').invoke('observe', 'click', function(e){ if( !e107Helper.confirm(e107.getModLan('delete_confirm')) ) e.stop(); });
			},

			tCheckHandler: function(event) {
				//do nothing if checkbox or its label is clicked
				if(event.element().nodeName.toLowerCase() == 'input') return;
				//stop event
				event.stop();
				//td element
				var element = event.findElement('td'), check = null;
				if(element) {
					check = element.select('input.checkbox'); //search for checkbox
				}
				//toggle checked property
				if(check && check[0]) {
					\$(check[0]).checked = !(\$(check[0]).checked);
				}
			},

			allCheckHandler: function(event) {
				event.stop();
				var form = event.element().up('form');
				if(form) {
					form.toggleChecked(true, 'name^=multiaction');
				}
			},

			allUnCheckHandler: function(event) {
				event.stop();
				var form = event.element().up('form');
				if(form) {
					form.toggleChecked(false, 'name^=multiaction');
				}
			}
		}

		/**
		 * Observe e107:loaded
		 *
		 */
		 e107.runOnLoad(eCoreImage.init.bind(eCoreImage), document, true);
	</script>
	";

	return $ret;
}
/*
XXX - remove this odd thing?!

$pref['resize_method'] = $_POST['resize_method'];
$pref['im_path'] = $_POST['im_path'];

*/
?>