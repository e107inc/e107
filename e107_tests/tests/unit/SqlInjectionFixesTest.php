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

	// ---------------------------------------------------------------------
	// Identifier allowlists must be anchored at the absolute end of the
	// subject: a trailing newline must not slip past the '$' anchor.
	// ---------------------------------------------------------------------

	/**
	 * '$' without the D modifier also matches before a trailing newline, so
	 * "user\n" passed the allowlist and reached SHOW COLUMNS.
	 */
	public function testDbFieldListRejectsTrailingNewline()
	{
		$this->assertFalse(
			$this->db->db_FieldList("user\n", ''),
			'db_FieldList() must reject an identifier with a trailing newline'
		);
	}

	// ---------------------------------------------------------------------
	// e_user_model::load() int-cast of the unquoted {ID} context (user_model ~1241)
	// ---------------------------------------------------------------------

	/**
	 * The query template embeds {ID} unquoted ("WHERE u.user_id={ID}"), so the
	 * id must be int-cast. A tautology payload must collapse to 0 and match no
	 * row instead of returning the first user in the table.
	 */
	public function testUserModelLoadIntCastsUserId()
	{
		$payloads = array(
			'0 OR 1=1',
			'0 OR 1=1 -- ',
			'0 UNION SELECT 1',
		);

		foreach($payloads as $bad)
		{
			$user = $this->make('e_user_model');
			$user->load($bad);
			$this->assertEmpty(
				$user->getId(),
				'e_user_model::load() must not match a row for the payload: '.$bad
			);
		}
	}

	/**
	 * Regression guard for the int-cast: a numeric-string id (what routing and
	 * $_GET always hand over) must still load its user.
	 */
	public function testUserModelLoadStillAcceptsNumericStringId()
	{
		$user = $this->make('e_user_model');
		$user->load('1');
		$this->assertEquals(1, $user->getId(), 'e_user_model::load() must still accept a numeric-string id');
	}

	// ---------------------------------------------------------------------
	// e_front_tree_model::batchUpdate() field allowlist (model_class ~4039)
	// ---------------------------------------------------------------------

	/**
	 * $field lands unquoted in the SET clause. A non-identifier field must
	 * short-circuit to false before the UPDATE is built.
	 */
	public function testBatchUpdateRejectsMaliciousField()
	{
		$tree = $this->make('e_front_tree_model');

		$payloads = array(
			"user_ban` = 1, `user_admin",
			"user_ban WHERE 1=1",
			"user_ban=1, user_perms='0'",
			"user_ban'",
			"user_ban\n",
			array('user_ban'),
		);

		foreach($payloads as $bad)
		{
			$this->assertFalse(
				$tree->batchUpdate($bad, 'x', array(1)),
				'batchUpdate() must reject the injection field: '.var_export($bad, true)
			);
		}
	}

	// ---------------------------------------------------------------------
	// e_admin_controller_ui::isFieldIdentifier() (admin_ui ~3053)
	// The shared gate for etrigger_batch and the "search in field" filter.
	// ---------------------------------------------------------------------

	/**
	 * A crafted etrigger_batch field name must be rejected, whether it carries
	 * SQL metacharacters or is merely a column the controller never declared.
	 */
	public function testIsFieldIdentifierRejectsCraftedBatchField()
	{
		$probe = $this->fieldProbe();

		$payloads = array(
			"user_name` = 1, `user_admin",
			"user_name WHERE 1=1",
			"user_name'",
			"user_name, user_perms",
			"user_name;DROP TABLE user",
			"(SELECT 1)",
			"1=1",
			"user_name\n",           // trailing newline must not pass the anchor
			"u.user_name.extra",     // only a single alias prefix is legal
			"user_password",         // well-formed, but not a declared field
			"",
			null,
			array('user_name'),
		);

		foreach($payloads as $bad)
		{
			$this->assertFalse(
				$probe->checkFieldIdentifier($bad),
				'isFieldIdentifier() must reject: '.var_export($bad, true)
			);
		}
	}

	/**
	 * BC guard, and the reason this round exists: every field the controller
	 * genuinely declares must be accepted - including the dotted alias keys
	 * core really ships (e107_admin/comment.php declares 'u.user_name').
	 */
	public function testIsFieldIdentifierAcceptsDeclaredFields()
	{
		$probe = $this->fieldProbe();

		$good = array(
			'user_id',
			'user_name',
			'u.user_name',
			'options',
			'checkboxes',
		);

		foreach($good as $field)
		{
			$this->assertTrue(
				$probe->checkFieldIdentifier($field),
				'isFieldIdentifier() must accept the declared field: '.$field
			);
		}
	}

	// ---------------------------------------------------------------------
	// news.php 'item' pre-auth SQLi (news ~1507/1530)
	// ---------------------------------------------------------------------

	/**
	 * The release blocker. news.php cannot be included from a unit test (it
	 * instantiates news_front and renders at file scope), and renderDefaultTemplate()
	 * is private, so this asserts on the live (comment-stripped) source instead:
	 * the 'item' action must int-cast the subaction before it reaches the
	 * unquoted "n.news_id=" position, so `0 OR sleep(5)` cannot survive.
	 */
	public function testNewsItemActionIntCastsNewsId()
	{
		$live = $this->liveSource(e_PLUGIN.'news/news.php');

		$this->assertStringNotContainsString(
			'n.news_id=".$this->subAction."',
			$live,
			'news.php must never concatenate the raw subaction into the news_id predicate'
		);

		$body = $this->methodSource(e_PLUGIN.'news/news.php', 'renderDefaultTemplate');
		$this->assertNotEmpty($body, 'news_front::renderDefaultTemplate() not found');

		$item = strstr($body, 'case "item"');
		$this->assertNotEmpty($item, 'the "item" case not found in renderDefaultTemplate()');

		$this->assertStringContainsString(
			'$sub_action = intval($this->subAction);',
			$item,
			'the "item" action must int-cast the news id'
		);
		$this->assertStringContainsString(
			'n.news_id=".$sub_action."',
			$item,
			'the "item" query must use the int-cast id'
		);
	}

	/**
	 * The same subaction reaches an unquoted numeric position in several other
	 * live queries; none of them may carry the raw value.
	 */
	public function testNewsUnquotedSubActionSinksAreIntCast()
	{
		$live = $this->liveSource(e_PLUGIN.'news/news.php');

		$payloads = array(
			'n.news_id=".$this->subAction',
			'n.news_category=".$this->subAction',
			'n.news_category={$this->subAction}',
			'n.news_id={$this->subAction}',
		);

		foreach($payloads as $bad)
		{
			$this->assertStringNotContainsString($bad, $live, 'unescaped subaction sink in news.php: '.$bad);
		}
	}

	// ---------------------------------------------------------------------
	// users_extended.php beforeUpdate()/beforeCreate() name validation
	// ---------------------------------------------------------------------

	/**
	 * BC lock for a break that actually shipped and was caught in review: an
	 * inline (AJAX) edit posts only the changed field, so beforeUpdate() must
	 * validate user_extended_struct_name ONLY when it is actually submitted.
	 * An unconditional guard rejected every inline edit.
	 *
	 * The admin page cannot be included from a unit test, so this asserts on
	 * the live (comment-stripped) method source.
	 */
	public function testUsersExtendedBeforeUpdateOnlyValidatesNameWhenPosted()
	{
		$src = $this->methodSource(e_ADMIN.'users_extended.php', 'beforeUpdate');
		$this->assertNotEmpty($src, 'user_extended_struct_ui::beforeUpdate() not found');

		$flat = preg_replace('/\s+/', ' ', $src);

		$this->assertStringContainsString(
			"preg_match('/^[A-Za-z0-9_]+\$/D'",
			$flat,
			'beforeUpdate() must validate the extended-field name identifier'
		);

		$this->assertStringContainsString(
			"isset(\$new_data['user_extended_struct_name']) && !preg_match(",
			$flat,
			'beforeUpdate() must skip the name check when the name is not posted (inline edit)'
		);
	}

	/**
	 * The create path always posts the name, so its guard is unconditional.
	 */
	public function testUsersExtendedBeforeCreateValidatesName()
	{
		$src = $this->methodSource(e_ADMIN.'users_extended.php', 'beforeCreate');
		$this->assertNotEmpty($src, 'user_extended_struct_ui::beforeCreate() not found');

		$this->assertStringContainsString(
			"preg_match('/^[A-Za-z0-9_]+\$/D'",
			preg_replace('/\s+/', ' ', $src),
			'beforeCreate() must validate the extended-field name identifier'
		);
	}

	// ---------------------------------------------------------------------
	// install.php e_install::check_name() (install ~2277)
	// ---------------------------------------------------------------------

	/**
	 * BC guard: a hyphenated database name is legal MySQL (it only ever reaches
	 * a backtick-quoted position) and must still pass the non-strict check.
	 */
	public function testInstallCheckNameAcceptsHyphenatedDbName()
	{
		$probe = $this->installProbe();

		$good = array('e107', 'e107_db', 'my-db', 'my-db-2', 'MyDb_1');

		foreach($good as $name)
		{
			$this->assertTrue(
				$probe->check_name($name, false, false),
				'check_name() must accept the legitimate db name: '.$name
			);
		}
	}

	/**
	 * A table prefix reaches an unquoted identifier position, so the strict
	 * check must reject anything outside [A-Za-z0-9_] - including the hyphen.
	 */
	public function testInstallCheckNameRejectsMetacharacterPrefix()
	{
		$probe = $this->installProbe();

		$bad = array(
			"e107_`; DROP TABLE user; --",
			"e107_' OR '1'='1",
			"e107_ WHERE 1=1",
			"e107_;DROP",
			"e107 _",
			"e107-_",           // hyphen is unquoted-unsafe in the prefix
			"e107_\n",          // trailing newline must not pass the anchor
			"e107_\0",
		);

		foreach($bad as $name)
		{
			$this->assertFalse(
				$probe->check_name($name, true, true),
				'check_name() must reject the injection prefix: '.var_export($name, true)
			);
		}
	}

	/**
	 * Regression guard for the strict path and the pre-existing contract:
	 * ordinary prefixes pass, the blank-string result still follows $blank_ok,
	 * and the numeric-then-e rule is untouched.
	 */
	public function testInstallCheckNamePreservesExistingContract()
	{
		$probe = $this->installProbe();

		$this->assertTrue($probe->check_name('e107_', true, true), 'the default prefix must pass');
		$this->assertTrue($probe->check_name('e1_', true, true));
		$this->assertTrue($probe->check_name('', true), 'blank must follow $blank_ok');
		$this->assertFalse($probe->check_name('', false), 'blank must follow $blank_ok');
		$this->assertFalse($probe->check_name('1e7', false, false), 'the numeric-then-e rule must survive');
	}

	// ---------------------------------------------------------------------
	// helpers
	// ---------------------------------------------------------------------

	/**
	 * Source of $file with comments removed, so an assertion cannot be
	 * satisfied (or defeated) by commented-out code.
	 *
	 * @param string $file
	 * @return string
	 */
	protected function liveSource($file)
	{
		$out = '';

		foreach(token_get_all(file_get_contents($file)) as $token)
		{
			if(is_array($token))
			{
				if($token[0] === T_COMMENT || $token[0] === T_DOC_COMMENT)
				{
					continue;
				}
				$out .= $token[1];
			}
			else
			{
				$out .= $token;
			}
		}

		return $out;
	}

	/**
	 * Source of a named function/method in $file, comments removed, from the
	 * 'function' keyword to its matching closing brace.
	 *
	 * @param string $file
	 * @param string $name
	 * @param integer $occurrence [optional] 0-based, for overloaded names
	 * @return string empty when not found
	 */
	protected function methodSource($file, $name, $occurrence = 0)
	{
		$tokens = token_get_all(file_get_contents($file));
		$count = count($tokens);
		$hits = 0;

		for($i = 0; $i < $count; $i++)
		{
			if(!is_array($tokens[$i]) || $tokens[$i][0] !== T_FUNCTION)
			{
				continue;
			}

			$j = $i + 1;
			while($j < $count && is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE)
			{
				$j++;
			}

			if($j >= $count || !is_array($tokens[$j]) || $tokens[$j][0] !== T_STRING || $tokens[$j][1] !== $name)
			{
				continue;
			}

			if($hits++ !== $occurrence)
			{
				continue;
			}

			$out = '';
			$depth = 0;
			$started = false;

			for($k = $i; $k < $count; $k++)
			{
				if(is_array($tokens[$k]) && ($tokens[$k][0] === T_COMMENT || $tokens[$k][0] === T_DOC_COMMENT))
				{
					continue;
				}

				$text = is_array($tokens[$k]) ? $tokens[$k][1] : $tokens[$k];
				$out .= $text;

				if($text === '{')
				{
					$depth++;
					$started = true;
				}
				elseif($text === '}')
				{
					$depth--;
					if($started && $depth === 0)
					{
						return $out;
					}
				}
			}

			return $out;
		}

		return '';
	}

	/**
	 * install.php cannot be included (it bootstraps and runs the installer), so
	 * lift check_name() out of the shipped source into a standalone probe. The
	 * method has no dependency on $this, so the extracted code is the real one.
	 *
	 * @return object exposing check_name()
	 */
	protected function installProbe()
	{
		if(!class_exists('SqlInjectionFixesInstallProbe', false))
		{
			$src = $this->methodSource(e_BASE.'install.php', 'check_name');
			$this->assertNotEmpty($src, 'e_install::check_name() not found in install.php');
			eval('class SqlInjectionFixesInstallProbe { '.$src.' }');
		}

		return new SqlInjectionFixesInstallProbe();
	}

	/**
	 * Exposes the batch/search field gate, with a declared field set that mirrors
	 * what core really ships - including the dotted alias key ('u.user_name' is
	 * declared by e107_admin/comment.php).
	 *
	 * eval'd like installProbe(): the unit loader includes this file and reflects
	 * every class token in it, so a file-scope class cannot reference admin_ui.php.
	 * isFieldIdentifier()'s only $this dependency is getFields(), so the extracted
	 * code is the real one.
	 *
	 * @return object exposing checkFieldIdentifier()
	 */
	protected function fieldProbe()
	{
		if(!class_exists('SqlInjectionFixesFieldProbe', false))
		{
			$src = $this->methodSource(e_HANDLER.'admin_ui.php', 'isFieldIdentifier');
			$this->assertNotEmpty($src, 'e_admin_controller_ui::isFieldIdentifier() not found in admin_ui.php');

			$probe = <<<'PROBE'
class SqlInjectionFixesFieldProbe {
	protected $fields = array(
		'checkboxes'  => array('title' => '', 'type' => null, 'forced' => true),
		'user_id'     => array('title' => 'ID', 'type' => 'number'),
		'user_name'   => array('title' => 'Name', 'type' => 'text'),
		'u.user_name' => array('title' => 'User', 'type' => 'user', 'noedit' => true),
		'options'     => array('title' => '', 'type' => null, 'forced' => true),
	);
	public function getFields() { return $this->fields; }
	public function checkFieldIdentifier($field) { return $this->isFieldIdentifier($field); }
PROBE;

			eval($probe."\n".$src."\n}");
		}

		return new SqlInjectionFixesFieldProbe();
	}
}
