<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2011 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * System XUP controller
 *
 *    $URL: https://e107.svn.sourceforge.net/svnroot/e107/trunk/e107_0.8/e107_admin/update_routines.php $
 *    $Revision: 12933 $
 *    $Id: update_routines.php 12933 2012-08-06 08:55:51Z e107coders $
 *    $Author: e107coders $
*/


class core_system_xup_controller extends eController
{
		
	var $backUrl = null;
	
		
	public function init()
	{
		//$back = 'system/xup/test';
		$this->backUrl = vartrue($_GET['back']) ? base64_decode($_GET['back']) : true;	
	}
	
	public function actionSignup()
	{
		$allow = true;
		$session = e107::getSession();
		if($session->get('HAuthError'))
		{
			$allow = false;
			$session->set('HAuthError', null);
		}
		
		if($allow && vartrue($_GET['provider']))
		{
			require_once(e_HANDLER."user_handler.php");
			$provider = new e_user_provider($_GET['provider']);
			try
			{
				$provider->signup($this->backUrl); // redirect to test page is expected, if true - redirect to SITEURL
			}
			catch (Exception $e)
			{
				e107::getMessage()->addError('['.$e->getCode().']'.$e->getMessage(), 'default', true);
			}
		}
		
		e107::getRedirect()->redirect(true === $this->backUrl ? SITEURL : e107::getUrl()->create($this->backUrl));
	}
	
	public function actionLogin()
	{
		$allow = true;
		$session = e107::getSession();
		if($session->get('HAuthError'))
		{
			$allow = false;
			$session->set('HAuthError', null);
		}

		if($allow && vartrue($_GET['provider']))
		{
			require_once(e_HANDLER."user_handler.php");
			$provider = new e_user_provider($_GET['provider']);
			try
			{
				$provider->login($this->backUrl); // redirect to test page is expected, if true - redirect to SITEURL
			}
			catch (Exception $e)
			{
				e107::getMessage()->addError('['.$e->getCode().']'.$e->getMessage(), 'default', true);
			}
		}
		e107::getRedirect()->redirect(true === $this->backUrl ? SITEURL : e107::getUrl()->create($this->backUrl));
	}
	
	public function actionTest()
	{
		echo 'Login controller<br /><br />';
		
		if(isset($_GET['lgt']))
		{
			e107::getUser()->logout();
		}
		
		echo 'Logged in: '.(e107::getUser()->isUser() ? 'true' : 'false');
		
		$provider = e107::getUser()->getProvider();
		if($provider) print_a($provider->getUserProfile());
		
		echo '<br /><br /><a href="'.e107::getUrl()->create('system/xup/test?lgt').'">Test logout</a>';
		echo '<br /><a href="'.e107::getUrl()->create('system/xup/login?provider=Facebook').'">Test login with Facebook</a>';
		echo '<br /><a href="'.e107::getUrl()->create('system/xup/signup?provider=Facebook').'">Test signup with Facebook</a>';
	}
	
	public function actionEndpoint()
	{
		require_once( e_HANDLER."hybridauth/Hybrid/Auth.php" );
		require_once( e_HANDLER."hybridauth/Hybrid/Endpoint.php" ); 
		try 
		{
			Hybrid_Endpoint::process();
		}
		catch (Exception $e)
		{
			e107::getMessage()->addError('['.$e->getCode().']'.$e->getMessage(), 'default', true);
			$session = e107::getSession();
			$session->set('HAuthError', true);
		}
		//echo 'End point';
	}
}
