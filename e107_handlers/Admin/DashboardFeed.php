<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Admin;

/**
 * One of the e107.org news feeds on the admin dashboard, kept for the whole site and shown while a background request renews it.
 */
class DashboardFeed
{
	/** @var int minutes before e107.org is asked again after a fetch that brought a copy */
	private $lifetime = 180;

	/** @var int minutes before e107.org is asked again after a fetch that brought none */
	private $retry = 15;

	/** @var int minutes after its fetch that a copy stops being shown */
	private $maximumAge = 7 * 24 * 60;

	/** @var string core, plugin or theme */
	private $type;

	/** @var \ecache */
	private $cache;

	/** @var \xmlClass */
	private $xml;

	/** @var \e_parse */
	private $tp;

	/** @var string */
	private $tag;

	/** @var array|null|false the stored entry, null when there is none, false until it has been read */
	private $entry = false;

	/**
	 * @param string $type core, plugin or theme
	 * @param \ecache $cache
	 * @param \xmlClass $xml
	 * @param \e_parse $tp
	 */
	public function __construct($type, \ecache $cache, \xmlClass $xml, \e_parse $tp)
	{
		if(!in_array($type, array('core', 'plugin', 'theme'), true))
		{
			throw new \InvalidArgumentException('No dashboard feed is called '.var_export($type, true));
		}

		$this->type = $type;
		$this->cache = $cache;
		$this->xml = $xml;
		$this->tp = $tp;
		$this->tag = 'Infopanel_'.$type;
	}

	/**
	 * @return string the kept copy, with an add-on feed's link to more, or '' when there is none or its fetch is older than the maximum age
	 */
	public function copy()
	{
		$entry = $this->entry();

		if($entry === null || $entry['html'] === '' || (time() - $entry['fetched']) > ($this->maximumAge * 60))
		{
			return '';
		}

		if($this->type === 'core')
		{
			return $entry['html'];
		}

		return $entry['html']."<div class='right'><a href='".$this->onlineLink()."'>".LAN_MORE."</a></div>";
	}

	/**
	 * @return bool true when nothing is kept, the last fetch is dated after now, or it has outlived the lifetime when it brought a copy and the retry interval when it did not
	 */
	public function isDue()
	{
		$entry = $this->entry();

		if($entry === null)
		{
			return true;
		}

		$wait = ($entry['fetched'] >= $entry['checked']) ? $this->lifetime : $this->retry;
		$since = time() - $entry['checked'];

		return ($since < 0) || ($since >= ($wait * 60));
	}

	/**
	 * Ask e107.org for a new copy when one is due, keeping the old copy when the answer is empty.
	 *
	 * @return string the copy to show
	 */
	public function renew()
	{
		if(!$this->isDue())
		{
			return $this->copy();
		}

		$entry = $this->entry();
		$checked = time();
		$this->keep(($entry === null) ? '' : $entry['html'], ($entry === null) ? 0 : min($entry['fetched'], $checked - 1), $checked);

		$html = $this->fetch();

		if($html !== '')
		{
			$this->keep($html, $checked, $checked);
		}

		return $this->copy();
	}

	/**
	 * @param string $html
	 * @param int $fetched when the fetch that brought the copy started, 0 for never
	 * @param int $checked when the last fetch started
	 * @return void
	 */
	private function keep($html, $fetched, $checked)
	{
		$this->entry = array('html' => $html, 'fetched' => $fetched, 'checked' => $checked);
		$this->cache->set($this->tag, json_encode($this->entry), true, false, true);
	}

	/**
	 * @return array|null
	 */
	private function entry()
	{
		if($this->entry === false)
		{
			$stored = $this->cache->retrieve($this->tag, false, true, true);
			$entry = is_string($stored) ? json_decode($stored, true) : null;
			$valid = isset($entry['html'], $entry['fetched'], $entry['checked']) && is_string($entry['html']) && is_int($entry['fetched']) && is_int($entry['checked']);
			$this->entry = $valid ? $entry : null;
		}

		return $this->entry;
	}

	/**
	 * @return string the rendered feed, '' when the fetch failed or found nothing
	 */
	private function fetch()
	{
		$data = $this->xml->getRemoteFile($this->source(), 3);

		if(empty($data))
		{
			return '';
		}

		$rows = $this->xml->parseXml($data, 'advanced');

		return ($this->type === 'core') ? $this->renderNews($rows) : $this->renderAddons($rows);
	}

	/**
	 * @return string the feed's address, which e107_config.php may override
	 */
	private function source()
	{
		return ($this->type === 'core') ? ADMINFEED : ADDONFEED.'?limit=3&type='.$this->type;
	}

	/**
	 * @param array|false $rows
	 * @return string
	 */
	private function renderNews($rows)
	{
		$items = $this->listOf(isset($rows['channel']['item']) ? $rows['channel']['item'] : null);

		if(empty($items))
		{
			return '';
		}

		$tp = $this->tp;
		$text = '<div style="margin-left:10px;margin-top:10px">';

		foreach(array_slice($items, 0, 3) as $row)
		{
			$description = $tp->text_truncate($tp->toText($this->field($row, 'description')), 150);
			$text .= '
			<div class="media">
			  <div class="media-body">
			    <h4 class="media-heading"><a target="_blank" href="'.$tp->toUrlAttribute($this->field($row, 'link')).'">'.htmlspecialchars($this->field($row, 'title'), ENT_QUOTES, 'UTF-8').'</a> <small>— '.htmlspecialchars($this->field($row, 'pubDate'), ENT_QUOTES, 'UTF-8').'</small></h4>
			   '.htmlspecialchars($description, ENT_QUOTES, 'UTF-8', false).'
			  </div></div>';
		}

		return $text.'</div>';
	}

	/**
	 * @param array|false $rows
	 * @return string
	 */
	private function renderAddons($rows)
	{
		$items = $this->listOf(isset($rows[$this->type]) ? $rows[$this->type] : null);

		if(empty($items))
		{
			return '';
		}

		$tp = $this->tp;
		$link = $this->onlineLink();

		$text = "<div style='margin-top:10px'>";

		foreach($items as $val)
		{
			$meta = isset($val['@attributes']) ? $val['@attributes'] : null;
			$img = $this->field($meta, ($this->type === 'theme') ? 'thumbnail' : 'icon');
			$description = $tp->text_truncate($tp->toText($this->field($val, 'description')), 150);
			$text .= '<div class="media">';
			$text .= '<div class="media-left">
		    <a href="'.$link.'">
		      <img class="media-object img-rounded rounded" src="'.$tp->toUrlAttribute($img).'" style="width:100px" alt="" />
		    </a>
		  </div>
		  <div class="media-body">
		    <h4 class="media-heading"><a href="'.$link.'">'.htmlspecialchars($this->field($meta, 'name'), ENT_QUOTES, 'UTF-8').' v'.htmlspecialchars($this->field($meta, 'version'), ENT_QUOTES, 'UTF-8').'</a> <small>&mdash; '.htmlspecialchars($this->field($meta, 'author'), ENT_QUOTES, 'UTF-8').'</small></h4>
		    '.htmlspecialchars($description, ENT_QUOTES, 'UTF-8', false).'
		  </div>';
			$text .= '</div>';
		}

		return $text."</div>";
	}

	/**
	 * @param mixed $items what the parser made of the feed's repeated element
	 * @return array the items as a list, which the parser does not give for a single item
	 */
	private function listOf($items)
	{
		if(empty($items) || !is_array($items))
		{
			return array();
		}

		return isset($items[0]) ? $items : array($items);
	}

	/**
	 * @param mixed $fields an item or its attributes, as the parser gave them
	 * @param string $name
	 * @return string the field's text, '' when it is missing or is not text, as the parser gives an empty element
	 */
	private function field($fields, $name)
	{
		return (is_array($fields) && isset($fields[$name]) && is_string($fields[$name])) ? $fields[$name] : '';
	}

	/**
	 * @return string the admin page listing what e107.org offers of this feed's add-on type
	 */
	private function onlineLink()
	{
		return ($this->type === 'plugin') ? e_ADMIN_ABS.'plugin.php?mode=online' : e_ADMIN_ABS.'theme.php?mode=main&action=online';
	}
}
