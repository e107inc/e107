require_once(e_PLUGIN."tagwords/tagwords_class.php");
$tag = new tagwords();

global $parm;
$tmp = explode("^", $parm);
return $tag->getRecords($tmp[0],$tmp[1], TRUE);
