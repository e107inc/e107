<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/import/wordpress_import_class.php,v $
 * $Revision: 1.4 $
 * $Date: 2009-11-17 13:48:44 $
 * $Author: marj_nl_fr $
 */

// This must be an incredibly pointless file! But it does allow testing of the basic plugin structure.

// Each import file has an identifier which must be the same for:
//		a) This file name - add '_class.php' to get the file name
//		b) The array index of certain variables
// Array element key defines the function prefix and the class name; value is displayed in drop-down selection box
$import_class_names['wordpress_import'] = 'Wordpress';
$import_class_comment['wordpress_import'] = 'Tested with version 2.8.x (salted passwords)';
$import_class_support['wordpress_import'] = array('users');
$import_default_prefix['wordpress_import'] = 'wp_';


require_once('import_classes.php');

class wordpress_import extends base_import_class
{
  // Set up a query for the specified task.
  // Returns TRUE on success. FALSE on error
  function setupQuery($task, $blank_user=FALSE)
  {
    if ($this->ourDB == NULL) return FALSE;
    switch ($task)
	{
	  case 'users' :
	  	$query =  "SELECT * FROM {$this->DBPrefix}users WHERE `user_id` != 1";

        $query = "SELECT u.*,
		  	w.meta_value AS admin,
		   	f.meta_value as firstname,
			l.meta_value as lastname
		   	FROM {$this->DBPrefix}users AS u
		  	LEFT JOIN {$this->DBPrefix}usermeta AS w ON (u.ID = w.user_id AND w.meta_key = 'wp_capabilities')
          	 LEFT JOIN {$this->DBPrefix}usermeta AS f ON (u.ID = f.user_id AND f.meta_key = 'first_name')
              LEFT JOIN {$this->DBPrefix}usermeta AS l ON (u.ID = l.user_id AND l.meta_key = 'last_name')
  		   GROUP BY u.ID";

         $this->ourDB -> db_Select_gen($query);

	    $result = $this->ourDB->db_Select_gen($query);

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
  
  // Copy data read from the DB into the record to be returned.
  function copyUserData(&$target, &$source)
  {
  	$user_meta = unserialize($source['admin']);

	if ($this->copyUserInfo)
	{
		 $target['user_id'] = $source['ID'];
	}
	$target['user_name'] = $source['user_nicename'];
	$target['user_loginname'] = $source['user_login'];
	$target['user_password'] = $source['user_pass'];   // needs to be salted!!!!
	$target['user_email'] = $source['user_email'];
	$target['user_hideemail'] = $source['user_hideemail'];
	$target['user_join'] = strtotime($source['user_registered']);
	$target['user_admin'] = ($user_meta['administrator'] == 1) ? 1 : 0;
	$target['user_lastvisit'] = $source['user_lastvisit'];
	$target['user_login'] = $source['firstname']." ".$source['lastname'];
	$target['user_ban'] = $source['user_ban'];
	$target['user_customtitle'] = $source['display_name'];
	$target['user_sess'] = $source['user_sess'];			// Photo
	$target['user_signature'] = $source['user_signature'];
	$target['user_image'] = $source['user_image'];			// Avatar
	$target['user_currentvisit'] = $source['user_currentvisit'];
	$target['user_lastpost'] = $source['user_lastpost'];
	$target['user_chats'] = $source['user_chats'];
	$target['user_comments'] = $source['user_comments'];

	$target['user_ip'] = $source['user_ip'];
	$target['user_prefs'] = $source['user_prefs'];
	$target['user_visits'] = $source['user_visits'];
	$target['user_class'] = $source['user_class'];
	$target['user_perms'] = $source['user_perms'];
	$target['user_xup'] = $source['user_xup'];
	$target['user_language'] = $source['user_language'];
	$target['user_country'] = $source['user_country'];
	$target['user_location'] = $source['user_location'];
	$target['user_aim'] = $source['user_aim'];
	$target['user_icq'] = $source['user_icq'];
	$target['user_yahoo'] = $source['user_yahoo'];
	$target['user_msn'] = $source['user_msn'];
	$target['user_homepage'] = $source['user_url'];
	$target['user_birthday'] = $source['user_birthday'];
	$target['user_timezone'] = $source['user_timezone'];


// user_pass 	user_nicename 	user_email 	user_url 	user_registered 	user_activation_key 	user_status 	display_name

	return $target;
  }

}


?>
