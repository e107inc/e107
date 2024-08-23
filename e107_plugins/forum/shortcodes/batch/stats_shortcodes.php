<?php
/*
 * Copyright e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 *
 * Forum plugin - view shortcodess
 *
*/

if (!defined('e107_INIT')) { exit; }

e107::plugLan('forum', 'front', true);

class plugin_forum_stats_shortcodes extends e_shortcode
{
	private $forum_rules;
	private $gen;
	private $prefs;	
	private $sql;
	public $newFlagList;


	function __construct()
	{
//		$this->forum_rules = function_exists('forum_rules') ? forum_rules('check') : '';
        $this->gen = e107::getDate();
		$this->sql = e107::getDb();
		$this->count = 1;
//        $this->prefs = e107::pref('forum');
/*
$this->sql->select('forum_post', 'post_datestamp', 'post_datestamp > 0 ORDER BY post_datestamp ASC LIMIT 0,1', 'default');
		$fp = $this->sql->fetch();
		$fp = is_array($fp) ? $fp : array();
//		var_dump ($row);
//		var_dump ($fp);
		$this->open_ds_in = (int) varset($fp['post_datestamp']);
*/
}

	function sc_open_date()
	{
//var_dump ($this->var['open_ds']);
//var_dump ($this->open_ds_in);
//var_dump ("PLUGIN");
		return $this->gen->convert_date($this->var['open_ds'], 'long');
	}
		
	function sc_open_since()
	{
		return $this->gen->computeLapse($this->var['open_ds']);
	}

	function sc_postsperday()
	{
        $open_days = floor((time()-$this->var['open_ds']) / 86400);
		return ($open_days < 1 ? $this->sc_total_posts() : round($this->sc_total_posts() / $open_days));
	}

	function sc_total_views()
	{
		$total_views = 0;

	//	$sql = e107::getDb();
		if ($this->sql->gen('SELECT sum(thread_views) AS total FROM `#forum_thread` '))
		{
			$row = $this->sql->fetch();
			$total_views = $row['total'];
		}
		return $total_views;
	}

	function sc_total_posts(){
		return e107::getDb()->count('forum_post');
	}

	function sc_total_topics(){
		return e107::getDb()->count('forum_thread');
	}

	function sc_total_replies(){
		return $this->sc_total_posts() - $this->sc_total_topics();
	}
	
	function sc_db_size(){
		return $this->var['db_size'];
	}

	function sc_avg_row_len(){
		return $this->var['avg_row_len'];
	}

	function sc_uinfo(){
//var_dump ($this->var);
		if($this->var['ma']['user_name'])
		{
			//$uinfo = "<a href='".e_HTTP."user.php ?id.{$ma['user_id']}'>{$ma['user_name']}</a>"; //TODO SEf Url .
/*
			$uparams = array('id' => $ma['user_id'], 'name' => $ma['user_name']);
			$link = e107::getUrl()->create('user/profile/view', $uparams);
			$uinfo = "<a href='".$link."'>".$ma['user_name']."</a>";
*/
			return "<a href='".e107::getUrl()->create('user/profile/view', array('id' => $this->var['ma']['user_id'], 'name' => $this->var['ma']['user_name']))."'>".$this->var['ma']['user_name']."</a>";
		}
		else
		{
			$tmp = explode(chr(1), $this->var['ma']['thread_anon']);
			return e107::getParser()->toHTML($tmp[0]);
		}
	}

	function sc_url(){
		$this->var['ma']['thread_sef'] = eHelper::title2sef($this->var['ma']['thread_name'],'dashl');
		return e107::url('forum','topic', $this->var['ma']);
	}

	function sc_thread_name(){
		return $this->var['ma']['thread_name'];
	}
	
	function sc_thread_total_replies(){
		return $this->var['ma']['thread_total_replies'];
	}

	function sc_thread_views(){
		return $this->var['ma']['thread_views'];
	}

	function sc_thread_datestamp(){
		return $this->gen->convert_date($this->var['ma']['thread_datestamp'], "forum");
	}

	function sc_count(){
		return $this->count++;
	}

	function sc_user_name(){
		return $this->var['ma']['user_name'];
	}
	
	function sc_user_url(){
		return e107::url('user/profile/view', $this->var['ma']);
	}

	function sc_user_forums(){
		return $this->var['ma']['user_forums'];
	}

	function sc_user_percentage(){
		return $this->var['ma']['percentage'];
	}

	function sc_percentage_bar(){
		return e107::getForm()->progressBar('prog',$this->var['ma']['percentage']);
	}
}