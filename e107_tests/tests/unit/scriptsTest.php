<?php


	class scriptsTest extends \Codeception\Test\Unit
	{


		protected function _before()
		{
			define("SEP", " <span class='fa fa-angle-double-right e-breadcrumb'></span> ");
		}


		public function testAdminScripts()
		{
		//	$globalList = e107::getPref('lan_global_list');


			$list = scandir(e_ADMIN);

			$config = e107::getConfig();

			$preInstall = array('banner', 'page');
			$exclude = array('index.php', 'menus.php'); // FIXME menus defines e_ADMIN_AREA which messes up other tests.

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
			$_E107['no_theme'] = true; //FIXME unable to change to admin theme in testing environment.

			foreach($list as $file)
			{
				$ext = pathinfo(e_ADMIN.$file, PATHINFO_EXTENSION);

				if($ext !== 'php' || in_array($file, $exclude))
				{
					continue;
				}

			//	echo " --- ".$file." --- \n";
				ob_start();
				// test for PHP Notice/Warning etc.
				$error = false;

				if(require_once(e_ADMIN.$file))
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
