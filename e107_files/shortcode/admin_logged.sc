if(ADMIN)
{
	$str = str_replace(".", "", ADMINPERMS);
	if(ADMINPERMS == "0"){
		echo ADLAN_48.": ".ADMINNAME." (".ADLAN_49.")";
	}
	else
	{
		echo ADLAN_48.": ".ADMINNAME." (".ADLAN_50.":  ".$str.")";
	}
}
else
{
	echo ADLAN_51." ...";
}
