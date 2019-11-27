<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2010 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * $Id$
*/

if (!defined('LOGSTATS_INIT')) { exit; }

$logIfile = e_LOG."logi_{$date}.php";
$i_handle = fopen($logIfile, 'r+');
if($i_handle && flock( $i_handle, LOCK_EX ) ) 
{
	$log_file_contents = '';
	while (!feof($i_handle))
	{  // Assemble a string of data
		$log_file_contents.= fgets($i_handle,1000);
	}
	$log_file_contents = str_replace(array('<'.'?php','?'.'>'),'',$log_file_contents);
	if (eval($log_file_contents) === FALSE) echo "error in log file contents<br /><br /><br /><br />";
}
else
{
	echo "Couldn't log data<br /><br /><br /><br />";
	exit;
}

$browser = getBrowser($agent);
$os = getOs($agent);

if($screenstats && $screenstats != "@") 
{
	if(array_key_exists($screenstats, $screenInfo)) 
	{
		$screenInfo[$screenstats] ++;
	} 
	else 
	{
		$screenInfo[$screenstats] = 1;
	}
}

if(array_key_exists($browser, $browserInfo)) 
{
	$browserInfo[$browser] ++;
} 
else 
{
	$browserInfo[$browser] = 1;
}

if(array_key_exists($os, $osInfo)) 
{
	$osInfo[$os] ++;
} 
else 
{
	$osInfo[$os] =1;
}

/* referer data ... */
if($ref && !strstr($ref, $_SERVER['HTTP_HOST'])) 
{
	if(preg_match("#http://(.*?)($|/)#is", $ref, $match)) 
	{
		$refdom = $match[0];
		if(array_key_exists($refdom, $refInfo)) 
		{
			$refInfo[$refdom]['ttl'] ++;
		} 
		else 
		{
			$refInfo[$refdom] = array('url' => $ref, 'ttl' => 1);
		}
	}
}

/* is the referal from Google? If so get search string ... */
if(preg_match("#q=(.*?)($|&)#is", $oldref, $match)) 
{
	$schstr = trim($match[1]);
	$schstr = htmlentities(urldecode($schstr));
	if(array_key_exists($schstr, $searchInfo) && $schstr) 
	{
		$searchInfo[$schstr] ++;
	} 
	else 
	{
		$searchInfo[$schstr] = 1;
	}
}

if ($tmp = gethostbyaddr(getenv('REMOTE_ADDR'))) 
{
	$host = trim(strtolower(substr($tmp, strrpos($tmp, ".")+1)));
	if(!is_numeric($host) && !strstr($host, "calhost")) 
	{
		if(array_key_exists($host, $domainInfo)) 
		{
			$domainInfo[$host] ++;
		} 
		else 
		{
			$domainInfo[$host] =1;
		}
	}
}

/* last 20 visitors */
if(count($visitInfo) >= 20) 
{
	$length = 20;
	$offset = count($visitInfo)-$length;
	$visitInfo = array_slice($visitInfo, $offset, $length);
}

$visitInfo[$tmp] = array(
	'host'    => trim($tmp),
	'date'    => time(),
	'os'      => trim($os),
	'browser' => trim($browser),
	'screen'  => trim($screenstats),
	'referer' => substr(trim($ref), 0, 255),
);

$data = "<?php

/* e107 website system: Log info file: ".date("z:Y", time())." */

";
$data .= '$domainInfo = '.var_export($domainInfo, true).";\n\n";
$data .= '$screenInfo = '.var_export($screenInfo, true).";\n\n";
$data .= '$browserInfo = '.var_export($browserInfo, true).";\n\n";
$data .= '$osInfo = '.var_export($osInfo, true).";\n\n";
$data .= '$refInfo = '.var_export($refInfo, true).";\n\n";
$data .= '$searchInfo = '.var_export($searchInfo, true).";\n\n";
$data .= '$visitInfo = '.var_export($visitInfo, true).";\n\n";
$data .= '?>';


if ($i_handle)
{
	ftruncate($i_handle, 0);
	fseek( $i_handle, 0 );
	fwrite($i_handle, $data);
	fclose($i_handle);
}


function getBrowser($agent) 
{
	//
	// All "root" browsers must come at the end of the list, unfortunately.
	// Otherwise, browsers based on them will never be seen.
	//(But #1997)
	// http://www.zytrax.com/tech/web/browser_ids.htm
	$browsers = array(
		"netcaptor"    => array('name' => 'Netcaptor',         'rule' => 'netcaptor[ /]([0-9.]{1,10})'),
		"opera"        => array('name' => 'Opera Mini',        'rule' => 'Opera[ /]([0-9.]{1,10})(.*)Opera Mini'),
		"opera1"       => array('name' => 'Opera Mobile',      'rule' => 'Opera[ /]([0-9.]{1,10})(.*)Opera Mobi'),
		"opera2"       => array('name' => 'Opera',             'rule' => 'opera[ /]([0-9.]{1,10})'),
		"chrome"       => array('name' => 'Chrome',            'rule' => 'Chrome[ /]([0-9.+]{1,10})'),
		"nokia"        => array('name' => 'Nokia Browser',     'rule' => 'Nokia([^/]+)/([^ SP]+)'),
		"nokia1"       => array('name' => 'Nokia Browser',     'rule' => 'Series60|S60/([0-9.]{1,10})'),
		"nokia2"       => array('name' => 'Nokia Browser',     'rule' => 'Mozilla(.*)SymbianOS(.*)AppleWebKit'), // catch it or it'll become a safari hit!
		"aol"          => array('name' => 'AOL',               'rule' => 'aol[ /\-]([0-9.]{1,10})'),
		"aol2"         => array('name' => 'AOL',               'rule' => 'aol[ /\-]?browser'),
		"mosaic"       => array('name' => 'Mosaic',            'rule' => 'mosaic[ /]([0-9.]{1,10})'),
		"k-meleon"     => array('name' => 'K-Meleon',          'rule' => 'K-Meleon[ /]([0-9.]{1,10})'),
		"konqueror"    => array('name' => 'Konqueror',         'rule' => 'konqueror/([0-9.]{1,10})'),
		"avantbrowser" => array('name' => 'Avant Browser',     'rule' => 'Avant[ ]?Browser'),
		"avantgo"      => array('name' => 'AvantGo',           'rule' => 'AvantGo[ /]([0-9.]{1,10})'),
		"proxomitron"  => array('name' => 'Proxomitron',       'rule' => 'Space[ ]?Bison/[0-9.]{1,10}'),
		"lynx"         => array('name' => 'Lynx',              'rule' => 'lynx/([0-9a-z.]{1,10})'),
		"links"        => array('name' => 'Links',             'rule' => 'Links[ /]\(([0-9.]{1,10})'),
		"galeon"       => array('name' => 'Galeon',            'rule' => 'galeon/([0-9.]{1,10})'),
		"abrowse"      => array('name' => 'ABrowse',           'rule' => 'abrowse/([0-9.]{1,10})'),
		"amaya"        => array('name' => 'Amaya',             'rule' => 'amaya/([0-9.]{1,10})'),
		"ant"          => array('name' => 'ANTFresco',         'rule' => 'ANTFresco[ /]([0-9.]{1,10})'),
		"aweb"         => array('name' => 'Aweb',              'rule' => 'Aweb[/ ]([0-9.]{1,10})'),
		"beonex"       => array('name' => 'Beonex',            'rule' => 'beonex/([0-9.]{1,10})'),
		"blazer"       => array('name' => 'Blazer',            'rule' => 'Blazer[/ ]([0-9.]{1,10})'),
		"camino"       => array('name' => 'Camino',            'rule' => 'camino/([0-9.+]{1,10})'),
		"chimera"      => array('name' => 'Chimera',           'rule' => 'chimera/([0-9.+]{1,10})'),
		"columbus"     => array('name' => 'Columbus',          'rule' => 'columbus[ /]([0-9.]{1,10})'),
		"crazybrowser" => array('name' => 'Crazy Browser',     'rule' => 'Crazy Browser[ /]([0-9.]{1,10})'),
		"curl"         => array('name' => 'Curl',              'rule' => 'curl[ /]([0-9.]{1,10})'),
		"deepnet"      => array('name' => 'Deepnet Explorer',  'rule' => 'Deepnet Explorer[/ ]([0-9.]{1,10})'),
		"dillo"        => array('name' => 'Dillo',             'rule' => 'dillo/([0-9.]{1,10})'),
		"doris"        => array('name' => 'Doris',             'rule' => 'Doris/([0-9.]{1,10})'),
		"elinks"       => array('name' => 'ELinks',            'rule' => 'ELinks[ /][(]*([0-9.]{1,10})'),
		"epiphany"     => array('name' => 'Epiphany',          'rule' => 'Epiphany/([0-9.]{1,10})'),
		"ibrowse"      => array('name' => 'IBrowse',           'rule' => 'ibrowse[ /]([0-9.]{1,10})'),
		"icab"         => array('name' => 'iCab',              'rule' => 'icab[/ ]([0-9.]{1,10})'),
		"ice"          => array('name' => 'ICEbrowser',        'rule' => 'ICEbrowser/v?([0-9._]{1,10})'),
		"isilox"       => array('name' => 'iSiloX',            'rule' => 'iSilox/([0-9.]{1,10})'),
		"lotus"        => array('name' => 'Lotus Notes',       'rule' => 'Lotus[ -]?Notes[ /]([0-9.]{1,10})'),
		"lunascape"    => array('name' => 'Lunascape',         'rule' => 'Lunascape[ /]([0-9.]{1,10})'),
		"maxthon"      => array('name' => 'Maxthon',           'rule' => ' Maxthon[);]'),
		"mbrowser"     => array('name' => 'mBrowser',          'rule' => 'mBrowser[ /]([0-9.]{1,10})'),
		"multibrowser" => array('name' => 'Multi-Browser',     'rule' => 'Multi-Browser[ /]([0-9.]{1,10})'),
		"nautilus"     => array('name' => 'Nautilus',          'rule' => '(gnome[ -]?vfs|nautilus)/([0-9.]{1,10})'),
		"netfront"     => array('name' => 'NetFront',          'rule' => 'NetFront[ /]([0-9.]{1,10})$'),
		"netpositive"  => array('name' => 'NetPositive',       'rule' => 'netpositive[ /]([0-9.]{1,10})'),
		"omniweb"      => array('name' => 'OmniWeb',           'rule' => 'omniweb/[ a-z]?([0-9.]{1,10})$'),
		"oregano"      => array('name' => 'Oregano',           'rule' => 'Oregano[0-9]?[ /]([0-9.]{1,10})$'),
		"phaseout"     => array('name' => 'PhaseOut',          'rule' => 'www.phaseout.net'),
		"plink"        => array('name' => 'PLink',             'rule' => 'PLink[ /]([0-9a-z.]{1,10})'),
		"phoenix"      => array('name' => 'Phoenix',           'rule' => 'Phoenix/([0-9.+]{1,10})'),
		"proxomitron"  => array('name' => 'Proxomitron',       'rule' => 'Space[ ]?Bison/[0-9.]{1,10}'),
		"shiira"       => array('name' => 'Shiira',            'rule' => 'Shiira/([0-9.]{1,10})'),
		"sleipnir"     => array('name' => 'Sleipnir',          'rule' => 'Sleipnir( Version)?[ /]([0-9.]{1,10})'),
		"slimbrowser"  => array('name' => 'SlimBrowser',       'rule' => 'Slimbrowser'),
		"staroffice"   => array('name' => 'StarOffice',        'rule' => 'staroffice[ /]([0-9.]{1,10})'),
		"sunrise"      => array('name' => 'Sunrise',           'rule' => 'SunriseBrowser[ /]([0-9.]{1,10})'),
		"voyager"      => array('name' => 'Voyager',           'rule' => 'voyager[ /]([0-9.]{1,10})'),
		"w3m"          => array('name' => 'w3m',               'rule' => 'w3m/([0-9.]{1,10})'),
		"webtv"        => array('name' => 'Webtv',             'rule' => 'webtv[ /]([0-9.]{1,10})'),
		"xiino"        => array('name' => 'Xiino',             'rule' => '^Xiino[ /]([0-9a-z.]{1,10})'),
		"explorer"     => array('name' => 'Internet Explorer', 'rule' => '\(compatible; MSIE[ /]([0-9.]{1,10})'),
		"safari"       => array('name' => 'Safari',            'rule' => 'safari/([0-9.]{1,10})'),
		"firefox"      => array('name' => 'Firefox',           'rule' => 'Firefox/([0-9.+]{1,10})'),
		"netscape"     => array('name' => 'Netscape',          'rule' => 'netscape[0-9]?/([0-9.]{1,10})'),
		"netscape2"    => array('name' => 'Netscape',          'rule' => '^mozilla/([0-4]\.[0-9.]{1,10})'),
		"mozilla"      => array('name' => 'Mozilla',           'rule' => '^mozilla/[5-9]\.[0-9.]{1,10}.+rv:([0-9a-z.+]{1,10})'),
		"mozilla2"     => array('name' => 'Mozilla',           'rule' => '^mozilla/([5-9]\.[0-9a-z.]{1,10})'),
		"firebird"     => array('name' => 'Firebird',          'rule' => 'Firebird/([0-9.+]{1,10})'),
	);
	$browser = "";
	foreach($browsers as $key => $info) 
	{
		if (preg_match("#".$info['rule']."#i", $agent, $results)) 
		{
			switch ($key) 
			{
				case 'nokia':
				case 'nokia1':
				case 'nokia2':
					if(strpos(strtolower($agent), 'series60') !== false || strpos($agent, 'S60') !== false ) 
					{
						$info['name'] = 'Nokia S60 OSS Browser';
					}
					return ($info['name'].(isset($results[2]) && $results[2] ? ' v'.$results[2] : ''));
				break;
				
				default:
					return ($info['name'].(isset($results[1]) && $results[1] ? ' v'.$results[1] : ''));
				break;
			}
		}
	}
	return ('Unknown');
}

function getOs($agent) 
{
	// http://www.zytrax.com/tech/web/browser_ids.htm
	$os = array(
		// mobile come first - latest rules could break the check
		"android"     => array('name' => 'Android',   	 'rule' => 'Android\s([0-9.]{1,10})'),
		"symbian"     => array('name' => 'Symbian',   	 'rule' => 'symbianOS[ /]?([0-9.]{1,10})'),
		"symbian1"    => array('name' => 'Symbian',   	 'rule' => 'series60[ /]'),
		"symbian2"    => array('name' => 'Symbian',   	 'rule' => 'Symbian OS Series'),
		"windows7" 	  => array('name' => 'Windows 7', 	 'rule' => 'wi(n|ndows)[ \-]?nt[ /]?6\.1'),
		"windowsvista" => array('name' => 'Windows Vista', 'rule' => 'wi(n|ndows)[ \-]?nt[ /]?6\.0'),
		"windows2003" => array('name' => 'Windows 2003', 'rule' => 'wi(n|ndows)[ \-]?(2003|nt[ /]?5\.2)'),
		"windowsxp"   => array('name' => 'Windows XP',   'rule' => 'Windows XP'),
		"windowsxp2"  => array('name' => 'Windows XP',   'rule' => 'wi(n|ndows)[ \-]?nt[ /]?5\.1'),
		"windows2k"   => array('name' => 'Windows 2000', 'rule' => 'wi(n|ndows)[ \-]?(2000|nt[ /]?5\.0)'),
		"windows95"   => array('name' => 'Windows 95',   'rule' => 'wi(n|ndows)[ \-]?95'),
		"windowsce"   => array('name' => 'Windows CE',   'rule' => 'wi(n|ndows)[ \-]?ce'),
		"windowsme"   => array('name' => 'Windows ME',   'rule' => 'win 9x 4\.90'),
		"windowsme2"  => array('name' => 'Windows ME',   'rule' => 'wi(n|ndows)[ \-]?me'),
		"windowsnt"   => array('name' => 'Windows NT',   'rule' => 'wi(n|ndows)[ \-]?nt[ /]?([0-4][0-9.]{1,10})'),
		"windowsnt2"  => array('name' => 'Windows NT',   'rule' => 'wi(n|ndows)[ \-]?nt'),
		"windows98"   => array('name' => 'Windows 98',   'rule' => 'wi(n|ndows)[ \-]?98'),
		"windows"     => array('name' => 'Windows',      'rule' => 'wi(n|n32|ndows)'),
		"linux"       => array('name' => 'Linux',        'rule' => 'mdk for ([0-9.]{1,10})'),
		"linux2"      => array('name' => 'Linux',        'rule' => 'linux[ /\-]([a-z0-9.]{1,10})'),
		"linux3"      => array('name' => 'Linux',        'rule' => 'linux'),
		"macosx"      => array('name' => 'MacOS X',      'rule' => 'Mac[ ]?OS[ ]?X'),
		"macppc"      => array('name' => 'MacOS PPC',    'rule' => 'Mac(_Power|intosh.+P)PC'),
		"mac"         => array('name' => 'MacOS',        'rule' => 'mac[^hk]'),
		"amiga"       => array('name' => 'Amiga',        'rule' => 'Amiga[ ]?OS[ /]([0-9.]{1,10})'),
		"beos"        => array('name' => 'BeOS',         'rule' => 'beos[ a-z]*([0-9.]{1,10})'),
		"freebsd"     => array('name' => 'FreeBSD',      'rule' => 'free[ \-]?bsd[ /]([a-z0-9.]{1,10})'),
		"freebsd2"    => array('name' => 'FreeBSD',      'rule' => 'free[ \-]?bsd'),
		"irix"        => array('name' => 'Irix',         'rule' => 'irix[0-9]*[ /]([0-9.]{1,10})'),
		"netbsd"      => array('name' => 'NetBSD',       'rule' => 'net[ \-]?bsd[ /]([a-z0-9.]{1,10})'),
		"netbsd2"     => array('name' => 'NetBSD',       'rule' => 'net[ \-]?bsd'),
		"os2"         => array('name' => 'OS/2 Warp',    'rule' => 'warp[ /]?([0-9.]{1,10})'),
		"os22"        => array('name' => 'OS/2 Warp',    'rule' => 'os[ /]?2'),
		"openbsd"     => array('name' => 'OpenBSD',      'rule' => 'open[ \-]?bsd[ /]([a-z0-9.]{1,10})'),
		"openbsd2"    => array('name' => 'OpenBSD',      'rule' => 'open[ \-]?bsd'),
		"palm"        => array('name' => 'PalmOS',       'rule' => 'Palm[ \-]?(Source|OS)[ /]?([0-9.]{1,10})'),
		"palm2"       => array('name' => 'PalmOS',       'rule' => 'Palm[ \-]?(Source|OS)')
	);
	foreach($os as $key => $info) 
	{
		if (preg_match("#".$info['rule']."#i", $agent, $results)) 
		{
			if(strstr($key, "win")) 
			{
				return ($info['name']);
			} 
			else 
			{
				return ($info['name']." ".$results[1]);
			}
		}
	}
	return ('Unspecified');
}
