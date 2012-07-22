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

$import_class_names['rss_import'] 	= 'RSS';
$import_class_comment['rss_import'] 	= 'Import RSS v2.0 feeds';
$import_class_support['rss_import'] 	= array('news','page','links');
$import_default_prefix['rss_import'] 	= '';

require_once('import_classes.php');

class rss_import extends base_import_class
{
  // Set up a query for the specified task.
  // Returns TRUE on success. FALSE on error
	function setupQuery($task, $blank_user=FALSE)
	{
		$this->arrayData = array();
		
		$xml = e107::getXml();	
		$file = RSS_IMPORT;
					
    	switch ($task)
		{		
			case 'news' :
			case 'page' :
			case 'links' :
				$array = $xml->loadXMLfile($file,'advanced');
				if ($array === FALSE || RSS_IMPORT === FALSE) return FALSE;
				
				foreach($array['channel']['item'] as $val)
				{
					$this->arrayData[] = $val;
				}

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
	 * Align source data to e107 News Table 
	 * @param $target array - default e107 target values for e107_news table. 
	 * @param $source array - WordPress table data
	 */
	function copyNewsData(&$target, &$source)
	{
			$target['news_title']				= $source['title'][0];
		//	$target['news_sef']					= $source['post_name'];
			$target['news_body']				= "[html]".$source['description'][0]."[/html]";
		//	$target['news_extended']			= '';
		//	$target['news_meta_keywords']		= '';
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
		
		// DEBUG INFO BELOW. 		
		
	}




	/**
	 * Align source data to e107 Page Table 
	 * @param $target array - default e107 target values for e107_page table. 
	 * @param $source array - WordPress table data
	 */
	function copyPageData(&$target, &$source)
	{
		
	// 	$target['page_id']				= $source['ID']; //  auto increment
		$target['page_title']			= $source['title'][0];
	//	$target['page_sef']				= $source['post_name'];
		$target['page_text']			= "[html]".$source['description'][0]."[/html]";
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