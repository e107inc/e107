<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2024 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Focused unit tests for the SQL-injection hardening changes:
 * identifier-allowlist rejection and value escaping at the rewritten sinks.
 * These cover changed lines that the existing suite does not exercise.
 */

class SqlInjectionFixesTest extends \Codeception\Test\Unit
{
	/** @var e_db_mysql */
	protected $db;

	/** @var db_table_admin */
	protected $dta;

	/** @var e_form */
	protected $frm;

	/** @var e107_user_extended */
	protected $ue;

	protected function _before()
	{
		require_once(e_HANDLER . "mysql_class.php");
		require_once(e_HANDLER . "db_table_admin_class.php");

		// The hardened db_FieldList() lives in e_db_mysql (the legacy mysqli
		// driver). e107::getDb() returns the PDO driver, so build and connect
		// an e_db_mysql instance explicitly, mirroring e_db_mysqlTest.
		$this->db = $this->make('e_db_mysql');
		$this->db->__construct();

		/** @var \Helper\DelayedDb $dbHelper */
		$dbHelper = $this->getModule('\Helper\DelayedDb');
		$this->db->db_Connect(
			$dbHelper->_getDbHostname(),
			$dbHelper->_getDbUsername(),
			$dbHelper->_getDbPassword(),
			$dbHelper->_getDbName()
		);

		$this->dta = $this->make('db_table_admin');
		$this->frm = e107::getForm();
		$this->ue  = e107::getUserExt();
	}

	// ---------------------------------------------------------------------
	// e_db_mysql::db_FieldList() identifier allowlist (mysql_class.php ~1869)
	// ---------------------------------------------------------------------

	/**
	 * A table identifier carrying an injection payload (quote, semicolon,
	 * space, stacked query) must be rejected before any query is built.
	 */
	public function testDbFieldListRejectsMaliciousIdentifier()
	{
		$payloads = array(
			"user`; DROP TABLE user; --",
			"user' OR '1'='1",
			"user WHERE 1=1",
			"user UNION SELECT 1",
			"user; DROP TABLE user",
		);

		foreach ($payloads as $bad)
		{
			$this->assertFalse(
				$this->db->db_FieldList($bad, ''),
				'db_FieldList() must reject the injection identifier: ' . $bad
			);
		}
	}

	/**
	 * Regression guard: the relaxed allowlist must still accept a legitimate
	 * plain table identifier (the secondary-database `db`.prefix form is
	 * covered separately by e_db_abstractTest::testSecondaryDatabaseInstance).
	 */
	public function testDbFieldListAcceptsPlainIdentifier()
	{
		$result = $this->db->db_FieldList('user');
		$this->assertNotFalse($result, 'db_FieldList() must accept a valid table name');
		$this->assertIsArray($result);
		$this->assertContains('user_id', $result);
	}

	// ---------------------------------------------------------------------
	// db_table_admin::get_current_table() identifier allowlist (~55)
	// ---------------------------------------------------------------------

	/**
	 * get_current_table() concatenates prefix+name into a backtick-quoted
	 * identifier; a non-identifier name must short-circuit to FALSE.
	 */
	public function testGetCurrentTableRejectsMaliciousIdentifier()
	{
		$payloads = array(
			"user` ; DROP TABLE user; --",
			"user' OR '1'='1",
			"user WHERE 1",
		);

		foreach ($payloads as $bad)
		{
			$this->assertFalse(
				$this->dta->get_current_table($bad),
				'get_current_table() must reject the injection identifier: ' . $bad
			);
		}
	}

	// ---------------------------------------------------------------------
	// db_table_admin::createTable() identifier allowlist (~764)
	// ---------------------------------------------------------------------

	/**
	 * createTable() injects the (renamed) table name into a CREATE TABLE
	 * identifier position. A malicious rename target must be rejected.
	 */
	public function testCreateTableRejectsMaliciousRenameTarget()
	{
		$this->assertFalse(
			$this->dta->createTable('', 'user', false, "evil`(x INT); DROP TABLE user; --"),
			'createTable() must reject a non-identifier rename target'
		);
	}

	// ---------------------------------------------------------------------
	// e107_user_extended::user_extended_setvalue() identifier allowlist (~1712)
	// ---------------------------------------------------------------------

	/**
	 * The extended-field column name is interpolated into an INSERT ... ON
	 * DUPLICATE KEY UPDATE identifier position; a non-identifier name must
	 * cause the setter to bail out with false instead of querying.
	 */
	public function testUserExtendedSetValueRejectsMaliciousFieldName()
	{
		$payloads = array(
			"evil` = (SELECT 1); -- ",
			"evil', user_admin) VALUES (1, 1) -- ",
			"evil WHERE 1=1",
		);

		foreach ($payloads as $bad)
		{
			$this->assertFalse(
				$this->ue->user_extended_setvalue(1, $bad, 'x'),
				'user_extended_setvalue() must reject the injection field name: ' . $bad
			);
		}
	}

	// ---------------------------------------------------------------------
	// e_form::userlist() value escaping + fields allowlist (form_handler ~1824/1859)
	// ---------------------------------------------------------------------

	/**
	 * For a custom class value the WHERE clause embeds it inside a quoted
	 * REGEXP literal; a single quote in the class must be escaped so it
	 * cannot terminate the literal and break out of the clause.
	 */
	public function testUserlistEscapesClassValueInWhere()
	{
		$where = $this->frm->userlist('uname', null, array(
			'classes' => "abc' OR '1'='1",
			'return'  => 'sqlWhere',
		));

		$this->assertIsString($where);
		// The injected single quote must be backslash-escaped, leaving no
		// bare quote that could close the REGEXP string literal early.
		$this->assertStringContainsString("\\'", $where, 'class value quote should be escaped');
		$this->assertStringNotContainsString("(abc' OR '1'='1)", $where, 'unescaped payload must not appear verbatim');
	}

	/**
	 * An attacker-supplied "fields" column list containing non-identifier
	 * characters must be discarded in favour of the safe default list.
	 * userlist() returns an array of records when return=array; the query it
	 * runs uses the sanitized field list, so it must not error out.
	 */
	public function testUserlistFieldsListFallsBackToSafeDefault()
	{
		$result = $this->frm->userlist('uname', null, array(
			'fields' => "user_id,(SELECT password FROM user) AS x",
			'return' => 'array',
		));

		// With the malicious field list rejected and replaced by the default,
		// the query runs cleanly and returns an array (never false/exception).
		$this->assertIsArray($result);
	}
}
