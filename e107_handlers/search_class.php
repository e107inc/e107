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
|     $Revision: 1.1 $
|     $Date: 2005-02-07 03:47:38 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/

class e_search {
	
	function search_query($keywords, $table, $fields, $pre_query, $post_query) {
		global $sql;
		$search_query = '';
		foreach ($keywords as $keyword) {
			foreach ($fields as $field) {
				if ($search_query != '') {
					$search_query .= " OR ";
				}
				$search_query .= " ".$field." REGEXP ('".$keyword."') ";
			}
		}
		return $results = $sql->db_Select($table, '*', $pre_query." (".$search_query.") ".$post_query);
	}
	
	function parsesearch_match($text, $temp, $match, $key_count, $weight, $exact, $rel_weight) {
		$match_array['pos'] = strlen($text)-strlen($temp);
		$match_array['matchedText'] = substr($text,$match_array['pos'],strlen($match));
		if ($exact) {
			$match_array['rel_weight'] = (($weight*2)*count($key_count));
		} else {
			$match_array['rel_weight'] = $rel_weight + $weight;
		}
		return $match_array;
	}
	
	function parsesearch_crop($pos, $crop_match, $text, $nocrop, $endcrop) {
		if (!$nocrop && !$endcrop && (strlen($text) > 150)){
			if ($pos < (150 - strlen($crop_match))) {
				$text = substr($text, 0, 150)."...";
			} else if ($pos > (strlen($text) - (150 - strlen($crop_match)))) {
				$text = "...".substr($text, (strlen($text) - (150 - strlen($crop_match))));
			} else {
				$text = "...".substr($text, ($pos - round((150 / 3))), 150)."...";
			}
		}
		return $text;
	}
	
	function parsesearch_highlight($match, $match_text, $text) {
		$text = eregi_replace($match, "<span class='searchhighlight'>".$match_text."</span>", $text);
		return $text;
	}
	
	function parsesearch_routine($text, $temp, $text_crop, $query, $match, $weight, $nocrop, $exact, $total_weight, $endcrop) {
		$match_array = $this -> parsesearch_match($text, $temp, $query, $match, $weight, $exact, $total_weight);
		$text_crop = $this -> parsesearch_crop($match_array['pos'], $query, $text_crop, $nocrop, $endcrop);
		$text_crop = $this -> parsesearch_highlight($query, $match_array['matchedText'], $text_crop);
		$ret['text'] = $text_crop;
		$ret['weight'] = $match_array['rel_weight'];
		return $ret;
	}
	
	function parsesearch($text, $match, $weight = FALSE, $nocrop=FALSE) {
		global $tp;
		$text = strip_tags($tp->toHTML(str_replace(array('[', ']'), array('<', '>'), $text), FALSE));
		if (is_array($match)) {
			$exact_match = implode(' ', $match);
			$ret['text'] = $text;
			if ($temp = stristr($text, $exact_match)) {
				$ret = $this -> parsesearch_routine($text, $temp, $ret['text'], $exact_match, $match, $weight, $nocrop, TRUE, FALSE, $endcrop);
				$endcrop = TRUE;
			} else {
				foreach ($match as $match_extract) {
					if ($temp = stristr($text, $match_extract)) {
						$ret = $this -> parsesearch_routine($text, $temp, $ret['text'], $match_extract, $match, $weight, $nocrop, FALSE, $ret['weight'], $endcrop);
						$endcrop = TRUE;
					}
				}
			}
			$ret['text'] = $this -> parsesearch_crop('', '', $ret['text'], $nocrop, $endcrop);
			return($ret);
		} else {
			$temp = stristr($text, $match);
			$pos = strlen($text)-strlen($temp);
			$matchedText = substr($text,$pos,strlen($match));
			$text = $this -> parsesearch_crop($pos, $match, $text, $nocrop, $endcrop);
			$text = $this -> parsesearch_highlight($match, $matchedText, $text);
			return($text);
		}
	}
}
?>