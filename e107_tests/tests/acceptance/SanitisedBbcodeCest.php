<?php

/**
 * A [sanitised] block shows a visitor its placeholder, and the page around it
 * still renders.
 */
class SanitisedBbcodeCest
{
	const AFTER = 'Text after the sanitised block';

	public function aVisitorSeesThePlaceholderAndTheRestOfThePage(AcceptanceTester $I)
	{
		$I->wantTo('confirm a guest sees the placeholder of a [sanitised] block and the text after it');

		$id = $I->haveInDatabase('e107_page', array(
			'page_title'     => 'Sanitised placeholder',
			'page_sef'       => 'sanitised-placeholder',
			'page_text'      => '[sanitised]x[/sanitised] '.self::AFTER,
			'page_author'    => 1,
			'page_datestamp' => time() - 3600,
			'page_class'     => 0,
			'page_password'  => '',
			'page_template'  => 'default',
			'page_order'     => 0,
		));

		$I->amOnPage('/page.php?id='.$id);

		$I->seeResponseCodeIs(200);
		$I->see('##Sanitised##');
		$I->see(self::AFTER);
	}
}
