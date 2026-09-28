<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2026 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */

	use E107\PhpStan\DefinedConstants;

	/**
	 * Unit tests for the constant reader behind the PHPStan gate (e107_tests/_tools/phpstan), which tells the
	 * analyser what e107 defines at run time so that only a constant nothing defines is reported.
	 *
	 * _before() loads e107_tests/_tools/phpstan/src (PHP 8.1-only), so never a legacy cell.
	 *
	 * @group requires-modern-php
	 */
	class DefinedConstantsTest extends \Test\Unit
	{

		protected function _before()
		{
			require_once(e_BASE.'e107_tests/_tools/phpstan/src/DefinedConstants.php');
		}

		/**
		 * @param array $files path => source
		 * @return array name => type
		 */
		private function typesOf($files)
		{
			$constants = new DefinedConstants();
			foreach($files as $path => $source)
			{
				$constants->read($path, $source);
			}

			return $constants->types();
		}

		public function testDefineInsideAMethodIsRead()
		{
			$source = '<?php class e107 { function prepare() { define("e_PLUGIN", $this->base . "e107_plugins/"); } }';

			$this->assertSame(array('e_PLUGIN' => 'mixed'), $this->typesOf(array('e107_class.php' => $source)));
		}

		public function testALiteralValueGivesItsType()
		{
			$source = "<?php
				define('A_STRING', 'text');
				define('AN_INT', 3);
				define('A_NEGATIVE', -1);
				define('A_FLOAT', 1.5);
				define('A_BOOL', FALSE);
				define('A_NULL', null);
				define('AN_EXPRESSION', 'a' . 'b');
				\\define('QUALIFIED', true);
			";

			$this->assertSame(array(
				'AN_EXPRESSION' => 'mixed',
				'AN_INT'        => 'int',
				'A_BOOL'        => 'bool',
				'A_FLOAT'       => 'float',
				'A_NEGATIVE'    => 'int',
				'A_NULL'        => 'null',
				'A_STRING'      => 'string',
				'QUALIFIED'     => 'bool',
			), $this->typesOf(array('defines.php' => $source)));
		}

		public function testEveryValueANameIsGivenJoinsItsType()
		{
			$types = $this->typesOf(array(
				'one.php'   => "<?php define('TWICE', 1); define('SOMETIMES_UNKNOWN', 1);",
				'two.php'   => "<?php if (!defined('TWICE')) { define('TWICE', 'one'); } define('SOMETIMES_UNKNOWN', \$value);",
			));

			$this->assertSame(array('SOMETIMES_UNKNOWN' => 'mixed', 'TWICE' => 'int|string'), $types);
		}

		public function testWhatIsNotACallToDefineIsSkipped()
		{
			$source = "<?php
				\$registry->define('METHOD', 1);
				Registry::define('STATIC_METHOD', 1);
				function define(\$name, \$value) {}
				if (defined('ONLY_TESTED')) {}
				define(\$name, 1);
				define('NOT' . 'LITERAL', 1);
				define('NOT-AN-IDENTIFIER', 1);
				// define('COMMENTED_OUT', 1);
			";

			$this->assertSame(array(), $this->typesOf(array('noise.php' => $source)));
		}

		public function testALanguageFileDefinesTheKeysItReturns()
		{
			$types = $this->typesOf(array(
				'e107_languages/English/lan_admin.php'             => "<?php\nif (!defined('e107_INIT')) { exit; }\nreturn ['LAN_SHORT' => 'a', \"LAN_DOUBLE\" => 'b', 'LAN_COUNT' => 3, 'LAN_NESTED' => ['LAN_INNER' => 'c'], 'LAN_LAST' => 'd'];",
				'e107_plugins/forum/languages/English_front.php'   => "<?php return array('LAN_FORUM_1' => 'Forum');",
				'e107_themes/voux/languages/English/English.php'   => "<?php return array('LAN_THEME_1' => 'Voux');",
			));

			$this->assertSame(array(
				'LAN_COUNT'   => 'int',
				'LAN_DOUBLE'  => 'string',
				'LAN_FORUM_1' => 'string',
				'LAN_LAST'    => 'string',
				'LAN_NESTED'  => 'mixed',
				'LAN_SHORT'   => 'string',
				'LAN_THEME_1' => 'string',
			), $types);
		}

		public function testOnlyALanguageFilesOwnReturnDefinesKeys()
		{
			$types = $this->typesOf(array(
				'e107_plugins/forum/forum_setup.php'           => "<?php return array('NOT_A_LAN' => 1);",
				'e107_plugins/forum/languages.php'             => "<?php return array('NOR_THIS' => 1);",
				'e107_languages/English/lan_functions.php'     => "<?php function lans() { return array('INSIDE_A_FUNCTION' => 'x'); }",
			));

			$this->assertSame(array(), $types);
		}
	}
