<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * Regression coverage for issue #5785: the news archive shortcodes
 * {ARCHIVE_LINK} and {ARCHIVE_CATEGORY} concatenated news_title / category_name
 * straight into markup and hard-coded the deprecated news.php?item.X URL. Every
 * sibling news shortcode instead renders the title through
 * e107::getParser()->toHTML(..., 'TITLE') and builds the href through
 * e107::getUrl()->create('news/view/item', ...). This locks the archive
 * shortcodes to that same parser + router idiom.
 */
class news_archive_shortcodesTest extends \Codeception\Test\Unit
{
	/** @var news_archive_shortcodes */
	protected $sc;

	/** @var array */
	protected $row;

	/** @var string */
	protected $hostileTitle = '<b>x</b><img src=x onerror=alert(1)>"\'';

	/** @var string */
	protected $hostileCategory = '<img src=x onerror=alert(2)>"\'';

	protected function _before()
	{
		$this->row = array(
			'news_id'       => 4242,
			'news_title'    => $this->hostileTitle,
			'news_sef'      => 'hostile-news',
			'news_category' => 7,
			'category_id'   => 7,
			'category_name' => $this->hostileCategory,
			'category_sef'  => 'hostile-cat',
			'user_id'       => 1,
			'user_name'     => 'admin',
		);

		$this->sc = e107::getScBatch('news_archive');
		$this->sc->setVars($this->row);
	}

	public function testArchiveLinkRoutesUrlAndRunsTitleThroughParser()
	{
		$tp     = e107::getParser();
		$result = $this->sc->sc_archive_link();

		$expectedUrl   = e107::getUrl()->create('news/view/item', $this->row);
		$expectedTitle = $tp->toHTML($this->hostileTitle, TRUE, 'TITLE');

		// The anchor is the sibling idiom exactly: routed href + parsed title.
		self::assertEquals("<a href='" . $expectedUrl . "'>" . $expectedTitle . "</a>", $result);

		// The deprecated hard-coded legacy form is gone; the href is routed.
		self::assertStringNotContainsString('news.php?item.', $result);
		self::assertStringContainsString($expectedUrl, $result);

		// The parser actually ran (raw event-handler no longer intact).
		self::assertStringNotContainsString('onerror=alert', $result);
	}

	public function testArchiveCategoryRunsThroughParser()
	{
		$tp     = e107::getParser();
		$result = $this->sc->sc_archive_category();

		// Matches the sibling sc_newscategory() parser call.
		self::assertEquals($tp->toHTML($this->hostileCategory, FALSE, 'defs'), $result);

		// No longer returned raw, and the event-handler is neutralised.
		self::assertNotEquals($this->hostileCategory, $result);
		self::assertStringNotContainsString('onerror=alert', $result);
	}

	public function testArchiveCategoryEmptyReturnsEmptyString()
	{
		$this->sc->setVars(array('category_name' => ''));
		self::assertSame('', $this->sc->sc_archive_category());
	}
}
