<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * Asks e107_admin/image.php for the media picker dialog's own search in a booted child, as an administrator without Media Manager (All).
 */
class imageDialogSearchTest extends \Test\Unit
{
	/** @var string the permissions of an administrator with Media Manager (Upload/Import) and (Categories) alone */
	const DELEGATED = 'A1.A2.';

	/** @var string what the child prints its outcome after */
	const TAG = 'IMAGE-DIALOG-SEARCH:';

	/** @var string a media name no other test or earlier run uses, which the search looks for */
	private $name;

	/** @var int the media row the search should find */
	private $id;

	protected function _before()
	{
		$this->name = 'image-dialog-search-'.substr(md5(uniqid('', true)), 0, 12);

		$this->id = (int) e107::getDb()->createQueryBuilder()->insert('core_media')->insertGetId(array(
			'media_name'      => $this->name.'.jpg',
			'media_category'  => '_common_image',
			'media_url'       => '{e_MEDIA_IMAGE}'.$this->name.'.jpg',
			'media_type'      => 'image/jpeg',
			'media_userclass' => 0,
		));

		putenv('HTTP_X_REQUESTED_WITH=XMLHttpRequest');
	}

	protected function _after()
	{
		putenv('HTTP_X_REQUESTED_WITH');

		e107::getDb()->createQueryBuilder()->delete('core_media')->where('media_id', $this->id)->execute();
	}

	public function testThePickerDialogsImageSearchAnswersAnAdministratorWithoutMediaManagerAll()
	{
		list($page, $outcome) = $this->search(self::DELEGATED, array('mode' => 'main', 'action' => 'image2', 'for' => '_common_image', 'search' => $this->name));

		self::assertTrue($outcome['dispatched'], "the search was refused before the Media Manager ran:\n".$page);
		self::assertOffered($this->name.'.jpg', $page);
	}

	public function testThePickerDialogsYouTubeSearchAnswersAnAdministratorWithoutAOrA1()
	{
		$video = substr(md5($this->name), 0, 11);

		list($page, $outcome) = $this->search('A2.', array('mode' => 'main', 'action' => 'youtube', 'search' => 'video:'.$video));

		self::assertTrue($outcome['dispatched'], "the search was refused before the Media Manager ran:\n".$page);
		self::assertOffered($video.'.youtube', $page);
	}

	public function testThePickerDialogsSearchLeavesTheImageCacheAlone()
	{
		$cached = e_CACHE_IMAGE.$this->name.'.cache.bin';
		file_put_contents($cached, $this->name);

		try
		{
			list($page, $outcome) = $this->search(self::DELEGATED, array('mode' => 'main', 'action' => 'image2', 'for' => '_common_image', 'search' => $this->name));

			self::assertTrue($outcome['dispatched'], "the search was refused before the Media Manager ran:\n".$page);
			self::assertFileExists($cached, 'the search emptied the image cache');
		}
		finally
		{
			if(is_file($cached))
			{
				unlink($cached);
			}
		}
	}

	/**
	 * @return array
	 */
	public function requestsTheDialogsRuleDoesNotCover()
	{
		return array(
			'the media list'                  => array(array('mode' => 'main', 'action' => 'grid', 'search' => true)),
			'the categories'                  => array(array('mode' => 'cat', 'action' => 'image2', 'search' => true)),
			'a picker request with no search' => array(array('mode' => 'main', 'action' => 'image2')),
		);
	}

	/**
	 * @dataProvider requestsTheDialogsRuleDoesNotCover
	 * @param array $route the mode and action the request is sent to, and whether it carries the search
	 */
	public function testARefusedRequestIsAnsweredWithForbiddenRatherThanTheAdminFrontPage(array $route)
	{
		if(isset($route['search']))
		{
			$route['search'] = $this->name;
		}

		list($page, $outcome) = $this->search(self::DELEGATED, $route + array('for' => '_common_image'));

		self::assertFalse($outcome['dispatched'], "the Media Manager ran:\n".$page);
		self::assertSame(403, $outcome['status'], "the refusal was not answered with 403:\n".$page);
		self::assertStringNotContainsString($this->name, $page);
	}

	public function testThePickerDialogsSearchIsRefusedWhenItIsNotAnAjaxRequest()
	{
		putenv('HTTP_X_REQUESTED_WITH');

		list($page, $outcome) = $this->search(self::DELEGATED, array('mode' => 'main', 'action' => 'image2', 'for' => '_common_image', 'search' => $this->name));

		self::assertFalse($outcome['dispatched'], "the Media Manager ran:\n".$page);
		self::assertStringNotContainsString($this->name, $page);
	}

	/**
	 * @param string $perms the administrator's permissions
	 * @param array $get the query string the child is opened with, as e_QUERY and as $_GET
	 * @return array the page, and whether a dispatcher was built and the response status
	 */
	private function search($perms, array $get)
	{
		$seed = "\$_GET = ".var_export($get, true)."; ";
		$seed .= "register_shutdown_function(function() { echo PHP_EOL, '".self::TAG."', json_encode(array('dispatched' => is_object(e107::getRegistry('admin/ui/dispatcher')), 'status' => http_response_code())); }); ";

		$page = $this->renderInBootedCli('e107_admin/image.php', $seed, self::BUFFERED, array('ADMINPERMS' => $perms, 'e_QUERY' => http_build_query($get)));

		self::assertSame(1, preg_match('/'.preg_quote(self::TAG, '/').'(.*)$/', $page, $match), "the child did not finish:\n".$page);

		return array($page, json_decode($match[1], true));
	}

	/**
	 * Asserts the page is the picker's own answer, offering the item that saves as $saveValue, rather than any other Media Manager page.
	 *
	 * @param string $saveValue the end of what the item writes into the field it is picked for
	 * @param string $page
	 */
	private static function assertOffered($saveValue, $page)
	{
		self::assertSame(1, preg_match("/class='e-media-select [^>]*data-path='[^']*".preg_quote($saveValue, '/')."'/", $page), "the picker did not offer ".$saveValue.":\n".$page);
	}
}
