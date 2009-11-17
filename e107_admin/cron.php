<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2001-2009 e107 Inc
|     http://e107.org/
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org/).
|
|     $Source: /cvs_backup/e107_0.8/e107_admin/cron.php,v $
|     $Revision: 1.17 $
|     $Date: 2009-11-17 20:47:13 $
|     $Author: e107steved $
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
	var $e_cron = array();

    function cron()
	{
		global $pref;
		$mes = $mes = e107::getMessage();
    	$this->cronAction = e_QUERY;
		
		$this->coreCrons['_system_cron'] = array(
			0 => array('name' => 'Test Email', 'function' => 'sendEmail', 'description' => 'Send a test email to '.$pref['siteadminemail'].'<br />Recommended to test the scheduling system.'),
			1 => array('name' => 'Mail Queue', 'function' => 'procEmailQueue', 'description' => 'Process mail queue'),
		//	1 => array('name'=>'User Purge', 'function' => 'userPurge', 'description'=>'Purge Unactivated Users'),
		//	2 => array('name'=>'User UnActivated', 'function' => 'userUnactivated', 'description'=>'Resend activation email to unactivated users.'),
		//	3 => array('name'=>'News Sticky', 'function' => 'newsPurge', 'description'=>'Remove Sticky News Items')		
		);		
		
				
		if(!vartrue($pref['e_cron_pwd']))
		{	
			$pwd = $this->setCronPwd();
		}
		
	

        if(isset($_POST['submit']))
		{
        	$this->cronSave();
		}
		
		$this->lastRefresh();
		$this->cronLoad();

		if(isset($_POST['save_prefs']))
		{
        	$this -> cronSavePrefs();
		}
		
		if(isset($_POST['execute']))
		{

			$class_func = key($_POST['execute']);
			$this -> cronExecute($class_func);
		}

		// Set Core Cron Options.
		

	
		

        // These core functions need to be put into e_BASE/cron.php  e.g. news_purge()

        if($this->cronAction == '' || $this->cronAction == 'main')
		{
			$this -> cronRenderPage();
		}
		
		

		if($this->cronAction == 'pref')
		{
        	$this -> cronRenderPrefs();
		}
	}


	function lastRefresh()
	{
		global $pref;
		e107::getCache()->CachePageMD5 = '_';
		$lastload = e107::getCache()->retrieve('cronLastLoad',FALSE,TRUE,TRUE);
		$mes = e107::getMessage();
		$ago = (time() - $lastload); 
		
		$active = ($ago < 125) ? TRUE : FALSE;
		$status = ($active) ? LAN_ENABLED : LAN_DISABLED; // "Enabled" : "Offline";

		$mes->add("Status: <b>".$status."</b>", E_MESSAGE_INFO);
		
		// print_a($pref['e_cron_pref']);	
		
		if($pref['e_cron_pref']) // grab cron
		{
			foreach($pref['e_cron_pref'] as $func=>$cron)
			{
		    	if($cron['active']==1)
				{
		        	$list[$func] = $cron;
				}
			}
		}
		$mes->add("Active Crons: <b>".count($list)."</b>", E_MESSAGE_INFO);			
		$mes->add("Last cron refresh was ".$ago.' seconds ago.', E_MESSAGE_INFO);

		//FIXME: for Windows, the is_executable() function only checks the file
		// extensions of exe, com, bat and cmd.
		if(!is_executable(e_BASE."cron.php"))
		{
				$mes->add("Please CHMOD /cron.php to 755" , E_MESSAGE_WARNING);	
		}
		elseif(!$active)
		{
				$setpwd_message = "Use the following Cron Command: <b style='color:black'>".$_SERVER['DOCUMENT_ROOT'].e_HTTP."cron.php ".$pref['e_cron_pwd']."</b><br />
				Using your server control panel (eg. cPanel,Plesk etc.) please create a crontab to run this command on your server every minute.";
				$mes->add($setpwd_message, E_MESSAGE_INFO);
		}
		

		
	}


function cronName($classname,$method)
{
	$tp = e107::getParser();
	
	foreach($this->e_cron as $class=>$val)
	{
		
		if($class == $classname)
		{
			foreach($val as $func)
			{
				if($func['function'] == $method)
				{
					return $tp->toHtml($func['name']);
				}
			}
		}
	}
}
	
	
	function cronExecute($class_func)
	{
		//TODO LANs
		list($class_name,$method_name) = explode("__",$class_func);
		$mes = e107::getMessage();
		
		if($class_name =='_system_cron')
		{
			require_once(e_HANDLER."cron_class.php");
		}
		
		$status =  $this->cronExecuteMethod($class_name,$method_name) ? E_MESSAGE_SUCCESS : E_MESSAGE_ERROR;
		$mes->add("Running <b>".$this->cronName($class_name,$method_name)."</b>", $status); 	

	}

    function cronSavePref()
	{
    	// Store the USERID with the password.
		// This way only the one password is needed, and the user login can be looked up in e_base/cron.php

	}

	function cronSave()
	{
		global $pref;
		
		$mes = e107::getMessage();
		$activeCount = 0;
		
    	foreach($_POST['cron'] as $key=>$val)
		{
	    	if(!$val['active'])
			{
	         	$val['active'] = 0;
			}
			else
			{
				$activeCount++;
			}

			$t['minute'] 	= implode(",",$_POST['tab'][$key]['minute']);
			$t['hour'] 		= implode(",",$_POST['tab'][$key]['hour']);
			$t['day']		= implode(",",$_POST['tab'][$key]['day']);
	        $t['month']		= implode(",",$_POST['tab'][$key]['month']);
			$t['weekday']	= implode(",",$_POST['tab'][$key]['weekday']);

			$val['tab'] = implode(" ",$t);
			$tabs .= $val['tab']."<br />";
			
			list($class,$func) = explode("__",$key);
			
			$val['function'] = $func;
			$val['class']	= $class;
			$val['path']	= $class;
			
	    	$cron[$key] = $val;
		}

		$pref['e_cron_pref'] = $cron;
		
		if(!vartrue($pref['e_cron_pwd']) || varset($_POST['generate_pwd']))
		{	
			$pwd = $this->setCronPwd();
		
			$setpwd_message = "Use the following Cron Command:<br /><b style='color:black'>".$_SERVER['DOCUMENT_ROOT'].e_HTTP."cron.php ".$pwd."</b><br />
			This cron command is unique and will not be displayed again. Please copy and paste it into your webserver cron area to be run every minute of every day.";
			$mes->add($setpwd_message, E_MESSAGE_WARNING);
		}
		
		
	//	print_a($pref['e_cron_pref']);

		if(save_prefs())
		{
	  		$mes->add(LAN_SETSAVED, E_MESSAGE_SUCCESS);
			$mes->add($activeCount." Cron(s) Active", E_MESSAGE_SUCCESS);
		}
		else
		{
        	$mes->add("There was a problem saving your settings.", E_MESSAGE_ERROR);
		}

	}
	

function setCronPwd()
{
		global $pref;
		
		require_once (e_HANDLER.'user_handler.php');
			$userMethods = new UserHandler;		
			$newpwd = $userMethods->generateRandomString('*^*#.**^*');
			$newpwd = sha1($newpwd.time());			
			$pref['e_cron_pwd'] = $newpwd; 
			
	return save_prefs();
	
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

	function cronLoad()
	{
		global $pref;
		
		$new_cron = array();
		
		if(vartrue($pref['e_cron_list']))
		{	
			
			foreach($pref['e_cron_list'] as $key=>$val)
			{
				$eplug_cron = array();
				if(is_readable(e_PLUGIN.$key."/e_cron.php"))
				{
					include_once(e_PLUGIN.$key."/e_cron.php");
					
					$class_name = $key."_cron";
					$method_name = 'config';
					
					if($array = $this->cronExecuteMethod($class_name,$method_name,'data'))
					{
						$new_cron[] = $array;
					}
							
				}
			}
		}
		$this->e_cron = $this->coreCrons;
		$this->e_cron['_plugins'] = $new_cron;
		//print_a($this->e_cron);
	}



// ----------- Grab All e_cron parameters -----------------------------------

	function cronRenderPage()
	{
    	global $pref;
    	$cronpref = $pref['e_cron_pref'];
		$ns = e107::getRender();
		$frm = e107::getForm();
		$mes = e107::getMessage();
	
		$e_cron = $this->e_cron;
		
		
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
			   <th>Run Now</th>
			   </tr>
		   </thead>
		   <tbody>";

	foreach($e_cron as $plug=>$cfg)
	{
		foreach($cfg as $class=>$cron)
		{
		    $c = $plug.'__'. $cron['function']; // class and function. 
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
						$minute = array();
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
						$hour = array();
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
		       	<td class='center'>";
		        $checked = ($cronpref[$c]['active'] == 1) ? "checked='checked'" : "";
				$text .= "<input type='checkbox' name='cron[$c][active]' value='1' $checked />
		      	</td>
				
				<td class='center'>".$frm->admin_button('execute['.$c.']', 'Run Now')."</td>
		   	</tr>";
		}
	}
		$text .= "

		   <tr >
		   <td colspan='9' class='center'>
		   <div class='center buttons-bar'>";
		 //  $text .= "<input class='button' type='submit' name='submit' value='".LAN_SAVE."' />";
		   $text .=  $frm->admin_button('submit', LAN_SAVE, $action = 'update');
		   $text .= $frm->checkbox_switch('generate_pwd',1,'','Generate new cron command');
		   $text .= "</div></td>
		   </tr>
		   </tbody>
		   </table>
		   </form>
		   </div>";

		   $ns -> tablerender(PAGE_NAME, $mes->render() . $text);
	}

	function cronOptions()
	{
		$e107 = &e107::getInstance();

		$var['main']['text'] = PAGE_NAME;
		$var['main']['link'] = e_SELF;
/*
		$var['pref']['text'] = LAN_PREFS;
		$var['pref']['link'] = e_SELF."?pref";
		$var['pref']['perm'] = "N";

	*/	$action = ($this->cronAction) ? $this->cronAction : "main";

		e_admin_menu(PAGE_NAME, $action, $var);
	}


	function cronExecuteMethod($class_name,$method_name,$return='boolean')
	{
		$mes = e107::getMessage();

		if(class_exists($class_name))
		{
			$obj = new $class_name;
			if(method_exists($obj,$method_name))
			{
				$mes->add("Executing config function <b>".$key." : ".$method_name."()</b>", E_MESSAGE_DEBUG);
				if($return == 'boolean')
				{
					call_user_func(array($obj,$method_name));
					return TRUE;
				}
				else
				{
					return call_user_func(array($obj,$method_name));
				}
			}
			else
			{
				$mes->add("Config function <b>".$method_name."()</b> NOT found.", E_MESSAGE_DEBUG);					
			}	
		}
		return FALSE;
	}
}

function cron_adminmenu()
{
	global $cron;
	$cron->cronOptions();
}


?>