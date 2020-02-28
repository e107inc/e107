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
	/**
	 * @var social_login_config
	 */
	private $social_login_config_manager;

	public function __construct(eRequest $request, eResponse $response = null)
	{
		parent::__construct($request, $response);
		require_once(e_PLUGIN."social/includes/social_login_config.php");
		$this->social_login_config_manager = new social_login_config(e107::getConfig());
	}

	public function init()
	{
		$this->backUrl = isset($_GET['back']) ? $_GET['back'] : null;
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
			$provider = e107::getUserProvider($_GET['provider']);
			try
			{
				$provider->login($this->backUrl, true, false); // redirect to test page is expected, if true - redirect to SITEURL
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
		require_once(e_PLUGIN . "social/includes/social_login_config.php");
		$manager = new social_login_config(e107::getConfig());

		if (!$manager->isFlagActive($manager::ENABLE_BIT_TEST_PAGE))
		{
			e107::getRedirect()->redirect(SITEURL);
			return;
		}

		echo '<h3>'.LAN_XUP_ERRM_07.'</h3>';
		
		if(getperms('0'))
		{
			echo e107::getMessage()->addError(LAN_XUP_ERRM_08)->render();
			return; 	
		}
		
		if(isset($_GET['logout']))
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
		$providers = $manager->getValidConfiguredProviderConfigs();

		foreach($providers as $key=>$var)
		{
			if($var['enabled'] == 1)
			{
				$testLoginUrl = e107::getUrl()->create('system/xup/login', [
					'provider' => $key,
					'back' => $testUrl,
				]);

				echo '<h4>'.$key.'</h4>';
				echo '<div><a class="btn btn-default btn-secondary" href="'.$testLoginUrl.'">'.e107::getParser()->lanVars(LAN_XUP_ERRM_10, array('x'=>$key)).'</a></div>';
			}
		}
		
			echo '<br /><br /><a class="btn btn-default btn-secondary" href="'.e107::getUrl()->create('system/xup/test?logout=true').'">'.LAN_XUP_ERRM_12.'</a>';
	}
}
