<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * @see https://github.com/e107inc/e107/issues/6808
 */
class e_userpermsTest extends \Codeception\Test\Unit
{
	use \Test\BootedCli;

	protected function _before()
	{
		include_once(e_HANDLER . 'user_handler.php');
	}

	public function testAnEmptyCodePassesAnAdminHoldingAPermissionWhicheverPageSavedThem()
	{
		$this::assertTrue(e_userperms::simulateHasAdminPerms('', 'I.5.J.P14'), 'saved from Users, with no dot at the end');
		$this::assertTrue(e_userperms::simulateHasAdminPerms('', 'I.5.J.P14.'), 'saved from Administrators');
		$this::assertTrue(e_userperms::simulateHasAdminPerms('', '0'), 'a main admin');
		$this::assertFalse(e_userperms::simulateHasAdminPerms('', ''), 'no permissions at all');
		$this::assertFalse(e_userperms::simulateHasAdminPerms('', '.'), 'nothing but a dot');
	}

	public function testAnEmptyAlternativePassesAsAnEmptyCodeDoes()
	{
		$this::assertTrue(e_userperms::simulateHasAdminPerms('X|', 'I.5'));
		$this::assertTrue(e_userperms::simulateHasAdminPerms('X|', 'I.5.'));
		$this::assertTrue(e_userperms::simulateHasAdminPerms('|I', 'X'), 'a leading pipe leaves an empty alternative, which passes any admin');
		$this::assertFalse(e_userperms::simulateHasAdminPerms('X|Y', 'I.5.'));
	}

	public function testATrailingDotChangesNoAnswerForARealCode()
	{
		foreach(array('I.5.J.P14', 'I.5.J.P14.') as $stored)
		{
			$this::assertTrue(e_userperms::simulateHasAdminPerms('P14', $stored), $stored);
			$this::assertTrue(e_userperms::simulateHasAdminPerms('X|5', $stored), $stored);
			$this::assertFalse(e_userperms::simulateHasAdminPerms('P1', $stored), $stored);
			$this::assertFalse(e_userperms::simulateHasAdminPerms('0', $stored), $stored);
		}
	}

	public function testSerializePermsWritesTheFormAdministratorsSaves()
	{
		$tp = e107::getParser();

		$this::assertSame('I.5.J.P14.', e_userperms::serializePerms(array('I', '5', '', 'J', 'P14'), false, $tp));
		$this::assertSame('0', e_userperms::serializePerms(array('I', '0', '5'), true, $tp), 'a main admin is stored as 0 alone');
		$this::assertSame('I.5.', e_userperms::serializePerms(array('I', '0', '5'), false, $tp), 'a saver who is not a main admin cannot grant it');
		$this::assertSame('I.5.', e_userperms::serializePerms(array('I', "0 ", "0\f", '&#48;', "0\x01", " 5"), false, $tp), 'a 0 is judged in the form the row would hold it, so no padding or encoding carries it past the drop');
		$this::assertSame('H.J.', e_userperms::serializePerms(array('0.H', 'J.0', '0.'), false, $tp), 'each piece between dots is judged, as the check reads it, so a dot carries no 0 past the drop');
		$this::assertSame('H.', e_userperms::serializePerms(array('0[h]<x></x>[/h]', 'H'), false, $tp), 'a code a second encoding would change is dropped, since Users -> Edit encodes the stored string again');
		$this::assertSame('', e_userperms::serializePerms(array(), true, $tp));
	}

	public function testUpdatePermsDropsOnlyTheMainAdminCodeForASaverWhoIsNotOne()
	{
		$id = (int) e107::getDb()->insert('user', array(
			'user_name'      => 'eupupdate'.uniqid(),
			'user_loginname' => 'eupupdate'.uniqid(),
			'user_email'     => uniqid().'@example.test',
			'user_join'      => time(),
			'user_admin'     => 1,
			'user_perms'     => 'I.',
		));

		try
		{
			$php = "e107::coreLan('administrator', true); \$_POST['ad_name'] = 'eupupdate'; ";
			$php .= "e107::getUser()->setData(array('user_perms' => '4.3.') + e107::user(USERID)); ";
			$php .= "e107::getUserPerms()->updatePerms(".$id.", array('I', '0', '0 ', '5')); echo '@@SAVED@@';";

			list($output, $status) = $this->runInBootedCliDefining(array('ADMINPERMS' => '4.3.'), $php);

			$this::assertSame(0, $status, implode("\n", $output));
			$this::assertStringContainsString('@@SAVED@@', implode("\n", $output));
			$this::assertSame('I.5.', (string) e107::getDb()->retrieve('user', 'user_perms', 'user_id = '.$id));
		}
		finally
		{
			e107::getDb()->delete('user', 'user_id = '.$id);
		}
	}
}
