<?php



class e_userpermsTest extends \Test\Unit
{

	/** @var e_userperms */
	protected $eup;

	protected function _before()
	{

		try
		{
			e107::loadAdminIcons();
			e107::includeLan(e_LANGUAGEDIR . 'English/English.php');
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
			    0 => 'Theme Manager',
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

	public function testAnEmptyCodePassesAnAdminHoldingAPermissionWhicheverPageSavedThem()
	{
		$this::assertTrue(e_userperms::simulateHasAdminPerms('', 'I.5.J.P14'), 'saved from Users, with no dot at the end');
		$this::assertTrue(e_userperms::simulateHasAdminPerms('', 'I.5.J.P14.'), 'saved from Administrators');
		$this::assertTrue(e_userperms::simulateHasAdminPerms('', '0'), 'a main admin');
		$this::assertFalse(e_userperms::simulateHasAdminPerms('', ''), 'no permissions at all');
		$this::assertFalse(e_userperms::simulateHasAdminPerms('', '.'), 'nothing but a dot');
	}

	public function testAnEmptyAlternativePassesAsAnEmptyCodeDoes()
	{
		$this::assertTrue(e_userperms::simulateHasAdminPerms('X|', 'I.5'));
		$this::assertTrue(e_userperms::simulateHasAdminPerms('X|', 'I.5.'));
		$this::assertTrue(e_userperms::simulateHasAdminPerms('|I', 'X'), 'a leading pipe leaves an empty alternative, which passes any admin');
		$this::assertFalse(e_userperms::simulateHasAdminPerms('X|Y', 'I.5.'));
	}

	public function testATrailingDotChangesNoAnswerForARealCode()
	{
		foreach(array('I.5.J.P14', 'I.5.J.P14.') as $stored)
		{
			$this::assertTrue(e_userperms::simulateHasAdminPerms('P14', $stored), $stored);
			$this::assertTrue(e_userperms::simulateHasAdminPerms('X|5', $stored), $stored);
			$this::assertFalse(e_userperms::simulateHasAdminPerms('P1', $stored), $stored);
			$this::assertFalse(e_userperms::simulateHasAdminPerms('0', $stored), $stored);
		}
	}

	public function testSerializePermsWritesTheFormAdministratorsSaves()
	{
		$tp = e107::getParser();

		$this::assertSame('I.5.J.P14.', e_userperms::serializePerms(array('I', '5', '', 'J', 'P14'), false, $tp));
		$this::assertSame('0', e_userperms::serializePerms(array('I', '0', '5'), true, $tp), 'a main admin is stored as 0 alone');
		$this::assertSame('I.5.', e_userperms::serializePerms(array('I', '0', '5'), false, $tp), 'a saver who is not a main admin cannot grant it');
		$this::assertSame('I.5.', e_userperms::serializePerms(array('I', "0 ", "0\f", '&#48;', "0\x01", " 5"), false, $tp), 'a 0 is judged in the form the row would hold it, so no padding or encoding carries it past the drop');
		$this::assertSame('H.J.', e_userperms::serializePerms(array('0.H', 'J.0', '0.'), false, $tp), 'each piece between dots is judged, as the check reads it, so a dot carries no 0 past the drop');
		$this::assertSame('H.', e_userperms::serializePerms(array('0[h]<x></x>[/h]', 'H'), false, $tp), 'a code a second encoding would change is dropped, since Users -> Edit encodes the stored string again');
		$this::assertSame('', e_userperms::serializePerms(array(), true, $tp));
	}

	public function testUpdatePermsDropsOnlyTheMainAdminCodeForASaverWhoIsNotOne()
	{
		$id = (int) e107::getDb()->createQueryBuilder()->insert('user')->insertGetId(array(
			'user_name'      => 'eupupdate'.uniqid(),
			'user_loginname' => 'eupupdate'.uniqid(),
			'user_email'     => uniqid().'@example.test',
			'user_join'      => time(),
			'user_admin'     => 1,
			'user_perms'     => 'I.',
		));

		try
		{
			list($constants, $saver) = $this->bootedAdminHolding('4.3.');
			$php = $saver."e107::coreLan('administrator', true); \$_POST['ad_name'] = 'eupupdate'; ";
			$php .= "e107::getUserPerms()->updatePerms(".$id.", array('I', '0', '0 ', '5')); echo '@@SAVED@@';";

			list($output, $status) = $this->runInBootedCliDefining($constants, $php);

			$this::assertSame(0, $status, implode("\n", $output));
			$this::assertStringContainsString('@@SAVED@@', implode("\n", $output));
			$this::assertSame('I.5.', (string) e107::getDb()->createQueryBuilder()->select('user_perms')->from('user')->where('user_id', $id)->fetchOne());
		}
		finally
		{
			e107::getDb()->createQueryBuilder()->delete('user')->where('user_id', $id)->execute();
		}
	}
}
