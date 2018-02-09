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

e107::getDb()->db_Mark_Time('(Start boot.php)');
header('Content-type: text/html; charset=utf-8', TRUE);

define('ADMINFEED', 'https://e107.org/adminfeed');



if(!empty($_GET['iframe'])) // global iframe support. 
{
	define('e_IFRAME', true);
}

// .e-sef-generate routine.
if(e_AJAX_REQUEST && ADMIN && defset('e_ADMIN_UI') && varset($_POST['mode']) == 'sef' && !empty($_POST['source']))
{
	$d = array('converted'=> eHelper::title2sef($_POST['source']));
	echo json_encode($d);
	exit;
}

if(e_AJAX_REQUEST && getperms('0') &&  varset($_GET['mode']) == 'core' && ($_GET['type'] == 'update'))
{

		require_once(e_ADMIN.'update_routines.php');

		e107::getSession()->set('core-update-checked',false);

		$status = (update_check() === true) ? true : false;

		e107::getSession()->set('core-update-status',$status);

		echo json_encode($status);

		exit;

}

if(e_AJAX_REQUEST && getperms('0') &&  varset($_GET['mode']) == 'addons' && ($_GET['type'] == 'update'))
{
	e107::getSession()->set('addons-update-checked',true);

	$sc = e107::getScBatch('admin');

	$themes = $sc->getUpdateable('theme');
	$plugins = $sc->getUpdateable('plugin');

	$text = $sc->renderAddonUpdate($plugins);
	$text .= $sc->renderAddonUpdate($themes);

	if(empty($text))
	{
		exit;
	}

	$ns = e107::getRender();

	$tp = e107::getParser();
	$ns->setUniqueId('e-addon-updates');
	$ns->setStyle('warning');
	$ret = $ns->tablerender($tp->toGlyph('fa-arrow-circle-o-down').LAN_UPDATE_AVAILABLE,$text,'default', true);

	echo $ret;

	e107::getSession()->set('addons-update-status',$ret);

	exit;

}


if(e_AJAX_REQUEST &&  ADMIN && varset($_GET['mode']) == 'core' && ($_GET['type'] == 'feed'))
{

	$limit = 3;

	if($data = e107::getXml()->getRemoteFile(ADMINFEED,3))
	{
	//	print_a($data);
		$rows = e107::getXml()->parseXml($data, 'advanced');
		$defaultImg = $rows['channel']['image']['url'];

		$text = '<div style="margin-left:10px;margin-top:10px">';
		$count = 1;
		foreach($rows['channel']['item'] as $row)
		{
			if($count > $limit){ break; }

			$description = $tp->toText($row['description']);
			$text .= '
			<div class="media">
			  <div class="media-body">
			    <h4 class="media-heading"><a target="_blank" href="'.$row['link'].'">'.$row['title'].'</a> <small>— '.$row['pubDate'].'</small></h4>
			   '.$tp->text_truncate($description,150).'
			  </div></div>';
			  $count++;
		}
		$text .= '</div>';
		echo $text;

	}
	else
	{
		if(e_DEBUG)
		{
		//	echo "Feed failed: ".ADMINFEED;
		}
	}
	exit;
}



if(ADMIN && (e_AJAX_REQUEST || deftrue('e_DEBUG_FEEDS')) && varset($_GET['mode']) == 'addons' )
{
	$type = ($_GET['type'] == 'plugin') ? 'plugin' : 'theme';
	$tag = 'Infopanel_'.$type;

	$cache = e107::getCache();

	$feed = 'https://e107.org/feed/?limit=3&type='.$type;

	if($text = $cache->retrieve($tag,180,true, true)) // check every 3 hours.
	{
		echo $text;

		if(e_DEBUG === true)
		{
			echo "<span class='label label-warning' title='".$feed."'>Cached</span>";
		}
		exit;
	}


	if($data = e107::getXml()->getRemoteFile($feed,3))
	{
		$rows = e107::getXml()->parseXml($data, 'advanced');
//	print_a($rows);
//  exit;
		$link = ($type == 'plugin') ? e_ADMIN."plugin.php?mode=online" : e_ADMIN."theme.php?mode=main&action=online";

		$text = "<div style='margin-top:10px'>";

		foreach($rows[$type] as $val)
		{
			$meta = $val['@attributes'];
			$img = ($type == 'theme') ? $meta['thumbnail'] : $meta['icon'];
			$text .= '<div class="media">';
			$text .= '<div class="media-left">
		    <a href="'.$link.'">
		      <img class="media-object img-rounded rounded" src="'.$img.'" style="width:100px">
		    </a>
		  </div>
		  <div class="media-body">
		    <h4 class="media-heading"><a href="'.$link.'">'.$meta['name'].' v'.$meta['version'].'</a> <small>&mdash; '.$meta['author'].'</small></h4>
		    '.$val['description'].'
		  </div>';
			$text .= '</div>';
		}

		$text .= "</div>";
		$text .= "<div class='right'><a href='".$link."'>".LAN_MORE."</a></div>";

		echo $text;

		$cache->set($tag, $text, true, null, true);

	}
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
	$_globalLans = e107::pref('core', 'lan_global_list'); 
	$_plugins = e107::getPref('plug_installed');
	$plugDir = e107::getFolder('plugins');

	if(strpos(e_REQUEST_URI,$plugDir) !== false && !deftrue('e_ADMIN_UI') && !empty($_plugins) && !empty($_globalLans) && is_array($_plugins) && (count($_plugins) > 0))
	{
		$_plugins = array_keys($_plugins);
		
		foreach ($_plugins as $_p) 
		{
			if(defset('e_CURRENT_PLUGIN') != $_p)
			{
				continue;
			}

			if(in_array($_p, $_globalLans)) // filter out those with globals unless we are in a plugin folder.
			{
				continue;
			}
			
			e107::getDb()->db_Mark_Time('[boot.php: Loading LANS for '.$_p.']');
			e107::loadLanFiles($_p, 'admin');
		}
	}
}


// Get Icon constants, theme override (theme/templates/admin_icons_template.php) is allowed
e107::getDb()->db_Mark_Time('[boot.php: Loading admin_icons]');
include_once(e107::coreTemplatePath('admin_icons'));


if(!defset('e_ADMIN_UI') && !defset('e_PAGETITLE'))
{
	$array_functions = e107::getNav()->adminLinks('legacy'); // replacement see e107_handlers/sitelinks.php
	foreach($array_functions as $val)
	{
	    $link = str_replace("../","",$val[0]);
		if(strpos(e_SELF,$link)!==FALSE)
		{
	 //   	define('e_PAGETITLE',$val[1]);
		}
	}
}


if (!defined('ADMIN_WIDTH')) //BC Only 
{
	define('ADMIN_WIDTH', "width:100%;");
}



/**
 * Automate DB system messages DEPRECATED
 * NOTE: default value of $output parameter will be changed to false (no output by default) in the future
 *
 * @param integer|bool $update return result of db::db_Query
 * @param string $type update|insert|update
 * @param string $success forced success message
 * @param string $failed forced error message
 * @param bool $output false suppress any function output
 * @return integer|bool db::db_Query result
 */
 // TODO - This function often needs to be available BEFORE header.php is loaded. 
 
 
 //XXX DEPRECATED It has been copied to message_handler.php as addAuto();
 
function admin_updXXate($update, $type = 'update', $success = false, $failed = false, $output = true)
{
	e107::getMessage()->addDebug("Using deprecated admin_update () which has been replaced by \$mes->addAuto();"); 
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
		$msg .= $num." ".LAN_COMMENTS." ".LAN_DELETED."<br />";
	}

	// Delete any related ratings
	require_once (e_HANDLER."rate_class.php");
	$_rate = new rater;
	$num = $_rate->delete_ratings($table, $id);
	if ($num)
	{
		$msg .= LAN_RATING." ".LAN_DELETED."<br />";
	}

	if ($msg)
	{
		$ns->tablerender(LAN_DELETE, $msg);
	}
}

// legacy vars, will be removed soon
$ns = e107::getRender();
$e107_var = array();

// Left in for BC for now. 

function e_admin_menu($title, $active_page, $e107_vars, $tmpl = array(), $sub_link = false, $sortlist = false)
{
			
	global $E_ADMIN_MENU;
	if (!$tmpl)
		$tmpl = $E_ADMIN_MENU;
	
	
	return e107::getNav()->admin($title, $active_page, $e107_vars, $tmpl, $sub_link , $sortlist );
	
	
	// See e107::getNav()->admin(); 
	
	
	
	
	
	/*
	/// Search for id

	$temp = explode('--id--', $title, 2);
	$title = $temp[0];
	$id = str_replace(array(' ', '_'), '-', varset($temp[1]));

	unset($temp);

	//  SORT

	 

	if ($sortlist == TRUE)
	{
		$temp = $e107_vars;
		unset($e107_vars);
		$func_list = array();
		foreach (array_keys($temp) as $key)
		{
			$func_list[] = $temp[$key]['text'];
		}

		usort($func_list, 'strcoll');

		foreach ($func_list as $func_text)
		{
			foreach (array_keys($temp) as $key)
			{
				if ($temp[$key]['text'] == $func_text)
				{
					$e107_vars[] = $temp[$key];
				}
			}
		}
		unset($temp);
	}



	$kpost = '';
	$text = '';
	
	if ($sub_link)
	{
		$kpost = '_sub';
	}
	else
	{
		 $text = $tmpl['start'];
	}

	//FIXME - e_parse::array2sc()
	$search = array();
	$search[0] = '/\{LINK_TEXT\}(.*?)/si';
	$search[1] = '/\{LINK_URL\}(.*?)/si';
	$search[2] = '/\{ONCLICK\}(.*?)/si';
	$search[3] = '/\{SUB_HEAD\}(.*?)/si';
	$search[4] = '/\{SUB_MENU\}(.*?)/si';
	$search[5] = '/\{ID\}(.*?)/si';
	$search[6] = '/\{SUB_ID\}(.*?)/si';
	$search[7] = '/\{LINK_CLASS\}(.*?)/si';
	$search[8] = '/\{SUB_CLASS\}(.*?)/si';
	$search[9] = '/\{LINK_IMAGE\}(.*?)/si';
	
	foreach (array_keys($e107_vars) as $act)
	{
		if (isset($e107_vars[$act]['perm']) && !getperms($e107_vars[$act]['perm'])) // check perms first.
		{
			continue;
		}
		
		// check class so that e.g. e_UC_NOBODY will result no permissions granted (even for main admin)
		if (isset($e107_vars[$act]['userclass']) && !e107::getUser()->checkClass($e107_vars[$act]['userclass'], false)) // check userclass perms 
		{
			continue;
		}

		//  print_a($e107_vars[$act]);

		$replace = array();
		
		$rid = str_replace(array(' ', '_'), '-', $act).($id ? "-{$id}" : '');
		
		if (($active_page == $act && !is_numeric($act))|| (str_replace("?", "", e_PAGE.e_QUERY) == str_replace("?", "", $act)))
		{
			$temp = $tmpl['button_active'.$kpost];
		}
		else
		{
			$temp = $tmpl['button'.$kpost];
		}

	//	$temp = $tmpl['button'.$kpost];
	//	echo "ap = ".$active_page;
	//	echo " act = ".$act."<br /><br />";
	
		if($rid == 'adminhome')
		{
			$temp = $tmpl['button_other'.$kpost];	
		}

		if($rid == 'home')
		{
			$temp = $tmpl['button_home'.$kpost];	
		}
		
		if($rid == 'language')
		{
			$temp = $tmpl['button_language'.$kpost];	
		}
		
		if($rid == 'logout')
		{
			$temp = $tmpl['button_logout'.$kpost];	
		}
		
		$replace[0] = str_replace(" ", "&nbsp;", $e107_vars[$act]['text']);
		// valid URLs
		$replace[1] = str_replace(array('&amp;', '&'), array('&', '&amp;'), varsettrue($e107_vars[$act]['link'], "#{$act}"));
		$replace[2] = '';
		if (varsettrue($e107_vars[$act]['include']))
		{
			$replace[2] = $e107_vars[$act]['include'];
			//$replace[2] = $js ? " onclick=\"showhideit('".$act."');\"" : " onclick=\"document.location='".$e107_vars[$act]['link']."'; disabled=true;\"";
		}
		$replace[3] = $title;
		$replace[4] = '';
		
		$replace[5] = $id ? " id='eplug-nav-{$rid}'" : '';
		$replace[6] = $rid;
	
		$replace[7] = varset($e107_vars[$act]['link_class']);
		$replace[8] = '';
		$replace[9] = varset($e107_vars[$act]['image']);
		
		if($rid == 'logout' || $rid == 'home' || $rid == 'language')
		{
			$START_SUB = $tmpl['start_other_sub'];
		}
		else 
		{
			$START_SUB = $tmpl['start_sub'];	
		}		

		if (varsettrue($e107_vars[$act]['sub']))
		{
			$replace[6] = $id ? " id='eplug-nav-{$rid}-sub'" : '';
			$replace[7] = ' '.varset($e107_vars[$act]['link_class'], 'e-expandit');
			$replace[8] = ' '.varset($e107_vars[$act]['sub_class'], 'e-hideme e-expandme');
			$replace[4] = preg_replace($search, $replace, $START_SUB);
			$replace[4] .= e_ad/min_menu(false, $active_page, $e107_vars[$act]['sub'], $tmpl, true, (isset($e107_vars[$act]['sort']) ? $e107_vars[$act]['sort'] : $sortlist));
			$replace[4] .= $tmpl['end_sub'];
		}

		$text .= preg_replace($search, $replace, $temp);
	//	echo "<br />".$title." act=".$act;
		//print_a($e107_vars[$act]);
	}

	$text .= (!$sub_link) ? $tmpl['end'] : '';
	
	if ($sub_link || empty($title))
	{
		return $text;
	}

	$ns = e107::getRender();
	$ns->tablerender($title, $text, array('id'=>$id, 'style'=>'button_menu'));
	return '';
	  */
}

/*
 *  DEPRECATED - use e_adm/in_menu()  e107::getNav()->admin
 */
if (!function_exists('show_admin_menu'))
{
	function show_admin_menu($title, $active_page, $e107_vars, $js = FALSE, $sub_link = FALSE, $sortlist = FALSE)
	{
		
		return e107::getNav()->admin($title, $active_page, $e107_vars, false, false, $sortlist);
	}
}

if (!function_exists("parse_admin"))
{
	function parse_admin($ADMINLAYOUT)
	{
		$sc = e107::getScBatch('admin');
		$tp = e107::getParser();
		$adtmp = explode("\n", $ADMINLAYOUT);
		
		for ($a = 0; $a < count($adtmp); $a++)
		{
			if (preg_match("/{.+?}/", $adtmp[$a]))
			{
				echo $tp->parseTemplate($adtmp[$a], true, $sc);
			}
			else
			{
				echo $adtmp[$a];
			}
		}
	}
}
