<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/classes/rate_class.php
|
|	©Steve Dunstan 2001-2002
|	http://jalist.com
|	stevedunstan@jalist.com
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).	
+---------------------------------------------------------------+
*/
class rater{
	function rateselect($text, $table, $id){
		$self = $_SERVER['PHP_SELF'];
		if($_SERVER['QUERY_STRING']){
			$self .= "?".$_SERVER['QUERY_STRING'];
		}
		
		$str = $text."
		<select name=\"rateindex\" onChange=\"urljump(this.options[selectedIndex].value)\" class=\"tbox\">
		<option selected  value=\"0\">Rate</option>
		<option value=\"rate.php?$table^$id^$self^1\">1</option>
		<option value=\"rate.php?$table^$id^$self^2\">2</option>
		<option value=\"rate.php?$table^$id^$self^3\">3</option>
		<option value=\"rate.php?$table^$id^$self^4\">4</option>
		<option value=\"rate.php?$table^$id^$self^5\">5</option>
		<option value=\"rate.php?$table^$id^$self^6\">6</option>
		<option value=\"rate.php?$table^$id^$self^7\">7</option>
		<option value=\"rate.php?$table^$id^$self^8\">8</option>
		<option value=\"rate.php?$table^$id^$self^9\">9</option>
		<option value=\"rate.php?$table^$id^$self^10\">10</option>
		</select>";
		return $str;
	}

	function rateradio($table, $id){
		$str = "
		<input type=\"radio\" value=\"1\">1
		<input type=\"radio\" value=\"2\">2&nbsp;
		<input type=\"radio\" value=\"3\">3&nbsp;
		<input type=\"radio\" value=\"4\">4&nbsp;
		<input type=\"radio\" value=\"5\">5&nbsp;
		<input type=\"radio\" value=\"6\">6&nbsp;
		<input type=\"radio\" value=\"7\">7&nbsp;
		<input type=\"radio\" value=\"8\">8&nbsp;
		<input type=\"radio\" value=\"9\">9&nbsp;
		<input type=\"radio\" value=\"10\">10";
		return $str;
	}

	function checkrated($table, $id){
		$sql = new db;
		if(!$sql -> db_Select("rate", "*", "rate_table='$table' AND rate_itemid='$id' ")){
			return FALSE;
		}else{
			$row = $sql -> db_Fetch();
			extract($row);
			if(ereg("\.".USERID."\.", $rate_voters)){
				return TRUE;
			}else{
				return FALSE;
			}
		}
	}

	function getrating($table, $id){
		$sql = new db;
		if(!$sql -> db_Select("rate", "*", "rate_table='$table' AND rate_itemid='$id' ")){
			return FALSE;
		}else{
			$row = $sql -> db_Fetch();
			extract($row);
			$rating[0] = $rate_votes; // $rating[0] == number of votes
			$tmp = $rate_rating / $rate_votes;
			$tmp = explode(".", $tmp);
			$rating[1] = $tmp[0];	// $ratomg[1] = main result
			$rating[2] = substr($tmp[1],0,1);	//	$rating[2] == remainder
			return $rating;
		}
	}
}
?>