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


	/**
	 * A thumbnail URL e107 renders has to be one thumb.php can answer.
	 *
	 * e107::set_request() strips { and } out of every query string, so a src=
	 * that still carries them arrives naming nothing: {e_IMAGE}generic/x.jpg
	 * reaches the thumbnailer as e_IMAGEgeneric/x.jpg. The unit suite covers
	 * the string thumbUrl() returns; only a request proves the two halves
	 * agree.
	 *
	 * The admin area is where it shows: every page there renders the current
	 * user's avatar through e_parse::toAvatar(), which asks for $raw = true.
	 *
	 * @see https://github.com/e107inc/e107/discussions/5893
	 */
	public function testRenderedThumbUrlCanBeFetched(AcceptanceTester $I)
	{
		$I->wantTo('Fetch the thumbnail URL the admin area actually renders');

		$I->amOnPage('/e107_admin/admin.php');
		$I->fillField('authname', \Helper\AdminLogin::ADMIN_USER);
		$I->fillField('authpass', \Helper\AdminLogin::ADMIN_PASS);
		$I->click('authsubmit');

		$rendered = $I->grabPageSource();
		$matched = preg_match('#["\'](/?[^"\']*thumb\.php\?src=[^"\']+)["\']#', $rendered, $found);

		$I->assertSame(1, $matched, 'The admin area rendered no thumb.php URL to test.');

		$url = html_entity_decode($found[1], ENT_QUOTES);

		$I->assertStringNotContainsString('%7B', $url,
			'A rendered src= carries an encoded { that set_request() will strip: '.$url);

		$I->amOnPage($url);
		$I->seeResponseCodeIs(200);
		$I->assertStringStartsWith('image/', (string) $I->grabHttpHeader('Content-Type'),
			'The rendered thumbnail URL did not answer with an image: '.$url);
	}


}
