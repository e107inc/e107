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
 * Regression coverage for issue #5777: the newsfeed admin help string
 * NFLAN_42 ended with a "Tip" block pointing at DMOZ (directory shut down in
 * 2017) and Feedster (defunct), alongside the typos "direcotries" and
 * "immage". The dead links and typos must stay gone. NFLAN_42 itself must
 * remain defined because e107_plugins/newsfeed/e_help.php renders it.
 */
class NewsfeedHelpLanTest extends \Codeception\Test\Unit
{
	/** @var array */
	protected $lan;

	protected function _before()
	{
		$this->lan = include "e107_plugins/newsfeed/languages/English_admin.php";
	}

	public function testHelpConstantStillDefined()
	{
		$this->assertArrayHasKey('NFLAN_42', $this->lan);
		$this->assertNotEmpty($this->lan['NFLAN_42']);
	}

	public function testNoDeadFeedDirectoryLinks()
	{
		$help = $this->lan['NFLAN_42'];
		$this->assertStringNotContainsString('dmoz', $help);
		$this->assertStringNotContainsString('feedster', $help);
	}

	public function testTyposFixed()
	{
		$help = $this->lan['NFLAN_42'];
		$this->assertStringNotContainsString('direcotries', $help);
		$this->assertStringNotContainsString('immage', $help);
	}
}
