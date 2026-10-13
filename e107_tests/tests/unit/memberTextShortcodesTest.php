<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * Text a member wrote prints its shortcodes as written; an administrator's text still expands them.
 */
class memberTextShortcodesTest extends \Codeception\Test\Unit
{
	use \Test\BootedCli;

	const MARKER = 'memberTextShortcodesTest';

	const WRITTEN = 'Written {SITENAME} here';

	/** @var int */
	private $memberId;

	/** @var int */
	private $adminId;

	/** @var bool */
	private $forumInstalled = false;

	protected function _before()
	{
		$GLOBALS['_E107']['phpunit'] = true;

		$sql = e107::getDb();
		$sql->delete('user', "user_name = '".self::MARKER."'");

		$this->memberId = (int) $sql->insert('user', array(
			'user_name'      => self::MARKER,
			'user_loginname' => self::MARKER,
			'user_password'  => '',
			'user_email'     => 'member-text@example.com',
			'user_join'      => time(),
			'user_ban'       => 0,
			'user_class'     => '',
			'user_admin'     => 0,
			'user_perms'     => '',
			'user_prefs'     => '',
			'user_signature' => '',
			'user_realm'     => '',
			'user_xup'       => '',
		));
		self::assertGreaterThan(0, $this->memberId, 'could not write the member this test needs: '.$sql->getLastErrorText());

		$this->adminId = (int) $sql->retrieve('user', 'user_id', "user_admin = 1 AND user_perms = '0' ORDER BY user_id LIMIT 1");
		self::assertGreaterThan(0, $this->adminId, 'the test site has no main administrator');
	}

	protected function _after()
	{
		$sql = e107::getDb();
		$sql->delete('user', "user_id = ".$this->memberId);
		$sql->delete('forum', "forum_description = '".self::MARKER."'");
		$sql->delete('forum_post', "post_thread IN (SELECT thread_id FROM ".MPREFIX."forum_thread WHERE thread_name LIKE '%".self::MARKER."%')");
		$sql->delete('forum_thread', "thread_name LIKE '%".self::MARKER."%'");
		e107::getConfig()->removePref('e_notify_list/memberTextShortcodesProbe');

		if($this->forumInstalled)
		{
			e107::getPlugin()->uninstall('forum');
		}

		unset($GLOBALS['_E107']['phpunit']);
	}

	public function testANewsItemByAMemberPrintsItsTitleAndBodyAsWritten()
	{
		$sc = $this->newsShortcodes($this->memberId);

		self::assertStringContainsString('{SITENAME}', $sc->sc_news_title());
		self::assertStringContainsString('{SITENAME}', $sc->sc_news_body());
		self::assertStringContainsString('{SITENAME}', $sc->sc_captionclass());
		self::assertStringContainsString('{SITENAME}', $sc->sc_admincaption());

		require_once(e_CORE.'shortcodes/batch/news_archive_shortcodes.php');
		e107::getDb()->update('user', "user_signature = '".self::WRITTEN."' WHERE user_id = ".$this->memberId);
		e107::setRegistry('core/e107/user/'.$this->memberId, null);
		$sc->setScVar('news_item', array('user_id' => $this->memberId, 'news_author' => $this->memberId));
		self::assertStringContainsString('{SITENAME}', $sc->sc_news_author_signature());

		$archive = new news_archive_shortcodes();
		$archive->setVars(array('news_id' => 1, 'news_title' => self::WRITTEN, 'news_sef' => 'written', 'news_author' => $this->memberId));

		self::assertStringContainsString('{SITENAME}', $archive->sc_archive_link());
	}

	public function testANewsItemByAnAdministratorStillExpandsItsShortcodes()
	{
		$sc = $this->newsShortcodes($this->adminId);

		self::assertStringNotContainsString('{SITENAME}', $sc->sc_news_title());
		self::assertStringContainsString(SITENAME, $sc->sc_news_body());
	}

	public function testAFaqQuestionPrintsAsWrittenWhereverItIsShown()
	{
		require_once(e_PLUGIN.'faqs/faqs_shortcodes.php');

		$sc = new faqs_shortcodes();
		$sc->setVars(array(
			'faq_id'        => 42,
			'faq_question'  => self::WRITTEN,
			'faq_answer'    => 'An answer.',
			'faq_tags'      => '',
			'faq_datestamp' => 0,
			'faq_author'    => $this->adminId,
		));

		self::assertStringContainsString('{SITENAME}', $sc->sc_faq_question());
		self::assertStringContainsString('{SITENAME}', $sc->sc_faq_question('expand'));
		self::assertStringContainsString('{SITENAME}', $sc->sc_faq_question_link());
		self::assertStringContainsString('%7BSITENAME%7D', $sc->sc_faq_share());
	}

	public function testAPollTitleFollowsItsAuthor()
	{
		require_once(e_PLUGIN.'poll/poll_class.php');

		$sc = new poll_shortcodes();

		$sc->setVars(array('poll_id' => 1, 'poll_title' => self::WRITTEN, 'poll_admin_id' => $this->memberId));
		self::assertStringContainsString('{SITENAME}', $sc->sc_question());

		$sc->setVars(array('poll_id' => 1, 'poll_title' => self::WRITTEN, 'poll_admin_id' => $this->adminId));
		self::assertStringNotContainsString('{SITENAME}', $sc->sc_question());
	}

	public function testTheCommentMenuPrintsAHeadingAsWritten()
	{
		require_once(e_PLUGIN.'comment_menu/comment_menu_shortcodes.php');

		$sc = new comment_menu_shortcodes();
		$sc->setVars(array('comment_title' => self::WRITTEN));

		self::assertStringContainsString('{SITENAME}', $sc->sc_cm_heading());
	}

	public function testAMailBodyIsNotParsedButItsTemplateIs()
	{
		$eml = $this->make('e107Email');
		$eml->arraySet(array(
			'subject'  => 'Subject',
			'sender_email' => 'noreply@example.com',
			'sender_name'  => 'Sender',
			'template' => 'default',
			'body'     => self::WRITTEN,
		));

		self::assertStringContainsString(self::WRITTEN, $eml->Body);
		self::assertStringContainsString("<h4 class='sitename'>", $eml->Body);
	}

	public function testFeedTextExpandsShortcodesOnlyForAnAdministrator()
	{
		$tp = e107::getParser();

		self::assertStringContainsString('{SITENAME}', $tp->toRssForAuthor(self::WRITTEN, $this->memberId));
		self::assertStringContainsString('{SITENAME}', $tp->toRssForAuthor(self::WRITTEN, $this->memberId, true));
		self::assertStringContainsString('{SITENAME}', $tp->toRssForAuthor(self::WRITTEN, 0));
		self::assertStringNotContainsString('{SITENAME}', $tp->toRssForAuthor(self::WRITTEN, $this->adminId));
		self::assertStringNotContainsString('{SITENAME}', $tp->toRss(self::WRITTEN));
	}

	public function testANewsNotificationSubjectExpandsShortcodesOnlyForAnAdministrator()
	{
		self::assertStringContainsString('{SITENAME}', $this->newsNotification($this->memberId)['subject']);
		self::assertStringNotContainsString('{SITENAME}', $this->newsNotification($this->adminId)['subject']);
	}

	public function testANotificationSentTheOldWayStillExpandsItsSubject()
	{
		require_once(e_HANDLER.'notify_class.php');

		$notify = new notify();
		$this->routeToProbe($notify, 'custom_event');
		$notify->send('custom_event', self::WRITTEN, 'Message');

		self::assertStringNotContainsString('{SITENAME}', memberTextShortcodesProbe_notify::$sent[0]['subject']);
	}

	/**
	 * Sent from a process of its own, because a debug level above 0 only simulates the mailing.
	 */
	public function testANotificationSentTheOldWayIsQueuedToExpandForEachRecipient()
	{
		require_once(e_HANDLER.'notify_class.php');
		require_once(e_HANDLER.'mail_manager_class.php');

		$sql = e107::getDb();
		$class = 211;
		$members = array();
		for($i = 1; $i <= 6; $i++)
		{
			$members[] = (int) $sql->insert('user', array(
				'user_name'      => self::MARKER.$i,
				'user_loginname' => self::MARKER.$i,
				'user_password'  => '',
				'user_email'     => 'member-text-'.$i.'@example.com',
				'user_join'      => time(),
				'user_ban'       => 0,
				'user_class'     => (string) $class,
				'user_admin'     => 0,
				'user_perms'     => '',
				'user_prefs'     => '',
				'user_signature' => '',
				'user_realm'     => '',
				'user_xup'       => '',
			));
		}

		$php = "require_once(e_HANDLER.'notify_class.php'); \$notify = new notify(); ";
		$php .= "\$notify->notify_prefs['event']['custom_event'] = array('class' => '".$class."', 'recipient' => '', 'include' => '', 'legacy' => '0'); ";
		$php .= "\$notify->send('custom_event', '".self::MARKER." queued', '".self::WRITTEN."'); ";
		list($output, ) = $this->runInBootedCli($php, '', array('cli' => true, 'phpunit' => true));

		$row = $sql->retrieve('mail_content', '*', "mail_subject = '".self::MARKER." queued' ORDER BY mail_source_id DESC LIMIT 1");
		$sql->delete('user', 'user_id IN ('.implode(',', $members).')');

		self::assertNotEmpty($row, "six recipients should have queued the notification:\n".implode("\n", $output));
		$sql->delete('mail_recipients', 'mail_detail_id = '.(int) $row['mail_source_id']);
		$sql->delete('mail_content', 'mail_source_id = '.(int) $row['mail_source_id']);

		$manager = new e107MailManager();
		$queued = $manager->dbToMail($row);
		self::assertSame(1, (int) varset($queued['mail_body_parse'], 0), 'a queued send() body must still expand for each recipient');
	}

	public function testAQueuedMailKeepsAskingForItsBodyToBeExpanded()
	{
		require_once(e_HANDLER.'mail_manager_class.php');

		$manager = new e107MailManager();
		$id = $manager->saveEmail(array(
			'mail_title'          => self::MARKER,
			'mail_subject'        => self::MARKER,
			'mail_body'           => self::WRITTEN,
			'mail_content_status' => MAIL_STATUS_TEMP,
			'mail_send_style'     => 'notify',
			'mail_body_parse'     => 1,
		), true);
		self::assertNotEmpty($id, 'could not queue the mail this test needs');

		$row = e107::getDb()->retrieve('mail_content', '*', 'mail_source_id = '.(int) $id);
		e107::getDb()->delete('mail_content', 'mail_source_id = '.(int) $id);

		$saved = $manager->dbToMail($row);
		self::assertSame(1, (int) varset($saved['mail_body_parse'], 0));
	}

	public function testAForumPostNotificationPrintsEvenAnAdministratorsPostAsWritten()
	{
		$postId = $this->haveForumPost($this->adminId);

		require_once(e_PLUGIN.'forum/e_notify.php');
		$notify = new forum_notify();
		$this->routeToProbe($notify, 'user_forum_post_created');
		$notify->user_forum_post_created(array('post_id' => $postId));

		self::assertNotEmpty(memberTextShortcodesProbe_notify::$sent, 'the notification never reached its router');
		self::assertStringContainsString(self::WRITTEN, memberTextShortcodesProbe_notify::$sent[0]['message']);
	}

	public function testTheNewsFeedExpandsShortcodesOnlyInAnAdministratorsItem()
	{
		$sql = e107::getDb();
		foreach(array('admin' => $this->adminId, 'member' => $this->memberId) as $who => $author)
		{
			$sql->insert('news', array(
				'news_title'       => self::MARKER.' '.$who.' {SITENAME}',
				'news_sef'         => strtolower(self::MARKER).'-'.$who,
				'news_body'        => 'Body',
				'news_extended'    => '',
				'news_datestamp'   => time(),
				'news_author'      => $author,
				'news_category'    => 1,
				'news_allow_comments' => 0,
				'news_start'       => 0,
				'news_end'         => 0,
				'news_class'       => '0',
				'news_render_type' => 0,
				'news_summary'     => '',
				'news_thumbnail'   => '',
				'news_sticky'      => 0,
				'news_meta_keywords' => '',
				'news_meta_description' => '',
				'news_meta_robots' => '',
				'news_template'    => '',
			));
		}

		$php = "\$_GET['type'] = 2; \$_GET['cat'] = 'news'; ";
		$php .= "require_once('".addslashes(e_PLUGIN.'rss_menu/rss.php')."'); ";

		list($output, ) = $this->runInBootedCli($php);
		$feed = implode("\n", $output);

		$sql->delete('news', "news_sef LIKE '".strtolower(self::MARKER)."-%'");

		self::assertStringContainsString(self::MARKER.' member {SITENAME}', $feed, "the member's item is missing or expanded:\n".$feed);
		self::assertStringContainsString(self::MARKER.' admin ', $feed);
		self::assertStringNotContainsString(self::MARKER.' admin {SITENAME}', $feed);
	}

	public function testAMailoutBodyExpandsShortcodesOnlyForAnAdministrator()
	{
		self::assertStringNotContainsString('{SITENAME}', $this->mailoutBody($this->adminId));
		self::assertStringContainsString('{SITENAME}', $this->mailoutBody($this->memberId));
		self::assertStringContainsString('{SITENAME}', $this->mailoutBody(0));
		self::assertStringContainsString('{SITENAME}', $this->mailoutBody($this->adminId, 'texthtml'));
		$forEveryone = $this->mailoutBody($this->memberId, 'default', true);
		self::assertStringNotContainsString('{SITENAME}', $forEveryone);
		self::assertStringContainsString('to Rec{SITEDESCRIPTION}ipient', $forEveryone);
	}

	public function testTheTrackedThreadListPrintsAMembersThreadNameAsWritten()
	{
		if(!e107::getDb()->gen("SHOW TABLES LIKE '".MPREFIX."forum_thread'"))
		{
			e107::getPlugin()->install('forum');
			$this->forumInstalled = true;
		}

		$sql = e107::getDb();
		$categoryId = $this->haveForum('category', 0);
		$forumId = $this->haveForum('board', $categoryId);
		$threadId = (int) $sql->insert('forum_thread', array(
			'thread_name'      => self::MARKER.' '.self::WRITTEN,
			'thread_forum_id'  => $forumId,
			'thread_user'      => $this->memberId,
			'thread_datestamp' => time(),
			'thread_lastpost'  => time(),
			'thread_active'    => 1,
		));
		self::assertGreaterThan(0, $threadId, 'could not write the thread this test needs: '.$sql->getLastErrorText());

		$php = "e107::getConfig()->setPref('plug_installed/forum', '2.0'); ";
		$php .= "e107::plugLan('forum', 'global', true); ";
		$php .= "e107::getPlugConfig('forum')->set('track', 1); ";
		$php .= "e107::getDb()->delete('forum_track', 'track_thread = ".$threadId."'); ";
		$php .= "e107::getDb()->insert('forum_track', array('track_userid' => USERID, 'track_thread' => ".$threadId.")); ";
		$php .= "foreach(array('USERLV' => 0, 'e_TOKEN' => '') as \$k => \$v) { if(!defined(\$k)) { define(\$k, \$v); } } ";
		$php .= "\$_GET['f'] = 'track'; ";
		$php .= "require_once('".addslashes(APP_PATH.'/e107_plugins/forum/forum.php')."'); ";

		list($output, ) = $this->runInBootedCli($php, '', array('cli' => true, 'phpunit' => true));
		$page = implode("\n", $output);

		$sql->delete('forum_track', 'track_thread = '.$threadId);

		self::assertStringContainsString(self::MARKER.' Written', $page, "the tracked thread was never listed:\n".$page);
		self::assertStringContainsString(self::MARKER.' '.self::WRITTEN, $page);
	}

	/**
	 * @param int $author
	 * @return array what a news notification for an item $author wrote hands its router
	 */
	private function newsNotification($author)
	{
		require_once(e_HANDLER.'notify_class.php');

		$notify = new notify();
		$this->routeToProbe($notify, 'newspost');
		$notify->notify_newspost(array('news_title' => self::WRITTEN, 'news_author' => $author, 'news_summary' => '', 'news_body' => 'Body', 'news_extended' => ''));

		self::assertCount(1, memberTextShortcodesProbe_notify::$sent, 'the notification never reached its router');

		return memberTextShortcodesProbe_notify::$sent[0];
	}

	/**
	 * @param notify $notify
	 * @param string $event
	 */
	private function routeToProbe($notify, $event)
	{
		e107::getConfig()->setPref('e_notify_list/memberTextShortcodesProbe', 'memberTextShortcodesProbe');
		$notify->notify_prefs['event'][$event] = array('class' => 'memberTextShortcodesProbe::capture', 'recipient' => '', 'include' => '', 'legacy' => '0');
		memberTextShortcodesProbe_notify::$sent = array();
	}

	/**
	 * @param int $author
	 * @return int the id of a post $author wrote, in a thread of its own
	 */
	private function haveForumPost($author)
	{
		if(!e107::getDb()->gen("SHOW TABLES LIKE '".MPREFIX."forum_thread'"))
		{
			e107::getPlugin()->install('forum');
			$this->forumInstalled = true;
		}

		$sql = e107::getDb();
		$forumId = $this->haveForum('post-board', $this->haveForum('post-category', 0));
		$threadId = (int) $sql->insert('forum_thread', array(
			'thread_name'      => self::MARKER.' thread',
			'thread_forum_id'  => $forumId,
			'thread_user'      => $author,
			'thread_datestamp' => time(),
			'thread_lastpost'  => time(),
			'thread_active'    => 1,
		));
		$postId = (int) $sql->insert('forum_post', array(
			'post_entry'     => self::WRITTEN,
			'post_thread'    => $threadId,
			'post_forum'     => $forumId,
			'post_user'      => $author,
			'post_datestamp' => time(),
		));
		self::assertGreaterThan(0, $postId, 'could not write the post this test needs: '.$sql->getLastErrorText());

		return $postId;
	}

	/**
	 * @param int    $author
	 * @param string $style send style; one without an email template sends the body untouched
	 * @param bool   $forEveryone expand the body whoever wrote it, as notify::send() asks
	 * @return string the body a bulk mail written by $author sends to one recipient
	 */
	private function mailoutBody($author, $style = 'default', $forEveryone = false)
	{
		require_once(e_HANDLER.'mail_manager_class.php');

		$manager = new e107MailManager();
		$body = new ReflectionProperty('e107MailManager', 'currentMailBody');
		$body->setAccessible(true);
		$body->setValue($manager, '<p>'.self::WRITTEN.' for |USERNAME|'.($forEveryone ? ' to {USERNAME}' : '').'</p>');

		$block = new ReflectionMethod('e107MailManager', 'makeEmailBlock');
		$block->setAccessible(true);
		$email = $block->invoke($manager, array(
			'mail_subject'       => 'Subject',
			'mail_sender_email'  => 'noreply@example.com',
			'mail_sender_name'   => 'Sender',
			'mail_send_style'    => $style,
			'mail_target_info'   => array('USERNAME' => 'Rec{SITEDESCRIPTION}ipient'),
			'mail_recipient_id'  => 1,
			'mail_body_author'   => $author,
			'mail_body_parse'    => $forEveryone,
			'e107_header'        => 1,
		));

		$eml = $this->make('e107Email');
		$eml->arraySet($email);

		self::assertStringContainsString('for Rec{SITEDESCRIPTION}ipient', $eml->Body);

		return $eml->Body;
	}

	/**
	 * @param string $sef
	 * @param int    $parent 0 for a category
	 * @return int forum id
	 */
	private function haveForum($sef, $parent)
	{
		$id = (int) e107::getDb()->insert('forum', array(
			'forum_name'        => self::MARKER,
			'forum_description' => self::MARKER,
			'forum_parent'      => $parent,
			'forum_sub'         => 0,
			'forum_moderators'  => 0,
			'forum_class'       => e_UC_PUBLIC,
			'forum_postclass'   => e_UC_PUBLIC,
			'forum_threadclass' => e_UC_PUBLIC,
			'forum_order'       => 0,
			'forum_sef'         => strtolower(self::MARKER).'-'.$sef,
			'forum_datestamp'   => time(),
		));
		self::assertGreaterThan(0, $id, 'could not write the forum this test needs: '.e107::getDb()->getLastErrorText());

		return $id;
	}

	/**
	 * @param int $authorId
	 * @return news_shortcodes
	 */
	private function newsShortcodes($authorId)
	{
		require_once(e_CORE.'shortcodes/batch/news_shortcodes.php');

		$sc = new news_shortcodes();
		$sc->setScVar('news_item', array(
			'news_id'       => 1,
			'news_title'    => self::WRITTEN,
			'news_body'     => self::WRITTEN,
			'news_extended' => '',
			'news_author'   => $authorId,
			'news_category' => 1,
			'news_render_type' => 0,
		));
		$sc->setScVar('param', array('current_action' => 'list'));

		return $sc;
	}
}


/**
 * Stands in for a plugin's e_notify router, recording what notify hands it.
 */
class memberTextShortcodesProbe_notify
{
	/** @var array[] */
	public static $sent = array();

	/**
	 * @param array $data
	 * @return bool
	 */
	public function capture($data)
	{
		self::$sent[] = $data;

		return true;
	}
}
