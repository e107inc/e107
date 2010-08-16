/* $Id$ */
// [youtube=tiny|small|medium|big|huge or width,height|norel&border&privacy&nofull]ID[/youtube]
// Youtube ID is the only required data!
// TODO - more: http://code.google.com/apis/youtube/player_parameters.html
// TODO - use swfobject JS - XHTML validation
if(empty($code_text)) return '';

$parms = explode('|', $parm, 2);
parse_str(varset($parms[1], ''), $params);

if(empty($parms[0])) $parms[0] = 'small';

switch ($parms[0]) 
{
	case 'tiny':
		$params['w'] = 200;
		$params['h'] = 180;
	break;
	
	case 'small':
		$params['w'] = 445;
		$params['h'] = 364;
	break;
	
	case 'medium':
		$params['w'] = 500;
		$params['h'] = 405;
	break;
	
	case 'big':
		$params['w'] = 660;
		$params['h'] = 525;
	break;
	
	case 'huge':
		$params['w'] = 980;
		$params['h'] = 765;
	break;
	
	default:
		$dim = explode(',', $parms[0], 2);
		$params['w'] = (integer) varset($dim[0], 445);
		if($params['w'] > 980 || $params['w'] < 200) $params['w'] = 445;
		
		$params['h'] = (integer) varset($dim[1], 364);
		if($params['h'] > 765 || $params['h'] < 180) $params['h'] = 364;
	break;
}

$yID = preg_replace('/[^0-9a-z]/i', '', $code_text);

$url = isset($params['privacy']) ? 'http://www.youtube-nocookie.com/v/' : 'http://www.youtube.com/v/';
$url .= $yID.'?fs=1';

if(isset($params['border'])) $url .= $yID.'&amp;border=1';
if(isset($params['norel'])) $url .= $yID.'&amp;rel=0';

$fscr = 'true';
if(isset($params['nofull'])) $fscr = 'false';

$ret = ' 
<object width="'.$params['w'].'" height="'.$params['h'].'">
	<param name="movie" value="'.$url.'"></param>
	<param name="allowFullScreen" value="'.$fscr.'"></param>
	<param name="allowscriptaccess" value="always"></param>
	<param name="wmode" value="transparent"></param>
	<embed src="'.$url.'" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="'.$fscr.'" wmode="transparent" width="'.$params['w'].'" height="'.$params['h'].'"></embed>
</object>
';

return $ret;