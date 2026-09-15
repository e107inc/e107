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
 * The post form takes its action, its thread and its post from the query
 * string, and the links into it carry only what each of them needs.
 *
 * The page file defines MODERATOR and renders, so each case runs in its own
 * subprocess: a second require in the same process would warn about the
 * constant rather than about the reads under test.
 */
class forum_postTest extends \Test\Unit
{
	const READ = '@@e107help-read-the-query-string@@';

	/**
	 * e_QUERY is defined before the boot rather than after it, because the form
	 * turns an empty one away before it reads anything, and a CLI process has
	 * no query string of its own.
	 *
	 * The marker is written from a shutdown function, because the page
	 * redirects and exits on plenty of query strings, and it reports MODERATOR:
	 * that constant is defined on the line after the three reads under test, so
	 * its presence is what says they happened.
	 *
	 * @param array $get the query string the form is opened with
	 * @return string everything the page wrote, diagnostics included
	 */
	private function openThePostForm(array $get)
	{
		$php = "error_reporting(E_ALL); ini_set('display_errors', 1); ";
		$php .= "define('e_QUERY', '".http_build_query($get)."'); ";
		$php .= "\$_E107 = array('cli' => true); ";
		$php .= "require_once('".addslashes(APP_PATH.'/class2.php')."'); ";
		$php .= "error_reporting(E_ALL); \$_GET = ".var_export($get, true)."; ";
		$php .= "e107::getConfig()->setPref('plug_installed/forum', '2.0'); ";
		$php .= "register_shutdown_function(function() { if(defined('MODERATOR')) { echo '".self::READ."'; } }); ";
		$php .= "require_once('".addslashes(APP_PATH.'/e107_plugins/forum/forum_post.php')."'); ";

		list($output, ) = $this->runInCli($php);

		$printed = implode("\n", $output);

		self::assertStringContainsString(self::READ, $printed,
			"the form never got as far as reading the query string:\n".$printed);

		return $printed;
	}

	/**
	 * "Start a new topic" carries f and id and no post at all, which is the
	 * link @tgtje followed.
	 */
	public function testOpeningTheFormForANewTopicReadsNoPostId()
	{
		$printed = $this->openThePostForm(array('f' => 'nt', 'id' => '1'));

		self::assertDoesNotMatchRegularExpression('/Undefined array key "post"/i', $printed,
			"starting a topic carries no post id:\n".$printed);
	}

	/**
	 * A link that names only the thread leaves out the action as well, and the
	 * action is handed to trim(), which has refused null since PHP 8.1.
	 */
	public function testOpeningTheFormWithoutAnActionReadsNoActionEither()
	{
		$printed = $this->openThePostForm(array('id' => '1'));

		self::assertDoesNotMatchRegularExpression('/Undefined array key "(f|post)"/i', $printed,
			"the form reads query keys that are not there:\n".$printed);

		self::assertDoesNotMatchRegularExpression('/trim\(\): Passing null/i', $printed,
			"a missing action is handed to trim() as null:\n".$printed);
	}
}
