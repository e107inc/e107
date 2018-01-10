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

e107::coreLan('user');

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
				$provider->signup($this->backUrl, true, false); // redirect to test page is expected, if true - redirect to SITEURL
			}
			catch (Exception $e)
			{
				e107::getMessage()->addError('['.$e->getCode().']'.$e->getMessage(), 'default', true);
			}
		}
		
		e107::getRedirect()->redirect(true === $this->backUrl ? SITEURL : $this->backUrl);
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
		e107::getRedirect()->redirect(true === $this->backUrl ? SITEURL : $this->backUrl);
	}
	
	public function actionTest()
	{
		echo '<h3>'.LAN_XUP_ERRM_07.'</h3>';
		
		if(getperms('0'))
		{
			echo e107::getMessage()->addError(LAN_XUP_ERRM_08)->render();
			return; 	
		}
		
		if(isset($_GET['lgt']))
		{
			e107::getUser()->logout();
		}
		
		$profileData = null;
		$provider = e107::getUser()->getProvider();
		if($provider)
		{
			$profileData = $provider->getUserProfile();
			
			if(!empty($profileData))
			{
				print_a($profileData);	
			}
		
			 
		}
		
		echo ' '.LAN_XUP_ERRM_11.' '.(e107::getUser()->isUser() && !empty($profileData) ? '<span class="label label-success">true</span>' : '<span class="label label-danger">false</span>');
	
	
		$testUrl = SITEURL."?route=system/xup/test"; 
		$providers = e107::getPref('social_login', array());
		
		foreach($providers as $key=>$var)
		{
			if($var['enabled'] == 1)
			{
				echo '<h3>'.$key.'</h3><ul>';
				echo '<li><a class="btn btn-default btn-secondary" href="'.e107::getUrl()->create('system/xup/login?provider='.$key.'&back='.base64_encode($testUrl)).'">'.e107::getParser()->lanVars(LAN_XUP_ERRM_09, array('x'=>$key)).'</a></li>';
				echo '<li><a class="btn btn-default btn-secondary" href="'.e107::getUrl()->create('system/xup/signup?provider='.$key.'&back='.base64_encode($testUrl)).'">'.e107::getParser()->lanVars(LAN_XUP_ERRM_10, array('x'=>$key)).'</a></li>';
			
				echo "</ul>";
			}
			
		//	print_a($var);
		}
		
			echo '<br /><br /><a class="btn btn-default btn-secondary" href="'.e107::getUrl()->create('system/xup/test?lgt').'">'.LAN_XUP_ERRM_12.'</a>';
		
		/*
		echo '<h3>Facebook</h3>';
		echo '<br /><a href="'.e107::getUrl()->create('system/xup/login?provider=Facebook').'">Test login with Facebook</a>';
		echo '<br /><a href="'.e107::getUrl()->create('system/xup/signup?provider=Facebook').'">Test signup with Facebook</a>';
		
		echo '<h3>Twitter</h3>';
		echo '<br /><a href="'.e107::getUrl()->create('system/xup/login?provider=Twitter').'">Test login with Twitter</a>';
		echo '<br /><a href="'.e107::getUrl()->create('system/xup/signup?provider=Twitter').'">Test signup with Twitter</a>';
		
		 */
	}
	
	public function actionEndpoint()
	{
		require_once( e_HANDLER."hybridauth/Hybrid/Auth.php" );
		require_once( e_HANDLER."hybridauth/Hybrid/Endpoint.php" );
		require_once( e_HANDLER."hybridauth/vendor/autoload.php");

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
	}
}
