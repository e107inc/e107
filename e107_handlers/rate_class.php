<?php
/*
 * e107 website system
 *
 * Copyright (C) e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * $URL$
 * $Id$
 */

if (!defined('e107_INIT')) { exit; }

include_lan(e_LANGUAGEDIR.e_LANGUAGE."/lan_rate.php");

class rater {
	
	
	function render($table,$id,$options=array())
	{
			
		list($votes,$score,$uvoted) = $this->getrating($table, $id);
		
	//	return "Table=".$table." itmeId=".$id." Votes=".$votes." score=".$score;
		
		if(is_string($options))
		{
			parse_str($options,$options);
		}
		
		$label = varset($options['label'],RATELAN_5);
			
		$readonly = $this->checkrated($table, $id) ? '1' : '0';
		
		$hintArray = array(RATELAN_POOR,RATELAN_FAIR,RATELAN_GOOD,RATELAN_VERYGOOD,RATELAN_EXCELLENT);

		$datahint = implode(",",$hintArray);
		$path = e_JS_ABS."rate/img/";
		
		$score = ($score / 2);
	//	var_dump($readonly);
	
	
		if(!$votes)
		{
			$voteDiz = RATELAN_4;	
		}
		else
		{
			$voteDiz = ($votes == 1) ? RATELAN_0 : RATELAN_1;	
		}
		
		if($readonly == '1')
		{
			$label = RATELAN_3;	
		}
		
		if(!USERID)
		{
			$label = RATELAN_6; // Please login to vote. 
			$readonly = '1';	
		}
		
		$template = vartrue($options['template'], " STATUS |RATE|VOTES");
		
		$TEMPLATE['STATUS'] 	= "&nbsp;<span class='e-rate-status e-rate-status-{$table}' id='e-rate-{$table}-{$id}' style='display:none'>".$label."</span>";
		$TEMPLATE['RATE'] = "<div class='e-rate e-rate-{$table}' id='{$table}-{$id}'  data-hint=\"{$datahint}\" data-readonly='{$readonly}' data-score='{$score}' data-url='".e_HTTP."rate.php' data-path='{$path}'></div>";
		$TEMPLATE['VOTES'] 	= "<div class='muted e-rate-votes e-rate-votes-{$table}' id='e-rate-votes-{$table}-{$id}'><small>".$this->renderVotes($votes,$score)."</small></div>";

		$tmp = explode("|",$template);
		
		$text = "";
		foreach($tmp as $k)
		{
			$text .= $TEMPLATE[$k];	
		}	
		
		return $text;
	}
	
	

	
	function renderVotes($votes,$score) // TODO use template?
	{	
		if(!$votes)
		{
			$voteDiz = RATELAN_4;	
		}
		else
		{
			$voteDiz = ($votes == 1) ? RATELAN_0 : RATELAN_1;	
		}
		
		return "{$score}/5 : {$votes} ".$voteDiz;
	}
	
	
	
	// Legacy Rate Selector. 
	function rateselect($text, $table, $id, $mode=FALSE)
	{
		//$mode	: if mode is set, no urljump will be used (used in combined comments+rating system)

		$table = preg_replace('/\W/', '', $table);
		$id = intval($id);
		
	//	return $this->render($text,$table,$id,$mode);
		

		// $self = $_SERVER['PHP_SELF'];
		// if ($_SERVER['QUERY_STRING']) {
			// $self .= "?".$_SERVER['QUERY_STRING'];
		// }
		$self = e_REQUEST_URI;

		$jump = "";
		$url = "";
		if($mode==FALSE){
			$jump = "onchange='urljump(this.options[selectedIndex].value)'";
			$url = e_HTTP."rate.php?";
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

		$table = preg_replace('/\W/', '', $table);
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
		
		$table = preg_replace('/\W/', '', $table);
		$id = intval($id);

		$sql = new db;
		if (!$sql->db_Select("rate", "*", "rate_table = '{$table}' AND rate_itemid = '{$id}' ")) {
			return FALSE;
		} else {
			$row = $sql->db_Fetch();

			if (preg_match("/\.".USERID."\./", $row['rate_voters'])) {
				return TRUE;
			//added option to split an individual users rating
			}else if (preg_match("/\.".USERID.chr(1)."([0-9]{1,2})\./", $row['rate_voters'])) {
				return TRUE;
			} else {
				return FALSE;
			}
		}
	}

	function getrating($table, $id, $userid=FALSE) {
		//userid	: boolean, get rating for a single user, or get general total rating of the item

		$table = preg_replace('/\W/', '', $table);
		$id = intval($id);
		
		if($id == 0)
		{
			return "There is no item ID in the rating";	
		}
		$sep = chr(1);

		$sql = new db;
		if (!$sql->select("rate", "*", "rate_table = '{$table}' AND rate_itemid = '{$id}' ")) 
		{
			return FALSE;
		}
		 else 
		 {
			$rowgr = $sql->fetch();
					
			if($userid==TRUE)
			{
				$rating = "";
				$rateusers = explode(".", $rowgr['rate_voters']);
				for($i=0;$i<count($rateusers);$i++){
					if(strpos($rateusers[$i], $sep)){
						$rateuserinfo[$i] = explode($sep, $rateusers[$i]);
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
			}
			else
			{
				$rating[0] = $rowgr['rate_votes']; // $rating[0] == number of votes
				$tmp = $rowgr['rate_rating'] / $rowgr['rate_votes'];
				$tmp = (strpos($tmp,",")) ? explode(",", $tmp) : explode(".", $tmp);
				$rating[1] = $tmp[0];
				if(isset($tmp[1]))
				{
					$rating[2] = substr($tmp[1], 0, 1);
				}
				else
				{
					$rating[2] = "0";
				}
			}

			return $rating;
		}
	}




	function submitVote($table,$itemid,$rate)
	{
		$array = $table."^".$itemid."^^".$rate;
		return $this->enterrating($array,true);
			
	}
	
	/**
	 * @param $table: table without prefix that the like is for
	 * @param $itemid: item id within that table for the item to be liked
	 * @param $curval: optional array of current values for 'up' and 'down'
	 * @param $perc: optional percentage mode. Displays percentages instead of totals. 
	 */
	function renderLike($table,$itemid,$curVal=false,$perc=false)
	{
		$tp = e107::getParser();
			
		$id = "rate-".$table."-".$itemid;	 // "-up or -down is appended to the ID by jquery as both value will need updating. 
		
		if($curVal == false)
		{
			$curVal = $this->getLikes($table,$itemid);	
		}
		
		$p = ($perc) ? "%" : "";	
		
		$upImg = "<img class='e-tip' src='".e_IMAGE_ABS."rate/like_16.png' alt='' title='Like' />";
		$upDown = "<img class='e-tip' src='".e_IMAGE_ABS."rate/dislike_16.png' alt='' title='Dislike' />";
		
		if(deftrue('BOOTSTRAP'))
		{
			$upImg = $tp->toGlyph('icon-thumbs-up',false); // "<i class='icon-thumbs-up'></i>";
			$upDown = $tp->toGlyph('icon-thumbs-down',false); // "<i class='icon-thumbs-down'></i>";
		}
			
		$text = "<span id='{$id}-up'>".intval($curVal['up'])."{$p}</span>
			<a class='e-rate-thumb e-rate-up'  href='".e_HTTP."rate.php?table={$table}&id={$itemid}&type=up#{$id}'>{$upImg}</a> 
				
				<span id='{$id}-down'>".intval($curVal['down'])."{$p}</span>
				<a  class='e-rate-thumb e-rate-down' href='".e_HTTP."rate.php?table={$table}&id={$itemid}&type=down#{$id}'>{$upDown}</a>"; 	
		return $text;	
	}
	
	
	
	protected function getLikes($table,$itemid,$perc=false)
	{
		$sql = e107::getDb();
		if($sql->db_Select("rate","*","rate_table = '{$table}' AND rate_itemid = '{$itemid}' LIMIT 1"))
		{
			$row 		= $sql->db_Fetch();	
			if($perc == true) // Percentage Mode
			{
				$up 	= round(($row['rate_up'] / $row['rate_votes']) * 100) . "%";
				$down 	= round(($row['rate_down'] / $row['rate_votes']) * 100) . "%";	
				return array('up'=>$up,'down'=>$down,'total'=> $row['rate_votes']);	
			}
			else // Counts mode. 
			{
				$up 	= $row['rate_up'];
				$down 	= $row['rate_down'];
				return array('up'=>$up,'down'=>$down,'total'=>$row['rate_votes']);	
			}		
		}
		
		return ($perc == false) ? array('up'=>0,'down'=>0,'total'=>0) : array('up'=>'0%','down'=>'0%','total'=>'0%');
	}
	
	
	function submitLike($table,$itemid,$type,$perc=false)
	{	
		$sql 	= e107::getDb();
			
		if($sql->db_Select("rate","*","rate_table = '{$table}' AND rate_itemid = '{$itemid}' LIMIT 1"))
		{
			$row 		= $sql->db_Fetch();
			
			if(preg_match("/\.". USERID."\./",$row['rate_voters'])) // already voted. 
			{		
				return false;
			}
						
			$newvoters 	= $row['rate_voters'].".".USERID.".";	
			$totalVotes = $row['rate_votes'] + 1; 
			$totalDown	= $row['rate_down'] + (($type == 'down') ? 1 : 0);
			$totalUp	= $row['rate_up'] + (($type == 'up') ? 1 : 0);
					
			$qry = ($type == 'up') ? "rate_up = {$totalUp} " : "rate_down = {$totalDown}";
			$qry .= ", rate_voters = '{$newvoters}', rate_votes = {$totalVotes} ";
			$qry .= " WHERE rate_table = '{$table}' AND rate_itemid = '{$itemid}'";
			
			if($sql->db_Update("rate",$qry))
			{
				if($perc == true) // Percentage Mode
				{
					$up 	= round(($totalUp /$totalVotes) * 100) . "%";
					$down 	= round(($totalDown /$totalVotes) * 100) . "%";		
				}
				else // Counts mode. 
				{
					$up 	= $totalUp;
					$down 	= $totalDown;	
				}
					
				return $up."|".$down;
			}		
		}
		else
		{			
			$insert = array(
				//	"rate_id"		=> 0, // let it increment
					"rate_table"	=> $table,
					"rate_itemid"	=> $itemid,
					"rate_rating"	=> 0,
					"rate_votes"	=> 1,
					"rate_voters"	=> ".".USERID.".",
					"rate_up"		=> ($type == 'up') ? 1 : 0,
					"rate_down"		=> ($type == 'down') ? 1 : 0
			);
				
			if($sql->db_Insert("rate", $insert))
			{
				if($perc == true) // Percentage Mode
				{
					return ($type == 'up') ? "100%|0%" : "0%|100%";
				}
				else
				{
					return ($type == 'up') ? "1|0" : "0|1";	
				}
			}		
		}		
	}


	function enterrating($rateindex,$ajax = false)
	{
		
		$sql = e107::getDb();
		$tp = e107::getParser();

		$qs = explode("^", $rateindex);

		if (!$qs[0] || USER == FALSE || $qs[3] > 10 || $qs[3] < 1)
		{
				
			if($ajax == false)
			{
				header("location:".e_BASE."index.php");
				exit;	
			}
			else
			{
				return "Error: ".print_a($qs,true);	
			}	
			
		}

		$table = $tp -> toDB($qs[0], true);
		$itemid = intval($qs[1]);
		$rate = intval($qs[3]);

		//rating is now stored as userid-rating (to retain individual users rating)
		//$sep = "^";
		$sep = chr(1); // problematic - invisible in phpmyadmin. 
		$voter = USERID.$sep.intval($qs[3]);

		if ($sql->db_Select("rate", "*", "rate_table='{$table}' AND rate_itemid='{$itemid}' "))
		{
		
			$row = $sql -> db_Fetch();
			$rate_voters = $row['rate_voters'].".".$voter.".";
			$new_votes = $row['rate_votes'] + 1;
			$new_rating = $row['rate_rating'] + $rate;
			
			$stat = ($new_rating /$new_votes)/2;
			$statR = round($stat,1);
			
			if(strpos($row['rate_voters'], ".".$voter.".") == true || strpos($row['rate_voters'], ".".USERID.".") == true)
			{
				
				return "You already voted|".$this->renderVotes($new_votes,$statR); // " newvotes = ".($statR). " =".$new_votes;
			}
			
			
			if($sql->db_Update("rate", "rate_votes= ".$new_votes.", rate_rating='{$new_rating}', rate_voters='{$rate_voters}' WHERE rate_id='{$row['rate_id']}' "))
			{
				return RATELAN_3."|".$this->renderVotes($new_votes,$statR);	// Thank you for your vote. 
			}
			else
			{
				return "Error";	
			}
				
		}
		else
		{
			
			$insert = array(
			//	"rate_id"	=> 0,
				"rate_table"	=> $table,
				"rate_itemid"	=> $itemid,
				"rate_rating"	=> $rate,
				"rate_votes"	=> 1,
				"rate_voters"	=> ".".$voter.".",
				"rate_up"		=> 0,
				"rate_down"		=> 0
			);
			
			
			if($sql->db_Insert("rate", $insert))
		//	if($sql->db_Insert("rate", " 0, '$table', '$itemid', '$rate', '1', '.".$voter.".' "))
			{
				$stat = ($rate /1)/2;
				$statR = round($stat,1);
				return RATELAN_3."|".$this->renderVotes(1,$statR);	;	// Thank you for your vote. 	
			}
			elseif(getperms('0'))
			{
				return "Rating Failed ";	
			}
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
					$rate .= "<img src='".e_IMAGE_ABS."rate/box.png' alt='' style='height:8px; vertical-align:middle' />";
				}
				if($ratearray[1] < 10){
					for($c=9; $c>=$ratearray[1]; $c--){
						$rate .= "<img src='".e_IMAGE_ABS."rate/empty.png' alt='' style='height:8px; vertical-align:middle' />";
					}
				}
				$rate .= "<img src='".e_IMAGE_ABS."rate/boxend.png' alt='' style='height:8px; vertical-align:middle' />";
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

	function delete_ratings($table, $id)
	{
		global $tp, $sql;
		$table = $tp->toDB($table, true);
		$id = intval($id);
		return $sql -> db_Delete("rate", "rate_itemid='{$id}' AND rate_table='{$table}'");
	}
}
