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
 * The topic page offers an svg attachment through the forum's download route, whichever key the post filed it under.
 */
class forumSvgAttachmentTest extends \Test\Unit
{
	/** @var plugin_forum_view_shortcodes */
	private $sc;

	/** @var string a poster's attachment directory */
	private $dir;

	/** @var array the files the attachments below name, each present in $dir */
	private $files = array('photo.jpg', 'stored-drawing.svg', '1690000000_0_legacy.svg', 'STORED-UPPER.SVG');

	protected function _before()
	{
		e107::lan('forum', 'front', true);

		require_once(e_PLUGIN . 'forum/shortcodes/batch/view_shortcodes.php');
		require_once(e_PLUGIN . 'forum/forum_class.php');

		$this->dir = e_SYSTEM . 'forumSvgAttachmentTest/';

		if(!is_dir($this->dir))
		{
			mkdir($this->dir, 0755, true);
		}

		foreach($this->files as $file)
		{
			file_put_contents($this->dir . $file, 'forumSvgAttachmentTest');
		}

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
		foreach($this->files as $file)
		{
			@unlink($this->dir . $file);
		}

		@rmdir($this->dir);
	}

	/**
	 * The download route numbers an svg after the post's other files, in both shapes an entry is stored in, and a picture beside it is still shown.
	 */
	public function testAnSvgFiledAsAnImageIsLinkedThroughTheDownloadRoute()
	{
		$page = $this->renderAttachments(array(
			'img'  => array(
				array('file' => 'photo.jpg', 'name' => 'photo.jpg', 'size' => 1),
				array('file' => 'stored-drawing.svg', 'name' => 'drawing.svg', 'size' => 1),
				'1690000000_0_legacy.svg',
			),
			'file' => array(
				array('file' => 'a.txt', 'name' => 'a.txt', 'size' => 1),
			),
		));

		self::assertStringNotContainsString('stored-drawing.svg', $page, "an svg was linked by its own path:\n" . $page);
		self::assertStringNotContainsString('1690000000_0_legacy.svg', $page, "an svg was linked by its own path:\n" . $page);
		self::assertSame(1, substr_count($page, 'forum-attachment-image'), "only the picture is shown as an image:\n" . $page);

		$this->assertDownloadLink($page, 0, 'a.txt');
		$this->assertDownloadLink($page, 1, 'drawing.svg');
		$this->assertDownloadLink($page, 2, 'legacy.svg');
	}

	public function testAnSvgIsOfferedAsADownloadWhateverTheCaseOfItsExtension()
	{
		$page = $this->renderAttachments(array('img' => array(
			array('file' => 'STORED-UPPER.SVG', 'name' => 'UPPER.SVG', 'size' => 1),
		)));

		self::assertStringNotContainsString('forum-attachment-image', $page, "the svg is shown as an image:\n" . $page);

		$this->assertDownloadLink($page, 0, 'UPPER.SVG');
	}

	public function testAnSvgIsOfferedBesideAFileHeldUnderTheLastIndexThereIs()
	{
		$page = $this->renderAttachments(array(
			'file' => array(PHP_INT_MAX => array('file' => 'a.txt', 'name' => 'a.txt', 'size' => 1)),
			'img'  => array(array('file' => 'stored-drawing.svg', 'name' => 'drawing.svg', 'size' => 1)),
		));

		self::assertStringNotContainsString('forum-attachment-image', $page, "the svg is shown as an image:\n" . $page);

		$this->assertDownloadLink($page, 0, 'a.txt');
		$this->assertDownloadLink($page, 1, 'drawing.svg');
	}

	/**
	 * @param array $attachments
	 * @return string
	 */
	private function renderAttachments(array $attachments)
	{
		$this->sc->postInfo = array(
			'post_id'          => 1,
			'post_user'        => 0,
			'post_attachments' => e107::serialize($attachments),
		);

		return (string) $this->sc->sc_attachments(array());
	}

	/**
	 * @param string $page
	 * @param int $key
	 * @param string $name
	 */
	private function assertDownloadLink($page, $key, $name)
	{
		$link = "#href='[^']*\\?id=1&amp;dl=" . $key . "'[^>]*>(?:(?!</a>).)*" . preg_quote($name, '#') . '</a>#s';

		self::assertSame(1, preg_match($link, $page), $name . " is not offered through the download route as dl=" . $key . ":\n" . $page);
	}
}
