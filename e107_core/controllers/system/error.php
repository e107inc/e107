<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2011 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * System error controller
 *
 * $URL$
 * $Id$
*/
class core_system_error_controller extends eController
{
	function preAction()
	{
		e107::coreLan('error');
	}
	
	public function actionNotfound()
	{
		$this->addTitle(LAN_ERROR_7);
		//var_dump($this->getRequest()->getRouteHistory());
		$errorText = "<img src='".e_IMAGE_ABS."generic/warning.png' alt='".LAN_ERROR_37."' /> ".LAN_ERROR_21.'<br />'.LAN_ERROR_9."<br /><br />";
		if (strlen($errFrom)) $errorText .= LAN_ERROR_23." <a href='{$errFrom}' rel='external'>{$errFrom}</a> ".LAN_ERROR_24." -- ".LAN_ERROR_19."<br /><br />";
		
	
		$errorText .= "<h3>".LAN_ERROR_45."</h3>";
		if($errReturnTo) 
		{
			foreach ($errReturnTo as $url => $label)
			{
				$errorText .= "<a href='{$url}'>".$label."</a><br />";
			}
			$errorText .= '<br />';
		}
		$url = e107::getUrl();
		
		$errorText .= "<a href='".SITEURL."'>".LAN_ERROR_20."</a><br />";
		$errorText .= "<a href='".$url->create('search')."'>".LAN_ERROR_22."</a>";
		
		$this->addBody($errorText);
	}
	
	function actionHelloWorld()
	{
		$this->addTitle('Hello!');
		echo 'Hello World';
	}
}
