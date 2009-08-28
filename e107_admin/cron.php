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
|     $Source: /cvs_backup/e107_0.8/e107_admin/cron.php,v $
|     $Revision: 1.3 $
|     $Date: 2009-08-28 16:11:00 $
|     $Author: marj_nl_fr $
+----------------------------------------------------------------------------+
*/
require_once('../class2.php');
if (!getperms('U'))
{
  header('location:'.e_BASE.'index.php');
  exit;
}

include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_'.e_PAGE);

$e_sub_cat = 'cron';

require_once('auth.php');
require_once (e_HANDLER.'message_handler.php');
require_once(e_HANDLER."form_handler.php");
$frm = new e_form(true);
$cron = new cron;

require_once(e_ADMIN."footer.php");
exit;

class cron
{
	var $coreCrons = array();
    var $cronAction;

    function cron()
	{

    	$this->cronAction = e_QUERY;

        if(isset($_POST['submit']))
		{
        	$this -> cronSave();
		}

		if(isset($_POST['save_prefs']))
		{
        	$this -> cronSavePrefs();
		}

		// Set Core Cron Options.

		$this->coreCrons[] = array('name'=>'User Purge','function' => 'user_purge', 'description'=>'Purge Unactivated Users');
		$this->coreCrons[] = array('name'=>'User UnActivated','function' => 'user_unactivated', 'description'=>'Resend activation email to unactivated users.');
		$this->coreCrons[] = array('name'=>'News Sticky','function' => 'news_purge', 'description'=>'Remove Sticky News Items');

        // These core functions need to be put into e_BASE/cron.php  ie. news_purge()

        if($this->cronAction == "" || $this->cronAction == "main")
		{
			$this -> cronRenderPage();
		}

		if($this->cronAction == "pref")
		{
        	$this -> cronRenderPrefs();
		}
	}

    function cronSavePref()
	{
    	// Store the USERID with the password.
		// This way only the one password is needed, and the user login can be looked up in e_base/cron.php

	}

	function cronSave()
	{
		global $pref;
    	foreach($_POST['cron'] as $key=>$val)
		{
	    	if(!$val['active'])
			{
	         	$val['active'] = 0;
			}

			$t['minute'] 	= implode(",",$_POST['tab'][$key]['minute']);
			$t['hour'] 		= implode(",",$_POST['tab'][$key]['hour']);
			$t['day']		= implode(",",$_POST['tab'][$key]['day']);
	        $t['month']		= implode(",",$_POST['tab'][$key]['month']);
			$t['weekday']	= implode(",",$_POST['tab'][$key]['weekday']);

			$val['tab'] = implode(" ",$t);
			$tabs .= $val['tab']."<br />";
	    	$cron[$key] = $val;
		}

		$pref['e_cron_pref'] = $cron;
        $emessage = &eMessage::getInstance();
		if(save_prefs())
		{
	  		$emessage->add(LAN_SETSAVED, E_MESSAGE_SUCCESS);
		}
		else
		{
        	$emessage->add("There was a problem saving your settings.", E_MESSAGE_ERROR);
		}

	}
// --------------------------------------------------------------------------
	function cronRenderPrefs()
	{
        global $frm,$ns;

	 	$text = "<div style='text-align:center'>
	    <form method='post' action='".e_SELF."' id='linkform'>
	    <table class='adminlist'>
	    <tr>
	    <td style='width:30%'>Cron Password</td>
	    <td style='width:70%'>
	    	".$frm->password('cron_password',100)."
	    </td>
	    </tr>

	    <tr>
	    <td style='width:30%'>bcc: </td>
	    <td style='width:70%'>
	    <input type='text' name='name2' class='tbox' style='width:80%' value='' />
	    </td>
	    </tr>

	    <tr style='vertical-align:top'>
	    <td colspan='2' class='center buttons-bar'>";
	    $text .= $frm->admin_button('save_prefs',LAN_SAVE, 'update');
	    $text .= "</td>
	    </tr>
	    </table>
	    </form>
	    </div>";

	    $ns -> tablerender(LAN_PREFS, $text);

    }





// ----------- Grab All e_cron parameters -----------------------------------

	function cronRenderPage()
	{
    	global $pref,$ns,$frm;
    	$cronpref = $pref['e_cron_pref'];

	  //	$count = 0;

		$e_cron = $this->coreCrons;
		$count = count($this->coreCrons);

		foreach($pref['e_cron_list'] as $key=>$val)
		{
			$eplug_cron = array();
			if(is_readable(e_PLUGIN.$key."/e_cron.php"))
			{
				require_once(e_PLUGIN.$key."/e_cron.php");
				foreach($eplug_cron as $v)
				{
					$e_cron[$count]['name'] 		= $v['name'];
					$e_cron[$count]['function'] 	= $v['function'];
					$e_cron[$count]['description'] 	= $v['description'];
					$e_cron[$count]['path'] 		= $key;
					$count++;
				}
			}

		}

	// ----------------------  List All Functions -----------------------------

		$text = "<div style='text-align:center'>
		   <form method='post' action='".e_SELF."' id='cronform'>
		   <table class='adminlist'>
		   <colgroup span='8'>
			   	<col></col>
				<col></col>
				<col></col>
				<col></col>
				<col></col>
				<col></col>
				<col></col>
				<col></col>
			</colgroup>
		   <thead>
		   	<tr>
			   <th>".LAN_CRON_1."</th>
			   <th>".LAN_CRON_2."</th>
			   <th>".LAN_CRON_3."</th>
			   <th>".LAN_CRON_4."</th>
			   <th>".LAN_CRON_5."</th>
			   <th>".LAN_CRON_6."</th>
			   <th>".LAN_CRON_7."</th>
			   <th>".LAN_CRON_8."</th>
			   </tr>
		   </thead>
		   <tbody>";


		foreach($e_cron as $cron)
		{
		    $c = $cron['function'];
		    $sep = array();

			list($sep['minute'],$sep['hour'],$sep['day'],$sep['month'],$sep['weekday']) = explode(" ",$cronpref[$c]['tab']);

		    foreach($sep as $key=>$value)
			{
		    	if($value=="")
				{
		        	$sep[$key] = "*";
				}
			}

		    $minute 	= explode(",",$sep['minute']);
			$hour 		= explode(",",$sep['hour']);
		    $day 		= explode(",",$sep['day']);
		    $month 		= explode(",",$sep['month']);
			$weekday	= explode(",",$sep['weekday']);

			$min_options = array(
				"*" => LAN_CRON_11,
				"0,2,4,6,8,10,12,14,16,18,20,22,24,26,28,30,32,34,36,38,40,42,44,46,48,50,52,54,56,58" => LAN_CRON_12,
				"0,5,10,15,20,25,30,35,40,45,50,55" => LAN_CRON_13,
				"0,10,20,30,40,50" => LAN_CRON_14,
				"0,15,30,45" => LAN_CRON_15
			);

			$hour_options = array(
				"*" => LAN_CRON_16,
				"0,2,4,6,8,10,12,14,16,18,20,22" => LAN_CRON_17,
				"0,3,6,9,12,15,18,21" => LAN_CRON_18,
				"0,6,12,18" => LAN_CRON_19
			);

			$text .= "<tr>
		     <td>".$cron['name']."</td>
		   	<td>".$cron['description']."</td>
		   	<td>
			<input type='hidden'  name='cron[$c][path]' value='".$cron['path']."' />
		   		<select class='tbox' style='height:70px' multiple='multiple' name='tab[$c][minute][]'>\n";

		        foreach($min_options as $key=>$val)
				{
					if($sep['minute'] == $key)
					{
						$sel = "selected='selected'";
						$minute = "";
					}
					else
					{
						$sel =  "";
					}
		        	$text .= "<option value='$key' $sel>".$val."</option>\n";
		    	}


				for ($i=0; $i<=59; $i++)
				{
					$sel = (in_array(strval($i),$minute)) ? "selected='selected'" : "";
			    	$text .= "<option value='$i' $sel>".$i."</option>\n";
			    }
				$text .= "</select>
			</td>
		   	<td>
				<select class='tbox' style='height:70px' multiple='multiple' name='tab[$c][hour][]'>
				\n";

		        foreach($hour_options as $key=>$val)
				{
					if($sep['hour'] == $key)
					{
						$sel = "selected='selected'";
						$hour = "";
					}
					else
					{
						$sel =  "";
					}
		        	$text .= "<option value='$key' $sel>".$val."</option>\n";
		    	}

				for ($i=0; $i<=23; $i++)
				{
					$sel = (in_array(strval($i),$hour)) ? "selected='selected'" : "";
					$diz = mktime($i,00,00,1,1,2000);
			    	$text .= "<option value='$i' $sel>".$i." - ".date("g A",$diz)."</option>\n";
			    }
				$text .= "</select>
		 	</td>
		   	<td>
				<select class='tbox' style='height:70px' multiple='multiple' name='tab[$c][day][]'>\n";

				$sel_day = ($day[0] == "*") ? "selected='selected'" : "";

				$text .= "<option value='*' {$sel_day}>".LAN_CRON_20."</option>\n"; // Every Day
				for ($i=1; $i<=31; $i++)
				{
					$sel = (in_array($i,$day)) ? "selected='selected'" : "";
			    	$text .= "<option value='$i' $sel>".$i."</option>\n";
			    }
				$text .= "</select>
			</td>
		   	<td>
				<select class='tbox' style='height:70px' multiple='multiple' name='tab[$c][month][]'>\n";

				$sel_month = ($month[0] == "*") ? "selected='selected'" : "";
				$text .= "<option value='*' $sel_month>".LAN_CRON_21."</option>\n"; // Every Month

				for ($i=1; $i<=12; $i++)
				{
					$sel = (in_array($i,$month)) ? "selected='selected'" : "";
					$diz = mktime(00,00,00,$i,1,2000);
			    	$text .= "<option value='$i' $sel>".strftime("%B",$diz)."</option>\n";
			    }
				$text .= "</select>
			</td>
		   	<td>
		    	<select class='tbox' style='height:70px' multiple='multiple' name='tab[$c][weekday][]'>\n";

				$sel_weekday = ($weekday[0] == "*") ? "selected='selected'" : "";
				$text .= "<option value='*' $sel_weekday>".LAN_CRON_22."</option>\n"; // Every Week Day.
				$days = array(LAN_SUN,LAN_MON,LAN_TUE,LAN_WED,LAN_THU,LAN_FRI,LAN_SAT);

				for ($i=0; $i<=6; $i++)
				{
					$sel = (in_array(strval($i),$weekday)) ? "selected='selected'" : "";
			    	$text .= "<option value='$i' $sel>".$days[$i]."</option>\n";
			    }
				$text .= "</select>
			</td>
		       	<td style='text-align:center'>";
		        $checked = ($cronpref[$c]['active'] == 1) ? "checked='checked'" : "";
				$text .= "<input type='checkbox' name='cron[$c][active]' value='1' $checked />
		      	</td>
		   	</tr>";
		}

		$text .= "

		   <tr >
		   <td colspan='8' class='center'>
		   <div class='center buttons-bar'>";
		 //  $text .= "<input class='button' type='submit' name='submit' value='".LAN_SAVE."' />";
		   $text .=  $frm->admin_button('submit', LAN_SAVE, $action = 'update');
		   $text .= "</div></td>
		   </tr>
		   </tbody>
		   </table>
		   </form>
		   </div>";

           $emessage = &eMessage::getInstance();
		   $ns -> tablerender(PAGE_NAME, $emessage->render() . $text);
	}

	function cronOptions()
	{
		$e107 = &e107::getInstance();

		$var['main']['text'] = PAGE_NAME;
		$var['main']['link'] = e_SELF;

		$var['pref']['text'] = LAN_PREFS;
		$var['pref']['link'] = e_SELF."?pref";
		$var['pref']['perm'] = "N";

		$action = ($this->cronAction) ? $this->cronAction : "main";

		e_admin_menu(PAGE_NAME, $action, $var);
	}
}


function cron_adminmenu()
{
	global $cron;
	$cron->cronOptions();
}


?>