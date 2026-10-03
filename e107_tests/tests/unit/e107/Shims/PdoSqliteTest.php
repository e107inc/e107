<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Shims;

use PDO;

class PdoSqliteTest extends \Test\Unit
{
	protected function _before()
	{
		if(!extension_loaded('pdo_sqlite'))
		{
			$this->markTestSkipped('pdo_sqlite is not loaded');
		}

		require_once(e_HANDLER.'Shims/PdoSqlite.php');
	}

	public function testAColumnOfAResultWithRowsIsDescribed()
	{
		$pdo = new PDO('sqlite::memory:', null, null, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
		$meta = PdoSqlite::columnMeta($pdo->query('SELECT 1 AS one'), 0);

		$this->assertSame('one', $meta['name']);
	}

	public function testAColumnOfAnEmptyResultIsDescribedOrAnsweredFalseRatherThanAnError()
	{
		$pdo = new PDO('sqlite::memory:', null, null, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
		$meta = PdoSqlite::columnMeta($pdo->query('SELECT 1 AS one WHERE 0'), 0);

		$this->assertTrue($meta === false || $meta['name'] === 'one', var_export($meta, true));
	}
}
