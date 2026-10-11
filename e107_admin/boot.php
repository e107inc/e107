<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Admin BootLoader
 *
*/

if (!defined('e107_INIT'))
{
	exit;
}

e107::getDebug()->logTime('(Start boot.php)');

if(!e107::isCli())
{
	header('Content-type: text/html; charset=utf-8', TRUE);
}

if(!defined('ADMINFEED')) // Allow e107_config.php to override.
{
	define('ADMINFEED', 'https://e107.org/adminfeed');
}

if(!defined('ADDONFEED')) // Allow e107_config.php to override.
{
	define('ADDONFEED', 'https://e107.org/feed/');
}

if(!empty($_GET['iframe']) && !defined('e_IFRAME')) // global iframe support.
{
	define('e_IFRAME', true);
}

$bootTokenRefused = (defined('e_TOKEN') && empty($_GET['e-token']));
$bootTokenMessage = defset('ADLAN_REFUSED_TOKEN_MISSING', 'Invalid or missing security token.');

// .e-sef-generate routine.
if(e_AJAX_REQUEST && ADMIN && defset('e_ADMIN_UI') && varset($_POST['mode']) == 'sef' && !empty($_POST['source']))
{
	$d = array('converted'=> eHelper::title2sef($_POST['source']));
	echo json_encode($d);
	exit;
}

if(e_AJAX_REQUEST && getperms('0') &&  varset($_GET['mode']) == 'core' && ($_GET['type'] == 'update'))
{
		if($bootTokenRefused)
		{
			header('HTTP/1.1 403 Forbidden', true, 403);
			header('Content-type: application/json; charset=UTF-8');
			echo json_encode(array('msg' => $bootTokenMessage, 'error' => true));
			exit;
		}

		require_once(e_ADMIN.'update_routines.php');

		$status = update_check() === true;

		e107::getSession()->set('core-update-status',$status);

		if(!E107_DEBUG_LEVEL)
		{
			// Record that the check ran, not what it answered. The dashboard used
			// to gate on the answer, which is false on a healthy site, so the check
			// re-ran on every single visit. Set after the check so one that died
			// on the way is not remembered as done.
			e107::getSession()->set('core-update-checked',true);
		}

		echo json_encode($status);

		exit;

}

if(e_AJAX_REQUEST && getperms('0') &&  varset($_GET['mode']) == 'addons' && ($_GET['type'] == 'update'))
{
	if($bootTokenRefused)
	{
		header('HTTP/1.1 403 Forbidden', true, 403);
		echo $bootTokenMessage;
		exit;
	}

	if(!E107_DEBUG_LEVEL)
	{
		e107::getSession()->set(e107\Admin\AddonUpdateStatus::CHECKED, true);
	}

	e107::getSession()->end();

	/** @var admin_shortcodes $sc */
	$sc = e107::getScBatch('admin');

	$themes = $sc->getUpdateable('theme');
	$plugins = $sc->getUpdateable('plugin');

	$updates = array('plugin' => $plugins, 'theme' => $themes);

	if(!E107_DEBUG_LEVEL)
	{
		(new e107\Admin\AddonUpdateStatus(e107::getCache(), e107::getSession()))->set($updates);
	}

	echo $sc->renderAddonUpdateNotice($updates);

	exit;

}


if(e_AJAX_REQUEST &&  ADMIN && varset($_GET['mode']) == 'core' && ($_GET['type'] == 'feed'))
{
	if($bootTokenRefused)
	{
		header('HTTP/1.1 403 Forbidden', true, 403);
		echo $bootTokenMessage;
		exit;
	}

	e107::getSession()->end();

	echo (new e107\Admin\DashboardFeed('core', e107::getCache(), e107::getXml(), e107::getParser()))->renew();

	exit;
}



if(ADMIN && (e_AJAX_REQUEST || deftrue('e_DEBUG_FEEDS')) && varset($_GET['mode']) == 'addons' )
{
	if($bootTokenRefused)
	{
		header('HTTP/1.1 403 Forbidden', true, 403);
		echo $bootTokenMessage;
		exit;
	}

	e107::getSession()->end();

	$type = ($_GET['type'] == 'plugin') ? 'plugin' : 'theme';

	echo (new e107\Admin\DashboardFeed($type, e107::getCache(), e107::getXml(), e107::getParser()))->renew();

	exit;

}


### Language files
e107::coreLan('header', true);
e107::coreLan('footer', true);

// DEPRECATED - plugins should load their lans manually
// plugin autoload, will be removed in the future! 
// here mostly because of BC reasons
//if(!deftrue('e_MINIMAL'))
{
	$_plugins = e107::getPref('plug_installed');
	$plugDir = e107::getFolder('plugins');

	if(strpos(e_REQUEST_URI,$plugDir) !== false && !deftrue('e_ADMIN_UI') && !empty($_plugins) && is_array($_plugins) && (count($_plugins) > 0))
	{
		$_plugins = array_keys($_plugins);

		foreach ($_plugins as $_p) 
		{
			if(defset('e_CURRENT_PLUGIN') != $_p)
			{
				continue;
			}

			if(e107\Language\GlobalLanguageList::has($_p)) // filter out those with globals unless we are in a plugin folder.
			{
				continue;
			}

			e107::getDebug()->logTime('[boot.php: Loading LANS for '.$_p.']');
			e107::loadLanFiles($_p, 'admin');
		}
	}
}


// Get Icon constants, theme override (theme/templates/admin_icons_template.php) is allowed
e107::getDebug()->logTime('[boot.php: Loading admin_icons]');
e107::loadAdminIcons();
e107::getDebug()->logTime('[boot.php: After Loading admin_icons]');


if(!defset('e_ADMIN_UI') && !defset('e_PAGETITLE'))
{
	e107::getDebug()->logTime('[boot.php: Loading adminLinks(\'legacy\')]');
	$array_functions = e107::getNav()->adminLinks('legacy'); // replacement see e107_handlers/sitelinks.php
	foreach($array_functions as $val)
	{
	    $link = str_replace("../","",$val[0]);
		//if(strpos(e_SELF,$link)!==FALSE)
	//	{
	 //   	define('e_PAGETITLE',$val[1]);
	//	}
	}
}


if (!defined('ADMIN_WIDTH')) //BC Only 
{
	define('ADMIN_WIDTH', "width:100%;");
}



/**
 * @deprecated
 * @param int|bool $update return result of db::db_Query
 * @param string $type update|insert|update
 * @param string|bool $success forced success message
 * @param string|bool $failed forced error message
 * @param bool $output false suppress any function output
 * @return int|bool db::db_Query result
 */
function admin_update($update, $type = 'update', $success = false, $failed = false, $output = true)
{
	$message = "admin_update() is deprecated (). Use e107::getMessage()->addAuto(); instead. ";
	e107::getMessage()->addDebug($message);
	trigger_error($message);
	return e107::getMessage()->addAuto($update, $type, $success , $failed , $output);
}


function admin_purge_related($table, $id)
{
	$ns = e107::getRender();
	$tp = e107::getParser();
	$msg = "";
	$tp->parseTemplate("");

	// Delete any related comments
	require_once (e_HANDLER."comment_class.php");
	$_com = new comment;
	$num = $_com->delete_comments($table, $id);
	if ($num)
	{
		$msg .= $num." ".LAN_COMMENTS." ".defset('LAN_DELETED', 'Deleted')."<br />";
	}

	// Delete any related ratings
	require_once (e_HANDLER."rate_class.php");
	$_rate = new rater;
	$num = $_rate->delete_ratings($table, $id);
	if ($num)
	{
		$msg .= LAN_RATING." ".defset('LAN_DELETED', 'Deleted')."<br />";
	}

	if ($msg)
	{
		$ns->tablerender(LAN_DELETE, $msg);
	}
}

// legacy vars, will be removed soon
$ns = e107::getRender();
$e107_var = array();


/**
 * @deprecated  Left in for BC for now. Use admin-ui instead.
 * @param $title
 * @param $active_page
 * @param $e107_vars
 * @param array $tmpl
 * @param false $sub_link
 * @param false $sortlist
 * @return string|null
 */
function e_admin_menu($title, $active_page, $e107_vars, $tmpl = array(), $sub_link = false, $sortlist = false)
{
	if (!$tmpl)
	{
		$tmpl = e107::getCoreTemplate('admin', 'menu', false);
	}

	return e107::getNav()->admin($title, $active_page, $e107_vars, $tmpl, $sub_link , $sortlist );
}

/*
 *  DEPRECATED - use e_adm/in_menu()  e107::getNav()->admin
 */

if (!function_exists('show_admin_menu'))
{
	/**
	 * @deprecated Use admin-ui instead.
	 * @param $title
	 * @param $active_page
	 * @param $vars
	 * @param false $js
	 * @param false $sub_link
	 * @param false $sortlist
	 * @return string|null
	 */
	function show_admin_menu($title, $active_page, $var, $js = FALSE, $sub_link = FALSE, $sortlist = FALSE)
	{
		unset($js,$sub_link);

		if(!isset($var['_extras_']['icon']) && deftrue('e_CURRENT_PLUGIN'))
		{
			$var['_extras_'] = array('icon'=>  e107::getPlug()->load(e_CURRENT_PLUGIN)->getIcon(24), 'return'=>false);
		}

		return e107::getNav()->admin($title, $active_page, $var, false, false, $sortlist);
	}
}

// parse_admin() has been replaced by e107::renderLayout()

