<?php


class ThumbCest
{
	/**
     * @param \AcceptanceTester $I
     */
    public function _before($I)
	{
	}

	/**
     * @param \AcceptanceTester $I
     */
    public function _after($I)
	{
	}

	// tests
    /**
     * @param \AcceptanceTester $I
     */
    public function testThumbOutput($I)
	{

		$I->amOnPage('/thumb.php?src=e_PLUGIN%2Fgallery%2Fimages%2Fbutterfly.jpg&w=220&h=190');

		$I->seeResponseCodeIs(200);


	}


}
