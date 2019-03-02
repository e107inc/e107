<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2016 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)

 */

	if (!defined('e107_INIT')) { exit; }

	if (!e107::isInstalled('rss_menu'))
	{
		return '';
	}

	$sql = e107::getDb();

	e107::includeLan(e_PLUGIN."rss_menu/languages/".e_LANGUAGE."_admin_rss_menu.php");

	$topic = "";
	$caption = "";


	if(e_PAGE == "news.php" && e107::getPref('rss_newscats'))
	{

		$qry = explode(".",e_QUERY);
		if($qry[0] == "cat" || $qry[0] == "list")
		{
			$topic = intval($qry[1]);
		}
	}


	if(deftrue('e_CURRENT_PLUGIN')  && $res = $sql->retrieve("rss", "rss_path,rss_url", " rss_path = '".e_CURRENT_PLUGIN."' LIMIT 1"))
	{
		$caption = e107::getParser()->lanVars(LAN_PLUGIN_RSS_SUBSCRIBE_TO, deftrue('LAN_PLUGIN_'.strtoupper(e_CURRENT_PLUGIN).'_NAME'));
		$type = $res['rss_url'];
		$plug = $res['rss_path'];
	}
	elseif($sql->select("rss", "rss_path", " rss_path = 'news' LIMIT 1")) // Fall back to news, if available.
	{
		$caption = LAN_PLUGIN_RSS_SUBSCRIBE;
		$type = 'news';
		$plug = 'news';
	}



	if(!empty($type))
	{
		$arr = array('rss_topicid'=> $topic, 'rss_url'=>$type);


		if(deftrue('BOOTSTRAP')) // v2.x
		{
			$text = "
			<div>
				<a class='btn btn-sm btn-default'  href='".e107::url('rss_menu', 'rss', $arr)."'>".$tp->toGlyph('fa-rss')." RSS</a>
				<a class='btn btn-sm btn-default'  href='".e107::url('rss_menu', 'atom', $arr)."'>".$tp->toGlyph('fa-rss')." Atom</a>
			</div>";

		}
		else // v1.x
		{
			$path = e_PLUGIN_ABS."rss_menu/";

			$description = array(

				'chatbox'       => RSS_MENU_L7,
				'download'      => RSS_MENU_L9,
				'bugtracker'    => RSS_MENU_L8,
				'forumtopic'    => RSS_MENU_L6,
				'forumname'     => RSS_MENU_L5,
				'forumposts'   => '',
				'comments'     => RSS_MENU_L4,
				5              => RSS_MENU_L4,
				6               => RSS_MENU_L5,
				7               => RSS_MENU_L6,
				9               => RSS_MENU_L7,
				10             => RSS_MENU_L8,
				12              => RSS_MENU_L9,

			);
			

			$text = "
			<div>
				".varset($description[$type]).RSS_MENU_L1."<br />

				<div class='spacer'><a href='".e107::url('rss_menu', 'rss', $arr)."'><img src='".$path."images/rss2.png' alt='rss2.0' /></a></div>
				<div class='spacer'><a href='".e107::url('rss_menu', 'atom', $arr)."'><img src='".$path."images/rss4.png' alt='atom' /></a></div>
			</div>";

			$caption = RSS_MENU_L2;

		}

		e107::getRender()->tablerender($caption, $text, 'rss_menu');
	}
?>