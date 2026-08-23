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

	/**
     * @param \eResponse|null $response
     */
    public function __construct(eRequest $request, $response = null)
	{
		parent::__construct($request, $response);
		require_once(e_PLUGIN."social/includes/social_login_config.php");
		$this->social_login_config_manager = new social_login_config(e107::getConfig());
	}

	public function init()
	{
		$back = isset($_GET['back']) ? $_GET['back'] : null;
		$verified = e107::getRedirect()->verifyDestinationUrl($back);

		$this->backUrl = ($verified === false) ? null : $verified;
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
			$tokenless = empty($_GET['e-token']);

			if($tokenless && deftrue('e_TOKEN') && $provider->loginNeedsToken())
			{
				e107::getMessage()->addError(defset('LAN_XUP_REFUSED_TOKEN_MISSING', 'That sign-in was not started, because the link carried no security token.'), 'default', true);
			}
			elseif($tokenless && self::askedForByAnotherSiteInTheBackground())
			{
				e107::getMessage()->addError(defset('LAN_XUP_REFUSED_NOT_A_NAVIGATION', 'That sign-in was not started, because another site asked for it in the background rather than sending you here.'), 'default', true);
			}
			else
			{
				try
				{
					$provider->login($this->backUrl, true, false); // redirect to test page is expected, if true - redirect to SITEURL
				}
				catch (Exception $e)
				{
					e107::getMessage()->addError('['.$e->getCode().']'.$e->getMessage(), 'default', true);
				}
			}
		}
		
		e107::getRedirect()->redirect(true === $this->backUrl ? SITEURL : $this->backUrl);
	}

	/**
	 * Has the browser said another site asked for this request, and asked for it
	 * as something other than a page to show the visitor?
	 *
	 * The five OpenID providers put nothing in the session on their way out, so
	 * {@see e_user_provider::loginNeedsToken()} cannot tell a sign-in that began
	 * here from one another site asked for, and a token cannot stand in for it:
	 * this address is also the address those providers return the visitor to,
	 * and the only marker of that returning leg, openid_mode, sits in the query
	 * string where a forger writes it as easily as a provider does. What is left
	 * over is a forced login, in which a session still holding a profile from an
	 * earlier sign-in is signed back in by a request the visitor never made.
	 *
	 * What a forger cannot write is the browser's own account of the request.
	 * Sec-Fetch-Site is a forbidden header name, so no document can set it, and
	 * Sec-Fetch-Dest says what the response was asked for. An image, a script or
	 * a fetch() started by a page on another site is neither a sign-in a visitor
	 * asked for nor a provider bringing one back, because both of those are
	 * top-level navigations, so refusing that combination leaves both legitimate
	 * legs untouched.
	 *
	 * A cross-site navigation to a document is deliberately let through: every
	 * genuine OpenID return is one, and refusing or interrupting it would cost a
	 * click on every sign-in to defend against an attacker who has to move the
	 * visitor's own window, which the visitor watches happen.
	 *
	 * A client that sends no fetch metadata has said nothing, which is not the
	 * same as saying it came from elsewhere, so it is let through the way
	 * {@see e_core_session::attest()} lets it through.
	 *
	 * @return bool
	 */
	private static function askedForByAnotherSiteInTheBackground()
	{
		if(empty($_SERVER['HTTP_SEC_FETCH_SITE']) || empty($_SERVER['HTTP_SEC_FETCH_DEST']))
		{
			return false;
		}

		$site = strtolower(trim($_SERVER['HTTP_SEC_FETCH_SITE']));

		if($site === 'same-origin' || $site === 'none')
		{
			return false;
		}

		return strtolower(trim($_SERVER['HTTP_SEC_FETCH_DEST'])) !== 'document';
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
		$providers = $manager->getSupportedConfiguredProviderConfigs();

		foreach($providers as $key=>$var)
		{
			if($var['enabled'] == 1)
			{
				$testLoginUrl = e107::getUserProvider($key)->generateLoginUrl($testUrl);

				echo '<h4>'.$key.'</h4>';
				echo '<div><a class="btn btn-default btn-secondary" href="'.$testLoginUrl.'">'.e107::getParser()->lanVars(LAN_XUP_ERRM_10, array('x'=>$key)).'</a></div>';
			}
		}
		
			echo '<br /><br /><a class="btn btn-default btn-secondary" href="'.e107::getUrl()->create('system/xup/test?logout=true').'">'.LAN_XUP_ERRM_12.'</a>';
	}
}
