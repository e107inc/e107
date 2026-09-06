<?php
	/**
	 * Created by PhpStorm.
	 * User: Wiz
	 * Date: 11/15/2018
	 * Time: 12:01 PM
	 */


	class xmlClassTest extends \Test\Unit
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

		/**
		 * A duplicate key is invisible in every install XML e107 ships, and it
		 * resolves differently depending on which importer reads the file.
		 *
		 * xmlClass keeps every occurrence. e107ImportPrefs() then assigns them
		 * with a plain assignment, so the last in document order wins, while
		 * e107plugin::XmlPrefs() applies an 'install' through e_pref::add(),
		 * which only writes a key that is absent, so the first wins. Neither is
		 * anything a reader of the file can see, which is why this sweeps the
		 * shipped set rather than the one file a defect was reported against.
		 */
		public function testBundledXmlDeclaresEachPreferenceOnce()
		{
			$files = self::bundledXmlFiles();

			$this->assertNotEmpty($files, 'Found no bundled XML to check.');

			$duplicates = array();

			foreach($files as $file)
			{
				foreach(self::duplicatePrefNames($file) as $name)
				{
					$duplicates[] = self::relativePath($file).': '.$name;
				}
			}

			$this->assertSame(array(), $duplicates,
				'A preference declared twice resolves to whichever occurrence the reading importer happens to keep.');
		}

		/**
		 * '#' is not a destination, and an icon carrying it is an anchor whose
		 * aria-label names somewhere the visitor cannot be sent.
		 *
		 * A theme's install.xml is imported in 'replace' mode after the core one
		 * (install.php, and themeHandler::installContent() from the theme
		 * manager), so a theme can put back a placeholder the core file no
		 * longer seeds.
		 */
		public function testBundledXmlSeedsNoSocialUrlThatGoesNowhere()
		{
			$seeded = array();

			foreach(self::bundledXmlFiles() as $file)
			{
				$document = new DOMDocument();
				$document->load($file);

				$xpath = new DOMXPath($document);

				foreach($xpath->query('//*[@name="xurl"]') as $element)
				{
					$xurl = e107::unserialize($element->nodeValue);

					foreach((array) $xurl as $network => $url)
					{
						if($url === '#')
						{
							$seeded[] = self::relativePath($file).': '.$network;
						}
					}
				}
			}

			$this->assertSame(array(), $seeded,
				'A bundled XML must not seed a social URL that goes nowhere.');
		}

		/**
		 * Every XML e107 ships that can seed a preference.
		 *
		 * @return string[]
		 */
		private static function bundledXmlFiles()
		{
			$files = array_merge(
				glob(e_CORE.'xml/*.xml'),
				glob(e_THEME.'*/install.xml'),
				glob(e_THEME.'*/install/install.xml'),
				glob(e_THEME.'*/theme.xml'),
				glob(e_PLUGIN.'*/plugin.xml')
			);

			sort($files);

			return $files;
		}

		/**
		 * Names declared more than once inside one preference block of one file.
		 *
		 * @param string $file
		 * @return string[]
		 */
		private static function duplicatePrefNames($file)
		{
			$document = new DOMDocument();

			if(!$document->load($file))
			{
				throw new RuntimeException($file.' does not parse.');
			}

			$xpath = new DOMXPath($document);
			$duplicates = array();

			foreach(array('prefs', 'mainPrefs', 'pluginPrefs', 'themePrefs') as $container)
			{
				foreach($xpath->query('//'.$container) as $block)
				{
					$seen = array();

					foreach($block->childNodes as $child)
					{
						if($child->nodeType !== XML_ELEMENT_NODE)
						{
							continue;
						}

						$key = $container.' <'.$child->nodeName.' name="'.$child->getAttribute('name').'">';

						if(isset($seen[$key]))
						{
							$duplicates[$key] = $key;
						}

						$seen[$key] = true;
					}
				}
			}

			return array_values($duplicates);
		}

		/**
		 * @param string $file
		 * @return string path relative to the app root
		 */
		private static function relativePath($file)
		{
			return strpos($file, e_BASE) === 0 ? (string) substr($file, strlen(e_BASE)) : $file;
		}

		/**
		 * The values a new site is founded on, read back through the importer.
		 *
		 * Asserting the file's text would say nothing about what a site ends up
		 * with, because the resolution happens after parsing. These seven are the
		 * keys default_install.xml has historically declared twice.
		 */
		public function testE107ImportPrefsResolvesDefaultInstallValues()
		{
			$xmlArray = $this->_xml->loadXMLfile(e_CORE."xml/default_install.xml", 'advanced');

			$prefs = $this->_xml->e107ImportPrefs($xmlArray);

			$expected = array(
				// A userclass, so the preferences dropdown can preselect it, and
				// one the contact form can resolve to the main admin.
				'sitecontacts'     => '250',
				// The value every install has been founded on since 2007. The
				// preference is inert (htmlAbuseFilter() has no callers) and
				// prefs.php falls back to 1 for a site missing the key, so the
				// deduplication keeps the shipped default rather than adopting
				// b8e5190780, which edited the losing declaration.
				'html_abuse'       => '1',
				'filter_script'    => '1',
				'sitename'         => 'My Website',
				'sitetag'          => 'e107 Website System',
				'pageCookieExpire' => '84600',
				// Empty, so no icon is published for a destination the site has
				// not been given.
				'xurl'             => array(),
			);

			$resolved = array();

			foreach($expected as $key => $unused)
			{
				$resolved[$key] = isset($prefs[$key]) ? $prefs[$key] : null;
			}

			$this->assertSame($expected, $resolved);
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
