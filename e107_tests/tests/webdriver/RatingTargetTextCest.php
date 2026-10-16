<?php

/**
 * A rating widget writes its hints and its result into its own status line, next to it, as text, whatever a page's author put elsewhere under the same id.
 */
class RatingTargetTextCest
{
	const TYPED = '<details open ontoggle=window.__rt=1>t</details>';

	/** @var int */
	private $page;

	public function _before(WebDriverTester $I)
	{
		$this->page = $I->haveInDatabase('e107_page', array(
			'page_title' => 'Rating target', 'page_sef' => 'rating-target-'.uniqid(),
			'page_chapter' => 0, 'page_metakeys' => '', 'page_metadscr' => '', 'page_metarobots' => '',
			'page_text' => '', 'page_author' => 1, 'page_datestamp' => time() - 3600,
			'page_rating_flag' => 1, 'page_comment_flag' => 0,
			'page_password' => '', 'page_class' => '0', 'page_ip_restrict' => '',
			'page_template' => '', 'page_order' => 9999, 'page_fields' => '',
			'menu_name' => '', 'menu_title' => '', 'menu_text' => '', 'menu_image' => '',
			'menu_icon' => '', 'menu_template' => '', 'menu_class' => '0',
			'menu_button_url' => '', 'menu_button_text' => '',
		));
		$I->updateInDatabase('e107_page', array('page_text' => '<p>Rate <span id=&quot;'.$this->status().'&quot;>'.htmlspecialchars(self::TYPED, ENT_NOQUOTES).'</span> end</p>'), array('page_id' => $this->page));
	}

	public function aTextUnderTheStatusIdStaysText(WebDriverTester $I)
	{
		$I->amSignedInAs();
		$I->amOnPage('/page.php?id='.$this->page);
		$I->waitForElement('#page-'.$this->page.'-rate img', 10);

		$star = "document.querySelector('#page-".$this->page."-rate img').getAttribute('src')";
		$I->moveMouseOver('#page-'.$this->page.'-rate img');
		$I->waitForJS('return '.$star.".indexOf('star-off') === -1;", 10);
		$I->moveMouseOver('#'.$this->status());
		$I->waitForJS('return '.$star.".indexOf('star-off') !== -1;", 10);

		$I->assertNull($I->executeJS('return window.__rt === undefined ? null : window.__rt;'));
		$I->assertSame(self::TYPED, $I->executeJS("return ".$this->statuses()."[0].textContent;"));
		$I->assertSame(0, $I->executeJS("return ".$this->statuses()."[0].children.length;"));
	}

	public function aBareRateClassLeavesThePageRatingWorking(WebDriverTester $I)
	{
		$I->updateInDatabase('e107_page', array('page_text' => '<p>Bare <span class=&quot;e-rate&quot;>stars</span> end</p>'), array('page_id' => $this->page));
		$I->amSignedInAs();
		$I->amOnPage('/page.php?id='.$this->page);

		$I->waitForElement('#page-'.$this->page.'-rate img', 10);
	}

	/**
	 * @return string JavaScript for every element under the status line's id, in document order
	 */
	private function statuses()
	{
		return "document.querySelectorAll('[id=\"".$this->status()."\"]')";
	}

	/**
	 * @return string the id of the page's rating status line
	 */
	private function status()
	{
		return 'e-rate-page-'.$this->page.'-rate';
	}
}
