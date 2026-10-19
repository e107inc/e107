<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * @group plugins
 *
 * The error Hybridauth raises while it builds a provider from the stored social login configuration, as the social plugin's Configure page and a social sign-in show it; Apple's error repeats the key file named in the provider's settings.
 */
class socialProviderBuildErrorTest extends \Test\Unit
{
	/** A key file name holding a markup tag, as a hand-made post to the login forms' save stores it. */
	const KEY_FILE = '/nonexistent/<b>e107tests-key-file</b>';

	protected function _after()
	{
		putenv('QUERY_STRING');
	}

	public function testTheConfigurePageShowsAProviderBuildErrorAsText()
	{
		putenv('QUERY_STRING=mode=main&action=configure');

		$page = $this->renderInBootedCli('e107_plugins/social/admin_config.php', $this->brokenApple(), self::BUFFERED);

		self::assertStringNotContainsString('Fatal error', $page, "The page died:\n".$page);
		$this->assertTheErrorReadsAsText($page);
	}

	public function testASignInShowsAProviderBuildErrorAsText()
	{
		$php = $this->brokenApple();
		$php .= "\$method = new \\e107\\Reflection\\ReflectionMethod('e_user_provider', 'updateXupProfile'); ";
		$php .= "\$method->invoke(new e_user_provider()); ";
		$php .= "echo \"\\nMESSAGES:\".e107::getMessage()->render('default', true).\"\\nEND\\n\"; ";

		list($output) = $this->runInBootedCli($php, self::BUFFERED);
		$out = implode("\n", $output);

		self::assertSame(1, preg_match('/^MESSAGES:(.*)^END$/ms', $out, $match), "The sign-in step did not finish:\n".$out);
		$this->assertTheErrorReadsAsText($match[1]);
	}

	/**
	 * @return string PHP storing the social_login preference with Apple enabled and its key file named but absent
	 */
	private function brokenApple()
	{
		$apple = array('Apple-OAuth2' => array(
			'enabled' => 1,
			'keys' => array('id' => 'e107tests', 'team_id' => 'e107tests', 'key_id' => 'e107tests', 'key_file' => self::KEY_FILE),
		));

		return "e107::getConfig()->set('social_login', ".var_export($apple, true)."); ";
	}

	/**
	 * @param string $html
	 * @return void
	 */
	private function assertTheErrorReadsAsText($html)
	{
		preg_match_all('#<div class=([\'"])s-message-item\\1>(.*?)</div>#s', $html, $items);
		$named = array_values(preg_grep('/e107tests-key-file/', $items[2]));

		self::assertCount(1, $named, "No one message names the key file:\n".$html);
		self::assertStringNotContainsString('<', $named[0], "The key file's name added markup to the error:\n".$named[0]);
		self::assertStringContainsString(self::KEY_FILE, html_entity_decode($named[0], ENT_QUOTES, 'UTF-8'),
			"The error does not read as the key file's name:\n".$named[0]);
	}
}
