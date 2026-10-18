<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * Posts the Media Manager's import, create, delete and batch forms through e107_admin/image.php in a booted child as the main admin, and runs its duplicate check there.
 */
class mediaAdminImportTest extends \Test\Unit
{
	/** @var string what the name of the copy the list's resize batch writes beside a JPEG begins with */
	const RESIZED = '2816x2112_';

	/** @var string the name of a month folder the media folder does not hold */
	const GONE = '1999-01';

	/** @var string PHP expression of what a staged text file holds */
	const TEXT = "'media import test'";

	/** @var string a name no other test or earlier run uses */
	private $name;

	/** @var string the month the test began in, as the Media Manager names a month folder */
	private $month;

	/** @var string[] absolute paths the child reported, removed again after each test */
	private $reported = array();

	/** @var string[] folders a test made outside the site, removed after the paths in them */
	private $folders = array();

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

		foreach($this->folders as $folder)
		{
			rmdir($folder);
		}

		e107::getDb()->createQueryBuilder()->delete('core_media')->whereLike('media_url', $this->pattern())->execute();
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
	 * @return array
	 */
	public function rowsAtAQuotedAddress()
	{
		return array(
			'a row at the address'  => array(true),
			'no row at the address' => array(false),
		);
	}

	/**
	 * @dataProvider rowsAtAQuotedAddress
	 * @param bool $stored whether a row holds the address before checkDupe() looks for one
	 */
	public function testAQuoteInTheAddressLeavesTheDuplicateQueryIntact($stored)
	{
		$quoted = $this->month."/o'".$this->name;
		$staged = $this->expression('e_IMPORT', $this->name);

		if($stored)
		{
			$this->insertItem($this->address('e_MEDIA_FILE', $quoted));
		}

		list($page) = $this->runPage('import',
			$this->staging($staged, self::TEXT),
			array($staged),
			"\$sql = e107::getDb(); \$sql->resetLastError(); \$path = ".$this->expression('e_MEDIA_FILE', $quoted)."; ".
			"\$ui = (new ReflectionClass('media_admin_ui'))->newInstanceWithoutConstructor(); ".
			"\$free = (\$ui->checkDupe(".$staged.", \$path) === \$path); ".
			$this->report('DUPE', "array('error' => \$sql->getLastErrorNumber(), 'free' => \$free)")
		);

		self::assertSame(array('error' => 0, 'free' => !$stored), $this->answer($page, 'DUPE', 'checkDupe() did not answer:'), "the quote changed checkDupe()'s query or its answer:\n".$page);
	}

	public function testCreatingAnItemFromAFileInTheImportFolderMovesItIntoTheMonthFolderUnderItsOwnName()
	{
		$address = $this->address('e_IMPORT', $this->name);
		$staged = $this->expression('e_IMPORT', $this->name);

		list($page, $found) = $this->create($address, $staged, array(
			$staged,
			$this->expression('e_MEDIA_FILE', $this->month.'/'.$this->name),
			$this->expression('e_MEDIA_FILE', $this->month.'/'.$address),
		));

		self::assertSame(array(false, true, false), $found, "the file did not reach the month folder under its own name:\n".$page);
	}

	public function testCreatingAnItemFromAnImportAddressOutsideTheImportFolderMovesNothing()
	{
		$path = '../'.$this->name;
		$staged = $this->expression('e_IMPORT', $path);

		list($page, $found) = $this->create($this->address('e_IMPORT', $path), $staged, array(
			$staged,
			$this->expression('e_MEDIA_FILE', $this->month.'/'.$this->name),
		));

		self::assertSame(array(true, false), $found, "the file outside the import folder was moved:\n".$page);
		self::assertSame(array(), $this->storedUrls());
	}

	/**
	 * @return array
	 */
	public function addressesOutsideTheMediaFolder()
	{
		return array(
			'a path'                                => array(''),
			'a media address that climbs out of it' => array($this->address('e_MEDIA_FILE', str_repeat('../', 32))),
		);
	}

	/**
	 * @dataProvider addressesOutsideTheMediaFolder
	 * @param string $prefix what the item's address holds before the path of its file, less the path's leading slash
	 */
	public function testDeletingAnItemLeavesAFileOutsideTheMediaFolderAlone($prefix)
	{
		list($address, $staged) = $this->outsideItem($prefix, 'txt');

		list($page, $found) = $this->deleteItem($address, $staged);

		self::assertSame(array(), $this->storedUrls(), "the item was not deleted:\n".$page);
		self::assertTrue($found, "the file outside the media folder was deleted with the item:\n".$page);
		self::assertSame(array('info'), $this->messagesNaming($page, $address), "the page did not say the file was kept:\n".$page);
	}

	public function testDeletingAnItemDeletesItsFileInTheMediaFolder()
	{
		$path = $this->month.'/'.$this->name;
		$address = $this->address('e_MEDIA_FILE', $path);

		list($page, $found) = $this->deleteItem($address, $this->expression('e_MEDIA_FILE', $path));

		self::assertSame(array(), $this->storedUrls(), "the item was not deleted:\n".$page);
		self::assertFalse($found, "the file of the deleted item is still in the media folder:\n".$page);
		self::assertSame(array('success'), $this->messagesNaming($page, $address), "the page did not say the file was deleted:\n".$page);
	}

	public function testDeletingAnItemWhoseFolderIsGoneSaysItsFileCouldNotBeDeleted()
	{
		$address = $this->address('e_MEDIA_FILE', self::GONE.'/'.$this->name);

		list($page, $found) = $this->deleteItem($address, $this->expression('e_MEDIA_FILE', $this->month.'/'.$this->name));

		self::assertSame(array(), $this->storedUrls(), "the item was not deleted:\n".$page);
		self::assertTrue($found, "a file the item's address does not name was deleted:\n".$page);
		self::assertSame(array('error'), $this->messagesNaming($page, $address), "the page did not say the file could not be deleted:\n".$page);
	}

	/**
	 * @dataProvider addressesOutsideTheMediaFolder
	 * @param string $prefix what the item's address holds before the path of its file, less the path's leading slash
	 */
	public function testConvertingAnItemToJpegLeavesAFileOutsideTheMediaFolderAlone($prefix)
	{
		list($address, $staged) = $this->outsideItem($prefix, 'png');

		list($page, $found) = $this->convertItem($address, $staged);

		self::assertSame(array(true, false), $found, "the file outside the media folder was converted:\n".$page);
		self::assertSame(array($address), $this->storedUrls(), "the item's address changed:\n".$page);
		self::assertSame(array('info'), $this->messagesNaming($page, $address), "the page did not say the file was kept:\n".$page);
	}

	public function testConvertingAnItemToJpegThroughAFolderThatIsGoneLeavesAFileOutsideTheMediaFolderAlone()
	{
		list($address, $staged) = $this->outsideItem($this->climbThroughAFolderThatIsGone(), 'png');

		list($page, $found) = $this->convertItem($address, $staged);

		self::assertSame(array(true, false), $found, "the file outside the media folder was converted:\n".$page);
		self::assertSame(array($address), $this->storedUrls(), "the item's address changed:\n".$page);
	}

	public function testConvertingAnItemToJpegConvertsItsFileInTheMediaFolder()
	{
		$path = $this->month.'/'.$this->named('png');

		list($page, $found) = $this->convertItem($this->address('e_MEDIA_IMAGE', $path), $this->expression('e_MEDIA_IMAGE', $path));

		self::assertSame(array(false, true), $found, "the file in the media folder was not converted:\n".$page);
		self::assertSame(array($this->address('e_MEDIA_IMAGE', $this->month.'/'.$this->named('jpg'))), $this->storedUrls(), "the item does not name the converted file:\n".$page);
	}

	/**
	 * @return array
	 */
	public function jpegBatchesOnAddressesOutsideTheMediaFolder()
	{
		$rows = array();

		foreach(array('rotate' => 'rotate_cw', 'resize' => 'resize_2048') as $label => $batch)
		{
			foreach($this->addressesOutsideTheMediaFolder() as $address => $prefix)
			{
				$rows[$label.', '.$address] = array($batch, $prefix[0]);
			}
		}

		return $rows;
	}

	/**
	 * @dataProvider jpegBatchesOnAddressesOutsideTheMediaFolder
	 * @param string $batch the list's batch option the item is run through
	 * @param string $prefix what the item's address holds before the path of its file, less the path's leading slash
	 */
	public function testRotatingOrResizingAnItemLeavesAFileOutsideTheMediaFolderAlone($batch, $prefix)
	{
		$jpeg = $this->image('jpeg');
		list($address, $staged, $file) = $this->outsideItem($prefix, 'jpg');
		$copy = $this->outsideFile(self::RESIZED.basename($file));

		list($page) = $this->batchItem($batch, $address, $jpeg, $staged);

		self::assertSame($jpeg, file_get_contents($file), "the file outside the media folder was rewritten:\n".$page);
		self::assertFalse(is_file($copy), "a resized copy was written beside the file outside the media folder:\n".$page);
		self::assertSame(array('info'), $this->messagesNaming($page, $address), "the page did not say the file was kept:\n".$page);
	}

	public function testRotatingAnItemThroughAFolderThatIsGoneLeavesAFileOutsideTheMediaFolderAlone()
	{
		$jpeg = $this->image('jpeg');
		list($address, $staged, $file) = $this->outsideItem($this->climbThroughAFolderThatIsGone(), 'jpg');

		list($page) = $this->batchItem('rotate_cw', $address, $jpeg, $staged);

		self::assertSame($jpeg, file_get_contents($file), "the file outside the media folder was rewritten:\n".$page);
	}

	public function testRotatingAnItemRotatesItsFileInTheMediaFolder()
	{
		$path = $this->month.'/'.$this->named('jpg');

		list($page, , $paths) = $this->batchItem('rotate_cw', $this->address('e_MEDIA_IMAGE', $path), $this->image('jpeg'), $this->expression('e_MEDIA_IMAGE', $path));
		$size = getimagesize($paths[0]);

		self::assertSame(array(1, 2), array($size[0], $size[1]), "the file in the media folder was not rotated:\n".$page);
	}

	public function testResizingAnItemWritesAResizedCopyBesideItsFileInTheMediaFolder()
	{
		$name = $this->named('jpg');
		$path = $this->month.'/'.$name;
		$copy = $this->expression('e_MEDIA_IMAGE', $this->month.'/'.self::RESIZED.$name);

		list($page, $found) = $this->batchItem('resize_2048', $this->address('e_MEDIA_IMAGE', $path), $this->image('jpeg'), $this->expression('e_MEDIA_IMAGE', $path), array($copy));

		self::assertSame(array(true, true), $found, "no resized copy was written beside the file in the media folder:\n".$page);
	}

	public function testResizingAnItemWhoseFolderIsGoneSaysItsFileCouldNotBeResized()
	{
		$name = $this->named('jpg');

		list($page, $found) = $this->batchItem('resize_2048', $this->address('e_MEDIA_IMAGE', self::GONE.'/'.$name), $this->image('jpeg'), $this->expression('e_MEDIA_IMAGE', $this->month.'/'.$name));

		self::assertSame(array(true), $found, "a file the item's address does not name was removed:\n".$page);
		self::assertSame(array('error'), $this->messageTypes($page, ': '.$name), "the page did not say the file could not be resized:\n".$page);
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
	 * @param string $address the media_url the create form posts
	 * @param string $staged PHP expression of the path the file is staged at
	 * @param string[] $places PHP expressions of paths whose files the child reports on shutdown, in this order
	 * @return array the page, and whether a file is at each place
	 */
	private function create($address, $staged, array $places)
	{
		$post = array(
			'etrigger_submit' => 'create',
			'media_url'       => $address,
			'media_name'      => 'media import test',
			'media_category'  => '_common_file',
		);

		$created = $this->runPage('create', $this->posting($post, $staged), $places);
		$this->skipIfTheMonthChanged();

		return $created;
	}

	/**
	 * @param string $address the media_url of the item the list deletes
	 * @param string $staged PHP expression of the path its file is staged at
	 * @return array the page, and whether the file is still there once the item is deleted
	 */
	private function deleteItem($address, $staged)
	{
		$id = $this->insertItem($address, 'text/plain');
		$post = array('etrigger_delete' => array($id => $id), 'etrigger_delete_confirm' => 1);

		\Test\CoreTable::create('admin_history');

		try
		{
			list($page, $found) = $this->listPage($post, $staged, self::TEXT);
		}
		finally
		{
			\Test\CoreTable::drop('admin_history');
		}

		return array($page, $found[0]);
	}

	/**
	 * @param string $address the media_url of the PNG item the list's batch converts to JPEG
	 * @param string $staged PHP expression of the path its file is staged at
	 * @return array the page, and whether the PNG and a JPEG beside it are there once the batch has run
	 */
	private function convertItem($address, $staged)
	{
		list($page, $found) = $this->batchItem('convert_to_jpeg', $address, $this->image('png'), $staged, array("substr(".$staged.", 0, -4).'.jpg'"));

		return array($page, $found);
	}

	/**
	 * @param string $batch the list's batch option the item is run through, less its options__ prefix
	 * @param string $address the media_url of the item
	 * @param string $image the image its file holds, whose type the item is stored with
	 * @param string $staged PHP expression of the path its file is staged at
	 * @param string[] $places PHP expressions of more paths whose files the child reports once the batch has run
	 * @return array the page, whether a file is at the staged path and at each of $places, and the path of each
	 */
	private function batchItem($batch, $address, $image, $staged, array $places = array())
	{
		$size = getimagesizefromstring($image);
		$id = $this->insertItem($address, $size['mime']);
		$post = array('multiselect' => array($id), 'etrigger_batch' => 'options__'.$batch, 'e__execute_batch' => 1);

		return $this->listPage($post, $staged, "base64_decode('".base64_encode($image)."')", $places);
	}

	/**
	 * Posts the list with an item's file staged and the media folders an address can climb out of made.
	 *
	 * @param array $post the form the list is posted
	 * @param string $staged PHP expression of the path the item's file is staged at
	 * @param string $content PHP expression of what the file holds
	 * @param string[] $places PHP expressions of more paths whose files the child reports once the list has run
	 * @return array the page, whether a file is at the staged path and at each of $places, and the path of each
	 */
	private function listPage(array $post, $staged, $content, array $places = array())
	{
		return $this->runPage('list', $this->makingFolders(array('e_MEDIA_FILE', 'e_MEDIA_IMAGE', 'dirname('.$staged.')')).$this->posting($post, $staged, $content), array_merge(array($staged), $places));
	}

	/**
	 * @param string $format png or jpeg
	 * @return string an image in that format, two pixels wide and one high
	 */
	private function image($format)
	{
		$image = imagecreatetruecolor(2, 1);

		ob_start();
		call_user_func('image'.$format, $image);

		return ob_get_clean();
	}

	/**
	 * @param string $name the name of a file
	 * @return string its path in a folder this test makes outside the site, both removed again after the test
	 */
	private function outsideFile($name)
	{
		$folder = sys_get_temp_dir().'/'.$this->stem();

		if(!in_array($folder, $this->folders, true))
		{
			mkdir($folder);
			$this->folders[] = $folder;
		}

		$this->reported[] = $folder.'/'.$name;

		return $folder.'/'.$name;
	}

	/**
	 * @param string $prefix what the address holds before the path of the file, less the path's leading slash
	 * @param string $extension the file's extension
	 * @return string[] the address of a file named for this test in a folder outside the site, a PHP expression of its path, and the path
	 */
	private function outsideItem($prefix, $extension)
	{
		$file = $this->outsideFile($this->named($extension));

		return array(($prefix === '') ? $file : $prefix.ltrim($file, '/'), var_export($file, true), $file);
	}

	/**
	 * @return string what an address holds to climb out of the media folder through a month folder that does not exist
	 */
	private function climbThroughAFolderThatIsGone()
	{
		return $this->address('e_MEDIA_IMAGE', self::GONE.'/'.str_repeat('../', 33));
	}

	/**
	 * @param string $extension
	 * @return string this test's name, with $extension in place of its own
	 */
	private function named($extension)
	{
		return $this->stem().'.'.$extension;
	}

	/**
	 * @return string this test's name less its extension, which names every file and item the test makes
	 */
	private function stem()
	{
		return basename($this->name, '.txt');
	}

	/**
	 * @return string a LIKE pattern that matches the address of every item the test stores
	 */
	private function pattern()
	{
		return '%'.$this->stem().'%';
	}

	/**
	 * @param string $page the page the child printed
	 * @param string $text what a message says
	 * @return string[] the type of each message box on the page that says it
	 */
	private function messageTypes($page, $text)
	{
		$types = array();

		foreach(array_slice(explode("<div class='s-message alert", $page), 1) as $box)
		{
			if(strpos($box, $text) !== false && preg_match('/s-message-icon s-message-(\w+)/', $box, $type))
			{
				$types[] = $type[1];
			}
		}

		return $types;
	}

	/**
	 * @param string $page the page the child printed
	 * @param string $address an address, which a message names in bold
	 * @return string[] the type of each message box on the page that names it
	 */
	private function messagesNaming($page, $address)
	{
		return $this->messageTypes($page, '<strong>'.$address.'</strong>');
	}

	/**
	 * @param string $address the media_url the item is stored with
	 * @param string $type its media_type
	 * @return int the item's id
	 */
	private function insertItem($address, $type = '')
	{
		return (int) e107::getDb()->createQueryBuilder()->insert('core_media')->values(array(
			'media_url'         => $address,
			'media_type'        => $type,
			'media_name'        => 'media import test',
			'media_caption'     => '',
			'media_description' => '',
			'media_category'    => '_common_file',
			'media_usedby'      => '',
			'media_tags'        => '',
		))->insertGetId();
	}

	/**
	 * @param array $post the form the page is posted
	 * @param string $staged PHP expression of the path the file is staged at
	 * @param string $content PHP expression of what the file holds
	 * @return string statements that post the form with the session's token and stage the file
	 */
	private function posting(array $post, $staged, $content = self::TEXT)
	{
		return "\$_POST = ".var_export($post, true)." + array('e-token' => defset('e_TOKEN')); ".$this->staging($staged, $content);
	}

	/**
	 * @param string $action the Media Manager action the page is opened with
	 * @param string $seed statements run after the boot and before the page
	 * @param string[] $places PHP expressions of paths whose files the child reports on shutdown, in this order
	 * @param string $after statements run once the page has returned
	 * @return array the page, whether a file is at each place, and the path the child reported for each
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
		$paths = array();
		foreach($this->answer($page, 'PLACES', 'the page did not finish:') as $place)
		{
			$this->reported[] = $place[0];
			$found[] = $place[1];
			$paths[] = $place[0];
		}

		return array($page, $found, $paths);
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
	 * @return string[] media_url of every row whose address holds this test's name less its extension
	 */
	private function storedUrls()
	{
		$rows = e107::getDb()->createQueryBuilder()->select('media_url')->from('core_media')->whereLike('media_url', $this->pattern())->fetchAll();
		$urls = array_column((array) $rows, 'media_url');
		sort($urls);

		return $urls;
	}
}
