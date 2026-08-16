<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

use e107\Reflection\ReflectionMethod;

/**
 * Coverage for GHSA-72q5-94gw-prww.
 *
 * The injector runs over every finished HTML page, so a mistake here is not a
 * cosmetic one: writing into a textarea corrupts what the operator saves, and
 * writing into an off-site form hands the session's CSRF token to a third party.
 */
class e_token_injectorTest extends \Test\Unit
{

	const TOKEN = 'abcdef0123456789abcdef0123456789';

	private $hosts = array('example.com');

	protected function _before()
	{
		e107::getInstance();
		require_once(e_HANDLER . 'token_injector_handler.php');
	}

	private function inject($html)
	{
		return e_token_injector::inject($html, self::TOKEN, $this->hosts);
	}

	private function assertInjected($html)
	{
		$this->assertStringContainsString(
			'<input type="hidden" name="e-token" value="' . self::TOKEN . '" />',
			$this->inject($html)
		);
	}

	private function assertNotInjected($html)
	{
		$this->assertStringNotContainsString('name="e-token"', $this->inject($html));
	}

	public function testPostFormGetsTheToken()
	{
		$this->assertInjected('<form method="post" action="/admin.php"><input name="a" /></form>');
	}

	public function testTokenLandsInsideTheForm()
	{
		$out = $this->inject('<form method="post"><table><tr><td>x</td></tr></table></form>');

		$this->assertSame(
			'<form method="post"><input type="hidden" name="e-token" value="' . self::TOKEN . '" /><table><tr><td>x</td></tr></table></form>',
			$out
		);
	}

	public function testMethodIsMatchedCaseInsensitivelyAndUnquoted()
	{
		$this->assertInjected('<form method="POST">x</form>');
		$this->assertInjected('<form method=post>x</form>');
		$this->assertInjected("<form\n\tmethod='post'\n\taction='/x'>x</form>");
	}

	public function testGetFormIsLeftAlone()
	{
		$this->assertNotInjected('<form method="get" action="/search.php"><input name="q" /></form>');
	}

	public function testFormWithNoMethodIsLeftAlone()
	{
		$this->assertNotInjected('<form action="/search.php"><input name="q" /></form>');
	}

	public function testLookalikeAttributesDoNotCount()
	{
		$this->assertNotInjected('<form data-method="post" method="get">x</form>');
		$this->assertInjected('<form data-method="get" method="post">x</form>');
	}

	/**
	 * The one that costs data rather than security. The language-file editor and
	 * several plugin admin areas put form markup inside a textarea, and an input
	 * written in there is saved back into the content.
	 */
	public function testFormInsideATextareaIsNeverTouched()
	{
		$html = '<textarea name="body"><form method="post" action="/x">inner</form></textarea>';

		$this->assertSame($html, $this->inject($html));
	}

	public function testTextareaDoesNotSuppressALaterForm()
	{
		$out = $this->inject('<textarea><form method="post">a</form></textarea><form method="post">b</form>');

		$this->assertSame(1, substr_count($out, 'name="e-token"'));
		$this->assertStringContainsString('<textarea><form method="post">a</form></textarea>', $out);
	}

	public function testFormInsideAScriptIsNeverTouched()
	{
		$html = '<script>var markup = \'<form method="post" action="/x">\';</script>';

		$this->assertSame($html, $this->inject($html));
	}

	public function testFormInsideAnHtmlCommentIsNeverTouched()
	{
		$html = '<!-- <form method="post" action="/x">old</form> -->';

		$this->assertSame($html, $this->inject($html));
	}

	/**
	 * A lazy quantifier costs one backtrack per character, so a page carrying a
	 * large inline script used to blow pcre.backtrack_limit and quietly leave the
	 * whole page untouched.
	 */
	public function testALargeInlineScriptDoesNotDefeatThePass()
	{
		$html = '<script>' . str_repeat('a', 2000000) . '</script><form method="post">x</form>';

		$this->assertInjected($html);
	}

	public function testSameOriginActionsAreAccepted()
	{
		$this->assertInjected('<form method="post" action="">x</form>');
		$this->assertInjected('<form method="post" action="?mode=x">x</form>');
		$this->assertInjected('<form method="post" action="#">x</form>');
		$this->assertInjected('<form method="post" action="/e107_admin/users.php">x</form>');
		$this->assertInjected('<form method="post" action="relative/path.php">x</form>');
		$this->assertInjected('<form method="post" action="//example.com/x">x</form>');
		$this->assertInjected('<form method="post" action="https://example.com/x">x</form>');
		$this->assertInjected('<form method="post" action="http://EXAMPLE.COM./x">x</form>');
		$this->assertInjected('<form method="post" action="https://example.com:443/x">x</form>');
	}

	public function testOffOriginActionsAreRefused()
	{
		$this->assertNotInjected('<form method="post" action="https://evil.example.net/pay">x</form>');
		$this->assertNotInjected('<form method="post" action="//evil.example.net/pay">x</form>');
		$this->assertNotInjected('<form method="post" action="https://user@evil.example.net/pay">x</form>');
		$this->assertNotInjected('<form method="post" action="https://example.com.evil.net/pay">x</form>');
		$this->assertNotInjected('<form method="post" action="https://example.com:8443/x">x</form>');
	}

	public function testBackslashActionsAreRefused()
	{
		$this->assertNotInjected('<form method="post" action="\\\\evil.example.net/pay">x</form>');
		$this->assertNotInjected('<form method="post" action="/\\evil.example.net/pay">x</form>');
	}

	public function testNonHttpSchemesAreRefused()
	{
		$this->assertNotInjected('<form method="post" action="mailto:someone@example.com">x</form>');
		$this->assertNotInjected('<form method="post" action="javascript:submitIt()">x</form>');
		$this->assertNotInjected('<form method="post" action="ftp://example.com/x">x</form>');
		$this->assertNotInjected('<form method="post" action="data:text/html,x">x</form>');
	}

	public function testEntityEncodedOffOriginActionIsRefused()
	{
		$this->assertNotInjected('<form method="post" action="&#47;&#47;evil.example.net/pay">x</form>');
	}

	/**
	 * Browsers strip tab, carriage return and newline from a URL before parsing it.
	 */
	public function testControlCharactersInTheActionDoNotHideTheHost()
	{
		$this->assertNotInjected("<form method=\"post\" action=\"htt\np://evil.example.net/pay\">x</form>");
		$this->assertNotInjected("<form method=\"post\" action=\"//evil.\texample.net/pay\">x</form>");
	}

	public function testAGreaterThanInsideAnAttributeDoesNotSplitTheTag()
	{
		$out = $this->inject('<form method="post" action="/x" title="a>b">y</form>');

		$this->assertStringContainsString('title="a>b"><input type="hidden" name="e-token"', $out);
	}

	public function testTokenIsPublishedInTheDocumentHead()
	{
		$out = $this->inject('<html><head><title>t</title></head><body>x</body></html>');

		$this->assertStringContainsString('<meta name="e-token" content="' . self::TOKEN . '" />', $out);
		$this->assertLessThan(strpos($out, '</head>'), strpos($out, '<meta name="e-token"'));
	}

	public function testPageWithoutAHeadIsStillSafe()
	{
		$out = $this->inject('<form method="post">x</form>');

		$this->assertStringNotContainsString('<meta', $out);
	}

	public function testTokenIsEscapedForAnAttribute()
	{
		$out = e_token_injector::inject('<form method="post">x</form>', 'a"b<c', $this->hosts);

		$this->assertStringContainsString('value="a&quot;b&lt;c"', $out);
	}

	public function testPageWithNoFormIsReturnedUnchanged()
	{
		$html = '<div><p>nothing to do here</p></div>';

		$this->assertSame($html, $this->inject($html));
	}

	public function testEmptyContentIsReturnedUnchanged()
	{
		$this->assertSame('', e_token_injector::process(''));
	}

	/**
	 * The same output buffer carries RSS feeds and the sitemap. Injecting a hidden
	 * input into either produces invalid XML, so the gate has to hold before any
	 * form is even looked for.
	 *
	 * headers_list() is empty under CLI, which is exactly the path that falls back
	 * to default_mimetype, so the fallback is what this exercises.
	 */
	public function testNonHtmlResponsesAreSkipped()
	{
		$method = new ReflectionMethod('e_token_injector', 'isHtmlResponse');

		$mimetype = ini_get('default_mimetype');

		try
		{
			foreach(array('text/html', 'text/html; charset=utf-8', 'application/xhtml+xml', '') as $type)
			{
				ini_set('default_mimetype', $type);
				$this->assertTrue($method->invoke(null), 'Should inject into: ' . var_export($type, true));
			}

			foreach(array('application/xml', 'text/xml', 'application/json', 'text/plain', 'application/rss+xml') as $type)
			{
				ini_set('default_mimetype', $type);
				$this->assertFalse($method->invoke(null), 'Should skip: ' . var_export($type, true));
			}
		}
		finally
		{
			ini_set('default_mimetype', $mimetype);
		}
	}

	/**
	 * process() is the gated wrapper. With no token defined there is nothing to
	 * inject and the page has to come back byte-identical.
	 */
	public function testProcessWithoutATokenIsAPassThrough()
	{
		if(defined('e_TOKEN') && !empty(e_TOKEN))
		{
			$this->markTestSkipped('e_TOKEN is defined for this run.');
		}

		$html = '<html><head></head><body><form method="post">x</form></body></html>';

		$this->assertSame($html, e_token_injector::process($html));
	}

	public function testHostListIsNormalised()
	{
		$hosts = e_token_injector::currentHosts();

		$this->assertIsArray($hosts);

		foreach($hosts as $host)
		{
			$this->assertSame(strtolower($host), $host);
		}
	}

	/**
	 * The scan must never start inside an attribute value.
	 *
	 * A browser reads `<script>` in an attribute as text, so the page renders
	 * exactly as its author intended and nothing looks wrong. The pass used to
	 * read it as the start of a script and skip forward to the theme's real
	 * `</script>`, swallowing every form in between and sending them out with no
	 * token. Anyone who can post HTML could switch off token injection for the
	 * rest of the page by accident.
	 */
	public function testAScriptTagInsideAnAttributeDoesNotSwallowLaterForms()
	{
		$html = '<div data-x="<script>">rendered fine</div>'
			. '<form method="post" action="/x">a</form>'
			. '<script>var a = 1;</script>';

		$this->assertInjected($html);
	}

	public function testACommentOpenerInsideAnAttributeDoesNotSwallowLaterForms()
	{
		$html = '<a title="<!--">x</a>'
			. '<form method="post" action="/x">a</form>'
			. '<!-- footer -->';

		$this->assertInjected($html);
	}

	public function testATextareaTagInsideAnAttributeDoesNotSwallowLaterForms()
	{
		$html = '<div data-y="<textarea>">x</div>'
			. '<form method="post" action="/x">a</form>'
			. '<textarea name="q">hi</textarea>';

		$this->assertInjected($html);
	}

	/**
	 * A browser ends a comment at `--!>` as well as at `-->`, so the pass has to
	 * agree with it about where the comment stops.
	 */
	public function testCommentClosedWithABangIsNotRunPast()
	{
		$html = '<!-- x --!><form method="post" action="/x">a</form><!-- y -->';

		$this->assertInjected($html);
	}

	/**
	 * `title="Don"t"` is the everyday way a tag ends up with unbalanced quotes.
	 * A browser ends the tag at the first ">"; so must this, rather than giving
	 * up on the tag and resuming the scan somewhere inside it.
	 */
	public function testATagWithUnbalancedQuotesDoesNotSuppressALaterForm()
	{
		$html = '<div title="Don"t">x</div><form method="post" action="/x">a</form>';

		$this->assertInjected($html);
	}

	public function testAFormTagWithUnbalancedQuotesStillGetsTheToken()
	{
		$this->assertInjected('<form method=post action=/x?a=b\'c><input value="y" /></form>');
	}

	/**
	 * The tag branches added for the cases above must not start matching things
	 * that are genuinely inside a script, a textarea or a comment.
	 */
	public function testMarkupInsideSkippedConstructsIsStillLeftAlone()
	{
		$html = '<script>var m = \'<div id="a"><form method="post" action="/x"></form></div>\';</script>'
			. '<textarea><div><form method="post" action="/y"></form></div></textarea>'
			. '<!-- <div><form method="post" action="/z"></form></div> -->';

		$this->assertSame($html, $this->inject($html));
	}

	public function testAPageOfOrdinaryTagsStillGetsExactlyOneTokenPerForm()
	{
		$html = str_repeat('<div class="a b" data-x="1">text</div>', 200)
			. '<form method="post" action="/a">1</form>'
			. str_repeat('<p><span>t</span></p>', 200)
			. '<form method="post" action="/b">2</form>';

		$this->assertSame(2, substr_count($this->inject($html), 'name="e-token"'));
	}
}
