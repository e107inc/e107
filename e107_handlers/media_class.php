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
	public $imagelist = array();
	
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
		
		//print_a($img_array);
		//return;
	
		foreach($img_array as $f)
		{
			$fullpath = $tp->createConstants($f['path'].$f['fname'],1);
			// echo "<br />cat=".$cat;
			$insert = array(
				'media_caption'		=> $f['fname'],
				'media_description'	=> '',
				'media_category'	=> $cat,
				'media_datestamp'	=> $f['modified'],
				'media_url'	=> $fullpath,
				'media_userclass'	=> 0,
				'media_name'	=> $f['fname'],
				'media_author'	=> USERID,
				'media_size'	=> $f['fsize'],
				'media_dimensions'	=> $f['img-width']." x ".$f['img-height'],
				'media_usedby'	=> '',
				'media_tags'	=> '',
				'media_type'	=> $f['mime']
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
		$qry .= ($owner) ? " WHERE media_cat_owner = '".$owner."' " : "";
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
		$query = "SELECT media_id FROM #core_media WHERE media_category = '".$cat."' ";
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
			$inc[] = "media_category = '_common' ";
		}
		if($cat)
		{
			$inc[] = "media_category = '".$cat."' ";
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


	public function mediaSelect($cat='',$tagid=null,$att=null)
	{
		
		$cat = ($cat) ? $cat."+" : "";
		$images = $this->getImages($cat);
		$att = 'aw=120&ah=100';
		
		$name = $tagid;
		$prevId = $name."_prev";
		
		$onclick_clear = "onclick =\"
		 	parent.document.getElementById('{$tagid}').value = '';
		 	parent.document.getElementById('".$prevId."').src = '".e_IMAGE_ABS."generic/blank.gif';
		 	parent.e107Widgets.DialogManagerDefault.getWindow('e-dialog').close();
		 	 return false; \"";
	
		$text .= "<a class='media-select-clear' style='float:left' href='#' {$onclick_clear} >
		<div style='display:block;border:1px solid silver;padding-top:40px;text-align:center;vertical-align:middle;width:120px;height:60px'>
		No Image</div>";
		
		
		foreach($images as $im)
		{
			$realPath = e107::getParser()->thumbUrl($im['media_url'], $att);
			$diz = e107::getParser()->toAttribute($im['media_title']);

		 	$onclick = "onclick =\"
		 	parent.document.getElementById('{$tagid}').value = '{$im['media_url']}';
		 	parent.document.getElementById('".$prevId."').src = '{$realPath}';
		 	parent.e107Widgets.DialogManagerDefault.getWindow('e-dialog').close();
		 	 return false; \"";

			//FIXME Make Window Close automatically when selection is made. 
			
			$text .= "<a class='media-select' title=\"".$diz."\" href='#' {$onclick} >";
			$text .= "<img src='".e107::getParser()->thumbUrl($im['media_url'], $att)."' alt=\"".$im['media_title']."\"  />";
			$text .= "</a>";
		}	
				
		return $text;	
	}

	
}
