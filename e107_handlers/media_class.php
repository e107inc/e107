<?php
/*
 * e107 website system
 *
 * Copyright (C) e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Administration - Media Management Class
 *
 * $URL$
 * $Id$
 *
*/


if (!defined('e107_INIT')) { exit; }

/**
 * Subject of rewrite/rethinking after the pre-alpha
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
	
	function __construct()
	{
		if(E107_DEBUG_LEVEL > 0)
		{
			$this->logging = true; 	
		}	
		
	}
	/**
	 * Import files from specified path into media database. 
	 * @param string $cat Category nickname
	 * @param string $epath path to file.
	 * @param string $fmask [optional] filetypes eg. .jpg|.gif IMAGES is the default mask. 
	 * @return e_media
	 */
	public function import($cat='',$epath,$fmask='',$options=array())
	{
		if(!vartrue($cat)){ return $this;}
		
		if(is_string($options))
		{
			parse_str($options,$options);	
		}
		
		if(!is_readable($epath))
		{
			return $this;
		}
			
		$fl = e107::getFile();
		$tp = e107::getParser();
		$sql = e107::getDb();
		$mes = e107::getMessage();
	
		$fl->setFileInfo('all');
		if(!$fmask)
		{
			$fmask = '[a-zA-z0-9_-]+\.(png|jpg|jpeg|gif|PNG|JPG|JPEG|GIF)$';
		}
		$img_array = $fl->get_files($epath,$fmask,'',2);
	
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
		if($count)
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
		return $this;
	}
	
	
	
	
	/**
	 * Remove Media from media table
	 * @param string $cat [optional] remove a full category of media
	 * @return 
	 */
	function removeCat($cat)
	{
		$tp = e107::getParser();
		$sql = e107::getDb();
		$mes = e107::getMessage();
						
		if(vartrue($cat))
		{
			$status = ($sql->delete('core_media',"media_cat = '".$cat."'")) ? TRUE : FALSE;
			$mes->add("Removing Media in Category: ".$cat, E_MESSAGE_DEBUG);
			return $status;	
		}	
	}



	/**
	 * Remove Media from media table
	 * @param string $epath remove media in the specified path.
	 * @param string $type [optional] image|icon
	 * @return 
	 */
	function removePath($epath, $type='image')
	{
		$tp = e107::getParser();
		$sql = e107::getDb();
		$mes = e107::getMessage();
		
		$qry = ($type == 'icon') ? " AND media_category REGEXP '_icon_16|_icon_32|_icon_48|_icon_64' " : " AND NOT media_category REGEXP '_icon_16|_icon_32|_icon_48|_icon_64' ";
								
		if(vartrue($epath))
		{
			$path = $tp->createConstants($epath, 'rel');
			$status = ($sql->delete('core_media',"media_url LIKE '".$path."%'".$qry)) ? TRUE : FALSE;
			$message = ($type == 'image') ?  "Removing Media with path: ".$path : "Removing Icons with path: ".$path;
			$mes->add($message, E_MESSAGE_DEBUG);
			return $status;	
		}			
	}
	
	
	
	/**
	 * Return a list if icons in the specified path
	 * @param string $epath
	 * @return array
	 */
	function listIcons($epath)
	{
		if(!$epath) return;
		
		$ret = array();
		$sql = e107::getDb();
		$tp = e107::getParser();
		
		$path = $tp->createConstants($epath, 'rel');
	
		$status = ($sql->gen("SELECT * FROM `#core_media` WHERE `media_url` LIKE '".$path."%' AND media_category REGEXP '_icon_16|_icon_32|_icon_48|_icon_64' ")) ? TRUE : FALSE;		
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
	 * @param array $data associative array, db keys should be passed without the leading 'media_cat_' e.g. 'class', 'type', etc.
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
	 */
	public function createUserCategory($type='image', $userId = USERID, $userName = USERNAME, $parms=null)
	{
		
		if($type !='image' && $type='file' && $type !='video')
		{
			return false;
		}
				
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
	 * @param array $data
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
			return;	
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
	 */
	public function getCategories($owner='')
	{
		$ret = array();
		
		
		$qry = "SELECT * FROM #core_media_cat ";
		$qry .= ($owner) ? " WHERE media_cat_owner = '".$owner."' " : " (1) ";
		$qry .= "AND media_cat_class IN (".USERCLASS_LIST.") ";
		$qry .= "ORDER BY media_cat_order";
		
		e107::getDb()->gen($qry);
		while($row = e107::getDb()->fetch(MYSQL_ASSOC))
		{
			$id = $row['media_cat_category'];
			$ret[$id] = $row;
		}
		return $ret;	
	}
	
	/**
	 * Return the total number of Images in a particular category
	 * 
	 */	
	public function countImages($cat,$search=null)
	{
		
		return $this->getImages($cat, 0, 'all',$search);
		
		/*
		
		$inc 		= array();
		$searchinc 	= array();
		
		if(strpos($cat,"+") || !$cat)
		{
			$cat = str_replace("+","",$cat);
			$inc[] = "media_category = '_common_image' ";
		}
		if($cat)
		{
			$inc[] = "media_category REGEXP '(^|,)(".$cat.")(,|$)' "; // for multiple category field. 
		}
		
		if($search)
		{
			$searchinc[] = "media_name LIKE '%".$search."%' "; 
			$searchinc[] = "media_description LIKE '%".$search."%' "; 
			$searchinc[] = "media_caption LIKE '%".$search."%' ";
			$searchinc[] = "media_tags LIKE '%".$search."%' ";  
		}
		
		
		$query = "SELECT * FROM #core_media WHERE media_userclass IN (".USERCLASS_LIST.") AND ( ".implode(" OR ",$inc)." )" ;
		
		if($search)
		{
			$query .= " AND ( ".implode(" OR ",$searchinc)." ) " ;	
		}
		
		return e107::getDb()->db_Select_gen($query);	
		*/
	}
	
	
	public function getFiles($from=0, $amount = null, $search = null)
	{
		return $this->getImages('_common_file', $from, $amount, $search);	
	}
	
	
	
	
	/**
	 * Return an array of Images in a particular category
	 * @param string $cat : category name. use + to include _common eg. 'news+'
	 * @param $from
	 * @param $amount
	 * @param $search
	 */
	public function getImages($cat='', $from=0, $amount=null,$search=null)
	{
		$inc 		= array();
		$searchinc 	= array();
		
		if(strpos($cat,"+") || !$cat)
		{
			$cat = str_replace("+","",$cat);
			// $inc[] = "media_category = '_common_image' ";
		//	$inc[] = "media_category REGEXP '(^|,)(_common_image)(,|$)' "; 
		//		$inc[] = "media_category LIKE '%_common_image%' "; 
			$catArray[] = '_common_image';
		}
		if($cat)
		{
			if(strpos($cat, "|") && !strpos($cat,"+") )
			{
				$catArray = explode("|",$cat);	
			}
			else
			{
				$catArray[] = $cat; 
			}
	//		$inc[] = "media_category LIKE '%".$cat."%' "; // for multiple category field. 
		//	$inc[] = "media_category REGEXP '(^|,)(".$cat.")(,|$)' "; // for multiple category field. 
		}
		
		
	//	$inc[] = "media_category REGEXP '(^|,)_common_image|banner_image(,|$)' ";
		
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
		
		$query = "SELECT ".$fields." FROM #core_media WHERE `media_category` REGEXP '(^|,)".implode("|",$catArray)."(,|$)' AND `media_userclass` IN (".USERCLASS_LIST.")  " ;	
	//	$query = "SELECT ".$fields." FROM #core_media WHERE media_userclass IN (".USERCLASS_LIST.") AND ( ".implode(" OR ",$inc)." ) " ;	
			
		if($search)
		{
			$query .= " AND ( ".implode(" OR ",$searchinc)." ) " ;	
		}
		
		$query .= " ORDER BY media_id DESC";

		if($amount == 'all')
		{
			return e107::getDb()->gen($query);		
		}

		
		if($amount)
		{
			$query .= " LIMIT ".$from." ,".$amount;	
		}
		e107::getDb()->gen($query);
		while($row = e107::getDb()->fetch(MYSQL_ASSOC))
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
		
		e107::getDb()->db_Select_gen($query);
		while($row = e107::getDb()->db_Fetch(mySQL_ASSOC))
		{
			$id = $row['media_id'];
			$ret[$id] = $row;
		}
		return $ret;	
	}
	


		
	/**
	 * Generate Simple Thumbnail window for image -selection 
	 */
	private function imageSelect($cat,$formid='imageSel')
	{
		$sql = e107::getDb();
		$tp = e107::getParser();
		
		$text .= "<div style='margin-left:500px;text-align:center; position:relative;z-index:1000;float:left;display:none' id='{$formid}'>";
		$text .="<div style='-moz-box-shadow: 3px 3px 3px #808080;
			-webkit-box-shadow: 3px 3px 3px #808080;
			box-shadow: 3px 3px 3px #808080;
			background-color:black;border:1px solid black;position:absolute; height:200px;width:205px;overflow-y:scroll; bottom:30px; right:100px'>";
		
		$total = ($sql->db_Select_gen("SELECT * FROM `#core_media` WHERE media_category = '_common' OR media_category = '".$cat."' ORDER BY media_category,media_datestamp DESC ")) ? TRUE : FALSE;		
		$text .= "<div style='font-size:120%;font-weight:bold;text-align:right;margin-right:10px'><a title='Close' style='text-decoration:none;color:white' href='#' onclick=\"expandit('{$formid}'); return false;\" >x</a></div>";
			
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



	public function mediaSelect($category='',$tagid=null,$att=null)
	{
	
		if(is_string($att))
		{
			parse_str($att,$option); // grab 'onclick' . 
		}
		else {
			$option = $att;
		}
			
		$frm 		= varset($option['from']) ? $option['from'] : 0;
		$limit 		= varset($option['limit']) ? $option['limit'] : 20;
		$newfrm 	= $frm + $limit; 
		$bbcode		= varset($option['bbcode']) ? $option['bbcode'] : null;
		$navMode	= varset($option['nav']) ? TRUE : FALSE;
		$search		= varset($option['search']) ? $option['search'] : null;
		
		if($category !='_icon')
		{
			$cat 	= ($category) ? $category."+" : ""; // the '+' loads category '_common' as well as the chosen category. 
			$images = $this->getImages($cat,$frm,$limit,$search);
			$class 	= "media-select-image";
			$w		= 120;
			$h		= 100;
			$total	= $this->countImages($cat,$search);
		}
		else // Icons
		{
			$cat 	= "";
			$images = $this->getIcons($cat,0,200);
			$class 	= "media-select-icon";
			$w		= 64;
			$h		= 64;
			$total 	= 500;
			$total	= $this->countImages("_icon_16|_icon_32|_icon_48|_icon_64",$search);
			// $total	= $this->countIcons($cat); //TODO
		}
		
		
		
	//	$total_images 	= $this->getImages($cat); // for use by next/prev in filter at some point. 
	
		$prevAtt		= '&aw='.vartrue($option['w'],$w); // .'&ah=100';	// Image Parsed back to Form as PREVIEW image. 	
		$prevId 		= $tagid."_prev"; // ID of image in Form. 
		$thumbAtt		= 'aw=120&ah=120';	// Thumbnail of the Media-Manager Preview. 	
		
		
		// EXAMPLE of FILTER GUI. 
		$text = "";
		$dipTotal = (($frm + $limit) < $total) ? ($frm + $limit) : $total;

		if($navMode === false)
		{
		//	$data_src = $this->mediaSelectNav($category,$tagid, "bbcode=".$bbcode)."&amp;from=0";
			$data_src = $this->mediaSelectNav($category,$tagid, $option); // ."&amp;from=0";
		
			// Inline style to override jquery-ui stuff. 
			$text .= "<div class='btn-group'>
			<div class='input-append' style='margin-top:10px;font-size:12px'>
			<input type='text' id='media-search' placeholder='Search...' name='search' value='' class='e-tip' data-target='media-select-container' data-src='".$data_src."' />
			";
		//	$text .= "<input type='button' value='Go' class='btn btn-primary e-media-nav' data-target='media-select-container' data-src='".$this->mediaSelectNav($category,"tagid=".$tagid."&bbcode=".$bbcode)."&amp;from=0' /> "; // Manual filter, if onkeyup ajax fails for some reason. 
			$text .= "<button type='button'  class='btn btn-primary e-media-nav' data-target='media-select-container' data-src='".$data_src."' >Go</button>"; // Manual filter, if onkeyup ajax fails for some reason. 
	
			$text .= "<button type='button' title='previous page' class='btn e-nav e-media-nav e-tip'  data-target='media-select-container' data-nav-total='".$total."' data-nav-dir='down' data-nav-inc='".$limit."' data-src='".$data_src."' >&laquo;</button>"; // see next page of images. 
		
			$text .= "<button type='button' title='next page' class='btn e-nav e-media-nav e-tip' style='text-align:center'  data-target='media-select-container' data-nav-total='".$total."' data-nav-dir='up' data-nav-inc='".$limit."' data-src='".$data_src."' >&raquo;</button>"; // see next page of images. 
			$text .= "</div></div>";
		
			$text .= "
			<div id='media-select-container'>";	
		}
		
		$text .= "<div class='media-select-count' style='text-align:right; display:block'> Displaying ".($frm +1)."-".($dipTotal)." of ".$total." images.</div>\n";
		
		
		if($bbcode == null) // e107 Media Manager - new-image mode. 
		{
			$onclick_clear = "parent.document.getElementById('{$tagid}').value = '';
		 	parent.document.getElementById('".$prevId."').src = '".e_IMAGE_ABS."generic/blank.gif';
		 	 return false;";
			
			$text .= "<a class='thumbnail {$class} media-select-none e-dialog-close' data-src='".varset($im['media_url'])."' style='vertical-align:middle;display:block;float:left;' href='#' onclick=\"{$onclick_clear}\" >
			<div style='text-align:center;position: relative; top: 30%'>No image</div>
			</a>";		
		}

		$srch = array("{MEDIA_URL}","{MEDIA_PATH}");
		
		$w	= false;
		$h = false;
			
		if($bbcode) // ie. TinyMce Editor, not imagepicker(); 
		{
			e107::getBB()->setClass($category);
			$w = e107::getBB()->resizeWidth(); // resize the image according to prefs. 
			$h = e107::getBB()->resizeHeight();
			e107::getBB()->clearclass();
				
			
		}
		
//		print_a($option);
		
		$tp = e107::getParser();
	
		/*
            $media_path : Inserted into html tags eg. <img src='here'...
        */

	//	print_a($images);
	//	return;
		
		foreach($images as $im)
		{
			list($dbWidth,$dbHeight) = explode(" x ",$im['media_dimensions']);	
				
			$w = ($dbWidth > 400) ? 400 : intval($dbWidth);		
		//	$w = vartrue($w,0);
       //     $h = vartrue($w,0);		
								
			$class 			= ($category !='_icon') ? "media-select-image" : "media-select-icon";
			$media_path 	= ($w || $h) ? $tp->thumbUrl($im['media_url'], "&w={$w}") : $tp->thumbUrl($im['media_url']); // $tp->replaceConstants($im['media_url'],'full'); // max-size 
				
			$realPath 		= $tp->thumbUrl($im['media_url'], $prevAtt); // Parsed back to Form as Preview Image. 
			
			$diz 			= $tp->toAttribute(varset($im['media_title']))."\n".varset($im['media_dimensions']);		
			$repl 			= array($im['media_url'],$media_path);
			
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
				//TODO Add a preview window 
				$onclicki = "document.getElementById('src').value = '{$im['media_url']}';
				document.getElementById('preview').src = '{$realPath}';
		 		
				return false;";	
				//$onclicki = "";
				$class .= " e-media-select";
				$onclicki = "";
				
			}
			
			$data_bb = ($bbcode) ? "img" : "";
		 	
		 	$img_url = ($cat !='_icon') ? e107::getParser()->thumbUrl($im['media_url'], $thumbAtt) : $media_path;
			
			$text .= "<a data-toggle='context' class='thumbnail {$class} e-tip' data-id='{$im['media_id']}' data-width='{$w}' data-height='{$h}' data-src='{$media_path}' data-bbcode='{$data_bb}' data-target='{$tagid}' data-path='{$im['media_url']}' data-preview='{$realPath}' title=\"".$diz."\" style='float:left' href='#' onclick=\"{$onclicki}\" >";
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



	function checkDupe($oldpath,$newpath)
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
			$row = $sql->fetch();
			$newpath = $row['media_url'];
		}

		return $newpath;	
	}


	/**
	 * Get all Glyphs
	 * @todo Cache 
	 */
	function getGlyphs($type='fa4',$prefix = '')
	{
		$icons = array();
		
		if($type == 'bs2')
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
			    $icons[] = $prefix.$match;
			}
			
			return $icons;
		}
					
		if($type == 'bs3')
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
			    $icons[] = $prefix.$match;
			}
			
			return $icons;
		}
		
		$cache = e107::getCache();
		$cache->setMD5('_'.$prefix.$type);
		
		if($data = $cache->retrieve($type,360,true))
		{
			return e107::unserialize($data);
		}
		
		
		if($type == 'fa4')
		{
			$pattern = '/\.(fa-(?:\w+(?:-)?)+):before/';
			$subject = e107::getFile()->getRemoteContent('http://netdna.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.css');
		//	print_a($subject);
		}
		elseif($type == 'fa3')
		{
			$pattern = '/\.(icon-(?:\w+(?:-)?)+):before/';
			$subject = file_get_contents(e_WEB_JS.'font-awesome/css/font-awesome.css');
		}
				
		preg_match_all($pattern, $subject, $matches, PREG_SET_ORDER);
		
		
		
		foreach($matches as $match)
		{
		    $icons[] = $prefix.substr($match[1],3);
		}
			
		$data = e107::serialize($icons);
		$cache->set($type,$data,true);		
		return $icons; 
	
	}


	
	
	function getPath($mime)
	{
		$mes = e107::getMessage();

		list($pmime,$tmp) = explode('/',$mime);

		if(!vartrue($this->mimePaths[$pmime]))
		{
			$this->log("Couldn't detect mime-type ($mime).");
			$mes->add("Couldn't detect mime-type ($mime). Upload failed.", E_MESSAGE_ERROR);
			return FALSE;
		}

		$dir = $this->mimePaths[$pmime].date("Y-m");

		if(!is_dir($dir))
		{
			if(!mkdir($dir, 0755))
			{
				$this->log("Couldn't create folder ($dir).");
				$mes->add("Couldn't create folder ($dir).", E_MESSAGE_ERROR);
				return FALSE;
			};
		}
		return $dir;
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
	
	
	
	
	
	public function importFile($file='',$category='_common_image', $oldpath = null, $new_data = array())
	{
		$mes = e107::getMessage();
		$tp = e107::getParser();
		$sql = e107::getDb();

        if(!$oldpath) $oldpath = e_IMPORT.$file;
		
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
			return;
		}	
			
		$img_data = $this->mediaData($oldpath); // Basic File Info only
		
		if(!$typePath = $this->getPath($img_data['media_type']))
		{		
				$this->log("Line: ".__LINE__." Couldn't generate path from file info:".$oldpath);
				$mes->addError("Couldn't generate path from file info:".$oldpath);
				return FALSE;
		}
				
		$newpath = $this->checkDupe($oldpath,$typePath.'/'.$file);
		
		if(!rename($oldpath, $newpath)) // e_MEDIA.$newpath was working before. 
		{
			$this->log("Couldn't move file from ".realpath($oldpath)." to ".e_MEDIA.$newpath);
			$mes->add("Couldn't move file from ".$oldpath." to ".$newpath, E_MESSAGE_ERROR);
			return FALSE;
		};
		
		$img_data['media_url']			= $tp->createConstants($newpath,'rel');
		$img_data['media_name'] 		= $tp->toDB($file);
		$img_data['media_caption'] 		= $new_data['media_caption'];
		$img_data['media_category'] 	= vartrue($category,'_common_image');
		$img_data['media_description'] 	= $new_data['media_description'];
		$img_data['media_userclass'] 	= '0';	

		if($sql->insert("core_media",$img_data))
		{		
			$mes->add("Importing Media: ".$file, E_MESSAGE_SUCCESS);
			$this->log("Importing Media: ".$file." successful");
			return $img_data['media_url'];	
		}
		else
		{
			$this->log("Db Insert Failed ");
			rename($newpath,$oldpath);	//move it back.
			return FALSE;
		}
		
		
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
			'thumbUrl'		=> $defaultThumb,
			'title'			=> '',
			'gridClass'		=> 'span2',
			'bbcode'		=> ''
			
		);
		
		$data = array();
		
		foreach($default as $k=>$v)
		{
			$data[$k] = isset($row[$k]) ? $row[$k] : $default[$k];	
		}
		
			
		$close = (E107_DEBUG_LEVEL > 0) ? "" : "  data-close='true' ";	//
	
		// 		
		$text .= "\n\n<!-- Start Item -->\n<div class='media-carousel ".$data['gridClass']."'>
		
			<div class='well clearfix'>

				<a data-toggle='context' class='e-media-select e-tip e-dialog-save e-dialog-close' ".$close." data-id='".$data['id']."' data-width='".$data['width']."' data-height='".$data['height']."' data-src='".$data['previewUrl']."' data-type='".$data['type']."' data-bbcode='".$data['bbcode']."' data-target='".$data['tagid']."' data-path='".$data['saveValue']."' data-preview='".$data['previewUrl']."' title=\"".$data['title']."\" style='float:left' href='#' >";
		
				if($data['type'] == 'image')
				{
					$text .= '<img class="img-responsive" alt="" src="'.$data['thumbUrl'].'" style="width:100%;display:inline-block" />';
				}
				elseif($data['type'] == 'glyph')
				{
					$text .= "\n<span style='margin:7px;display:inline-block;color: inherit'>".$tp->toGlyph($data['thumbUrl'],false)."</span>";	
				}		
				$text .= "\n</a>\n\n";
				
				if($data['type'] == 'image')
				{
					$text .= "\n<div><small class='media-carousel-item-caption'>".$data['title']."</small></div>";
				}
			
			$text .= "</div>
			
			</div>\n<!-- End Item -->\n\n";
		
		return $text;

	}
	
	function browserIndicators($slides=array(),$uniqueID)
	{
	
		if(count($slides)<1)
		{
			return;	
		}
		
		 $indicators = '<ol class="carousel-indicators span2" style="top:-40px">
			<li data-target="#'.$uniqueID.'" data-slide-to="0" class="active"></li>';
				
		foreach($slides as $key=>$v)
		{
			$id = $key + 1;	
			$indicators .= '<li data-target="#'.$uniqueID.'" data-slide-to="'.$id.'"></li>';
		}
		
		$indicators .=	'</ol>';		
						
		return $indicators;
		
	}
	
	
	function browserCarousel($data,$parm=null)
	{
			/* Fix for Bootstrap2 margin-left issue when wrapping */
		e107::css('inline','
				
		.media-carousel { margin-bottom:15px }
		
		.row-fluid .media-carousel.span6:nth-child(2n + 3) { margin-left : 0px; }
		.row-fluid .media-carousel.span4:nth-child(3n + 4) { margin-left : 0px; }
		.row-fluid .media-carousel.span3:nth-child(4n + 5) { margin-left : 0px; }
		.row-fluid .media-carousel.span2:nth-child(6n + 7) { margin-left : 0px; }
		');
			
		$frm = e107::getForm();
		
	//	$text .= print_a($_GET,true);
	
			$data_src = $this->mediaSelectNav($category,$parm['tagid'], $parm);
			$carouselID = 'myCarousel-'.$parm['action'];
		
			//$text = "<form class='form-search' action='".e_SELF."?".e_QUERY."' id='core-plugin-list-form' method='get'>";
					
					
						
			if(!e_AJAX_REQUEST)
			{
				$searchPlaceholder = varset($parm['searchPlaceholder'], LAN_SEARCH);
				
				$text = '<div class="btn-group"><span class="input-append">';
				$text .= "<input type='text' class='e-ajax-keyup e-tip' placeholder= '".$searchPlaceholder."...' title='Enter some text to filter results' name='search' value=''  data-target='media-browser-container-".$parm['action']."' data-src='".$data_src."' />";	
			//	$text .= '<button class="btn btn-primary" name="'.$submitName.'" type="submit">'.LAN_GO.'</button>';
				$text .= '<a class="btn btn-primary" href="#'.$carouselID.'" data-slide="prev">&lsaquo;</a><a class="btn btn-primary" href="#'.$carouselID.'" data-slide="next">&rsaquo;</a>';
				$text .= "</span>";		
				$text .= "</div>";
				$text .= "<div id='media-browser-container-".$parm['action']."' class='form-inline clearfix row-fluid'>";
			}
		
			if(count($data) < 1)
			{
				return "<div class='alert alert-info alert-block text-center'>No Results Found.</div>";	
			}
			
			
		
		//	$text .= $this->search('srch', $srch, 'go', $filterName, $filterArray, $filterVal).$frm->hidden('mode','online');
			
			
				$text .= '<div id="'.$carouselID.'"  class="carousel slide" data-interval="false">';
				$text .= '{INDICATORS}';
				$text .= '<div style="margin-top:10px" class="carousel-inner">';	
			
			
			//	$text .= "<div class='item active'>";
				
				$perPage = vartrue($parm['perPage'],12);
				
				$c=0;
				
				$slides = array();
				
				foreach($data as $key=>$val)
				{
					if($c == 0)
					{
						$active = (count($slides) <1) ? ' active' : '';
						$text .= '
						
						<!-- Start Slide -->
						<div class="item'.$active.'">';	
						
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
						
					if($c == $perPage)
					{
						$text .= '
						</div>
						<!-- End Slide -->
						
						';
						$slides[] = 1;
						$c = 0;
					}
				}
				
			
				
				$text .= ($c != 0) ? "</div>\n<!-- End Slide -->\n" : "";
			
			
				
				$text .= "</div>";
				
				$text .= "\n<!-- End Carousel -->\n<div class='clearfix'>&nbsp;</div>\n\n";
				
			if(!e_AJAX_REQUEST)
			{	
				$text .= "</div></div>";
			}	
			
			$ret = str_replace('{INDICATORS}', $this->browserIndicators($slides,$carouselID), $text);
			
			return $ret;
				
	}

	
}
