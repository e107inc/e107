<?php
/*
 * Copyright e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 *
 * Forum plugin - statistics shortcodes
 *
 */

if (!defined('e107_INIT')) { exit; }

e107::plugLan('forum', 'front', true);

/**
 * Shortcodes for forum_stats_template.php.
 *
 * Every value is supplied by forumStats::init() through setVars(); nothing here
 * queries. The summary block is handed the totals it needs, and each table row
 * is handed its own record plus a 'count' key holding its position in that
 * table.
 */
class plugin_forum_stats_shortcodes extends e_shortcode
{
	/** @var convert */
	private $gen;

	public function __construct()
	{
		parent::__construct();

		$this->gen = e107::getDate();
	}

	/**
	 * Row number within the current table. Supplied per row by the caller so
	 * that every table restarts at 1.
	 */
	public function sc_count()
	{
		return varset($this->var['count']);
	}

	// ------------------------------------------------------------------ summary

	public function sc_open_date()
	{
		return $this->gen->convert_date((int) varset($this->var['open_ds']), 'long');
	}

	public function sc_open_since()
	{
		return $this->gen->computeLapse((int) varset($this->var['open_ds']));
	}

	public function sc_total_posts()
	{
		return varset($this->var['total_posts']);
	}

	public function sc_total_topics()
	{
		return varset($this->var['total_topics']);
	}

	public function sc_total_replies()
	{
		return varset($this->var['total_replies']);
	}

	public function sc_total_views()
	{
		return varset($this->var['total_views']);
	}

	public function sc_postsperday()
	{
		return varset($this->var['postsperday']);
	}

	public function sc_db_size()
	{
		return varset($this->var['db_size']);
	}

	public function sc_avg_row_len()
	{
		return varset($this->var['avg_row_len']);
	}

	// -------------------------------------------------------------- thread rows

	public function sc_url()
	{
		$row = $this->var;
		$row['thread_sef'] = eHelper::title2sef(varset($row['thread_name']), 'dashl');

		return e107::url('forum', 'topic', $row);
	}

	public function sc_thread_name()
	{
		return varset($this->var['thread_name']);
	}

	public function sc_thread_total_replies()
	{
		return varset($this->var['thread_total_replies']);
	}

	public function sc_thread_views()
	{
		return varset($this->var['thread_views']);
	}

	public function sc_thread_datestamp()
	{
		return $this->gen->convert_date(varset($this->var['thread_datestamp']), 'forum');
	}

	/**
	 * Thread author: linked where the account still exists, rendered from the
	 * stored guest name where it does not.
	 */
	public function sc_uinfo()
	{
		if(!empty($this->var['user_name']))
		{
			$params = array('id' => $this->var['user_id'], 'name' => $this->var['user_name']);

			return "<a href='".e107::getUrl()->create('user/profile/view', $params)."'>".$this->var['user_name']."</a>";
		}

		$tmp = explode(chr(1), (string) varset($this->var['thread_anon']));

		return e107::getParser()->toHTML($tmp[0]);
	}

	// ---------------------------------------------------------------- user rows

	public function sc_user_name()
	{
		return varset($this->var['user_name']);
	}

	public function sc_user_url()
	{
		return e107::url('user/profile/view', $this->var);
	}

	public function sc_user_forums()
	{
		return varset($this->var['user_forums']);
	}

	public function sc_user_percentage()
	{
		return varset($this->var['percentage']);
	}

	public function sc_percentage_bar()
	{
		return e107::getForm()->progressBar('prog', varset($this->var['percentage']));
	}
}
