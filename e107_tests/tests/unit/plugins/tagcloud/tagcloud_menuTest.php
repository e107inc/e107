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
 */
class tagcloud_menuTest extends \Test\Unit
{
	/**
	 * The menu reads its cloud back from the content cache only while that cache is on, so it writes the cloud there only then, and renders the same cloud either way.
	 */
	public function testTheCloudIsCachedOnlyWhileTheContentCacheIsOn()
	{
		$php = <<<'PHP'
ob_start();
require_once(e_PLUGIN.'tagcloud/tagcloud_menu.php');
ob_end_clean();
$cache = e107::getCache();
$menu = new tagcloud_menu();
$report = array();
foreach(array('off' => false, 'on' => true) as $state => $active)
{
	$cache->UserCacheActive = $active;
	$cache->clear('tagcloud');
	$report[$state] = $menu->render();
	$report[$state.' entry'] = $cache->retrieve('tagcloud', false, true);
	$report[$state.' again'] = $menu->render();
}
$cache->clear('tagcloud');
echo "\n@@CLOUD=", json_encode($report), "@@\n";
PHP;

		list($output, $status) = $this->bootPluginInCli('tagcloud', '1.4', array(), $php);

		$printed = implode("\n", $output);

		self::assertSame(0, $status, "the menu never returned:\n".$printed);
		self::assertSame(1, preg_match('/@@CLOUD=(.*)@@/', $printed, $match), "the child printed no cloud:\n".$printed);

		$report = json_decode($match[1], true);

		self::assertStringContainsString('welcome', $report['off'], "the sample news item's keywords should make the cloud");
		self::assertFalse($report['off entry'], 'with the content cache off nothing reads the entry back, so none should be written');
		self::assertSame($report['on'], $report['on entry'], 'with the content cache on the cloud rendered should be the entry written');
		self::assertSame($report['off'], $report['off again']);
		self::assertSame($report['off'], $report['on']);
		self::assertSame($report['on'], $report['on again']);
	}
}
