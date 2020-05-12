<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Plugin configuration module - gsitemap
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/gsitemap/e_module.php,v $
 * $Revision$
 * $Date$
 * $Author$
 *
*/

 if (!defined('e107_INIT')) { exit; }
if(!e107::isInstalled('gsitemap'))
{ 
	return '';
}

 global $e_event,$e107cache,$ns;
// $e_event->register("newspost", "pingit");
// $e_event->register("newsupd", "pingit");		// Disable these for now, until admin functions written

function pingit($vals)
{
	global $admin_log;
	require(e_PLUGIN."gsitemap/weblog_pinger.php");
	$pinger = new Weblog_Pinger();
 //	$pinger->ping_ping_o_matic("Ekzemplo", "http://www.ekzemplo.com/");

	$xml_rpc_server = "blogsearch.google.com";
    $xml_rpc_port 	= 80;
	$xml_rpc_path	= "/ping/RPC2";
	$xml_rpc_method	= "weblogUpdates.extendedPing";
	$weblog_name	= SITENAME;
	$weblog_url		= $_SERVER['HTTP_HOST'].e_HTTP;
	$changes_url	= $_SERVER['HTTP_HOST'].e_HTTP."news.php?extend.".$vals['news_id'];
	$cat_or_rss		= $_SERVER['HTTP_HOST'].e_PLUGIN_ABS."rss_menu/rss.php?1.2";
    $extended		= TRUE;

  	$pinger->ping($xml_rpc_server, $xml_rpc_port, $xml_rpc_path, $xml_rpc_method, $weblog_name, $weblog_url, $changes_url, $cat_or_rss, $extended);
    $log = strip_tags($vals['news_title']."\n".$changes_url."\n".$cat_or_rss."\n".$pinger->smessage);
	e107::getLog()->add("Gsitemap Google-ping",$log, 4);

}

