<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2020 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

namespace e107\Factories;


class BaseFactoryTest extends \Codeception\Test\Unit
{
	public function testMakeRefusesToMakeObjectsItDoesNotKnowAbout()
	{
		$this->expectException(\InvalidArgumentException::class);
		SessionHandlerFactory::make('Bologna');
	}
}
