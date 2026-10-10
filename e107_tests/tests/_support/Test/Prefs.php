<?php

namespace Test;

/**
 * Core preferences changed for the duration of one closure, for tests that need a preference other than the installed one.
 *
 * Keep this in PHP 5.6 syntax: it is part of the shipping-adjacent test tree
 * that the downgrade pipeline walks.
 */
trait Prefs
{
	/**
	 * Runs $fn with $prefs applied to the core preferences in memory, restoring them afterwards; nothing is written, so the stored preferences are untouched.
	 *
	 * @param array    $prefs
	 * @param callable $fn
	 * @return void
	 */
	protected function withPrefs(array $prefs, $fn)
	{
		$config = \e107::getConfig();
		$previous = array();

		foreach($prefs as $key => $value)
		{
			$previous[$key] = $config->get($key);
			$config->set($key, $value);
		}

		try
		{
			$fn();
		}
		finally
		{
			foreach($previous as $key => $value)
			{
				if($value === null)
				{
					$config->remove($key);
					continue;
				}

				$config->set($key, $value);
			}
		}
	}
}
