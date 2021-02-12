<?php


	class scriptsTest extends \Codeception\Test\Unit
	{


		protected function _before()
		{
			if(!defined('SEP'))
			{
				define("SEP", " <span class='fa fa-angle-double-right e-breadcrumb'></span> ");
			}

			e107::loadAdminIcons();
		}

		public function testAdminScripts()
		{
			$exclude = array(
				'index.php',
				'menus.php', // FIXME menus defines e_ADMIN_AREA which messes up other tests.
				'header.php',
				'footer.php'
			);
			
			$this->loadScripts(e_ADMIN, $exclude);

		}

		public function testAdminIncludes()
		{
			ob_start();
			require_once(e_ADMIN."admin.php");
			ob_end_clean();
			$this->loadScripts(e_ADMIN."includes/");

		}

		public function testAdminLayouts()
		{
			$this->loadScripts(e_ADMIN.'includes/layouts/');
		}


		public function testFrontend()
		{
			e107::getConfig()->setPref('plug_installed/gsitemap', '1.0');

			$include = 	array (
				  0 => 'banner.php',
			//	  1 => 'class2.php',
			//	  2 => 'comment.php',
				  3 => 'contact.php',

			//	  5 => 'cron.php',
			//	  6 => 'download.php',
			//	  7 => 'e107_config.php',

			//	  12 => 'email.php',
				  13 => 'error.php',

				  15 => 'fpw.php',
				  16 => 'gsitemap.php',
			//	  17 => 'index.php', // redirects
			//	  18 => 'install.php', // not compatible with core.

				  20 => 'login.php',
				  21 => 'membersonly.php',
			//	  22 => 'metaweblog.php',
				  23 => 'news.php',
				  24 => 'online.php',
				  25 => 'page.php',
			//	  26 => 'print.php',
			//	  27 => 'rate.php', // has a redirect.
			//	  28 => 'request.php', // redirects
				  29 => 'search.php',
			//	  30 => 'signup.php', too many 'exit';
				  31 => 'sitedown.php',
				  32 => 'submitnews.php',

			//	  34 => 'thumb.php', // separate test.
				  35 => 'top.php',
				  36 => 'unsubscribe.php',
		//		  37 => 'upload.php', // FIXME LAN conflict.
				  38 => 'user.php',
			//	  39 => 'userposts.php', // FIXME needs a rework
				  40 => 'usersettings.php',
				);

			$this->loadScripts(e_BASE, array(), $include);
		}




		private function loadScripts($folder, $exclude= array(), $include=array())
		{
		//	$globalList = e107::getPref('lan_global_list');


			$list = scandir($folder);

			$config = e107::getConfig();

			$preInstall = array('banner', 'page');


			foreach($preInstall as $plug)
			{
				e107::getConfig()->setPref('plug_installed/'.$plug, '1.0');
			}

			global $pref, $ns, $tp, $frm;

			$pref = e107::getPref();
			$ns = e107::getRender();
			$tp = e107::getParser();
			$frm = e107::getForm();

			global $_E107;
			$_E107['cli'] = true;
		//	$_E107['no_theme'] = true; //FIXME unable to change to admin theme in testing environment.

			foreach($list as $file)
			{
				$ext = pathinfo($folder.$file, PATHINFO_EXTENSION);

				if($ext !== 'php' || in_array($file, $exclude) || (!empty($include) && !in_array($file,$include)))
				{
					continue;
				}

		//		echo " --- ".$file." --- \n";
			//	codecept_debug("Loading file: ".$file);
				ob_start();
				// test for PHP Notice/Warning etc.
				$error = false;

				if(require_once($folder.$file))
				{
					$this->assertTrue(true, "loading ".$file);
				}
				else
				{
					$error = true;
				}
				ob_end_clean();

				if($error)
				{
					$this->fail("Couldn't load ".$file);
				}



			}

		}






	}
