<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2016 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
*/



if (!defined('e107_INIT')) { exit; }

class pm_shortcodes extends e_shortcode
{

	private $pm = null;
	private $prefs = null;




	function __construct()
	{
		include_lan(e_PLUGIN.'pm/languages/'.e_LANGUAGE.'.php');
		require_once(e_PLUGIN."pm/pm_func.php");

		$this->pm = new pmbox_manager;

		$this->prefs = $this->pm->prefs();

	}



	function sc_pm_nav($parm='')
	{
		$tp = e107::getParser();

		if(!check_class($this->prefs['pm_class']))
		{
			return null;
		}

		$mbox = $this->pm->pm_getInfo('inbox');

		if(!empty($mbox['inbox']['new']))
		{
			$count = "<span class='label label-warning'>".$mbox['inbox']['new']."</span>";
			$icon = $tp->toGlyph('fa-envelope');
		}
		else
		{
			$icon = $tp->toGlyph('fa-envelope-o');
			$count = '';
		}


		$urlInbox = e107::url('pm','index','', array('query'=>array('mode'=>'inbox')));
		$urlOutbox = e107::url('pm','index','', array('query'=>array('mode'=>'outbox')));
		$urlCompose = e107::url('pm','index','', array('query'=>array('mode'=>'send')));

		return '<a class="dropdown-toggle" data-toggle="dropdown" href="#">'.$icon.$count.'</a>
		<ul class="dropdown-menu">
		<li>

			<a href="'.$urlInbox.'">'.LAN_PLUGIN_PM_INBOX.'</a>
			<a href="'.$urlOutbox.'">'.LAN_PLUGIN_PM_OUTBOX.'</a>
			<a href="'.$urlCompose.'">'.LAN_PM_35.'</a>

		</li>
		</ul>';

	}




	function sc_sendpm($parm='')
	{

		// global $sysprefs, $pm_prefs;
		// $pm_prefs = $sysprefs->getArray("pm_prefs");
		$pm_prefs = e107::getPlugPref('pm');

		$url = e107::url('pm','index').'?send.'.$parm;


		if(check_class($pm_prefs['pm_class']))
		{
		    if(deftrue('FONTAWESOME') && deftrue('BOOTSTRAP'))
		    {
		        $img =  e107::getParser()->toGlyph('fa-paper-plane','');
		        return  "<a class='btn btn-sm btn-default' href='".$url ."'>{$img} ".LAN_PM_35."</a>";
		    }


		    if(file_exists(THEME.'forum/pm.png'))
		    {
		           $img = "<img src='".THEME_ABS."forum/pm.png' alt='".LAN_PM."' title='".LAN_PM."' style='border:0' />";
		     }
		     else
		     {
		          $img = "<img src='".e_PLUGIN_ABS."pm/images/pm.png' alt='".LAN_PM."' title='".LAN_PM."' style='border:0' />";
		     }



			return  "<a href='".$url ."'>{$img}</a>";
		}
		else
		{
			return null;
		}




	}



}