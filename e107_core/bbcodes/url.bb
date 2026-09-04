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

if ($url !== '' && $tp->toUrlAttribute($url) === '') return $code_text;

return '<a href="'.$tp->toAttribute($url).'" class="bbcode '.$class.'"'.$external.'>'.$code_text.'</a>';
