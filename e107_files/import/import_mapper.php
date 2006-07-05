<?php
/*  Database import function for E107 website system
Use: See separate documentation.

11.06.06 - zeronull handling added
*/

function createQuery($convertArray, $dataArray, $table, $maptable = null)
{
	global $tp;

	$columns = "(";
	$values = "(";


	foreach($convertArray as $convert)
	{   // Possible types: STRING, INT
	  if (array_key_exists("value", $convert))
	  {
	    $newvalue = $convert['value'];
	  }
	  elseif (array_key_exists($convert['srcdata'],$dataArray))
	  {   // Real value read from database here - need to check it
		if($convert['type'] == "STRING")
		  {
		    $newvalue = $tp -> toDB($dataArray[$convert['srcdata']]);
//		    $newvalue = $dataArray[$convert['srcdata']];				// USE IN PLACE OF PREVIOUS LINE FOR STANDALONE TEST
			if (array_key_exists('sproc',$convert))
			{
			  if (strpos($convert['sproc'],"usebb") !== FALSE) $newvalue = proc_bb($newvalue,$convert['sproc'], $maptable);
			  if (strpos($convert['sproc'],"stripbb") !== FALSE) $newvalue = preg_replace("#\[.*\]#", "",$newvalue);
			}
		  }
		  else
		  if ($convert['type'] == "INT")
		  {
		    $newvalue = intval($dataArray[$convert['srcdata']]); 	// intval added 25.05.06
			if (($newvalue == 0) && ((array_key_exists('sproc',$convert)) && (strpos($convert['sproc'],"zeronull") !== FALSE))) $newvalue = '';
		  }
		  else
		    echo "Invalid field type: ".$convert['type'];
	  }
	  else
	  {    // blank (undefined) value
		if (array_key_exists('default', $convert)) 
		{
		  $newvalue = $convert['default'];
		}
		else
		{
	      if (($convert['type'] == "INT") && ((array_key_exists('sproc',$convert)) && (strpos($convert['sproc'],"zeronull") === FALSE)))
		    $newvalue = "0";     // Should help mySQL5
		  else
		    $newvalue = '';
		}
	  }
	  
	  $columns .= $convert['e107'].",";
	  $values .=  "'".$newvalue."',";
	}

// Knock off last comma of each line
	$columns = substr($columns, 0, -1).")";
	$values = substr($values, 0, -1).")";

	return "INSERT INTO $table $columns VALUES $values";

}

// Process all bbcodes in the passed value; return the processed string.
// Works recursively
// Start by assembling matched pairs. Then map and otherwise process as required.
// Divide the value into five bits:
//      Preamble - up to the identified bbcode (won't contain bbcode)
//		BBCode start code
//		Inner - text between the two bbcodes (may contain another bbcode)
//		BBCode end code
//		Trailer - remaining unprocessed text (may contain more bbcodes)
// (Note: preg_split might seem obvious, but doesn't pick out the actual codes
// Tested with various strings (see testdata.txt; also mapping table to see what is going on.

function proc_bb($value, $options = "", $maptable = null)
{
  $nextchar = 0;
  $loopcount = 0;
// echo "<br />starting match<br />";
 
  while ($nextchar < strlen($value)) :
    $firstbit = '';
    $middlebit = '';
    $lastbit = '';
    $loopcount++;
	if ($loopcount > 10) return 'Max depth exceeded';
    unset($bbword);
    $firstcode = strpos($value,'[',$nextchar);
    if ($firstcode === FALSE) return $value;   	// Done if no square brackets
    $firstend = strpos($value,']',$firstcode);
    if ($firstend === FALSE) return $value;		// Done if no closing bracket
    $bbword = substr($value,$firstcode+1,$firstend - $firstcode - 1);	// May need to process this more if parameter follows
	$bbparam = '';
	$temp = strpos($bbword,'=');
	if ($temp !== FALSE)
	{
	  $bbparam = substr($bbword,$temp);
	  $bbword  = substr($bbword,0,-strlen($bbparam));
	}
//	echo $bbword."<<||>>".$bbparam;
    if (($bbword) && ($bbword == trim($bbword)))
    {
      $laststart = strpos($value,'[/'.$bbword,$firstend);    // Find matching end
	  $lastend   = strpos($value,']',$laststart);
	  if (($laststart === FALSE) || ($lastend === FALSE))
	  {   //  No matching end character
	    $nextchar = $firstend;	// Just move scan pointer along 
//		echo " - no match<br />";
	  }
	  else
	  {  // Got a valid bbcode pair here
	    $firstbit = '';
	    if ($firstcode > 0) $firstbit = substr($value,0,$firstcode);
	    $middlebit = substr($value,$firstend+1,$laststart - $firstend-1);
	    $lastbit = substr($value,$lastend+1,strlen($value) - $lastend);
//	    echo " - match||".$firstbit."||".$middlebit."||".$lastbit."<br />";
	    // Process bbcodes here
		if (strpos($options,'bblower') !== FALSE) $bbword = strtolower($bbword);
		if ((strpos($options,'phpbb') !== FALSE) && (strpos($bbword,':') !== FALSE)) $bbword = substr($bbword,0,strpos($bbword,':'));
		if ($maptable)
		{   // Do mapping
		  if (array_key_exists($bbword,$maptable)) $bbword = $maptable[$bbword];
		}
	    $bbbegin = '['.$bbword.$bbparam.']';
	    $bbend   = '[/'.$bbword.']';
	    return $firstbit.$bbbegin.proc_bb($middlebit,$options,$maptable).$bbend.proc_bb($lastbit,$options,$maptable);
	  }
    }
	else
	{
	  $nextchar = $firstend+1;
	}
//  echo "  -->".$firstcode.", ".$firstend.", ".$laststart.", ".$lastend."<br />";
//  echo "<br />";
  endwhile;
  
}




?>