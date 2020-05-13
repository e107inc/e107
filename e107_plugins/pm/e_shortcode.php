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
		e107::includeLan(e_PLUGIN.'pm/languages/'.e_LANGUAGE.'.php');
		require_once(e_PLUGIN."pm/pm_func.php");

		$this->pm = new pmbox_manager();

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
			<a href="'.$urlCompose.'">'.LAN_PLUGIN_PM_NEW.'</a>

		</li>
		</ul>';

	}


	/**
	 * @param array|string $parm - User ID or array of values (see below)
	 * @param int $parm['user']
	 * @param string $parm['glyph']
	 * @param string $parm['class']
	 *
	 * @return null|string
	 */
	function sc_sendpm($parm=null)
	{

		// global $sysprefs, $pm_prefs;
		// $pm_prefs = $sysprefs->getArray("pm_prefs");

		if(is_string($parm))
		{
			$parm = array('user'=>$parm);
		}
		
		$pm_prefs = e107::getPlugPref('pm');

		$url = e107::url('pm','index').'?send.'.$parm['user'];

		require_once(e_PLUGIN."pm/pm_class.php");

		$pm = new private_message;

		$glyph  = empty($parm['glyph']) ? 'fa-paper-plane' : $parm['glyph'];
		$class  = empty($parm['class']) ? 'btn btn-sm btn-default btn-secondary' : $parm['class'];


		if(check_class($pm_prefs['pm_class']) && $pm->canSendTo($parm['user'])) // check $this->pmPrefs['send_to_class'].
		{
		    if(deftrue('FONTAWESOME') && deftrue('BOOTSTRAP'))
		    {
		        $img =  e107::getParser()->toGlyph($glyph,'');
		        return  "<a class='".$class."' href='".$url ."'>{$img} ".LAN_PLUGIN_PM_NEW."</a>";
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
