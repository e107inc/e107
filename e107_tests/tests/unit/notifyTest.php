<?php


class notifyTest extends \Test\Unit
{

	/** @var notify */
	protected $nt;

	protected function _before()
	{
		e107::getPlugin()->install('_blank');

		try
		{
			$this->nt = $this->make('notify');
		}

		catch(Exception $e)
		{
			$this->fail($e->getMessage());
		}

		$this->nt->__construct();

	}

	function _after()
	{
		e107::getPlugin()->uninstall('_blank');
	}

	public function testSendCustom()
	{
		// Simulate saved pref
		$this->nt->notify_prefs['event'] = array (
			'custom_event' =>  array (
				    'class' => '_blank::other_type', // _blank plugin e_notify.php router()
				    'recipient' => 'exampleAccount',
				    'include' => '',
				    'legacy' => '0',
				  ),
		);

		$expected = array (
		  'id'          => 'custom_event',
		  'subject'     => 'Test subject',
		  'message'     => 'Test message',
		  'recipient'   => 'exampleAccount',
		);

		$result = $this->nt->send('custom_event','Test subject','Test message');
		$this->assertSame($expected, $result);

	}

	/**
	 * Each line of the user verification notice puts a value after its label, and the labels used to carry the space themselves.
	 *
	 * @see https://github.com/e107inc/e107/issues/6331
	 */
	public function testUserVerificationLinesAreSpacedFromTheirValues()
	{
		e107::coreLan('notify');

		$sent = array();
		$nt = $this->make('notify', array('send' => function($id, $subject, $message) use (&$sent) {
			$sent[] = $message;
		}));

		$nt->notify_userveri(array('user_id' => 42, 'user_loginname' => 'exampleAccount'));

		$this->assertCount(1, $sent, 'The verification notice must have been sent once');

		$this->assertStringContainsString(NT_LAN_UV_2.' 42', $sent[0], $sent[0]);
		$this->assertStringContainsString(NT_LAN_UV_3.' exampleAccount', $sent[0], $sent[0]);
		$this->assertStringContainsString(NT_LAN_UV_4.' '.e107::getIPHandler()->getIP(FALSE), $sent[0], $sent[0]);
		$this->assertStringNotContainsString(NT_LAN_UV_2.'42', $sent[0], $sent[0]);
	}


}
