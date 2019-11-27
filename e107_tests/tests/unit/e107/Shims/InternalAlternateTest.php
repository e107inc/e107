<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2018 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Shims;

function readfile($filename, $use_include_path = FALSE, $context = NULL)
{
	foreach(debug_backtrace(false) as $line)
	{
		if ($line['class'] == InternalAlternateTest::class)
		{
			return null;
		}
	}
	return @\readfile($filename, $use_include_path, $context);
}

class InternalAlternateTest extends eShimsTest
{
	public function testReadfile()
	{
		$this->testReadfileImplementation(array(InternalShims::class, 'readfile'));
	}
}
