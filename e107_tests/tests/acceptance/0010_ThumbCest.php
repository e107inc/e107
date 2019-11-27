<?php


class ThumbCest
{
	public function _before(AcceptanceTester $I)
	{
	}

	public function _after(AcceptanceTester $I)
	{
	}

	// tests
	public function testThumbOutput(AcceptanceTester $I)
	{

		$I->amOnPage('/thumb.php?src=e_PLUGIN%2Fgallery%2Fimages%2Fbutterfly.jpg&w=220&h=190');

		$I->seeResponseCodeIs(200);


	}


}
