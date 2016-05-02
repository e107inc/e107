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
	
	/**
	 * Alias
	 */
	public function action404()
	{
		$this->_forward('notfound');
	}
	
	public function actionNotfound()
	{
		$this->getResponse()
			->setRenderMod('error404')
			->addHeader('HTTP/1.0 404 Not Found');
		
		$this->addTitle(LAN_ERROR_7);
		$template = e107::getCoreTemplate('error', 404);
		
		$vars = new e_vars(array(
			'SITEURL' => SITEURL,
			'SEARCHURL' => e107::getUrl()->create('search'),
		));


		$body = e107::getParser()->parseTemplate(
				$this->updateTemplate($template['start']).
				$this->updateTemplate($template['body']).
				$this->updateTemplate($template['end'])
				, true, null, $vars);
		
		$this->addBody($body);
	}


	/**
	 * Update template to v2.x spec. ALL CAPS shortcodes only.
	 * @param $template
	 * @return mixed
	 */
	private function updateTemplate($template)
	{
		$srch = array('{siteUrl}','{searchUrl}');
		$repl = array('{SITEURL}','{SEARCHURL}');

		return str_replace($srch,$repl,$template);
	}
	
	/**
	 * Alias
	 */
	public function action403()
	{
		$this->_forward('forbidden');
	}

	public function actionForbidden()
	{
		$this->getResponse()
			->setRenderMod('error403')
			->addHeader('HTTP/1.0 403 Forbidden');
		
		$this->addTitle(LAN_ERROR_7);
		$template = e107::getCoreTemplate('error', 403);
		
		$vars = new e_vars(array(
			'SITEURL' => SITEURL,
		));
		
		$body = e107::getParser()->parseTemplate(
			$this->updateTemplate($template['start']).
			$this->updateTemplate($template['body']).
			$this->updateTemplate($template['end'])
			, true, null, $vars);
		
		$this->addBody($body);
	}
	
	function actionHelloWorld()
	{
		//$this->addTitle('Hello!');
		//echo 'Hello World';
	}
}
