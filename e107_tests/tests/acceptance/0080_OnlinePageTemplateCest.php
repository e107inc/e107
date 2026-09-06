<?php

/**
 * online.php set $ONLINE_TABLE, $ONLINE_TABLE_START, $ONLINE_TABLE_END and
 * $ONLINE_TABLE_MISC to '' before requiring the template, and
 * e107_core/templates/online_template.php guards each of its four defaults with
 * !isset(). isset('') is true, so the template assigned nothing, every block
 * reaching {@see e_parse::parseTemplate()} was empty and the page rendered its
 * frame around an empty content area. No core theme ships its own
 * online_template.php, so the core template is always the one loaded and every
 * request was affected.
 *
 * Each test names the block it proves came back:
 *   ONLINE_TABLE_MISC  the most-ever-online line, rendered for everyone
 *   ONLINE_TABLE_START the table header, rendered only when a member is on
 *   ONLINE_TABLE       the member row, same condition
 *
 * ONLINE_TABLE_END has no assertion of its own. It is the only block that
 * closes the table START opens, but a bare </table> would be satisfied by any
 * table the surrounding theme emits, so it rests on the red proof: the two
 * lines these tests red against blanked all four together.
 *
 * Online tracking is on by default (track_online in
 * e107_core/xml/default_install.xml), so neither test touches the preference.
 */
class OnlinePageTemplateCest
{
	const PAGE = '/online.php';

	/**
	 * A guest is never in $listuserson, so the counts block is the whole page.
	 */
	public function theCountsRenderForAGuest(AcceptanceTester $I)
	{
		$I->wantTo('read the online counts on online.php as a guest');

		$I->resetAllCookies();
		$I->amOnPage(self::PAGE);

		$I->seeInSource('most ever online:');
	}

	/**
	 * {@see e_online::goOnline()} records the requesting member before it
	 * selects the list, so the admin is in the table on this request itself.
	 */
	public function theMemberTableRendersForAMember(AcceptanceTester $I)
	{
		$I->wantTo('read the member table on online.php as a logged-in member');

		$I->loginAsAdmin();
		$I->amOnPage(self::PAGE);

		$I->seeInSource('Member Name');
		$I->seeInSource('Viewing Page');
		$I->seeInSource('forumheader3');
	}
}
