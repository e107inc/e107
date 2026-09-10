<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Covers what the menu configuration screens tell an administrator about a
 * save that did not happen, which used to be a green "Saved" like any other.
 *
 * Nothing here reaches storage: {@see e_pref::save()} refuses before it
 * writes, and neither screen logs anything once it has been refused, so the
 * shared menu preferences and the admin log are left as they were found.
 *
 * @see https://github.com/e107inc/e107/issues/6196
 */
class menuConfigSaveMessagesTest extends \Codeception\Test\Unit
{
	use \Test\BootedCli;

	/** Marker proving the subprocess got past booting e107. */
	const BOOTED = 'E107-BOOTED';

	/** The class the message handler puts on a success box, {@see eMessage::formatMessage()}. */
	const SUCCESS_BOX = 'alert-success';

	/** Refuses the save. The screens cannot tell one refusal from another, and this is the one a test can provoke without breaking the database. */
	const SEED_FAILED_SAVE = "e107::getConfig('menu')->addValidationError('forced by menuConfigSaveMessagesTest'); ";

	/** A login menu form carrying values for every field the screen reads. */
	const POST_LOGIN_MENU = "\$_POST['pref'] = array('new_news' => '1', 'new_comments' => '1', 'new_members' => '1'); \$_POST['external_stats'] = array('menuConfigSaveMessagesTest' => 1); ";

	/** The online menu form, carrying a caption. */
	const POST_ONLINE_MENU = "\$_POST['online_caption'] = 'menuConfigSaveMessagesTest'; ";

	public function testLoginMenuScreenDoesNotAnnounceSuccessWhenTheSaveFailed()
	{
		$page = $this->submitScreen('login_menu/config.php', self::SEED_FAILED_SAVE.self::POST_LOGIN_MENU);

		$this->assertSame(0, substr_count($page, self::SUCCESS_BOX),
			"The login menu screen must not report a save that failed as saved (#6196).\n".$page);
	}

	public function testOnlineMenuScreenDoesNotAnnounceSuccessWhenTheSaveFailed()
	{
		$page = $this->submitScreen('online/config.php', self::SEED_FAILED_SAVE.self::POST_ONLINE_MENU);

		$this->assertSame(0, substr_count($page, self::SUCCESS_BOX),
			"The online menu screen must not report a save that failed as saved (#6196).\n".$page);
	}

	/**
	 * Posts the screen's own save button and returns what the administrator would have read.
	 *
	 * The screens finish in footer.php, which ends the request with its output buffer still open, so the page arrives on shutdown rather than after the require.
	 *
	 * @param string $screen path under e107_plugins
	 * @param string $seed PHP setting up preferences and post data before the screen is loaded
	 * @return string everything the screen wrote
	 */
	private function submitScreen($screen, $seed)
	{
		$php = "fwrite(STDERR, '".self::BOOTED."'); ";
		$php .= "register_shutdown_function(function() { while(ob_get_level() > 0) { @ob_end_flush(); } }); ";
		$php .= "\$_POST['update_menu'] = 1; ";
		$php .= $seed;
		$php .= "require_once('".addslashes(APP_PATH.'/e107_plugins/'.$screen)."'); ";

		list($output) = $this->runInBootedCli($php);
		$page = implode("\n", $output);

		$this->assertStringContainsString(self::BOOTED, $page,
			"The subprocess did not get as far as booting e107, so nothing below can be trusted.\n".$page);
		$this->assertStringContainsString('update_menu', $page,
			"The screen did not render its form, so what it did or did not say proves nothing.\n".$page);

		return $page;
	}
}
