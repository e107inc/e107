<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * Covers the forum's icon constants: the window where a template reads one that is not defined yet,
 * and the loader that fills in whatever a theme's own icons template left out.
 *
 * @group plugins
 */
class forum_templateIconsTest extends \Codeception\Test\Unit
{

	use \Test\BootedCli;

	const INCLUDED = 'PROBE-INCLUDED';

	const ICONS = 'PROBE-ICONS';

	const COMPLAINTS = 'PROBE-COMPLAINTS';

	const KEPT = 'PROBE-KEPT';

	const FILLED = 'PROBE-FILLED';

	const FIXTURES = 'PROBE-FIXTURES';

	const SOURCE = 'PROBE-SOURCE';

	/**
	 * @return array every forum layout id, the icons template itself excluded
	 */
	private function forumLayoutIds()
	{
		$ids = array();

		foreach(glob(APP_PATH.'/e107_plugins/forum/templates/*_template.php') as $file)
		{
			$id = basename($file, '_template.php');

			if($id !== 'forum_icons')
			{
				$ids[] = $id;
			}
		}

		return $ids;
	}

	/**
	 * @param array $output
	 * @param string $prefix
	 * @return string what the probe reported under that prefix
	 */
	private function reported(array $output, $prefix)
	{
		foreach($output as $line)
		{
			$at = strpos($line, $prefix);

			if($at !== false)
			{
				return trim((string) substr($line, $at + strlen($prefix)));
			}
		}

		self::fail('the probe never reported '.trim($prefix).":\n".implode("\n", $output));
	}

	/**
	 * Shuffle puts the forum icons in the constant table for half of this suite's runs, so the probe is a subprocess.
	 */
	public function testListingForumLayoutsRaisesNothingAboutTheIconConstants()
	{
		$ids = $this->forumLayoutIds();
		self::assertNotEmpty($ids, 'there are no forum templates to enumerate');

		$probe = <<<'PHP'
$raised = array();
set_error_handler(function($no, $str) use (&$raised) { $raised[] = $str; return true; });
foreach($ids as $id)
{
	try { e107::getLayouts('forum', $id); }
	catch (Throwable $e) { $raised[] = get_class($e).': '.$e->getMessage(); }
}
restore_error_handler();
$seen = array();
foreach(get_included_files() as $file)
{
	if(strpos($file, '/forum/templates/') !== false || strpos($file, '/templates/forum/') !== false) { $seen[] = basename($file); }
}
$icons = array();
foreach($raised as $one)
{
	if(stripos($one, 'undefined constant') !== false && strpos($one, 'IMAGE_') !== false) { $icons[] = $one; }
}
echo "\nPROBE-INCLUDED ".implode(',', $seen)."\n";
echo "PROBE-ICONS ".(defined('IMAGE_e') || defined('IMAGE_post2') ? 'loaded' : 'absent')."\n";
echo "PROBE-COMPLAINTS ".implode(' ~ ', $icons)."\n";
PHP;

		list($output, ) = $this->runInBootedCli('$ids = '.var_export($ids, true).';'."\n".$probe);

		$included = explode(',', $this->reported($output, self::INCLUDED));

		foreach($ids as $id)
		{
			self::assertContains($id.'_template.php', $included,
				$id.'_template.php was never included, so this probe proves nothing about it');
		}

		self::assertSame('absent', $this->reported($output, self::ICONS),
			'the icon constants were already defined, so this probe proves nothing');

		self::assertSame('', $this->reported($output, self::COMPLAINTS),
			'listing the forum layouts complained about an icon constant');
	}

	/**
	 * A theme icons template that sets one constant and stops leaves the rest of them to the plugin's copy.
	 */
	public function testAThemeIconSetIsFilledOutFromThePluginsWithoutBeingOverwritten()
	{
		$probe = <<<'PHP'
$fixtures = array(
	THEME.'templates/forum/forum_icons_template.php' => "<?php\ndefine('IMAGE_new', 'PROBE-THEME-ICON');\ndefine('PROBE_FROM', 'preferred');\n",
	THEME.'forum/forum_icons_template.php'           => "<?php\ndefine('IMAGE_new', 'PROBE-WRONG-ICON');\ndefine('PROBE_FROM', 'fallback');\n",
);
$made = array();
foreach($fixtures as $path => $body)
{
	$dir = dirname($path);
	if(!is_dir($dir) && mkdir($dir, 0755, true)) { $made[] = $dir; }
	file_put_contents($path, $body);
}
register_shutdown_function(function() use ($fixtures, $made) {
	foreach(array_keys($fixtures) as $path) { @unlink($path); }
	foreach(array_reverse($made) as $dir) { @rmdir($dir); }
});
$raised = array();
set_error_handler(function($no, $str) use (&$raised) { $raised[] = $str; return true; });
require_once(e_PLUGIN.'forum/forum_class.php');
restore_error_handler();
$clashes = array();
foreach($raised as $one)
{
	if(stripos($one, 'already defined') !== false) { $clashes[] = $one; }
}
$missing = array();
foreach(array('IMAGE_e', 'IMAGE_post2', 'IMAGE_track', 'IMAGE_admin_lock', 'IMAGE_rank_admin_image') as $one)
{
	if(!defined($one)) { $missing[] = $one; }
}
echo "\nPROBE-FIXTURES ".implode('|', array_keys($fixtures))."\n";
echo "PROBE-SOURCE ".defset('PROBE_FROM')."\n";
echo "PROBE-KEPT ".defset('IMAGE_new')."\n";
echo "PROBE-FILLED ".implode(',', $missing)."\n";
echo "PROBE-COMPLAINTS ".implode(' ~ ', $clashes)."\n";
PHP;

		list($output, ) = $this->runInBootedCli($probe);

		$fixtures = explode('|', $this->reported($output, self::FIXTURES));

		foreach($fixtures as $fixture)
		{
			self::assertFileDoesNotExist($fixture, 'the probe left a theme icons template behind');
		}

		self::assertSame('preferred', $this->reported($output, self::SOURCE),
			'the loader must read the first theme location it finds and stop there');

		self::assertSame('PROBE-THEME-ICON', $this->reported($output, self::KEPT),
			'the plugin overwrote an icon constant the theme had already set');

		self::assertSame('', $this->reported($output, self::FILLED),
			'an icon constant the theme left out was still undefined after the forum loaded');

		self::assertSame('', $this->reported($output, self::COMPLAINTS),
			'loading the forum redefined an icon constant that was already set');
	}

}
