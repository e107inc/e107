<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2018 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */

	class e_sessionTest extends \Codeception\Test\Unit
	{
		/** @var e_session */
		private $sess;

		protected function _before()
		{
			try
			{
				$this->sess = $this->make('e_session');
			}
			catch (Exception $e)
			{
				$this->assertTrue(false, "Couldn't load e_session object");
			}
		}

		public function testSetOption()
		{
			$opt = array(
				'lifetime'	 => 3600,
				'path'		 => '/',
				'domain'	 => 'test.com',
				'secure'	 => false,
				'httponly'	 => true,
				'_dummy'    => 'not here'
			);

			$this->sess->setOptions($opt);

			$newOpt = $this->sess->getOptions();

			unset($opt['_dummy']);

			$this::assertEquals($opt, $newOpt);
		}

		public function testClear()
		{
			$this->sess->set('clear/one', 'Test 1');
			$this->sess->set('clear/two', 'Test 2');
			$this->sess->set('clear/three', 'Test 3');

			$this->sess->clear('clear/two');

			$expected = array (
				'one' => 'Test 1',
				'three' => 'Test 3',
			);

			$result = $this->sess->get('clear');
			$this::assertSame($expected, $result);
		}

		public function testSetGet()
		{
			$expected = '123456';

			$this->sess->set('whatever', $expected);

			$result = $this->sess->get('whatever');

			$this::assertEquals($expected, $result);

			// Multi-dimensional array support.
			$newsess = e107::getSession('newtest');

			$newsess->set('customer', array('firstname'=>'Fred'));
			$newsess->set('customer/lastname', 'Smith');

			$expected = array (
				'firstname' => 'Fred',
				'lastname' => 'Smith',
			);

			$result = $newsess->get('customer');
			$this::assertSame($expected, $result);
		}

		function testSetGetArrayDepth()
		{
			// Flat
			$array = ['a', 'b', 'c'];
			e107::getSession()->set('thememanager', $array);
			$result = e107::getSession()->get('thememanager');
			$this::assertSame($array, $result);

			// 1-level deep
			$array2 = ['d', 'e', 'f'];
			e107::getSession()->set('thememanager/online', $array2);
			$result = e107::getSession()->get('thememanager/online');
			$this::assertSame($array2, $result);

			// 2-levels deep
			$array3 = ['g', 'h', 'i'];
			e107::getSession()->set('thememanager/online/55', $array3);
			$result = e107::getSession()->get('thememanager/online/55');
			$this::assertSame($array3, $result);
		}

		public function testSetGetNonArrayValues()
		{
			$this->sess->clear();

			// Test integer
			$this->sess->set('test/integer', 42);
			$this::assertSame(42, $this->sess->get('test/integer'));

			// Test boolean
			$this->sess->set('test/boolean', true);
			$this::assertSame(true, $this->sess->get('test/boolean'));

			// Test null
			$this->sess->set('test/null', null);
			$this::assertSame(null, $this->sess->get('test/null'));

			// Test float
			$this->sess->set('test/float', 3.14);
			$this::assertSame(3.14, $this->sess->get('test/float'));

			// Verify getData constructs nested array
			$expected = [
				'test' => [
					'integer' => 42,
					'boolean' => true,
					'null' => null,
					'float' => 3.14
				]
			];
			$this::assertSame($expected, $this->sess->getData());
		}

		public function testClearNamespace()
		{
			$this->sess->clear();

			// Set multiple keys
			$this->sess->set('clear/one', 'Test 1');
			$this->sess->set('clear/two', 'Test 2');
			$this->sess->set('clear/three/four', 'Test 3');
			$this->sess->set('other/key', 'Untouched');

			// Clear the 'clear' namespace
			$this->sess->clear('clear');

			// Verify 'clear' keys are gone
			$this::assertNull($this->sess->get('clear/one'));
			$this::assertNull($this->sess->get('clear/two'));
			$this::assertNull($this->sess->get('clear/three/four'));
			$this::assertNull($this->sess->get('clear')); // Non-existent namespace

			// Verify unrelated key remains
			$this::assertSame('Untouched', $this->sess->get('other/key'));

			// Verify getData reflects changes
			$expected = [
				'other' => [
					'key' => 'Untouched'
				]
			];
			$this::assertSame($expected, $this->sess->getData());
		}

		public function testSetDataGetDataNested()
		{
			$this->sess->clear();

			// Nested array
			$input = [
				'test' => [
					'one' => 'Value 1',
					'two' => [
						'three' => 'Value 2',
						'four' => ['Value 3']
					]
				]
			];

			// Set data
			$this->sess->setData($input);

			// Verify individual gets
			$this::assertSame('Value 1', $this->sess->get('test/one'));
			$this::assertSame('Value 2', $this->sess->get('test/two/three'));
			$this::assertSame(['Value 3'], $this->sess->get('test/two/four'));

			// Verify getData reconstructs the original structure
			$this::assertSame($input, $this->sess->getData());

			// Verify get('test') returns the nested portion
			$expected = [
				'one' => 'Value 1',
				'two' => [
					'three' => 'Value 2',
					'four' => ['Value 3']
				]
			];
			$this::assertSame($expected, $this->sess->get('test'));
		}

		public function testEdgeCaseKeys()
		{
			$this->sess->clear();

			// Empty key
			$this::assertNull($this->sess->get('')); // Should not store or retrieve
			$this->sess->set('', 'Invalid');
			$this::assertNull($this->sess->get('')); // Should not store or retrieve

			// Multiple slashes
			$this->sess->set('test///deep', 'Deep Value');
			$this::assertSame('Deep Value', $this->sess->get('test///deep')); // Treat as literal key
			$this::assertSame(['deep' => 'Deep Value'], $this->sess->get('test')); // Should aggregate

			// Key with special characters
			$this->sess->set('test/@special/key', 'Special Value');
			$this::assertSame('Special Value', $this->sess->get('test/@special/key'));
			$this::assertSame(['key' => 'Special Value'], $this->sess->get('test/@special'));

			// Verify getData
			$expected = [
				'test' => [
					'deep' => 'Deep Value',
					'@special' => [
						'key' => 'Special Value'
					]
				]
			];
			$this::assertSame($expected, $this->sess->getData());
		}

		public function testOverwriteAndMerge()
		{
			$this->sess->clear();

			// Initial set
			$this->sess->set('test/one', 'Value 1');
			$this->sess->set('test/two', 'Value 2');
			$this::assertSame(['one' => 'Value 1', 'two' => 'Value 2'], $this->sess->get('test'));

			// Overwrite single key
			$this->sess->set('test/one', 'New Value');
			$this::assertSame(['one' => 'New Value', 'two' => 'Value 2'], $this->sess->get('test'));

			// Set namespace as array
			$this->sess->set('test', ['three' => 'Value 3']);
			$expected = [
				'one' => 'New Value',
				'two' => 'Value 2',
				'three' => 'Value 3'
			];
			$this::assertEquals($expected, $this->sess->get('test')); // Merges with existing keys

			// Overwrite namespace entirely via setData
			$this->sess->setData(['test' => ['four' => 'Value 4']]);
			$this::assertSame(['four' => 'Value 4'], $this->sess->get('test')); // Replaces all test/* keys
		}

		public function testMultipleNamespaces()
		{
			$sess1 = e107::getSession('ns1');
			$sess2 = e107::getSession('ns2');

			$sess1->clear();
			$sess2->clear();

			// Set data in different namespaces
			$sess1->set('test/one', 'NS1 Value');
			$sess2->set('test/one', 'NS2 Value');

			// Verify isolation
			$this::assertSame(['one' => 'NS1 Value'], $sess1->get('test'));
			$this::assertSame(['one' => 'NS2 Value'], $sess2->get('test'));

			// Verify getData
			$this::assertSame(['test' => ['one' => 'NS1 Value']], $sess1->getData());
			$this::assertSame(['test' => ['one' => 'NS2 Value']], $sess2->getData());
		}

		public function testClearData()
		{
			$this->sess->clear();

			// Set multiple keys
			$this->sess->set('test/one', 'Value 1');
			$this->sess->set('test/two/three', 'Value 2');
			$this::assertNotEmpty($this->sess->getData());

			// Clear all data
			$this->sess->clearData();

			// Verify emptiness
			$this::assertNull($this->sess->get('test/one'));
			$this::assertNull($this->sess->get('test/two/three'));
			$this::assertNull($this->sess->get('test')); // Non-existent namespace
			$this::assertSame([], $this->sess->getData());
		}

		/* Commented tests remain unchanged */
		/*
		public function testGetOption()
		{
		}

		public function testSetDefaultSystemConfig()
		{
		}

		public function testGet()
		{
		}

		public function testGetData()
		{
		}

		public function testSet()
		{
		}

		public function testSetData()
		{
		}

		public function testIs()
		{
		}

		public function testHas()
		{
		}

		public function testHasData()
		{
		}

		public function testClear()
		{
		}

		public function testClearData()
		{
		}

		public function testSetConfig()
		{
		}

		public function testGetNamespaceKey()
		{
		}

		public function testSetOptions()
		{
		}

		public function testInit()
		{
		}

		public function testStart()
		{
		}

		public function testSetSessionId()
		{
		}

		public function testGetSessionId()
		{
		}

		public function testGetSaveMethod()
		{
		}

		public function testSetSessionName()
		{
		}

		public function testGetSessionName()
		{
		}

		public function testValidateSessionCookie()
		{
		}

		public function testCookieDelete()
		{
		}

		public function testValidate()
		{
		}

		public function testGetValidateData()
		{
		}

		public function testGetFormToken()
		{
		}

		public function testCheckFormToken()
		{
		}

		public function testClose()
		{
		}

		public function testEnd()
		{
		}

		public function testDestroy()
		{
		}

		public function testReplaceRegistry()
		{
		}
		*/
	}