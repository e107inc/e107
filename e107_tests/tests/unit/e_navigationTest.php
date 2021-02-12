<?php


	class e_navigationTest extends \Codeception\Test\Unit
	{

		protected function _before()
		{
			e107::loadAdminIcons();
		}
/*
		public function testCompile()
		{

		}

		public function testCacheString()
		{

		}

		public function testRender()
		{

		}
*/

		public function testAdminLinksLegacy()
		{
			$expected = array (
			  0 =>
			  array (
			    0 => '/e107_admin/administrator.php',
			    1 => 'Administrators',
			    2 => 'Add/delete site administrators',
			    3 => '3',
			    4 => 2,
			    5 => '<i class=\'S16 e-admins-16\'></i>',
			    6 => '<i class=\'S32 e-admins-32\'></i>',
			  ),
			  1 =>
			  array (
			    0 => '/e107_admin/updateadmin.php',
			    1 => 'Admin password',
			    2 => 'Change your password',
			    3 => false,
			    4 => 2,
			    5 => '<i class=\'S16 e-adminpass-16\'></i>',
			    6 => '<i class=\'S32 e-adminpass-32\'></i>',
			  ),
			  2 =>
			  array (
			    0 => '/e107_admin/banlist.php',
			    1 => 'Banlist',
			    2 => 'Ban visitors',
			    3 => '4',
			    4 => 2,
			    5 => '<i class=\'S16 e-banlist-16\'></i>',
			    6 => '<i class=\'S32 e-banlist-32\'></i>',
			  ),
			  4 =>
			  array (
			    0 => '/e107_admin/cache.php',
			    1 => 'Cache',
			    2 => 'Set cache status',
			    3 => 'C',
			    4 => 1,
			    5 => '<i class=\'S16 e-cache-16\'></i>',
			    6 => '<i class=\'S32 e-cache-32\'></i> ',
			  ),
			  5 =>
			  array (
			    0 => '/e107_admin/cpage.php',
			    1 => 'Pages/Menus',
			    2 => 'Create menu items',
			    3 => '5|J',
			    4 => 3,
			    5 => '<i class=\'S16 e-custom-16\'></i>',
			    6 => '<i class=\'S32 e-custom-32\'></i> ',
			  ),
			  6 =>
			  array (
			    0 => '/e107_admin/db.php',
			    1 => 'Database',
			    2 => 'Database utilities',
			    3 => '0',
			    4 => 4,
			    5 => '<i class=\'S16 e-database-16\'></i>',
			    6 => '<i class=\'S32 e-database-32\'></i> ',
			  ),
			  8 =>
			  array (
			    0 => '/e107_admin/emoticon.php',
			    1 => 'Emoticons',
			    2 => 'Configure emoticons',
			    3 => 'F',
			    4 => 1,
			    5 => '<i class=\'S16 e-emoticons-16\'></i>',
			    6 => '<i class=\'S32 e-emoticons-32\'></i> ',
			  ),
			  10 =>
			  array (
			    0 => '/e107_admin/frontpage.php',
			    1 => 'Front Page',
			    2 => 'Configure front page content',
			    3 => 'G',
			    4 => 1,
			    5 => '<i class=\'S16 e-frontpage-16\'></i>',
			    6 => '<i class=\'S32 e-frontpage-32\'></i> ',
			  ),
			  11 =>
			  array (
			    0 => '/e107_admin/image.php',
			    1 => 'Media Manager',
			    2 => 'Media Manager',
			    3 => 'A',
			    4 => 5,
			    5 => '<i class=\'S16 e-images-16\'></i>',
			    6 => '<i class=\'S32 e-images-32\'></i> ',
			  ),
			  12 =>
			  array (
			    0 => '/e107_admin/links.php',
			    1 => 'Navigation',
			    2 => 'Add/edit/delete links',
			    3 => 'I',
			    4 => 1,
			    5 => '<i class=\'S16 e-links-16\'></i>',
			    6 => '<i class=\'S32 e-links-32\'></i> ',
			  ),
			  13 =>
			  array (
			    0 => '/e107_admin/wmessage.php',
			    1 => 'Welcome Message',
			    2 => 'Set static welcome message',
			    3 => 'M',
			    4 => 3,
			    5 => '<i class=\'S16 e-welcome-16\'></i>',
			    6 => '<i class=\'S32 e-welcome-32\'></i> ',
			  ),
			  14 =>
			  array (
			    0 => '/e107_admin/ugflag.php',
			    1 => 'Maintenance',
			    2 => 'Take site down for maintenance',
			    3 => '9',
			    4 => 4,
			    5 => '<i class=\'S16 e-maintain-16\'></i>',
			    6 => '<i class=\'S32 e-maintain-32\'></i> ',
			  ),
			  15 =>
			  array (
			    0 => '/e107_admin/menus.php',
			    1 => 'Menu Manager',
			    2 => 'Alter the order of your menus',
			    3 => '2',
			    4 => 5,
			    5 => '<i class=\'icon S16 e-menus-16\'></i>',
			    6 => '<i class=\'S32 e-menus-32\'></i> ',
			  ),
			  16 =>
			  array (
			    0 => '/e107_admin/meta.php',
			    1 => 'Meta Tags',
			    2 => 'Add/Edit site meta tags',
			    3 => 'T',
			    4 => 1,
			    5 => '<i class=\'icon S16 e-meta-16\'></i>',
			    6 => '<i class=\'S32 e-meta-32\'></i> ',
			  ),
			  17 =>
			  array (
			    0 => '/e107_admin/newspost.php',
			    1 => 'News',
			    2 => 'Manage news items',
			    3 => 'H|N|7|H0|H1|H2|H3|H4|H5',
			    4 => 3,
			    5 => '<i class=\'icon S16 e-news-16\'></i>',
			    6 => '<i class=\'S32 e-news-32\'></i> ',
			  ),
			  18 =>
			  array (
			    0 => '/e107_admin/phpinfo.php',
			    1 => 'PHP Info',
			    2 => 'PHP Info page',
			    3 => '0',
			    4 => 20,
			    5 => '<i class=\'S16 e-phpinfo-16\'></i>',
			    6 => '<i class=\'S32 e-phpinfo-32\'></i> ',
			  ),
			  19 =>
			  array (
			    0 => '/e107_admin/prefs.php',
			    1 => 'Preferences',
			    2 => 'Edit Site Preferences',
			    3 => '1',
			    4 => 1,
			    5 => '<i class=\'S16 e-prefs-16\'></i>',
			    6 => '<i class=\'S32 e-prefs-32\'></i> ',
			  ),
			  20 =>
			  array (
			    0 => '/e107_admin/search.php',
			    1 => 'Search',
			    2 => 'Search Configuration',
			    3 => 'X',
			    4 => 1,
			    5 => '<i class=\'S16 e-search-16\'></i>',
			    6 => '<i class=\'S32 e-search-32\'></i> ',
			  ),
			  21 =>
			  array (
			    0 => '/e107_admin/admin_log.php',
			    1 => 'System Logs',
			    2 => 'Admin log, user audit, rolling log',
			    3 => 'S',
			    4 => 4,
			    5 => '<i class=\'S16 e-adminlogs-16\'></i>',
			    6 => '<i class=\'S32 e-adminlogs-32\'></i> ',
			  ),
			  22 =>
			  array (
			    0 => '/e107_admin/theme.php',
			    1 => 'Theme Manager',
			    2 => 'Click here to install and configure themes, which control the appearance of your site.',
			    3 => '1|TMP',
			    4 => 5,
			    5 => '<i class=\'S16 e-themes-16\'></i>',
			    6 => '<i class=\'S32 e-themes-32\'></i> ',
			  ),
			  23 =>
			  array (
			    0 => '/e107_admin/upload.php',
			    1 => 'Public Uploads',
			    2 => 'Configure public file uploads',
			    3 => 'V',
			    4 => 3,
			    5 => '<i class=\'S16 e-uploads-16\'></i>',
			    6 => '<i class=\'S32 e-uploads-32\'></i> ',
			  ),
			  24 =>
			  array (
			    0 => '/e107_admin/users.php',
			    1 => 'Users',
			    2 => 'Moderate site members',
			    3 => '4|U0|U1|U2|U3',
			    4 => 2,
			    5 => '<i class=\'S16 e-users-16\'></i>',
			    6 => '<i class=\'S32 e-users-32\'></i> ',
			  ),
			  25 =>
			  array (
			    0 => '/e107_admin/userclass2.php',
			    1 => 'User Classes',
			    2 => 'Create/edit user classes',
			    3 => '4',
			    4 => 2,
			    5 => '<i class=\'S16 e-userclass-16\'></i>',
			    6 => '<i class=\'S32 e-userclass-32\'></i> ',
			  ),
			  26 =>
			  array (
			    0 => '/e107_admin/language.php',
			    1 => 'Language',
			    2 => 'default',
			    3 => 'L',
			    4 => 1,
			    5 => '<i class=\'S16 e-language-16\'></i>',
			    6 => '<i class=\'S32 e-language-32\'></i> ',
			  ),
			  27 =>
			  array (
			    0 => '/e107_admin/mailout.php',
			    1 => 'Mail',
			    2 => 'Email Settings And Mailout',
			    3 => 'W',
			    4 => 2,
			    5 => '<i class=\'S16 e-mail-16\'></i>',
			    6 => '<i class=\'S32 e-mail-32\'></i> ',
			  ),
			  28 =>
			  array (
			    0 => '/e107_admin/users_extended.php',
			    1 => 'Extended User Fields',
			    2 => 'Edit extended user fields',
			    3 => '4',
			    4 => 2,
			    5 => '<i class=\'S16 e-extended-16\'></i>',
			    6 => '<i class=\'S32 e-extended-32\'></i> ',
			  ),
			  29 =>
			  array (
			    0 => '/e107_admin/fileinspector.php',
			    1 => 'File Inspector',
			    2 => 'Scan site files',
			    3 => 'Y',
			    4 => 4,
			    5 => '<i class=\'S16 e-fileinspector-16\'></i>',
			    6 => '<i class=\'S32 e-fileinspector-32\'></i> ',
			  ),
			  30 =>
			  array (
			    0 => '/e107_admin/notify.php',
			    1 => 'Notify',
			    2 => 'Admin Email Notifications',
			    3 => 'O',
			    4 => 4,
			    5 => '<i class=\'S16 e-notify-16\'></i>',
			    6 => '<i class=\'S32 e-notify-32\'></i> ',
			  ),
			  31 =>
			  array (
			    0 => '/e107_admin/cron.php',
			    1 => 'Schedule Tasks',
			    2 => 'Cron Jobs and Automated Maintenance',
			    3 => 'U',
			    4 => 4,
			    5 => '<i class=\'S16 e-cron-16\'></i>',
			    6 => '<i class=\'S32 e-cron-32\'></i> ',
			  ),
			  32 =>
			  array (
			    0 => '/e107_admin/eurl.php',
			    1 => 'URL Configuration',
			    2 => 'Configure Site URLs',
			    3 => 'K',
			    4 => 1,
			    5 => '<i class=\'S16 e-eurl-16\'></i>',
			    6 => '<i class=\'S32 e-eurl-32\'></i> ',
			  ),
			  33 =>
			  array (
			    0 => '/e107_admin/plugin.php',
			    1 => 'Plugin Manager',
			    2 => 'Click here to install, maintain and configure plugins which provide additional features on your site.',
			    3 => 'Z',
			    4 => 5,
			    5 => '<i class=\'S16 e-plugmanager-16\'></i>',
			    6 => '<i class=\'S32 e-plugmanager-32\'></i> ',
			  ),
			  34 =>
			  array (
			    0 => '/e107_admin/docs.php',
			    1 => 'Docs',
			    2 => 'System documentation',
			    3 => false,
			    4 => 20,
			    5 => '<i class=\'S16 e-docs-16\'></i>',
			    6 => '<i class=\'S32 e-docs-32\'></i> ',
			  ),
			  36 =>
			  array (
			    0 => '/e107_admin/credits.php',
			    1 => 'Credits',
			    2 => 'Credits',
			    3 => false,
			    4 => 20,
			    5 => '<img class=\'icon S16\' src=\'./e107_images/e107_icon_16.png\' alt=\'\' />',
			    6 => '<img class=\'icon S32\' src=\'./e107_images/e107_icon_32.png\' alt=\'\' />',
			  ),
			  38 =>
			  array (
			    0 => '/e107_admin/comment.php',
			    1 => 'Comments Manager',
			    2 => 'Comments Manager',
			    3 => 'B',
			    4 => 5,
			    5 => '<i class=\'S16 e-comments-16\'></i>',
			    6 => '<i class=\'S32 e-comments-32\'></i> ',
			  ),
			);


			$result	= e107::getNav()->adminLinks('legacy');
			$this->assertSame($expected, $result);

		}

		public function testAdminLinksSub()
		{
			$expected = array (
			  17 =>
			  array (
			    0 =>
			    array (
			      0 => './e107_admin/newspost.php',
			      1 => 'Manage',
			      2 => 'News items List',
			      3 => 'H',
			      4 => 3,
			      5 => '<i class=\'S16 e-manage-16\'></i>',
			      6 => '<i class=\'S32 e-manage-32\'></i> ',
			    ),
			    1 =>
			    array (
			      0 => './e107_admin/newspost.php?create',
			      1 => 'Create',
			      2 => 'Create news item',
			      3 => 'H',
			      4 => 3,
			      5 => '<i class=\'S16 e-add-16\'></i>',
			      6 => '<i class=\'S32 e-add-32\'></i> ',
			    ),
			    2 =>
			    array (
			      0 => './e107_admin/newspost.php?pref',
			      1 => 'Preferences',
			      2 => 'Preferences',
			      3 => 'H',
			      4 => 3,
			      5 => '<i class=\'S16 e-settings-16\'></i>',
			      6 => '<i class=\'S32 e-settings-32\'></i> ',
			    ),
			  ),
			);


			$result	= e107::getNav()->adminLinks('sub');
			$this->assertSame($expected, $result);


		}

		public function testAdminLinksPlugins()
		{
			e107::loadAdminIcons();

			$expected = array (
			/*  'plugnav-featurebox' =>
			  array (
			    'text' => 'Feature Box',
			    'description' => 'Displays an animated area on the top of your page with news-items and other content you would like to feature.',
			    'link' => '/e107_plugins/featurebox/admin_config.php',
			    'image' => '<img src=\'./e107_plugins/featurebox/images/featurebox_16.png\' alt="Feature Box"  class=\'icon S16\'  />',
			    'image_large' => '<img src=\'./e107_plugins/featurebox/images/featurebox_32.png\' alt="Feature Box"  class=\'icon S32\'  />',
			    'category' => 'content',
			    'perm' => 'P8',
			    'sort' => 2,
			    'sub_class' => NULL,
			    'key' => 'plugnav-featurebox',
			    'title' => 'Feature Box',
			    'caption' => 'Configure feature box',
			    'perms' => 'P8',
			    'icon' => '<img src=\'./e107_plugins/featurebox/images/featurebox_16.png\' alt="Feature Box"  class=\'icon S16\'  />',
			    'icon_32' => '<img src=\'./e107_plugins/featurebox/images/featurebox_32.png\' alt="Feature Box"  class=\'icon S32\'  />',
			    'cat' => 3,
			  ),
			  'plugnav-gallery' =>
			  array (
			    'text' => 'Gallery',
			    'description' => 'A simple image gallery',
			    'link' => '/e107_plugins/gallery/admin_gallery.php',
			    'image' => '<img src=\'./e107_plugins/gallery/images/gallery_16.png\' alt="Gallery"  class=\'icon S16\'  />',
			    'image_large' => '<img src=\'./e107_plugins/gallery/images/gallery_32.png\' alt="Gallery"  class=\'icon S32\'  />',
			    'category' => 'content',
			    'perm' => 'P10',
			    'sort' => 2,
			    'sub_class' => NULL,
			    'key' => 'plugnav-gallery',
			    'title' => 'Gallery',
			    'caption' => 'Configure',
			    'perms' => 'P10',
			    'icon' => '<img src=\'./e107_plugins/gallery/images/gallery_16.png\' alt="Gallery"  class=\'icon S16\'  />',
			    'icon_32' => '<img src=\'./e107_plugins/gallery/images/gallery_32.png\' alt="Gallery"  class=\'icon S32\'  />',
			    'cat' => 3,
			  ),
			  'plugnav-rss_menu' =>
			  array (
			    'text' => 'RSS',
			    'description' => 'RSS Feeds from your site.',
			    'link' => '/e107_plugins/rss_menu/admin_prefs.php',
			    'image' => '<img src=\'./e107_plugins/rss_menu/images/rss_16.png\' alt="RSS"  class=\'icon S16\'  />',
			    'image_large' => '<img src=\'./e107_plugins/rss_menu/images/rss_32.png\' alt="RSS"  class=\'icon S32\'  />',
			    'category' => 'misc',
			    'perm' => 'P24',
			    'sort' => 2,
			    'sub_class' => NULL,
			    'key' => 'plugnav-rss_menu',
			    'title' => 'RSS',
			    'caption' => 'Configure',
			    'perms' => 'P24',
			    'icon' => '<img src=\'./e107_plugins/rss_menu/images/rss_16.png\' alt="RSS"  class=\'icon S16\'  />',
			    'icon_32' => '<img src=\'./e107_plugins/rss_menu/images/rss_32.png\' alt="RSS"  class=\'icon S32\'  />',
			    'cat' => 7,
			  ),
			*/
			  'plugnav-social' =>
			  array (
			    'text' => 'Social',
			    'description' => 'Adds options to replace the e107 comment engine with Facebook. Add Twitter feeds to your site. etc.',
			    'link' => '/e107_plugins/social/admin_config.php',
			    'image' => '<img src=\'./e107_plugins/social/images/icon_16.png\' alt="Social"  class=\'icon S16\'  />',
			    'image_large' => '<img src=\'./e107_plugins/social/images/icon_32.png\' alt="Social"  class=\'icon S32\'  />',
			    'category' => 'settings',
			    'perm' => 'P26',
			    'sort' => 2,
			    'sub_class' => NULL,
			    'key' => 'plugnav-social',
			    'title' => 'Social',
			    'caption' => false,
			    'perms' => 'P26',
			    'icon' => '<img src=\'./e107_plugins/social/images/icon_16.png\' alt="Social"  class=\'icon S16\'  />',
			    'icon_32' => '<img src=\'./e107_plugins/social/images/icon_32.png\' alt="Social"  class=\'icon S32\'  />',
			    'cat' => 1,
			  ),
			  'plugnav-tinymce4' =>
			  array (
			    'text' => 'TinyMce4',
			    'description' => 'TinyMce4 CDN version',
			    'link' => '/e107_plugins/tinymce4/admin_config.php',
			    'image' => '<img src=\'./e107_plugins/tinymce4/images/icon_16.png\' alt="TinyMce4"  class=\'icon S16\'  />',
			    'image_large' => '<img src=\'./e107_plugins/tinymce4/images/icon_32.png\' alt="TinyMce4"  class=\'icon S32\'  />',
			    'category' => 'misc',
			    'perm' => 'P28',
			    'sort' => 2,
			    'sub_class' => NULL,
			    'key' => 'plugnav-tinymce4',
			    'title' => 'TinyMce4',
			    'caption' => 'Configure',
			    'perms' => 'P28',
			    'icon' => '<img src=\'./e107_plugins/tinymce4/images/icon_16.png\' alt="TinyMce4"  class=\'icon S16\'  />',
			    'icon_32' => '<img src=\'./e107_plugins/tinymce4/images/icon_32.png\' alt="TinyMce4"  class=\'icon S32\'  />',
			    'cat' => 7,
			  ),
			);


			$result = e107::getNav()->adminLinks('plugin2');

			foreach($expected as $key=>$val)
			{
				$this->assertArrayHasKey($key,$result);
			}


		}
/*
		public function testPlugCatToCoreCat()
		{

		}

		public function testGetDefaultAdminPanelArray()
		{

		}

		public function testRenderAdminButton()
		{

		}

		public function testPluginLinks()
		{

		}
*/
		public function testAdminCats()
		{
			$expected = array (
			  'title' =>
			  array (
			    1 => 'Settings',
			    2 => 'Users',
			    3 => 'Content',
			    4 => 'Tools',
			    5 => 'Manage',
			    6 => 'Misc',
			    20 => 'About',
			  ),
			  'id' =>
			  array (
			    1 => 'setMenu',
			    2 => 'userMenu',
			    3 => 'contMenu',
			    4 => 'toolMenu',
			    5 => 'managMenu',
			    6 => 'miscMenu',
			    20 => 'aboutMenu',
			  ),
			  'img' =>
			  array (
			    1 => 'fa-cogs.glyph',
			    2 => 'fa-users.glyph',
			    3 => 'fa-file-text-o.glyph',
			    4 => 'fa-wrench.glyph',
			    5 => 'fa-desktop.glyph',
			    6 => 'fa-puzzle-piece.glyph',
			    20 => 'fa-info-circle.glyph',
			  ),
			  'lrg_img' =>
			  array (
			    1 => '<i class=\'S32 e-settings-32\'></i>',
			    2 => '<i class=\'S32 e-cat_users-32\'></i>',
			    3 => '<i class=\'S32 e-cat_content-32\'></i>',
			    4 => '<i class=\'S32 e-cat_tools-32\'></i>',
			    5 => '<i class=\'S32 e-manage-32\'></i>',
			    6 => '',
			    20 => '',
			  ),
			  'sort' =>
			  array (
			    1 => true,
			    2 => true,
			    3 => true,
			    4 => true,
			    5 => true,
			    6 => true,
			    20 => false,
			  ),
			);

			$result = e107::getNav()->adminCats();
			$this->assertSame($expected, $result);

		}
/*
		public function testCacheBase()
		{

		}

		public function testIsActive()
		{

		}

		public function testSetIconArray()
		{

		}

		public function testAdmin()
		{

		}

		public function testGetIconArray()
		{

		}

		public function testInitData()
		{

		}*/




	}
