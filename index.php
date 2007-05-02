<?php

/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/index.php,v $
|     $Revision: 1.2 $
|     $Date: 2007-05-02 19:47:47 $
|     $Author: e107steved $

Mods for prioritised system

+----------------------------------------------------------------------------+
*/

require_once('class2.php');

if (file_exists('index_include.php')) 
{
  include('index_include.php');
}


// Legacy bit to handle 0.6xx values of prefs - simplify to a sensible option later
if (!is_array($pref['frontpage']) && $pref['frontpage'] != 'Array') {
	if (!$pref['frontpage'] || $pref['frontpage'] == 'Array.php') {
		$up_pref = 'news.php';
	} else if ($pref['frontpage'] == 'links') {
		$up_pref = $PLUGINS_DIRECTORY.'links_page/links.php';
	} else if ($pref['frontpage'] == 'forum') {
		$up_pref = $PLUGINS_DIRECTORY.'forum/forum.php';
	} else if (is_numeric($pref['frontpage'])) {
		$up_pref = $PLUGINS_DIRECTORY.'content/content.php?content.'.$pref['frontpage'];
	} else if (substr($pref['frontpage'], -1) != '/' && strpos($pref['frontpage'], '.') === FALSE) {
		$up_pref = $pref['frontpage'].'.php';
	} else {
		$up_pref = $pref['frontpage'];
	}
	unset($pref['frontpage']);
	$pref['frontpage']['all'] = $up_pref;
	save_prefs();
}

$query = (e_QUERY && e_QUERY != '' && !$_GET['elan']) ? '?'.e_QUERY : '';
$location = '';

if ($pref['membersonly_enabled'] && !USER) 
{
  header('location: '.e_LOGIN);
  exit;
}


$class_list = explode(',',USERCLASS_LIST);


if (isset($pref['frontpage']['all']) && $pref['frontpage']['all']) 
{
  $location = ((strpos($pref['frontpage']['all'], 'http') === FALSE) ? e_BASE : '').$pref['frontpage']['all'].$query;
} 
else
{	// This is the 'new' method - assumes $pref['frontpage'] is an ordered list of rules
  echo "Using new method: ".USERCLASS_LIST."<br />";
  foreach ($pref['frontpage'] as $fk=>$fp)
  {
	if (in_array($fk,$class_list))
	{
	  $location = ((strpos($fp, 'http') === FALSE) ? e_BASE : '').$fp.$query;
	  echo "Redirecting to: ".$location."<br />";
	  break;
	}
  }
}


if (!$location)
{  // Try and use the 'old' method (this bit can go later)
  echo "Using OLD METHOD<br />";
  if (ADMIN) 
  {
    $location =  ((strpos($pref['frontpage'][e_UC_ADMIN], 'http') === FALSE) ? e_BASE : '').$pref['frontpage'][e_UC_ADMIN].$query;
  } 
  elseif (USER) 
  {  // This is the key bit - what to do for a 'normal' logged in user
	// We have USERCLASS_LIST - comma separated. Also e_CLASS_REGEXP
    foreach ($class_list as $fp_class) 
	{
	  $inclass = false;
	  if (!$inclass && check_class($fp_class['userclass_id'])) 
	  {
		$location = ((strpos($pref['frontpage'][$fp_class['userclass_id']], 'http') === FALSE) ? e_BASE : '').$pref['frontpage'][$fp_class['userclass_id']].$query;
		$inclass = true;
	  }
    }
    $location = $location ? $location : ((strpos($pref['frontpage'][e_UC_MEMBER], 'http') === FALSE) ? e_BASE : '').$pref['frontpage'][e_UC_MEMBER].$query;
  } 
  else 
  {
    $location = ((strpos($pref['frontpage'][e_UC_GUEST], 'http') === FALSE) ? e_BASE : '').$pref['frontpage'][e_UC_GUEST].$query;
  }
}

if (!trim($location)) $location = 'news.php';


// handle redirect and include front page methods ($pref['frontpage_method'] looks as if not used)
if(isset($pref['frontpage_method']) && $pref['frontpage_method'] == "include") 
{
  if($location == "news.php") 
  {
	require_once("news.php");
  } 
  elseif ($location == PLUGINS_DIRECTORY."forum/forum.php") 
  {
	require_once($PLUGINS_DIRECTORY."forum/forum.php");
  } 
  elseif (preg_match('/^page\.php\?([0-9]*)$/', $location)) 
  {
	$e_QUERY = preg_match('/^page\.php\?([0-9]*)$/', $location);
	require_once("page.php");
  } 
  else 
  {
  	header("Location: {$location}");
	exit();
  }
} 
else 
{
  list($page,$str) = explode("?",$location."?"); // required to prevent infinite looping when queries are used on index.php.
  if($page == "index.php") // Welcome Message is the front-page.
  {
 	require_once(HEADERF);
 	require_once(FOOTERF);
  	exit;
  }
  else 
  {  // redirect to different frontpage.
	header("Location: {$location}");
  }
  exit();
}

?>
