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
|     $Revision: 1.14 $
|     $Date: 2005-02-13 20:24:20 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/

class e_search {
	
	var $query;
	var $text;
	var $pos;
	
	function e_search() {
		global $pref;
		$pref['search_sort'] = 'php';
		if (!$pref['search_chars']) {
			$pref['search_chars'] = 150;
		}
		save_prefs();
	}
	
	function parsesearch($table, $return_fields, $search_fields, $weights, $handler, $no_results, $where, $order, $php_order) {
		global $sql, $query, $tp, $pref;
		$this -> query = $query;	
		$keywords = explode(' ', $this -> query);
		$field_query = implode(',', $search_fields);
		if($pref['search_sort'] == 'php') {
			foreach ($keywords as $key) {
				foreach ($search_fields as $field) {
					$search_query[] = " ".$field." REGEXP '[[:<:]]".$key."[[:>:]]' ";
				}
				$match_query = implode(' OR ', $search_query);
			}
			$sql_query = "SELECT ".$return_fields." FROM #".$table." WHERE ".$where." (".$match_query.");";
		} else {
			foreach ($search_fields as $field_key => $field) {
				$search_query[] = "(".$weights[$field_key]." * (MATCH(".$field.") AGAINST ('".$this -> query."' IN BOOLEAN MODE)))";
			}
			$match_query = implode(' + ', $search_query);
			$sql_query = "SELECT ".$return_fields.", (".$match_query.") AS relevance FROM #".$table." WHERE ".$where." ( MATCH(".$field_query.") AGAINST ('".$this -> query."' IN BOOLEAN MODE) ) HAVING relevance > 0 ORDER BY relevance DESC ".$order.";";
		}
		if($pref['search_sort'] == 'php') {
			if (count($keywords) > 1) {
				$php_keywords[0] = $query;
				foreach ($keywords as $php_key) {
					$php_keywords[] = $php_key;
				}
				$keywords = $php_keywords;
				$keycount = count($keywords) - 1;
			} else {
				$keycount = count($keywords);
			}
		}
		if ($ps['results'] = $sql->db_Select_gen($sql_query)) {
			while ($row = $sql->db_Fetch()) {
				$res = call_user_func($handler, $row);
				$matches = array($res['title'], $res['summary']);
				$endcrop = FALSE;
				$output = '';
				$title = TRUE;
				if($pref['search_sort'] == 'php') {
					$weight = 0;
					$x = 0;
				}
				foreach ($matches as $this -> text) {
					if($pref['search_sort'] == 'php') {
						$exact = TRUE;
					}
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
								if($pref['search_sort'] == 'php') {
									if ($exact) {
										$weight = (($weights[$x] * 2) * ($keycount));
										$endweight = TRUE;
									} else if (!$endweight) {
										$weight += $weights[$x];
									}
								}
								if (!$endcrop && !$title) {
									$this -> parsesearch_crop();
									$endcrop = TRUE;
								}
								$key = substr($this -> text, $this -> pos, strlen($this -> query));
								$this -> text = eregi_replace("[[:<:]]".$this -> query.$regex_append, "<span class='searchhighlight'>".$key."</span>", $this -> text);
							}
						}
						if($pref['search_sort'] == 'php') {
							$exact = FALSE;
						}
					}
					if ($title) {
						$this -> text = "<img src='".THEME."images/bullet2.gif' alt='bullet' /> <b><a href='".$res['link']."'>".$this -> text."</a></b><br />";
					} else if (!$endcrop && !$title) {
						$this -> parsesearch_crop();
					}
					$output .= $this -> text;
					$title = FALSE;
					if($pref['search_sort'] == 'php') {
						$endweight = FALSE;
						$x++;
					}
				}
				if($pref['search_sort'] == 'php') {
					$relevance = $weight;
					$output_array['weight'][] = $weight;
					$output_array['news_datestamp'][] = $row['news_datestamp'];
				} else {
					$relevance = $row['relevance'];
				}
				$output_array['text'][] = $output."<br /><span class='smalltext'>".$res['detail']." | Relevance: ".$relevance."</span><br /><br />";
			}
			if($pref['search_sort'] == 'php') {
				$args[] = $output_array['weight'];
				$args[] = SORT_DESC;
				$a = FALSE;
				foreach ($php_order as $user_args) {
					if (!$a) {
						$args[] = $output_array[$user_args];
						$a = TRUE;
					} else {
						$args[] = $user_args;
						$a = FALSE;
					}
				}
				$args[] = $output_array['text'];
				$args[] = SORT_DESC;
				call_user_func_array('array_multisort', $args);
				foreach ($output_array['weight'] as $arg_id => $arg_value) {
					$ps['text'] .= $output_array['text'][$arg_id];
				}
			} else {
				$ps['text'] = implode('', $output_array['text']);
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