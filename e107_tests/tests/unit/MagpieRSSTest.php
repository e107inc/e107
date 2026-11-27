<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

require_once(codecept_data_dir('MagpieRSSDocuments.php'));

class MagpieRSSTest extends \Codeception\Test\Unit
{
	use \Test\BootedCli;
	use \Test\ProcessorTime;
	use MagpieRSSDocuments;

	/** @var array[] the constructor variants every fixture is read under: output, input, detect */
	private static $variants = array(
		'default'                => array('utf-8', null, true),
		'out-iso-8859-1'         => array('ISO-8859-1', null, true),
		'out-empty'              => array('', null, true),
		'in-iso-8859-1-nodetect' => array('utf-8', 'ISO-8859-1', false),
		'in-utf-8-detect'        => array('utf-8', 'UTF-8', true),
		'nodetect-noin'          => array('utf-8', null, false),
	);

	/** @var array[] PHP errors raised while {@see MagpieRSSTest::quietly()} ran its callable */
	private $raised = array();

	/** @var string a small well-formed feed for the child processes and the direct method tests */
	private static $probe = '<rss version="2.0"><channel><title>t</title></channel></rss>';

	protected function _before()
	{
		require_once(e_HANDLER.'magpie_rss.php');
		require_once(codecept_data_dir('MagpieRSSTestDouble.php'));
	}

	public function testTheFileDefinesTheFeedTypeConstantsAndBothHelperFunctions()
	{
		$this->assertSame('RSS', RSS);
		$this->assertSame('Atom', ATOM);
		$this->assertTrue(function_exists('map_attrs'));
		$this->assertTrue(function_exists('parse_w3cdtf'));
	}

	public function testPublicPropertiesKeepTheirOrderAndInitialValues()
	{
		$expected = array(
			'parser'              => null,
			'current_item'        => array(),
			'items'               => array(),
			'channel'             => array(),
			'textinput'           => array(),
			'image'               => array(),
			'feed_type'           => null,
			'feed_version'        => null,
			'encoding'            => '',
			'_source_encoding'    => '',
			'ERROR'               => '',
			'WARNING'             => '',
			'_CONTENT_CONSTRUCTS' => array('content', 'summary', 'info', 'title', 'tagline', 'copyright'),
			'_KNOWN_ENCODINGS'    => array('UTF-8', 'US-ASCII', 'ISO-8859-1'),
			'stack'               => array(),
			'inchannel'           => false,
			'initem'              => false,
			'incontent'           => false,
			'intextinput'         => false,
			'inimage'             => false,
			'current_field'       => '',
			'current_namespace'   => false,
		);

		$class = new ReflectionClass('MagpieRSS');
		$defaults = $class->getDefaultProperties();
		$public = array();

		foreach($class->getProperties(ReflectionProperty::IS_PUBLIC) as $property)
		{
			$this->assertFalse($property->isStatic(), $property->getName().' is static');
			$public[$property->getName()] = $defaults[$property->getName()];
		}

		$this->assertSame($expected, $public);
		$this->assertSame(array(), $class->getProperties(ReflectionProperty::IS_PROTECTED));
	}

	public function testPublicMethodsKeepTheirNamesParametersAndDefaults()
	{
		$expected = array(
			'__construct'         => array('source', 'output_encoding=CHARSET', 'input_encoding=NULL', 'detect_encoding=true'),
			'feed_start_element'  => array('p', 'element', 'attrs'),
			'feed_cdata'          => array('p', 'text'),
			'feed_end_element'    => array('p', 'el'),
			'concat'              => array('&str1', "str2=''"),
			'append_content'      => array('text'),
			'append'              => array('el', 'text'),
			'normalize'           => array(),
			'is_rss'              => array(),
			'is_atom'             => array(),
			'create_parser'       => array('source', 'out_enc', 'in_enc', 'detect'),
			'php5_create_parser'  => array('in_enc', 'detect'),
			'php4_create_parser'  => array('source', 'in_enc', 'detect'),
			'known_encoding'      => array('enc'),
			'error'               => array('errormsg', 'lvl=E_USER_WARNING'),
		);

		$class = new ReflectionClass('MagpieRSS');
		$this->assertFalse($class->isFinal());
		$this->assertFalse($class->getParentClass());
		$this->assertSame(array(), $class->getInterfaceNames());
		$this->assertFalse($class->inNamespace());

		$actual = array();

		foreach($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method)
		{
			$this->assertFalse($method->isFinal(), $method->getName().' is final');
			$this->assertFalse($method->isStatic(), $method->getName().' is static');
			$actual[$method->getName()] = array();

			foreach($method->getParameters() as $parameter)
			{
				$actual[$method->getName()][] = $this->describeParameter($parameter);
			}
		}

		ksort($expected);
		ksort($actual);
		$this->assertSame($expected, $actual);
		$this->assertSame(array(), $class->getMethods(ReflectionMethod::IS_PROTECTED));
	}

	public function testAParsedFeedCarriesOnlyTheDeclaredProperties()
	{
		$rss = $this->fixture('rss20-basic');
		$declared = array();

		foreach((new ReflectionClass('MagpieRSS'))->getProperties(ReflectionProperty::IS_PUBLIC) as $property)
		{
			$declared[] = $property->getName();
		}

		$this->assertSame($declared, array_keys(get_object_vars($rss)));
		$this->assertNull($rss->parser);
		$this->assertSame(array(), $rss->current_item);
		$this->assertSame(array(), $rss->stack);
	}

	public function testRss20FillsChannelItemsImageAndTextInput()
	{
		$rss = $this->fixture('rss20-basic');

		$this->assertSame(RSS, $rss->feed_type);
		$this->assertSame('2.0', $rss->is_rss());
		$this->assertFalse($rss->is_atom());
		$this->assertSame('Example Channel', $rss->channel['title']);
		$this->assertSame('https://example.test/', $rss->channel['link']);
		$this->assertSame('Sat, 07 Nov 2026 10:00:00 GMT', $rss->channel['lastbuilddate']);
		$this->assertSame('An example feed for testing.', $rss->channel['tagline']);
		$this->assertSame(array('url' => 'https://example.test/logo.png', 'title' => 'Example Logo', 'link' => 'https://example.test/', 'width' => '88', 'height' => '31'), $rss->image);
		$this->assertSame(array('title' => 'Search', 'description' => 'Search the site', 'name' => 'q', 'link' => 'https://example.test/search'), $rss->textinput);
		$this->assertCount(3, $rss->items);
		$this->assertSame('author@example.test (Ann Author)', $rss->items[0]['author']);
		$this->assertSame('NewsSecond category', $rss->items[0]['category']);
		$this->assertSame('The first item.', $rss->items[0]['summary']);
		$this->assertSame(1793953800, $rss->items[0]['date_timestamp']);
		$this->assertArrayNotHasKey('enclosure', $rss->items[0]);
		$this->assertArrayNotHasKey('date_timestamp', $rss->items[1]);
		$this->assertSame(array('title' => 'Third item, no date', 'guid' => 'https://example.test/3'), $rss->items[2]);
	}

	public function testRss10KeepsItemsImageAndTextInputBesideTheChannel()
	{
		$rss = $this->fixture('rss10-rdf');

		$this->assertSame('1.0', $rss->is_rss());
		$this->assertSame('https://example.test/r1', $rss->items[0]['about']);
		$this->assertSame('RDF Author', $rss->items[0]['dc']['creator']);
		$this->assertSame('<p>Encoded RDF content</p>', $rss->items[0]['atom_content']);
		$this->assertSame("\n      \n    ", $rss->channel['items']);
		$this->assertSame("\n        \n        \n      ", $rss->channel['items_seq']);
		$this->assertSame(array('title' => 'RDF image', 'url' => 'https://example.test/rdf.png', 'link' => 'https://example.test/'), $rss->image);
		$this->assertSame('Search RDF', $rss->textinput['title']);
	}

	public function testAtom03CapturesContentAsMarkupAndNormalisesEntries()
	{
		$rss = $this->fixture('atom03');

		$this->assertSame(ATOM, $rss->feed_type);
		$this->assertSame('0.3', $rss->is_atom());
		$this->assertSame('Atom 0.3 tagline', $rss->channel['description']);
		$this->assertSame('Feed Author', $rss->channel['author_name']);
		$this->assertSame('https://example.test/a1', $rss->items[0]['link']);
		$this->assertSame('Entry summary', $rss->items[0]['description']);
		$this->assertSame($rss->items[0]['atom_content'], $rss->items[0]['content']['encoded']);
		$this->assertSame('https://example.test/rel', $rss->items[1]['link_related']);
		$this->assertSame('<p>Escaped content</p>', $rss->items[1]['atom_content']);
		$this->assertSame(1793872800, $rss->items[1]['date_timestamp']);
	}

	public function testANetscapeDoctypeParsesWithoutItsDtd()
	{
		$rss = $this->fixture('rss091-netscape-doctype');

		$this->assertSame('', $rss->ERROR);
		$this->assertSame('0.91', $rss->feed_version);
		$this->assertSame('Old logo', $rss->image['title']);
		$this->assertSame('Old item', $rss->items[0]['title']);
	}

	public function testRss092ItemsNeedNoTitleAndEmptyElementsAddNothing()
	{
		$rss = $this->fixture('rss092-enclosure-cloud');

		$this->assertSame('0.92', $rss->feed_version);
		$this->assertArrayNotHasKey('cloud', $rss->channel);
		$this->assertSame(array('description' => 'Item without a title', 'summary' => 'Item without a title'), $rss->items[0]);
	}

	public function testElementNamesAreLowerCased()
	{
		$rss = $this->fixture('rss20-uppercase-tags');

		$this->assertSame('2.0', $rss->feed_version);
		$this->assertSame('Upper Channel', $rss->channel['title']);
		$this->assertSame('Wed, 04 Nov 2026 00:00:00 GMT', $rss->items[0]['pubdate']);
	}

	public function testNestedElementsJoinTheirNamesIntoTheKey()
	{
		$rss = $this->fixture('rss20-nested-unknown');

		$this->assertSame('foo textafter bar', $rss->items[0]['foo']);
		$this->assertSame('bar text', $rss->items[0]['foo_bar']);
		$this->assertSame('deep', $rss->items[0]['foo_baz_qux']);
		$this->assertSame("\n      \n      \n    ", $rss->channel['custom']);
		$this->assertSame('Channel inner valueRepeated inner value', $rss->channel['custom_inner']);
	}

	public function testWhitespaceIsKeptAndEmptyElementsLeaveNoKey()
	{
		$rss = $this->fixture('rss20-whitespace-empty');

		$this->assertSame("\n      Padded Title\n    ", $rss->channel['title']);
		$this->assertArrayNotHasKey('description', $rss->channel);
		$this->assertNull($rss->channel['tagline']);
		$this->assertSame(array('title' => '  spaced  '), $rss->items[0]);
		$this->assertSame("Multi\nline\ntitle", $rss->items[1]['title']);
	}

	public function testCdataAndReferencesArriveDecoded()
	{
		$rss = $this->fixture('rss20-cdata-entities');

		$this->assertSame('Fish & Chips — ☃ café', $rss->channel['title']);
		$this->assertSame('<p>Escaped <em>HTML</em></p>', $rss->channel['description']);
		$this->assertSame('CDATA <title> & stuff', $rss->items[0]['title']);
		$this->assertSame('Mixed "quotes" \'apos\' > <', $rss->items[1]['title']);
		$this->assertSame('Text before <b>cdata</b> text after', $rss->items[1]['description']);
	}

	public function testCommentsAndProcessingInstructionsAddNothing()
	{
		$rss = $this->fixture('rss20-stylesheet-comments');

		$this->assertSame('Styled feed', $rss->channel['title']);
		$this->assertSame(array(array('title' => 'Item title')), $rss->items);
	}

	public function testAnImageInsideAnItemFillsTheItemAndPrefixedTextGoesToTheChannel()
	{
		$rss = $this->fixture('rss20-image-in-item-and-textinput-ns');

		$this->assertSame(array('image' => 'Namespaced image text', 'extra' => 'image extension', 'hint' => 'hint'), $rss->channel['x']);
		$this->assertSame(array('url' => 'https://example.test/c.png'), $rss->image);
		$this->assertSame(array('title' => 'TI'), $rss->textinput);
		$this->assertSame('https://example.test/item.png', $rss->items[0]['url']);
	}

	public function testAnItemBeforeTheChannelFieldsStillFillsBoth()
	{
		$rss = $this->fixture('rss20-item-before-channel-fields');

		$this->assertSame('Early item', $rss->items[0]['title']);
		$this->assertSame('Late title', $rss->channel['title']);
	}

	public function testADocumentWithoutAFeedRootLeavesTheResultEmpty()
	{
		$rss = $this->fixture('not-a-feed-html');

		$this->assertNull($rss->feed_type);
		$this->assertFalse($rss->is_rss());
		$this->assertFalse($rss->is_atom());
		$this->assertSame(array(), $rss->channel);
		$this->assertSame(array(), $rss->items);
		$this->assertSame('', $rss->ERROR);
	}

	public function testPrefixedElementsLandUnderTheirPrefix()
	{
		$rss = $this->fixture('rss20-namespaces');

		$this->assertSame(array('creator' => 'Channel Creator', 'date' => '2026-11-07T10:00:00Z'), $rss->channel['dc']);
		$this->assertSame('Podcaster', $rss->channel['itunes']['author']);
		$this->assertSame('First subjectSecond subject', $rss->items[0]['dc']['subject']);
		$this->assertSame('https://example.test/m1/comments/feed', $rss->items[0]['wfw']['commentrss']);
		$this->assertSame('Picture', $rss->items[0]['media']['content_title']);
		$this->assertSame('4', $rss->items[0]['slash']['comments']);
		$this->assertArrayNotHasKey('date_timestamp', $rss->items[1]);
	}

	public function testTheRdfPrefixCountsAsNone()
	{
		$rss = $this->parse('<rdf:RDF><rdf:channel><rdf:title>Plain</rdf:title></rdf:channel><rdf:item rdf:about="u"><rdf:title>T</rdf:title></rdf:item></rdf:RDF>');

		$this->assertSame('Plain', $rss->channel['title']);
		$this->assertSame(array('about' => 'u', 'title' => 'T'), $rss->items[0]);
	}

	public function testTextNeverTurnsAnArrayIntoAStringNorAStringIntoAnArray()
	{
		$rss = $this->parse('<rss version="2.0"><channel><item><content:encoded>E</content:encoded><content>plain</content><dc>flat</dc><dc:creator>C</dc:creator></item></channel></rss>');

		$this->assertSame(array('content' => array('encoded' => 'E'), 'dc' => 'flat', 'atom_content' => 'E'), $rss->items[0]);
	}

	public function testADeclaredLatin1DocumentIsDecoded()
	{
		$rss = $this->fixture('rss20-iso-8859-1');

		$this->assertSame('Café Latin-1 £5 ¼ ÿ', $rss->channel['title']);
		$this->assertSame('Größe', $rss->items[0]['description']);
	}

	public function testUtf16WithAByteOrderMarkIsDecoded()
	{
		$rss = $this->fixture('rss20-utf16le-bom');

		$this->assertSame('UTF-16 feed é', $rss->channel['title']);
		$this->assertSame('', $rss->_source_encoding);
	}

	public function testUtf16WithoutAByteOrderMarkIsDecodedWhateverTheHint()
	{
		$rss = $this->parse(iconv('UTF-8', 'UTF-16BE', '<?xml version="1.0" encoding="UTF-16"?><rss version="2.0"><channel><title>caf'."\xC3\xA9".'</title></channel></rss>'), 'utf-8', 'ISO-8859-1', false);

		$this->assertSame('', $rss->ERROR);
		$this->assertSame('café', $rss->channel['title']);
	}

	public function testAByteOrderMarkOutranksTheDeclaration()
	{
		$rss = $this->parse("\xEF\xBB\xBF".'<?xml version="1.0" encoding="ISO-8859-1"?><rss version="2.0"><channel><title>caf'."\xC3\xA9".'</title></channel></rss>');

		$this->assertSame('café', $rss->channel['title']);
		$this->assertSame('ISO-8859-1', $rss->_source_encoding);
	}

	public function testOnlyTheRealDeclarationNamesTheEncoding()
	{
		$rss = $this->fixture('rss20-minified-misleading-encoding');

		$this->assertSame('Café', $rss->items[0]['title']);
		$this->assertSame('', $rss->_source_encoding);
	}

	public function testTheOutputEncodingIsWrittenAndRecordedAsGiven()
	{
		$latin = $this->fixture('rss20-iso-8859-1', 'ISO-8859-1');
		$unnamed = $this->fixture('rss20-iso-8859-1', '');
		$unknown = $this->fixture('rss20-iso-8859-1', 'KOI8-R');

		$this->assertSame("Caf\xE9 Latin-1 \xA35 \xBC \xFF", $latin->channel['title']);
		$this->assertSame('ISO-8859-1', $latin->encoding);
		$this->assertSame('Café Latin-1 £5 ¼ ÿ', $unnamed->channel['title']);
		$this->assertSame('', $unnamed->encoding);
		$this->assertSame('Café Latin-1 £5 ¼ ÿ', $unknown->channel['title']);
		$this->assertSame('KOI8-R', $unknown->encoding);
		$this->assertNotSame('', $unknown->WARNING);
		$this->assertSame('', $unknown->ERROR);
	}

	public function testAnEncodingNothingCanDecodeIsAParseErrorWithNothingParsed()
	{
		$rss = $this->fixture('rss20-unknown-encoding-label');

		$this->assertNotSame('', $rss->ERROR);
		$this->assertNull($rss->feed_type);
		$this->assertSame(array(), $rss->channel);
		$this->assertSame('X-NO-SUCH-CHARSET', $rss->_source_encoding);
	}

	public function testKnownEncodingUpperCasesWhatItKnows()
	{
		$rss = $this->parse('<rss/>');

		$this->assertSame('UTF-8', $rss->known_encoding('utf-8'));
		$this->assertSame('US-ASCII', $rss->known_encoding('US-ASCII'));
		$this->assertSame('ISO-8859-1', $rss->known_encoding('iso-8859-1'));
		$this->assertFalse($rss->known_encoding('ISO-8859-15'));
		$this->assertFalse($rss->known_encoding('windows-1252'));
		$this->assertFalse($rss->known_encoding(''));
		$this->assertFalse($rss->known_encoding(null));
	}

	public function testAMalformedDocumentKeepsWhatCameBeforeTheError()
	{
		$rss = $this->fixture('malformed-mismatched-tag');

		$this->assertRegExp('/^\S.* at line 10, column \d+$/', $rss->ERROR);
		$this->assertSame(array(array('title' => 'Before the break')), $rss->items);
		$this->assertSame(array('title' => 'Mismatched'), $rss->current_item);
		$this->assertSame(array('title'), $rss->stack);
		$this->assertTrue($rss->initem);
		$this->assertSame('Desc', $rss->channel['tagline']);
	}

	public function testAnUndeclaredEntityIsAnError()
	{
		$broken = $this->fixture('malformed-undefined-entity');
		$html = $this->fixture('rss091-html-entity');

		$this->assertNotSame('', $broken->ERROR);
		$this->assertSame('Entity ', $broken->channel['title']);
		$this->assertNotSame('', $html->ERROR);
		$this->assertSame('HTML entityfeed', $html->channel['title']);
	}

	public function testErrorKeepsNoticesAsWarningsAndLogsWithoutDebug()
	{
		$log = tempnam(sys_get_temp_dir(), 'magpie');
		$previous = ini_set('error_log', $log);
		$rss = $this->parse('<rss/>');

		$rss->error('first notice', E_USER_NOTICE);
		$rss->error('plain notice', E_NOTICE);
		$rss->error('a warning');
		$written = file_get_contents($log);
		ini_set('error_log', $previous);
		unlink($log);

		$this->assertSame('plain notice', $rss->WARNING);
		$this->assertSame('a warning', $rss->ERROR);
		$this->assertStringContainsString('first notice', $written);
		$this->assertStringContainsString('a warning', $written);
	}

	public function testANonStringSourceIsCastWhenScalarAndRefusedOtherwise()
	{
		$refused = 'MagpieRSS can only parse a feed given as a string';
		$parsedShape = '/^\S.* at line \d+, column \d+$/';

		$this->assertSame($refused, $this->parse(array(self::$probe))->ERROR);
		$this->assertSame($refused, $this->parse(new stdClass())->ERROR);

		foreach(array(42, 1.5, true, null) as $scalar)
		{
			$rss = $this->parse($scalar);

			$this->assertRegExp($parsedShape, $rss->ERROR, var_export($scalar, true));
			$this->assertNull($rss->feed_type);
		}

		$this->assertSame($this->parse('42')->ERROR, $this->parse(42)->ERROR);
		$this->assertSame($this->parse('')->ERROR, $this->parse(null)->ERROR);
		$this->assertSame(array(), $this->raised);
	}

	public function testEntityExpansionPastTheAllowanceIsRefused()
	{
		$entity = str_repeat('x', 100000);
		$source = '<?xml version="1.0"?><!DOCTYPE r [<!ENTITY x "'.$entity.'">]><rss version="2.0"><channel><title>'
			.str_repeat('&x;', 20000).'</title><item><title>after</title></item></channel></rss>';

		$rss = $this->parse($source);

		$this->assertStringContainsString('Entity expansion', $rss->ERROR);
		$this->assertLessThanOrEqual(3 * strlen($source) + 65536, strlen($rss->channel['title']));
		$this->assertSame(array(), $rss->items);
	}

	public function testInternalEntitiesThatExpandModestlyKeepWorking()
	{
		$rss = $this->fixture('rss20-internal-subset');

		$this->assertSame('', $rss->ERROR);
		$this->assertSame('Example Site feed', $rss->channel['title']);
		$this->assertSame('Item at Example Site', $rss->items[0]['title']);
	}

	public function testExternalEntitiesAreNeverResolved()
	{
		$secret = tempnam(sys_get_temp_dir(), 'magpie');
		file_put_contents($secret, 'SECRET');
		$rss = $this->parse('<?xml version="1.0"?><!DOCTYPE r [<!ENTITY e SYSTEM "file://'.$secret.'">]><rss version="2.0"><channel><title>[&e;]</title></channel></rss>');
		unlink($secret);

		$this->assertSame('[]', $rss->channel['title']);
	}

	public function testTheWholeCorpusRaisesNothingUnderEveryVariant()
	{
		foreach(glob(codecept_data_dir().'magpie_rss/*.xml') as $file)
		{
			foreach(self::$variants as $name => $variant)
			{
				$this->fixture(basename($file, '.xml'), $variant[0], $variant[1], $variant[2]);
				$this->assertSame(array(), $this->raised, basename($file).' under '.$name);
			}
		}
	}

	public function testDrivingTheEventsByHandMatchesParsing()
	{
		$document = '<rss version="2.0"><channel><title>T</title><item><title>I</title><dc:creator>C</dc:creator></item></channel></rss>';
		$events = array(
			array('start', 'rss', array('version' => '2.0')),
			array('start', 'channel', array()),
			array('start', 'title', array()), array('text', 'T'), array('end', 'title'),
			array('start', 'item', array()),
			array('start', 'title', array()), array('text', 'I'), array('end', 'title'),
			array('start', 'dc:creator', array()), array('text', 'C'), array('end', 'dc:creator'),
			array('end', 'item'),
			array('end', 'channel'),
			array('end', 'rss'),
		);

		$this->assertSameResult($this->parse($document), $this->drive($events));

		$document = '<feed><entry><content type="xhtml"><div class="a&amp;b">x &lt; y</div></content><updated>2026-11-06T10:00:00Z</updated></entry></feed>';
		$events = array(
			array('start', 'feed', array()),
			array('start', 'entry', array()),
			array('start', 'content', array('type' => 'xhtml')),
			array('start', 'div', array('class' => 'a&b')), array('text', 'x '), array('text', '<'), array('text', ' y'), array('end', 'div'),
			array('end', 'content'),
			array('start', 'updated', array()), array('text', '2026-11-06T10:00:00Z'), array('end', 'updated'),
			array('end', 'entry'),
			array('end', 'feed'),
		);

		$this->assertSameResult($this->parse($document), $this->drive($events));
	}

	public function testParseW3cdtfReadsDateTimesAndHonoursTheirOffsets()
	{
		$cases = array(
			'2003-12-13T18:30:02Z'               => 1071340202,
			'2003-12-13T18:30:02+01:00'          => 1071336602,
			'2003-12-13T18:30:02-05:00'          => 1071358202,
			'2003-12-13T18:30Z'                  => 1071340200,
			'2003-12-13T18:30'                   => 1071340200,
			'2003-12-13T18:30:02.25Z'            => 1071340202,
			'2003-12-13T18:30:02'                => 1071340202,
			'2003-12-13T18:30:02+0130'           => 1071334802,
			'1999-01-01T00:00:00Z'               => 915148800,
			'2026-11-06T14:30:15+01:00'          => 1793971815,
			'prefix 2003-12-13T18:30:02Z suffix' => 1071340202,
		);

		foreach($cases as $input => $timestamp)
		{
			$this->assertSame($timestamp, $this->quietly(function () use ($input)
			{
				return parse_w3cdtf($input);
			}), $input);
		}

		$this->assertSame(array(), $this->raised);
	}

	public function testParseW3cdtfRefusesAnythingWithoutATime()
	{
		foreach(array('2003-12-13', 'garbage', '', '2003-12-13T18') as $input)
		{
			$this->assertSame(-1, parse_w3cdtf($input), $input);
		}

		$this->assertSame(-1, parse_w3cdtf(null));
		$this->assertSame(-1, parse_w3cdtf(array()));
	}

	public function testMapAttrsEscapesAmpersandsLessThanSignsAndQuotes()
	{
		$this->assertSame('href="https://example.test/"', map_attrs('href', 'https://example.test/'));
		$this->assertSame('alt="a &quot;quote&quot;"', map_attrs('alt', 'a "quote"'));
		$this->assertSame('x=""', map_attrs('x', ''));
		$this->assertSame('k="a &amp; b &lt;c>"', map_attrs('k', 'a & b <c>'));
	}

	public function testFeedsThatOnceThrewParseUnderEveryVariant()
	{
		foreach(array('rss10-rdf', 'atom03') as $fixture)
		{
			foreach(self::$variants as $name => $variant)
			{
				$this->assertCount(2, $this->fixture($fixture, $variant[0], $variant[1], $variant[2])->items, $fixture.' under '.$name);
			}
		}

		$unknown = $this->fixture('rss20-unknown-encoding-label');

		$this->assertRegExp('/ at line 1, column \d+$/', $unknown->ERROR);
		$this->assertSame(array(), $this->raised);
	}

	public function testDatesWithSecondsOrAnOffsetAreStamped()
	{
		$rdf = $this->fixture('rss10-rdf');
		$atom = $this->fixture('atom03');

		$this->assertSame(1793971815, $rdf->items[0]['date_timestamp']);
		$this->assertSame(1793869200, $rdf->items[1]['date_timestamp']);
		$this->assertSame(1793953800, $atom->items[0]['date_timestamp']);
		$this->assertArrayNotHasKey('date_timestamp', $this->fixture('rss10-rdf-undated')->items[0]);
	}

	public function testEightBitEncodingsDecodeTheSameOnEveryPhpVersion()
	{
		$koi = $this->fixture('rss20-koi8-r');
		$windows = $this->fixture('rss20-windows-1252');

		$this->assertSame('Новости', $koi->channel['title']);
		$this->assertSame('Привет мир', $koi->items[0]['title']);
		$this->assertSame('“Smart quotes” – €10 … ™', $windows->channel['title']);
		$this->assertSame('‘Single’ quotes', $windows->items[0]['title']);
	}

	public function testTheDocumentNamesItsEncodingAndTheHintOnlyFillsTheGap()
	{
		$undeclaredLatin = '<rss version="2.0"><channel><title>caf'."\xE9".'</title></channel></rss>';
		$declared = $this->fixture('rss20-iso-8859-1', 'utf-8', 'UTF-8', true);
		$undetected = $this->fixture('rss20-iso-8859-1', 'utf-8', 'UTF-8', false);
		$mislabelled = $this->fixture('rss20-declared-latin1-actually-utf8', 'utf-8', 'UTF-8', true);
		$marked = $this->fixture('rss20-utf8-bom', 'utf-8', 'ISO-8859-1', false);
		$hinted = $this->parse($undeclaredLatin, 'utf-8', 'ISO-8859-1', false);
		$unhinted = $this->parse($undeclaredLatin);

		$this->assertSame('', $declared->ERROR);
		$this->assertSame('Café Latin-1 £5 ¼ ÿ', $declared->channel['title']);
		$this->assertSame('Café Latin-1 £5 ¼ ÿ', $undetected->channel['title']);
		$this->assertSame('', $undetected->_source_encoding);
		$this->assertSame('Mislabelled cafÃ©', $mislabelled->channel['title']);
		$this->assertSame('BOM feed ✓', $marked->channel['title']);
		$this->assertSame('café', $hinted->channel['title']);
		$this->assertSame('', $hinted->ERROR);
		$this->assertNotSame('', $unhinted->ERROR);
		$this->assertSame('No prolog cafÃ©', $this->fixture('rss20-no-prolog', 'utf-8', 'ISO-8859-1', true)->channel['title']);
		$this->assertSame('Prolog without encoding, naÃ¯ve', $this->fixture('rss20-prolog-without-encoding', 'utf-8', 'ISO-8859-1', false)->channel['title']);
	}

	public function testDetectionRecordsTheDeclaredEncoding()
	{
		$this->assertSame('ISO-8859-1', $this->fixture('rss20-iso-8859-1')->_source_encoding);
		$this->assertSame('UTF-8', $this->fixture('atom03')->_source_encoding);
		$this->assertSame('WINDOWS-1252', $this->fixture('rss20-windows-1252')->_source_encoding);
		$this->assertSame('', $this->fixture('rss20-no-prolog')->_source_encoding);
		$this->assertSame('', $this->fixture('rss20-prolog-without-encoding')->_source_encoding);
		$this->assertSame('ISO-8859-1', $this->fixture('rss20-iso-8859-1', 'utf-8', 'UTF-8', true)->_source_encoding);
		$this->assertSame('', $this->fixture('rss20-iso-8859-1', 'utf-8', null, false)->_source_encoding);
		$this->assertArrayNotHasKey('source_encoding', get_object_vars($this->fixture('rss20-iso-8859-1')));
	}

	public function testAPrefixGovernsEverythingInsideItsElement()
	{
		$nested = $this->fixture('rss20-nested-unknown');
		$media = $this->fixture('rss20-namespaces');

		$this->assertSame(array('thing' => "\n        \n        \n      ", 'thing_sub' => 'namespaced sub', 'thing_plain' => 'plain in ns parent'), $nested->items[0]['ext']);
		$this->assertArrayNotHasKey('thing', $nested->items[0]);
		$this->assertArrayNotHasKey('thing_plain', $nested->items[0]);
		$this->assertSame('after namespaced', $nested->items[0]['after']);
		$this->assertSame(array('content' => "\n        \n      ", 'content_title' => 'Picture'), $media->items[0]['media']);
	}

	public function testContentEncodedSurvivesTheWhitespaceAfterAPrefixedChild()
	{
		$rss = $this->fixture('rss20-namespaces');

		$this->assertSame(array('encoded' => '<p>Full <strong>HTML</strong> content.</p>'), $rss->items[0]['content']);
		$this->assertSame('<p>Full <strong>HTML</strong> content.</p>', $rss->items[0]['atom_content']);
	}

	public function testAnIncompleteDocumentIsAnError()
	{
		$truncated = $this->fixture('malformed-truncated');

		$this->assertNotSame('', $truncated->ERROR);
		$this->assertSame(array(array('title' => 'Complete item')), $truncated->items);
		$this->assertSame('Desc', $truncated->channel['description']);
		$this->assertNotSame('', $this->fixture('empty-string')->ERROR);
		$this->assertNotSame('', $this->fixture('whitespace-only')->ERROR);
		$this->assertNotSame('', $this->fixture('not-xml-text')->ERROR);
	}

	public function testEveryEndTagClosesWhatItsOwnStartOpened()
	{
		$prefixed = $this->fixture('atom10-prefixed');
		$author = $this->fixture('atom10-author-link');

		$this->assertCount(2, $prefixed->items);
		$this->assertSame(array('title' => 'First prefixed entry', 'atom' => array('id' => 'p1')), $prefixed->items[0]);
		$this->assertSame('Prefixed feed', $prefixed->channel['title']);
		$this->assertSame('https://example.test/ann', $author->items[0]['link']);
		$this->assertSame('Ann', $author->items[0]['author_name']);
		$this->assertArrayNotHasKey('name', $author->items[0]);
	}

	public function testAnAtomLinkWithoutRelIsTheAlternateAndTheFirstLinkWins()
	{
		$rss = $this->fixture('atom10');

		$this->assertSame('https://example.test/', $rss->channel['link']);
		$this->assertSame('https://example.test/atom', $rss->channel['link_self']);
		$this->assertArrayNotHasKey('link_', $rss->channel);
		$this->assertSame('https://example.test/e1.mp3', $rss->items[0]['link_enclosure']);

		$late = $this->parse('<feed><entry><link rel="alternate" href="https://example.test/first"/><link href="https://example.test/second"/></entry></feed>');

		$this->assertSame('https://example.test/first', $late->items[0]['link']);
	}

	public function testCapturedMarkupEscapesAttributeValuesAndText()
	{
		$atom = $this->fixture('atom03');
		$undated = $this->fixture('atom03-undated');

		$this->assertSame('<div xmlns="http://www.w3.org/1999/xhtml"><p >Hello <b >bold</b> <a href="https://example.test/?a=1&amp;b=2">link</a><br ></br>line</p></div>', $atom->items[0]['atom_content']);
		$this->assertSame('<div xmlns="http://www.w3.org/1999/xhtml">Inline <i >xhtml</i> with <img src="https://example.test/i.png" alt="a &quot;quote&quot;"></img> image</div>', $undated->items[0]['atom_content']);

		$text = $this->parse('<feed><entry><content type="xhtml"><div>a &amp; b &lt; c &gt; d</div></content></entry></feed>');

		$this->assertSame('<div >a &amp; b &lt; c &gt; d</div>', $text->items[0]['atom_content']);
	}

	public function testAConstructNameInsideACaptureIsSerialisedLikeAnyElement()
	{
		$rss = $this->fixture('atom10-nested-construct');

		$this->assertSame('Outer title', $rss->items[0]['title']);
		$this->assertSame('<div ><title >Inner &amp; title</title><p >After <summary >inner summary</summary></p></div>', $rss->items[0]['atom_content']);
		$this->assertSame('Outer summary', $rss->items[0]['summary']);
	}

	public function testDebugModeRaisesUserErrorsAsWarnings()
	{
		$report = $this->inChild("define('MAGPIE_DEBUG', true); "
			."\$levels = array(); set_error_handler(function (\$no, \$str) use (&\$levels) { \$levels[] = array(\$no, \$str); return true; }); "
			."\$rss = new MagpieRSS(".var_export(self::$probe, true)."); "
			."\$rss->error('as error', E_USER_ERROR); \$rss->error('as notice', E_USER_NOTICE); \$rss->error('as warning'); "
			."\$report = array('levels' => \$levels, 'error' => \$rss->ERROR, 'warning' => \$rss->WARNING);");

		$this->assertSame(array(array(E_USER_WARNING, 'as error'), array(E_USER_NOTICE, 'as notice'), array(E_USER_WARNING, 'as warning')), $report['levels']);
		$this->assertSame('as warning', $report['error']);
		$this->assertSame('as notice', $report['warning']);
	}

	public function testAnErrorHandlerSetBeforeTheParseGetsWhatPhpGivesItAndNothingItsMaskLeavesOut()
	{
		$report = $this->inChild("define('MAGPIE_DEBUG', true); \$counts = array(); \$masked = 0; "
			."set_error_handler(function () use (&\$counts) { \$counts[] = func_num_args(); return true; }); "
			."new MagpieRSS('<rss><channel>'); "
			."set_error_handler(function () use (&\$masked) { \$masked++; return true; }, E_USER_NOTICE); "
			."new MagpieRSS('<rss><channel>'); "
			."\$report = array('counts' => \$counts, 'masked' => \$masked);", '-d display_errors=0');

		$this->assertSame(array(PHP_VERSION_ID < 80000 ? 5 : 4), $report['counts']);
		$this->assertSame(0, $report['masked']);
	}

	public function testWhatTheOverridesOfASubclassRaiseMeetsTheHandlerTheCallerChose()
	{
		$overrides = array(
			'feed_cdata' => 'public function feed_cdata($p, $text) { $this->probe(); parent::feed_cdata($p, $text); }',
			'append' => 'public function append($el, $text) { $this->probe(); parent::append($el, $text); }',
			'concat' => 'public function concat(&$str1, $str2 = "") { $this->probe(); parent::concat($str1, $str2); }',
			'append_content' => 'public function append_content($text) { $this->probe(); parent::append_content($text); }',
		);
		$classes = 'trait MagpieRSSProbe { public $calls = 0; private function probe() { $this->calls++; simplexml_load_string("<bad"); trigger_error("probe", E_USER_NOTICE); } } ';

		foreach($overrides as $method => $override)
		{
			$classes .= 'final class MagpieRSSProbing_'.$method.' extends MagpieRSS { use MagpieRSSProbe; '.$override.' } ';
		}

		$feed = '<feed xmlns="http://www.w3.org/2005/Atom"><title>t</title><entry><id>1</id><link href="https://example.test/"/><title>i</title><content type="xhtml"><p>x</p></content></entry></feed>';
		$report = $this->inChild($classes."error_reporting(E_ALL & ~E_USER_NOTICE); \$seen = array(); "
			."set_error_handler(function (\$level, \$message) use (&\$seen) { \$seen[] = \$level === E_WARNING && strpos(\$message, 'simplexml_load_string()') === 0 ? 'simplexml' : \$message; return true; }, E_WARNING); "
			."simplexml_load_string('<bad'); \$report = array('each' => count(\$seen)); "
			."foreach(".var_export(array_keys($overrides), true)." as \$method) { foreach(array('ignoring' => false, 'collecting' => true) as \$setting => \$collect) { "
			."libxml_use_internal_errors(\$collect); \$seen = array(); \$class = 'MagpieRSSProbing_'.\$method; \$rss = new \$class(".var_export($feed, true)."); "
			."\$report[\$method][\$setting] = array('calls' => \$rss->calls, 'seen' => \$seen, 'collecting' => libxml_use_internal_errors(), 'content' => \$rss->items[0]['atom_content']); } }");

		$this->assertGreaterThan(0, $report['each']);

		foreach(array_keys($overrides) as $method)
		{
			foreach(array('ignoring' => false, 'collecting' => true) as $setting => $collecting)
			{
				$this->assertGreaterThan(0, $report[$method][$setting]['calls'], $method.', '.$setting);
				$this->assertSame(array_fill(0, $report[$method][$setting]['calls'] * $report['each'], 'simplexml'), $report[$method][$setting]['seen'], $method.', '.$setting);
				$this->assertSame($collecting, $report[$method][$setting]['collecting'], $method.', '.$setting);
				$this->assertSame('<p >x</p>', $report[$method][$setting]['content'], $method.', '.$setting);
			}
		}
	}

	public function testHandlersASubclassSetsForProcessingInstructionsSeeEveryInstruction()
	{
		$report = $this->inChild("final class MagpieRSSWithInstructionHandler extends MagpieRSS { public \$seen = array(); public function create_parser(\$source, \$out_enc, \$in_enc, \$detect) { \$made = parent::create_parser(\$source, \$out_enc, \$in_enc, \$detect); "
			."xml_set_processing_instruction_handler(\$made[0], function (\$p, \$target, \$data) { \$this->seen[] = \$target.' '.\$data; }); return \$made; } } "
			."final class MagpieRSSWithDefaultHandler extends MagpieRSS { public \$seen = array(); public function create_parser(\$source, \$out_enc, \$in_enc, \$detect) { \$made = parent::create_parser(\$source, \$out_enc, \$in_enc, \$detect); "
			."xml_set_default_handler(\$made[0], function (\$p, \$data) { if(strncmp(\$data, '<?', 2) === 0) { \$this->seen[] = \$data; } }); return \$made; } } "
			."\$source = ".var_export($this->rss('<item><title>i</title><?p one?><?q two?></item>'), true)."; "
			."\$instructions = new MagpieRSSWithInstructionHandler(\$source); \$defaults = new MagpieRSSWithDefaultHandler(\$source); "
			."\$report = array('instructions' => \$instructions->seen, 'defaults' => \$defaults->seen, 'errors' => \$instructions->ERROR.\$defaults->ERROR, 'items' => count(\$instructions->items) + count(\$defaults->items));");

		$this->assertSame(array('p one', 'q two'), $report['instructions']);
		$this->assertSame(array('<?p one?>', '<?q two?>'), $report['defaults']);
		$this->assertSame('', $report['errors']);
		$this->assertSame(2, $report['items']);
	}

	public function testTheEventMethodsASubclassLeavesAloneAreCalledStraightFromTheParser()
	{
		$report = $this->inChild("final class MagpieRSSTracingAppend extends MagpieRSS { public \$callers = array(); public function append(\$el, \$text) { \$frames = array_map(function (\$frame) { return \$frame['function']; }, debug_backtrace()); \$this->callers[] = \$frames[array_search('feed_cdata', \$frames, true) + 1]; parent::append(\$el, \$text); } } "
			."\$rss = new MagpieRSSTracingAppend(".var_export(self::$probe, true)."); "
			."\$report = array('callers' => array_unique(\$rss->callers), 'title' => \$rss->channel['title']);");

		$this->assertSame(array('xml_parse'), $report['callers']);
		$this->assertSame('t', $report['title']);
	}

	public function testASubclassThatDeclaresOnlyAppendOrConcatIsHandedEveryPieceOfText()
	{
		$overrides = array(
			'append' => 'public function append($el, $text) { $this->pieces[] = $text; parent::append($el, $text); }',
			'concat' => 'public function concat(&$str1, $str2 = "") { $this->pieces[] = $str2; parent::concat($str1, $str2); }',
		);
		$classes = '';

		foreach($overrides as $method => $override)
		{
			$classes .= 'final class MagpieRSSPieces_'.$method.' extends MagpieRSS { public $pieces = array(); '.$override.' } ';
		}

		$report = $this->inChild($classes."\$report = array(); foreach(".var_export(array_keys($overrides), true)." as \$method) { \$class = 'MagpieRSSPieces_'.\$method; "
			."\$rss = new \$class('<rss version=\"2.0\"><channel><item><title>A &amp; B</title></item></channel></rss>'); \$report[\$method] = array(\$rss->pieces, \$rss->items[0]['title']); }");

		foreach(array_keys($overrides) as $method)
		{
			$this->assertSame(array(array('A ', '&', ' B'), 'A & B'), $report[$method], $method);
		}
	}

	public function testAMissingXmlExtensionIsAnErrorRatherThanAFatal()
	{
		$report = $this->inChild("\$rss = new MagpieRSS(".var_export(self::$probe, true)."); "
			."\$report = array('error' => \$rss->ERROR, 'type' => \$rss->feed_type, 'channel' => \$rss->channel);", '-d disable_functions=xml_parser_create');

		$this->assertSame('MagpieRSS needs the PHP xml extension, which is not loaded', $report['error']);
		$this->assertNull($report['type']);
		$this->assertSame(array(), $report['channel']);
	}

	public function testAtom10IsNormalisedOntoTheAtom03Keys()
	{
		$rss = $this->fixture('atom10');

		$this->assertSame('1.0', $rss->feed_version);
		$this->assertSame('1.0', $rss->is_atom());
		$this->assertSame('Atom 1.0 subtitle', $rss->channel['tagline']);
		$this->assertSame('Atom 1.0 subtitle', $rss->channel['description']);
		$this->assertSame('Rights statement', $rss->channel['rights']);
		$this->assertArrayNotHasKey('copyright', $rss->channel);
		$this->assertArrayNotHasKey('lastbuilddate', $rss->channel);
		$this->assertSame('Entry summary text', $rss->items[0]['description']);
		$this->assertSame(array('encoded' => '<p>HTML <em>content</em></p>'), $rss->items[0]['content']);
		$this->assertSame(1793953800, $rss->items[0]['date_timestamp']);
		$this->assertSame(1793890800, $rss->items[1]['date_timestamp']);

		$bare = $this->fixture('atom10-content-src');

		$this->assertNull($bare->channel['description']);
		$this->assertArrayNotHasKey('tagline', $bare->channel);
		$this->assertArrayNotHasKey('atom_content', $bare->items[0]);
		$this->assertSame(1793491200, $bare->items[0]['date_timestamp']);
	}

	public function testRssWithoutAVersionIsNormalised()
	{
		$rss = $this->fixture('rss-without-version');

		$this->assertNull($rss->feed_version);
		$this->assertNull($rss->is_rss());
		$this->assertSame('Desc', $rss->channel['tagline']);

		$dated = $this->parse('<rss><channel><item><description>D</description><pubDate>Fri, 06 Nov 2026 08:30:00 GMT</pubDate></item></channel></rss>');

		$this->assertSame('D', $dated->items[0]['summary']);
		$this->assertSame(1793953800, $dated->items[0]['date_timestamp']);
	}

	public function testNormaliseCanRunAgainWithoutChangingTheResult()
	{
		foreach(array('rss20-basic', 'atom03', 'atom10', 'rss10-rdf') as $fixture)
		{
			$rss = $this->fixture($fixture);
			$before = get_object_vars($rss);
			$rss->normalize();

			$this->assertSame($before, get_object_vars($rss), $fixture);
		}
	}

	public function testTextOutsideEveryContainerIsDiscarded()
	{
		$rss = $this->parse('<rss version="2.0"><channel><title>T</title></channel><title>Stray</title><x:note>stray</x:note></rss>');
		$rdf = $this->parse('<rdf:RDF><channel><title>R</title></channel><item><title>I</title></item>'
			.'<rdf:Description><dc:rights>r</dc:rights><title>D</title></rdf:Description><cc:License><cc:permits>p</cc:permits></cc:License></rdf:RDF>');

		$this->assertSame(array('title' => 'T', 'tagline' => null), $rss->channel);
		$this->assertSame(array('title' => 'R', 'tagline' => null), $rdf->channel);
		$this->assertSame(array(array('title' => 'I')), $rdf->items);
		$this->assertSame(array(), $rdf->image);
		$this->assertSame(array(), $rdf->textinput);
	}

	public function testAppendContentWithNeitherAnItemNorTheChannelOpenAddsNothing()
	{
		$rss = $this->parse('');
		$rss->feed_start_element(null, 'rss', array('version' => '2.0'));
		$rss->incontent = 'title';
		$rss->append_content('stray');

		$this->assertSame(array(), $rss->channel);
		$this->assertSame(array(), $rss->current_item);
	}

	public function testAContainerStartsAFreshKeyPath()
	{
		$rss = $this->parse('<rss version="2.0"><channel><wrap><item><title>T</title></item></wrap><x:wrap><item><title>P</title></item></x:wrap></channel></rss>');

		$this->assertSame(array(array('title' => 'T'), array('title' => 'P')), $rss->items);
		$this->assertArrayNotHasKey('x', $rss->channel);
	}

	public function testPrefixedElementsInsideACaptureCloseWithTheirPrefix()
	{
		$rss = $this->parse('<feed><entry><content type="xhtml"><xhtml:div xmlns:xhtml="http://www.w3.org/1999/xhtml"><xhtml:p>a</xhtml:p><xhtml:br/></xhtml:div></content></entry></feed>');

		$this->assertSame('<xhtml:div xmlns:xhtml="http://www.w3.org/1999/xhtml"><xhtml:p >a</xhtml:p><xhtml:br ></xhtml:br></xhtml:div>', $rss->items[0]['atom_content']);
	}

	public function testALinkWithNoOrAnEmptyHrefAddsNothingAndTheNextRealLinkWins()
	{
		$rss = $this->parse('<feed><entry><link rel="alternate" href=""/><link rel="alternate" href="https://example.test/real"/><link rel="self"/><link href=""/></entry></feed>');

		$this->assertSame(array('link' => 'https://example.test/real'), $rss->items[0]);
	}

	public function testAWhitespaceOnlySubtitleFillsNeitherTaglineNorDescription()
	{
		$rss = $this->parse("<feed><subtitle type=\"xhtml\">\n  <div xmlns=\"http://www.w3.org/1999/xhtml\">Sub</div>\n</subtitle></feed>");

		$this->assertSame("\n  \n", $rss->channel['subtitle']);
		$this->assertSame('Sub', $rss->channel['subtitle_div']);
		$this->assertArrayNotHasKey('tagline', $rss->channel);
		$this->assertNull($rss->channel['description']);
	}

	public function testOverridesOfAppendConcatAndTheEventsSeeEveryTextRunAndLink()
	{
		$rss = new MagpieRSSTestDouble('<rss version="2.0"><channel><title>A &amp; B</title><item><dc:creator>C</dc:creator></item></channel></rss>');

		$this->assertSame(array('A ', '&', ' B', 'C'), $rss->runs);
		$this->assertSame(array(array(false, 'title', 'A '), array(false, 'title', '&'), array(false, 'title', ' B'), array('dc', 'creator', 'C')), $rss->appended);
		$this->assertSame(array('A ', '&', ' B', 'C'), $rss->concatenated);
		$this->assertSame('A & B', $rss->channel['title']);

		$atom = new MagpieRSSTestDouble('<feed><entry><link href="https://example.test/a"/><link rel="self" href="https://example.test/s"/><link href="https://example.test/late"/><title>T</title></entry></feed>');

		$this->assertSame(array(array(false, 'link', 'https://example.test/a'), array(false, 'link_self', 'https://example.test/s')), $atom->appended);
		$this->assertSame(array('https://example.test/a', 'https://example.test/s', 'T'), $atom->concatenated);
		$this->assertSame(array('link' => 'https://example.test/a', 'link_self' => 'https://example.test/s', 'title' => 'T'), $atom->items[0]);
	}

	public function testConcatTreatsUnsetAndNullAsEmpty()
	{
		$rss = $this->parse(self::$probe);
		$target = array('null' => null);

		$rss->concat($target['unset'], 'u');
		$rss->concat($target['null'], 'n');
		$rss->concat($target['unset'], 'v');

		$this->assertSame(array('null' => 'n', 'unset' => 'uv'), $target);
	}

	public function testAppendAndAppendContentLeaveAKeyHoldingAnArrayAlone()
	{
		$rss = $this->quietly(function ()
		{
			return new MagpieRSSTestDouble('');
		});
		$rss->feed_start_element(null, 'rss', array());
		$rss->feed_start_element(null, 'channel', array());
		$rss->channel['list'] = array('kept');
		$rss->append('list', 'dropped');
		$rss->incontent = 'list';
		$rss->append_content('dropped');

		$this->assertSame(array('kept'), $rss->channel['list']);
		$this->assertSame(array(), $rss->concatenated);
	}

	public function testAppendIgnoresAnEmptyKeyAndAppendContentIgnoresTextOutsideAConstruct()
	{
		$rss = $this->parse('');
		$rss->feed_start_element(null, 'rss', array());
		$rss->feed_start_element(null, 'channel', array());
		$rss->append('', 'nothing');
		$rss->append(null, 'nothing');
		$rss->append_content('nothing');
		$rss->append('kept', 'yes');

		$this->assertSame(array('kept' => 'yes'), $rss->channel);
	}

	public function testCreateParserReturnsTheParserAndBytesAndRecordsTheEncodings()
	{
		$latin = '<?xml version="1.0" encoding="ISO-8859-1"?>'.self::$probe;
		$rss = $this->parse(self::$probe);

		$created = $rss->create_parser($latin, 'ISO-8859-1', null, true);

		$this->assertCount(2, $created);
		$this->assertTrue($this->isXmlParser($created[0]));
		$this->assertSame($latin, $created[1]);
		$this->assertSame('ISO-8859-1', $rss->encoding);
		$this->assertSame('ISO-8859-1', $rss->_source_encoding);

		$unnamed = $this->parse(self::$probe, '');
		$unnamed->create_parser(self::$probe, '', null, true);

		$this->assertSame('', $unnamed->encoding);
	}

	public function testPhp4CreateParserSaysWhatTheDocumentDoesNotAndRecordsOnlyDetectedLabels()
	{
		$latin = '<?xml version="1.0" encoding="ISO-8859-1"?>'.self::$probe;
		$detected = $this->parse(self::$probe);
		$undetected = $this->parse(self::$probe);

		list($parser, $bytes) = $detected->php4_create_parser($latin, 'UTF-8', true);

		$this->assertTrue($this->isXmlParser($parser));
		$this->assertSame($latin, $bytes);
		$this->assertSame('ISO-8859-1', $detected->_source_encoding);

		list(, $bytes) = $undetected->php4_create_parser($latin, null, false);

		$this->assertSame($latin, $bytes);
		$this->assertSame('', $undetected->_source_encoding);

		list(, $bytes) = $undetected->php4_create_parser(self::$probe, 'ISO-8859-1', false);

		$this->assertSame($latin, $bytes);

		list(, $bytes) = $undetected->php4_create_parser("\xEF\xBB\xBF".'<?xml version="1.0" encoding="ISO-8859-1"?>'.self::$probe, null, false);

		$this->assertSame('<?xml version="1.0" encoding="UTF-8"?>'.self::$probe, $bytes);
	}

	public function testPhp5CreateParserBuildsAParserAndTouchesNoProperty()
	{
		$rss = $this->parse(self::$probe);
		$before = get_object_vars($rss);

		$this->assertTrue($this->isXmlParser($rss->php5_create_parser(null, true)));
		$this->assertTrue($this->isXmlParser($rss->php5_create_parser('ISO-8859-1', false)));
		$this->assertTrue($this->isXmlParser($rss->php5_create_parser('KOI8-R', false)));
		$this->assertSame($before, get_object_vars($rss));
	}

	public function testEntitiesInAttributeValuesCountTowardsTheAllowance()
	{
		$source = $this->rdf(str_repeat('<item rdf:about="'.str_repeat('&a;', 200).'"><title>i</title></item>', 20), $this->entity('a', 1000));

		$rss = $this->parse($source);

		$this->assertNotSame('', $rss->ERROR);
		$this->assertLessThan(10 * strlen($source) + 65536, $this->stored($rss));
		$this->assertSame(array(), $this->raised);
	}

	public function testAttributeValuesAndTextThatEntitiesLengthenModestlyAreKept()
	{
		$rss = $this->parse($this->rss('<item rdf:about="'.str_repeat('&a;', 100).'"><title>i</title><description>'.str_repeat('&a;', 100).'</description></item>', '<!ENTITY a "0123456789">'));

		$this->assertSame('', $rss->ERROR);
		$this->assertSame(str_repeat('0123456789', 100), $rss->items[0]['about']);
		$this->assertSame(str_repeat('0123456789', 100), $rss->items[0]['description']);
	}

	public function testAStartTagWith256ValuesThatEntitiesLengthenModestlyIsKept()
	{
		$rss = $this->parse($this->rss('<item'.$this->attributes(256, '="'.str_repeat('&a;', 100).'"').'><title>i</title></item>', '<!ENTITY a "0123456789">'));

		$this->assertSame('', $rss->ERROR);
		$this->assertCount(1, $rss->items);
	}

	public function testACharacterReferenceThatYieldsAnotherIsNoEntityReference()
	{
		$rss = $this->parse($this->rss('<item a="'.str_repeat('&q;', 100).'"><title>i</title><description>'.str_repeat('&q;', 100).'</description></item>', '<!ENTITY q "&#38;#38;&#72;&#105;">'));

		$this->assertSame('', $rss->ERROR);
		$this->assertCount(1, $rss->items);
	}

	public function testAnUndeclaredEntityInTextBehindAParameterEntityReferenceStopsNothingMore()
	{
		$subset = '<!ENTITY % lat1 SYSTEM "lat1.ent"> %lat1;';
		$referenced = $this->parse($this->rss($this->item('Caf&eacute;').'<item><title>j</title></item>', $subset));
		$plain = $this->parse($this->rss($this->item('Caf').'<item><title>j</title></item>', $subset));

		$this->assertStringStartsNotWith('Entity expansion', $referenced->ERROR);
		$this->assertCount(count($plain->items), $referenced->items);
		$this->assertSame(array(), $this->raised);
	}

	public function testALatin1DocumentIsWeighedAtTheLengthsItDelivers()
	{
		$rss = $this->parse('<?xml version="1.0" encoding="ISO-8859-1"?>'.$this->rss($this->plainItems(2000).'<item a="'.str_repeat('&a;', 300).'"><title>last</title></item>', $this->entity('a', 100)));

		$this->assertSame('', $rss->ERROR);
		$this->assertCount(2001, $rss->items);
	}

	public function testElementsNestedDeeperThan256AreRefused()
	{
		$source = $this->rss('<item><title>i</title>'.$this->chain(254, 1, '').'</item>');

		$rss = $this->parse($source);

		$this->assertRefused($rss, 'Elements nest deeper than 256', $source);
		$this->assertRegExp('/^Elements nest deeper than 256 at line \d+, column \d+$/', $rss->ERROR);
		$this->assertSame(array(), $rss->items);
	}

	public function testElementsNested256DeepAreKept()
	{
		$rss = $this->parse($this->rss('<item><title>i</title>'.$this->chain(253, 1, '').'</item>'));

		$this->assertSame('', $rss->ERROR);
		$this->assertCount(1, $rss->items);
	}

	public function testAtomContentNested250DeepIsKept()
	{
		$rss = $this->parse($this->atom(str_repeat('<div>', 250).'x'.str_repeat('</div>', 250)));

		$this->assertSame('', $rss->ERROR);
		$this->assertSame(250, substr_count($rss->items[0]['atom_content'], '<div'));
	}

	public function testAKeyLongerThan1024BytesIsRefused()
	{
		$source = $this->rss('<item><title>i</title>'.$this->chain(10, 2000, 'x').'</item>');

		$rss = $this->parse($source);

		$this->assertRefused($rss, 'An element path is longer than 1024 bytes', $source);
		$this->assertRegExp('/^An element path is longer than 1024 bytes at line \d+, column \d+$/', $rss->ERROR);
	}

	public function testAPrefixCountsTowardsTheLengthOfItsKey()
	{
		$prefix = str_repeat('p', 1100);
		$source = $this->rss('<item><title>i</title><'.$prefix.':x>'.str_repeat('&lt;', 1000).'</'.$prefix.':x></item>');

		$this->assertRefused($this->parse($source), 'An element path is longer than 1024 bytes', $source);
	}

	public function testAKeyOf1025BytesWithItsPrefixIsRefused()
	{
		$key = str_repeat('k', 1024);
		$source = $this->rss('<item><title>i</title><p:'.$key.'>v</p:'.$key.'></item>');

		$this->assertRefused($this->parse($source), 'An element path is longer than 1024 bytes', $source);
	}

	public function testAKeyOf1024BytesWithItsPrefixIsKept()
	{
		$key = str_repeat('k', 1023);

		$rss = $this->parse($this->rss('<item><title>i</title><p:'.$key.'>v</p:'.$key.'></item>'));

		$this->assertSame('', $rss->ERROR);
		$this->assertSame('v', $rss->items[0]['p'][$key]);
	}

	public function testARefusedKeyLeavesNoArrayForItsPrefix()
	{
		$name = 'dc:'.str_repeat('k', 1100);
		$source = $this->rss('<'.$name.'>x</'.$name.'>');

		$rss = $this->parse($source);

		$this->assertRefused($rss, 'An element path is longer than 1024 bytes', $source);
		$this->assertArrayNotHasKey('dc', $rss->channel);
	}

	public function testKeysThatOutgrowTheDocumentAreRefused()
	{
		$source = $this->rss(str_repeat('<item><title>i</title>'.$this->chain(200, 3, 'x').'</item>', 40));

		$rss = $this->parse($source);

		$this->assertRefused($rss, 'Element paths outgrew the document', $source);
		$this->assertRegExp('/^Element paths outgrew the document at line \d+, column \d+$/', $rss->ERROR);
		$this->assertLessThan(40, count($rss->items));
	}

	public function testTextFiledAgainUnderAKeyAddsNothingToTheVolume()
	{
		$rows = str_repeat('<tr>'.str_repeat("\n<td>0</td>", 5)."\n</tr>", 2000);

		$rss = $this->parse($this->rss('<item><title>i</title><body><div><div><table><tbody>'.$rows.'</tbody></table></div></div></body></item>'));

		$this->assertSame('', $rss->ERROR);
		$this->assertSame(str_repeat('0', 10000), $rss->items[0]['body_div_div_table_tbody_tr_td']);
	}

	public function testDeeplyIndentedEncodedContentIsKept()
	{
		$tags = array('div', 'section', 'article', 'span', 'em', 'p', 'blockquote', 'ul', 'li', 'strong');
		$items = '';

		for($n = 1; $n <= 200; $n++)
		{
			$open = '';
			$close = '';

			for($level = 0; $level < 60; $level++)
			{
				$open .= "\n".str_repeat("\t", $level).'<'.$tags[$level % 10].'>';
				$close = ($level === 59 ? '' : "\n".str_repeat("\t", $level)).'</'.$tags[$level % 10].'>'.$close;
			}

			$items .= '<item><title>Item '.$n.'</title><content:encoded>'.$open.'Body text '.$n.$close.'</content:encoded></item>'."\n";
		}

		$rss = $this->parse('<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/"><channel><title>t</title><link>https://example.test/</link>'.$items.'</channel></rss>');

		$this->assertSame('', $rss->ERROR);
		$this->assertCount(200, $rss->items);
	}

	public function testAStartTagWithMoreThan256AttributesIsRefusedBeforeItIsRead()
	{
		$source = $this->rss('<item'.$this->attributes(300, '=">"').'><title>i</title></item>');

		$rss = $this->parse($source);

		$this->assertRefused($rss, 'An element carries more than 256 attributes', $source);
		$this->assertSame(array(), $rss->items);
		$this->assertSame('t', $rss->channel['title']);
	}

	public function testAttributesAreCountedInAUtf16Document()
	{
		$source = $this->utf16($this->rss('<item'.$this->attributes(300, '=""').'><title>i</title></item>'));

		$rss = $this->parse($source);

		$this->assertRefused($rss, 'An element carries more than 256 attributes', $source);
		$this->assertSame(array(), $rss->items);
	}

	public function testAttributesAreCountedInAUtf16DocumentWithoutAByteOrderMark()
	{
		$source = (string) substr($this->utf16($this->rss('<item'.$this->attributes(300, '=""').'><title>i</title></item>')), 2);

		$rss = $this->parse($source);

		$this->assertRefused($rss, 'An element carries more than 256 attributes', $source);
		$this->assertSame(array(), $rss->items);
	}

	public function testAttributesAreCountedInAnEbcdicDocument()
	{
		$source = $this->ebcdic('IBM037', $this->crowded(300));

		$rss = $this->parse($source);

		$this->assertRefused($rss, 'An element carries more than 256 attributes', $source);
		$this->assertSame(array(), $rss->items);
	}

	public function testAttributesAreCountedInAnEbcdicDocumentWhateverItsCodePage()
	{
		$source = $this->ebcdic('IBM1047', $this->crowded(300));

		$rss = $this->parse($source);

		$this->assertRefused($rss, 'An element carries more than 256 attributes', $source);
		$this->assertSame(array(), $rss->items);
	}

	public function testAUtf16DocumentIsRefusedWhenNothingCanConvertIt()
	{
		$this->assertRefusedWithAndWithoutConverters($this->utf16($this->crowded(300)), array('iconv', 'mb_convert_encoding'));
	}

	public function testAUtf16DocumentWithoutAByteOrderMarkIsRefusedWhenNothingCanConvertIt()
	{
		$this->assertRefusedWithAndWithoutConverters((string) substr($this->utf16($this->crowded(300)), 2), array('iconv', 'mb_convert_encoding'));
	}

	public function testAUtf16DocumentWhoseBytesTheConverterRejectsIsRefused()
	{
		$this->assertRefusedWithAndWithoutConverters($this->loneSurrogate($this->utf16($this->crowded(300))), array('mb_convert_encoding'));
	}

	public function testAttributesAreCountedWhateverTheirQuotesAndSpacing()
	{
		$source = $this->rss('<item'.$this->attributes(300, " =\n'x'").'><title>i</title></item>');

		$rss = $this->parse($source);

		$this->assertRefused($rss, 'An element carries more than 256 attributes', $source);
		$this->assertSame(array(), $rss->items);
	}

	public function testAttributesAreCountedPastOneLibxmlCannotRead()
	{
		foreach($this->unreadableOpenings() as $opening)
		{
			$source = $this->crowded(300, $opening);

			$rss = $this->parse($source);

			$this->assertRefused($rss, 'An element carries more than 256 attributes', $source);
			$this->assertSame(array(), $rss->items, $opening);
		}
	}

	public function testWhatAQuotedValueHoldsIsNoAttributes()
	{
		$rss = $this->parse($this->crowded(300, '<item k="', "=''", '"'));

		$this->assertSame('', $rss->ERROR);
		$this->assertCount(2, $rss->items);
	}

	public function testATagLibxmlStopsInBeforeTheLimitIsLeftToLibxml()
	{
		foreach(array($this->crowded(300, '<item k="<', "=''", '"'), $this->crowded(255, '<item k')) as $source)
		{
			$rss = $this->parse($source);

			$this->assertNotSame('', $rss->ERROR);
			$this->assertStringStartsNotWith('An element carries', $rss->ERROR);
			$this->assertSame(array(), $rss->items);
			$this->assertSame(array(), $this->raised);
		}
	}

	public function testStartTagsWith256AttributesAreKept()
	{
		$rss = $this->parse($this->rss(str_repeat('<item'.$this->attributes(256, '=""').'><title>i</title></item>', 50)));

		$this->assertSame('', $rss->ERROR);
		$this->assertCount(50, $rss->items);
	}

	public function testEqualsSignsInACdataSectionAreNoAttributes()
	{
		$rss = $this->parse($this->rss($this->item('<![CDATA[count<limit '.str_repeat("\n".str_repeat('=', 72), 14).']]>')));

		$this->assertSame('', $rss->ERROR);
		$this->assertCount(1, $rss->items);
	}

	public function testMarkupWrittenWithCharacterReferencesIsText()
	{
		$markup = $this->attributes(300, "=''");

		$rss = $this->parse($this->rss($this->item('&e;'), '<!ENTITY e "&#60;b'.$markup.'/>">'));

		$this->assertSame('', $rss->ERROR);
		$this->assertSame('<b'.$markup.'/>', $rss->items[0]['description']);
	}

	public function testALabelLibxmlReadsAsLatin1IsDecoded()
	{
		$rss = $this->parse($this->labelled('ISO-LATIN-1', "Caf\xE9"));

		$this->assertSame('', $rss->ERROR);
		$this->assertCount(2, $rss->items);
		$this->assertSame("Caf\xC3\xA9", $rss->items[0]['title']);
	}

	public function testALabelLibxmlReadsAsLatin1IsDecodedWithoutConverters()
	{
		$this->assertTitledWithoutConverters($this->labelled('ISO-LATIN-1', "Caf\xE9"), "Caf\xC3\xA9");
	}

	public function testLibxmlsOwnNameForUtf8IsReadWithoutConverters()
	{
		$this->assertTitledWithoutConverters($this->labelled('UTF8', 'Cafe'), 'Cafe');
	}

	public function testLibxmlsOwnNameForAsciiIsReadWithoutConverters()
	{
		$this->assertTitledWithoutConverters($this->labelled('ASCII', 'Cafe'), 'Cafe');
	}

	public function testADeclarationOverSeveralLinesIsRead()
	{
		$source = "<?xml\n\tversion=\"1.0\"\n\tencoding=\"ISO-8859-1\"\n\tstandalone=\"yes\"\n?>".$this->rss("<item><title>Caf\xE9</title></item><item><title>two</title></item>");

		$this->assertTitledWithoutConverters($source, "Caf\xC3\xA9");
	}

	public function testTagLikeRunsInACommentAreNoAttributes()
	{
		$rss = $this->parse($this->rss('<!-- '.$this->twoTags(300).' --><item><title>i</title></item><item><title>j</title></item>'));

		$this->assertSame('', $rss->ERROR);
		$this->assertCount(2, $rss->items);
	}

	public function testTagLikeRunsInACdataSectionAreNoAttributes()
	{
		$rss = $this->parse($this->rss($this->item('<![CDATA['.$this->twoTags(300).']]>').'<item><title>j</title></item>'));

		$this->assertSame('', $rss->ERROR);
		$this->assertCount(2, $rss->items);
		$this->assertSame($this->twoTags(300), $rss->items[0]['description']);
	}

	public function testTagLikeRunsInAProcessingInstructionAreNoAttributes()
	{
		$rss = $this->parse($this->rss('<?x '.$this->twoTags(300).' ?><item><title>i</title></item><item><title>j</title></item>'));

		$this->assertSame('', $rss->ERROR);
		$this->assertCount(2, $rss->items);
	}

	public function testMoreThan4096DistinctNamesAreRefused()
	{
		$source = $this->rss($this->item($this->names(20000)));

		$rss = $this->parse($source);

		$this->assertRefused($rss, 'The document uses more than 4096 distinct names', $source);
		$this->assertSame(array(), $rss->items);
	}

	public function testEightHundredThousandDistinctNamesAreRefusedQuickly()
	{
		$this->assertRefusedQuickly($this->rss($this->item($this->names(800000))), 'The document uses more than 4096 distinct names');
	}

	public function testAttributeNamesCountAsDistinctNames()
	{
		$elements = '';

		for($element = 0; $element < 3200; $element++)
		{
			$elements .= '<x';

			for($attribute = 0; $attribute < 250; $attribute++)
			{
				$elements .= ' '.$this->fiveLetterName(250 * $element + $attribute).'=""';
			}

			$elements .= '/>';
		}

		$this->assertRefusedQuickly($this->rss($this->item($elements)), 'The document uses more than 4096 distinct names');
	}

	public function testDistinctNamesAreCountedInAUtf16Document()
	{
		$source = $this->utf16($this->rss($this->item($this->names(20000))));

		$this->assertRefused($this->parse($source), 'The document uses more than 4096 distinct names', $source);
	}

	public function testFourThousandDistinctNamesAreKept()
	{
		$rss = $this->parse($this->rss($this->item($this->names(4000))));

		$this->assertSame('', $rss->ERROR);
		$this->assertCount(1, $rss->items);
	}

	public function testNamesTheInternalSubsetDeclaresCountAsDistinctNames()
	{
		$source = $this->rss($this->item($this->names(1200)), $this->repeated(3000, '<!ENTITY e%d "x">'));

		$this->assertRefused($this->parse($source), 'The document uses more than 4096 distinct names', $source);
	}

	public function testProcessingInstructionTargetsCountAsDistinctNames()
	{
		$source = $this->rss($this->item($this->repeated(5000, '<?p%d?>')));

		$this->assertRefused($this->parse($source), 'The document uses more than 4096 distinct names', $source);
	}

	public function testProcessingInstructionTargetsBeforeTheRootElementCountAsDistinctNames()
	{
		$kept = $this->parse($this->repeated(4000, '<?p%d?>').$this->rss($this->item('x')));
		$source = '<!-- c -->'.$this->repeated(5000, '<?p%d?>').$this->rss($this->item('x'), '<!ENTITY z "z">');

		$this->assertSame(array('', 1), array($kept->ERROR, count($kept->items)));
		$this->assertRefused($this->parse($source), 'The document uses more than 4096 distinct names', $source);
	}

	public function testElementEventsWithAnEmptyNameRaiseNothing()
	{
		$rss = $this->quietly(function ()
		{
			return $this->drive(array(array('start', 'rss', array('version' => '2.0')), array('start', '', array()), array('text', 'x'), array('end', ''), array('end', 'rss')));
		});

		$this->assertSame(array(), $this->raised);
		$this->assertSame(array(), $rss->items);
	}

	public function testTargetsAfterInstructionLikeTextInACommentOrCdataSectionCountAsDistinctNames()
	{
		foreach(array('<!-- <?a--><?p%d?>', '<![CDATA[<?a]]><?p%d?>') as $format)
		{
			$this->assertRefusedQuickly($this->rss($this->item($this->repeated(5000, $format))), null);
			$this->assertRefusedQuickly($this->rss($this->item($this->repeated(600000, $format))), null);
		}
	}

	public function testNamesAfterLongNamesCountAsDistinctNames()
	{
		$spread = '';

		for($i = 0; $i < 40; $i++)
		{
			$spread .= '<?n'.$i.str_repeat('n', 33000).'?>'.$this->repeated(125, '<?p'.$i.'x%d?>');
		}

		foreach(array_merge($this->afterALongName(5000), array($this->rss($this->item($spread)))) as $source)
		{
			$this->assertRefused($this->parse($source), 'The document uses more than 4096 distinct names', $source);
		}
	}

	public function testFourThousandNamesAfterALongNameAreKept()
	{
		list($instructions) = $this->afterALongName(4000);
		list(, , $references) = $this->afterALongName(4000, '<item><title>j</title></item>');

		$rss = $this->parse($instructions);
		$reported = $this->parse($references);

		$this->assertSame('', $rss->ERROR);
		$this->assertCount(1, $rss->items);
		$this->assertStringStartsWith('Undeclared entity ', $reported->ERROR);
		$this->assertCount(2, $reported->items);
		$this->assertSame(array(), $this->raised);
	}

	public function testFourThousandDeclaredEntitiesAreKept()
	{
		$rss = $this->parse($this->rss('<item><title>i</title><description>&e1;</description></item>', $this->repeated(4000, '<!ENTITY e%d "x">')));

		$this->assertSame('', $rss->ERROR);
		$this->assertSame('x', $rss->items[0]['description']);
	}

	public function testSixHundredThousandDeclaredNamesAreRefusedQuickly()
	{
		foreach(array('<!ENTITY e%d "x">', '<!ENTITY %% p%d "x">', '<!ELEMENT e%d EMPTY>', '<!ATTLIST x a%d CDATA #IMPLIED>', '<!NOTATION n%d SYSTEM "x">') as $declaration)
		{
			$this->assertRefusedQuickly($this->rss('<item><title>i</title></item>', $this->repeated(600000, $declaration)), null);
		}

		$this->assertRefusedQuickly($this->rss($this->item($this->repeated(600000, '<?p%d?>'))), 'The document uses more than 4096 distinct names');
	}

	public function testSixHundredThousandUndeclaredReferencesInOneValueAreRefusedQuickly()
	{
		$value = $this->repeated(600000, '&e%d;');

		$this->assertRefusedQuickly($this->external($this->rss($this->attributedItem($value))), null);
		$this->assertRefusedQuickly($this->external($this->rss($this->attributedItem($value), '<!ENTITY z "z">')), null);
	}

	public function testFourThousandUndeclaredReferencesInOneValueAreKeptAndReported()
	{
		$rss = $this->parse($this->external($this->rss($this->attributedItem($this->repeated(4000, '&e%d;')).'<item><title>j</title></item>')));

		$this->assertStringStartsWith('Undeclared entity ', $rss->ERROR);
		$this->assertCount(2, $rss->items);
		$this->assertSame(array(), $this->raised);
	}

	public function testALongEntityValueIsParsedQuickly()
	{
		$rss = $this->assertParsedWithin(10, $this->rss($this->item('x'), '<!ENTITY e "'.str_repeat('<abcde/>', 800000).'">'));

		$this->assertCount(1, $rss->items);
	}

	public function testALongAttributeValueHoldingGreaterThanSignsIsParsedQuickly()
	{
		$rss = $this->assertParsedWithin(10, $this->rss($this->attributedItem(str_repeat('abc>defg', 800000)), '<!ENTITY z "z">'));

		$this->assertCount(1, $rss->items);
	}

	public function testALongCdataSectionIsParsedQuickly()
	{
		$text = str_repeat('a<b>]c]d', 800000);

		$rss = $this->assertParsedWithin(10, $this->rss($this->item('<![CDATA['.$text.']]>'), '<!ENTITY z "z">'));

		$this->assertCount(1, $rss->items);
		$this->assertSame($text, $rss->items[0]['description']);
	}

	public function testALongEntityValueInUtf16IsParsedQuickly()
	{
		$rss = $this->assertParsedWithin(10, $this->utf16($this->rss($this->item('x'), '<!ENTITY e "'.str_repeat('<abcde/>', 800000).'">')));

		$this->assertCount(1, $rss->items);
	}

	public function testALongAttributeValueWithoutEntitiesIsParsedWithinFiveTimesItsFloor()
	{
		$rss = $this->assertParsedWithin(5, $this->rss($this->attributedItem(str_repeat('abc>defg', 1240000))));

		$this->assertCount(1, $rss->items);
	}

	public function testALongTextRunBrokenByReferencesIsParsedQuickly()
	{
		$rss = $this->assertParsedWithin(20, $this->rss($this->item(str_repeat('abc&gt;de', 200000))));

		$this->assertSame(str_repeat('abc>de', 200000), $rss->items[0]['description']);
	}

	public function testALongTextRunReadInPiecesIsParsedQuickly()
	{
		$text = str_repeat('abcdefgh', 1600000);

		$rss = $this->assertParsedWithin(20, $this->rss($this->item($text), '<!ENTITY z "z">'));

		$this->assertSame($text, $rss->items[0]['description']);
	}

	public function testAnEntityDeclarationNothingUsesLeavesTheCostOfManyShortReferencesAsItWas()
	{
		$report = $this->timedAgainstTheFloor($this->twinned(array('text' => $this->item(str_repeat('&gt;', 250000)), 'value' => $this->attributedItem(str_repeat('&gt;', 500000)))));

		$this->assertTwinsKeptAndCostAsTheirItem($report, array('text' => array('', 1, str_repeat('>', 250000)), 'value' => array('', 1, 'x')));
		$this->assertNotFarAboveTheFloor($report, 20);
	}

	public function testAnEntityDeclarationNothingUsesLeavesTheRunsOfTextAsTheyWere()
	{
		$runs = array();

		foreach($this->twinned(array('text' => $this->item(str_repeat('abcdefgh', 100000)))) as $name => $source)
		{
			$runs[$name] = array_map('strlen', (new MagpieRSSTestDouble($source))->runs);
		}

		foreach(array_keys($this->twinSubsets()) as $prefix)
		{
			$this->assertSame($runs['text'], $runs[$prefix.'text'], $prefix);
		}
	}

	public function testReferencesBetweenCommentsInstructionsOrCdataSectionsKeepTheirCostUnderAnEntityLongerThanAReference()
	{
		$greater = str_repeat('>', 250000);
		$expanded = str_repeat('xxxx', 250000);

		$report = $this->timedAgainstTheFloor($this->referencesBetweenMarkup());

		foreach(array('predefined' => $greater, 'declared' => $greater, 'used' => $expanded, 'used beside cdata' => $expanded) as $name => $description)
		{
			$this->assertSame(array('', 1, $description), $report['kept'][$name], $name);
		}

		$this->assertNotFarAboveTheFloor($report, 20);
		$this->assertCostsAsItsTwin($report['parse']['declared'], $report['parse']['predefined'], 'declared');
	}

	public function testALongNameLearnedEarlyLeavesTheCostOfTheNamesAfterItAsItWas()
	{
		$instructions = str_repeat('<?p?><?q?>', 40000);
		$references = str_repeat('&a;&b;', 60000);
		$subset = '<!ENTITY a "x"><!ENTITY b "y">';

		$report = $this->timedAgainstTheFloor(array(
			'instructions' => $this->rss($this->item('<!--<?n?>-->'.$instructions)),
			'long instructions' => $this->rss($this->item('<!--<?'.str_repeat('n', 40000).'?>-->'.$instructions)),
			'references' => $this->rss($this->item('<!--&n;-->'.$references), $subset),
			'long references' => $this->rss($this->item('<!--&'.str_repeat('n', 40000).';-->'.$references), $subset),
		));

		foreach(array('instructions', 'references') as $name)
		{
			$this->assertSame('', $report['kept'][$name][0], $name);
			$this->assertSame($report['kept'][$name], $report['kept']['long '.$name], 'long '.$name);
			$this->assertCostsAsItsTwin($report['parse']['long '.$name], $report['parse'][$name], 'long '.$name);
		}
	}

	public function testADocumentTypeDeclarationInEveryCommentCostsWhatOneDoes()
	{
		$report = $this->timedAgainstTheFloor(array(
			'one' => $this->rss($this->item('<!--<!DOCTYPE-->'.str_repeat('<!--<!DOCTYPX-->', 249999))),
			'every' => $this->rss($this->item(str_repeat('<!--<!DOCTYPE-->', 250000))),
		));

		$this->assertSame(array('', 1, null), $report['kept']['one']);
		$this->assertSame($report['kept']['one'], $report['kept']['every']);
		$this->assertCostsAsItsTwin($report['parse']['every'], $report['parse']['one'], 'every');
	}

	public function testRunsOfDeclarationsInContentAreRefusedQuickly()
	{
		foreach(array('<!x[]>' => 2800000, '<!DOCTYPE x []>' => 1100000) as $declaration => $count)
		{
			$source = $this->rss($this->item('x'.str_repeat($declaration, $count).str_repeat('&a;', 1000)), $this->entity('a', 50000));

			$this->assertSame(0, $this->assertRefusedQuickly($source, null)['items'], $declaration);
		}
	}

	public function testHeavyReferencesAfterLongCommentsInARowAreRefusedWithoutAWarning()
	{
		$comment = '<!--'.str_repeat('c', 20000).'-->';
		$source = $this->rss($this->item('x').$comment.$comment.$this->item(str_repeat('&a;', 1000)), $this->entity('a', 50000));

		$this->assertSame(1, $this->assertRefusedQuickly($source, 'Entity expansion outgrew the document')['items']);
	}

	public function testAFeedWrittenAgainstNetscapesDtdKeepsEveryItemPastItsUndeclaredEntities()
	{
		$rss = $this->parse($this->netscape($this->rss($this->item('one&nbsp;two').$this->plainItems(3000))));

		$this->assertStringStartsWith('Undeclared entity ', $rss->ERROR);
		$this->assertCount(3001, $rss->items);
		$this->assertSame('onetwo', $rss->items[0]['description']);
		$this->assertSame(array(), $this->raised);
	}

	public function testADocumentNamingAnExternalSubsetKeepsEveryItemPastAnUndeclaredEntity()
	{
		$rss = $this->parse($this->external($this->rss($this->item('&site; one&nbsp;two').$this->plainItems(20), '<!ENTITY site "Example">')));

		$this->assertStringStartsWith('Undeclared entity ', $rss->ERROR);
		$this->assertCount(21, $rss->items);
		$this->assertSame('Example onetwo', $rss->items[0]['description']);
		$this->assertSame(array(), $this->raised);
	}

	public function testAFeedThatDrawsAWarningFromLibxmlKeepsEveryItem()
	{
		$warnings = array(
			'an XML 1.1 declaration' => array('<?xml version="1.1"?>', ''),
			'an instruction named xml-foo' => array('', '<?xml-foo?>'),
			'a bad xml:space value' => array('', '<category xml:space="bad">c</category>'),
			'a malformed catalog instruction' => array('<?oasis-xml-catalog bad?>', ''),
			'an instruction past the first piece' => array('', $this->plainItems(3000).'<?xml-foo?>'),
		);

		foreach($warnings as $name => $warning)
		{
			list($before, $inside) = $warning;

			foreach(array('plain' => $this->rss($inside.$this->plainItems(3000)), 'weighed' => $this->rss($inside.$this->item('&e;').$this->plainItems(20), '<!ENTITY e "Example">')) as $kind => $document)
			{
				$rss = $this->parse($before.$document);

				$this->assertSame('', $rss->ERROR, $name.', '.$kind);
				$this->assertCount(substr_count($document, '<item>'), $rss->items, $name.', '.$kind);
				$this->assertSame(array(), $this->raised, $name.', '.$kind);
			}
		}
	}

	public function testTheNamesLimitStopsADocumentQuicklyAfterAReportLibxmlReadsPast()
	{
		$this->assertRefusedQuickly($this->external($this->rss($this->item('&nbsp;').$this->item($this->names(800000)))), 'The document uses more than 4096 distinct names');
	}

	public function testTheNamesLimitStopsAnXml11DocumentQuickly()
	{
		$this->assertRefusedQuickly('<?xml version="1.1"?>'.$this->rss($this->item($this->names(800000))), 'The document uses more than 4096 distinct names');
	}

	public function testAnUndeclaredEntityBehindAnUnreadEntitySetKeepsWhatLibxmlDelivers()
	{
		$rss = $this->parse($this->rss($this->item('Caf&eacute;').$this->plainItems(20), '<!ENTITY % HTMLlat1 PUBLIC "-//W3C//ENTITIES Latin 1 for XHTML//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml-lat1.ent"> %HTMLlat1;'));

		$this->assertStringStartsWith('Undeclared entity ', $rss->ERROR);
		$this->assertSame(array(), $this->raised);

		if(LIBXML_VERSION < 21300)
		{
			$this->assertSame(array(), $rss->items);

			return;
		}

		$this->assertCount(21, $rss->items);
		$this->assertSame('Caf', $rss->items[0]['description']);
	}

	public function testEntityReferencesInAnAttributeDefaultAreRefusedQuickly()
	{
		$report = $this->assertRefusedQuickly($this->attributeDefault(), null);

		$this->assertFalse($report['collecting']);
	}

	public function testUnresolvedParameterEntityReferencesAreRefusedQuickly()
	{
		$this->assertRefusedQuickly($this->rss('<item><title>i</title></item>', str_repeat('%p;', 1000000)), null);
	}

	public function testLibxmlReportsHoldNoMemoryWhenErrorCollectionIsAlreadyOn()
	{
		$report = $this->assertRefusedQuickly($this->attributeDefault(), null, '', 'libxml_use_internal_errors(true);');

		$this->assertTrue($report['collecting']);
		$this->assertLessThan(64 * 1048576, $report['held']);
	}

	public function testRedeclaredPredefinedEntitiesHoldNoMemoryAfterTheParse()
	{
		$source = $this->rss('<item><title>i</title></item>', str_repeat('<!ENTITY lt "x">', 625000));

		$quiet = $this->assertRefusedQuickly($source, null);
		$collecting = $this->assertRefusedQuickly($source, null, '', 'libxml_use_internal_errors(true);');

		$this->assertLessThan(64 * 1048576, $quiet['held']);
		$this->assertTrue($collecting['collecting']);
		$this->assertLessThan(64 * 1048576, $collecting['held']);
	}

	public function testAnInternalSubsetOf4096DeclarationsIsKept()
	{
		$rss = $this->parse($this->rss('<item><title>i</title></item>', str_repeat('<!ENTITY lt "x">', 4096)));

		$this->assertSame('', $rss->ERROR);
		$this->assertCount(1, $rss->items);
	}

	public function testAnInternalSubsetOfMoreThan4096DeclarationsIsRefused()
	{
		$source = $this->rss('<item><title>i</title></item>', str_repeat('<!ENTITY lt "x">', 4097));

		$rss = $this->parse($source);

		$this->assertRefused($rss, 'The document type declaration holds more than 4096 markup declarations', $source);
		$this->assertSame(array(), $rss->items);
	}

	public function testAnInternalSubsetOfMoreThan4096ReferencesIsRefused()
	{
		$source = $this->rss('<item><title>i</title></item>', '<!ENTITY a "A"><!ATTLIST x y CDATA "'.str_repeat('&a;', 4097).'">');

		$this->assertRefused($this->parse($source), 'The document type declaration holds more than 4096 references', $source);
	}

	public function testAnInternalSubsetOfMoreThan65536BytesBesidesItsEntitiesIsRefused()
	{
		$source = $this->rss('<item><title>i</title></item>', '<!--'.str_repeat('c', 65536).'-->');

		$this->assertRefused($this->parse($source), 'The document type declaration holds more than 65536 bytes besides its entity declarations', $source);
	}

	public function testAnAttributeTypeOf256ValuesIsKept()
	{
		$rss = $this->parse($this->rss('<item><title>i</title></item>', $this->enumeration(256, '')));

		$this->assertSame('', $rss->ERROR);
		$this->assertCount(1, $rss->items);
	}

	public function testAnAttributeTypeOfMoreThan256ValuesIsRefused()
	{
		foreach(array($this->rss('<item><title>i</title></item>', $this->enumeration(257, '')), $this->rss('<item><title>i</title></item>', $this->enumeration(257, 'NOTATION ')), $this->utf16($this->rss('<item><title>i</title></item>', $this->enumeration(257, '')))) as $source)
		{
			$rss = $this->parse($source);

			$this->assertRefused($rss, 'An attribute type lists more than 256 values', $source);
			$this->assertSame(array(), $rss->items);
		}
	}

	public function testAStartTagAfterACommentOrInstructionWhoseCloserReadsAsAnOpenerIsCounted()
	{
		foreach(array('c', 'p') as $kind)
		{
			$this->assertSame(0, $this->assertRefusedQuickly($this->screened($kind, $this->crowded(300)), null)['items'], $kind);
		}
	}

	public function testASubsetAfterACommentOrInstructionWhoseCloserReadsAsAnOpenerIsCounted()
	{
		foreach(array('c', 'p') as $kind)
		{
			foreach(array(600000, 5000) as $count)
			{
				$this->assertRefusedQuickly($this->prefaced($kind, $this->rss('<item><title>i</title><description>&e1;</description></item>', $this->repeated($count, '<!ENTITY e%d "x">'))), null);
			}
		}

		$report = $this->parsedInChild($this->prefaced('c', $this->rss('<item><title>i</title></item>', str_repeat('<!ENTITY lt "x">', 625000))));

		$this->assertTrue($report['error'] !== '' || $report['items'] === 1);
		$this->assertSame(0, $report['raised']);
		$this->assertLessThan(64 * 1048576, $report['peak']);
		$this->assertLessThan(64 * 1048576, $report['held']);
	}

	public function testALongTagOrCdataSectionAfterACommentOrInstructionWhoseCloserReadsAsAnOpenerIsParsedQuickly()
	{
		$value = str_repeat('abc>defg', 800000);
		$text = str_repeat('a<b>]c]d', 800000);
		$padding = str_repeat('pad ', 375);
		$subset = '<!ENTITY e "'.str_repeat('e', 16).'">';

		foreach(array('<!-- x <!--><item a="-->', '<?p <?><item a="?>') as $opening)
		{
			$this->assertCount(2, $this->assertParsedWithin(20, $this->rss($this->item($padding.'&e;').$opening.$value.'"><title>i</title></item>', $subset))->items);
		}

		$rss = $this->assertParsedWithin(20, $this->rss('<item><title>i</title><description>'.$padding.'&e;<!-- x <!-->'.str_repeat('pad ', 500).'<![CDATA[ -->'.$text.']]></description></item>', $subset));

		$this->assertCount(1, $rss->items);
		$this->assertSame($padding.str_repeat('e', 16).str_repeat('pad ', 500).' -->'.$text, $rss->items[0]['description']);
	}

	public function testCommentsAndInstructionsWhoseCloserReadsAsAnOpenerAreKept()
	{
		foreach(array('c', 'p') as $kind)
		{
			list($open, $close) = $this->screens($kind);

			$rss = $this->parse($this->rss($open.$this->plainItems(2).$close.$this->plainItems(1).$open.'<item><title>last</title></item>'.$close));

			$this->assertSame('', $rss->ERROR, $kind);
			$this->assertCount(4, $rss->items, $kind);
			$this->assertSame('last', $rss->items[3]['title'], $kind);
		}
	}

	/**
	 * Formats a parameter as its name, with & when by reference and =default when it has one.
	 *
	 * @param ReflectionParameter $parameter
	 * @return string
	 */
	private function describeParameter(ReflectionParameter $parameter)
	{
		$text = ($parameter->isPassedByReference() ? '&' : '').$parameter->getName();

		if(!$parameter->isDefaultValueAvailable())
		{
			return $text;
		}

		return $text.'='.($parameter->isDefaultValueConstant() ? $parameter->getDefaultValueConstantName() : var_export($parameter->getDefaultValue(), true));
	}

	/**
	 * Parses a fixture under tests/_data/magpie_rss, collecting whatever PHP raises into {@see MagpieRSSTest::$raised}.
	 *
	 * @param string $name file name without .xml
	 * @param string|null $output
	 * @param string|null $input
	 * @param bool $detect
	 * @return MagpieRSS
	 */
	private function fixture($name, $output = 'utf-8', $input = null, $detect = true)
	{
		return $this->parse(file_get_contents(codecept_data_dir().'magpie_rss/'.$name.'.xml'), $output, $input, $detect);
	}

	/**
	 * Parses a document, collecting whatever PHP raises into {@see MagpieRSSTest::$raised}.
	 *
	 * @param mixed $source
	 * @param string|null $output
	 * @param string|null $input
	 * @param bool $detect
	 * @return MagpieRSS
	 */
	private function parse($source, $output = 'utf-8', $input = null, $detect = true)
	{
		return $this->quietly(function () use ($source, $output, $input, $detect)
		{
			return new MagpieRSS($source, $output, $input, $detect);
		});
	}

	/**
	 * Runs a callable with the error log silenced, recording each PHP error it raises in {@see MagpieRSSTest::$raised}.
	 *
	 * @param callable $run
	 * @return mixed what $run returns
	 */
	private function quietly($run)
	{
		$raised = &$this->raised;
		$log = ini_set('error_log', '/dev/null');
		set_error_handler(function ($level, $message) use (&$raised)
		{
			$raised[] = array($level, $message);

			return true;
		});

		try
		{
			return $run();
		}
		finally
		{
			restore_error_handler();
			ini_set('error_log', $log);
		}
	}

	/**
	 * Tells whether a value is an ext/xml parser: a resource before PHP 8, an XMLParser object since.
	 *
	 * @param mixed $value
	 * @return bool
	 */
	private function isXmlParser($value)
	{
		return is_resource($value) ? get_resource_type($value) === 'xml' : is_object($value) && get_class($value) === 'XMLParser';
	}

	/**
	 * Feeds start, text and end events to a fresh parser as ext/xml would, then normalises it.
	 *
	 * @param array[] $events each array('start', name, attributes), array('text', text) or array('end', name)
	 * @return MagpieRSS
	 */
	private function drive(array $events)
	{
		$rss = $this->parse('');

		foreach($events as $event)
		{
			switch($event[0])
			{
				case 'start':
					$rss->feed_start_element(null, $event[1], $event[2]);
					break;

				case 'text':
					$rss->feed_cdata(null, $event[1]);
					break;

				default:
					$rss->feed_end_element(null, $event[1]);
			}
		}

		$rss->normalize();

		return $rss;
	}

	/**
	 * Asserts that two parsers hold the same feed, ignoring the error the hand-driven one met on its empty source.
	 *
	 * @param MagpieRSS $expected
	 * @param MagpieRSS $actual
	 * @return void
	 */
	private function assertSameResult(MagpieRSS $expected, MagpieRSS $actual)
	{
		$this->assertSame('', $expected->ERROR);

		foreach(array('items', 'channel', 'image', 'textinput', 'current_item', 'feed_type', 'feed_version', 'stack', 'inchannel', 'initem', 'incontent') as $property)
		{
			$this->assertSame($expected->$property, $actual->$property, $property);
		}
	}

	/**
	 * Asserts that a document is refused with nothing in $items both here and in a child PHP that has $disabled disabled.
	 *
	 * @param string $source
	 * @param string[] $disabled converter functions
	 * @return void
	 */
	private function assertRefusedWithAndWithoutConverters($source, array $disabled)
	{
		$rss = $this->parse($source);
		$report = $this->parsedInChild($source, '-d disable_functions='.implode(',', $disabled));

		$this->assertNotSame('', $rss->ERROR);
		$this->assertSame(array(), $rss->items);
		$this->assertLessThan(10 * strlen($source) + 65536, $this->stored($rss));
		$this->assertSame(array(), $this->raised);
		$this->assertSame(array(), array_intersect($disabled, $report['converters']));
		$this->assertNotSame('', $report['error']);
		$this->assertSame(0, $report['items']);
		$this->assertSame(0, $report['raised']);
	}

	/**
	 * Asserts that a document parses with no error, within $times its floor as {@see MagpieRSSDocuments::assertNotFarAboveTheFloor()} holds it, and within 64 MiB in a child PHP.
	 *
	 * @param int $times
	 * @param string $source
	 * @return MagpieRSS
	 */
	private function assertParsedWithin($times, $source)
	{
		$took = $this->timedAgainstTheFloor(array('document' => $source));
		$report = $this->parsedInChild($source);
		$rss = $this->parse($source);

		$this->assertSame('', $rss->ERROR);
		$this->assertSame(array(), $this->raised);
		$this->assertNotFarAboveTheFloor($took, $times);
		$this->assertSame('', $report['error']);
		$this->assertSame(0, $report['raised']);
		$this->assertLessThan(64 * 1048576, $report['peak']);

		return $rss;
	}

	/**
	 * Asserts that a parse stopped with $message, kept less than ten times the document plus 64 KiB and raised nothing.
	 *
	 * @param MagpieRSS $rss
	 * @param string $message what $ERROR starts with
	 * @param string $source the document parsed
	 * @return void
	 */
	private function assertRefused(MagpieRSS $rss, $message, $source)
	{
		$this->assertStringStartsWith($message, $rss->ERROR);
		$this->assertLessThan(10 * strlen($source) + 65536, $this->stored($rss));
		$this->assertSame(array(), $this->raised);
	}

	/**
	 * Puts markup into the XHTML content of an Atom feed's one entry.
	 *
	 * @param string $markup
	 * @return string
	 */
	private function atom($markup)
	{
		return '<feed xmlns="http://www.w3.org/2005/Atom"><title>t</title><entry><title>i</title><content type="xhtml">'.$markup.'</content></entry></feed>';
	}

	/**
	 * Writes an RSS channel of two items, the first titled $title and the second 'two', behind an XML declaration naming $label.
	 *
	 * @param string $label
	 * @param string $title
	 * @return string
	 */
	private function labelled($label, $title)
	{
		return '<?xml version="1.0" encoding="'.$label.'"?>'.$this->rss('<item><title>'.$title.'</title></item><item><title>two</title></item>');
	}

	/**
	 * Asserts that a document of two items is kept with its first title $title, both here and in a child PHP without iconv or mb_convert_encoding.
	 *
	 * @param string $source
	 * @param string $title in UTF-8
	 * @return void
	 */
	private function assertTitledWithoutConverters($source, $title)
	{
		$rss = $this->parse($source);
		$report = $this->parsedInChild($source, '-d disable_functions=iconv,mb_convert_encoding');

		$this->assertSame('', $rss->ERROR);
		$this->assertCount(2, $rss->items);
		$this->assertSame($title, $rss->items[0]['title']);
		$this->assertSame(array(), $this->raised);
		$this->assertSame(array(), $report['converters']);
		$this->assertSame('', $report['error']);
		$this->assertSame(2, $report['items']);
		$this->assertSame(bin2hex($title), $report['first']);
		$this->assertSame(0, $report['raised']);
	}

	/**
	 * Puts Netscape's document type declaration for RSS 0.91 before a document.
	 *
	 * @param string $document
	 * @return string
	 */
	private function netscape($document)
	{
		return '<!DOCTYPE rss PUBLIC "-//Netscape Communications//DTD RSS 0.91//EN" "http://my.netscape.com/publish/formats/rss-0.91.dtd">'.$document;
	}

	/**
	 * Writes two tag-like runs, '<a' and ' <b', each followed by $count empty attributes and neither closed.
	 *
	 * @param int $count
	 * @return string
	 */
	private function twoTags($count)
	{
		return '<a'.$this->attributes($count, '=""').' <b'.$this->attributes($count, '=""');
	}

	/**
	 * Writes three RSS channels whose item holds a name of 40,000 bytes and then $count names of their own, with $after behind the item: processing-instruction targets after a target, the same after a target inside a comment, and references to undeclared entities in an attribute value after a reference, behind an external subset.
	 *
	 * @param int $count
	 * @param string $after
	 * @return string[]
	 */
	private function afterALongName($count, $after = '')
	{
		$long = str_repeat('n', 40000);
		$targets = $this->repeated($count, '<?p%d?>');

		return array(
			$this->rss($this->item('<?'.$long.'?>'.$targets).$after),
			$this->rss($this->item('<!--<?'.$long.'?>-->'.$targets).$after),
			$this->external($this->rss($this->attributedItem('&'.$long.';'.$this->repeated($count, '&e%d;')).$after)),
		);
	}

	/**
	 * Writes $format $count times, through sprintf() with the count so far.
	 *
	 * @param int $count
	 * @param string $format
	 * @return string
	 */
	private function repeated($count, $format)
	{
		$text = '';

		for($i = 0; $i < $count; $i++)
		{
			$text .= sprintf($format, $i);
		}

		return $text;
	}

	/**
	 * Writes an RSS channel of one item behind a doctype whose attribute default holds a million references to a declared entity.
	 *
	 * @return string
	 */
	private function attributeDefault()
	{
		return $this->rss('<item><title>i</title></item>', '<!ENTITY a "A"><!ATTLIST x y CDATA "'.str_repeat('&a;', 1000000).'">');
	}
}
