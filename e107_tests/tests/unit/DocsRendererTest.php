<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Locks in the contracts established by the rich Bootstrap 3 docs renderer
 * in e107_admin/docs.php (PR #5596). The four cases below mirror the
 * regressions that the e107help[bot] review surfaced, so any future change
 * to docs_ui::renderDoc() / buildToc() / slug() that breaks them fails fast
 * here instead of in production admin pages.
 */

class DocsRendererTest extends \Codeception\Test\Unit
{

	/**
	 * @var ReflectionClass
	 */
	private $reflection;

	/**
	 * @var docs_ui
	 */
	private $ui;

	protected function _before()
	{
		// docs.php is a full admin entry point: it requires class2.php, exits
		// when ADMIN is not set, registers inline CSS/JS, and constructs the
		// dispatcher (`new docs_admin();`) at the bottom. We can't `require`
		// it directly from the test, so extract only the two class bodies
		// with a small regex and eval them once.
		if(!class_exists('docs_ui', false))
		{
			if(!defined('DOC_MEDIA_URL'))
			{
				define('DOC_MEDIA_URL', e_DOCS . '_media/');
			}
			// docs_ui extends e_admin_ui — make sure the parent is loaded
			// before eval() resolves the class hierarchy.
			require_once(e_HANDLER . 'admin_ui.php');

			$source = file_get_contents(e_ADMIN . 'docs.php');
			$classes = '';
			if(preg_match_all('/^class\s+(docs_ui|docs_form_ui)\b.*?^\}/sm', $source, $matches))
			{
				$classes = implode("\n\n", $matches[0]);
			}
			$this->assertNotSame('', $classes, 'failed to extract docs_ui from docs.php');
			eval($classes);
		}

		$this->reflection = new ReflectionClass('docs_ui');
		$this->ui = $this->reflection->newInstanceWithoutConstructor();
	}

	/**
	 * Invoke a private method by name via reflection.
	 */
	private function invoke($method, array $args = array())
	{
		$m = $this->reflection->getMethod($method);
		$m->setAccessible(true);
		return $m->invokeArgs($this->ui, $args);
	}

	/**
	 * Round-1 regression: a multi-line `A>` body must stay inside the
	 * panel-body wrapper. The legacy parser used `preg_replace('/Q\>(.*?)A>/si', ...)`
	 * which crossed newlines; the line-based rewrite has to reproduce that
	 * contract or third-party translations leak content into trailing
	 * paragraphs outside the panel.
	 */
	public function testMultilineAnswerStaysInsidePanelBody()
	{
		$raw = "Q> Multi-line example\nA> first line\nsecond line\nthird line";
		$rendered = $this->invoke('renderDoc', array($raw, 0));

		$this->assertMatchesRegularExpression(
			'#<div class="panel-body">.*?first line.*?second line.*?third line.*?</div></div></div>#s',
			$rendered['html'],
			'multi-line A> body must remain inside <div class="panel-body">…</div>'
		);
	}

	/**
	 * Round-1 regression: a `Q>` with no trailing `A>` must still emit a
	 * complete (empty-body) panel rather than leaving the panel open so the
	 * next marker leaks into it.
	 */
	public function testOrphanQuestionEmitsEmptyPanel()
	{
		$raw = "Q> Orphan question\n\nP> Following lead paragraph";
		$rendered = $this->invoke('renderDoc', array($raw, 0));

		// The orphan Q> must produce a fully-closed panel.
		$this->assertStringContainsString(
			'Orphan question',
			$rendered['html']
		);
		// The lead paragraph that follows must NOT be wrapped inside the panel-body.
		$this->assertDoesNotMatchRegularExpression(
			'#<div class="panel-body">[^<]*<p class="lead">Following lead#s',
			$rendered['html'],
			'a P> following an orphan Q> must not be captured inside the panel-body'
		);
		$this->assertStringContainsString(
			'<p class="lead">Following lead paragraph</p>',
			$rendered['html']
		);
	}

	/**
	 * Round-1 regression: two headings with the same text must be assigned
	 * distinct anchor ids. The uniqueSlug closure suffixes -2, -3, … on
	 * collision; without it the TOC links to the first occurrence only.
	 */
	public function testRepeatedHeadingsGetUniqueSlugs()
	{
		$raw = "H1> Configuration\nP> first section\n\nH1> Configuration\nP> second section";
		$rendered = $this->invoke('renderDoc', array($raw, 0));

		// Both ids must be present in the rendered HTML.
		$this->assertMatchesRegularExpression(
			'#id="doc0-h1-configuration"#',
			$rendered['html'],
			'first occurrence keeps the base slug'
		);
		$this->assertMatchesRegularExpression(
			'#id="doc0-h1-configuration-2"#',
			$rendered['html'],
			'second occurrence is suffixed with -2'
		);
		// And the TOC must list both, not be deduped to one.
		$this->assertCount(2, $rendered['toc']);
	}

	/**
	 * Round-1 regression: buildToc() must produce well-formed HTML when the
	 * heading levels mix H1 and H2. A nested <ul> may only appear inside a
	 * still-open <li>, never as a direct sibling of <li>.
	 */
	public function testBuildTocProducesValidNestedHtml()
	{
		// [level, id, text] triples. Levels match what renderDoc() emits:
		// H1 => 2, H2 => 3.
		$toc = array(
			array(2, 'doc0-h1-intro',   'Intro'),
			array(3, 'doc0-h2-details', 'Details'),
			array(2, 'doc0-h1-summary', 'Summary'),
		);
		$html = $this->invoke('buildToc', array($toc));

		// No <ul> may appear directly after a closing </li> at the inner
		// level — the nested <ul> belongs INSIDE its parent <li>.
		$this->assertDoesNotMatchRegularExpression(
			'#</li>\s*<ul>#',
			$html,
			'<ul> must never be a direct sibling of <li>'
		);
		// Each opening <ul> must be balanced by a closing </ul>.
		$this->assertSame(
			substr_count($html, '<ul>'),
			substr_count($html, '</ul>'),
			'<ul>/</ul> tags must balance'
		);
		// Each opening <li> must be balanced by a closing </li>.
		$this->assertSame(
			substr_count($html, '<li>'),
			substr_count($html, '</li>'),
			'<li>/</li> tags must balance'
		);
		// And the three anchors must all be present.
		$this->assertStringContainsString('href="#doc0-h1-intro"',   $html);
		$this->assertStringContainsString('href="#doc0-h2-details"', $html);
		$this->assertStringContainsString('href="#doc0-h1-summary"', $html);
	}
}
