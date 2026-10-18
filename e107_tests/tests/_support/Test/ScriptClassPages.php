<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

namespace Test;

/**
 * Opens a page in a booted child as the CLI main admin with a given class that may post script; keep this in PHP 5.6 syntax.
 */
trait ScriptClassPages
{
	/** @var string an address that runs no script as stored, and one that does once toDB() has taken out its empty bbcode pair */
	protected static $inertScriptAddress = 'java[p][/p]script:void(0)';

	/**
	 * @return array stored addresses that an untouched save has to keep byte for byte: one that runs script, and two a form that decoded its value for display would change
	 */
	public function untouchedStoredAddresses()
	{
		return array(
			'one that runs script'         => array('javascript:void(0)'),
			'one whose colon is %-escaped' => array('javascript%3Avoid(0)'),
			'one holding an encoded tag'   => array('news.php?q=&lt;b&gt;'),
		);
	}

	/**
	 * @param int $scriptClass the class that may post script
	 * @param string $script path under the app root, e.g. 'e107_admin/cpage.php'; one in no folder is a page outside the admin area
	 * @param string $query the query string
	 * @param array $post what the form posts, its token added; empty to open the page
	 * @param string $seed PHP to run before the page, e.g. setting preferences
	 * @param bool $inline whether the post is an inline edit, which carries a token of its own
	 * @return string the page
	 */
	protected function openAsScriptClass($scriptClass, $script, $query, array $post = array(), $seed = '', $inline = false)
	{
		putenv('QUERY_STRING='.$query);
		putenv($inline ? 'HTTP_X_REQUESTED_WITH=XMLHttpRequest' : 'HTTP_X_REQUESTED_WITH');

		$php = "\$GLOBALS['_E107']['phpunit'] = true; e107::getUser()->setData(e107::user(USERID)); e107::getParser()->setScriptAccess(".(int) $scriptClass.'); '.$seed;

		if(dirname($script) !== '.')
		{
			$php = "e107::includeLan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_admin.php'); ".$php;
		}

		if($post)
		{
			$php .= '$_POST = '.var_export($post, true)." + array('e-token' => defset('e_TOKEN')); ";
			$php .= $inline ? "\$_POST['token'] = password_hash(session_id(), PASSWORD_DEFAULT); " : '';
		}

		try
		{
			$page = $this->renderInBootedCli($script, $php);
		}
		finally
		{
			putenv('QUERY_STRING');
			putenv('HTTP_X_REQUESTED_WITH');
		}

		self::assertStringNotContainsString('Fatal error', $page, "the page died:\n".$page);

		return $page;
	}

	/**
	 * @param string $page
	 * @return array the value of each input on the page as a browser posts it back, by name
	 */
	protected function inputValues($page)
	{
		$document = new \DOMDocument();
		$errors = libxml_use_internal_errors(true);
		$document->loadHTML('<?xml encoding="utf-8"?>'.$page);
		libxml_clear_errors();
		libxml_use_internal_errors($errors);

		$values = array();

		foreach($document->getElementsByTagName('input') as $input)
		{
			$values[$input->getAttribute('name')] = $input->getAttribute('value');
		}

		return $values;
	}
}
