<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_admin/notify.php,v $
 * $Revision: 1.10 $
 * $Date: 2009-11-12 14:30:07 $
 * $Author: marj_nl_fr $
 */

require_once('../class2.php');
if (!getperms('O')) 
{
	header('location:'.e_BASE.'index.php');
	exit;
}

include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_'.e_PAGE);

$e_sub_cat = 'notify';

require_once('auth.php');

require_once(e_HANDLER.'userclass_class.php');
require_once(e_HANDLER.'form_handler.php');

$emessage = &eMessage::getInstance();

$rs = new form;
$frm = new e_form;
$nc = new notify_config;
$uc = new user_class;

$uc->fixed_classes['email'] = 'Email Address =>';
$uc->text_class_link['email'] = 'email';

if (isset($_POST['update']))
{
	if($nc -> update())
	{
    	$message = LAN_UPDATED;
        $style = E_MESSAGE_SUCCESS;
	}
	else
	{
    	$message = LAN_UPDATED_FAILED;
		$style = E_MESSAGE_FAILED;
	}
	$emessage->add($message, $style);

 //	$ns -> tablerender($message,"<div style='text-align:center'>".$message."</div>");
}
$nc -> config();


class notify_config
{
	var $notify_prefs;
	var $changeList = array();

	function notify_config() 
	{
		global $sysprefs, $eArrayStorage, $tp, $sql,$pref;
		$this -> notify_prefs = $sysprefs -> get('notify_prefs');
		$this -> notify_prefs = $eArrayStorage -> ReadArray($this -> notify_prefs);

		// load every e_notify.php file.
		if($pref['e_notify_list'])
		{
	        foreach($pref['e_notify_list'] as $val)
			{
					if (!isset($this -> notify_prefs['plugins'][$val]))
					{
						$this -> notify_prefs['plugins'][$val] = TRUE;
						if (is_readable(e_PLUGIN.$val."/e_notify.php"))
						{
							require_once(e_PLUGIN.$val.'/e_notify.php');
							foreach ($config_events as $event_id => $event_text)
					   		{
								$this -> notify_prefs['event'][$event_id] = array('class' => '255', 'email' => '');
							}
							$recalibrate = true;
						}
					}
			}
		}


		if ($recalibrate) 
		{
			$s_prefs = $tp -> toDB($this -> notify_prefs);
			$s_prefs = $eArrayStorage -> WriteArray($s_prefs);
			$sql -> db_Update("core", "e107_value='".$s_prefs."' WHERE e107_name='notify_prefs'");
		}
	}

	function config()
	{
		global $ns, $rs, $frm, $emessage;

		$text = "<div style='text-align: center'>
		<div>".NT_LAN_2.":</div>
		<form action='".e_SELF."?results' method='post' id='scanform'>
		<fieldset id='core-notify-config'>
		<legend>".NU_LAN_1."</legend>
        <table cellpadding='0' cellspacing='0' class='adminform'>
        	<colgroup span='2'>
        		<col class='col-label' />
        		<col class='col-control' />
        	</colgroup>
		";

		$text .= $this -> render_event('usersup', NU_LAN_2);
		$text .= $this -> render_event('userveri', NU_LAN_3);
		$text .= $this -> render_event('login', NU_LAN_4);
		$text .= $this -> render_event('logout', NU_LAN_5);

		$text .= "</table></fieldset>
		<fieldset id='core-notify-2'>
        <legend>".NS_LAN_1."</legend>
        <table cellpadding='0' cellspacing='0' class='adminform'>
        	<colgroup span='2'>
        		<col class='col-label' />
        		<col class='col-control' />
        	</colgroup>";

		$text .= $this -> render_event('flood', NS_LAN_2);


		$text .= "</table></fieldset>
		<fieldset id='core-notify-3'>
        <legend>".NN_LAN_1."</legend>
        <table cellpadding='0' cellspacing='0' class='adminform'>
        	<colgroup span='2'>
        		<col class='col-label' />
        		<col class='col-control' />
        	</colgroup>";

		$text .= $this -> render_event('subnews', NN_LAN_2);
		$text .= $this -> render_event('newspost', NN_LAN_3);
		$text .= $this -> render_event('newsupd', NN_LAN_4);
		$text .= $this -> render_event('newsdel', NN_LAN_5);

		$text .= "</table></fieldset>
		<fieldset id='core-notify-4'>
        <legend>".NF_LAN_1."</legend>
        <table cellpadding='0' cellspacing='0' class='adminform'>
        	<colgroup span='2'>
        		<col class='col-label' />
        		<col class='col-control' />
        	</colgroup>";

		$text .= $this -> render_event('fileupload', NF_LAN_2);

		$text .= "</table>";

		foreach ($this -> notify_prefs['plugins'] as $plugin_id => $plugin_settings)
		{
            if(is_readable(e_PLUGIN.$plugin_id.'/e_notify.php'))
			{
				require(e_PLUGIN.$plugin_id.'/e_notify.php');
				$text .= "</fieldset>
				<fieldset id='core-notify-".str_replace(" ","_",$config_category)."'>
		        <legend>".$config_category."</legend>
		        <table cellpadding='0' cellspacing='0' class='adminform'>
		        	<colgroup span='2'>
		        		<col class='col-label' />
		        		<col class='col-control' />
		        	</colgroup>";
				foreach ($config_events as $event_id => $event_text)
				{
					$text .= $this -> render_event($event_id, $event_text);
				}
				$text .= "</table> ";
			}
		}

		$text .= "
		<div class='buttons-bar center'>";
        $text .= $frm->admin_button('update', LAN_UPDATE,'update');
		$text .= "
		</div>
		</fieldset>
		</form>
		</div>";

		$ns -> tablerender(NT_LAN_1,$emessage->render(). $text);
	}


	function render_event($id, $description) 
	{
		global $rs, $tp, $uc;
		$text .= "
			<tr>
				<td >".$description.":	</td>
				<td  class='nowrap'>
				".$uc->uc_dropdown('event['.$id.'][class]', $this -> notify_prefs['event'][$id]['class'],"nobody,main,admin,member,classes,email","onchange=\"mail_field(this.value,'event_".$id."');\" ");

			if($this -> notify_prefs['event'][$id]['class'] == 'email')
			{
            	$disp='display:visible';
				$value = $tp -> toForm($this -> notify_prefs['event'][$id]['email']);
			}
			else
			{
            	$disp = "display:none";
				$value= "";
			}

			$text .= "<input type='text' style='width:180px;$disp' class='tbox' id='event_".$id."' name='event[".$id."][email]' value=\"".$value."\" />\n";

		$text .= "</td>
		</tr>";
		return $text;
	}


	function update() 
	{
		global $sql, $pref, $tp, $eArrayStorage, $admin_log;
		$this->changeList = array();
		foreach ($_POST['event'] as $key => $value)
		{
			if ($this -> update_event($key))
			{
				$active = TRUE;
			}
		}
        if ($active)
		{
		   	$pref['notify'] = TRUE;
		}
		else
		{
		 	$pref['notify'] = FALSE;
		}
	  	save_prefs();
		$s_prefs = $tp -> toDB($this -> notify_prefs);
		$s_prefs = $eArrayStorage -> WriteArray($s_prefs);
		if($sql -> db_Update("core", "e107_value='".$s_prefs."' WHERE e107_name='notify_prefs'")!==FALSE)
		{
			$admin_log->logArrayAll('NOTIFY_01',$this->changeList);
			return TRUE;
		}
		else
		{
        	return FALSE;
		}

	}

	function update_event($id) 
	{
		$changed = FALSE;
		
		if ($this -> notify_prefs['event'][$id]['class'] != $_POST['event'][$id]['class'])
		{
			$this -> notify_prefs['event'][$id]['class'] = $_POST['event'][$id]['class'];
			$changed = TRUE;
		}
		if ($this -> notify_prefs['event'][$id]['email'] != $_POST['event'][$id]['email'])
		{
			$this -> notify_prefs['event'][$id]['email'] = $_POST['event'][$id]['email'];
			$changed = TRUE;
		}
		if ($changed)
		{
			$this->changeList[$id] = $this->notify_prefs['event'][$id]['class'].', '.$this->notify_prefs['event'][$id]['email'];
		}
		if ($this -> notify_prefs['event'][$id]['class'] != 255) 
		{
			return TRUE;
		} 
		else 
		{
			return FALSE;
		}
	}
}

require_once('footer.php');
function headerjs()
{

	$js = "
	<script type='text/javascript'>

    function mail_field(val,id)
	{
    	if(val == 'email')
		{
        	document.getElementById(id).style.display ='';
		}
        else
		{
        	document.getElementById(id).style.display ='none';
		}
	}

	</script>";

	return $js;
}
?>
