<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * The 2.x update's session re-key step, for a stored row it cannot move.
 *
 * @see https://github.com/e107inc/e107/issues/6691
 */
class update_routinesSessionRekeyTest extends \Codeception\Test\Unit
{
	const ID = 'rekeytest0123456789abcdefABCDEF';

	protected function _before()
	{
		require_once(e_ADMIN . 'update_routines.php');
		require_once(e_HANDLER . 'session_handler.php');
		$this->forget();
	}

	protected function _after()
	{
		$this->forget();
	}

	/**
	 * @return string
	 */
	private function fixtureKeys()
	{
		return "session_id IN ('".self::ID."', '".e_session_db::storageKey(self::ID)."')";
	}

	private function forget()
	{
		e107::getDb()->delete('session', $this->fixtureKeys());
	}

	/**
	 * @param string $key
	 * @param string $data
	 * @param int $expires
	 */
	private function haveRow($key, $data, $expires)
	{
		e107::getDb()->insert('session', array(
			'session_id'      => $key,
			'session_expires' => $expires,
			'session_user'    => 0,
			'session_data'    => base64_encode($data),
		));
	}

	/**
	 * @return array session data by key, for the fixture's raw and hashed keys
	 */
	private function rows()
	{
		$sql = e107::getDb();
		$rows = array();
		$sql->select('session', 'session_id, session_data', $this->fixtureKeys());

		while($row = $sql->fetch())
		{
			$rows[$row['session_id']] = $row['session_data'];
		}

		return $rows;
	}

	/**
	 * @param string $hashed data under the hashed key
	 * @param int $hashedExpires
	 */
	private function updateWithARawRowBehind($hashed, $hashedExpires)
	{
		update_20x_to_latest('do');
		$this->assertTrue(update_20x_to_latest('check'), 'The fixture must have nothing else pending, or the check below proves nothing.');

		$this->haveRow(self::ID, 'raw', time() + 600);
		$this->haveRow(e_session_db::storageKey(self::ID), $hashed, $hashedExpires);
		$this->assertFalse(update_20x_to_latest('check'), 'update_needed() reports by returning false');

		update_20x_to_latest('do');

		$this->assertTrue(update_20x_to_latest('check'), 'A raw row whose hashed key is taken must not keep the update pending after it has run.');
	}

	public function testARawSessionWhoseHashedKeyIsTakenNoLongerKeepsTheUpdatePending()
	{
		$this->updateWithARawRowBehind('hashed', time() + 600);

		$this->assertSame(array(e_session_db::storageKey(self::ID) => base64_encode('hashed')), $this->rows(),
			'The raw row goes and the stored session under the hashed key is left as it was.');
	}

	public function testARawSessionWhoseHashedKeyIsHeldByAnExpiredRowIsMovedThere()
	{
		$this->updateWithARawRowBehind('expired', time() - 60);

		$this->assertSame(array(e_session_db::storageKey(self::ID) => base64_encode('raw')), $this->rows(),
			'The live session takes its hashed key from the expired row rather than being deleted for it.');
	}
}
