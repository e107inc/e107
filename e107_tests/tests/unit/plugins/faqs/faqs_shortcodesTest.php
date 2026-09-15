<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * @group plugins
 *
 * Covers the parameter shapes {@see faqs_shortcodes::sc_faq_question()} is
 * handed, for issue #6376. No template on this branch writes the colon form,
 * which is why nothing warns here yet, and why the read is fixed before one
 * does.
 */
class faqs_shortcodesTest extends \Codeception\Test\Unit
{
	/** @var faqs_shortcodes */
	protected $sc;

	/** @var string */
	protected $faqQuestion = 'Which "theme" do you prefer & why?';

	protected function _before()
	{
		require_once(e_PLUGIN . 'faqs/faqs_shortcodes.php');

		$this->sc = new faqs_shortcodes();
		$this->sc->setVars(array(
			'faq_id'        => 42,
			'faq_question'  => $this->faqQuestion,
			'faq_answer'    => 'Whichever one you can still edit in five years.',
			'faq_tags'      => '',
			'faq_datestamp' => 0,
		));
	}

	public function testAQuestionWithNoParametersIsParsedAsATitle()
	{
		$this->assertSame(
			e107::getParser()->toHTML($this->faqQuestion, true, 'TITLE'),
			$this->sc->sc_faq_question()
		);
	}

	/**
	 * The colon form arrives already parsed, so the pipe form's two sets are
	 * absent rather than empty and reading them warns on every render. This
	 * branch has no parameter that changes what comes back, so the question
	 * renders as it does with none at all.
	 */
	public function testTheColonFormAsATemplateWouldWriteIt()
	{
		$this->assertSame(
			e107::getParser()->toHTML($this->faqQuestion, true, 'TITLE'),
			$this->sc->sc_faq_question(array('html' => 0))
		);
	}

	public function testThePipeFormStillReachesTheSecondSet()
	{
		$this->assertSame(
			e107::getParser()->toHTML($this->faqQuestion, true, 'TITLE'),
			$this->sc->sc_faq_question('|tags=1')
		);
	}
}
