<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

/**
 * Puts a messenger of the test's own in front of the one the cron task would
 * build, so a test can watch what the queue looks like from inside the send.
 *
 * In its own file because neither class can be declared until the bootstrap has
 * defined e_PLUGIN and the plugin's own files have been read, and Codeception
 * parses a *Test.php file before either has happened.
 */
class pm_cron_double extends pm_cron
{
	/** @var private_message the messenger this run adds through */
	public $pm;

	protected function messenger()
	{
		return $this->pm;
	}

	/**
	 * @param int $genId gen_id of the row to claim
	 * @return boolean what the claim answered
	 */
	public function claim($genId)
	{
		return $this->claimQueuedSend($genId);
	}
}

/**
 * A messenger that counts the sends it is asked for and hands the send itself
 * to the test, which is where a concurrent delete or a dead run goes.
 */
class private_message_add_double extends private_message_attachment_double
{
	/** @var callable|null run in place of the insert, with this double as its argument */
	public $onAdd;

	/** @var int sends this double has been asked for */
	public $adds = 0;

	public function add($vars, $bulk = FALSE)
	{
		$this->adds++;

		return isset($this->onAdd) ? call_user_func($this->onAdd, $this) : '';
	}
}

/**
 * The run dying part way through a send, thrown by a test rather than caused by
 * one. Its own class because PHPUnit's own failures are RuntimeExceptions, and
 * catching one of those would swallow them.
 */
class pm_cron_run_died extends Exception
{
}
