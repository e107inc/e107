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

$import_class_names['rss_import'] 		= 'RSS';
$import_class_comment['rss_import'] 	= 'Import RSS v2.0 feeds';
$import_class_support['rss_import'] 	= array('news','page','links');
$import_default_prefix['rss_import'] 	= '';

require_once('import_classes.php');

class rss_import extends base_import_class
{
	var $sourceType = 'rss';
	var $feedUrl	= null;
	
	function init()
	{
		$this->feedUrl	= vartrue($_POST['rss_feed'],false);
	}
	
	function config()
	{
		$var[0]['caption']	= "Feed URL";
		$var[0]['html'] 	= "<input class='tbox' type='text' name='rss_feed' size='80' value='{$_POST['rss_feed']}' maxlength='250' />";

		return $var;
	}
	

  // Set up a query for the specified task.
  // Returns TRUE on success. FALSE on error
	function setupQuery($task, $blank_user=FALSE)
	{
		$this->arrayData = array();
		
		$xml = e107::getXml();	
		$file = $this->feedUrl;
					
    	switch ($task)
		{		
			case 'news' :
			case 'page' :
			case 'links' :
				
				// $rawData = $xml->getRemoteFile($file);
				//	print_a($rawData);
				$array = $xml->loadXMLfile($file,'advanced');
				if ($array === FALSE || $file === FALSE) return FALSE;
				
				foreach($array['channel']['item'] as $val)
				{
					$this->arrayData[] = $val;
				}
				
				$this->arrayData = array_reverse($this->arrayData); // most recent last. 
				reset($this->arrayData);
				
			break;

			default :
			return FALSE;
		}

		$this->copyUserInfo = !$blank_user;
		$this->currentTask = $task;
		return TRUE;
  }


  //------------------------------------
  //	Internal functions below here
  //------------------------------------
  
	/**
	 * Align source data to e107 User Table 
	 * @param $target array - default e107 target values for e107_user table. 
	 * @param $source array - WordPress table data
	 */
	function copyUserData(&$target, &$source)
	{

	}

	/**
	 * Align source data with e107 News Table 
	 * @param $target array - default e107 target values for e107_news table. 
	 * @param $source array - WordPress table data
	 */
	function copyNewsData(&$target, &$source)
	{
		
		if(!$content = $this->process('content_encoded',$source))
		{
			$body = $this->process('description',$source);	
		}
		else
		{
			$body = $content;
		}
				
		$body 								= $this->saveImages($body,'news');
		$keywords 							= $this->process('category',$source);
			
				
		if(!vartrue($source['title'][0]))
		{
			list($title,$newbody) = explode("<br />",$body,2);
			$title = strip_tags($title);
			if(trim($newbody)!='')
			{
				$body = $newbody;	
			}	
		}
		else 
		{
			$title = $source['title'][0];
		}
		
		$target['news_title']					= $title;
		//	$target['news_sef']					= $source['post_name'];
		$target['news_body']					= "[html]".$body."[/html]";
		//	$target['news_extended']			= '';
		$target['news_meta_keywords']			= implode(",",$keywords);
		//	$target['news_meta_description']	= '';
			$target['news_datestamp']			= strtotime($source['pubDate'][0]);
		//	$target['news_author']				= $source['post_author'];
		//	$target['news_category']			= '';
		//	$target['news_allow_comments']		= ($source['comment_status']=='open') ? 1 : 0;
		//	$target['news_start']				= '';
		//	$target['news_end']					= '';
		///	$target['news_class']				= '';
		//	$target['news_render_type']			= '';
		//	$target['news_comment_total']		= $source['comment_count'];
		//	$target['news_summary']				= $source['post_excerpt'];
		//	$target['news_thumbnail']			= '';
		//	$target['news_sticky']				= '';

		
		
		return $target;  // comment out to debug 
		
		$this->renderDebug($source,$target);
		
		// DEBUG INFO BELOW. 		
		
	}


	function process($type='description',$source)
	{
		switch ($type) 
		{			
			case 'category':
				$keywords = array();
				if(is_array(varset($source['category'][0])))
				{
					foreach($source['category'] as $val)
					{
						if(varset($val['@value']))
						{
							$keywords[] = $val['@value'];
						}
					}
					return $keywords;
				}
				elseif(is_array(varset($source['category'])))
				{
					foreach($source['category'] as $val)
					{
						if(varset($val) && is_string($val))
						{
							$keywords[] = $val;
						}
					}
					return $keywords;		
				}
			break;
			
			default:
				return varset($source[$type][0]);
			break;
		}	
	}

	/**
	 * Align source data to e107 Page Table 
	 * @param $target array - default e107 target values for e107_page table. 
	 * @param $source array - WordPress table data
	 */
	function copyPageData(&$target, &$source)
	{
		$body 							= $this->saveImages($source['description'][0],'page');
	// 	$target['page_id']				= $source['ID']; //  auto increment
		$target['page_title']			= $source['title'][0];
	//	$target['page_sef']				= $source['post_name'];
		$target['page_text']			= "[html]".$body."[/html]";
	//	$target['page_metakeys']		= '';
	//	$target['page_metadscr']		= '';
		$target['page_datestamp']		= strtotime($source['pubDate'][0]);
	//	$target['page_author']			= $source['post_author'];
	//	$target['page_category']		= '',
	//	$target['page_comment_flag']	= ($source['comment_status']=='open') ? 1 : 0;
	//	$target['page_password']		= $source['post_password'];
		
		return $target;  // comment out to debug 
		
		// DEBUG INFO BELOW. 
		$this->renderDebug($source,$target);
		
	}
	

	/**
	 * Align source data to e107 Links Table 
	 * @param $target array - default e107 target values for e107_links table. 
	 * @param $source array - WordPress table data
	 */
	function copyLinksData(&$target, &$source)
	{
		$tp = e107::getParser();
			 		
	// 	$target['page_id']				= $source['ID']; //  auto increment
		$target['link_name']			= $source['title'][0];
		$target['link_url']				= $source['link'][0];
	//	$target['link_description']		= "[html]".$source['post_content']."[/html]";
	//	$target['link_button']			= '';
	//	$target['link_category']		= '';
	//	$target['link_order']			= strtotime($source['post_date']);
	//	$target['link_parent']			= $source['post_author'];
	//	$target['link_open']			= '';
	//	$target['link_class']			= '';
	//	$target['link_sefurl']			= $source['post_password'];
		
		
		
		return $target;  // comment out to debug 
			
		$this->renderDebug($source,$target);
		
	}
	
	
	/** Download and Import remote images and update body text with local relative-links. eg. {e_MEDIA}
	 * @param returns text-body with remote links replaced with local ones for the images downloaded. 
	 */
	function saveImages($body,$cat='news')
	{
		$mes = e107::getMessage();
		$med = e107::getMedia();
		$tp = e107::getParser();
		$search = array();
		$replace = array();
		
		
	//	echo htmlentities($body);
		preg_match_all("/(((http:\/\/www)|(http:\/\/)|(www))[-a-zA-Z0-9@:%_\+.~#?&\/\/=]+)\.(jpg|jpeg|gif|png|svg)/im",$body,$matches);
		$fl = e107::getFile();
			
		if(is_array($matches[0]))
		{
			$relPath = 'images/'.md5($this->feedUrl);
			
			if(!is_dir(e_MEDIA.$relPath))
			{
				mkdir(e_MEDIA.$relPath,'0755');	
			}
			
			foreach($matches[0] as $link)
			{
				if(file_exists($relPath."/".$filename))
				{
					continue;
				}
				
				$filename = basename($link);
				$fl->getRemoteFile($link,$relPath."/".$filename);
				$search[] = $link;
				$replace[] = $tp->createConstants(e_MEDIA.$relPath."/".$filename,1);
			}	
		}
		
		if(count($search))
		{
			$med->import($cat,e_MEDIA.$relPath);	
		}
		
		return str_replace($search,$replace,$body);
		
	}
	

	
	
	
	
	
	function renderDebug($source,$target)
	{
		
	//	echo print_a($target);
	//	return;
				
		echo "
		<div style='width:1000px'>
			<table style='width:100%'>
				<tr>
					<td style='width:500px;padding:10px'>".print_a($source,TRUE)."</td>
					<td style='border-left:1px solid black;padding:10px'>".print_a($target,TRUE)."</td>
				</tr>
			</table>
		</div>";
	}

}


?>