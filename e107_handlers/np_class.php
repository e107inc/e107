<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/np_class.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
@include(e_LANGUAGEDIR.e_LANGUAGE."/lan_np.php");
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
class nextprev{
	function nextprev($url, $from, $view, $total, $td, $qs=""){
		/*
		# Next previous pages
		# - parameter #1:		string $url, refer url
		# - parameter #2:		int $from, start figure
		# - parameter #3:		int $view, items per page
		# - parameter #4:		int $total, total items
		# - parameter #5:		string $td, comfort text
		# - parameter #6:		string $qs, QUERY_STRIING, default null
		# - return				null
		# - scope					public
		*/
  if($total == 0){
   return;
  }
  $ns = new e107table;
  echo "<table style=\"width:100%\">
  <tr>";
  if($from > 1){
   $s = $from-$view;
   echo "<td style=\"width:33%\" class=\"nextprev\">";
   if($qs != ""){
		$text = "<div style=\"text-align:left\"><span class=\"smalltext\"><a href=\"".$url."?".$s.".".$qs."\">".NP_1."</a></span></div>";
   }else{
		$text = "<div style=\"text-align:left\"><span class=\"smalltext\"><a href=\"".$url."?".$s."\">".NP_1."</a></span></div>";
   }
   echo $text;
  }else{
   echo "<td style=\"width:33%\">&nbsp;";
  }
 
  echo "</td>\n<td style=\"width:34%\" class=\"nextprev\">";
  $start = $from+1;
  $finish = $from+$view;
  if($finish>$total){
   $finish = $total;
  }
  $text = "<div style=\"text-align:center\"><span class=\"smalltext\">$td $start - $finish of $total</span></div>";
  echo $text;
 
  $s = $from+$view;
  if($s < $total){
   echo "</td><td style=\"width:33%\" class=\"nextprev\">";
   if($qs != ""){
		$text = "<div style=\"text-align:right\"><span class=\"smalltext\"><a href=\"".$url."?".$s.".".$qs."\">".NP_2."</a></span></div></td>";
   }else{
		$text = "<div style=\"text-align:right\"><span class=\"smalltext\"><a href=\"".$url."?".$s."\">".NP_2."</a></span></div></td>";
   }
   echo $text;
  }else{
   echo "</td><td style=\"width:33%\">&nbsp;</td>";
  }
  echo "</tr>\n</table>";
 }
}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
?>