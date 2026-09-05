<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

/** The constants a file that resolves a template may not pick its shape from (#6017, RFC #5909). */
class TemplateShapeConventionsTest extends \Codeception\Test\Unit
{
	/** The trees a template-shape decision can be taken in; e107_tests and anything vendored is somebody else's. */
	private static $trees = array('e107_admin', 'e107_core', 'e107_handlers', 'e107_plugins', 'e107_themes');

	/** What makes the rule a file's business: it asks a loader for a template, or it names a template file. */
	private static $markers = array('getCoreTemplate', 'getTemplate', 'coreTemplatePath', 'templatePath');

	/** The files issue #6017 is about, none of which may name THEME_LEGACY at all. */
	private static $reworked = array(
		'fpw.php',
		'login.php',
		'membersonly.php',
		'search.php',
		'signup.php',
		'user.php',
		'usersettings.php',
		'e107_core/shortcodes/batch/user_shortcodes.php',
		'e107_core/shortcodes/batch/usersettings_shortcodes.php',
		'e107_core/shortcodes/single/search.php',
		'e107_plugins/online/lastseen_menu.php',
		'e107_plugins/online/online_menu.php',
	);

	/** How often a scanned file may still name BOOTSTRAP and THEME_LEGACY, in that order; one the scan reaches and this table does not name may name neither. */
	private static $allowance = array(
		'e107_admin/header.php'                                  => array(1, 0),
		'e107_core/shortcodes/batch/admin_shortcodes.php'        => array(1, 0),
		'e107_core/shortcodes/batch/news_shortcodes.php'         => array(4, 0),
		'e107_core/shortcodes/batch/page_shortcodes.php'         => array(2, 0),
		'e107_core/shortcodes/batch/user_shortcodes.php'         => array(3, 0),
		'e107_core/shortcodes/batch/usersettings_shortcodes.php' => array(2, 0),
		'e107_core/shortcodes/single/nextprev.php'               => array(2, 0),
		'e107_handlers/comment_class.php'                        => array(5, 1),
		'e107_handlers/e107_class.php'                           => array(1, 1),
		'e107_handlers/form_handler.php'                         => array(11, 2),
		'e107_plugins/banner/banner_menu.php'                    => array(1, 0),
		'e107_plugins/download/handlers/download_class.php'      => array(5, 0),
		'e107_plugins/forum/forum.php'                           => array(4, 2),
		'e107_plugins/forum/forum_class.php'                     => array(2, 0),
		'e107_plugins/forum/forum_post.php'                      => array(3, 0),
		'e107_plugins/forum/forum_viewforum.php'                 => array(2, 3),
		'e107_plugins/forum/forum_viewtopic.php'                 => array(1, 1),
		'e107_plugins/gallery/controllers/index.php'             => array(4, 0),
		'e107_plugins/gallery/gallery.php'                       => array(4, 0),
		'e107_plugins/news/news.php'                             => array(2, 4),
		'e107_plugins/news/other_news2_menu.php'                 => array(0, 1),
		'e107_plugins/news/other_news_menu.php'                  => array(0, 1),
		'e107_plugins/pm/pm.php'                                 => array(5, 8),
		'e107_plugins/pm/pm_class.php'                           => array(0, 1),
		'e107_plugins/poll/poll_class.php'                       => array(3, 0),
		'fpw.php'                                                => array(1, 0),
		'search.php'                                             => array(2, 0),
		'signup.php'                                             => array(2, 0),
		'usersettings.php'                                       => array(3, 0),
	);

	const RULE = 'A template shape is chosen from the keys getCoreTemplate() returns, never from a constant. A widget or message gate may stay until its phase of RFC #5909, and the numbers above are a ceiling: a count may fall without an edit and may never rise.';

	/** @var array|null */
	private static $counts = null;

	public function testNoReworkedFileReadsThemeLegacy()
	{
		$counts = $this->counts();

		foreach(self::$reworked as $file)
		{
			$this->assertArrayHasKey($file, $counts, $file.' is not among the files the scan reached, so the count below means nothing');
			$this->assertSame(0, $counts[$file]['THEME_LEGACY'], $file.' names THEME_LEGACY. '.self::RULE);
		}
	}

	public function testNoFileGainsAShapeRead()
	{
		foreach($this->counts() as $file => $count)
		{
			$allowed = isset(self::$allowance[$file]) ? self::$allowance[$file] : array(0, 0);

			$this->assertLessThanOrEqual($allowed[0], $count['BOOTSTRAP'],
				$file.' names BOOTSTRAP more often than the table allows. '.self::RULE);
			$this->assertLessThanOrEqual($allowed[1], $count['THEME_LEGACY'],
				$file.' names THEME_LEGACY more often than the table allows. '.self::RULE);
		}
	}

	/** The scan is the whole of the rule's reach, so a table naming a file it never visits passes on nothing. */
	public function testTheScanReachesEveryFileTheTableNames()
	{
		$counts = $this->counts();

		$this->assertGreaterThan(50, count($counts), 'the scan found almost nothing, so the assertions above are vacuous');

		foreach(array_keys(self::$allowance) as $file)
		{
			$this->assertArrayHasKey($file, $counts, $file.' is in the table and not in the scan, so its ceiling holds nothing down');
		}
	}

	/**
	 * Every file in the trees above that resolves a template, with the times it names each shape constant, counted with the tokenizer so a name in a comment does not.
	 *
	 * @return array relative path => array('BOOTSTRAP' => int, 'THEME_LEGACY' => int)
	 */
	private function counts()
	{
		if(self::$counts !== null)
		{
			return self::$counts;
		}

		self::$counts = array();

		foreach($this->sources() as $path)
		{
			$relevant = false;
			$count = array('BOOTSTRAP' => 0, 'THEME_LEGACY' => 0);

			foreach(token_get_all(file_get_contents($path)) as $token)
			{
				if(!is_array($token) || $token[0] === T_COMMENT || $token[0] === T_DOC_COMMENT)
				{
					continue;
				}

				$value = $token[0] === T_CONSTANT_ENCAPSED_STRING ? (string) substr($token[1], 1, -1) : $token[1];

				if(isset($count[$value]))
				{
					$count[$value]++;
				}

				if(!$relevant && (in_array($value, self::$markers, true) || strpos($value, '_template.php') !== false))
				{
					$relevant = true;
				}
			}

			if($relevant)
			{
				self::$counts[substr($path, strlen(e_ROOT))] = $count;
			}
		}

		ksort(self::$counts);

		return self::$counts;
	}

	/**
	 * Every PHP file the rule can reach: the pages at the installation root and the trees above it, minus anything vendored.
	 *
	 * @return string[] absolute paths
	 */
	private function sources()
	{
		$paths = glob(e_ROOT.'*.php');

		foreach(self::$trees as $tree)
		{
			if(!is_dir(e_ROOT.$tree))
			{
				continue;
			}

			$files = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator(e_ROOT.$tree, RecursiveDirectoryIterator::SKIP_DOTS));

			foreach($files as $file)
			{
				if(substr($file->getFilename(), -4) === '.php')
				{
					$paths[] = $file->getPathname();
				}
			}
		}

		$found = array();

		foreach($paths as $path)
		{
			if(strpos(str_replace('\\', '/', $path), '/vendor/') === false)
			{
				$found[] = $path;
			}
		}

		return $found;
	}
}
