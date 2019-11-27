//<?php

global $$parm;
$bc = $$parm;
$flist = explode(",", $bc['fieldlist']);
$ret = "";

$opt = array();

foreach($flist as $f)
{
	if($bc[$f]['value'])
	{
		$ret .= $bc[$f]['value'];
	//	$opt[] = $bc[$f]['value'];
		
		
		
		if(isset($bc[$f]['sep']))
		{
			$ret .= $bc[$f]['sep'];
		}
	}
}


if(deftrue('BOOTSTRAP'))
{
	$text = '<ul class="breadcrumb">
	<li>';
	
	foreach($flist as $f)
	{
		if(isset($bc[$f]['value']))
		{
			$opt[] = $bc[$f]['value'];
		}	
		
	}
	
	$text .= implode("<span class='divider'>/</span></li><li>",$opt); 
	
	$text .= "</li></ul>";
	
	return $text;
	
}


return $ret;