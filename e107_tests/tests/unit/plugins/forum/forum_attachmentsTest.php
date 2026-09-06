<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * Covers the answer {@see forum_attachments::protectPath()} gives and the four paths that must obey it.
 *
 * @group plugins
 */
class forum_attachmentsTest extends \Test\Unit
{
	/** @var array absolute paths to remove after each test, deepest first */
	private $litter = array();

	protected function _before()
	{
		require_once(APP_PATH . '/e107_plugins/forum/forum_attachments.php');
	}

	protected function _after()
	{
		foreach($this->litter as $path)
		{
			if(is_dir($path))
			{
				@chmod($path, 0755);

				foreach(array('.htaccess', 'index.html') as $guard)
				{
					if(is_file($path . $guard))
					{
						unlink($path . $guard);
					}
				}

				rmdir($path);
			}
			elseif(is_file($path))
			{
				unlink($path);
			}
		}

		$this->litter = array();
	}

	public function testADirectoryThatCannotBeMadeIsRefused()
	{
		$blocker = e_TEMP . 'forum_attachments_' . uniqid('', true);

		file_put_contents($blocker, '');
		$this->litter[] = $blocker;

		self::assertFileExists($blocker, 'the fixture that stands in the way of the directory was not written');

		self::assertFalse(forum_attachments::protectPath($blocker . '/user_000001/'),
			'protectPath() must answer false for a directory it could neither find nor create');
	}

	public function testAWritableDirectoryIsCoveredAndAnsweredTrue()
	{
		$dir = $this->scratchDir();

		self::assertTrue(forum_attachments::protectPath($dir),
			'protectPath() must answer true for a directory it could cover');

		foreach(array('.htaccess', 'index.html') as $guard)
		{
			self::assertFileExists($dir . $guard,
				'protectPath() answered true without writing the ' . $guard . ' guard');
		}

		self::assertTrue(forum_attachments::protectPath($dir),
			'a directory already covered must still answer true');
	}

	public function testADirectoryThatCannotTakeAGuardFileIsRefused()
	{
		$dir = $this->scratchDir();

		chmod($dir, 0555);

		if(@file_put_contents($dir . 'writable_probe', '') !== false)
		{
			unlink($dir . 'writable_probe');

			self::markTestSkipped('this runner can write into a directory it has no write permission on');
		}

		self::assertFalse(forum_attachments::protectPath($dir),
			'protectPath() must answer false when the deny rule cannot be written');
	}

	public function testTheStoringPathRefusesWhenTheAnswerIsFalse()
	{
		$body = $this->body(APP_PATH . '/e107_plugins/forum/forum_post.php', 'processAttachments');

		self::assertMatchesRegularExpression('/if\s*\(.*?!\s*forum_attachments::protectPath\(/', $body,
			'forum_post_handler::processAttachments() must gate on protectPath() rather than call it for effect');

		self::assertLessThan(strpos($body, 'getUploaded'), strpos($body, 'protectPath'),
			'the deny rules have to go down before the bytes do');
	}

	public function testTheMigrationPathsRefuseWhenTheAnswerIsFalse()
	{
		$paths = array(
			APP_PATH . '/e107_plugins/forum/forum_update.php' => 'moveAttachment',
			APP_PATH . '/e107_plugins/import/providers/phpbb3_import_class.php' => 'convertAttachment',
		);

		foreach($paths as $file => $method)
		{
			$body = $this->body($file, $method);

			self::assertMatchesRegularExpression('/if\s*\(.*?!\s*forum_attachments::protectPath\(/', $body,
				$method . '() must gate on protectPath() rather than call it for effect');
		}
	}

	public function testNoWritingPathStillDiscardsTheAnswer()
	{
		$writers = array(
			APP_PATH . '/e107_plugins/forum/forum_post.php',
			APP_PATH . '/e107_plugins/forum/forum_update.php',
			APP_PATH . '/e107_plugins/import/providers/phpbb3_import_class.php',
		);

		foreach($writers as $file)
		{
			self::assertStringNotContainsString('protectDirectory', file_get_contents($file),
				$file . ' calls protectDirectory() directly, so its answer is nobody\'s to act on');
		}
	}

	/**
	 * An empty directory outside the app tree, removed with its guards after the test.
	 */
	private function scratchDir()
	{
		$dir = e_TEMP . 'forum_attachments_' . uniqid('', true) . '/';

		mkdir($dir, 0755, true);
		array_unshift($this->litter, $dir);

		self::assertDirectoryExists($dir, 'the scratch directory the test works in was not made');

		return $dir;
	}

	/**
	 * The source text of one method of a file, braces and all, read with the tokenizer.
	 */
	private function body($file, $method)
	{
		self::assertFileExists($file);

		$tokens = token_get_all(file_get_contents($file));
		$start = null;

		foreach($tokens as $i => $token)
		{
			if(!is_array($token) || $token[0] !== T_STRING || $token[1] !== $method)
			{
				continue;
			}

			for($j = $i - 1; $j >= 0; $j--)
			{
				if(is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE)
				{
					continue;
				}
				if(is_array($tokens[$j]) && $tokens[$j][0] === T_FUNCTION)
				{
					$start = $i;
				}
				break;
			}

			if($start !== null)
			{
				break;
			}
		}

		self::assertNotNull($start, $method . '() must be declared in ' . $file . '.');

		$body = '';
		$depth = 0;
		$open = false;

		for($i = $start, $n = count($tokens); $i < $n; $i++)
		{
			$text = is_array($tokens[$i]) ? $tokens[$i][1] : $tokens[$i];

			if($text === '{' || $text === '${')
			{
				$depth++;
				$open = true;
			}
			elseif($text === '}')
			{
				$depth--;
			}

			$body .= $text;

			if($open && $depth === 0)
			{
				break;
			}
		}

		return $body;
	}
}
