<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * The notices the read assertions look for, against the wording each PHP the suite runs on prints.
 */
class readNoticeWordingTest extends \Test\Unit
{
	/**
	 * @return array[] the keys, a notice, and whether it reports a read of one of those keys
	 */
	public function missingKeyNotices()
	{
		return array(
			'PHP 8, a string key'        => array('post', 'Warning: Undefined array key "post" in /x.php on line 1', true),
			'PHP 7, a string key'        => array('post', 'Notice: Undefined index: post in /x.php on line 1', true),
			'PHP 8, a longer string key' => array('post', 'Warning: Undefined array key "post_id" in /x.php on line 1', false),
			'PHP 7, a longer string key' => array('post', 'Notice: Undefined index: post_id in /x.php on line 1', false),
			'PHP 8, an integer key'      => array('[^"\s]*', 'Warning: Undefined array key 0 in /x.php on line 1', true),
			'PHP 7, an integer key'      => array('[^"\s]*', 'Notice: Undefined offset: 0 in /x.php on line 1', true),
			'PHP 8, an empty key'        => array('[^"\s]*', 'Warning: Undefined array key "" in /x.php on line 1', true),
			'PHP 7, an empty key'        => array('[^"\s]*', 'Notice: Undefined index:  in /x.php on line 1', true),
		);
	}

	/**
	 * @dataProvider missingKeyNotices
	 */
	public function testAMissingKeyIsRecognisedInEveryWording($keys, $notice, $reportsARead)
	{
		self::assertSame($reportsARead ? 1 : 0, preg_match(self::missingKeyNotice($keys), $notice), $notice);
	}

	/**
	 * @return array[] the names, a notice, and whether it reports a read of one of those variables
	 */
	public function undefinedVariableNotices()
	{
		return array(
			'PHP 8'                => array('total_topics', 'Warning: Undefined variable $total_topics in /x.php on line 1', true),
			'PHP 7'                => array('total_topics', 'Notice: Undefined variable: total_topics in /x.php on line 1', true),
			'PHP 8, a longer name' => array('total_topics', 'Warning: Undefined variable $total_topics_all in /x.php on line 1', false),
			'PHP 7, a longer name' => array('total_topics', 'Notice: Undefined variable: total_topics_all in /x.php on line 1', false),
		);
	}

	/**
	 * @dataProvider undefinedVariableNotices
	 */
	public function testAnUndefinedVariableIsRecognisedInEveryWording($names, $notice, $reportsARead)
	{
		self::assertSame($reportsARead ? 1 : 0, preg_match(self::undefinedVariableNotice($names), $notice), $notice);
	}

	/**
	 * @return array[] the names, a notice, and whether it reports a read of one of those constants
	 */
	public function undefinedConstantNotices()
	{
		return array(
			'PHP 8'                        => array('LAN_PLUGIN_POLL_NAME', 'Fatal error: Uncaught Error: Undefined constant "LAN_PLUGIN_POLL_NAME" in /x.php:1', true),
			'PHP 7.2 to 7.4'               => array('LAN_PLUGIN_POLL_NAME', "Warning: Use of undefined constant LAN_PLUGIN_POLL_NAME - assumed 'LAN_PLUGIN_POLL_NAME' (this will throw an Error in a future version of PHP) in /x.php on line 1", true),
			'PHP 5.6'                      => array('LAN_PLUGIN_POLL_NAME', "Notice: Use of undefined constant LAN_PLUGIN_POLL_NAME - assumed 'LAN_PLUGIN_POLL_NAME' in /x.php on line 1", true),
			'PHP 7, a constant expression' => array('LAN_PLUGIN_POLL_NAME', "Fatal error: Uncaught Error: Undefined constant 'LAN_PLUGIN_POLL_NAME' in /x.php:1", true),
			'PHP 8, a longer name'         => array('LAN_PLUGIN_POLL', 'Fatal error: Uncaught Error: Undefined constant "LAN_PLUGIN_POLL_NAME" in /x.php:1', false),
			'PHP 7, a longer name'         => array('LAN_PLUGIN_POLL', "Notice: Use of undefined constant LAN_PLUGIN_POLL_NAME - assumed 'LAN_PLUGIN_POLL_NAME' in /x.php on line 1", false),
		);
	}

	/**
	 * @dataProvider undefinedConstantNotices
	 */
	public function testAnUndefinedConstantIsRecognisedInEveryWording($names, $notice, $reportsARead)
	{
		self::assertSame($reportsARead ? 1 : 0, preg_match(self::undefinedConstantNotice($names), $notice), $notice);
	}
}
