<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2008 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Image Administration Area
 *
 * $Source: /cvs_backup/e107_0.8/e107_admin/image.php,v $
 * $Revision: 1.14 $
 * $Date: 2008-12-11 11:25:21 $
 * $Author: secretr $
 *
*/
require_once("../class2.php");
if (!getperms("A")) {
	header("location:".e_HTTP."index.php");
	exit;
}

$e_sub_cat = 'image';

require_once("auth.php");
require_once(e_HANDLER."form_handler.php");
require_once(e_HANDLER."userclass_class.php");
$rs = new form;

/*
 * CLOSE - GO TO MAIN SCREEN
 */
if(isset($_POST['submit_cancel_show']))
{
	header('Location: '.e_SELF);
	exit();
}

/*
 * DELETE CHECKED AVATARS - SHOW AVATAR SCREEN
 */
if (isset($_POST['submit_show_delete_multi']))
{
	if(varset($_POST['multiaction']))
	{
		$tmp = array(); $tmp1 = array(); $message = array();

		foreach ($_POST['multiaction'] as $todel)
		{
			$todel = explode('#', $todel);
			$todel[1] = basename($todel[1]);

			$image_type = 2;
			if(strpos($todel[1], '-upload-') === 0)
			{
				$image_type = 1;
				$todel[1] = substr($todel[1], strlen('-upload-'));
			}

			//delete it from server
			@unlink(e_FILE."public/avatars/".$todel[1]);

			//admin log & sysmessage
			$message[] = $todel[1];

			//It's owned by an user
			if($todel[0])
			{
				switch ($image_type)
				{
					case 1: //avatar
						$tmp[] = intval($todel[0]);
						break;

					case 2: //photo
						$tmp1[] = intval($todel[0]);
						break;
				}
			}
		}

		//Reset all deleted user avatars with one query
		if(!empty($tmp))
		{
			$sql->db_Update("user", "user_image='' WHERE user_id IN (".implode(',', $tmp).")");
		}
		//Reset all deleted user photos with one query
		if(!empty($tmp1))
		{
			$sql->db_Update("user", "user_sess='' WHERE user_id IN (".implode(',', $tmp1).")");
		}
		unset($tmp, $tmp1);

		//Format system message
		if(!empty($message))
		{
			$admin_log->log_event('IMALAN_01', implode('[!br!]', $message), E_LOG_INFORMATIVE, '');
			$message = implode(', ', $message).' '.IMALAN_28;
		}
		else $message = '';
	}
}

/*
 * DELETE ALL UNUSED IMAGES - SHOW AVATAR SCREEN
 */
if (isset($_POST['submit_show_deleteall']))
{
	$handle = opendir(e_FILE."public/avatars/");
	$dirlist = array();
	while ($file = readdir($handle)) {
		if (!is_dir(e_FILE."public/avatars/{$file}") && $file != '.' && $file != '..' && $file != "index.html" && $file != "null.txt" && $file != '/' && $file != 'CVS' && $file != 'Thumbs.db') {
			$dirlist[] = $file;
		}
	}
	closedir($handle);

	if(!empty($dirlist))
	{
		$imgList = '';
		$count = 0;
		foreach ($dirlist as $image_name)
		{
			$image_name = basename($image_name);
			$image_todb = $tp->toDB($image_name);
			if (!$sql->db_Count('user', '(*)', "WHERE user_image='-upload-{$image_todb}' OR user_sess='{$image_todb}'")) {
				unlink(e_FILE."public/avatars/".$image_name);
				$imgList .= '[!br!]'.$image_name;
				$count++;
			}
		}

		$message = $count." ".IMALAN_26;
		$admin_log->log_event('IMALAN_02', $message.$imgList,E_LOG_INFORMATIVE, '');
		unset($imgList);
	}
}


/*
 * DELETE ALL CHECKED BAD IMAGES - VALIDATE SCREEN
 */
if (isset($_POST['submit_avdelete_multi']))
{
	require_once(e_HANDLER."avatar_handler.php");
	$avList = array();
	$tmp = array();
	$uids = array();
	//Sanitize
	$_POST['multiaction'] = $tp->toDB($_POST['multiaction']);

	//sql queries significant reduced
	if(!empty($_POST['multiaction']) && $sql->db_Select("user", 'user_id, user_name, user_image', "user_id IN (".implode(',', $_POST['multiaction']).")"))
	{
		$search_users = $sql->db_getList('ALL', FALSE, FALSE, 'user_id');
		foreach($_POST['multiaction'] as $uid)
		{
			if (varsettrue($search_users[$uid]))
			{
				$avname = avatar($search_users[$uid]['user_image']);
				if (strpos($avname, "http://") === FALSE)
				{ // Internal file, so unlink it
					@unlink($avname);
				}

				$uids[] = $uid;
				$tmp[] = $search_users[$uid]['user_name'];
				$avList[] = $uid.':'.$search_users[$uid]['user_name'].':'.$search_users[$uid]['user_image'];
			}
		}

		//sql queries significant reduced
		if(!empty($uids))
		{
			$sql->db_Update("user", "user_image='' WHERE user_id IN (".implode(',', $uids).")");
		}

		$message = IMALAN_51.'<strong>'.implode(', ', $tmp).'</strong> '.IMALAN_28;
		$admin_log->log_event('IMALAN_03', implode('[!br!]', $avList), E_LOG_INFORMATIVE, '');
		unset($search_users);
	}
	unset($avList, $tmp, $uids);

}

/*
 * UPDATE IMAGE OPTIONS - MAIN SCREEN
 */
if (isset($_POST['update_options']))
{
	$tmp = array();
	$tmp['image_post'] = intval($_POST['image_post']);
	$tmp['resize_method'] = $tp->toDB($_POST['resize_method']);
	$tmp['im_path'] = trim($tp->toDB($_POST['im_path']));
	$tmp['image_post_class'] = intval($_POST['image_post_class']);
	$tmp['image_post_disabled_method'] = intval($_POST['image_post_disabled_method']);
	$tmp['enable_png_image_fix'] = intval($_POST['enable_png_image_fix']);

	if ($admin_log->logArrayDiffs($tmp, $pref, 'IMALAN_04'))
	{
		save_prefs();		// Only save if changes
		$message = IMALAN_9;
	}
	else
	{
		$message = IMALAN_20;
	}
}

/*
 * SYSTEM MESSAGE
 */
//FIXME - better message handler, sysmessages CSS rules
if (varsettrue($message))
{
	//no tablerender for sys-messages anymore
	$message = "
		<div class='s-message info'><span>".$message."</span></div>
	";
}
else
{
	$message = '';
}

/*
 * SHOW AVATARS SCREEN
 */
if (isset($_POST['show_avatars']))
{
	$handle = opendir(e_FILE."public/avatars/");
	$dirlist = array();
	while ($file = readdir($handle))
	{
		if ($file != '.' && $file != '..' && $file != "index.html" && $file != "null.txt" && $file != '/' && $file != 'CVS' && $file != 'Thumbs.db' && !is_dir($file))
		{
			$dirlist[] = $file;
		}
	}
	closedir($handle);

	$text = '';

	if (empty($dirlist))
	{
		$text .= IMALAN_29;
	}
	else
	{
		$text = "
			<form method='post' action='".e_SELF."' id='core-iamge-show-avatars-form'>
			<fieldset id='core-iamge-show-avatars'>
		";

		$count = 0;
		while (list($key, $image_name) = each($dirlist))
		{
			$users = IMALAN_21." | ";
			$row = array('user_id' => '');
			$image_pre = '';
			$disabled = '';
			if ($sql->db_Select("user", "*", "user_image='-upload-".$tp->toDB($image_name)."' OR user_sess='".$tp->toDB($image_name)."'"))
			{
				/*
				//Is it possible?! I don't think so
				while ($row = $sql->db_Fetch())
				{
					extract($row); // kill this!!!
					$users .= "<a href='".e_BASE."user.php?id.$user_id'>$user_name</a> <span class='smalltext'>(".($user_sess == $image_name ? IMALAN_24 : IMALAN_23).")</span> | ";
				}*/
				$row = $sql->db_Fetch();
				if($row['user_image'] == '-upload-'.$image_name) $image_pre = '-upload-';
				$users .= "<a href='".e_BASE."user.php?id.{$row['user_id']}'>{$row['user_name']}</a> <span class='smalltext'>(".($row['user_sess'] == $image_name ? IMALAN_24 : IMALAN_23).")</span>";
			} else {
				$users = '<span class="warning">'.IMALAN_22.'</span>';
			}

			//directory?
			if(is_dir(e_FILE."public/avatars/".$image_name))
			{
				//File info
				$users = "<a class='e-tooltip' href='#' title='".IMALAN_69.": {$image_name}'><img class='icon S16' src='".e_IMAGE_ABS."admin_images/docs_16.png' alt='".IMALAN_66.": {$image_name}' /></a> <span class='error'>".IMALAN_69."</span>";

				//Friendly UI - click text to select a form element
				$img_src =  '<span class="error">'.IMALAN_70.'</span>';
				$disabled = ' disabled="disabled"';
			}
			else
			{
				//File info
				$users = "<a class='e-tooltip' href='#' title='".IMALAN_66.": {$image_name}'><img src='".e_IMAGE_ABS."admin_images/docs_16.png' alt='".IMALAN_66.": {$image_name}' /></a> ".$users;

				// Control over the image size (design)
				$image_size = getimagesize(e_FILE."public/avatars/".$image_name);

				//Friendly UI - click text to select a form element
				$img_src = "<label for='image-action-{$count}' title='".IMALAN_56."'><img src='".e_FILE_ABS."public/avatars/{$image_name}' alt='{$image_name}' /></label>";
				if ($image_size[0] > $pref['im_width'] || $image_size[1] > $pref['im_height'])
				{
					$img_src = "<a class='image-preview' href='".e_FILE_ABS."public/avatars/".rawurlencode($image_name)."' rel='external'>".IMALAN_57."</a>";
				}
			}

			//style attribute allowed here - server side width/height control
			//autocheck class - used for JS selectors (see eCoreImage object)
			$text .= "
			<div class='image-box f-left center autocheck' style='width: ".(intval($pref['im_width'])+40)."px; height: ".(intval($pref['im_height'])+100)."px;'>
				<div class='spacer'>
				<div class='image-users'>{$users}</div>
				<div class='image-preview'>{$img_src}</div>
				<div class='image-delete'>
					<input type='checkbox' class='checkbox' id='image-action-{$count}' name='multiaction[]' value='{$row['user_id']}#{$image_pre}{$image_name}'{$disabled} />
				</div>

				</div>
			</div>

			";

			$count++;
		}

		$text .= "
			<div class='spacer clear'>
				<div class='buttons-bar'>
					<input type='hidden' name='show_avatars' value='1' />
					<button class='action' type='button' name='check_all' value='".IMALAN_59."'><span>".IMALAN_59."</span></button>
					<button class='action' type='button' name='uncheck_all' value='".IMALAN_60."'><span>".IMALAN_60."</span></button>
					<button class='delete' type='submit' name='submit_show_delete_multi' value='".IMALAN_58."'><span>".IMALAN_58."</span></button>
					<button class='delete' type='submit' name='submit_show_deleteall' value='".IMALAN_25."'><span>".IMALAN_25."</span></button>
					<button class='cancel' type='submit' name='submit_cancel_show' value='".IMALAN_68."'><span>".IMALAN_68."</span></button>
				</div>
			</div>
			</fieldset>
			</form>
		";

	}


	$ns->tablerender(IMALAN_18, $message.$text);
}

/*
 * CHECK AVATARS SCREEN
 */
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
		<fieldset id='core-image-check-avatar'>
			<legend class='e-hideme'>".CACLAN_3."</legend>
			<table cellpadding='0' cellspacing='0' class='adminlist'>
				<colgroup span='4'>
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
	$found = false;
	$allowedWidth = intval($pref['im_width']);
	$allowedHeight = intval($pref['im_width']);
	if ($sql->db_Select("user", "*", "user_image!=''")) {

		while ($row = $sql->db_Fetch())
		{
			//Check size
			$avname=avatar($row['user_image']);
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
			}
			else
			{
				$imageWidth = $image_stats[0];
				$imageHeight = $image_stats[1];

				if ( ($imageHeight > $allowedHeight) || ($imageWidth > $allowedWidth) )
				{ // Too tall or too wide
					$iAVtoobig++;
					if ($imageWidth > $allowedWidth)
					{
						$sBadImage = IMALAN_40." ($imageWidth)";
					}

					if ($imageHeight > $allowedHeight)
					{
						if (strlen($sBadImage))
						{
							$sBadImage .= ", ";
						}
						$sBadImage .= IMALAN_41." ($imageHeight)";
					}
				}
			}

			//If not found or too large, allow delete
			if (strlen($sBadImage))
			{
				$found = true;
				$text .= "
				<tr>
					<td class='autocheck center'>
						<input class='checkbox' type='checkbox' name='multiaction[]' id='avdelete-{$row['user_id']}' value='{$row['user_id']}' />
					</td>
					<td>
						<label for='avdelete-{$row['user_id']}' title='".IMALAN_56."'>".IMALAN_51."</label><a href='".e_BASE."user.php?id.{$row['user_id']}'>".$row['user_name']."</a>
					</td>
					<td>".$sBadImage."</td>
					<td>".$avname."</td>
				</tr>";
			}
		}
	}

	//Nothing found
	if(!$found)
	{
		$text .= "
				<tr>
					<td colspan='4' class='center'>".IMALAN_65."</td>
				</tr>
		";
	}

	$text .= "
				</tbody>
			</table>
			<div class='buttons-bar'>
				<input type='hidden' name='check_avatar_sizes' value='1' />
				<button class='action' type='button' name='check_all' value='".IMALAN_59."'><span>".IMALAN_59."</span></button>
				<button class='action' type='button' name='uncheck_all' value='".IMALAN_60."'><span>".IMALAN_60."</span></button>
				<button class='delete' type='submit' name='submit_avdelete_multi' value='".IMALAN_58."'><span>".IMALAN_58."</span></button>
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
				<td class='control'>{$allowedWidth}</td>
			</tr>
			<tr>
				<td class='label'>".IMALAN_39."</td>
				<td class='control'>{$allowedHeight}</td>
			</tr>
			<tr>
				<td class='label'>".IMALAN_45."</td>
				<td class='control'>{$iAVnotfound}</td>
			</tr>
			<tr>
				<td class='label'>".IMALAN_46."</td>
				<td class='control'>{$iAVtoobig}</td>
			</tr>
			<tr>
				<td class='label'>".IMALAN_47."</td>
				<td class='control'>{$iAVinternal}</td>
			</tr>
			<tr>
				<td class='label'>".IMALAN_48."</td>
				<td class='control'>{$iAVexternal}</td>
			</tr>
			<tr>
				<td class='label'>".IMALAN_49."</td>
				<td class='control'>".($iAVexternal+$iAVinternal)." (".(int)(100.0*(($iAVexternal+$iAVinternal)/$iUserCount)).'%, '.$iUserCount." ".IMALAN_50.")</td>
			</tr>
		</tbody>
	</table>
	";

	$ns->tablerender(IMALAN_37, $message.$text);
}

/*
 * MAIN CONFIG SCREEN
 */
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
		<fieldset id='core-image-settings'>
			<legend class='e-hideme'>".IMALAN_7."</legend>
			<table cellpadding='0' cellspacing='0' class='adminform'>
				<colgroup span='2'>
					<col class='col-label'></col>
					<col class='col-control'></col>
				</colgroup>
				<tbody>
					<tr>
						<td class='label'>
							".IMALAN_1."
						</td>
						<td class='control'>
							<div class='auto-toggle-area autocheck'>
								<input class='checkbox' type='checkbox' name='image_post' value='1'".($pref['image_post'] ? " checked='checked'" : '')." />
								<div class='smalltext field-help'>".IMALAN_2."</div>
							</div>
						</td>
					</tr>
					<tr>
						<td class='label'>
							".IMALAN_10."
						</td>
						<td class='control'>
							".r_userclass('image_post_class',$pref['image_post_class'],"off","public,guest,nobody,member,admin,main,classes")."
							<div class='smalltext field-help'>".IMALAN_11."</div>
						</td>
					</tr>

					<tr>
						<td class='label'>
							".IMALAN_12."
						</td>
						<td class='control'>
							<select name='image_post_disabled_method' class='tbox select'>
								<option value='0'".($pref['image_post_disabled_method'] == "0" ? " selected='selected'" : '').">".IMALAN_14."</option>
								<option value='1'".($pref['image_post_disabled_method'] == "1" ? " selected='selected'" : "").">".IMALAN_15."</option>
							</select>
							<div class='smalltext field-help'>".IMALAN_13."</div>
						</td>
					</tr>

					<tr>
						<td class='label'>".IMALAN_3."<div class='label-note'>".IMALAN_54." {$gd_version}</div></td>
						<td class='control'>
							<select name='resize_method' class='tbox'>
								<option".($pref['resize_method'] == "gd1" ? " selected='selected'" : '').">gd1</option>
								<option".($pref['resize_method'] == "gd2" ? " selected='selected'" : '').">gd2</option>
								<option".($pref['resize_method'] == "ImageMagick" ? " selected='selected'" : '').">ImageMagick</option>
							</select>
							<div class='smalltext field-help'>".IMALAN_4."</div>
						</td>
					</tr>

					<tr>
						<td class='label'>".IMALAN_5."<div class='label-note'>{$IM_NOTE}</div></td>
						<td class='control'>
							<input class='tbox input-text' type='text' name='im_path' size='40' value='{$pref['im_path']}' maxlength='200' />
							<div class='smalltext field-help'>".IMALAN_6."</div>
						</td>
					</tr>

					<tr>
						<td class='label'>".IMALAN_34."
						</td>
						<td class='control'>
							<div class='auto-toggle-area autocheck'>
								<input type='checkbox' class='checkbox' name='enable_png_image_fix' value='1'".($pref['enable_png_image_fix'] ? " checked='checked'" : '')." />
								<div class='smalltext field-help'>".IMALAN_35."</div>
							</div>
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
				<button class='update' type='submit' name='update_options' value='".IMALAN_8."'><span>".IMALAN_8."</span></button>
			</div>
		</fieldset>
	</form>";
$ns->tablerender(IMALAN_7, $message.$text);

//Just in case...
if(!e_AJAX_REQUEST) require_once("footer.php");

/**
 * Handle page DOM within the page header
 *
 * @return string JS source
 */
function headerjs()
{
	require_once(e_HANDLER.'js_helper.php');
	//FIXME - how exactly to call JS lan?
	$ret = "
		<script type='text/javascript'>
			//add required core lan
			(".e_jshelper::toString(IMALAN_67).").addModLan('core', 'delete_confirm');
		</script>
		<script type='text/javascript' src='".e_FILE_ABS."jslib/core/admin.js'></script>
	";

	return $ret;
}
?>