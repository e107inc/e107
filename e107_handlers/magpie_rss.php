<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * RSS and Atom feed parser
 *
 */

if (!defined('e107_INIT')) { exit; }

if(!defined('RSS'))
{
	define('RSS', 'RSS');
}

if(!defined('ATOM'))
{
	define('ATOM', 'Atom');
}

if(!function_exists('map_attrs'))
{
	/**
	 * Writes one attribute as k="v" for markup rebuilt from a feed, with &, < and " in the value escaped.
	 *
	 * @param string $k attribute name
	 * @param string $v attribute value, as decoded text
	 * @return string
	 */
	function map_attrs($k, $v)
	{
		return $k.'="'.str_replace(array('&', '<', '"'), array('&amp;', '&lt;', '&quot;'), (string) $v).'"';
	}
}

if(!function_exists('parse_w3cdtf'))
{
	/**
	 * Reads the first W3C-DTF date-time in a string: YYYY-MM-DDThh:mm, optional :ss and fraction, then Z, ±hh:mm, ±hhmm or nothing for UTC.
	 *
	 * @param string $date_str
	 * @return int Unix timestamp, or -1 when the string holds no date-time (a date alone included)
	 */
	function parse_w3cdtf($date_str)
	{
		$date_str = is_scalar($date_str) ? (string) $date_str : '';

		if(!isset($date_str[15]) || !preg_match('/(\d{4})-(\d\d)-(\d\d)T(\d\d):(\d\d)(?::(\d\d)(?:\.\d+)?)?(?:Z|([+-])(\d\d):?(\d\d))?/', $date_str, $part))
		{
			return -1;
		}

		$offset = isset($part[9]) ? ($part[8] * 3600 + $part[9] * 60) * ($part[7] === '-' ? -1 : 1) : 0;

		return gmmktime($part[4], $part[5], isset($part[6]) && $part[6] !== '' ? $part[6] : 0, $part[2], $part[3], $part[1]) - $offset;
	}
}

/**
 * Parses an RSS 0.9x, 1.0 or 2.0 or Atom 0.3 or 1.0 document, already downloaded, into the public arrays below.
 */
class MagpieRSS
{
	/** @var resource|XMLParser|null the ext/xml parser while the constructor runs; null once it returns */
	public $parser = null;

	/** @var array the item being built; array() when parsing ended outside an item */
	public $current_item = array();

	/** @var array[] every completed item or entry, in document order */
	public $items = array();

	/** @var array fields of the channel, or of the Atom feed itself */
	public $channel = array();

	/** @var array fields of an RSS text input */
	public $textinput = array();

	/** @var array fields of an RSS channel image */
	public $image = array();

	/** @var string|null RSS or ATOM, or null when no feed root was recognised */
	public $feed_type = null;

	/** @var string|null the feed's version ('2.0', '1.0', '0.3' ...), or null for RSS that names none */
	public $feed_version = null;

	/** @var string the output encoding asked for, as given; '' when none was */
	public $encoding = '';

	/** @var string the encoding label of the XML declaration, upper-cased, when detection read one */
	public $_source_encoding = '';

	/** @var string the last error, '' when there was none */
	public $ERROR = '';

	/** @var string the last warning, '' when there was none */
	public $WARNING = '';

	/** @var string[] Atom elements kept as markup under their own key ('content' under 'atom_content'); read when the root is met */
	public $_CONTENT_CONSTRUCTS = array('content', 'summary', 'info', 'title', 'tagline', 'copyright');

	/** @var string[] input encodings handed to ext/xml as they are; others are converted to UTF-8 first */
	public $_KNOWN_ENCODINGS = array('UTF-8', 'US-ASCII', 'ISO-8859-1');

	/** @var string[] local names of the open elements below the current container, innermost first */
	public $stack = array();

	/** @var bool inside the channel */
	public $inchannel = false;

	/** @var bool inside an item */
	public $initem = false;

	/** @var string|false the key of the Atom content construct being captured, or false */
	public $incontent = false;

	/** @var bool inside an RSS text input */
	public $intextinput = false;

	/** @var bool inside an RSS image */
	public $inimage = false;

	/** @var string unused, kept for code that reads it */
	public $current_field = '';

	/** @var string|false the prefix the current text is stored under, or false */
	public $current_namespace = false;


	/** @var int the elements open, as the event methods have counted them */
	private $depth = 0;

	/** @var int the most elements that may be open at once while parsing */
	private $deepest = PHP_INT_MAX;

	/** @var string[] per open element, outermost first, what its end tag undoes: 'step', 'prefixed', 'markup', 'construct', 'container' or 'ignored' */
	private $kinds = array();

	/** @var array<int, string|array> per open element, what undoing it takes: a step's previous key path, with the previous prefix when the step is prefixed; the name of an element inside a construct; what {@see MagpieRSS::enter()} keeps of a container */
	private $saved = array();

	/** @var string[] per open step, its local name: what {@see MagpieRSS::$stack} holds, which this class brings up to date once its own parse is over */
	private $steps = array();

	/** @var string {@see MagpieRSS::$stack} joined outer to inner with '_': the key text lands under, cut one byte past {@see MagpieRSS::$longest} where names are joined */
	private $path = '';

	/** @var string the content construct captured so far, handed to {@see MagpieRSS::append_content()} when it ends */
	private $markup = '';

	/** @var array<string, string> what an element does by local name, once the root has said which feed this is; any other element is a step */
	private $roles = array();

	/** @var array<string, array{0: string, 1: string|false, 2: string}> {@see MagpieRSS::nameOf()} by name as written, one entry per distinct element, attribute, entity or processing-instruction name */
	private $names = array();

	/** @var int the most distinct names the parse may meet */
	private $distinct = PHP_INT_MAX;

	/** @var int the most bytes a key and its prefix may take together while parsing */
	private $longest = PHP_INT_MAX;

	/** @var int bytes the parse may still spend on keys new to their array, each with its prefix */
	private $volume = PHP_INT_MAX;

	/** @var int|float bytes the entity references {@see MagpieRSS::vet()} has yet to read may still expand to */
	private $allowance = PHP_INT_MAX;

	/** @var array<string, array{0: int, 1: int}|int|float|null>|null by name ('%' first for a parameter entity), where each entity the document type declaration declares keeps its value in the document, or what one reference expands to once {@see MagpieRSS::expansion()} has measured it, or null for an external one; null without a document type declaration */
	private $entities = null;

	/** @var string|null why the event methods ignore the rest of the document, once it passed a limit */
	private $refusal = null;

	/** @var int how far into the document {@see MagpieRSS::vet()} lets libxml read */
	private $reach = 0;

	/** @var string|null the limit the document passes at {@see MagpieRSS::$reach}; null when it reads to its end or stops being XML there */
	private $verdict = null;

	/** @var int[] offsets between tokens where {@see MagpieRSS::read()} divides the document: after 8388608 bytes or 100000 tags and attributes, and either side of a token of 1048576 bytes or more */
	private $pieces = array();

	/** @var string the later pieces of the text run being read, until an element event or 65536 bytes hand them to {@see MagpieRSS::append()} */
	private $text = '';

	/** @var string the key path of the text run whose first piece last went straight to {@see MagpieRSS::append()} */
	private $run = '';

	/** @var bool whether this class itself is parsing, with no subclass to see each call ext/xml makes: text then reaches {@see MagpieRSS::append()} in fewer pieces */
	private $gathering = false;

	/** @var array<string, string> the grammar {@see MagpieRSS::grammar()} reads a document by, under each encoding label libxml reads without a converter */
	private static $charsets = array('' => 'utf8', 'UTF-8' => 'utf8', 'UTF8' => 'utf8', 'US-ASCII' => 'ascii', 'ASCII' => 'ascii', 'ISO-8859-1' => 'latin', 'ISO-LATIN-1' => 'latin');

	/** @var array<string, array<string, string>> {@see MagpieRSS::grammar()} by charset, once built */
	private static $grammars = array();

	/** @var string a pattern for one character of XML's white space */
	private static $space = '[ \t\r\n]';

	/** @var int the error code libxml gives a reference to an entity no declaration it has read names, in a document whose declarations it has not all read; libxml reads on past it */
	private static $undeclared = 27;

	/** @var string what the parse refuses a document with more distinct names than {@see MagpieRSS::$distinct} for */
	private static $manyNames = 'The document uses more than 4096 distinct names';

	/** @var array<string, int> the keywords of markup declarations, which are not names */
	private static $keywords = array('ANY' => 1, 'ATTLIST' => 1, 'CDATA' => 1, 'ELEMENT' => 1, 'EMPTY' => 1, 'ENTITIES' => 1, 'ENTITY' => 1, 'ID' => 1, 'IDREF' => 1, 'IDREFS' => 1, 'NDATA' => 1, 'NMTOKEN' => 1, 'NMTOKENS' => 1, 'NOTATION' => 1, 'PUBLIC' => 1, 'SYSTEM' => 1);

	/**
	 * Parses the whole document and normalises the result; never throws, and reports problems through {@see MagpieRSS::error()}.
	 *
	 * @param string $source the feed document
	 * @param string|null $output_encoding UTF-8, ISO-8859-1 or US-ASCII; empty for UTF-8
	 * @param string|null $input_encoding the encoding of a document whose byte-order mark and declaration name none; UTF-8 when empty
	 * @param bool $detect_encoding record a declared encoding in {@see MagpieRSS::$_source_encoding}
	 */
	public function __construct($source, $output_encoding = CHARSET, $input_encoding = null, $detect_encoding = true)
	{
		if(!function_exists('xml_parser_create'))
		{
			$this->error('MagpieRSS needs the PHP xml extension, which is not loaded');
			return;
		}

		if(!is_scalar($source) && $source !== null)
		{
			$this->error('MagpieRSS can only parse a feed given as a string');
			return;
		}

		if(!is_string($source))
		{
			$source = (string) $source;
		}

		list($parser, $source) = $this->create_parser($source, $output_encoding, $input_encoding, $detect_encoding);
		$this->parse($parser, $source);
		$this->normalize();
	}

	/**
	 * Element-start event, as ext/xml raises it.
	 *
	 * @param mixed $p ignored
	 * @param string $element element name as written in the document
	 * @param array<string, string> $attrs attribute values by name
	 * @return void
	 */
	public function feed_start_element($p, $element, $attrs)
	{
		if($this->refusal !== null)
		{
			return;
		}

		if($this->text !== '')
		{
			$this->fileText();
		}

		if($this->depth >= $this->deepest)
		{
			$this->stop('Elements nest deeper than 256');

			return;
		}

		if(!isset($this->names[$element]) || ($attrs !== array() && (!is_array($attrs) || array_diff_key($attrs, $this->names) !== array())))
		{
			$attrs = is_array($attrs) ? $attrs : array();

			if(!$this->known(array_merge(array($element), array_map('strval', array_keys($attrs)))))
			{
				$this->stop(self::$manyNames);

				return;
			}
		}

		$depth = $this->depth++;
		$known = $this->names[$element];

		if($this->incontent !== false)
		{
			$this->kinds[$depth] = 'markup';
			$this->saved[$depth] = $known[0];
			$this->markup .= $this->startTag($known[0], $attrs);

			return;
		}

		if($this->feed_type === null)
		{
			$this->kinds[$depth] = 'ignored';
			$this->openRoot($known[2], $attrs);

			return;
		}

		$local = $known[2];
		$role = isset($this->roles[$local]) ? $this->roles[$local] : 'step';

		if($role === 'step' || ($role === 'box' && ($known[1] !== false || $this->current_namespace !== false)))
		{
			$this->kinds[$depth] = 'step';
			$this->saved[$depth] = $this->path;
			$this->steps[$depth] = $local;
			$this->path = $this->path === '' ? $local : $this->path.'_'.$local;

			if(!$this->gathering)
			{
				array_unshift($this->stack, $local);
			}

			if(isset($this->path[$this->longest]))
			{
				$this->path = (string) substr($this->path, 0, $this->longest + 1);
			}

			if($known[1] !== false)
			{
				$this->kinds[$depth] = 'prefixed';
				$this->saved[$depth] = array($this->saved[$depth], $this->current_namespace);
				$this->current_namespace = $known[1];
			}
		}
		elseif($role === 'construct')
		{
			$this->kinds[$depth] = 'construct';
			$this->incontent = $local === 'content' ? 'atom_content' : $local;
		}
		elseif($role === 'link')
		{
			$this->kinds[$depth] = 'ignored';
			$this->openLink($attrs);
		}
		else
		{
			$this->enter($role === 'box' ? $local : $role, $attrs);
		}
	}

	/**
	 * Character-data event: text, CDATA and resolved references, in runs that may split anywhere.
	 *
	 * @param mixed $p ignored
	 * @param string $text
	 * @return void
	 */
	public function feed_cdata($p, $text)
	{
		if($this->refusal !== null)
		{
			return;
		}

		if($this->incontent !== false)
		{
			$this->markup .= $this->depth > 0 && $this->kinds[$this->depth - 1] === 'markup' ? str_replace(array('&', '<', '>'), array('&amp;', '&lt;', '&gt;'), $text) : $text;
		}
		elseif(!$this->gathering)
		{
			$this->append($this->path, $text);
		}
		elseif($this->text !== '')
		{
			$this->text .= $text;

			if(isset($this->text[65535]))
			{
				$this->fileText();
			}
		}
		elseif($this->path === $this->run)
		{
			$this->text = $text;
		}
		elseif($this->path !== '')
		{
			$this->run = $this->path;
			$this->append($this->path, $text);
		}
	}

	/**
	 * Element-end event: closes whatever the matching start opened, whatever name it carries.
	 *
	 * @param mixed $p ignored
	 * @param string $el ignored
	 * @return void
	 */
	public function feed_end_element($p, $el)
	{
		if($this->refusal !== null || $this->depth === 0)
		{
			return;
		}

		if($this->text !== '')
		{
			$this->fileText();
		}

		$depth = --$this->depth;
		$kind = $this->kinds[$depth];

		if($kind === 'step' || $kind === 'prefixed')
		{
			if($kind === 'step')
			{
				$this->path = $this->saved[$depth];
			}
			else
			{
				$this->path = $this->saved[$depth][0];
				$this->current_namespace = $this->saved[$depth][1];
			}

			if(!$this->gathering)
			{
				array_shift($this->stack);
			}
		}
		elseif($kind === 'markup')
		{
			$this->markup .= '</'.$this->saved[$depth].'>';
		}
		elseif($kind === 'construct')
		{
			$this->fileMarkup();
			$this->incontent = false;
		}
		elseif($kind === 'container')
		{
			$this->leave($this->saved[$depth]);
		}
	}

	/**
	 * Appends $str2 to $str1, treating an unset or null $str1 as ''.
	 *
	 * @param string|null $str1
	 * @param string $str2
	 * @return void
	 */
	public function concat(&$str1, $str2 = "")
	{
		if(!isset($str1))
		{
			$str1 = '';
		}

		$str1 .= $str2;
	}

	/**
	 * Appends to the Atom content construct being captured, in the current item or else the channel; does nothing outside a construct, without either, or when the construct's key holds an array.
	 *
	 * @param string $text
	 * @return void
	 */
	public function append_content($text)
	{
		if($this->incontent === false)
		{
			return;
		}

		if($this->initem)
		{
			$target = &$this->current_item;
		}
		elseif($this->inchannel)
		{
			$target = &$this->channel;
		}
		else
		{
			return;
		}

		if(!isset($target[$this->incontent]) || !is_array($target[$this->incontent]))
		{
			$this->put($target, $this->incontent, $text);
		}
	}

	/**
	 * Appends text to key $el of the item, text input, image or channel being read, under {@see MagpieRSS::$current_namespace} when set; while parsing, a key new to its array must fit the parse's limits.
	 *
	 * @param string $el key; nothing happens when it is empty, holds an array or no container is open
	 * @param string $text
	 * @return void
	 */
	public function append($el, $text)
	{
		if(!is_string($el))
		{
			$el = is_scalar($el) ? (string) $el : '';
		}

		if($el === '')
		{
			return;
		}

		if($this->initem && $this->current_namespace === false)
		{
			$bucket = &$this->current_item;
		}
		else
		{
			$bucket = &$this->bucket($this->current_namespace);
		}

		if($bucket === null)
		{
			return;
		}

		if(!isset($bucket[$el]))
		{
			$weight = strlen($el) + ($this->current_namespace === false ? 0 : strlen($this->current_namespace));

			if($weight > $this->longest || ($this->volume -= $weight) < 0)
			{
				$this->bucket($this->current_namespace, null);
				$this->stop($weight > $this->longest ? 'An element path is longer than 1024 bytes' : 'Element paths outgrew the document');

				return;
			}
		}
		elseif(is_array($bucket[$el]))
		{
			return;
		}

		$this->put($bucket, $el, $text);
	}

	/**
	 * Files a content construct the document left open, then fills the keys every feed of a type is expected to have (RSS 'tagline' and 'summary', Atom 'description', 'date_timestamp' ...); safe to call again.
	 *
	 * @return void
	 */
	public function normalize()
	{
		$this->fileMarkup();

		if($this->is_atom())
		{
			$this->normaliseAtom();
		}
		elseif($this->feed_type === RSS)
		{
			$this->normaliseRss();
		}
	}

	/**
	 * Tells whether the document is RSS.
	 *
	 * @return string|false|null the RSS version, null for RSS that names none, or false for anything else
	 */
	public function is_rss()
	{
		return $this->feed_type === RSS ? $this->feed_version : false;
	}

	/**
	 * Tells whether the document is Atom.
	 *
	 * @return string|false the Atom version ('1.0' when the feed names none), or false for anything else
	 */
	public function is_atom()
	{
		return $this->feed_type === ATOM ? $this->feed_version : false;
	}

	/**
	 * Builds the ext/xml parser and the bytes to feed it; sets {@see MagpieRSS::$encoding} when an output encoding is given.
	 *
	 * @param string $source the feed document
	 * @param string|null $out_enc UTF-8, ISO-8859-1 or US-ASCII; anything else gives UTF-8 and a warning
	 * @param string|null $in_enc see {@see MagpieRSS::php4_create_parser()}
	 * @param bool $detect see {@see MagpieRSS::php4_create_parser()}
	 * @return array{0: resource|XMLParser, 1: string}
	 */
	public function create_parser($source, $out_enc, $in_enc, $detect)
	{
		list($parser, $source) = $this->php4_create_parser($source, $in_enc, $detect);
		$target = $this->xmlEncoding($out_enc);
		xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, $target === false ? 'UTF-8' : $target);

		if($this->ascii($out_enc, CASE_UPPER) !== '')
		{
			$this->encoding = $out_enc;

			if($target === false)
			{
				$this->error('MagpieRSS cannot write '.$out_enc.' and writes UTF-8 instead', E_USER_NOTICE);
			}
		}

		return array($parser, $source);
	}

	/**
	 * Builds an ext/xml parser with case folding off; libxml reads the input by the document's byte-order mark and declaration, else as UTF-8, whatever $in_enc says, and $in_enc (when $detect is false and ext/xml accepts it) only sets the default output encoding.
	 *
	 * @param string|null $in_enc
	 * @param bool $detect
	 * @return resource|XMLParser
	 */
	public function php5_create_parser($in_enc, $detect)
	{
		$fixed = $detect ? false : $this->xmlEncoding($in_enc);
		$parser = $fixed === false ? xml_parser_create() : xml_parser_create($fixed);
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);

		return $parser;
	}

	/**
	 * Settles the input encoding (the byte-order mark's, an EBCDIC document's declared code page or IBM037, else the declaration's, else $in_enc, else UTF-8), records a declared one in {@see MagpieRSS::$_source_encoding} when detecting, and converts or relabels the bytes so ext/xml reads them as that.
	 *
	 * @param string $source the feed document
	 * @param string|null $in_enc the encoding of a document that names none
	 * @param bool $detect record a declared encoding
	 * @return array{0: resource|XMLParser, 1: string} the parser and the bytes to feed it
	 */
	public function php4_create_parser($source, $in_enc, $detect)
	{
		if(!is_string($source))
		{
			$source = (string) $source;
		}

		list($marked, $markLength) = $this->byteOrderMark($source);
		$declaration = $this->declaration($marked === 'EBCDIC' ? (string) $this->converted((string) substr($source, 0, 512), 'IBM037') : $source, $markLength);
		$declared = is_array($declaration) ? $declaration[2] : null;
		$marked = $marked === 'EBCDIC' ? ($declared === null ? 'IBM037' : $declared) : $marked;
		$label = $this->inputEncoding($in_enc, $detect, $marked, $declared);

		if($this->known_encoding($label) === false && ($converted = $this->converted((string) substr($source, $markLength), $label)) !== false)
		{
			$source = $this->declaring($this->withoutMark($converted), 'UTF-8');
			$label = 'UTF-8';
		}
		elseif(!$this->announces($marked, $declared, $label))
		{
			$source = $this->declaring((string) substr($source, $markLength), $label);
		}

		return array($this->php5_create_parser($label, false), $source);
	}

	/**
	 * Upper-cases an encoding name and says whether it is handed to ext/xml unconverted.
	 *
	 * @param string|null $enc
	 * @return string|false the upper-cased name, or false when it is not one of {@see MagpieRSS::$_KNOWN_ENCODINGS}
	 */
	public function known_encoding($enc)
	{
		$enc = $this->ascii($enc, CASE_UPPER);

		return in_array($enc, $this->_KNOWN_ENCODINGS, true) ? $enc : false;
	}

	/**
	 * Records a problem in {@see MagpieRSS::$WARNING} (notice levels) or {@see MagpieRSS::$ERROR}, then logs it, or raises it when MAGPIE_DEBUG is on.
	 *
	 * @param string $errormsg
	 * @param int $lvl an E_* level; E_USER_ERROR is raised as E_USER_WARNING
	 * @return void
	 */
	public function error($errormsg, $lvl = E_USER_WARNING)
	{
		$notice = $lvl === E_NOTICE || $lvl === E_USER_NOTICE;

		if($notice)
		{
			$this->WARNING = $errormsg;
		}
		else
		{
			$this->ERROR = $errormsg;
		}

		if(defined('MAGPIE_DEBUG') && MAGPIE_DEBUG)
		{
			trigger_error($errormsg, $notice ? E_USER_NOTICE : ($lvl === E_USER_DEPRECATED ? E_USER_DEPRECATED : E_USER_WARNING));
		}
		else
		{
			error_log($errormsg);
		}
	}

	/**
	 * Parses the document as far as {@see MagpieRSS::vet()} lets libxml read, and reports a limit passed or else libxml's error through {@see MagpieRSS::error()}.
	 *
	 * @param resource|XMLParser $parser
	 * @param string $source
	 * @return void
	 */
	private function parse($parser, $source)
	{
		$length = strlen($source);
		$this->parser = $parser;
		$this->distinct = 4096;
		$this->deepest = 256;
		$this->longest = 1024;
		$this->volume = 8 * $length + 65536;
		$this->allowance = 3 * $length + 65536;
		$this->gathering = get_class($this) === __CLASS__;
		$this->vet($source);
		xml_set_element_handler($parser, array($this, 'feed_start_element'), array($this, 'feed_end_element'));
		xml_set_character_data_handler($parser, array($this, 'feed_cdata'));
		$parsed = $this->reach === 0 && $this->verdict !== null || $this->read($parser, $source);
		$this->fileText();

		for($depth = $this->depth; $this->gathering && $depth > 0 && $this->kinds[$depth - 1] !== 'container'; $depth--)
		{
			if($this->kinds[$depth - 1] === 'step' || $this->kinds[$depth - 1] === 'prefixed')
			{
				$this->stack[] = $this->steps[$depth - 1];
			}
		}

		if($this->refusal !== null)
		{
			$this->error($this->refusal);
		}
		elseif($this->verdict !== null && ($parsed || xml_get_error_code($parser) === self::$undeclared))
		{
			$this->error($this->verdict);
		}
		elseif(!$parsed || $this->reach < $length)
		{
			$this->error($parsed ? 'The document is not XML'.$this->placed($source, $this->reach) : $this->located((string) xml_error_string(xml_get_error_code($parser))));
		}

		$this->parser = $this->refusal = $this->verdict = $this->entities = null;
		$this->pieces = array();
		$this->run = '';
		$this->gathering = false;
		$this->distinct = $this->deepest = $this->longest = $this->volume = $this->allowance = PHP_INT_MAX;
	}

	/**
	 * Reads the document token by token before libxml does and sets how far libxml may read: to the end of a document that keeps XML's grammar and the parse's limits, else to just before the token that passes a limit, or just past the byte that breaks the grammar, so that libxml reports it and never reads what follows.
	 *
	 * @param string $source the bytes ext/xml will read
	 * @return void
	 */
	private function vet($source)
	{
		$length = strlen($source);
		list($marked, $at) = $this->byteOrderMark($source);
		$declaration = $this->declaration($source, $at);
		$label = is_array($declaration) ? (string) $declaration[2] : '';
		$charset = isset(self::$charsets[$label]) ? self::$charsets[$label] : null;

		if($declaration === false || ($marked !== null && ($marked !== 'UTF-8' || $charset !== 'utf8')))
		{
			$this->cut(0, 'MagpieRSS cannot convert the document to UTF-8');

			return;
		}

		$at += is_array($declaration) ? $declaration[3] : 0;

		if($charset === null)
		{
			$this->cut($at);

			return;
		}

		$grammar = self::grammar($charset);

		for($base = $size = $piece = $names = 0, $window = '', $pattern = $grammar['prolog']; $at !== false && $at < $length; )
		{
			if($at >= $base + $size)
			{
				$base = $at;
				$window = (string) substr($source, $at, 32768);
				$size = strlen($window);
			}

			if($at - $piece > 8388608 || $names > 100000)
			{
				$this->pieces[] = $piece = $at;
				$names = 0;
			}

			$passed = preg_match($pattern, $window, $run, 0, $at - $base) === 1 ? strlen($run[0]) : 0;

			if($passed > 0 && !$this->account($source, $at, $run[0], $grammar, false))
			{
				return;
			}

			$names += $passed > 0 ? substr_count($run[0], '<') + substr_count($run[0], '=') : 0;
			$at += $passed;
			$token = $at;
			$opens = $at < $base + $size ? (string) substr($source, $at, 9) : '';

			if($pattern !== $grammar['run'] && $opens !== '' && strncmp($opens, '<!--', 4) !== 0 && strncmp($opens, '<?', 2) !== 0)
			{
				$pattern = $opens === '<!DOCTYPE' && $this->entities === null ? $pattern : $grammar['run'];
				$at = $pattern === $grammar['run'] ? $at : $this->doctype($source, $at, $grammar);
			}
			elseif($opens !== '')
			{
				$at = $this->token($source, $at, $grammar);
				$names += 257;
			}

			if($at - $token > 1048576)
			{
				array_push($this->pieces, $token, $piece = $at);
			}
		}

		if($at !== false)
		{
			$this->reach = $length;
		}
	}

	/**
	 * Ends what libxml may read at $at, for the limit the document passes there when $verdict names one.
	 *
	 * @param int $at
	 * @param string|null $verdict
	 * @return false
	 */
	private function cut($at, $verdict = null)
	{
		$this->reach = $at;
		$this->verdict = $verdict;

		return false;
	}

	/**
	 * Ends what libxml may read just past the byte at $at, which breaks XML's grammar, so that libxml reports it.
	 *
	 * @param string $source
	 * @param int $at
	 * @return false
	 */
	private function fault($source, $at)
	{
		return $this->cut(min(strlen($source), $at + 4));
	}

	/**
	 * Reads the one token at $at that {@see MagpieRSS::vet()} could not read in its stride: one longer than its window, a start tag of more than 1280 bytes or with '>' or an entity reference in a value, or one that breaks the grammar.
	 *
	 * @param string $source
	 * @param int $at
	 * @param array<string, string> $grammar see {@see MagpieRSS::grammar()}
	 * @return int|false where the token ends; false once {@see MagpieRSS::cut()} has ended the reading
	 */
	private function token($source, $at, array $grammar)
	{
		$kind = $source[$at] === '<' ? (string) substr($source, $at + 1, 1) : $source[$at];

		if($kind === '!' && substr_compare($source, '<!--', $at, 4) === 0)
		{
			$hyphens = strpos($source, '--', $at + 4);
			$closer = strpos($source, '-->', $at + 4);

			return $hyphens === $closer ? $this->delimited($source, $at + 4, '-->', $grammar) : $this->cut($at, 'A comment holds a double hyphen'.$this->placed($source, $at));
		}

		if($kind === '!')
		{
			return substr_compare($source, '<![CDATA[', $at, 9) === 0 ? $this->delimited($source, $at + 9, ']]>', $grammar) : $this->fault($source, $at + 2);
		}

		if($kind === '?')
		{
			if(preg_match($grammar['target'], $source, $target, 0, $at) !== 1)
			{
				return $this->fault($source, $at + 2);
			}

			return $this->known(array((string) substr($target[0], 2))) ? $this->delimited($source, $at + strlen($target[0]), '?>', $grammar) : $this->cut($at, self::$manyNames);
		}

		if($kind === '&' || $kind === '/' || $source[$at] !== '<')
		{
			$pattern = $grammar[$kind === '&' ? 'reference' : ($kind === '/' ? 'end' : 'character')];

			if(preg_match($pattern, $source, $token, 0, $at) !== 1)
			{
				return $this->fault($source, $at + ($kind === '/' ? 2 : ($kind === '&' ? 1 : 0)));
			}

			return $this->account($source, $at, $token[0], $grammar, false) ? $at + strlen($token[0]) : false;
		}

		if(preg_match($grammar['crowded'], $source, $tag, 0, $at) === 1)
		{
			return $this->cut($at, 'An element carries more than 256 attributes'.$this->placed($source, $at));
		}

		if(preg_match($grammar['tag'], $source, $tag, 0, $at) !== 1)
		{
			return $this->fault($source, $at + (preg_match($grammar['opening'], $source, $tag, 0, $at) === 1 ? strlen($tag[0]) : 0));
		}

		return $this->account($source, $at, $tag[0], $grammar, true) ? $at + strlen($tag[0]) : false;
	}

	/**
	 * Reads a comment, CDATA section or processing instruction from the end of its opener to its closer: it may hold any character XML allows.
	 *
	 * @param string $source
	 * @param int $from where its text starts
	 * @param string $closer
	 * @param array<string, string> $grammar see {@see MagpieRSS::grammar()}
	 * @return int|false where it ends; false once {@see MagpieRSS::cut()} has ended the reading, at a character XML forbids or at the document's end
	 */
	private function delimited($source, $from, $closer, array $grammar)
	{
		$close = strpos($source, $closer, $from);
		$end = $close === false ? strlen($source) : $close;
		$text = (string) substr($source, $from, $end - $from);

		if(preg_match($grammar['characters'], $text) !== 1)
		{
			return $this->fault($source, $from + (preg_match($grammar['allowed'], $text, $allowed) === 1 ? strlen($allowed[0]) : 0));
		}

		return $close === false ? $this->cut($end) : $close + strlen($closer);
	}

	/**
	 * Gives libxml the document up to {@see MagpieRSS::$reach}, divided at {@see MagpieRSS::$pieces}, until the event methods refuse it, and tells libxml the document ends there unless a limit does; meanwhile PHP neither collects libxml's reports nor raises them as warnings, and holds none afterwards.
	 *
	 * @param resource|XMLParser $parser
	 * @param string $source
	 * @return bool whether libxml read it without an error
	 */
	private function read($parser, $source)
	{
		$ends = array_merge($this->pieces, array($this->reach));
		$parsed = true;
		$collecting = function_exists('libxml_use_internal_errors') && libxml_use_internal_errors(false);
		$previous = null;
		$previous = set_error_handler(function ($level, $message) use (&$previous)
		{
			return strncmp((string) $message, 'xml_parse()', 11) === 0 || ($previous !== null && call_user_func_array($previous, func_get_args()) !== false);
		}, E_WARNING);

		try
		{
			for($from = 0; $ends !== array() && $this->refusal === null; $from = $to)
			{
				$to = max($from, array_shift($ends));
				$parsed = xml_parse($parser, $from === 0 && $to === strlen($source) ? $source : (string) substr($source, $from, $to - $from), $ends === array() && $this->verdict === null) === 1 && $parsed;
			}

			if($this->entities !== null)
			{
				xml_parse(xml_parser_create(), '<!DOCTYPE a [<!ATTLIST a b (c|c) "c">]><a/>', true);
			}
		}
		finally
		{
			restore_error_handler();

			if($collecting)
			{
				libxml_use_internal_errors(true);
			}
		}

		return $parsed;
	}

	/**
	 * Reads the document type declaration at $at and its internal subset, noting the entities it declares in {@see MagpieRSS::$entities}; the subset may hold 4096 declarations and references, and 65536 bytes besides its entity declarations.
	 *
	 * @param string $source
	 * @param int $at
	 * @param array<string, string> $grammar see {@see MagpieRSS::grammar()}
	 * @return int|false where it ends; false once {@see MagpieRSS::cut()} has ended the reading
	 */
	private function doctype($source, $at, array $grammar)
	{
		if(preg_match($grammar['doctype'], $source, $head, 0, $at) !== 1)
		{
			return $this->fault($source, $at + 9);
		}

		$this->entities = array();
		$at += strlen($head[0]);

		if((string) substr($source, $at, 1) === '[')
		{
			for($at++, $declarations = $references = $bulk = 0; (string) substr($source, $at += strspn($source, " \t\r\n", $at), 1) !== ']'; $at = $next)
			{
				$next = $this->declared($source, $at, $grammar, $references, $bulk);

				if($next === false)
				{
					return false;
				}

				if(++$declarations > 4096 || $references > 4096 || $bulk > 65536)
				{
					return $this->cut($at, 'The document type declaration holds more than '.($bulk > 65536 ? '65536 bytes besides its entity declarations' : ($references > 4096 ? '4096 references' : '4096 markup declarations')).$this->placed($source, $at));
				}
			}

			$at++;
		}

		return preg_match($grammar['closed'], $source, $closed, 0, $at) === 1 ? $at + strlen($closed[0]) : $this->fault($source, $at);
	}

	/**
	 * Reads one declaration, comment, processing instruction or parameter-entity reference of the internal subset.
	 *
	 * @param string $source
	 * @param int $at
	 * @param array<string, string> $grammar see {@see MagpieRSS::grammar()}
	 * @param int $references raised by each reference it writes outside an entity's value
	 * @param int $bulk raised by its length, unless it declares an entity
	 * @return int|false where it ends; false once {@see MagpieRSS::cut()} has ended the reading
	 */
	private function declared($source, $at, array $grammar, &$references, &$bulk)
	{
		if(preg_match($grammar['entity'], $source, $entity, 0, $at) === 1)
		{
			return $this->declareEntity($source, $at, $entity, $grammar);
		}

		if(preg_match($grammar['parameter'], $source, $reference, 0, $at) === 1)
		{
			$references++;
			$value = isset($this->entities['%'.$reference[1]]) ? $this->entities['%'.$reference[1]] : array(0, 0);

			if(strspn($source, " \t\r\n", $value[0], $value[1]) < $value[1])
			{
				return $this->cut($at, 'The document type declaration expands a parameter entity'.$this->placed($source, $at));
			}

			return $this->known(array($reference[1])) ? $at + strlen($reference[0]) : $this->cut($at, self::$manyNames);
		}

		if(preg_match($grammar['markup'], $source, $markup, 0, $at) === 1)
		{
			$bare = (string) preg_replace('/"[^"]*+"|\'[^\']*+\'/', ' ', $markup[0]);
			$references += substr_count($markup[0], '&') + substr_count($markup[0], '%');
			$bulk += strlen($markup[0]);

			if($bulk > 65536 || preg_match('/\((?:[^()|]*+\|){256}/', $bare) === 1)
			{
				return $bulk > 65536 ? $at + strlen($markup[0]) : $this->cut($at, 'An attribute type lists more than 256 values'.$this->placed($source, $at));
			}

			$words = array_diff_key(array_flip(preg_split('/[ \t\r\n()|,?*+>]++|\#[A-Z]++/', (string) substr($bare, 2), -1, PREG_SPLIT_NO_EMPTY)), self::$keywords);

			if(!$this->known(array_map('strval', array_keys($words))))
			{
				return $this->cut($at, self::$manyNames);
			}

			return $this->account($source, $at, $markup[0], $grammar, true) ? $at + strlen($markup[0]) : false;
		}

		$next = (string) substr($source, $at, 2) === '<?' || (string) substr($source, $at, 4) === '<!--' ? $this->token($source, $at, $grammar) : $this->fault($source, $at);
		$bulk += $next === false ? 0 : $next - $at;

		return $next;
	}

	/**
	 * Notes the entity a declaration of the internal subset declares, the first of a name winning; its value may hold no '%'.
	 *
	 * @param string $source
	 * @param int $at where the declaration starts
	 * @param array<int, string> $entity what {@see MagpieRSS::grammar()}'s 'entity' matched there: all of it, '%' and white space for a parameter entity, the name, and the quote that opens a value
	 * @param array<string, string> $grammar see {@see MagpieRSS::grammar()}
	 * @return int|false where the declaration ends; false once {@see MagpieRSS::cut()} has ended the reading
	 */
	private function declareEntity($source, $at, array $entity, array $grammar)
	{
		$next = $at + strlen($entity[0]);
		$value = null;

		if(isset($entity[3]))
		{
			$close = strpos($source, $entity[3], $next);
			$end = $close === false ? strlen($source) : $close;
			$percent = $next + ($end > $next ? strcspn($source, '%', $next, $end - $next) : 0);

			if($percent < $end || $close === false)
			{
				return $percent < $end ? $this->fault($source, $percent) : $this->cut($end);
			}

			if(preg_match($grammar['closed'], $source, $closed, 0, $close + 1) !== 1)
			{
				return $this->fault($source, $close + 1);
			}

			$value = array($next, $close - $next);
			$next = $close + 1 + strlen($closed[0]);
		}

		$key = ($entity[1] === '' ? '' : '%').$entity[2];

		if(!array_key_exists($key, $this->entities))
		{
			$this->entities[$key] = $value;
		}

		return $this->known(array($entity[2])) ? $next : $this->cut($at, self::$manyNames);
	}

	/**
	 * Counts what a stretch of the document that keeps the grammar brings: the target of each processing instruction, and, under a document type declaration, the name of each entity reference and what it expands to.
	 *
	 * @param string $source
	 * @param int $at where the stretch starts
	 * @param string $text the stretch
	 * @param array<string, string> $grammar see {@see MagpieRSS::grammar()}
	 * @param bool $whole whether the stretch is a start tag or a declaration, which libxml reads as a whole and whose values the grammar has yet to hold to their references
	 * @return bool false once {@see MagpieRSS::cut()} has ended the reading: at $at when the names pass their limit or $whole is set, else at the reference that overdraws the allowance
	 */
	private function account($source, $at, $text, array $grammar, $whole)
	{
		if($this->instructs($text) && (preg_match_all($grammar['targets'], $text, $instructed) === false || !$this->known(array_keys(array_flip($instructed[1])))))
		{
			return isset($instructed[1]) ? $this->cut($at, self::$manyNames) : $this->fault($source, $at);
		}

		$loose = $whole ? substr_count($text, '&') - substr_count($text, '&lt;') - substr_count($text, '&gt;') - substr_count($text, '&amp;') - substr_count($text, '&quot;') - substr_count($text, '&apos;') : 0;

		if($whole ? $loose === 0 : $this->entities === null || strpos($text, '&') === false)
		{
			return true;
		}

		$counts = $this->references($text, $grammar['referred']);
		$left = $this->allowance;

		if($counts !== null && $this->entities !== null && !$this->known(array_keys(array_diff_key($counts, array('' => 0)))))
		{
			return $this->cut($at, self::$manyNames);
		}

		if($counts === null || ($whole && array_sum($counts) !== $loose))
		{
			return $this->fault($source, $at);
		}

		if($this->entities === null)
		{
			return true;
		}

		foreach($counts as $name => $count)
		{
			$this->allowance -= $count * $this->expansion($source, (string) $name, 0);
		}

		if($this->allowance >= 0)
		{
			return true;
		}

		foreach(!$whole && preg_match_all($grammar['referred'], $text, $referred, PREG_OFFSET_CAPTURE) > 0 ? $referred[0] : array() as $index => $reference)
		{
			if(($left -= $this->expansion($source, $referred[1][$index][0], 0)) < 0)
			{
				$at += $reference[1];

				break;
			}
		}

		return $this->cut($at, 'Entity expansion outgrew the document'.$this->placed($source, $at));
	}

	/**
	 * Counts the references a text writes, by what $pattern captures of each, reading 65536 bytes or so at a time, and no further once they name more than {@see MagpieRSS::$distinct}.
	 *
	 * @param string $text
	 * @param string $pattern
	 * @return array<string, int>|null null when the pattern could not be matched
	 */
	private function references($text, $pattern)
	{
		$counts = array();

		for($from = 0, $length = strlen($text); $from < $length && count($counts) <= $this->distinct; $from = $to)
		{
			$to = $length - $from > 65536 ? strpos($text, '&', $from + 65536) : $length;
			$to = $to === false ? $length : $to;

			if(preg_match_all($pattern, $from === 0 && $to === $length ? $text : (string) substr($text, $from, $to - $from), $referred) === false)
			{
				return null;
			}

			foreach(array_count_values($referred[1]) as $name => $count)
			{
				$counts[$name] = $count + (isset($counts[$name]) ? $counts[$name] : 0);
			}
		}

		return $counts;
	}

	/**
	 * Tells whether a stretch of the document may open a processing instruction whose target is a name the document has not written before.
	 *
	 * @param string $text
	 * @return bool
	 */
	private function instructs($text)
	{
		foreach(strpos($text, '<?') === false ? array() : array_flip(array_slice(explode('<?', $text), 1)) as $opened => $index)
		{
			if(!isset($this->names[(string) substr($opened, 0, strcspn($opened, " \t\r\n?"))]))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Says how many bytes one reference to an entity can deliver in UTF-8, with every entity its value refers to expanded.
	 *
	 * @param string $source
	 * @param string $name
	 * @param int $depth how many entities deep the reference is
	 * @return int|float 0 for an entity the document type declaration does not declare with a value; INF past 64 levels or for an entity that reaches itself
	 */
	private function expansion($source, $name, $depth)
	{
		$entity = isset($this->entities[$name]) ? $this->entities[$name] : 0;

		if(!is_array($entity) || $depth > 64)
		{
			return is_array($entity) ? INF : $entity;
		}

		$this->entities[$name] = INF;
		$value = preg_replace_callback('/&\#(x?+)0*+([0-9A-Fa-f]{1,7});/', function (array $numbered)
		{
			return html_entity_decode('&#'.$numbered[1].$numbered[2].';', ENT_QUOTES, 'UTF-8');
		}, (string) substr($source, $entity[0], $entity[1]));
		$counts = $value === null ? null : $this->references($value, '/&([^;&]++);/');
		$weight = $counts === null || count($counts) > $this->distinct ? INF : strlen($value) + array_sum(array_slice(count_chars($value, 0), 128));

		foreach((array) $counts as $inner => $count)
		{
			$weight += $count * $this->expansion($source, (string) $inner, $depth + 1);
		}

		return $this->entities[$name] = $weight;
	}

	/**
	 * Counts the names among $names that the document has not written before.
	 *
	 * @param string[] $names
	 * @return bool whether the document still writes no more distinct names than the parse allows
	 */
	private function known(array $names)
	{
		foreach($names as $name)
		{
			if(!isset($this->names[$name]))
			{
				$this->names[$name] = $this->nameOf($name);
			}
		}

		return count($this->names) <= $this->distinct;
	}

	/**
	 * Stops the event methods for $reason, located where the parser stands; the first reason stands.
	 *
	 * @param string $reason
	 * @return void
	 */
	private function stop($reason)
	{
		if($this->refusal === null)
		{
			$this->refusal = $this->located($reason);
		}
	}

	/**
	 * Appends the parser's position to a message, in the form ext/xml errors are reported.
	 *
	 * @param string $message
	 * @return string
	 */
	private function located($message)
	{
		return $message.' at line '.xml_get_current_line_number($this->parser).', column '.xml_get_current_column_number($this->parser);
	}

	/**
	 * Writes the line and the column of the byte at $at in the form {@see MagpieRSS::located()} appends; the column counts characters where the line before it is UTF-8, else bytes.
	 *
	 * @param string $source
	 * @param int $at
	 * @return string
	 */
	private function placed($source, $at)
	{
		$feed = $at > 0 ? strrpos($source, "\n", $at - 1 - strlen($source)) : false;
		$before = (string) substr($source, $feed === false ? 0 : $feed + 1, $feed === false ? $at : $at - $feed - 1);
		$trailing = preg_match('//u', $before) === 1 ? array_sum(array_slice(count_chars($before, 0), 128, 64)) : 0;

		return ' at line '.($at > 0 ? substr_count($source, "\n", 0, $at) + 1 : 1).', column '.(strlen($before) - $trailing + 1);
	}

	/**
	 * Writes the patterns {@see MagpieRSS::vet()} reads a document by, each anchored where its reading starts: XML's grammar as libxml reads it in one of the charsets of {@see MagpieRSS::$charsets}.
	 *
	 * @param string $charset 'utf8', 'latin' or 'ascii'
	 * @return array<string, string> by what each matches
	 */
	private static function grammar($charset)
	{
		if(isset(self::$grammars[$charset]))
		{
			return self::$grammars[$charset];
		}

		$charsets = array(
			'ascii' => array('', '(?!)', '(?!)', '(?!)', '/^[\t\n\r\x20-\x7F]*+$/D'),
			'latin' => array('\x80-\xFF', '(?!)', '[\xC0-\xD6\xD8-\xF6\xF8-\xFF]', '\xB7', '/^[^\x00-\x08\x0B\x0C\x0E-\x1F]*+$/D'),
			'utf8' => array(
				'',
				'[\xC2-\xDF][\x80-\xBF]|\xE0[\xA0-\xBF][\x80-\xBF]|[\xE1-\xEC\xEE][\x80-\xBF]{2}|\xED[\x80-\x9F][\x80-\xBF]|\xEF(?:[\x80-\xBE][\x80-\xBF]|\xBF[\x80-\xBD])|\xF0[\x90-\xBF][\x80-\xBF]{2}|[\xF1-\xF3][\x80-\xBF]{3}|\xF4[\x80-\x8F][\x80-\xBF]{2}',
				'\xC3[\x80-\x96\x98-\xB6\xB8-\xBF]|[\xC4-\xCB\xCE-\xDF][\x80-\xBF]|\xCD[\xB0-\xBD\xBF]|\xE0[\xA0-\xBF][\x80-\xBF]|[\xE1\xE4-\xEC][\x80-\xBF]{2}|\xE2(?:\x80[\x8C\x8D]|\x81[\xB0-\xBF]|[\x82-\x85\xB0-\xBE][\x80-\xBF]|\x86[\x80-\x8F]|\xBF[\x80-\xAF])|\xE3(?:\x80[\x81-\xBF]|[\x81-\xBF][\x80-\xBF])|\xED[\x80-\x9F][\x80-\xBF]|\xEF(?:[\xA4-\xB6\xB8-\xBE][\x80-\xBF]|\xB7[\x80-\x8F\xB0-\xBF]|\xBF[\x80-\xBD])|\xF0[\x90-\xBF][\x80-\xBF]{2}|[\xF1\xF2][\x80-\xBF]{3}|\xF3[\x80-\xAF][\x80-\xBF]{2}',
				'\xC2\xB7|\xCC[\x80-\xBF]|\xCD[\x80-\xAF]|\xE2\x80\xBF|\xE2\x81\x80',
				'/^[^\x{0}-\x{8}\x{B}\x{C}\x{E}-\x{1F}\x{FFFE}\x{FFFF}]*+$/Du',
			),
		);
		list($high, $wide, $start, $part, $characters) = $charsets[$charset];
		$space = self::$space;
		$wide = '(?=[\x80-\xFF])(?:'.$wide.')';
		$name = '(?:[:A-Z_a-z]|(?=[\x80-\xFF])(?:'.$start.'))[-.0-9:A-Z_a-z]*+(?:(?=[\x80-\xFF])(?:'.$start.'|'.$part.')[-.0-9:A-Z_a-z]*+)*+';
		$number = '\#(?:[0-9]++|x[0-9A-Fa-f]++)';
		$predefined = '&(?:lt|gt|amp|quot|apos|'.$number.');';
		$reference = '&(?:'.$name.'|'.$number.');';
		$literal = '(?:"[^"]*+"|\'[^\']*+\')';
		$external = '(?:SYSTEM'.$space.'++'.$literal.'|PUBLIC'.$space.'++'.$literal.$space.'++'.$literal.')';
		$attribute = $space.'++'.$name.$space.'*+='.$space.'*+';
		$plain = $attribute.'(?:"[^"<>&]*+(?:'.$predefined.'[^"<>&]*+)*+"|\'[^\'<>&]*+(?:'.$predefined.'[^\'<>&]*+)*+\')';
		$valued = $attribute.'(?:"[^"<]*+"|\'[^\'<]*+\')';
		$comment = '!--(?:[\t\n\r\x20-\x2C\x2E-\x7F'.$high.']++|'.$wide.'|-(?!-))*+-->';
		$section = '!\[CDATA\[(?:[\t\n\r\x20-\x5C\x5E-\x7F'.$high.']++|'.$wide.'|\](?!\]>))*+\]\]>';
		$target = '\?(?!xml(?:'.$space.'|\?>))'.$name;
		$instruction = $target.'(?:'.$space.'(?:[\t\n\r\x20-\x3E\x40-\x7F'.$high.']++|'.$wide.'|\?(?!>))*+)?+\?>';

		return self::$grammars[$charset] = array(
			'prolog' => '/\G(?:'.$space.'++|<(?:'.$comment.'|'.$instruction.'))*+/',
			'run' => '/\G(?:[\t\n\r\x20-\x25\x27-\x3B\x3D-\x7F'.$high.']++|<(?:\/'.$name.$space.'*+>|'.$name.'(?=[^>]{0,1275}>)(?:'.$plain.')*+'.$space.'*+\/?+>|'.$comment.'|'.$section.'|'.$instruction.')|'.$reference.'|'.$wide.')*+/',
			'tag' => '/\G<'.$name.'(?:'.$valued.')*+'.$space.'*+\/?+>/',
			'opening' => '/\G<(?:'.$name.'(?:'.$valued.')*+'.$space.'*+)?+/',
			'crowded' => '/\G<(?:(?:[ \t\r\n\/=]++|"[^"]*+"?+|\'[^\']*+\'?+)*+[^ \t\r\n\/=<>"\']++){258}/',
			'end' => '/\G<\/'.$name.$space.'*+>/',
			'reference' => '/\G'.$reference.'/',
			'character' => '/\G(?:[\t\n\r\x20-\x7F'.$high.']|'.$wide.')/',
			'target' => '/\G<'.$target.'(?='.$space.'|\?>)/',
			'characters' => $characters,
			'allowed' => '/\G(?:[\t\n\r\x20-\x7F'.$high.']++|'.$wide.')*+/',
			'targets' => '/<(?:!(?:--.*?-->|\[CDATA\[.*?\]\]>)(*SKIP)(*F)|\?('.$name.').*?\?>)/s',
			'referred' => '/<(?:!--.*?-->|\?.*?\?>|!\[CDATA\[.*?\]\]>)(*SKIP)(*F)|&(?!(?:lt|gt|amp|quot|apos);)(?:('.$name.')|'.$number.');/s',
			'doctype' => '/\G<!DOCTYPE'.$space.'++'.$name.'(?:'.$space.'++'.$external.')?+'.$space.'*+/',
			'entity' => '/\G<!ENTITY'.$space.'++(%'.$space.'++)?+('.$name.')'.$space.'++(?:(["\'])|'.$external.'(?:'.$space.'++NDATA'.$space.'++'.$name.')?+'.$space.'*+>)/',
			'markup' => '/\G<!(?:ELEMENT|ATTLIST|NOTATION)(?='.$space.')(?:[^<>"\']++|'.$literal.')*+>/',
			'parameter' => '/\G%('.$name.');/',
			'closed' => '/\G'.$space.'*+>/',
		);
	}

	/**
	 * Splits an element name, lower-cased, into the name, its prefix (false when none, or 'rdf') and its local part.
	 *
	 * @param string $element
	 * @return array{0: string, 1: string|false, 2: string}
	 */
	private function nameOf($element)
	{
		$name = $this->ascii($element, CASE_LOWER);
		$colon = strpos($name, ':');

		if($colon === false)
		{
			return array($name, false, $name);
		}

		$prefix = (string) substr($name, 0, $colon);

		return array($name, $prefix === '' || $prefix === 'rdf' ? false : $prefix, (string) substr($name, $colon + 1));
	}

	/**
	 * Recognises rdf:RDF, rss and feed as the root; elements before it are ignored.
	 *
	 * @param string $local
	 * @param array<string, string> $attrs
	 * @return void
	 */
	private function openRoot($local, array $attrs)
	{
		if($local === 'feed')
		{
			$version = $this->attribute($attrs, 'version');
			$this->feed_type = ATOM;
			$this->feed_version = $version === null ? '1.0' : $version;
			$this->roles = array('channel' => 'channel', 'item' => 'item', 'entry' => 'item', 'link' => 'link');
			$this->roles = array_merge($this->roles, array_fill_keys($this->_CONTENT_CONSTRUCTS, 'construct'));
			$this->enter('channel', array());

			return;
		}

		if($local === 'rdf' || $local === 'rss')
		{
			$this->feed_type = RSS;
			$this->feed_version = $local === 'rdf' ? '1.0' : $this->attribute($attrs, 'version');
			$this->roles = array('channel' => 'channel', 'item' => 'item', 'entry' => 'item', 'image' => 'box', 'textinput' => 'box');
		}
	}

	/**
	 * Opens a channel, item, image or text input: the text below it is keyed afresh and lands in its array.
	 *
	 * @param string $kind 'channel', 'item', 'image' or 'textinput'
	 * @param array<string, string> $attrs
	 * @return void
	 */
	private function enter($kind, array $attrs)
	{
		$this->kinds[$this->depth - 1] = 'container';
		$this->saved[$this->depth - 1] = array($kind, $this->switchTo($kind, true), $this->stack, $this->path, $this->current_namespace);
		$this->stack = array();
		$this->path = '';
		$this->current_namespace = false;
		$about = $kind === 'item' && $attrs !== array() ? $this->attribute($attrs, 'rdf:about') : null;

		if($about !== null)
		{
			$this->current_item['about'] = $about;
		}
	}

	/**
	 * Closes what {@see MagpieRSS::enter()} opened, filing a finished item.
	 *
	 * @param array $frame
	 * @return void
	 */
	private function leave(array $frame)
	{
		list($kind, $was, $this->stack, $this->path, $this->current_namespace) = $frame;

		if($kind === 'item')
		{
			$this->items[] = $this->current_item;
			$this->current_item = array();
		}

		$this->switchTo($kind, $was);
	}

	/**
	 * Sets the flag that says a channel, item, image or text input is open.
	 *
	 * @param string $kind
	 * @param bool $open
	 * @return bool the flag's previous value
	 */
	private function switchTo($kind, $open)
	{
		switch($kind)
		{
			case 'channel':
				$was = $this->inchannel;
				$this->inchannel = $open;
				break;

			case 'item':
				$was = $this->initem;
				$this->initem = $open;
				break;

			case 'image':
				$was = $this->inimage;
				$this->inimage = $open;
				break;

			default:
				$was = $this->intextinput;
				$this->intextinput = $open;
		}

		return $was;
	}

	/**
	 * Writes the start tag of an element inside a captured construct: '<name attr="v">', with a space before '>' even when bare.
	 *
	 * @param string $name
	 * @param array<string, string> $attrs
	 * @return string
	 */
	private function startTag($name, array $attrs)
	{
		$pairs = array();

		foreach($attrs as $key => $value)
		{
			$pairs[] = map_attrs($this->names[$key][0], $value);
		}

		return '<'.$name.' '.implode(' ', $pairs).'>';
	}

	/**
	 * Appends text to a key of a result array through {@see MagpieRSS::concat()}, or straight when this class itself parses, which leaves no reference in the array.
	 *
	 * @param array $to
	 * @param string $key a key that holds a string, or nothing yet
	 * @param string $text
	 * @return void
	 */
	private function put(array &$to, $key, $text)
	{
		if(!$this->gathering)
		{
			$this->concat($to[$key], $text);
		}
		elseif(isset($to[$key]))
		{
			$to[$key] .= $text;
		}
		else
		{
			$to[$key] = $text;
		}
	}

	/**
	 * Hands the markup captured so far to {@see MagpieRSS::append_content()}; a construct with nothing in it leaves no key.
	 *
	 * @return void
	 */
	private function fileMarkup()
	{
		if($this->markup !== '')
		{
			$this->append_content($this->markup);
			$this->markup = '';
		}
	}

	/**
	 * Hands the text gathered in {@see MagpieRSS::$text} to {@see MagpieRSS::append()}, for the path it came on.
	 *
	 * @return void
	 */
	private function fileText()
	{
		$text = $this->text;
		$this->text = '';

		if($text !== '')
		{
			$this->append($this->path, $text);
		}
	}

	/**
	 * Appends an Atom link's href, like text, under 'link' (rel alternate or none) or 'link_'.rel, unless that key already holds a link or the href is empty.
	 *
	 * @param array<string, string> $attrs
	 * @return void
	 */
	private function openLink(array $attrs)
	{
		$href = (string) $this->attribute($attrs, 'href');
		$rel = (string) $this->attribute($attrs, 'rel');
		$key = $rel === '' || $rel === 'alternate' ? 'link' : 'link_'.$rel;
		$bucket = &$this->bucket($this->current_namespace, false);

		if($href !== '' && !isset($bucket[$key]))
		{
			$this->append($key, $href);
		}
	}

	/**
	 * Finds the array text lands in: the item, else for prefixed text the channel, else the text input, the image or the open channel; then the prefix's own array.
	 *
	 * @param string|false $prefix
	 * @param bool|null $create add the prefix's array when it is missing; null drops it instead when it is empty
	 * @return array|null a reference; null when no container is open, the prefix's key holds text, its array is missing and not created, or $create is null
	 */
	private function &bucket($prefix, $create = true)
	{
		$nowhere = null;

		if($this->initem)
		{
			$target = &$this->current_item;
		}
		elseif($prefix !== false && $this->inchannel)
		{
			$target = &$this->channel;
		}
		elseif($this->intextinput)
		{
			$target = &$this->textinput;
		}
		elseif($this->inimage)
		{
			$target = &$this->image;
		}
		elseif($this->inchannel)
		{
			$target = &$this->channel;
		}
		else
		{
			return $nowhere;
		}

		if($prefix === false)
		{
			return $target;
		}

		if($create === null)
		{
			if(isset($target[$prefix]) && $target[$prefix] === array())
			{
				unset($target[$prefix]);
			}

			return $nowhere;
		}

		if(!isset($target[$prefix]) && $create)
		{
			$target[$prefix] = array();
		}

		if(isset($target[$prefix]) && is_array($target[$prefix]))
		{
			return $target[$prefix];
		}

		return $nowhere;
	}


	/**
	 * Looks an attribute up by its lower-cased name.
	 *
	 * @param array<string, string> $attrs
	 * @param string $name lower case
	 * @return string|null
	 */
	private function attribute(array $attrs, $name)
	{
		if(isset($attrs[$name]))
		{
			return $attrs[$name];
		}

		foreach($attrs as $key => $value)
		{
			if($this->ascii($key, CASE_LOWER) === $name)
			{
				return $value;
			}
		}

		return null;
	}

	/**
	 * Copies RSS fields to the names Atom uses ('tagline', 'summary', 'atom_content') and dates items from dc:date (RSS 1.0) or pubDate.
	 *
	 * @return void
	 */
	private function normaliseRss()
	{
		$this->channel['tagline'] = isset($this->channel['description']) ? $this->channel['description'] : null;

		for(reset($this->items); ($index = key($this->items)) !== null; next($this->items))
		{
			$item = $this->items[$index];

			if(isset($item['description']))
			{
				$item['summary'] = $item['description'];
			}

			if(isset($item['content']['encoded']))
			{
				$item['atom_content'] = $item['content']['encoded'];
			}

			if($this->feed_version === '1.0' && isset($item['dc']['date']))
			{
				$timestamp = parse_w3cdtf($item['dc']['date']);
			}
			else
			{
				$timestamp = isset($item['pubdate']) && is_string($item['pubdate']) ? strtotime($item['pubdate']) : false;
			}

			if($timestamp > 0)
			{
				$item['date_timestamp'] = $timestamp;
			}

			$this->items[$index] = $item;
		}
	}

	/**
	 * Copies Atom fields to the names RSS uses ('description', content:encoded) and dates entries; Atom 1.0 names map onto Atom 0.3's.
	 *
	 * @return void
	 */
	private function normaliseAtom()
	{
		$atom10 = $this->feed_version === '1.0';
		$dates = $atom10 ? array('issued', 'published', 'modified', 'updated') : array('issued', 'modified');

		if($atom10 && !isset($this->channel['tagline']) && isset($this->channel['subtitle']) && is_string($this->channel['subtitle']) && trim($this->channel['subtitle'], " \t\r\n") !== '')
		{
			$this->channel['tagline'] = $this->channel['subtitle'];
		}

		$this->channel['description'] = isset($this->channel['tagline']) ? $this->channel['tagline'] : null;

		for(reset($this->items); ($index = key($this->items)) !== null; next($this->items))
		{
			$item = $this->items[$index];

			if(isset($item['summary']))
			{
				$item['description'] = $item['summary'];
			}

			if(isset($item['atom_content']) && (!isset($item['content']) || is_array($item['content'])))
			{
				$item['content']['encoded'] = $item['atom_content'];
			}

			foreach($dates as $key)
			{
				if(isset($item[$key]))
				{
					$timestamp = parse_w3cdtf($item[$key]);

					if($timestamp > 0)
					{
						$item['date_timestamp'] = $timestamp;
					}

					break;
				}
			}

			$this->items[$index] = $item;
		}
	}

	/**
	 * Picks the input encoding: the byte-order mark's, else the declared one, else the one given, else UTF-8; detecting records a declared one.
	 *
	 * @param string|null $given used only when the document names no encoding
	 * @param bool $detect
	 * @param string|null $marked the byte-order mark's encoding
	 * @param string|null $declared the XML declaration's encoding, upper-cased
	 * @return string upper case
	 */
	private function inputEncoding($given, $detect, $marked, $declared)
	{
		if($detect && $declared !== null)
		{
			$this->_source_encoding = $declared;
		}

		if($marked !== null)
		{
			return $marked;
		}

		if($declared !== null)
		{
			return $declared;
		}

		$given = $this->ascii($given, CASE_UPPER);

		return $given === '' ? 'UTF-8' : $given;
	}

	/**
	 * Reads the encoding the document's first bytes announce: a byte-order mark; '<' or '<?' in UTF-32 or UTF-16, which ext/xml reads without a mark; or '<?xm' in EBCDIC, whose declaration names the code page.
	 *
	 * @param string $source
	 * @return array{0: string|null, 1: int} the encoding ('EBCDIC' for any EBCDIC code page, null when they announce none) and the mark's length in bytes
	 */
	private function byteOrderMark($source)
	{
		$marks = array("\xEF\xBB\xBF" => 'UTF-8', "\x00\x00\xFE\xFF" => 'UTF-32BE', "\xFF\xFE\x00\x00" => 'UTF-32LE', "\xFE\xFF" => 'UTF-16BE', "\xFF\xFE" => 'UTF-16LE');
		$unmarked = array("\x00\x00\x00<" => 'UTF-32BE', "<\x00\x00\x00" => 'UTF-32LE', "\x00<\x00?" => 'UTF-16BE', "<\x00?\x00" => 'UTF-16LE', "\x4C\x6F\xA7\x94" => 'EBCDIC');
		$head = (string) substr($source, 0, 4);

		foreach($marks as $mark => $encoding)
		{
			if(strncmp($head, $mark, strlen($mark)) === 0)
			{
				return array($encoding, strlen($mark));
			}
		}

		return array(isset($unmarked[$head]) ? $unmarked[$head] : null, 0);
	}

	/**
	 * Reads the XML declaration at $offset to its end, which must come within 512 bytes.
	 *
	 * @param string $source
	 * @param int $offset
	 * @return array{0: int, 1: int, 2: string|null, 3: int}|false|null where the encoding label starts (or would go), its length, the label upper-cased, and the declaration's length; false for a declaration that does not end within 512 bytes; null without a declaration libxml reads as one
	 */
	private function declaration($source, $offset)
	{
		$space = self::$space;
		$head = (string) substr($source, $offset, 512);

		if(strncmp($head, '<?xml', 5) !== 0 || strspn($head, " \t\r\n", 5, 1) !== 1)
		{
			return null;
		}

		if(strpos($head, '?>') === false)
		{
			return false;
		}

		if(!preg_match('/\A(<\?xml'.$space.'++version'.$space.'*+='.$space.'*+(?:"1\.[0-9]*+"|\'1\.[0-9]*+\'))(?:'.$space.'++encoding'.$space.'*+='.$space.'*+(["\'])([A-Za-z][-.0-9A-Z_a-z]*+)\2)?+(?:'.$space.'++standalone'.$space.'*+='.$space.'*+(?:"(?:yes|no)"|\'(?:yes|no)\'))?+'.$space.'*+\?>/', $head, $match, PREG_OFFSET_CAPTURE))
		{
			return null;
		}

		if(isset($match[3]))
		{
			return array($match[3][1], strlen($match[3][0]), $this->ascii($match[3][0], CASE_UPPER), strlen($match[0][0]));
		}

		return array(strlen($match[1][0]), 0, null, strlen($match[0][0]));
	}

	/**
	 * Makes the document's XML declaration name $encoding, adding the pseudo-attribute or the declaration where it is missing; a declaration {@see MagpieRSS::declaration()} cannot read to its end stays as it is.
	 *
	 * @param string $source a document without a byte-order mark
	 * @param string $encoding
	 * @return string
	 */
	private function declaring($source, $encoding)
	{
		$declaration = $this->declaration($source, 0);

		if($declaration === false)
		{
			return $source;
		}

		if($declaration === null)
		{
			return $encoding === 'UTF-8' ? $source : '<?xml version="1.0" encoding="'.$encoding.'"?>'.$source;
		}

		if($declaration[2] === null)
		{
			return $encoding === 'UTF-8' ? $source : substr_replace($source, ' encoding="'.$encoding.'"', $declaration[0], 0);
		}

		return $declaration[2] === $encoding ? $source : substr_replace($source, $encoding, $declaration[0], $declaration[1]);
	}

	/**
	 * Converts text to UTF-8 with iconv, else mbstring, taking any complaint from either (an unknown encoding, a bad byte) as failure and raising none.
	 *
	 * @param string $text
	 * @param string $from
	 * @return string|false false when neither can
	 */
	private function converted($text, $from)
	{
		$complaints = 0;
		set_error_handler(function () use (&$complaints)
		{
			$complaints++;

			return true;
		});

		try
		{
			$result = function_exists('iconv') ? iconv($from, 'UTF-8', $text) : false;

			if(!is_string($result) && function_exists('mb_convert_encoding'))
			{
				$complaints = 0;
				$result = mb_convert_encoding($text, 'UTF-8', $from);
			}
		}
		catch(Throwable $e)
		{
			$result = false;
		}
		catch(Exception $e)
		{
			$result = false;
		}

		restore_error_handler();

		return is_string($result) && $complaints === 0 ? $result : false;
	}

	/**
	 * Drops a leading UTF-8 byte-order mark.
	 *
	 * @param string $text
	 * @return string
	 */
	private function withoutMark($text)
	{
		list($marked, $length) = $this->byteOrderMark($text);

		return $marked === 'UTF-8' ? (string) substr($text, $length) : $text;
	}

	/**
	 * Tells whether the byte-order mark and the XML declaration, as far as the document has them, already make ext/xml read it as $encoding.
	 *
	 * @param string|null $marked the byte-order mark's encoding
	 * @param string|null $declared the declaration's encoding, upper-cased
	 * @param string $encoding upper case
	 * @return bool
	 */
	private function announces($marked, $declared, $encoding)
	{
		if($marked !== null && $marked !== $encoding)
		{
			return false;
		}

		if($declared !== null)
		{
			return $declared === $encoding;
		}

		return $marked !== null || $encoding === 'UTF-8';
	}

	/**
	 * Upper-cases an encoding name when it is one ext/xml accepts as a parser or output encoding: UTF-8, ISO-8859-1 or US-ASCII.
	 *
	 * @param mixed $enc
	 * @return string|false
	 */
	private function xmlEncoding($enc)
	{
		$enc = $this->ascii($enc, CASE_UPPER);

		return $enc === 'UTF-8' || $enc === 'ISO-8859-1' || $enc === 'US-ASCII' ? $enc : false;
	}

	/**
	 * Changes the case of ASCII letters only, whatever the locale; anything but a scalar gives ''.
	 *
	 * @param mixed $text
	 * @param int $case CASE_LOWER or CASE_UPPER
	 * @return string
	 */
	private function ascii($text, $case)
	{
		$lower = 'abcdefghijklmnopqrstuvwxyz';
		$upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

		return strtr(is_scalar($text) ? (string) $text : '', $case === CASE_UPPER ? $lower : $upper, $case === CASE_UPPER ? $upper : $lower);
	}
}
