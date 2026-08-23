<?php

/**
 * rate.php takes a whole ballot out of the query string.
 *
 * e_session::isStateChangingRequest() answers true only for POST, so attest()
 * returns early on a GET that carries no token at all, and what stands between
 * a hostile <img> tag and a vote is whatever rate.php does for itself. It did
 * nothing: rate.php?<table>^<itemid>^<returnurl>^<score> reached
 * rater::enterrating(), which wrote a row in the rate table whose table name,
 * item id and score the attacker chose and which was attributed to whichever
 * logged-in member loaded the page.
 *
 * The e-token in a query string is e107's marker for a state-changing GET, the
 * way e107_admin/plugin.php, theme.php and language.php already use it: the
 * endpoint tests that one is present and attest() decides whether it is the
 * right one. These cases assert both halves of that division of labour, and
 * the last one is the control that the rating box's own link still votes.
 *
 * The AJAX half of rate.php is keyed on real $_POST values, so it is out of
 * reach of a forged GET and is left alone.
 *
 * The fixture writes a probe into the docroot, so it answers nobody who cannot
 * show the secret this run minted for it, and it loads class2.php before it
 * looks at what it was asked to do. A probe left behind by a run that died
 * would otherwise be an anonymous way to delete rows from the rate table and
 * to have the site render a rating box to a caller nobody authenticated. That
 * is untidy where the other two fixtures are dangerous, and it is still state
 * a stranger must not be able to reach.
 *
 * @see e107_handlers/session_handler.php  e_session::attest()
 * @see e107_handlers/rate_class.php       rater::rateselect()
 */
class RateVoteCsrfCest
{
	const PROBE_FILE = 'e107_tests_rate_csrf.php';

	/** rate_table the fixture votes on, so no real content is disturbed. */
	const TABLE = 'ratecsrfprobe';

	const ITEM = 1;

	/** A distinctive fragment of RATELAN_REFUSED_TOKEN_MISSING. */
	const REFUSED = 'no security token';

	/**
	 * A GET that the framework does police: attest() refuses any e-token it
	 * cannot validate, whatever the request method, and answers with this.
	 */
	const UNAUTHORIZED = 'Unauthorized access!';

	/** @var string what a caller shows to prove it is this run of this case */
	private $secret;

	public function _before(AcceptanceTester $I)
	{
		$I->loginAsAdmin();
		$this->secret = substr(hash('sha256', uniqid('', true).mt_rand()), 0, 32);
		$I->writeAppFile(self::PROBE_FILE, $this->probeSource());
		$I->amOnPage($this->probeUrl('act=reset'));
		$I->seeInSource('PROBE_OK');
	}

	public function _after(AcceptanceTester $I)
	{
		$I->amOnPage($this->probeUrl('act=reset'));
		$I->deleteAppFile(self::PROBE_FILE);
	}

	/**
	 * One <img> tag on any page a member visits, and the vote is theirs.
	 */
	public function aTokenlessGetDoesNotRecordAVote(AcceptanceTester $I)
	{
		$I->amOnPage($this->forgedBallot());

		$this->dontSeeAVote($I);
		$I->seeInSource(self::REFUSED);
	}

	/**
	 * Presence is all the endpoint tests; whether the value is the right one is
	 * attest()'s half. Both halves are needed, so assert the second one too.
	 */
	public function aGetCarryingTheWrongTokenIsRefused(AcceptanceTester $I)
	{
		$I->amOnPage($this->forgedBallot().'&e-token=not-even-close');

		$this->dontSeeAVote($I);
		$I->seeInSource(self::UNAUTHORIZED);
	}

	/**
	 * The control that matters most. A guard on a link members reach by
	 * ordinary navigation is worse than the hole it closes if the navigation
	 * stops working, so this follows whatever rater::rateselect() publishes
	 * rather than a URL of the test's own.
	 */
	public function theRatingBoxesOwnLinkStillRecordsAVote(AcceptanceTester $I)
	{
		$I->amOnPage($this->publishedBallot($I));

		$I->dontSeeInSource(self::REFUSED);
		$I->dontSeeInSource(self::UNAUTHORIZED);
		$I->seeInDatabase('e107_rate', array(
			'rate_table'  => self::TABLE,
			'rate_itemid' => self::ITEM,
			'rate_rating' => 10,
			'rate_votes'  => 1,
		));
	}

	/**
	 * The probe deletes rows, so a caller that cannot show this run's secret has
	 * to get nothing at all. A probe left in the docroot by a run that died is
	 * otherwise an anonymous way to wipe out what the site recorded.
	 */
	public function theProbeRefusesACallerThatCannotShowTheSecret(AcceptanceTester $I)
	{
		$I->amOnPage($this->publishedBallot($I));
		$I->amOnPage('/'.self::PROBE_FILE.'?act=reset');

		$I->seeResponseCodeIs(403);
		$I->seeInDatabase('e107_rate', array(
			'rate_table'  => self::TABLE,
			'rate_itemid' => self::ITEM,
		));
	}

	/**
	 * @param AcceptanceTester $I
	 * @return void
	 */
	private function dontSeeAVote(AcceptanceTester $I)
	{
		$I->dontSeeInDatabase('e107_rate', array(
			'rate_table'  => self::TABLE,
			'rate_itemid' => self::ITEM,
		));
	}

	/**
	 * @return string the legacy ballot an attacker's markup can build unaided
	 */
	private function forgedBallot()
	{
		return '/rate.php?'.self::TABLE.'^'.self::ITEM.'^/'.self::PROBE_FILE.'^10';
	}

	/**
	 * @param AcceptanceTester $I
	 * @return string path to follow, tokenised exactly as the rating box
	 *   published it
	 */
	private function publishedBallot(AcceptanceTester $I)
	{
		$I->amOnPage($this->probeUrl(''));

		if(!preg_match("#<option value='([^']+)'>10</option>#", $I->grabPageSource(), $matches))
		{
			throw new \RuntimeException('the rating box published no link for a score of 10');
		}

		return html_entity_decode($matches[1]);
	}

	/**
	 * @param string $query
	 * @return string
	 */
	private function probeUrl($query)
	{
		$url = '/'.self::PROBE_FILE.'?probe='.$this->secret;

		return ($query === '') ? $url : $url.'&'.$query;
	}

	/**
	 * @return string
	 */
	private function probeSource()
	{
		$secret = $this->secret;
		$table = self::TABLE;
		$item = self::ITEM;

		return <<<PHP
<?php
require_once(__DIR__.'/class2.php');

if(!isset(\$_GET['probe']) || !hash_equals('$secret', \$_GET['probe']))
{
	header('HTTP/1.1 403 Forbidden', true, 403);
	echo 'Unauthorized access!';
	exit;
}

if(isset(\$_GET['act']) && \$_GET['act'] === 'reset')
{
	e107::getDb()->delete('rate', "rate_table = '$table'");
	echo 'PROBE_OK';
	exit;
}

echo e107::getRate()->rateselect('', '$table', $item);
PHP;
	}
}
