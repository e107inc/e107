<?php

namespace Helper;

use Codeception\Module as CodeceptionModule;

/**
 * A forum to test against.
 *
 * The forum plugin has never had a test, and standing one up is not a matter of
 * creating its tables. Every front-end page gates on e107::isInstalled('forum'),
 * so the plugin has to be installed rather than merely present, and the shape of
 * the data matters as much as its existence:
 *
 *  - _getForumPermList() only grants permission on a forum whose forum_parent is
 *    not 0 AND whose parent row passes the same class test. A forum seeded at the
 *    top level grants nobody anything, which would make every "X cannot do Y"
 *    assertion in the suite pass without exercising anything.
 *  - forum.forum_moderators is a tinyint, so it is one userclass id and it must
 *    be under 256. A stored 0 does not mean "admins"; forumGetMods() only takes
 *    the admin branch for e_UC_ADMIN or an empty string, so 0 resolves to
 *    userclass 0, which is Everyone.
 *  - The two forums need DIFFERENT moderator classes, or a test that a moderator
 *    of one cannot act on the other has nothing to detect.
 *
 * Anything needing the application itself (installing the plugin, writing a
 * preference, clearing a cache) goes through a probe file dropped into the
 * docroot for the duration, the same shape 0020, 0022 and 0023 use. Rows go in
 * through haveInDatabase so the Db module removes them after each test.
 */
class ForumFixture extends CodeceptionModule
{
	const PROBE_FILE = 'e107_tests_forum_fixture_probe.php';

	/** Under 256, and clear of e107's own reserved range at the top. */
	const CLASS_MOD_A = 200;
	const CLASS_MOD_B = 201;

	/** Members can log in with this; seeded as md5 so no hash is guessed at. */
	const MEMBER_PASS = 'Password1234';

	/** @var bool */
	private $probeWritten = false;

	/** @var bool */
	private $pluginInstalled = false;

	// -----------------------------------------------------------------
	// collaborators
	// -----------------------------------------------------------------

	/**
	 * @return \Helper\Acceptance|\Helper\Webdriver
	 */
	private function app()
	{
		foreach (array('\Helper\Acceptance', '\Helper\Webdriver') as $name)
		{
			if ($this->hasModule($name))
			{
				return $this->getModule($name);
			}
		}

		throw new \RuntimeException('ForumFixture needs Helper\Acceptance or Helper\Webdriver');
	}

	/**
	 * @return \Codeception\Module\PhpBrowser|\Codeception\Module\WebDriver
	 */
	private function browser()
	{
		foreach (array('PhpBrowser', 'WebDriver') as $name)
		{
			if ($this->hasModule($name))
			{
				return $this->getModule($name);
			}
		}

		throw new \RuntimeException('ForumFixture needs PhpBrowser or WebDriver');
	}

	/**
	 * @return \Helper\DelayedDb
	 */
	private function db()
	{
		return $this->getModule('\Helper\DelayedDb');
	}

	// -----------------------------------------------------------------
	// the probe
	// -----------------------------------------------------------------

	public function haveForumProbe()
	{
		if ($this->probeWritten)
		{
			return;
		}

		$this->app()->writeAppFile(self::PROBE_FILE, $this->probeSource());
		$this->probeWritten = true;
	}

	public function dropForumProbe()
	{
		if (!$this->probeWritten)
		{
			return;
		}

		$this->app()->deleteAppFile(self::PROBE_FILE);
		$this->probeWritten = false;
	}

	/**
	 * @param string $query
	 * @return string probe output
	 */
	private function probe($query)
	{
		$this->haveForumProbe();

		$browser = $this->browser();
		$browser->amOnPage('/'.self::PROBE_FILE.'?'.$query);

		$body = $browser->grabPageSource();

		if (strpos($body, 'PROBE_OK') === false)
		{
			throw new \RuntimeException('Forum fixture probe failed for "'.$query.'": '.trim(strip_tags($body)));
		}

		return $body;
	}

	// -----------------------------------------------------------------
	// application state
	// -----------------------------------------------------------------

	/**
	 * Install the plugin properly rather than just creating its tables: the
	 * front-end pages gate on the plug_installed preference, and postDelete()
	 * touches user_extended columns that come from the plugin's extendedFields
	 * rather than from forum_sql.php.
	 *
	 * Once per suite. It creates four tables with FULLTEXT indexes, a userclass
	 * and two extended fields, which is far too much to repeat per test.
	 */
	public function haveForumPluginInstalled()
	{
		if ($this->pluginInstalled)
		{
			return;
		}

		$this->probe('act=install');
		$this->pluginInstalled = true;
	}

	public function dropForumPlugin()
	{
		$this->probe('act=uninstall');
		$this->pluginInstalled = false;
	}

	/**
	 * Pin csrf_enforce for the duration of a test.
	 *
	 * Not optional. 0023's teardown removes the preference, and it is the highest
	 * numbered Cest with shuffle off, so anything after it inherits an unset
	 * value. Unset resolves to the recommended mode, which reads no token and
	 * wants a Sec-Fetch-Site header PhpBrowser never sends.
	 *
	 * @param int|string $mode a csrf_enforce value, or 'default' to remove it
	 */
	public function haveForumCsrfMode($mode)
	{
		$this->probe('act=mode&m='.urlencode($mode));
	}

	/**
	 * e107 bans an address after fifty requests and localhost is exempt, but the
	 * client address inside the container is the bridge. Without this the suite
	 * bans itself part way through and every later response comes back empty,
	 * which reads as pages that do not exist.
	 */
	public function resetForumFloodProtection()
	{
		$this->probe('act=flood');
	}

	/**
	 * The permission list is cached under C_forum_perms_*, keyed on the language
	 * and the caller's userclass list. PrefCacheReset only sweeps S_Config_*, so
	 * rows seeded after a page load would otherwise stay invisible.
	 *
	 * Call after changing a forum's class columns or a user's classes.
	 */
	public function purgeForumPermCache()
	{
		$this->probe('act=purge');
	}

	// -----------------------------------------------------------------
	// rows
	// -----------------------------------------------------------------

	/**
	 * A category, two forums under it with different moderator classes, and a
	 * thread with an opening post in each.
	 *
	 * @return array ids, keyed category/forumA/forumB/threadA/threadB/postA/postB
	 */
	public function haveForumStructure()
	{
		$db = $this->db();
		$now = time();

		$category = $db->haveInDatabase('e107_forum', array(
			'forum_name' => 'Fixture Category', 'forum_description' => 'fixture',
			'forum_parent' => 0, 'forum_sub' => 0, 'forum_moderators' => 0,
			'forum_class' => 0, 'forum_postclass' => 0, 'forum_threadclass' => 0,
			'forum_order' => 1, 'forum_sef' => 'fixture-category', 'forum_datestamp' => $now,
		));

		$forumA = $this->haveForum('Fixture Forum A', 'fixture-forum-a', $category, self::CLASS_MOD_A, 1);
		$forumB = $this->haveForum('Fixture Forum B', 'fixture-forum-b', $category, self::CLASS_MOD_B, 2);

		$threadA = $this->haveForumThread('Fixture Thread A', $forumA, 1);
		$threadB = $this->haveForumThread('Fixture Thread B', $forumB, 1);

		return array(
			'category' => $category,
			'forumA'   => $forumA,
			'forumB'   => $forumB,
			'threadA'  => $threadA,
			'threadB'  => $threadB,
			'postA'    => $this->haveForumPost('Opening post in A', $threadA, $forumA, 1),
			'postB'    => $this->haveForumPost('Opening post in B', $threadB, $forumB, 1),
		);
	}

	/**
	 * @param string $name
	 * @param string $sef must be unique; forum_sef carries a unique key
	 * @param int $parent must not be 0, see the class docblock
	 * @param int $moderatorClass
	 * @param int $order
	 * @param int $viewClass 0 is e_UC_PUBLIC
	 * @return int forum id
	 */
	public function haveForum($name, $sef, $parent, $moderatorClass, $order = 1, $viewClass = 0)
	{
		return $this->db()->haveInDatabase('e107_forum', array(
			'forum_name' => $name, 'forum_description' => 'fixture',
			'forum_parent' => $parent, 'forum_sub' => 0,
			'forum_moderators' => $moderatorClass,
			'forum_class' => $viewClass, 'forum_postclass' => $viewClass, 'forum_threadclass' => $viewClass,
			'forum_order' => $order, 'forum_sef' => $sef, 'forum_datestamp' => time(),
		));
	}

	/**
	 * @param string $name
	 * @param int $forumId
	 * @param int $userId
	 * @param int $active 0 locks the thread
	 * @return int thread id
	 */
	public function haveForumThread($name, $forumId, $userId, $active = 1)
	{
		$now = time();

		return $this->db()->haveInDatabase('e107_forum_thread', array(
			'thread_name' => $name, 'thread_forum_id' => $forumId,
			'thread_active' => $active, 'thread_sticky' => 0,
			'thread_datestamp' => $now, 'thread_lastpost' => $now,
			'thread_user' => $userId, 'thread_lastuser' => $userId,
			'thread_total_replies' => 0, 'thread_views' => 0,
		));
	}

	/**
	 * @param string $entry
	 * @param int $threadId
	 * @param int $forumId
	 * @param int $userId 0 makes it an anonymous post, which is what an
	 *                    unauthenticated caller's USERID compares equal to
	 * @param string|null $attachments serialised post_attachments
	 * @return int post id
	 */
	public function haveForumPost($entry, $threadId, $forumId, $userId, $attachments = null)
	{
		$row = array(
			'post_entry' => $entry, 'post_thread' => $threadId, 'post_forum' => $forumId,
			'post_status' => 0, 'post_datestamp' => time(),
			'post_user' => $userId, 'post_ip' => '127.0.0.1',
		);

		if ($userId === 0)
		{
			$row['post_user_anon'] = 'Fixture Guest';
		}

		if ($attachments !== null)
		{
			$row['post_attachments'] = $attachments;
		}

		return $this->db()->haveInDatabase('e107_forum_post', $row);
	}

	/**
	 * @param int $id under 256
	 * @param string $name
	 * @return int the same id, for readability at the call site
	 */
	public function haveUserClass($id, $name)
	{
		$this->db()->haveInDatabase('e107_userclass_classes', array(
			'userclass_id' => $id, 'userclass_name' => $name,
			'userclass_description' => 'fixture', 'userclass_editclass' => 254,
			'userclass_parent' => 0, 'userclass_accum' => (string) $id,
			'userclass_visibility' => 254, 'userclass_type' => 0,
			'userclass_icon' => '', 'userclass_perms' => '',
		));

		return $id;
	}

	/**
	 * A member who can actually sign in.
	 *
	 * The password is stored as a plain md5. UserHandler::getHashType() reads any
	 * 32 character hash as PASSWORD_E107_MD5 and CheckPassword() accepts it
	 * whatever the site's configured encoding is, so the plaintext is known to
	 * the test. 0021 seeds a bcrypt hash whose plaintext is recorded nowhere,
	 * which is worth not repeating.
	 *
	 * The first successful login rehashes the row, so do not assert on
	 * user_password afterwards.
	 *
	 * @param string $name login name, also the display name
	 * @param string $classes comma separated userclass ids; 253 is e_UC_MEMBER
	 * @return int user id
	 */
	public function haveForumMember($name, $classes = '253')
	{
		return $this->db()->haveInDatabase('e107_user', array(
			'user_name' => $name, 'user_loginname' => $name, 'user_login' => $name,
			'user_password' => md5(self::MEMBER_PASS),
			'user_email' => $name.'@example.com',
			'user_join' => time(), 'user_ban' => 0,
			'user_class' => $classes,
			// Not an admin, and no perms. getperms('0') short-circuits every
			// MODERATOR test, so an admin would mask the bug under test.
			'user_admin' => 0, 'user_perms' => '',
			'user_prefs' => '', 'user_signature' => '', 'user_realm' => '', 'user_xup' => '',
		));
	}

	// -----------------------------------------------------------------
	// sessions
	// -----------------------------------------------------------------

	/**
	 * Sign in through the front end. AdminLogin::loginAsAdmin() cannot be used
	 * for this: it drives the admin area and asserts on the control panel
	 * heading, which a member never sees.
	 *
	 * @param string $name
	 * @param string|null $pass
	 */
	public function loginToForum($name, $pass = null)
	{
		$browser = $this->browser();

		$browser->amOnPage('/login.php');
		$browser->fillField('username', $name);
		$browser->fillField('userpass', $pass === null ? self::MEMBER_PASS : $pass);
		$browser->click('userlogin');
	}

	/**
	 * Drop the session.
	 *
	 * Emptying the jar rather than naming a cookie, because e107 takes its
	 * session cookie name from the cookie_name preference, so PHPSESSID is not
	 * reliably it and resetCookie() would silently leave the session standing.
	 */
	public function logoutFromForum()
	{
		$browser = $this->browser();

		if (method_exists($browser, 'deleteAllCookies'))
		{
			$browser->deleteAllCookies();

			return;
		}

		$browser->client->getCookieJar()->clear();
	}

	/**
	 * The token the page publishes, which is what a real client would send.
	 *
	 * Grab it after signing in, never before: e107 regenerates the session id on
	 * login, which retires a token minted for the guest session.
	 *
	 * @param string $page
	 * @return string
	 */
	public function grabForumToken($page)
	{
		$browser = $this->browser();
		$browser->amOnPage($page);

		$source = $browser->grabPageSource();

		if (!preg_match('/name=[\'"]e-token[\'"][^>]*(?:value|content)=[\'"]([^\'"]+)[\'"]/', $source, $m))
		{
			throw new \RuntimeException('No e-token published on '.$page);
		}

		return $m[1];
	}

	// -----------------------------------------------------------------

	/**
	 * @return string
	 */
	private function probeSource()
	{
		return <<<'PHP'
<?php
// Fixture for the forum Cests. Written per suite, removed in _after().
$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
header('Content-Type: text/plain');

$act = isset($_GET['act']) ? $_GET['act'] : '';

switch($act)
{
	case 'install':
		e107::getPlugin()->install_plugin_xml('forum', 'install');
		e107::getPlug()->clearCache()->buildAddonPrefLists();
		echo e107::isInstalled('forum') ? "PROBE_OK installed\n" : "not installed\n";
		break;

	case 'uninstall':
		e107::getPlugin()->install_plugin_xml('forum', 'uninstall', array('delete_tables' => true));
		e107::getPlug()->clearCache()->buildAddonPrefLists();
		echo "PROBE_OK uninstalled\n";
		break;

	case 'mode':
		$config = e107::getConfig('core');
		if($_GET['m'] === 'default') { $config->remove('csrf_enforce'); }
		else { $config->set('csrf_enforce', (int) $_GET['m']); }
		$config->save(false, true, false);
		echo "PROBE_OK mode\n";
		break;

	case 'flood':
		e107::getDb()->delete('online');
		e107::getDb()->delete('banlist', 'banlist_bantype IN (2, -2)');
		echo "PROBE_OK flood\n";
		break;

	case 'purge':
		e107::getCache()->clear('forum_perms', false, true);
		foreach(glob(e_CACHE_CONTENT.'C_forum_perms_*.cache.php') ?: array() as $file)
		{
			@unlink($file);
		}
		echo "PROBE_OK purge\n";
		break;

	default:
		echo "unknown action\n";
}
PHP;
	}
}
