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


}
