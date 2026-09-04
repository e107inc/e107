<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * e107_admin/users.php is an entry point that cannot be included from a test, so the batch
 * guard's structural obligations are checked against the source: the two batch triggers route
 * through refusesBatch(), and refusesBatch() applies the same administrator rule beforeUpdate()
 * applies on the edit route, which a batch write never reaches.
 *
 * What the guard does with a posted batch is exercised through the running application by
 * AdminRoutePermsCest, which is where a guard that stopped guarding would be caught.
 */
class usersAdminBatchGuardTest extends \Codeception\Test\Unit
{
	/** @var string */
	private $page;

	protected function _before()
	{
		$this->page = e_ADMIN . 'users.php';
	}

	public function testBothBatchTriggersRouteThroughTheGuard()
	{
		foreach(array('ListBatchTrigger', 'GridBatchTrigger') as $trigger)
		{
			$this->assertContains('refusesBatch', $this->callsIn($trigger),
				$trigger . '() must ask refusesBatch() before it dispatches.');
		}
	}

	public function testTheGuardAppliesTheAdministratorRule()
	{
		$this->assertContains('batchSelectsProtectedAdmin', $this->callsIn('refusesBatch'),
			'refusesBatch() must refuse a selection holding an administrator the caller may not edit.');
		$this->assertContains('canGrantAdmin', $this->callsIn('holdsProtectedAdmin'),
			'The administrator rule is permission 3, the same one beforeUpdate() asks for.');
		$this->assertContains('batchSelection', $this->callsIn('batchSelectsProtectedAdmin'),
			'The rule must read every row the batch acts on, not only the ticked ones.');
	}

	/**
	 * The batch route is not the only one that writes an administrator's row. Every single-row
	 * trigger on this page that writes user_ban, user_sess or user_password reaches a write of
	 * its own, and the rule has to be on all of them: a list that is one short is how the
	 * asymmetry this test exists to catch got there in the first place.
	 */
	public function testTheSingleRowRoutesApplyTheSameAdministratorRule()
	{
		foreach(array('ListBanTrigger', 'ListUnbanTrigger', 'ListVerifyTrigger',
			'ListReqverifyTrigger', 'ListResendTrigger') as $trigger)
		{
			$this->assertContains('refusesRowTrigger', $this->callsIn($trigger),
				$trigger . '() must ask the administrator rule before it writes.');
		}

		$this->assertContains('holdsProtectedAdmin', $this->callsIn('refusesRowTrigger'),
			'The single-row rule must be the rule the batch route applies, not a second one.');
		$this->assertContains('holdsProtectedAdmin', $this->callsIn('ListDeleteTrigger'),
			'The single-row delete must ask the rule about every row it is given.');
	}

	public function testTheGuardStillAsksWhetherTheFieldOffersABatch()
	{
		$calls = $this->callsIn('refusesBatch');

		$this->assertContains('isTypedBatchTrigger', $calls,
			'refusesBatch() must read the field segment the dispatcher reads.');
		$this->assertContains('isBatchField', $calls,
			'refusesBatch() must put the field to the same test the dispatcher puts it to.');
	}

	/**
	 * Names called inside one method of the page, read with the tokenizer.
	 */
	private function callsIn($method)
	{
		$this->assertFileExists($this->page);

		$tokens = token_get_all(file_get_contents($this->page));
		$start = null;

		foreach($tokens as $i => $token)
		{
			if(!is_array($token) || $token[0] !== T_FUNCTION)
			{
				continue;
			}

			for($j = $i + 1, $n = count($tokens); $j < $n; $j++)
			{
				if(is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE)
				{
					continue;
				}
				if(is_array($tokens[$j]) && $tokens[$j][0] === T_STRING && $tokens[$j][1] === $method)
				{
					$start = $j;
				}
				break;
			}

			if($start !== null)
			{
				break;
			}
		}

		$this->assertNotNull($start, $method . '() must be declared in ' . $this->page . '.');

		$calls = array();
		$depth = 0;
		$open = false;

		for($i = $start, $n = count($tokens); $i < $n; $i++)
		{
			if($tokens[$i] === '{')
			{
				$depth++;
				$open = true;
				continue;
			}
			if($tokens[$i] === '}')
			{
				$depth--;
				if($open && $depth === 0)
				{
					break;
				}
				continue;
			}
			if($open && is_array($tokens[$i]) && $tokens[$i][0] === T_STRING)
			{
				$calls[] = $tokens[$i][1];
			}
		}

		return $calls;
	}
}
