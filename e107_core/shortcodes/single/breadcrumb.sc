//<?php

global $$parm;
$bc = $$parm;
$flist = explode(",", $bc['fieldlist']);
$ret = "";
foreach($flist as $f)
{
	if($bc[$f]['value'])
	{
		$ret .= $bc[$f]['value'];
		$opt[] = $bc[$f]['value'];
		
		
		if($bc[$f]['sep'])
		{
			$ret .= $bc[$f]['sep'];
		}
	}
}


if($bc['style']=='bootstrap')
{
	return '<ul class="breadcrumb">
	
    <li><a href="#">Home</a> <span class="divider">/</span></li>
    <li><a href="#">Library</a> <span class="divider">/</span></li>
    <li class="active">Data</li>
    </ul>';		
}


return $ret;