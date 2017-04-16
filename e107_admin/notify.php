<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

require_once('../class2.php');

if (!getperms('O')) 
{
	e107::redirect('admin');
	exit;
}

e107::coreLan('notify', true);

class plugin_notify_admin extends e_admin_dispatcher
{

	protected $modes = array(
		'main'		=> array('controller' => 'plugin_notify_admin_ui',
		'path' 		=> null,
		'ui' 		=> 'plugin_notify_admin_form_ui', 'uipath' => null)
	);

	protected $adminMenu = array(
		'main/config' 		=> array('caption'=> "Email", 'perm' => '0'),
//		'main/push'		=> array('caption'=> "Push (experimental)", 'perm' => '0')
	);

	protected $adminMenuAliases = array(
		'main/edit'	=> 'main/list'
	);


	protected $adminMenuIcon = 'e-notify-24';

	/**
	 * Navigation menu title
	 * @var string
	 */
	protected $menuTitle = NT_LAN_1;

	function init()
	{
		if(e_DEBUG !== true)
		{
			unset($this->adminMenu['main/push']);
		}



	}
}



class plugin_notify_admin_ui extends e_admin_ui
{
		protected $pluginTitle = NT_LAN_1;

		protected $pluginName = 'core';

		protected $table = "";

		protected $listQry = "";

		protected $pid = "notify_id";

		protected $perPage = 20;

		protected $batchDelete = true;

		//	protected $displaySettings = array();

		//	protected $disallowPages = array('main/create', 'main/prefs');


    	protected  $fields = array();

		//required - default column user prefs
		protected $fieldpref = array();


		// optional, if $pluginName == 'core', core prefs will be used, else e107::getPluginConfig($pluginName);
		protected $prefs = array(
	/*		'pref_type'	   				=> array('title'=> 'type', 'type'=>'text', 'data' => 'string', 'validate' => true),
			'pref_folder' 				=> array('title'=> 'folder', 'type' => 'boolean', 'data' => 'integer'),
			'pref_name' 				=> array('title'=> 'name', 'type' => 'text', 'data' => 'string', 'validate' => 'regex', 'rule' => '#^[\w]+$#i', 'help' => 'allowed characters are a-zA-Z and underscore')*/
		);

		var $notify_prefs;
		var $changeList = array();
		var $pluginConfig = array();

		function init()
		{


			if(!empty($_POST['update']))
			{
				if($this-> update())
				{
			   //     e107::getMessage()->addSuccess(LAN_UPDATED);
				}
				else
				{
					e107::getMessage()->addError(LAN_UPDATED_FAILED);
				}

			}


			$pref 	= e107::getPref();
			$this->notify_prefs = e107::getConfig('notify')->getPref();

			$this->prefCleanup();
			$this->test();

			$recalibrate = FALSE;

			// load every e_notify.php file.
			if($pref['e_notify_list'])
			{
		        foreach($pref['e_notify_list'] as $val) // List of available e_notify.php files.
				{
					//	if (!isset($this->notify_prefs['plugins'][$val]))
						{

							$this -> notify_prefs['plugins'][$val] = TRUE;

							if (is_readable(e_PLUGIN.$val."/e_notify.php"))
							{
								require_once(e_PLUGIN.$val.'/e_notify.php');

								if(class_exists($val."_notify")) // new v2.x
								{
									$legacy = 0; // Newe.
									$config_events = array();

									$data = e107::callMethod($val."_notify", 'config');

									$config_category = str_replace("_menu","",ucfirst($val))." ".LAN_NOTIFY_01;

									foreach($data as $v)
									{
										$func = $v['function'];
										$config_events[$func] = $v['name'];
									}

								}
								else
								{
									$legacy = 1;	// Legacy Mode.
								}

						//		foreach ($config_events as $event_id => $event_text)
						//   		{
								//	$this -> notify_prefs['event'][$event_id] = array('class' => '255', 'email' => '', 'plugin'=> $val);

						//		}
								$this->pluginConfig[$val] = array('category' => $config_category, 'events' => $config_events, 'legacy'=> $legacy);
								$recalibrate = true;
							}
						}
				}
			}

		//	print_a($this->pluginConfig);

			if ($recalibrate)
			{
			//	$s_prefs = $tp -> toDB($this -> notify_prefs);
			//	$s_prefs = $eArrayStorage -> WriteArray($s_prefs);
			//	$sql -> db_Update("core", "e107_value='".$s_prefs."' WHERE e107_name='notify_prefs'");
			}
		}





		function prefCleanup()
		{
			$oldPrefs = e107::getEvent()->oldCoreList();
			$curData = $this->notify_prefs['event'];

			foreach($curData as $k=>$v)
			{
				if(isset($oldPrefs[$k]))
				{
					$newKey = $oldPrefs[$k];
					$this->notify_prefs['event'][$newKey] = $v;
					unset($this->notify_prefs['event'][$k]);
				}

			}

		}




		function pushPage()
		{

			e107::getMessage()->addInfo("Under Construction");




		}


	function test()
	{
		if(!empty($_POST['test']))
		{
			$id = key( $_POST['test']);
			$exampleData = array('id'=>'Test for '.$id, 'data'=>'example data'	);
			e107::getMessage()->addSuccess('Triggering: '.$id);
			e107::getEvent()->trigger($id, $exampleData);
		}


	}



	function configPage()
	{
		//global $ns, $rs, $frm, $emessage;
		$ns = e107::getRender();
		$frm = e107::getForm();
		$mes = e107::getMessage();


		$events = e107::getEvent()->coreList();
		$tab = array();

		foreach($events as $k=>$cat)
		{
			$text = " <table class='table adminform'>
        	<colgroup>
        		<col class='col-label' />
        		<col class='col-control' />
        	</colgroup>";

			foreach($cat as $c=>$ev)
			{
				$text .= $this -> render_event($c, $ev);
			}
			$text .= "</table>";

			$caption = str_replace("_menu","",ucfirst($k))." ".LAN_NOTIFY_01;

			$tab[] = array('caption'=>$caption, 'text' => $text);
		}

		if(!empty($this->notify_prefs['plugins']))
		{

			foreach ($this->notify_prefs['plugins'] as $plugin_id => $plugin_settings)
			{
	            if(is_readable(e_PLUGIN.$plugin_id.'/e_notify.php'))
				{
					$config_category = $this->pluginConfig[$plugin_id]['category'];
					$legacy = $this->pluginConfig[$plugin_id]['legacy'];

					$text = "<table class='table adminform'>
			        	<colgroup>
			        		<col class='col-label' />
			        		<col class='col-control' />
			        	</colgroup>";
					;

					foreach ($this->pluginConfig[$plugin_id]['events'] as $event_id => $event_text)
					{
						$text .= $this->render_event($event_id, $event_text, $plugin_id, $legacy);
					}

					$text .= "</table>\n";

					$tab[] = array('caption'=> $config_category, 'text'=> $text);
				}
			}
		}



		$text2 = $frm->open('scanform', 'post', e_REQUEST_URL); // <form action='".e_SELF."?results' method='post' id='scanform'>
		$text2 .= $frm->tabs($tab);
		$text2 .= "<div class='buttons-bar center'>". $frm->admin_button('update', LAN_UPDATE,'update') . "</div>";
		$text2 .= $frm->close();


	//	$ns -> tablerender(NT_LAN_1, $mes->render() . $text2);

		return $text2;
//	return;



	}


	function render_event($id, $description, $include='', $legacy = 0)
	{
		$tp = e107::getParser();
		$frm = e107::getForm();
		$uc = e107::getUserClass();
		$uc->fixed_classes['email'] = NM_LAN_3;
		$uc->text_class_link['email'] = 'email';


		if(defined($description))
		{
			$description = constant($description);
		}



		$highlight = varset($_GET['type']) == $id ? " class='text-warning'" : '';

		$text = "
			<tr>
				<td title='".$id."'><span{$highlight}>".$description.":</span></td>";



		if(e_DEBUG)
		{
			$text .= "<td>".$id."</td>";
		}

				$text .= "
				<td  class='form-inline nowrap'>
				".$uc->uc_dropdown('event['.$id.'][class]', varset($this->notify_prefs['event'][$id]['class'], e_UC_NOBODY), "nobody,main,admin,member,classes,email","onchange=\"mail_field(this.value,'event_".$id."');\" ");

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

			$text .= "<input class='form-control' type='text' style='width:200px;$disp' class='tbox' id='event_".$id."' name='event[".$id."][email]' value=\"".$value."\" />\n";

		$text .= $frm->hidden("event[".$id."][include]", $include);
		$text .= $frm->hidden("event[".$id."][legacy]", $legacy); // function or method

		if(isset($this->notify_prefs['event'][$id]['class']) && $this->notify_prefs['event'][$id]['class'] != e_UC_NOBODY)
		{
			$text .= $frm->button('test['.$id.']', $id, 'confirm', LAN_TEST);
		}


		$text .= "</td>";




		$text .= "
		</tr>";
		return $text;
	}


	function update()
	{
		$this->changeList = array();

		$active = false;

		foreach ($_POST['event'] as $key => $value)
		{
			if ($this -> update_event($key))
			{
				$active = true;
			}
		}

	//	print_a($this->notify_prefs);
		/*
		$s_prefs = $tp -> toDB($this -> notify_prefs);
		$s_prefs = $eArrayStorage -> WriteArray($s_prefs);
		if($sql -> db_Update("core", "e107_value='".$s_prefs."' WHERE e107_name='notify_prefs'")!==FALSE)
		*/

		e107::getConfig()->set('notify',$active)->save(true,true,false);
		e107::getConfig('notify')->updatePref($this->notify_prefs);
		if (e107::getConfig('notify')->save(FALSE))
		{
			// e107::getAdminLog()->logArrayAll('NOTIFY_01',$this->changeList);
			return true;
		}
		else
		{
        	return false;
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

		$this -> notify_prefs['event'][$id]['include'] 	= $_POST['event'][$id]['include'];
		$this -> notify_prefs['event'][$id]['legacy'] 	= $_POST['event'][$id]['legacy'];

		unset($this -> notify_prefs['event'][$id]['plugin']);
		unset($this -> notify_prefs['event'][$id]['type']);

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

class plugin_notify_admin_form_ui extends e_admin_form_ui
{



}


new plugin_notify_admin();


require_once(e_ADMIN."auth.php");

e107::getAdminUI()->runPage();

require_once(e_ADMIN."footer.php");

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

exit;

























$e_sub_cat = 'notify';

require_once('auth.php');
require_once(e_HANDLER.'userclass_class.php');

$frm = e107::getForm();

$nc = new notify_config;

$uc = new user_class;
$mes = e107::getMessage();

if(!empty($_GET['iframe']))
{
	define('e_IFRAME', true);
}



if (isset($_POST['update']))
{
	if($nc -> update())
	{
    	//$message = LAN_UPDATED;
        //$style = E_MESSAGE_SUCCESS;
        //$mes->addSuccess(LAN_UPDATED);
	}
	else
	{
    	//$message = LAN_UPDATED_FAILED;
		//$style = E_MESSAGE_FAILED;
		$mes->addError(LAN_UPDATED_FAILED);
	}
	//$emessage->add($message, $style);

 //	$ns -> tablerender($message,"<div style='text-align:center'>".$message."</div>");
}

$nc -> config();


class notify_config
{
	var $notify_prefs;
	var $changeList = array();
	var $pluginConfig = array();

	function __construct() 
	{
		$pref 	= e107::getPref();
		$this->notify_prefs = e107::getConfig('notify')->getPref();

		$this->prefCleanup();
		$this->test();

		$recalibrate = FALSE;

		// load every e_notify.php file.
		if($pref['e_notify_list'])
		{
	        foreach($pref['e_notify_list'] as $val) // List of available e_notify.php files. 
			{
				//	if (!isset($this->notify_prefs['plugins'][$val]))
					{

						$this -> notify_prefs['plugins'][$val] = TRUE;
						
						if (is_readable(e_PLUGIN.$val."/e_notify.php"))
						{
							require_once(e_PLUGIN.$val.'/e_notify.php');
							
							if(class_exists($val."_notify")) // new v2.x 
							{
								$legacy = 0; // Newe. 
								$config_events = array();
								
								$data = e107::callMethod($val."_notify", 'config');
								
								$config_category = str_replace("_menu","",ucfirst($val))."  ".LAN_NOTIFY_01;
								
								foreach($data as $v)
								{
									$func = $v['function'];
									$config_events[$func] = $v['name'];	
								}
								
							}
							else
							{
								$legacy = 1;	// Legacy Mode. 
							}
							
					//		foreach ($config_events as $event_id => $event_text)
					//   		{
							//	$this -> notify_prefs['event'][$event_id] = array('class' => '255', 'email' => '', 'plugin'=> $val);
								
					//		}
							$this->pluginConfig[$val] = array('category' => $config_category, 'events' => $config_events, 'legacy'=> $legacy);
							$recalibrate = true;
						}
					}
			}
		}
		
	//	print_a($this->pluginConfig);
		
		if ($recalibrate) 
		{
		//	$s_prefs = $tp -> toDB($this -> notify_prefs);
		//	$s_prefs = $eArrayStorage -> WriteArray($s_prefs);
		//	$sql -> db_Update("core", "e107_value='".$s_prefs."' WHERE e107_name='notify_prefs'");
		}
	}

	function prefCleanup()
	{
		$oldPrefs = e107::getEvent()->oldCoreList();
		$curData = $this->notify_prefs['event'];

		foreach($curData as $k=>$v)
		{
			if(isset($oldPrefs[$k]))
			{
				$newKey = $oldPrefs[$k];
				$this->notify_prefs['event'][$newKey] = $v;
				unset($this->notify_prefs['event'][$k]);	
			}		
		
		}	
					
	}
		

	function test()
	{
		if(!empty($_POST['test']))
		{
			$id = key( $_POST['test']);
			$exampleData = array('id'=>'Test for '.$id, 'data'=>'example data'	);
			e107::getMessage()->addSuccess('Triggering: '.$id);
			e107::getEvent()->trigger($id, $exampleData);
		}


	}
		
		

	function config()
	{
		//global $ns, $rs, $frm, $emessage;
		$ns = e107::getRender();
		$frm = e107::getForm();
		$mes = e107::getMessage();


		$events = e107::getEvent()->coreList(); 
		$tab = array(); 
		
		foreach($events as $k=>$cat)
		{
			$text = " <table class='table adminform'>
        	<colgroup>
        		<col class='col-label' />
        		<col class='col-control' />
        	</colgroup>";
        	
			foreach($cat as $c=>$ev)
			{
				$text .= $this -> render_event($c, $ev);		
			}	
			$text .= "</table>";
			
			$caption = str_replace("_menu","",ucfirst($k))." ".LAN_NOTIFY_01;
			
			$tab[] = array('caption'=>$caption, 'text' => $text);
		}
	
		if(!empty($this->notify_prefs['plugins']))
		{
	
			foreach ($this->notify_prefs['plugins'] as $plugin_id => $plugin_settings)
			{
	            if(is_readable(e_PLUGIN.$plugin_id.'/e_notify.php'))
				{
					$config_category = $this->pluginConfig[$plugin_id]['category'];
					$legacy = $this->pluginConfig[$plugin_id]['legacy'];
						
					$text = "<table class='table adminform'>
			        	<colgroup>
			        		<col class='col-label' />
			        		<col class='col-control' />
			        	</colgroup>";
					;
	
					foreach ($this->pluginConfig[$plugin_id]['events'] as $event_id => $event_text)
					{
						$text .= $this->render_event($event_id, $event_text, $plugin_id, $legacy);
					}
					
					$text .= "</table>\n";
					
					$tab[] = array('caption'=> $config_category, 'text'=> $text); 
				}
			}
		}
	
	
	
		$text2 = $frm->open('scanform', 'post', e_REQUEST_URL); // <form action='".e_SELF."?results' method='post' id='scanform'>
		$text2 .= $frm->tabs($tab); 
		$text2 .= "<div class='buttons-bar center'>". $frm->admin_button('update', LAN_UPDATE,'update') . "</div>";
		$text2 .= $frm->close(); 
		

		$ns -> tablerender(NT_LAN_1, $mes->render() . $text2);


	return; 
	

// <div>".NT_LAN_2.":</div>
	/*
		$text = "
		
		<form action='".e_SELF."?results' method='post' id='scanform'>
		    <ul class='nav nav-tabs'>
    <li class='active'><a href='#core' data-toggle='tab'>Users</a></li>
    <li><a href='#news' data-toggle='tab'>News</a></li>
    <li><a href='#mail' data-toggle='tab'>Mail</a></li>
    <li><a href='#files' data-toggle='tab'>Files</a></li>";
	
	if(!empty($this->notify_prefs['plugins']))
	{
		foreach ($this -> notify_prefs['plugins'] as $id => $var)
		{
			$text .= "<li><a href='#notify-".$id."' data-toggle='tab'>".ucfirst($id)."</a></li>";
		}
	}
	
	$text .= "
    </ul>
    <div class='tab-content'>
    <div class='tab-pane active' id='core'>
		<fieldset id='core-notify-config'>
		<legend>".NU_LAN_1."</legend>
        <table class='table adminform'>
        	<colgroup>
        		<col class='col-label' />
        		<col class='col-control' />
        	</colgroup>
		";

		$text .= $this -> render_event('usersup', NU_LAN_2);
		$text .= $this -> render_event('userveri', NU_LAN_3);
		$text .= $this -> render_event('login', NU_LAN_4);
		$text .= $this -> render_event('logout', NU_LAN_5);
		$text .= $this -> render_event('user_xup_', NU_LAN_5);

		$text .= "</table></fieldset>
		<fieldset id='core-notify-2'>
        <legend>".NS_LAN_1."</legend>
        <table class='table adminform'>
        	<colgroup>
        		<col class='col-label' />
        		<col class='col-control' />
        	</colgroup>";

		$text .= $this -> render_event('flood', NS_LAN_2);


		$text .= "</table></fieldset>
		</div>
		
		
		<div class='tab-pane' id='news'>
		<fieldset id='core-notify-3'>
        <legend>".NN_LAN_1."</legend>
        <table class='table adminform'>
        	<colgroup>
        		<col class='col-label' />
        		<col class='col-control' />
        	</colgroup>";

		$text .= $this -> render_event('subnews', NN_LAN_2);
		$text .= $this -> render_event('newspost', NN_LAN_3);
		$text .= $this -> render_event('newsupd', NN_LAN_4);
		$text .= $this -> render_event('newsdel', NN_LAN_5);

		$text .= "</table></fieldset>
		</div>
		
		
		<div class='tab-pane' id='mail'>
		<fieldset id='core-notify-4'>
        <legend>".NM_LAN_1."</legend>
        <table class='table adminform'>
        	<colgroup>
        		<col class='col-label' />
        		<col class='col-control' />
        	</colgroup>";

		$text .= $this -> render_event('maildone', NM_LAN_2);


		$text .= "</table></fieldset>
		</div>
		
		
		<div class='tab-pane' id='files'>
		<fieldset id='core-notify-5'>
        <legend>".NF_LAN_1."</legend>
        <table class='table adminform'>
        	<colgroup>
        		<col class='col-label' />
        		<col class='col-control' />
        	</colgroup>";

		$text .= $this -> render_event('fileupload', NF_LAN_2);

		$text .= "</table>
		</fieldset>
		</div>";

		if(!empty($this->notify_prefs['plugins']))
		{
	
			foreach ($this->notify_prefs['plugins'] as $plugin_id => $plugin_settings)
			{
	            if(is_readable(e_PLUGIN.$plugin_id.'/e_notify.php'))
				{
					$config_category = $this->pluginConfig[$plugin_id]['category'];
					$legacy = $this->pluginConfig[$plugin_id]['legacy'];
					
				//	require(e_PLUGIN.$plugin_id.'/e_notify.php');
	
					$text .= "<div class='tab-pane' id='notify-".$plugin_id."'>
					<fieldset id='core-notify-".str_replace(" ","_",$config_category)."'>
			        <legend>".$config_category."</legend>
			        <table class='table adminform'>
			        	<colgroup>
			        		<col class='col-label' />
			        		<col class='col-control' />
			        	</colgroup>";
					;
	
					foreach ($this->pluginConfig[$plugin_id]['events'] as $event_id => $event_text)
					{
						$text .= $this->render_event($event_id, $event_text, $plugin_id, $legacy);
					}
					
					$text .= "</table>
					</div>";
				}
			}
		}

		$text .= "
	
		<div class='buttons-bar center'>";
        $text .= $frm->admin_button('update', LAN_UPDATE,'update');
		$text .= "
		</div>
		</fieldset>
		</form>
		";

		$ns -> tablerender(NT_LAN_1, $mes->render() . $text);
	 */
	 
	}


	function render_event($id, $description, $include='', $legacy = 0) 
	{
		$tp = e107::getParser();
		$frm = e107::getForm();
		$uc = e107::getUserClass();
		$uc->fixed_classes['email'] = NM_LAN_3;
		$uc->text_class_link['email'] = 'email';

		
		if(defined($description))
		{
			$description = constant($description); 
		}



		$highlight = varset($_GET['type']) == $id ? " class='text-warning'" : '';

		$text = "
			<tr>
				<td title='".$id."'><span{$highlight}>".$description.":</span></td>";



		if(e_DEBUG)
		{
			$text .= "<td>".$id."</td>";
		}

				$text .= "
				<td  class='form-inline nowrap'>
				".$uc->uc_dropdown('event['.$id.'][class]', varset($this->notify_prefs['event'][$id]['class'], e_UC_NOBODY), "nobody,main,admin,member,classes,email","onchange=\"mail_field(this.value,'event_".$id."');\" ");

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

			$text .= "<input type='text' style='width:200px;$disp' class='tbox' id='event_".$id."' name='event[".$id."][email]' value=\"".$value."\" />\n";

		$text .= $frm->hidden("event[".$id."][include]", $include);
		$text .= $frm->hidden("event[".$id."][legacy]", $legacy); // function or method 

		if(isset($this->notify_prefs['event'][$id]['class']) && $this->notify_prefs['event'][$id]['class'] != e_UC_NOBODY)
		{
			$text .= $frm->button('test['.$id.']', $id, 'confirm', LAN_TEST);
		}


		$text .= "</td>";




		$text .= "
		</tr>";
		return $text;
	}


	function update() 
	{
		$this->changeList = array();

		$active = false;

		foreach ($_POST['event'] as $key => $value)
		{
			if ($this -> update_event($key))
			{
				$active = true;
			}
		}

	//	print_a($this->notify_prefs);
		/*
		$s_prefs = $tp -> toDB($this -> notify_prefs);
		$s_prefs = $eArrayStorage -> WriteArray($s_prefs);
		if($sql -> db_Update("core", "e107_value='".$s_prefs."' WHERE e107_name='notify_prefs'")!==FALSE)
		*/

		e107::getConfig()->set('notify',$active)->save(true,true,false);
		e107::getConfig('notify')->updatePref($this->notify_prefs);
		if (e107::getConfig('notify')->save(FALSE))
		{
			// e107::getAdminLog()->logArrayAll('NOTIFY_01',$this->changeList);
			return true;
		}
		else
		{
        	return false;
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
		
		$this -> notify_prefs['event'][$id]['include'] 	= $_POST['event'][$id]['include'];
		$this -> notify_prefs['event'][$id]['legacy'] 	= $_POST['event'][$id]['legacy'];
		
		unset($this -> notify_prefs['event'][$id]['plugin']);
		unset($this -> notify_prefs['event'][$id]['type']);
		
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

require_once(e_ADMIN.'footer.php');/*
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
}*/
?>
