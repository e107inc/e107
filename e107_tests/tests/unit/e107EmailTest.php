<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2020 e107.org
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */


	class e107EmailTest extends \Codeception\Test\Unit
	{

		/** @var e107Email */
		protected $eml;

		protected function _before()
		{

			try
			{
				$this->eml = $this->make('e107Email');
			}
			catch(Exception $e)
			{
				$this->assertTrue(false, "Couldn't load e107Email object");
			}


			$this->eml->__construct();

		}

/*
		public function testAllSent()
		{

		}

		public function testProcessShortcodes()
		{

		}
*/

		public function testArraySet()
		{
			$eml = array(
					'subject' 		=> "[URGENT EXAMPLE]",
					'sender_email'	=> "noreply@test.com",
					'sender_name'	=> "Test Person",
					'replyto'		=> "",
					'html'			=> true,
					'priority'      => 1,
					'template'		=> 'default',
					'body'			=> "This is the body text",
					'cc'            => ''
				);

			$this->eml->arraySet($eml);

			$this->assertStringContainsString("noreply@test.com", $this->eml->From);
			$this->assertStringContainsString("Test Person", $this->eml->FromName);
			$this->assertStringContainsString("e107: [URGENT EXAMPLE] ", $this->eml->Subject);
			$this->assertStringContainsString("This is the body text", $this->eml->Body);
			$this->assertStringContainsString("<h4 class='sitename'><a href='", $this->eml->Body);
			$this->assertStringNotContainsString('{MEDIA1}', $this->eml->Body);
		}

/*
		public function testMakePrintableAddress()
		{

		}

		public function testPreview()
		{

		}

		public function testAddInlineImages()
		{

		}
*/
		public function testMsgHTML()
		{
			$html = "\n
Hi <b>Joe</b><br />

Check out <a href='http://e107.org'>http://e107.org</a><br />
<br />
Thanks,<br />
Admin<br />
<br />
<table>
<tr>
<td>Website:</td><td>https://e107.org</td>
</tr>
<tr>
<td>Github:</td><td>https://github.com/e107inc/</td></tr>
</table>";


			$this->eml->MsgHTML($html);

			$result = json_encode($this->eml->AltBody);
			$expected = '"Hi Joe\\nCheck out http:\\/\\/e107.org\\n\\nThanks,\\nAdmin\\n\\nWebsite:\\thttps:\\/\\/e107.org\\t\\nGithub:\\thttps:\\/\\/github.com\\/e107inc\\/"';
			$this->assertSame($expected, $result);

		}
/*
		public function testSendEmail()
		{

				$eml = array(
					'subject' 		=> "[URGENT EXAMPLE] ",
					'sender_email'	=> "noreply@test.com",
					'sender_name'	=> "Test",
					'replyto'		=> "",
					'html'			=> true,
					'priority'      => 1,
					'template'		=> 'default',
					'body'			=> "This is the body text",
					'cc'            => ''
				);


			$this->eml->sendEmail('test@nowhere.com',"This is the subject", $eml);

		}

		public function testSetDebug()
		{

		}

		public function testAddAddressList()
		{

		}

		public function testAttach()
		{

		}

		public function testMakeBody()
		{

		}

*/


	}
