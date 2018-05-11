<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2018 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */

	use PHPUnit\Framework\TestCase;

	class XMLClassTest extends TestCase
	{
		public function testLoadDefaultXMLPreferences()
		{
		//	$config = e_CORE."xml/default_install.xml";
		//	$ret = e107::getXml()->e107Import($config, 'replace', true, true); // Add core pref values

			$this->assertTrue(true); // doing nothing right now.
		}
	}
