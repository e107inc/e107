<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * Saves navigation links through e107_admin/links.php in a booted child as the CLI main admin, with the classes that
 * may post script and HTML set for each case and a parser made after them, because booting has already granted the
 * parser the script tags of the suite's own class, and with the phpunit flag that stops check_class() granting every
 * class on the command line; e_QUERY is fixed at boot, so the query string goes in through the environment, and the
 * child buffers its output so that the status it sets is still its own.
 */
class linksAdminScriptAddressTest extends \Codeception\Test\Unit
{
	use \Test\BootedCli;
	use \Test\CorePrefs;

	const NAME = 'linksAdminScriptAddressTest';

	/** @var bool whether this test added the link_rel column the suite's database predates and links.php saves */
	private $addedRel = false;

	/** @var callable puts back the stored core preferences, which a page a child opens may save with its classes */
	private $restorePrefs;

	protected function _before()
	{
		$this->restorePrefs = $this->keepStoredCorePrefs();

		if(!e107::getDb()->field('links', 'link_rel'))
		{
			$this->addedRel = (bool) e107::getDb()->gen("ALTER TABLE `#links` ADD `link_rel` varchar(200) NOT NULL default ''");
		}
	}

	protected function _after()
	{
		putenv('QUERY_STRING');
		putenv('HTTP_X_REQUESTED_WITH');

		e107::getDb()->delete('links', "link_name LIKE '".self::NAME."%'");

		if($this->addedRel)
		{
			e107::getDb()->gen('ALTER TABLE `#links` DROP `link_rel`');
		}

		call_user_func($this->restorePrefs);
	}

	/**
	 * @return array addresses that run script as posted, as toDB() stores them, or through a brace, one or two of each
	 */
	public function scriptAddresses()
	{
		return array(
			'as posted'                              => array('javascript://%0Avoid(0)'),
			'a reference to a control character'     => array('&#1;javascript://%0Avoid(0)'),
			'a tag inside the scheme'                => array('java<x></x>script://%0Avoid(0)'),
			'an empty bbcode pair inside the scheme' => array('java[p][/p]script://%0Avoid(0)'),
			'behind an empty constant'               => array('{e_HANDLER}javascript:void(0)'),
			'a shortcode where the scheme is read'   => array('{USER_EXTENDED=homepage.value.1}'),
		);
	}

	/**
	 * @return array addresses that run no script
	 */
	public function otherAddresses()
	{
		return array(
			'a page'                       => array('news.php'),
			'a constant before a page'     => array('{e_BASE}news.php'),
			'a phone number'               => array('tel:+15550100'),
			'a shortcode after the scheme' => array('news.php?item={USERID}'),
		);
	}

	/**
	 * @return array addresses that run script, each as the save of an author who may post script stores it
	 */
	public function storedScriptAddresses()
	{
		return array(
			'with two slashes' => array('javascript://%0Avoid(0)'),
			'with quotes'      => array('javascript:alert(&#039;e107&#039;)'),
		);
	}

	/**
	 * @return array an address, the role of the admin who saves it and that of the admin who saves the edit form unchanged, whose save would store an address that runs script
	 */
	public function addressesAnUnchangedSaveWouldTurnIntoScript()
	{
		return array(
			'nested empty bbcode pairs, twice by one admin'      => array('java[[p][/p]p][/[p][/p]p]script://%0Avoid(0)', 'navigation', 'navigation'),
			'a script element, by an admin without post_script'  => array('java<script></script>script://%0Avoid(0)', 'script', 'navigation'),
			'a tag in the scheme, by an admin without post_html' => array('java<b></b>script://%0Avoid(0)', 'navigation', 'text'),
		);
	}

	/**
	 * @return array stored addresses a browser would post back changed if the edit form showed them decoded
	 */
	public function addressesAFormCouldDecode()
	{
		return array(
			'a percent-encoded line break' => array('javascript://%0Avoid(0)'),
			'a percent-encoded space'      => array('news.php?q=e107%20cms'),
			'a stored quote'               => array('javascript:alert(&quot;e107&quot;)'),
			'a stored dollar sign'         => array('page.php?x=&#036;1'),
		);
	}

	/**
	 * @return array stored addresses an untouched save used to rewrite, because the edit form showed them decoded
	 */
	public function addressesAnUntouchedSaveRewrote()
	{
		return array(
			'an encoded ampersand'        => array('news.php?a=1&amp;b=2'),
			'an encoded tag'              => array('news.php?q=&lt;b&gt;'),
			'an encoded entity'           => array('news.php?q=&amp;amp;'),
			'a percent-encoded plus sign' => array('search.php?q=a%2Bb'),
			'a percent-encoded space'     => array('news.php?q=e107%20cms'),
		);
	}

	/**
	 * @dataProvider scriptAddresses
	 * @param string $address
	 */
	public function testAnAddressThatRunsScriptIsRefusedOutsideThePostScriptClass($address)
	{
		$page = $this->create($address, 'navigation');

		self::assertSame(array(), $this->stored('link_url'), "the address was saved:\n".$page);
		self::assertStringContainsString("<div class='s-message-item'><strong>".LAN_URL.'</strong> - ', $page, 'the refusal has to name the field');
		self::assertStringContainsString('has-warning', $page, 'the field has to be marked');
	}

	/**
	 * @dataProvider scriptAddresses
	 * @param string $address
	 */
	public function testThePostScriptClassMaySaveAnAddressThatRunsScript($address)
	{
		$page = $this->create($address, 'script');

		self::assertCount(1, $this->stored('link_url'), "the address was refused:\n".$page);
	}

	/**
	 * @dataProvider otherAddresses
	 * @param string $address
	 */
	public function testAnAddressThatRunsNoScriptIsSavedOutsideThePostScriptClass($address)
	{
		$page = $this->create($address, 'navigation');

		self::assertSame(array($address), $this->stored('link_url'), "the address was refused or changed:\n".$page);
	}

	/**
	 * @dataProvider storedScriptAddresses
	 * @param string $address
	 */
	public function testAnUnchangedAddressThatRunsScriptIsKeptWhenAnotherFieldIsSavedOutsideThePostScriptClass($address)
	{
		$id = $this->seedLink($address);

		$page = $this->edit($id, self::NAME.' renamed', $address, 'navigation');

		self::assertSame(array(self::NAME.' renamed', $address), $this->storedLink($id), "the edit was not saved as posted:\n".$page);
	}

	/**
	 * @dataProvider addressesAnUnchangedSaveWouldTurnIntoScript
	 * @param string $address
	 * @param string $author
	 * @param string $editor
	 */
	public function testAnUnchangedAddressIsRefusedWhenTheSaveWouldStoreOneThatRunsScript($address, $author, $editor)
	{
		$this->create($address, $author);
		$ids = $this->stored('link_id');
		self::assertCount(1, $ids, 'the author could not save the link');
		$stored = $this->storedLink($ids[0]);

		$page = $this->edit($ids[0], self::NAME.' renamed', $stored[1], $editor);

		self::assertSame($stored, $this->storedLink($ids[0]), "the edit was saved:\n".$page);
		self::assertStringContainsString("<div class='s-message-item'><strong>".LAN_URL.'</strong> - ', $page, 'the refusal has to name the field');
	}

	/**
	 * @dataProvider addressesAFormCouldDecode
	 * @param string $address
	 */
	public function testTheEditFormShowsTheStoredAddressAsItIsStored($address)
	{
		self::assertSame(array('text', $address), $this->shownAddress($this->seedLink($address)), 'in the text box');
		self::assertSame(array('hidden', $address), $this->shownAddress($this->seedLink($address, 'index', 'news')), 'in the hidden field a link with a route has');
	}

	/**
	 * @dataProvider addressesAnUntouchedSaveRewrote
	 * @param string $address
	 */
	public function testAnUntouchedSaveKeepsTheAddressAsItIsStored($address)
	{
		$id = $this->seedLink($address);
		list(, $shown) = $this->shownAddress($id);

		$page = $this->edit($id, self::NAME.' renamed', $shown, 'navigation');

		self::assertSame(array(self::NAME.' renamed', $address), $this->storedLink($id), "the address was not kept as stored:\n".$page);
	}

	public function testAChangedAddressThatRunsScriptIsRefusedOutsideThePostScriptClass()
	{
		$id = $this->seedLink('javascript:void(0)');

		$page = $this->edit($id, self::NAME.' renamed', 'javascript:void(1)', 'navigation');

		self::assertSame(array(self::NAME, 'javascript:void(0)'), $this->storedLink($id), "the edit was saved:\n".$page);
		self::assertStringContainsString("<div class='s-message-item'><strong>".LAN_URL.'</strong> - ', $page, 'the refusal has to name the field');
	}

	public function testAnInlineEditCannotGiveALinkAnAddressThatRunsScript()
	{
		$id = $this->seedLink('news.php');

		list($response, $status) = $this->editInline($id, 'javascript:void(0)');
		self::assertSame(array('news.php'), $this->stored('link_url'));
		self::assertSame(400, $status, "the editor has to be told the address was not saved:\n".$response);
		self::assertStringContainsString(LAN_URL.' - Only ', $response, 'the refusal has to name the field, in text the editor shows as it is');

		list($response, $status) = $this->editInline($id, 'javascript:void(0)', '[debug=basic]');
		self::assertSame(array('news.php'), $this->stored('link_url'), 'in debug mode');
		self::assertSame(400, $status, "the editor has to be told the address was not saved, in debug mode:\n".$response);
		self::assertStringContainsString(LAN_URL.' - Only ', $response, 'the refusal has to name the field in debug mode too');

		list($response, $status) = $this->editInline($id, 'news.php?item=1');
		self::assertSame(array('news.php?item=1'), $this->stored('link_url'), 'an inline edit of the address has to save it');
		self::assertSame(200, $status, "a saved inline edit has to answer as before:\n".$response);
	}

	/**
	 * @param string $address
	 * @param string $role see {@see linksAdminScriptAddressTest::prefs()}
	 * @return string the page
	 */
	private function create($address, $role)
	{
		list($page) = $this->runPage('mode=main&action=create', $role, $this->form('create', self::NAME, $address));

		return $page;
	}

	/**
	 * @param int $id
	 * @param string $name
	 * @param string $address
	 * @param string $role see {@see linksAdminScriptAddressTest::prefs()}
	 * @return string the page
	 */
	private function edit($id, $name, $address, $role)
	{
		list($page) = $this->runPage('mode=main&action=edit&id='.$id, $role, $this->form('update', $name, $address));

		return $page;
	}

	/**
	 * @param int $id
	 * @return array the type of the edit form's address field and the value a browser reads from it
	 */
	private function shownAddress($id)
	{
		list($page) = $this->runPage('mode=main&action=edit&id='.$id, 'navigation', array());

		self::assertSame(1, preg_match("/<input type='(\\w+)' name='link_url' value='([^']*)'/", $page, $match), "the form has no address field:\n".$page);

		return array($match[1], html_entity_decode($match[2], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
	}

	/**
	 * @param string $trigger
	 * @param string $name
	 * @param string $address
	 * @return array the fields the create and edit forms post
	 */
	private function form($trigger, $name, $address)
	{
		return array(
			'etrigger_submit'  => $trigger,
			'link_name'        => $name,
			'link_url'         => $address,
			'link_category'    => 1,
			'link_class'       => 0,
			'link_open'        => 0,
			'link_parent'      => 0,
			'link_description' => '',
			'link_button'      => '',
			'link_order'       => 0,
			'link_function'    => '',
			'link_sefurl'      => '',
			'link_owner'       => '',
			'link_rel'         => '',
		);
	}

	/**
	 * @param int $id
	 * @param string $address
	 * @param string $menu what the query string opens with, e.g. [debug=basic]
	 * @return array the response and its HTTP status
	 */
	private function editInline($id, $address, $menu = '')
	{
		putenv('HTTP_X_REQUESTED_WITH=XMLHttpRequest');

		return $this->runPage($menu.'mode=main&action=inline&id='.$id, 'navigation', array(
			'name'  => 'link_url',
			'value' => $address,
			'pk'    => $id,
		), true);
	}

	/**
	 * @param string $query
	 * @param string $role see {@see linksAdminScriptAddressTest::prefs()}
	 * @param array $post
	 * @param bool $token whether to post the inline edit token
	 * @return array the page and its HTTP status
	 */
	private function runPage($query, $role, $post, $token = false)
	{
		putenv('QUERY_STRING='.$query);

		$php = "register_shutdown_function(function() { while(ob_get_level() > 0) { @ob_end_flush(); } echo \"\\nHTTP-STATUS:\".(http_response_code() ?: 200); }); ";
		$php .= "\$GLOBALS['_E107']['phpunit'] = true; ";
		$php .= 'foreach('.var_export($this->prefs($role), true).' as $key => $value) { e107::getConfig()->set($key, $value); } ';
		$php .= "e107::setRegistry('core/e107/singleton/e_parse', new e_parse()); ";
		$php .= '$_POST = '.var_export($post, true)." + array('e-token' => defset('e_TOKEN')); ";
		$php .= $token ? "\$_POST['token'] = password_hash(session_id(), PASSWORD_DEFAULT); " : '';
		$php .= "require '".addslashes(APP_PATH)."/e107_admin/links.php'; ";

		list($output, $status) = $this->runInBootedCli($php, '-d output_buffering=1');
		$page = implode("\n", $output);

		self::assertSame(0, $status, "the page died:\n".$page);
		self::assertStringNotContainsString('Fatal error', $page, "the page died:\n".$page);
		self::assertSame(1, preg_match('/\nHTTP-STATUS:(\d+)$/', $page, $match), "the page did not finish:\n".$page);

		return array((string) substr($page, 0, -strlen($match[0])), (int) $match[1]);
	}

	/**
	 * @param string $role 'script' for an admin in the class that may post script, 'navigation' for one outside it, 'text' for one outside the class that may post HTML too
	 * @return array the post_script and post_html classes the child saves with
	 */
	private function prefs($role)
	{
		$roles = array(
			'script'     => array('post_script' => e_UC_MAINADMIN),
			'navigation' => array('post_script' => e_UC_NOBODY),
			'text'       => array('post_script' => e_UC_NOBODY, 'post_html' => e_UC_NOBODY),
		);

		return $roles[$role];
	}

	/**
	 * @param string $address
	 * @param string $route the link_sefurl route, if any
	 * @param string $owner the plugin that owns the route
	 * @return int
	 */
	private function seedLink($address, $route = '', $owner = '')
	{
		$id = (int) e107::getDb()->insert('links', array(
			'link_name'        => self::NAME,
			'link_url'         => $address,
			'link_description' => '',
			'link_button'      => '',
			'link_category'    => 1,
			'link_order'       => 0,
			'link_parent'      => 0,
			'link_open'        => 0,
			'link_class'       => 0,
			'link_function'    => '',
			'link_sefurl'      => $route,
			'link_owner'       => $owner,
		));

		self::assertGreaterThan(0, $id, 'the link was not seeded');

		return $id;
	}

	/**
	 * @param string $column
	 * @return array the column of each link this test saved, in the order they were saved
	 */
	private function stored($column)
	{
		$rows = e107::getDb()->retrieve('links', $column, "link_name = '".self::NAME."' ORDER BY link_id", true);

		return array_map(function ($row) use ($column) { return $row[$column]; }, $rows ? $rows : array());
	}

	/**
	 * @param int $id
	 * @return array the link's name and address as stored
	 */
	private function storedLink($id)
	{
		$row = e107::getDb()->retrieve('links', 'link_name, link_url', 'link_id = '.(int) $id);

		return array($row['link_name'], $row['link_url']);
	}
}
