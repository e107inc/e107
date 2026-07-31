<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Automatic CSRF token injection into the finished page.
 */

if(!defined('e107_INIT'))
{
	exit;
}

/**
 * Adds the CSRF token to every eligible form in the finished page.
 *
 * e_form::open() has never emitted a token and a large part of core, plus an
 * unbounded set of third-party plugins, writes raw <form> markup, so token
 * protection has always been opt-in and mostly opted out of. Injecting at the
 * output-buffer flush chokepoint, {@see e_http_header::setContent()}, makes it
 * opt-out instead and requires no plugin changes.
 *
 * Eligibility is deliberately narrow, because a token sent to the wrong place is
 * worse than no token at all:
 *
 * - the response has to be HTML, since the same buffer also carries XML feeds and
 *   sitemaps;
 * - the form has to be method="post", because a GET form would carry the token
 *   into the query string, the Referer header and the access log;
 * - the action has to resolve to this host, because a form posting to a payment
 *   gateway or an external search would otherwise hand the session's CSRF token
 *   to a third party. Anything that cannot be proven same-origin is left alone;
 * - markup inside <textarea>, <script> and HTML comments is never touched. The
 *   language-file editor and several plugin admin areas legitimately place form
 *   markup inside a textarea, and writing into one corrupts the content on save.
 *
 * The token is inserted immediately after the form's opening tag rather than
 * before its closing tag. That position is always inside the form element even
 * when the markup is a legacy <form><table> construction, where an input placed
 * last would be foster-parented out of the table by the browser and never
 * submitted. A form that already carries e_form::token() ends up with two
 * identical hidden inputs, which PHP resolves last-wins to the same value.
 *
 * Two deliberate changes to rendered HTML follow from this, on the front end as
 * well as in the admin area: every same-origin POST form gains a hidden input as
 * its FIRST child, which shifts what a theme's form > :first-child rule selects,
 * and every HTML page gains a <meta name="e-token"> before </head>. Neither is
 * configurable: {@see e_form::token()} is deprecated and core no longer emits a
 * token by hand anywhere, so injection is the only thing standing between a form
 * and the refusal in {@see e_core_session::check()}.
 */
class e_token_injector
{
	const FIELD_NAME = 'e-token';

	/**
	 * One pass over the page. The alternation order matters: a comment, a
	 * textarea or a script is consumed whole and handed back untouched, so a
	 * <form> written inside one is never seen as a form.
	 *
	 * The last two branches consume any other complete tag, and they are what
	 * keeps the first three honest. Without them the scan could start inside an
	 * attribute value, so `<div data-x="<script>">` in a news item began a script
	 * that ran to the theme's footer and quietly swallowed every form between.
	 * The page rendered perfectly and its forms went out with no token. Now a tag
	 * is always consumed whole, so its attribute values are never a match start.
	 *
	 * Each of the tag branches is followed by a lenient twin matching to the
	 * first ">", for a tag whose quotes do not balance (`title="Don"t"`). That is
	 * what a browser's tokenizer does with one, and matching it is better than
	 * skipping the tag and resuming the scan in the middle of it.
	 *
	 * Every repetition is possessive. A lazy .*? costs one backtrack per
	 * character, which a page carrying a large inline script pushes past
	 * pcre.backtrack_limit, and the whole pass would then silently do nothing.
	 */
	const PATTERN = '~<!--(?:[^-]++|-(?!->|-!>))*+--!?>|<textarea\b[^>]*+>[^<]*+(?:<(?!/textarea\s*+>)[^<]*+)*+</textarea\s*+>|<script\b[^>]*+>[^<]*+(?:<(?!/script\s*+>)[^<]*+)*+</script\s*+>|<form\b(?:[^>"\']++|"[^"]*+"|\'[^\']*+\')*+>|<form\b[^>]*+>|<[a-z][a-z0-9:._-]*+(?:[^>"\']++|"[^"]*+"|\'[^\']*+\')*+>|<[a-z][a-z0-9:._-]*+[^>]*+>~is';

	/**
	 * Gate and transform a finished page.
	 *
	 * @param string $content whole response body
	 * @return string
	 */
	public static function process($content)
	{
		if(!is_string($content) || $content === '')
		{
			return $content;
		}

		// Publishing a token that nothing will check is not merely wasted work.
		// It is a live session token stamped into every same-origin form on the
		// page, including one an author or an attacker put in a news item, and
		// this pass cannot tell whose form is whose.
		//
		// e_TOKEN itself is still minted, because a fair amount of core writes it
		// into a form by hand and some of those read the bare constant.
		if(!e_session::modeUsesToken())
		{
			return $content;
		}

		$token = defset('e_TOKEN');

		if(empty($token) || !self::isHtmlResponse())
		{
			return $content;
		}

		return self::inject($content, $token, self::currentHosts());
	}

	/**
	 * Inject the token into every eligible form, and into the document head.
	 *
	 * Kept free of globals so it can be exercised directly.
	 *
	 * @param string $content whole response body
	 * @param string $token   value of e_TOKEN
	 * @param array  $hosts   normalised hosts that count as this origin
	 * @return string
	 */
	public static function inject($content, $token, array $hosts)
	{
		$token = htmlspecialchars($token, ENT_QUOTES);

		$content = self::injectMeta($content, $token);

		if(stripos($content, '<form') === false)
		{
			return $content;
		}

		$field = '<input type="hidden" name="' . self::FIELD_NAME . '" value="' . $token . '" />';

		$replaced = preg_replace_callback(
			self::PATTERN,
			function ($match) use ($field, $hosts)
			{
				if(strncasecmp($match[0], '<form', 5) !== 0)
				{
					return $match[0];
				}

				return e_token_injector::isEligibleForm($match[0], $hosts) ? $match[0] . $field : $match[0];
			},
			$content
		);

		return ($replaced === null) ? $content : $replaced;
	}

	/**
	 * Does this opening form tag describe a same-origin POST?
	 *
	 * @param string $tag opening <form ...> tag, as written
	 * @param array  $hosts normalised hosts that count as this origin
	 * @return bool
	 */
	public static function isEligibleForm($tag, array $hosts)
	{
		if(!preg_match('~(?<![\w-])method\s*=\s*["\']?\s*post\b~i', $tag))
		{
			return false;
		}

		if(!preg_match('~(?<![\w-])action\s*=\s*("[^"]*"|\'[^\']*\'|[^\s"\'>]+)~i', $tag, $match))
		{
			return true;
		}

		$action = trim($match[1], '"\'');
		$action = html_entity_decode($action, ENT_QUOTES, 'UTF-8');
		$action = str_replace(array("\t", "\n", "\r"), '', $action);
		$action = trim($action);

		return self::isSameOrigin($action, $hosts);
	}

	/**
	 * Would a browser send this form action back to one of our own hosts?
	 *
	 * Fails closed: anything that cannot be resolved with certainty is refused.
	 *
	 * @param string $action decoded form action
	 * @param array  $hosts  normalised hosts that count as this origin
	 * @return bool
	 */
	public static function isSameOrigin($action, array $hosts)
	{
		if($action === '' || $action[0] === '#' || $action[0] === '?')
		{
			return true;
		}

		// A backslash is normalised to a slash by browsers, so "\\evil.com" and
		// "/\evil.com" are both off-origin. No legitimate action contains one.
		if(strpos($action, '\\') !== false)
		{
			return false;
		}

		if(strncmp($action, '//', 2) === 0)
		{
			return self::hostMatches(substr($action, 2), $hosts);
		}

		if($action[0] === '/')
		{
			return true;
		}

		if(preg_match('~^https?://~i', $action, $match))
		{
			return self::hostMatches(substr($action, strlen($match[0])), $hosts);
		}

		if(preg_match('~^[a-z][a-z0-9+.\-]*:~i', $action))
		{
			return false;
		}

		return true;
	}

	/**
	 * @param string $rest everything after the scheme and its slashes
	 * @param array  $hosts normalised hosts that count as this origin
	 * @return bool
	 */
	private static function hostMatches($rest, array $hosts)
	{
		$authority = substr($rest, 0, strcspn($rest, '/?#'));

		$at = strrpos($authority, '@');

		if($at !== false)
		{
			$authority = substr($authority, $at + 1);
		}

		if($authority === '')
		{
			return false;
		}

		return in_array(self::normaliseHost($authority), $hosts, true);
	}

	/**
	 * Hosts a form may post to without leaving this site.
	 *
	 * @return array
	 */
	public static function currentHosts()
	{
		$hosts = array();

		if(!empty($_SERVER['HTTP_HOST']))
		{
			$hosts[] = self::normaliseHost($_SERVER['HTTP_HOST']);
		}

		$siteurl = defset('SITEURL');

		if(!empty($siteurl))
		{
			$parts = parse_url($siteurl);

			if(!empty($parts['host']))
			{
				$hosts[] = self::normaliseHost($parts['host'] . (empty($parts['port']) ? '' : ':' . $parts['port']));
			}
		}

		return array_values(array_unique(array_filter($hosts)));
	}

	/**
	 * @param string $host
	 * @return string
	 */
	private static function normaliseHost($host)
	{
		$host = strtolower(trim($host));
		$host = rtrim($host, '.');

		return preg_replace('~:(80|443)$~', '', $host);
	}

	/**
	 * Publish the token in the document head so scripts can find it on a page
	 * that has no form of its own.
	 *
	 * @param string $content
	 * @param string $token already escaped for an attribute value
	 * @return string
	 */
	private static function injectMeta($content, $token)
	{
		$position = stripos($content, '</head>');

		if($position === false)
		{
			return $content;
		}

		$meta = '<meta name="' . self::FIELD_NAME . '" content="' . $token . '" />' . "\n";

		return substr($content, 0, $position) . $meta . substr($content, $position);
	}

	/**
	 * Is the response we are about to send HTML?
	 *
	 * Nothing in e107 routes Content-Type through e_http_header, so the pending
	 * header list is consulted directly. It is reliable here because the whole
	 * response is still buffered.
	 *
	 * @return bool
	 */
	private static function isHtmlResponse()
	{
		$type = null;

		foreach(headers_list() as $header)
		{
			if(strncasecmp($header, 'Content-Type:', 13) === 0)
			{
				$type = substr($header, 13);
			}
		}

		if($type === null)
		{
			// PHP does not list the implicit default, so ask for it.
			$type = (string) ini_get('default_mimetype');
		}

		$semicolon = strpos($type, ';');

		if($semicolon !== false)
		{
			$type = substr($type, 0, $semicolon);
		}

		$type = strtolower(trim($type));

		return ($type === '' || $type === 'text/html' || $type === 'application/xhtml+xml');
	}
}
