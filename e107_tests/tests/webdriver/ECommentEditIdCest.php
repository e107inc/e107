<?php

/**
 * What core's .e-comment-edit control builds from the id of the element clicked, in a browser that runs the handler.
 */
class ECommentEditIdCest
{
	/** The id part the handler takes, as the browser reads it: a selector list whose last entry holds markup. */
	const MARKUP_ID_PART = "x,p,b:contains('><img/src/ONERROR=window.top.__ccedit=1>')";

	public function anIdHoldingMarkupBuildsNoMarkup(\WebDriverTester $I)
	{
		$I->amOnPage('/page.php?id='.$I->haveInDatabase('e107_page', array(
			'page_title'     => 'Comment edit id page',
			'page_text'      => '<p>A page.</p><span id="c-o-m-'.htmlspecialchars(self::MARKUP_ID_PART, ENT_NOQUOTES).'" class="e-comment-edit">edit</span>',
			'page_class'     => '0',
			'page_author'    => 1,
			'page_datestamp' => time(),
		)));
		$I->click('.e-comment-edit');

		$I->waitForElement('div.e-comment-edit-save', 10);
		$I->assertSame(array('INPUT'), $I->executeJS('return Array.prototype.map.call(document.querySelectorAll("div.e-comment-edit-save > *"),'
			.' function (element) { return element.tagName; }).filter(function (tag, i, tags) { return tags.indexOf(tag) === i; });'),
			'The Save control holds markup built from the id.');
		$I->assertSame('e-comment-edit-save-'.self::MARKUP_ID_PART,
			$I->executeJS('return document.querySelector("div.e-comment-edit-save > input").id;'));
	}

	public function aCommentStillOpensForEditing(\WebDriverTester $I)
	{
		$I->amOnPage('/news.php');
		$I->executeJS('document.body.insertAdjacentHTML("afterbegin", '.json_encode(
			'<div id="comment-7-edit">A comment.</div>'
			.'<a id="e-comment-edit-7" class="e-comment-edit" data-target="/comment.php" data-token="t0k3n" href="#" style="position:fixed;top:0;left:0;z-index:99999">edit</a>'
		).');');
		$I->click('#e-comment-edit-7');

		$I->waitForElement('#e-comment-edit-save-7', 10);
		$I->assertSame('true', $I->executeJS('return document.getElementById("comment-7-edit").getAttribute("contenteditable");'));
		$I->assertSame(array('/comment.php', 't0k3n'), $I->executeJS(
			'var save = document.getElementById("e-comment-edit-save-7"); return [save.getAttribute("data-target"), save.getAttribute("data-token")];'));
	}
}
