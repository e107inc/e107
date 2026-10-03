<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2018 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */


	class e_shortcodeTest extends \Codeception\Test\Unit
	{
		protected function _before()
		{
			require_once(__DIR__ . '/fixtures/ShortcodeConstructorOrderFixture.php');
		}

		public function testPropertyStoredBeforeTheParentConstructorSurvivesIt()
		{
			$batch = new ShortcodeConstructorOrderFixture(true);

			$this->assertSame(array('layout' => 'wide'), $batch->themeoptions);
		}

		public function testValueStoreWorksWhenTheParentConstructorNeverRuns()
		{
			$batch = new ShortcodeConstructorOrderFixture(false);

			$this->assertSame(array('layout' => 'wide'), $batch->getScVar('themeoptions'));

			$batch->setScVar('one', 1)->addScVars(array('two' => 2));
			$this->assertSame(array('themeoptions' => array('layout' => 'wide'), 'one' => 1, 'two' => 2), $batch->getScVars());

			$batch->unsetScVar('one');
			$this->assertFalse($batch->issetScVar('one'));
			$this->assertTrue(isset($batch->two));

			$batch->emptyScVars();
			$this->assertSame(array(), $batch->getScVars());
		}

		public function testEveryStoreMethodWorksAsTheFirstCallWithoutTheParentConstructor()
		{
			$batch = new ShortcodeEmptyConstructorFixture();
			$this->assertNull($batch->getScVar('missing'));

			$batch = new ShortcodeEmptyConstructorFixture();
			$this->assertSame(array(), $batch->getScVars());

			$batch = new ShortcodeEmptyConstructorFixture();
			$this->assertFalse($batch->issetScVar('missing'));

			$batch = new ShortcodeEmptyConstructorFixture();
			$this->assertSame($batch, $batch->unsetScVar('missing'));

			$batch = new ShortcodeEmptyConstructorFixture();
			$this->assertSame($batch, $batch->emptyScVars());

			$batch = new ShortcodeEmptyConstructorFixture();
			$batch->addScVars(array('two' => 2));
			$this->assertSame(array('two' => 2), $batch->getScVars());
		}
	}
