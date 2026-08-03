<?php

namespace Helper;

/**
 * Shared probe for the P19 (open redirect) Cests.
 *
 * Not a Codeception module: it holds no state, so a module would only add a
 * suite-configuration entry to maintain. The same shape as {@see OutputEncodingFixture}.
 *
 * Two things have to be asked of the application rather than assumed. The
 * first is SITEURL, because a refused redirect is measured as "bounced to the
 * site" and the site is whatever SITEURL says it is. The second is the URL the
 * forum jump dropdown actually puts in its options: the shortcodes that build
 * that option list do not agree on the shape, some asking for the absolute form
 * and some for the short one, and a fix that only accepts the absolute form
 * would break the feature for the rest while every refusal assertion passed.
 *
 * The probe also calls redirect_class::go() directly with a destination taken
 * from the query string. That is the systemic shape the package is about: it
 * stands in for every caller in the tree that passes request data to go(), and
 * it measures go()'s own default rather than any one call site's care.
 *
 * The filename is registered in {@see \Extension\WorkspaceCleanup} so a crashed
 * run does not leave it behind.
 */
class RedirectFixture
{
	/** Probe dropped into the docroot for as long as a Cest needs it. */
	const PROBE_FILE = 'e107_tests_redirect_probe.php';

	/**
	 * A host that cannot resolve, so a regression cannot emit real outbound
	 * traffic even if something follows the redirect.
	 */
	const OFFSITE = 'https://evil.example.invalid/phish';

	/** The same destination without a scheme. */
	const OFFSITE_PROTOCOL_RELATIVE = '//evil.example.invalid/phish';

	/**
	 * And with a backslash, which several browsers normalise to a forward
	 * slash before they resolve the authority.
	 */
	const OFFSITE_BACKSLASH = '/\\evil.example.invalid/phish';

	/**
	 * The same destination with a tab where a string predicate reads a rooted
	 * path and a URL parser reads an authority. The WHATWG basic URL parser
	 * deletes every ASCII tab, LF and CR before it parses anything, and PHP's
	 * header() rejects only LF and CR, so the tab reaches the client.
	 *
	 * curl percent-encodes a tab rather than deleting it, which is exactly why
	 * this has to be asserted on the Location header rather than on where a
	 * PhpBrowser-driven client lands.
	 */
	const OFFSITE_TAB = "/\t/evil.example.invalid/phish";

	/** Leading whitespace, which every HTTP client strips from a header value. */
	const OFFSITE_LEADING_SPACE = ' //evil.example.invalid/phish';

	/** The substring whose absence in a Location header is the assertion. */
	const OFFSITE_HOST = 'evil.example.invalid';

	/**
	 * @return string
	 */
	public static function probeSource()
	{
		return <<<'PHP'
<?php
// Fixture for the P19 Cests. Written per test, removed in _after().
$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');

$act = isset($_GET['p19']) ? $_GET['p19'] : '';
$dest = isset($_GET['dest']) ? $_GET['dest'] : '';

switch($act)
{
	case 'constants':
		header('Content-Type: text/plain');
		echo "P19_OK constants\n";
		echo 'SITEURL:'.SITEURL."\n";
		echo 'SITEURLBASE:'.SITEURLBASE."\n";
		echo 'e_HTTP:'.e_HTTP."\n";
		break;

	case 'jumpurl':
		header('Content-Type: text/plain');
		$row = e107::getDb()->retrieve('forum', '*', 'forum_id='.(int) varset($_GET['id']));
		echo "P19_OK jumpurl\n";
		// The two forms the live sc_forumjump() implementations emit.
		echo 'FULL:'.e107::url('forum', 'forum', $row, 'full')."\n";
		echo 'SHORT:'.e107::url('forum', 'forum', $row)."\n";
		// Where a refused jump lands, which is not where a refused go() lands.
		echo 'INDEX:'.var_export(e107::url('forum', 'index', null, array('mode' => 'full')), true)."\n";
		break;

	case 'verify':
		header('Content-Type: text/plain');
		echo "P19_OK verify\n";
		echo 'VERIFY:'.var_export(e107::getRedirect()->verifyDestinationUrl($dest), true)."\n";
		break;

	case 'go':
		// The fifth argument is the off-site opt-in. PHP ignores a surplus
		// argument to a user-defined function, so this same probe runs against
		// a go() that has not got one yet.
		e107::getRedirect()->go($dest, true, null, true, !empty($_GET['external']));
		break;

	default:
		header('Content-Type: text/plain');
		echo "unknown action\n";
}
PHP;
	}
}
