<?php

	/**
	 * The denial e107_handlers ships with.
	 *
	 * Nothing in the directory is meant to be fetched over the web, and until
	 * this file shipped, every reachable script in it defended itself alone.
	 */
	class handlersHtaccessTest extends \Codeception\Test\Unit
	{

		public function testTheHandlersDirectoryShipsTheDenialE107Writes()
		{
			$guard = e_HANDLER . '.htaccess';

			self::assertFileExists($guard,
				'Three sibling directories ship a denial and this one holds the bounce handler');

			self::assertSame($this->directives($this->ruleE107Writes()),
				$this->directives(file_get_contents($guard)),
				'The shipped denial has to be the rule e_file::protectDirectory() writes, '
				. 'or a host is reading one of the two long after the other was corrected');
		}

		/**
		 * @return string the guard e_file::protectDirectory() writes today
		 */
		private function ruleE107Writes()
		{
			$dir = e_TEMP . 'handlers_htaccess_' . uniqid() . '/';
			mkdir($dir);

			try
			{
				self::assertTrue(e107::getFile()->protectDirectory($dir));

				return file_get_contents($dir . '.htaccess');
			}
			finally
			{
				@unlink($dir . '.htaccess');
				@unlink($dir . 'index.html');
				@rmdir($dir);
			}
		}

		/**
		 * @param string $rule
		 * @return array the rule's directives, without its comments or blank lines
		 */
		private function directives($rule)
		{
			$directives = array();

			foreach(explode("\n", $rule) as $line)
			{
				$line = trim($line);

				if($line !== '' && strpos($line, '#') !== 0)
				{
					$directives[] = $line;
				}
			}

			return $directives;
		}

	}
