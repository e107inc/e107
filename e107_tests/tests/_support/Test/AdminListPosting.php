<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

namespace Test;

/**
 * The batch and confirmed-delete posts of an e_admin_ui list, for a Cest that says where its e-token comes from and which checkbox column its list declares.
 */
trait AdminListPosting
{
	/**
	 * @param \AcceptanceTester $I
	 * @param string $route
	 * @return string the e-token to post to $route
	 */
	abstract protected function tokenFor(\AcceptanceTester $I, $route);

	/**
	 * @param \AcceptanceTester $I
	 * @param string $route
	 * @param array $payload posted keys, which the Cest's e-token is added to
	 * @return void
	 */
	private function postWithToken(\AcceptanceTester $I, $route, array $payload)
	{
		$payload['e-token'] = $this->tokenFor($I, $route);

		$I->sendPostRequest($route, $payload);
	}

	/**
	 * Post a batch the way the list's batch menu posts one.
	 *
	 * @param \AcceptanceTester $I
	 * @param string $route
	 * @param string $trigger etrigger_batch value
	 * @param int $id the one row the selection carries
	 * @param string $selection name of the list's checkbox column
	 * @return void
	 */
	private function sendBatch(\AcceptanceTester $I, $route, $trigger, $id, $selection = self::SELECTION)
	{
		$this->postWithToken($I, $route, AdminListPost::batch($trigger, $id, $selection));
	}

	/**
	 * Post the request a single-row delete is finished by.
	 *
	 * @param \AcceptanceTester $I
	 * @param string $route
	 * @param int $id row to delete
	 * @return void
	 */
	private function sendConfirmedRowDelete(\AcceptanceTester $I, $route, $id)
	{
		$this->postWithToken($I, $route, AdminListPost::confirmedRowDelete($id));
	}

	/**
	 * Post the request a batch delete is finished by.
	 *
	 * @param \AcceptanceTester $I
	 * @param string $route
	 * @param int $id row to delete
	 * @param string $command the batch command, as the confirm screen or a caller spells it
	 * @return void
	 */
	private function sendConfirmedBatchDelete(\AcceptanceTester $I, $route, $id, $command = 'delete')
	{
		$this->postWithToken($I, $route, AdminListPost::confirmedBatchDelete($id, $command));
	}
}
