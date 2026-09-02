<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * Covers the window where a forum template is included and the icon constants it reads are not defined yet.
 *
 * @group plugins
 */
class forum_templateIconsTest extends \Codeception\Test\Unit
{

	use \Test\BootedCli;

	const INCLUDED = 'PROBE-INCLUDED';

	const ICONS = 'PROBE-ICONS';

	const COMPLAINTS = 'PROBE-COMPLAINTS';

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

}
