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
	 * @param int|null $datestamp when the thread was started
	 * @param int|null $lastpost when it was last posted in; defaults to $datestamp
	 * @return int thread id
	 */
	public function haveForumThread($name, $forumId, $userId, $active = 1, $datestamp = null, $lastpost = null)
	{
		// Backdated, because e107's flood check compares the newest
		// thread_datestamp against now (forum_post.php:1152). A fixture stamped
		// "just now" makes every reply the test then tries to post look like
		// flooding, and it is refused with nothing written.
		$started = $datestamp === null ? time() - 3600 : (int) $datestamp;
		$latest = $lastpost === null ? $started : (int) $lastpost;

		return $this->db()->haveInDatabase('e107_forum_thread', array(
			'thread_name' => $name, 'thread_forum_id' => $forumId,
			'thread_active' => $active, 'thread_sticky' => 0,
			'thread_datestamp' => $started, 'thread_lastpost' => $latest,
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
			'post_status' => 0, 'post_datestamp' => time() - 3600,
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
			// A visit that ended yesterday, so USERLV lands there rather than on
			// the moment of signing in. e_user::updateVisit() moves currentvisit
			// into lastvisit when the stored one is over an hour old, and stamps
			// lastvisit with *now* when it finds a zero, which would make every
			// fixture row older than the member's "last visit" and empty out
			// anything the plugin counts as new.
			'user_lastvisit' => time() - 86400, 'user_currentvisit' => time() - 86400,
			'user_class' => $classes,
			// Not an admin, and no perms. getperms('0') short-circuits every
			// MODERATOR test, so an admin would mask the bug under test.
			'user_admin' => 0, 'user_perms' => '',
			'user_prefs' => '', 'user_signature' => '', 'user_realm' => '', 'user_xup' => '',
		));
	}

	/**
	 * Recount a thread's replies through the plugin's own routine.
	 *
	 * Reached from the probe because its only caller is the split-topic branch
	 * of forum_post.php, which needs an editor and a form to get to; what is
	 * worth pinning is the count it leaves behind.
	 *
	 * @param int $threadId
	 */
	public function recountForumThread($threadId)
	{
		$this->probe('act=counts&thread='.(int) $threadId);
	}

	/**
	 * The thread ids forum.php?new would list for whoever holds the session.
	 *
	 * Asked of the query rather than read off the page, because the page hands
	 * thread rows to a template built for forum rows (forum.php:170) and so
	 * prints none of their names. That is a separate defect and not one this
	 * fixture can paper over; what is testable here is which rows the caller is
	 * offered, which is the whole of the permission question.
	 *
	 * @return int[]
	 */
	public function grabForumNewThreadIds()
	{
		$body = $this->probe('act=newlist');

		if (!preg_match('/NEW_THREADS=([^\r\n]*)/', $body, $m))
		{
			throw new \RuntimeException('Fixture did not report a thread list: '.trim(strip_tags($body)));
		}

		return array_values(array_filter(array_map('intval', explode(',', trim($m[1])))));
	}

	/**
	 * Mark threads read for a member, the way threadMarkAsRead() does.
	 *
	 * Call it after signing in, never before: the plugin's own login handler
	 * (e_event.php) empties this column on every login, so a value seeded first
	 * is gone by the time the page under test runs.
	 *
	 * Written through the probe rather than haveInDatabase because a member may
	 * or may not have a user_extended row by then, and an insert on top of an
	 * existing one collides on the primary key.
	 *
	 * @param int $userId
	 * @param int[] $threadIds
	 */
	public function haveForumThreadsRead($userId, array $threadIds)
	{
		$this->probe('act=viewed&uid='.(int) $userId.'&list='.urlencode(implode(',', array_map('intval', $threadIds))));
	}

	/**
	 * Create a member's attachment directory, as their first upload would.
	 *
	 * The traversal only resolves when it does exist: the operating system
	 * walks `user_000002/../..` component by component, so with no such
	 * directory the unlink fails for a reason that has nothing to do with the
	 * defect. A test that skipped this would report the bug as absent.
	 *
	 * @param int $userId
	 * @return string path relative to the app root, with a trailing slash
	 */
	public function haveForumAttachmentDir($userId)
	{
		$body = $this->probe('act=attachdir&uid='.(int) $userId);

		if (!preg_match('~ATTACH_DIR=(\S+)~', $body, $m))
		{
			throw new \RuntimeException('Fixture did not report an attachment path: '.trim(strip_tags($body)));
		}

		return ltrim($m[1], './');
	}

	/**
	 * A post whose post_attachments is written through e107's own serialiser,
	 * so the stored value has exactly the shape the application produces.
	 *
	 * @param string $entry post body
	 * @param int $threadId
	 * @param int $forumId
	 * @param int $userId
	 * @param array $attachments e.g. array('file' => array('note.txt'))
	 * @return int post id
	 */
	public function haveForumPostWithAttachments($entry, $threadId, $forumId, $userId, array $attachments)
	{
		$body = $this->probe('act=attachpost'
			.'&entry='.urlencode($entry)
			.'&thread='.(int) $threadId
			.'&forum='.(int) $forumId
			.'&user='.(int) $userId
			.'&payload='.urlencode(json_encode($attachments)));

		if (!preg_match('/POST_ID=(\d+)/', $body, $m))
		{
			throw new \RuntimeException('Fixture did not report a post id: '.trim(strip_tags($body)));
		}

		return (int) $m[1];
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

		if (isset($browser->webDriver))
		{
			// A real browser returns from click() before the form's navigation
			// has finished, so whatever the test asks for next races it and
			// often loses: the next amOnPage() starts loading, the login
			// response then arrives and wins, and the test finds itself back on
			// /login.php looking for a forum. Waiting for the redirect to leave
			// that page settles it, and times out with a useful message if the
			// sign-in was refused.
			$browser->waitForJS('return window.location.pathname.indexOf("/login.php") === -1;', 10);
		}
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

		if (isset($browser->webDriver))
		{
			// The browser has to be on the app's domain before its cookies can
			// be cleared, which is the same order WebDriverSession uses.
			$browser->amOnPage('/');
			$browser->webDriver->manage()->deleteAllCookies();

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
		// Empty the forum before the run starts.
		//
		// Two kinds of row outlive a test: the ones this fixture inserts through
		// the probe, and the ones the application creates when a test posts. The
		// Db module tracks neither, and the tests clean up after themselves only
		// when they pass, so a run that failed part way through leaves rows that
		// the next run's assertions count. That turns one real failure into a
		// second, unrelated-looking one on the next run.
		foreach(array('forum_post', 'forum_thread', 'forum', 'forum_track') as $table)
		{
			e107::getDb()->delete($table);
		}
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
		// Reports are throttled per reporter, and guests share one bucket, so
		// a report left by an earlier test would throttle the next one.
		e107::getDb()->delete('generic', "gen_type = 'reported_post'");
		echo "PROBE_OK flood\n";
		break;

	case 'attachdir':
		require_once(e_PLUGIN.'forum/forum_class.php');
		$forum = new e107forum();
		$dir = $forum->getAttachmentPath((int) $_GET['uid'], true);
		echo is_dir($dir) ? "PROBE_OK ATTACH_DIR=".$dir."\n" : "could not create ".$dir."\n";
		break;

	case 'attachpost':
		$payload = json_decode($_GET['payload'], true);
		e107::getDb()->insert('forum_post', array(
			'post_entry'       => $_GET['entry'],
			'post_thread'      => (int) $_GET['thread'],
			'post_forum'       => (int) $_GET['forum'],
			'post_status'      => 0,
			'post_datestamp'   => time(),
			'post_user'        => (int) $_GET['user'],
			'post_ip'          => '127.0.0.1',
			'post_attachments' => e107::serialize($payload),
		));
		echo "PROBE_OK POST_ID=".e107::getDb()->lastInsertId()."\n";
		break;

	case 'counts':
		require_once(e_PLUGIN.'forum/forum_class.php');
		$forum = new e107forum();
		$forum->threadUpdateCounts((int) $_GET['thread']);
		echo "PROBE_OK counts\n";
		break;

	case 'newlist':
		require_once(e_PLUGIN.'forum/forum_class.php');
		$forum = new e107forum();
		$ids = array();
		foreach($forum->threadGetNew(50) as $row)
		{
			$ids[] = (int) $row['thread_id'];
		}
		echo "PROBE_OK NEW_THREADS=".implode(',', $ids)."\n";
		break;

	case 'viewed':
		$uid = (int) $_GET['uid'];
		$db = e107::getDb();
		if(!$db->count('user_extended', '(*)', 'WHERE user_extended_id='.$uid))
		{
			$db->insert('user_extended', array('user_extended_id' => $uid));
		}
		$db->update('user_extended', "user_plugin_forum_viewed = '"
			.$db->escape($_GET['list'])."' WHERE user_extended_id=".$uid);
		echo "PROBE_OK viewed\n";
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
