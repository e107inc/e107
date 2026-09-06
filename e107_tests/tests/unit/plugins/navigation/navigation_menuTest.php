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
 * navigation_menu.php hands whatever {@see navigation_shortcode()} produced to
 * {@see e_render::tablerender()}. An empty link category is an empty string by
 * design in {@see e_navigation::render()}, so the caption used to be painted
 * over nothing at all.
 */
class navigation_menuTest extends \Test\Unit
{
	const EMPTY_CATEGORY = 6;

	const MARKER = 'navigation_menuTest link';

	protected function _before()
	{
		e107::plugLan('navigation', 'global', true);
		$this->removeSeededLinks();
	}

	protected function _after()
	{
		$this->removeSeededLinks();
	}

	public function testAnEmptyLinkCategoryRendersNothingAtAll()
	{
		$this->assertSame(
			0,
			$this->countLinksInEmptyCategory(),
			'Fixture no longer leaves link category '.self::EMPTY_CATEGORY.' empty'
		);

		$this->assertSame('', $this->renderMenu(array('type' => 'alt6')));
	}

	public function testALinkCategoryWithLinksStillRendersCaptionAndLinks()
	{
		$this->seedLink();

		$rendered = $this->renderMenu(array('type' => 'alt6'));

		$this->assertStringContainsString(self::MARKER, $rendered);
		$this->assertStringContainsString(LAN_PLUGIN_NAVIGATION_NAME, $rendered);
	}

	public function testAnEmptyLinkCategoryRendersNothingEvenWithACaptionOfItsOwn()
	{
		$parm = array('type' => 'alt6', 'caption' => array(e_LANGUAGE => 'My Sidebar'));

		$this->assertSame('', $this->renderMenu($parm));
	}

	private function renderMenu($parm)
	{
		ob_start();
		include(e_PLUGIN.'navigation/navigation_menu.php');

		return ob_get_clean();
	}

	private function seedLink()
	{
		e107::getDb()->createQueryBuilder()->insert('links')->values(array(
			'link_name'     => self::MARKER,
			'link_url'      => 'news.php',
			'link_category' => self::EMPTY_CATEGORY,
			'link_order'    => 1,
			'link_class'    => 0,
		))->execute();
	}

	private function removeSeededLinks()
	{
		e107::getDb()->createQueryBuilder()->delete('links')->where('link_name', self::MARKER)->execute();
	}

	private function countLinksInEmptyCategory()
	{
		return (int) e107::getDb()->createQueryBuilder()->selectCount()->from('links')
			->where('link_category', self::EMPTY_CATEGORY)->fetchOne();
	}
}
