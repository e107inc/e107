<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * A sitelink set to "all but <class>" stores the negative class id. The two
 * link readers in sitelinks_class.php honoured it with a query of their own,
 * and the sublinks shortcode reading the same table did not, so a child link
 * hidden from one class was hidden from everybody. All three now read the
 * table through the same predicate. See issue #6282.
 */
class sitelinksVisibilityTest extends \Test\Unit
{
	const CATEGORY = 97;
	const PAGE = 'uc_probe_parent.php';

	/** @var int a userclass the runtime user is not a member of */
	private $absentClass = 42;

	/** @var int the parent link the sublinks shortcode hangs its children under */
	private $parentId;

	protected function _before()
	{
		require_once(e_HANDLER.'sitelinks_class.php');
		require_once(e_CORE.'shortcodes/single/sublinks.php');
		$GLOBALS['tp'] = e107::getParser();

		$this->assertNotContains((string) $this->absentClass, explode(',', USERCLASS_LIST), 'This test needs a userclass the runtime user is outside of.');

		$this->removeProbeLinks();
		$this->parentId = $this->addLink('uc_probe_parent', 0, self::PAGE);
		$this->addLink('uc_probe_public', 0);
		$this->addLink('uc_probe_restricted', $this->absentClass);
		$this->addLink('uc_probe_inverted', -$this->absentClass);
		$this->addLink('uc_probe_child_restricted', $this->absentClass, 'child1.php', $this->parentId);
		$this->addLink('uc_probe_child_inverted', -$this->absentClass, 'child2.php', $this->parentId);
	}

	protected function _after()
	{
		unset($GLOBALS['tp']);
		$this->removeProbeLinks();
	}

	public function testGetlinksKeepsALinkHiddenFromAnotherClass()
	{
		$sitelinks = new sitelinks();
		$sitelinks->getlinks(self::CATEGORY);
		$names = array_column($sitelinks->getLinkArray()['head_menu'], 'link_name');

		$this->assertContains('uc_probe_public', $names);
		$this->assertContains('uc_probe_inverted', $names, 'A link hidden from one class is shown to everybody else.');
		$this->assertNotContains('uc_probe_restricted', $names, 'A link limited to a class the user is outside of stays hidden.');
	}

	public function testNavigationDataKeepsALinkHiddenFromAnotherClass()
	{
		$navigation = new e_navigation();
		$names = array_column($navigation->initData(self::CATEGORY, array('flat' => true)), 'link_name');

		$this->assertContains('uc_probe_inverted', $names);
		$this->assertNotContains('uc_probe_restricted', $names);
	}

	public function testTheSublinksShortcodeKeepsAChildHiddenFromAnotherClass()
	{
		$html = sublinks_shortcode(self::PAGE.':'.self::CATEGORY);

		$this->assertStringContainsString('uc_probe_child_inverted', $html, 'A child link hidden from one class is shown to everybody else.');
		$this->assertStringNotContainsString('uc_probe_child_restricted', $html);
	}

	/**
	 * @param string $name
	 * @param int $class
	 * @param string $url
	 * @param int $parent
	 * @return int link_id
	 */
	private function addLink($name, $class, $url = 'index.php', $parent = 0)
	{
		return (int) e107::getDb()->insert('links', array(
			'link_name'        => $name,
			'link_url'         => $url,
			'link_description' => '',
			'link_button'      => '',
			'link_category'    => self::CATEGORY,
			'link_order'       => 1,
			'link_parent'      => $parent,
			'link_open'        => 0,
			'link_class'       => $class,
		));
	}

	private function removeProbeLinks()
	{
		e107::getDb()->delete('links', 'link_category = '.self::CATEGORY);
	}
}
