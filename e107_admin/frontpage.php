<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Administration Area - Front page
 *
 * $Source: /cvs_backup/e107_0.8/e107_admin/frontpage.php,v $
 * $Revision$
 * $Date$
 * $Author$
 *
*/

/**
 *	e107 Front page administration
 *
 *	@package	e107
 *	@subpackage	admin
 *	@version 	$Id$;
 */

require_once ('../class2.php');
if(! getperms('G'))
{
	header('location:'.e_BASE.'index.php');
	exit();
}

include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_'.e_PAGE);

$e_sub_cat = 'frontpage';
require_once ('auth.php');
require_once (e_HANDLER.'form_handler.php');
require_once (e_HANDLER.'message_handler.php');
$emessage = &eMessage::getInstance();

require_once (e_HANDLER.'userclass_class.php');

// Get list of possible options for front page
$front_page['news'] = array('page' => 'news.php', 'title' => ADLAN_0);
$front_page['download'] = array('page' => 'download.php', 'title' => ADLAN_24);
$front_page['wmessage'] = array('page' => 'index.php', 'title' => ADLAN_28);

if($sql->db_Select('page', 'page_id, page_title', "page_theme=''"))
{
	$front_page['custom']['title'] = 'Custom Page';
	while($row = $sql->db_Fetch())
	{
		$front_page['custom']['page'][] = array('page' => 'page.php?'.$row['page_id'], 'title' => $row['page_title']);
	}
}

// Now let any plugins add to the options - must append to the $front_page array as above
if(varset($pref['e_frontpage_list']))
{
	foreach($pref['e_frontpage_list'] as $val)
	{
		if(is_readable(e_PLUGIN.$val.'/e_frontpage.php'))
		{
			require_once (e_PLUGIN.$val.'/e_frontpage.php');
		}
	}
}

// Now sort out list of rules for display (based on $pref data to start with)
$gotpub = FALSE;
if(is_array($pref['frontpage']))
{
	$i = 1;
	foreach($pref['frontpage'] as $class => $val)
	{
		if($class == 'all')
		{
			$class = e_UC_PUBLIC;
			$gotpub = TRUE;
		}
		if($val)
		{ // Only add non-null pages
			$fp_settings[$i] = array('order' => $i, 'class' => $class, 'page' => $val, 'force' => varset($pref['frontpage_force'][$class], ''));
			$i ++;
		}
	}
}
else
{ // Legacy stuff to convert
	$fp_settings = array();
	$fp_settings[] = array('order' => 0, 'class' => e_UC_PUBLIC, 'page' => varset($pref['frontpage'], 'news.php'), 'force' => '');
}

if(!$gotpub)
{ // Need a 'default' setting - usually 'all'
	$fp_settings[] = array('order' => $i, 'class' => e_UC_PUBLIC, 'page' => (isset($pref['frontpage']['all']) ? $pref['frontpage']['all'] : 'news.php'), 'force' => '');
}

$fp_update_prefs = FALSE;

if(isset($_POST['fp_inc']))
{
	$mv = intval($_POST['fp_inc']);
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
	if(($mv > 0) && ($mv < count($fp_settings)))
	{
		$temp = $fp_settings[$mv + 1];
		$fp_settings[$mv + 1] = $fp_settings[$mv];
		$fp_settings[$mv] = $temp;
		$fp_update_prefs = TRUE;
		frontpage_adminlog('01', 'Dec '.$mv);
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
	if($temp['order'] == 0)
	{ // New index to add
		$ind = 0;
		for($i = 1; $i <= count($fp_settings); $i ++)
		{
			if($fp_settings[$i]['class'] == $temp['class'])
				$ind = $i;
		}
		if($ind)
		{
			unset($fp_settings[$ind]); // Knock out duplicate definition for class
			echo "duplicate definition for class: ".$ind."<br />";
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
		//$ns->tablerender(LAN_UPDATED, "<div style='text-align:center'><b>"."Software error"."</b></div>");
		$emessage->add('Software error', E_MESSAGE_ERROR);
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
	$pref['frontpage'] = $fp_list;
	$pref['frontpage_force'] = $fp_force;
	save_prefs();
	$emessage->add(FRTLAN_1, E_MESSAGE_SUCCESS);
}




// All updates complete now - latest data is in the $fp_settings, $fp_list and $fp_force arrays
$fp = new frontpage($front_page);


if(isset($_POST['fp_add_new']))
{
	$text = $fp->edit_rule(array('order' => 0, 'class' => e_UC_PUBLIC, 'page' => 'news.php', 'force' => FALSE)); // Display edit form as well
	$text .= $fp->select_class($fp_settings, FALSE);
	$e107->ns->tablerender(FRTLAN_PAGE_TITLE." - ".FRTLAN_42, $text);
}
elseif(isset($_POST['fp_edit_rule']))
{
	$text = $fp->edit_rule($fp_settings[key($_POST['fp_edit_rule'])]); // Display edit form as well
	$text .= $fp->select_class($fp_settings, FALSE);
	$e107->ns->tablerender(FRTLAN_PAGE_TITLE." - ".FRTLAN_46, $text);
}
else
{ // Just show existing rules
	$e107->ns->tablerender(FRTLAN_PAGE_TITLE." - ".FRTLAN_13, $emessage->render().$fp->select_class($fp_settings, TRUE));
}



class frontpage
{
	protected	$frm;
	protected	$frontPage = array();		// List of options for front page

	public function __construct($fp)
	{
		$this->frm = new e_form();
		$this->frontPage = $fp;
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
		// List of current settings
		$show_legend = $show_button ? " class='e-hideme'" : '';
		$text = "
		<form method='post' action='".e_SELF."'>
			<fieldset id='frontpage-settings'>
				<legend{$show_legend}>".FRTLAN_13."</legend>

				<table cellpadding='0' cellspacing='0' class='adminlist'>
					<colgroup span='5'>
						<col style='width:  5%' />
						<col style='width: 25%' />
						<col style='width: 30%' />
						<col style='width: 30%' />
						<col style='width: 10%' />
					</colgroup>
					<thead>
						<tr>
							<th class='first'>".FRTLAN_40."</th>
							<th>".FRTLAN_53."</th>
							<th>".FRTLAN_49."</th>
							<th>".FRTLAN_35."</th>
							<th class='center last'>".LAN_EDIT."</th>
						</tr>
					</thead>
					<tbody>";

		foreach($fp_settings as $order => $current_value)
		{
			$title = e107::getUserClass()->uc_get_classname($current_value['class']);
			$text .= "
					<tr>
						<td class='right'>".$order."</td>
						<td>".$title."</td>
						<td>".$this->lookup_path($current_value['page'])."</td>
						<td>".$this->lookup_path($current_value['force'])."</td>
						<td class='center'>
							<input class='image' type='image' src='".ADMIN_UP_ICON_PATH."' title='".FRTLAN_47."' value='".$order."' name='fp_inc' />
							<input class='image' type='image' src='".ADMIN_DOWN_ICON_PATH."' title='".FRTLAN_48."' value='".$order."' name='fp_dec' />
							<input class='image edit' type='image' title='".LAN_EDIT."' name='fp_edit_rule[".$order."]' src='".ADMIN_EDIT_ICON_PATH."' />
							<input class='image delete' type='image' title='".LAN_DELETE."' name='fp_delete_rule[".$order."]' src='".ADMIN_DELETE_ICON_PATH."' />
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
					 ".$this->frm->admin_button('fp_add_new', FRTLAN_42, 'create')."
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

		$text = "
		<form method='post' action='".e_SELF."'>
			<fieldset id='core-frontpage-edit'>
				<legend class='e-hideme'>".($rule_info['order'] ? FRTLAN_46 : FRTLAN_42)."</legend>
				<div id='core-frontpage-edit-home'>
					<table cellpadding='0' cellspacing='0' class='adminlist'>
						<colgroup span='2'>
							<col style='width: 40%' />
							<col style='width: 60%' />
						</colgroup>
						<thead>
							<tr>
								<th colspan='2' class='last'>
									".FRTLAN_49."
								</th>
							</tr>
						</thead>
						<tbody>
							{$text_tmp_1}
							<tr>
								".$this->add_other('frontpage', $is_other_home, $rule_info['page'])."
							</tr>
						</tbody>
					</table>
				</div>
				<div id='core-frontpage-edit-post-login'>
					<table cellpadding='0' cellspacing='0' class='adminlist'>
						<colgroup span='2'>
							<col style='width: 40%' />
							<col style='width: 60%' />
						</colgroup>
						<thead>
							<tr>
								<th colspan='2' class='last'>
									".FRTLAN_35." ".FRTLAN_50."
								</th>
							</tr>
						</thead>
						<tbody>
							{$text_tmp_2}
							<tr>
								".$this->add_other('fp_force_page', $is_other_force, $rule_info['force'])."
							</tr>
						</tbody>
					</table>
				</div>
				<div class='buttons-bar center'>
					".$this->frm->hidden('fp_order', $rule_info['order'])."
					".FRTLAN_43.e107::getUserClass()->uc_dropdown('class', $rule_info['class'], 'public,guest,member,admin,main,classes')."
					".$this->frm->admin_button('fp_save_new', FRTLAN_12, 'update')."
					".$this->frm->admin_button('fp_cancel', LAN_CANCEL, 'cancel')."
				</div>
			</fieldset>
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
			return FRTLAN_52; // 'None'
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
					".$this->frm->radio($ob_name, $front_key, $type_selected)."&nbsp;
					".$this->frm->label($front_value['title'], $ob_name, $front_key)."
				</td>
				<td>
			";
			$text .= $this->frm->select_open($ob_name.'_multipage['.$front_key.']');
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
					".$this->frm->radio($ob_name, $front_key, $type_selected)."&nbsp;
					".$this->frm->label($front_value['title'], $ob_name, $front_key)."

				</td>
				<td>&nbsp;</td>";
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
	  	return  "
			<td>".$this->frm->radio($ob_name, 'other', $cur_val)."&nbsp;".$this->frm->label(FRTLAN_15, $ob_name, 'other')."</td>
			<td>".$this->frm->text($ob_name.'_other', ($cur_val ? $cur_page : ''), 150, "size=50&id={$ob_name}-other-txt")."</td>
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

/**
 * Handle page DOM within the page header
 *
 * @return string JS source
 */
function headerjs()
{
	require_once(e_HANDLER.'js_helper.php');
	$ret = "
		<script type='text/javascript'>
			//add required core lan - delete confirm message
			(".e_jshelper::toString(FRTLAN_54).").addModLan('core', 'delete_confirm');
		</script>
		<script type='text/javascript' src='".e_FILE_ABS."jslib/core/admin.js'></script>
	";

	return $ret;
}

?>