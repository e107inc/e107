<?php


/**
 *
 */
class e_emote
{

	private $search = array();
	private $replace = array();
	public $emotes;
	private $singleSearch = array();
	private $singleReplace = array();

	public function __construct()
	{

		$pref = e107::getPref();

		if(empty($pref['emotepack']))
		{
			$pref['emotepack'] = 'default';
			e107::getConfig('emote')->clearPrefCache('emote');
			e107::getConfig('core')->set('emotepack', 'default')->save(false, true, false);
		}

		$this->emotes = e107::getConfig('emote')->getPref();

		if(empty($this->emotes))
		{
			return;
		}

		$base = defined('e_HTTP_STATIC') && is_string(e_HTTP_STATIC) ? e_HTTP_STATIC : SITEURLBASE;

		foreach($this->emotes as $key => $value)
		{

			$value = trim($value);

			if($value)
			{    // Only 'activate' emote if there's a substitution string set


				$key = preg_replace("#!(\w{3,}?)$#si", ".\\1", $key);
				// Next two probably to sort out legacy issues - may not be required any more
				//	$key = preg_replace("#_(\w{3})$#", ".\\1", $key);

				$key = str_replace('!', '_', $key);

				$filename = e_IMAGE . 'emotes/' . $pref['emotepack'] . '/' . $key;


				$fileloc = $base . e_IMAGE_ABS . 'emotes/' . $pref['emotepack'] . '/' . $key;

				$alt = str_replace(array('.png', '.gif', '.jpg'), '', $key);

				if(file_exists($filename))
				{
					$tmp = explode(' ', $value);
					foreach($tmp as $code)
					{
						$img = "<img class='e-emoticon' src='" . $fileloc . "' alt=\"" . $alt . '"  />';

						$this->search[] = "\n" . $code;
						$this->replace[] = "\n" . $img;

						$this->search[] = ' ' . $code;
						$this->replace[] = ' ' . $img;

						$this->search[] = '>' . $code; // Fix for emote within html.
						$this->replace[] = '>' . $img;

						$this->singleSearch[] = $code;
						$this->singleReplace[] = $img;

					}
				}
			}
			else
			{
				unset($this->emotes[$key]);
			}


		}

		//	print_a($this->regSearch);
		//	print_a($this->regReplace);

	}

	/**
	 * Return a list of the available emoticons.
	 * @return array
	 */
	public function getList()
	{

		return $this->emotes;
	}

	/**
	 * @param $text
	 * @return string
	 */
	public function filterEmotes($text)
	{

		if(empty($text))
		{
			return '';
		}

		if(!empty($this->singleSearch) && (strlen($text) < 12) && in_array($text, $this->singleSearch)) // just one emoticon with no space, line-break or html tags around it.
		{
			return str_replace($this->singleSearch, $this->singleReplace, $text);
		}

		return str_replace($this->search, $this->replace, $text);

	}


	/**
	 * @param $text
	 * @return string|string[]
	 */
	public function filterEmotesRev($text)
	{

		return str_replace($this->replace, $this->search, $text);
	}

}