<?php

	/**
	 * The denial e107_handlers ships with.
	 *
	 * Nothing in the directory is meant to be fetched over the web, and until
	 * this file shipped, every reachable script in it defended itself alone.
	 */
	class handlersHtaccessTest extends \Codeception\Test\Unit
	{

		public function testTheHandlersDirectoryShipsADenial()
		{
			$guard = e_HANDLER . '.htaccess';

			self::assertFileExists($guard,
				'Three sibling directories ship a denial and this one holds the bounce handler');

			$rule = file_get_contents($guard);

			self::assertStringContainsString('RedirectMatch 403', $rule,
				'The refusal an Apache 2.4 reads, whether or not it still loads mod_access_compat');
			self::assertStringContainsString('Deny from all', $rule,
				'The refusal an Apache 2.2 reads');
			self::assertStringContainsString('<IfModule !mod_authz_core.c>', $rule,
				'Unguarded, the 2.2 form is an unknown directive on a 2.4 without mod_access_compat, '
				. 'which answers 500 for the whole directory');
			self::assertStringNotContainsString('Require ', $rule,
				'A guard file may ask for no AllowOverride class beyond the ones e107.htaccess already needs');
		}

	}
