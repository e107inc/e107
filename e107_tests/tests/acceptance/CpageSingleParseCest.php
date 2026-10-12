<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * A custom page's body is parsed for shortcodes once, as text is everywhere else.
 */
class CpageSingleParseCest
{
	public function aShortcodeTheBodyLeftAsTextStaysText(AcceptanceTester $I)
	{
		$id = $I->haveInDatabase('e107_page', array(
			'page_title'     => 'Single parse',
			'page_sef'       => '',
			'page_chapter'   => 0,
			'page_text'      => 'Before [scode]{SITENAME}[/scode] after',
			'page_author'    => 1,
			'page_datestamp' => time(),
			'page_class'     => 0,
			'page_password'  => '',
			'page_template'  => 'default',
			'page_order'     => 0,
		));

		$I->amOnPage('/page.php?id='.$id);

		$I->see('[scode]{SITENAME}[/scode]');
	}
}
