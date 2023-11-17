<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2016 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Administration - Media Management Class
 *
*/


if (!defined('e107_INIT')) { exit; }
//TODO LANS

use Intervention\Image\ImageManagerStatic as Intervension;

/**
 *  Media class.
 */
class e_media
{
	protected $imagelist = array();
	
	protected $logging = false; 
	
	protected $mimePaths = array(
				'text'			=> e_MEDIA_FILE,
				'multipart'		=> e_MEDIA_FILE,
				'application'	=> e_MEDIA_FILE,
				'audio'			=> e_MEDIA_FILE,
				'image'			=> e_MEDIA_IMAGE,
				'video'			=> e_MEDIA_VIDEO,
				'other'			=> e_MEDIA_FILE
		);

	/** @var array  */
	protected $mimeExtensions = array(
				'text'			=> array('txt'),
				'multipart'		=> array(),
				'application'	=> array('zip','doc','gz'),
				'audio'			=> array('mp3','wav'),
				'image'			=> array('jpeg','jpg','png','gif', 'svg', 'webp'),
				'video'			=> array('mp4', 'youtube','youtubepl', 'mov'),
				'other'			=> array(),
			//	'glyph'         => array('glyph')
		);
	
	function __construct()
	{
		if(E107_DEBUG_LEVEL > 0)
		{
			$this->logging = true; 	
		}

		e107::includeLan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_image.php');
	}


	/**
	 * @param $val
	 * @return void
	 */
	public function debug($val)
	{

		$this->logging = intval($val);
	}



	/**
	 * Import files from specified path into media database. 
	 * @param string $cat Category nickname
	 * @param string $epath path to file.
	 * @param string $fmask [optional] filetypes eg. .jpg|.gif IMAGES is the default mask.
	 * @param array $options
	 * @return e_media
	 */
	public function import($cat='', $epath='', $fmask='', $options=array())
	{
		if(!vartrue($cat)){ return $this;}
		
		if(is_string($options))
		{
			parse_str($options,$options);	
		}
		
		if(!is_readable($epath))
		{
			e107::getMessage()->addDebug("Unable to import: ".$epath);
			return $this;
		}
			
		$fl = e107::getFile();
		$tp = e107::getParser();
		$sql = e107::getDb();
		$mes = e107::getMessage();
	
		$fl->setFileInfo('all');

		if(empty($fmask))
		{
			$fmask = '[a-zA-z0-9_-]+\.(png|jpg|jpeg|gif|PNG|JPG|JPEG|GIF)$';
		}

		$img_array = $fl->get_files($epath, $fmask,'',2);
	
		if(empty($img_array))
		{
			e107::getMessage()->addDebug("Media-Import could not find any files in <b>".$epath."</b> with fmask: ".$fmask);
			return $this;
		}
		
	//	print_a($img_array);
	//	return;
		$count = 0;
		foreach($img_array as $f)
		{

			if(!is_array($f))
			{
				continue;
			}
			
			if($f['fsize'] === 0) // prevent zero-byte files. 
			{
				continue;	
			}
			
			if(vartrue($options['min-width']) && ($f['img-width'] < $options['min-width']))
			{
				$mes->addDebug("Skipped: ".$f['fname']);
				continue;	
			}
			
			if(vartrue($options['min-size']) && ($f['fsize'] < $options['min-size']))
			{
				$mes->addDebug("Skipped: ".$f['fname']);
				continue;	
			}
			
				
			$fullpath = $tp->createConstants($f['path'].$f['fname'],1);
			// echo "<br />cat=".$cat;
			$insert = array(
				'media_caption'		=> $f['fname'],
				'media_description'	=> '',
				'media_category'	=> $cat,
				'media_datestamp'	=> $f['modified'],
				'media_url'			=> $fullpath,
				'media_userclass'	=> '0',
				'media_name'		=> $f['fname'],
				'media_author'		=> USERID,
				'media_size'		=> $f['fsize'],
				'media_dimensions'	=> vartrue($f['img-width']) ? $f['img-width']." x ".$f['img-height'] : "",
				'media_usedby'		=> '',
				'media_tags'		=> '',
				'media_type'		=> $f['mime']
			);

			if(!$sql->select('core_media','media_url',"media_url = '".$fullpath."' LIMIT 1"))
			{
			
				if($sql->insert("core_media",$insert))
				{
					$count++;
					$mes->addDebug("Imported Media: ".$f['fname']);
				}
				else
				{
					$mes->addError("Media not imported: ".$f['fname']);
				}
			}
			else
			{
				$mes->addDebug("Skipped (already exists): ".$f['fname']);
			}
		}

		if(!empty($count))
		{
			 $mes->addDebug("Imported {$count} Media items.");
		}
		
		return $this;
	}


	/**
	 * Import icons into media-manager from specified path.
	 * @param string $path
	 * @return e_media
	 */
	public function importIcons($path)
	{
		$iconsrch = array(16,32,48,64);

		foreach($iconsrch as $size)
		{
			$types = '[a-zA-z0-9_-]+'.$size.'\.(png|PNG)$';
			
			$this->import('_icon_'.$size, $path, $types);
		}

		$types = '[a-zA-z0-9_-]\.(svg|SVG)$';

		$this->import('_icon_svg', $path, $types);

		return $this;
	}
	
	
	
	
	/**
	 * Remove Media from media table
	 * @param string $cat [optional] remove a full category of media
	 * @return bool
	 */
	function removeCat($cat)
	{
		if(empty($cat))
		{
			return false;
		}

		$sql = e107::getDb();
		$mes = e107::getMessage();

		$status = ($sql->delete('core_media',"media_cat = '".$cat."'")) ? true : false;
		$mes->add("Removing Media in Category: ".$cat, E_MESSAGE_DEBUG);
		return $status;
	}



	/**
	 * Remove Media from media table
	 * @param string $epath remove media in the specified path.
	 * @param string $type [optional] image|icon
	 * @return bool
	 */
	function removePath($epath, $type='image')
	{
		$tp = e107::getParser();
		$sql = e107::getDb();
		$mes = e107::getMessage();
		
		$qry = ($type == 'icon') ? " AND media_category REGEXP '_icon_16|_icon_32|_icon_48|_icon_64' " : " AND NOT media_category REGEXP '_icon_16|_icon_32|_icon_48|_icon_64' ";

		if(empty($epath))
		{
			return false;
		}

		$path = $tp->createConstants($epath, 'rel');
		$status = ($sql->delete('core_media',"media_url LIKE '".$path."%'".$qry)) ? TRUE : FALSE;
		$message = ($type == 'image') ?  "Removing Media with path: ".$path : "Removing Icons with path: ".$path;
		$mes->add($message, E_MESSAGE_DEBUG);
		return $status;

	}
	
	
	
	/**
	 * Return a list if icons in the specified path
	 * @param string $epath
	 * @return array
	 */
	function listIcons($epath)
	{
		if(!$epath) return array();
		
		$ret = array();
		$sql = e107::getDb();
		$tp = e107::getParser();
		
		$path = $tp->createConstants($epath, 'rel');
	
		$sql->gen("SELECT * FROM `#core_media` WHERE `media_url` LIKE '".$path."%' AND media_category REGEXP '_icon_16|_icon_32|_icon_48|_icon_64|_icon_svg' ");
		while ($row = $sql->fetch())
		{
			$ret[] = $row['media_url'];
		}
		
		return $ret;	
	}

	/**
	 * Create media category.
	 * 'class' data is optional, 'id' key is ignored
	 * 
	 * @param array $datas associative array, db keys should be passed without the leading 'media_cat_' e.g. 'class', 'type', etc.
	 * @return integer last inserted ID or false on error
	 */
	public function createCategory($datas)
	{
		foreach ($datas as $k => $v) 
		{
			$data['media_cat_'.$k] = $v;
		}
		$data['media_cat_id'] = 0;
		if(!isset($data['media_cat_class']) || '' === $data['media_cat_class']) 
		{
			$data['media_cat_class'] = defset('e_UC_MEMBER', 253);
		}
		return e107::getDb()->insert('core_media_cat', $data);
	}


	/**
	 * Create a user Media-Category.
	 * @param $type string image | file | video
	 * @param $userId int - leave empty for currently logged in user.
	 * @param $userName string - leave blank for currently logged in user
	 * @param $parms (optional) - for future use.
	 * @return bool|int
	 */
	public function createUserCategory($type='image', $userId = USERID, $userName = USERNAME, $parms=null)
	{
		
		if($type !='image' && $type='file' && $type !='video')
		{
			return false;
		}

		unset($parms); // remove later if $parms becomes used.
				
		$cat = 'user_'.$type.'_'.intval($userId);
		
		if(!e107::getDb()->gen('SELECT media_cat_id FROM #core_media_cat WHERE media_cat_category = "'.$cat.'" LIMIT 1'))
		{
			$insert = array(
				'owner' => 'user',
				'category'	=> $cat,
				'title'	=> $userName,
				'sef'	=> 'media-'.eHelper::title2sef($userName),
				'diz'	=> '',
				'class'	=> '',
				'image'	=> '',
				'order'	=> ''
			);

			return $this->createCategory($insert);
		}
		
		return false;

	}
	
	
	
	/**
	 * Create multiple media categories in once
	 * @param array $multi_data
	 * @return integer number of successfully inserted records
	 */
	public function createCategories($multi_data)
	{
		$cnt = 0;
		foreach ($multi_data as $cats) 
		{
			if($this->createCategory($cats)) $cnt++;
		}
		return $cnt;
	}
	/*
	public function deleteCategory($id)
	{
		// TODO
	}*/

	/**
	 * @param (string) $owner
	 * @return bool|null
	 */
	public function deleteAllCategories($owner='')
	{
		if($owner == '')
		{
			return null;
		}
		
		$sql = e107::getDb();
		
		$sql->select('core_media_cat',"media_cat_category", "media_cat_owner = '".$owner."' ");
		while($row = $sql->fetch())
		{
			$categories[] = "'".$row['media_cat_category']."'";	
		}
		
		if($sql->delete('core_media_cat', "media_cat_owner = '".$owner."' "))
		{
			//TODO retrieve all category names for owner, and reset all media categories to _common. 
			return TRUE;
		//	return $sql->db_Update('core_media', "media_category = '_common_image' WHERE media_category IN (".implode(",",$categories).")");	
		}
		
		return FALSE; 
	}

	/**
	 * Return an Array of Media Categories
	 *
	 * @param string $owner
	 * @return array
	 */
	public function getCategories($owner='')
	{
		$ret = array();
		
		
		$qry = "SELECT * FROM #core_media_cat ";
		$qry .= ($owner) ? " WHERE media_cat_owner = '".$owner."' " : " (1) ";
		$qry .= "AND media_cat_class IN (".USERCLASS_LIST.") ";
		$qry .= "ORDER BY media_cat_order";
		
		e107::getDb()->gen($qry);
		while($row = e107::getDb()->fetch())
		{
			$id = $row['media_cat_category'];
			$ret[$id] = $row;
		}
		return $ret;	
	}

	/**
	 * Return the total number of Images in a particular category
	 *
	 * @param string $cat
	 * @param string $search
	 * @return array
	 */
	public function countImages($cat,$search=null)
	{
		return $this->getImages($cat, 0, 'all',$search);
	}


	/**
	 * @param string $cat
	 * @param int  $from
	 * @param int $amount
	 * @param string $search
	 * @return array
	 */
	public function getFiles($cat, $from=0, $amount = null, $search = null)
	{
		return $this->getMedia('application', $cat, $from, $amount, $search);
	}


	/**
	 * @param string $cat
	 * @param int  $from
	 * @param int $amount
	 * @param string $search
	 * @return array
	 */
	public function getVideos($cat, $from=0, $amount = null, $search = null)
	{
		return $this->getMedia('video', $cat, $from, $amount, $search);
	}


	/**
	 * @param string $cat
	 * @param int   $from
	 * @param int  $amount
	 * @param string  $search
	 * @return array
	 */
	public function getAudios($cat='', $from=0, $amount = null, $search = null)
	{
		return $this->getMedia('audio', $cat, $from, $amount, $search);
	}

	/**
	 * Return an array of Images in a particular category
	 *
	 * @param string $cat : category name. use + to include _common eg. 'news+'
	 * @param int    $from
	 * @param  int      $amount
	 * @param  string      $search
	 * @param null   $orderby
	 * @return array
	 */
	public function getImages($cat='', $from=0, $amount=null, $search=null, $orderby=null)
	{
		return $this->getMedia('image', $cat, $from, $amount, $search, $orderby);
	}


	/**
	 * Return an array of Images in a particular category
	 *
	 * @param string $type image|audio|video
	 * @param string $cat : category name. use ^ to include _common eg. 'news^'
	 * @param int    $from
	 * @param int|string     $amount
	 * @param string  $search
	 * @param string   $orderby
	 * @return array|bool
	 */
	private function getMedia($type, $cat='', $from=0, $amount=null, $search=null, $orderby=null)
	{

	//	print_a($cat);
	//	$inc 		= array();
		$searchinc 	= array();
		$catArray   = array();
		
		if(strpos($cat,"^") || !$cat)
		{
			$cat = str_replace("^","",$cat);
			$catArray[] = '_common_'.$type;
		}

		if($cat)
		{
			if(strpos($cat, "|") && !strpos($cat,"^") )
			{
				$catArray = explode("|",$cat);	
			}
			else
			{
				$catArray[] = $cat;

				if($type === 'image' || $type === 'audio'|| $type === 'video')
				{
					$catArray[] = $cat.'_'.$type; // BC Fix.
				}
			}
		}


		// TODO check the category is valid. 
		
		if($search)
		{
			$searchinc[] = "media_name LIKE '%".$search."%' ";
			$searchinc[] = "media_description LIKE '%".$search."%' "; 
			$searchinc[] = "media_caption LIKE '%".$search."%' ";
			$searchinc[] = "media_tags LIKE '%".$search."%' ";  
		}

		
		$ret = array();
		
		
		$fields = ($amount == 'all') ? "media_id" : "*";
		
		$query = "SELECT ".$fields." FROM #core_media WHERE `media_category` REGEXP '(^|,)".implode("|",$catArray)."(,|$)' 
		AND `media_userclass` IN (".USERCLASS_LIST.") 
		AND `media_type` LIKE '".$type."/%' " ;

		if($search)
		{
			$query .= " AND ( ".implode(" OR ",$searchinc)." ) " ;	
		}

		if($orderby)
		{
			$query .= " ORDER BY " . $orderby;
		}
		else
		{
			$query .= " ORDER BY media_category ASC, media_id DESC"; // places the specified category before the _common categories.
		}

		if($amount == 'all')
		{
			return e107::getDb()->gen($query);		
		}

		
		if($amount)
		{
			$query .= " LIMIT ".$from." ,".$amount;	
		}

		e107::getDebug()->log($query);

		e107::getDb()->gen($query);
		while($row = e107::getDb()->fetch())
		{
			$id = $row['media_id'];
			$ret[$id] = $row;
		}

		return $ret;	
	}


	/**
	 * Return an array of Images in a particular category
	 * @param string $type : 16 | 32 | 48 | 64
	 * @param integer $from
	 * @param integer $amount
	 * @return array
	 */
	public function getIcons($type='', $from=0, $amount=null)
	{
		$inc = array();
		
		if($type)
		{
			$inc[] = "media_category = '_icon_".$type."' ";
		}

		$ret = array();
		$query = "SELECT * FROM #core_media WHERE media_userclass IN (".USERCLASS_LIST.") AND media_category LIKE '_icon%' ";
		$query .= (count($inc)) ? " AND ( ".implode(" OR ",$inc)." )" : "";
		$query .= "  ORDER BY media_category, media_name";
		
		if($amount)
		{
			$query .= " LIMIT ".$from." ,".$amount;	
		}
		
		e107::getDb()->gen($query);
		while($row = e107::getDb()->fetch())
		{
			$id = $row['media_id'];
			$ret[$id] = $row;
		}
		return $ret;	
	}
	


		
	/**
	 * Generate Simple Thumbnail window for image -selection
	 * Currently used only by ren_help PreImage_Select
	 * @param string $cat
	 * @param string $formid
	 * @return string
	 */
	public function imageSelect($cat,$formid='imageSel')
	{
		$sql = e107::getDb();
	//	$tp = e107::getParser();
		
		$text = "<div style='margin-left:500px;text-align:center; position:relative;z-index:1000;float:left;display:none' id='{$formid}'>";
		$text .="<div style='-moz-box-shadow: 3px 3px 3px #808080;
			-webkit-box-shadow: 3px 3px 3px #808080;
			box-shadow: 3px 3px 3px #808080;
			background-color:black;border:1px solid black;position:absolute; height:200px;width:205px;overflow-y:scroll; bottom:30px; right:100px'>";
		
		$sql->gen("SELECT * FROM `#core_media` WHERE media_category = '_common' OR media_category = '".$cat."' ORDER BY media_category,media_datestamp DESC ");
		$text .= "<div style='font-size:120%;font-weight:bold;text-align:right;margin-right:10px'><a title='".LAN_CLOSE."' style='text-decoration:none;color:white' href='#' onclick=\"expandit('{$formid}'); return false;\" >x</a></div>";
			
		while ($row = $sql->fetch())
		{
			$image	= $row['media_url'];
			$diz	= $row['media_name']." : ". $row['media_dimensions'];
			$insert = "[img]".$image."[/img]";
			
			$text .= "
			<div style='border:1px solid silver;margin:5px;width:50px;height:50px;overflow:hidden;float:left'>
			<a title=\"".$diz."\" href='#' onclick=\"addtext('".$insert."', true); expandit('{$formid}'); return false;\" >
			<img src='".e107::getParser()->thumbUrl($image, 'w=100', true)."' alt=\"".$diz."\" style='width: 50px' />
			</a>
			</div>";
		}
				
		$text .= "</div></div>";
		
		return $text;	
	}


	/**
	 * @param $category
	 * @param $tagid
	 * @param $option
	 * @return string
	 */
	private function mediaSelectNav($category, $tagid='', $option=null)
	{
		if(is_string($option))
		{
			parse_str($option,$option); 
		}
		
		$cat = varset($category) 			? '&amp;for='.$category : "";
		$cat .= varset($tagid) 				? '&amp;tagid='.$tagid : ""; 
		
		$cat .= varset($option['bbcode']) 	? '&amp;bbcode='.$option['bbcode'] : ""; 		
		$cat .= varset($option['limit']) 	? "&amp;limit=".$option['limit'] : "";
		$cat .= varset($option['frm']) 		? "&amp;frm=".$option['frm'] : "";
		$cat .= varset($option['w']) 		? "&amp;w=".$option['w'] : "";
		
		$action = varset($option['action'],'nav');
					
		return e_ADMIN_ABS."image.php?mode=main&amp;action=".$action."&amp;iframe=1".$cat."&amp;from=0";

	}


	/**
	 * @deprecated by browserCarousel
	 * @param string $category
	 * @param null   $tagid
	 * @param null   $att
	 * @return string
	 */
	public function mediaSelect($category='',$tagid=null,$att=null)
	{
	
		if(is_string($att))
		{
			parse_str($att,$option); // grab 'onclick' . 
		}
		else {
			$option = $att;
		}

		$tp = e107::getParser();
			
		$frm 		= varset($option['from']) ? $option['from'] : 0;
		$limit 		= varset($option['limit']) ? $option['limit'] : 20;
	//	$newfrm 	= $frm + $limit;
		$bbcode		= varset($option['bbcode']) ? $option['bbcode'] : null;
		$navMode	= varset($option['nav']) ? TRUE : FALSE;
		$search		= varset($option['search']) ? $option['search'] : null;
		$prevId 	= $tagid."_prev"; // ID of image in Form.
		
		if($category !='_icon')
		{
			$cat 	= ($category) ? $category : ""; // the '+' loads category '_common' as well as the chosen category.
			$images = $this->getImages($cat,$frm,$limit,$search);
			$class 	= "media-select-image";
			$classN = "media-select-image-none";
			$w		= 120;
		//	$h		= 100;
			$total	= $this->countImages($cat,$search);
			$onclick_clear = "parent.document.getElementById('{$tagid}').value = '';
		 	parent.document.getElementById('".$prevId."').src = '".e_IMAGE_ABS."generic/nomedia.png';
		 	 return false;";
		}
		else // Icons
		{
			$cat 	= "";
			$images = $this->getIcons($cat,0,200);
			$class 	= "media-select-icon";
			$classN = "media-select-icon-none";
			$w		= 64;
		//	$h		= 64;
		//	$total 	= 500;
			$total	= $this->countImages("_icon_16|_icon_32|_icon_48|_icon_64|_icon_svg",$search);
			$onclick_clear = "parent.document.getElementById('{$tagid}').value = '';
		 	parent.document.getElementById('".$prevId."').innerHTML= '';
		 	 return false;";

		}
		
		
		
	//	$total_images 	= $this->getImages($cat); // for use by next/prev in filter at some point. 
	
		$prevAtt		= '&aw='.vartrue($option['w'],$w); // .'&ah=100';	// Image Parsed back to Form as PREVIEW image. 	

		$thumbAtt		= 'aw=120&ah=120';	// Thumbnail of the Media-Manager Preview. 	
		
		
		// EXAMPLE of FILTER GUI. 
		$text = "";
		$dipTotal = (($frm + $limit) < $total) ? ($frm + $limit) : $total;

		if($navMode === false)
		{
		//	$data_src = $this->mediaSelectNav($category,$tagid, "bbcode=".$bbcode)."&amp;from=0";
			$data_src = $this->mediaSelectNav($category,$tagid, $option); // ."&amp;from=0";
		
			// Inline style to override jquery-ui stuff. 
			$text .= "<div>
			<div id='admin-ui-media-manager-search' class='input-append form-inline' style='margin-top:10px;font-size:12px'>
			<input type='text' id='media-search' placeholder='".LAN_SEARCH."' name='search' value='' class='form-control e-tip' data-target='media-select-container' data-src='".$data_src."' />
			";
		//	$text .= "<input type='button' value='Go' class='btn btn-primary e-media-nav' data-target='media-select-container' data-src='".$this->mediaSelectNav($category,"tagid=".$tagid."&bbcode=".$bbcode)."&amp;from=0' /> "; // Manual filter, if onkeyup ajax fails for some reason. 
			$text .= "<button type='button'  class='btn btn-primary e-media-nav' data-target='media-select-container' data-src='".$data_src."' >".LAN_GO."</button>"; // Manual filter, if onkeyup ajax fails for some reason.
	
			$text .= "<button id='admin-ui-media-nav-down' type='button' title='".IMALAN_130."' class='btn btn-default btn-secondary e-nav e-media-nav e-tip' style='outline:0' data-target='media-select-container' data-nav-total='".$total."' data-nav-dir='down' data-nav-inc='".$limit."' data-src='".$data_src."'>&laquo;</button>"; // see next page of images.
		
			$text .= "<button id='admin-ui-media-nav-up' type='button' title='".IMALAN_131."' class='btn btn-default btn-secondary e-nav e-media-nav e-tip' style='outline:0;text-align:center'  data-target='media-select-container' data-nav-total='".$total."' data-nav-dir='up' data-nav-inc='".$limit."' data-src='".$data_src."' >&raquo;</button>"; // see next page of images.
			$text .= "</div></div>";
			$text .= "<div id='admin-ui-media-select-count' class='media-select-count' style='text-align:right; display:block'>";
			$text .= e107::getParser()->lanVars(IMALAN_162, array('x'=> $frm +1, 'y'=> $dipTotal, 'z'=>$total ));
			$text .= "</div>\n";	

			$text .= "
			<div id='media-select-container'>";	
		}
		
		$text .= "<div id='admin-ui-media-select-count-hidden' class='media-select-count' data-media-select-current-limit='".$dipTotal."' style='text-align:right; display:none'>";
		$text .= e107::getParser()->lanVars(IMALAN_162, array('x'=> $frm +1, 'y'=> $dipTotal, 'z'=>$total ));
		$text .= "</div>\n";
		
		
		if($bbcode == null) // e107 Media Manager - new-image mode. 
		{
			$text .= "<a title='".IMALAN_165."' class='e-tip thumbnail {$class} ".$classN." media-select-none e-dialog-close' data-src='' style='vertical-align:middle;display:block;float:left;' href='#' onclick=\"{$onclick_clear}\" >
			<span>".$tp->toGlyph('fa-ban')."</span>
			</a>";		
		}

		//$w	= false; //
		$h = false;
		$defaultResizeWidth = 400;
			
		if($bbcode) // ie. TinyMce Editor, not imagepicker(); 
		{
			e107::getBB()->setClass($category);
			$defaultResizeWidth = e107::getBB()->resizeWidth(); // resize the image according to prefs.
			$h = e107::getBB()->resizeHeight();
			e107::getBB()->clearClass();

		}
		
//		print_a($option);
		
		$tp = e107::getParser();
	
		/*
            $media_path : Inserted into html tags eg. <img src='here'...
        */

		foreach($images as $im)
		{
			list($dbWidth,$dbHeight) = explode(" x ",$im['media_dimensions']);
			unset($dbHeight);
				
			$w = ($dbWidth > $defaultResizeWidth) ? $defaultResizeWidth : intval($dbWidth);

            if($category === '_icon')
            {
                $class           = "media-select-icon";
	            $media_path     = $tp->replaceConstants($im['media_url']); // $tp->replaceConstants($im['media_url'],'full'); // max-size
				$realPath       = $media_path;
                $img_url        = $media_path;

            }
            else // Regular image.
            {

                $class          = "media-select-image";
	            $media_path 	= ($w || $h) ? $tp->thumbUrl($im['media_url'], "&w={$w}") : $tp->thumbUrl($im['media_url']); // $tp->replaceConstants($im['media_url'],'full'); // max-size
				$realPath 		= $tp->thumbUrl($im['media_url'], $prevAtt); // Parsed back to Form as Preview Image.
	            $img_url        = e107::getParser()->thumbUrl($im['media_url'], $thumbAtt);

            }

			
			$diz 			= $tp->toAttribute(varset($im['media_name']))." (".str_replace(" ","", varset($im['media_dimensions'])).")";
			$media_alt      = $tp->toAttribute(vartrue($im['media_caption']));
			
			if($bbcode == null) // e107 Media Manager
			{			
				$onclicki = "parent.document.getElementById('{$tagid}').value = '{$im['media_url']}';
				parent.document.getElementById('".$prevId."').src = '{$realPath}';
				return false;";	
		 		//$onclicki = "";
				$class .= " e-media-select e-dialog-close";
			}
			else // TinyMce and textarea bbcode  
			{
				$class .= " e-media-select";
				$onclicki = "";
				
			}
			
			$data_bb = ($bbcode) ? "img" : "";

			
			$text .= "<a data-toggle='context' data-bs-toggle='context' class='thumbnail {$class} e-tip' data-id='{$im['media_id']}' data-width='{$w}' data-height='{$h}' data-src='{$media_path}' data-bbcode='{$data_bb}' data-target='{$tagid}' data-path='{$im['media_url']}' data-preview='{$realPath}' data-alt=\"".$media_alt."\" title=\"".$diz."\" style='float:left' href='#' onclick=\"{$onclicki}\" >";
			$text .= "<img class='image-rounded' src='".$img_url."' alt=\"".$im['media_title']."\" title=\"{$diz}\" />";
			$text .= "</a>\n\n";
		}	
		
		
		$text .= "<div style='clear:both'><!-- --></div>";
		
		//fixing tip icon when navigation prev/next page
		$text .="<script>";
		$text .="$(document).ready(function(){
						$('.e-tip').each(function() {
										
							
							var tip = $(this).attr('title');
							if(!tip)
							{
								return;
							}
							
							var pos = $(this).attr('data-placement'); 
							if(!pos)
							{
								pos = 'top';	
							}
							
							$(this).tooltip({opacity:1.0,fade:true, placement: pos});

						});	
					});			
		";
		$text .="</script>";
		$mes = e107::getMessage();
		$mes->addDebug("Target: {$tagid}");
		
		if($navMode === false)
		{			
			$text .= "</div>";
		}
				
		return $text;	
	}


	/**
	 * @param string $oldpath - path to pre-moved file (no e107 constants)
	 * @param string $newpath - new path to move file to (no e107 constants)
	 * @return bool|string returns false if duplciate entry found otherwise return new path.
	 */
	function checkDupe($oldpath, $newpath)
	{
		$mes = e107::getMessage();	
		$tp = e107::getParser();
		$sql = e107::getDb();
		
	//	$mes->addDebug("checkDupe(): newpath=".$newpath."<br />oldpath=".$oldpath."<br />".print_r($upload,TRUE));
		if(file_exists($newpath) && ($f = e107::getFile()->getFileInfo($oldpath,TRUE)))
		{
			$this->log($newpath." already exists and will be renamed during import.");
			$mes->addWarning($newpath." already exists and was renamed during import.");	
			$file = $f['pathinfo']['filename']."_.".$f['pathinfo']['extension'];
			$newpath = $this->getPath($f['mime']).'/'.$file;						
		}
		
		if($sql->select("core_media","media_url","media_url LIKE '%".$tp->createConstants($newpath,'rel')."' LIMIT 1"))
		{
			// $mes->addWarning($newpath." detected in media-manager.");
			$this->log("Import not performed. ".$newpath." detected in media table already.");

			return false;
			//$row = $sql->fetch();
			//$newpath = $row['media_url']; // causes trouble with importFile() if {e_MEDIA_CONSTANT} returned.
		}

		return $newpath;	
	}


	/**
	 * @param string|array $type array('prefix'=>'', 'pattern'=>'', 'path'=>'', 'name'=>'')
	 * @param string $addPrefix
	 * @return array
	 */
	function getGlyphs($type, $addPrefix = '')
	{
		$icons = [];

		$precompiled = [
			'bs2',
			'bs3',
			'fa4',
			'fa5-fab',
			'fa5-fas',
			'fa5-far',
			'fa5-shims',
			'fa6-fab',
			'fa6-fas',
		//	'fa6-far', PRO.
			'fa6-shims'
		];
		
		if(is_string($type) && in_array($type, $precompiled))
		{

			$matches = array();
			
			// FontAwesome 6
			$matches['fa6-fas'] = array ('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'address-book', 'address-card', 'align-center', 'align-justify', 'align-left', 'align-right', 'anchor', 'anchor-circle-check', 'anchor-circle-exclamation', 'anchor-circle-xmark', 'anchor-lock', 'angle-down', 'angle-left', 'angle-right', 'angle-up', 'angles-down', 'angles-left', 'angles-right', 'angles-up', 'ankh', 'apple-whole', 'archway', 'arrow-down', 'arrow-down-1-9', 'arrow-down-9-1', 'arrow-down-a-z', 'arrow-down-long', 'arrow-down-short-wide', 'arrow-down-up-across-line', 'arrow-down-up-lock', 'arrow-down-wide-short', 'arrow-down-z-a', 'arrow-left', 'arrow-left-long', 'arrow-pointer', 'arrow-right', 'arrow-right-arrow-left', 'arrow-right-from-bracket', 'arrow-right-long', 'arrow-right-to-bracket', 'arrow-right-to-city', 'arrow-rotate-left', 'arrow-rotate-right', 'arrow-trend-down', 'arrow-trend-up', 'arrow-turn-down', 'arrow-turn-up', 'arrow-up', 'arrow-up-1-9', 'arrow-up-9-1', 'arrow-up-a-z', 'arrow-up-from-bracket', 'arrow-up-from-ground-water', 'arrow-up-from-water-pump', 'arrow-up-long', 'arrow-up-right-dots', 'arrow-up-right-from-square', 'arrow-up-short-wide', 'arrow-up-wide-short', 'arrow-up-z-a', 'arrows-down-to-line', 'arrows-down-to-people', 'arrows-left-right', 'arrows-left-right-to-line', 'arrows-rotate', 'arrows-spin', 'arrows-split-up-and-left', 'arrows-to-circle', 'arrows-to-dot', 'arrows-to-eye', 'arrows-turn-right', 'arrows-turn-to-dots', 'arrows-up-down', 'arrows-up-down-left-right', 'arrows-up-to-line', 'asterisk', 'at', 'atom', 'audio-description', 'austral-sign', 'award', 'b', 'baby', 'baby-carriage', 'backward', 'backward-fast', 'backward-step', 'bacon', 'bacteria', 'bacterium', 'bag-shopping', 'bahai', 'baht-sign', 'ban', 'ban-smoking', 'bandage', 'bangladeshi-taka-sign', 'barcode', 'bars', 'bars-progress', 'bars-staggered', 'baseball', 'baseball-bat-ball', 'basket-shopping', 'basketball', 'bath', 'battery-empty', 'battery-full', 'battery-half', 'battery-quarter', 'battery-three-quarters', 'bed', 'bed-pulse', 'beer-mug-empty', 'bell', 'bell-concierge', 'bell-slash', 'bezier-curve', 'bicycle', 'binoculars', 'biohazard', 'bitcoin-sign', 'blender', 'blender-phone', 'blog', 'bold', 'bolt', 'bolt-lightning', 'bomb', 'bone', 'bong', 'book', 'book-atlas', 'book-bible', 'book-bookmark', 'book-journal-whills', 'book-medical', 'book-open', 'book-open-reader', 'book-quran', 'book-skull', 'book-tanakh', 'bookmark', 'border-all', 'border-none', 'border-top-left', 'bore-hole', 'bottle-droplet', 'bottle-water', 'bowl-food', 'bowl-rice', 'bowling-ball', 'box', 'box-archive', 'box-open', 'box-tissue', 'boxes-packing', 'boxes-stacked', 'braille', 'brain', 'brazilian-real-sign', 'bread-slice', 'bridge', 'bridge-circle-check', 'bridge-circle-exclamation', 'bridge-circle-xmark', 'bridge-lock', 'bridge-water', 'briefcase', 'briefcase-medical', 'broom', 'broom-ball', 'brush', 'bucket', 'bug', 'bug-slash', 'bugs', 'building', 'building-circle-arrow-right', 'building-circle-check', 'building-circle-exclamation', 'building-circle-xmark', 'building-columns', 'building-flag', 'building-lock', 'building-ngo', 'building-shield', 'building-un', 'building-user', 'building-wheat', 'bullhorn', 'bullseye', 'burger', 'burst', 'bus', 'bus-simple', 'business-time', 'c', 'cable-car', 'cake-candles', 'calculator', 'calendar', 'calendar-check', 'calendar-day', 'calendar-days', 'calendar-minus', 'calendar-plus', 'calendar-week', 'calendar-xmark', 'camera', 'camera-retro', 'camera-rotate', 'campground', 'candy-cane', 'cannabis', 'capsules', 'car', 'car-battery', 'car-burst', 'car-on', 'car-rear', 'car-side', 'car-tunnel', 'caravan', 'caret-down', 'caret-left', 'caret-right', 'caret-up', 'carrot', 'cart-arrow-down', 'cart-flatbed', 'cart-flatbed-suitcase', 'cart-plus', 'cart-shopping', 'cash-register', 'cat', 'cedi-sign', 'cent-sign', 'certificate', 'chair', 'chalkboard', 'chalkboard-user', 'champagne-glasses', 'charging-station', 'chart-area', 'chart-bar', 'chart-column', 'chart-gantt', 'chart-line', 'chart-pie', 'chart-simple', 'check', 'check-double', 'check-to-slot', 'cheese', 'chess', 'chess-bishop', 'chess-board', 'chess-king', 'chess-knight', 'chess-pawn', 'chess-queen', 'chess-rook', 'chevron-down', 'chevron-left', 'chevron-right', 'chevron-up', 'child', 'child-combatant', 'child-dress', 'child-reaching', 'children', 'church', 'circle', 'circle-arrow-down', 'circle-arrow-left', 'circle-arrow-right', 'circle-arrow-up', 'circle-check', 'circle-chevron-down', 'circle-chevron-left', 'circle-chevron-right', 'circle-chevron-up', 'circle-dollar-to-slot', 'circle-dot', 'circle-down', 'circle-exclamation', 'circle-h', 'circle-half-stroke', 'circle-info', 'circle-left', 'circle-minus', 'circle-nodes', 'circle-notch', 'circle-pause', 'circle-play', 'circle-plus', 'circle-question', 'circle-radiation', 'circle-right', 'circle-stop', 'circle-up', 'circle-user', 'circle-xmark', 'city', 'clapperboard', 'clipboard', 'clipboard-check', 'clipboard-list', 'clipboard-question', 'clipboard-user', 'clock', 'clock-rotate-left', 'clone', 'closed-captioning', 'cloud', 'cloud-arrow-down', 'cloud-arrow-up', 'cloud-bolt', 'cloud-meatball', 'cloud-moon', 'cloud-moon-rain', 'cloud-rain', 'cloud-showers-heavy', 'cloud-showers-water', 'cloud-sun', 'cloud-sun-rain', 'clover', 'code', 'code-branch', 'code-commit', 'code-compare', 'code-fork', 'code-merge', 'code-pull-request', 'coins', 'colon-sign', 'comment', 'comment-dollar', 'comment-dots', 'comment-medical', 'comment-slash', 'comment-sms', 'comments', 'comments-dollar', 'compact-disc', 'compass', 'compass-drafting', 'compress', 'computer', 'computer-mouse', 'cookie', 'cookie-bite', 'copy', 'copyright', 'couch', 'cow', 'credit-card', 'crop', 'crop-simple', 'cross', 'crosshairs', 'crow', 'crown', 'crutch', 'cruzeiro-sign', 'cube', 'cubes', 'cubes-stacked', 'd', 'database', 'delete-left', 'democrat', 'desktop', 'dharmachakra', 'diagram-next', 'diagram-predecessor', 'diagram-project', 'diagram-successor', 'diamond', 'diamond-turn-right', 'dice', 'dice-d20', 'dice-d6', 'dice-five', 'dice-four', 'dice-one', 'dice-six', 'dice-three', 'dice-two', 'disease', 'display', 'divide', 'dna', 'dog', 'dollar-sign', 'dolly', 'dong-sign', 'door-closed', 'door-open', 'dove', 'down-left-and-up-right-to-center', 'down-long', 'download', 'dragon', 'draw-polygon', 'droplet', 'droplet-slash', 'drum', 'drum-steelpan', 'drumstick-bite', 'dumbbell', 'dumpster', 'dumpster-fire', 'dungeon', 'e', 'ear-deaf', 'ear-listen', 'earth-africa', 'earth-americas', 'earth-asia', 'earth-europe', 'earth-oceania', 'egg', 'eject', 'elevator', 'ellipsis', 'ellipsis-vertical', 'envelope', 'envelope-circle-check', 'envelope-open', 'envelope-open-text', 'envelopes-bulk', 'equals', 'eraser', 'ethernet', 'euro-sign', 'exclamation', 'expand', 'explosion', 'eye', 'eye-dropper', 'eye-low-vision', 'eye-slash', 'f', 'face-angry', 'face-dizzy', 'face-flushed', 'face-frown', 'face-frown-open', 'face-grimace', 'face-grin', 'face-grin-beam', 'face-grin-beam-sweat', 'face-grin-hearts', 'face-grin-squint', 'face-grin-squint-tears', 'face-grin-stars', 'face-grin-tears', 'face-grin-tongue', 'face-grin-tongue-squint', 'face-grin-tongue-wink', 'face-grin-wide', 'face-grin-wink', 'face-kiss', 'face-kiss-beam', 'face-kiss-wink-heart', 'face-laugh', 'face-laugh-beam', 'face-laugh-squint', 'face-laugh-wink', 'face-meh', 'face-meh-blank', 'face-rolling-eyes', 'face-sad-cry', 'face-sad-tear', 'face-smile', 'face-smile-beam', 'face-smile-wink', 'face-surprise', 'face-tired', 'fan', 'faucet', 'faucet-drip', 'fax', 'feather', 'feather-pointed', 'ferry', 'file', 'file-arrow-down', 'file-arrow-up', 'file-audio', 'file-circle-check', 'file-circle-exclamation', 'file-circle-minus', 'file-circle-plus', 'file-circle-question', 'file-circle-xmark', 'file-code', 'file-contract', 'file-csv', 'file-excel', 'file-export', 'file-image', 'file-import', 'file-invoice', 'file-invoice-dollar', 'file-lines', 'file-medical', 'file-pdf', 'file-pen', 'file-powerpoint', 'file-prescription', 'file-shield', 'file-signature', 'file-video', 'file-waveform', 'file-word', 'file-zipper', 'fill', 'fill-drip', 'film', 'filter', 'filter-circle-dollar', 'filter-circle-xmark', 'fingerprint', 'fire', 'fire-burner', 'fire-extinguisher', 'fire-flame-curved', 'fire-flame-simple', 'fish', 'fish-fins', 'flag', 'flag-checkered', 'flag-usa', 'flask', 'flask-vial', 'floppy-disk', 'florin-sign', 'folder', 'folder-closed', 'folder-minus', 'folder-open', 'folder-plus', 'folder-tree', 'font', 'font-awesome', 'football', 'forward', 'forward-fast', 'forward-step', 'franc-sign', 'frog', 'futbol', 'g', 'gamepad', 'gas-pump', 'gauge', 'gauge-high', 'gauge-simple', 'gauge-simple-high', 'gavel', 'gear', 'gears', 'gem', 'genderless', 'ghost', 'gift', 'gifts', 'glass-water', 'glass-water-droplet', 'glasses', 'globe', 'golf-ball-tee', 'gopuram', 'graduation-cap', 'greater-than', 'greater-than-equal', 'grip', 'grip-lines', 'grip-lines-vertical', 'grip-vertical', 'group-arrows-rotate', 'guarani-sign', 'guitar', 'gun', 'h', 'hammer', 'hamsa', 'hand', 'hand-back-fist', 'hand-dots', 'hand-fist', 'hand-holding', 'hand-holding-dollar', 'hand-holding-droplet', 'hand-holding-hand', 'hand-holding-heart', 'hand-holding-medical', 'hand-lizard', 'hand-middle-finger', 'hand-peace', 'hand-point-down', 'hand-point-left', 'hand-point-right', 'hand-point-up', 'hand-pointer', 'hand-scissors', 'hand-sparkles', 'hand-spock', 'handcuffs', 'hands', 'hands-asl-interpreting', 'hands-bound', 'hands-bubbles', 'hands-clapping', 'hands-holding', 'hands-holding-child', 'hands-holding-circle', 'hands-praying', 'handshake', 'handshake-angle', 'handshake-simple', 'handshake-simple-slash', 'handshake-slash', 'hanukiah', 'hard-drive', 'hashtag', 'hat-cowboy', 'hat-cowboy-side', 'hat-wizard', 'head-side-cough', 'head-side-cough-slash', 'head-side-mask', 'head-side-virus', 'heading', 'headphones', 'headphones-simple', 'headset', 'heart', 'heart-circle-bolt', 'heart-circle-check', 'heart-circle-exclamation', 'heart-circle-minus', 'heart-circle-plus', 'heart-circle-xmark', 'heart-crack', 'heart-pulse', 'helicopter', 'helicopter-symbol', 'helmet-safety', 'helmet-un', 'highlighter', 'hill-avalanche', 'hill-rockslide', 'hippo', 'hockey-puck', 'holly-berry', 'horse', 'horse-head', 'hospital', 'hospital-user', 'hot-tub-person', 'hotdog', 'hotel', 'hourglass', 'hourglass-end', 'hourglass-half', 'hourglass-start', 'house', 'house-chimney', 'house-chimney-crack', 'house-chimney-medical', 'house-chimney-user', 'house-chimney-window', 'house-circle-check', 'house-circle-exclamation', 'house-circle-xmark', 'house-crack', 'house-fire', 'house-flag', 'house-flood-water', 'house-flood-water-circle-arrow-right', 'house-laptop', 'house-lock', 'house-medical', 'house-medical-circle-check', 'house-medical-circle-exclamation', 'house-medical-circle-xmark', 'house-medical-flag', 'house-signal', 'house-tsunami', 'house-user', 'hryvnia-sign', 'hurricane', 'i', 'i-cursor', 'ice-cream', 'icicles', 'icons', 'id-badge', 'id-card', 'id-card-clip', 'igloo', 'image', 'image-portrait', 'images', 'inbox', 'indent', 'indian-rupee-sign', 'industry', 'infinity', 'info', 'italic', 'j', 'jar', 'jar-wheat', 'jedi', 'jet-fighter', 'jet-fighter-up', 'joint', 'jug-detergent', 'k', 'kaaba', 'key', 'keyboard', 'khanda', 'kip-sign', 'kit-medical', 'kitchen-set', 'kiwi-bird', 'l', 'land-mine-on', 'landmark', 'landmark-dome', 'landmark-flag', 'language', 'laptop', 'laptop-code', 'laptop-file', 'laptop-medical', 'lari-sign', 'layer-group', 'leaf', 'left-long', 'left-right', 'lemon', 'less-than', 'less-than-equal', 'life-ring', 'lightbulb', 'lines-leaning', 'link', 'link-slash', 'lira-sign', 'list', 'list-check', 'list-ol', 'list-ul', 'litecoin-sign', 'location-arrow', 'location-crosshairs', 'location-dot', 'location-pin', 'location-pin-lock', 'lock', 'lock-open', 'locust', 'lungs', 'lungs-virus', 'm', 'magnet', 'magnifying-glass', 'magnifying-glass-arrow-right', 'magnifying-glass-chart', 'magnifying-glass-dollar', 'magnifying-glass-location', 'magnifying-glass-minus', 'magnifying-glass-plus', 'manat-sign', 'map', 'map-location', 'map-location-dot', 'map-pin', 'marker', 'mars', 'mars-and-venus', 'mars-and-venus-burst', 'mars-double', 'mars-stroke', 'mars-stroke-right', 'mars-stroke-up', 'martini-glass', 'martini-glass-citrus', 'martini-glass-empty', 'mask', 'mask-face', 'mask-ventilator', 'masks-theater', 'mattress-pillow', 'maximize', 'medal', 'memory', 'menorah', 'mercury', 'message', 'meteor', 'microchip', 'microphone', 'microphone-lines', 'microphone-lines-slash', 'microphone-slash', 'microscope', 'mill-sign', 'minimize', 'minus', 'mitten', 'mobile', 'mobile-button', 'mobile-retro', 'mobile-screen', 'mobile-screen-button', 'money-bill', 'money-bill-1', 'money-bill-1-wave', 'money-bill-transfer', 'money-bill-trend-up', 'money-bill-wave', 'money-bill-wheat', 'money-bills', 'money-check', 'money-check-dollar', 'monument', 'moon', 'mortar-pestle', 'mosque', 'mosquito', 'mosquito-net', 'motorcycle', 'mound', 'mountain', 'mountain-city', 'mountain-sun', 'mug-hot', 'mug-saucer', 'music', 'n', 'naira-sign', 'network-wired', 'neuter', 'newspaper', 'not-equal', 'notdef', 'note-sticky', 'notes-medical', 'o', 'object-group', 'object-ungroup', 'oil-can', 'oil-well', 'om', 'otter', 'outdent', 'p', 'pager', 'paint-roller', 'paintbrush', 'palette', 'pallet', 'panorama', 'paper-plane', 'paperclip', 'parachute-box', 'paragraph', 'passport', 'paste', 'pause', 'paw', 'peace', 'pen', 'pen-clip', 'pen-fancy', 'pen-nib', 'pen-ruler', 'pen-to-square', 'pencil', 'people-arrows', 'people-carry-box', 'people-group', 'people-line', 'people-pulling', 'people-robbery', 'people-roof', 'pepper-hot', 'percent', 'person', 'person-arrow-down-to-line', 'person-arrow-up-from-line', 'person-biking', 'person-booth', 'person-breastfeeding', 'person-burst', 'person-cane', 'person-chalkboard', 'person-circle-check', 'person-circle-exclamation', 'person-circle-minus', 'person-circle-plus', 'person-circle-question', 'person-circle-xmark', 'person-digging', 'person-dots-from-line', 'person-dress', 'person-dress-burst', 'person-drowning', 'person-falling', 'person-falling-burst', 'person-half-dress', 'person-harassing', 'person-hiking', 'person-military-pointing', 'person-military-rifle', 'person-military-to-person', 'person-praying', 'person-pregnant', 'person-rays', 'person-rifle', 'person-running', 'person-shelter', 'person-skating', 'person-skiing', 'person-skiing-nordic', 'person-snowboarding', 'person-swimming', 'person-through-window', 'person-walking', 'person-walking-arrow-loop-left', 'person-walking-arrow-right', 'person-walking-dashed-line-arrow-right', 'person-walking-luggage', 'person-walking-with-cane', 'peseta-sign', 'peso-sign', 'phone', 'phone-flip', 'phone-slash', 'phone-volume', 'photo-film', 'piggy-bank', 'pills', 'pizza-slice', 'place-of-worship', 'plane', 'plane-arrival', 'plane-circle-check', 'plane-circle-exclamation', 'plane-circle-xmark', 'plane-departure', 'plane-lock', 'plane-slash', 'plane-up', 'plant-wilt', 'plate-wheat', 'play', 'plug', 'plug-circle-bolt', 'plug-circle-check', 'plug-circle-exclamation', 'plug-circle-minus', 'plug-circle-plus', 'plug-circle-xmark', 'plus', 'plus-minus', 'podcast', 'poo', 'poo-storm', 'poop', 'power-off', 'prescription', 'prescription-bottle', 'prescription-bottle-medical', 'print', 'pump-medical', 'pump-soap', 'puzzle-piece', 'q', 'qrcode', 'question', 'quote-left', 'quote-right', 'r', 'radiation', 'radio', 'rainbow', 'ranking-star', 'receipt', 'record-vinyl', 'rectangle-ad', 'rectangle-list', 'rectangle-xmark', 'recycle', 'registered', 'repeat', 'reply', 'reply-all', 'republican', 'restroom', 'retweet', 'ribbon', 'right-from-bracket', 'right-left', 'right-long', 'right-to-bracket', 'ring', 'road', 'road-barrier', 'road-bridge', 'road-circle-check', 'road-circle-exclamation', 'road-circle-xmark', 'road-lock', 'road-spikes', 'robot', 'rocket', 'rotate', 'rotate-left', 'rotate-right', 'route', 'rss', 'ruble-sign', 'rug', 'ruler', 'ruler-combined', 'ruler-horizontal', 'ruler-vertical', 'rupee-sign', 'rupiah-sign', 's', 'sack-dollar', 'sack-xmark', 'sailboat', 'satellite', 'satellite-dish', 'scale-balanced', 'scale-unbalanced', 'scale-unbalanced-flip', 'school', 'school-circle-check', 'school-circle-exclamation', 'school-circle-xmark', 'school-flag', 'school-lock', 'scissors', 'screwdriver', 'screwdriver-wrench', 'scroll', 'scroll-torah', 'sd-card', 'section', 'seedling', 'server', 'shapes', 'share', 'share-from-square', 'share-nodes', 'sheet-plastic', 'shekel-sign', 'shield', 'shield-cat', 'shield-dog', 'shield-halved', 'shield-heart', 'shield-virus', 'ship', 'shirt', 'shoe-prints', 'shop', 'shop-lock', 'shop-slash', 'shower', 'shrimp', 'shuffle', 'shuttle-space', 'sign-hanging', 'signal', 'signature', 'signs-post', 'sim-card', 'sink', 'sitemap', 'skull', 'skull-crossbones', 'slash', 'sleigh', 'sliders', 'smog', 'smoking', 'snowflake', 'snowman', 'snowplow', 'soap', 'socks', 'solar-panel', 'sort', 'sort-down', 'sort-up', 'spa', 'spaghetti-monster-flying', 'spell-check', 'spider', 'spinner', 'splotch', 'spoon', 'spray-can', 'spray-can-sparkles', 'square', 'square-arrow-up-right', 'square-caret-down', 'square-caret-left', 'square-caret-right', 'square-caret-up', 'square-check', 'square-envelope', 'square-full', 'square-h', 'square-minus', 'square-nfi', 'square-parking', 'square-pen', 'square-person-confined', 'square-phone', 'square-phone-flip', 'square-plus', 'square-poll-horizontal', 'square-poll-vertical', 'square-root-variable', 'square-rss', 'square-share-nodes', 'square-up-right', 'square-virus', 'square-xmark', 'staff-snake', 'stairs', 'stamp', 'stapler', 'star', 'star-and-crescent', 'star-half', 'star-half-stroke', 'star-of-david', 'star-of-life', 'sterling-sign', 'stethoscope', 'stop', 'stopwatch', 'stopwatch-20', 'store', 'store-slash', 'street-view', 'strikethrough', 'stroopwafel', 'subscript', 'suitcase', 'suitcase-medical', 'suitcase-rolling', 'sun', 'sun-plant-wilt', 'superscript', 'swatchbook', 'synagogue', 'syringe', 't', 'table', 'table-cells', 'table-cells-large', 'table-columns', 'table-list', 'table-tennis-paddle-ball', 'tablet', 'tablet-button', 'tablet-screen-button', 'tablets', 'tachograph-digital', 'tag', 'tags', 'tape', 'tarp', 'tarp-droplet', 'taxi', 'teeth', 'teeth-open', 'temperature-arrow-down', 'temperature-arrow-up', 'temperature-empty', 'temperature-full', 'temperature-half', 'temperature-high', 'temperature-low', 'temperature-quarter', 'temperature-three-quarters', 'tenge-sign', 'tent', 'tent-arrow-down-to-line', 'tent-arrow-left-right', 'tent-arrow-turn-left', 'tent-arrows-down', 'tents', 'terminal', 'text-height', 'text-slash', 'text-width', 'thermometer', 'thumbs-down', 'thumbs-up', 'thumbtack', 'ticket', 'ticket-simple', 'timeline', 'toggle-off', 'toggle-on', 'toilet', 'toilet-paper', 'toilet-paper-slash', 'toilet-portable', 'toilets-portable', 'toolbox', 'tooth', 'torii-gate', 'tornado', 'tower-broadcast', 'tower-cell', 'tower-observation', 'tractor', 'trademark', 'traffic-light', 'trailer', 'train', 'train-subway', 'train-tram', 'transgender', 'trash', 'trash-arrow-up', 'trash-can', 'trash-can-arrow-up', 'tree', 'tree-city', 'triangle-exclamation', 'trophy', 'trowel', 'trowel-bricks', 'truck', 'truck-arrow-right', 'truck-droplet', 'truck-fast', 'truck-field', 'truck-field-un', 'truck-front', 'truck-medical', 'truck-monster', 'truck-moving', 'truck-pickup', 'truck-plane', 'truck-ramp-box', 'tty', 'turkish-lira-sign', 'turn-down', 'turn-up', 'tv', 'u', 'umbrella', 'umbrella-beach', 'underline', 'universal-access', 'unlock', 'unlock-keyhole', 'up-down', 'up-down-left-right', 'up-long', 'up-right-and-down-left-from-center', 'up-right-from-square', 'upload', 'user', 'user-astronaut', 'user-check', 'user-clock', 'user-doctor', 'user-gear', 'user-graduate', 'user-group', 'user-injured', 'user-large', 'user-large-slash', 'user-lock', 'user-minus', 'user-ninja', 'user-nurse', 'user-pen', 'user-plus', 'user-secret', 'user-shield', 'user-slash', 'user-tag', 'user-tie', 'user-xmark', 'users', 'users-between-lines', 'users-gear', 'users-line', 'users-rays', 'users-rectangle', 'users-slash', 'users-viewfinder', 'utensils', 'v', 'van-shuttle', 'vault', 'vector-square', 'venus', 'venus-double', 'venus-mars', 'vest', 'vest-patches', 'vial', 'vial-circle-check', 'vial-virus', 'vials', 'video', 'video-slash', 'vihara', 'virus', 'virus-covid', 'virus-covid-slash', 'virus-slash', 'viruses', 'voicemail', 'volcano', 'volleyball', 'volume-high', 'volume-low', 'volume-off', 'volume-xmark', 'vr-cardboard', 'w', 'walkie-talkie', 'wallet', 'wand-magic', 'wand-magic-sparkles', 'wand-sparkles', 'warehouse', 'water', 'water-ladder', 'wave-square', 'weight-hanging', 'weight-scale', 'wheat-awn', 'wheat-awn-circle-exclamation', 'wheelchair', 'wheelchair-move', 'whiskey-glass', 'wifi', 'wind', 'window-maximize', 'window-minimize', 'window-restore', 'wine-bottle', 'wine-glass', 'wine-glass-empty', 'won-sign', 'worm', 'wrench', 'x', 'x-ray', 'xmark', 'xmarks-lines', 'y', 'yen-sign', 'yin-yang', 'z' );
			$matches['fa6-fab'] = array ('42-group', '500px', 'accessible-icon', 'accusoft', 'adn', 'adversal', 'affiliatetheme', 'airbnb', 'algolia', 'alipay', 'amazon', 'amazon-pay', 'amilia', 'android', 'angellist', 'angrycreative', 'angular', 'app-store', 'app-store-ios', 'apper', 'apple', 'apple-pay', 'artstation', 'asymmetrik', 'atlassian', 'audible', 'autoprefixer', 'avianex', 'aviato', 'aws', 'bandcamp', 'battle-net', 'behance', 'bilibili', 'bimobject', 'bitbucket', 'bitcoin', 'bity', 'black-tie', 'blackberry', 'blogger', 'blogger-b', 'bluetooth', 'bluetooth-b', 'bootstrap', 'bots', 'btc', 'buffer', 'buromobelexperte', 'buy-n-large', 'buysellads', 'canadian-maple-leaf', 'cc-amazon-pay', 'cc-amex', 'cc-apple-pay', 'cc-diners-club', 'cc-discover', 'cc-jcb', 'cc-mastercard', 'cc-paypal', 'cc-stripe', 'cc-visa', 'centercode', 'centos', 'chrome', 'chromecast', 'cloudflare', 'cloudscale', 'cloudsmith', 'cloudversify', 'cmplid', 'codepen', 'codiepie', 'confluence', 'connectdevelop', 'contao', 'cotton-bureau', 'cpanel', 'creative-commons', 'creative-commons-by', 'creative-commons-nc', 'creative-commons-nc-eu', 'creative-commons-nc-jp', 'creative-commons-nd', 'creative-commons-pd', 'creative-commons-pd-alt', 'creative-commons-remix', 'creative-commons-sa', 'creative-commons-sampling', 'creative-commons-sampling-plus', 'creative-commons-share', 'creative-commons-zero', 'critical-role', 'css3', 'css3-alt', 'cuttlefish', 'd-and-d', 'd-and-d-beyond', 'dailymotion', 'dashcube', 'deezer', 'delicious', 'deploydog', 'deskpro', 'dev', 'deviantart', 'dhl', 'diaspora', 'digg', 'digital-ocean', 'discord', 'discourse', 'dochub', 'docker', 'draft2digital', 'dribbble', 'dropbox', 'drupal', 'dyalog', 'earlybirds', 'ebay', 'edge', 'edge-legacy', 'elementor', 'ello', 'ember', 'empire', 'envira', 'erlang', 'ethereum', 'etsy', 'evernote', 'expeditedssl', 'facebook', 'facebook-f', 'facebook-messenger', 'fantasy-flight-games', 'fedex', 'fedora', 'figma', 'firefox', 'firefox-browser', 'first-order', 'first-order-alt', 'firstdraft', 'flickr', 'flipboard', 'fly', 'font-awesome', 'fonticons', 'fonticons-fi', 'fort-awesome', 'fort-awesome-alt', 'forumbee', 'foursquare', 'free-code-camp', 'freebsd', 'fulcrum', 'galactic-republic', 'galactic-senate', 'get-pocket', 'gg', 'gg-circle', 'git', 'git-alt', 'github', 'github-alt', 'gitkraken', 'gitlab', 'gitter', 'glide', 'glide-g', 'gofore', 'golang', 'goodreads', 'goodreads-g', 'google', 'google-drive', 'google-pay', 'google-play', 'google-plus', 'google-plus-g', 'google-wallet', 'gratipay', 'grav', 'gripfire', 'grunt', 'guilded', 'gulp', 'hacker-news', 'hackerrank', 'hashnode', 'hips', 'hire-a-helper', 'hive', 'hooli', 'hornbill', 'hotjar', 'houzz', 'html5', 'hubspot', 'ideal', 'imdb', 'instagram', 'instalod', 'intercom', 'internet-explorer', 'invision', 'ioxhost', 'itch-io', 'itunes', 'itunes-note', 'java', 'jedi-order', 'jenkins', 'jira', 'joget', 'joomla', 'js', 'jsfiddle', 'kaggle', 'keybase', 'keycdn', 'kickstarter', 'kickstarter-k', 'korvue', 'laravel', 'lastfm', 'leanpub', 'less', 'line', 'linkedin', 'linkedin-in', 'linode', 'linux', 'lyft', 'magento', 'mailchimp', 'mandalorian', 'markdown', 'mastodon', 'maxcdn', 'mdb', 'medapps', 'medium', 'medrt', 'meetup', 'megaport', 'mendeley', 'meta', 'microblog', 'microsoft', 'mix', 'mixcloud', 'mixer', 'mizuni', 'modx', 'monero', 'napster', 'neos', 'nfc-directional', 'nfc-symbol', 'nimblr', 'node', 'node-js', 'npm', 'ns8', 'nutritionix', 'octopus-deploy', 'odnoklassniki', 'old-republic', 'opencart', 'openid', 'opera', 'optin-monster', 'orcid', 'osi', 'padlet', 'page4', 'pagelines', 'palfed', 'patreon', 'paypal', 'perbyte', 'periscope', 'phabricator', 'phoenix-framework', 'phoenix-squadron', 'php', 'pied-piper', 'pied-piper-alt', 'pied-piper-hat', 'pied-piper-pp', 'pinterest', 'pinterest-p', 'pix', 'playstation', 'product-hunt', 'pushed', 'python', 'qq', 'quinscape', 'quora', 'r-project', 'raspberry-pi', 'ravelry', 'react', 'reacteurope', 'readme', 'rebel', 'red-river', 'reddit', 'reddit-alien', 'redhat', 'renren', 'replyd', 'researchgate', 'resolving', 'rev', 'rocketchat', 'rockrms', 'rust', 'safari', 'salesforce', 'sass', 'schlix', 'screenpal', 'scribd', 'searchengin', 'sellcast', 'sellsy', 'servicestack', 'shirtsinbulk', 'shopify', 'shopware', 'simplybuilt', 'sistrix', 'sith', 'sitrox', 'sketch', 'skyatlas', 'skype', 'slack', 'slideshare', 'snapchat', 'soundcloud', 'sourcetree', 'space-awesome', 'speakap', 'speaker-deck', 'spotify', 'square-behance', 'square-dribbble', 'square-facebook', 'square-font-awesome', 'square-font-awesome-stroke', 'square-git', 'square-github', 'square-gitlab', 'square-google-plus', 'square-hacker-news', 'square-instagram', 'square-js', 'square-lastfm', 'square-odnoklassniki', 'square-pied-piper', 'square-pinterest', 'square-reddit', 'square-snapchat', 'square-steam', 'square-tumblr', 'square-twitter', 'square-viadeo', 'square-vimeo', 'square-whatsapp', 'square-xing', 'square-youtube', 'squarespace', 'stack-exchange', 'stack-overflow', 'stackpath', 'staylinked', 'steam', 'steam-symbol', 'sticker-mule', 'strava', 'stripe', 'stripe-s', 'studiovinari', 'stumbleupon', 'stumbleupon-circle', 'superpowers', 'supple', 'suse', 'swift', 'symfony', 'teamspeak', 'telegram', 'tencent-weibo', 'the-red-yeti', 'themeco', 'themeisle', 'think-peaks', 'tiktok', 'trade-federation', 'trello', 'tumblr', 'twitch', 'twitter', 'typo3', 'uber', 'ubuntu', 'uikit', 'umbraco', 'uncharted', 'uniregistry', 'unity', 'unsplash', 'untappd', 'ups', 'usb', 'usps', 'ussunnah', 'vaadin', 'viacoin', 'viadeo', 'viber', 'vimeo', 'vimeo-v', 'vine', 'vk', 'vnv', 'vuejs', 'watchman-monitoring', 'waze', 'weebly', 'weibo', 'weixin', 'whatsapp', 'whmcs', 'wikipedia-w', 'windows', 'wirsindhandwerk', 'wix', 'wizards-of-the-coast', 'wodu', 'wolf-pack-battalion', 'wordpress', 'wordpress-simple', 'wpbeginner', 'wpexplorer', 'wpforms', 'wpressr', 'xbox', 'xing', 'y-combinator', 'yahoo', 'yammer', 'yandex', 'yandex-international', 'yarn', 'yelp', 'yoast', 'youtube', 'zhihu' );
			$matches['fa6-far'] = array ('address-book', 'address-card', 'bell', 'bell-slash', 'bookmark', 'building', 'calendar', 'calendar-check', 'calendar-days', 'calendar-minus', 'calendar-plus', 'calendar-xmark', 'chart-bar', 'chess-bishop', 'chess-king', 'chess-knight', 'chess-pawn', 'chess-queen', 'chess-rook', 'circle', 'circle-check', 'circle-dot', 'circle-down', 'circle-left', 'circle-pause', 'circle-play', 'circle-question', 'circle-right', 'circle-stop', 'circle-up', 'circle-user', 'circle-xmark', 'clipboard', 'clock', 'clone', 'closed-captioning', 'comment', 'comment-dots', 'comments', 'compass', 'copy', 'copyright', 'credit-card', 'envelope', 'envelope-open', 'eye', 'eye-slash', 'face-angry', 'face-dizzy', 'face-flushed', 'face-frown', 'face-frown-open', 'face-grimace', 'face-grin', 'face-grin-beam', 'face-grin-beam-sweat', 'face-grin-hearts', 'face-grin-squint', 'face-grin-squint-tears', 'face-grin-stars', 'face-grin-tears', 'face-grin-tongue', 'face-grin-tongue-squint', 'face-grin-tongue-wink', 'face-grin-wide', 'face-grin-wink', 'face-kiss', 'face-kiss-beam', 'face-kiss-wink-heart', 'face-laugh', 'face-laugh-beam', 'face-laugh-squint', 'face-laugh-wink', 'face-meh', 'face-meh-blank', 'face-rolling-eyes', 'face-sad-cry', 'face-sad-tear', 'face-smile', 'face-smile-beam', 'face-smile-wink', 'face-surprise', 'face-tired', 'file', 'file-audio', 'file-code', 'file-excel', 'file-image', 'file-lines', 'file-pdf', 'file-powerpoint', 'file-video', 'file-word', 'file-zipper', 'flag', 'floppy-disk', 'folder', 'folder-closed', 'folder-open', 'font-awesome', 'futbol', 'gem', 'hand', 'hand-back-fist', 'hand-lizard', 'hand-peace', 'hand-point-down', 'hand-point-left', 'hand-point-right', 'hand-point-up', 'hand-pointer', 'hand-scissors', 'hand-spock', 'handshake', 'hard-drive', 'heart', 'hospital', 'hourglass', 'hourglass-half', 'id-badge', 'id-card', 'image', 'images', 'keyboard', 'lemon', 'life-ring', 'lightbulb', 'map', 'message', 'money-bill-1', 'moon', 'newspaper', 'note-sticky', 'object-group', 'object-ungroup', 'paper-plane', 'paste', 'pen-to-square', 'rectangle-list', 'rectangle-xmark', 'registered', 'share-from-square', 'snowflake', 'square', 'square-caret-down', 'square-caret-left', 'square-caret-right', 'square-caret-up', 'square-check', 'square-full', 'square-minus', 'square-plus', 'star', 'star-half', 'star-half-stroke', 'sun', 'thumbs-down', 'thumbs-up', 'trash-can', 'user', 'window-maximize', 'window-minimize', 'window-restore' );
			$matches['fa6-shims'] =  array ('glass' => 'fa fa-martini-glass-empty', 'envelope-o' => 'far fa-envelope', 'star-o' => 'far fa-star', 'remove' => 'fa fa-xmark', 'close' => 'fa fa-xmark', 'gear' => 'fa fa-gear', 'trash-o' => 'far fa-trash-can', 'home' => 'fa fa-house', 'file-o' => 'far fa-file', 'clock-o' => 'far fa-clock', 'arrow-circle-o-down' => 'far fa-circle-down', 'arrow-circle-o-up' => 'far fa-circle-up', 'play-circle-o' => 'far fa-circle-play', 'repeat' => 'fa fa-arrow-rotate-right', 'rotate-right' => 'fa fa-arrow-rotate-right', 'refresh' => 'fa fa-arrows-rotate', 'list-alt' => 'far fa-rectangle-list', 'dedent' => 'fa fa-outdent', 'video-camera' => 'fa fa-video', 'picture-o' => 'far fa-image', 'photo' => 'far fa-image', 'image' => 'far fa-image', 'map-marker' => 'fa fa-location-dot', 'pencil-square-o' => 'far fa-pen-to-square', 'edit' => 'far fa-pen-to-square', 'share-square-o' => 'fa fa-share-from-square', 'check-square-o' => 'far fa-square-check', 'arrows' => 'fa fa-up-down-left-right', 'times-circle-o' => 'far fa-circle-xmark', 'check-circle-o' => 'far fa-circle-check', 'mail-forward' => 'fa fa-share', 'expand' => 'fa fa-up-right-and-down-left-from-center', 'compress' => 'fa fa-down-left-and-up-right-to-center', 'eye' => 'far fa-eye', 'eye-slash' => 'far fa-eye-slash', 'warning' => 'fa fa-triangle-exclamation', 'calendar' => 'fa fa-calendar-days', 'arrows-v' => 'fa fa-up-down', 'arrows-h' => 'fa fa-left-right', 'bar-chart' => 'fa fa-chart-column', 'bar-chart-o' => 'fa fa-chart-column', 'twitter-square' => 'fab fa-square-twitter', 'facebook-square' => 'fab fa-square-facebook', 'gears' => 'fa fa-gears', 'thumbs-o-up' => 'far fa-thumbs-up', 'thumbs-o-down' => 'far fa-thumbs-down', 'heart-o' => 'far fa-heart', 'sign-out' => 'fa fa-right-from-bracket', 'linkedin-square' => 'fab fa-linkedin', 'thumb-tack' => 'fa fa-thumbtack', 'external-link' => 'fa fa-up-right-from-square', 'sign-in' => 'fa fa-right-to-bracket', 'github-square' => 'fab fa-square-github', 'lemon-o' => 'far fa-lemon', 'square-o' => 'far fa-square', 'bookmark-o' => 'far fa-bookmark', 'twitter' => 'fab fa-twitter', 'facebook' => 'fab fa-facebook-f', 'facebook-f' => 'fab fa-facebook-f', 'github' => 'fab fa-github', 'credit-card' => 'far fa-credit-card', 'feed' => 'fa fa-rss', 'hdd-o' => 'far fa-hard-drive', 'hand-o-right' => 'far fa-hand-point-right', 'hand-o-left' => 'far fa-hand-point-left', 'hand-o-up' => 'far fa-hand-point-up', 'hand-o-down' => 'far fa-hand-point-down', 'globe' => 'fa fa-earth-americas', 'tasks' => 'fa fa-bars-progress', 'arrows-alt' => 'fa fa-maximize', 'group' => 'fa fa-users', 'chain' => 'fa fa-link', 'cut' => 'fa fa-scissors', 'files-o' => 'far fa-copy', 'floppy-o' => 'far fa-floppy-disk', 'save' => 'far fa-floppy-disk', 'navicon' => 'fa fa-bars', 'reorder' => 'fa fa-bars', 'magic' => 'fa fa-wand-magic-sparkles', 'pinterest' => 'fab fa-pinterest', 'pinterest-square' => 'fab fa-square-pinterest', 'google-plus-square' => 'fab fa-square-google-plus', 'google-plus' => 'fab fa-google-plus-g', 'money' => 'fa fa-money-bill-1', 'unsorted' => 'fa fa-sort', 'sort-desc' => 'fa fa-sort-down', 'sort-asc' => 'fa fa-sort-up', 'linkedin' => 'fab fa-linkedin-in', 'rotate-left' => 'fa fa-arrow-rotate-left', 'legal' => 'fa fa-gavel', 'tachometer' => 'fa fa-gauge-high', 'dashboard' => 'fa fa-gauge-high', 'comment-o' => 'far fa-comment', 'comments-o' => 'far fa-comments', 'flash' => 'fa fa-bolt', 'clipboard' => 'fa fa-paste', 'lightbulb-o' => 'far fa-lightbulb', 'exchange' => 'fa fa-right-left', 'cloud-download' => 'fa fa-cloud-arrow-down', 'cloud-upload' => 'fa fa-cloud-arrow-up', 'bell-o' => 'far fa-bell', 'cutlery' => 'fa fa-utensils', 'file-text-o' => 'far fa-file-lines', 'building-o' => 'far fa-building', 'hospital-o' => 'far fa-hospital', 'tablet' => 'fa fa-tablet-screen-button', 'mobile' => 'fa fa-mobile-screen-button', 'mobile-phone' => 'fa fa-mobile-screen-button', 'circle-o' => 'far fa-circle', 'mail-reply' => 'fa fa-reply', 'github-alt' => 'fab fa-github-alt', 'folder-o' => 'far fa-folder', 'folder-open-o' => 'far fa-folder-open', 'smile-o' => 'far fa-face-smile', 'frown-o' => 'far fa-face-frown', 'meh-o' => 'far fa-face-meh', 'keyboard-o' => 'far fa-keyboard', 'flag-o' => 'far fa-flag', 'mail-reply-all' => 'fa fa-reply-all', 'star-half-o' => 'far fa-star-half-stroke', 'star-half-empty' => 'far fa-star-half-stroke', 'star-half-full' => 'far fa-star-half-stroke', 'code-fork' => 'fa fa-code-branch', 'chain-broken' => 'fa fa-link-slash', 'unlink' => 'fa fa-link-slash', 'calendar-o' => 'far fa-calendar', 'maxcdn' => 'fab fa-maxcdn', 'html5' => 'fab fa-html5', 'css3' => 'fab fa-css3', 'unlock-alt' => 'fa fa-unlock', 'minus-square-o' => 'far fa-square-minus', 'level-up' => 'fa fa-turn-up', 'level-down' => 'fa fa-turn-down', 'pencil-square' => 'fa fa-square-pen', 'external-link-square' => 'fa fa-square-up-right', 'compass' => 'far fa-compass', 'caret-square-o-down' => 'far fa-square-caret-down', 'toggle-down' => 'far fa-square-caret-down', 'caret-square-o-up' => 'far fa-square-caret-up', 'toggle-up' => 'far fa-square-caret-up', 'caret-square-o-right' => 'far fa-square-caret-right', 'toggle-right' => 'far fa-square-caret-right', 'eur' => 'fa fa-euro-sign', 'euro' => 'fa fa-euro-sign', 'gbp' => 'fa fa-sterling-sign', 'usd' => 'fa fa-dollar-sign', 'dollar' => 'fa fa-dollar-sign', 'inr' => 'fa fa-indian-rupee-sign', 'rupee' => 'fa fa-indian-rupee-sign', 'jpy' => 'fa fa-yen-sign', 'cny' => 'fa fa-yen-sign', 'rmb' => 'fa fa-yen-sign', 'yen' => 'fa fa-yen-sign', 'rub' => 'fa fa-ruble-sign', 'ruble' => 'fa fa-ruble-sign', 'rouble' => 'fa fa-ruble-sign', 'krw' => 'fa fa-won-sign', 'won' => 'fa fa-won-sign', 'btc' => 'fab fa-btc', 'bitcoin' => 'fab fa-btc', 'file-text' => 'fa fa-file-lines', 'sort-alpha-asc' => 'fa fa-arrow-down-a-z', 'sort-alpha-desc' => 'fa fa-arrow-down-z-a', 'sort-amount-asc' => 'fa fa-arrow-down-short-wide', 'sort-amount-desc' => 'fa fa-arrow-down-wide-short', 'sort-numeric-asc' => 'fa fa-arrow-down-1-9', 'sort-numeric-desc' => 'fa fa-arrow-down-9-1', 'youtube-square' => 'fab fa-square-youtube', 'youtube' => 'fab fa-youtube', 'xing' => 'fab fa-xing', 'xing-square' => 'fab fa-square-xing', 'youtube-play' => 'fab fa-youtube', 'dropbox' => 'fab fa-dropbox', 'stack-overflow' => 'fab fa-stack-overflow', 'instagram' => 'fab fa-instagram', 'flickr' => 'fab fa-flickr', 'adn' => 'fab fa-adn', 'bitbucket' => 'fab fa-bitbucket', 'bitbucket-square' => 'fab fa-bitbucket', 'tumblr' => 'fab fa-tumblr', 'tumblr-square' => 'fab fa-square-tumblr', 'long-arrow-down' => 'fa fa-down-long', 'long-arrow-up' => 'fa fa-up-long', 'long-arrow-left' => 'fa fa-left-long', 'long-arrow-right' => 'fa fa-right-long', 'apple' => 'fab fa-apple', 'windows' => 'fab fa-windows', 'android' => 'fab fa-android', 'linux' => 'fab fa-linux', 'dribbble' => 'fab fa-dribbble', 'skype' => 'fab fa-skype', 'foursquare' => 'fab fa-foursquare', 'trello' => 'fab fa-trello', 'gratipay' => 'fab fa-gratipay', 'gittip' => 'fab fa-gratipay', 'sun-o' => 'far fa-sun', 'moon-o' => 'far fa-moon', 'vk' => 'fab fa-vk', 'weibo' => 'fab fa-weibo', 'renren' => 'fab fa-renren', 'pagelines' => 'fab fa-pagelines', 'stack-exchange' => 'fab fa-stack-exchange', 'arrow-circle-o-right' => 'far fa-circle-right', 'arrow-circle-o-left' => 'far fa-circle-left', 'caret-square-o-left' => 'far fa-square-caret-left', 'toggle-left' => 'far fa-square-caret-left', 'dot-circle-o' => 'far fa-circle-dot', 'vimeo-square' => 'fab fa-square-vimeo', 'try' => 'fa fa-turkish-lira-sign', 'turkish-lira' => 'fa fa-turkish-lira-sign', 'plus-square-o' => 'far fa-square-plus', 'slack' => 'fab fa-slack', 'wordpress' => 'fab fa-wordpress', 'openid' => 'fab fa-openid', 'institution' => 'fa fa-building-columns', 'bank' => 'fa fa-building-columns', 'mortar-board' => 'fa fa-graduation-cap', 'yahoo' => 'fab fa-yahoo', 'google' => 'fab fa-google', 'reddit' => 'fab fa-reddit', 'reddit-square' => 'fab fa-square-reddit', 'stumbleupon-circle' => 'fab fa-stumbleupon-circle', 'stumbleupon' => 'fab fa-stumbleupon', 'delicious' => 'fab fa-delicious', 'digg' => 'fab fa-digg', 'pied-piper-pp' => 'fab fa-pied-piper-pp', 'pied-piper-alt' => 'fab fa-pied-piper-alt', 'drupal' => 'fab fa-drupal', 'joomla' => 'fab fa-joomla', 'behance' => 'fab fa-behance', 'behance-square' => 'fab fa-square-behance', 'steam' => 'fab fa-steam', 'steam-square' => 'fab fa-square-steam', 'automobile' => 'fa fa-car', 'cab' => 'fa fa-taxi', 'spotify' => 'fab fa-spotify', 'deviantart' => 'fab fa-deviantart', 'soundcloud' => 'fab fa-soundcloud', 'file-pdf-o' => 'far fa-file-pdf', 'file-word-o' => 'far fa-file-word', 'file-excel-o' => 'far fa-file-excel', 'file-powerpoint-o' => 'far fa-file-powerpoint', 'file-image-o' => 'far fa-file-image', 'file-photo-o' => 'far fa-file-image', 'file-picture-o' => 'far fa-file-image', 'file-archive-o' => 'far fa-file-zipper', 'file-zip-o' => 'far fa-file-zipper', 'file-audio-o' => 'far fa-file-audio', 'file-sound-o' => 'far fa-file-audio', 'file-video-o' => 'far fa-file-video', 'file-movie-o' => 'far fa-file-video', 'file-code-o' => 'far fa-file-code', 'vine' => 'fab fa-vine', 'codepen' => 'fab fa-codepen', 'jsfiddle' => 'fab fa-jsfiddle', 'life-bouy' => 'fa fa-life-ring', 'life-buoy' => 'fa fa-life-ring', 'life-saver' => 'fa fa-life-ring', 'support' => 'fa fa-life-ring', 'circle-o-notch' => 'fa fa-circle-notch', 'rebel' => 'fab fa-rebel', 'ra' => 'fab fa-rebel', 'resistance' => 'fab fa-rebel', 'empire' => 'fab fa-empire', 'ge' => 'fab fa-empire', 'git-square' => 'fab fa-square-git', 'git' => 'fab fa-git', 'hacker-news' => 'fab fa-hacker-news', 'y-combinator-square' => 'fab fa-hacker-news', 'yc-square' => 'fab fa-hacker-news', 'tencent-weibo' => 'fab fa-tencent-weibo', 'qq' => 'fab fa-qq', 'weixin' => 'fab fa-weixin', 'wechat' => 'fab fa-weixin', 'send' => 'fa fa-paper-plane', 'paper-plane-o' => 'far fa-paper-plane', 'send-o' => 'far fa-paper-plane', 'circle-thin' => 'far fa-circle', 'header' => 'fa fa-heading', 'futbol-o' => 'far fa-futbol', 'soccer-ball-o' => 'far fa-futbol', 'slideshare' => 'fab fa-slideshare', 'twitch' => 'fab fa-twitch', 'yelp' => 'fab fa-yelp', 'newspaper-o' => 'far fa-newspaper', 'paypal' => 'fab fa-paypal', 'google-wallet' => 'fab fa-google-wallet', 'cc-visa' => 'fab fa-cc-visa', 'cc-mastercard' => 'fab fa-cc-mastercard', 'cc-discover' => 'fab fa-cc-discover', 'cc-amex' => 'fab fa-cc-amex', 'cc-paypal' => 'fab fa-cc-paypal', 'cc-stripe' => 'fab fa-cc-stripe', 'bell-slash-o' => 'far fa-bell-slash', 'trash' => 'fa fa-trash-can', 'copyright' => 'far fa-copyright', 'eyedropper' => 'fa fa-eye-dropper', 'area-chart' => 'fa fa-chart-area', 'pie-chart' => 'fa fa-chart-pie', 'line-chart' => 'fa fa-chart-line', 'lastfm' => 'fab fa-lastfm', 'lastfm-square' => 'fab fa-square-lastfm', 'ioxhost' => 'fab fa-ioxhost', 'angellist' => 'fab fa-angellist', 'cc' => 'far fa-closed-captioning', 'ils' => 'fa fa-shekel-sign', 'shekel' => 'fa fa-shekel-sign', 'sheqel' => 'fa fa-shekel-sign', 'buysellads' => 'fab fa-buysellads', 'connectdevelop' => 'fab fa-connectdevelop', 'dashcube' => 'fab fa-dashcube', 'forumbee' => 'fab fa-forumbee', 'leanpub' => 'fab fa-leanpub', 'sellsy' => 'fab fa-sellsy', 'shirtsinbulk' => 'fab fa-shirtsinbulk', 'simplybuilt' => 'fab fa-simplybuilt', 'skyatlas' => 'fab fa-skyatlas', 'diamond' => 'far fa-gem', 'transgender' => 'fa fa-mars-and-venus', 'intersex' => 'fa fa-mars-and-venus', 'transgender-alt' => 'fa fa-transgender', 'facebook-official' => 'fab fa-facebook', 'pinterest-p' => 'fab fa-pinterest-p', 'whatsapp' => 'fab fa-whatsapp', 'hotel' => 'fa fa-bed', 'viacoin' => 'fab fa-viacoin', 'medium' => 'fab fa-medium', 'y-combinator' => 'fab fa-y-combinator', 'yc' => 'fab fa-y-combinator', 'optin-monster' => 'fab fa-optin-monster', 'opencart' => 'fab fa-opencart', 'expeditedssl' => 'fab fa-expeditedssl', 'battery-4' => 'fa fa-battery-full', 'battery' => 'fa fa-battery-full', 'battery-3' => 'fa fa-battery-three-quarters', 'battery-2' => 'fa fa-battery-half', 'battery-1' => 'fa fa-battery-quarter', 'battery-0' => 'fa fa-battery-empty', 'object-group' => 'far fa-object-group', 'object-ungroup' => 'far fa-object-ungroup', 'sticky-note-o' => 'far fa-note-sticky', 'cc-jcb' => 'fab fa-cc-jcb', 'cc-diners-club' => 'fab fa-cc-diners-club', 'clone' => 'far fa-clone', 'hourglass-o' => 'fa fa-hourglass', 'hourglass-1' => 'fa fa-hourglass-start', 'hourglass-2' => 'fa fa-hourglass-half', 'hourglass-3' => 'fa fa-hourglass-end', 'hand-rock-o' => 'far fa-hand-back-fist', 'hand-grab-o' => 'far fa-hand-back-fist', 'hand-paper-o' => 'far fa-hand', 'hand-stop-o' => 'far fa-hand', 'hand-scissors-o' => 'far fa-hand-scissors', 'hand-lizard-o' => 'far fa-hand-lizard', 'hand-spock-o' => 'far fa-hand-spock', 'hand-pointer-o' => 'far fa-hand-pointer', 'hand-peace-o' => 'far fa-hand-peace', 'registered' => 'far fa-registered', 'creative-commons' => 'fab fa-creative-commons', 'gg' => 'fab fa-gg', 'gg-circle' => 'fab fa-gg-circle', 'odnoklassniki' => 'fab fa-odnoklassniki', 'odnoklassniki-square' => 'fab fa-square-odnoklassniki', 'get-pocket' => 'fab fa-get-pocket', 'wikipedia-w' => 'fab fa-wikipedia-w', 'safari' => 'fab fa-safari', 'chrome' => 'fab fa-chrome', 'firefox' => 'fab fa-firefox', 'opera' => 'fab fa-opera', 'internet-explorer' => 'fab fa-internet-explorer', 'television' => 'fa fa-tv', 'contao' => 'fab fa-contao', '500px' => 'fab fa-500px', 'amazon' => 'fab fa-amazon', 'calendar-plus-o' => 'far fa-calendar-plus', 'calendar-minus-o' => 'far fa-calendar-minus', 'calendar-times-o' => 'far fa-calendar-xmark', 'calendar-check-o' => 'far fa-calendar-check', 'map-o' => 'far fa-map', 'commenting' => 'fa fa-comment-dots', 'commenting-o' => 'far fa-comment-dots', 'houzz' => 'fab fa-houzz', 'vimeo' => 'fab fa-vimeo-v', 'black-tie' => 'fab fa-black-tie', 'fonticons' => 'fab fa-fonticons', 'reddit-alien' => 'fab fa-reddit-alien', 'edge' => 'fab fa-edge', 'credit-card-alt' => 'fa fa-credit-card', 'codiepie' => 'fab fa-codiepie', 'modx' => 'fab fa-modx', 'fort-awesome' => 'fab fa-fort-awesome', 'usb' => 'fab fa-usb', 'product-hunt' => 'fab fa-product-hunt', 'mixcloud' => 'fab fa-mixcloud', 'scribd' => 'fab fa-scribd', 'pause-circle-o' => 'far fa-circle-pause', 'stop-circle-o' => 'far fa-circle-stop', 'bluetooth' => 'fab fa-bluetooth', 'bluetooth-b' => 'fab fa-bluetooth-b', 'gitlab' => 'fab fa-gitlab', 'wpbeginner' => 'fab fa-wpbeginner', 'wpforms' => 'fab fa-wpforms', 'envira' => 'fab fa-envira', 'wheelchair-alt' => 'fab fa-accessible-icon', 'question-circle-o' => 'far fa-circle-question', 'volume-control-phone' => 'fa fa-phone-volume', 'asl-interpreting' => 'fa fa-hands-asl-interpreting', 'deafness' => 'fa fa-ear-deaf', 'hard-of-hearing' => 'fa fa-ear-deaf', 'glide' => 'fab fa-glide', 'glide-g' => 'fab fa-glide-g', 'signing' => 'fa fa-hands', 'viadeo' => 'fab fa-viadeo', 'viadeo-square' => 'fab fa-square-viadeo', 'snapchat' => 'fab fa-snapchat', 'snapchat-ghost' => 'fab fa-snapchat', 'snapchat-square' => 'fab fa-square-snapchat', 'pied-piper' => 'fab fa-pied-piper', 'first-order' => 'fab fa-first-order', 'yoast' => 'fab fa-yoast', 'themeisle' => 'fab fa-themeisle', 'google-plus-official' => 'fab fa-google-plus', 'google-plus-circle' => 'fab fa-google-plus', 'font-awesome' => 'fab fa-font-awesome', 'fa' => 'fab fa-font-awesome', 'handshake-o' => 'far fa-handshake', 'envelope-open-o' => 'far fa-envelope-open', 'linode' => 'fab fa-linode', 'address-book-o' => 'far fa-address-book', 'vcard' => 'fa fa-address-card', 'address-card-o' => 'far fa-address-card', 'vcard-o' => 'far fa-address-card', 'user-circle-o' => 'far fa-circle-user', 'user-o' => 'far fa-user', 'id-badge' => 'far fa-id-badge', 'drivers-license' => 'fa fa-id-card', 'id-card-o' => 'far fa-id-card', 'drivers-license-o' => 'far fa-id-card', 'quora' => 'fab fa-quora', 'free-code-camp' => 'fab fa-free-code-camp', 'telegram' => 'fab fa-telegram', 'thermometer-4' => 'fa fa-temperature-full', 'thermometer' => 'fa fa-temperature-full', 'thermometer-3' => 'fa fa-temperature-three-quarters', 'thermometer-2' => 'fa fa-temperature-half', 'thermometer-1' => 'fa fa-temperature-quarter', 'thermometer-0' => 'fa fa-temperature-empty', 'bathtub' => 'fa fa-bath', 's15' => 'fa fa-bath', 'window-maximize' => 'far fa-window-maximize', 'window-restore' => 'far fa-window-restore', 'times-rectangle' => 'fa fa-rectangle-xmark', 'window-close-o' => 'far fa-rectangle-xmark', 'times-rectangle-o' => 'far fa-rectangle-xmark', 'bandcamp' => 'fab fa-bandcamp', 'grav' => 'fab fa-grav', 'etsy' => 'fab fa-etsy', 'imdb' => 'fab fa-imdb', 'ravelry' => 'fab fa-ravelry', 'eercast' => 'fab fa-sellcast', 'snowflake-o' => 'far fa-snowflake', 'superpowers' => 'fab fa-superpowers', 'wpexplorer' => 'fab fa-wpexplorer', 'meetup' => 'fab fa-meetup',);

			// FontAwesome 5
			$matches['fa5-fab'] = array ('500px','accessible-icon','accusoft','acquisitions-incorporated','adn','adobe','adversal','affiliatetheme','airbnb','algolia','alipay','amazon','amazon-pay','amilia','android','angellist','angrycreative','angular','app-store','app-store-ios','apper','apple','apple-pay','artstation','asymmetrik','atlassian','audible','autoprefixer','avianex','aviato','aws','bandcamp','battle-net','behance','behance-square','bimobject','bitbucket','bitcoin','bity','black-tie','blackberry','blogger','blogger-b','bluetooth','bluetooth-b','bootstrap','btc','buffer','buromobelexperte','buysellads','canadian-maple-leaf','cc-amazon-pay','cc-amex','cc-apple-pay','cc-diners-club','cc-discover','cc-jcb','cc-mastercard','cc-paypal','cc-stripe','cc-visa','centercode','centos','chrome','chromecast','cloudscale','cloudsmith','cloudversify','codepen','codiepie','confluence','connectdevelop','contao','cpanel','creative-commons','creative-commons-by','creative-commons-nc','creative-commons-nc-eu','creative-commons-nc-jp','creative-commons-nd','creative-commons-pd','creative-commons-pd-alt','creative-commons-remix','creative-commons-sa','creative-commons-sampling','creative-commons-sampling-plus','creative-commons-share','creative-commons-zero','critical-role','css3','css3-alt','cuttlefish','d-and-d','d-and-d-beyond','dashcube','delicious','deploydog','deskpro','dev','deviantart','dhl','diaspora','digg','digital-ocean','discord','discourse','dochub','docker','draft2digital','dribbble','dribbble-square','dropbox','drupal','dyalog','earlybirds','ebay','edge','elementor','ello','ember','empire','envira','erlang','ethereum','etsy','evernote','expeditedssl','facebook','facebook-f','facebook-messenger','facebook-square','fantasy-flight-games','fedex','fedora','figma','firefox','first-order','first-order-alt','firstdraft','flickr','flipboard','fly','font-awesome','font-awesome-alt','font-awesome-flag','font-awesome-logo-full','fonticons','fonticons-fi','fort-awesome','fort-awesome-alt','forumbee','foursquare','free-code-camp','freebsd','fulcrum','galactic-republic','galactic-senate','get-pocket','gg','gg-circle','git','git-square','github','github-alt','github-square','gitkraken','gitlab','gitter','glide','glide-g','gofore','goodreads','goodreads-g','google','google-drive','google-play','google-plus','google-plus-g','google-plus-square','google-wallet','gratipay','grav','gripfire','grunt','gulp','hacker-news','hacker-news-square','hackerrank','hips','hire-a-helper','hooli','hornbill','hotjar','houzz','html5','hubspot','imdb','instagram','intercom','internet-explorer','invision','ioxhost','itch-io','itunes','itunes-note','java','jedi-order','jenkins','jira','joget','joomla','js','js-square','jsfiddle','kaggle','keybase','keycdn','kickstarter','kickstarter-k','korvue','laravel','lastfm','lastfm-square','leanpub','less','line','linkedin','linkedin-in','linode','linux','lyft','magento','mailchimp','mandalorian','markdown','mastodon','maxcdn','medapps','medium','medium-m','medrt','meetup','megaport','mendeley','microsoft','mix','mixcloud','mizuni','modx','monero','napster','neos','nimblr','nintendo-switch','node','node-js','npm','ns8','nutritionix','odnoklassniki','odnoklassniki-square','old-republic','opencart','openid','opera','optin-monster','osi','page4','pagelines','palfed','patreon','paypal','penny-arcade','periscope','phabricator','phoenix-framework','phoenix-squadron','php','pied-piper','pied-piper-alt','pied-piper-hat','pied-piper-pp','pinterest','pinterest-p','pinterest-square','playstation','product-hunt','pushed','python','qq','quinscape','quora','r-project','raspberry-pi','ravelry','react','reacteurope','readme','rebel','red-river','reddit','reddit-alien','reddit-square','redhat','renren','replyd','researchgate','resolving','rev','rocketchat','rockrms','safari','salesforce','sass','schlix','scribd','searchengin','sellcast','sellsy','servicestack','shirtsinbulk','shopware','simplybuilt','sistrix','sith','sketch','skyatlas','skype','slack','slack-hash','slideshare','snapchat','snapchat-ghost','snapchat-square','soundcloud','sourcetree','speakap','speaker-deck','spotify','squarespace','stack-exchange','stack-overflow','staylinked','steam','steam-square','steam-symbol','sticker-mule','strava','stripe','stripe-s','studiovinari','stumbleupon','stumbleupon-circle','superpowers','supple','suse','symfony','teamspeak','telegram','telegram-plane','tencent-weibo','the-red-yeti','themeco','themeisle','think-peaks','trade-federation','trello','tripadvisor','tumblr','tumblr-square','twitch','twitter','twitter-square','typo3','uber','ubuntu','uikit','uniregistry','untappd','ups','usb','usps','ussunnah','vaadin','viacoin','viadeo','viadeo-square','viber','vimeo','vimeo-square','vimeo-v','vine','vk','vnv','vuejs','waze','weebly','weibo','weixin','whatsapp','whatsapp-square','whmcs','wikipedia-w','windows','wix','wizards-of-the-coast','wolf-pack-battalion','wordpress','wordpress-simple','wpbeginner','wpexplorer','wpforms','wpressr','xbox','xing','xing-square','y-combinator','yahoo','yammer','yandex','yandex-international','yarn','yelp','yoast','youtube','youtube-square','zhihu',  );
			$matches['fa5-fas'] =   array ('ad','address-book','address-card','adjust','air-freshener','align-center','align-justify','align-left','align-right','allergies','ambulance','american-sign-language-interpreting','anchor','angle-double-down','angle-double-left','angle-double-right','angle-double-up','angle-down','angle-left','angle-right','angle-up','angry','ankh','apple-alt','archive','archway','arrow-alt-circle-down','arrow-alt-circle-left','arrow-alt-circle-right','arrow-alt-circle-up','arrow-circle-down','arrow-circle-left','arrow-circle-right','arrow-circle-up','arrow-down','arrow-left','arrow-right','arrow-up','arrows-alt','arrows-alt-h','arrows-alt-v','assistive-listening-systems','asterisk','at','atlas','atom','audio-description','award','baby','baby-carriage','backspace','backward','bacon','balance-scale','ban','band-aid','barcode','bars','baseball-ball','basketball-ball','bath','battery-empty','battery-full','battery-half','battery-quarter','battery-three-quarters','bed','beer','bell','bell-slash','bezier-curve','bible','bicycle','binoculars','biohazard','birthday-cake','blender','blender-phone','blind','blog','bold','bolt','bomb','bone','bong','book','book-dead','book-medical','book-open','book-reader','bookmark','bowling-ball','box','box-open','boxes','braille','brain','bread-slice','briefcase','briefcase-medical','broadcast-tower','broom','brush','bug','building','bullhorn','bullseye','burn','bus','bus-alt','business-time','calculator','calendar','calendar-alt','calendar-check','calendar-day','calendar-minus','calendar-plus','calendar-times','calendar-week','camera','camera-retro','campground','candy-cane','cannabis','capsules','car','car-alt','car-battery','car-crash','car-side','caret-down','caret-left','caret-right','caret-square-down','caret-square-left','caret-square-right','caret-square-up','caret-up','carrot','cart-arrow-down','cart-plus','cash-register','cat','certificate','chair','chalkboard','chalkboard-teacher','charging-station','chart-area','chart-bar','chart-line','chart-pie','check','check-circle','check-double','check-square','cheese','chess','chess-bishop','chess-board','chess-king','chess-knight','chess-pawn','chess-queen','chess-rook','chevron-circle-down','chevron-circle-left','chevron-circle-right','chevron-circle-up','chevron-down','chevron-left','chevron-right','chevron-up','child','church','circle','circle-notch','city','clinic-medical','clipboard','clipboard-check','clipboard-list','clock','clone','closed-captioning','cloud','cloud-download-alt','cloud-meatball','cloud-moon','cloud-moon-rain','cloud-rain','cloud-showers-heavy','cloud-sun','cloud-sun-rain','cloud-upload-alt','cocktail','code','code-branch','coffee','cog','cogs','coins','columns','comment','comment-alt','comment-dollar','comment-dots','comment-medical','comment-slash','comments','comments-dollar','compact-disc','compass','compress','compress-arrows-alt','concierge-bell','cookie','cookie-bite','copy','copyright','couch','credit-card','crop','crop-alt','cross','crosshairs','crow','crown','crutch','cube','cubes','cut','database','deaf','democrat','desktop','dharmachakra','diagnoses','dice','dice-d20','dice-d6','dice-five','dice-four','dice-one','dice-six','dice-three','dice-two','digital-tachograph','directions','divide','dizzy','dna','dog','dollar-sign','dolly','dolly-flatbed','donate','door-closed','door-open','dot-circle','dove','download','drafting-compass','dragon','draw-polygon','drum','drum-steelpan','drumstick-bite','dumbbell','dumpster','dumpster-fire','dungeon','edit','egg','eject','ellipsis-h','ellipsis-v','envelope','envelope-open','envelope-open-text','envelope-square','equals','eraser','ethernet','euro-sign','exchange-alt','exclamation','exclamation-circle','exclamation-triangle','expand','expand-arrows-alt','external-link-alt','external-link-square-alt','eye','eye-dropper','eye-slash','fast-backward','fast-forward','fax','feather','feather-alt','female','fighter-jet','file','file-alt','file-archive','file-audio','file-code','file-contract','file-csv','file-download','file-excel','file-export','file-image','file-import','file-invoice','file-invoice-dollar','file-medical','file-medical-alt','file-pdf','file-powerpoint','file-prescription','file-signature','file-upload','file-video','file-word','fill','fill-drip','film','filter','fingerprint','fire','fire-alt','fire-extinguisher','first-aid','fish','fist-raised','flag','flag-checkered','flag-usa','flask','flushed','folder','folder-minus','folder-open','folder-plus','font','font-awesome-logo-full','football-ball','forward','frog','frown','frown-open','funnel-dollar','futbol','gamepad','gas-pump','gavel','gem','genderless','ghost','gift','gifts','glass-cheers','glass-martini','glass-martini-alt','glass-whiskey','glasses','globe','globe-africa','globe-americas','globe-asia','globe-europe','golf-ball','gopuram','graduation-cap','greater-than','greater-than-equal','grimace','grin','grin-alt','grin-beam','grin-beam-sweat','grin-hearts','grin-squint','grin-squint-tears','grin-stars','grin-tears','grin-tongue','grin-tongue-squint','grin-tongue-wink','grin-wink','grip-horizontal','grip-lines','grip-lines-vertical','grip-vertical','guitar','h-square','hamburger','hammer','hamsa','hand-holding','hand-holding-heart','hand-holding-usd','hand-lizard','hand-middle-finger','hand-paper','hand-peace','hand-point-down','hand-point-left','hand-point-right','hand-point-up','hand-pointer','hand-rock','hand-scissors','hand-spock','hands','hands-helping','handshake','hanukiah','hard-hat','hashtag','hat-wizard','haykal','hdd','heading','headphones','headphones-alt','headset','heart','heart-broken','heartbeat','helicopter','highlighter','hiking','hippo','history','hockey-puck','holly-berry','home','horse','horse-head','hospital','hospital-alt','hospital-symbol','hot-tub','hotdog','hotel','hourglass','hourglass-end','hourglass-half','hourglass-start','house-damage','hryvnia','i-cursor','ice-cream','icicles','id-badge','id-card','id-card-alt','igloo','image','images','inbox','indent','industry','infinity','info','info-circle','italic','jedi','joint','journal-whills','kaaba','key','keyboard','khanda','kiss','kiss-beam','kiss-wink-heart','kiwi-bird','landmark','language','laptop','laptop-code','laptop-medical','laugh','laugh-beam','laugh-squint','laugh-wink','layer-group','leaf','lemon','less-than','less-than-equal','level-down-alt','level-up-alt','life-ring','lightbulb','link','lira-sign','list','list-alt','list-ol','list-ul','location-arrow','lock','lock-open','long-arrow-alt-down','long-arrow-alt-left','long-arrow-alt-right','long-arrow-alt-up','low-vision','luggage-cart','magic','magnet','mail-bulk','male','map','map-marked','map-marked-alt','map-marker','map-marker-alt','map-pin','map-signs','marker','mars','mars-double','mars-stroke','mars-stroke-h','mars-stroke-v','mask','medal','medkit','meh','meh-blank','meh-rolling-eyes','memory','menorah','mercury','meteor','microchip','microphone','microphone-alt','microphone-alt-slash','microphone-slash','microscope','minus','minus-circle','minus-square','mitten','mobile','mobile-alt','money-bill','money-bill-alt','money-bill-wave','money-bill-wave-alt','money-check','money-check-alt','monument','moon','mortar-pestle','mosque','motorcycle','mountain','mouse-pointer','mug-hot','music','network-wired','neuter','newspaper','not-equal','notes-medical','object-group','object-ungroup','oil-can','om','otter','outdent','pager','paint-brush','paint-roller','palette','pallet','paper-plane','paperclip','parachute-box','paragraph','parking','passport','pastafarianism','paste','pause','pause-circle','paw','peace','pen','pen-alt','pen-fancy','pen-nib','pen-square','pencil-alt','pencil-ruler','people-carry','pepper-hot','percent','percentage','person-booth','phone','phone-slash','phone-square','phone-volume','piggy-bank','pills','pizza-slice','place-of-worship','plane','plane-arrival','plane-departure','play','play-circle','plug','plus','plus-circle','plus-square','podcast','poll','poll-h','poo','poo-storm','poop','portrait','pound-sign','power-off','pray','praying-hands','prescription','prescription-bottle','prescription-bottle-alt','print','procedures','project-diagram','puzzle-piece','qrcode','question','question-circle','quidditch','quote-left','quote-right','quran','radiation','radiation-alt','rainbow','random','receipt','recycle','redo','redo-alt','registered','reply','reply-all','republican','restroom','retweet','ribbon','ring','road','robot','rocket','route','rss','rss-square','ruble-sign','ruler','ruler-combined','ruler-horizontal','ruler-vertical','running','rupee-sign','sad-cry','sad-tear','satellite','satellite-dish','save','school','screwdriver','scroll','sd-card','search','search-dollar','search-location','search-minus','search-plus','seedling','server','shapes','share','share-alt','share-alt-square','share-square','shekel-sign','shield-alt','ship','shipping-fast','shoe-prints','shopping-bag','shopping-basket','shopping-cart','shower','shuttle-van','sign','sign-in-alt','sign-language','sign-out-alt','signal','signature','sim-card','sitemap','skating','skiing','skiing-nordic','skull','skull-crossbones','slash','sleigh','sliders-h','smile','smile-beam','smile-wink','smog','smoking','smoking-ban','sms','snowboarding','snowflake','snowman','snowplow','socks','solar-panel','sort','sort-alpha-down','sort-alpha-up','sort-amount-down','sort-amount-up','sort-down','sort-numeric-down','sort-numeric-up','sort-up','spa','space-shuttle','spider','spinner','splotch','spray-can','square','square-full','square-root-alt','stamp','star','star-and-crescent','star-half','star-half-alt','star-of-david','star-of-life','step-backward','step-forward','stethoscope','sticky-note','stop','stop-circle','stopwatch','store','store-alt','stream','street-view','strikethrough','stroopwafel','subscript','subway','suitcase','suitcase-rolling','sun','superscript','surprise','swatchbook','swimmer','swimming-pool','synagogue','sync','sync-alt','syringe','table','table-tennis','tablet','tablet-alt','tablets','tachometer-alt','tag','tags','tape','tasks','taxi','teeth','teeth-open','temperature-high','temperature-low','tenge','terminal','text-height','text-width','th','th-large','th-list','theater-masks','thermometer','thermometer-empty','thermometer-full','thermometer-half','thermometer-quarter','thermometer-three-quarters','thumbs-down','thumbs-up','thumbtack','ticket-alt','times','times-circle','tint','tint-slash','tired','toggle-off','toggle-on','toilet','toilet-paper','toolbox','tools','tooth','torah','torii-gate','tractor','trademark','traffic-light','train','tram','transgender','transgender-alt','trash','trash-alt','trash-restore','trash-restore-alt','tree','trophy','truck','truck-loading','truck-monster','truck-moving','truck-pickup','tshirt','tty','tv','umbrella','umbrella-beach','underline','undo','undo-alt','universal-access','university','unlink','unlock','unlock-alt','upload','user','user-alt','user-alt-slash','user-astronaut','user-check','user-circle','user-clock','user-cog','user-edit','user-friends','user-graduate','user-injured','user-lock','user-md','user-minus','user-ninja','user-nurse','user-plus','user-secret','user-shield','user-slash','user-tag','user-tie','user-times','users','users-cog','utensil-spoon','utensils','vector-square','venus','venus-double','venus-mars','vial','vials','video','video-slash','vihara','volleyball-ball','volume-down','volume-mute','volume-off','volume-up','vote-yea','vr-cardboard','walking','wallet','warehouse','water','wave-square','weight','weight-hanging','wheelchair','wifi','wind','window-close','window-maximize','window-minimize','window-restore','wine-bottle','wine-glass','wine-glass-alt','won-sign','wrench','x-ray','yen-sign','yin-yang',  );
			$matches['fa5-far'] = array ('address-book','address-card','angry','arrow-alt-circle-down','arrow-alt-circle-left','arrow-alt-circle-right','arrow-alt-circle-up','bell','bell-slash','bookmark','building','calendar','calendar-alt','calendar-check','calendar-minus','calendar-plus','calendar-times','caret-square-down','caret-square-left','caret-square-right','caret-square-up','chart-bar','check-circle','check-square','circle','clipboard','clock','clone','closed-captioning','comment','comment-alt','comment-dots','comments','compass','copy','copyright','credit-card','dizzy','dot-circle','edit','envelope','envelope-open','eye','eye-slash','file','file-alt','file-archive','file-audio','file-code','file-excel','file-image','file-pdf','file-powerpoint','file-video','file-word','flag','flushed','folder','folder-open','font-awesome-logo-full','frown','frown-open','futbol','gem','grimace','grin','grin-alt','grin-beam','grin-beam-sweat','grin-hearts','grin-squint','grin-squint-tears','grin-stars','grin-tears','grin-tongue','grin-tongue-squint','grin-tongue-wink','grin-wink','hand-lizard','hand-paper','hand-peace','hand-point-down','hand-point-left','hand-point-right','hand-point-up','hand-pointer','hand-rock','hand-scissors','hand-spock','handshake','hdd','heart','hospital','hourglass','id-badge','id-card','image','images','keyboard','kiss','kiss-beam','kiss-wink-heart','laugh','laugh-beam','laugh-squint','laugh-wink','lemon','life-ring','lightbulb','list-alt','map','meh','meh-blank','meh-rolling-eyes','minus-square','money-bill-alt','moon','newspaper','object-group','object-ungroup','paper-plane','pause-circle','play-circle','plus-square','question-circle','registered','sad-cry','sad-tear','save','share-square','smile','smile-beam','smile-wink','snowflake','square','star','star-half','sticky-note','stop-circle','sun','surprise','thumbs-down','thumbs-up','times-circle','tired','trash-alt','user','user-circle','window-close','window-maximize','window-minimize','window-restore',  );
			$matches['fa5-shims'] = array ('glass'=>'fa fa-glass-martini','meetup'=>'fab fa-meetup','star-o'=>'far fa-star','remove'=>'fa fa-times','close'=>'fa fa-times','gear'=>'fa fa-cog','trash-o'=>'far fa-trash-alt','file-o'=>'far fa-file','clock-o'=>'far fa-clock','arrow-circle-o-down'=>'far fa-arrow-alt-circle-down','arrow-circle-o-up'=>'far fa-arrow-alt-circle-up','play-circle-o'=>'far fa-play-circle','repeat'=>'fa fa-redo','rotate-right'=>'fa fa-redo','refresh'=>'fa fa-sync','list-alt'=>'far fa-list-alt','dedent'=>'fa fa-outdent','video-camera'=>'fa fa-video','picture-o'=>'far fa-image','photo'=>'far fa-image','image'=>'far fa-image','pencil'=>'fa fa-pencil-alt','map-marker'=>'fa fa-map-marker-alt','pencil-square-o'=>'far fa-edit','share-square-o'=>'far fa-share-square','check-square-o'=>'far fa-check-square','arrows'=>'fa fa-arrows-alt','times-circle-o'=>'far fa-times-circle','check-circle-o'=>'far fa-check-circle','mail-forward'=>'fa fa-share','eye'=>'far fa-eye','eye-slash'=>'far fa-eye-slash','warning'=>'fa fa-exclamation-triangle','calendar'=>'fa fa-calendar-alt','arrows-v'=>'fa fa-arrows-alt-v','arrows-h'=>'fa fa-arrows-alt-h','bar-chart'=>'far fa-chart-bar','bar-chart-o'=>'far fa-chart-bar','twitter-square'=>'fab fa-twitter-square','facebook-square'=>'fab fa-facebook-square','gears'=>'fa fa-cogs','thumbs-o-up'=>'far fa-thumbs-up','thumbs-o-down'=>'far fa-thumbs-down','heart-o'=>'far fa-heart','sign-out'=>'fa fa-sign-out-alt','linkedin-square'=>'fab fa-linkedin','thumb-tack'=>'fa fa-thumbtack','external-link'=>'fa fa-external-link-alt','sign-in'=>'fa fa-sign-in-alt','github-square'=>'fab fa-github-square','lemon-o'=>'far fa-lemon','square-o'=>'far fa-square','bookmark-o'=>'far fa-bookmark','twitter'=>'fab fa-twitter','facebook'=>'fab fa-facebook-f','facebook-f'=>'fab fa-facebook-f','github'=>'fab fa-github','credit-card'=>'far fa-credit-card','feed'=>'fa fa-rss','hdd-o'=>'far fa-hdd','hand-o-right'=>'far fa-hand-point-right','hand-o-left'=>'far fa-hand-point-left','hand-o-up'=>'far fa-hand-point-up','hand-o-down'=>'far fa-hand-point-down','arrows-alt'=>'fa fa-expand-arrows-alt','group'=>'fa fa-users','chain'=>'fa fa-link','scissors'=>'fa fa-cut','files-o'=>'far fa-copy','floppy-o'=>'far fa-save','navicon'=>'fa fa-bars','reorder'=>'fa fa-bars','pinterest'=>'fab fa-pinterest','pinterest-square'=>'fab fa-pinterest-square','google-plus-square'=>'fab fa-google-plus-square','google-plus'=>'fab fa-google-plus-g','money'=>'far fa-money-bill-alt','unsorted'=>'fa fa-sort','sort-desc'=>'fa fa-sort-down','sort-asc'=>'fa fa-sort-up','linkedin'=>'fab fa-linkedin-in','rotate-left'=>'fa fa-undo','legal'=>'fa fa-gavel','tachometer'=>'fa fa-tachometer-alt','dashboard'=>'fa fa-tachometer-alt','comment-o'=>'far fa-comment','comments-o'=>'far fa-comments','flash'=>'fa fa-bolt','clipboard'=>'far fa-clipboard','paste'=>'far fa-clipboard','lightbulb-o'=>'far fa-lightbulb','exchange'=>'fa fa-exchange-alt','cloud-download'=>'fa fa-cloud-download-alt','cloud-upload'=>'fa fa-cloud-upload-alt','bell-o'=>'far fa-bell','cutlery'=>'fa fa-utensils','file-text-o'=>'far fa-file-alt','building-o'=>'far fa-building','hospital-o'=>'far fa-hospital','tablet'=>'fa fa-tablet-alt','mobile'=>'fa fa-mobile-alt','mobile-phone'=>'fa fa-mobile-alt','circle-o'=>'far fa-circle','mail-reply'=>'fa fa-reply','github-alt'=>'fab fa-github-alt','folder-o'=>'far fa-folder','folder-open-o'=>'far fa-folder-open','smile-o'=>'far fa-smile','frown-o'=>'far fa-frown','meh-o'=>'far fa-meh','keyboard-o'=>'far fa-keyboard','flag-o'=>'far fa-flag','mail-reply-all'=>'fa fa-reply-all','star-half-o'=>'far fa-star-half','star-half-empty'=>'far fa-star-half','star-half-full'=>'far fa-star-half','code-fork'=>'fa fa-code-branch','chain-broken'=>'fa fa-unlink','shield'=>'fa fa-shield-alt','calendar-o'=>'far fa-calendar','maxcdn'=>'fab fa-maxcdn','html5'=>'fab fa-html5','css3'=>'fab fa-css3','ticket'=>'fa fa-ticket-alt','minus-square-o'=>'far fa-minus-square','level-up'=>'fa fa-level-up-alt','level-down'=>'fa fa-level-down-alt','pencil-square'=>'fa fa-pen-square','external-link-square'=>'fa fa-external-link-square-alt','compass'=>'far fa-compass','caret-square-o-down'=>'far fa-caret-square-down','toggle-down'=>'far fa-caret-square-down','caret-square-o-up'=>'far fa-caret-square-up','toggle-up'=>'far fa-caret-square-up','caret-square-o-right'=>'far fa-caret-square-right','toggle-right'=>'far fa-caret-square-right','eur'=>'fa fa-euro-sign','euro'=>'fa fa-euro-sign','gbp'=>'fa fa-pound-sign','usd'=>'fa fa-dollar-sign','dollar'=>'fa fa-dollar-sign','inr'=>'fa fa-rupee-sign','rupee'=>'fa fa-rupee-sign','jpy'=>'fa fa-yen-sign','cny'=>'fa fa-yen-sign','rmb'=>'fa fa-yen-sign','yen'=>'fa fa-yen-sign','rub'=>'fa fa-ruble-sign','ruble'=>'fa fa-ruble-sign','rouble'=>'fa fa-ruble-sign','krw'=>'fa fa-won-sign','won'=>'fa fa-won-sign','btc'=>'fab fa-btc','bitcoin'=>'fab fa-btc','file-text'=>'fa fa-file-alt','sort-alpha-asc'=>'fa fa-sort-alpha-down','sort-alpha-desc'=>'fa fa-sort-alpha-up','sort-amount-asc'=>'fa fa-sort-amount-down','sort-amount-desc'=>'fa fa-sort-amount-up','sort-numeric-asc'=>'fa fa-sort-numeric-down','sort-numeric-desc'=>'fa fa-sort-numeric-up','youtube-square'=>'fab fa-youtube-square','youtube'=>'fab fa-youtube','xing'=>'fab fa-xing','xing-square'=>'fab fa-xing-square','youtube-play'=>'fab fa-youtube','dropbox'=>'fab fa-dropbox','stack-overflow'=>'fab fa-stack-overflow','instagram'=>'fab fa-instagram','flickr'=>'fab fa-flickr','adn'=>'fab fa-adn','bitbucket'=>'fab fa-bitbucket','bitbucket-square'=>'fab fa-bitbucket','tumblr'=>'fab fa-tumblr','tumblr-square'=>'fab fa-tumblr-square','long-arrow-down'=>'fa fa-long-arrow-alt-down','long-arrow-up'=>'fa fa-long-arrow-alt-up','long-arrow-left'=>'fa fa-long-arrow-alt-left','long-arrow-right'=>'fa fa-long-arrow-alt-right','apple'=>'fab fa-apple','windows'=>'fab fa-windows','android'=>'fab fa-android','linux'=>'fab fa-linux','dribbble'=>'fab fa-dribbble','skype'=>'fab fa-skype','foursquare'=>'fab fa-foursquare','trello'=>'fab fa-trello','gratipay'=>'fab fa-gratipay','gittip'=>'fab fa-gratipay','sun-o'=>'far fa-sun','moon-o'=>'far fa-moon','vk'=>'fab fa-vk','weibo'=>'fab fa-weibo','renren'=>'fab fa-renren','pagelines'=>'fab fa-pagelines','stack-exchange'=>'fab fa-stack-exchange','arrow-circle-o-right'=>'far fa-arrow-alt-circle-right','arrow-circle-o-left'=>'far fa-arrow-alt-circle-left','caret-square-o-left'=>'far fa-caret-square-left','toggle-left'=>'far fa-caret-square-left','dot-circle-o'=>'far fa-dot-circle','vimeo-square'=>'fab fa-vimeo-square','try'=>'fa fa-lira-sign','turkish-lira'=>'fa fa-lira-sign','plus-square-o'=>'far fa-plus-square','slack'=>'fab fa-slack','wordpress'=>'fab fa-wordpress','openid'=>'fab fa-openid','institution'=>'fa fa-university','bank'=>'fa fa-university','mortar-board'=>'fa fa-graduation-cap','yahoo'=>'fab fa-yahoo','google'=>'fab fa-google','reddit'=>'fab fa-reddit','reddit-square'=>'fab fa-reddit-square','stumbleupon-circle'=>'fab fa-stumbleupon-circle','stumbleupon'=>'fab fa-stumbleupon','delicious'=>'fab fa-delicious','digg'=>'fab fa-digg','pied-piper-pp'=>'fab fa-pied-piper-pp','pied-piper-alt'=>'fab fa-pied-piper-alt','drupal'=>'fab fa-drupal','joomla'=>'fab fa-joomla','spoon'=>'fa fa-utensil-spoon','behance'=>'fab fa-behance','behance-square'=>'fab fa-behance-square','steam'=>'fab fa-steam','steam-square'=>'fab fa-steam-square','automobile'=>'fa fa-car','cab'=>'fa fa-taxi','envelope-o'=>'far fa-envelope','deviantart'=>'fab fa-deviantart','soundcloud'=>'fab fa-soundcloud','file-pdf-o'=>'far fa-file-pdf','file-word-o'=>'far fa-file-word','file-excel-o'=>'far fa-file-excel','file-powerpoint-o'=>'far fa-file-powerpoint','file-image-o'=>'far fa-file-image','file-photo-o'=>'far fa-file-image','file-picture-o'=>'far fa-file-image','file-archive-o'=>'far fa-file-archive','file-zip-o'=>'far fa-file-archive','file-audio-o'=>'far fa-file-audio','file-sound-o'=>'far fa-file-audio','file-video-o'=>'far fa-file-video','file-movie-o'=>'far fa-file-video','file-code-o'=>'far fa-file-code','vine'=>'fab fa-vine','codepen'=>'fab fa-codepen','jsfiddle'=>'fab fa-jsfiddle','life-ring'=>'far fa-life-ring','life-bouy'=>'far fa-life-ring','life-buoy'=>'far fa-life-ring','life-saver'=>'far fa-life-ring','support'=>'far fa-life-ring','circle-o-notch'=>'fa fa-circle-notch','rebel'=>'fab fa-rebel','ra'=>'fab fa-rebel','resistance'=>'fab fa-rebel','empire'=>'fab fa-empire','ge'=>'fab fa-empire','git-square'=>'fab fa-git-square','git'=>'fab fa-git','hacker-news'=>'fab fa-hacker-news','y-combinator-square'=>'fab fa-hacker-news','yc-square'=>'fab fa-hacker-news','tencent-weibo'=>'fab fa-tencent-weibo','qq'=>'fab fa-qq','weixin'=>'fab fa-weixin','wechat'=>'fab fa-weixin','send'=>'fa fa-paper-plane','paper-plane-o'=>'far fa-paper-plane','send-o'=>'far fa-paper-plane','circle-thin'=>'far fa-circle','header'=>'fa fa-heading','sliders'=>'fa fa-sliders-h','futbol-o'=>'far fa-futbol','soccer-ball-o'=>'far fa-futbol','slideshare'=>'fab fa-slideshare','twitch'=>'fab fa-twitch','yelp'=>'fab fa-yelp','newspaper-o'=>'far fa-newspaper','paypal'=>'fab fa-paypal','google-wallet'=>'fab fa-google-wallet','cc-visa'=>'fab fa-cc-visa','cc-mastercard'=>'fab fa-cc-mastercard','cc-discover'=>'fab fa-cc-discover','cc-amex'=>'fab fa-cc-amex','cc-paypal'=>'fab fa-cc-paypal','cc-stripe'=>'fab fa-cc-stripe','bell-slash-o'=>'far fa-bell-slash','trash'=>'fa fa-trash-alt','copyright'=>'far fa-copyright','eyedropper'=>'fa fa-eye-dropper','area-chart'=>'fa fa-chart-area','pie-chart'=>'fa fa-chart-pie','line-chart'=>'fa fa-chart-line','lastfm'=>'fab fa-lastfm','lastfm-square'=>'fab fa-lastfm-square','ioxhost'=>'fab fa-ioxhost','angellist'=>'fab fa-angellist','cc'=>'far fa-closed-captioning','ils'=>'fa fa-shekel-sign','shekel'=>'fa fa-shekel-sign','sheqel'=>'fa fa-shekel-sign','meanpath'=>'fab fa-font-awesome','buysellads'=>'fab fa-buysellads','connectdevelop'=>'fab fa-connectdevelop','dashcube'=>'fab fa-dashcube','forumbee'=>'fab fa-forumbee','leanpub'=>'fab fa-leanpub','sellsy'=>'fab fa-sellsy','shirtsinbulk'=>'fab fa-shirtsinbulk','simplybuilt'=>'fab fa-simplybuilt','skyatlas'=>'fab fa-skyatlas','diamond'=>'far fa-gem','intersex'=>'fa fa-transgender','facebook-official'=>'fab fa-facebook','pinterest-p'=>'fab fa-pinterest-p','whatsapp'=>'fab fa-whatsapp','hotel'=>'fa fa-bed','viacoin'=>'fab fa-viacoin','medium'=>'fab fa-medium','y-combinator'=>'fab fa-y-combinator','yc'=>'fab fa-y-combinator','optin-monster'=>'fab fa-optin-monster','opencart'=>'fab fa-opencart','expeditedssl'=>'fab fa-expeditedssl','battery-4'=>'fa fa-battery-full','battery'=>'fa fa-battery-full','battery-3'=>'fa fa-battery-three-quarters','battery-2'=>'fa fa-battery-half','battery-1'=>'fa fa-battery-quarter','battery-0'=>'fa fa-battery-empty','object-group'=>'far fa-object-group','object-ungroup'=>'far fa-object-ungroup','sticky-note-o'=>'far fa-sticky-note','cc-jcb'=>'fab fa-cc-jcb','cc-diners-club'=>'fab fa-cc-diners-club','clone'=>'far fa-clone','hourglass-o'=>'far fa-hourglass','hourglass-1'=>'fa fa-hourglass-start','hourglass-2'=>'fa fa-hourglass-half','hourglass-3'=>'fa fa-hourglass-end','hand-rock-o'=>'far fa-hand-rock','hand-grab-o'=>'far fa-hand-rock','hand-paper-o'=>'far fa-hand-paper','hand-stop-o'=>'far fa-hand-paper','hand-scissors-o'=>'far fa-hand-scissors','hand-lizard-o'=>'far fa-hand-lizard','hand-spock-o'=>'far fa-hand-spock','hand-pointer-o'=>'far fa-hand-pointer','hand-peace-o'=>'far fa-hand-peace','registered'=>'far fa-registered','creative-commons'=>'fab fa-creative-commons','gg'=>'fab fa-gg','gg-circle'=>'fab fa-gg-circle','tripadvisor'=>'fab fa-tripadvisor','odnoklassniki'=>'fab fa-odnoklassniki','odnoklassniki-square'=>'fab fa-odnoklassniki-square','get-pocket'=>'fab fa-get-pocket','wikipedia-w'=>'fab fa-wikipedia-w','safari'=>'fab fa-safari','chrome'=>'fab fa-chrome','firefox'=>'fab fa-firefox','opera'=>'fab fa-opera','internet-explorer'=>'fab fa-internet-explorer','television'=>'fa fa-tv','contao'=>'fab fa-contao','500px'=>'fab fa-500px','amazon'=>'fab fa-amazon','calendar-plus-o'=>'far fa-calendar-plus','calendar-minus-o'=>'far fa-calendar-minus','calendar-times-o'=>'far fa-calendar-times','calendar-check-o'=>'far fa-calendar-check','map-o'=>'far fa-map','commenting'=>'fa fa-comment-dots','commenting-o'=>'far fa-comment-dots','houzz'=>'fab fa-houzz','vimeo'=>'fab fa-vimeo-v','black-tie'=>'fab fa-black-tie','fonticons'=>'fab fa-fonticons','reddit-alien'=>'fab fa-reddit-alien','edge'=>'fab fa-edge','credit-card-alt'=>'fa fa-credit-card','codiepie'=>'fab fa-codiepie','modx'=>'fab fa-modx','fort-awesome'=>'fab fa-fort-awesome','usb'=>'fab fa-usb','product-hunt'=>'fab fa-product-hunt','mixcloud'=>'fab fa-mixcloud','scribd'=>'fab fa-scribd','pause-circle-o'=>'far fa-pause-circle','stop-circle-o'=>'far fa-stop-circle','bluetooth'=>'fab fa-bluetooth','bluetooth-b'=>'fab fa-bluetooth-b','gitlab'=>'fab fa-gitlab','wpbeginner'=>'fab fa-wpbeginner','wpforms'=>'fab fa-wpforms','envira'=>'fab fa-envira','wheelchair-alt'=>'fab fa-accessible-icon','question-circle-o'=>'far fa-question-circle','volume-control-phone'=>'fa fa-phone-volume','asl-interpreting'=>'fa fa-american-sign-language-interpreting','deafness'=>'fa fa-deaf','hard-of-hearing'=>'fa fa-deaf','glide'=>'fab fa-glide','glide-g'=>'fab fa-glide-g','signing'=>'fa fa-sign-language','viadeo'=>'fab fa-viadeo','viadeo-square'=>'fab fa-viadeo-square','snapchat'=>'fab fa-snapchat','snapchat-ghost'=>'fab fa-snapchat-ghost','snapchat-square'=>'fab fa-snapchat-square','pied-piper'=>'fab fa-pied-piper','first-order'=>'fab fa-first-order','yoast'=>'fab fa-yoast','themeisle'=>'fab fa-themeisle','google-plus-official'=>'fab fa-google-plus','google-plus-circle'=>'fab fa-google-plus','font-awesome'=>'fab fa-font-awesome','fa'=>'fab fa-font-awesome','handshake-o'=>'far fa-handshake','envelope-open-o'=>'far fa-envelope-open','linode'=>'fab fa-linode','address-book-o'=>'far fa-address-book','vcard'=>'fa fa-address-card','address-card-o'=>'far fa-address-card','vcard-o'=>'far fa-address-card','user-circle-o'=>'far fa-user-circle','user-o'=>'far fa-user','id-badge'=>'far fa-id-badge','drivers-license'=>'fa fa-id-card','id-card-o'=>'far fa-id-card','drivers-license-o'=>'far fa-id-card','quora'=>'fab fa-quora','free-code-camp'=>'fab fa-free-code-camp','telegram'=>'fab fa-telegram','thermometer-4'=>'fa fa-thermometer-full','thermometer'=>'fa fa-thermometer-full','thermometer-3'=>'fa fa-thermometer-three-quarters','thermometer-2'=>'fa fa-thermometer-half','thermometer-1'=>'fa fa-thermometer-quarter','thermometer-0'=>'fa fa-thermometer-empty','bathtub'=>'fa fa-bath','s15'=>'fa fa-bath','window-maximize'=>'far fa-window-maximize','window-restore'=>'far fa-window-restore','times-rectangle'=>'fa fa-window-close','window-close-o'=>'far fa-window-close','times-rectangle-o'=>'far fa-window-close','bandcamp'=>'fab fa-bandcamp','grav'=>'fab fa-grav','etsy'=>'fab fa-etsy','imdb'=>'fab fa-imdb','ravelry'=>'fab fa-ravelry','eercast'=>'fab fa-sellcast','snowflake-o'=>'far fa-snowflake','superpowers'=>'fab fa-superpowers','wpexplorer'=>'fab fa-wpexplorer','spotify'=>'fab fa-spotify',);


			$matches['bs2'] = array(
				'glass','music','search','envelope','heart','star','star-empty','user','film','th-large','th','th-list','ok',
				'remove','zoom-in','zoom-out','off','signal','cog','trash','home','file','time','road','download-alt','download',
				'upload','inbox','play-circle','repeat','refresh','list-alt','lock','flag','headphones','volume-off','volume-down',
				'volume-up','qrcode','barcode','tag','tags','book','bookmark','print','camera','font','bold','italic','text-height',
				'text-width','align-left','align-center','align-right','align-justify','list','indent-left','indent-right',
				'facetime-video','picture','pencil','map-marker','adjust','tint','edit','share','check','move','step-backward',
				'fast-backward','backward','play','pause','stop','forward','fast-forward','step-forward','eject','chevron-left',
				'chevron-right','plus-sign','minus-sign','remove-sign','ok-sign','question-sign','info-sign','screenshot',
				'remove-circle','ok-circle','ban-circle','arrow-left','arrow-right','arrow-up','arrow-down','share-alt',
				'resize-full','resize-small','plus','minus','asterisk','exclamation-sign','gift','leaf','fire','eye-open',
				'eye-close','warning-sign','plane','calendar','random','comment','magnet','chevron-up','chevron-down',
				'retweet','shopping-cart','folder-close','folder-open','resize-vertical','resize-horizontal','hdd',
				'bullhorn','bell','certificate','thumbs-up','thumbs-down','hand-right','hand-left','hand-up','hand-down',
				'circle-arrow-right','circle-arrow-left','circle-arrow-up','circle-arrow-down','globe','wrench','tasks',
				'filter','briefcase','fullscreen'
			);


			$matches['bs3'] = array(
			'adjust','align-center','align-justify','align-left','align-right','arrow-down','arrow-left','arrow-right','arrow-up','asterisk','backward','ban-circle','barcode','bell','bold','book
			','bookmark','briefcase','bullhorn','calendar','camera','certificate','check','chevron-down','chevron-left','chevron-right','chevron-up','circle-arrow-down','circle-arrow-left','circle-arrow-right
			','circle-arrow-up','cloud','cloud-download','cloud-upload','cog','collapse-down','collapse-up','comment','compressed','copyright-mark','credit-card','cutlery','dashboard','download','download-alt
			','earphone','edit','eject','envelope','euro','exclamation-sign','expand','export','eye-close','eye-open','facetime-video','fast-backward','fast-forward','file','film','filter','fire','flag
			','flash','floppy-disk','floppy-open','floppy-remove','floppy-save','floppy-saved','folder-close','folder-open','font','forward','fullscreen','gbp','gift
			','glass','globe','hand-down','hand-left','hand-right','hand-up','hd-video','hdd','header','headphones','heart','heart-empty','home','import','inbox','indent-left','indent-right','info-sign','italic','leaf','link','list
			','list-alt','lock','log-in','log-out','magnet','map-marker','minus','minus-sign','move','music','new-window','off','ok','ok-circle','ok-sign','open','paperclip','pause','pencil','phone','phone-alt','picture
			','plane','play','play-circle','plus','plus-sign','print','pushpin','qrcode','question-sign','random','record','refresh','registration-mark','remove','remove-circle','remove-sign','repeat','resize-full','resize-horizontal
			','resize-small','resize-vertical','retweet','road','save','saved','screenshot','sd-video','search','send','share','share-alt','shopping-cart','signal','sort','sort-by-alphabet','sort-by-alphabet-alt
			','sort-by-attributes','sort-by-attributes-alt','sort-by-order','sort-by-order-alt','sound-5-1','sound-6-1','sound-7-1','sound-dolby','sound-stereo','star','stats','step-backward','step-forward','stop
			','subtitles','tag','tags','tasks','text-height','text-width','th','th-large','th-list','thumbs-down','thumbs-up','time','tint','tower','transfer','trash','tree-conifer','tree-deciduous','unchecked','upload
			','usd','user','volume-down','volume-off','volume-up','warning-sign','wrench','zoom-in','zoom-out'
			);


			$matches['fa4'] = array(
				"glass","music","search","envelope-o","heart","star","star-o","user","film","th-large","th","th-list","check","remove","close","times","search-plus","search-minus","power-off","signal","gear","cog","trash-o","home",
				"file-o","clock-o","road","download","arrow-circle-o-down","arrow-circle-o-up","inbox","play-circle-o","rotate-right","repeat","refresh","list-alt","lock","flag","headphones","volume-off","volume-down","volume-up","qrcode",
				"barcode","tag","tags","book","bookmark","print","camera","font","bold","italic","text-height","text-width","align-left","align-center","align-right","align-justify","list","dedent","outdent","indent","video-camera","photo",
				"image","picture-o","pencil","map-marker","adjust","tint","edit","pencil-square-o","share-square-o","check-square-o","arrows","step-backward","fast-backward","backward","play","pause","stop","forward","fast-forward","step-forward",
				"eject","chevron-left","chevron-right","plus-circle","minus-circle","times-circle","check-circle","question-circle","info-circle","crosshairs","times-circle-o","check-circle-o","ban","arrow-left","arrow-right","arrow-up","arrow-down",
				"mail-forward","share","expand","compress","plus","minus","asterisk","exclamation-circle","gift","leaf","fire","eye","eye-slash","warning","exclamation-triangle","plane","calendar","random","comment","magnet","chevron-up","chevron-down",
				"retweet","shopping-cart","folder","folder-open","arrows-v","arrows-h","bar-chart-o","bar-chart","twitter-square","facebook-square","camera-retro","key","gears","cogs","comments","thumbs-o-up","thumbs-o-down","star-half","heart-o",
				"sign-out","linkedin-square","thumb-tack","external-link","sign-in","trophy","github-square","upload","lemon-o","phone","square-o","bookmark-o","phone-square","twitter","facebook-f","facebook","github","unlock","credit-card","feed","rss",
				"hdd-o","bullhorn","bell","certificate","hand-o-right","hand-o-left","hand-o-up","hand-o-down","arrow-circle-left","arrow-circle-right","arrow-circle-up","arrow-circle-down","globe","wrench","tasks","filter","briefcase","arrows-alt","group",
				"users","chain","link","cloud","flask","cut","scissors","copy","files-o","paperclip","save","floppy-o","square","navicon","reorder","bars","list-ul","list-ol","strikethrough","underline","table","magic","truck","pinterest","pinterest-square",
				"google-plus-square","google-plus","money","caret-down","caret-up","caret-left","caret-right","columns","unsorted","sort","sort-down","sort-desc","sort-up","sort-asc","envelope","linkedin","rotate-left","undo","legal","gavel","dashboard",
				"tachometer","comment-o","comments-o","flash","bolt","sitemap","umbrella","paste","clipboard","lightbulb-o","exchange","cloud-download","cloud-upload","user-md","stethoscope","suitcase","bell-o","coffee","cutlery","file-text-o","building-o",
				"hospital-o","ambulance","medkit","fighter-jet","beer","h-square","plus-square","angle-double-left","angle-double-right","angle-double-up","angle-double-down","angle-left","angle-right","angle-up","angle-down","desktop","laptop","tablet",
				"mobile-phone","mobile","circle-o","quote-left","quote-right","spinner","circle","mail-reply","reply","github-alt","folder-o","folder-open-o","smile-o","frown-o","meh-o","gamepad","keyboard-o","flag-o","flag-checkered","terminal","code",
				"mail-reply-all","reply-all","star-half-empty","star-half-full","star-half-o","location-arrow","crop","code-fork","unlink","chain-broken","question","info","exclamation","superscript","subscript","eraser","puzzle-piece","microphone",
				"microphone-slash","shield","calendar-o","fire-extinguisher","rocket","maxcdn","chevron-circle-left","chevron-circle-right","chevron-circle-up","chevron-circle-down","html5","css3","anchor","unlock-alt","bullseye","ellipsis-h","ellipsis-v",
				"rss-square","play-circle","ticket","minus-square","minus-square-o","level-up","level-down","check-square","pencil-square","external-link-square","share-square","compass","toggle-down","caret-square-o-down","toggle-up","caret-square-o-up",
				"toggle-right","caret-square-o-right","euro","eur","gbp","dollar","usd","rupee","inr","cny","rmb","yen","jpy","ruble","rouble","rub","won","krw","bitcoin","btc","file","file-text","sort-alpha-asc","sort-alpha-desc","sort-amount-asc",
				"sort-amount-desc","sort-numeric-asc","sort-numeric-desc","thumbs-up","thumbs-down","youtube-square","youtube","xing","xing-square","youtube-play","dropbox","stack-overflow","instagram","flickr","adn","bitbucket","bitbucket-square","tumblr",
				"tumblr-square","long-arrow-down","long-arrow-up","long-arrow-left","long-arrow-right","apple","windows","android","linux","dribbble","skype","foursquare","trello","female","male","gittip","gratipay","sun-o","moon-o","archive","bug","vk",
				"weibo","renren","pagelines","stack-exchange","arrow-circle-o-right","arrow-circle-o-left","toggle-left","caret-square-o-left","dot-circle-o","wheelchair","vimeo-square","turkish-lira","try","plus-square-o","space-shuttle","slack",
				"envelope-square","wordpress","openid","institution","bank","university","mortar-board","graduation-cap","yahoo","google","reddit","reddit-square","stumbleupon-circle","stumbleupon","delicious","digg","pied-piper-pp","pied-piper-alt","drupal",
				"joomla","language","fax","building","child","paw","spoon","cube","cubes","behance","behance-square","steam","steam-square","recycle","automobile","car","cab","taxi","tree","spotify","deviantart","soundcloud","database","file-pdf-o","file-word-o",
				"file-excel-o","file-powerpoint-o","file-photo-o","file-picture-o","file-image-o","file-zip-o","file-archive-o","file-sound-o","file-audio-o","file-movie-o","file-video-o","file-code-o","vine","codepen","jsfiddle","life-bouy","life-buoy",
				"life-saver","support","life-ring","circle-o-notch","ra","resistance","rebel","ge","empire","git-square","git","y-combinator-square","yc-square","hacker-news","tencent-weibo","qq","wechat","weixin","send","paper-plane","send-o","paper-plane-o",
				"history","circle-thin","header","paragraph","sliders","share-alt","share-alt-square","bomb","soccer-ball-o","futbol-o","tty","binoculars","plug","slideshare","twitch","yelp","newspaper-o","wifi","calculator","paypal","google-wallet","cc-visa",
				"cc-mastercard","cc-discover","cc-amex","cc-paypal","cc-stripe","bell-slash","bell-slash-o","trash","copyright","at","eyedropper","paint-brush","birthday-cake","area-chart","pie-chart","line-chart","lastfm","lastfm-square","toggle-off","toggle-on",
				"bicycle","bus","ioxhost","angellist","cc","shekel","sheqel","ils","meanpath","buysellads","connectdevelop","dashcube","forumbee","leanpub","sellsy","shirtsinbulk","simplybuilt","skyatlas","cart-plus","cart-arrow-down","diamond","ship","user-secret",
				"motorcycle","street-view","heartbeat","venus","mars","mercury","intersex","transgender","transgender-alt","venus-double","mars-double","venus-mars","mars-stroke","mars-stroke-v","mars-stroke-h","neuter","genderless","facebook-official","pinterest-p",
				"whatsapp","server","user-plus","user-times","hotel","bed","viacoin","train","subway","medium","yc","y-combinator","optin-monster","opencart","expeditedssl","battery-4","battery","battery-full","battery-3","battery-three-quarters","battery-2",
				"battery-half","battery-1","battery-quarter","battery-0","battery-empty","mouse-pointer","i-cursor","object-group","object-ungroup","sticky-note","sticky-note-o","cc-jcb","cc-diners-club","clone","balance-scale","hourglass-o","hourglass-1",
				"hourglass-start","hourglass-2","hourglass-half","hourglass-3","hourglass-end","hourglass","hand-grab-o","hand-rock-o","hand-stop-o","hand-paper-o","hand-scissors-o","hand-lizard-o","hand-spock-o","hand-pointer-o","hand-peace-o","trademark",
				"registered","creative-commons","gg","gg-circle","tripadvisor","odnoklassniki","odnoklassniki-square","get-pocket","wikipedia-w","safari","chrome","firefox","opera","internet-explorer","tv","television","contao","500px","amazon","calendar-plus-o",
				"calendar-minus-o","calendar-times-o","calendar-check-o","industry","map-pin","map-signs","map-o","map","commenting","commenting-o","houzz","vimeo","black-tie","fonticons","reddit-alien","edge","credit-card-alt","codiepie","modx","fort-awesome",
				"usb","product-hunt","mixcloud","scribd","pause-circle","pause-circle-o","stop-circle","stop-circle-o","shopping-bag","shopping-basket","hashtag","bluetooth","bluetooth-b","percent","gitlab","wpbeginner","wpforms","envira","universal-access",
				"wheelchair-alt","question-circle-o","blind","audio-description","volume-control-phone","braille","assistive-listening-systems","asl-interpreting","american-sign-language-interpreting","deafness","hard-of-hearing","deaf","glide","glide-g","signing",
				"sign-language","low-vision","viadeo","viadeo-square","snapchat","snapchat-ghost","snapchat-square","pied-piper","first-order","yoast","themeisle","google-plus-circle","google-plus-official","fa","font-awesome","handshake-o","envelope-open",
				"envelope-open-o","linode","address-book","address-book-o","vcard","address-card","vcard-o","address-card-o","user-circle","user-circle-o","user-o","id-badge","drivers-license","id-card","drivers-license-o","id-card-o","quora","free-code-camp",
				"telegram","thermometer-4","thermometer","thermometer-full","thermometer-3","thermometer-three-quarters","thermometer-2","thermometer-half","thermometer-1","thermometer-quarter","thermometer-0","thermometer-empty","shower","bathtub","s15","bath",
				"podcast","window-maximize","window-minimize","window-restore","times-rectangle","window-close","times-rectangle-o","window-close-o","bandcamp","grav","etsy","imdb","ravelry","eercast","microchip","snowflake-o","superpowers","wpexplorer","meetup"
			);

			if(strpos($type,'-shims') !==false)
			{
				return $matches[$type];
			}

			foreach($matches[$type] as $ic)
			{
			    $icons[$ic] = $addPrefix.$ic;
			}

			return $icons;
		}
					


		if(is_array($type))
		{
			$prefix     = $type['prefix'];
			$pattern    = $type['pattern'];
			$path       = $type['path'];
			$type       = $type['name'];

		}



		$cache = e107::getCache();

		$cachTag = !empty($addPrefix) ? "Glyphs_".$addPrefix."_".$type : "Glyphs_".$type;


		if($data = $cache->retrieve($cachTag ,360,true,true))
		{
			return e107::unserialize($data);
		}
		
		
		if($type === 'fa4')
		{
			$pattern = '/\.(fa-(?:\w+(?:-)?)+):before/';
			$path = e107::getLibrary()->getPath('fontawesome');
			$subject = file_get_contents($path.'css/font-awesome.css');
			$prefix = 'fa-';
		}
		elseif($type === 'fa3')
		{
			$pattern = '/\.(icon-(?:\w+(?:-)?)+):before/';
			$subject = file_get_contents(e_WEB_JS.'font-awesome/css/font-awesome.css');
			$prefix = 'fa-';
		}
		elseif(!empty($pattern) && !empty($path))
		{
			$pattern = '/'.$pattern.'/';
			if(strpos($path, 'http') === 0)
			{
				$subject = e107::getFile()->getRemoteContent($path);
			}
			else
			{
				$path = e107::getParser()->replaceConstants($path);
				$subject = file_get_contents($path);
			}



		}


		$prefixLength = !empty($prefix) ? strlen($prefix) : 3;

		if(!empty($pattern) && !empty($subject))
		{
			preg_match_all($pattern, $subject, $matches, PREG_SET_ORDER);

			foreach($matches as $match)
			{
			    $icons[] = $addPrefix.substr($match[1],$prefixLength);
			}
		}

		if(empty($icons)) // failed to produce a result so don't cache it. .
		{
			return array();
		}

		$data = e107::serialize($icons,'json');

		$cache->set_sys($cachTag ,$data,true,true);

		return $icons; 
	
	}


	/**
	 * @param (string) $mime
	 * @param (string) $path (optional)
	 * @return false|string
	 */
	public function getPath($mime, $path=null)
	{
		$mes = e107::getMessage();

		list($pmime,$tmp) = explode('/',$mime);
		unset($tmp);

		if(empty($this->mimePaths[$pmime]))
		{
			$this->log("Couldn't detect mime-type ($mime).");
			$text = $text = str_replace('[x]',$mime,IMALAN_111); //FIXME LAN IMALAN_112 is not generic. This method can be called from anywhere, not only e107_admin/image.php.
			$mes->add($text, E_MESSAGE_ERROR);
			return FALSE;
		}

		if(!empty($path))
		{
			$dir = e_MEDIA."plugins/".e107::getParser()->filter($path,'w');
		}
		else
		{
			$dir = $this->mimePaths[$pmime].date("Y-m");
		}


		if(!is_dir($dir))
		{
			if(!mkdir($dir, 0755,true))
			{

				$this->log("Couldn't create folder ($dir).");
				$text = str_replace('[x]',$dir,IMALAN_112);
				$mes->add($text, E_MESSAGE_ERROR);
				return FALSE;
			}
		}
		return $dir;
	}

	/**
	 * detected Media Type from Media URL
	 * @param string $mediaURL
	 * @return string|null
	 */
	public function detectType($mediaURL)
	{
		$mediaURL = (string) $mediaURL;
		$type = pathinfo($mediaURL,PATHINFO_EXTENSION);

		if($type == 'glyph')
		{
			return 'glyph';
		}

		foreach($this->mimeExtensions as $key=>$exts)
		{
			if(!in_array($type, $exts))
			{
				continue;
			}

			return $key;
		}

		if(strpos($mediaURL, 'via.placeholder') !== false)
		{
			return 'image';
		}

		return null;
	}


	/**
	 * @param string $default eg. {e_MEDIA_VIDEO}2018-10/myvideo.mp4
	 * @param array $options
	 * @return bool|string
	 */
	public function previewTag($default, $options=array())
	{

		$tp = e107::getParser();

		$type       = !empty($options['type']) ? $options['type'] : $this->detectType($default);

		$width      = vartrue($options['w'], 220);
		$height     = vartrue($options['h'], 190);
		$crop       = vartrue($options['crop'], 0);
		$preview    = '';

		switch($type)
		{

			case "video":
				$preview = $tp->toVideo($default, array('w'=>$width, 'h'=> ($height - 50)));
			break;

			case "audio":
				$preview = $tp->toAudio($default);
			break;

			case "image":
				$preview = $tp->toImage($default, array('w'=>$width, 'h'=>$height, 'crop'=>$crop, 'class'=> varset($options['class'],'image-selector img-responsive img-fluid'), 'legacy'=>varset($options['legacyPath'])));
			break;

			case "application": // file.
				$preview = "<div class='text-center' style='padding-top:40px; padding-bottom:40px'>".defset('LAN_FILE', 'File')."</div>";
			break;

			case "glyph":
				$preview = $tp->toGlyph($default);
			break;

			case "icon":
				$preview = $tp->toIcon($default);
			break;

			default: // blank
				$preview = null;

		}

		return $preview;
	}


	/**
	 * @param (string) $sc_path
	 * @return array|false
	 */
	public function mediaData($sc_path)
	{
		if(!$sc_path) return array();
		
		$mes = e107::getMessage();
		$path = e107::getParser()->replaceConstants($sc_path);
		
		if(!is_readable($path))
		{
			$mes->addError("Couldn't read file: {$path}");	
			$this->log("Couldn't read file: {$path}");
			return FALSE;
		}
		
		$info = e107::getFile()->getFileInfo($path);

		$this->log("File info for $path : ".print_r($info,true));
		
		return array(
			'media_type'		=> vartrue($info['mime']),
			'media_datestamp'	=> time(),
			'media_url'			=> e107::getParser()->createConstants($info['fullpath'], 'rel'),
			'media_size'		=> filesize($info['fullpath']),
			'media_author'		=> USERID,
			'media_usedby'		=> '',
			'media_tags'		=> '',
			'media_dimensions'	=> (isset($info['img-width']) && isset($info['img-height'])) ? $info['img-width']." x ".$info['img-height'] : ''
		);
	}


	/**
	 * @param $message
	 * @return void
	 */
	public function log($message)
	{
		if($this->logging == false) return; 
		$insert = "\n\n".date('r')."\n".$message;
		file_put_contents(e_LOG."mediaUpload.log",$insert,FILE_APPEND | LOCK_EX);	
	}


	/**
	 * Import a file into the Media Manager
	 * @param string $file Path to file
	 * @param string $category media-category to import into
	 * @param null|array $opts('path'=> Custom Folder (optional))
	 * @param array $new_data - Additional media info to save.
	 * @return bool|string
	 */
	public function importFile($file='', $category='_common_image', $opts = null, $new_data = array())
	{
		$mes = e107::getMessage();
		$tp = e107::getParser();
		$sql = e107::getDb();

		if(is_array($opts))
        {
            $uploadPath = varset($opts['path']);
            $oldpath = null;
        }
        else
        {
	        $uploadPath = null;
            $oldpath = $opts;
        }

        if(empty($oldpath)) $oldpath = e_IMPORT.$file;

		if(!file_exists($oldpath))
		{
			// Check it hasn't been imported already. 	
			if($newpath = $this->checkDupe($oldpath, $file))
			{
				$this->log("Line: ".__LINE__." Couldn't find the file: ".$oldpath);
				return $newpath; 
			}
			$this->log("Line: ".__LINE__." Couldn't find the file: ".$oldpath);
			$mes->addError("Couldn't find the file: ".$oldpath);
			return false;
		}	
			
		$img_data = $this->mediaData($oldpath); // Basic File Info only


		if($category === '_icon')
		{
			$typePath = rtrim(e_MEDIA_ICON,'/');
		}
		elseif(!$typePath = $this->getPath($img_data['media_type'], $uploadPath))
		{		
				$this->log("Line: ".__LINE__." Couldn't generate path from file info:".$oldpath);
				$mes->addError("Couldn't generate path from file info:".$oldpath);
				return false;
		}


		if(!$newpath = $this->checkDupe($oldpath,$typePath.'/'. $tp->filter($file,'file')))
		{
			return $tp->createConstants($typePath.'/'.$file,'rel');
		}

		$newpath = $this->checkFileExtension($newpath, $img_data['media_type']);

		if(!rename($oldpath, $newpath)) // e_MEDIA.$newpath was working before. 
		{
			$this->log("Couldn't move file from ".realpath($oldpath)." to ".e_MEDIA.$newpath);
			$mes->add("Couldn't move file from ".$oldpath." to ".$newpath, E_MESSAGE_ERROR);
			return false;
		}

		if($category === '_icon') // convert to _icon_16, _icon_32 etc.
		{
			$category = $this->getIconCategory($img_data);
		}
		
		$img_data['media_url']			= $tp->createConstants($newpath,'rel');
		$img_data['media_name'] 		= $tp->toDB(basename($newpath));
		$img_data['media_caption'] 		= vartrue($new_data['media_caption']);
		$img_data['media_category'] 	= vartrue($category,'_common_image');
		$img_data['media_description'] 	= vartrue($new_data['media_description']);
		$img_data['media_userclass'] 	= '0';

		if($sql->insert("core_media",$img_data))
		{		
			$mes->add("Importing Media: ".$file, E_MESSAGE_SUCCESS);
			$this->log("Importing Media: ".$file." successful");
			return $img_data['media_url'];	
		}
		else
		{
			$this->log("Db Insert Failed: ".var_export($img_data,true));
			rename($newpath,$oldpath);	//move it back.
			return false;
		}
		
		
	}


	/**
	 * Calculate Icon Category from image meta data. 
	 * @param array $img image meta data.
	 * @return string
	 */
	private function getIconCategory($img)
	{

		if($img['media_type'] == 'image/svg+xml')
		{
			return "_icon_svg";
		}

		$sizes = array(16,32,48,64);

		$dimensions = $img['media_dimensions'];

		foreach($sizes as $dim)
		{
			list($w,$h) = explode(" x ", $dimensions);

			if($w == $dim || $h == $dim)
			{
				return "_icon_".$dim;
			}

		}

		return "_icon_64"; // default.

	}




	/**
	 * Check File-name against mime-type and add missing extension if necessary.
	 * @param $path
	 * @param $mime
	 * @return string
	 */
	public function checkFileExtension($path, $mime)
	{
		if(empty($mime))
		{
			return $path;
		}

		$ext = (string) e107::getFile()->getFileExtension($mime);

		$len = strlen($ext);

		if($ext && (substr($path,- $len) != $ext))
		{
			return $path.$ext;
		}
		else
		{
			return $path;
		}

	}


	/**
	 * @param (array) $data = [
	 *  'close' => (int)
	 *  'style' => (string)
	 *  'class' => (string)
	 * ]
	 * @return string
	 */
	private function browserCarouselItemSelector($data)
	{
	//	$close  = (E107_DEBUG_LEVEL > 0) ? "" : "  data-close='true' ";	//
	//	$select = (E107_DEBUG_LEVEL > 0) ? '' : " ";
		$close = '';
		$select = '';

		if(!empty($data['close']) && E107_DEBUG_LEVEL < 1)
		{
			$select .= "e-dialog-close";
			$close = "  data-close='true' ";
		}

		// e-dialog-save

		$style  = varset($data['style']);
		$class  = varset($data['class']);
		$dataPreview = !empty($data['previewHtml']) ? base64_encode($data['previewHtml']) : '';

		return "<a data-toggle='context' data-bs-toggle='context' class='e-media-select ".$select." ".$class."' ".$close." data-id='".$data['id']."' data-width='".$data['width']."' data-height='".$data['height']."' data-src='".$data['previewUrl']."' data-type='".$data['type']."' data-bbcode='".$data['bbcode']."' data-target='".$data['tagid']."' data-path='".$data['saveValue']."' data-preview='".$data['previewUrl']."'  data-preview-html='".$dataPreview."' title=\"".$data['title']."\" style='".$style."' href='#' >";

	}


	/**
	 * @param array $row
	 * @return string
	 */
	function browserCarouselItem($row = array())
	{
		$tp = e107::getParser();
		
		$defaultThumb = $tp->thumbUrl('','w=400&h=240');	
	
		$default = array(
			'width'			=> 200,
			'height'		=> 113,
			'id'			=> '',
			'type'			=> 'image',
			'tagid'			=> '',
			'saveValue'		=> '',
			'previewUrl'	=> $defaultThumb ,
			'previewHtml'   => null,
			'thumbUrl'		=> $defaultThumb,
			'title'			=> '',
			'gridClass'		=> 'span2 col-md-2',
			'bbcode'		=> '',
			'tooltip'       => '',
			'close'         => true // close modal window after item selected
			
		);
		
		$data = array();
		
		foreach($default as $k=>$v)
		{
			$data[$k] = isset($row[$k]) ? $row[$k] : $v;
		}
		
			


		$text = "\n\n
		
		<div class='media-carousel ".$data['gridClass']."'>
		
			<div class='well clearfix media-carousel-item-container'>\n";


				$caption = $data['title'];

				if(!empty($data['tooltip']))
				{
					$data['title'] = $data['tooltip'];
				}

				$linkTag = $this->browserCarouselItemSelector($data);



				switch($data['type'])
				{
					case "video":
					case "audio":

						if($data['type'] === 'video') // video
						{
							$text .= $tp->toVideo($data['thumbUrl'], array('w'=>$data['width'],'h'=>'', 'mime'=>$data['mime']));
						}
						else    // audio
						{
							$text .= $tp->toAudio($data['thumbUrl'], array('mime'=>$data['mime']));
						}

						$text .= "<div class='row media-carousel-item-controls'>
									<div class='col-sm-8'><small class='media-carousel-item-caption'>";

						$text .= $this->browserCarouselItemSelector($data);
						$text .= "\n".$caption;
						$text .= "\n</a></small></div>";

						$data['style'] = 'float:right';

						$text .= "<div class='col-sm-4 text-right'>".
						$this->browserCarouselItemSelector($data).
						"<button class='btn btn-xs btn-primary' style='margin-top:7px'>".LAN_SELECT."</button></a></div>
								</div>\n\n";
						break;
						

					case "image":


						$text .= $linkTag;
						$text .= "<span>";
						$text .= '<img class="img-responsive img-fluid" alt="" src="'.$data['thumbUrl'].'" style="display:inline-block" />';
						$text .= "</span>";
						$text .= "\n</a>\n\n";

							$text .= "<div class='row media-carousel-item-controls'>
									<div class='col-sm-8'><small class='media-carousel-item-caption'>";

						$text .= $this->browserCarouselItemSelector($data);
						$text .= "\n".$caption;
						$text .= "\n</a></small></div>";

						$data['style'] = 'float:right';

						$text .= "<div class='col-sm-4 text-right'>".
						$this->browserCarouselItemSelector($data).
						"<button class='btn btn-xs btn-primary' style='margin-top:7px'>".LAN_SELECT."</button></a></div>
</div>";



					//	$text .= "\n<div><small class='media-carousel-item-caption'>".$data['title']."</small></div>";
						break;


					case "glyph":
						$text .= $linkTag;
						$text .= "\n<span style='margin:7px;display:inline-block;color: inherit'>".$tp->toGlyph($data['thumbUrl'],array('placeholder'=>''))."</span>";
						$text .= "\n</a>\n\n";

						break;

					case "icon":
						$text .= $linkTag;
						$text .= "\n<span style='margin:7px;display:inline-block;color: inherit'>".$tp->toIcon($data['thumbUrl'],array('placeholder'=>''))."</span>";
						$text .= "\n</a>\n\n";

						break;

					default:
						// code to be executed if n is different from all labels;
				}

			
			$text .= "</div>
			
			</div>\n\n\n";



		
		return $text;

	}

	/**
	 * @param $slides
	 * @param $uniqueID
	 * @return string
	 */
	function browserIndicators($slides, $uniqueID)
	{
	
		if(count($slides)<1)
		{
			return '';
		}
		
		 $indicators = '<ol class="carousel-indicators col-md-2 span2" style="top:-40px">
			<li data-target="#'.$uniqueID.'" data-slide-to="0" class="active"></li>';
				
		foreach($slides as $key=>$v)
		{
			$id = $key + 1;	
			$indicators .= '<li data-target="#'.$uniqueID.'" data-slide-to="'.$id.'"></li>';
		}
		
		$indicators .=	'</ol>';		
						
		return $indicators;
		
	}




	/**
	 * Retriveve a Media-Manager thumbnail which was saved from a remote location. .
	 * @param $id
	 * @return bool|string
	 */
	function getThumb($id)
	{
		$id = trim($id);
		$filename = 'temp/thumb-'.md5($id).".jpg";
		$filepath = e_MEDIA.$filename;

		if(file_exists($filepath))
		{
			return e107::getParser()->createConstants($filepath);
		}

		e107::getMessage()->addDebug("Couldn't find ".$filepath);
		return false;
	}



	/**
	 * Save a Media-Manager thumbnail from remote location.
	 * @param string $imageUrl
	 * @param string $id
	 * @return bool|string
	 */
	function saveThumb($imageUrl='',$id='')
	{

		if(empty($id) || empty($imageUrl))
		{
			return false;
		}

		$filename = 'temp/thumb-'.md5($id).".jpg";
		$filepath = e_MEDIA.$filename;

		if(!file_exists($filepath))
		{
			e107::getFile()->getRemoteFile($imageUrl, $filename,'media');
		}

		return $filepath;
	}


	/**
	 * Carousel Item Browser. 
	 * @param array|string $data - array for items or string for an error alert.
	 * @param array $parm
	 * @return string
	 */
	function browserCarousel($data,$parm=null)
	{
			/* Fix for Bootstrap2 margin-left issue when wrapping */
		e107::css('inline','
				
		
		
		.row-fluid .media-carousel.span6:nth-child(2n + 3) { margin-left : 0px; }
		.row-fluid .media-carousel.span4:nth-child(3n + 4) { margin-left : 0px; }
		.row-fluid .media-carousel.span3:nth-child(4n + 5) { margin-left : 0px; }
		.row-fluid .media-carousel.span2:nth-child(6n + 7) { margin-left : 0px; }
		');
			
	//	$frm = e107::getForm();
		
	//	$text .= print_a($_GET,true);
	
			$data_src = $this->mediaSelectNav(varset($parm['category']), $parm['tagid'], $parm);
			$carouselID = 'media-carousel-'.$parm['action'];
			$searchToolttip = (empty($parm['searchTooltip'])) ? IMALAN_186 : $parm['searchTooltip'];
			//$text = "<form class='form-search' action='".e_SELF."?".e_QUERY."' id='core-plugin-list-form' method='get'>";
					
			$text = '';
						
			if(!e_AJAX_REQUEST)
			{
				$searchPlaceholder = varset($parm['searchPlaceholder'], LAN_SEARCH);
				
				$text = '<div class="btn-group"><span class="input-append form-inline">';
				$text .= "<input type='text' class='form-control e-ajax-keyup input-xxlarge ' placeholder= '".$searchPlaceholder."...' title=\"".$searchToolttip."\" name='search' value=''  data-target='media-browser-container-".$parm['action']."' data-src='".$data_src."' />";
		//		$text .= "<span class='field-help'>bablalal</span>";
			//	$text .= '<button class="btn btn-primary" name="'.$submitName.'" type="submit">'.LAN_GO.'</button>';
			//	$text .= '<a class="btn btn-primary" href="#'.$carouselID.'" data-slide="prev">&lsaquo;</a><a class="btn btn-primary" href="#'.$carouselID.'" data-slide="next">&rsaquo;</a>';


				$text .= '&nbsp;<div class="btn-group" >
			<a id="'.$carouselID.'-prev" class="btn btn-primary btn-secondary" href="#'.$carouselID.'" data-slide="prev"><i class="fa fa-backward"></i></a>
			<a id="'.$carouselID.'-index" class="media-carousel-index btn btn-primary btn-secondary">1</a>
			<a id="'.$carouselID.'-next" class="btn btn-primary btn-secondary" href="#'.$carouselID.'" data-slide="next"><i class="fa fa-forward"></i></a>
			</div>';



				$text .= "</span>";
				$text .= "</div>";
				$text .= "<div id='media-browser-container-".$parm['action']."' class='form-inline clearfix row-fluid'>";
			}
		

			
		
		//	$text .= $this->search('srch', $srch, 'go', $filterName, $filterArray, $filterVal).$frm->hidden('mode','online');
			
			
				$text .= '<div id="'.$carouselID.'"  class="carousel slide" data-interval="false" data-wrap="false">';
			//	$text .= '{INDICATORS}';
				$text .= '<div style="margin-top:10px" class="row admingrid carousel-inner">';
			
			
			//	$text .= "<div class='item active'>";
				
				$perPage = vartrue($parm['perPage'],12);
				
				$c=0;
				$count = 0;

				
				$slides = array();
				$totalSlides = 0;

				if(is_array($data) && count($data) > 0)
				{


					foreach($data as $key=>$val)
					{

						if($c == 0)
						{
							$active = (count($slides) <1) ? ' active' : '';
							$totalSlides++;

							$text .= '

							<!-- Start Slide '.$parm['action'].' '.$totalSlides.' -->
							<div class="item'.$active.'">';

							if($totalSlides  > 2)
							{
								$text .= "<!-- ";
							}



							if(vartrue($val['slideCaption']))
							{
								$text .= "<h4>".$val['slideCaption']."</h4>";
							}
						}

						$val['width']	= $parm['width'];
						$val['height']	= $parm['height'];
						$val['id']		= varset($parm['id']);
						$val['tagid']	= $parm['tagid'];
						$val['type']	= $parm['type'];
						$val['bbcode']	= $parm['bbcode'];
						$val['gridClass'] = $parm['gridClass'];


						$text .= $this->browserCarouselItem($val);

						$c++;


						if(varset($val['slideCategory']) && isset($prevCat))
						{
							if($val['slideCategory'] !== $prevCat)
							{
								$c = $perPage;
							}

							$prevCat = 	$val['slideCategory'];

						}

						$count++;

						if($c == $perPage || (count($data) == $count))
						{

							if($totalSlides > 2)
							{
								$text .= " -->";
							}


							$text .= '
							</div>
							<!-- End Slide '.$parm['action'].' '.$totalSlides.' -->';



							$slides[] = 1;
							$c = 0;
						}


					}
				
				}
				elseif(is_string($data)) // error message.
				{
					$text .= "<div style='line-height: 1.5;'>".$data."</div>";
				}
				else
				{
					$text .= "<div class='alert alert-info alert-block text-center'>".LAN_NO_RESULTS_FOUND."</div>";
				}

				$text .= ($c != 0) ? "</div>\n<!-- End Slide -->\n" : "";
			
			
				
				$text .= "</div>";
				
				$text .= "\n<!-- End Carousel -->\n<div class='clearfix'>&nbsp;</div>\n\n";
				
			if(!e_AJAX_REQUEST)
			{	
				$text .= "</div></div>";
			}	
			
			$ret = str_replace('{INDICATORS}', $this->browserIndicators($slides,$carouselID), $text);

			//if(E107_DEBUG_LEVEL > 0)
			{
		//		print_a($parm);
			}


			return $ret;
				
	}


	/**
	 * Resize an image.
	 * @param string $src
	 * @param string $dest
	 * @param string|array $opts
	 * @return bool
	 */
	public function resizeImage($src='',$dest='',$opts=null)
	{
		$pref = e107::getPref();
		$tp = e107::getParser();

		if(empty($src))
		{
			return false;
		}

		if(is_string($opts))
		{
			parse_str($opts,$opts);
		}

		$quality = vartrue($pref['thumbnail_quality'], 65);

		$src = $tp->replaceConstants($src);
		$dest =  $tp->replaceConstants($dest);

		if(!file_exists($src))
		{
			return false;
		}

		$maxWidth = varset($opts['w'], 800);
		$maxHeight = varset($opts['h'], 800);

		$destDir = dirname($dest);
		$destFile = basename($dest);

		$destFilePath = $destDir."/".varset($opts['prefix'],$maxWidth.'x'.$maxHeight).'_'.$destFile;

		if(empty($opts['overwrite']) && file_exists($destFilePath))
		{
			return $destFilePath;
		}

		try
		{
			$thumb = Intervension::make($src);
			if(!empty($opts['crop']))
			{
				$thumb->fit($maxWidth, $maxHeight, function ($constraint) {
				    $constraint->upsize();
				});
			}
			else
			{
				$thumb->resize(vartrue($maxWidth, null), vartrue($maxHeight, null), function ($constraint)
				{

			        $constraint->aspectRatio();
		            $constraint->upsize();
				});
			}

			$thumb->save($destFilePath, $quality);
			return $destFilePath;
		}
		catch (Exception $e)
		{
			$error =  array('thumbnailer'=> $e->getMessage(), 'src'=>$src, 'dest'=>$dest, 'savePath'=>$destFilePath, 'backtrace'=>'e_media::resizeImage');
			e107::getMessage()->addDebug(print_a($error,true));
			e107::getLog()->add("RESIZE ERROR",$error,E_LOG_INFORMATIVE,'RESIZE');
			return false;
		}




	}



	/**
	 * Convert an image to jpeg format. 
	 * @param string $oldFile path to png or gif file.
	 * @param bool $deleteOld - set to true to delete original after conversion.
	 * @return string path to new file.
	 */
	public function convertImageToJpeg($oldFile, $deleteOld=false)
	{
		if(substr($oldFile,-4) !== '.gif' && substr($oldFile,-4) !== '.png') //  jpg or some other format already
		{
			return false;
		}

		if(strpos($oldFile,".gif") !==false)
		{
			$type = '.gif';
		}

		if(strpos($oldFile,".png") !==false)
		{
			$type = '.png';
		}

		if(empty($type))
		{
			return $oldFile;
		}

		$jpgFile = str_replace($type, ".jpg", $oldFile);

		$compression = e107::getPref('thumbnail_quality', 45); // 0 = worst / smaller file, 100 = better / bigger file

		if(!file_exists($jpgFile))
		{

			switch($type)
			{
				case ".gif":
					$image = imagecreatefromgif($oldFile);
					break;

				case ".png":
					$image = imagecreatefrompng($oldFile);
					break;

			}

			if(empty($image))
			{
				return false;
			}

			if(imagejpeg($image, $jpgFile, $compression) === true)
			{
				if($deleteOld === true)
				{
					unlink($oldFile);
				}
			}
			else
			{
				$jpgFile  = false; // fallback to original
			}

		//	e107::getLog()->addSuccess("Converting <b>".$oldFile."</b> to <b>".$jpgFile."</b>");
			imagedestroy($image);
		}


		return $jpgFile;
	}


	/**
	 * Media-Manager Upload processing - drag-n-drop and plupload
	* @return string
	*/
	public function processAjaxUpload()
	{

		// Settings
		$targetDir = e_IMPORT;
		$cleanupTargetDir = true; // Remove old files
		$maxFileAge = 5 * 3600; // Temp file age in seconds


		// Get parameters
		$chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
		$chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;
		$fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';

		if(!empty($_FILES['file']['name']) && $_FILES['file']['name'] !== 'blob' ) // dropzone support v2.1.9
		{
			$fileName = $_FILES['file']['name'];
		}


		// Clean the fileName for security reasons
		$fileName = preg_replace('/[^\w\._]+/', '_', $fileName);

		//	$array = array("jsonrpc" => "2.0", "error" => array('code'=>$_FILES['file']['error'], 'message'=>'Failed to move file'), "id" => "id",  'data'=>$_FILES );


		// Make sure the fileName is unique but only if chunking is disabled
		if($chunks < 2 && file_exists($targetDir . $fileName))
		{
			$ext = strrpos($fileName, '.');
			$fileName_a = substr($fileName, 0, $ext);
			$fileName_b = substr($fileName, $ext);

			$count = 1;
			while(file_exists($targetDir .  $fileName_a . '_' . $count . $fileName_b))
			{
				$count++;
			}

			$fileName = $fileName_a . '_' . $count . $fileName_b;
		}


		$filePath = $targetDir .  $fileName;

		// Create target dir
		if(!file_exists($targetDir))
		{
			@mkdir($targetDir);
		}

		// Remove old temp files
		if($cleanupTargetDir && is_dir($targetDir) && ($dir = opendir($targetDir)))
		{
			while(($file = readdir($dir)) !== false)
			{
				$tmpfilePath = $targetDir .  $file;

				// Remove temp file if it is older than the max age and is not the current file
				if(preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge) && ($tmpfilePath != "{$filePath}.part"))
				{
					@unlink($tmpfilePath);
				}
			}

			closedir($dir);
		}
		else
		{
			return '{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}';
		}


		// Look for the content type header

		$contentType = null;

		if(isset($_SERVER["HTTP_CONTENT_TYPE"]))
		{
			$contentType = $_SERVER["HTTP_CONTENT_TYPE"];
		}

		if(isset($_SERVER["CONTENT_TYPE"]))
		{
			$contentType = $_SERVER["CONTENT_TYPE"];
		}

		// Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
		if(strpos($contentType, "multipart") !== false)
		{
			if(isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name']))
			{
				// Open temp file
				$out = fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");

				if($out)
				{
					// Read binary input stream and append it to temp file
					$tmpName = e107::getParser()->filter($_FILES['file']['tmp_name']);
					$in = fopen($tmpName, "rb");

					if($in)
					{
						while($buff = fread($in, 4096))
						{
							fwrite($out, $buff);
						}
					}
					else
					{
						return '{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}';
					}
					fclose($in);
					fclose($out);
					@unlink($tmpName);
				}
				else
				{
					return '{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}';
				}
			}
			else // Misc Error.
			{
				$phpFileUploadErrors = array(
				    0 => 'There is no error, the file uploaded with success',
				    1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
				    2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
				    3 => 'The uploaded file was only partially uploaded',
				    4 => 'No file was uploaded',
				    6 => 'Missing a temporary folder',
				    7 => 'Failed to write file to disk.',
				    8 => 'A PHP extension stopped the file upload.',
				);

				$err = (int) $_FILES['file']['error'];

				$array = array("jsonrpc" => "2.0", "error" => array('code'=>$err, 'message'=> $phpFileUploadErrors[$err]), "id" => "id",  'data'=>$_FILES );

				return json_encode($array);

			}


		}
		else
		{
			// Open temp file
			$out = fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
			if($out)
			{
				// Read binary input stream and append it to temp file
				$in = fopen("php://input", "rb");

				if($in)
				{
					while($buff = fread($in, 4096))
					{
						fwrite($out, $buff);
					}
				}
				else
				{
					return '{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}';
				}

				fclose($in);
				fclose($out);
			}
			else
			{
				return '{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}';
			}
		}

		$filePath = str_replace('//','/',$filePath); // cleanup .

		// Check if file has been uploaded
		if(!$chunks || $chunk == $chunks - 1)
		{
			// Strip the temp .part suffix off
			rename("{$filePath}.part", $filePath);
		}


		return $this->processAjaxImport($filePath, $_REQUEST); 
		
	}

	/**
	 * For Internal Use Only
	 * Second half of processAjaxUpload()
	 * Subject to change at any time. Use at own risk.
	 *
	 * @param string $filePath
	 * @param array $request
	 * @return false|string
	 */
	public function processAjaxImport($filePath, $request = array())
	{
		if(!file_exists($filePath))
		{
			return '{"jsonrpc" : "2.0", "error" : {"code": 110, "message": "File Not Found: '.$filePath.'"}, "id" : "id"}';
		}

		$targetDir = e_IMPORT;
		$fileName = basename($filePath);

		if(e107::getFile()->isAllowedType($filePath) !== true)
		{
			$this->ajaxUploadLog($filePath, $fileName, filesize($filePath), false, "Unapproved file-type. (".__METHOD__.")");
			@unlink($filePath);
			return '{"jsonrpc" : "2.0", "error" : {"code": 120, "message": "Unapproved file-type detected. '.$filePath.'"}, "id" : "id"}';
		}
	
		if(e107::getFile()->isClean($filePath) !== true)
		{
			$this->ajaxUploadLog($filePath, $fileName, filesize($filePath), false, "File detected as not clean. (".__METHOD__.")");
			@unlink($filePath);
			return '{"jsonrpc" : "2.0", "error" : {"code": 104, "message": "Bad File Detected. '.$filePath.'"}, "id" : "id"}';
		}


		$convertToJpeg = e107::getPref('convert_to_jpeg', 0);

		if(!empty($request['convert']) && $request['convert'] === 'jpg')
		{
			$convertToJpeg = true;
		}

		$fileSize = filesize($filePath);

		if(varset($request['for']) !== '_icon' && !empty($convertToJpeg))
		{
			if($jpegFile = e107::getMedia()->convertImageToJpeg($filePath, true))
			{
				$filePath = $jpegFile;
				$fileName = basename($filePath);
				$fileSize = filesize($jpegFile);
			}

		}

		if(!empty($request['resize']))
		{

			$thumb = Intervension::make($filePath);
			$w = (int) $request['resize']['w'];
			$h = (int) $request['resize']['h'];

			$thumb->resize(vartrue($w, null), vartrue($h, null), function ($constraint)
			{
		        $constraint->aspectRatio();
	            $constraint->upsize();
			});

			$thumb->save($filePath);

		}

		if(!empty($request['rename']))
		{
			$newPath = $targetDir.basename($request['rename']);
			if(!rename($filePath, $newPath))
			{
				return '{"jsonrpc" : "2.0", "error" : {"code": 105, "message": "Unable to rename '.$filePath.' to '.$newPath.'"}, "id" : "id"}';
			}
			$fileName = basename($newPath);
		}

		$msg = '';

		if(!empty($request['for'])) // leave in upload directory if no category given.
		{
			$uploadPath = varset($request['path'],null);
			$for = e107::getParser()->filter($request['for']);
			$for = str_replace(array('+','^'),'', $for);

			if(!$result = e107::getMedia()->importFile($fileName, $for, array('path'=>$uploadPath)))
			{
				$msg = 'Unable to import ('.__METHOD__.' Line: '.__LINE__ .')';
			}
		}
		else
		{
			$result = true; // uploaded but not imported.
		}


		$this->ajaxUploadLog($filePath,$fileName,$fileSize,$result, $msg);

		$opts = array();

		// set correct size for preview image.
		if(isset($request['w']))
		{
			$opts['w'] = (int) $request['w'];
		}

		if(isset($request['h']))
		{
			$opts['h'] = (int) $request['h'];
		}

		$preview = $this->previewTag($result,$opts);
		$array = array("jsonrpc" => "2.0", "result" => $result, "id" => "id", 'preview' => $preview, 'data'=>$_FILES );

		return json_encode($array);
	
	
	}

	/**
	 * @param string $filePath
	 * @param string $fileName
	 * @param int $fileSize
	 * @param bool $result
	 * @param string $msg
	 * @return void
	 */
	private function ajaxUploadLog($filePath, $fileName, $fileSize, $result, $msg='')
	{
		$log = e107::getParser()->filter($_GET);

		$log['filepath'] = str_replace('../','',$filePath);
		$log['filename'] = $fileName;
		$log['filesize'] = $fileSize;
		$log['status'] = ($result) ? 'ok' : 'failed';
		$log['_files'] = $_FILES;
		$log['_request'] = $_REQUEST;

		if(!empty($msg))
		{
			$log['_msg'] = $msg;
		}

		//	$log['_get'] = $_GET;
		//	$log['_post'] = $_POST;
		$type = ($result) ? E_LOG_INFORMATIVE : E_LOG_WARNING;

		e107::getLog()->add('LAN_AL_MEDIA_01', print_r($log, true), $type, 'MEDIA_01');

	}

}
