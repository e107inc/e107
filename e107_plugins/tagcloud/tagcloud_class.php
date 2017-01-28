<?php

// namespace lotsofcode\TagCloud;

class TagCloud
{
	/**
	 * Tag cloud version
	 */
	public $version = '4.0.0';

	/*
	 * Tag array container
	 */
	protected $_tagsArray = array();

	/**
	 * List of tags to remove from final output
	 */
	protected $_removeTags = array();

	/**
	 * Cached attributes for order comparison
	 */
	protected $_attributes = array();

	/*
	 * Amount to limit cloud by
	 */
	protected $_limit = null;

	/*
	 * Minimum length of string to filtered in string
	 */
	protected $_minLength = null;

	/*
	 * Custom format output of tags
	 *
	 * transformation: upper and lower for change of case
	 * trim: bool, applies trimming to tag
	 */
	protected $_formatting = array(
		'transformation' => 'lower',
		'trim' => true
	);

	/**
	 * Custom function to create the tag-output
	 */
	protected $_htmlizeTagFunction = null;

  /**
   * @var array Conversion map
   */
  protected $_transliterationTable = array(
    'á' => 'a', 'Á' => 'A', 'à' => 'a', 'À' => 'A', 'ă' => 'a','Ă' => 'A', 'â' => 'a', 'Â' => 'A',
    'å' => 'a', 'Å' => 'A', 'ã' => 'a', 'Ã' => 'A', 'ą' => 'a', 'Ą' => 'A', 'ā' => 'a', 'Ā' => 'A',
    'ä' => 'ae', 'Ä' => 'AE', 'æ' => 'ae', 'Æ' => 'AE', 'ḃ' => 'b', 'Ḃ' => 'B', 'ć' => 'c', 'Ć' => 'C',
    'ĉ' => 'c', 'Ĉ' => 'C', 'č' => 'c', 'Č' => 'C', 'ċ' => 'c', 'Ċ' => 'C', 'ç' => 'c', 'Ç' => 'C',
    'ď' => 'd', 'Ď' => 'D', 'ḋ' => 'd', 'Ḋ' => 'D', 'đ' => 'd', 'Đ' => 'D', 'ð' => 'dh', 'Ð' => 'Dh',
    'é' => 'e', 'É' => 'E', 'è' => 'e', 'È' => 'E', 'ĕ' => 'e', 'Ĕ' => 'E', 'ê' => 'e', 'Ê' => 'E',
    'ě' => 'e', 'Ě' => 'E', 'ë' => 'e', 'Ë' => 'E', 'ė' => 'e', 'Ė' => 'E', 'ę' => 'e', 'Ę' => 'E',
    'ē' => 'e', 'Ē' => 'E', 'ḟ' => 'f', 'Ḟ' => 'F', 'ƒ' => 'f', 'Ƒ' => 'F', 'ğ' => 'g', 'Ğ' => 'G',
    'ĝ' => 'g', 'Ĝ' => 'G', 'ġ' => 'g', 'Ġ' => 'G', 'ģ' => 'g', 'Ģ' => 'G', 'ĥ' => 'h', 'Ĥ' => 'H',
    'ħ' => 'h', 'Ħ' => 'H', 'í' => 'i', 'Í' => 'I', 'ì' => 'i', 'Ì' => 'I', 'î' => 'i', 'Î' => 'I',
    'ï' => 'i', 'Ï' => 'I', 'ĩ' => 'i', 'Ĩ' => 'I', 'į' => 'i', 'Į' => 'I', 'ī' => 'i', 'Ī' => 'I',
    'ĵ' => 'j', 'Ĵ' => 'J', 'ķ' => 'k', 'Ķ' => 'K', 'ĺ' => 'l', 'Ĺ' => 'L', 'ľ' => 'l', 'Ľ' => 'L',
    'ļ' => 'l', 'Ļ' => 'L', 'ł' => 'l', 'Ł' => 'L', 'ṁ' => 'm', 'Ṁ' => 'M', 'ń' => 'n', 'Ń' => 'N',
    'ň' => 'n', 'Ň' => 'N', 'ñ' => 'n', 'Ñ' => 'N', 'ņ' => 'n', 'Ņ' => 'N', 'ó' => 'o', 'Ó' => 'O',
    'ò' => 'o', 'Ò' => 'O', 'ô' => 'o', 'Ô' => 'O', 'ő' => 'o', 'Ő' => 'O', 'õ' => 'o', 'Õ' => 'O',
    'ø' => 'oe', 'Ø' => 'OE', 'ō' => 'o', 'Ō' => 'O', 'ơ' => 'o', 'Ơ' => 'O', 'ö' => 'oe', 'Ö' => 'OE',
    'ṗ' => 'p', 'Ṗ' => 'P', 'ŕ' => 'r', 'Ŕ' => 'R', 'ř' => 'r', 'Ř' => 'R', 'ŗ' => 'r', 'Ŗ' => 'R',
    'ś' => 's', 'Ś' => 'S', 'ŝ' => 's', 'Ŝ' => 'S', 'š' => 's', 'Š' => 'S', 'ṡ' => 's', 'Ṡ' => 'S',
    'ş' => 's', 'Ş' => 'S', 'ș' => 's', 'Ș' => 'S', 'ß' => 'SS', 'ť' => 't', 'Ť' => 'T', 'ṫ' => 't',
    'Ṫ' => 'T', 'ţ' => 't', 'Ţ' => 'T', 'ț' => 't', 'Ț' => 'T', 'ŧ' => 't', 'Ŧ' => 'T', 'ú' => 'u',
    'Ú' => 'U', 'ù' => 'u', 'Ù' => 'U', 'ŭ' => 'u', 'Ŭ' => 'U', 'û' => 'u', 'Û' => 'U', 'ů' => 'u',
    'Ů' => 'U', 'ű' => 'u', 'Ű' => 'U', 'ũ' => 'u', 'Ũ' => 'U', 'ų' => 'u', 'Ų' => 'U', 'ū' => 'u',
    'Ū' => 'U', 'ư' => 'u', 'Ư' => 'U', 'ü' => 'ue', 'Ü' => 'UE', 'ẃ' => 'w', 'Ẃ' => 'W', 'ẁ' => 'w',
    'Ẁ' => 'W', 'ŵ' => 'w', 'Ŵ' => 'W', 'ẅ' => 'w', 'Ẅ' => 'W', 'ý' => 'y', 'Ý' => 'Y', 'ỳ' => 'y',
    'Ỳ' => 'Y', 'ŷ' => 'y', 'Ŷ' => 'Y', 'ÿ' => 'y', 'Ÿ' => 'Y', 'ź' => 'z', 'Ź' => 'Z', 'ž' => 'z',
    'Ž' => 'Z', 'ż' => 'z', 'Ż' => 'Z', 'þ' => 'th', 'Þ' => 'Th', 'µ' => 'u', 'а' => 'a', 'А' => 'a',
    'б' => 'b', 'Б' => 'b', 'в' => 'v', 'В' => 'v', 'г' => 'g', 'Г' => 'g', 'д' => 'd', 'Д' => 'd',
    'е' => 'e', 'Е' => 'e', 'ё' => 'e', 'Ё' => 'e', 'ж' => 'zh', 'Ж' => 'zh', 'з' => 'z', 'З' => 'z',
    'и' => 'i', 'И' => 'i', 'й' => 'j', 'Й' => 'j', 'к' => 'k', 'К' => 'k', 'л' => 'l', 'Л' => 'l',
    'м' => 'm', 'М' => 'm', 'н' => 'n', 'Н' => 'n', 'о' => 'o', 'О' => 'o', 'п' => 'p', 'П' => 'p',
    'р' => 'r', 'Р' => 'r', 'с' => 's', 'С' => 's', 'т' => 't', 'Т' => 't', 'у' => 'u', 'У' => 'u',
    'ф' => 'f', 'Ф' => 'f', 'х' => 'h', 'Х' => 'h', 'ц' => 'c', 'Ц' => 'c', 'ч' => 'ch', 'Ч' => 'ch',
    'ш' => 'sh', 'Ш' => 'sh', 'щ' => 'sch', 'Щ' => 'sch', 'ъ' => '', 'Ъ' => '', 'ы' => 'y', 'Ы' => 'y',
    'ь' => '', 'Ь' => '', 'э' => 'e', 'Э' => 'e', 'ю' => 'ju', 'Ю' => 'ju', 'я' => 'ja', 'Я' => 'ja'
  );

	/*
	 * Constructor
	 *
	 * @param array $tags
	 *
	 * @return void
	 */
	public function __construct($tags = false)
	{
		if ($tags !== false) {
			if (is_string($tags)) {
				$this->addString($tags);
			} elseif (count($tags)) {
				foreach ($tags as $key => $value) {
					$this->addTag($value);
				}
			}
		}
	}

	/*
	 * Convert a string into a array
	 *
	 * @param string $string    The string to use
	 * @param string $seperator The seperator to extract the tags
	 *
	 * @return void
	 */
	public function addString($string, $seperator = ' ')
	{
		$inputArray = explode($seperator, $string);
		$tagArray = array();
		foreach ($inputArray as $inputTag) {
			$tagArray[]=$this->formatTag($inputTag);
		}
		$this->addTags($tagArray);
	}

	/*
	 * Parse tag into safe format
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public function formatTag($string)
	{
    $string = $this->_convertCharacters($string);
		if ($this->_formatting['transformation']) {
			switch ($this->_formatting['transformation']) {
				case 'upper':
					$string = strtoupper($string);
					break;
				default:
					$string = strtolower($string);
			}
		}
		if ($this->_formatting['trim']) {
			$string = trim($string);
		}
		return preg_replace('/[^\w ]/u', '', strip_tags($string));
	}

	/*
	 * Assign tag to array
	 *
	 * @param array $tagAttributes Tags or tag attributes array
	 *
	 * @return array $this->tagsArray
	 */
	public function addTag($tagAttributes = array())
	{
		if (is_string($tagAttributes)) {
			$tagAttributes = array('tag' => $tagAttributes);
		}
		$tagAttributes['tag'] = $this->formatTag($tagAttributes['tag']);
		if (!array_key_exists('size', $tagAttributes)) {
			$tagAttributes = array_merge($tagAttributes, array('size' => 1));
		}
		if (!array_key_exists('tag', $tagAttributes)) {
			return false;
		}
		$tag = $tagAttributes['tag'];
		if (empty($this->_tagsArray[$tag])) {
			$this->_tagsArray[$tag] = array();
		}
		if (!empty($this->_tagsArray[$tag]['size']) && !empty($tagAttributes['size'])) {
			$tagAttributes['size'] = ($this->_tagsArray[$tag]['size'] + $tagAttributes['size']);
		} elseif (!empty($this->_tagsArray[$tag]['size'])) {
			$tagAttributes['size'] = $this->_tagsArray[$tag]['size'];
		}
		$this->_tagsArray[$tag] = $tagAttributes;
		$this->addAttributes($tagAttributes);
		return $this->_tagsArray[$tag];
	}

	/*
	 * Add all attributes to cached array
	 *
	 * @return void
	 */
	public function addAttributes($attributes)
	{
		$this->_attributes = array_unique(
			array_merge(
				$this->_attributes,
				array_keys($attributes)
			)
		);
	}

	/*
	 * Get attributes from cache
	 *
	 * @return array $this->_attibutes
	 */
	public function getAttributes()
	{
		return $this->_attributes;
	}

	/*
	 * Assign multiple tags to array
	 *
	 * @param array $tags
	 *
	 * @return void
	 */
	public function addTags($tags = array())
	{
		if (!is_array($tags)) {
			$tags = func_get_args();
		}
		foreach ($tags as $tagAttributes) {
			$this->addTag($tagAttributes);
		}
	}

	/*
	 * Sets a minimum string length for the
	 * tags to display
	 *
	 * @param int $minLength
	 *
	 * @returns obj $this
	 */
	public function setMinLength($minLength)
	{
		$this->_minLength = $minLength;
		return $this;
	}


	/*
	 * Gets the minimum length value
	 *
	 * @returns void
	 */
	public function getMinLength()
	{
		return $this->_minLength;
	}


	/*
	 * Sets a limit for the amount of clouds
	 *
	 * @param int $limit
	 *
	 * @returns obj $this
	 */
	public function setLimit($limit)
	{
		$this->_limit = $limit;
		return $this;
	}

	/*
	 * Get the limit for the amount tags
	 * to display
	 *
	 * @param int $limit
	 *
	 * @returns int $this->_limit
	 */
	public function getLimit()
	{
		return $this->_limit;
	}

	/*
	 * Remove a tag from the array
	 *
	 * @param string $tag
	 *
	 * @returns obj $this
	 */
	public function setRemoveTag($tag)
	{
		$this->_removeTags[] = $this->formatTag($tag);
		return $this;
	}

	/*
	 * Remove multiple tags from the array
	 *
	 * @param array $tags
	 *
	 * @returns obj $this
	 */
	public function setRemoveTags($tags)
	{
		foreach ($tags as $tag) {
			$this->setRemoveTag($tag);
		}
		return $this;
	}

	/*
	 * Get the list of remove tags
	 *
	 * @returns array $this->_removeTags
	 */
	public function getRemoveTags()
	{
		return $this->_removeTags;
	}

	/*
	 * Assign the order field and order direction of the array
	 *
	 * Order by tag or size / defaults to random
	 *
	 * @param array  $field
	 * @param string $direction
	 *
	 * @returns $this->orderBy
	 */
	public function setOrder($field, $direction = 'ASC')
	{
		return $this->orderBy = array(
			'field' => $field,
			'direction' => $direction
		);
	}

	/*
	 * Inject a custom function for generatinng the rendered HTML
	 *
	 * @param function $htmlizer
	 *
	 * @return $this->_htmlizeTagFunction
	 */
	public function setHtmlizeTagFunction($htmlizer)
	{
		return $this->_htmlizeTagFunction = $htmlizer;
	}

	/*
	 * Generate the output for each tag.
	 *
	 * @returns string/array $return
	 */
	public function render($returnType = 'html')
	{
		$this->_remove();
		$this->_minLength();
		if (empty($this->orderBy)) {
			$this->_shuffle();
		} else {
			$orderDirection = strtolower($this->orderBy['direction']) == 'desc' ? 'SORT_DESC' : 'SORT_ASC';
			$this->_tagsArray = $this->_order(
				$this->_tagsArray,
				$this->orderBy['field'],
				$orderDirection
			);
		}

    $this->_limit();
		$max = $this->_getMax();
		if (count($this->_tagsArray)) {
			$return = ($returnType == 'html' ? '' : ($returnType == 'array' ? array() : ''));
			foreach ($this->_tagsArray as $tag => $arrayInfo) {
				$sizeRange = $this->_getClassFromPercent(($arrayInfo['size'] / $max) * 100);
				$arrayInfo['range'] = $sizeRange;
				if ($returnType == 'array') {
					$return [$tag] = $arrayInfo;
				} elseif ($returnType == 'html') {
					$return .= $this->htmlizeTag( $arrayInfo, $sizeRange );
				}
			}
			return $return;
		}
		return null;
	}

	/**
	 * Convert a tag into an html-snippet
	 *
	 * This function is mainly an anchor to decide if a user-supplied
	 * custom function should be used or the normal output method.
	 *
	 * This will most likely only work in PHP >= 5.3
	 *
	 * @param array  $arrayInfo
	 * @param string $sizeRange
	 *
	 * @return string
	 */
	public function htmlizeTag($arrayInfo, $sizeRange)
	{
    $htmlizeTagFunction = $this->_htmlizeTagFunction;
		if (isset($htmlizeTagFunction) &&
        is_callable($htmlizeTagFunction)
    ) {
			// this cannot be written in one line or the PHP interpreter will puke
			// apparently, it's okay to have a function in a variable,
			// but it's not okay to have it in an instance-variable.
			return $htmlizeTagFunction($arrayInfo, $sizeRange);
		} else {
			return "<span class='tag size{$sizeRange}'> &nbsp; {$arrayInfo['tag']} &nbsp; </span>";
		}
	}

	/*
	 * Removes tags from the whole array
	 *
	 * @returns array $this->_tagsArray
	 */
	protected function _remove()
	{
    $_tagsArray = array();
		foreach ($this->_tagsArray as $key => $value) {
			if (!in_array($value['tag'], $this->getRemoveTags())) {
				$_tagsArray[$value['tag']] = $value;
			}
		}
		$this->_tagsArray = array();
		$this->_tagsArray = $_tagsArray;
		return $this->_tagsArray;
	}

	/*
	 * Orders the cloud by a specific field
	 *
	 * @param array $unsortedArray
	 * @param string $sortField
	 * @param string $sortWay
	 *
	 * @returns array $unsortedArray
	 */
	protected function _order($unsortedArray, $sortField, $sortWay = 'SORT_ASC')
	{
		$sortedArray = array();
		foreach ($unsortedArray as $uniqid => $row) {
			foreach ($this->getAttributes() as $attr) {
				if (isset($row[$attr])) {
					$sortedArray[$attr][$uniqid] = $unsortedArray[$uniqid][$attr];
				} else {
					$sortedArray[$attr][$uniqid] = null;
				}
			}
		}
		if ($sortWay) {
			array_multisort($sortedArray[$sortField], constant($sortWay), $unsortedArray);
		}
		return $unsortedArray;
	}

	/*
	 * Parses the array and retuns
	 * limited amount of items
	 *
	 * @returns array $this->_tagsArray
	 */
	protected function _limit()
	{
		$limit = $this->getLimit();
		if ($limit !== null) {
			$i = 0;
			$_tagsArray = array();
			foreach ($this->_tagsArray as $key => $value) {
				if ($i < $limit) {
					$_tagsArray[$value['tag']] = $value;
				}
				$i++;
			}
			$this->_tagsArray = array();
			$this->_tagsArray = $_tagsArray;
		}
		return $this->_tagsArray;
	}

	/*
	 * Reduces the array by removing strings
	 * with a length shorter than the minLength
	 *
	 * @returns array $this->_tagsArray
	 */
	protected function _minLength()
	{
		$limit = $this->getMinLength();
		if ($limit !== null) {
			$i = 0;
			$_tagsArray = array();
			foreach ($this->_tagsArray as $key => $value) {
				if (strlen($value['tag']) >= $limit) {
					$_tagsArray[$value['tag']] = $value;
				}
				$i++;
			}
			$this->_tagsArray = array();
			$this->_tagsArray = $_tagsArray;
		}
		return $this->_tagsArray;
	}

	/*
	 * Finds the maximum 'size' value of an array
	 *
	 * @returns string $max
	 */
	protected function _getMax()
	{
		$max = 0;
		if (!empty($this->_tagsArray)) {
			$p_size = 0;
			foreach ($this->_tagsArray as $cKey => $cVal) {
				$c_size = $cVal['size'];
				if ($c_size > $p_size) {
					$max = $c_size;
					$p_size = $c_size;
				}
			}
		}
		return $max;
	}

	/*
	 * Shuffle associated names in array
	 *
	 * @return array $this->_tagsArray The shuffled array
	 */
	protected function _shuffle()
	{
		$keys = array_keys($this->_tagsArray);
		shuffle($keys);
		if (count($keys) && is_array($keys)) {
			$tmpArray = $this->_tagsArray;
			$this->_tagsArray = array();
			foreach ($keys as $key => $value)
				$this->_tagsArray[$value] = $tmpArray[$value];
		}
		return $this->_tagsArray;
	}

	/*
	 * Get the class range using a percentage
	 *
	 * @returns int $class The respective class
	 * name based on the percentage value
	 */
	protected function _getClassFromPercent($percent)
	{
		$class = floor(($percent / 10));

		if ($percent >= 5) {
			$class++;
		}

		if ($percent >= 80 && $percent < 100) {
			$class = 8;
		} elseif ($percent == 100) {
			$class = 9;
		}

		return $class;
	}

  /**
   * Calculate the class given to a tag from the
   * weight percentage of the given tag.
   */
  public function calculateClassFromPercent($percent)
  {
    return $this->_getClassFromPercent($percent);
  }

  /**
   * Convert accented chars into basic latin chars
   *
   * Taken from http://stackoverflow.com/questions/6837148/change-foreign-characters-to-normal-equivalent
   */
  function _convertCharacters($string) {
    return str_replace(array_keys($this->_transliterationTable), array_values($this->_transliterationTable), $string);
  }
}
