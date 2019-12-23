<?php
	/**
	 * Created by PhpStorm.
	 * User: Wiz
	 * Date: 11/15/2018
	 * Time: 12:01 PM
	 */


	class xmlClassTest extends \Codeception\Test\Unit
	{
		const RAW_XML = '<?xml version="1.0" encoding="UTF-8"?>
				<feed xmlns:yt="http://www.youtube.com/xml/schemas/2015" xmlns:media="http://search.yahoo.com/mrss/" xmlns="http://www.w3.org/2005/Atom">
				 <link rel="self" href="http://www.youtube.com/feeds/videos.xml?channel_id=UC7vv3cBq14FRXajteZt6FEg"/>
				 <id>yt:channel:UC7vv3cBq14FRXajteZt6FEg</id>
				 <yt:channelId>UC7vv3cBq14FRXajteZt6FEg</yt:channelId>
				 <title>egucom2014</title>
				 <link rel="alternate" href="https://www.youtube.com/channel/UC7vv3cBq14FRXajteZt6FEg"/>
				 <author>
				  <name>egucom2014</name>
				  <uri>https://www.youtube.com/channel/UC7vv3cBq14FRXajteZt6FEg</uri>
				 </author>
				 <published>2016-01-17T11:31:33+00:00</published>
				 <entry>
				  <id>yt:video:palm1QdV8ZI</id>
				  <yt:videoId>palm1QdV8ZI</yt:videoId>
				  <yt:channelId>UC7vv3cBq14FRXajteZt6FEg</yt:channelId>
				  <title>[EGU] Erstes Offizielles Intro</title>
				  <link rel="alternate" href="https://www.youtube.com/watch?v=palm1QdV8ZI"/>
				  <author>
				   <name>egucom2014</name>
				   <uri>https://www.youtube.com/channel/UC7vv3cBq14FRXajteZt6FEg</uri>
				  </author>
				  <published>2017-09-30T18:44:07+00:00</published>
				  <updated>2019-01-18T20:11:48+00:00</updated>
				  <media:group>
				   <media:title>[EGU] Erstes Offizielles Intro</media:title>
				   <media:content url="https://www.youtube.com/v/palm1QdV8ZI?version=3" type="application/x-shockwave-flash" width="640" height="390"/>
				   <media:thumbnail url="https://i1.ytimg.com/vi/palm1QdV8ZI/hqdefault.jpg" width="480" height="360"/>
				   <media:description>Das erste Intro von Eternal GamerZ United!</media:description>
				   <media:community>
				    <media:starRating count="3" average="3.67" min="1" max="5"/>
				    <media:statistics views="71"/>
				   </media:community>
				  </media:group>
				 </entry>
				</feed>';
		/** @var xmlClass */
		private $_xml;

		protected function _before()
		{
			try
			{
				$this->_xml = $this->make('xmlClass',
					[
						'getRemoteFile' => function($address, $timeout = 10, $postData = null)
						{
							$this->_xml->xmlFileContents = self::RAW_XML;
							return self::RAW_XML;
						},
						'xmlFileContents' => self::RAW_XML
					]
					);
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
*/
		public function testLoadXMLfile()
		{
			$feed = 'https://www.youtube.com/feeds/videos.xml?channel_id=UC7vv3cBq14FRXajteZt6FEg';
			$contents = $this->_xml->reset(true)->loadXMLFile($feed,true);

			$this->assertNotEmpty($contents);

			// print_r($contents);

		}
/*
		public function testSetOptFilter()
		{

		}

		public function testSetOptStringTags()
		{

		}
*/
		public function testParseXml()
		{
			$raw = self::RAW_XML;

		$result = $this->_xml->parseXml($raw,true);

		$this->assertEquals('egucom2014', $result['author']['name']);


		}
/*
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
*/
		public function testGetRemoteFile()
		{
			$feed = 'https://www.youtube.com/feeds/videos.xml?channel_id=UC7vv3cBq14FRXajteZt6FEg';
			$contents = $this->_xml->getRemoteFile($feed,true);

			$this->assertStringContainsString('<?xml version="1.0" encoding="UTF-8"?>',$contents);

		}
/*
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
			$ret = $this->_xml->e107Export(array('core'), null, null, null, array('return'=>true));

			$incorrect = '<core name="e_jslib_plugin"><![CDATA[Array]]></core>';
			$correct = '<core name="e_jslib_plugin"><![CDATA[array ()]]></core>';

			$this->assertStringNotContainsString($incorrect, $ret);
			$this->assertStringContainsString($correct, $ret);

		}
	}
