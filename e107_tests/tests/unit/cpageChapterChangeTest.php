<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

use Symfony\Component\CssSelector\Exception\ExceptionInterface;
use Symfony\Component\DomCrawler\Crawler;

/**
 * The Additional tab of e107_admin/cpage.php's create form, which a chapter with custom fields fills and which stays hidden while no chapter is chosen.
 */
class cpageChapterChangeTest extends \Codeception\Test\Unit
{
	use \Test\BootedCli;
	use \Test\ScriptClassPages;
	use \Test\PageChapters;

	const TITLE = 'cpageChapterChangeTest';

	const SCRIPT = 'e107_admin/cpage.php';

	const FIELD = 'difficulty';

	/** the label {@see e_customfields} gives its tab while no chapter names one */
	const LABEL = 'Additional';

	protected function _after()
	{
		$this->dropChapters();
	}

	public function testChoosingAChapterWithFieldsFillsTheAdditionalTabAndShowsIt()
	{
		$chapter = $this->haveChapterWithField(self::TITLE, self::FIELD, 'text');
		$form = $this->createForm();
		$tab = $this->additionalTab($form);

		$dropdown = $form->filter("select[name='page_chapter']");
		self::assertSame(1, $dropdown->count(), 'the create form has no Book/Chapter dropdown');

		$target = (string) $dropdown->attr('data-target');
		self::assertGreaterThan(0, $form->filter($target)->count(), 'the dropdown aims its request at nothing on the form: '.$target);

		$filled = array();
		$shown = array();

		foreach($this->chapterChange($chapter) as $command)
		{
			$targets = $form->filter($command['target']);
			self::assertGreaterThan(0, $targets->count(), 'the response aims '.$command['method'].' at nothing on the form: '.$command['target']);

			if($targets->getNode(0)->isSameNode($tab))
			{
				$shown[] = $command['method'];
			}

			if($command['method'] === 'html' && strpos(implode('', $command['arguments']), 'page_fields__'.self::FIELD) !== false)
			{
				$filled[] = $targets;
			}
		}

		self::assertContains('fadeInAdminTab', $shown, 'the response has to show the '.self::LABEL.' tab');
		self::assertNotContains('fadeOut', $shown, 'the response must not hide the '.self::LABEL.' tab');
		self::assertCount(1, $filled, "no command puts the chapter's field into the form");
		self::assertSame('tabpanel', $filled[0]->attr('role'), 'the field has to land in the tab pane');
	}

	public function testTheAdditionalTabIsHiddenWhileNoChapterIsChosen()
	{
		$form = $this->createForm();
		$tab = $this->additionalTab($form);
		$hidden = array();

		foreach($form->filter('style') as $style)
		{
			preg_match_all('/([^{}]+)\{\s*display:\s*none;?\s*\}/', $style->textContent, $rules);

			foreach(array_map('trim', $rules[1]) as $selector)
			{
				$hidden[] = $selector;

				foreach($this->selected($form, $selector) as $element)
				{
					if($element->isSameNode($tab))
					{
						return;
					}
				}
			}
		}

		self::fail('no rule hides the '.self::LABEL." tab; the rules hiding something are:\n".implode("\n", $hidden));
	}

	/**
	 * @return Crawler the create form with no chapter chosen
	 */
	private function createForm()
	{
		return new Crawler($this->openAsScriptClass(e_UC_NOBODY, self::SCRIPT, 'mode=page&action=create'));
	}

	/**
	 * @param Crawler $form
	 * @return DOMElement the link that opens the Additional tab
	 */
	private function additionalTab(Crawler $form)
	{
		$tabs = $form->filter('.nav-tabs a')->reduce(function (Crawler $link)
		{
			return trim($link->text()) === self::LABEL;
		});

		self::assertSame(1, $tabs->count(), 'the create form has to show one '.self::LABEL.' tab');

		return $tabs->getNode(0);
	}

	/**
	 * @param int $chapter
	 * @return array[] the commands the request the dropdown sends answers with, each with its target, method and arguments
	 */
	private function chapterChange($chapter)
	{
		$response = $this->openAsScriptClass(e_UC_NOBODY, self::SCRIPT, 'mode=page&action=chapter-change', array('page_chapter' => $chapter), '', true);
		self::assertSame(1, preg_match('/\[\{"command".*$/m', $response, $line), "the chapter change sent no commands:\n".$response);

		$commands = json_decode($line[0], true);
		self::assertNotEmpty($commands, 'the commands are not JSON: '.$line[0]);

		return $commands;
	}

	/**
	 * @param Crawler $form
	 * @param string $selector
	 * @return Crawler|array what the selector selects; nothing for a selector the CSS reader cannot read
	 */
	private function selected(Crawler $form, $selector)
	{
		try
		{
			return $form->filter($selector);
		}
		catch(ExceptionInterface $unreadable)
		{
			return array();
		}
	}
}
