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
 * An attachment's display name and index are whatever the poster sent, so the topic page and the admin post list print them as text.
 */
class forumAttachmentNameTest extends \Codeception\Test\Unit
{
	use \Test\BootedCli;

	/** Markup a stored attachment carries when the poster put it there. */
	const MARKUP = '<i data-probe="forumAttachmentName">';

	/** @var plugin_forum_view_shortcodes */
	private $sc;

	/** @var string a poster's attachment directory, holding one image */
	private $dir;

	/** @var bool whether this test installed the forum plugin for its tables */
	private $forumInstalled = false;

	/** @var int */
	private $postId = 0;

	protected function _before()
	{
		e107::lan('forum', 'front', true);

		require_once(e_PLUGIN . 'forum/shortcodes/batch/view_shortcodes.php');
		require_once(e_PLUGIN . 'forum/forum_class.php');

		$this->dir = e_SYSTEM . 'forumAttachmentNameTest/';

		if(!is_dir($this->dir))
		{
			mkdir($this->dir, 0755, true);
		}

		file_put_contents($this->dir . 'photo.jpg', 'not an image');

		try
		{
			$this->sc = $this->make('plugin_forum_view_shortcodes');
			$this->sc->forum = $this->make('e107forum', array('getAttachmentPath' => $this->dir));
		}
		catch(Exception $e)
		{
			self::fail($e->getMessage());
		}
	}

	protected function _after()
	{
		putenv('QUERY_STRING');

		@unlink($this->dir . 'photo.jpg');
		@rmdir($this->dir);

		if($this->postId)
		{
			e107::getDb()->delete('forum_post', 'post_id = ' . (int) $this->postId);
			$this->postId = 0;
		}

		if($this->forumInstalled)
		{
			e107::getPlugin()->uninstall('forum');
			$this->forumInstalled = false;
		}
	}

	public function testTheTopicPagePrintsADisplayNameCarryingMarkupAsText()
	{
		$page = $this->renderAttachments(array('file' => array(
			array('file' => 'a.txt', 'name' => self::MARKUP, 'size' => 1),
			'1690000000_0_' . self::MARKUP,
		)));

		$this->assertMarkupIsText($page);
		self::assertSame(2, substr_count($page, '&lt;i data-probe='), "both names should still be readable:\n" . $page);
	}

	/**
	 * The Bootstrap topic template asks for the modal, and the modal's script sets the caption as text.
	 */
	public function testTheModalCaptionReadsAsTheDisplayNameOnceTheBrowserHasDecodedIt()
	{
		$names = array(
			self::MARKUP                => self::MARKUP,
			'O&#039;Brien &amp; Co.jpg' => "O'Brien & Co.jpg",
		);

		foreach($names as $stored => $shown)
		{
			$page = $this->renderAttachments(array('img' => array(
				array('file' => 'photo.jpg', 'name' => $stored, 'size' => 1),
			)), array('modal' => 1));

			self::assertSame(1, preg_match('/data-modal-caption="([^"]*)"/', $page, $match), "no caption was written:\n" . $page);
			self::assertSame($shown, html_entity_decode($match[1], ENT_QUOTES, 'UTF-8'), $page);
		}
	}

	public function testTheMissingFileNoticePrintsTheStoredNameAsText()
	{
		self::assertTrue(ADMIN, 'the notice is printed to administrators only');

		$page = $this->renderAttachments(array('img' => array(
			array('file' => self::MARKUP, 'name' => 'gone.jpg', 'size' => 1),
		)));

		self::assertStringContainsString('Missing File', $page, "no notice was printed:\n" . $page);
		$this->assertMarkupIsText($page);
	}

	public function testTheDownloadLinkCarriesItsIndexAsText()
	{
		$page = $this->renderAttachments(array('file' => array(
			"0' data-probe='forumAttachmentName" => array('file' => 'a.txt', 'name' => 'a.txt', 'size' => 1),
		)));

		self::assertStringNotContainsString("data-probe='forumAttachmentName", $page,
			"the index has left the link's address:\n" . $page);
	}

	/**
	 * Uploads store their name through e_parse::filter(), which has already encoded it once.
	 */
	public function testAnUploadedNameIsNotEncodedTwice()
	{
		$page = $this->renderAttachments(array('file' => array(
			array('file' => 'a.txt', 'name' => 'O&#039;Brien &amp; Co.pdf', 'size' => 1),
		)));

		self::assertStringContainsString('O&#039;Brien &amp; Co.pdf', $page);
	}

	/**
	 * The child is no admin-area request, so it is given the phrases class2.php loads for one.
	 */
	public function testTheAdminPostListPrintsADisplayNameAndSizeCarryingMarkupAsText()
	{
		$this->seedPost(array(
			'img'  => array(array('file' => 'photo.jpg', 'name' => self::MARKUP, 'size' => '0' . self::MARKUP)),
			'file' => array(array('file' => 'a.txt', 'name' => self::MARKUP, 'size' => 1)),
		));

		putenv('QUERY_STRING=mode=post&action=list');

		$page = $this->renderInBootedCli('e107_plugins/forum/forum_admin.php',
			"e107::includeLan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_admin.php'); ");

		self::assertStringContainsString('forumAttachmentName', $page, "the list never reached the seeded post:\n" . $page);
		$this->assertMarkupIsText($page);
	}

	/**
	 * @param array $attachments
	 * @param array $parm
	 * @return string
	 */
	private function renderAttachments(array $attachments, array $parm = array())
	{
		$this->sc->postInfo = array(
			'post_id'          => 1,
			'post_user'        => 0,
			'post_attachments' => e107::serialize($attachments),
		);

		return (string) $this->sc->sc_attachments($parm);
	}

	/**
	 * @param array $attachments
	 */
	private function seedPost(array $attachments)
	{
		if(!e107::getDb()->gen("SHOW TABLES LIKE '" . MPREFIX . "forum_post'"))
		{
			e107::getPlugin()->install('forum');
			$this->forumInstalled = true;
		}

		$this->postId = (int) e107::getDb()->insert('forum_post', array(
			'post_entry'       => 'forumAttachmentNameTest',
			'post_thread'      => 0,
			'post_forum'       => 0,
			'post_user'        => 0,
			'post_datestamp'   => time() + 3600,
			'post_attachments' => e107::serialize($attachments),
		));

		self::assertGreaterThan(0, $this->postId, 'the post was not seeded');
	}

	/**
	 * @param string $page
	 */
	private function assertMarkupIsText($page)
	{
		self::assertStringNotContainsString(self::MARKUP, $page, "a stored attachment reached the page as markup:\n" . $page);
	}
}
