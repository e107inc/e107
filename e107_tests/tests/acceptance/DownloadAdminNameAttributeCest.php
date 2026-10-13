<?php

/**
 * The download admin's edit form keeps a stored name inside its value attribute.
 */
class DownloadAdminNameAttributeCest
{
	public function _before(AcceptanceTester $I)
	{
		$I->havePluginInstalled('download');
		$I->loginAsAdmin();
	}

	public function aNameHoldingAQuoteStaysInItsField(AcceptanceTester $I)
	{
		$typed = 'Dl '.uniqid().' a &amp; b x" autofocus onfocus="window.__dl=1" data-dl="1';

		$this->openTheEditPage($I, str_replace('"', '&quot;', $typed));

		$I->dontSeeElement('#download-name[data-dl]');
		$I->seeInField('#download-name', html_entity_decode($typed, ENT_QUOTES, 'UTF-8'));
	}

	public function aNameHoldingAPercentEscapedQuoteStaysInItsField(AcceptanceTester $I)
	{
		$typed = 'Dl '.uniqid().' x%22 autofocus onfocus=%22window.__dl=2%22 data-dl=%221';

		$this->openTheEditPage($I, $typed);

		$I->dontSeeElement('#download-name[data-dl]');
		$I->seeInField('#download-name', urldecode($typed));
	}

	/**
	 * Store a download under $stored and open its edit page as the main administrator.
	 *
	 * @param AcceptanceTester $I
	 * @param string $stored the name as toDB() stores it
	 */
	private function openTheEditPage(AcceptanceTester $I, $stored)
	{
		$category = $I->haveInDatabase('e107_download_category', array(
			'download_category_name' => 'Name attribute category', 'download_category_description' => 'fixture',
			'download_category_icon' => '', 'download_category_parent' => 0, 'download_category_class' => 0,
			'download_category_order' => 1, 'download_category_sef' => 'name-attribute-category-'.uniqid(),
		));
		$download = $I->haveInDatabase('e107_download', array(
			'download_name' => $stored, 'download_url' => 'fixture.txt',
			'download_sef' => 'name-attribute-'.uniqid(), 'download_author' => 'fixture',
			'download_author_email' => 'fixture@example.com', 'download_author_website' => '',
			'download_description' => 'fixture', 'download_keywords' => '', 'download_filesize' => 12,
			'download_requested' => 0, 'download_category' => $category, 'download_active' => 1,
			'download_datestamp' => time() - 3600, 'download_thumb' => '', 'download_image' => '',
			'download_comment' => 0, 'download_class' => 0, 'download_mirror' => '', 'download_mirror_type' => 0,
			'download_visible' => 0,
		));

		$I->amOnPage('/e107_plugins/download/admin_download.php?mode=main&action=edit&id='.$download);
	}
}
