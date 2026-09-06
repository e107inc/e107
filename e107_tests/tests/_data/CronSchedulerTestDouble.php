<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Test double for CronSchedulerTest; lives outside tests/unit because a test
 * file is parsed before class2.php has loaded the class it would extend.
 */

class CronSchedulerTestDouble extends cronScheduler
{
	/** @var array */
	public $mails = array();

	/** @var string */
	public $ip = '';

	public function refuse($via, $tokenPresented)
	{
		$_GET = $tokenPresented ? array('token' => 'wrong-'.uniqid('', false)) : array();
		unset($_SERVER['argv']);
		$this->validateToken();
		$this->recordRefusal($via);
	}

	public function ran($via)
	{
		$this->recordRun($via);
	}

	public function due($signature, $interval)
	{
		return $this->noticeIsDue($signature, $interval);
	}

	public function sendMail($mail)
	{
		$this->mails[] = $mail;
	}

	protected function requestIp()
	{
		return $this->ip;
	}
}
