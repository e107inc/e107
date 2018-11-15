<?php
	/**
	 * Created by PhpStorm.
	 * User: Wiz
	 * Date: 11/15/2018
	 * Time: 12:01 PM
	 */


	class xmlClassTest extends \Codeception\Test\Unit
	{
		/** @var xmlClass */
		private $_xml;

		protected function _before()
		{
			try
			{
				$this->_xml = $this->make('xmlClass');
			//	$this->_xml->__construct();
			}
			catch (Exception $e)
			{
				$this->assertTrue(false, "Couldn't load xmlClass object");
			}

		}
/*
		public function testXml_convert_to_array()
		{

		}

		public function testLoadXMLfile()
		{

		}

		public function testSetOptFilter()
		{

		}

		public function testSetOptStringTags()
		{

		}

		public function testParseXml()
		{

		}

		public function testE107ExportValue()
		{

		}

		public function testSetOptArrayTags()
		{

		}

		public function testParseStringTags()
		{

		}

		public function testGetErrors()
		{

		}

		public function testSetOptAddRoot()
		{

		}

		public function testE107ImportValue()
		{

		}

		public function testGetLastErrorMessage()
		{

		}

		public function testSetOptStripComments()
		{

		}

		public function testGetRemoteFile()
		{

		}

		public function testSetOptForceArray()
		{

		}

		public function testSetOptValueKey()
		{

		}
*/
		public function testE107ImportPrefs()
		{
			$file = e_CORE."xml/default_install.xml";

			$checks = array('ssl_enabled', 'smtp_server', 'e_jslib_core', 'e_jslib_plugin');

			$xmlArray = $this->_xml->loadXMLfile($file, 'advanced');

			$arr = array();

			foreach($xmlArray['prefs']['core'] as $val)
			{
				if(in_array($val['@attributes']['name'],$checks))
				{
					$arr['prefs']['core'][] = $val;
				}
			}


			$result = $this->_xml->e107ImportPrefs($arr);

			$expected = array (
			  'e_jslib_core' =>
			  array (
			    'prototype' => 'none',
			    'jquery' => 'all',
			  ),
			  'e_jslib_plugin' =>
			  array (
			  ),
			  'smtp_server' => '',
			  'ssl_enabled' => '0',
			);

			$this->assertEquals($expected,$result);


		}
/*
		public function testSetFeedUrl()
		{

		}

		public function testXml2array()
		{

		}
*/
		public function testE107Import()
		{

		}
/*
		public function testSetUrlPrefix()
		{

		}
*/

		public function testE107Export()
		{
			$ret = $this->_xml->e107Export(array('core'), null, null, array('return'=>true));

			$incorrect = '<core name="e_jslib_plugin"><![CDATA[Array]]></core>';
			$correct = '<core name="e_jslib_plugin"><![CDATA[array ()]]></core>';

			$this->assertNotContains($incorrect, $ret);
			$this->assertContains($correct, $ret);

		}
	}
