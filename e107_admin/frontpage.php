<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2015 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Administration Area - Front page
 *
*/

/**
 *	e107 Front page administration
 *
 *	@package	e107
 *	@subpackage	admin
 *	@version 	$Id$;
 */

if(!empty($_POST) && !isset($_POST['e-token']))
{
	$_POST['e-token'] = '';
}
require_once ('../class2.php');
if(! getperms('G'))
{
	e107::redirect('admin');
	exit();
}

e107::coreLan('frontpage', true);

$e_sub_cat = 'frontpage';
require_once ('auth.php');

$mes = e107::getMessage();

$frontPref = e107::pref('core');              		 	// Get prefs

// Get list of possible options for front page
$front_page['news'] = array('page' => 'news.php', 'title' => ADLAN_0); // TODO Move to e107_plugins/news

$front_page['wmessage'] = array('page' => 'index.php', 'title' => ADLAN_28, 'diz'=>'index.php');

if($sql->db_Select('page', 'page_id, page_title', "menu_name=''")) // TODO Move to e107_plugins/page
{
	$front_page['custom']['title'] = FRTLAN_30;
	while($row = $sql->db_Fetch())
	{
		$front_page['custom']['page'][] = array('page' => 'page.php?'.$row['page_id'], 'title' => $row['page_title']);
	}
}

// Now let any plugins add to the options - must append to the $front_page array as above


	//v2.x spec. ----------
	$new = e107::getAddonConfig('e_frontpage');
	foreach($new as $k=>$v)
	{
		$front_page[$k] = $v;
	}

	// v1.x spec.---------------
	if(!empty($frontPref['e_frontpage_list']))
	{
		foreach($frontPref['e_frontpage_list'] as $val)
		{
			if(is_readable(e_PLUGIN.$val.'/e_frontpage.php'))
			{
				require_once (e_PLUGIN.$val.'/e_frontpage.php');
			}
		}
	}



// Make sure links relative to SITEURL
foreach($front_page as &$front_value)
{
	if(is_array($front_value['page']))
	{ // Its a URL with multiple options
		foreach($front_value['page'] as &$multipage)
		{
			$multipage = str_replace(e_HTTP, '', $multipage);
			//if (substr($multipage, 0, 1) != '/') $multipage = '/'.$multipage;
		}
	}
	else
	{
		$front_value = str_replace(e_HTTP, '', $front_value);
		//if (substr($front_value, 0, 1) != '/') $front_value = '/'.$front_value;
	}
}

// print_a($front_page);


// Now sort out list of rules for display (based on $pref data to start with)
$gotpub = FALSE;
if(is_array($frontPref['frontpage']))
{
	$i = 1;
	foreach($frontPref['frontpage'] as $class => $val)
	{
		if($class == 'all')
		{
			$class = e_UC_PUBLIC;
			$gotpub = TRUE;
		}
		if($val)
		{ // Only add non-null pages
			$fp_settings[$i] = array('order' => $i, 'class' => $class, 'page' => $val, 'force' => varset($frontPref['frontpage_force'][$class], ''));
			$i ++;
		}
	}
}
else
{ // Legacy stuff to convert
	$fp_settings = array();
	$fp_settings[] = array('order' => 0, 'class' => e_UC_PUBLIC, 'page' => varset($frontPref['frontpage'], 'news.php'), 'force' => '');
}

if(!$gotpub)
{ // Need a 'default' setting - usually 'all'
	$fp_settings[] = array('order' => $i, 'class' => e_UC_PUBLIC, 'page' => (isset($frontPref['frontpage']['all']) ? $frontPref['frontpage']['all'] : 'news.php'), 'force' => '');
}

$fp_update_prefs = FALSE;


/*
Following code replaced - values not passed on image clicks with Firefox
if(isset($_POST['fp_inc']))
{
	$mv = intval($_POST['fp_inc']);
	echo "Increment: {$mv}<br />";
	if(($mv > 1) && ($mv <= count($fp_settings)))
	{
		$temp = $fp_settings[$mv - 1];
		$fp_settings[$mv - 1] = $fp_settings[$mv];
		$fp_settings[$mv] = $temp;
		$fp_update_prefs = TRUE;
		frontpage_adminlog('01', 'Inc '.$mv);
	}
}
elseif(isset($_POST['fp_dec']))
{
	$mv = intval($_POST['fp_dec']);
	echo "Decrement: {$mv}<br />";
	if(($mv > 0) && ($mv < count($fp_settings)))
	{
		$temp = $fp_settings[$mv + 1];
		$fp_settings[$mv + 1] = $fp_settings[$mv];
		$fp_settings[$mv] = $temp;
		$fp_update_prefs = TRUE;
		frontpage_adminlog('01', 'Dec '.$mv);
	}
}
*/

if (isset($_POST))
{

	// avoid endless loop.
	if($_POST['frontpage'] == 'other' && (trim($_POST['frontpage_other']) == 'index.php' || trim($_POST['frontpage_other']) == '{e_BASE}index.php'))
	{
		$_POST['frontpage'] = 'wmessage';
		$_POST['frontpage_other'] = '';
	}


	foreach ($_POST as $k => $v)
	{
		$incDec = substr($k, 0, 6);
		$idNum = substr($k, 6);
		if ($incDec == 'fp_inc')
		{
			$mv = intval($idNum);
			if(($mv > 1) && ($mv <= count($fp_settings)))
			{
				$temp = $fp_settings[$mv - 1];
				$fp_settings[$mv - 1] = $fp_settings[$mv];
				$fp_settings[$mv] = $temp;
				$fp_update_prefs = TRUE;
				frontpage_adminlog('01', 'Inc '.$mv);
			}
			break;
		}
		elseif ($incDec == 'fp_dec')
		{
			$mv = intval($idNum);
			if(($mv > 0) && ($mv < count($fp_settings)))
			{
				$temp = $fp_settings[$mv + 1];
				$fp_settings[$mv + 1] = $fp_settings[$mv];
				$fp_settings[$mv] = $temp;
				$fp_update_prefs = TRUE;
				frontpage_adminlog('01', 'Dec '.$mv);
			}
			break;
		}
	}
}



// Edit an existing rule
if(isset($_POST['fp_edit_rule']))
{
	$_POST['type'] = (isset($_POST['edit']['all'])) ? 'all_users' : 'user_class';
	$_POST['class'] = key($_POST['edit']);
}

// Cancel Edit


if(isset($_POST['fp_save_new']))
{ // Add or edit an existing rule here.
	// fp_order - zero for a new rule, non-zero if editing an existing rule
	// class - user class for rule
	// frontpage - radio button option indicating type of page (for home page)
	// frontpage_multipage[] - the other information for custom pages and similar - array index matches value of 'frontpage' when selected
	// frontpage_other - URL for 'other' home page
	// fp_force_page - radio button option indicating type of page (for post-login page)
	// fp_force_page_multipage[] - the other information for custom pages and similar - array index matches value of 'frontpage' when selected
	// fp_force_page_other - URL for forced post-login 'other' page


	if($_POST['frontpage'] == 'other')
	{
		$_POST['frontpage_other'] = trim($tp->toForm($_POST['frontpage_other']));
		$frontpage_value = $_POST['frontpage_other'] ? $_POST['frontpage_other'] : 'news.php';
	}
	else
	{
		if(is_array($front_page[$_POST['frontpage']]['page']))
		{
			$frontpage_value = $front_page[$_POST['frontpage']]['page'][$_POST['frontpage_multipage'][$_POST['frontpage']]]['page'];
		}
		else
		{
			$frontpage_value = $front_page[$_POST['frontpage']]['page'];
		}
	}

	if($_POST['fp_force_page'] == 'other')
	{
		$_POST['fp_force_page_other'] = trim($tp->toForm($_POST['fp_force_page_other']));
		$forcepage_value = $_POST['fp_force_page_other']; // A null value is allowable here
	}
	else
	{
		if(is_array($front_page[$_POST['fp_force_page']]['page']))
		{
			$forcepage_value = $front_page[$_POST['fp_force_page']]['page'][$_POST['fp_force_page_multipage'][$_POST['fp_force_page']]]['page'];
		}
		else
		{
			$forcepage_value = $front_page[$_POST['fp_force_page']]['page'];
		}
	}

	$temp = array('order' => intval($_POST['fp_order']), 'class' => $_POST['class'], 'page' => $frontpage_value, 'force' => trim($forcepage_value));

	if($temp['order'] == 0) // New index to add
	{
		$ind = 0;
		for($i = 1; $i <= count($fp_settings); $i ++)
		{
			if($fp_settings[$i]['class'] == $temp['class'])
				$ind = $i;
		}

		if($ind)
		{
			$mes->addDebug(print_a($fp_settings,true));
			$mes->addError(FRTLAN_56." ".$ind);
			unset($fp_settings[$ind]); // Knock out duplicate definition for class
		}

		array_unshift($fp_settings, $temp); // Deliberately add twice
		array_unshift($fp_settings, $temp); // ....because re-indexed from zero
		unset($fp_settings[0]); // Then knock out index zero
		$fp_update_prefs = TRUE;
		frontpage_adminlog('02', "class => {$_POST['class']},[!br!]page => {$frontpage_value},[!br!]force => {$forcepage_value}");
	}
	elseif(array_key_exists($temp['order'], $fp_settings))
	{
		$fp_settings[$temp['order']] = $temp;
		$fp_update_prefs = TRUE;
		frontpage_adminlog('03', "posn => {$temp},[!br!]class => {$_POST['class']},[!br!]page => {$frontpage_value},[!br!]force => {$forcepage_value}");
	}
	else
	{ // Someone playing games
		$mes->addError(FRTLAN_57);
	}
}

if(isset($_POST['fp_delete_rule']))
{
	if(isset($fp_settings[key($_POST['fp_delete_rule'])]))
	{
		$rule_no = key($_POST['fp_delete_rule']);
		$array_size = count($fp_settings);
		frontpage_adminlog('04', "Rule {$rule_no},[!br!]class => {$fp_settings[$rule_no]['class']},[!br!]page => {$fp_settings[$rule_no]['page']},[!br!]force => {$fp_settings[$rule_no]['force']}");
		unset($fp_settings[$rule_no]);
		while($rule_no < $array_size)
		{ // Move up and renumber any entries after the deleted rule
			$fp_settings[$rule_no] = $fp_settings[$rule_no + 1];
			$rule_no ++;
			unset($fp_settings[$rule_no]);
		}
		$fp_update_prefs = TRUE;
	}
}

if($fp_update_prefs)
{ // Save the two arrays
	$fp_list = array();
	$fp_force = array();
	for($i = 1; $i <= count($fp_settings); $i ++)
	{
		$fp_list[$fp_settings[$i]['class']] = $fp_settings[$i]['page'];
		$fp_force[$fp_settings[$i]['class']] = $fp_settings[$i]['force'];
	}

	$corePrefs = e107::getConfig('core');				// Core Prefs Object.
	$corePrefs->set('frontpage', $fp_list);
	$corePrefs->set('frontpage_force', $fp_force);
	$result = $corePrefs->save(FALSE, TRUE);
	$mes->addDebug("<h4>Home</h4>".print_a($fp_list, true));
	$mes->addDebug("<h4>Post-Login</h4>".print_a($fp_force, true));
}




// All updates complete now - latest data is in the $fp_settings, $fp_list and $fp_force arrays
$fp = new frontpage($front_page);





class frontpage
{
	protected	$frm;
	protected	$frontPage = array();		// List of options for front page

	public function __construct($fp)
	{
		$this->frm = e107::getForm();
		$this->frontPage = $fp;
		
		$ns = e107::getRender();
		$mes = e107::getMessage();
		
		global $fp_settings;
		
		
		if(vartrue($_GET['mode']) == 'create')
		{
			$text = $this->edit_rule(array('order' => 0, 'class' => e_UC_PUBLIC, 'page' => 'news.php', 'force' => FALSE)); // Display edit form as well
		//	$text .= $this->select_class($fp_settings, FALSE);
			$ns->tablerender(FRTLAN_PAGE_TITLE.SEP.FRTLAN_42, $text);
		}
		elseif(vartrue($_GET['id']))
		{
			$key = intval($_GET['id']);
			$text = $this->edit_rule($fp_settings[$key]); // Display edit form as well
		//	$text .= $this->select_class($fp_settings, FALSE);
			$ns->tablerender(FRTLAN_PAGE_TITLE.SEP.FRTLAN_46, $text);
		}
		else
		{ // Just show existing rules
			$ns->tablerender(FRTLAN_PAGE_TITLE.SEP.FRTLAN_13, $mes->render().$this->select_class($fp_settings, TRUE));
		}
		
	}



	/**
	 *	Show a list of existing rules, with edit/delete/move buttons, and optional button to add a new rule
	 *
	 *	@param boolean $show_button - show the 'Add new rule' button if true
	 *
	 *	@return string text for display
	 */
	function select_class(&$fp_settings, $show_button = TRUE)
	{
		$frm = e107::getForm();
		// List of current settings
		$show_legend = $show_button ? " class='e-hideme'" : '';
		$text = "
		<form method='post' action='".e_SELF."'>
		<input type='hidden' name='e-token' value='".e_TOKEN."' />
			<fieldset id='frontpage-settings'>
				<legend{$show_legend}>".FRTLAN_13."</legend>

				<table class='table adminlist'>
					<colgroup>
						<col style='width:  5%' />
						<col style='width: 25%' />
						<col style='width: 30%' />
						<col style='width: 25%' />
						<col style='width: 15%' />
					</colgroup>
					<thead>
						<tr>
							<th class='first left'>".LAN_ORDER."</th>
							<th>".LAN_USERCLASS."</th>
							<th>".FRTLAN_49."</th>
							<th>".FRTLAN_35."</th>
							<th class='center last'>".LAN_OPTIONS."</th>
						</tr>
					</thead>
					<tbody>";

		foreach($fp_settings as $order => $current_value)
		{
			$title = e107::getUserClass()->getName($current_value['class']);
			$text .= "
					<tr>
						<td class='left'>".$order."</td>
						<td>".$title."</td>
						<td>".$this->lookup_path($current_value['page'])."</td>
						<td>".$this->lookup_path($current_value['force'])."</td>
						<td class='center options last'>
						<div class='btn-group'>";

					//		".$frm->admin_button('fp_inc',$order,'up',ADMIN_UP_ICON)."
					//		".$frm->admin_button('fp_dec',$order,'down',ADMIN_DOWN_ICON)."

						$text .= "
							<a class='btn btn-default' title='".LAN_EDIT."' href='".e_SELF."?id=".$order."' >".ADMIN_EDIT_ICON."</a>
							".$frm->admin_button('fp_delete_rule['.$order.']',$order,'',ADMIN_DELETE_ICON)."					
						</div>
						</td>
					</tr>";
					
					
		}
		$text .= "
		 		</tbody>
		 	</table>";

		if($show_button)
		{
			$text .= "
				<div class='buttons-bar center'>
					 <a href='".e_SELF."?mode=create' class='btn btn-success'>".FRTLAN_42."</a>
				</div>";
		}

		$text .= "
			</fieldset>
			</form>";

		return $text;
	}



	/**
	 *	Display form to add/edit rules
	 *
	 *	@param array $rule_info - initial data (must be preset if new rule)
	 *
	 *	@return string - text for display
	 */
	function edit_rule($rule_info)
	{
		$is_other_home = TRUE;
		$is_other_force = TRUE;
		//$force_checked = $rule_info['force'] ? " checked='checked'" : '';
		$text_tmp_1 = '';
		$text_tmp_2 = '';
		foreach($this->frontPage as $front_key => $front_value)
		{
			//$type_selected = FALSE;

			$text_tmp_1 .= "
			<tr>
				".$this->show_front_val('frontpage', $front_key, $front_value, $is_other_home, $rule_info['page'])."
			</tr>
		  	";

			$text_tmp_2 .= "
			<tr>
				".$this->show_front_val('fp_force_page', $front_key, $front_value, $is_other_force, $rule_info['force'])."
			</tr>
		  	";

		}

// <legend class='e-hideme'>".($rule_info['order'] ? FRTLAN_46 : FRTLAN_42)."</legend>

		$text = "
		<form method='post' action='".e_SELF."'>
		<input type='hidden' name='e-token' value='".e_TOKEN."' />
		";
		
		$text .= '<ul class="nav nav-tabs" id="myTabs">
			<li class="active"><a data-toggle="tab" href="#home">'.FRTLAN_49.'</a></li>
			<li><a data-toggle="tab" href="#postlogin">'.FRTLAN_35.'</a></li>
			</ul>
			 ';
			
			$text .= "
			<div class='tab-content'>	
				<div class='tab-pane active' id='home'>
					<table class='table adminform'>
						<colgroup>
							<col style='width: 20%' />
							<col style='width: 80%' />
						</colgroup>
						<tbody>
							<tr>
							<td>Selection</td>
							<td>
								<table class='table table-striped table-bordered'>
									<colgroup>
										<col style='width: 20%' />
										<col style='width: 80%' />
									</colgroup>
									".$text_tmp_1."
									".$this->add_other('frontpage', $is_other_home, $rule_info['page'])."
								</table>
							</td>
							</tr>

						</tbody>
					</table>
				</div>
				
				<div class='tab-pane' id='postlogin'>
					<table class='table adminform'>
						<colgroup>
							<col style='width: 20%' />
							<col style='width: 80%' />
						</colgroup>
						<tbody><tr>
							<td></td>
							<td>
								<table class='table table-striped table-bordered'>
								<colgroup>
									<col style='width: 20%' />
									<col style='width: 80%' />
								</colgroup>
								".$text_tmp_2."
								".$this->add_other('fp_force_page', $is_other_force, $rule_info['force'])."
								</table>
							</td>
							</tr>

						</tbody>
					</table>
				</div>
			</div>
			<table class='table adminform'>
				<colgroup>
					<col style='width: 20%' />
					<col style='width: 80%' />
				</colgroup>
				<tr>
					<td>".FRTLAN_43."</td>
					<td>".e107::getUserClass()->uc_dropdown('class', $rule_info['class'], 'public,guest,member,admin,main,classes')."</td>
				</tr>
				<tr>
					<td>".LAN_ORDER."</td>
					<td>".$this->frm->number('fp_order', $rule_info['order'], 3, 'min=0')."</td>
				</tr>
			</table>
			
				<div class='buttons-bar center form-inline'>

					".$this->frm->admin_button('fp_save_new', LAN_UPDATE, 'update')."
					".$this->frm->admin_button('fp_cancel', LAN_CANCEL, 'cancel')."
				</div>
			
		</form>
		";
		return $text;
	}



	/**
	 *	Given a path string related to a choice, returns the 'type' (title) for it
	 *
	 *	@param string $path
	 *
	 *	@return string - title of option
	 */
	function lookup_path($path)
	{
		foreach($this->frontPage as $front_value)
		{
			if(is_array($front_value['page']))
			{ // Its a URL with multiple options
				foreach($front_value['page'] as $multipage)
				{
					if($path == $multipage['page'])
					{
						//			  return $front_value['title'].":".$path;
						return $front_value['title'].":".$multipage['title'];
					}
				}
			}
			else
			{
				if($path == $front_value['page'])
				{
					return $front_value['title'];
				}
			}
		}
		if(strlen($path))
			return FRTLAN_51.":".$path; // 'Other'
		else
			return LAN_NONE; // 'None'
	}



	/**
	 *	Show the selection options for a possible target of a rule
	 *
	 *	@param string $ob_name - name of the radio button which selects this element 
	 *	@param string $front_key 
	 *	@param array|string $front_value - array of choices, or a single value
	 *	@param boolean $is_other - passed by reference - set if some other option is selected
	 *	@param string $current_setting - current value
	 *
	 *	@return string - text for display
	 */
	function show_front_val($ob_name, $front_key, $front_value, &$is_other, $current_setting)
	{
		$type_selected = FALSE;
		$text = '';

		// First, work out if the selection os one of these options
		if (is_array($front_value['page']))
		{ // Its a URL with multiple options
			foreach($front_value['page'] as $multipage)
			{
				if($current_setting == $multipage['page'])
				{
					$type_selected = TRUE;
					$is_other = FALSE;
				}
			}
		}
		else
		{
			if($current_setting == $front_value['page'])
			{
				$type_selected = TRUE;
				$is_other = FALSE;
			}
		}

		// Now generate the display text - two table cells worth
		if (is_array($front_value['page']))
		{ // Multiple options for same page name
			$text .= "
				<td>
					".$this->frm->radio($ob_name, $front_key, $type_selected, array('label'=>$front_value['title']))."
				</td>
				<td>
			";
			$text .= $this->frm->select_open($ob_name.'_multipage['.$front_key.']', 'size=xxlarge');
			foreach($front_value['page'] as $multipage_key => $multipage_value)
			{
				$text .= "\n".$this->frm->option($multipage_value['title'], $multipage_key, ($current_setting == $multipage_value['page']))."\n";
			}
			$text .= $this->frm->select_close();


			$text .= "</td>";
		}
		else
		{ // Single option for URL
			$text .= "
				<td>
					".$this->frm->radio($ob_name, $front_key, $type_selected, array('label'=>$front_value['title']))."

				</td>
				<td>".vartrue($front_value['diz'],"&nbsp;")."</td>";
		}
		return $text;
	}



	/**
	 *	Provide the text for an 'other' option - a text box for URL entry
	 *
	 *	@param string $ob_name - name of the radio button which selects this element 
	 *	@param string $front_key 
	 *	@param string $curval - current 'selected' value
	 *	@param string $cur_page - probably the secondary (e.g. custom page) value for any option that has one
	 *
	 *	@return string - text for display
	 */
	function add_other($ob_name, $cur_val, $cur_page)
	{
		$label = ($cur_val) ? LAN_CUSTOM_URL_DISABLED : LAN_CUSTOM_URL;
		
	  	return  "
			<td>".$this->frm->radio($ob_name, 'other', $cur_val, array('label'=> $label))."</td>
			<td>".$this->frm->text($ob_name.'_other', ($cur_val ? $cur_page : ''), 150, "size=xxlarge&id={$ob_name}-other-txt")."</td>
		";
	}
}

require_once(e_ADMIN.'footer.php');

/**
 *	Log event to admin log
 *
 *	@param string $msg_num - exactly two numeric characters corresponding to a log message
 *	@param string $woffle - information for the body of the log entre
 *
 *	@return none
 */
function frontpage_adminlog($msg_num = '00', $woffle = '')
{
	e107::getAdminLog()->log_event('FRONTPG_'.$msg_num, $woffle, E_LOG_INFORMATIVE, '');
}


function frontpage_adminmenu() 
{

	$action = vartrue($_GET['mode'],'main');
	
	$var['main']['text'] = LAN_MANAGE;
	$var['main']['link'] = e_SELF;
	$var['create']['text'] = LAN_CREATE;
	$var['create']['link'] = e_SELF."?mode=create";

		$icon  = e107::getParser()->toIcon('e-frontpage-24');
		$caption = $icon."<span>".FRTLAN_PAGE_TITLE."</span>";


	show_admin_menu($caption, $action, $var);
}


?>