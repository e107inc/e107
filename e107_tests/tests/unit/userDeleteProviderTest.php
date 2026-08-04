<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2026 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */

	/**
	 * Covers the e_user delete() provider contract that usersettings.php's
	 * processUserDelete() consumes: bundled providers now express the WHERE as a
	 * structured, bindable predicate, and that structure binds cleanly through the
	 * query builder (no value inlined into the SQL string).
	 */
	class userDeleteProviderTest extends \Codeception\Test\Unit
	{
		public function testBundledUserProviderUsesStructuredWhere()
		{
			require_once(e_PLUGIN.'user/e_user.php');

			$provider = new user_user();
			$config = $provider->delete(123);

			// 'user' row: update mode, WHERE is a column => value map (bound), not a string.
			$this->assertSame('update', $config['user']['MODE']);
			$this->assertSame(array('user_id' => 123), $config['user']['WHERE']);

			// 'user_extended' row: delete mode, structured WHERE.
			$this->assertSame('delete', $config['user_extended']['MODE']);
			$this->assertSame(array('user_extended_id' => 123), $config['user_extended']['WHERE']);
		}

		public function testStructuredWhereBindsThroughBuilder()
		{
			// Mirror processUserDelete()'s structured-delete path: a column => value
			// map becomes one bound predicate per pair.
			$qb = e107::getDb()->createQueryBuilder()->delete('user_extended');

			foreach(array('user_extended_id' => 123) as $column => $value)
			{
				$qb->where($column, $value);
			}

			$sql = $qb->getSQL();
			$params = $qb->getParameters();

			$this->assertStringContainsString('DELETE FROM `e107_user_extended`', $sql);
			$this->assertStringContainsString('`user_extended_id`', $sql);
			// The id is bound, never concatenated into the statement.
			$this->assertStringNotContainsString('123', $sql);
			$this->assertContains(123, array_values($params));
		}

		public function testStructuredUpdateWhereBindsThroughBuilder()
		{
			// Mirror the structured-update path: SET columns plus a bound WHERE.
			$qb = e107::getDb()->createQueryBuilder()->update('user');
			$qb->set('user_name', 'Deleted-User-123');

			foreach(array('user_id' => 123) as $column => $value)
			{
				$qb->where($column, $value);
			}

			$sql = $qb->getSQL();
			$params = $qb->getParameters();

			$this->assertStringContainsString('UPDATE `e107_user`', $sql);
			$this->assertStringContainsString('`user_name`', $sql);
			$this->assertStringContainsString('`user_id`', $sql);
			// Neither the SET value nor the WHERE id is inlined.
			$this->assertStringNotContainsString('Deleted-User-123', $sql);
			$this->assertStringNotContainsString('123', $sql);
			$this->assertContains('Deleted-User-123', array_values($params));
			$this->assertContains(123, array_values($params));
		}
	}
