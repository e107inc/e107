<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * The member list in user.php, whose own form posts the offset the visitor was reading in a hidden field.
 *
 * @see https://github.com/e107inc/e107/issues/6052
 */
class userPageMemberListTest extends \Test\Unit
{
	const MARKER = 'e107help-memberlist-';

	/** @var int[] */
	private $members = array();

	protected function _before()
	{
		$sql = e107::getDb();
		$sql->delete('user', "user_name LIKE '".self::MARKER."%'");

		for($i = 1; $i <= 30; $i++)
		{
			$name = self::MARKER.sprintf('%02d', $i);

			$this->members[] = (int) $sql->insert('user', \Test\Rows::user(array(
				'user_name'      => $name,
				'user_loginname' => $name,
				'user_email'     => $name.'@example.test',
				'user_join'      => time(),
			)));
		}
	}

	protected function _after()
	{
		e107::getDb()->delete('user', 'user_id IN ('.implode(',', $this->members).')');
		$this->members = array();
	}

	public function testANewPageSizeKeepsThePlaceRoundedDownToAWholePage()
	{
		$page = $this->postTheListForm(array('records' => '15', 'order' => 'DESC', 'from' => '20'));

		$this->assertTheListStartsAt(15, 'DESC', $page);
	}

	public function testAFlippedSortOrderKeepsThePlace()
	{
		$page = $this->postTheListForm(array('records' => '20', 'order' => 'ASC', 'from' => '20'));

		$this->assertTheListStartsAt(20, 'ASC', $page);
	}

	public function testAPageSizeOutOfRangeIsClampedBeforeThePlaceIsRounded()
	{
		$page = $this->postTheListForm(array('records' => '0', 'order' => 'DESC', 'from' => '30'));

		$this->assertTheListStartsAt(20, 'DESC', $page);
	}

	public function testANegativePostedOffsetStartsAtTheFirstRecord()
	{
		$page = $this->postTheListForm(array('records' => '20', 'order' => 'DESC', 'from' => '-25'));

		$this->assertTheListStartsAt(0, 'DESC', $page);
	}

	public function testANegativeOffsetInTheAddressStartsAtTheFirstRecord()
	{
		$page = $this->renderInBootedCli('user.php', 'error_reporting(E_ALL); ', '', array('e_QUERY' => '-5.20.DESC'));

		$this->assertTheListStartsAt(0, 'DESC', $page);
	}

	/**
	 * @param array<string, string> $post what the member list's form posts
	 * @return string the page
	 */
	private function postTheListForm(array $post)
	{
		return $this->renderInBootedCli('user.php', 'error_reporting(E_ALL); $_POST = '.var_export($post, true).'; ');
	}

	/**
	 * @param int    $from the offset the listing should start at
	 * @param string $order ASC or DESC
	 * @param string $page
	 * @return void
	 */
	private function assertTheListStartsAt($from, $order, $page)
	{
		$first = e107::getDb()->createQueryBuilder()->select('user_name')->from('user')
			->where('user_ban', 0)->orderBy('user_id', $order)
			->setFirstResult($from)->setMaxResults(1)->fetchOne();

		self::assertStringContainsString("name='from' value='".$from."'", $page);
		self::assertStringContainsString((string) $first, $page, 'the member at offset '.$from.' is not listed');
	}
}
