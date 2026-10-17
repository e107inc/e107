<?php

/**
 * List New Items prints each item's heading as its section built it, on its page and in its menus, and its info, on its page, to a guest.
 */
class ListNewItemsCest
{
	/** A thread name calling a shortcode, as the new-thread form stores it. */
	const SHORTCODE_NAME = '{PRINT_ITEM=x&quot; autofocus onfocus=&quot;document.title=1^y} Fixture shortcode thread';

	/** A thread name holding bbcode inside a table, as the new-thread form stores it. */
	const TABLE_NAME = '[table][img]/e107_images/e107_icon_32.png&#039; onload=&#039;document.title=1&#039;[/img][/table] Fixture table thread';

	/** A page comment's subject holding a link and an emoticon, as the comment form stores it. */
	const LINK_SUBJECT = '[link=//x/&#039;/autofocus/onfocus=&#039;document.title=1&#039;/]Fixture linked subject :)[/link]';

	/** Three page comments' subjects, oldest first, as the comment form stores them, that together close a tag a cut heading left open. */
	const CUT_SUBJECTS = array('&quot;onError=title=1//', '&quot;src x=&quot;', '[img]{e_MEDIA_IMAGE}x.png[/img]');

	/** A chatbox message holding a link inside a link, as the chatbox form stores it. */
	const CHATBOX_MESSAGE = 'http://x[url=//z/autofocus/onfocus=document.title=1//]q[/url]';

	/** The recent menu's caption, so a case can see the menu is on the page. */
	const MENU_CAPTION = 'Fixture recent menu';

	/** The menu area the default theme's news page renders. */
	const MENU_AREA = 1;

	/** An emoticon as an upgraded site holds it, which a fresh install such as the test site does not. */
	const EMOTES = array('smile!png' => ':) :o) :-)');

	/** @var array ids from Helper\ForumFixture::haveForumStructure() */
	private $ids;

	/** @var int */
	private $member;

	public function _before(AcceptanceTester $I)
	{
		$I->haveForumPluginInstalled();
		$I->havePluginInstalled('list_new');
		$I->havePluginInstalled('chatbox_menu');
		$I->havePluginPrefs('list_new', $this->listPrefs(''));
		$I->haveInDatabase('e107_core', array('e107_name' => 'emote_default', 'e107_value' => var_export(self::EMOTES, true)));

		$I->haveUserClass(\Helper\ForumFixture::CLASS_MOD_A, 'fixture_mod_a');
		$I->haveUserClass(\Helper\ForumFixture::CLASS_MOD_B, 'fixture_mod_b');
		$this->ids = $I->haveForumStructure();
		$this->member = $I->haveForumMember('listheadings');

		$I->purgeForumPermCache();
		$I->logoutFromForum();
	}

	public function _after(AcceptanceTester $I)
	{
		$I->havePluginPrefs('list_new', array());
		$I->removeAppPath('e107_system/'.\Helper\Acceptance::INSTALL_SITE_PATH.'/cache/content/S_Config_emote.cache.php');

		$I->restoreSitePrefs();

		$I->dropPluginInstall('chatbox_menu');
		$I->dropForumProbe();
		$I->dropPluginProbe();
	}

	public function aThreadNamesShortcodeIsPrintedAsText(AcceptanceTester $I)
	{
		$I->haveForumThread(self::SHORTCODE_NAME, $this->ids['forumA'], $this->member);

		$this->amOnTheListPageAsAGuest($I);

		$I->dontSeeInSource('onfocus="document.title=1');
		$I->dontSeeElement('[onfocus]');
		$I->seeInSource('{PRINT_ITEM=x&quot; autofocus onfocus=&quot;document.title=1^y} Fixture shortcode thread');
	}

	public function aThreadNamesBbcodeIsPrintedAsText(AcceptanceTester $I)
	{
		$I->haveForumThread(self::TABLE_NAME, $this->ids['forumA'], $this->member);

		$this->amOnTheListPageAsAGuest($I);

		$I->dontSeeInSource("onload='document.title=1");
		$I->dontSeeElement('[onload]');
		$I->seeInSource('[table][img]/e107_images/e107_icon_32.png&#039; onload=&#039;document.title=1&#039;[/img][/table]');
	}

	/**
	 * The comment section takes a title already rendered, and prints its text with each emoticon as a code for it.
	 */
	public function aPageCommentsSubjectIsPrintedAsText(AcceptanceTester $I)
	{
		$page = $this->havePage($I);
		$this->haveComment($I, $page, self::LINK_SUBJECT, 120);
		$this->haveComment($I, $page, ':)', 60);

		$this->amOnTheListPageAsAGuest($I);

		$I->dontSeeInSource("onfocus='document.title=1");
		$I->dontSeeElement('[onfocus]');
		$I->dontSeeInSource('bbcode-link');
		$I->dontSeeInSource('e-emoticon');
		$I->see('Fixture linked subject :)');
		$I->seeInSource('>:)</a>');
	}

	public function theRecentMenuPrintsAHeadingAsItsSectionBuiltIt(AcceptanceTester $I)
	{
		$this->haveComment($I, $this->havePage($I), self::LINK_SUBJECT, 60);

		$this->amOnTheMenuAsAGuest($I);

		$I->dontSeeInSource("onfocus='document.title=1");
		$I->dontSeeElement('[onfocus]');
		$I->dontSeeInSource('bbcode-link');
		$I->dontSeeInSource('e-emoticon');
		$I->see('Fixture linked subject :)');
	}

	/**
	 * The menus cut headings to 20 characters by default, and a comment's title is cut as text, never inside a tag.
	 */
	public function theRecentMenuCutsACommentsTitleAsText(AcceptanceTester $I)
	{
		$I->havePluginPrefs('list_new', $this->listPrefs('20'));
		$page = $this->havePage($I);

		foreach(self::CUT_SUBJECTS as $i => $subject)
		{
			$this->haveComment($I, $page, $subject, 300 - $i * 60);
		}

		$this->amOnTheMenuAsAGuest($I);

		$I->dontSeeInSource('<img class="img-roun');
		$I->dontSeeInSource('>"onError=');
		$I->seeInSource('&quot;onError=title=1//');
	}

	/**
	 * The chatbox section renders a message once, making its links clickable, and the page prints what it built.
	 */
	public function aChatboxMessageIsPrintedAsItsSectionBuiltIt(AcceptanceTester $I)
	{
		$I->haveSitePrefUntilAfter('make_clickable', 1);
		$I->haveInDatabase('e107_chatbox', array(
			'cb_nick' => $this->member.'.listheadings', 'cb_message' => self::CHATBOX_MESSAGE,
			'cb_datestamp' => time() - 60, 'cb_blocked' => 0, 'cb_ip' => '',
		));

		$this->amOnTheListPageAsAGuest($I);

		$I->dontSeeInSource('href="//z/autofocus');
		$I->dontSeeElement('[onfocus]');
		$I->seeInSource('class="e-url" href="'.self::CHATBOX_MESSAGE.'"');
	}

	public function aDisplayNameIsPrintedAsTyped(AcceptanceTester $I)
	{
		$I->haveForumMember('e_SYSTEM');

		$this->amOnTheListPageAsAGuest($I);

		$I->dontSeeInSource('e107_system/');
		$I->see('e_SYSTEM');
	}

	// -----------------------------------------------------------------

	/**
	 * @param AcceptanceTester $I
	 */
	private function amOnTheListPageAsAGuest(AcceptanceTester $I)
	{
		$I->resetAllCookies();
		$I->amOnPage('/e107_plugins/list_new/list.php');
		$I->seeResponseCodeIs(200);
	}

	/**
	 * The recent menu in the default theme's sidebar, on the news page, as a guest.
	 *
	 * @param AcceptanceTester $I
	 */
	private function amOnTheMenuAsAGuest(AcceptanceTester $I)
	{
		$I->haveInDatabase('e107_menus', array(
			'menu_name' => 'list_recent_menu', 'menu_location' => self::MENU_AREA, 'menu_order' => 0,
			'menu_class' => '0', 'menu_pages' => '', 'menu_path' => 'list_new/', 'menu_layout' => '', 'menu_parms' => '',
		));

		$I->resetAllCookies();
		$I->amOnPage('/news.php');
		$I->seeInSource(self::MENU_CAPTION);
	}

	/**
	 * A page open to comments.
	 *
	 * @param AcceptanceTester $I
	 * @return int page id
	 */
	private function havePage(AcceptanceTester $I)
	{
		return $I->haveInDatabase('e107_page', \Test\Rows::page(array(
			'page_title' => 'Fixture commented page', 'page_sef' => 'fixture-commented-page', 'page_text' => 'fixture',
			'page_author' => 1, 'page_datestamp' => time() - 3600, 'page_comment_flag' => 1,
		)));
	}

	/**
	 * The member's comment on a page.
	 *
	 * @param AcceptanceTester $I
	 * @param int $page
	 * @param string $subject as the comment form stores it
	 * @param int $age seconds since it was posted
	 */
	private function haveComment(AcceptanceTester $I, $page, $subject, $age)
	{
		$I->haveInDatabase('e107_comments', \Test\Rows::comment(array(
			'comment_item_id' => $page, 'comment_subject' => $subject,
			'comment_author_id' => $this->member, 'comment_author_name' => 'listheadings',
			'comment_datestamp' => time() - $age, 'comment_comment' => 'Fixture comment', 'comment_type' => 'page',
		)));
	}

	/**
	 * The comment, forum, members and chatbox sections on the recent page, and the comment section in the recent menu.
	 *
	 * @param string $menuHeadingChars where the menu cuts a heading; empty leaves it whole
	 * @return array
	 */
	private function listPrefs($menuHeadingChars)
	{
		$prefs = \Test\ListNewPrefs::showing(array('recent_page'), array('comment', 'forum', 'members', 'chatbox_menu'))
			+ \Test\ListNewPrefs::showing(array('recent_menu'), array('comment'));

		foreach(array('recent_page', 'recent_menu') as $mode)
		{
			$prefs[$mode.'_caption'] = $mode === 'recent_menu' ? self::MENU_CAPTION : 'Fixture recent page';
			$prefs[$mode.'_icon_use'] = '1';
			$prefs[$mode.'_icon_default'] = '1';
			$prefs[$mode.'_char_heading'] = $mode === 'recent_menu' ? $menuHeadingChars : '';
			$prefs[$mode.'_char_postfix'] = $mode === 'recent_menu' ? '...' : '';
			$prefs[$mode.'_datestyle'] = '%d %b';
			$prefs[$mode.'_datestyletoday'] = '%H:%M';
			$prefs[$mode.'_showempty'] = '1';
			$prefs[$mode.'_openifrecords'] = '';
		}

		return $prefs;
	}
}
