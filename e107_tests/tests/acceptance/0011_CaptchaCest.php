<?php


class CaptchaCest
{
	public function _before(AcceptanceTester $I)
	{
	}

	public function _after(AcceptanceTester $I)
	{
	}

	// tests
	public function testSecImgOutput(AcceptanceTester $I)
	{
		$recnum = 1534090983051500000;

		$_SESSION['secure_img'][$recnum] = 'ABCDEFG';

		$I->amOnPage('/e107_images/secimg.php?id='.$recnum.'&clr=cccccc');

		$I->seeResponseCodeIs(200);


	}


}
