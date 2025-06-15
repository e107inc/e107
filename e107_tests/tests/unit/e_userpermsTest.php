<?php



class e_userpermsTest extends \Codeception\Test\Unit
{

	/** @var e_userperms */
	protected $eup;

	protected function _before()
	{

		try
		{
			e107::loadAdminIcons();
			e107::includeLan(e_LANGUAGEDIR . 'English/English.php');
			e107::includeLan(e_LANGUAGEDIR . 'English/admin/lan_admin.php');
			include_once(e_HANDLER . 'user_handler.php');
			$this->eup = $this->make('e_userperms');

		}
		catch(Exception $e)
		{
			$this::fail("Couldn't load e_userperms: {$e}");
		}


	}

	function testGetPermList()
	{
		$this::assertSame(LAN_EDIT,'Edit');
		$this::assertSame(LAN_CATEGORY,'Category');
		$this::assertSame(constant('ADLAN_0'), 'News');
		$this::assertSame(constant('LAN_MEDIAMANAGER'), 'Media Manager');

		$this->eup->__construct();


		$expected = array (
			  'C' =>
			  array (
			    0 => 'Cache',
			    1 => '<i class=\'S16 e-cache-16\'></i>',
			    2 => '<i class=\'S32 e-cache-32\'></i> ',
			  ),
			  'F' =>
			  array (
			    0 => 'Emoticons',
			    1 => '<i class=\'S16 e-emoticons-16\'></i>',
			    2 => '<i class=\'S32 e-emoticons-32\'></i> ',
			  ),
			  'G' =>
			  array (
			    0 => 'Front Page',
			    1 => '<i class=\'S16 e-frontpage-16\'></i>',
			    2 => '<i class=\'S32 e-frontpage-32\'></i> ',
			  ),
			  'L' =>
			  array (
			    0 => 'Language',
			    1 => '<i class=\'S16 e-language-16\'></i>',
			    2 => '<i class=\'S32 e-language-32\'></i> ',
			  ),
			  'T' =>
			  array (
			    0 => 'Meta Tags',
			    1 => '<i class=\'icon S16 e-meta-16\'></i>',
			    2 => '<i class=\'S32 e-meta-32\'></i> ',
			  ),
			  1 =>
			  array (
			    0 => 'Preferences',
			    1 => '<i class=\'S16 e-prefs-16\'></i>',
			    2 => '<i class=\'S32 e-prefs-32\'></i> ',
			  ),
			  'X' =>
			  array (
			    0 => 'Search',
			    1 => '<i class=\'S16 e-search-16\'></i>',
			    2 => '<i class=\'S32 e-search-32\'></i> ',
			  ),
			  'I' =>
			  array (
			    0 => 'Navigation',
			    1 => '<i class=\'S16 e-links-16\'></i>',
			    2 => '<i class=\'S32 e-links-32\'></i> ',
			  ),
			  8 =>
			  array (
			    0 => 'Oversee link categories',
			    1 => '<i class=\'S16 e-links-16\'></i>',
			    2 => '<i class=\'S32 e-links-32\'></i> ',
			  ),
			  'K' =>
			  array (
			    0 => 'URL Configuration',
			    1 => '<i class=\'S16 e-eurl-16\'></i>',
			    2 => '<i class=\'S32 e-eurl-32\'></i> ',
			  ),
			  3 =>
			  array (
			    0 => 'Administrators',
			    1 => '<i class=\'S16 e-admins-16\'></i>',
			    2 => '<i class=\'S32 e-admins-32\'></i>',
			  ),
			  4 =>
			  array (
			    0 => 'Manage all User, Userclass and Extended User-Field settings',
			    1 => '<i class=\'S16 e-users-16\'></i>',
			    2 => '<i class=\'S32 e-users-32\'></i> ',
			  ),
			  'U0' =>
			  array (
			    0 => 'Banlist',
			    1 => '<i class=\'S16 e-users-16\'></i>',
			    2 => '<i class=\'S32 e-users-32\'></i> ',
			  ),
			  'U1' =>
			  array (
			    0 => 'Quick Add User',
			    1 => '<i class=\'S16 e-users-16\'></i>',
			    2 => '<i class=\'S32 e-users-32\'></i> ',
			  ),
			  'U2' =>
			  array (
			    0 => 'User Options',
			    1 => '<i class=\'S16 e-users-16\'></i>',
			    2 => '<i class=\'S32 e-users-32\'></i> ',
			  ),
			  'U3' =>
			  array (
			    0 => 'User Ranks',
			    1 => '<i class=\'S16 e-users-16\'></i>',
			    2 => '<i class=\'S32 e-users-32\'></i> ',
			  ),
			  'W' =>
			  array (
			    0 => 'Mail',
			    1 => '<i class=\'S16 e-mail-16\'></i>',
			    2 => '<i class=\'S32 e-mail-32\'></i> ',
			  ),
			  5 =>
			  array (
			    0 => 'Pages/Menus',
			    1 => '<i class=\'S16 e-custom-16\'></i>',
			    2 => '<i class=\'S32 e-custom-32\'></i> ',
			  ),
			  'J' =>
			  array (
			    0 => 'Pages/Menus',
			    1 => '<i class=\'S16 e-custom-16\'></i>',
			    2 => '<i class=\'S32 e-custom-32\'></i> ',
			  ),
			  'J1' =>
			  array (
			    0 => 'Pages/Menus (Delete)',
			    1 => '<i class=\'S16 e-custom-16\'></i>',
			    2 => '<i class=\'S32 e-custom-32\'></i> ',
			  ),
			  'H' =>
			  array (
			    0 => 'News',
			    1 => '<i class=\'icon S16 e-news-16\'></i>',
			    2 => '<i class=\'S32 e-news-32\'></i> ',
			  ),
			  'H0' =>
			  array (
			    0 => 'News (Create)',
			    1 => '<i class=\'icon S16 e-news-16\'></i>',
			    2 => '<i class=\'S32 e-news-32\'></i> ',
			  ),
			  'H1' =>
			  array (
			    0 => 'News (Edit)',
			    1 => '<i class=\'icon S16 e-news-16\'></i>',
			    2 => '<i class=\'S32 e-news-32\'></i> ',
			  ),
			  'H2' =>
			  array (
			    0 => 'News (Delete)',
			    1 => '<i class=\'icon S16 e-news-16\'></i>',
			    2 => '<i class=\'S32 e-news-32\'></i> ',
			  ),
			  'H3' =>
			  array (
			    0 => 'News (Category - Create)',
			    1 => '<i class=\'icon S16 e-news-16\'></i>',
			    2 => '<i class=\'S32 e-news-32\'></i> ',
			  ),
			  'H4' =>
			  array (
			    0 => 'News (Category - Edit)',
			    1 => '<i class=\'icon S16 e-news-16\'></i>',
			    2 => '<i class=\'S32 e-news-32\'></i> ',
			  ),
			  'H5' =>
			  array (
			    0 => 'News (Category - Delete)',
			    1 => '<i class=\'icon S16 e-news-16\'></i>',
			    2 => '<i class=\'S32 e-news-32\'></i> ',
			  ),
			  'N' =>
			  array (
			    0 => 'News (Submitted)',
			    1 => '<i class=\'icon S16 e-news-16\'></i>',
			    2 => '<i class=\'S32 e-news-32\'></i> ',
			  ),
			  'V' =>
			  array (
			    0 => 'Manage/upload files',
			    1 => '<i class=\'S16 e-uploads-16\'></i>',
			    2 => '<i class=\'S32 e-uploads-32\'></i> ',
			  ),
			  'M' =>
			  array (
			    0 => 'Welcome Message',
			    1 => '<i class=\'S16 e-welcome-16\'></i>',
			    2 => '<i class=\'S32 e-welcome-32\'></i> ',
			  ),
			  'Y' =>
			  array (
			    0 => 'File Inspector',
			    1 => '<i class=\'S16 e-fileinspector-16\'></i>',
			    2 => '<i class=\'S32 e-fileinspector-32\'></i> ',
			  ),
			  7 =>
			  array (
			    0 => 'History',
			    1 => '<img class=\'icon S16\' src=\'./e107_images/admin_images/undo_16.png\' alt=\'\' />',
			    2 => '<img class=\'icon S32\' src=\'./e107_images/admin_images/undo_32.png\' alt=\'\' />',
			  ),
			  9 =>
			  array (
			    0 => 'Maintenance',
			    1 => '<i class=\'S16 e-maintain-16\'></i>',
			    2 => '<i class=\'S32 e-maintain-32\'></i> ',
			  ),
			  'O' =>
			  array (
			    0 => 'Notify',
			    1 => '<i class=\'S16 e-notify-16\'></i>',
			    2 => '<i class=\'S32 e-notify-32\'></i> ',
			  ),
			  'U' =>
			  array (
			    0 => 'Schedule Tasks',
			    1 => '<i class=\'S16 e-cron-16\'></i>',
			    2 => '<i class=\'S32 e-cron-32\'></i> ',
			  ),
			  'S' =>
			  array (
			    0 => 'System Logs',
			    1 => '<i class=\'S16 e-adminlogs-16\'></i>',
			    2 => '<i class=\'S32 e-adminlogs-32\'></i> ',
			  ),
			  'B' =>
			  array (
			    0 => 'Comments Manager',
			    1 => '<i class=\'S16 e-comments-16\'></i>',
			    2 => '<i class=\'S32 e-comments-32\'></i> ',
			  ),
		/*	  6 =>
			  array (
			    0 => 'Media Manager',
			    1 => '<i class=\'S16 e-filemanager-16\'></i>',
			    2 => '<i class=\'S32 e-filemanager-32\'></i> ',
			  ),*/
			  'A' =>
			  array (
			    0 => 'Media Manager (All)',
			    1 => '<i class=\'S16 e-images-16\'></i>',
			    2 => '<i class=\'S32 e-images-32\'></i> ',
			  ),
			  'A1' =>
			  array (
			    0 => 'Media Manager (Upload/Import)',
			    1 => '<i class=\'S16 e-images-16\'></i>',
			    2 => '<i class=\'S32 e-images-32\'></i> ',
			  ),
			  'A2' =>
			  array (
			    0 => 'Media Manager (Categories)',
			    1 => '<i class=\'S16 e-images-16\'></i>',
			    2 => '<i class=\'S32 e-images-32\'></i> ',
			  ),
			  'TMP' =>
			  array (
			    0 => 'Theme Manager (Preferences)',
			    1 => '<i class=\'S16 e-themes-16\'></i>',
			    2 => '<i class=\'S32 e-themes-32\'></i> ',
			  ),
			  2 =>
			  array (
			    0 => 'Menu Manager',
			    1 => '<i class=\'icon S16 e-menus-16\'></i>',
			    2 => '<i class=\'S32 e-menus-32\'></i> ',
			  ),
			);



		$result = $this->eup->getPermList('core');
		$this::assertNotEmpty($result);
		$this::assertSame($expected,$result);

	}
}
