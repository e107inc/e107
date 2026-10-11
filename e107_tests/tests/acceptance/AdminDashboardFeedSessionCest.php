<?php

/**
 * The dashboard's feed and add-on update handlers in e107_admin/boot.php ask e107.org for data; every other request the administrator's browser makes waits on the PHP session's lock until they let it go.
 *
 * The dashboard shows each feed's kept copy, up to seven days after its fetch, and only a missing or expired copy sends a request to renew it.
 *
 * The probe answers each remote fetch itself, and the answer names the session state at the moment the handler asked.
 */
class AdminDashboardFeedSessionCest
{
	const PROBE_FILE = 'e107_tests_feed_session_probe.php';

	const RELEASED = 'FEED_SESSION_RELEASED';
	const HELD = 'FEED_SESSION_HELD';

	/** The name the probe gives the theme update it invents. */
	const UPDATE_NAME = 'FEED_SESSION_UPDATE';

	/** A feed copy the probe keeps, as an earlier fetch would have. */
	const KEPT = 'FEED_COPY_KEPT';

	/** An age a minute past the three hours a copy is kept for, in seconds. */
	const EXPIRED = 10860;

	/** The fifteen minutes before a fetch that brought no copy is tried again, in seconds. */
	const RETRY = 900;

	/** An age a day past the seven days a copy is shown for, in seconds. */
	const OUTDATED = 691200;

	const CORE_RENEWAL = 'admin.php?mode=core&type=feed';
	const PLUGIN_RENEWAL = 'admin.php?mode=addons&type=plugin';

	/**
	 * The reset empties the system cache, so the next admin page asks e107.org for the core release ({@see e107::coreUpdateAvailable()}), whichever test it is in.
	 */
	public function _before(AcceptanceTester $I)
	{
		$I->expectOutboundRequest('e107.org');
		$I->haveProbe(self::PROBE_FILE, $this->probeSource());
		$I->probe('act=reset');
		$I->loginAsAdmin();
	}

	public function _after(AcceptanceTester $I)
	{
		$I->probe('act=reset');
	}

	public function theCoreFeedIsFetchedAfterTheSessionIsReleased(AcceptanceTester $I)
	{
		$I->amOnProbe('act=handler&mode=core&type=feed');

		$I->seeInSource(self::RELEASED);
		$I->dontSeeInSource(self::HELD);
	}

	public function theAddonFeedIsFetchedAfterTheSessionIsReleased(AcceptanceTester $I)
	{
		$I->amOnProbe('act=handler&mode=addons&type=plugin');

		$I->seeInSource(self::RELEASED);
		$I->dontSeeInSource(self::HELD);
	}

	public function theAddonVersionsAreLookedUpAfterTheSessionIsReleased(AcceptanceTester $I)
	{
		$I->amOnProbe('act=handler&mode=addons&type=update');

		$I->seeInSource(self::UPDATE_NAME.' '.self::RELEASED);
		$I->dontSeeInSource(self::HELD);
	}

	/**
	 * The check's answer outlives the request that found it: the next dashboard shows the notice, with this session's token in its links, and does not ask again.
	 */
	public function theDashboardShowsTheUpdateTheCheckFound(AcceptanceTester $I)
	{
		$style = $I->haveSitePref('adminstyle', 'infopanel');

		try
		{
			$I->amOnProbe('act=handler&mode=addons&type=update');
			$I->seeInSource(self::UPDATE_NAME);

			$token = $I->grabProbe('act=token');
			$I->amOnPage('/e107_admin/admin.php');

			$I->seeInSource(self::UPDATE_NAME);
			$I->seeInSource('action=download&amp;e-token='.$token);
			$I->dontSeeInSource('mode=addons&type=update');
		}
		finally
		{
			$I->haveSitePref('adminstyle', $style === '' ? null : $style);
		}
	}

	/**
	 * Any system cache clear removes the answer, a preference save among them, and the next dashboard asks again rather than showing nothing for the rest of the session.
	 */
	public function theDashboardRunsTheCheckAgainOnceTheAnswerIsGone(AcceptanceTester $I)
	{
		$style = $I->haveSitePref('adminstyle', 'infopanel');

		try
		{
			$I->amOnProbe('act=handler&mode=addons&type=update');
			$I->seeInSource(self::UPDATE_NAME);

			$I->probe('act=reset');
			$I->amOnPage('/e107_admin/admin.php');

			$I->dontSeeInSource(self::UPDATE_NAME);
			$I->seeInSource("id='e-admin-addons-update'");
			$I->seeInSource('mode=addons&type=update');
		}
		finally
		{
			$I->haveSitePref('adminstyle', $style === '' ? null : $style);
		}
	}

	/**
	 * A copy within its lifetime is on the dashboard as it renders, nothing on the page asks for another, and a request for one fetches nothing.
	 */
	public function aFreshCopyIsShownWithoutAFetch(AcceptanceTester $I)
	{
		$I->probe('act=keep&feed=core&age=60');

		$I->amOnPage('/e107_admin/admin.php');
		$I->seeInSource(self::KEPT);
		$I->dontSeeInSource(self::CORE_RENEWAL);

		$I->amOnProbe('act=handler&mode=core&type=feed');
		$I->seeInSource(self::KEPT);
		$I->dontSeeInSource(self::RELEASED);
	}

	/**
	 * An expired copy is shown at once while the page asks for a new one, and the answer to that request is the new copy, which the next dashboard shows without asking.
	 */
	public function aStaleCopyIsShownAndThenReplaced(AcceptanceTester $I)
	{
		$style = $I->haveSitePref('adminstyle', 'infopanel');

		try
		{
			$I->probe('act=keep&feed=core&age='.self::EXPIRED);
			$I->probe('act=keep&feed=plugin&age='.self::EXPIRED);

			$I->amOnPage('/e107_admin/admin.php');
			$I->seeNumberOfElements('#e-adminfeed .'.self::KEPT, 1);
			$I->seeNumberOfElements('#e-adminfeed-plugin .'.self::KEPT, 1);
			$I->seeInSource(self::CORE_RENEWAL);
			$I->seeInSource(self::PLUGIN_RENEWAL);

			$I->amOnProbe('act=handler&mode=core&type=feed');
			$I->seeInSource(self::RELEASED);
			$I->dontSeeInSource(self::KEPT);

			$I->amOnProbe('act=handler&mode=addons&type=plugin');
			$I->seeInSource(self::RELEASED);
			$I->dontSeeInSource(self::KEPT);

			$I->amOnPage('/e107_admin/admin.php');
			$I->dontSeeInSource(self::KEPT);
			$I->seeInSource(self::RELEASED);
			$I->dontSeeInSource(self::CORE_RENEWAL);
			$I->dontSeeInSource(self::PLUGIN_RENEWAL);
		}
		finally
		{
			$I->haveSitePref('adminstyle', $style === '' ? null : $style);
		}
	}

	/**
	 * With nothing kept the panel is empty until its request answers, as before, and what that request fetched is kept for the next dashboard.
	 */
	public function aMissingCopyIsFetched(AcceptanceTester $I)
	{
		$I->amOnPage('/e107_admin/admin.php');
		$I->seeInSource("<div id='e-adminfeed' style='min-height:300px'></div>");
		$I->seeInSource(self::CORE_RENEWAL);

		$I->amOnProbe('act=handler&mode=core&type=feed');
		$I->seeInSource(self::RELEASED);

		$I->amOnPage('/e107_admin/admin.php');
		$I->seeInSource(self::RELEASED);
		$I->dontSeeInSource(self::CORE_RENEWAL);
	}

	/**
	 * A fetch that fails, or answers with no items, leaves the expired copy in place and is not tried again for fifteen minutes.
	 */
	public function aFailedRenewalKeepsTheStaleCopyAndBacksOff(AcceptanceTester $I)
	{
		foreach(array('fail', 'empty') as $answer)
		{
			$I->probe('act=keep&feed=core&age='.self::EXPIRED);

			$I->amOnProbe('act=handler&mode=core&type=feed&answer='.$answer);
			$I->seeInSource(self::KEPT);
			$I->dontSeeInSource(self::RELEASED);

			$I->probe('act=rewind&feed=core&by='.(self::RETRY - 60));
			$I->amOnPage('/e107_admin/admin.php');
			$I->seeInSource(self::KEPT);
			$I->dontSeeInSource(self::CORE_RENEWAL);

			$I->amOnProbe('act=handler&mode=core&type=feed');
			$I->seeInSource(self::KEPT);
			$I->dontSeeInSource(self::RELEASED);
		}
	}

	/**
	 * A failed first fetch is remembered without becoming a copy, so the panel stays empty and is not asked for again for fifteen minutes.
	 */
	public function aFailedFirstFetchBacksOffWithoutACopy(AcceptanceTester $I)
	{
		$I->amOnProbe('act=handler&mode=core&type=feed&answer=fail');
		$I->dontSeeInSource(self::RELEASED);

		$I->probe('act=rewind&feed=core&by='.(self::RETRY - 60));
		$I->amOnPage('/e107_admin/admin.php');
		$I->seeInSource("<div id='e-adminfeed' style='min-height:300px'></div>");
		$I->dontSeeInSource(self::CORE_RENEWAL);
	}

	/**
	 * Fifteen minutes after a failed fetch the dashboard asks again, with the panel empty or showing the expired copy until the retry answers.
	 */
	public function aFailedFetchIsTriedAgainAfterFifteenMinutes(AcceptanceTester $I)
	{
		$I->amOnProbe('act=handler&mode=core&type=feed&answer=fail');
		$I->probe('act=rewind&feed=core&by='.self::RETRY);

		$I->amOnPage('/e107_admin/admin.php');
		$I->seeInSource("<div id='e-adminfeed' style='min-height:300px'></div>");
		$I->seeInSource(self::CORE_RENEWAL);

		$I->amOnProbe('act=handler&mode=core&type=feed');
		$I->seeInSource(self::RELEASED);

		$I->probe('act=keep&feed=core&age='.self::EXPIRED);
		$I->amOnProbe('act=handler&mode=core&type=feed&answer=fail');
		$I->probe('act=rewind&feed=core&by='.self::RETRY);

		$I->amOnPage('/e107_admin/admin.php');
		$I->seeInSource(self::KEPT);
		$I->seeInSource(self::CORE_RENEWAL);

		$I->amOnProbe('act=handler&mode=core&type=feed');
		$I->seeInSource(self::RELEASED);
		$I->dontSeeInSource(self::KEPT);
	}

	/**
	 * A copy fetched more than seven days ago is shown as no copy would be, even while a failed fetch to replace it backs off.
	 */
	public function aCopyFetchedMoreThanSevenDaysAgoIsNotShown(AcceptanceTester $I)
	{
		$I->probe('act=keep&feed=core&age='.self::OUTDATED.'&checked=60');

		$I->amOnPage('/e107_admin/admin.php');
		$I->seeInSource("<div id='e-adminfeed' style='min-height:300px'></div>");
		$I->dontSeeInSource(self::KEPT);
		$I->dontSeeInSource(self::CORE_RENEWAL);

		$I->amOnProbe('act=handler&mode=core&type=feed');
		$I->dontSeeInSource(self::KEPT);
		$I->dontSeeInSource(self::RELEASED);
	}

	/**
	 * @return string
	 */
	private function probeSource()
	{
		return <<<'PHP'
<?php
$act = isset($_GET['act']) ? $_GET['act'] : '';

if($act === 'handler')
{
	$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
}

require_once(__DIR__.'/class2.php');
{{E107_TEST_PROBE_GUARD}}

require_once(e_HANDLER.'xml_class.php');

function feed_session_state()
{
	return (session_status() === PHP_SESSION_ACTIVE) ? 'FEED_SESSION_HELD' : 'FEED_SESSION_RELEASED';
}

function feed_session_versions($type)
{
	$themes = e107::getTheme()->getList('version');
	$folder = key($themes);

	if($type !== 'theme' || $folder === null)
	{
		return array('-unable-to-connect');
	}

	return array($folder => array(
		'folder'    => $folder,
		'name'      => 'FEED_SESSION_UPDATE '.feed_session_state(),
		'version'   => '999.0',
		'author'    => $themes[$folder]['author'],
		'icon'      => '',
		'thumbnail' => '',
		'date'      => '2026-10-06',
		'url'       => '',
		'price'     => '',
		'params'    => array('id' => 0, 'mode' => ''),
	));
}

class FeedSessionXml extends xmlClass
{
	public function getRemoteFile($address, $timeout = 10, $postData = null)
	{
		$answer = isset($_GET['answer']) ? $_GET['answer'] : '';

		if($answer === 'fail')
		{
			return false;
		}

		$state = feed_session_state();
		$repeat = ($answer === 'empty') ? 0 : 2;

		if(strpos($address, ADMINFEED) === 0)
		{
			$item = '<item><title>'.$state.'</title><link>https://example.com/</link><pubDate>Tue, 06 Oct 2026 00:00:00 +0000</pubDate><description>-</description></item>';

			return '<?xml version="1.0" encoding="UTF-8"?><rss version="2.0"><channel><title>-</title><image><url>https://example.com/logo.png</url></image>'.str_repeat($item, $repeat).'</channel></rss>';
		}

		$addon = '<plugin name="'.$state.'" version="1.0" author="-" icon="https://example.com/icon.png" thumbnail="https://example.com/icon.png"><description>-</description></plugin>';

		return '<?xml version="1.0" encoding="UTF-8"?><e107>'.str_repeat($addon, $repeat).'</e107>';
	}
}

class FeedSessionCache extends ecache
{
	public function retrieve($CacheTag, $MaximumAge = false, $ForcedCheck = false, $syscache = false)
	{
		if(strpos($CacheTag, 'Versions_') === 0)
		{
			return json_encode(feed_session_versions(substr($CacheTag, strlen('Versions_'))));
		}

		return parent::retrieve($CacheTag, $MaximumAge, $ForcedCheck, $syscache);
	}
}

switch($act)
{
	case 'handler':
		e107::setRegistry('core/e107/singleton/xmlClass', new FeedSessionXml());
		e107::setRegistry('core/e107/singleton/ecache', new FeedSessionCache());
		$_GET['e-token'] = defset('e_TOKEN');
		require_once(e_ADMIN.'boot.php');
		echo "PROBE_FAIL the handler did not answer\n";
		break;

	case 'token':
		echo defset('e_TOKEN');
		break;

	case 'keep':
		$copy = '<p class="FEED_COPY_KEPT">FEED_COPY_KEPT</p>';
		$now = time();
		$checked = isset($_GET['checked']) ? (int) $_GET['checked'] : (int) $_GET['age'];
		$entry = array('html' => $copy, 'fetched' => $now - (int) $_GET['age'], 'checked' => $now - $checked);
		e107::getCache()->set('Infopanel_'.$_GET['feed'], json_encode($entry), true, false, true);
		echo "PROBE_OK keep\n";
		break;

	case 'rewind':
		$tag = 'Infopanel_'.$_GET['feed'];
		$entry = json_decode(e107::getCache()->retrieve($tag, false, true, true), true);
		$entry['fetched'] -= (int) $_GET['by'];
		$entry['checked'] -= (int) $_GET['by'];
		e107::getCache()->set($tag, json_encode($entry), true, false, true);
		echo "PROBE_OK rewind\n";
		break;

	case 'reset':
		e107::getCache()->clearAll('system');
		echo "PROBE_OK reset\n";
		break;
}
PHP;
	}
}
