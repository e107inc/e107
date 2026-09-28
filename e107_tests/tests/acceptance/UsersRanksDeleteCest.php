<?php

/**
 * Issue #6527: the delete button on a Users > User Ranks row removes the rank.
 */
class UsersRanksDeleteCest
{
	const ROUTE = '/e107_admin/users.php?mode=ranks&action=list';

	/**
	 * Not click(): PhpBrowser would post the button after the batch select, an order no browser sends.
	 */
	public function theRowDeleteButtonRemovesACustomRank(AcceptanceTester $I)
	{
		$I->wantTo('Delete a custom user rank with the delete button on its row');

		$id = $this->haveRank($I, 0, 'Custom rank 6527');

		$I->loginAsAdmin();
		$I->amOnPage(self::ROUTE);
		$I->sendPostRequest(self::ROUTE, array(
			'e-token'        => $I->grabToken(),
			$I->grabAttributeFrom(array('css' => "button.delete[value='{$id}']"), 'name') => $id,
			'etrigger_batch' => '',
		));

		$I->dontSeeInDatabase('e107_generic', array('gen_id' => $id));
	}

	public function aSpecialRankKeepsItsEditLinkButHasNoDeleteButton(AcceptanceTester $I)
	{
		$I->wantTo('Offer an edit link on every rank row and a delete button only on the custom ones');

		$special = $this->haveRank($I, 1, 'Main admin rank 6527');
		$custom = $this->haveRank($I, 0, 'Custom rank 6527');

		$I->loginAsAdmin();
		$I->amOnPage(self::ROUTE);

		$I->seeElement(array('css' => "a[href$='action=edit&id={$special}']"));
		$I->seeElement(array('css' => "a[href$='action=edit&id={$custom}']"));
		$I->dontSeeElement(array('css' => "button.delete[value='{$special}']"));
		$I->seeElement(array('css' => "button.delete[value='{$custom}']"));
	}

	/**
	 * @param int $special gen_datestamp: 0 for a custom rank, 1 main admin, 2 admin.
	 */
	private function haveRank(AcceptanceTester $I, $special, $title)
	{
		return (int) $I->haveInDatabase('e107_generic', array(
			'gen_type'      => 'user_rank_data',
			'gen_datestamp' => $special,
			'gen_user_id'   => 0,
			'gen_ip'        => $title,
			'gen_intdata'   => 1,
			'gen_chardata'  => 'lev1.png',
		));
	}
}
