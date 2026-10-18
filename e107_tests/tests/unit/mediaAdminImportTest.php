<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * Posts the Media Manager's import form through e107_admin/image.php in a booted child as the main admin.
 */
class mediaAdminImportTest extends \Codeception\Test\Unit
{
	use \Test\BootedCli;

	/** @var string PHP expression of what a staged text file holds */
	const TEXT = "'media import test'";

	/** @var string a name no other test or earlier run uses */
	private $name;

	/** @var string the month the test began in, as the Media Manager names a month folder */
	private $month;

	/** @var string[] absolute paths the child reported, removed again after each test */
	private $reported = array();

	protected function _before()
	{
		$this->name = 'media-import-test-'.substr(md5(uniqid('', true)), 0, 12).'.txt';
		$this->month = date('Y-m');
	}

	protected function _after()
	{
		putenv('QUERY_STRING');

		foreach($this->reported as $path)
		{
			if(is_file($path))
			{
				unlink($path);
			}
		}

		e107::getDb()->db_Query(array(
			'PREPARE' => 'DELETE FROM '.MPREFIX.'core_media WHERE media_url LIKE :url',
			'BIND'    => array('url' => array('value' => '%'.$this->name, 'type' => PDO::PARAM_STR)),
		), null, 'db_Delete');
	}

	public function testAListedFileIsImportedIntoTheMonthFolderUnderItsOwnName()
	{
		$where = $this->import($this->name, $this->name);

		self::assertTrue($where['month folder'], "the file did not reach the month folder of the media files:\n".$where['page']);
		self::assertSame(array($this->address('e_MEDIA_FILE', $this->month.'/'.$this->name)), $this->storedUrls());
	}

	/**
	 * @return array
	 */
	public function climbsOutOfTheMonthFolder()
	{
		return array(
			'through a slash'     => array('../'),
			'through a backslash' => array('..\\'),
		);
	}

	/**
	 * @dataProvider climbsOutOfTheMonthFolder
	 * @param string $climb what the posted name holds before the file's own name
	 */
	public function testANameThatClimbsOutOfTheMonthFolderImportsTheFileInsideItUnderItsOwnName($climb)
	{
		$staged = e107::getParser()->filter($climb.$this->name, 'file');

		$where = $this->import($climb.$this->name, $staged);

		self::assertFalse($where['as posted'], 'the file was written where the posted name points');
		self::assertTrue($where['month folder, as staged'], "the file did not reach the month folder under the name it was staged with:\n".$where['page']);
		self::assertSame(array($this->address('e_MEDIA_FILE', $this->month.'/'.$staged)), $this->storedUrls());
	}

	/**
	 * @param string $posted the name the import form posts
	 * @param string $staged the name of the file in the import folder
	 * @return array whether a file is in each place the import could put it, and the page
	 */
	private function import($posted, $staged)
	{
		$post = array(
			'batch_selected'        => array('k' => $posted),
			'batch_import_selected' => 1,
			'batch_import_name'     => array('k' => 'media import test'),
			'batch_import_diz'      => array('k' => ''),
			'batch_category'        => '_common_file',
		);

		$places = array(
			'month folder'            => $this->expression('e_MEDIA_FILE', $this->month.'/'.$this->name),
			'month folder, as staged' => $this->expression('e_MEDIA_FILE', $this->month.'/'.$staged),
			'as posted'               => $this->expression('e_MEDIA_FILE', $this->month.'/'.$posted),
			'import folder'           => $this->expression('e_IMPORT', $staged),
		);

		list($page, $found) = $this->runPage('import', $this->posting($post, $places['import folder']), $places);
		$this->skipIfTheMonthChanged();

		return array_combine(array_keys($places), $found) + array('page' => $page);
	}

	/**
	 * @param array $post the form the page is posted
	 * @param string $staged PHP expression of the path the file is staged at
	 * @return string statements that post the form with the session's token and stage the file
	 */
	private function posting(array $post, $staged)
	{
		return "\$_POST = ".var_export($post, true)." + array('e-token' => defset('e_TOKEN')); ".$this->staging($staged, self::TEXT);
	}

	/**
	 * @param string $action the Media Manager action the page is opened with
	 * @param string $seed statements run after the boot and before the page
	 * @param string[] $places PHP expressions of paths whose files the child reports on shutdown, in this order
	 * @param string $after statements run once the page has returned
	 * @return array the page, and whether a file is at each place
	 */
	private function runPage($action, $seed, array $places, $after = '')
	{
		$get = array('mode' => 'main', 'action' => $action);
		putenv('QUERY_STRING='.http_build_query($get));

		$php = "define('SEP', ' &raquo; '); \$_GET = ".var_export($get, true)."; ";
		$php .= "date_default_timezone_set(".var_export(date_default_timezone_get(), true)."); ";
		$php .= "e107::getUser()->setData(e107::getDb()->retrieve('user', '*', 'user_id = 1')); ";
		$php .= $this->makingFolders(array('e_IMPORT'));
		$php .= "register_shutdown_function(function() { clearstatcache(); \$found = array(); ";
		$php .= "foreach(array(".implode(', ', $places).") as \$p) { \$found[] = array(realpath(dirname(\$p)).'/'.basename(\$p), is_file(\$p)); } ";
		$php .= $this->report('PLACES', '$found')."}); ";
		$php .= $seed;
		$php .= "require_once('".addslashes(APP_PATH)."/e107_admin/image.php'); ";
		$php .= $after;

		list($output) = $this->runInBootedCli($php);
		$page = implode("\n", $output);

		$found = array();
		foreach($this->answer($page, 'PLACES', 'the page did not finish:') as $place)
		{
			$this->reported[] = $place[0];
			$found[] = $place[1];
		}

		return array($page, $found);
	}

	/**
	 * @param string $constant the name of the constant of the folder a path starts in, such as e_MEDIA_FILE
	 * @param string $path what follows the constant
	 * @return string the path as an item's address holds it
	 */
	private function address($constant, $path)
	{
		return '{'.$constant.'}'.$path;
	}

	/**
	 * @param string $constant the name of the constant of the folder a path starts in, such as e_MEDIA_FILE
	 * @param string $path what follows the constant
	 * @return string a PHP expression of the path, for the child to evaluate
	 */
	private function expression($constant, $path)
	{
		return $constant.'.'.var_export($path, true);
	}

	/**
	 * @param string $staged PHP expression of the path a file is staged at
	 * @param string $content PHP expression of what the file holds
	 * @return string a statement that writes the file
	 */
	private function staging($staged, $content)
	{
		return "file_put_contents(".$staged.", ".$content."); ";
	}

	/**
	 * @param string[] $folders PHP expressions of folders
	 * @return string a statement that makes each of them the child does not have
	 */
	private function makingFolders(array $folders)
	{
		return "foreach(array(".implode(', ', $folders).") as \$f) { if(!is_dir(\$f)) { mkdir(\$f, 0755, true); } } ";
	}

	/**
	 * @param string $tag what the line begins with
	 * @param string $value PHP expression of what the line carries
	 * @return string a statement with which the child writes the value on a line of its own, for answer() to read
	 */
	private function report($tag, $value)
	{
		return "fwrite(STDERR, \"\\n".$tag.":\".json_encode(".$value.").\"\\n\"); ";
	}

	/**
	 * @param string $page what the child printed
	 * @param string $tag what the line report() wrote begins with
	 * @param string $missing what the test says when the child wrote no such line
	 * @return mixed what the line carries
	 */
	private function answer($page, $tag, $missing)
	{
		self::assertSame(1, preg_match('/^'.$tag.':(.*)$/m', $page, $match), $missing."\n".$page);

		return json_decode($match[1], true);
	}

	/**
	 * Skips the test when the month changed after it began.
	 */
	private function skipIfTheMonthChanged()
	{
		if(date('Y-m') !== $this->month)
		{
			self::markTestSkipped('The month changed while the page ran, so the month folder it used is not known.');
		}
	}

	/**
	 * @return string[] media_url of every row whose address ends in this test's name
	 */
	private function storedUrls()
	{
		$sql = e107::getDb();
		$sql->select('core_media', 'media_url', 'media_url LIKE ?', array('%'.$this->name));
		$urls = array_column((array) $sql->rows(), 'media_url');
		sort($urls);

		return $urls;
	}
}
