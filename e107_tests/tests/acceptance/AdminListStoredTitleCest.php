<?php

/**
 * An admin list prints a title an HTML author saved as that HTML, with each attribute value quoted.
 */
class AdminListStoredTitleCest
{
	public function aStoredTitleKeepsItsAttributeValueInTheList(AcceptanceTester $I)
	{
		$I->haveInDatabase('e107_page', array(
			'page_title'     => 'Listq7d <b id=&quot;listq7d&quot; title=&quot;x autofocus onfocus=listq7d() y&quot;>T</b>',
			'page_sef'       => 'list-q7d',
			'page_chapter'   => 0,
			'page_text'      => 'Body',
			'page_author'    => 1,
			'page_datestamp' => time(),
			'page_class'     => 0,
			'page_password'  => '',
			'page_template'  => 'default',
			'page_order'     => 0,
			'page_fields'    => '',
		));

		$I->loginAsAdmin();
		$I->amOnPage('/e107_admin/cpage.php?mode=page&action=list&field=page_id&asc=desc');
		$I->see('Listq7d');
		$I->dontSeeElement('b[onfocus]');
		$I->seeElement('b[title="x autofocus onfocus=listq7d() y"]');
	}

	public function aStoredChapterNameKeepsItsAttributeValueInTheLists(AcceptanceTester $I)
	{
		$book = $I->haveInDatabase('e107_page_chapters', $this->chapter(0, 'Bookq7d'));
		$chapter = $I->haveInDatabase('e107_page_chapters', $this->chapter($book, 'Chapq7d <i title=&quot;x autofocus onfocus=chapq7d() y&quot;>C</i>'));
		$I->haveInDatabase('e107_page', array(
			'page_title' => 'Chapter list q7d', 'page_sef' => 'chapter-list-q7d', 'page_chapter' => $chapter, 'page_text' => 'Body',
			'page_author' => 1, 'page_datestamp' => time(), 'page_class' => 0, 'page_password' => '', 'page_template' => 'default',
			'page_order' => 0, 'page_fields' => '',
		));

		$I->loginAsAdmin();
		$I->amOnPage('/e107_admin/cpage.php?mode=page&action=list&field=page_id&asc=desc');
		$I->see('Chapq7d');
		$I->dontSeeElement('i[onfocus]');
		$I->amOnPage('/e107_admin/cpage.php?mode=cat&action=list&field=chapter_id&asc=desc');
		$I->see('Chapq7d');
		$I->dontSeeElement('i[onfocus]');
	}

	/**
	 * @param int $parent
	 * @param string $name as toDB() stores it
	 * @return array an e107_page_chapters row
	 */
	private function chapter($parent, $name)
	{
		return array(
			'chapter_parent' => $parent, 'chapter_name' => $name, 'chapter_sef' => 'chapter-q7d-'.$parent, 'chapter_template' => 'default',
			'chapter_meta_description' => '', 'chapter_meta_keywords' => '', 'chapter_icon' => '', 'chapter_fields' => '',
			'chapter_manager' => 254, 'chapter_image' => '', 'chapter_order' => 0, 'chapter_visibility' => 0,
		);
	}
}
