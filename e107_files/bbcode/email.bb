if ($parm) {
	return "<a href='mailto:".$tp -> toAttribute($parm)."'>".$code_text."</a>";
} else {
	return "<a href='mailto:".$tp -> toAttribute($code_text)."'>".$code_text."</a>";
}