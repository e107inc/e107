<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * Posts the "delete checked" form of the Media Manager's avatar page through e107_admin/image.php in a booted child as the main admin.
 */
class mediaAdminAvatarTest extends \Test\Unit
{
	/** @var string a name no other test or earlier run uses */
	private $name;

	/** @var string[] files and links the test made, removed again after it */
	private $files = array();

	/** @var string[] folders the test made, removed after the files in them */
	private $folders = array();

	/** @var int the user the test made to own an avatar, removed again after it */
	private $owner = 0;

	protected function _before()
	{
		$this->name = 'media-avatar-test-'.substr(md5(uniqid('', true)), 0, 12);

		foreach(array(e_AVATAR, e_AVATAR_DEFAULT, e_AVATAR_UPLOAD) as $folder)
		{
			$this->folder($folder);
		}
	}

	protected function _after()
	{
		putenv('QUERY_STRING');
		clearstatcache();

		foreach($this->files as $file)
		{
			if(is_link($file) || is_file($file))
			{
				unlink($file);
			}
		}

		foreach(array_reverse($this->folders) as $folder)
		{
			rmdir($folder);
		}

		if($this->owner)
		{
			e107::getDb()->createQueryBuilder()->delete('user')->where('user_id', $this->owner)->execute();
		}
	}

	/**
	 * @return array
	 */
	public function climbsOutOfTheAvatarsFolder()
	{
		return array(
			'from the avatars folder'   => array('', str_repeat('../', 40)),
			'from its default folder'   => array('', 'default/'.str_repeat('../', 41)),
			'after the uploaded prefix' => array('-upload-', str_repeat('../', 40)),
		);
	}

	/**
	 * @dataProvider climbsOutOfTheAvatarsFolder
	 * @param string $prefix what the entry holds before the path
	 * @param string $climb what the path holds before the file's own path, less that path's leading slash
	 */
	public function testAnEntryThatClimbsOutOfTheAvatarsFolderLeavesTheFileThereAlone($prefix, $climb)
	{
		$file = $this->outsideFile();
		$path = $climb.ltrim($file, '/');

		$page = $this->deleteChecked($prefix.$path);

		self::assertTrue(is_file($file), "the file outside the avatars folder was deleted:\n".$page);
		self::assertSame(array('info'), $this->messageTypes($page, '<strong>'.$path.'</strong>'), "the page did not say the file was kept:\n".$page);
	}

	/**
	 * @return array
	 */
	public function climbsLeftByRemovingTwoDotsAndASlashOnce()
	{
		return array(
			'four dots and two slashes'                            => array('', str_repeat('....//', 40)),
			'three dots, a slash, a dot and a slash'               => array('', str_repeat('..././', 40)),
			'four dots and two slashes, after the uploaded prefix' => array('-upload-', str_repeat('....//', 40)),
		);
	}

	/**
	 * @dataProvider climbsLeftByRemovingTwoDotsAndASlashOnce
	 * @param string $prefix what the entry holds before the path
	 * @param string $folders what the path holds before the file's own path, less that path's leading slash: folders the avatars folder does not hold, which climb out of it once every "../" in them is removed
	 */
	public function testAnEntryThatClimbsOutOnceTwoDotsAndASlashAreRemovedLeavesTheFileThereAlone($prefix, $folders)
	{
		$file = $this->outsideFile();

		$page = $this->deleteChecked($prefix.$folders.ltrim($file, '/'));

		self::assertTrue(is_file($file), "the file outside the avatars folder was deleted:\n".$page);
		self::assertSame(array('error'), $this->messageTypes($page, basename($file)), "the page did not say the file in the folders it names could not be deleted:\n".$page);
	}

	/**
	 * @return array
	 */
	public function foldersOfTheAvatarsFolder()
	{
		return array(
			'default' => array('default/'),
			'upload'  => array('upload/'),
		);
	}

	/**
	 * @dataProvider foldersOfTheAvatarsFolder
	 * @param string $folder the folder of the avatars folder that the page lists the avatar under
	 */
	public function testAnEntryInTheAvatarsFolderDeletesItsAvatar($folder)
	{
		$file = $this->file(e_AVATAR.$folder.$this->name.'.png');

		$page = $this->deleteChecked($folder.$this->name.'.png');

		self::assertFalse(is_file($file), "the avatar was not deleted:\n".$page);
		self::assertSame(array('success'), $this->messageTypes($page, $this->name.'.png'), "the page did not say the avatar was deleted:\n".$page);
	}

	public function testAnEntryWhoseFolderIsGoneSaysItCouldNotBeDeleted()
	{
		$page = $this->deleteChecked('gone-'.$this->name.'/'.$this->name.'.png');

		self::assertSame(array('error'), $this->messageTypes($page, $this->name.'.png'), "the page did not say the avatar could not be deleted:\n".$page);
	}

	public function testAnAvatarWhoseNameBeginsWithALetterOutsideAsciiIsDeletedUnderItsOwnName()
	{
		$name = "\xc3\x96".$this->name.'.png';
		$file = $this->file(e_AVATAR_DEFAULT.$name);
		$other = $this->file(e_AVATAR_DEFAULT.$this->name.'.png');

		$page = $this->deleteChecked('default/'.$name, 0, "setlocale(LC_ALL, 'C'); ");

		self::assertFalse(is_file($file), "the avatar was not deleted:\n".$page);
		self::assertTrue(is_file($other), "another avatar was deleted in its place:\n".$page);
		self::assertSame(array('success'), $this->messageTypes($page, $name), "the page did not name the avatar it deleted:\n".$page);
	}

	public function testAnAvatarInAFolderWhoseNameEndsInTwoDotsIsDeleted()
	{
		$file = $this->file($this->folder(e_AVATAR.$this->name.'..').'/'.$this->name.'.png');
		$other = $this->file(e_AVATAR.$this->name.$this->name.'.png');

		$page = $this->deleteChecked($this->name.'../'.$this->name.'.png');

		self::assertFalse(is_file($file), "the avatar was not deleted:\n".$page);
		self::assertTrue(is_file($other), "another avatar was deleted in its place:\n".$page);
	}

	public function testDeletingAnEntryResetsThePhotoOfTheUserPostedWithIt()
	{
		$file = $this->file(e_AVATAR_DEFAULT.$this->name.'.png');
		$owner = $this->owner();

		$page = $this->deleteChecked('default/'.$this->name.'.png', $owner);

		self::assertFalse(is_file($file), "the avatar was not deleted:\n".$page);
		self::assertSame('', $this->photo($owner), "the photo of the user posted with the entry was not reset:\n".$page);
	}

	public function testAnEntryKeptOutsideTheAvatarsFolderLeavesThePhotoOfTheUserPostedWithItAlone()
	{
		$file = $this->outsideFile();
		$owner = $this->owner();

		$page = $this->deleteChecked(str_repeat('../', 40).ltrim($file, '/'), $owner);

		self::assertTrue(is_file($file), "the file outside the avatars folder was deleted:\n".$page);
		self::assertSame($this->name.'.png', $this->photo($owner), "the photo of the user posted with the entry was reset for a file that was kept:\n".$page);
	}

	public function testAFileInAFolderThatLinksOutOfTheAvatarsFolderIsKept()
	{
		$file = $this->outsideFile();
		$this->link(dirname($file), e_AVATAR.$this->name);
		$path = $this->name.'/'.basename($file);

		$page = $this->deleteChecked($path);

		self::assertTrue(is_file($file), "the file in the folder the link points to was deleted:\n".$page);
		self::assertSame(array('info'), $this->messageTypes($page, '<strong>'.$path.'</strong>'), "the page did not say the file was kept:\n".$page);
	}

	public function testALinkInTheAvatarsFolderIsDeletedAsALink()
	{
		$file = $this->outsideFile();
		$link = $this->link($file, e_AVATAR_DEFAULT.$this->name.'.png');

		$page = $this->deleteChecked('default/'.$this->name.'.png');

		self::assertFalse(is_link($link), "the link in the avatars folder was not deleted:\n".$page);
		self::assertTrue(is_file($file), "the file the link points to was deleted:\n".$page);
	}

	/**
	 * Posts one checkbox of the avatar page's delete form and returns the page.
	 *
	 * @param string $entry what the page's checkbox for an avatar holds after the owner's user id and '#'
	 * @param int $owner the user id the checkbox holds before the '#'
	 * @param string $seed statements the child runs before the page
	 * @return string the page
	 */
	private function deleteChecked($entry, $owner = 0, $seed = '')
	{
		$get = array('mode' => 'main', 'action' => 'avatar');
		putenv('QUERY_STRING='.http_build_query($get));

		$seed .= "define('SEP', ' &raquo; '); \$_GET = ".var_export($get, true)."; ";
		$seed .= "e107::getUser()->setData(e107::getDb()->retrieve('user', '*', 'user_id = 1')); ";
		$seed .= "\$_POST = array('multiaction' => array(rawurldecode(".var_export(rawurlencode((int) $owner.'#'.$entry), true).")), 'submit_show_delete_multi' => 1, 'e-token' => defset('e_TOKEN')); ";

		$page = $this->renderInBootedCli('e107_admin/image.php', $seed);
		clearstatcache();

		return $page;
	}

	/**
	 * @param string $page the page the child printed
	 * @param string $text what a message says
	 * @return string[] the type of each message box on the page that says it
	 */
	private function messageTypes($page, $text)
	{
		preg_match_all("#<div class='s-message alert alert-block alert-dismissible fade in show (\\w+) .*?<div class='s-message-body'>(.*?)</div>\\s*</div>#s", $page, $boxes, PREG_SET_ORDER);

		$types = array();

		foreach($boxes as $box)
		{
			if(strpos($box[2], $text) !== false)
			{
				$types[] = $box[1];
			}
		}

		return $types;
	}

	/**
	 * @return int a user whose photo is named for this test
	 */
	private function owner()
	{
		$this->owner = (int) e107::getDb()->createQueryBuilder()->insert('user')->insertGetId(array(
			'user_name'      => $this->name,
			'user_loginname' => $this->name,
			'user_email'     => $this->name.'@example.com',
			'user_join'      => time(),
			'user_class'     => '',
			'user_sess'      => $this->name.'.png',
		));

		return $this->owner;
	}

	/**
	 * @param int $user
	 * @return string what the user's photo is, as stored
	 */
	private function photo($user)
	{
		return (string) e107::getDb()->createQueryBuilder()->select('user_sess')->from('user')->where('user_id', $user)->fetchOne();
	}

	/**
	 * @return string the path of a file named for this test in a folder it makes outside the site
	 */
	private function outsideFile()
	{
		return $this->file($this->folder(sys_get_temp_dir().'/'.$this->name).'/'.$this->name.'.txt');
	}

	/**
	 * @param string $path a file
	 * @return string the path, once a file is there that the test removes again
	 */
	private function file($path)
	{
		file_put_contents($path, 'media avatar test');
		$this->files[] = $path;

		return $path;
	}

	/**
	 * @param string $target what the link points to
	 * @param string $path a link
	 * @return string the path, once a link is there that the test removes again
	 */
	private function link($target, $path)
	{
		symlink($target, $path);
		$this->files[] = $path;

		return $path;
	}

	/**
	 * @param string $path a folder
	 * @return string the path, once the folder is there; a folder the test made is removed again after it
	 */
	private function folder($path)
	{
		if(!is_dir($path))
		{
			mkdir($path, 0755);
			$this->folders[] = $path;
		}

		return $path;
	}
}
