<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2020 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

namespace e107\Factories;


use e107\SessionHandlers\DatabaseSessionHandler;
use e107\SessionHandlers\FilesSessionHandler;
use e107\SessionHandlers\NonblockingFilesSessionHandler;

class SessionHandlerFactoryTest extends \Codeception\Test\Unit
{
	public function testGetImplementations()
	{
		$implementations = SessionHandlerFactory::getImplementations();
		$this->assertContains(DatabaseSessionHandler::class, $implementations);
		$this->assertContains(FilesSessionHandler::class, $implementations);
		$this->assertContains(NonblockingFilesSessionHandler::class, $implementations);
	}

	public function testGetDefaultImplementationIsDatabaseWhenDatabaseIsSetUp()
	{
		$implementation = SessionHandlerFactory::getDefaultImplementation();
		$this->assertEquals(DatabaseSessionHandler::class, $implementation);
	}

	public function testGetDefaultImplementationFollowsPref()
	{
		\e107::getConfig()->set('session_handler', FilesSessionHandler::class)->save(false, true);

		$implementation = SessionHandlerFactory::getDefaultImplementation();
		$this->assertEquals(FilesSessionHandler::class, $implementation);

		\e107::getConfig()->set('session_handler', DatabaseSessionHandler::class)->save(false, true);

		$implementation = SessionHandlerFactory::getDefaultImplementation();
		$this->assertEquals(DatabaseSessionHandler::class, $implementation);
	}

	public function testGetDefaultImplementationChoosesDefaultIfNoPref()
	{
		\e107::getConfig()->remove('session_handler')->save(false, true);

		$implementation = SessionHandlerFactory::getDefaultImplementation();
		$this->assertEquals(DatabaseSessionHandler::class, $implementation);

		\e107::getConfig()->set('session_handler', DatabaseSessionHandler::class)->save(false, true);
	}

	public function testMakeMakesDefaultImplementation()
	{
		\e107::getConfig()->set('session_handler', FilesSessionHandler::class)->save(false, true);

		$object = SessionHandlerFactory::make();
		$this->assertTrue($object instanceof FilesSessionHandler);

		\e107::getConfig()->set('session_handler', DatabaseSessionHandler::class)->save(false, true);

		$object = SessionHandlerFactory::make();
		$this->assertTrue($object instanceof DatabaseSessionHandler);
	}
}
