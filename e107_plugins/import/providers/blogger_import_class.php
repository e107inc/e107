<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/import/wordpress_import_class.php,v $
 * $Revision: 11315 $
 * $Date: 2010-02-10 10:18:01 -0800 (Wed, 10 Feb 2010) $
 * $Author: secretr $
 */

// This must be an incredibly pointless file! But it does allow testing of the basic plugin structure.

// Each import file has an identifier which must be the same for:
//		a) This file name - add '_class.php' to get the file name
//		b) The array index of certain variables
// Array element key defines the function prefix and the class name; value is displayed in drop-down selection box



// require_once('import_classes.php');
require_once('rss_import_class.php');

//$import_class_names['blogger_import'] 		= 'Blogger';
//$import_class_comment['blogger_import'] 	= 'Import up to 500 items from yourblog.blogspot.com';
//$import_class_support['blogger_import'] 	= array('news');
//$import_default_prefix['blogger_import'] 	= '';

class blogger_import extends rss_import
{	
	public $title		= "Blogger";
	public $description	= 'Import up to 500 items from yourblog.blogspot.com';
	public $supported	=  array('news');
	public $mprefix		= false;
	
	public $cleanupHtml = false;
	public $defaultClass = false;
	
	
	
	
	/*
	 If the first 500 posts of your blog feed are here:

    http://YOURBLOG.blogspot.com/feeds/posts/default?max-results=999

	the second 500 posts are here:

    http://YOURBLOG.blogspot.com/feeds/posts/default?max-results=999&start-index=501
	 */
	function init()
	{
		$mes = e107::getMessage();
	
		if(vartrue($_POST['bloggerUrl']))
		{			
			$this->feedUrl = rtrim($_POST['bloggerUrl'],"/")."/feeds/posts/default?max-results=999&alt=rss";	
		}
		
		if(vartrue($_POST['bloggerCleanup']))
		{
			$this->cleanupHtml = true;
		}	
		
		$mes->addDebug("Blogger Feed:".$this->feedUrl);
	}
		
	
	function config()
	{
		$var[0]['caption']	= "Blogger URL";
		$var[0]['html'] 	= "<input class='tbox' type='text' name='bloggerUrl' size='80' value='{$_POST['bloggerUrl']}' maxlength='250' />";
		$var[0]['help']		= "eg. http://blogname.blogspot.com";
		
		$var[1]['caption']	= "Cleanup HTML in content";
		$var[1]['html'] 	= "<input class='tbox' type='checkbox' name='bloggerCleanup' size='80' value='1' />";
		$var[1]['help']		= "Tick to enable";
		
		return $var;
	}

	function process($type,$source)
	{
				
		switch ($type) 
		{
			case 'description':
				$body = $source[$type][0];
				if($this->cleanupHtml == TRUE)
				{
					$body = preg_replace("/font-family: [\w]*;/i","", $body);
					$body = preg_replace('/class="[\w]*" /i',"", $body);
					$body = str_replace("<br>","<br />",$body);
					return $body;
				}
				else 
				{
					return $body;
				}		
			break;
			
			default:
				return $source[$type][0];
			break;
		}		
		
		
	}

	//TODO Comment Import: 
	//http://blogname.blogspot.com/feeds/comments/default?alt=rss
	
}


?>