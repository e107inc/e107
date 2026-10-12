<?php

/**
 * A quote that a page's author writes inside an attribute value stays inside that attribute when the page prints.
 */
class PageBodyAttributeQuoteCest
{
	/** An administrator who holds the Pages permission and nothing else. */
	const PAGES_ADMIN = 'n1q7cpages';

	const PAGES_ADMIN_PASS = 'N1q7cPagesPass';

	public function _before(AcceptanceTester $I)
	{
		$I->haveMember(self::PAGES_ADMIN, self::PAGES_ADMIN_PASS, array('user_admin' => 1, 'user_perms' => '5.'));
	}

	public function aQuoteInsideAnAttributeValueStaysThere(AcceptanceTester $I)
	{
		$page = $I->haveInDatabase('e107_page', array(
			'page_title'     => 'Attribute quotes n1q7c',
			'page_sef'       => 'attribute-quotes-n1q7c',
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

		$I->amSignedInAs(self::PAGES_ADMIN);
		$I->amOnPage('/e107_admin/cpage.php?mode=page&action=edit&id='.$page);
		$I->submitForm('#core-page-form', array(
			'page_text' => '<p><b id="n1q7cb" title="q&quot; onclick=&#039;n1q7c()&#039; x=&#039;">Both</b> '
				.'<b id="n1q7ce" title="&amp;quot; onclick=&amp;quot;n1q7c()&amp;quot; x=&amp;quot;">Encoded</b> '
				.'<b id="n1q7cp" title="it\'s">Plain</b></p>',
		), 'etrigger_submit');

		$I->amOnPage('/page.php?id='.$page);
		$I->dontSeeElement('b[onclick]');
		$I->seeElement('#n1q7cb');
		$I->seeElement('#n1q7ce');
		$I->seeElement('#n1q7cp', array('title' => "it's"));
	}
}
