<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * An admin list's search, and the suggestions its ajax filter offers, read only the columns the list searches.
 */
class adminUiFilterResponseColumnsTest extends \Codeception\Test\Unit
{
	const NAME = 'p7rpfilter';

	const PASSWORD = '$2y$04$p7rpfilterHASHHASHHASHHASHHASHHASHHASHHASHHASHHASHHA';

	const SESSION = 'p7rpfilterSESSIONKEY';

	const SIGNATURE = 'p7rpfilterSIGNATURE';

	const LOGIN = '<i>p7rpfilterlogin</i>';

	/** @var array what users.php declares for these columns, one of each kind the filter has to tell apart */
	private $fields = array(
		'user_id'        => array('title' => 'ID', 'type' => 'text', 'data' => 'int'),
		'user_name'      => array('title' => 'Name', 'type' => 'text', 'data' => 'safestr'),
		'user_email'     => array('title' => 'Email', 'type' => 'text', 'data' => 'safestr'),
		'user_password'  => array('title' => 'Password', 'type' => 'method', 'data' => 'safestr', 'search' => false),
		'user_sess'      => array('title' => 'Session', 'type' => 'text'),
		'user_signature' => array('title' => 'Signature', 'type' => 'textarea', 'data' => 'str', 'nolist' => true),
		'user_login'     => array('title' => 'Real name', 'type' => 'text', 'data' => 'safestr'),
	);

	protected function _before()
	{
		require_once(e_HANDLER . 'admin_ui.php');
		require_once(__DIR__ . '/fixtures/AdminUiFilterResponseProbeFixture.php');
		e107::coreLan('admin', true);

		e107::getDb()->insert('user', array(
			'user_name'      => self::NAME,
			'user_loginname' => self::NAME,
			'user_email'     => self::NAME . '@example.com',
			'user_password'  => self::PASSWORD,
			'user_sess'      => self::SESSION,
			'user_signature' => self::SIGNATURE,
			'user_login'     => self::LOGIN,
			'user_join'      => 1262304000,
			'user_class'     => '',
			'user_perms'     => '',
			'user_prefs'     => '',
			'user_realm'     => '',
			'user_xup'       => '',
		));
	}

	protected function _after()
	{
		e107::getDb()->delete('user', "user_loginname = '" . self::NAME . "'");
	}

	/**
	 * @param string $term
	 * @return string
	 */
	private function filter($term)
	{
		$list = new AdminUiFilterResponseProbeFixture($this->fields, $term);

		return $list->probe();
	}

	public function testTheFilterSuggestsOnlyTheColumnsTheListSearches()
	{
		$answer = $this->filter(self::NAME);

		$this->assertStringContainsString(self::NAME . '@example.com', $answer,
			'A column the list searches is still suggested.');
		$this->assertStringNotContainsString(self::PASSWORD, $answer,
			'A column declaring \'search\' => false must not be suggested.');
		$this->assertStringNotContainsString(self::SESSION, $answer,
			'A column the list does not search must not be suggested.');
		$this->assertStringNotContainsString(self::SIGNATURE, $answer,
			'A column the list does not draw must not be suggested.');
	}

	public function testTheFilterTakesTheTermAsTextNotAsAPattern()
	{
		$answer = $this->filter('.');

		$this->assertStringContainsString(self::NAME . '@example.com', $answer,
			'A full stop still matches a value holding one.');
		$this->assertStringNotContainsString('<li>' . self::NAME . '</li>', $answer,
			'A full stop must not match a value that holds none.');
	}

	public function testAWildcardTermStillGetsSuggestions()
	{
		$answer = $this->filter('p7rp*ter');

		$this->assertStringContainsString(self::NAME . '@example.com', $answer,
			'A term with the list search\'s wildcard is matched as the search matches it.');
		$this->assertStringNotContainsString(self::PASSWORD, $answer);
	}

	public function testTheListSearchDoesNotReadAColumnDeclaringSearchFalse()
	{
		$query = $this->listQuery('needle');

		$this->assertStringContainsString("u.user_name LIKE '%needle%'", $query);
		$this->assertStringNotContainsString('user_password', $query,
			'The list search must not match the term against a column declaring \'search\' => false.');
	}

	public function testTheMultipleWordSearchReadsOnlyTheColumnsTheListSearches()
	{
		$query = $this->listQuery('needle thread');

		$this->assertStringContainsString("u.user_name LIKE '%needle%'", $query);
		$this->assertStringNotContainsString('user_password', $query,
			'The multiple-word search must not read a column declaring \'search\' => false.');
		$this->assertStringNotContainsString('user_sess', $query,
			'The multiple-word search must not read a column the list does not search.');
		$this->assertStringNotContainsString('user_signature', $query,
			'The multiple-word search must not read a column the list does not draw.');
	}

	public function testTheListDoesNotSortByAColumnDeclaringSearchFalse()
	{
		$list = new AdminUiFilterResponseProbeFixture($this->fields, '');

		$this->assertStringNotContainsString('user_password', $list->listQuery('', 'user_password'),
			'The order of a list must not be taken from a column declaring \'search\' => false.');
	}

	public function testTheFilterEscapesTheTypedTermAndTheSuggestions()
	{
		$answer = $this->filter('<b>p7rpfilterlogin</b>');

		$this->assertStringNotContainsString('<b>p7rpfilterlogin', $answer, 'The typed term was printed as markup.');
		$this->assertStringContainsString('&lt;b&gt;p7rpfilterlogin', $answer);

		$answer = $this->filter('p7rpfilterlogin');

		$this->assertStringNotContainsString(self::LOGIN, $answer, 'A suggested value was printed as markup.');
		$this->assertStringContainsString('&lt;i&gt;p7rpfilterlogin&lt;/i&gt;', $answer);
	}

	public function testASearchInTheFieldDeclaringSearchFalseDoesNotReadIt()
	{
		$this->assertStringNotContainsString('user_password', $this->listQuery('needle', 'searchfield__user_password'),
			'The search-in-field option must not match the term against a column declaring \'search\' => false.');
	}

	public function testASearchInTheFieldOptionSpeltAnotherWayDoesNotReadIt()
	{
		foreach(array('SEARCHFIELD__user_password', 'search_field__user_password', 'Search-Field__user_password') as $option)
		{
			$this->assertStringNotContainsString('user_password', $this->listQuery('needle', $option),
				$option . ' reached the search-in-field handler for a column declaring \'search\' => false.');
		}
	}

	public function testAValueFilterOnTheFieldDeclaringSearchFalseDoesNotReadIt()
	{
		foreach(array('user_password__needle', 'bool__user_password__needle') as $option)
		{
			$this->assertStringNotContainsString('user_password', $this->listQuery('', $option),
				$option . ' matched request text against a column declaring \'search\' => false.');
		}
	}

	/**
	 * @param string $term
	 * @param string $filterOptions
	 * @return string the list query for the term
	 */
	private function listQuery($term, $filterOptions = '')
	{
		$list = new AdminUiFilterResponseProbeFixture($this->fields, $term);

		return $list->listQuery($filterOptions);
	}
}
