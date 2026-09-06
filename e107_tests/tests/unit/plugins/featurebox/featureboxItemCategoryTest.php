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
 * Regression coverage for issue #5958: a featurebox item template could not
 * print its own category, because {@see plugin_featurebox_item} offered the
 * category shortcodes only through __call(), and
 * {@see e_parse_shortcode::doCode()} gates batch methods with method_exists(),
 * which is false for a method that exists only through __call().
 */
class featureboxItemCategoryTest extends \Test\Unit
{
	/** @var plugin_featurebox_category */
	protected $category;

	/** @var plugin_featurebox_item */
	protected $item;

	/** @var string */
	protected $categoryTitle = 'Featured "picks" & finds';

	/** @var string */
	protected $categoryIcon = '{e_PLUGIN}featurebox/images/featurebox_32.png';

	protected function _before()
	{
		$this->category = new plugin_featurebox_category();
		$this->category->setData(array(
			'fb_category_id'    => 7,
			'fb_category_title' => $this->categoryTitle,
			'fb_category_icon'  => $this->categoryIcon,
			'fb_category_limit' => 3,
		));

		$this->item = new plugin_featurebox_item();
		$this->item->setData(array(
			'fb_id'       => 42,
			'fb_title'    => 'An item',
			'fb_category' => 7,
		));
		$this->item->setCategory($this->category);
	}

	/**
	 * The mechanism the fix turns on: the shortcode handler asks
	 * method_exists() before it will call a batch method, so the forwarders
	 * have to be real declared methods rather than __call() magic.
	 */
	public function testTheItemBatchDeclaresTheCategoryForwardersAsRealMethods()
	{
		self::assertTrue(
			method_exists('plugin_featurebox_item', 'sc_featurebox_category_title'),
			'method_exists() is the gate in e_parse_shortcode::doCode(); a __call() forwarder never passes it.'
		);
		self::assertTrue(
			method_exists('plugin_featurebox_item', 'sc_featurebox_category_icon'),
			'method_exists() is the gate in e_parse_shortcode::doCode(); a __call() forwarder never passes it.'
		);
	}

	public function testCategoryTitleResolvesInAnItemTemplate()
	{
		$rendered = $this->item->toHTML('{FEATUREBOX_CATEGORY_TITLE}');

		self::assertNotSame('', $rendered);
		self::assertStringContainsString('finds', $rendered);
		self::assertSame($this->category->toHTML('{FEATUREBOX_CATEGORY_TITLE}'), $rendered);
	}

	public function testCategoryTitleParmIsForwarded()
	{
		$plain = $this->item->toHTML('{FEATUREBOX_CATEGORY_TITLE}');
		$alt   = $this->item->toHTML('{FEATUREBOX_CATEGORY_TITLE=alt}');

		self::assertNotSame('', $alt);
		self::assertNotSame($plain, $alt);
		self::assertSame($this->category->toHTML('{FEATUREBOX_CATEGORY_TITLE=alt}'), $alt);
		self::assertStringContainsString('&quot;picks&quot;', $alt);
	}

	public function testCategoryIconResolvesInAnItemTemplate()
	{
		$rendered = $this->item->toHTML('{FEATUREBOX_CATEGORY_ICON}');

		self::assertStringContainsString('<img ', $rendered);
		self::assertStringContainsString('featurebox_32.png', $rendered);
		self::assertSame($this->category->toHTML('{FEATUREBOX_CATEGORY_ICON}'), $rendered);
	}

	public function testCategoryIconParmIsForwarded()
	{
		$src = $this->item->toHTML('{FEATUREBOX_CATEGORY_ICON=src}');

		self::assertStringNotContainsString('<img', $src);
		self::assertStringContainsString('featurebox_32.png', $src);
		self::assertSame($this->category->toHTML('{FEATUREBOX_CATEGORY_ICON=src}'), $src);
	}

	/**
	 * Control: the item's own shortcodes are unaffected, so a red run here
	 * means the suite broke rather than the forwarders.
	 */
	public function testTheItemsOwnShortcodesStillResolve()
	{
		self::assertStringContainsString('An item', $this->item->toHTML('{FEATUREBOX_TITLE}'));
	}

	/**
	 * Control: only title and icon were exposed, so the rest still render
	 * empty in an item template. That is the status quo and not a guarantee;
	 * the 'dynamic' navigation template needs several of them and wants its
	 * own fix.
	 */
	public function testNothingBeyondTitleAndIconIsExposedYet()
	{
		self::assertSame('', $this->item->toHTML('{FEATUREBOX_NAV_COUNTER}'));
		self::assertSame('', $this->item->toHTML('{FEATUREBOX_NAV_ACTIVE}'));
		self::assertSame('', $this->item->toHTML('{FEATUREBOX_CATEGORY_LIMIT}'));
	}

	/**
	 * BC control: an item with no category still renders empty rather than
	 * fataling, which is what those tokens did before the forwarders existed.
	 */
	public function testAnItemWithoutACategoryRendersEmpty()
	{
		$orphan = new plugin_featurebox_item();
		$orphan->setData(array('fb_id' => 43, 'fb_title' => 'Orphan'));

		self::assertSame('', $orphan->toHTML('{FEATUREBOX_CATEGORY_TITLE}'));
		self::assertSame('', $orphan->toHTML('{FEATUREBOX_CATEGORY_TITLE=alt}'));
		self::assertSame('', $orphan->toHTML('{FEATUREBOX_CATEGORY_ICON}'));
		self::assertSame('', $orphan->toHTML('{FEATUREBOX_CATEGORY_ICON=src}'));
	}
}
