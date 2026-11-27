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
	use \Test\PageRows;

	const PREFIX = 'related_probe_';

	const TAG = 'related_probe_tag';

	protected function _before()
	{
		require_once(e_PLUGIN.'page/includes/pageHelper.php');
		require_once(e_PLUGIN.'page/e_related.php');
	}

	protected function _after()
	{
		$this->removePages();
	}

	public function testListingPagesAddsNoPropertyToTheAddon()
	{
		$this->addPage('plain');
		$addon = new page_related();

		$this->assertNotEmpty($addon->compile(self::TAG, array('current' => 0, 'limit' => 5)), 'the probe page has to be listed');
		$this->assertSame(array(), get_object_vars($addon));
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
