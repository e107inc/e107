<?php

/**
 * GHSA-vhf5-8cfh-jr22: posting a comment on an item whose comments are off,
 * through every route that reaches comment::enter_comment().
 *
 * Two things about the shape of it. Every case reads the stored row back,
 * because comment.php answers 200 and 302 for reasons unrelated to the insert
 * and the response separates neither. And every refusal has a positive control
 * that changes nothing but the item's flag: the flags disagree about which way
 * round they read, news_allow_comments being 0 for allowed where every other one
 * is 1, so a guard with the polarity inverted would refuse every comment on the
 * site and still pass a file of refusals.
 */
class CommentPostAuthzCest
{
	/**
	 * Registered in Extension\WorkspaceCleanup so a crashed run does not leave
	 * it in the docroot.
	 */
	const PROBE_FILE = 'e107_tests_comment_post_authz_probe.php';

	/** e_session::TOKEN_CHECK_ENFORCE. Pinned; see _before(). */
	const CSRF_TOKEN_ENFORCE = 2;

	const MEMBER_PASS = 'Password1234';

	/** @var int news item with news_allow_comments 0, which is comments on */
	private $newsOn;

	/** @var int news item with news_allow_comments 1, which is comments off */
	private $newsOff;

	/** @var int */
	private $pageOn;

	/** @var int */
	private $pageOff;

	/** @var int */
	private $downloadOn;

	/** @var int */
	private $downloadOff;

	/** @var int */
	private $pollOn;

	/** @var int */
	private $pollOff;

	/** @var int comment on the item with comments on, replied to below */
	private $parentOn;

	/** @var int comment on the item with comments off, replied to below */
	private $parentOff;

	/** @var int the member every request here is made as */
	private $member;

	/** @var int the member whose profile is commented on */
	private $profile;

	/** @var string */
	private $token;

	public function _before(AcceptanceTester $I)
	{
		$I->writeAppFile(self::PROBE_FILE, $this->probeSource());

		// Every request from the container arrives from the bridge address, and
		// e107 bans an address once it has been seen enough times in a window.
		$this->probe($I, 'act=flood');

		// Pin the CSRF mode rather than inherit it. What an unset preference
		// resolves to is a decision that moves between releases, and this file is
		// about authorisation: a POST refused by the CSRF gate would never reach
		// the check under test and every refusal would pass for the wrong reason.
		$this->probe($I, 'act=pref&k=csrf_enforce&v='.self::CSRF_TOKEN_ENFORCE);

		// What a fresh install ships. One test turns it on.
		$this->probe($I, 'act=pref&k=profile_comments&v=0');

		// The tables, not the plugin. comment.php asks neither plugin whether it
		// is installed, and installing and uninstalling around every test would
		// drop the tables under the Db module's own row cleanup.
		$I->havePluginTables('download');
		$I->havePluginTables('poll');

		$this->newsOn = $this->haveNews($I, 'vhf5 news on', 'vhf5-news-on', 0);
		$this->newsOff = $this->haveNews($I, 'vhf5 news off', 'vhf5-news-off', 1);
		$this->pageOn = $this->havePage($I, 'vhf5 page on', 'vhf5-page-on', 1);
		$this->pageOff = $this->havePage($I, 'vhf5 page off', 'vhf5-page-off', 0);
		$this->downloadOn = $this->haveDownload($I, 'vhf5 download on', 1);
		$this->downloadOff = $this->haveDownload($I, 'vhf5 download off', 0);
		$this->pollOn = $this->havePoll($I, 'vhf5 poll on', 1);
		$this->pollOff = $this->havePoll($I, 'vhf5 poll off', 0);

		$this->member = $this->haveMember($I, 'vhf5carol');
		$this->profile = $this->haveMember($I, 'vhf5dave');

		$this->parentOn = $this->haveComment($I, 'vhf5 parent on', $this->newsOn);
		$this->parentOff = $this->haveComment($I, 'vhf5 parent off', $this->newsOff);

		$this->loginAs($I, 'vhf5carol', $this->member);
		$this->token = $this->grabCommentToken($I);

		// A POST to this route answers 302 as often as 200, and an e107 that
		// cannot serve a request answers with a relative Location of install.php,
		// so following redirects can loop rather than fail.
		$I->stopFollowingRedirects();
	}

	public function _after(AcceptanceTester $I)
	{
		$this->probe($I, 'act=prefdel&k=csrf_enforce');
		$this->probe($I, 'act=pref&k=profile_comments&v=0');
		$I->deleteAppFile(self::PROBE_FILE);
	}

	/**
	 * The route the advisory was filed about.
	 */
	public function aNewsItemWithCommentsOffRefusesTheAjaxRoute(AcceptanceTester $I)
	{
		$text = 'vhf5 ajax news off';

		$this->postAjax($I, 'news', $this->newsOff, $text);

		$I->dontSeeInDatabase('e107_comments', array('comment_comment' => $text));
	}

	/**
	 * No AJAX involved. The switch that covered this route is the one the fix
	 * deletes, so this case proves the rule survived the move to the sink.
	 */
	public function aNewsItemWithCommentsOffRefusesTheFormRoute(AcceptanceTester $I)
	{
		$text = 'vhf5 form news off';

		$this->postForm($I, 'news', $this->newsOff, $text);

		$I->dontSeeInDatabase('e107_comments', array('comment_comment' => $text));
	}

	/**
	 * The reply branch, which is where the second defect was. Its guard selected
	 * news_allow_comments and then tested $row['news_id'], a column that select
	 * never returned, so the condition was true whatever the flag said and the
	 * insert always ran.
	 */
	public function aNewsItemWithCommentsOffRefusesTheReplyRoute(AcceptanceTester $I)
	{
		$text = 'vhf5 reply news off';

		$this->postReply($I, $this->parentOff, $this->newsOff, $text);

		$I->dontSeeInDatabase('e107_comments', array('comment_comment' => $text));
	}

	public function aNewsItemWithCommentsOnStillAcceptsTheAjaxRoute(AcceptanceTester $I)
	{
		$text = 'vhf5 ajax news on';

		$this->postAjax($I, 'news', $this->newsOn, $text);

		$this->seeCommentOn($I, $text, 0, $this->newsOn);
	}

	public function aNewsItemWithCommentsOnStillAcceptsTheFormRoute(AcceptanceTester $I)
	{
		$text = 'vhf5 form news on';

		$this->postForm($I, 'news', $this->newsOn, $text);

		$this->seeCommentOn($I, $text, 0, $this->newsOn);
	}

	public function aNewsItemWithCommentsOnStillAcceptsTheReplyRoute(AcceptanceTester $I)
	{
		$text = 'vhf5 reply news on';

		$this->postReply($I, $this->parentOn, $this->newsOn, $text);

		$this->seeCommentOn($I, $text, 0, $this->newsOn);
	}

	public function aPageWithCommentsOffRefusesTheFormRoute(AcceptanceTester $I)
	{
		$text = 'vhf5 form page off';

		$this->postForm($I, 'page', $this->pageOff, $text);

		$I->dontSeeInDatabase('e107_comments', array('comment_comment' => $text));
	}

	public function aPageWithCommentsOffRefusesTheAjaxRoute(AcceptanceTester $I)
	{
		$text = 'vhf5 ajax page off';

		$this->postAjax($I, 'page', $this->pageOff, $text);

		$I->dontSeeInDatabase('e107_comments', array('comment_comment' => $text));
	}

	public function aPageWithCommentsOnStillAcceptsAComment(AcceptanceTester $I)
	{
		$text = 'vhf5 form page on';

		$this->postForm($I, 'page', $this->pageOn, $text);

		$this->seeCommentOn($I, $text, 'page', $this->pageOn);
	}

	public function aProfileRefusesTheFormRouteWhileProfileCommentsIsOff(AcceptanceTester $I)
	{
		foreach ($this->profileTypes() as $table)
		{
			$text = 'vhf5 form profile off '.$table;

			$this->postForm($I, $table, $this->profile, $text);

			$I->dontSeeInDatabase('e107_comments', array('comment_comment' => $text));
		}
	}

	/**
	 * The one case where the stored comment reached the public: comment.php
	 * renders these rows to any visitor whose class passes, with no
	 * profile_comments check on the display path either.
	 */
	public function aProfileRefusesTheAjaxRouteWhileProfileCommentsIsOff(AcceptanceTester $I)
	{
		foreach ($this->profileTypes() as $table)
		{
			$text = 'vhf5 ajax profile off '.$table;

			$this->postAjax($I, $table, $this->profile, $text);

			$I->dontSeeInDatabase('e107_comments', array('comment_comment' => $text));
		}
	}

	public function aProfileStillAcceptsACommentWhileProfileCommentsIsOn(AcceptanceTester $I)
	{
		$this->probe($I, 'act=pref&k=profile_comments&v=1');

		foreach ($this->profileTypes() as $table)
		{
			$text = 'vhf5 form profile on '.$table;

			$this->postForm($I, $table, $this->profile, $text);

			$this->seeCommentOn($I, $text, $table, $this->profile);
		}
	}

	/**
	 * A member's profile answers to two comment types, and a rule that knows one
	 * of them leaves the other open. comment.php's own route calls it 'user',
	 * which is what the deleted switch matched; user.php's insert and the form
	 * the profile page publishes both call it 'profile'.
	 *
	 * @return array
	 */
	private function profileTypes()
	{
		return array('user', 'profile');
	}

	/**
	 * The type comes off the request line unfiltered, and getCommentType() hands
	 * back anything numeric unchanged, so '00' reaches the guard as a type that
	 * is not the array key 0 and is not a plugin's type either. It has to resolve
	 * to news, the way every other reader of a numeric type resolves it.
	 */
	public function aNumericAliasOfTheNewsTypeIsResolvedRatherThanWavedThrough(AcceptanceTester $I)
	{
		$refused = 'vhf5 form alias news off';
		$accepted = 'vhf5 form alias news on';

		$this->postForm($I, '00', $this->newsOff, $refused);
		$this->postForm($I, '00', $this->newsOn, $accepted);

		$I->dontSeeInDatabase('e107_comments', array('comment_comment' => $refused));
		$I->seeInDatabase('e107_comments', array(
			'comment_comment' => $accepted,
			'comment_item_id' => $this->newsOn,
		));
	}

	public function aDownloadWithCommentsOffRefusesTheFormRoute(AcceptanceTester $I)
	{
		$text = 'vhf5 form download off';

		$this->postForm($I, 'download', $this->downloadOff, $text);

		$I->dontSeeInDatabase('e107_comments', array('comment_comment' => $text));
	}

	public function aDownloadWithCommentsOnStillAcceptsAComment(AcceptanceTester $I)
	{
		$text = 'vhf5 form download on';

		$this->postForm($I, 'download', $this->downloadOn, $text);

		$this->seeCommentOn($I, $text, 2, $this->downloadOn);
	}

	public function aPollWithCommentsOffRefusesTheFormRoute(AcceptanceTester $I)
	{
		$text = 'vhf5 form poll off';

		$this->postForm($I, 'poll', $this->pollOff, $text);

		$I->dontSeeInDatabase('e107_comments', array('comment_comment' => $text));
	}

	public function aPollWithCommentsOnStillAcceptsAComment(AcceptanceTester $I)
	{
		$text = 'vhf5 form poll on';

		$this->postForm($I, 'poll', $this->pollOn, $text);

		$this->seeCommentOn($I, $text, 4, $this->pollOn);
	}

	/**
	 * The ordinary form, whose branch takes the table and the item id out of the
	 * request line and not out of the posted fields.
	 *
	 * @param AcceptanceTester $I
	 * @param string $table comment.php's table segment
	 * @param int $itemId
	 * @param string $text
	 */
	private function postForm(AcceptanceTester $I, $table, $itemId, $text)
	{
		$I->sendPostRequest('/comment.php?comment.'.$table.'.'.$itemId, array(
			'commentsubmit' => 'Post comment',
			'comment'       => $text,
			'subject'       => 'vhf5 subject',
			'author_name'   => '',
			'pid'           => 0,
			'e-token'       => $this->token,
		));
	}

	/**
	 * The reply branch. Its request line carries the comment being replied to in
	 * the third segment and the item id in the fourth, and the insert uses the
	 * fourth.
	 *
	 * @param AcceptanceTester $I
	 * @param int $parentId the comment being replied to
	 * @param int $itemId
	 * @param string $text
	 */
	private function postReply(AcceptanceTester $I, $parentId, $itemId, $text)
	{
		$I->sendPostRequest('/comment.php?comment.news.'.$parentId.'.'.$itemId, array(
			'replysubmit' => 'Post reply',
			'comment'     => $text,
			'subject'     => 'vhf5 subject',
			'author_name' => '',
			'pid'         => $parentId,
			'e-token'     => $this->token,
		));
	}

	/**
	 * The AJAX endpoint, which takes both from the posted fields.
	 *
	 * @param AcceptanceTester $I
	 * @param string $table
	 * @param int $itemId
	 * @param string $text
	 */
	private function postAjax(AcceptanceTester $I, $table, $itemId, $text)
	{
		$I->sendAjaxPostRequest('/comment.php?ajax_used=1&mode=submit', array(
			'comment'       => $text,
			'subject'       => 'vhf5 subject',
			'author_name'   => '',
			'table'         => $table,
			'itemid'        => $itemId,
			'pid'           => 0,
			'comment_share' => 0,
			'e-token'       => $this->token,
		));
	}

	/**
	 * Assert the comment landed against the item it was aimed at. A row alone
	 * would not say that: enter_comment() derives the stored comment_type from
	 * the table it was handed, so a fix that normalised the type wrongly could
	 * file the comment under another type and still leave a row behind.
	 *
	 * @param AcceptanceTester $I
	 * @param string $text
	 * @param string|int $type comment_type as comment::getCommentType() returns it
	 * @param int $itemId
	 */
	private function seeCommentOn(AcceptanceTester $I, $text, $type, $itemId)
	{
		$I->seeInDatabase('e107_comments', array(
			'comment_comment' => $text,
			'comment_type'    => (string) $type,
			'comment_item_id' => $itemId,
		));
	}

	/**
	 * The token the comment form publishes, which is what a real client sends.
	 * Grab it after signing in: e107 regenerates the session id on login, which
	 * retires a token minted for the guest session.
	 *
	 * @param AcceptanceTester $I
	 * @return string
	 */
	private function grabCommentToken(AcceptanceTester $I)
	{
		$I->amOnPage('/comment.php?comment.news.'.$this->newsOn);

		$source = $I->grabPageSource();

		// Take the token from the comment form and not merely from the page. A
		// theme publishes a token in its login menu too, so a page that failed to
		// serve the comment form would still hand back a usable token and every
		// test below would go on as though the route had answered.
		$form = strpos($source, "id='e-comment-form'");

		if ($form === false)
		{
			throw new \RuntimeException('The comment page served no comment form');
		}

		if (!preg_match('/name=[\'"]e-token[\'"][^>]*value=[\'"]([^\'"]+)[\'"]/', (string) substr($source, $form), $m))
		{
			throw new \RuntimeException('No e-token published in the comment form');
		}

		return $m[1];
	}

	/**
	 * Sign in through the front end, then have the application say who it thinks
	 * the caller is.
	 *
	 * The second half is not ceremony. Every refusal in this file is a row that
	 * is not there, and a request that silently arrived as a guest produces the
	 * same absence, so a login that quietly failed would turn each of them into a
	 * pass for the wrong reason.
	 *
	 * @param AcceptanceTester $I
	 * @param string $name
	 * @param int $userId
	 */
	private function loginAs(AcceptanceTester $I, $name, $userId)
	{
		$I->resetAllCookies();

		$I->amOnPage('/login.php');
		$I->fillField('username', $name);
		$I->fillField('userpass', self::MEMBER_PASS);
		$I->click('userlogin');

		$body = $this->probe($I, 'act=whoami');

		if (strpos($body, 'USERID='.$userId."\n") === false)
		{
			throw new \RuntimeException('Could not sign in as "'.$name.'": '.trim(strip_tags($body)));
		}
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $title
	 * @param string $sef
	 * @param int $allowComments news_allow_comments, where 0 is comments allowed
	 * @return int news id
	 */
	private function haveNews(AcceptanceTester $I, $title, $sef, $allowComments)
	{
		return $I->haveInDatabase('e107_news', array(
			'news_title' => $title, 'news_sef' => $sef,
			'news_body' => 'fixture', 'news_extended' => '',
			'news_meta_title' => '', 'news_meta_keywords' => '', 'news_meta_description' => '',
			'news_datestamp' => time() - 3600, 'news_author' => 1, 'news_category' => 0,
			'news_allow_comments' => $allowComments,
			'news_start' => 0, 'news_end' => 0, 'news_class' => '0', 'news_render_type' => '0',
			'news_summary' => '', 'news_thumbnail' => '',
		));
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $title
	 * @param string $sef
	 * @param int $commentFlag page_comment_flag, where 1 is comments allowed
	 * @return int page id
	 */
	private function havePage(AcceptanceTester $I, $title, $sef, $commentFlag)
	{
		return $I->haveInDatabase('e107_page', array(
			'page_title' => $title, 'page_sef' => $sef, 'page_subtitle' => '',
			'page_chapter' => 0, 'page_metatitle' => '', 'page_metakeys' => '',
			'page_metadscr' => '', 'page_metaimage' => '', 'page_metarobots' => '',
			'page_text' => 'fixture', 'page_author' => 1, 'page_datestamp' => time() - 3600,
			'page_rating_flag' => 0, 'page_comment_flag' => $commentFlag,
			'page_password' => '', 'page_class' => '0', 'page_ip_restrict' => '',
			'page_template' => '', 'page_order' => 9999, 'page_fields' => '',
			'menu_name' => '', 'menu_title' => '', 'menu_text' => '', 'menu_image' => '',
			'menu_icon' => '', 'menu_template' => '', 'menu_class' => '0',
			'menu_button_url' => '', 'menu_button_text' => '',
		));
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $name
	 * @param int $comment download_comment, where 1 is comments allowed
	 * @return int download id
	 */
	private function haveDownload(AcceptanceTester $I, $name, $comment)
	{
		return $I->haveInDatabase('e107_download', array(
			'download_name' => $name, 'download_url' => '', 'download_sef' => '',
			'download_author' => '', 'download_description' => '', 'download_keywords' => '',
			'download_filesize' => 0, 'download_requested' => 0, 'download_category' => 0,
			'download_active' => 1, 'download_datestamp' => time() - 3600,
			'download_thumb' => '', 'download_image' => '',
			'download_comment' => $comment, 'download_class' => 0,
			'download_mirror' => '', 'download_mirror_type' => 0,
		));
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $title
	 * @param int $comment poll_comment, where 1 is comments allowed
	 * @return int poll id
	 */
	private function havePoll(AcceptanceTester $I, $title, $comment)
	{
		return $I->haveInDatabase('e107_polls', array(
			'poll_title' => $title, 'poll_datestamp' => time() - 3600,
			'poll_start_datestamp' => 0, 'poll_end_datestamp' => 0, 'poll_admin_id' => 1,
			'poll_options' => '', 'poll_votes' => '', 'poll_ip' => '',
			'poll_type' => 0, 'poll_comment' => $comment, 'poll_allow_multiple' => 0,
			'poll_result_type' => 0, 'poll_vote_userclass' => 0, 'poll_storage_method' => 0,
		));
	}

	/**
	 * A member who can actually sign in.
	 *
	 * The password is stored as a plain md5: UserHandler::getHashType() reads any
	 * 32 character hash as PASSWORD_E107_MD5 and CheckPassword() accepts it
	 * whatever the site's configured encoding is, so the plaintext is known here.
	 *
	 * @param AcceptanceTester $I
	 * @param string $name
	 * @return int user id
	 */
	private function haveMember(AcceptanceTester $I, $name)
	{
		return $I->haveInDatabase('e107_user', array(
			'user_name' => $name, 'user_loginname' => $name, 'user_login' => $name,
			'user_password' => md5(self::MEMBER_PASS),
			'user_email' => $name.'@example.com',
			'user_join' => time(), 'user_ban' => 0,
			'user_lastvisit' => time() - 86400, 'user_currentvisit' => time() - 86400,
			'user_class' => '253',
			'user_admin' => 0, 'user_perms' => '',
			'user_prefs' => '', 'user_signature' => '', 'user_realm' => '', 'user_xup' => '',
		));
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $text
	 * @param int $itemId news item this comment belongs to
	 * @return int comment id
	 */
	private function haveComment(AcceptanceTester $I, $text, $itemId)
	{
		return $I->haveInDatabase('e107_comments', array(
			'comment_pid' => 0, 'comment_item_id' => $itemId,
			'comment_subject' => 'vhf5 subject',
			'comment_author_id' => 1, 'comment_author_name' => 'admin',
			'comment_author_email' => '', 'comment_datestamp' => time() - 1800,
			'comment_comment' => $text, 'comment_blocked' => 0, 'comment_ip' => '127.0.0.1',
			// getCommentType('news') is 0, and that is what enter_comment() stores.
			'comment_type' => '0', 'comment_lock' => 0, 'comment_share' => 0,
		));
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $query
	 * @return string probe output
	 */
	private function probe(AcceptanceTester $I, $query)
	{
		$I->amOnPage('/'.self::PROBE_FILE.'?'.$query);

		$body = $I->grabPageSource();

		if (strpos($body, 'PROBE_OK') === false)
		{
			throw new \RuntimeException('Comment post authz probe failed for "'.$query.'": '.trim(strip_tags($body)));
		}

		return $body;
	}

	/**
	 * Core preferences live serialised inside a single e107_core row, so no
	 * database assertion can read or write one. Boot the application instead.
	 *
	 * @return string
	 */
	private function probeSource()
	{
		return <<<'PHP'
<?php
// Fixture for CommentPostAuthzCest. Written per test, removed in _after().
$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
{{E107_TEST_PROBE_GUARD}}
header('Content-Type: text/plain');

$act = isset($_GET['act']) ? $_GET['act'] : '';
$key = isset($_GET['k']) ? preg_replace('/[^\w]/', '', $_GET['k']) : '';

switch($act)
{
	case 'flood':
		e107::getDb()->delete('online');
		e107::getDb()->delete('banlist', 'banlist_bantype IN (2, -2)');
		echo "PROBE_OK flood\n";
		break;

	case 'pref':
		$value = isset($_GET['v']) ? $_GET['v'] : '';
		$config = e107::getConfig('core');
		$config->set($key, is_numeric($value) ? (int) $value : $value);
		$config->save(false, true, false);
		echo "PROBE_OK pref ".$key."\n";
		break;

	case 'prefdel':
		$config = e107::getConfig('core');
		$config->remove($key);
		$config->save(false, true, false);
		echo "PROBE_OK prefdel ".$key."\n";
		break;

	case 'whoami':
		echo "PROBE_OK whoami\n";
		echo "USERID=".USERID."\n";
		echo "ANON=".(deftrue('ANON') ? 1 : 0)."\n";
		break;

	default:
		echo "unknown action\n";
}
PHP;
	}
}
