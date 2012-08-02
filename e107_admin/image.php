<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Image Administration Area
 *
 * $URL$
 * $Id$
 *
*/

if (!defined('e107_INIT'))
{
	require_once("../class2.php");
}

if (!getperms("A") && ($_GET['action'] != 'dialog')) 
{
	header("location:".e_HTTP."index.php");
	exit;
}

e107::js('core', 	'plupload/plupload.full.js', 'jquery', 2);
e107::css('core', 	'plupload/jquery.plupload.queue/css/jquery.plupload.queue.css', 'jquery');
e107::js('core', 	'plupload/jquery.plupload.queue/jquery.plupload.queue.js', 'jquery', 2);
e107::js("core",	"core/mediaManager.js","jquery",3);

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



// $frm = new e_form(); //new form handler
$emessage = eMessage::getInstance();

class media_admin extends e_admin_dispatcher
{

	protected $modes = array(
		'main'		=> array(
			'controller' 	=> 'media_admin_ui',
			'path' 			=> null,
			'ui' 			=> 'media_form_ui',
			'uipath' 		=> null
		),
		'dialog'		=> array(
			'controller' 	=> 'media_admin_ui',
			'path' 			=> null,
			'ui' 			=> 'media_form_ui',
			'uipath' 		=> null
		),
		'cat'		=> array(
			'controller' 	=> 'media_cat_ui',
			'path' 			=> null,
			'ui' 			=> 'media_cat_form_ui',
			'uipath' 		=> null
		)
	);


	protected $adminMenu = array(
		'main/list'			=> array('caption'=> 'Media Library', 'perm' => 'A'),
	//	'main/create' 		=> array('caption'=> "Add New Media", 'perm' => 'A'), // Should be handled in Media-Import.
		'main/import' 		=> array('caption'=> "Media Upload/Import", 'perm' => 'A|A2'),
		'cat/list' 			=> array('caption'=> 'Media Categories', 'perm' => 'A'),
		'cat/create' 		=> array('caption'=> "Create Category", 'perm' => 'A'), // is automatic.
	//	'main/settings' 	=> array('caption'=> LAN_PREFS, 'perm' => 'A'), // legacy
		'main/prefs' 		=> array('caption'=> LAN_PREFS, 'perm' => 'A'),
		'main/avatar'		=> array('caption'=> IMALAN_23, 'perm' => 'A')
	);

/*
	$var['main']['text'] = IMALAN_7;
	$var['main']['link'] = e_SELF;



	$var['avatars']['text'] = IMALAN_23;
	$var['avatars']['link'] = e_SELF."?avatars";


	$var['editor']['text'] = "Image Manipulation (future release)";
	$var['editor']['link'] = e_SELF."?editor";*/



	protected $adminMenuAliases = array(
		'main/edit'	=> 'main/list'
	);

	protected $menuTitle = LAN_MEDIAMANAGER;

}

class media_cat_ui extends e_admin_ui
{
		protected $pluginTitle	= 'Media Categories';
		protected $pluginName	= 'core';
		protected $table 		= "core_media_cat";
		protected $pid			= "media_cat_id";
		protected $perPage = 0; //no limit
		protected $batchDelete = false;
		
		public 	$ownerCount = array();
	//	protected $listQry = "SELECT * FROM #faq_info"; // without any Order or Limit.
	//	protected $editQry = "SELECT * FROM #faq_info WHERE faq_info_id = {ID}";

		protected $fields = array(
			//'checkboxes'				=> array('title'=> '',				'type' => null, 			'width' =>'5%', 'forced'=> TRUE, 'thclass'=>'center', 'class'=>'center'),
			'media_cat_id'			=> array('title'=> LAN_ID,			'type' => 'number',			'width' =>'5%', 'forced'=> TRUE, 'readonly'=>TRUE),
         	'media_cat_image' 		=> array('title'=> LAN_IMAGE,		'type' => 'image', 			'data' => 'str',		'width' => '100px',	'thclass' => 'center', 'class'=>'center', 'readParms'=>'thumb=60&thumb_urlraw=0&thumb_aw=60','readonly'=>FALSE,	'batch' => FALSE, 'filter'=>FALSE),			       	
         	'media_cat_owner' 		=> array('title'=> "Owner",			'type' => 'dropdown',			'width' => 'auto', 'thclass' => 'left', 'readonly'=>FALSE),
			'media_cat_category' 	=> array('title'=> LAN_CATEGORY,	'type' => 'text',			'width' => 'auto', 'thclass' => 'left', 'readonly'=>TRUE),		
			'media_cat_title' 		=> array('title'=> LAN_TITLE,		'type' => 'text',			'width' => 'auto', 'thclass' => 'left', 'readonly'=>FALSE),
         	'media_cat_diz' 		=> array('title'=> LAN_DESCRIPTION,	'type' => 'bbarea',			'width' => '30%', 'readParms' => 'expand=...&truncate=150&bb=1','readonly'=>FALSE), // Display name
			'media_cat_class' 		=> array('title'=> LAN_VISIBILITY,	'type' => 'userclass',		'width' => 'auto', 'data' => 'int'),
			'media_cat_order' 		=> array('title'=> LAN_ORDER,		'type' => 'text',			'width' => '5%', 'thclass' => 'right', 'class'=> 'right' ),										
			'options' 				=> array('title'=> LAN_OPTIONS,		'type' => null,				'width' => '10%', 'forced'=>TRUE, 'thclass' => 'center last', 'class' => 'center')
		);

	function init()
	{
		$this->fields['media_cat_owner']['writeParms'] = array(
			"_common" 	=> "_common",
			"_icon"		=> "_icon",
			"news"		=> "news",	
			"page"		=> "page",
			"gallery"	=> "gallery",
			"download"	=> "download"
					
		);
		
		$sql = e107::getDb();
		$sql->db_Select_gen("SELECT media_cat_owner, count(media_cat_id) as number FROM `#core_media_cat` GROUP BY media_cat_owner");
		while($row = $sql->db_Fetch())	
		{
			$this->ownerCount[$row['media_cat_owner']] = $row['number'];		
		}
		
	}
	
	public function beforeCreate($new_data)
	{

		//$replace = array("_"," ","'",'"',"."); //FIXME Improve
		//$new_data['media_cat_category'] = str_replace($replace,"-",$new_data['media_cat_category']);
		
		$increment = ($this->ownerCount[$new_data['media_cat_owner']] +1);
		$new_data['media_cat_category'] = $new_data['media_cat_owner']."_".$increment;
	
		return $new_data;
	}
	
	
	public function beforeUpdate($new_data, $old_data, $id)
	{
		$mes = e107::getMessage();
	
		if($new_data['media_cat_owner']	!= "gallery")
		{
			$mes->addError("Modification is not permitted");
			return FALSE;
		}
		
		return $new_data;
	}

}

class media_cat_form_ui extends e_admin_form_ui
{

}




class media_form_ui extends e_admin_form_ui
{

	function init()
	{
		/*$sql = e107::getDb();
	//	$sql->db_Select_gen("SELECT media_cat_title, media_title_nick FROM #core_media as m LEFT JOIN #core_media_cat as c ON m.media_category = c.media_cat_owner GROUP BY m.media_category");
		$sql->db_Select_gen("SELECT media_cat_title, media_cat_owner FROM #core_media_cat");
		while($row = $sql->db_Fetch())
		{
			$cat = $row['media_cat_owner'];
			$this->cats[$cat] = $row['media_cat_title'];
		}
		asort($this->cats);*/
				
		if(varset($_POST['multiselect']) && varset($_POST['e__execute_batch']) && (varset($_POST['etrigger_batch']) == 'options__rotate_cw' || varset($_POST['etrigger_batch']) == 'options__rotate_ccw'))
		{
			$type = str_replace('options__','',$_POST['etrigger_batch']);
			$ids = implode(",",$_POST['multiselect']);
			$this->rotateImages($ids,$type);
		}
		
	}
	
	function resize_method($curval)
	{
		$frm = e107::getForm();
		
		$options = array(
			'gd1' => 'gd1',
			'gd2' => 'gd2',
			'ImageMagick' => 'ImageMagick'
		);
		
		return $frm->selectbox('resize_method',$options,$curval)."<div class='field-help'>".IMALAN_4."</div>";										
	}
	
	private function rotateImages($ids,$type)
	{
		$sql = e107::getDb();
		$tp = e107::getParser();
		$mes = e107::getMessage();
		
		$degrees = ($type == 'rotate_cw') ? 270 : 90;
		
	//	$mes->addDebug("Rotate Mode Set: ".$type);
		
		//TODO GIF and PNG rotation. 
		
		if($sql->db_Select("core_media","media_url","media_id IN (".$ids.") AND media_type = 'image/jpeg' "))
		{
			while($row = $sql->db_Fetch())
			{
				$original = $tp->replaceConstants($row['media_url']);

				$mes->addDebug("Attempting to rotate by {$degrees} degrees: ".basename($original));
				
				$source = imagecreatefromjpeg($original);
				
				$rotate = imagerotate($source, $degrees, 0);
				$srch = array(".jpg",".jpeg");
				$cacheFile = str_replace($srch,"",strtolower(basename($original)))."_(.*)\.cache\.bin";
				
				if(imagejpeg($rotate,$original,100))
				{
					$mes->addSuccess("Rotated: ".basename($original));
					e107::getCache()->clearAll('image',$cacheFile);
					$mes->addDebug("Clearing Image cache with mask: ".$cacheFile);
				}			
			}
		}	
	}
	
	
	function resize_dimensions($curval) // ie. never manually resize another image again!
	{
		$text = "";
		$frm = e107::getForm();
		$pref 	= e107::getPref();
		
		$options = array(
			"news-image" 			=> "News Images",
			"news-bbcode" 			=> "News [img] bbcode",
			"page-bbcode" 			=> "Page [img] bbcode",
		//	"featurebox-image" 		=> "Featurebox Images",
		//	"featurebox-bbcode" 	=> "Featurebox [img] bbcode",		
		);
		
		if(vartrue($pref['e_imageresize']))
		{
			foreach($pref['e_imageresize'] as $val)
			{
			
				$options[$k]		= ucfirst($k). " [img] bbcode";
			}
		}
		
		
		
		foreach($options as $key=>$title)
		{
			$valW = vartrue($curval[$key]['w']);
			$valH = vartrue($curval[$key]['h']);
		
			$text .= "<div style='margin-bottomp:8px;text-align:right;width:280px'>".$title.": ";
			$text .= "<input class='e-tip' placeholder='ex. 400' style='text-align:right' type='text' name='resize_dimensions[{$key}][w]' value='$valW' size='5' title='maximum width in pixels' /> X ";
			$text .= "<input class='e-tip' placeholder='ex. 400' style='text-align:right' type='text' name='resize_dimensions[{$key}][h]' value='$valH' size='5' title='maximum height in pixels' />";
			$text .= "</div>";
		//	$text .= $frm->text("resize_dimensions[{$key}]",$val, 5, array('size'=>'5')).$title."<br />";			
		}	
		
	//	$text .= "<div><br />Warning: This feature is experimental.</div>";
		
		return $text;
		
		
	}
	

	function options($parms, $value, $id)
	{
		//return print_a($_GET,true);
		if($value == 'batch')
		{
			return array(
				"rotate_cw"		=> "Rotate 90&deg; cw",
				"rotate_ccw"	=> "Rotate 90&deg; ccw"
			);	
		}
		
		if($_GET['action'] == 'edit')
		{
			return;
		}	
		
		$tagid = $_GET['tagid'];
		$path = $this->getController()->getListModel()->get('media_url');
		$title = $this->getController()->getListModel()->get('media_name');
		$id = $this->getController()->getListModel()->get('media_id');
		$preview = basename($path);
		
		$bbcode = ($_GET['bbcode']=='file')  ? "file" : "";
	//	$save = ($_GET['bbcode']!='file')  ? "e-dialog-save" : "";
	// e-dialog-close
		$text = $this->renderValue('options',$value,'',$id);
	
		if($_GET['action'] == 'dialog')
		{		
			$text .= "<input type='button' value='Select' class='e-media-select e-dialog-save e-dialog-close' data-id='{$id}' data-name=\"".$title."\" data-target='{$tagid}' data-bbcode='{$bbcode}' data-path='{$path}' data-preview='{$preview}' title=\"".$title."\"  />";
		}
		
		return $text;
		
	}
	




	function media_category($curVal,$mode) // not really necessary since we can use 'dropdown' - but just an example of a custom function.
	{
		
		$curVal = explode(",",$curVal);
		
		if($mode == 'read')
		{
			return $this->getController()->getMediaCategory($curVal);
			//return $this->cats[$curVal];
		}

		if($mode == 'batch') // Custom Batch List for release_type
		{
			return $this->getController()->getMediaCategory();
		}

		if($mode == 'filter') // Custom Filter List for release_type
		{
			return $this->getController()->getMediaCategory();
		}


		$text = "<select class='tbox e-multiselect' name='media_category[]' multiple='multiple'>";
		$cats = $this->getController()->getMediaCategory();
		
		foreach($cats as $key => $val)
		{
			$selected = (in_array($key,$curVal)) ? "selected='selected'" : "";
			$text .= "<option value='{$key}' {$selected}>".$val."</option>\n";
		}
		$text .= "</select>";
		return $text;
	}
}

class media_admin_ui extends e_admin_ui
{

		protected $pluginTitle = LAN_MEDIAMANAGER;
		protected $pluginName = 'core';
		protected $table = "core_media";

		protected $listQry = "SELECT m.*,u.user_id,u.user_name FROM #core_media AS m LEFT JOIN #user AS u ON m.media_author = u.user_id "; // without any Order or Limit.

	//	//protected $editQry = "SELECT * FROM #comments WHERE comment_id = {ID}";

		protected $pid = "media_id";
		protected $perPage = 10;
		protected $batchDelete = true;
	//	protected $defaultOrder = 'desc';
		protected $listOrder = 'm.media_id desc'; // show newest images first. 

		//TODO - finish 'user' type, set 'data' to all editable fields, set 'noedit' for all non-editable fields
    	/*
    	 * We need a column with a preview that is generated from the path of another field.
    	 * ie. the preview column should show a thumbnail which is generated from the media_url column.
    	 * It needs to also take into consideration the type of media (image, video etc) which comes from another field.
    	 */

		protected $fields = array(
			'checkboxes'			=> array('title'=> '',				'type' => null,			'data'=> null,		'width' =>'5%', 'forced'=> TRUE, 'thclass'=>'center', 'class'=>'center'),
			'media_id'				=> array('title'=> LAN_ID,			'type' => 'number',		'data'=> 'int',		'width' =>'5%', 'forced'=> TRUE, 'nolist'=>TRUE),
      		'media_url' 			=> array('title'=> 'Preview',		'type' => 'image',		'data'=> 'str',		'thclass' => 'center', 'class'=>'center', 'readParms'=>'thumb=60&thumb_urlraw=0&thumb_aw=60','readonly'=>TRUE, 'writeParms'=>'thumb=180&thumb_urlraw=0&thumb_aw=180',	'width' => '110px'),
			'media_category' 		=> array('title'=> LAN_CATEGORY,	'type' => 'method',		'data'=> 'comma',	'width' => 'auto', 'filter' => true, 'batch' => true,'writeParms'=>'multiple=1'),
			
		// Upload should be managed completely separately via upload-handler.
       	//	'media_upload' 			=> array('title'=> "Upload File",	'type' => 'upload',		'data'=> false,		'readParms' => 'hidden', 'writeParms' => 'disable_button=1', 'width' => '10%', 'nolist' => true),
			'media_name' 			=> array('title'=> LAN_TITLE,		'type' => 'text',		'data'=> 'str',		'width' => 'auto'),
			'media_caption' 		=> array('title'=> "Caption",		'type' => 'text',		'data'=> 'str',		'width' => 'auto'),
         	// media_description is type = textarea until bbarea can be reduced to not include youtube etc
         	'media_description' 	=> array('title'=> LAN_DESCRIPTION,	'type' => 'textarea',		'data'=> 'str',		'width' => 'auto', 'thclass' => 'left first', 'readParms' => 'truncate=100', 'writeParms' => 'counter=0'),
         	'media_type' 			=> array('title'=> "Mime Type",		'type' => 'text',		'data'=> 'str',		'width' => 'auto', 'noedit'=>TRUE),
			'media_author' 			=> array('title'=> LAN_USER,		'type' => 'user',		'data'=> 'int', 	'width' => 'auto', 'thclass' => 'center', 'class'=>'center','readParms' => 'link=1', 'filter' => true, 'batch' => true, 'noedit'=>TRUE	),
			'media_datestamp' 		=> array('title'=> LAN_DATESTAMP,	'type' => 'datestamp',	'data'=> 'int',		'width' => '10%', 'noedit'=>TRUE),	// User date
          	'media_size' 			=> array('title'=> "Size",			'type' => 'number',		'data'=> 'int',		'width' => 'auto', 'noedit'=>TRUE),
			'media_dimensions' 		=> array('title'=> "Dimensions",	'type' => 'text',		'data'=> 'str',		'width' => '5%', 'readonly'=>TRUE, 'class'=>'nowrap','noedit'=>TRUE),
			'media_userclass' 		=> array('title'=> LAN_USERCLASS,	'type' => 'userclass',	'data'=> 'str',		'width' => '10%', 'thclass' => 'center','filter'=>TRUE,'batch'=>TRUE ),
			'media_tags' 			=> array('title'=> "Tags/Keywords",	'type' => 'text',		'data'=> 'str',		'width' => '10%',  'filter'=>TRUE,'batch'=>TRUE ),
			'media_usedby' 			=> array('title'=> '',				'type' => 'text',		'data'=> 'text', 	'width' => 'auto', 'thclass' => 'center', 'class'=>'center', 'nolist'=>true, 'readonly'=>TRUE	),

			'options' 				=> array('title'=> LAN_OPTIONS,		'type' => 'method',			'data'=> null,		'forced'=>TRUE, 'width' => '10%', 'thclass' => 'center last', 'class' => 'center', 'batch'=>true, 'noedit'=>true)
		);


		protected $mimePaths = array(
				'text'			=> e_MEDIA_FILE,
				'multipart'		=> e_MEDIA_FILE,
				'application'	=> e_MEDIA_FILE,
				'audio'			=> e_MEDIA_AUDIO,
				'image'			=> e_MEDIA_IMAGE,
				'video'			=> e_MEDIA_VIDEO,
				'other'			=> e_MEDIA_FILE
		);
	//	protected $fieldpref = array('checkboxes','media_url', 'media_id', 'media_thumb', 'media_title', 'media_caption', 'media_description', 'media_category', 'media_datestamp','media_userclass', 'options');




	
	protected $prefs = array(
		'image_post'	   				=> array('title'=> IMALAN_1, 'type'=>'boolean', 'data'=>'int', 'writeParms'=>'help=IMALAN_2'),
		'image_post_class' 				=> array('title'=> IMALAN_10, 'type' => 'userclass', 'data'=>'int', 'writeParms'=>'help=IMALAN_11&classlist=public,guest,nobody,member,admin,main,classes' ),
		'image_post_disabled_method'	=> array('title'=> IMALAN_12, 'type' => 'boolean','writeParms'=>'enabled=IMALAN_15&disabled=IMALAN_14'),
		'resize_method'					=> array('title'=> IMALAN_3, 'type'=>'method', 'data'=>'str'),
		'im_width'						=> array('title'=> "Avatar Width", 'type'=>'text', 'data'=>'int', 'writeParms'=>'help=Avatar images will be constrained to these dimensions (in pixels)'), //TODO LAN
		'im_height'						=> array('title'=> "Avatar Height", 'type'=>'text', 'data'=>'int', 'writeParms'=>'help=Avatar images will be constrained to these dimensions (in pixels)'),
		'resize_dimensions'				=> array('title'=> "Resize-Image Dimensions", 'type'=>'method', 'data'=>'str'),
		
		'watermark_activate'			=> array('title'=> 'Watermark Activation', 'type' => 'text', 'data' => 'str', 'help'=>'All images with a width or height greater than this value will be given a watermark during resizing.'), // 'validate' => 'regex', 'rule' => '#^[\d]+$#i', 'help' => 'allowed characters are a-zA-Z and underscore')),						
		'watermark_text'				=> array('title'=> 'Watermark Text', 'type' => 'text', 'data' => 'str', 'help'=>'Optional Watermark Text'), // 'validate' => 'regex', 'rule' => '#^[\d]+$#i', 'help' => 'allowed characters are a-zA-Z and underscore')),				
		'watermark_font'				=> array('title'=> 'Watermark Font', 'type' => 'dropdown', 'data' => 'str', 'help'=>'Optional Watermark Font. Upload more .ttf fonts to the /fonts folder in your theme directory.'), // 'validate' => 'regex', 'rule' => '#^[\d]+$#i', 'help' => 'allowed characters are a-zA-Z and underscore')),				
		'watermark_size'				=> array('title'=> 'Watermark Size', 'type' => 'text', 'data' => 'int', 'help'=>'Size of the font in pts'), // 'validate' => 'regex', 'rule' => '#^[\d]+$#i', 'help' => 'allowed characters are a-zA-Z and underscore')),						
		
		'watermark_pos'					=> array('title'=> 'Watermark Position', 'type' => 'dropdown', 'data' => 'str', 'help'=>'Watermark Position'), // 'validate' => 'regex', 'rule' => '#^[\d]+$#i', 'help' => 'allowed characters are a-zA-Z and underscore')),				
		'watermark_margin'				=> array('title'=> 'Watermark Margin', 'type' => 'text', 'data' => 'int', 'help'=>'The distance that watermark will appear from the edge of the image.'), // 'validate' => 'regex', 'rule' => '#^[\d]+$#i', 'help' => 'allowed characters are a-zA-Z and underscore')),						
		
		'watermark_color'				=> array('title'=> 'Watermark Color', 'type' => 'text', 'data' => 'str', 'help'=>'Color of the watermark eg. 000000'), // 'validate' => 'regex', 'rule' => '#^[\d]+$#i', 'help' => 'allowed characters are a-zA-Z and underscore')),						
		'watermark_shadowcolor'			=> array('title'=> 'Watermark Shadow-Color', 'type' => 'text', 'data' => 'str', 'help'=>'Shadow Color of the watermark eg. ffffff '), // 'validate' => 'regex', 'rule' => '#^[\d]+$#i', 'help' => 'allowed characters are a-zA-Z and underscore')),						
	
		'watermark_opacity'				=> array('title'=> 'Watermark Opacity', 'type' => 'text', 'data' => 'int', 'help'=>'Enter a number between 1 and 100'), // 'validate' => 'regex', 'rule' => '#^[\d]+$#i', 'help' => 'allowed characters are a-zA-Z and underscore')),				
	
	);
	

	

	/*

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
							</tr>";
							
							list($img_import_w,$img_import_h) = explode("x",$pref['img_import_resize']);
							
							//TODO LANS
							$text .= "						
							<tr>
								<td class='label'>Resize images during media import<div class='label-note'>Leave empty to disable</div></td>
								<td class='control'>
									".$frm->text('img_import_resize_w', $img_import_w,4)."px X ".$frm->text('img_import_resize_h', $img_import_h,4)."px
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
							</tr>";
	*/
	protected $cats = array();
	protected $owner = array();
	protected $ownercats = array();
	

	function init()
	{
		$sql = e107::getDb();
	//	$sql->db_Select_gen("SELECT media_cat_title, media_title_nick FROM #core_media as m LEFT JOIN #core_media_cat as c ON m.media_category = c.media_cat_owner GROUP BY m.media_category");
		$sql->db_Select_gen("SELECT media_cat_title, media_cat_owner, media_cat_category FROM #core_media_cat");
		while($row = $sql->db_Fetch())
		{
			$cat = $row['media_cat_category'];
			$owner = $row['media_cat_owner'];
			$this->owner[$owner] = $owner;
			$this->ownercats[$owner.'|'.$cat] = $row['media_cat_title'];
			$this->cats[$cat] = $row['media_cat_title'];
		}
		asort($this->cats);
				
		$pref 	= e107::getPref();
		$tp 	= e107::getParser();
		$fl 	= e107::getFile();
		$path 	= e_THEME.$pref['sitetheme']."/fonts/";
	
		$fDir = $fl->get_files(e_THEME.$pref['sitetheme']."/fonts/",".ttf",'',2);
		$fonts = array(0=>'None');
		foreach($fDir as $f)
		{			
			$id = $tp->createConstants($f['path'].$f['fname'],'rel');
			$fonts[$id] = $f['fname'];	
		}
		
		
		$this->prefs['watermark_font']['writeParms'] 		= $fonts;	
		$this->prefs['watermark_font']['readParms'] 		= $fonts;	
		
		$wm_pos = array(
			'BR'	=> "Bottom Right",
			'BL'	=> "Bottom Left",
			'TR'	=> "Top Right",
			'TL'	=> "Top Left",
			'C'		=> "Center",
			'R'		=> "Right",
			'L'		=> "Left",
			'T'		=> "Top",
			'B'		=> "Bottom",
			'*'		=> "Tile"
		);
		
		$this->prefs['watermark_pos']['writeParms'] 		= $wm_pos;	
		$this->prefs['watermark_pos']['readParms'] 			= $wm_pos;	
		
		//FIXME TODO - clear thumbnail cache when prefs are saved/updated. 
		// e107::getCache()->clearAll('image');
		
		
		


		if(varset($_POST['batch_import_selected']))
		{
			$this->batchImport();
		}
		
		if(varset($_POST['batch_import_delete']))
		{
			$this->batchDelete();
		}

		if(varset($_POST['update_options']))
		{
			$this->updateSettings();
		}
		
		if($this->getQuery('mode') == 'dialog')//TODO Check this actually does something, as it was changed to 'action'. 
		{
			if(!ADMIN){ exit; }
			
			$cat = $_GET['for'];
			if(!isset($this->cats[$cat]))
			{
				return;
			}
			
			$this->listQry = "SELECT m.*,u.user_id,u.user_name FROM #core_media AS m LEFT JOIN #user AS u ON m.media_author = u.user_id WHERE media_category = '".$cat."' "; // without any Order or Limit.
			
			unset($this->fields['checkboxes']);
			$this->fields['options']['type'] = 'method';
			$this->fields['media_category']['nolist'] = true;
			$this->fields['media_userclass']['nolist'] = true;
			$this->fields['media_dimensions']['nolist'] = true;
			$this->fields['media_description']['nolist'] = true;
			$this->fields['media_type']['nolist'] = true;
			
			foreach($this->fields as $k=>$v)
			{
				$this->fields[$k]['filter'] = false;	
			}	
		}
		
		
		

		if($this->getQuery('iframe'))
		{
			
			
			/*
			if($this->getQuery('bbcode'))
			{
				//TODO this is not really used when jquery is running. 
				e107::getJS()->headerInline("
				
				// Send the generated IMG bbcode back to the textarea/window 
				function saveBB()
				{
								
							
					var add = document.getElementById('bbcode_holder').value;		
					var html = document.getElementById('html_holder').value;			
				//	tinyMCE.execCommand('mceInsertContent',false,add);
					tinyMCE.execCommand('mceInsertRawHTML',false,html);
			   		tinyMCEPopup.close();
					
					
					//addtext(add);
					//parent.e107Widgets.DialogManagerDefault.getWindow('e-dialog').close();
					return false;
				}
				
				");
				
			}
			*/
					
 			$this->getResponse()->setIframeMod(); // disable header/footer menus etc. 
 			if(!$this->getQuery('for'))
			{
				$this->setPosted('media_category', "_common");
				$this->getModel()->set('media_category', "_common");
			}
			elseif($this->getMediaCategory($this->getQuery('for')))
			{
				$this->setPosted('media_category', $this->getQuery('for'));
				if(!$this->getId())
				{
					$this->getModel()->set('media_category', $this->getQuery('for'));
				}	
			}
			
		
		}
// 
		// if($this->getQuery('for') && $this->getMediaCategory($this->getQuery('for')))
		// {
// 			
			// $this->setPosted('media_category', $this->getQuery('for'));
			// if(!$this->getId())
			// {
				// $this->getModel()->set('media_category', $this->getQuery('for'));
			// }
		// }
// 		
	}

	function dialogPage() // Popup dialogPage for Image Selection. 
	{
		$cat = $this->getQuery('for');		
		$file		= (substr($cat,-5) == "_file") ? TRUE : FALSE;
		
		if($file)
		{
			
			echo $this->getUI()->getList();

			return;
		}
			
		
	//	$this->getModel()->setAction('create');
	//	$this->getUI()->getController()->getRequest()->setAction('create');
	//$this->setAction('create');;
	
		if($_POST['etrigger_submit'])
		{		
		//	$data = $this->beforeCreate($_POST);
		//	e107::getDb()->db_Insert('core_media',$data); // Replace with Generic (needs parm sent)	
		}
		echo $this->imageSelectUpload();	
	}
	
	function uploadPage()
	{
		if(!ADMIN){ exit; } //TODO check for upload-access in perms. 
		
		// if 'for' has no value, files are placed in /temp and not added to the db. 
		$text = '<div id="uploader" rel="'.e_JS.'plupload/upload.php?for='.$this->getQuery('for').'">
	        <p>No HTML5 support.</p>
		</div>';
	    
		return $text;
	}


	function imageSelectUpload() 
	{
		$frm = e107::getForm();
		$bbcodeMode = ($this->getQuery('bbcode')=='img') ? 'bbcode=img' : FALSE;
						
		$text = "
			<div class='admintabs' id='tab-container'>
			<ul class='e-tabs' id='core-emote-tabs'>
				<li id='tab-select'><a href='#core-media-select'>Choose from Library</a></li>
				<li id='tab-upload'><a href='#core-media-upload'>Upload a File</a></li>";
		
		if($bbcodeMode)
		{
			$text .= "<li id='tab-style'><a href='#core-media-style'>Appearance</a></li>\n";	
		}
		
		if($_GET['from'])
		{
			$bbcodeMode .= "&from=".intval($_GET['from']);
		}
		
				
		$text .= "
			</ul>
			<fieldset id='core-media-select'>
			<legend>Library</legend>
			<table class='adminedit'>
			<tbody><tr><td>
			";
			
		$tag = ($bbcodeMode) ? "" : $this->getQuery('tagid');
		
		$text .= e107::getMedia()->mediaSelect($this->getQuery('for'),$this->getQuery('tagid'),$bbcodeMode); // eg. news, news-thumbnail	
			
		$text .= "
			</td></tr>
			</tbody></table>
			</fieldset>
			
			<fieldset id='core-media-upload'>
			<legend>Upload</legend>";
			
		$this->fields['media_category']['readonly']	= TRUE;
		$this->fields['media_url']['noedit'] 		= TRUE;
		$this->fields['media_userclass']['noedit']	= TRUE;
		
		$text .=  $this->uploadPage(); // To test upload script with plupload
	//	$text .=  $this->CreatePage(); // comment me out to test plupload
				
		$text .= "	
			</fieldset>";
		
		/* In BBCODE-Mode this dialog rerturns an [img] bbcode to the 'tagid' container with the appropriate parms generated. 
		 * eg. [img style=float:left;margin-right:3px]{e_MEDIA_IMAGE}someimage.jpg[/img]
		 * Then the dialog may be used as a bbcode img popup and within TinyMce also. 
		 * 
		 */
		
		if($bbcodeMode)
		{
			$text .= "<fieldset id='core-media-style'>
				<legend>Appearance</legend>
				<table class='adminedit'>
				<colgroup>
					<col class='col-label' />
					<col class='col-control' />
				</colgroup>
				<tbody>
					<tr>
						<td>Dimensions: </td>
						<td>
						<input type='text' class='e-media-attribute' id='width' name='width' size='4' style='width:50px' value='' /> px
						 X <input type='text' class='e-media-attribute' id='height' name='height' size='4' style='width:50px' value=''  /> px
						</td>
					</tr>
			
					<tr>
						<td>Text flow: </td>
						<td>".$frm->selectbox('float', array('default'=>'Default','left'=>'Right','right'=>"Left"))."</td>
					</tr>
					
					<tr>
						<td>Margin-Left: </td>
						<td><input class='e-media-attribute' type='text' id='margin-left' name='margin_left' value='' /></td>
					</tr>
					
					<tr>
						<td>Margin-Right: </td>
						<td><input class='e-media-attribute' type='text' id='margin-right' name='margin_right' value=''  /></td>
					</tr>
					
					<tr>
						<td>Margin-Top: </td>
						<td><input class='e-media-attribute' type='text' id='margin-top' name='margin_top' value=''  /></td>
					</tr>
					
					<tr>
						<td>Margin-Bottom: </td>
						<td><input class='e-media-attribute' type='text' id='margin-bottom' name='margin_bottom' value=''  /></td>
					</tr>
		
			</tbody></table>
			<table><tr><td>Preview<br /></td></tr>
			<tr><td style='text-align:center'>
			<img id='preview' src='".e_IMAGE_ABS."generic/blank.gif' style='border:1px solid silver; min-width:220px; min-height:180px;' />
			
			</td></tr></table>
			</fieldset>";
		}	
		$text .= "</div>";
		
		// For BBCODE mode. //TODO image-float. 
		if($bbcodeMode)
		{
			
						
			$text .= "<div style='text-align:right;padding:5px'>
			
			<button type='submit' class='submit e-dialog-save e-dialog-close' data-target='".$this->getQuery('tagid')."' name='save_image' value='Save it'  >
			<span>Save</span>
			</button>
			<button type='submit' class='submit e-dialog-close' name='cancel_image' value='Cancel' >
			<span>Cancel</span>
			</button>
			</div>";
			
			// TODO to eventually be hidden. 
			$text .= "bbcode: <input type='text' readonly='readonly' style='border:0px; width:700px' id='bbcode_holder' name='bbcode_holder' value='' />
			<input title='html' type='text' style='width:800px' id='html_holder' name='html_holder' value='' />
			<input title='src' type='text' style='width:600px' id='src' name='src' value='' />
			<input title='path' type='text' style='width:600px' id='path' name='path' value='' />				
			";		
						
		}
		
		return $text;
	}






	function importPage()
	{
		$this->batchImportForm();
	}

	function settingsPage()
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
		
		if($pref['resize_method'] == "ImageMagick" && (!vartrue(e107::getFolder('imagemagick'))))
		{
			
			$mes->addWarning('Please add: <b>$IMAGEMAGICK_DIRECTORY="'.$pref['im_path'].'";</b> to your e107_config.php file');	
		}
		
			
		//$IM_NOTE = "";
		$im_path = vartrue(e107::getFolder('imagemagick'));
		if($im_path != "")
		{
		  $im_file = $im_path.'convert';
			if(!file_exists($im_file))
			{
				//$IM_NOTE = "<span class='error'>".IMALAN_52."</span>";
				$mes->addWarning(IMALAN_52);
			}
			else
			{
				$cmd = "{$im_file} -version";
				$tmp = `$cmd`;
				if(strpos($tmp, "ImageMagick") === FALSE)
				{
					//$IM_NOTE = "<span class='error'>".IMALAN_53."</span>";
					$mes->addWarning(IMALAN_53);
				}
			}
		}
	

	
	
	
	
		$text = "
			<form method='post' action='".e_SELF."?".e_QUERY."'>
				<fieldset id='core-image-settings'>
					<legend class='e-hideme'>".IMALAN_7."</legend>
					<table class='adminform'>
						<colgroup>
							<col class='col-label' />
							<col class='col-control' />
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
							</tr>";
							
							list($img_import_w,$img_import_h) = explode("x",$pref['img_import_resize']);
							
							//TODO LANS
							$text .= "						
							<tr>
								<td class='label'>Resize images during media import<div class='label-note'>Leave empty to disable</div></td>
								<td class='control'>
									".$frm->text('img_import_resize_w', $img_import_w,4)."px X ".$frm->text('img_import_resize_h', $img_import_h,4)."px
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
							</tr>";
				/*			
				$text .= "
							// Removed to prevent mod_security blocks, and show only when relevant (non-GD2 users)
							<tr>
								<td class='label'>".IMALAN_5."<div class='label-note'>{$IM_NOTE}</div></td>
								<td class='control'>
									".$frm->text('im_path', $pref['im_path'])."
									<div class='field-help'>".IMALAN_6."</div>
								</td>
							</tr>";		
							
				// Removed as IE6 should no longer be supported. A 3rd-party plugin can be made for this functionality if really needed. 			
				
							
				
							$text .= "
										<tr>
											<td class='label'>".IMALAN_34."
											</td>
											<td class='control'>
												<div class='auto-toggle-area autocheck'>
													".$frm->checkbox('enable_png_image_fix', 1, ($pref['enable_png_image_fix']))."
													<div class='field-help'>".IMALAN_35."</div>
												</div>
											</td>
										</tr>";
										
							*/
							
							
			$text .= "
	
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
	
			echo $mes->render().$text;
			return;
		//	$ns->tablerender(LAN_MEDIAMANAGER." :: ".IMALAN_7, $mes->render().$text);
	}

	function avatarPage()
	{
		show_avatars();
	}

	function iconsPage()
	{
		// $this->icon_editor();
	}



	/**
	 * Invoked just before item create event
	 * @return array
	 */
	public function beforeCreate($new_data)
	{
		// print_a($_POST);
		// return data to be merged with posted model data
		$this->getRequest()->setPosted('media_upload', null);
		//$dataFields = $this->getModel()->getDataFields();
		//unset($dataFields['media_upload']);
		//$this->getModel()->setDataFields($dataFields);
		if($this->getQuery('for') && $this->getMediaCategory($this->getQuery('for')))
		{
			$new_data['media_category'] = $this->getQuery('for');
		}
		return $this->observeUploaded($new_data);
	}

	/**
	 * Same as beforeCreate() but invoked on edit
	 * @return TBD
	 */
	public function beforeUpdate($new_data, $old_data, $id)
	{
		// return data to be merged with posted model data
	//	$new_data['media_category'] = implode(",",$new_data['media_category']);
		$this->fields['media_category']['data'] = 'str'; //XXX Quick fix for 'comma' incompatibility in Db-Update routines. 
		return $new_data;
	//	return $this->observeUploaded($new_data);
	}

	public function mediaData($sc_path)
	{
		if(!$sc_path) return array();
		$path = e107::getParser()->replaceConstants($sc_path);
		$info = e107::getFile()->get_file_info($path,true);
		return array(
			'media_type'		=> $info['mime'],
			'media_datestamp'	=> time(),
			'media_url'			=> e107::getParser()->createConstants($path, 'rel'),
			'media_size'		=> filesize($path),
			'media_author'		=> USERID,
			'media_usedby'		=> '',
			'media_tags'		=> '',
			'media_dimensions'	=> $info['img-width']." x ".$info['img-height']
		);
	}

	// XXX - strict mysql error on Create without UPLOAD!
	function observeUploaded($new_data)
	{
		$fl = e107::getFile();

		$mes = e107::getMessage();

		if(vartrue($_FILES['file_userfile'])) // CREATE
		{
			
			$pref['upload_storagetype'] = "1";
			require_once(e_HANDLER."upload_handler.php"); //TODO - still not a class!
			$uploaded = process_uploaded_files(e_MEDIA.'temp/'); //FIXME doesn't handle xxx.JPG (uppercase)
			$upload = array_shift($uploaded);
			if(vartrue($upload['error']))
			{
				$mes->addError($upload['message']);
				return FALSE;
			}	

				if(!$typePath = $this->getPath($upload['type']))
				{
					$mes->addError("Couldn't generated path from upload data");
					return FALSE;
				}
				$mes->addDebug(print_a($upload,TRUE));

				$oldpath = e_MEDIA."temp/".$upload['name'];
				$newpath = $this->checkDupe($oldpath,$typePath.'/'.$upload['name']);

				if(!rename($oldpath, e_MEDIA.$newpath))
				{
					$mes->add("Couldn't move file from ".$oldpath." to ".$newpath, E_MESSAGE_ERROR);
					return FALSE;
				};

				$img_data = $this->mediaData($newpath); // Basic File Info only
				
				$img_data['media_name'] 		= $new_data['name'];
				$img_data['media_caption'] 		= $new_data['media_caption'];
				$img_data['media_category'] 	= $new_data['media_category'];
				$img_data['media_description'] 	= $new_data['media_description'];
				$img_data['media_tags'] 		= $new_data['media_tags'];
				$img_data['media_userclass'] 	= 0;	
				$img_data['media_author']		= USERID;
				
				if(!varset($img_data['media_name']))
				{
					$img_data['media_name'] = $upload['name'];
				}			
				
				$mes->addDebug(print_a($img_data,TRUE));
		
				return $img_data;
		}
		else // Update Only ?
		{
		
			$img_data = $this->mediaData($new_data['media_url']);


			if(!($typePath = $this->getPath($img_data['media_type'])))
			{
				$mes->addError("Couldn't get path ".$typePath);
					
				return FALSE;
			}
			
			$fname = basename($new_data['media_url']);
			// move to the required place
			if(strpos($new_data['media_url'], '{e_MEDIA}temp/') !== FALSE)
			{
				$tp = e107::getParser();
				$oldpath = $tp->replaceConstants($new_data['media_url']);
				$newpath = $this->checkDupe($oldpath,$typePath.'/'.$fname);
				
			
				if(!rename($oldpath, $newpath))
				{
					$mes->add("Couldn't move file from ".$oldpath." to ".str_replace('../', '', $newpath), E_MESSAGE_ERROR);
					return FALSE;
				}
				$img_data['media_url'] = $tp->createConstants($newpath, 'rel');
			}

			if(!varset($new_data['media_name']))
			{
				$img_data['media_name'] = basename($new_data['media_url']);
			}


			return $img_data;
		}
		
		
	}

	// Check for existing image path in db and rename if found. 
	function checkDupe($oldpath,$newpath)
	{
		$mes = e107::getMessage();	
		$tp = e107::getParser();
		$f = e107::getFile()->get_file_info($oldpath,TRUE);
		
		$mes->addDebug("checkDupe(): newpath=".$newpath."<br />oldpath=".$oldpath."<br />".print_r($upload,TRUE));
		if(file_exists($newpath) || e107::getDb()->db_Select("core_media","*","media_url = '".$tp->createConstants($newpath,'rel')."' LIMIT 1") )
		{
			$mes->addWarning($newpath." already exists.");	
			$file = $f['pathinfo']['filename']."_.".$f['pathinfo']['extension'];
			$newpath = $this->getPath($f['mime']).'/'.$file;
			return false;			
		}
		
		return $newpath;	
	}


	function beforeDelete($data, $id) // call before 'delete' is executed. - return false to prevent delete execution (e.g. some dependencies check)
	{
		return true;
	}

	function afterDelete($deleted_data, $id) // call after 'delete' is successfully executed. - delete the file with the db record (optional pref)
	{

	}

	function getPath($mime)
	{
		$mes = e107::getMessage();

		list($pmime,$tmp) = explode('/',$mime);

		if(!vartrue($this->mimePaths[$pmime]))
		{
			$mes->add("Couldn't detect mime-type($mime). Upload failed.", E_MESSAGE_ERROR);
			return FALSE;
		}

		$dir = $this->mimePaths[$pmime].date("Y-m");

		if(!is_dir($dir))
		{
			if(!mkdir($dir, 0755))
			{
				$mes->add("Couldn't create folder ($dir).", E_MESSAGE_ERROR);
				return FALSE;
			};
		}
		return $dir;
	}

	function batchImportForm()
	{
		$frm = e107::getForm();
		$mes = e107::getMessage();
		$fl = e107::getFile();

		$fl->setFileInfo('all');
		$rejectArray = array('^\.ftpquota$','^index\.html$','^null\.txt$','\.bak$','^.tmp','.*\.xml$','^\.$','^\.\.$','^\/$','^CVS$','thumbs\.db','.*\._$','^\.htaccess$','index\.html','null\.txt');
		$fl->setFileFilter($rejectArray);
		$files = $fl->get_files(e_MEDIA."temp/");
		
		e107::js('core','core/admin.js','prototype');


		//TODO Detect XML file, and if found - read that instead of the directory.

		if(!vartrue($_POST['batch_import_selected']))
		{
			$mes->add("Scanning for new media (images, videos, files) in folder: <b> ".e_UPLOAD."</b>", E_MESSAGE_INFO);
		}

		if(!count($files))
		{
			if(!vartrue($_POST['batch_import_selected']))
			{
				$mes->add("No media Found! Please upload some files.", E_MESSAGE_INFO);
			}
			
			$text = $this->uploadPage();
			echo $mes->render().$text;
			return;
		}

			$text = "
				<form method='post' action='".e_SELF."?".e_QUERY."' id='batch_import'>
					<fieldset id='core-mediamanager-batch'>
						<legend class='e-hideme'>".DBLAN_20."</legend>
						<table class='adminlist'>
							<colgroup>
								<col style='width: 5%' />
								<col />
								<col />
								<col />
							</colgroup>
							<thead>
								<tr>
									<th class='center'>".e107::getForm()->checkbox_toggle('e-column-toggle', 'batch_selected')."</th>
									<th class='center' style='width:50px'>Preview</th>
									<th class='center'>".LAN_FILE."</th>
									<th >Title (internal use)</th>
									<th >Caption (seen by public)</th>
									<th >Author</th>
									<th>Mime Type</th>
									<th>File Size</th>
									<th>".LAN_DATESTAMP."</th>
									<th class='center last'>Dimensions</th>
								</tr>
							</thead>
							<tbody>";
		
		$c = 0;
		foreach($files as $f)
		{
			$default = $this->getFileXml($f['fname']);
			$f = $fl->cleanFileName($f,true);
			
			if($f['error'])
			{
				$mes->addWarning($f['fname']." couldn't be renamed. Check file perms.");
			}
				
			
			$text .= "
			
			<tr>
				<td class='center'>".$frm->checkbox("batch_selected[".$c."]",$f['fname'])."</td>
				<td class='center'>".$this->preview($f)."</td>			
				<td>".$f['fname']."</td>
				<td>".$frm->text('batch_import_name['.$c.']', ($_POST['batch_import_name'][$c] ? $_POST['batch_import_name'][$c] : $default['title']))."</td>
				<td><textarea name='batch_import_diz[".$c."]' rows='3' cols='50'>". ($_POST['batch_import_diz'][$c] ? $_POST['batch_import_diz'][$c] : $default['description'])."</textarea></td>
			
				<td><a href='mailto:".$default['authorEmail']."'>".$default['authorName']."</a></td>
				<td>".$f['mime']."</td>
				<td>".$f['fsize']."</td>
				<td>".e107::getDateConvert()->convert_date($f['modified'])."</td>
				<td class='center last'>".$f['img-width']." x ".$f['img-height']."</td>
			</tr>
				
				\n";
				
			$c++;
			$lastMime = $f['mime'];
		}

		// <td>".$frm->textarea('batch_import_diz['.$c.']', ($_POST['batch_import_diz'][$c] ? $_POST['batch_import_diz'][$c] : $default['description']))."</td>

		if(!isset($_POST['batch_category']) && substr($lastMime,0,5)=='image')
		{
			$_POST['batch_category'] = "_common_image";
		}
		
		$text .= "
				</tbody>
						</table>
						<div class='buttons-bar center'>
						Import into Category: ".$frm->selectbox('batch_category',$this->cats, $_POST['batch_category']);
			
		//	$waterMarkPath = e_THEME.e107::getPref('sitetheme')."/images/watermark.png"; // Now performed site-wide dynamically. 				
					
			if(is_readable($waterMarkPath))
			{
		//		$text .= $frm->checkbox_label("Add Watermark", 'batch_import_watermark',1);
			}
						
						$text .= "
						</div>
						<div class='buttons-bar center'>
							".$frm->admin_button('batch_import_selected', "Import Selected Files", 'import')
							.$frm->admin_button('batch_import_delete', "Delete Selected Files", 'delete');				
			$text .= "
						</div>
					</fieldset>
				</form>


			";


		echo $mes->render().$text;
	}



	// Check for matching XML file name and if found, return data from it during import. 
	function getFileXml($imgFile)
	{
		list($file,$ext) = explode(".",$imgFile);
		
		$xmlFile = e_UPLOAD.$file.".xml";
		
		if(is_readable($xmlFile))
		{
			$data = file_get_contents($xmlFile);
			$tmp = preg_match("/<author name=(?:'|\")([^'\"]*)/i",$data,$authorName);
			$tmp = preg_match("/email=(?:'|\")([^'\"]*)/i",$data,$authorEmail);
			$tmp = preg_match("/<title>(.*)<\/title>/i",$data,$title);
			$tmp = preg_match("/<description>(.*)<\/description>/i",$data,$diz);
			
			return array(
				'title'			=> $title[1],
				'description'	=> $diz[1],
				'authorName'	=> $authorName[1],
				'authorEmail'	=> $authorEmail[1]
			);				
		}
			
		return array('title'=>basename($file),'description'=>'','authorName'=>'','authorEmail'=>'');
		
		/*
		Example: matchingfilename.xml (ie. same name as jpg|.gif|.png etc)
		 
		<?xml version='1.0' encoding='utf-8' ?>
		<e107Media>
			<item file='filename.jpg' date='2012-10-25'>
		   		<author name='MyName' url='http://mysite.com' email='email@email.com' />
		   		<title>Title of File</title>
				<description>Description of File</description>
				<category></category>
			</item>  
		</e107Media>

		*/
					
	}



	function deleteFileXml($imgFile)
	{
		list($file,$ext) = explode(".",$imgFile);
		
		$xmlFile = e_UPLOAD.$file.".xml";
		
		if(file_exists($xmlFile))
		{
			unlink($xmlFile);	
		}			
	}

	
	function batchDelete()
	{
		foreach($_POST['batch_selected'] as $key=>$file)
		{
			if(trim($file) == '')
			{
				continue;
			}	
			
			$oldpath = e_MEDIA."temp/".$file;
			if(file_exists($oldpath))
			{
				unlink($oldpath);
			}
		}
	}


	function batchImport()
	{
		$fl = e107::getFile();
		$mes = e107::getMessage();
		$sql = e107::getDb();
		$tp = e107::getParser();
	
		
		if(!count($_POST['batch_selected']))
		{
			$mes->addError("Please check at least one image.");
			return;
		}
		

		require(e_HANDLER.'phpthumb/ThumbLib.inc.php');	// For resizing on import. 
		list($img_import_w,$img_import_h) = explode("x",e107::getPref('img_import_resize'));
		
		if(vartrue($_POST['batch_import_watermark']))
		{
			$WM = TRUE;
			$watermarkPath = e_THEME.e107::getPref('sitetheme')."/images/watermark.png";	
			$watermark = PhpThumbFactory::create($watermarkPath);
		}
		else 
		{
		 	$WM = FALSE; 
		}	

		// Disable resize-on-import and watermark for now. 
		$img_import_w = false;
		$img_import_h = false; 
		
		foreach($_POST['batch_selected'] as $key=>$file)
		{
						
			$oldpath = e_MEDIA."temp/".$file;
			
			// Resize on Import Routine ------------------------
			if(vartrue($img_import_w) && vartrue($img_import_h))
			{
				try
				{
				    $thumb = PhpThumbFactory::create($oldpath);
				    $thumb->setOptions(array('correctPermissions' => true));
				}
				catch (Exception $e)
				{
				     $mes->addError($e->getMessage());
				     continue;
				    // return $this;
				}
				if($WM) // TODO Add watermark prefs for alpha and position. 
				{
					$thumb->resize($img_import_w,$img_import_h)->addWatermark($watermark, 'rightBottom', 30, 0, 0)->save($oldpath); 
			
				}
				else
				{
				 	$thumb->resize($img_import_w,$img_import_h)->save($oldpath); 
				}
				
			
			}
			// End Resize routine. ---------------------

			$f = $fl->get_file_info($oldpath);

			if(!$f['mime'])
			{
				
				$mes->add("Couldn't get file info from : ".$oldpath, E_MESSAGE_WARNING);
				// $mes->add(print_a($f,true), E_MESSAGE_ERROR);
				$f['mime'] = "other/file";
			}

			$newpath = $this->checkDupe($oldpath,$this->getPath($f['mime']).'/'.$file);
			$newname = $tp->toDB($_POST['batch_import_name'][$key]);
			$newdiz = $tp->toDB($_POST['batch_import_diz'][$key]);
			
			$f['fname'] = $file;
			
			/*
				
						if(file_exists($newpath) || $sql->db_Select("core_media","media_url = '".$tp->createConstants($newpath,'rel')."' LIMIT 1") )
						{
							$mes->addWarning($newpath." already exists and was renamed during import.");	
							$file = $f['pathinfo']['filename']."_.".$f['pathinfo']['extension'];
							$newpath = $this->getPath($f['mime']).'/'.$file;						
						}
			*/
			
			
			
			if(rename($oldpath,$newpath))
			{
				$insert = array(
					'media_caption'		=> $newdiz,
					'media_description'	=> '',
					'media_category'	=> $_POST['batch_category'],
					'media_datestamp'	=> $f['modified'],
					'media_url'			=> $tp->createConstants($newpath,'rel'),
					'media_userclass'	=> '0',
					'media_name'		=> $newname,
					'media_author'		=> USERID,
					'media_size'		=> $f['fsize'],
					'media_dimensions'	=> $f['img-width']." x ".$f['img-height'],
					'media_usedby'		=> '',
					'media_tags'		=> '',
					'media_type'		=> $f['mime']
					);


				if($sql->db_Insert("core_media",$insert))
				{
					$mes->add("Importing Media: ".$f['fname'], E_MESSAGE_SUCCESS);
					$this->deleteFileXml($f['fname']);
				}
				else
				{
					rename($newpath,$oldpath);	//move it back.
				}
			}
		}
	}



	function preview($f)
	{
		list($type,$tmp) = explode("/",$f['mime']);

		if($type == 'image')
		{
			$url = e107::getParser()->thumbUrl($f['path'].$f['fname'], 'w=100', true);
			$large = e107::getParser()->thumbUrl($f['path'].$f['fname'], 'w=800', true);
			//echo $url;
			return "<a class='e-dialog' href='".$large."'><img src='".$url."' alt=\"".$f['name']."\" width='100px' /></a>";
		}
		else
		{
			return; //TODO generic icon/image for no preview.
		}
	}

	public function getMediaCategory($id = false)
	{
		if(is_array($id))
		{
			$text = "";
			foreach($id as $val)
			{
				$text .= (isset($this->cats[$val]) ? "<span class='nowrap'>".$this->cats[$val]."</span><br />" : '');			
			}		
			return $text;						
		}

		if($id) return (isset($this->cats[$id]) ? $this->cats[$id] : 0);
		return $this->cats;
	}
	
	
	/*
 * UPDATE IMAGE OPTIONS - MAIN SCREEN
 */
 	function updateSettings()
	{
		global $pref,$admin_log,$tp;
		
		$mes = e107::getMessage();
		
		$tmp = array();
		$tmp['image_post'] = intval($_POST['image_post']);
		$tmp['resize_method'] = $tp->toDB($_POST['resize_method']);
		$tmp['im_path'] = trim($tp->toDB($_POST['im_path']));
		$tmp['image_post_class'] = intval($_POST['image_post_class']);
		$tmp['image_post_disabled_method'] = intval($_POST['image_post_disabled_method']);
		$tmp['enable_png_image_fix'] = intval($_POST['enable_png_image_fix']);
		
		if($_POST['img_import_resize_w'] && $_POST['img_import_resize_h'])
		{
			$tmp['img_import_resize'] = intval($_POST['img_import_resize_w'])."x".intval($_POST['img_import_resize_h']);		
		} 
	
		if ($admin_log->logArrayDiffs($tmp, $pref, 'IMALAN_04'))
		{
			save_prefs();		// Only save if changes
			$mes->add(IMALAN_9, E_MESSAGE_SUCCESS);
		}
		else
		{
			$mes->add(IMALAN_20, E_MESSAGE_INFO);
		}	
	}

	
	

}


new media_admin();

require_once(e_ADMIN."auth.php");

e107::getAdminUI()->runPage();











// -----------------------------------------------------------------------




$action = e_QUERY;


if(varset($_GET['action']) == "icons")
{
	// icon_editor();
}

if(varset($_GET['action']) == "avatars")
{
	// show_avatars();
}

if(varset($_GET['action']) == 'settings')
{
	// main_config();
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


	$avFiles = e107::getFile()->get_files(e_MEDIA."avatars/",".jpg|.png|.gif|.jpeg|.JPG|.GIF|.PNG");

	$dirlist = array();
	
	foreach($avFiles as $f)
	{
		$dirlist[] = $f['fname'];	
	}

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
				$users .= "<a href='".$e107->url->create('user/profile/view', 'name='.$row['user_name'].'&id='.$row['user_id'])."'>{$row['user_name']}</a> <span class='smalltext'>(".($row['user_sess'] == $image_name ? IMALAN_24 : IMALAN_23).")</span>";
			}
			else
			{
				$users = '<span class="warning">'.IMALAN_22.'</span>';
			}

			//directory?
			if(is_dir(e_MEDIA."avatars/".$image_name))
			{
				//File info
				$users = "<a href='#' title='".IMALAN_69.": {$image_name}'><img class='e-tip icon S16' src='".e_IMAGE_ABS."admin_images/info_16.png' alt='".IMALAN_66.": {$image_name}' title='".IMALAN_69.": {$image_name}' /></a> <span class='error'>".IMALAN_69."</span>";

				//Friendly UI - click text to select a form element
				$img_src =  '<span class="error">'.IMALAN_70.'</span>';
				$disabled = true;
			}
			else
			{
				//File info
			//	$users = "<a class='e-tip' href='#' title='".IMALAN_66.": {$image_name}'><img src='".e_IMAGE_ABS."admin_images/info_16.png' alt='".IMALAN_66.": {$image_name}' /></a> ".$users;

				// Control over the image size (design)
			//	$image_size = getimagesize(e_MEDIA."avatars/".$image_name);

				//Friendly UI - click text to select a form element
				
				// Resized on-the-fly - avatar-size no longer an issue. 
				$attr = "aw=".$pref['im_width']."&ah=".$pref['im_height'];
				$img_path = $tp->thumbUrl(e_MEDIA_ABS."avatars/".$image_name,$attr);
				$img_src = "<label for='image-action-{$count}' title='".IMALAN_56."'>
				<img class='e-tip' src='".$img_path."' alt='{$image_name}' title='".IMALAN_66.": {$image_name}' />
				</label>";
			//	if ($image_size[0] > $pref['im_width'] || $image_size[1] > $pref['im_height'])
			//	{
				//	$img_src = "<a class='image-preview' href='".e_MEDIA."avatars/".rawurlencode($image_name)."' rel='external'>".IMALAN_57."</a>";
			//	}
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
					".$frm->admin_button('e_check_all', LAN_CHECKALL, 'action')."
					".$frm->admin_button('e_uncheck_all', LAN_UNCHECKALL, 'action')."
					".$frm->admin_button('submit_show_delete_multi', LAN_DELCHECKED, 'delete')."
					".$frm->admin_button('submit_show_deleteall', IMALAN_25, 'delete')."
					".$frm->admin_button('submit_cancel_show', IMALAN_68, 'cancel')."
				</div>
			</div>
			</fieldset>
			</form>
		";

	}

	echo $mes->render().$text;
	return;
	// $ns->tablerender(LAN_MEDIAMANAGER." :: ".IMALAN_18, $mes->render().$text);
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
			<table class='adminlist'>
				<colgroup>
					<col style='width:10%' />
					<col style='width:20%' />
					<col style='width:25%' />
					<col style='width:45%' />
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

	<table class='adminform'>
	<colgroup>
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


//Just in case...
if(!e_AJAX_REQUEST) require_once("footer.php"); 



?>