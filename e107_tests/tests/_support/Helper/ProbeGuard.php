<?php

namespace Helper;

/**
 * Containment for the fixtures a run drops into the docroot.
 *
 * A probe boots e107 in the app root and then does what the query string asks:
 * rewrites core preferences, empties tables, installs and uninstalls plugins.
 * A run that is killed leaves it there, and the docroot is a live site.
 *
 * Every fixture that boots e107 in the app root reserves {@see self::MARKER}
 * on the line after its bootstrap, and {@see \Helper\E107Base::writeAppFile()}
 * substitutes the guard for it on the way to the docroot, refusing a fixture
 * that reserved no room. A caller that cannot show the run's secret is
 * answered 403 having done nothing.
 *
 * Show the secret in {@see self::HEADER}, which the acceptance suite sets on
 * every request, or in the {@see self::PARAMETER} query parameter, which is
 * what a caller that cannot set a header uses.
 *
 * Not a Codeception module: it holds one secret for the length of a run and
 * would only add a suite-configuration entry to maintain. The same shape as
 * {@see OutputEncodingFixture}.
 */
class ProbeGuard
{
	/**
	 * The line a docroot fixture reserves for the guard. Not a comment, so an
	 * unsubstituted marker is a parse error rather than an open door.
	 */
	const MARKER = '{{E107_TEST_PROBE_GUARD}}';

	/** The bootstrap that makes a docroot fixture a probe rather than a payload. */
	const BOOTSTRAP = "require_once(__DIR__.'/class2.php');";

	/** Request header the acceptance suite shows the secret in. */
	const HEADER = 'X-E107-Test-Probe';

	/** Query parameter, for a caller that cannot set a header. */
	const PARAMETER = 'probe';

	/** What a caller that cannot show the secret is answered with. */
	const REFUSAL = 'E107_TEST_PROBE_FORBIDDEN';

	/** @var string|null minted once, for the length of the run */
	private static $secret;

	/**
	 * @return string the secret this run's probes accept
	 */
	public static function secret()
	{
		if (self::$secret === null)
		{
			self::$secret = hash('sha256', uniqid('', true).mt_rand());
		}

		return self::$secret;
	}

	/**
	 * @return string query string fragment carrying the secret, without a leading ?
	 */
	public static function query()
	{
		return self::PARAMETER.'='.self::secret();
	}

	/**
	 * Substitute the guard, and refuse a probe that reserved no room for one.
	 *
	 * @param string $relative_path path relative to the app root
	 * @param string $contents bytes on their way to the docroot
	 * @return string
	 */
	public static function contain($relative_path, $contents)
	{
		if (strpos($contents, self::BOOTSTRAP) === false)
		{
			return $contents;
		}

		if (strpos($contents, self::MARKER) === false)
		{
			throw new \RuntimeException($relative_path
				.' boots e107 in the docroot, so it must carry '.self::MARKER
				.' on the line after its bootstrap');
		}

		return str_replace(self::MARKER, self::source(), $contents);
	}

	/**
	 * @return string the guard, as it is written into a probe
	 */
	private static function source()
	{
		$secret = self::secret();
		$header = 'HTTP_'.strtoupper(str_replace('-', '_', self::HEADER));
		$parameter = self::PARAMETER;
		$refusal = self::REFUSAL;

		return <<<PHP
\$e107_test_probe_shown = '';

if(isset(\$_SERVER['$header']))
{
	\$e107_test_probe_shown = \$_SERVER['$header'];
}
elseif(isset(\$_GET['$parameter']))
{
	\$e107_test_probe_shown = \$_GET['$parameter'];
}

if(!is_string(\$e107_test_probe_shown) || !hash_equals('$secret', \$e107_test_probe_shown))
{
	header('HTTP/1.1 403 Forbidden', true, 403);
	echo '$refusal';
	exit;
}

unset(\$e107_test_probe_shown);
PHP;
	}
}
