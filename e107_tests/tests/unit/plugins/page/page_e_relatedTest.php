<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

class page_e_relatedTest extends \Test\Unit
{
	use \Test\CoreColumns;
	use \Test\PageRows;

	const PREFIX = 'related_probe_';

	const TAG = 'related_probe_tag';

	protected function _before()
	{
		require_once(e_PLUGIN.'page/includes/pageHelper.php');
		require_once(e_PLUGIN.'page/e_related.php');

		$this->haveCoreColumns('page', array('page_metaimage'));
	}

	protected function _after()
	{
		$this->removePages();
		$this->dropCoreColumns();
	}

	public function testAPageWithNoMenuImageOffersItsMetaImage()
	{
		$this->addPage('meta_only', array('menu_image' => '', 'page_metaimage' => '{e_MEDIA_IMAGE}2026-11/meta.jpg'));

		$this->assertSame('{e_MEDIA_IMAGE}2026-11/meta.jpg', $this->relatedImage('meta_only'));
	}

	public function testAMenuImageStillComesBeforeTheMetaImage()
	{
		$this->addPage('both', array('menu_image' => '{e_MEDIA_IMAGE}2026-11/menu.jpg', 'page_metaimage' => '{e_MEDIA_IMAGE}2026-11/meta.jpg'));

		$this->assertSame('{e_MEDIA_IMAGE}2026-11/menu.jpg', $this->relatedImage('both'));
	}

	public function testAVideoMenuImageGivesWayToTheMetaImage()
	{
		$this->addPage('video', array('menu_image' => 'abcdefghijk.youtube', 'page_metaimage' => '{e_MEDIA_IMAGE}2026-11/meta.jpg'));

		$this->assertSame('{e_MEDIA_IMAGE}2026-11/meta.jpg', $this->relatedImage('video'));
	}

	public function testAPageWithNeitherImageOffersNone()
	{
		$this->addPage('none', array('menu_image' => '', 'page_metaimage' => ''));

		$this->assertSame('', $this->relatedImage('none'));
	}

	public function testListingPagesAddsNoPropertyToTheAddon()
	{
		$this->addPage('plain');
		$addon = new page_related();

		$this->assertNotEmpty($addon->compile(self::TAG, array('current' => 0, 'limit' => 5)), 'the probe page has to be listed');
		$this->assertSame(array(), get_object_vars($addon));
	}

	/**
	 * @param string $name
	 * @return string the image of the related item titled with $name
	 */
	private function relatedImage($name)
	{
		$items = (new page_related())->compile(self::TAG, array('current' => 0, 'limit' => 5));
		$titles = array_column($items, 'image', 'title');

		$this->assertArrayHasKey(self::PREFIX.$name, $titles);

		return $titles[self::PREFIX.$name];
	}

	/**
	 * Plants a page tagged so that the related list finds it.
	 *
	 * @param string $name
	 * @param array $columns page columns besides the tag
	 * @return void
	 */
	private function addPage($name, array $columns = array())
	{
		$this->havePage(self::PREFIX.$name, array_merge(array('page_metakeys' => self::TAG), $columns));
	}
}
