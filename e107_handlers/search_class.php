<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_handlers/search_class.php,v $
|     $Revision: 1.4 $
|     $Date: 2005-02-09 12:27:45 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/

class e_search {
	
	var $query;
	var $orig_query;
	var $keywords;
	var $text;
	var $weight;
	var $total_weight;
	var $nocrop;
	var $endcrop;
	var $stritext;
	var $ret = array();
	var $exact;
	var $match = array();
	
	function e_search() {
		global $pref;
		if (!isset($pref['search_chars'])) {
			$pref['search_chars'] = 150;
			save_prefs();
		}
	}
	
	function search_query($query, $table, $fields, $pre_query, $post_query) {
		global $sql;
		$this -> query = $query;
		$this -> orig_query = $query;
		$this -> keywords = explode(' ', $this -> query);
		foreach ($this -> keywords as $this -> query) {
			foreach ($fields as $field) {
				if (isset($search_query)) {
					$search_query .= " OR ";
				}
				$search_query .= " ".$field." REGEXP '[[:<:]]".$this -> query."[[:>:]]' ";
			}
		}
		return $results = $sql->db_Select($table, '*', $pre_query." (".$search_query.") ".$post_query);
	}
	
	function search_sort() {
		$args = func_get_args();
		$numargs = func_num_args();
		call_user_func_array('array_multisort', $args);
		foreach ($args[0] as $arg_id => $arg_value) {
			$text .= $args[$numargs-2][$arg_id];
		}
		return $text;
	}
	
	function search_link($link) {
		return "<img src='".THEME."images/bullet2.gif' alt='bullet' /> <b><a href='".$link."'>".$this -> ret['text']."</a></b><br />";
	}
	
	function search_detail($item_text) {
		$relevance = round((100 / (count($this -> keywords) * ($this -> total_weight * 2))) * $this -> ret['weight']);
		$this -> ret['text'] .= "<br /><span class='smalltext'>".$item_text." | Relevance: ".$relevance."%</span><br /><br />";
		return $this -> ret;
	}
	
	function parsesearch($text, $weight = FALSE, $endcrop=FALSE, $reset_weight=FALSE) {
		global $tp;
		$this -> weight = $weight;
		$this -> endcrop = $endcrop;
		$this -> text = strip_tags($tp -> toHTML(str_replace(array('[', ']'), array('<', '>'), $text), FALSE));
		$this -> ret['text'] = $this -> text;
		if ($reset_weight) {
			$this -> ret['weight'] = 0;
			$this -> total_weight = $this -> weight;
		} else {
			$this -> total_weight += $this -> weight;
		}
		$this -> exact = TRUE;
		$this -> query = $this -> orig_query;
		if ($this -> stritext = stristr($this -> text, $this -> query)) {
			$this -> parsesearch_match();
		} else {
			$this -> exact = FALSE;
			foreach ($this -> keywords as $this -> query) {
				if ($this -> stritext = stristr($this -> text, $this -> query)) {
					$this -> parsesearch_match();
				}
			}
		}
		$this -> parsesearch_crop();
	}
	
	function parsesearch_match() {
		$this -> match['pos'] = strlen($this -> text) - strlen($this -> stritext);
		$this -> match['key'] = substr($this -> text, $this -> match['pos'], strlen($this -> query));
		if ($this -> exact) {
			$this -> ret['weight'] = round((($this -> weight * 2) * count($this -> keywords)));
		} else {
			$this -> ret['weight'] += round(($this -> weight * 1.5));
		}
		$this -> parsesearch_crop();
		$this -> ret['text'] = eregi_replace("[[:<:]]".$this -> query."[[:>:]]", "<span class='searchhighlight'>".$this -> match['key']."</span>", $this -> ret['text']);
		$this -> endcrop = TRUE;
	}
	
	function parsesearch_crop() {
		global $pref;
		if (!$this -> endcrop && (strlen($this -> ret['text']) > $pref['search_chars'])) {
			if ($this -> match['pos'] < ($pref['search_chars'] - strlen($this -> query))) {
				$this -> ret['text'] = substr($this -> ret['text'], 0, $pref['search_chars'])."...";
			} else if ($this -> match['pos'] > (strlen($this -> ret['text']) - ($pref['search_chars'] - strlen($this -> query)))) {
				$this -> ret['text'] = "...".substr($this -> ret['text'], (strlen($this -> ret['text']) - ($pref['search_chars'] - strlen($this -> query))));
			} else {
				$this -> ret['text'] = "...".substr($this -> ret['text'], ($this -> match['pos'] - round(($pref['search_chars'] / 3))), $pref['search_chars'])."...";
			}
		}
	}
}
?>