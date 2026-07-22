<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * Regression coverage for the installer's db-name / table-prefix handling.
 *
 * The db name only ever reaches SQL backtick-quoted, and quoteDbName() doubles
 * the backtick (MySQL's own identifier escape), so every name the server itself
 * accepts is admissible; checkDbFields() imposes no character-set restriction
 * on it beyond the legacy ' / ; guard (a semicolon cannot be represented in the
 * PDO DSN). The table prefix is interpolated UNQUOTED into every query
 * (REPLACE INTO {prefix}user), so it must remain a bare identifier. These
 * tests pin both halves of that contract.
 *
 * install.php cannot be require()d (it runs the installer at file scope), so the
 * e_install class body is extracted from source and eval'd under a renamed name,
 * mirroring installStage7HashTest.
 */
class sqliInstallDbFieldsTest extends \Codeception\Test\Unit
{
	/** @var string */
	private static $renamedClass = 'e_install_for_dbfields_test';

	public static function setUpBeforeClass(): void
	{
		if (class_exists(self::$renamedClass, false))
		{
			return;
		}

		$installPhp = realpath(APP_PATH . '/install.php');
		self::assertNotFalse($installPhp, 'install.php must exist at the expected location');

		$source = file_get_contents($installPhp);
		$classBody = self::extractClassBody($source, 'e_install');
		self::assertNotNull($classBody, 'Unable to locate the e_install class definition in install.php');

		$renamed = preg_replace('/\bclass\s+e_install\b/', 'class ' . self::$renamedClass, $classBody, 1);
		eval($renamed);
	}

	private function installer()
	{
		$class = new \ReflectionClass(self::$renamedClass);
		return $class->newInstanceWithoutConstructor();
	}

	public function testAcceptsValidDbAndPrefix()
	{
		$ok = $this->installer()->checkDbFields(array(
			'server' => 'localhost',
			'user'   => 'root',
			'db'     => 'my-site_db1',   // hyphen allowed: db is backtick-quoted
			'prefix' => 'e107_',
		));
		$this->assertTrue($ok, 'A conventional db name and prefix must still pass.');
	}

	public function testAcceptsEmptyPrefix()
	{
		$this->assertTrue(
			$this->installer()->checkDbFields(array('db' => 'sitedb', 'prefix' => '')),
			'An empty table prefix is a legitimate choice and must pass.'
		);
	}

	public function testAcceptsEveryLegalServerSideDbName()
	{
		// MySQL/MariaDB accept all of these (discussion #5819: the v2.3.8-era
		// allowlist rejected the dotted form and broke real installs).
		$legal = array('e107_2.3.8', 'a.b', 'foo bar', 'foo`bar', 'my$db', 'café', '3com', '_lead');

		foreach ($legal as $name)
		{
			$this->assertTrue(
				$this->installer()->checkDbFields(array('db' => $name, 'prefix' => 'e107_')),
				'A db name the server itself accepts must pass: '.$name
			);
		}
	}

	public function testRejectsQuoteAndSemicolonInDbFields()
	{
		foreach (array("site'db", 'site;db') as $name)
		{
			$this->assertFalse(
				$this->installer()->checkDbFields(array('db' => $name, 'prefix' => 'e107_')),
				'The legacy quote/semicolon guard must survive: '.$name
			);
		}
	}

	public function testQuoteDbNameNeutralisesBacktickBreakout()
	{
		$installer = $this->installer();

		$this->assertSame('`sitedb`', $installer->quoteDbName('sitedb'));
		$this->assertSame('`e107_2.3.8`', $installer->quoteDbName('e107_2.3.8'));
		$this->assertSame(
			'`foo`` CHARACTER SET utf8; DROP TABLE x; -- `',
			$installer->quoteDbName('foo` CHARACTER SET utf8; DROP TABLE x; -- '),
			'The identifier-quote character must be doubled, never allowed to close the identifier.'
		);
	}

	public function testRejectsParensInPrefix()
	{
		// prefix is interpolated UNQUOTED (REPLACE INTO {prefix}user), so it must
		// be an even stricter bare identifier.
		$this->assertFalse(
			$this->installer()->checkDbFields(array('db' => 'sitedb', 'prefix' => 'e107_(SELECT')),
			'A prefix with SQL metacharacters must be rejected.'
		);
	}

	public function testRejectsBacktickInPrefix()
	{
		$this->assertFalse(
			$this->installer()->checkDbFields(array('db' => 'sitedb', 'prefix' => 'e1`07')),
			'A backtick in the prefix must be rejected.'
		);
	}

	/**
	 * Token-walk the class definition out of install.php so it can be eval'd
	 * without running the file's top-level installer bootstrap. Copied from
	 * installStage7HashTest.
	 */
	private static function extractClassBody($source, $className)
	{
		$tokens = token_get_all($source);
		$count = count($tokens);

		for ($i = 0; $i < $count; $i++)
		{
			$token = $tokens[$i];
			if (!is_array($token) || $token[0] !== T_CLASS)
			{
				continue;
			}

			$j = $i + 1;
			while ($j < $count && is_array($tokens[$j])
				&& in_array($tokens[$j][0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true))
			{
				$j++;
			}

			if ($j >= $count || !is_array($tokens[$j])
				|| $tokens[$j][0] !== T_STRING
				|| $tokens[$j][1] !== $className)
			{
				continue;
			}

			$start = $i;
			$k = $j + 1;
			while ($k < $count && !(is_string($tokens[$k]) && $tokens[$k] === '{'))
			{
				$k++;
			}
			if ($k >= $count)
			{
				return null;
			}

			$depth = 0;
			$end = null;
			for ($m = $k; $m < $count; $m++)
			{
				$cur = $tokens[$m];
				if (is_string($cur))
				{
					if ($cur === '{')
					{
						$depth++;
					}
					elseif ($cur === '}')
					{
						$depth--;
						if ($depth === 0)
						{
							$end = $m;
							break;
						}
					}
				}
				elseif (is_array($cur) && $cur[0] === T_CURLY_OPEN)
				{
					$depth++;
				}
			}

			if ($end === null)
			{
				return null;
			}

			$body = '';
			for ($n = $start; $n <= $end; $n++)
			{
				$body .= is_array($tokens[$n]) ? $tokens[$n][1] : $tokens[$n];
			}
			return $body;
		}

		return null;
	}
}
