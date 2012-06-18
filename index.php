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

 // BOOTSTRAP START


	define('e_SINGLE_ENTRY', TRUE);
	
	$_E107['single_entry'] = true; // TODO - notify class2.php
	
	define('ROOT', dirname(__FILE__));
	set_include_path(ROOT.PATH_SEPARATOR.get_include_path());
	
	require_once("class2.php");
	
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
			if (!$sql->db_Select("user", "*", "user_xup = '".$prov_id."' ")) // New User
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
				
				if($newid = $sql->db_Insert('user',$insert,true))
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

