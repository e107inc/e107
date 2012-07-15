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
	
	protected $logging = true;
	
	protected $mimePaths = array(
				'text'			=> e_MEDIA_FILE,
				'multipart'		=> e_MEDIA_FILE,
				'application'	=> e_MEDIA_FILE,
				'audio'			=> e_MEDIA_AUDIO,
				'image'			=> e_MEDIA_IMAGE,
				'video'			=> e_MEDIA_VIDEO,
				'other'			=> e_MEDIA_FILE
		);
	
	
	/**
	 * Import files from specified path into media database. 
	 * @param string $cat Category nickname
	 * @param string $epath path to file.
	 * @param string $fmask [optional] filetypes eg. .jpg|.gif
	 * @return e_media
	 */
	public function import($cat='',$epath,$fmask='')
	{
		if(!vartrue($cat)){ return $this;}
		
	
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
	
		if(!count($img_array)){ return $this;}
		
	//	print_a($img_array);
	//	return;
	
		foreach($img_array as $f)
		{
			$fullpath = $tp->createConstants($f['path'].$f['fname'],1);
			// echo "<br />cat=".$cat;
			$insert = array(
				'media_caption'		=> $f['fname'],
				'media_description'	=> '',
				'media_category'	=> $cat,
				'media_datestamp'	=> $f['modified'],
				'media_url'			=> $fullpath,
				'media_userclass'	=> 0,
				'media_name'		=> $f['fname'],
				'media_author'		=> USERID,
				'media_size'		=> $f['fsize'],
				'media_dimensions'	=> $f['img-width']." x ".$f['img-height'],
				'media_usedby'		=> '',
				'media_tags'		=> '',
				'media_type'		=> $f['mime']
			);
	
			if(!$sql->db_Select('core_media','media_url',"media_url = '".$fullpath."' LIMIT 1"))
			{
			
				if($sql->db_Insert("core_media",$insert))
				{
					$mes->addSuccess("Imported Media: ".$f['fname']);
				}
				else
				{
					$mes->addError("Media not imported: ".$f['fname']);
				}
			}
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
			$status = ($sql->db_Delete('core_media',"media_cat = '".$cat."'")) ? TRUE : FALSE;
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
			$status = ($sql->db_Delete('core_media',"media_url LIKE '".$path."%'".$qry)) ? TRUE : FALSE;
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
	
		$status = ($sql->db_Select_gen("SELECT * FROM `#core_media` WHERE `media_url` LIKE '".$path."%' AND media_category REGEXP '_icon_16|_icon_32|_icon_48|_icon_64' ")) ? TRUE : FALSE;		
		while ($row = $sql->db_Fetch())
		{
			$ret[] = $row['media_url'];
		}
		
		return $ret;	
	}

	/**
	 * Create media category.
	 * 'class' data is optional, 'id' key is ignored
	 * 
	 * @param array $data associative array, db keys should be passed without the leading 'media_cat_' e.g. 'class', 'nick', etc.
	 * @return integer last inserted ID or false on error
	 */
	public function createCategory($data)
	{
		foreach ($data as $k => $v) 
		{
			$data['media_cat_'.$k] = $v;
		}
		$data['media_cat_id'] = 0;
		if(!isset($data['media_cat_class']) || '' === $data['media_cat_class']) $data['media_cat_class'] = defset('e_UC_MEMBER', 253);
		return e107::getDb()->db_Insert('core_media_cat', $data);
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
		
		e107::getDb()->db_Select_gen($qry);
		while($row = e107::getDb()->db_Fetch(mySQL_ASSOC))
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
	public function countImages($cat)
	{
		$inc = array();
		
		if(strpos($cat,"+") || !$cat)
		{
			$cat = str_replace("+","",$cat);
			$inc[] = "media_category = '_common_image' ";
		}
		if($cat)
		{
			$inc[] = "media_category REGEXP '(^|,)(".$cat.")(,|$)' "; // for multiple category field. 
		}
		
		
		$query = "SELECT * FROM #core_media WHERE media_userclass IN (".USERCLASS_LIST.") AND ( ".implode(" OR ",$inc)." )" ;
		
		return e107::getDb()->db_Select_gen($query);	
	}
	
	
	/**
	 * Return an array of Images in a particular category
	 * @param string $cat : category name. use + to include _common eg. 'news+'
	 */
	public function getImages($cat='', $from=0, $amount=null)
	{
		$inc = array();
		
		if(strpos($cat,"+") || !$cat)
		{
			$cat = str_replace("+","",$cat);
			// $inc[] = "media_category = '_common_image' ";
			$inc[] = "media_category REGEXP '(^|,)(_common_image)(,|$)' "; 
		}
		if($cat)
		{
			$inc[] = "media_category REGEXP '(^|,)(".$cat.")(,|$)' "; // for multiple category field. 
		}
		// TODO check the category is valid. 

		$ret = array();
		$query = "SELECT * FROM #core_media WHERE media_userclass IN (".USERCLASS_LIST.") AND ( ".implode(" OR ",$inc) ;
		$query .= " ) ORDER BY media_datestamp DESC";
		
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
	 * Generate Simple Thumbnail window for image-selection 
	 * TODO Use Whole-page popup window
	 * TODO Add an upload Tab?. 
	 * TODO Real-time ajax filter by keyword
	 */
	public function imageSelect($cat,$formid='imageSel')
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


	public function mediaSelectNav($category,$att)
	{
		parse_str($att,$option); 
		
		$cat = ($category) ? '&amp;for='.$category : "";
		if(!$label) $label = ' Upload an image or file';
		if($option['tagid']) $cat .= '&amp;tagid='.$option['tagid']; 
		if($option['bbcode']) $cat .= '&amp;bbcode=1'; 
		
		
		
		$cat .= ($option['limit']) ? "&amp;limit=".$option['limit'] : "";
		
		$cat .= ($option['frm']) ? "&amp;frm=".$option['frm'] : "";
		
			
		$url = e_ADMIN_ABS."image.php?mode=main&amp;action=dialog".$cat;
		return $url;	
	}



	public function mediaSelect($category='',$tagid=null,$att=null)
	{
	
		parse_str($att,$option); // grab 'onclick' . 
			
		$frm 		= ($option['from']) ? $option['from'] : 0;
		$limit 		= ($option['limit']) ? $option['limit'] : 20;
		$newfrm 	= $frm + $limit; 
		$bbcode		= ($option['bbcode']) ? $option['bbcode'] : null;

		
		if($category !='_icon')
		{
			$cat 	= ($category) ? $category."+" : ""; // the '+' loads category '_common' as well as the chosen category. 
			$images = $this->getImages($cat,$frm,$limit);
			$class 	= "media-select-image";
			$w		= 120;
			$h		= 100;
			$total	= $this->countImages($cat);
		}
		else // Icons
		{
			$cat 	= "";
			$images = $this->getIcons($cat,0,200);
			$class 	= "media-select-icon";
			$w		= 64;
			$h		= 64;
			$total 	= 500;
			// $total	= $this->countIcons($cat); //TODO
		}
		
		
		
	//	$total_images 	= $this->getImages($cat); // for use by next/prev in filter at some point. 
	
		$att 			= 'aw=120&ah=100';		
		$prevId 		= $tagid."_prev";
		
		// EXAMPLE of FILTER GUI. 
	//	$text .= "CAT=".$cat;
		$dipTotal = (($frm + $limit) < $total) ? ($frm + $limit) : $total;

		$text .= "<div>Filter: <input type='text' name='non-working-filter-example' value='' />";
		$text .= "<input type='button' value='Go' /> "; // Manual filter, if onkeyup ajax fails for some reason. 
	//	$text .= "<input type='button' value='&laquo;' />"; // see previous page of images. 
		$text .= "<a class='button e-nav e-ajax' href='#media-select-container' data-nav-total='".$total."' data-nav-dir='down' data-nav-inc='".$limit."' data-src='".$this->mediaSelectNav($category,"tagid=".$tagid."&bbcode=".$bbcode)."&from=0 #media-select-container' >&laquo;</a>"; // see next page of images. 
		
		$text .= "&nbsp;<a class='button e-nav e-ajax' href='#media-select-container' data-nav-total='".$total."' data-nav-dir='up' data-nav-inc='".$limit."' data-src='".$this->mediaSelectNav($category,"tagid=".$tagid."&bbcode=".$bbcode)."&from=0 #media-select-container' >&raquo;</a>"; // see next page of images. 
		$text .= "</div>
		<div id='media-select-container'>
		<div style='text-align:right; display:block'> Displaying ".($frm +1)."-".($dipTotal)." of ".$total." images.</div>\n";
		
		if($bbcode == null) // e107 Media Manager - new-image mode. 
		{
			$onclick_clear = "parent.document.getElementById('{$tagid}').value = '';
		 	parent.document.getElementById('".$prevId."').src = '".e_IMAGE_ABS."generic/blank.gif';
		 	 return false;";
			
			$text .= "<a class='{$class} media-select-none e-dialog-close' data-src='{$im['media_url']}' style='vertical-align:middle;display:block;float:left;' href='#' onclick=\"{$onclick_clear}\" >
			<div style='text-align:center;position: relative; top: 30%'>No image</div>
			</a>";		
		}

		$srch = array("{MEDIA_URL}","{MEDIA_PATH}");
		
		$w	= false;
		$h = false;
			
		if($bbcode)
		{
			e107::getBB()->setClass($category);
			$w = e107::getBB()->resizeWidth(); // resize the image according to prefs. 
			$h = e107::getBB()->resizeHeight();
			e107::getBB()->clearclass();	
		}
		
		
		
		$tp = e107::getParser();
	//	e107::getParser()
		
		foreach($images as $im)
		{
			$class 			= ($category !='_icon') ? "media-select-image" : "media-select-icon";
			$media_path 	= ($w || $h) ? $tp->thumbUrl($im['media_url'], "w={$w}&h={$h}") : $tp->replaceConstants($im['media_url'],'full'); // max-size 
				
			$realPath 		= $tp->thumbUrl($im['media_url'], $att);
			$diz 			= $tp->toAttribute($im['media_title'])."\n".$im['media_dimensions'];		
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
		 	
		 	$img_url = ($cat !='_icon') ? e107::getParser()->thumbUrl($im['media_url'], $att) : $media_path;
			
			$text .= "<a class='{$class} e-tip' data-id='{$im['media_id']}' data-src='{$media_path}' data-bbcode='{$data_bb}' data-target='{$tagid}' data-path='{$im['media_url']}' data-preview='{$realPath}' title=\"".$diz."\" style='float:left' href='#' onclick=\"{$onclicki}\" >";
			$text .= "<img src='".$img_url."' alt=\"".$im['media_title']."\" title=\"{$diz}\" />";
			$text .= "</a>\n\n";
		}	
		
		
		$text .= "<div style='clear:both'><!-- --></div>";
		$mes = e107::getMessage();
		$mes->addDebug("Target: {$tagid}");
		$text .= "</div>";
				
		return $text;	
	}



	function checkDupe($oldpath,$newpath)
	{
		$mes = e107::getMessage();	
		$tp = e107::getParser();
		$f = e107::getFile()->get_file_info($oldpath,TRUE);
		
	//	$mes->addDebug("checkDupe(): newpath=".$newpath."<br />oldpath=".$oldpath."<br />".print_r($upload,TRUE));
		if(file_exists($newpath) || e107::getDb()->db_Select("core_media","*","media_url = '".$tp->createConstants($newpath,'rel')."' LIMIT 1") )
		{
			$this->log($newpath." already exists and will be renamed during import.");
			$mes->addWarning($newpath." already exists and was renamed during import.");	
			$file = $f['pathinfo']['filename']."_.".$f['pathinfo']['extension'];
			$newpath = $this->getPath($f['mime']).'/'.$file;						
		}
		
		return $newpath;	
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
	
	
	
	public function mediaData($sc_path)
	{
		if(!$sc_path) return array();
		
		$mes = e107::getMessage();
		$path = e107::getParser()->replaceConstants($sc_path);
		
		if(!is_readable($path))
		{
			$mes->addError("Couldn't read file: {$path}");	
			return FALSE;
		}
		
		$info = e107::getFile()->get_file_info($path);
		
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
	
	
	
	
	public function log($message)
	{
		if($this->logging == false) return; 
		$insert = "\n".$message;
		file_put_contents(e_LOG."mediaUpload.log",$insert,FILE_APPEND | LOCK_EX);	
	}
	
	
	
	
	
	public function importFile($file='',$category='_common_image')
	{
		$mes = e107::getMessage();
		$tp = e107::getParser();
		$sql = e107::getDb();
				
		$oldpath = e_UPLOAD.$file;
		
		if(!file_exists($oldpath))
		{
			$this->log("Couldn't find the file: ".$oldpath);
			$mes->add("Couldn't find the file: ".$oldpath, E_MESSAGE_ERROR);
			return;
		}	
			
		$img_data = $this->mediaData($oldpath); // Basic File Info only
		
		if(!$typePath = $this->getPath($img_data['media_type']))
		{
			
				$this->log("Couldn't generated path from file info:".$oldpath);
				$mes->addError("Couldn't generated path from file info:".$oldpath);
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
		$img_data['media_category'] 	= $category;
		$img_data['media_description'] 	= $new_data['media_description'];
		$img_data['media_userclass'] 	= '0';	

		if($sql->db_Insert("core_media",$img_data))
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
	
	

	
}
