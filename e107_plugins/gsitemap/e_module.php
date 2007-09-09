<?php

 if (!defined('e107_INIT')) { exit; }

 global $e_event,$e107cache,$ns;
 $e_event->register("newspost", "pingit");
 $e_event->register("newsupd", "pingit");

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
	$admin_log->log_event("Gsitemap Google-ping",$log, 4);

}

?>