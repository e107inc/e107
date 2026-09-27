<?php

/**
 * Issue #6391: a book or chapter that does not resolve for the visitor is a
 * not-found page carrying a 404, rather than a listing built out of a row that
 * was never fetched, and a chapter whose pages the visitor cannot see says so
 * rather than rendering an empty box.
 */
class CpageNotFoundCest
{
	const HIDDEN_NAME = 'Sweep hidden chapter 6391';
	const HIDDEN_DIZ  = 'HIDDENDIZ6391';
	const EMPTY_NAME  = 'Sweep empty chapter 6391';

	/** No visitor holds this class, so a chapter visible only to it resolves for nobody who is not logged in. */
	const ADMIN_CLASS = 254;

	/** An id no seeded row can hold, which is the deleted book or chapter a bookmark still points at. */
	const GONE = 999999;

	/** @var int */
	private $hiddenId;

	/** @var int */
	private $emptyId;

	public function _before(AcceptanceTester $I)
	{
		$this->hiddenId = $this->seedChapter($I, array(
			'chapter_name'             => self::HIDDEN_NAME,
			'chapter_sef'              => 'sweep-hidden-6391',
			'chapter_meta_description' => self::HIDDEN_DIZ,
			'chapter_visibility'       => self::ADMIN_CLASS,
		));

		$this->emptyId = $this->seedChapter($I, array(
			'chapter_name' => self::EMPTY_NAME,
			'chapter_sef'  => 'sweep-empty-6391',
		));
	}

	public function aBookThatIsNotThereIsNotFound(AcceptanceTester $I)
	{
		$I->wantTo('confirm a book id that resolves to nothing answers 404 and says so');

		$I->amOnPage('/page.php?bk='.self::GONE);

		$this->seeNotFound($I);
	}

	public function aChapterThatIsNotThereIsNotFound(AcceptanceTester $I)
	{
		$I->wantTo('confirm a chapter id that resolves to nothing answers 404 and says so');

		$I->amOnPage('/page.php?ch='.self::GONE);

		$this->seeNotFound($I);
	}

	public function aChapterTheVisitorMayNotSeeGivesUpNothing(AcceptanceTester $I)
	{
		$I->wantTo('confirm a chapter restricted to a class the visitor is not in is not found either');

		$I->amOnPage('/page.php?ch='.$this->hiddenId);

		$this->seeNotFound($I);
		$I->dontSee(self::HIDDEN_NAME);
		$I->assertStringNotContainsString(self::HIDDEN_DIZ, $I->grabPageSource(),
			'the description of a chapter the visitor may not see does not reach the page');
	}

	public function aBookTheVisitorMayNotSeeGivesUpNothing(AcceptanceTester $I)
	{
		$I->wantTo('confirm a book restricted to a class the visitor is not in is not found either');

		$I->amOnPage('/page.php?bk='.$this->hiddenId);

		$this->seeNotFound($I);
		$I->dontSee(self::HIDDEN_NAME);
		$I->assertStringNotContainsString(self::HIDDEN_DIZ, $I->grabPageSource(),
			'the description of a book the visitor may not see does not reach the page');
	}

	public function aPageThatIsNotThereAnswersLikeTheListings(AcceptanceTester $I)
	{
		$I->wantTo('confirm the page view a bookmark still points at answers the way the two listings do');

		$I->amOnPage('/page.php?id='.self::GONE);

		$this->seeNotFound($I);
	}

	public function aChapterWithNoPagesSaysSo(AcceptanceTester $I)
	{
		$I->wantTo('confirm a chapter the visitor may see, holding no pages, renders its own text');

		$I->amOnPage('/page.php?ch='.$this->emptyId);

		$I->seeResponseCodeIs(200);
		$I->see('There are no pages');
	}

	/**
	 * The whole of what a book or chapter that does not resolve answers: the status, the text, a titled head and no canonical naming another page.
	 *
	 * @param AcceptanceTester $I
	 */
	private function seeNotFound(AcceptanceTester $I)
	{
		$I->seeResponseCodeIs(404);
		$I->see('Requested page does not exist');
		$I->seeInTitle('Invalid page');
		$I->assertStringNotContainsString('rel="canonical"', $I->grabPageSource(),
			'a not-found response names no canonical, so it neither points at a live page nor says which ids exist');
	}

	/**
	 * @param AcceptanceTester $I
	 * @param array $row
	 * @return int chapter id
	 */
	private function seedChapter(AcceptanceTester $I, $row)
	{
		return $I->haveInDatabase('e107_page_chapters', array_merge(array(
			'chapter_parent'           => 0,
			'chapter_manager'          => 254,
			'chapter_icon'             => '',
			'chapter_image'            => '',
			'chapter_meta_description' => '',
			'chapter_meta_keywords'    => '',
			'chapter_template'         => 'default',
			'chapter_order'            => 0,
			'chapter_visibility'       => 0,
		), $row));
	}
}
