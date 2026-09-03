<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

class bb_youtubeTest extends \Test\Unit
{
	/** @var bb_youtube */
	protected $bb;

	/** @var bool */
	protected $libxml;

	protected function _before()
	{
		require_once(e_CORE.'bbcodes/bb_youtube.php');

		$this->libxml = libxml_use_internal_errors(false);

		try
		{
			$this->bb = $this->make('bb_youtube');
		}
		catch(Exception $e)
		{
			self::fail("Couldn't load bb_youtube object");
		}
	}

	protected function _after()
	{
		libxml_use_internal_errors($this->libxml);
	}

	public function testToDbTurnsAPastedPlayerIntoYoutubeBbcode()
	{
		$paste = '<object width="560" height="340">'
			.'<param name="movie" value="https://www.youtube.com/v/abc?hl=en&amp;autoplay=0"></param>'
			.'<embed src="https://www.youtube.com/v/abc?hl=en&amp;autoplay=0" type="application/x-shockwave-flash"'
			.' allowfullscreen="true" width="560" height="340"></embed>'
			.'</object>';

		self::assertSame(
			'[youtube=560,340]abc?hd=0&hl=en&color1=&color2=&cc_load_policy=0[/youtube]',
			$this->bb->toDB($paste, '')
		);
	}

	public function testToDbKeepsThePrivacyOfAPlayerPastedFromYoutubeNocookie()
	{
		$paste = '<object width="560" height="340">'
			.'<embed src="https://www.youtube-nocookie.com/v/abc?hl=en&amp;autoplay=0" width="560" height="340"></embed>'
			.'</object>';

		self::assertSame(
			'[youtube=560,340|privacy]abc?hd=0&hl=en&color1=&color2=&cc_load_policy=0[/youtube]',
			$this->bb->toDB($paste, '')
		);
	}

	public function testToDbReadsAPlayerWhoseSourceCarriesNoQueryString()
	{
		$paste = '<object width="560" height="340">'
			.'<embed src="https://www.youtube.com/v/abc" width="560" height="340"></embed>'
			.'</object>';

		self::assertStringStartsWith(
			'[youtube=560,340]abc?hd=0&hl=&color1=&color2=&cc_load_policy=0',
			$this->bb->toDB($paste, '')
		);
	}

	public function testToDbStoresThePasteItCannotReadAndWritesNothingToTheResponse()
	{
		$paste = '<iframe src="https://www.youtube.com/embed/abc"></iframe>';

		ob_start();
		$bbcode = $this->bb->toDB($paste, '');
		$stored = e107::getParser()->toDB('[b]x[/b][youtube]'.$paste.'[/youtube]');
		$echoed = ob_get_clean();

		self::assertSame('', $echoed);
		self::assertSame(
			'[sanitised]2B&lt;iframe src=&quot;https://www.youtube.com/embed/abc&quot;&gt;&lt;/iframe&gt;B[/sanitised]',
			$bbcode
		);
		self::assertSame(
			'[b]x[/b][sanitised]2B&lt;iframe src=&quot;https://www.youtube.com/embed/abc&quot;&gt;&lt;/iframe&gt;B[/sanitised]',
			$stored
		);
	}

	public function testToDbStoresThePasteTheXmlParserRejects()
	{
		$share = '<iframe width="560" height="315" src="https://www.youtube.com/embed/abc?si=xyz"'
			.' title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write"'
			.' referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>';
		$watch = '<iframe src="https://www.youtube.com/watch?v=abc&amp;t=5"></iframe>';

		ob_start();
		$shared = $this->bb->toDB($share, '');
		$watched = $this->bb->toDB($watch, '');
		$echoed = ob_get_clean();

		self::assertSame('', $echoed);
		self::assertSame('[sanitised]1B'.htmlspecialchars($share).'B[/sanitised]', $shared);
		self::assertSame('[sanitised]1B'.htmlspecialchars($watch).'B[/sanitised]', $watched);
	}
}
