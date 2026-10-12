//<?
$class = e107::getBB()->getClass('url');
global $pref;

$tp       = e107::getParser();
$parm     = trim($parm);
$external = ($pref['links_new_window'] || strpos($parm, 'external') === 0) ? ' rel="external"' : '';

if ($parm && $parm != 'external' && strpos($parm, ' ') === FALSE)
{
	$url = preg_replace('#^external.#is', '', $parm);
}
else
{
	$url = $code_text;
}

$text = $code_text;
$href = $tp->toBBCodeUrlAttribute($url);
if ($url !== '' && $href === '') return $text;

return '<a href="'.$href.'" class="bbcode '.$class.'"'.$external.'>'.$text.'</a>';
