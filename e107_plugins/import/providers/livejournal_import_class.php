<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2012 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/import/wordpress_import_class.php,v $
 * $Revision: 11315 $
 * $Date: 2010-02-10 10:18:01 -0800 (Wed, 10 Feb 2010) $
 * $Author: secretr $
 */

// require_once('import_classes.php');
require_once('rss_import_class.php');

//$import_class_names['livejournal_import'] 		= 'LiveJournal';
//$import_class_comment['livejournal_import'] 	= 'Import up to 500 items from yourblog.livejournal.com';
//$import_class_support['livejournal_import'] 	= array('news');
//$import_default_prefix['livejournal_import'] 	= '';

class livejournal_import extends rss_import
{

	
	public $title		= 'LiveJournal';
	public $description	= 'Import up to 500 items from yourblog.livejournal.com';
	public $supported	= array('news');
	public $mprefix		= false;


	var $cleanupHtml 	= false;
	var $defaultClass 	= false;
	/*

	 */
	function init()
	{
		$mes = e107::getMessage();
	
		if(vartrue($_POST['siteUrl']))
		{
			$domain = preg_replace("/https?:\/\//i",'',$_POST['siteUrl']);
			list($site,$dom,$tld) = explode(".",$domain);
									
			$this->feedUrl = "http://".$site.".livejournal.com/data/rss";	
		}
		
		if(vartrue($_POST['siteCleanup']))
		{
			$this->cleanupHtml = true;
		}	
		
		$mes->addDebug("LiveJournal Feed:".$this->feedUrl);
	}
		
	
	function config()
	{
		$var[0]['caption']	= "Your LiveJournal URL";
		$var[0]['html'] 	= "<input class='tbox' type='text' name='siteUrl' size='80' value='{$_POST['bloggerUrl']}' maxlength='250' />";
		$var[0]['help']		= "eg. http://blogname.livejournal.com";
		
		$var[1]['caption']	= "Cleanup HTML in content";
		$var[1]['html'] 	= "<input class='tbox' type='checkbox' name='siteCleanup' size='80' value='1' />";
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
}


