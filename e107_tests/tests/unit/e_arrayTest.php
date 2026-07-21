<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2018 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */


	class e_arrayTest extends \Codeception\Test\Unit
	{
		/** @var e_array */
		private $arrObj;

		protected function _before()
		{
			try
			{
				$this->arrObj = $this->make('e_array');
			}
			catch (Exception $e)
			{
				$this->fail("Couldn't load e_array object");
			}
		}


		private function getSitePrefExample()
		{
			$data = '
$data = array (
  \'email_password\' => \'$2y$10$IpizFx.gp5USl98SLXwwbeod3SYF3M3raAQX0y01ETexzoutvdyWW\',
);
';


			return (string) $data;
		}




/*
		public function testLoad()
		{

		}
*/
		public function testUnserialize()
		{

			$src = codecept_data_dir()."unserializeTest.log";
			$stringFile_0 = file_get_contents($src);
			$actual = $this->arrObj->unserialize($stringFile_0);
			$this->assertArrayHasKey('email_password', $actual);


			// Check for legacy (corrupted) link-words preferences.
			$src = codecept_data_dir()."unserializeTest2.log";
			$stringFile_1 = file_get_contents($src);
			$actual = $this->arrObj->unserialize($stringFile_1);
			$this->assertArrayHasKey('lw_context_visibility', $actual);


			// Buggy value test -------.
			$string_1 = "\$data = array(
			\'buggy_array\' => \'some value\',
			);
			";

			$actual = $this->arrObj->unserialize($string_1);
			$this->assertArrayHasKey('buggy_array', $actual);


			// var_export format test with slashes ----
			$string_2 = "array(\'var_export\' => \'some value\',)";
			$actual = $this->arrObj->unserialize($string_2);
			$this->assertArrayHasKey('var_export', $actual);


			// var_export format test without slashes ----
			$string_3 = "array('var_export' => 'some value',)";
			$actual = $this->arrObj->unserialize($string_3);
			$this->assertArrayHasKey('var_export', $actual);


			// json value test.
			$string_4 = '{ "json": "some value" }';
			$actual = $this->arrObj->unserialize($string_4);
			$this->assertArrayHasKey('json', $actual);

			// case linkwords prefs.
			$string_5 = "array (
				'OLDDEFAULT' => '',
				'TITLE' => '',
				'SUMMARY' => 1,
				'BODY' => 1,
				'DESCRIPTION'=> 1,
				'USER_TITLE' => '',
				'USER_BODY' => 1,
				'LINKTEXT' => '',
				'RAWTEXT' => ''
			)";

			$actual = $this->arrObj->unserialize($string_5);
			$this->assertArrayHasKey('TITLE', $actual);


			$tests = array(
				0   => array(
						'string' => $this->getSitePrefExample(),
						'expected' => array('email_password' => '$2y$10$IpizFx.gp5USl98SLXwwbeod3SYF3M3raAQX0y01ETexzoutvdyWW' )
						),
				1   => array(
						'string' => "{\n    \"hello\": \"h\u00e9ll\u00f2 w\u00f2rld\"\n}",
						'expected' => array('hello'=>'héllò wòrld')
						),


			);

			foreach($tests as $var)
			{
				$result = $this->arrObj->unserialize($var['string']);
				$this->assertEquals($var['expected'], $result);
			}

			// test with already an array.

			$input = array('myarray'=>'myvalue');
			$result = $this->arrObj->unserialize($input);
			$this->assertSame($input, $result);
		}
/*

		public function testStore()
		{

		}
*/
		public function testSerialize()
		{

			$pref1      = array('hello'=>'world');
			$result1    = $this->arrObj->serialize($pref1);
			$expected1  = "array (\n  'hello' => 'world',\n)";
			$this->assertEquals($expected1,$result1);

			$pref2      = array();
			$result2    = $this->arrObj->serialize($pref2);
			$expected2  = null;
			$this->assertEquals($expected2,$result2);


			$pref3      = array();
			$result3    = $this->arrObj->serialize($pref3,true);
			$expected3  = null;
			$this->assertEquals($expected3,$result3);


			$pref4      = array();
			$result4    = $this->arrObj->serialize($pref4,'json');
			$expected4  = null;
			$this->assertEquals($expected4,$result4);

			$pref5      = array('hello'=>'world');
			$result5    = $this->arrObj->serialize($pref5,'json');
			$expected5  = "{\n    \"hello\": \"world\"\n}";
			$this->assertEquals($expected5,$result5);

			$pref6      = array('hello'=> mb_convert_encoding('héllò wòrld', 'ISO-8859-1'));
			$result6    = $this->arrObj->serialize($pref6,'json');
			$expected6  = "{\n    \"hello\": \"h\u00e9ll\u00f2 w\u00f2rld\"\n}";

			$this->assertEquals($expected6,$result6);



		}


		/**
		 * Security regression: CVE-2026-57859.
		 *
		 * e_array::unserialize() must not execute attacker-controlled PHP embedded in a
		 * stored "array(...)" value. A poisoned user_prefs column is second-order RCE
		 * through the former eval()-based reconstruction. This payload would set an
		 * environment variable if it executed; the safe parser must reject it and run
		 * nothing.
		 */
		public function testUnserializeDoesNotEvalPayload()
		{
			$canary = 'E107_CVE_2026_57859';
			putenv($canary);

			$payload = "array(0 => putenv('$canary=pwned'))";
			$result  = $this->arrObj->unserialize($payload);

			$executed = (getenv($canary) === 'pwned');
			putenv($canary);

			$this->assertFalse($executed, 'e_array::unserialize() executed attacker PHP via eval() (CVE-2026-57859)');
			$this->assertSame(array(), $result);
		}

		/**
		 * Security regression: CVE-2026-57859, live second-order path.
		 *
		 * A poisoned user_prefs column detonates via e_user_pref::load(), whose
		 * constructor deserializes the raw column value. Feeding it through the real
		 * e_user_model -> e_user_pref chain must not execute the payload.
		 */
		public function testUserPrefLoadDoesNotEvalPayload()
		{
			$canary = 'E107_CVE_2026_57859_USERPREF';
			putenv($canary);

			$payload = "array(0 => putenv('$canary=pwned'))";

			$user = new e_user_model(array('user_id' => 1, 'user_prefs' => $payload));
			new e_user_pref($user);

			$executed = (getenv($canary) === 'pwned');
			putenv($canary);

			$this->assertFalse($executed, 'e_user_pref::load() executed attacker user_prefs via eval() (CVE-2026-57859)');
		}

		/**
		 * BC: legitimate var_export() data must round-trip unchanged through the safe
		 * parser - nested arrays, int / negative-int keys, floats (incl. E notation),
		 * booleans, null, and strings carrying escaped quotes and backslashes.
		 */
		public function testUnserializeVarExportRoundTrip()
		{
			$original = array(
				'name'     => "O'Brien \\ friends",
				'count'    => 42,
				'balance'  => -3.5,
				'ratio'    => 2.5,
				'sci'      => 6.022E23,
				'active'   => true,
				'disabled' => false,
				'note'     => null,
				'empty'    => '',
				7          => 'seven',
				-1         => 'minus one',
				'nested'   => array(
					'a' => array('deep' => 'value'),
					'b' => array(1, 2, 3),
				),
			);

			$serialized = $this->arrObj->serialize($original);
			$this->assertSame($original, $this->arrObj->unserialize($serialized));
		}

		/**
		 * BC: the multi-token forms var_export() emits for edge-case scalars must
		 * round-trip through the safe parser rather than silently blank the whole
		 * array. Covers NUL bytes (rendered as 'a' . "\0" . 'b'), PHP_INT_MIN (an
		 * arithmetic expression as a value, an int-overflow literal as a key), and
		 * the non-finite floats INF / -INF / NAN.
		 */
		public function testUnserializeVarExportEdgeForms()
		{
			// NUL bytes in values and keys.
			$nul = array(
				'name'  => 'joe',
				'blob'  => "secret\0byte",
				'multi' => "a\0\0b",
				'lead'  => "\0x",
				"k\0y"  => 'nulkey',
			);
			$this->assertSame($nul, $this->arrObj->unserialize($this->arrObj->serialize($nul)));

			// PHP_INT_MIN as a value and as a key. -PHP_INT_MAX - 1 == PHP_INT_MIN and
			// is safe on PHP 5.6 (no PHP_INT_MIN constant).
			$intMin       = -PHP_INT_MAX - 1;
			$roundtripped = $this->arrObj->unserialize($this->arrObj->serialize(
				array('min' => $intMin, 'max' => PHP_INT_MAX, $intMin => 'minkey')
			));
			// As a key it round-trips to an int on every supported PHP: var_export emits
			// the overflow literal and PHP coerces the float key to int on array build.
			$this->assertSame('minkey', $roundtripped[$intMin]);
			$this->assertSame(PHP_INT_MAX, $roundtripped['max']);
			// As a value it equals PHP_INT_MIN. On PHP < 7.2 var_export emits the plain
			// overflow literal, which eval() and this parser both read back as a float
			// (matching the historical behaviour), so compare by value, not strict type.
			$this->assertEquals($intMin, $roundtripped['min']);

			// INF / -INF compare by identity.
			$inf = array('pos' => INF, 'neg' => -INF);
			$this->assertSame($inf, $this->arrObj->unserialize($this->arrObj->serialize($inf)));

			// NAN never compares equal, so verify it decoded to a NAN float.
			$nan = $this->arrObj->unserialize($this->arrObj->serialize(array('x' => NAN)));
			$this->assertTrue(isset($nan['x']) && is_float($nan['x']) && is_nan($nan['x']));
		}

		/**
		 * Security: malicious "array(...)" strings that embed executable PHP must fail
		 * closed (return an empty array), never execute, and never round-trip.
		 */
		public function testUnserializeRejectsCodeInjection()
		{
			$payloads = array(
				"array(0 => passthru('echo pwned'))",
				"array('x' => phpinfo())",
				'array(0 => $GLOBALS)',
				"array(PHP_INT_MAX => 1)",
				"array('a' => 1) + array('b' => 2)",
				"array(0 => (function(){ return 1; })())",
				"array(0 => `id`)",
			);

			foreach($payloads as $payload)
			{
				$this->assertSame(array(), $this->arrObj->unserialize($payload), 'Payload should fail closed: '.$payload);
			}
		}
	}
