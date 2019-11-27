<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */


if (!defined('e107_INIT'))
{
	require_once("../../class2.php");
}

if (!e107::isInstalled('log')) 
{
	e107::redirect();
	exit;
}

e107::includeLan(e_PLUGIN.'log/languages/'.e_LANGUAGE.'.php');

$bar = (file_exists(THEME.'images/bar.png') ? THEME_ABS.'images/bar.png' : e_IMAGE_ABS.'generic/bar.png');
$mes = e107::getMessage();
 

e107::css('inline', "
/* Site Stats */
.b { background-image: url('".$bar."'); border: 1px solid #999; height: 10px; font-size: 0px }
");

require_once(HEADERF);

if(!check_class(e107::getPref('statUserclass'))) 
{
	$mes->addError(ADSTAT_L4); 
	$ns->tablerender(ADSTAT_L6, $mes->render());
	require_once(FOOTERF);
	exit;
}


if (!e107::getPref('statActivate')) 
{
	$text = (ADMIN ? "<div style='text-align:center'>".ADSTAT_L41."</div>" : "<div style='text-align:center'>".ADSTAT_L5."</div>");
	$ns->tablerender(ADSTAT_L6, $text);
	require_once(FOOTERF);
	exit;
}


$qs = explode('.', e_QUERY, 3);
$action = varset($qs[0],1);
$sec_action = varset($qs[1],FALSE);
$order = varset($qs[1],0);				// Sort order

$toremove = varset($qs[2],'');
$order = intval($order);

$stat = new siteStats($order);

if($stat->error) 
{
	$ns->tablerender(ADSTAT_L6, $stat->error);
	require_once(FOOTERF);
	exit;
}

/* stats displayed will depend on the query string. For example, ?1.2.4 will render today's stats, all time stats and browser stats */
/*
1: today's stats
2: all time total and unique
3: browsers
4: operating systems
5: domains
6: screen resolution/colour depth
7: referers
8: search engine strings
9: Recent visitors
10: Daily visitors
11: Monthly visitors
12: Error pages today
13: Error pages all-time
14: Browsers consolidated
15: OSs consolidated
*/

function display_pars($rec_pars, $disp_pars = '*')
{
	switch ($rec_pars)
	{
		case 1 : return array(1);
		case 2 : if (e107::getPref('statPrevMonth')) return array(2,3,1); else return array(2,1);
		case 3 : return array(2,3,1);
		default : return array();
	}
}


$text = '';
if ((ADMIN == TRUE) && ($sec_action == 'rem'))
{
	$toremove = rawurldecode($toremove);
	if ($stat -> remove_entry($toremove))
	{
		$text .= "<div style='text-align: center; font-weight: bold'>".ADSTAT_L45.$toremove."<br />".ADSTAT_L46."</div>";
	}
	else
	{
		$text .= "<div style='text-align: center; font-weight: bold'>".ADSTAT_L47.$toremove."</div>";
	}
}


$action = intval($action);

switch($action) 
{
	case 1:
		$text .= $stat -> renderTodaysVisits(FALSE);
		break;
	case 2:
		$text .= $stat -> renderAlltimeVisits($action, FALSE);
		break;
	case 12:
		if (ADMIN == TRUE)
		{
			$text .= $stat -> renderTodaysVisits(TRUE);
		}
		break;
	case 13:
		if (ADMIN == TRUE)
		{
			$text .= $stat -> renderAlltimeVisits($action, TRUE);
		}
		break;
	case 3 :		// 'Normal' render
	case 14 :		// 'Consolidated' render
		if(e107::getPref('statBrowser')) 
		{
			$text .= $stat -> renderBrowsers(display_pars(e107::getPref('statBrowser')), $action==3);
		} 
		else 
		{
			$text .= ADSTAT_L7;
		}
		break;
	case 4:			// 'Normal' render
	case 15 :		// 'Consolidated' render
		if(e107::getPref('statOs')) 
		{
			$text .= $stat -> renderOses(display_pars(e107::getPref('statOs')), $action==4);
		} 
		else 
		{
			$text .= ADSTAT_L7;
		}
		break;
	case 5:
		if(e107::getPref('statDomain')) 
		{
			$text .= $stat -> renderDomains(display_pars(e107::getPref('statDomain')));
		} 
		else 
		{
			$text .= ADSTAT_L7;
		}
		break;
	case 6:
		if(e107::getPref('statScreen')) 
		{
			$text .= $stat -> renderScreens(display_pars(e107::getPref('statScreen')));
		} 
		else 
		{
			$text .= ADSTAT_L7;
		}
		break;
	case 7:
		if (e107::getPref('statRefer')) 
		{
			$text .= $stat -> renderRefers(display_pars(e107::getPref('statRefer')));
		} 
		else 
		{
			$text .= ADSTAT_L7;
		}
		break;
	case 8:
		if (e107::getPref('statQuery')) 
		{
			$text .= $stat -> renderQueries(display_pars(e107::getPref('statQuery')));
		} 
		else 
		{
			$text .= ADSTAT_L7;
		}
		break;
	case 9:
		if (e107::getPref('statRecent')) 
		{
			$text .= $stat -> recentVisitors();
		} 
		else 
		{
			$text .= ADSTAT_L7;
		}
		break;
	case 10:
		$text .= $stat -> renderDaily();
		break;
	case 11:
		$text .= $stat -> renderMonthly();
		break;
	default :
		$text .= $stat -> renderTodaysVisits(FALSE);
}



/* render links 
 1 - Todays visits
 2 - All-time
 3 - Browser stats
 4 - OS Stats
 5 - Domain Stats
 6 - Screen resolution
 7 - Referral stats
 8 - Search strings
 9 - Recent visitors
10 - Daily Stats
11 - Monthly stats
12 - Today's error page visits
13 - All-time error page visits
14 - Consolidated browser view (not listed as a menu option)
15 - Consolidated OS view (not listed as a menu option)
*/

$path = e_PLUGIN_ABS.'log/stats.php';







$links = "
<div style='text-align: center;'>".
($action != 1 ? "<a href='{$path}?1'>".ADSTAT_L8."</a>" : "<b>".ADSTAT_L8."</b>")." | ".
($action != 2 ? "<a href='{$path}?2'>".ADSTAT_L9."</a>" : "<b>".ADSTAT_L9."</b>")." | ".
($action != 10 ? "<a href='{$path}?10'>".ADSTAT_L10."</a>" : "<b>".ADSTAT_L10."</b>")." | ".
($action != 11 ? "<a href='{$path}?11'>".ADSTAT_L11."</a>" : "<b>".ADSTAT_L11."</b>")." | ".
($action != 3 && e107::getPref('statBrowser') ? "<a href='{$path}?3'>".ADSTAT_L12."</a> | " : (e107::getPref('statBrowser') ? "<b>".ADSTAT_L12."</b> | " : "")).
($action != 4 && e107::getPref('statOs') ? "<a href='{$path}?4'>".ADSTAT_L13."</a> | " : (e107::getPref('statOs') ? "<b>".ADSTAT_L13."</b> | " : "")).
($action != 5 && e107::getPref('statDomain') ? "<a href='{$path}?5'>".ADSTAT_L14."</a> | " : (e107::getPref('statDomain') ? "<b>".ADSTAT_L14."</b> | " : "")).
($action != 6 && e107::getPref('statScreen') ? "<a href='{$path}?6'>".ADSTAT_L15."</a> | " : (e107::getPref('statScreen') ? "<b>".ADSTAT_L15."</b> | " : "")).
($action != 7 && e107::getPref('statRefer') ? "<a href='{$path}?7'>".ADSTAT_L16."</a> | " : (e107::getPref('statRefer') ? "<b>".ADSTAT_L16."</b> | " : "")).
($action != 8 && e107::getPref('statQuery') ? "<a href='{$path}?8'>".ADSTAT_L17."</a> | " : (e107::getPref('statQuery') ? "<b>".ADSTAT_L17."</b> | " : "")).
($action != 9 && e107::getPref('statRecent') ? "<a href='{$path}?9'>".ADSTAT_L18."</a> | " : (e107::getPref('statRecent') ? "<b>".ADSTAT_L18."</b> | " : ""));
if (ADMIN == TRUE)
{
	$links .= 
	($action != 12 ? "<a href='{$path}?12'>".ADSTAT_L43."</a>" : "<b>".ADSTAT_L43."</b>")." | ".
	($action != 13 ? "<a href='{$path}?13'>".ADSTAT_L44."</a>" : "<b>".ADSTAT_L44."</b>");
}
$links .= "</div><br /><br />";





// $links = statNav($action)."<br /><br />";

$nav = $stat->renderNav($action)."<br /><br />";

$ns->tablerender(ADSTAT_L6, $nav.$text);
require_once(FOOTERF);


function make_bits($prefix, $act)
{
  $ret = array();
  $now = getdate();
  $ret['hdg_extra'] = '';
  switch ($act)
  {
	case 1 : 
	  $ret['query'] = "log_id='{$prefix}'"; 
	  break;
	case 2 : 
	  $ret['query'] = "log_id='{$prefix}:".date("Y-m")."'"; 
	  $ret['hdg_extra'] = " (".$now['mon']."-".$now['year'].")";
	  break;
	case 3 : 
	  $now['mon']--;
	  if ($now['mon']==0)
	  {
		$now['mon'] = 12;
		$now['year']--;
	  }
	  $ret['query'] = "log_id='{$prefix}:".sprintf("%04d-%02d",$now['year'],$now['mon'])."'"; 
	  $ret['hdg_extra'] = " (".$now['mon']."-".$now['year'].")";
	  break;
	default: $ret = "Invalid selection: {$act}<br />";
  }
  return $ret;
}




class siteStats 
{
	protected $browser_headings = array(1 => ADSTAT_L50, 2 => ADSTAT_L51, 3 => ADSTAT_L52);

	protected $dbPageInfo;
	protected $fileInfo;
	protected $fileBrowserInfo;
	protected $fileOsInfo;
	protected $fileScreenInfo;
	protected $fileDomainInfo;
	protected $fileReferInfo;
	protected $fileQueryInfo;
	protected $fileRecent;

	protected $order;
	protected $bar;
	protected $plugFolder;

	protected $filesiteTotal;
	protected $filesiteUnique;

	public $error;		// Set if error



	protected $oses_map = array (
		"Windows" => "windows",
		"Mac" => "mac",
		"Linux" => "linux",
		"BeOS" => "beos",
		"FreeBSD" => "freebsd",
		"NetBSD" => "netbsd",
		"Unspecified"=> "unspecified",
		"OpenBSD" => "openbsd",
		"Unix" => "unix",
		"Spiders" => "spiders",
		"Android" => "android",
		"Symbian" => "symbian",
		);

	protected $browser_map = array (
		'Netcaptor'         => "netcaptor",
		'Internet Explorer' => "explorer",
		'Firefox'           => "firefox",
		'Opera'             => "opera",
		'AOL'               => "aol",
		'Netscape'          => "netscape",
		'Mozilla'           => "mozilla",
		'Mosaic'            => "mosaic",
		'K-Meleon'          => "k-meleon",
		'Konqueror'         => "konqueror",
		'Avant Browser'     => "avantbrowser",
		'AvantGo'           => "avantgo",
		'Proxomitron'       => "proxomitron",
		'Safari'            => "safari",
		'Lynx'              => "lynx",
		'Links'             => "links",
		'Galeon'            => "galeon",
		'ABrowse'           => "abrowse",
		'Amaya'             => "amaya",
		'ANTFresco'         => "ant",
		'Aweb'              => "aweb",
		'Beonex'            => "beonex",
		'Blazer'            => "blazer",
		'Camino'            => "camino",
		'Chimera'           => "chimera",
		'Columbus'          => "columbus",
		'Crazy Browser'     => "crazybrowser",
		'Curl'              => "curl",
		'Deepnet Explorer'  => "deepnet",
		'Dillo'             => "dillo",
		'Doris'             => "doris",
		'ELinks'            => "elinks",
		'Epiphany'          => "epiphany",
		'Firebird'          => "firebird",
		'IBrowse'           => "ibrowse",
		'iCab'              => "icab",
		'ICEbrowser'        => "ice",
		'iSiloX'            => "isilox",
		'Lotus Notes'       => "lotus",
		'Lunascape'         => "lunascape",
		'Maxthon'           => "maxthon",
		'mBrowser'          => "mbrowser",
		'Multi-Browser'     => "multibrowser",
		'Nautilus'          => "nautilus",
		'NetFront'          => "netfront",
		'NetPositive'       => "netpositive",
		'OmniWeb'           => "omniweb",
		'Oregano'           => "oregano",
		'PhaseOut'          => "phaseout",
		'PLink'             => "plink",
		'Phoenix'           => "phoenix",
		'Proxomitron'       => "proxomitron",
		'Shiira'            => "shiira",
		'Sleipnir'          => "sleipnir",
		'SlimBrowser'       => "slimbrowser",
		'StarOffice'        => "staroffice",
		'Sunrise'           => "sunrise",
		'Voyager'           => "voyager",
		'w3m'               => "w3m",
		'Webtv'             => "webtv",
		'Xiino'             => "xiino",
		'Nokia S60 OSS Browser' => "nokia",
		'Nokia Browser'     => "nokia",
		);


	protected $country = array(
		'arpa'	=> 'ARPANet',
		'com'	=> 'Commercial Users',
		'edu'	=> 'Education',
		'gov'	=> 'Government',
		'int' => 'Organisation established by an International Treaty',
		'mil' => 'Military',
		'net' => 'Network',
		'org' => 'Organisation',
		'ad' => 'Andorra',
		'ae' => 'United Arab Emirates',
		'af' => 'Afghanistan',
		'ag' => 'Antigua & Barbuda',
		'ai' => 'Anguilla',
		'al' => 'Albania',
		'am' => 'Armenia',
		'an' => 'Netherland Antilles',
		'ao' => 'Angola',
		'aq' => 'Antarctica',
		'ar' => 'Argentina',
		'as' => 'American Samoa',
		'at' => 'Austria',
		'au' => 'Australia',
		'aw' => 'Aruba',
		'az' => 'Azerbaijan',
		'ba' => 'Bosnia-Herzegovina',
		'bb' => 'Barbados',
		'bd' => 'Bangladesh',
		'be' => 'Belgium',
		'bf' => 'Burkina Faso',
		'bg' => 'Bulgaria',
		'bh' => 'Bahrain',
		'bi' => 'Burundi',
		'bj' => 'Benin',
		'bm' => 'Bermuda',
		'bn' => 'Brunei Darussalam',
		'bo' => 'Bolivia',
		'br' => 'Brasil',
		'bs' => 'Bahamas',
		'bt' => 'Bhutan',
		'bv' => 'Bouvet Island',
		'bw' => 'Botswana',
		'by' => 'Belarus',
		'bz' => 'Belize',
		'ca' => 'Canada',
		'cc' => 'Cocos (Keeling) Islands',
		'cf' => 'Central African Republic',
		'cg' => 'Congo',
		'ch' => 'Switzerland',
		'ci' => 'Ivory Coast',
		'ck' => 'Cook Islands',
		'cl' => 'Chile',
		'cm' => 'Cameroon',
		'cn' => 'China',
		'co' => 'Colombia',
		'cr' => 'Costa Rica',
		'cs' => 'Czechoslovakia',
		'cu' => 'Cuba',
		'cv' => 'Cape Verde',
		'cx' => 'Christmas Island',
		'cy' => 'Cyprus',
		'cz' => 'Czech Republic',
		'de' => 'Germany',
		'dj' => 'Djibouti',
		'dk' => 'Denmark',
		'dm' => 'Dominica',
		'do' => 'Dominican Republic',
		'dz' => 'Algeria',
		'ec' => 'Ecuador',
		'ee' => 'Estonia',
		'eg' => 'Egypt',
		'eh' => 'Western Sahara',
		'er' => 'Eritrea',
		'es' => 'Spain',
		'et' => 'Ethiopia',
		'fi' => 'Finland',
		'fj' => 'Fiji',
		'fk' => 'Falkland Islands (Malvibas)',
		'fm' => 'Micronesia',
		'fo' => 'Faroe Islands',
		'fr' => 'France',
		'fx' => 'France (European Territory)',
		'ga' => 'Gabon',
		'gb' => 'Great Britain',
		'gd' => 'Grenada',
		'ge' => 'Georgia',
		'gf' => 'Guyana (French)',
		'gh' => 'Ghana',
		'gi' => 'Gibralta',
		'gl' => 'Greenland',
		'gm' => 'Gambia',
		'gn' => 'Guinea',
		'gp' => 'Guadeloupe (French)',
		'gq' => 'Equatorial Guinea',
		'gr' => 'Greece',
		'gs' => 'South Georgia & South Sandwich Islands',
		'gt' => 'Guatemala',
		'gu' => 'Guam (US)',
		'gw' => 'Guinea Bissau',
		'gy' => 'Guyana',
		'hk' => 'Hong Kong',
		'hm' => 'Heard & McDonald Islands',
		'hn' => 'Honduras',
		'hr' => 'Croatia',
		'ht' => 'Haiti',
		'hu' => 'Hungary',
		'id' => 'Indonesia',
		'ie' => 'Ireland',
		'il' => 'Israel',
		'in' => 'India',
		'io' => 'British Indian Ocean Territories',
		'iq' => 'Iraq',
		'ir' => 'Iran',
		'is' => 'Iceland',
		'it' => 'Italy',
		'jm' => 'Jamaica',
		'jo' => 'Jordan',
		'jp' => 'Japan',
		'ke' => 'Kenya',
		'kg' => 'Kyrgyz Republic',
		'kh' => 'Cambodia',
		'ki' => 'Kiribati',
		'km' => 'Comoros',
		'kn' => 'Saint Kitts Nevis Anguilla',
		'kp' => 'Korea (North)',
		'kr' => 'Korea (South)',
		'kw' => 'Kuwait',
		'ky' => 'Cayman Islands',
		'kz' => 'Kazachstan',
		'la' => 'Laos',
		'lb' => 'Lebanon',
		'lc' => 'Saint Lucia',
		'li' => 'Liechtenstein',
		'lk' => 'Sri Lanka',
		'lr' => 'Liberia',
		'ls' => 'Lesotho',
		'lt' => 'Lithuania',
		'lu' => 'Luxembourg',
		'lv' => 'Latvia',
		'ly' => 'Libya',
		'ma' => 'Morocco',
		'mc' => 'Monaco',
		'md' => 'Moldova',
		'mg' => 'Madagascar',
		'mh' => 'Marshall Islands',
		'mk' => 'Macedonia',
		'ml' => 'Mali',
		'mm' => 'Myanmar',
		'mn' => 'Mongolia',
		'mo' => 'Macau',
		'mp' => 'Northern Mariana Islands',
		'mq' => 'Martinique (French)',
		'mr' => 'Mauretania',
		'ms' => 'Montserrat',
		'mt' => 'Malta',
		'mu' => 'Mauritius',
		'mv' => 'Maldives',
		'mw' => 'Malawi',
		'mx' => 'Mexico',
		'my' => 'Malaysia',
		'mz' => 'Mozambique',
		'na' => 'Namibia',
		'nc' => 'New Caledonia (French)',
		'ne' => 'Niger',
		'nf' => 'Norfolk Island',
		'ng' => 'Nigeria',
		'ni' => 'Nicaragua',
		'nl' => 'Netherlands',
		'no' => 'Norway',
		'np' => 'Nepal',
		'nr' => 'Nauru',
		'nt' => 'Saudiarab. Irak)',
		'nu' => 'Niue',
		'nz' => 'New Zealand',
		'om' => 'Oman',
		'pa' => 'Panama',
		'pe' => 'Peru',
		'pf' => 'Polynesia (French)',
		'pg' => 'Papua New Guinea',
		'ph' => 'Philippines',
		'pk' => 'Pakistan',
		'pl' => 'Poland',
		'pm' => 'Saint Pierre & Miquelon',
		'pn' => 'Pitcairn',
		'pr' => 'Puerto Rico (US)',
		'pt' => 'Portugal',
		'pw' => 'Palau',
		'py' => 'Paraguay',
		'qa' => 'Qatar',
		're' => 'Reunion (French)',
		'ro' => 'Romania',
		'ru' => 'Russian Federation',
		'rw' => 'Rwanda',
		'sa' => 'Saudi Arabia',
		'sb' => 'Salomon Islands',
		'sc' => 'Seychelles',
		'sd' => 'Sudan',
		'se' => 'Sweden',
		'sg' => 'Singapore',
		'sh' => 'Saint Helena',
		'si' => 'Slovenia',
		'sj' => 'Svalbard & Jan Mayen',
		'sk' => 'Slovakia',
		'sl' => 'Sierra Leone',
		'sm' => 'San Marino',
		'sn' => 'Senegal',
		'so' => 'Somalia',
		'sr' => 'Suriname',
		'st' => 'Sao Tome & Principe',
		'su' => 'Soviet Union',
		'sv' => 'El Salvador',
		'sy' => 'Syria',
		'sz' => 'Swaziland',
		'tc' => 'Turks & Caicos Islands',
		'td' => 'Chad',
		'tf' => 'French Southern Territories',
		'tg' => 'Togo',
		'th' => 'Thailand',
		'tj' => 'Tadjikistan',
		'tk' => 'Tokelau',
		'tm' => 'Turkmenistan',
		'tn' => 'Tunisia',
		'to' => 'Tonga',
		'tp' => 'East Timor',
		'tr' => 'Turkey',
		'tt' => 'Trinidad & Tobago',
		'tv' => 'Tuvalu',
		'tw' => 'Taiwan',
		'tz' => 'Tanzania',
		'ua' => 'Ukraine',
		'ug' => 'Uganda',
		'uk' => 'United Kingdom',
		'um' => 'US Minor outlying Islands',
		'us' => 'United States',
		'uy' => 'Uruguay',
		'uz' => 'Uzbekistan',
		'va' => 'Vatican City State',
		'vc' => 'St Vincent & Grenadines',
		've' => 'Venezuela',
		'vg' => 'Virgin Islands (British)',
		'vi' => 'Virgin Islands (US)',
		'vn' => 'Vietnam',
		'vu' => 'Vanuatu',
		'wf' => 'Wallis & Futuna Islands',
		'ws' => 'Samoa',
		'ye' => 'Yemen',
		'yt' => 'Mayotte',
		'yu' => 'Yugoslavia',
		'za' => 'South Africa',
		'zm' => 'Zambia',
		'zr' => 'Zaire',
		'zw' => 'Zimbabwe'
	);


	function __construct($order) 
	{
		/* constructor */
		$sql = e107::getDB();

		/* get today's logfile ... */
		$logfile = e_LOG.'logp_'.date('z.Y', time()).'.php';
	//	$logfile = e_PLUGIN.'log/logs/logp_'.date('z.Y', time()).'.php';
		if(is_readable($logfile)) 
		{
			require($logfile);
		}
	//	$logfile = e_PLUGIN.'log/logs/logi_'.date('z.Y', time()).'.php';
		$logfile = e_LOG.'logi_'.date('z.Y', time()).'.php';
		if(is_readable($logfile)) 
		{
			require($logfile);
		}

		$this -> filesiteTotal = vartrue($siteTotal);
		$this -> filesiteUnique = vartrue($siteUnique);

		/* set order var */
		$this -> order = $order;

		$this -> fileInfo = vartrue($pageInfo);
		$this -> fileBrowserInfo = vartrue($browserInfo);
		$this -> fileOsInfo = vartrue($osInfo);
		$this -> fileScreenInfo = vartrue($screenInfo);
		$this -> fileDomainInfo = vartrue($domainInfo);
		$this -> fileReferInfo = vartrue($refInfo);
		$this -> fileQueryInfo = vartrue($searchInfo);
		$this -> fileRecent = vartrue($visitInfo);

		/* get main stat info from database */
		if($sql->select('logstats', 'log_data', "log_id='pageTotal'"))
		{
			$row = $sql -> db_Fetch();
			$this -> dbPageInfo = unserialize($row['log_data']);
		} 
		else 
		{
			$this -> dbPageInfo = array();
		}

		/* temp consolidate today's info (if it exists)... */
		if(is_array($pageInfo)) 
		{
			foreach($pageInfo as $key => $info) 
			{
				$key = preg_replace("/\?.*/", "", $key);
				if(array_key_exists($key, $this -> dbPageInfo)) 
				{
					$this -> dbPageInfo[$key]['ttlv'] += $info['ttl'];
					$this -> dbPageInfo[$key]['unqv'] += $info['unq'];
				} 
				else 
				{
					$this -> dbPageInfo[$key]['url'] = $info['url'];
					$this -> dbPageInfo[$key]['ttlv'] = $info['ttl'];
					$this -> dbPageInfo[$key]['unqv'] = $info['unq'];
				}
			}
		}

		$this -> bar = (file_exists(THEME.'images/bar.png') ? THEME.'images/bar.png' : e_IMAGE.'generic/bar.png');


		$this->plugFolder = e107::getFolder('plugins');

		/* end constructor */
	}

	function renderNav($action)
	{
	//	$path = e_PLUGIN_ABS.'log/stats.php';
		$path = e_REQUEST_SELF;
		
		$links = array(
			1	=> array('label' 	=> ADSTAT_L8, 	'pref' 	=> null),
			2	=> array('label'	=> ADSTAT_L9,	'pref'	=> null),
			10	=> array('label'	=> ADSTAT_L10,	'pref'	=> null),
			11	=> array('label'	=> ADSTAT_L11,	'pref'	=> null),
			3	=> array('label'	=> ADSTAT_L12,	'pref'	=> 'statBrowser'),
			4	=> array('label'	=> ADSTAT_L13,	'pref'	=> 'statOs'),
			5	=> array('label'	=> ADSTAT_L14,	'pref'	=> 'statDomain'),
			6	=> array('label'	=> ADSTAT_L15,	'pref'	=> 'statScreen'),
			7	=> array('label'	=> ADSTAT_L16,	'pref'	=> 'statRefer'),
			8	=> array('label'	=> ADSTAT_L17,	'pref'	=> 'statQuery'),
			9	=> array('label'	=> ADSTAT_L18,	'pref'	=> 'statRecent'),
		);	
		
		if(ADMIN == true)
		{
			$links[12]	= array('label'	=> ADSTAT_L43, 'pref' => null);	
			$links[13]	= array('label'	=> ADSTAT_L44, 'pref' => null);	
		}
		
		$lk = array();
		
		foreach($links as $id => $val)
		{
			if($val['pref'] == null || e107::getPref($val['pref']))
			{
				$selected = ($id === $action) ? "class='active'" : "";
				$lk[] = "<a {$selected} href='".$path."?".$id."'>".$val['label']."</a>";	
			} 		
		}
	
		if(deftrue('BOOTSTRAP'))
		{
			return "<div class='text-right'>".e107::getForm()->button('statNav',$lk,'dropdown',$links[$action]['label'], array('align'=>'right','class'=>'btn-primary'))."</div>";
		}
	
		return "<div style='text-align: center;'>".implode(" | ", $lk)."</div>";
	}

	/**
	 *	sorts multi-dimentional array based on which field is passed 
	 *
	 *	@param array $array - the array to sort
	 *	@param string $column - name of column to sort by
	 *	@param string $order SORT_DESC|SORT_ASC - sort order
	 */
	function arraySort($array, $column, $order = SORT_DESC)
	{
		$i=0;
		foreach($array as $info) 
		{
			$sortarr[]=$info[$column];
			$i++;
		}
		array_multisort($sortarr, $order, $array, $order);
		return($array);
		/* end method */
	}
	

	function getLabel($key,$truncate=false)
	{
		list($url,$language) = explode("|",$key);

		$url = str_replace($this->plugFolder,'',$url);



		if($truncate)
		{
			$result = e107::getParser()->text_truncate($url,50);

			return $result;
		}

		return trim($url);
	}




	/**
	 *	renders information for today only 
	 *
	 *	@param boolean $do_errors - FALSE to show 'normal' accesses, TRUE to show error accesses (i.e. invalid pages)
	 *
	 *	@return string text for display
	 */
	function renderTodaysVisits($do_errors = FALSE) 
	{
		$tp = e107::getParser();
    $template = e107::getTemplate('log', 'log', 'todaysvisits', true, true);
    
    $do_errors = $do_errors && ADMIN && getperms('P');		// Only admins can see page errors

		// Now run through and keep either the non-error pages, or the error pages, according to $do_errors
		$totalArray = array();
		$totalv = 0;
		$totalu = 0;
		$total = 0;
		foreach ($this -> fileInfo as $k => $v)
		{
			$found = (strpos($k,'error/') === 0);
			if ($do_errors XOR !$found) 
			{
				$totalArray[$k] = $v;
				$total += vartrue($v['ttlv']);
			}
		}
		$totalArray = $this -> arraySort($totalArray, "ttl");

		foreach($totalArray as $key => $info) 
		{
			$totalv += $info['ttl'];
			$totalu += $info['unq'];
		}

    $text = $template['start'];    
		
		foreach($totalArray as $key => $info) 
		{
			if($info['ttl'])
			{                
				$percentage = round(($info['ttl']/$totalv) * 100, 2);
        
    		$var = array('ITEM_URL' => $info['url'],  
                     'ITEM_IMAGE' => ($image ? "<img src='".e_PLUGIN_ABS."log/images/html.png' alt='' style='vertical-align: middle;' /> " : ""),        
                     'ITEM_KEY' => $this->getLabel($key),
                     'ITEM_BAR' => $this -> bar($percentage, $info['ttl']." [".$info['unq']."]"),
                     'ITEM_PERC'=> $percentage,                     
        );
            
				$text .= $tp->simpleParse($template['item'], $var);

			}
		}
    $var = array('TOTALV' => $totalv,
                 'TOTALU' => $totalu,                   
    );
    $text .= $tp->simpleParse($template['end'], $var);    
		
		return $text;
	}




	/**
	 *	Renders information for alltime, total and unique 
	 *
	 *	@param string $action - value to incorporate in query part of clickable links
	 *	@param boolean $do_errors - FALSE to show 'normal' accesses, TRUE to show error accesses (i.e. invalid pages)
	 *
	 *	@return string text for display
	 */
	function renderAlltimeVisits($action, $do_errors = FALSE) 
	{
		$sql = e107::getDB();
		$tp = e107::getParser();
    $template = e107::getTemplate('log', 'log', 'alltimevisits_total', true, true);
    
		$text = '';
		$sql->select("logstats", "*", "log_id='pageTotal' ");
		$row = $sql->fetch();
		$pageTotal = unserialize($row['log_data']);
		$total = 0;

		$can_delete = ADMIN && getperms("P");
		$do_errors = $do_errors && $can_delete;
		
		foreach($this -> fileInfo as $url => $tmpcon) 
		{
			$pageTotal[$url]['url'] = $tmpcon['url'];
			$pageTotal[$url]['ttlv'] += $tmpcon['ttl'];
			$pageTotal[$url]['unqv'] += $tmpcon['unq'];
		}

		// Now run through and keep either the non-error pages, or the error pages, according to $do_errors
		$totalArray = array();
		foreach ($pageTotal as $k => $v)
		{
			$found = (strpos($k,'error/') === 0);
			if ($do_errors XOR !$found) 
			{
				$totalArray[$k] = $v;
				$total += $v['ttlv'];
			}
		}

		$totalArray = $this -> arraySort($totalArray, "ttlv");

    $text .= $template['start'];

			
		foreach($totalArray as $key => $info) 
		{
			if($info['ttlv'])
			{
			  if (!$info['url'] && (($key == 'index') || (strpos($key,':index') !== FALSE))) $info['url'] = e_HTTP.'index.php';		// Avoids empty link
				$percentage = round(($info['ttlv']/$total) * 100, 2);
        
        $var = array('ITEM_URL' => $info['url'],          
                     'ITEM_IMAGE' => ($image ? "<img src='".e_PLUGIN_ABS."log/images/html.png' alt='' style='vertical-align: middle;' /> " : ""),        
                     'ITEM_KEY' => $this->getLabel($key,true), 
                     'ITEM_TITLE' => $this->getLabel($key),
                     'ITEM_BAR' => $this->bar($percentage, $info['ttlv']),
                     'ITEM_PERC'=> $percentage,  
                     'ITEM_DELETE'=> ($can_delete ? "<a href='".e_SELF."?{$action}.rem.".rawurlencode($key)."'>
        <img src='".e_PLUGIN_ABS."log/images/remove.png' alt='".ADSTAT_L39."' title='".ADSTAT_L39."' style='vertical-align: middle;' /></a> " : ""),                   
        );
        $text .= $tp->simpleParse($template['item'], $var);
			}
		}
    
    $var = array('TOTAL' => number_format($total),                  
    );
    $text .= $tp->simpleParse($template['end'], $var);     

    $template = e107::getTemplate('log', 'log', 'alltimevisits_unique', true, true);
    
		$uniqueArray = array();
		$totalv = 0;
		foreach ($this -> dbPageInfo as $k => $v)
		{
			$found = (strpos($k,'error/') === 0);
			if ($do_errors XOR !$found) 
			{
				$uniqueArray[$k] = $v;
				$totalv += $v['unqv'];
			}
		}
		$uniqueArray = $this -> arraySort($uniqueArray, "unqv");

		$text .= $template['start'];

		foreach($uniqueArray as $key => $info) 
		{
			if ($info['ttlv'])
			{
			  if (!$info['url'] && (($key == 'index') || (strpos($key,':index') !== FALSE))) $info['url'] = e_HTTP.'index.php';		// Avoids empty link
				$percentage = round(($info['unqv']/$totalv) * 100, 2);
        
        $var = array('ITEM_URL' => $info['url'],
                     'ITEM_KEY' => $tp->text_truncate($key, 50),
                     'ITEM_BAR' => $this -> bar($percentage, $info['unqv']),
                     'ITEM_PERC'=> $percentage,       
        );
        $text .= $tp->simpleParse($template['item'], $var);
			}
		}
    $var = array('TOTAL' => number_format($totalv),                  
    );
    $text .= $tp->simpleParse($template['end'], $var);
		return $text;
	}



	/**
	 *	List browsers. 
	 *	@param integer $selection is an array of the info required - '2' = current month's stats, '1' = all-time stats (default)
	 *	@param boolean $show_version - if FALSE, browsers are consolidated across versions - e.g. 1 line for Firefox using info from $browser_map
	 *
	 *	@return string text for display
	 */
	function renderBrowsers($selection = FALSE, $show_version=TRUE) 
	{
		$sql = e107::getDB();
    $tp = e107::getParser();
    $template = e107::getTemplate('log', 'log', 'browsers', true, true);
    
    
		if (!$selection) $selection = array(1);
		if (!is_array($selection)) $selection = array(1);
		$text = '';

	
		
		foreach ($selection as $act)
		{
			unset($statBrowser);
			$statBrowser = array();
			
			$pars = make_bits('statBrowser',$act);		// Get the query, plus maybe date for heading
			if (!is_array($pars)) return $pars;			// Return error if necessary

			if ($entries = $sql->select('logstats', 'log_data', $pars['query'])) 
			{
				$row = $sql->fetch();
				$statBrowser = unserialize($row['log_data']);
			}
			else
			{
				continue;		// No data - terminate this loop
			}

			/* temp consolidate today's data ... */
			if (($act == 1) || ($act == 2))
			{
				foreach($this->fileBrowserInfo as $name => $count) 
				{
					$statBrowser[$name] += $count;
				}
			}
			
			if ($show_version == FALSE)
			{
				$temp_array = array();
				foreach ($statBrowser as $b_full=>$v)
				{
					$b_type = '';
					foreach ($this->browser_map as $name => $file) 
					{
					  if(stripos($b_full, $name) === 0)
					  {  // Match here
						$b_type = $name;
						break;
					  }
					}
					if (!$b_type) $b_type = $b_full;		// Default is an unsupported browser - use the whole name

					if (array_key_exists($b_type,$temp_array))
					{
					  $temp_array[$b_type] += $v;
					}
					else
					{
					  $temp_array[$b_type] = $v;		// New browser found
					}
				}
				$statBrowser = $temp_array;
				unset($temp_array);
			}


			if ($this -> order) 
			{
				ksort($statBrowser);
				reset ($statBrowser);
				$browserArray = $statBrowser;
			} 
			else 
			{
				$browserArray = $this -> arraySort($statBrowser, 0);
			}

			$total = array_sum($browserArray);
 
      $var = array('START_CAPTION' => $this->browser_headings[$act].$pars['hdg_extra'],
                   'START_TITLE' => ($this -> order ? ADSTAT_L48 : ADSTAT_L49),
                   'START_URL' => e_SELF."?".($show_version ? "3" : "14").($this -> order ? "" : ".1" ),    
        );
      $text .= $tp->simpleParse($template['start'], $var);

			if (count($browserArray))
			{
				foreach($browserArray as $key => $info) 
				{
					$image = "";
					foreach ($this->browser_map as $name => $file) 
					{
						if(strstr($key, $name)) 
						{
							$image = "{$file}.png";
							break;
						}
					}
					if($image == "") 
					{
						$image = "unknown.png";
					}
					$percentage = round(($info/$total) * 100, 2);
          $var = array(                        
                       'ITEM_IMAGE' => ($image ? "<img src='".e_PLUGIN_ABS."log/images/{$image}' alt='' style='vertical-align: middle;' /> " : ""),
                       'ITEM_KEY' => $key,
                       'ITEM_BAR' => $this -> bar($percentage, $info),
                       'ITEM_PERC'=> $percentage,       
          );
          $text .= $tp->simpleParse($template['item'], $var);
          
					$text .= "";
				}
        
        $var = array('TOTAL' => number_format($total),                  
        );
        $text .= $tp->simpleParse($template['end'], $var);
			}
			else
			{
				$text .= $tp->simpleParse($template['nostatistic'], null);
			}
		}
		return $text;
	}




	/**
	 *	Show operating systems.
	 *
	 *	@param integer $selection is an array of the info required - '2' = current month's stats, '1' = all-time stats (default)
	 *	@param $show_version boolean - show different versions of the operating system if  TRUE
	 *
	 *	@return string text for display
	 */
	function renderOses($selection = FALSE, $show_version=TRUE) 
	{
		$sql = e107::getDB();
    $tp = e107::getParser();
    $template = e107::getTemplate('log', 'log', 'oses', true, true);
		if (!$selection) $selection = array(1);
		if (!is_array($selection)) $selection = array(1);
		$text = '';


		$statOs = array();
		foreach ($selection as $act)
		{
			$pars = make_bits('statOs',$act);		// Get the query, plus maybe date for heading
			if (!is_array($pars)) return $pars;			// Return error if necessary

			if ($entries = $sql->select("logstats", "*", $pars['query'])) 
			{
				$row = $sql -> db_Fetch();
				$statOs = unserialize($row['log_data']);
			}
			else
			{
				continue;		// No data - terminate this loop
			}

			/* temp consolidate today's data ... */
			if (($act == 1) || ($act == 2))
			{
				foreach($this -> fileOsInfo as $name => $count) 
				{
					$statOs[$name] += $count;
				}
			}


			if ($show_version == FALSE)
			{
				$temp_array = array();
				foreach ($statOs as $b_full=>$v)
				{
					$b_type = '';
					foreach ($this->oses_map as $name => $file) 
					{
					  if(stripos($b_full, $name) === 0)
					  {  // Match here
						$b_type = $name;
						break;
					  }
					}
					if (!$b_type) $b_type = $b_full;		// Default is an unsupported browser - use the whole name

					if (array_key_exists($b_type,$temp_array))
					{
					  $temp_array[$b_type] += $v;
					}
					else
					{
					  $temp_array[$b_type] = $v;		// New browser found
					}
				}
				$statOs = $temp_array;
				unset($temp_array);
			}



			if($this -> order) 
			{
				ksort($statOs);
				reset ($statOs);
				$osArray = $statOs;
			} 
			else 
			{
				$osArray = $this->arraySort($statOs, 0);
			}

			$total = array_sum($osArray);
      $var = array('START_CAPTION' => $this->browser_headings[$act].$pars['hdg_extra'],
                   'START_TITLE' => ($this -> order ? "sort by total" : "sort alphabetically"),
                   'START_URL' => e_SELF."?".($show_version ? "4" : "15").($this -> order ? "" : ".1" ),    
      );
      $text .= $tp->simpleParse($template['start'], $var);
 
			  
			if (count($osArray))
			{
				foreach($osArray as $key => $info) 
				{
					$image = "";
					if(strstr($key, "Windows")) {	$image = "windows.png"; }
					elseif(strstr($key, "Mac")) {	$image = "mac.png"; }
					elseif(strstr($key, "Linux")) {	$image = "linux.png"; }
					elseif(strstr($key, "BeOS")) {	$image = "beos.png"; }
					elseif(strstr($key, "FreeBSD")) {	$image = "freebsd.png"; }
					elseif(strstr($key, "NetBSD")) {	$image = "netbsd.png"; }
					elseif(strstr($key, "Unspecified")) {	$image = "unspecified.png"; }
					elseif(strstr($key, "OpenBSD")) {	$image = "openbsd.png"; }
					elseif(strstr($key, "Unix")) {	$image = "unix.png"; }
					elseif(strstr($key, "Spiders")) {	$image = "spiders.png"; }
					elseif(stristr($key, "Android")) {	$image = "android.png"; }

					$percentage = round(($info/$total) * 100, 2);
          $var = array(                        
                       'ITEM_IMAGE' => ($image ? "<img src='".e_PLUGIN_ABS."log/images/{$image}' alt='' style='vertical-align: middle;' /> " : ""),
                       'ITEM_KEY' => $key,
                       'ITEM_BAR' => $this -> bar($percentage, $info),
                       'ITEM_PERC'=> $percentage,       
          );
          $text .= $tp->simpleParse($template['item'], $var);
				}
        $var = array('TOTAL' => number_format($total));                  
       
        $text .= $tp->simpleParse($template['end'], $var);
			}
			else
			{
				$text .= $tp->simpleParse($template['nostatistic'], null);
			}
		}
		return $text;
	}



	/**
	 *	Show domains of users
	 *
	 *	@param integer $selection is an array of the info required - '2' = current month's stats, '1' = all-time stats (default)
	 *
	 *	@return string text for display
	 */
	function renderDomains($selection = FALSE) 
	{
		$sql = e107::getDB();
    $tp = e107::getParser();
    $template = e107::getTemplate('log', 'log', 'domains', true, true);
    
		if (!$selection) $selection = array(1);
		if (!is_array($selection)) $selection = array(1);
		$text = '';

		$statDom = array();
		foreach ($selection as $act)
		{
			$pars = make_bits('statDomain',$act);		// Get the query, plus maybe date for heading
			if (!is_array($pars)) return $pars;			// Return error if necessary

			if ($entries = $sql->select('logstats', 'log_data', $pars['query'])) 
			{
				$row = $sql -> db_Fetch();
				$statDom = unserialize($row['log_data']);
			}
			else
			{
				continue;		// No data - terminate this loop
			}

			/* temp consolidate today's data ... */
			if (($act == 1) || ($act == 2))
			{
				foreach($this -> fileDomainInfo as $name => $count) 
				{
					$statDom[$name] += $count;
				}
			}

			if($this -> order) 
			{
				ksort($statDom);
				reset ($statDom);
				$domArray = $statDom;
			} 
			else 
			{
				$domArray = $this -> arraySort($statDom, 0);
			}

			$total = array_sum($domArray);
      $var = array('START_CAPTION' => $this->browser_headings[$act].$pars['hdg_extra'],
                   'START_TITLE' => ($this -> order ? "sort by total" : "sort alphabetically"),
                   'START_URL' => e_SELF."?5".($this -> order ? "" : ".1" ),    
      );
      $text .= $tp->simpleParse($template['start'], $var);

			if (count($domArray))
			{
				foreach($domArray as $key => $info) 
				{
					if($key = $this -> getcountry($key)) 
					{
						$percentage = round(($info/$total) * 100, 2);
            $var = array(                        
                         'ITEM_KEY' => $key,
                         'ITEM_BAR' => $this -> bar($percentage, $info),
                         'ITEM_PERC'=> $percentage,       
            );
            $text .= $tp->simpleParse($template['item'], $var);
					}
				}
        //before: $var = array('TOTAL' => $total,
        $var = array('TOTAL' => number_format($total)); 
        $text .= $tp->simpleParse($template['end'], $var);
			}
			else
			{
				$text .= $tp->simpleParse($template['nostatistic'], null);
			}
		}
		return $text;
	}




	/**
	 *	Show screen resolutions
	 *
	 *	@param integer $selection is an array of the info required - '2' = current month's stats, '1' = all-time stats (default)
	 *
	 *	@return string text for display
	 */
	function renderScreens($selection = FALSE) 
	{
		$sql = e107::getDB();
    $tp = e107::getParser();
    $template = e107::getTemplate('log', 'log', 'screens', true, true);
    
		if (!$selection) $selection = array(1);
		if (!is_array($selection)) $selection = array(1);
		$text = '';

		$statScreen = array();
		foreach ($selection as $act)
		{
			$pars = make_bits('statScreen',$act);		// Get the query, plus maybe date for heading
			if (!is_array($pars)) return $pars;			// Return error if necessary

			if ($entries = $sql->db_Select('logstats', 'log_data', $pars['query'])) 
			{
				$row = $sql -> db_Fetch();
				$statScreen = unserialize($row['log_data']);
			}
			else
			{
				continue;		// No data - terminate this loop
			}

			/* temp consolidate today's data ... */
			if (($act == 1) || ($act == 2))
			{
				foreach($this -> fileScreenInfo as $name => $count) 
				{
					$statScreen[$name] += $count;
				}
			}


			if($this -> order) 
			{
				$nsarray = array();
				foreach($statScreen as $key => $info) 
				{
					if(preg_match("/(\d+)x/", $key, $match)) 
					{
						$nsarray[$key] = array('width' => $match[1], 'info' => $info);
					}
				}
				$nsarray = $this -> arraySort($nsarray, 'width', SORT_ASC);
				reset($nsarray);
				$screenArray = array();
				foreach($nsarray as $key => $info) 
				{
					$screenArray[$key] = $info['info'];
				}
			} 
			else 
			{
				$screenArray = $this -> arraySort($statScreen, 0);
			}

			$total = array_sum($screenArray);
      $var = array('START_CAPTION' => $this->browser_headings[$act].$pars['hdg_extra'],
                   'START_TITLE' => ($this -> order ? "sort by total" : "sort alphabetically"),
                   'START_URL' => e_SELF."?6".($this -> order ? "" : ".1" ),    
      );
      $text .= $tp->simpleParse($template['start'], $var);
 
			if (count($screenArray))
			{
				foreach($screenArray as $key => $info) 
				{
					if(strstr($key, "@") && !strstr($key, "undefined") && preg_match("/(\d+)x(\d+)@(\d+)/", $key)) 
					{
						$percentage = round(($info/$total) * 100, 2);
            $var = array(         
             'ITEM_IMAGE' => ($image ? "<img src='".e_PLUGIN_ABS."log/images/screen.png' alt='' style='vertical-align: middle;' /> " : ""),               
             'ITEM_KEY' => $key,
             'ITEM_BAR' => $this -> bar($percentage, $info),
             'ITEM_PERC'=> $percentage,       
            );
            $text .= $tp->simpleParse($template['item'], $var);
					}
				}
        //before: $var = array('TOTAL' => $total,
        $var = array('TOTAL' => number_format($total)); 
        $text .= $tp->simpleParse($template['end'], $var);
			}
			else
			{
				$text .= $tp->simpleParse($template['nostatistic'], null);
			}
 
		}
		return $text;
	}



	/**
	 *	Show referrers
	 *
	 *	@param integer $selection is an array of the info required - '2' = current month's stats, '1' = all-time stats (default)
	 *
	 *	@return string text for display
	 */
	function renderRefers($selection = FALSE) 
	{
		$sql = e107::getDB();
    $tp = e107::getParser();
    $template = e107::getTemplate('log', 'log', 'refers', true, true);
    
		if (!$selection) $selection = array(1);
		if (!is_array($selection)) $selection = array(1);
		$text = '';

		$statRefer = array();
		foreach ($selection as $act)
		{
			$pars = make_bits('statReferer',$act);		// Get the query, plus maybe date for heading
			if (!is_array($pars)) return $pars;			// Return error if necessary

			if ($entries = $sql->select('logstats', 'log_data', $pars['query'])) 
			{
				$row = $sql -> db_Fetch();
				$statRefer = unserialize($row['log_data']);
			}
			else
			{
				continue;		// No data - terminate this loop
			}

			/* temp consolidate today's data ... */
			if (($act == 1) || ($act == 2))
			{
				foreach($this -> fileReferInfo as $name => $count) 
				{
					$statRefer[$name]['url'] = $count['url'];
					$statRefer[$name]['ttl'] += $count['ttl'];
				}
			}

			$statArray = $this -> arraySort($statRefer, 'ttl');
			$total = 0;
			foreach ($statArray as $key => $info) 
			{
				$total += $info['ttl'];
			}
      $var = array('START_CAPTION' => $this->browser_headings[$act].$pars['hdg_extra'],
                   'START_TITLE'   =>   $this -> order ? "show cropped url" : "show full url",
                   'START_URL'     => e_SELF."?7".($this -> order ? "" : ".1" ),    
      );
      $text .= $tp->simpleParse($template['start'], $var);
      
 
			$count = 0;
			if (count($statArray))
			{
				foreach($statArray as $key => $info) 
				{
					$percentage = round(($info['ttl']/$total) * 100, 2);
					if (!$this -> order && strlen($key) > 50) 
					{
						$key = substr($key, 0, 50)." ...";
					}
          
          $var = array( 
                       'ITEM_IMAGE' => ($image ? "<img src='".e_PLUGIN_ABS."log/images/html.png' alt='' style='vertical-align: middle;' /> " : ""),  
                       'ITEM_URL' => $info['url'],                     
                       'ITEM_KEY' => $key,
                       'ITEM_BAR' => $this -> bar($percentage, $info['ttl']),
                       'ITEM_PERC'=> $percentage,       
          );
          $text .= $tp->simpleParse($template['item'], $var);

					$count++;
					if($count == e107::getPref('statDisplayNumber')) 
					{
						break;
					}
				}
        //before: $var = array('TOTAL' => $total,
        $var = array('TOTAL' => number_format($total)); 
        $text .= $tp->simpleParse($template['end'], $var);
			}
			else
			{
				$text .= $tp->simpleParse($template['nostatistic'], null);
			}
		}
		return $text;
	}




	/**
	 *	Show search queries
	 *
	 *	@param integer $selection is an array of the info required - '2' = current month's stats, '1' = all-time stats (default)
	 *
	 *	@return string - text for display
	 */
	function renderQueries($selection = FALSE) 
	{
		$sql = e107::getDB();
    $tp = e107::getParser();
    $template = e107::getTemplate('log', 'log', 'queries', true, true);

		if (!$selection) $selection = array(1);
		if (!is_array($selection)) $selection = array(1);
		$text = '';

		$statQuery = array();
		foreach ($selection as $act)
		{
			$pars = make_bits('statQuery',$act);		// Get the query, plus maybe date for heading
			if (!is_array($pars)) return $pars;			// Return error if necessary

			if ($entries = $sql->select("logstats", "*", $pars['query'])) 
			{
				$row = $sql -> db_Fetch();
				$statQuery = unserialize($row['log_data']);
			}
			else
			{
				continue;		// No data - terminate this loop
			}

			/* temp consolidate today's data ... */
			if (($act == 1) || ($act == 2))
			{
				foreach ($this -> fileQueryInfo as $name => $count) 
				{
					$statQuery[$name] += $count;
				}
			}


			$queryArray = $this -> arraySort($statQuery, 0);
			$total = array_sum($queryArray);
			$text .= "		
				<table class='table table-striped fborder' style='width: 100%;'>\n
				<tr>
					<th class='fcaption' colspan='4' style='text-align:center'>".$this->browser_headings[$act].$pars['hdg_extra']."</th>
				</tr>
				<tr>
					<th class='fcaption' style='width: 60%;'>".ADSTAT_L31."</th>
					<th class='fcaption' style='width: 30%;' colspan='2'>".ADSTAT_L21."</th>
					<th class='fcaption' style='width: 10%; text-align: center;'>%</th>
				</tr>\n";
				
			$count = 1;
			if (count($queryArray))
			{
				foreach ($queryArray as $key => $info) 
				{
					$percentage = round(($info/$total) * 100, 2);
					$key = str_replace("%20", " ", $key);
					$text .= "<tr>
					<td class='forumheader3' style='width: 60%;'><img src='".e_PLUGIN_ABS."log/images/screen.png' alt='' style='vertical-align: middle;' /> ".$key."</td>
					<td class='forumheader3' style='width: 30%;'>".$this -> bar($percentage, $info)."</td>
					<td class='forumheader3' style='width: 10%; text-align: center;'>".$percentage."%</td>
					</tr>\n";
					$count ++;
					if($count == e107::getPref('statDisplayNumber')) 
					{
						break;
					}
				}
				$text .= "<tr><td class='forumheader' colspan='2'>".ADSTAT_L21."</td><td class='forumheader' style='text-align: center;'>{$total}</td><td class='forumheader'></td></tr>\n";
			}
			else
			{
				$text .= "<tr><td class='fcaption' colspan='4' style='text-align:center'>".ADSTAT_L25."</td></tr>\n";
			}
			$text .= "</table><br />";
		}
		return $text;
	}



	/**
	 *	Display list of recent visitors to site - essentially up to (currently 20) of the entries from today's stats
	 *
	 *	@return string - text for display
	 */
	function recentVisitors() 
	{
		
    $tp = e107::getParser();
    $template = e107::getTemplate('log', 'log', 'visitors', true, true);
    
    if(!is_array($this -> fileRecent) || !count($this -> fileRecent)) 
		{
			return "<div style='text-align: center;'>".ADSTAT_L25.".</div>";
		}

		$gen = new convert;
		$recentArray = array_reverse($this -> fileRecent, TRUE);
		$text = "
			<table class='table table-striped fborder' style='width: 100%;'>
			<tr>
				<th class='fcaption' style='width: 30%;'>".ADSTAT_L18."</th>
				<th class='fcaption' style='width: 70%;'>".ADSTAT_L53."</th>
			</tr>\n";

		foreach($recentArray as $key => $info) 
		{
			if(is_array($info)) 
			{
				$host      = $info['host'];
				$datestamp = $info['date'];
				$os        = $info['os'];
				$browser   = $info['browser'];
				$screen    = $info['screen'];
				$referer   = $info['referer'];
			} 
			else 
			{
				list($host, $datestamp, $os, $browser, $screen, $referer) = explode(chr(1), $info);
			}
			$datestamp = $gen -> convert_date($datestamp, "long");

			$text .= "<tr>
			<td class='forumheader3' style='width: 30%;'>{$datestamp}</td>
			<td class='forumheader3' style='width: 70%;'>Host: {$host}<br />".ADSTAT_L26.": {$browser}<br />".ADSTAT_L27.": {$os}<br />".ADSTAT_L29.": {$screen}".($referer ? "<br />".ADSTAT_L32.": <a href='{$referer}' rel='external'>{$referer}</a>" : "")."</td>
			</tr>\n";
		}

		$text .= "</table>";
		return $text;
	}



	/**
	 *	Show the daily stats - total visits and unique visits
	 *
	 *	@return string - text for display
	 */
	function renderDaily() 
	{
		$sql = e107::getDB();
    $tp = e107::getParser();
    $template = e107::getTemplate('log', 'log', 'daily', true, true);   
    
		$td = date("Y-m-j", time());
		$dayarray[$td] = array();
		$pagearray = array();

		$qry = "
		SELECT * from #logstats WHERE log_id REGEXP('[[:digit:]]+\-[[:digit:]]+\-[[:digit:]]+')
		ORDER BY CONCAT(LEFT(log_id,4), SUBSTRING(log_id, 6, 2), LPAD(SUBSTRING(log_id, 9), 2, '0'))
		DESC LIMIT 0,14
		";

		if($amount = $sql->gen($qry)) 
		{
			$array = $sql -> db_getList();

			$ttotal = 0;
			$utotal = 0;

			foreach($array as $key => $value) 
			{
				extract($value);
				if(is_array($log_data)) {
					$entries[0] = $log_data['host'];
					$entries[1] = $log_data['date'];
					$entries[2] = $log_data['os'];
					$entries[3] = $log_data['browser'];
					$entries[4] = $log_data['screen'];
					$entries[5] = $log_data['referer'];
				} 
				else 
				{
					$entries = explode(chr(1), $log_data);
				}

				$dayarray[$log_id]['daytotal'] = $entries[0];
				$dayarray[$log_id]['dayunique'] = $entries[1];

				unset($entries[0]);
				unset($entries[1]);
				
				foreach($entries as $entry) 
				{
					if($entry) 
					{
						list($url, $total, $unique) = explode("|", $entry);
						if(strstr($url, "/")) 
						{
							$urlname = preg_replace("/\.php|\?.*/", "", substr($url, (strrpos($url, "/")+1)));
						} 
						else 
						{
							$urlname = preg_replace("/\.php|\?.*/", "", $url);
						}
						$dayarray[$log_id][$urlname] = array('url' => $url, 'total' => $total, 'unique' => $unique);
						if (!isset($pagearray[$urlname]['total'])) $pagearray[$urlname]['total'] = 0;
						if (!isset($pagearray[$urlname]['unique'])) $pagearray[$urlname]['unique'] = 0;
						$pagearray[$urlname]['total'] += $total;
						$pagearray[$urlname]['unique'] += $unique;
						$ttotal += $total;
						$utotal += $unique;
					}
				}
			}
		}

		foreach($this -> fileInfo as $fkey => $fvalue)
		{
			$dayarray[$td][$fkey]['total'] += $fvalue['ttl'];
			$dayarray[$td][$fkey]['unique'] += $fvalue['unq'];
			$dayarray[$td]['daytotal'] += $fvalue['ttl'];
			$dayarray[$td]['dayunique'] += $fvalue['unq'];
			$pagearray[$fkey]['total'] += $fvalue['ttl'];
			$pagearray[$fkey]['unique'] += $fvalue['unq'];
			$ttotal += $fvalue['ttl'];
			$utotal += $fvalue['unq'];
		}

	//	print_a($dayarray);;

		$text = "
			<table class='table table-striped fborder' style='width: 100%;'>
			<tr>
				<th class='fcaption' style='width: 30%;'>".ADSTAT_L33." ".($amount+1)." ".ADSTAT_L40."</th>
				<th class='fcaption' style='width: 70%;' colspan='2'>".ADSTAT_L34."</th>
			</tr>\n";

		foreach($dayarray as $date => $total) 
		{
			if (!isset($total['daytotal'])) $total['daytotal'] = 0;
			list($year, $month, $day) = explode("-", $date);
			$date = strftime ("%A, %B %d", mktime (0,0,0,$month,$day,$year));
			$barWidth = round(($total['daytotal']/$ttotal) * 100, 2);
			$text .= "<tr>
			<td class='forumheader3' style='width: 30%;'>$date</td>
			<td class='forumheader3' style='width: 70%;'>".$this -> bar($barWidth, $total['daytotal'])."</td>
			</tr>\n";
		}

		$text .= "</table>";
		$text .= "<br />
		<table class='table table-striped fborder' style='width: 100%;'>
		<tr>
			<th class='fcaption' style='width: 30%;'>".ADSTAT_L35." ".($amount+1)." ".ADSTAT_L40."</th>
			<th class='fcaption' style='width: 70%;' colspan='2'>".ADSTAT_L34."</th>
		</tr>\n";


		if (!isset($total['dayunique'])) $total['dayunique'] = 0;
		if (!isset($total['total'])) $total['total']= 0;

		foreach($dayarray as $date => $total) 
		{
			if (!isset($total['dayunique'])) $total['dayunique'] = 0;
			list($year, $month, $day) = explode("-", $date);
			$date = strftime ("%A, %B %d", mktime (0,0,0,$month,$day,$year));
			$barWidth = round(($total['dayunique']/$utotal) * 100, 2);
			$text .= "<tr>
			<td class='forumheader3' style='width: 30%;'>{$date}</td>
			<td class='forumheader3' style='width: 70%;'>".$this -> bar($barWidth, $total['dayunique'])."</td>
			</tr>\n";
		}
		$text .= "</table>";

		$text .= "<br />
			<table class='table table-striped fborder' style='width: 100%;'>
			<tr>
				<th class='fcaption' style='width: 30%;'>".ADSTAT_L33." ".($amount+1)." ".ADSTAT_L36."</th>
				<th class='fcaption' style='width: 70%;' colspan='2'>".ADSTAT_L34."</th>
			</tr>\n";

		$newArray = $this -> arraySort($pagearray, "total");
		foreach($newArray as $key => $total) 
		{
			$barWidth = round(($total['total']/$ttotal) * 100, 2);
			$text .= "<tr>
			<td class='forumheader3' style='width: 30%;'><img src='".e_PLUGIN."log/images/html.png' alt='' style='vertical-align: middle;' /> {$key}</td>
			<td class='forumheader3' style='width: 70%;'>".$this -> bar($barWidth, $total['total'])."</td>
			</tr>\n";

		}
		$text .= "</table>";
		$text .= "<br />
		
			<table class='table table-striped fborder' style='width: 100%;'>
			<tr>
				<th class='fcaption' style='width: 30%;'>".ADSTAT_L35." ".($amount+1)." ".ADSTAT_L36."</th>
				<th class='fcaption' style='width: 70%;' colspan='2'>".ADSTAT_L34."</th>
			</tr>\n";
			
		$newArray = $this -> arraySort($pagearray, "unique");

		foreach($newArray as $key => $total) {
			$barWidth = round(($total['unique']/$utotal) * 100, 2);
			$text .= "<tr>
			<td class='forumheader3' style='width: 30%;'><img src='".e_PLUGIN_ABS."log/images/html.png' alt='' style='vertical-align: middle;' /> {$key}</td>
			<td class='forumheader3' style='width: 70%;'>".$this -> bar($barWidth, $total['unique'])."</td>
			</tr>\n";
		}
		$text .= "</table>";
		return $text;
	}




	/**
	 *	Show monthly stats
	 *
	 *	@return string text for display
	 */
	function renderMonthly() 
	{
		$sql = e107::getDB();
    $tp = e107::getParser();
    $template = e107::getTemplate('log', 'log', 'monthly', true, true); 
    
		// Month format entries have log_id = yyyy-mm
		if(!$entries = $sql->select("logstats", "*", "log_id REGEXP('^[[:digit:]]+\-[[:digit:]]+$') ORDER BY CONCAT(LEFT(log_id,4), RIGHT(log_id,2)) DESC")) 
		{
			return ADSTAT_L42;
		}

		$text = '';
		$array = $sql -> db_getList();

		$monthTotal = array();		// Array of totals, one per month, with 'totalv', 'uniquev' sub-indices
		$mtotal = 0;
		$utotal = 0;
				
		foreach($array as $info) 
		{
			$date = $info['log_id'];
			$stats = unserialize($info['log_data']);

			/*
			Used to have to calculate monthly stats by adding the individual page access fields
			foreach($stats as $key => $total) 
			{
				if (!isset($monthTotal[$date]['totalv'])) $monthTotal[$date]['totalv'] = 0;
				if (!isset($monthTotal[$date]['uniquev'])) $monthTotal[$date]['uniquev'] = 0;
				$monthTotal[$date]['totalv'] += $total['ttlv'];
				$monthTotal[$date]['uniquev'] += $total['unqv'];
				$mtotal += $total['ttlv'];
				$utotal += $total['unqv'];
			}
			*/
			// Now we store a total, so just use that
			$monthTotal[$date]['totalv'] = varset($stats['TOTAL']['ttlv'], 0);
			$monthTotal[$date]['uniquev'] = varset($stats['TOTAL']['unqv'], 0);
			$mtotal += $monthTotal[$date]['totalv'];
			$utotal += $monthTotal[$date]['uniquev'];
		}

		$tmpArray = $this -> arraySort($monthTotal, "totalv");

		$text .= "
			<table class='table table-striped fborder' style='width: 100%;'>
		<tr>
			<th class='fcaption' style='width: 30%;'>".ADSTAT_L37."</th>
			<th class='fcaption' style='width: 70%;' colspan='2'>".ADSTAT_L34."</th>
		</tr>\n";

		foreach($monthTotal as $date => $total) 
		{
			list($year, $month) = explode("-", $date);
			$date = strftime ("%B %Y", mktime (0,0,0,$month,1,$year));
			$barWidth = round(($total['totalv']/$mtotal) * 100, 2);
			$text .= "<tr>
			<td class='forumheader3' style='width: 30%;'>$date</td>".
			($entries == 1 ? "<td class='forumheader3' style='width: 70%;'>".$this -> bar($barWidth, $total['totalv'])."</td>" : "<td class='forumheader3' style='width: 70%;'>".$this -> bar($barWidth, $total['totalv'])."</td>")."
			</tr>\n";
		}
		$text .= "</table>";

		$text .= "<br />
			<table class='table table-striped fborder' style='width: 100%;'>
			<tr>
				<th class='fcaption' style='width: 30%;'>".ADSTAT_L38."</th>
				<th class='fcaption' style='width: 70%;' colspan='2'>".ADSTAT_L34."</th>
			</tr>\n";

		foreach($monthTotal as $date => $total) 
		{
			$barWidth = round(($total['uniquev']/$utotal) * 100, 2);
			list($year, $month) = explode("-", $date);
			$date = strftime ("%B %Y", mktime (0,0,0,$month,1,$year));
			$text .= "<tr>
			<td class='forumheader3' style='width: 30%;'>$date</td>".
			($entries == 1 ? "<td class='forumheader3' style='width: 70%;'>".$this -> bar($barWidth, $total['uniquev'])."</td>" : "<td class='forumheader3' style='width: 70%;'>".$this -> bar($barWidth, $total['uniquev'])."</td>")."
			</tr>\n";
		}
		$text .= "</table>";


		return $text;
	}




	function getWidthRatio ($array, $column) 
	{
		$tmpArray = $this -> arraySort($array, $column);
		$data = each($tmpArray);
		$maxValue = $data[1]['totalv'];
		echo "<b>maxValue</b> ".$maxValue." <br />";
		$ratio = 0;
		while($maxValue > 100) 
		{
			$maxValue = ($maxValue / 2);
			$ratio ++;
		}
		if(!$ratio)
		{
			return 1;
		}
		echo "<b>ratio</b> ".$ratio." <br />";
		return $ratio;
	}



	function getcountry($dom) 
	{
		return $this->country[$dom];
	}



	/**
	 *	Generate value including a bar of width (horizontal 'length')  as specified
	 *
	 *	@param float $percen - percentage of full width for the bar
	 *	@param float $val - value to be displayed
	 *
	 *	@return string text to be displayed
	 */
	function bar($percen, $val,$name='')
	{
		if(deftrue('BOOTSTRAP'))
		{
			$text = e107::getForm()->progressBar($name,$percen);
		}
		else
		{
			$text = "<div class='b' style='width: ".intval($percen)."%'></div>"; 
		}
		
		
		
		$text .= "
		</td>
		<td style='width:10%; text-align:right' class='forumheader3'>".number_format($val);
		
		return $text;
	}



	/**
	 *	Clear page access from DB entry (doesn't modify today's stats)
	 *
	 *	@param string $toremove - the page name to remove
	 *
	 *	@return boolean TRUE for success, FALSE if no entry found
	 */
	function remove_entry($toremove) 
	{	// Note - only removes info from the database - not from the current page file
		$sql = e107::getDB();
		if ($sql->select("logstats", "*", "log_id='pageTotal'"))
		{
			$row = $sql -> db_Fetch();
			$dbPageInfo = unserialize($row[2]);
			unset($dbPageInfo[$toremove]);
			$dbPageDone = serialize($dbPageInfo);
			$sql -> db_Update("logstats", "log_data='{$dbPageDone}' WHERE log_id='pageTotal' ");
			return TRUE;
		}
		return FALSE;
	}
}



?>
