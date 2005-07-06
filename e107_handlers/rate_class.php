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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/rate_class.php,v $
|     $Revision: 1.12 $
|     $Date: 2005-07-06 13:48:39 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/
@include_once(e_LANGUAGEDIR.e_LANGUAGE."/lan_rate.php");
@include_once(e_LANGUAGEDIR."English/lan_rate.php");
class rater {
	function rateselect($text, $table, $id, $mode=FALSE) {
		//$mode	: if mode is set, no urljump will be used (used in combined comments+rating system)

		$table = preg_replace('/\\W/i', '', $table);
		$id = intval($id);

		$self = $_SERVER['PHP_SELF'];
		if ($_SERVER['QUERY_STRING']) {
			$self .= "?".$_SERVER['QUERY_STRING'];
		}
		
		$jump = "";
		$url = "";
		if($mode==FALSE){
			$jump = "onchange='urljump(this.options[selectedIndex].value)'";
			$url = e_BASE."rate.php?";
		}
		
		$str = $text."
			<select name='rateindex' ".$jump." class='tbox'>
			<option selected='selected'  value='0'>".RATELAN_5."</option>
			<option value='".$url."{$table}^{$id}^{$self}^1'>1</option>
			<option value='".$url."{$table}^{$id}^{$self}^2'>2</option>
			<option value='".$url."{$table}^{$id}^{$self}^3'>3</option>
			<option value='".$url."{$table}^{$id}^{$self}^4'>4</option>
			<option value='".$url."{$table}^{$id}^{$self}^5'>5</option>
			<option value='".$url."{$table}^{$id}^{$self}^6'>6</option>
			<option value='".$url."{$table}^{$id}^{$self}^7'>7</option>
			<option value='".$url."{$table}^{$id}^{$self}^8'>8</option>
			<option value='".$url."{$table}^{$id}^{$self}^9'>9</option>
			<option value='".$url."{$table}^{$id}^{$self}^10'>10</option>
			</select>";
		return $str;
	}

	function rateradio($table, $id) {

		$table = preg_replace('/\\W/i', '', $table);
		$id = intval($id);

		$str = "
			<input type='radio' value='1' />1
			<input type='radio' value='2' />2&nbsp;
			<input type='radio' value='3' />3&nbsp;
			<input type='radio' value='4' />4&nbsp;
			<input type='radio' value='5' />5&nbsp;
			<input type='radio' value='6' />6&nbsp;
			<input type='radio' value='7' />7&nbsp;
			<input type='radio' value='8' />8&nbsp;
			<input type='radio' value='9' />9&nbsp;
			<input type='radio' value='10' />10";
		return $str;
	}

	function checkrated($table, $id) {
		global $sql;

		$table = preg_replace('/\\W/i', '', $table);
		$id = intval($id);

		//$sql = new db;
		if (!$sql->db_Select("rate", "*", "rate_table = '{$table}' AND rate_itemid = '{$id}' ")) {
			return FALSE;
		} else {
			$row = $sql->db_Fetch();

			if (ereg("\.".USERID."\.", $row['rate_voters'])) {
				return TRUE;
			//added option to split an individual users rating
			}elseif (ereg("\.".USERID.chr(1)."([0-9]{1,2})\.", $row['rate_voters'])) {
				return TRUE;
			} else {
				return FALSE;
			}
		}
	}

	function getrating($table, $id, $userid=FALSE) {
		global $sql;
		//userid	: boolean, get rating for a single user, or get general total rating of the item

		$table = preg_replace('/\\W/i', '', $table);
		$id = intval($id);

		//$sql = new db;
		if (!$sql->db_Select("rate", "*", "rate_table = '{$table}' AND rate_itemid = '{$id}' ")) {
			return FALSE;
		} else {
			$rowgr = $sql->db_Fetch();
			if($userid==TRUE){
				$rating = "";
				$rateusers = explode(".", $rowgr['rate_voters']);
				for($i=0;$i<count($rateusers);$i++){
					if(strpos($rateusers[$i], chr(1))){
						$rateuserinfo[$i] = explode(chr(1), $rateusers[$i]);
						if($userid == $rateuserinfo[$i][0]){
							$rating[0] = 0;						//number of votes, not relevant in users rating
							$rating[1] = $rateuserinfo[$i][1];	//the rating by this user
							$rating[2] = 0;						//no remainder is present, because we have a single users rating
							break;
						}
					}else{
						$rating[0] = 0;		//number of votes, not relevant in users rating
						$rating[1] = 0;		//the rating by this user
						$rating[2] = 0;		//no remainder is present, because we have a single users rating							
					}
				}
			}else{
				$rating[0] = $rowgr['rate_votes']; // $rating[0] == number of votes
				$tmp = $rowgr['rate_rating'] / $rowgr['rate_votes'];
				$tmp = explode(".", $tmp);
				$rating[1] = $tmp[0];
				if(isset($tmp[1])){
					$rating[2] = substr($tmp[1], 0, 1);
				}else{
					$rating[2] = "0";
				}
			}

			return $rating;
		}
	}

	function enterrating($rateindex){
		global $sql;

		$qs = explode("^", $rateindex);
			
		if (!$qs[0] || USER == FALSE || $qs[3] > 10 || $qs[3] < 1) {
			header("location:".e_BASE."index.php");
			exit;
		}
			
		$table = $qs[0];
		$itemid = $qs[1];
		$returnurl = $qs[2];
		$rate = $qs[3];

		//rating is now stored as userid-rating (to retain individual users rating)
		//$sep = "^";
		$sep = chr(1);
		$voter = USERID.$sep.$qs[3];

		if ($sql->db_Select("rate", "*", "rate_table='$table' AND rate_itemid='$itemid' ")) {
			$row = $sql->db_Fetch();
			$rate_voters = $row['rate_voters'].".".$voter.".";
			$sql->db_Update("rate", "rate_votes=rate_votes+1, rate_rating=rate_rating+'$rate', rate_voters='$rate_voters' WHERE rate_itemid='$itemid' ");
		} else {
			$sql->db_Insert("rate", " 0, '$table', '$itemid', '$rate', '1', '.".$voter.".' ");
		}
	}

	function composerating($table, $id, $enter=TRUE, $userid=FALSE, $nojump=FALSE){
		//enter		: boolean to show (rateselect box + textual info) or not
		//userid	: used to calculate a users given rating
		//nojump	: boolean, if present no urljump will be used (needed in comment_rating system)

		$rate = "";
		if($ratearray = $this -> getrating($table, $id, $userid)){
			if($ratearray[1] > 0){
				for($c=1; $c<= $ratearray[1]; $c++){
					$rate .= "<img src='".e_IMAGE."rate/box.png' alt='' style='height:8px; vertical-align:middle' />";
				}
				if($ratearray[1] < 10){
					for($c=9; $c>=$ratearray[1]; $c--){
						$rate .= "<img src='".e_IMAGE."rate/empty.png' alt='' style='height:8px; vertical-align:middle' />";
					}
				}
				$rate .= "<img src='".e_IMAGE."rate/boxend.png' alt='' style='height:8px; vertical-align:middle' />";
				if($ratearray[2] == ""){ $ratearray[2] = 0; }
				$rate .= "&nbsp;".$ratearray[1].".".$ratearray[2];
				if(!$userid){
					$rate .= " - ".$ratearray[0]."&nbsp;";
					$rate .= ($ratearray[0] == 1 ? RATELAN_0 : RATELAN_1);
				}
			}
		}else{
			if($enter===TRUE){
				$rate .= RATELAN_4;
			}
		}
		if($enter===TRUE){
			if(!isset($ratearray[1]) || $ratearray[1] > 0){
				$rate .= " - ";
			}
			if(!$this -> checkrated($table, $id) && USER){
				$rate .= $this -> rateselect(RATELAN_2, $table, $id, $nojump);
				
			}else if(USER){
				$rate .= RATELAN_3;
			}
		}

		return $rate;

	}
}
?>