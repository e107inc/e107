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

class eShimsTest extends \Codeception\Test\Unit
{
	public function testReadfile()
	{
		$this->testReadfileImplementation(array(\eShims::class, 'readfile'));
	}

	public function testReadfileAlt()
	{
		$this->testReadfileImplementation(array(\eShims::class, 'readfile_alt'));
	}

	protected function testReadfileImplementation($implementation)
	{
		$tmp_handle = tmpfile();
		$tmp_filename = stream_get_meta_data($tmp_handle)['uri'];
		$garbage = str_pad('', 16384, 'x');
		fwrite($tmp_handle, $garbage);
		ob_start();
		call_user_func($implementation, $tmp_filename);
		$output = ob_get_clean();
		fclose($tmp_handle);
		$this->assertEquals($garbage, $output);
	}
}
