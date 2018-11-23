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
				'image'			=> array('jpeg','jpg','png','gif', 'svg'),
				'video'			=> array('mp4', 'youtube','youtubepl'),
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
	
		if(!count($img_array))
		{
			e107::getMessage()->addDebug("Media-Import could not find any files in <b>".$epath."</b> with fmask: ".$fmask);
			return $this;
		}
		
	//	print_a($img_array);
	//	return;
		$count = 0;
		foreach($img_array as $f)
		{
			
			if($f['fsize'] === 0) // prevent zero-byte files. 
			{
				continue;	
			}
			
			if(vartrue($options['min-width']) && ($f['img-width'] < $options['min-width']))
			{
				continue;	
			}
			
			if(vartrue($options['min-size']) && ($f['fsize'] < $options['min-size']))
			{
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
		}

	//	if($count)
		{
			// $mes->addSuccess("Imported {$count} Media items.");
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
	
	public function deleteCategory($id)
	{
		// TODO
	}
	
	public function deleteAllCategories($owner='')
	{
		if($owner == '')
		{
			return null;
		}
		
		$sql = e107::getDb();
		
		$sql->select('core_media_cat',"media_cat_category", "media_cat_owner = '".$owner."' ");
		while($row = $sql->db_Fetch())
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
	 * @deprecated Currently used only by ren_help PreImage_Select
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
			
		while ($row = $sql->db_Fetch())
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
					
		$url = e_ADMIN_ABS."image.php?mode=main&amp;action=".$action."&amp;iframe=1".$cat."&amp;from=0";
		
		return $url;	
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
			e107::getBB()->clearclass();

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

			
			$text .= "<a data-toggle='context' class='thumbnail {$class} e-tip' data-id='{$im['media_id']}' data-width='{$w}' data-height='{$h}' data-src='{$media_path}' data-bbcode='{$data_bb}' data-target='{$tagid}' data-path='{$im['media_url']}' data-preview='{$realPath}' data-alt=\"".$media_alt."\" title=\"".$diz."\" style='float:left' href='#' onclick=\"{$onclicki}\" >";
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
		if(file_exists($newpath) && ($f = e107::getFile()->get_file_info($oldpath,TRUE)))
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
	function getGlyphs($type='fa4', $addPrefix = '')
	{
		$icons = array();
		
		if($type === 'bs2')
		{
			$matches = array(
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
				
			foreach($matches as $match)
			{
			    $icons[] = $addPrefix.$match;
			}
			
			return $icons;
		}
					
		if($type === 'bs3')
		{
			$matches = array(
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
			
			foreach($matches as $match)
			{
			    $icons[] = $addPrefix.$match;
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
			if(substr($path,0,4) === 'http')
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




	public function getPath($mime, $path=null)
	{
		$mes = e107::getMessage();

		list($pmime,$tmp) = explode('/',$mime);
		unset($tmp);

		if(!vartrue($this->mimePaths[$pmime]))
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
			};
		}
		return $dir;
	}

	/**
	 * detected Media Type from Media URL
	 * @param string $mediaURL
	 * @return int|string
	 */
	public function detectType($mediaURL)
	{
		$type = pathinfo($mediaURL,PATHINFO_EXTENSION);

		foreach($this->mimeExtensions as $key=>$exts)
		{
			if(!in_array($type, $exts))
			{
				continue;
			}

			return $key;
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

		$type = !empty($options['type']) ? $options['type'] : $this->detectType($default);

		$width = vartrue($options['w'], 220);
		$height = vartrue($options['h'], 190);
		$preview = '';

		switch($type)
		{

			case "video":
				$preview = $tp->toVideo($default, array('w'=>$width, 'h'=> ($height - 50)));
			//	$previewURL = $tp->toVideo($default, array('mode'=>'url'));
				break;

			case "audio":
				$preview = $tp->toAudio($default);
			//	$previewURL = false;
				break;

			case "image":
				$preview = $tp->toImage($default, array('w'=>$width, 'h'=>$height, 'class'=>'image-selector img-responsive img-fluid', 'legacy'=>varset($options['legacyPath'])));
			//	$previewURL = $tp->thumbUrl($default, array('w'=>800));
				break;

			case "application": // file.
			//	$preview = $tp->toImage($default, array('w'=>$width, 'h'=>$height, 'class'=>'image-selector img-responsive img-fluid'));
			//	$previewURL = $tp->thumbUrl($default, array('w'=>800));
				break;

			case "glyph":
				$preview = $tp->toGlyph($default);
			break;

			case "icon":
				$preview = $tp->toIcon($default);
			//	$previewURL = false;
			break;

			default: // blank
				$preview = null;

		}

		return $preview;
	}




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
		
		$info = e107::getFile()->get_file_info($path,true);
		
		
		
		$this->log("File info for $path : ".print_r($info,true));
		
		return array(
			'media_type'		=> vartrue($info['mime']),
			'media_datestamp'	=> time(),
			'media_url'			=> e107::getParser()->createConstants($info['fullpath'], 'rel'),
			'media_size'		=> filesize($info['fullpath']),
			'media_author'		=> USERID,
			'media_usedby'		=> '',
			'media_tags'		=> '',
			'media_dimensions'	=> $info['img-width']." x ".$info['img-height']
		);
	}
	

	
	
	
	
	
	
	
	
	
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

		
		if(!$typePath = $this->getPath($img_data['media_type'], $uploadPath))
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
		};

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


		$ext = e107::getFile()->getFileExtension($mime);

		if($ext && (substr($path,-4) != $ext))
		{
			return $path.$ext;
		}
		else
		{
			return $path;
		}

	}


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

		$style  = varset($data['style'],'');
		$class  = varset($data['class'],'');
		$dataPreview = !empty($data['previewHtml']) ? base64_encode($data['previewHtml']) : '';

		$linkTag = "<a data-toggle='context' class='e-media-select ".$select." ".$class."' ".$close." data-id='".$data['id']."' data-width='".$data['width']."' data-height='".$data['height']."' data-src='".$data['previewUrl']."' data-type='".$data['type']."' data-bbcode='".$data['bbcode']."' data-target='".$data['tagid']."' data-path='".$data['saveValue']."' data-preview='".$data['previewUrl']."'  data-preview-html='".$dataPreview."' title=\"".$data['title']."\" style='".$style."' href='#' >";

		return $linkTag;

	}

	
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
			$data[$k] = isset($row[$k]) ? $row[$k] : $default[$k];	
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
	
			$data_src = $this->mediaSelectNav($parm['category'], $parm['tagid'], $parm);
			$carouselID = 'media-carousel-'.$parm['action'];
			$searchToolttip = (empty($parm['searchTooltip'])) ? "Enter some text to filter results" : $parm['searchTooltip'];
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
						$val['id']		= $parm['id'];
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
	 * @param $src
	 * @param $dest
	 * @param string $opts
	 * @return bool
	 */
	function resizeImage($src='',$dest='',$opts=null)
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

		if(file_exists($destFilePath))
		{
			return $destFilePath;
		}


		@require(e_HANDLER.'phpthumb/ThumbLib.inc.php');
		try
		{
			$thumb = PhpThumbFactory::create($src);
			$thumb->setOptions(array('correctPermissions' => true, 'resizeUp' => false, 'jpegQuality' => $quality));
			$thumb->resize($maxWidth, $maxHeight);
			$thumb->save($destFilePath);
			return $destFilePath;
		}
		catch (Exception $e)
		{
			$error =  array('thumbnailer'=> $e->getMessage(), 'src'=>$src, 'dest'=>$dest, 'savePath'=>$destFilePath, 'backtrace'=>'e_media::resizeImage');;
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

		// Clean the fileName for security reasons
		$fileName = preg_replace('/[^\w\._]+/', '_', $fileName);

		if(!empty($_FILES['file']['name']) && $_FILES['file']['name'] !== 'blob' ) // dropzone support v2.1.9
		{
			$fileName = $_FILES['file']['name'];
		}

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
					$tmpName = e107::getParser()->filter($_FILES['file']['tmp_name'],'str');
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

		if(e107::getFile()->isClean($filePath) !== true)
		{
			$this->ajaxUploadLog($filePath,$fileName, filesize($filePath), false);
			@unlink($filePath);
			return '{"jsonrpc" : "2.0", "error" : {"code": 104, "message": "Bad File Detected. '.$filePath.'"}, "id" : "id"}';
		}


		$convertToJpeg = e107::getPref('convert_to_jpeg', 0);
		$fileSize = filesize($filePath);

		if(varset($_GET['for']) !== '_icon' && !empty($convertToJpeg))
		{
			if($jpegFile = e107::getMedia()->convertImageToJpeg($filePath, true))
			{
				$filePath = $jpegFile;
				$fileName = basename($filePath);
				$fileSize = filesize($jpegFile);
			}

		}

		if(!empty($_GET['for'])) // leave in upload directory if no category given.
		{
			$uploadPath = varset($_GET['path'],null);
			$for = e107::getParser()->filter($_GET['for']);
			$for = str_replace(array('+','^'),'', $for);

			$result = e107::getMedia()->importFile($fileName, $for, array('path'=>$uploadPath));
		}
		else
		{
			$result = true; // uploaded but not imported.
		}


		$this->ajaxUploadLog($filePath,$fileName,$fileSize,$result);


		$preview = $this->previewTag($result);
		$array = array("jsonrpc" => "2.0", "result" => $result, "id" => "id", 'preview' => $preview, 'data'=>$_FILES );

		return json_encode($array);


	}


	private function ajaxUploadLog($filePath,$fileName,$fileSize,$result)
	{
		$log = e107::getParser()->filter($_GET,'str');
		$log['filepath'] = str_replace('../','',$filePath);
		$log['filename'] = $fileName;
		$log['filesize'] = $fileSize;
		$log['status'] = ($result) ? 'ok' : 'failed';
		$log['_files'] = $_FILES;
		$log['_request'] = $_REQUEST;
		//	$log['_get'] = $_GET;
		//	$log['_post'] = $_POST;
		$type = ($result) ? E_LOG_INFORMATIVE : E_LOG_WARNING;

		e107::getLog()->add('LAN_AL_MEDIA_01', print_r($log, true), $type, 'MEDIA_01');

	}

}
