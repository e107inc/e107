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
 * $Revision$
 * $Date$
 * $Author$
 */

// This must be an incredibly pointless file! But it does allow testing of the basic plugin structure.

// Each import file has an identifier which must be the same for:
//		a) This file name - add '_class.php' to get the file name
//		b) The array index of certain variables
// Array element key defines the function prefix and the class name; value is displayed in drop-down selection box

//$import_class_names['wordpress_import'] 	= 'Wordpress';
//$import_class_comment['wordpress_import'] 	= 'Tested with version 3.4.x (salted passwords)';
//$import_class_support['wordpress_import'] 	= array('users','news','page','links');
//$import_default_prefix['wordpress_import'] 	= 'wp_';

require_once('import_classes.php');

class wordpress_import extends base_import_class
{
	public $title		= 'Wordpress 3.4+';
	public $description	= 'Import Users, News, Content and Links';
	public $supported	= array('users','news','page','links');
	public $mprefix		= 'wp_';	
	
	function init()
	{
		
		
		$this->newsAuthor	= intval($_POST['news_author']);
		
	//	if($data = e107::getDb('phpbb')->retrieve('userclass_classes','userclass_id',"userclass_name='FORUM_MODERATOR' "))
	//	{
	//		$this->forum_moderator_class = $data;
	//	}
		
	} 	
	
	function config()
	{
		$sql = e107::getDb();
		
		$sql->select('user','user_id, user_name','user_admin = 1');
		
		$adminList = array();
		
		$adminList[0] = "Default";
		
		while($row = $sql->fetch())
		{
			$id = $row['user_id'];
			$adminList[$id] = $row['user_name'];	
		}
		$frm = e107::getForm();
		
		$var[0]['caption']	= "News Author Override (optional)";
		$var[0]['html'] 	= $frm->select('news_author',$adminList);
		$var[0]['help'] 	= "Change the author of the news items";
		
	//	$var[1]['caption']	= "Include revisions";
	//	$var[1]['html'] 	= $frm->checkbox('news_revisions',1);
	//	$var[1]['help'] 	= "Change the author of the news items";

		return $var;
	}	
			

	
  // Set up a query for the specified task.
  // Returns TRUE on success. FALSE on error
	function setupQuery($task, $blank_user=FALSE)
	{
    	if ($this->ourDB == NULL) return FALSE;
		
    	switch ($task)
		{
	  		case 'users' :
	  			//$query =  "SELECT * FROM {$this->DBPrefix}users WHERE `user_id` != 1";

	        	$query = "SELECT u.*,
				  	w.meta_value AS admin,
				   	f.meta_value as firstname,
					l.meta_value as lastname
				   	FROM {$this->DBPrefix}users AS u
				  	LEFT JOIN {$this->DBPrefix}usermeta AS w ON (u.ID = w.user_id AND w.meta_key = 'wp_capabilities')
					LEFT JOIN {$this->DBPrefix}usermeta AS f ON (u.ID = f.user_id AND f.meta_key = 'first_name')
					LEFT JOIN {$this->DBPrefix}usermeta AS l ON (u.ID = l.user_id AND l.meta_key = 'last_name')
					GROUP BY u.ID";

			//	$this->ourDB -> gen($query);

				$result = $this->ourDB->gen($query);

				if ($result === FALSE) return FALSE;
			
			break;

			case 'userclass' :
			  /*       For reference: (stored in usermeta -> wp_capabilities
			  		    *  Administrator - Somebody who has access to all the administration features
			    		* Editor - Somebody who can publish posts, manage posts as well as manage other people's posts, etc.
			    		* Author - Somebody who can publish and manage their own posts
			    		* Contributor - Somebody who can write and manage their posts but not publish posts
			    		* Subscriber - Somebody who can read comments/comment/receive news letters, etc.
			  */

			break;
		
			case 'news' :
				$query =  "SELECT * FROM {$this->DBPrefix}posts WHERE (post_type = 'post') AND post_status !='trash' AND post_status != 'auto-draft' ORDER BY ID";
				$result = $this->ourDB->gen($query);
				if ($result === FALSE) return FALSE;
			break;
			
			case 'page' :
				$query =  "SELECT * FROM {$this->DBPrefix}posts WHERE post_type = 'page' AND post_status !='trash' ORDER BY ID";
				$result = $this->ourDB->gen($query);
				if ($result === FALSE) return FALSE;
			break;
			
			case 'media' :
				$query =  "SELECT * FROM {$this->DBPrefix}posts WHERE post_type = 'attachment' AND post_status !='trash' ORDER BY ID";
				$result = $this->ourDB->gen($query);
				if ($result === FALSE) return FALSE;
			break;
				
			case 'links':
			 	$query =  "SELECT * FROM {$this->DBPrefix}links WHERE link_id !='' ORDER BY link_id";
				$result = $this->ourDB->gen($query);
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
  		$user_meta = unserialize($source['admin']);

		if ($this->copyUserInfo)
		{
			 $target['user_id'] = $source['ID'];
		}
		
		$target['user_name'] 		= $source['user_nicename'];
		$target['user_loginname'] 	= $source['user_login'];
		$target['user_password'] 	= $source['user_pass'];   // needs to be salted!!!!
		$target['user_email'] 		= $source['user_email'];
		$target['user_hideemail'] 	= $source['user_hideemail'];
		$target['user_join'] 		= strtotime($source['user_registered']);
		$target['user_admin'] 		= ($user_meta['administrator'] == 1) ? 1 : 0;
		$target['user_lastvisit'] 	= $source['user_lastvisit'];
		$target['user_login'] 		= $source['firstname']." ".$source['lastname'];
		$target['user_ban'] 		= $source['user_ban'];
		$target['user_customtitle'] = $source['display_name'];
		$target['user_sess'] 		= $source['user_sess'];			// Photo
		$target['user_signature'] 	= $source['user_signature'];
		$target['user_image'] 		= $source['user_image'];			// Avatar
		$target['user_currentvisit'] = $source['user_currentvisit'];
		$target['user_lastpost'] 	= $source['user_lastpost'];
		$target['user_chats'] 		= $source['user_chats'];
		$target['user_comments'] 	= $source['user_comments'];
	
		$target['user_ip'] 			= $source['user_ip'];
		$target['user_prefs'] 		= $source['user_prefs'];
		$target['user_visits'] 		= $source['user_visits'];
		$target['user_class'] 		= $source['user_class'];
		$target['user_perms'] 		= $source['user_perms'];
	//	$target['user_xup'] 		= $source['user_xup'];
		$target['user_language'] 	= $source['user_language'];
		$target['user_country'] 	= $source['user_country'];
		$target['user_location'] 	= $source['user_location'];
		$target['user_aim'] 		= $source['user_aim'];
		$target['user_icq'] 		= $source['user_icq'];
		$target['user_yahoo'] 		= $source['user_yahoo'];
		$target['user_msn'] 		= $source['user_msn'];
		$target['user_homepage'] 	= $source['user_url'];
		$target['user_birthday'] 	= $source['user_birthday'];
		$target['user_timezone'] 	= $source['user_timezone'];

		$this->renderDebug($source,$target);
		
	//return $target;
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
			$target['news_title']				= $this->convertText($source['post_title']);
			$target['news_sef']					= $source['post_name'];
			$target['news_body']				= (vartrue($source['post_content'])) ? "[html]".$this->convertText($source['post_content'])."[/html]" : ""; 
		//	$target['news_extended']			= '';
		//	$target['news_meta_keywords']		= '';
		//	$target['news_meta_description']	= '';
			$target['news_datestamp']			= strtotime($source['post_date']);
			$target['news_author']				= ($this->newsAuthor !=0) ? $this->newsAuthor : $source['post_author'];
		//	$target['news_category']			= '';
			$target['news_allow_comments']		= ($source['comment_status']=='open') ? 1 : 0;
			$target['news_start']				= '';
			$target['news_end']					= '';
			$target['news_class']				= $this->newsClass($source['post_status']);
		//	$target['news_render_type']			= '0';
			$target['news_comment_total']		= $source['comment_count'];
			$target['news_summary']				= $this->convertText($source['post_excerpt']);
			$target['news_thumbnail']			= '';
			$target['news_sticky']				= '';

		return $target;  // comment out to debug 
		
		// DEBUG INFO BELOW. 		
		$this->renderDebug($source,$target);	
	}



	// Convert Wordpress Status to e107 News visibility class. 
	function newsClass($status)
	{
		$convert = array('publish'=> e_UC_PUBLIC, 'inherit' => e_UC_NOBODY, 'draft' => e_UC_NOBODY);
		
		return intval($convert[$status]);
	
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
		$target['page_title']			= $this->convertText($source['post_title']);
		$target['page_sef']				= $source['post_name'];
		$target['page_text']			= (vartrue($source['post_content'])) ? "[html]".$this->convertText($source['post_content'])."[/html]" : ""; 
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
		/* e107.
		0 => LCLAN_20, // 0 = same window
		1 => LCLAN_23, // new window
		4 => LCLAN_24, // 4 = miniwindow  600x400
		5 => LINKLAN_1 // 5 = miniwindow  800x600
		*/
		
			 
		$target['link_name']			= $this->convertText($source['link_name']);
		$target['link_url']				= $source['link_url'];
		$target['link_description']		= (vartrue($source['link_description'])) ? "[html]".$this->convertText($source['link_description'])."[/html]" : "";
	//	$target['link_button']			= '';
	//	$target['link_category']		= '';
	//	$target['link_order']			= '';
	//	$target['link_parent']			= '';
		$target['link_open']			= ''; // link_target
		$target['link_class']			= ($source['link_visible'] == 'Y') ? '0' : e_UC_MAINADMIN;
	//	$target['link_sefurl']			= '';
		
		return $target;  // comment out to debug 
		
		// DEBUG INFO BELOW. 
		$this->renderDebug($source,$target);
		
	}
	


	function convertText($text)
	{
		//$text = e107::getParser()->toDb($text);
		return $text;
					
		$text 		= html_entity_decode($text,ENT_QUOTES,'UTF-8');

		$detected 	= mb_detect_encoding($text); // 'ISO-8859-1'
		$text 		= iconv($detected,'UTF-8',$text);

		

		return $text;
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


