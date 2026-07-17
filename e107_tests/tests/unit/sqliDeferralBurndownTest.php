<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * Regression coverage for the SQLi audit deferrals closed on
 * e107help/sqli-deferral-burndown:
 *   - admin_ui search-field identifier injection (group 1)
 *   - e_model::load() {ID} conduit (group 2)
 *   - e_front_tree_model::batchUpdate() value side (group 3)
 */
class sqliDeferralBurndownTest extends \Codeception\Test\Unit
{
	/** @var string prefixed scratch table for the batchUpdate test */
	private $batchTable;

	protected function _before()
	{
		$this->batchTable = MPREFIX . 'sqli_burndown_batch';
	}

	protected function _after()
	{
		e107::getDb()->gen('DROP TEMPORARY TABLE IF EXISTS `' . $this->batchTable . '`');
	}

	// ---- Group 1: admin_ui search-field identifier guard ----

	/**
	 * Build a minimal e_admin_ui exposing the protected search-field filter with
	 * stubbed inputs. An anonymous class defined at runtime (after admin_ui.php
	 * is required) avoids resolving e_admin_ui at test-file parse time, when the
	 * e107 autoloader is not yet wired for handler classes.
	 */
	private function makeSearchfieldProbe(array $fields)
	{
		require_once(e_HANDLER . 'admin_ui.php');

		$probe = new class extends e_admin_ui {
			public $probeFields = array();

			public function __construct()
			{
			}

			public function getFields()
			{
				return $this->probeFields;
			}

			public function getQuery($key = null, $default = null)
			{
				return 'needle';
			}

			public function probe($selected)
			{
				return $this->handleListSearchfieldFilter($selected);
			}
		};
		$probe->probeFields = $fields;
		return $probe;
	}

	public function testSearchfieldFilterQuotesDeclaredField()
	{
		$out = $this->makeSearchfieldProbe(array('user_name' => array('type' => 'text', 'search' => true)))
			->probe('user_name');

		$this->assertStringStartsWith('`user_name`', $out, 'A declared field must be backtick-quoted.');
		$this->assertStringContainsString("LIKE '%needle%'", $out);
	}

	public function testSearchfieldFilterRejectsInjectionColumn()
	{
		// filter_options=searchfield__<payload>: an identifier-position breakout.
		$this->assertSame('',
			$this->makeSearchfieldProbe(array('user_name' => array('type' => 'text', 'search' => true)))
				->probe('news_id=1 OR 1=1 #'),
			'An injection string is not a declared field and must yield no filter.');
	}

	public function testSearchfieldFilterRejectsUndeclaredColumn()
	{
		// A syntactically valid identifier that is not a declared field: the
		// allow-list must still reject it (no cross-column probing).
		$this->assertSame('',
			$this->makeSearchfieldProbe(array('user_name' => array('type' => 'text', 'search' => true)))
				->probe('user_password'),
			'A column that is not a declared field must be rejected.');
	}

	// ---- Group 2: e_model::load() {ID} bind ----

	public function testUserModelLoadBindsIdAgainstInjection()
	{
		$usr = $this->make('e_user_model');
		// The {ID} template is "... WHERE u.user_id={ID}" (unquoted numeric).
		// Bound, this whole string is one value: MySQL coerces the leading "0"
		// to int 0, so no row matches. Spliced, "WHERE user_id=0 OR user_id=1"
		// would return user 1. A leading-"1" tautology is unusable here because
		// binding it still coerces to int 1 and legitimately matches user 1.
		$usr->load('0 OR user_id=1', true);

		$this->assertNotSame(1, (int) $usr->getId(),
			'e_model::load() must bind {ID}: an OR-tautology must not resolve to user 1.');
	}

	public function testUserModelLoadStillLoadsRealId()
	{
		$usr = $this->make('e_user_model');
		$usr->load(1, true);

		$this->assertSame(1, (int) $usr->getId(),
			'Binding {ID} must not break a legitimate numeric id.');
	}

	// ---- Group 3: e_front_tree_model::batchUpdate() value binding ----

	private function makeBatchTree()
	{
		$sql = e107::getDb();
		$sql->gen('DROP TEMPORARY TABLE IF EXISTS `' . $this->batchTable . '`');
		$sql->gen('CREATE TEMPORARY TABLE `' . $this->batchTable
			. '` (id INT NOT NULL, a VARCHAR(255) NULL, b VARCHAR(255) NULL)');
		$sql->gen("INSERT INTO `" . $this->batchTable . "` (id, a, b) VALUES (1, 'orig_a', 'orig_b')");

		$tree = new e_front_tree_model();
		$tree->setModelTable('sqli_burndown_batch');
		$tree->setFieldIdName('id');
		return $tree;
	}

	private function batchRow()
	{
		$sql = e107::getDb();
		$sql->gen('SELECT a, b FROM `' . $this->batchTable . '` WHERE id = 1');
		return $sql->fetch();
	}

	public function testBatchUpdateBindsLiteralValueAndBlocksBreakout()
	{
		$tree = $this->makeBatchTree();

		// A quote-breakout payload that, spliced, would also set column b.
		$payload = "x', b='HACKED";
		$tree->batchUpdate('a', $payload, array(1), null, false); // sanitize=false, plain literal

		$row = $this->batchRow();
		$this->assertSame($payload, $row['a'], 'Value must be stored literally (bound), not spliced.');
		$this->assertSame('orig_b', $row['b'], 'No SQL breakout: column b must be untouched.');
	}

	public function testBatchUpdateStillEvaluatesSqlFragmentExpression()
	{
		$tree = $this->makeBatchTree();

		// An explicit SqlFragment remains a raw column-to-column expression.
		$tree->batchUpdate('a', \e107\Database\SqlFragment::raw('b'), array(1), null, false);

		$row = $this->batchRow();
		$this->assertSame('orig_b', $row['a'], 'A SqlFragment expression must still evaluate (a := b).');
	}
}
