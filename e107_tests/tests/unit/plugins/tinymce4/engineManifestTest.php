<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * @group plugins
 *
 * The files under engines/<name>/ are exactly those tests/_data/editor/engine-<name>.sha256 lists, each with the SHA-256 recorded there.
 * That the manifest matches the publisher's tarball is checked when the manifest is written, not here.
 */
class engineManifestTest extends \Test\Unit
{
	/**
	 * @return array
	 */
	public function engines()
	{
		return array(
			'tinymce4' => array('tinymce4'),
		);
	}

	/**
	 * @dataProvider engines
	 * @param string $engine
	 */
	public function testTheShippedFilesAreTheManifestsFiles($engine)
	{
		$manifest = $this->manifest($engine);
		$shipped = $this->shipped($engine);

		$this->assertNotEmpty($manifest, 'the manifest of '.$engine.' lists no file');
		$this->assertSame(array(), array_keys(array_diff_key($shipped, $manifest)), 'shipped under engines/'.$engine.'/ and missing from the manifest');
		$this->assertSame(array(), array_keys(array_diff_key($manifest, $shipped)), 'listed in the manifest and not shipped under engines/'.$engine.'/');
		$this->assertSame(array(), array_keys(array_diff_assoc($shipped, $manifest)), 'shipped with bytes other than the manifest records');
	}

	/**
	 * @param string $engine
	 * @return array SHA-256 by path relative to the engine's folder
	 */
	private function manifest($engine)
	{
		$hashes = array();

		foreach(file(codecept_data_dir('editor/engine-'.$engine.'.sha256'), FILE_IGNORE_NEW_LINES) as $line)
		{
			if($line === '' || $line[0] === '#')
			{
				continue;
			}

			$this->assertSame(1, preg_match('/^([0-9a-f]{64})  (\S.*)$/', $line, $entry), 'not a sha256sum line: '.$line);
			$hashes[$entry[2]] = $entry[1];
		}

		ksort($hashes);

		return $hashes;
	}

	/**
	 * @param string $engine
	 * @return array SHA-256 by path relative to the engine's folder
	 */
	private function shipped($engine)
	{
		$folder = e_PLUGIN.'tinymce4/engines/'.$engine.'/';
		$hashes = array();

		if(!is_dir($folder))
		{
			return $hashes;
		}

		$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($folder, FilesystemIterator::SKIP_DOTS));

		foreach($files as $file)
		{
			$hashes[substr($file->getPathname(), strlen($folder))] = hash_file('sha256', $file->getPathname());
		}

		ksort($hashes);

		return $hashes;
	}
}
