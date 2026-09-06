<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * e107MailTemplate::loadTemplateInfo() reads an email template file and picks
 * the named template out of the variables it defines. It read them with
 * require_once, which hands back nothing at all the second time a file is
 * asked for, so only the first template loaded in a request could ever be
 * found. Every later one, of any other name, came back FALSE, and the caller
 * reports that as "template not found".
 */
class mail_template_classTest extends \Test\Unit
{

	/** @var string */
	private $fixtureFile;

	public function _before()
	{
		require_once(e_HANDLER.'mail_template_class.php');

		// A unique name per run: the file is included once per request, which
		// is the behaviour under test.
		$this->fixtureFile = sys_get_temp_dir().'/e107_email_template_'.uniqid().'.php';

		file_put_contents($this->fixtureFile, '<?php'."\n".
			'$fixture_first = array('."\n".
			"	'email_overrides' => array('mailer' => 'fixture'),\n".
			"	'email_header'    => '<html><body>',\n".
			"	'email_body'      => 'FIRST BODY {BODY}',\n".
			"	'email_footer'    => '</body></html>',\n".
			"	'email_plainText' => 'FIRST PLAIN',\n".
			');'."\n".
			'$fixture_second = array('."\n".
			"	'email_overrides' => array('mailer' => 'fixture'),\n".
			"	'email_header'    => '<html><body>',\n".
			"	'email_body'      => 'SECOND BODY {BODY}',\n".
			"	'email_footer'    => '</body></html>',\n".
			"	'email_plainText' => 'SECOND PLAIN',\n".
			');'."\n"
		);
	}

	public function _after()
	{
		if($this->fixtureFile && is_file($this->fixtureFile))
		{
			unlink($this->fixtureFile);
		}
	}

	/**
	 * Two templates out of one file, in one process. Both have to be found.
	 */
	public function testASecondTemplateFromTheSameFileIsStillFound()
	{
		$mailTemplate = new e107MailTemplate();

		$first = $mailTemplate->loadTemplateInfo('fixture_first', $this->fixtureFile);

		$this->assertIsArray($first, 'Precondition: the first template loads.');
		$this->assertEquals('FIRST BODY {BODY}', $first['email_body']);

		$second = $mailTemplate->loadTemplateInfo('fixture_second', $this->fixtureFile);

		$this->assertIsArray($second,
			'The second template is in the same file and must load too.');
		$this->assertEquals('SECOND BODY {BODY}', $second['email_body']);
		$this->assertEquals('SECOND PLAIN', $second['email_plainText']);
	}

	/**
	 * A fresh object does not help: PHP tracks included files per process, not
	 * per object, so this is the same defect seen from the caller's side.
	 */
	public function testAFreshObjectStillFindsTheTemplate()
	{
		$first = new e107MailTemplate();
		$first->loadTemplateInfo('fixture_first', $this->fixtureFile);

		$second = new e107MailTemplate();
		$loaded = $second->loadTemplateInfo('fixture_second', $this->fixtureFile);

		$this->assertIsArray($loaded);
		$this->assertEquals('SECOND BODY {BODY}', $loaded['email_body']);
	}
}
