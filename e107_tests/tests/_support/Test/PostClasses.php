<?php

namespace Test;

/**
 * The classes that may post HTML, script and PHP, borrowed for the length of one test; keep this in PHP 5.6 syntax.
 */
trait PostClasses
{
	use CorePrefs;

	/**
	 * Runs $run with the classes that may post HTML and script, and PHP unless $php is null, set as given.
	 *
	 * @param int $html
	 * @param int $script
	 * @param callable $run
	 * @param int|null $php
	 * @return mixed what $run returns, the parser reading its script access again before and after, and check_class()
	 *               not passing every class on the command line meanwhile
	 */
	protected function withPostClasses($html, $script, $run, $php = null)
	{
		global $_E107;
		$prefs = array('post_html' => $html, 'post_script' => $script);

		if($php !== null)
		{
			$prefs['php_bbcode'] = $php;
		}

		$restore = $this->withCorePrefs($prefs);
		$flag = isset($_E107['phpunit']) ? $_E107['phpunit'] : null;
		$_E107['phpunit'] = true;
		\e107::getParser()->setScriptAccess(false);

		try
		{
			return $run();
		}
		finally
		{
			if($flag === null)
			{
				unset($_E107['phpunit']);
			}
			else
			{
				$_E107['phpunit'] = $flag;
			}

			$restore();
			\e107::getParser()->setScriptAccess(false);
		}
	}
}
