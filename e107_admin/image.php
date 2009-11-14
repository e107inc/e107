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
 * $Revision: 1.29 $
 * $Date: 2009-11-14 04:13:10 $
 * $Author: e107coders $
 *
*/
require_once("../class2.php");
if (!getperms("A"))
{
	header("location:".e_HTTP."index.php");
	exit;
}

/*
 * CLOSE - GO TO MAIN SCREEN
 */
if(isset($_POST['submit_cancel_show']))
{
	header('Location: '.e_SELF);
	exit();
}

include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_'.e_PAGE);

$e_sub_cat = 'image';

// require_once("auth.php");
// require_once(e_HANDLER."form_handler.php");
require_once(e_HANDLER."userclass_class.php");
require_once(e_HANDLER."message_handler.php");
// $frm = new e_form(); //new form handler
$emessage = &eMessage::getInstance();

class media_admin extends e_admin_dispatcher
{

	protected $modes = array(
		'main'		=> array(
			'controller' 	=> 'media_admin_ui',
			'path' 			=> null,
			'ui' 			=> 'comments_admin_form_ui',
			'uipath' 		=> null
		)				
	);	


	protected $adminMenu = array(
		'main/list'			=> array('caption'=> 'Media Library', 'perm' => 'A'),
		'main/create' 		=> array('caption'=> "Add New Media", 'perm' => 'A'),
		'main/icons' 		=> array('caption'=> IMALAN_71, 'perm' => 'A'),
		'main/settings' 	=> array('caption'=> LAN_PREFS, 'perm' => 'A'),
		'main/avatars'		=> array('caption'=> IMALAN_23, 'perm' => 'A')		
	);
	
/*	
	$var['main']['text'] = IMALAN_7;
	$var['main']['link'] = e_SELF;

	$var['icons']['text'] = IMALAN_71;
	$var['icons']['link'] = e_SELF."?icons";

	$var['avatars']['text'] = IMALAN_23;
	$var['avatars']['link'] = e_SELF."?avatars";


	$var['editor']['text'] = "Image Manipulation (future release)";
	$var['editor']['link'] = e_SELF."?editor";*/
	
	protected $mimePaths = array(
			"text"			=> 'files',
			'multipart'		=> 'files',
			'application'	=> 'files',
			'audio'			=> 'audio',
			'image'			=> 'images',
			'video'			=> 'video',
			'other'			=> 'files'
	);

	protected $adminMenuAliases = array(
		'main/edit'	=> 'main/list'				
	);	
	
	protected $menuTitle = LAN_MEDIAMANAGER;
		
	function init()
	{
		$this->observeUploaded();
	}	
		
		
	function observeUploaded()
	{
		$sql = e107::getDb();
		$mes = e107::getMessage();
		
		if(!varset($_POST['uploadfiles']) && !varset($_POST['etrigger_submit']))
		{
			return;
		}
		
		$pref['upload_storagetype'] = "1";
		require_once(e_HANDLER."upload_handler.php"); //TODO - still not a class!
		$uploaded = process_uploaded_files(e_MEDIA.'temp/');
		
		foreach($uploaded as $upload)
		{
			$oldpath = 'temp/'.$upload['name'];
			$newpath =$this->getPath($upload['type']).'/'.$upload['name'];
						
			$insert = array(
				'media_type'		=> $upload['type'],
				'media_name'		=> $upload['name'],
				'media_description'	=> '',
				'media_url'			=> "{e_MEDIA}".$newpath,
				'media_datestamp'	=> time(),
				'media_size'		=> $upload['size'],
				'media_usedby'		=> '',
				'media_tags'		=> '',
				'media_author'		=> USERID
			);
			
			// Temporary workaround for class limitation. 
			/*
			 * We need to process the data before it's saved to the DB. 
			 * When the upload is done with ajax, we need to prepopulate the form with the data above.
			 */
			
			if(rename(e_MEDIA.$oldpath, e_MEDIA.$newpath))
			{
				if($sql->db_Insert('core_media',$insert)) 
				{
					$mes->add("Added ".$upload['name']." to DB", E_MESSAGE_SUCCESS);	
				} 				
			}			
		}
		
		$message .= print_a($uploaded,TRUE);

		$mes->add($message, E_MESSAGE_DEBUG);
		
		
	}
	
	function onDelete() // call when 'delete' is executed. - delete the file with the db record (optional pref)
	{
		
	}
	
	function getPath($type)
	{
		list($pmime,$tmp) = explode('/',$type);
		$dir = $this->mimePaths[$pmime]."/".date("Y-m"); 
		if(!is_dir(e_MEDIA.$dir))
		{
			mkdir(e_MEDIA.$dir,0755);	
		}
		return $dir;		
	}
	
}


class media_admin_ui extends e_admin_ui
{
		
		protected $pluginTitle = LAN_MEDIAMANAGER; 
		protected $pluginName = 'core';
		protected $table = "core_media";
		
	//	protected $listQry = "SELECT * FROM #core_media"; // without any Order or Limit. 
		
	//	//protected $editQry = "SELECT * FROM #comments WHERE comment_id = {ID}";
		
		protected $tableJoin = array(
			'u.user' => array('leftField' => 'media_author', 'rightField' => 'user_id', 'fields' => 'user_id,user_loginname,user_name')
		);
		
		protected $pid = "media_id";
		protected $perPage = 10;
		protected $batchDelete = true;
		
		//TODO - finish 'user' type, set 'data' to all editable fields, set 'noedit' for all non-editable fields
    	/*
    	 * We need a column with a preview that is generated from the path of another field. 
    	 * ie. the preview column should show a thumbnail which is generated from the media_url column. 
    	 * It needs to also take into consideration the type of media (image, video etc) which comes from another field. 
    	 */
			
		protected $fields = array(
			'checkboxes'			=> array('title'=> '',				'type' => null,			'data'=> null,		'width' =>'5%', 'forced'=> TRUE, 'thclass'=>'center', 'class'=>'center'),
			'media_id'				=> array('title'=> LAN_ID,			'type' => 'int',		'data'=> 'int',		'width' =>'5%', 'forced'=> TRUE),
      		'media_url' 			=> array('title'=> LAN_URL,			'type' => 'image',		'data'=> 'str',		'thclass' => 'center', 'class'=>'center', 'filter' => true, 'batch' => true,	'width' => 'auto'),	 
		
	   	//	'media_preview' 		=> array('title'=> "Preview",		'type' => 'image',			'data'=> null,		'width' => '10%'), 
       		'media_upload' 			=> array('title'=> "Upload File",	'type' => 'upload',		'data'=> null,		'readParm' => 'hidden',	'width' => '10%'),  
			'media_name' 			=> array('title'=> LAN_TITLE,		'type' => 'text',		'data'=> 'str',		'width' => '5%'),
			'media_caption' 		=> array('title'=> "Caption",		'type' => 'text',		'data'=> 'str',		'width' => '5%'),
         	'media_description' 	=> array('title'=> LAN_DESCRIPTION,	'type' => 'textarea',	'data'=> 'str',		'width' => 'auto', 'thclass' => 'left first'), 
         	'media_category' 		=> array('title'=> LAN_CATEGORY,	'type' => 'int',		'data'=> 'int',		'width' => '30%'), 
			'media_type' 			=> array('title'=> "Mime Type",		'type' => 'text',		'data'=> 'str',		'width' => '30%'), 
		//	'media_author'			=> array('title'=> LAN_AUTHOR,		'type' => 'user',		'data'=> 'int'),
			'media_author' 			=> array('title'=> LAN_USER,		'type' => 'user',			'data'=> 'int', 'width' => 'auto', 'thclass' => 'center', 'class'=>'center', 'writeParms' => 'currentInit=1', 'filter' => true, 'batch' => true, 'nolist' => false	),	
			'media_datestamp' 		=> array('title'=> LAN_DATESTAMP,	'type' => 'datestamp',	'data'=> 'int',		'width' => 'auto'),	// User date
          	'media_size' 			=> array('title'=> "Size",			'type' => 'int',		'data'=> 'int',		'width' => 'auto'), 
			'media_dimensions' 		=> array('title'=> "Dimensions",	'type' => 'text',		'data'=> 'str',		'width' => 'auto'), 
			'media_userclass' 		=> array('title'=> LAN_USERCLASS,	'type' => 'userclass',	'data'=> 'str',		'width' => '10%', 'thclass' => 'center' ),	 
			'media_tags' 			=> array('title'=> "Tags/Keywords",	'type' => 'text',		'data'=> 'str',		'width' => '10%',  'filter'=>TRUE,'batch'=>TRUE ),	
			'u.user_name' 			=> array('title'=> "User name",		'type' => 'user',			'width' => 'auto', 'noedit' => true, 'readParms'=>'idField=media_author&link=1'),	// User name
       		'u.user_loginname' 		=> array('title'=> "User login",	'type' => 'user',			'width' => 'auto', 'noedit' => true, 'readParms'=>'idField=media_author&link=1'),	// User login name			
			'options' 				=> array('title'=> LAN_OPTIONS,		'type' => null,			'data'=> null,		'forced'=>TRUE, 'width' => '10%', 'thclass' => 'center last', 'class' => 'center')
		);


	//	protected $fieldpref = array('checkboxes','media_url', 'media_id', 'media_thumb', 'media_title', 'media_caption', 'media_description', 'media_category', 'media_datestamp','media_userclass', 'options');
		



		/*
		protected $prefs = array( 
			'pref_type'	   				=> array('title'=> 'type', 'type'=>'text'),
			'pref_folder' 				=> array('title'=> 'folder', 'type' => 'boolean'),	
			'pref_name' 				=> array('title'=> 'name', 'type' => 'text')		
		);*/
	
		

		
}


new media_admin();

require_once(e_ADMIN."auth.php");

e107::getAdminUI()->runPage();











// -----------------------------------------------------------------------

 


$action = e_QUERY;


if(isset($_POST['delpref']) || (isset($_POST['delpref_checked']) && isset($_POST['delpref2'])))
{
	del_pref_val();
}

if(varset($_GET['action']) == "icons")
{
	icon_editor();
}

if(varset($_GET['action']) == "avatars")
{
	show_avatars();
}

if(varset($_GET['action']) == 'settings')
{
	main_config();
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
			@unlink(e_UPLOAD."avatars/".$todel[1]);

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
			$emessage->add(implode(', ', $message).' '.IMALAN_28, E_MESSAGE_SUCCESS);
		}
	}
}

/*
 * DELETE ALL UNUSED IMAGES - SHOW AVATAR SCREEN
 */
if (isset($_POST['submit_show_deleteall']))
{
	$handle = opendir(e_UPLOAD."avatars/");
	$dirlist = array();
	while ($file = readdir($handle)) {
		if (!is_dir(e_UPLOAD."avatars/{$file}") && $file != '.' && $file != '..' && $file != "index.html" && $file != "null.txt" && $file != '/' && $file != 'CVS' && $file != 'Thumbs.db') {
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
				unlink(e_UPLOAD."avatars/".$image_name);
				$imgList .= '[!br!]'.$image_name;
				$count++;
			}
		}

		$message = $count." ".IMALAN_26;
		$emessage->add($message, E_MESSAGE_SUCCESS);
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

		$emessage->add(IMALAN_51.'<strong>'.implode(', ', $tmp).'</strong> '.IMALAN_28, E_MESSAGE_SUCCESS);
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
		$emessage->add(IMALAN_9, E_MESSAGE_SUCCESS);
	}
	else
	{
		$emessage->add(IMALAN_20, E_MESSAGE_INFO);
	}
}

/*
 * SHOW AVATARS SCREEN
 */
function show_avatars()
{
	global $e107, $pref;
	
	$ns = e107::getRender();
	$sql = e107::getDb();
	$frm = e107::getForm();
	$tp = e107::getParser();
	$mes = e107::getMessage();

	$handle = opendir(e_UPLOAD."avatars/"); //TODO replace with $fl
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
			<form method='post' action='".e_SELF."?avatars' id='core-iamge-show-avatars-form'>
			<fieldset id='core-iamge-show-avatars'>
		";

		$count = 0;
		while (list($key, $image_name) = each($dirlist))
		{
			$users = IMALAN_21." | ";
			$row = array('user_id' => '');
			$image_pre = '';
			$disabled = false;
			if ($sql->db_Select("user", "*", "user_image='-upload-".$tp->toDB($image_name)."' OR user_sess='".$tp->toDB($image_name)."'"))
			{
				$row = $sql->db_Fetch();
				if($row['user_image'] == '-upload-'.$image_name) $image_pre = '-upload-';
				$users .= "<a href='".$e107->url->getUrl('core:user', 'main', 'func=profile&id='.$row['user_id'])."'>{$row['user_name']}</a> <span class='smalltext'>(".($row['user_sess'] == $image_name ? IMALAN_24 : IMALAN_23).")</span>";
			}
			else
			{
				$users = '<span class="warning">'.IMALAN_22.'</span>';
			}

			//directory?
			if(is_dir(e_UPLOADE."avatars/".$image_name))
			{
				//File info
				$users = "<a class='e-tooltip' href='#' title='".IMALAN_69.": {$image_name}'><img class='icon S16' src='".e_IMAGE_ABS."admin_images/info_16.png' alt='".IMALAN_66.": {$image_name}' /></a> <span class='error'>".IMALAN_69."</span>";

				//Friendly UI - click text to select a form element
				$img_src =  '<span class="error">'.IMALAN_70.'</span>';
				$disabled = true;
			}
			else
			{
				//File info
				$users = "<a class='e-tooltip' href='#' title='".IMALAN_66.": {$image_name}'><img src='".e_IMAGE_ABS."admin_images/info_16.png' alt='".IMALAN_66.": {$image_name}' /></a> ".$users;

				// Control over the image size (design)
				$image_size = getimagesize(e_UPLOAD."avatars/".$image_name);

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
					".$frm->checkbox('multiaction[]', "{$row['user_id']}#{$image_pre}{$image_name}", false, array('id' => false, 'disabled' => $disabled))."
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
					".$frm->admin_button('check_all', LAN_CHECKALL, 'action')."
					".$frm->admin_button('uncheck_all', LAN_UNCHECKALL, 'action')."
					".$frm->admin_button('submit_show_delete_multi', LAN_DELCHECKED, 'delete')."
					".$frm->admin_button('submit_show_deleteall', IMALAN_25, 'delete')."
					".$frm->admin_button('submit_cancel_show', IMALAN_68, 'cancel')."
				</div>
			</div>
			</fieldset>
			</form>
		";

	}


	$ns->tablerender(LAN_MEDIAMANAGER." :: ".IMALAN_18, $mes->render().$text);
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
						<th class='center'>".LAN_OPTIONS."</th>
						<th class='center'>".LAN_USER."</th>
						<th class='center'>".IMALAN_62."</th>
						<th class='center last'>".LAN_URL."</th>
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
				".$frm->admin_button('check_all', LAN_CHECKALL, 'action')."
				".$frm->admin_button('uncheck_all', LAN_UNCHECKALL, 'action')."
				".$frm->admin_button('submit_avdelete_multi', LAN_DELCHECKED, 'delete')."
			</div>
		</fieldset>
	</form>

	<table cellpadding='0' cellspacing='0' class='adminform'>
	<colgroup span='2'>
		<col class='col-label' />
		<col class='col-control' />
	</colgroup>
		</colgroup>
		<tbody>
			<tr>
				<td>".IMALAN_38."</td>
				<td>{$allowedWidth}</td>
			</tr>
			<tr>
				<td>".IMALAN_39."</td>
				<td>{$allowedHeight}</td>
			</tr>
			<tr>
				<td>".IMALAN_45."</td>
				<td>{$iAVnotfound}</td>
			</tr>
			<tr>
				<td>".IMALAN_46."</td>
				<td>{$iAVtoobig}</td>
			</tr>
			<tr>
				<td>".IMALAN_47."</td>
				<td>{$iAVinternal}</td>
			</tr>
			<tr>
				<td>".IMALAN_48."</td>
				<td>{$iAVexternal}</td>
			</tr>
			<tr>
				<td>".IMALAN_49."</td>
				<td>".($iAVexternal+$iAVinternal)." (".(int)(100.0*(($iAVexternal+$iAVinternal)/$iUserCount)).'%, '.$iUserCount." ".IMALAN_50.")</td>
			</tr>
		</tbody>
	</table>
	";

	$ns->tablerender(IMALAN_37, $emessage->render().$text);
}

/*
 * MAIN CONFIG SCREEN
 */
 function main_config()
 {
 	global $pref;
	
	$frm = e107::getForm();
	$tp = e107::getParser();
	$sql = e107::getDb();
	$ns = e107::getRender();
	$mes = e107::getMessage();

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
									".$frm->checkbox('image_post', 1, $pref['image_post'])."
									<div class='field-help'>".IMALAN_2."</div>
								</div>
							</td>
						</tr>
						<tr>
							<td class='label'>
								".IMALAN_10."
							</td>
							<td class='control'>
								".r_userclass('image_post_class',$pref['image_post_class'],"off","public,guest,nobody,member,admin,main,classes")."
								<div class='field-help'>".IMALAN_11."</div>
							</td>
						</tr>

						<tr>
							<td class='label'>
								".IMALAN_12."
							</td>
							<td class='control'>
								".$frm->select_open('image_post_disabled_method')."
									".$frm->option(IMALAN_14, '0', ($pref['image_post_disabled_method'] == "0"))."
									".$frm->option(IMALAN_15, '1', ($pref['image_post_disabled_method'] == "1"))."
								".$frm->select_close()."
								<div class='field-help'>".IMALAN_13."</div>
							</td>
						</tr>

						<tr>
							<td class='label'>".IMALAN_3."<div class='label-note'>".IMALAN_54." {$gd_version}</div></td>
							<td class='control'>
								".$frm->select_open('resize_method')."
									".$frm->option('gd1', 'gd1', ($pref['resize_method'] == "gd1"))."
									".$frm->option('gd2', 'gd2', ($pref['resize_method'] == "gd2"))."
									".$frm->option('ImageMagick', 'ImageMagick', ($pref['resize_method'] == "ImageMagick"))."
								".$frm->select_close()."
								<div class='field-help'>".IMALAN_4."</div>
							</td>
						</tr>

						<tr>
							<td class='label'>".IMALAN_5."<div class='label-note'>{$IM_NOTE}</div></td>
							<td class='control'>
								".$frm->text('im_path', $pref['im_path'])."
								<div class='field-help'>".IMALAN_6."</div>
							</td>
						</tr>

						<tr>
							<td class='label'>".IMALAN_34."
							</td>
							<td class='control'>
								<div class='auto-toggle-area autocheck'>
									".$frm->checkbox('enable_png_image_fix', 1, ($pref['enable_png_image_fix']))."
									<div class='field-help'>".IMALAN_35."</div>
								</div>
							</td>
						</tr>

						<tr>
							<td class='label'>".IMALAN_36."</td>
							<td class='control'>
								".$frm->admin_button('check_avatar_sizes', ADLAN_145)."
							</td>
						</tr>
					</tbody>
				</table>
				<div class='buttons-bar center'>
					".$frm->admin_button('update_options', IMALAN_8, 'update')."
				</div>
			</fieldset>
		</form>";

		$ns->tablerender(LAN_MEDIAMANAGER." :: ".IMALAN_7, $mes->render().$text);
}
//Just in case...
if(!e_AJAX_REQUEST) require_once("footer.php");




function icon_editor()
{
	global $iconpool, $e107;
	
	$ns = e107::getRender();
	$tp = e107::getParser();
	$frm = e107::getForm();
	$mes = e107::getMessage();
	
	ksort($iconpool);

	$text = "
			<form method='post' action='".e_SELF."?icons' id='icon_edit'>
				<fieldset id='core-imagemanager-icons'>
					<legend class='e-hideme'>".DBLAN_20."</legend>
					<table cellpadding='0' cellspacing='0' class='adminlist'>
						<colgroup span='4'>
							<col style='width: 5%'></col>
							<col style='width: 20%'></col>
							<col style='width: 70%'></col>
							<col style='width: 5%'></col>
						</colgroup>
						<thead>
							<tr>
								<th class='center'>".LAN_DELETE."</th>
								<th>".LAN_CATEGORY."</th>
								<th>".IMALAN_72."</th>
								<th class='center last'>".LAN_OPTIONS."</th>
							</tr>
						</thead>
						<tbody>
		";
    $tmp = array(16, 32, 48, 64, 128);

	foreach($iconpool as $key => $val)
	{
			$tmp1 = array();
            foreach($val as $icon)
			{
	            $filepath = $icon;
		 		$filepath_abs = $tp->replaceConstants($icon);
				$icon_file = basename($filepath_abs);

				$str = "<img class='icon picker list%%size%%' src='{$filepath_abs}' alt='{$icon_file}' />";
				foreach ($tmp as $isize)
				{
					if(strpos($icon_file, '_'.$isize.'.') !== false)
					{
						$tmp1[$isize] = varset($tmp1[$isize]).str_replace('%%size%%', ' S'.$isize, $str);
						continue 2;
					}
				}
			   	$tmp1['other'] = varset($tmp1['other']).$str;//other
            }

			$ptext = "<div class='field-spacer iconeditor'>".str_replace('%%size%%', '', implode('</div><div class="field-spacer iconeditor">', $tmp1))."</div>";

   //	$ptext = (is_array($val)) ? "<pre>".print_r($val, TRUE)."</pre>" : htmlspecialchars($val, ENT_QUOTES, CHARSET);
   //		$ptext = $e107->tp->textclean($ptext, 80);

		$text .= "
							<tr>
								<td class='center autocheck e-pointer'>".$frm->checkbox("delpref2[$key]", 1)."</td>
								<td>{$key}</td>
								<td>{$ptext}</td>
								<td class='center'>".$frm->submit_image("delpref[$key]", LAN_DELETE, 'delete', LAN_CONFIRMDEL." [$key]")."</td>
							</tr>
			";
	}

	$text .= "
						</tbody>
					</table>
					<div class='buttons-bar center'>
						".$frm->admin_button('delpref_checked', LAN_DELCHECKED, 'delete')."
					</div>
				</fieldset>
			</form>


		";
	//$text .= "<div style='text-align:center'><a href='".e_SELF."'>".DBLAN_13."</a></div>\n";
	$ns->tablerender(LAN_MEDIAMANAGER." :: ".IMALAN_71, $mes->render().$text);

	return $text;
}

function del_pref_val()
{
	global $iconpool, $e107cache, $emessage;
	$del = array_keys($_POST['delpref']);
	$delpref = key($_POST['delpref']);

	if($delpref)
	{
		unset($iconpool[$delpref]);
		$deleted_list .= "<li>".$delpref."</li>";
	}

	if($_POST['delpref2'])
	{

		foreach($_POST['delpref2'] as $k => $v)
		{
			$deleted_list .= "<li>".$k."</li>";
			unset($iconpool[$k]);
		}
	}

	if(save_prefs('iconpool'))
	{
		$emessage->add(LAN_DELETED."<ul>".$deleted_list."</ul>");
		$e107cache->clear();
	}
	//$e107->ns->tablerender(LAN_DELETED,$message);


}













/**
 * Handle page DOM within the page header
 *
 * @return string JS source
 */
function headerjs()
{
	require_once(e_HANDLER.'js_helper.php');
	//FIXME - how exactly to auto-call JS lan? This and more should be solved in Stage II.
	$ret = "
		<script type='text/javascript'>
			//add required core lan - delete confirm message
			(".e_jshelper::toString(LAN_JSCONFIRM).").addModLan('core', 'delete_confirm');
		</script>
		<script type='text/javascript' src='".e_FILE_ABS."jslib/core/admin.js'></script>
	";

	return $ret;
}
?>