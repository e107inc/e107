<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2011 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * News frontend
 *
 * $URL$
 * $Id$
 */

/*! \mainpage e107 Content Management System (CMS) - v2
 *
* \section intro_sec What is e107?
*
* e107 is a free (open-source) content management system which allows you to easily manage and publish your content online. Developers can save time in building websites and powerful online applications. Users can avoid programming completely! Blogs, Websites, Intranets - e107 does it all.
*
* \section requirements_sec Requirements
*
* - PHP v5.3 or higher
* - MySQL 4.x or higher
*
* \section install_sec Installation
*
* - Point your browser to the http://localhost/YOUR FOLDER/install.php (depending on your webserver setup)
* - Follow the installation wizard
*
* \section reporting_bugs_sec Reporting Bugs
*
* Be sure you are using the most recent version prior to reporting an issue. You may report any bugs or feature requests on GitHub (https://github.com/e107inc/e107/issues)
*
* \section pull_requests_sec Pull-Requests
*
* - Please submit 1 pull-request for each Github #issue you may work on.
* - Make sure that only the lines you have changed actually show up in a file-comparison (diff) ie. some text-editors alter every line so this should be avoided.
*
* \section license_sec License
*
* e107 is released under the terms and conditions of the GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
*/

 // BOOTSTRAP START


	define('e_SINGLE_ENTRY', TRUE);
	
	$_E107['single_entry'] = true; // TODO - notify class2.php
	
	define('ROOT', dirname(__FILE__));
	set_include_path(ROOT.PATH_SEPARATOR.get_include_path());


//	define('e_TOKEN_DISABLE', true);  // TODO FIXME cause of "Unauthorized Access!" message. SEF URL of Error pages causes e-token refresh.
	require_once("class2.php");

// ----------------------------

	$sql->db_Mark_Time("Start Simple URL-ReWrite Routine");

	$eUrl = e_url::instance();
	$eUrl->run();

	if($file = $eUrl->getInclude())
	{
		include_once($file);
		exit;
	}



// -----------------------------------------

	$sql->db_Mark_Time("Start regular eFront Class");
	
	$front = eFront::instance();
	$front->init()
		->run();
	
	$request = $front->getRequest();
	
	
	
	// If not already done - define legacy constants
	$request->setLegacyQstring();
	$request->setLegacyPage();


	
	$inc = $front->isLegacy(); 
	if($inc)
	{
		// last chance to set legacy env

		$request->populateRequestParams();
		if(!is_file($inc) || !is_readable($inc))
		{
			echo 'Bad request - destination unreachable - '.$inc;
		}
		include($inc);
		exit;
	}
	
	$response = $front->getResponse();
	if(e_AJAX_REQUEST)
	{
		$response->setParam('meta', false)
			->setParam('render', false)
			->send('default', false, true);
		exit;
	}
	$response->sendMeta();
	
	

// -------------- Experimental -----------------

	// unset($_SESSION['E:SOCIAL']);

	if(vartrue($_GET['provider']) && !isset($_SESSION['E:SOCIAL']) && e107::getPref('social_login_active', false) && (e_ADMIN_AREA !== true))
	{
		require_once(e_HANDLER."hybridauth/Hybrid/Auth.php");
	
		$config = array(
			"base_url" => SITEURL.$HANDLERS_DIRECTORY."hybridauth/", 
			"providers" => e107::getPref('social_login', array())	
		);
	
	//	print_a($config);
	 //	$params = array("hauth_return_to" => e_SELF);  
	
		$hybridauth = new Hybrid_Auth($config);
		
		$prov = (!isset($config['providers'][$_GET['provider']])) ? "Facebook" : $_GET['provider'];

	
		$adapter = $hybridauth->authenticate( $prov);
		$user_profile = $adapter->getUserProfile(); 
		
		$prov_id = $prov."_".$user_profile->identifier;
		
		if($user_profile->identifier >0)
		{
			if (!$sql->select("user", "*", "user_xup = '".$prov_id."' ")) // New User
			{
				$user_join 				= time();
				$user_pass 				= md5($user_profile->identifier.$user_join);
				$user_loginname 		= "xup_".$user_profile->identifier;
							
				$insert = array(
					'user_name'			=> $user_profile->displayName,
					'user_email'		=> $user_profile->email,
					'user_loginname'	=> $user_loginname,
					'user_password'		=> $user_pass,
					'user_login'		=> $user_profile->displayName,
					'user_join'			=> $user_join,
					'user_xup'			=> $prov_id
				);
				
				if($newid = $sql->insert('user',$insert,true))
				{
					e107::getEvent()->trigger('usersup', $insert);	
					if(!USERID)
					{
						require_once(e_HANDLER.'login.php');
						$usr = new userlogin($user_loginname, $user_pass, 'signup', '');		
					}
				}
			}
			else // Existing User. 
			{
				
			}

	
		}
	// 	echo "CHECKING";
		$_SESSION['E:SOCIAL'] = (array) $user_profile;	
		echo "USERNAME=".USERNAME;
		echo "<br />USEREMAIL=".USEREMAIL;
		echo "<br />USERIMAGE=".USERIMAGE;
	// print_a($_SESSION['E:SOCIAL']);
	}


// -------------------------------------------
	
	
	

	
	include_once(HEADERF);
		eFront::instance()->getResponse()->send('default', false, true);
	include_once(FOOTERF);
	exit;

 // BOOTSTRAP END

