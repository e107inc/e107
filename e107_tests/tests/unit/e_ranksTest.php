<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

class e_ranksTest extends \Codeception\Test\Unit
{
	/** @var array */
	private $storedRanks = array();

	/** @var int[] */
	private $userIds = array();

	protected function _before()
	{
		$this->storedRanks = e107::getDb()->retrieve('generic', '*', "gen_type = 'user_rank_data'", true) ?: array();
		$this->deleteRanks();
	}

	protected function _after()
	{
		$this->deleteRanks();

		foreach($this->storedRanks as $row)
		{
			e107::getDb()->insert('generic', $row);
		}

		if($this->userIds)
		{
			e107::getDb()->delete('user', 'user_id IN ('.implode(',', array_map('intval', $this->userIds)).')');
		}

		e107::getCache()->clear_sys('nomd5_user_ranks');
	}

	public function testAModeratorRankMissingFromTheTableFallsBackToTheDefault()
	{
		$this->seedRank(1, 'Site Owner', 'English_main_admin.png');
		$this->seedRank(2, 'Staff', 'English_admin.png');
		$this->seedLevels();

		$rank = (new e_ranks(true))->getRanks($this->member(0, ''), true);

		$this::assertStringContainsString("English_moderator.png'", $rank['special']);
		$this::assertNotSame('', $rank['name']);
	}

	public function testAdminRanksMissingFromTheTableFallBackToTheDefaults()
	{
		$this->seedLevels();
		$ranks = new e_ranks(true);

		$this::assertStringContainsString("English_main_admin.png'", $ranks->getRanks($this->member(1, '0'))['special']);
		$this::assertStringContainsString("English_admin.png'", $ranks->getRanks($this->member(1, 'x'))['special']);
	}

	public function testASpecialRankInTheTableIsKeptOverTheDefault()
	{
		$this->seedRank(3, 'Helper', 'lev5.png');
		$this->seedLevels();

		$rank = (new e_ranks(true))->getRanks($this->member(0, ''), true);

		$this::assertStringContainsString("lev5.png'", $rank['special']);
		$this::assertSame('Helper', $rank['name']);
	}

	/**
	 * @param int $special 1 main admin, 2 admin, 3 moderator, 0 a level
	 * @param string $name
	 * @param string $image
	 * @param int $thresh
	 * @return void
	 */
	private function seedRank($special, $name, $image, $thresh = 0)
	{
		e107::getDb()->insert('generic', array(
			'gen_type'      => 'user_rank_data',
			'gen_datestamp' => $special,
			'gen_user_id'   => 0,
			'gen_ip'        => $name,
			'gen_intdata'   => $thresh,
			'gen_chardata'  => $image,
		));
	}

	/**
	 * @return void
	 */
	private function seedLevels()
	{
		$this->seedRank(0, 'Newcomer', 'lev1.png', 20);
		$this->seedRank(0, 'Regular', 'lev2.png', 100);
	}

	/**
	 * @param int $admin
	 * @param string $perms
	 * @return int the new member's user_id
	 */
	private function member($admin, $perms)
	{
		$name = 'e_ranksTest'.count($this->userIds);
		$id = (int) e107::getDb()->insert('user', \Test\Rows::user(array(
			'user_name'      => $name,
			'user_loginname' => $name,
			'user_admin'     => $admin,
			'user_perms'     => $perms,
		)));
		$this->userIds[] = $id;

		return $id;
	}

	/**
	 * @return void
	 */
	private function deleteRanks()
	{
		e107::getDb()->delete('generic', "gen_type = 'user_rank_data'");
	}
}
