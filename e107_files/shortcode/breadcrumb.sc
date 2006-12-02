global $$parm;
$bc = $$parm;
$flist = explode(",", $bc['fieldlist']);
$ret = "";
foreach($flist as $f)
{
	if($bc[$f]['value'])
	{
		$ret .= $bc[$f]['value'];
		if($bc[$f]['sep'])
		{
			$ret .= $bc[$f]['sep'];
		}
	}
}

return $ret;