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
|     $Revision: 1.11 $
|     $Date: 2005-02-12 11:43:17 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/

class e_search {
	
	var $query;
	var $text;
	var $pos;
	
	function e_search() {
		global $pref;
		if (!isset($pref['search_chars'])) {
			$pref['search_chars'] = 150;
			save_prefs();
		}
	}
	
	function parsesearch($table, $return_fields, $search_fields, $weights, $handler, $no_results, $where, $order) {
		global $sql, $query, $tp;
		$this -> query = $query;	
		$keywords = explode(' ', $this -> query);
		foreach ($search_fields as $field_key => $field) {
			$search_query[] = "(".$weights[$field_key]." * (MATCH(".$field.") AGAINST ('".$this -> query."' IN BOOLEAN MODE)))";
		}
		$match_query = implode(' + ', $search_query);
		$field_query = implode(',', $search_fields);		
		if ($ps['results'] = $sql->db_Select_gen("SELECT ".$return_fields.", (".$match_query.") AS relevance FROM #".$table." WHERE ".$where." ( MATCH(".$field_query.") AGAINST ('".$this -> query."' IN BOOLEAN MODE) ) HAVING relevance > 0 ORDER BY relevance DESC ".$order.";")) {
			while ($row = $sql->db_Fetch()) {
				$res = call_user_func($handler, $row);
				$matches = array($res['title'], $res['summary']);
				$endcrop = FALSE;
				$output = '';
				$title = TRUE;
				foreach ($matches as $this -> text) {
					$this -> text = strip_tags($tp -> toHTML(str_replace(array('[', ']'), array('<', '>'), $this -> text), FALSE));
					foreach ($keywords as $this -> query) {
						if (strpos($this -> query, '-') == FALSE) {
							if (strpos($this -> query, '*') !== FALSE) {
								$regex_append = "";
								$this -> query = str_replace('*', '', $this -> query);
							} else {
								$regex_append = "[[:>:]]";	
							}
							$this -> query = str_replace(array('"', '+'), array('', ''), $this -> query);
							if (($match_start = stristr($this -> text, $this -> query)) !== FALSE) {
								$this -> pos = strlen($this -> text) - strlen($match_start);
								if (!$endcrop || !$title) {
									$this -> parsesearch_crop();
									$endcrop = TRUE;
								}
								$key = substr($this -> text, $this -> pos, strlen($this -> query));
								$this -> text = eregi_replace("[[:<:]]".$this -> query.$regex_append, "<span class='searchhighlight'>".$key."</span>", $this -> text);
							}
						}
					}
					if ($title) {
						$this -> text = "<img src='".THEME."images/bullet2.gif' alt='bullet' /> <b><a href='".$res['link']."'>".$this -> text."</a></b><br />";
					} else if (!$endcrop) {
						$this -> parsesearch_crop();
					}
					$output .= $this -> text;
					$title = FALSE;
				}
				$ps['text'] .= $output."<br /><span class='smalltext'>".$res['detail']." | Relevance: ".$row['relevance']."</span><br /><br />";
			}
		} else {
			$ps['text'] = $no_results;
		}
		return $ps;
	}
	
	function parsesearch_crop() {
		global $pref;
		if (strlen($this -> text) > $pref['search_chars']) {
			if ($this -> pos < ($pref['search_chars'] - strlen($this -> query))) {
				$this -> text = substr($this -> text, 0, $pref['search_chars'])."...";
			} else if ($this -> pos > (strlen($this -> text) - ($pref['search_chars'] - strlen($this -> query)))) {
				$this -> text = "...".substr($this -> text, (strlen($this -> text) - ($pref['search_chars'] - strlen($this -> query))));
			} else {
				$this -> text = "...".substr($this -> text, ($this -> pos - round(($pref['search_chars'] / 3))), $pref['search_chars'])."...";
			}
			$match_start = stristr($this -> text, $this -> query);
			$this -> pos = strlen($this -> text) - strlen($match_start);
		}
	}
}

?>