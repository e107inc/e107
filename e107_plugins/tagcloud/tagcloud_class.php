<?php

// namespace lotsofcode\TagCloud;

class TagCloud
{
  /**
   * Tag cloud version
   *
   * @var string
   */
  public $version = '4.0.1';

  /**
   * Tag array container
   *
   * @var array
   */
  protected $tagsArray = array();

  /**
   * List of tags to remove from final output
   *
   * @var array
   */
  protected $removeTags = array();

  /**
   * Cached attributes for order comparison
   *
   * @var array
   */
  protected $attributes = array();

  /**
   * Amount to limit cloud by
   *
   * @var null
   */
  protected $limit = null;

  /**
   * Minimum length of string to filtered in string
   *
   * @var null
   */
  protected $minLength = null;

  /**
   * Custom format output of tags
   *
   * transformation: upper and lower for change of case
   * transliterate: true\false
   * trim: bool, applies trimming to tag
   *
   * @var array
   */
  protected $options = array(
    'transformation' => 'lower',
    'transliterate' => true,
    'trim' => true
  );

  /**
   * Custom function to create the tag-output
   *
   * @var null
   */
  protected $htmlizeTagFunction = null;

  /**
   * @var array Conversion map
   */
  protected $transliterationTable = array(
    'á' => 'a', 'Á' => 'A', 'à' => 'a', 'À' => 'A', 'ă' => 'a', 'Ă' => 'A', 'â' => 'a', 'Â' => 'A',
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

  /**
   * Takes the tags and calls the correct
   * setter based on the type of input
   *
   * @constructor
   *
   * @param mixed $tags String or Collection of tags
   */
  public function __construct($tags = false)
  {
    if ($tags !== false) {
      if (is_string($tags)) {
        $this->addString($tags);
      } else if (count($tags)) {
        foreach ($tags as $tag) {
          $this->addTag($tag);
        }
      }
    }
  }

  /**
   * Convert a string into a array
   *
   * @param string $string    The string to use
   * @param string $separator The separator to extract the tags
   *
   * @return $this
   */
  public function addString($string, $separator = ' ')
  {
    $inputArray = explode($separator, $string);
    $tagArray = array();
    foreach ($inputArray as $inputTag) {
      $tagArray[] = $this->formatTag($inputTag);
    }
    $this->addTags($tagArray);

    return $this;
  }

  /**
   * Set option value
   *
   * @param string $option Option property name
   * @param string $value  New property value
   *
   * @return $this
   */
  public function setOption($option, $value)
  {
    $this->options[$option] = $value;
    return $this;
  }

  /**
   * Get option by name otherwise return all options
   *
   * @param string $option Option property name
   *
   * @return array
   */
  public function getOption($option = null)
  {
    if ($option !== null) {
      return $this->options[$option];
    }
    return $this->options;
  }

  /**
   * Parse tag into safe format
   *
   * @param string $string Tag to be formatted
   *
   * @return mixed
   */
  public function formatTag($string)
  {
    if ($this->options['transliterate']) {
      $string = $this->transliterate($string);
    }

    if ($this->options['transformation']) {
      switch ($this->options['transformation']) {
        case 'upper':
          $string = $this->options['transliterate'] ? strtoupper($string) : mb_convert_case($string, MB_CASE_UPPER, "UTF-8");
          break;
        default:
          $string = $this->options['transliterate'] ? strtolower($string) : mb_convert_case($string, MB_CASE_LOWER, "UTF-8");
      }
    }
    if ($this->options['trim']) {
      $string = trim($string);
    }
    return preg_replace('/[^\w ]/u', '', strip_tags($string));
  }

  /**
   * Assign tag to array
   *
   * @param array $tagAttributes Tags or tag attributes array
   *
   * @return bool
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
    if (empty($this->tagsArray[$tag])) {
      $this->tagsArray[$tag] = array();
    }
    if (!empty($this->tagsArray[$tag]['size']) && !empty($tagAttributes['size'])) {
      $tagAttributes['size'] = ($this->tagsArray[$tag]['size'] + $tagAttributes['size']);
    } elseif (!empty($this->tagsArray[$tag]['size'])) {
      $tagAttributes['size'] = $this->tagsArray[$tag]['size'];
    }
    $this->tagsArray[$tag] = $tagAttributes;
    $this->addAttributes($tagAttributes);
    return $this->tagsArray[$tag];
  }

  /**
   * Add all attributes to cached array
   *
   * @param $attributes
   *
   * @return $this
   */
  public function addAttributes($attributes)
  {
    $this->attributes = array_unique(
      array_merge(
        $this->attributes,
        array_keys($attributes)
      )
    );
    return $this;
  }

  /**
   * Get attributes from cache
   *
   * @return array Collection of Attributes
   */
  public function getAttributes()
  {
    return $this->attributes;
  }

  /**
   * Assign multiple tags to array
   *
   * @param array $tags A collection of multiple tabs
   *
   * @return $this
   */
  public function addTags($tags = array())
  {
    if (!is_array($tags)) {
      $tags = func_get_args();
    }
    foreach ($tags as $tagAttributes) {
      $this->addTag($tagAttributes);
    }
    return $this;
  }

  /**
   * Sets a minimum string length for the tags to display
   *
   * @param int $minLength The minimum string length of a tag
   *
   * @return $this
   */
  public function setMinLength($minLength)
  {
    $this->minLength = $minLength;
    return $this;
  }

  /**
   * Gets the minimum length value
   *
   * @return int
   */
  public function getMinLength()
  {
    return $this->minLength;
  }


  /**
   * Sets a limit for the amount of clouds
   *
   * @param int $limit The maximum number to display
   *
   * @return $this
   */
  public function setLimit($limit)
  {
    $this->limit = $limit;
    return $this;
  }

  /**
   * Get the limit for the amount tags to display
   *
   * @return int The maximum number
   */
  public function getLimit()
  {
    return $this->limit;
  }

  /**
   * Assign a tag to be removed from the array
   *
   * @param string $tag The tag value
   *
   * @return $this
   */
  public function setRemoveTag($tag)
  {
    $this->removeTags[] = $this->formatTag($tag);
    return $this;
  }

  /**
   * Remove multiple tags from the array
   *
   * @param $tags A collection of removable tags
   *
   * @return $this
   */
  public function setRemoveTags($tags)
  {
    foreach ($tags as $tag) {
      $this->setRemoveTag($tag);
    }
    return $this;
  }

  /**
   * Get the list of remove tags
   *
   * @return array A collection of tags to remove
   */
  public function getRemoveTags()
  {
    return $this->removeTags;
  }

  /**
   * Assign the order field and order direction of the array
   *
   * Order by tag or size / defaults to random
   *
   * @param string $field     The name of the field to sort by
   * @param string $direction The sort direction ASC|DESC
   *
   * @return $this
   */
  public function setOrder($field, $direction = 'ASC')
  {
    $this->orderBy = array(
      'field' => $field,
      'direction' => $direction
    );
    return $this;
  }

  /**
   * Inject a custom function/closure for generating the rendered HTML
   *
   * @param callable $htmlizeTagFunction The function/closure
   *
   * @return mixed
   */
  public function setHtmlizeTagFunction($htmlizeTagFunction)
  {
    $this->htmlizeTagFunction = $htmlizeTagFunction;
    return $this;
  }

  /**
   * Generate the output for each tag.
   *
   * @param string $returnType The type of data to return [html|array]
   *
   * @return array|null|string
   */
  public function render($returnType = 'html')
  {
    $this->remove();
    $this->minLength();
    if (empty($this->orderBy)) {
      $this->shuffle();
    } else {
      $orderDirection = strtolower($this->orderBy['direction']) == 'desc' ? 'SORT_DESC' : 'SORT_ASC';
      $this->tagsArray = $this->order(
        $this->tagsArray,
        $this->orderBy['field'],
        $orderDirection
      );
    }

    $this->limit();
    $max = $this->getMax();
    if (count($this->tagsArray)) {
      $return = ($returnType == 'html' ? '' : ($returnType == 'array' ? array() : ''));
      foreach ($this->tagsArray as $tag => $arrayInfo) {
        $sizeRange = $this->getClassFromPercent(($arrayInfo['size'] / $max) * 100);
        $arrayInfo['range'] = $sizeRange;
        if ($returnType == 'array') {
          $return [$tag] = $arrayInfo;
        } elseif ($returnType == 'html') {
          $return .= $this->htmlizeTag($arrayInfo, $sizeRange);
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
   * @param array  $arrayInfo The data to pass into the closure
   * @param string $sizeRange The size to pass into the closure
   *
   * @return string
   */
  public function htmlizeTag($arrayInfo, $sizeRange)
  {
    $htmlizeTagFunction = $this->htmlizeTagFunction;
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

  /**
   * Removes tags from the whole array
   *
   * @return array The tag array excluding the removed tags
   */
  protected function remove()
  {
    $_tagsArray = array();
    foreach ($this->tagsArray as $key => $value) {
      if (!in_array($value['tag'], $this->getRemoveTags())) {
        $_tagsArray[$value['tag']] = $value;
      }
    }
    $this->tagsArray = array();
    $this->tagsArray = $_tagsArray;
    return $this->tagsArray;
  }

  /**
   * Orders the cloud by a specific field
   *
   * @param array  $unsortedArray Collection of unsorted data
   * @param string $sortField     The field that should be sorted
   * @param string $sortWay       The direction to sort the data [SORT_ASC|SORT_DESC]
   *
   * @return mixed
   */
  protected function order($unsortedArray, $sortField, $sortWay = 'SORT_ASC')
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

  /**
   * Parses the array and returns limited amount of items
   *
   * @return array The collection limited to the amount defined
   */
  protected function limit()
  {
    $limit = $this->getLimit();
    if ($limit !== null) {
      $i = 0;
      $_tagsArray = array();
      foreach ($this->tagsArray as $key => $value) {
        if ($i < $limit) {
          $_tagsArray[$value['tag']] = $value;
        }
        $i++;
      }
      $this->tagsArray = array();
      $this->tagsArray = $_tagsArray;
    }
    return $this->tagsArray;
  }

  /**
   * Reduces the array by removing strings with a
   * length shorter than the minLength
   *
   * @return array The collection of items within
   * the string length boundaries
   */
  protected function minLength()
  {
    $limit = $this->getMinLength();
    if ($limit !== null) {
      $i = 0;
      $_tagsArray = array();
      foreach ($this->tagsArray as $key => $value) {
        if (strlen($value['tag']) >= $limit) {
          $_tagsArray[$value['tag']] = $value;
        }
        $i++;
      }
      $this->tagsArray = array();
      $this->tagsArray = $_tagsArray;
    }
    return $this->tagsArray;
  }

  /**
   * Finds the maximum 'size' value of an array
   *
   * @return int The maximum size value in the entire collection
   */
  protected function getMax()
  {
    $max = 0;
    if (!empty($this->tagsArray)) {
      $p_size = 0;
      foreach ($this->tagsArray as $cKey => $cVal) {
        $c_size = $cVal['size'];
        if ($c_size > $p_size) {
          $max = $c_size;
          $p_size = $c_size;
        }
      }
    }
    return $max;
  }

  /**
   * Shuffle associated names in array
   *
   * @return array The shuffled collection
   */
  protected function shuffle()
  {
    $keys = array_keys($this->tagsArray);
    shuffle($keys);
    if (count($keys) && is_array($keys)) {
      $tmpArray = $this->tagsArray;
      $this->tagsArray = array();
      foreach ($keys as $key => $value)
        $this->tagsArray[$value] = $tmpArray[$value];
    }
    return $this->tagsArray;
  }

  /**
   * Get the class range using a percentage
   *
   * @param $percent
   *
   * @return float|int The respective class name based on the percentage value
   */
  protected function getClassFromPercent($percent)
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
   *
   * @param int $percent The percentage value
   *
   * @return float|int
   */
  public function calculateClassFromPercent($percent)
  {
    return $this->getClassFromPercent($percent);
  }

  /**
   * Convert accented chars into basic latin chars
   * @see http://stackoverflow.com/questions/6837148/change-foreign-characters-to-normal-equivalent
   *
   * @param string $string Non transliterated string
   *
   * @return mixed Transliterated string
   */
  protected function transliterate($string)
  {
    return str_replace(array_keys($this->transliterationTable), array_values($this->transliterationTable), $string);
  }
}
