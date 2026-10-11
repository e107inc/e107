<?php

class e_admin_requestTest extends \Codeception\Test\Unit
{
	/**
	 * @var e_admin_request
	 */
	protected $eAdminRequest;

	protected function _before()
	{
		$this->eAdminRequest = new e_admin_request('');
	}

	/**
	 * Every spelling that reaches one method shares one canonical form, whatever the locale.
	 */
	public function testCanonicalFormsIgnoreTheLocale()
	{
		$previous = setlocale(LC_CTYPE, 0);

		if(setlocale(LC_CTYPE, 'tr_TR.UTF-8', 'tr_TR.utf8') === false)
		{
			$this->markTestSkipped('No Turkish locale is installed.');
		}

		try
		{
			foreach(array('edit', 'Edit', 'EDIT', 'e_d_i_t') as $spelling)
			{
				$this->assertSame('edit', $this->eAdminRequest->canonicalizeAction($spelling), $spelling);
			}

			$this->assertSame('InlineItems', $this->eAdminRequest->camelize('inline_items'));
			$this->assertSame('main', $this->eAdminRequest->canonicalizeMode('MAIN'));
			$this->assertSame('main', $this->eAdminRequest->setMode('MAIN')->getModeName());
		}
		finally
		{
			setlocale(LC_CTYPE, $previous);
		}
	}
}
