<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

/**
 * Feed documents for the MagpieRSS tests, and a child PHP to parse one in.
 */
trait MagpieRSSDocuments
{
	/** @var int the rounds these tests ask of {@see \Test\ProcessorTime::leastProcessorTimesOver()} and {@see \Test\ProcessorTime::processorTimesByRound()} */
	private static $timingRounds = 7;

	/**
	 * Wraps markup in an RSS 2.0 channel with a title and a link, behind a doctype holding $subset when there is one.
	 *
	 * @param string $inside
	 * @param string $subset
	 * @return string
	 */
	private function rss($inside, $subset = '')
	{
		return ($subset === '' ? '' : '<!DOCTYPE rss ['.$subset.']>').'<rss version="2.0"><channel><title>t</title><link>https://example.test/</link>'.$inside.'</channel></rss>';
	}

	/**
	 * Wraps markup in an RSS 1.0 document after its channel, behind a doctype holding $subset.
	 *
	 * @param string $inside
	 * @param string $subset
	 * @return string
	 */
	private function rdf($inside, $subset)
	{
		return '<!DOCTYPE rdf:RDF ['.$subset.']><rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns="http://purl.org/rss/1.0/">'
			.'<channel rdf:about="https://example.test/"><title>t</title></channel>'.$inside.'</rdf:RDF>';
	}

	/**
	 * Writes an RSS item with a title, a link and $description.
	 *
	 * @param string $description
	 * @return string
	 */
	private function item($description)
	{
		return '<item><title>i</title><link>https://example.test/1</link><description>'.$description.'</description></item>';
	}

	/**
	 * Writes an RSS item with an attribute holding $value, a title, a link and a description.
	 *
	 * @param string $value
	 * @return string
	 */
	private function attributedItem($value)
	{
		return '<item a="'.$value.'"><title>i</title><link>https://example.test/1</link><description>x</description></item>';
	}

	/**
	 * Writes $count RSS items, the i-th titled 'Item i', linked to https://example.test/i and described by twenty ordinary sentences.
	 *
	 * @param int $count
	 * @return string
	 */
	private function plainItems($count)
	{
		$items = '';

		for($i = 0; $i < $count; $i++)
		{
			$items .= '<item><title>Item '.$i.'</title><link>https://example.test/'.$i.'</link><description>'.str_repeat('Some ordinary words. ', 20).'</description></item>';
		}

		return $items;
	}

	/**
	 * Declares an internal entity whose text is $bytes letters A.
	 *
	 * @param string $name
	 * @param int $bytes
	 * @return string
	 */
	private function entity($name, $bytes)
	{
		return '<!ENTITY '.$name.' "'.str_repeat('A', $bytes).'">';
	}

	/**
	 * Nests $depth elements, the i-th named 'n' and i padded with 'q' to $length - 1 characters, with $text after each start tag.
	 *
	 * @param int $depth
	 * @param int $length
	 * @param string $text
	 * @return string
	 */
	private function chain($depth, $length, $text)
	{
		$open = '';
		$close = array();

		for($i = 0; $i < $depth; $i++)
		{
			$name = 'n'.str_pad((string) $i, $length - 1, 'q');
			$open .= '<'.$name.'>'.$text;
			$close[] = '</'.$name.'>';
		}

		return $open.implode('', array_reverse($close));
	}

	/**
	 * Writes $count attributes a0, a1 ..., each followed by $value, which brings the equals sign and the quoted value.
	 *
	 * @param int $count
	 * @param string $value
	 * @return string
	 */
	private function attributes($count, $value)
	{
		$text = '';

		for($i = 0; $i < $count; $i++)
		{
			$text .= ' a'.$i.$value;
		}

		return $text;
	}

	/**
	 * Writes an RSS channel whose first item's start tag carries $count attributes, each followed by $value, between $opening and $closing, then a second item titled j.
	 *
	 * @param int $count
	 * @param string $opening the tag up to the first of them
	 * @param string $value
	 * @param string $closing what comes between the last of them and the tag's '>'
	 * @return string
	 */
	private function crowded($count, $opening = '<item', $value = '=""', $closing = '')
	{
		return $this->rss($opening.$this->attributes($count, $value).$closing.'><title>i</title></item><item><title>j</title></item>');
	}

	/**
	 * Lists openings for {@see MagpieRSSDocuments::crowded()} whose first attribute libxml cannot read: one without a value, one with an unquoted value, one with no space before the next, and values holding a '<' or a whole declaration.
	 *
	 * @return string[]
	 */
	private function unreadableOpenings()
	{
		return array('<item k', '<item k=x', '<item k=""m=""', '<item k="<"', '<item k="<!x>"');
	}

	/**
	 * Writes a comment ('c') or processing instruction ('p') whose closer also reads as a second opener, then a plain one of the same kind.
	 *
	 * @param string $kind 'c' or 'p'
	 * @return string[]
	 */
	private function screens($kind)
	{
		return $kind === 'c' ? array('<!-- x <!-->', '<!-- z -->') : array('<?p <?>', '<?q z?>');
	}

	/**
	 * Puts the first of {@see MagpieRSSDocuments::screens()} straight after the channel's link in an {@see MagpieRSSDocuments::rss()} document, and the second straight before its '</channel>'.
	 *
	 * @param string $kind 'c' or 'p'
	 * @param string $document
	 * @return string
	 */
	private function screened($kind, $document)
	{
		list($open, $close) = $this->screens($kind);
		$link = '<link>https://example.test/</link>';
		$document = substr_replace($document, $open, strpos($document, $link) + strlen($link), 0);

		return substr_replace($document, $close, strrpos($document, '</channel>'), 0);
	}

	/**
	 * Puts the first of {@see MagpieRSSDocuments::screens()} before a document's type declaration, and the second straight after that declaration's '['.
	 *
	 * @param string $kind 'c' or 'p'
	 * @param string $document
	 * @return string
	 */
	private function prefaced($kind, $document)
	{
		list($open, $close) = $this->screens($kind);
		$doctype = strpos($document, '<!DOCTYPE');

		return substr_replace(substr_replace($document, $close, strpos($document, '[', $doctype) + 1, 0), $open, $doctype, 0);
	}

	/**
	 * Writes $count empty elements, each with a name of its own.
	 *
	 * @param int $count
	 * @return string
	 */
	private function names($count)
	{
		$elements = '';

		for($k = 0; $k < $count; $k++)
		{
			$elements .= '<'.$this->fiveLetterName($k).'/>';
		}

		return $elements;
	}

	/**
	 * Spells the $k-th of the five-letter names, its base-26 digits as letters from 'a', least significant first.
	 *
	 * @param int $k
	 * @return string
	 */
	private function fiveLetterName($k)
	{
		$name = '';

		for($letter = 0; $letter < 5; $letter++, $k = (int) floor($k / 26))
		{
			$name .= chr(97 + $k % 26);
		}

		return $name;
	}

	/**
	 * Names an external subset in a document's type declaration, adding one when it has none.
	 *
	 * @param string $document
	 * @return string
	 */
	private function external($document)
	{
		$open = '<!DOCTYPE rss [';

		return strncmp($document, $open, strlen($open)) === 0 ? '<!DOCTYPE rss SYSTEM "http://example.test/rss.dtd" ['.substr($document, strlen($open)) : '<!DOCTYPE rss SYSTEM "http://example.test/rss.dtd">'.$document;
	}

	/**
	 * Encodes a document as UTF-16LE behind a byte-order mark and a declaration that names UTF-16.
	 *
	 * @param string $document
	 * @return string
	 */
	private function utf16($document)
	{
		return "\xFF\xFE".iconv('UTF-8', 'UTF-16LE', '<?xml version="1.0" encoding="UTF-16"?>'.$document);
	}

	/**
	 * Declares attribute y of element x with a type that lists $count values v0, v1 ..., after $keyword.
	 *
	 * @param int $count
	 * @param string $keyword '' or 'NOTATION '
	 * @return string
	 */
	private function enumeration($count, $keyword)
	{
		return '<!ATTLIST x y '.$keyword.'(v'.implode('|v', range(0, $count - 1)).') #IMPLIED>';
	}

	/**
	 * Puts a lone high surrogate inside the second item's title of a {@see MagpieRSSDocuments::crowded()} channel in UTF-16LE.
	 *
	 * @param string $document
	 * @return string
	 */
	private function loneSurrogate($document)
	{
		return str_replace(iconv('UTF-8', 'UTF-16LE', '<title>j</title>'), iconv('UTF-8', 'UTF-16LE', '<title>')."\x00\xD8".iconv('UTF-8', 'UTF-16LE', '</title>'), $document);
	}

	/**
	 * Encodes a document in an EBCDIC code page behind a declaration that names it.
	 *
	 * @param string $codePage iconv's name for it
	 * @param string $document
	 * @return string
	 */
	private function ebcdic($codePage, $document)
	{
		return iconv('UTF-8', $codePage, '<?xml version="1.0" encoding="'.$codePage.'"?>'.$document);
	}

	/**
	 * Lists two internal subsets that declare an entity nothing uses, by the prefix that the name of a document behind one takes; behind either, a document should cost what it costs behind none.
	 *
	 * @return array<string, string>
	 */
	private function twinSubsets()
	{
		return array('declared ' => '<!ENTITY a "x">', 'longer ' => '<!ENTITY a "'.str_repeat('x', 16).'">');
	}

	/**
	 * Writes a document of each item, then one behind each of {@see MagpieRSSDocuments::twinSubsets()}, its prefix leading the name, so that {@see \Test\ProcessorTime::processorTimesByRound()} times each twin straight after the document it is measured against.
	 *
	 * @param array<string, string> $items by name
	 * @return array<string, string> by name
	 */
	private function twinned(array $items)
	{
		$documents = array();

		foreach($items as $name => $item)
		{
			foreach(array('' => '') + $this->twinSubsets() as $prefix => $subset)
			{
				$documents[$prefix.$name] = $this->rss($item, $subset);
			}
		}

		return $documents;
	}

	/**
	 * Writes an item whose description holds 250,000 times a comment, a processing instruction and a reference to '>' ('predefined'); the same behind a subset declaring a four-byte entity ('declared'); with references to that entity instead ('used'); and with those after CDATA sections alone ('used beside cdata').
	 *
	 * @return array<string, string> by name
	 */
	private function referencesBetweenMarkup()
	{
		$predefined = str_repeat('<!----><?p?>&gt;', 250000);

		return array(
			'predefined' => $this->rss($this->item($predefined)),
			'declared' => $this->rss($this->item($predefined), '<!ENTITY a "xxxx">'),
			'used' => $this->rss($this->item(str_repeat('<!----><?p?>&a;', 250000)), '<!ENTITY a "xxxx">'),
			'used beside cdata' => $this->rss($this->item(str_repeat('<![CDATA[]]>&a;', 250000)), '<!ENTITY a "xxxx">'),
		);
	}

	/**
	 * Asserts that in some round a parse took less than one and a half times what its twin took in the same round, plus 0.02 s; time both in one call of {@see \Test\ProcessorTime::processorTimesByRound()}.
	 *
	 * @param float[] $seconds by round
	 * @param float[] $twin by round
	 * @param string $name for the failure message
	 * @return void
	 */
	private function assertCostsAsItsTwin(array $seconds, array $twin, $name)
	{
		$this->assertUnderInSomeRound($seconds, $twin, 1.5, 0.02, $name);
	}

	/**
	 * Asserts that in some round $seconds took less than $times what $measure took in the same round, plus $slack.
	 *
	 * @param float[] $seconds by round
	 * @param float[] $measure by round
	 * @param float $times
	 * @param float $slack
	 * @param string $name for the failure message
	 * @return void
	 */
	private function assertUnderInSomeRound(array $seconds, array $measure, $times, $slack, $name)
	{
		$this->assertLessThan($slack, min(array_map(function ($took, $against) use ($times) { return $took - $times * $against; }, $seconds, $measure)), $name.' '.json_encode(array($seconds, $measure)));
	}

	/**
	 * Asserts that each document in a report of {@see MagpieRSSDocuments::timedAgainstTheFloor()} on {@see MagpieRSSDocuments::twinned()} kept what $kept lists for its item, and that each twin cost as its item's document behind no subset.
	 *
	 * @param array $report
	 * @param array $kept what {@see MagpieRSSDocuments::timedAgainstTheFloor()} reports kept, by item
	 * @return void
	 */
	private function assertTwinsKeptAndCostAsTheirItem(array $report, array $kept)
	{
		foreach($kept as $name => $expected)
		{
			$this->assertSame($expected, $report['kept'][$name], $name);

			foreach(array_keys($this->twinSubsets()) as $prefix)
			{
				$this->assertSame($expected, $report['kept'][$prefix.$name], $prefix.$name);
				$this->assertCostsAsItsTwin($report['parse'][$prefix.$name], $report['parse'][$name], $prefix.$name);
			}
		}
	}

	/**
	 * Asserts that each parse in a report of {@see MagpieRSSDocuments::timedAgainstTheFloor()} took less than $times the sum of its floor and 5 ms.
	 *
	 * @param array $report
	 * @param int $times
	 * @return void
	 */
	private function assertNotFarAboveTheFloor(array $report, $times)
	{
		foreach($report['parse'] as $name => $seconds)
		{
			$this->assertLessThan($times * ($report['floor'][$name] + 0.005), min($seconds), $name);
		}
	}

	/**
	 * Parses a document in a child PHP and asserts the parse stopped within 64 MiB, kept less than ten times the document plus 64 KiB and raised nothing, and that in some round of {@see MagpieRSSDocuments::$timingRounds}, timed in a second child under the same $ini and $setup, the parse took less than 100 times what libxml alone, with no handlers, took on an ordinary feed of about four megabytes in that round.
	 *
	 * @param string $source
	 * @param string|null $message what $ERROR starts with; null accepts any error, for documents libxml may stop itself
	 * @param string $ini extra php command-line arguments for the child
	 * @param string $setup PHP statements the child runs before the parse
	 * @return array see {@see MagpieRSSDocuments::parsedInChild()}
	 */
	private function assertRefusedQuickly($source, $message, $ini = '', $setup = '')
	{
		$report = $this->parsedInChild($source, $ini, $setup);
		$took = $this->timedInChild($setup." \$report = MagpieRSSChild::processorTimesByRound(function (\$parse) { \$parse(); }, array("
			."'refused' => function () use (\$documents) { new MagpieRSS(\$documents['refused']); }, "
			."'ordinary' => function () use (\$documents) { xml_parse(xml_parser_create(), \$documents['ordinary'], true); }), \$rounds);",
			array('refused' => $source, 'ordinary' => $this->rss($this->plainItems(8000))), $ini);

		if($message === null)
		{
			$this->assertNotSame('', $report['error']);
		}
		else
		{
			$this->assertStringStartsWith($message, $report['error']);
		}

		$this->assertLessThan(10 * strlen($source) + 65536, $report['stored']);
		$this->assertSame(0, $report['raised']);
		$this->assertUnderInSomeRound($took['refused'], $took['ordinary'], 100, 0, 'refused');
		$this->assertLessThan(64 * 1048576, $report['peak']);

		return $report;
	}

	/**
	 * Parses a document in a child PHP started with $ini, running $setup first, and reports what the parse kept, cost and raised.
	 *
	 * @param string $source
	 * @param string $ini extra php command-line arguments
	 * @param string $setup PHP statements the child runs before the parse
	 * @return array error, items (how many), first (the first item's title in hex, or null), stored (see {@see MagpieRSSDocuments::stored()}), raised (PHP errors), peak and held (bytes above what was in use before, at the most and once the constructor has returned), collecting (libxml_use_internal_errors() afterwards) and converters (iconv and mb_convert_encoding, as far as the child has them)
	 */
	private function parsedInChild($source, $ini = '', $setup = '')
	{
		return $this->timedInChild("\$raised = 0; set_error_handler(function () use (&\$raised) { \$raised++; return true; }); "
			."\$source = \$documents['source']; ".$setup." "
			."if(function_exists('memory_reset_peak_usage')) { memory_reset_peak_usage(); } "
			."\$before = memory_get_usage(); "
			."\$rss = new MagpieRSS(\$source); "
			."\$report = array('peak' => memory_get_peak_usage() - \$before, 'held' => memory_get_usage() - \$before, "
			."'error' => \$rss->ERROR, 'items' => count(\$rss->items), 'first' => isset(\$rss->items[0]['title']) ? bin2hex(\$rss->items[0]['title']) : null, "
			."'stored' => MagpieRSSChild::stored(\$rss), 'raised' => \$raised, 'collecting' => libxml_use_internal_errors(), "
			."'converters' => array_values(array_filter(array('iconv', 'mb_convert_encoding'), 'function_exists')));", array('source' => $source), $ini);
	}

	/**
	 * Runs statements in a child PHP with a 512 MiB memory limit and $ini, given $documents and $rounds, which holds {@see MagpieRSSDocuments::$timingRounds}; the statements leave their result in $report.
	 *
	 * @param string $php
	 * @param array<string, string> $documents by name, handed over in files
	 * @param string $ini extra php command-line arguments
	 * @return array what the child put in $report
	 */
	private function timedInChild($php, array $documents = array(), $ini = '')
	{
		$paths = array();

		try
		{
			foreach($documents as $name => $document)
			{
				$paths[$name] = tempnam(sys_get_temp_dir(), 'magpie');
				file_put_contents($paths[$name], $document);
			}

			return $this->inChild("\$documents = array_map('file_get_contents', ".var_export($paths, true)."); \$rounds = ".self::$timingRounds."; ".$php, '-d memory_limit=512M '.$ini);
		}
		finally
		{
			array_map('unlink', $paths);
		}
	}

	/**
	 * Times the parse of each document in a child PHP against its floor, the time a parser whose character-data handler only collects the text spends on it.
	 *
	 * @param array<string, string> $documents by name
	 * @return array{floor: array<string, float>, parse: array<string, float[]>, kept: array<string, array{0: string, 1: int, 2: string|null}>} what {@see \Test\ProcessorTime::leastProcessorTimesOver()} gives for each floor and {@see \Test\ProcessorTime::processorTimesByRound()} for each parse, over {@see MagpieRSSDocuments::$timingRounds} rounds, by name, and each parse's error, item count and first description
	 */
	private function timedAgainstTheFloor(array $documents)
	{
		return $this->timedInChild("\$floor = MagpieRSSChild::leastProcessorTimesOver(function (\$source) { \$parser = xml_parser_create(); \$text = ''; xml_set_element_handler(\$parser, function () {}, function () {}); "
			."xml_set_character_data_handler(\$parser, function (\$parser, \$data) use (&\$text) { \$text .= \$data; }); xml_parse(\$parser, \$source, true); }, \$documents, \$rounds); "
			."\$parse = MagpieRSSChild::processorTimesByRound(function (\$source) { new MagpieRSS(\$source); }, \$documents, \$rounds); "
			."\$kept = array(); foreach(\$documents as \$name => \$source) { \$rss = new MagpieRSS(\$source); \$kept[\$name] = array(\$rss->ERROR, count(\$rss->items), isset(\$rss->items[0]['description']) ? \$rss->items[0]['description'] : null); } "
			."\$report = array('floor' => \$floor, 'parse' => \$parse, 'kept' => \$kept);", $documents);
	}

	/**
	 * Runs statements in a child PHP that has loaded the handler, {@see \Test\ProcessorTime} and these documents alone, MagpieRSSChild using them with {@see \Test\ProcessorTime::leastProcessorTimesOver()} and {@see \Test\ProcessorTime::processorTimesByRound()} made public; the statements leave their result in $report, and a child still running after five minutes fails the test.
	 *
	 * @param string $php
	 * @param string $ini extra php command-line arguments
	 * @return array what the child put in $report
	 */
	private function inChild($php, $ini = '')
	{
		$timer = new ReflectionClass('Test\ProcessorTime');
		$boot = "define('e107_INIT', true); define('CHARSET', 'utf-8'); date_default_timezone_set('UTC'); ini_set('error_log', '/dev/null'); "
			."require ".var_export(APP_PATH.'/e107_handlers/magpie_rss.php', true)."; require ".var_export($timer->getFileName(), true)."; require ".var_export(__FILE__, true)."; "
			."final class MagpieRSSChild { use MagpieRSSDocuments; use \\Test\\ProcessorTime { leastProcessorTimesOver as public; processorTimesByRound as public; } } ";

		list($output) = $this->runInCli($boot.$php." echo 'MAGPIE:', json_encode(\$report), PHP_EOL;", $ini, 300);

		return self::reportedAfter($output, 'MAGPIE:', 'the child running '.$php);
	}

	/**
	 * Counts the bytes of every key and string a parse kept in its items, channel, text input and image.
	 *
	 * @param MagpieRSS|array $kept
	 * @return int
	 */
	public static function stored($kept)
	{
		$bytes = 0;

		foreach($kept instanceof MagpieRSS ? array($kept->items, $kept->channel, $kept->textinput, $kept->image) : $kept as $key => $value)
		{
			$bytes += strlen((string) $key) + (is_array($value) ? self::stored($value) : strlen((string) $value));
		}

		return $bytes;
	}
}
