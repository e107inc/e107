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
$import_class_comment['rss_import'] 	= '(work in progress)';
$import_class_support['rss_import'] 	= array('news','page','links');
$import_default_prefix['rss_import'] 	= '';

require_once('import_classes.php');

class rss_import extends base_import_class
{
  // Set up a query for the specified task.
  // Returns TRUE on success. FALSE on error
	function setupQuery($task, $blank_user=FALSE)
	{
		
		$xml = e107::getXml();
		require_once(e_HANDLER."magpie_rss.php");
		
	//	$file = "http://www.e107.org/releases.php"; //pluginfeed.php or similar. 
	//	$file = "http://localhost:8080/e107_0.8/e107_plugins/release/release.php"; // temporary testing
		$file = "http://raelianews.org/rss";
		
		$xml->setOptArrayTags('plugin'); // make sure 'plugin' tag always returns an array
		
			
    	switch ($task)
		{
	  		case 'users' :
	  			//$query =  "SELECT * FROM {$this->DBPrefix}users WHERE `user_id` != 1";
		
			break;
		
			case 'news' :
			//	$result = $xml->loadXMLfile($file,true);
			//	$rawData = $xml->getRemoteFile($file);
			//	$rss = new MagpieRSS( $rawData );
			//	$array = $xml->xml2array($rss);
				
				$xml->setOptArrayTags('item'); // make sure 'plugin' tag always returns an array
				$array = $xml->loadXMLfile($file,'advanced');
				$this->arrayData = $array['channel']['item'];	
				
				if ($result === FALSE) return FALSE;
			break;
			
			case 'page' :
				
				if ($result === FALSE) return FALSE;
			break;
			
			case 'media' :

			break;
				
			case 'links':
		
				if ($result === FALSE) return FALSE;
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
		/*	Example: 
			[ID] => 88
		    [post_author] => 1
		    [post_date] => 2012-01-25 04:11:22
		    [post_date_gmt] => 2012-01-25 09:11:22
		    [post_content] => [gallery itemtag="div" icontag="span" captiontag="p" link="file"]
		    [post_title] => Media Gallery
		    [post_excerpt] => 
		    [post_status] => inherit
		    [comment_status] => open
		    [ping_status] => open
		    [post_password] => 
		    [post_name] => 10-revision-6
		    [to_ping] => 
		    [pinged] => 
		    [post_modified] => 2012-01-25 04:11:22
		    [post_modified_gmt] => 2012-01-25 09:11:22
		    [post_content_filtered] => 
		    [post_parent] => 10
		    [guid] => http://siteurl.com/2012/01/25/10-revision-6/
		    [menu_order] => 0
		    [post_type] => post
		    [post_mime_type] => 
		    [comment_count] => 0
		 */	
	
	//		$target['news_id']					= $source['ID'];
			$target['news_title']				= $source['post_title'];
			$target['news_sef']					= $source['post_name'];
			$target['news_body']				= $source['post_content'];
		//	$target['news_extended']			= '';
		//	$target['news_meta_keywords']		= '';
		//	$target['news_meta_description']	= '';
			$target['news_datestamp']			= strtotime($source['post_date']);
			$target['news_author']				= $source['post_author'];
		//	$target['news_category']			= '';
			$target['news_allow_comments']		= ($source['comment_status']=='open') ? 1 : 0;
			$target['news_start']				= '';
			$target['news_end']					= '';
			$target['news_class']				= '';
			$target['news_render_type']			= '';
			$target['news_comment_total']		= $source['comment_count'];
			$target['news_summary']				= $source['post_excerpt'];
			$target['news_thumbnail']			= '';
			$target['news_sticky']				= '';

	//	return $target;  // comment out to debug 
		
		// DEBUG INFO BELOW. 		
		$this->renderDebug($source,$target);	
	}




	/**
	 * Align source data to e107 Page Table 
	 * @param $target array - default e107 target values for e107_page table. 
	 * @param $source array - WordPress table data
	 */
	function copyPageData(&$target, &$source)
	{
		$tp = e107::getParser();
		/*	post_status: 
				publish - A published post or page
				inherit - a revision
				pending - post is pending review
				private - a private post
				future - a post to publish in the future
				draft - a post in draft status
				trash - post is in trashbin (available with 2.9)
		*/
		
		if($source['post_status']=='private' || $source['post_status']=='future' || $source['post_status'] == 'draft')
		{
			$target['page_class']	 = e_UC_ADMIN;	
		}
		
	// 	$target['page_id']				= $source['ID']; //  auto increment
		$target['page_title']			= $source['post_title'];
		$target['page_sef']				= $source['post_name'];
		$target['page_text']			= "[html]".$source['post_content']."[/html]";
		$target['page_metakeys']		= '';
		$target['page_metadscr']		= '';
		$target['page_datestamp']		= strtotime($source['post_date']);
		$target['page_author']			= $source['post_author'];
	//	$target['page_category']		= '',
		$target['page_comment_flag']	= ($source['comment_status']=='open') ? 1 : 0;
		$target['page_password']		= $source['post_password'];
		
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
		/*		WP
		 		link_id
				link_url
				link_name
				link_image
				link_target
				link_description
				link_visible
				link_owner
				link_rating
				link_updated
				link_rel
				link_notes
				link_rss
		 * 
		 * 	e107
		 *	link_id
			link_name
			link_url
			link_description
			link_button
			link_category
			link_order
			link_parent
			link_open
			link_class
			link_function
			link_sefurl
			 */	
			 
			 		
	// 	$target['page_id']				= $source['ID']; //  auto increment
		$target['link_name']			= $source['post_title'];
		$target['link_url']				= $source['post_name'];
		$target['link_description']		= "[html]".$source['post_content']."[/html]";
		$target['link_button']			= '';
		$target['link_category']		= '';
		$target['link_order']			= strtotime($source['post_date']);
		$target['link_parent']			= $source['post_author'];
		$target['link_open']			= '';
		$target['link_class']			= '';
		$target['link_sefurl']			= $source['post_password'];
		
	//	return $target;  // comment out to debug 
		
		// DEBUG INFO BELOW. 
		$this->renderDebug($source,$target);
		
	}
	


	

	
	
	
	
	
	function renderDebug($source,$target)
	{		
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