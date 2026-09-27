<?php

/**
 * What download's feed hands to rss_menu.
 *
 * Every link it builds is relative to e_PLUGIN, because rssCreate prefixes
 * anything without a scheme with SITEURLBASE.e_PLUGIN_ABS; an absolute string
 * here is the site path twice on a subdirectory install.
 *
 * The suite escalates a PHP diagnostic into a failure, so data() reaching its
 * return at all is what covers the undefined variable the three link
 * expressions used to read a property on.
 *
 * An item carries the three values rss_menu needs before it writes an
 * enclosure: the request link, the size, and the type of the file behind it.
 *
 * @see https://github.com/e107inc/e107/discussions/6497
 */
class download_e_rssTest extends \Test\Unit
{
	/** Past what the table can hold, so no fixture row is crowded out of the feed. */
	const LIMIT = 999;

	/** The sef every fixture row carries, so _after can take them back out again. */
	const MARKER = 'e107help-feed-probe';

	/** @var bool whether this test installed the download plugin for its tables */
	private $downloadInstalled = false;

	/** @var int */
	private $categoryId;

	/** @var int */
	private $downloadId;

	/** @var int */
	private $anonymousId;

	/** @var int */
	private $extensionlessId;

	/** @var int */
	private $unknownTypeId;

	/** @var int */
	private $unmeasuredId;

	/** @var int */
	private $humanSizeId;

	protected function _before()
	{
		require_once(e_PLUGIN.'download/e_rss.php');

		// Asked of the server rather than through isTable(), which answers from a
		// list the connection cached before anything in this run created a table.
		if(!e107::getDb()->execute("SHOW TABLES LIKE '".MPREFIX."download'"))
		{
			e107::getPlugin()->install('download');
			$this->downloadInstalled = true;
		}

		$this->categoryId = e107::getDb()->createQueryBuilder()->insert('download_category')->insertGetId(array(
			'download_category_name'        => 'Feed fixture category',
			'download_category_description' => 'fixture',
			'download_category_icon'        => '',
			'download_category_parent'      => 0,
			'download_category_class'       => e_UC_PUBLIC,
			'download_category_order'       => 1,
			'download_category_sef'         => self::MARKER,
		));

		$this->downloadId = $this->haveDownload('Feed fixture download', 'Ahsanul');
		$this->anonymousId = $this->haveDownload('Feed fixture download without an author', '');
		$this->extensionlessId = $this->haveDownload('Feed fixture download with no extension', '', 'fixture');
		$this->unknownTypeId = $this->haveDownload('Feed fixture download of an unknown type', '', 'fixture.e107help');
		$this->unmeasuredId = $this->haveDownload('Feed fixture download of unstated size', '', 'fixture.txt', 'not measured');
		$this->humanSizeId = $this->haveDownload('Feed fixture download sized by hand', '', 'fixture.txt', '1.5 MB');
	}

	protected function _after()
	{
		e107::getDb()->createQueryBuilder()->delete('download')
			->where('download_sef', self::MARKER)->execute();
		e107::getDb()->createQueryBuilder()->delete('download_category')
			->where('download_category_sef', self::MARKER)->execute();

		if($this->downloadInstalled)
		{
			e107::getPlugin()->uninstall('download', array('delete_tables' => true));
			$this->downloadInstalled = false;
		}
	}

	public function testEveryLinkIsRelativeToThePluginDirectory()
	{
		$item = $this->feedItem($this->downloadId);

		$this->assertSame('download/download.php?view.'.$this->downloadId, $item['link']);
		$this->assertSame('download/download.php?list.'.$this->categoryId, $item['category_link']);
		$this->assertSame('download/request.php?'.$this->downloadId, $item['enc_url']);
	}

	/**
	 * The keys an item carries are the addon's to decide, so rss_menu reads
	 * every one of them for absence. item_id is never set by a v2 addon at all,
	 * and author is set only where there is one.
	 */
	public function testAnItemCarriesOnlyTheKeysTheAddonSet()
	{
		$item = $this->feedItem($this->anonymousId);

		$this->assertArrayNotHasKey('author', $item);
		$this->assertArrayNotHasKey('item_id', $item);
		$this->assertSame('Ahsanul', $this->feedItem($this->downloadId)['author']);
	}

	/**
	 * The type follows {@see e_file::getMime()} in full: a known extension by
	 * name, an unrecognised one as the generic binary, and no extension at all as
	 * nothing, which is what keeps the element off that item.
	 */
	public function testAnItemCarriesTheTypeOfTheFileBehindIt()
	{
		$this->assertSame('text/plain', $this->feedItem($this->downloadId)['enc_type']);
		$this->assertSame('application/octet-stream', $this->feedItem($this->unknownTypeId)['enc_type']);
		$this->assertSame('', $this->feedItem($this->extensionlessId)['enc_type']);
	}

	/**
	 * enclosure/@length is a count of bytes, and the column behind it is a varchar
	 * the admin form stores as typed when the size is given in bytes, so anything
	 * that is not a whole number of them has to leave the element off rather than
	 * publish a size that is wrong by orders of magnitude.
	 */
	public function testALengthThatIsNotANumberOfBytesLeavesTheEnclosureOff()
	{
		$this->assertSame(12, $this->feedItem($this->downloadId)['enc_leng']);
		$this->assertSame(0, $this->feedItem($this->unmeasuredId)['enc_leng']);
		$this->assertSame(0, $this->feedItem($this->humanSizeId)['enc_leng']);
	}

	/**
	 * @param int $id download_id
	 * @return array the item the feed built for that download
	 */
	private function feedItem($id)
	{
		$rss = new download_rss();

		foreach($rss->data(array('url' => 'download', 'id' => '', 'limit' => self::LIMIT)) as $item)
		{
			if($item['enc_url'] === 'download/request.php?'.$id)
			{
				return $item;
			}
		}

		$this->fail('The feed did not name download '.$id.', so there is nothing to assert about its links.');
	}

	/**
	 * @param string $name download_name, which carries a unique key
	 * @param string $author download_author, empty where nobody is credited
	 * @param string $url download_url, the file name the type is read from
	 * @param string $size download_filesize, a varchar that is usually bytes
	 * @return int download_id
	 */
	private function haveDownload($name, $author, $url = 'fixture.txt', $size = '12')
	{
		return e107::getDb()->createQueryBuilder()->insert('download')->insertGetId(array(
			'download_name'           => $name,
			'download_url'            => $url,
			'download_sef'            => self::MARKER,
			'download_author'         => $author,
			'download_author_email'   => 'fixture@example.com',
			'download_author_website' => '',
			'download_description'    => 'Body of '.$name,
			'download_keywords'       => '',
			'download_filesize'       => $size,
			'download_requested'      => 0,
			'download_category'       => $this->categoryId,
			'download_active'         => 1,
			'download_datestamp'      => time() - 3600,
			'download_thumb'          => '',
			'download_image'          => '',
			'download_comment'        => 0,
			'download_class'          => e_UC_PUBLIC,
			'download_mirror'         => '',
			'download_mirror_type'    => 0,
			'download_visible'        => e_UC_PUBLIC,
		));
	}
}
