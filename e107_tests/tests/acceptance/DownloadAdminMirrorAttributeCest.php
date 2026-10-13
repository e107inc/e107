<?php

/**
 * A mirror's stored URL stays text on the download admin's edit form and on the front end's mirror page.
 */
class DownloadAdminMirrorAttributeCest
{
	public function _before(AcceptanceTester $I)
	{
		$I->havePluginInstalled('download');
		$I->loginAsAdmin();
	}

	public function aMirrorUrlHoldingMarkupStaysInItsField(AcceptanceTester $I)
	{
		$url = 'https://example.com/x"><img src=x data-mr=1>';
		$mirror = $I->haveInDatabase('e107_download_mirror', array(
			'mirror_name' => 'Mirror attribute '.uniqid(), 'mirror_url' => 'https://example.com/', 'mirror_image' => '',
			'mirror_location' => 'fixture', 'mirror_description' => 'fixture', 'mirror_count' => 0,
		));
		$category = $I->haveInDatabase('e107_download_category', array(
			'download_category_name' => 'Mirror attribute category', 'download_category_description' => 'fixture',
			'download_category_icon' => '', 'download_category_parent' => 0, 'download_category_class' => 0,
			'download_category_order' => 1, 'download_category_sef' => 'mirror-attribute-category-'.uniqid(),
		));
		$download = $I->haveInDatabase('e107_download', array(
			'download_name' => 'Mirror attribute '.uniqid(), 'download_url' => 'fixture.txt',
			'download_sef' => 'mirror-attribute-'.uniqid(), 'download_author' => 'fixture',
			'download_author_email' => 'fixture@example.com', 'download_author_website' => '',
			'download_description' => 'fixture', 'download_keywords' => '', 'download_filesize' => 12,
			'download_requested' => 0, 'download_category' => $category, 'download_active' => 1,
			'download_datestamp' => time() - 3600, 'download_thumb' => '', 'download_image' => '',
			'download_comment' => 0, 'download_class' => 0, 'download_mirror' => $mirror.','.$url.',0,1'.chr(1),
			'download_mirror_type' => 0, 'download_visible' => 0,
		));

		$I->amOnPage('/e107_plugins/download/admin_download.php?mode=main&action=edit&id='.$download);

		$I->dontSeeElement('img[data-mr]');
		$I->seeInField('download_mirror[]', $url);
	}

	public function aMirrorUrlHoldingACommaShowsNoMarkupOnTheMirrorPage(AcceptanceTester $I)
	{
		$mirror = $I->haveInDatabase('e107_download_mirror', array(
			'mirror_name' => 'Mirror comma '.uniqid(), 'mirror_url' => 'https://example.com/', 'mirror_image' => '',
			'mirror_location' => 'fixture', 'mirror_description' => 'fixture', 'mirror_count' => 0,
		));
		$category = $I->haveInDatabase('e107_download_category', array(
			'download_category_name' => 'Mirror comma category', 'download_category_description' => 'fixture',
			'download_category_icon' => '', 'download_category_parent' => 0, 'download_category_class' => 0,
			'download_category_order' => 1, 'download_category_sef' => 'mirror-comma-category-'.uniqid(),
		));
		$download = $I->haveInDatabase('e107_download', array(
			'download_name' => 'Mirror comma '.uniqid(), 'download_url' => 'fixture.txt',
			'download_sef' => 'mirror-comma-'.uniqid(), 'download_author' => 'fixture',
			'download_author_email' => 'fixture@example.com', 'download_author_website' => '',
			'download_description' => 'fixture', 'download_keywords' => '', 'download_filesize' => 12,
			'download_requested' => 0, 'download_category' => $category, 'download_active' => 1,
			'download_datestamp' => time() - 3600, 'download_thumb' => '', 'download_image' => '',
			'download_comment' => 0, 'download_class' => 0, 'download_mirror' => $mirror.',https://example.com/x,<img src=x data-mr2=1>,1'.chr(1),
			'download_mirror_type' => 0, 'download_visible' => 0,
		));

		$I->amOnPage('/e107_plugins/download/download.php?mirror.'.$download);

		$I->see('Mirror comma', 'body');
		$I->dontSeeElement('img[data-mr2]');
	}
}
