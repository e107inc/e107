<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Search Administration
 *
*/

require_once('../class2.php');
if (!getperms('X'))
{
	e107::redirect('admin');
	exit;
}
e107::coreLan('search');// need LAN_SEARCH_98
e107::coreLan('search', true);

$e_sub_cat = 'search';
require_once('auth.php');
require_once(e_HANDLER.'userclass_class.php');
require_once(e_HANDLER.'search_class.php');

$frm = e107::getForm();
$mes = e107::getMessage();
$e_userclass = new user_class();

$query = explode('.', e_QUERY);

$search_prefs = $sysprefs -> getArray('search_prefs');



//$search_handlers['news'] = ADLAN_0; // Moved to Plugin
$search_handlers['comments'] = LAN_COMMENTS;
$search_handlers['users'] = SEALAN_7;
//$search_handlers['downloads'] = ADLAN_24; // Moved to Plugin
// $search_handlers['pages'] = SEALAN_39; // Moved to Plugin


foreach($pref['e_search_list'] as $file)
{
	if(!e107::isInstalled($file))
	{
		continue;	
	}
	
	if (is_readable(e_PLUGIN.$file."/e_search.php") && !isset($search_prefs['plug_handlers'][$file]))
	{
		$search_prefs['plug_handlers'][$file] = array('class' => 0, 'pre_title' => 1, 'pre_title_alt' => '', 'chars' => 150, 'results' => 10);
		$save_search = TRUE;
	}
	if (is_readable(e_PLUGIN.$file.'/search/search_comments.php') && !isset($search_prefs['comments_handlers'][$file]))
	{
		include_once(e_PLUGIN.$file.'/search/search_comments.php');
		$search_prefs['comments_handlers'][$file] = array('id' => $comments_type_id, 'class' => '0', 'dir' => $file);
		unset($comments_type_id);
		$save_search = TRUE;
	}
	
	

}



if (!isset($search_prefs['boundary']))
{
	$search_prefs['boundary'] = 1;
	$save_search = TRUE;
}

if (vartrue($save_search))
{
	
	// $serialpref = addslashes(serialize($search_prefs));
//	$serialpref = e107::getArrayStorage()->writeArray($search_prefs, true);
//	$sql -> db_Update("core", "e107_value='".$serialpref."' WHERE e107_name='search_prefs'");
	e107::getConfig('search')->setPref($search_prefs)->save(false,true,false);
	
	e107::getLog()->add('SEARCH_03','',E_LOG_INFORMATIVE,'');
}


if (isset($_POST['update_main'])) // Update all the basic handler info
{	


	foreach($_POST['core_handlers'] as $s_key => $s_value)
	{
		$search_prefs['core_handlers'][$s_key]['class'] = $_POST['core_handlers'][$s_key]['class'];
		$search_prefs['core_handlers'][$s_key]['order'] = $_POST['core_handlers'][$s_key]['order'];
	}

	foreach ($_POST['plug_handlers'] as $plug_dir => $active)
	{
		$search_prefs['plug_handlers'][$plug_dir]['class'] = $_POST['plug_handlers'][$plug_dir]['class'];
		$search_prefs['plug_handlers'][$plug_dir]['order'] = $_POST['plug_handlers'][$plug_dir]['order'];
	}

	$search_prefs['google'] = $_POST['google'];

	foreach ($_POST['comments_handlers'] as $key => $value)
	{
		$search_prefs['comments_handlers'][$key]['class'] = $_POST['comments_handlers'][$key]['class'];
	}

	e107::getConfig('search')->removePref('plug_handlers')->save(false,true,false);
	e107::getConfig('search')->setPref($search_prefs)->save(true,true);

}


if (isset($_POST['update_handler']))
{	// Update a specific handler
	if ($query[1] == 'c')
	{
		$handler_type = 'core_handlers';
	}
	else if ($query[1] == 'p')
	{
		$handler_type = 'plug_handlers';
	}
	else
	{
		exit;		// Illegal value
	}
	$query[2] = $tp->toDB($query[2]);
	$search_prefs[$handler_type][$query[2]]['class'] = intval($_POST['class']);
	$search_prefs[$handler_type][$query[2]]['chars'] = $tp -> toDB($_POST['chars']);
	$search_prefs[$handler_type][$query[2]]['results'] = $tp -> toDB($_POST['results']);
	$search_prefs[$handler_type][$query[2]]['pre_title'] = intval($_POST['pre_title']);
	$search_prefs[$handler_type][$query[2]]['pre_title_alt'] = $tp -> toDB($_POST['pre_title_alt']);

//	$tmp = addslashes(serialize($search_prefs));
	$tmp = e107::getArrayStorage()->writeArray($search_prefs, true);

	$check = $sql -> db_Update("core", "e107_value='".$tmp."' WHERE e107_name='search_prefs'");
	if($check)
	{
		$mes->addSuccess(LAN_UPDATED);
		e107::getLog()->add('SEARCH_05', $handler_type.', '.$query[2], E_LOG_INFORMATIVE, '');
	}
	elseif(0 === $check) $mes->addInfo(LAN_NO_CHANGE);
	else
	{
		$mes->addError(LAN_UPDATED_FAILED, E_MESSAGE_ERROR);
		$mes->addError(LAN_ERROR." ".$sql->getLastErrorNumber().': '.$sql->getLastErrorText());
	}

}

if (isset($_POST['update_prefs']))
{
	unset($temp);
	$temp['relevance'] = intval($_POST['relevance']);
	$temp['user_select'] = intval($_POST['user_select']);
	$temp['multisearch'] = intval($_POST['multisearch']);
	$temp['selector'] = intval($_POST['selector']);
	$temp['time_restrict'] = intval($_POST['time_restrict']);
	$temp['time_secs'] = min(intval($_POST['time_secs']), 300);
	$temp['mysql_sort'] = $_POST['search_sort'] ? 1 : 0;
	$temp['php_limit'] = intval($_POST['php_limit']);
	$temp['boundary'] = intval($_POST['boundary']);

	e107::getConfig('search')->updatePref($temp)->save(false,true,false);
/*
	if ($admin_log->logArrayDiffs($temp, $search_prefs, 'SEARCH_01'))
	{
		e107::getConfig('search')->setPref($temp)->save(false,true);
	//	$tmp = addslashes(serialize($search_prefs));
		$tmp = e107::getArrayStorage()->writeArray($search_prefs, true);
		$check = $sql -> db_Update("core", "e107_value='".$tmp."' WHERE e107_name='search_prefs'");
		if($check)
		{
			$mes->addSuccess(LAN_UPDATED);
			e107::getLog()->add('SEARCH_05', $handler_type.', '.$query[2], E_LOG_INFORMATIVE, '');
		}
		else //it's an error
		{
			$mes->addError(LAN_UPDATED_FAILED);
			$mes->addError(LAN_ERROR." ".$sql->getLastErrorNumber().': '.$sql->getLastErrorText());
		}
	}
	else $mes->addInfo(LAN_NO_CHANGE);*/

	unset($temp);
	$temp['search_restrict'] = intval($_POST['search_restrict']);
	$temp['search_highlight'] = intval($_POST['search_highlight']);

	e107::getConfig()->setPref($temp)->save(false,true,true);

}




$handlers_total = count($search_prefs['core_handlers']) + count($search_prefs['plug_handlers']);

if ($query[0] == 'settings')
{
	$text = "
	<form method='post' action='".e_SELF."?settings'>
		<fieldset id='core-search-settings'>
			<legend class='e-hideme'>".SEALAN_20."</legend>
			<table class='table adminform'>
				<colgroup>
					<col class='col-label' />
					<col class='col-control' />
				</colgroup>
				<tbody>
					<tr>
						<td>".SEALAN_15.": </td>
						<td>
							".$e_userclass->uc_dropdown('search_restrict', $pref['search_restrict'], 'public,guest,nobody,member,admin,classes', "tabindex='".$frm->getNext()."'")."
						</td>
					</tr>
					<tr>
						<td>".SEALAN_30."</td>
						<td>
							".$frm->radio_switch('search_highlight', $pref['search_highlight'])."
						</td>
					</tr>
					<tr>
						<td>".SEALAN_10."</td>
						<td>
							".$frm->radio_switch('relevance', $search_prefs['relevance'])."
						</td>
					</tr>
					<tr>
						<td>".SEALAN_11."</td>
						<td>
							".$frm->radio_switch('user_select', $search_prefs['user_select'])."
						</td>
					</tr>
					<tr>
						<td>".SEALAN_19."</td>
						<td>
							".$frm->radio_switch('multisearch', $search_prefs['multisearch'])."
						</td>
					</tr>
					<tr>
						<td>".SEALAN_35."</td>
						<td>
							".$frm->radio('selector', array(2 => SEALAN_36, 1 => SEALAN_37, 0 => SEALAN_38), $search_prefs['selector'])."
						</td>
					</tr>
					<tr>
						<td>".SEALAN_12."</td>
						<td class='form-inline'>
							".$frm->radio('time_restrict', array(0 => LAN_DISABLED, 1 => SEALAN_13), $search_prefs['time_restrict'])."&nbsp;
							".$frm->text('time_secs', $tp -> toForm($search_prefs['time_secs']), 3, 'class=tbox&size=mini')."&nbsp;".SEALAN_14."
						</td>
					</tr>
					<tr>
						<td>".SEALAN_3."</td>
						<td class='form-inline'>
							".$frm->radio_switch('search_sort', $search_prefs['mysql_sort'], 'MySQL', SEALAN_31)."&nbsp;
							".$frm->text('php_limit', $tp -> toForm($search_prefs['php_limit']), 5, 'class=tbox&size=mini')."&nbsp;".SEALAN_32."
							<span class='field-help'>".SEALAN_49."</span>
						</td>
					</tr>
					<tr>
						<td>".SEALAN_47."</td>
						<td>
							".$frm->radio_switch('boundary', $search_prefs['boundary'])."
							<span class='field-help'>".SEALAN_48."</span>
						</td>
					</tr>
				</tbody>
			</table>
			<div class='buttons-bar center'>
				".$frm->admin_button('update_prefs', LAN_UPDATE, 'update')."
			</div>
		</fieldset>
	</form>

";

$ns->tablerender(SEALAN_20, $mes->render().$text);

}
elseif ($query[0] == 'edit')
{
	if ($query[1] == 'c')
	{
		$handlers = $search_handlers;
		$handler_type = 'core_handlers';
	}
	elseif ($query[1] == 'p')
	{
		$handlers = $search_prefs['plug_handlers'];
		$handler_type = 'plug_handlers';
	}
	else
	{
		exit;
	}

	$caption = SEALAN_43.": ".$query[2];

	$text = "
	<form method='post' action='".e_SELF."?main.".$query[1].".".$query[2]."'>
		<fieldset id='core-search-edit'>
			<legend class='e-hideme'>{$caption}</legend>
			<table class='table adminform'>
				<colgroup>
					<col class='col-label' />
					<col class='col-control' />
				</colgroup>
				<tbody>
					<tr>
						<td>".SEALAN_44.":</td>
						<td>
							".r_userclass("class", $search_prefs[$handler_type][$query[2]]['class'], "off", "public,guest,nobody,member,admin,classes")."
						</td>
					</tr>
					<tr>
						<td>".SEALAN_45.":</td>
						<td>
							<input class='tbox input-text' type='text' name='results' value='".$tp -> toForm($search_prefs[$handler_type][$query[2]]['results'])."' size='4' maxlength='4' />
						</td>
					</tr>
					<tr>
						<td>".SEALAN_46.":</td>
						<td>
							<input class='tbox input-text' type='text' name='chars' value='".$tp -> toForm($search_prefs[$handler_type][$query[2]]['chars'])."' size='4' maxlength='4' />
						</td>
					</tr>
					<tr>
						<td>".SEALAN_26.":</td>
						<td>
							<input type='radio' class='radio' id='pre-title-1' name='pre_title' value='1'".(($search_prefs[$handler_type][$query[2]]['pre_title'] == 1) ? " checked='checked'" : "")." /><label for='pre-title-1'>". ADLAN_133."</label><br />
							<input type='radio' class='radio' id='pre-title-0' name='pre_title' value='0'".(($search_prefs[$handler_type][$query[2]]['pre_title'] == 0) ? " checked='checked'" : "")." /><label for='pre-title-0'>".LAN_DISABLED."</label><br />
							<input type='radio' class='radio' id='pre-title-2' name='pre_title' value='2'".(($search_prefs[$handler_type][$query[2]]['pre_title'] == 2) ? " checked='checked'" : "")." /><label for='pre-title-2'>".SEALAN_23.":</label>
							<div>
								<input class='tbox input-text' type='text' name='pre_title_alt' value='".$tp -> toForm($search_prefs[$handler_type][$query[2]]['pre_title_alt'])."' size='20' />
							</div>
						</td>
					</tr>
				</tbody>
			</table>
			<div class='buttons-bar center'>
				".$frm->admin_button('update_handler', 'no-value', 'update', LAN_UPDATE)."
			</div>
		</fieldset>
	</form>
	";

	$ns->tablerender($caption, $mes->render().$text);

}
else
{		// Default front page
	$text = "
		<form method='post' action='".e_SELF."'>
			<fieldset id='core-search-configuration-main'>
				<legend class='e-hideme'>".SEALAN_1."</legend>
				<table class='table adminlist'>
					<colgroup>
						<col style='width:55%' />
						<col style='width:25%' />
						<col style='width:10%' />
						<col style='width:10%' />
					</colgroup>
					<thead>
						<tr>
							<th>".SEALAN_21."</th>
							<th class='center'>".SEALAN_25."</th>
							<th class='center'>".LAN_ORDER."</th>
							<th class='center last'>".LAN_EDIT."</th>
						</tr>
					</thead>
					<tbody>
	";
	
	
	foreach($search_handlers as $key => $value)
	{
		$text .= "
						<tr>
							<td>".$value."</td>
							<td class='center'>".r_userclass("core_handlers[".$key."][class]", $search_prefs['core_handlers'][$key]['class'], "off", "public,guest,nobody,member,admin,classes")."</td>
							<td class='center'>
								<select name='core_handlers[".$key."][order]' class='tbox order form-control input-small'>
		";
		for($a = 1; $a <= $handlers_total; $a++) {
			$text .= ($search_prefs['core_handlers'][$key]['order'] == $a) ? "<option value='".$a."' selected='selected'>".$a."</option>" : "<option value='".$a."'>".$a."</option>";
		}
		$text .= "
								</select>
							</td>
							<td class='center'>
								<a class='btn btn-default btn-secondary btn-large' href='".e_SELF."?edit.c.".$key."'>".ADMIN_EDIT_ICON."</a>
							</td>
						</tr>
		";
	}

	
	$searchConfigs = e107::getAddonConfig('e_search');

	foreach ($search_prefs['plug_handlers'] as $plug_dir => $active)
	{
				
		if(varset($searchConfigs[$plug_dir])) // v2.x 
		{
			$search_handlers[] = $searchConfigs[$plug_dir];	
			$search_info[0]['qtype'] = $searchConfigs[$plug_dir]['name'];
		}	
		elseif(e107::isInstalled($plug_dir) && is_readable(e_PLUGIN.$plug_dir."/e_search.php")) // v1.x
		{
			e107::getMessage()->addDebug("Including: ".$plug_dir."/e_search.php");
			require(e_PLUGIN.$plug_dir."/e_search.php");
		}
		else   // workaround for messy pref data.  Missing a plugin or file. 
		{
			continue;	
		}
		

			
		/*
		if($obj = e107::getAddon($plug_dir,'e_search'))
		{
			$search_handlers[] = $obj->config();	
			$ret = $obj->config();	
			
			$search_info[0]['qtype'] = $ret['name'];
		}	
		*/
		
		$text .= "
						<tr>
							<td>".$search_info[0]['qtype'] . "</td>
							<td class='center'>".r_userclass("plug_handlers[".$plug_dir."][class]", $search_prefs['plug_handlers'][$plug_dir]['class'], "off", "public,guest,nobody,member,admin,classes")."</td>
							<td class='center'>
								<select name='plug_handlers[".$plug_dir."][order]' class='form-control input-small order'>
		";
		for($a = 1; $a <= $handlers_total; $a++)
		 {
			$text .= (vartrue($search_prefs['plug_handlers'][$plug_dir]['order']) == $a) ? "<option value='".$a."' selected='selected'>".$a."</option>" : "<option value='".$a."'>".$a."</option>";
		}
		$text .= "
								</select>
							</td>
							<td class='center'>
								<a class='btn btn-default btn-secondary btn-large' href='".e_SELF."?edit.p.".$plug_dir."'>".ADMIN_EDIT_ICON."</a>
							</td>
						</tr>
		";
		unset($search_info);
	}
	//$sel = (isset($search_prefs['google']) && $search_prefs['google']) ? " checked='checked'" : "";

	$text .= "
						<tr>
							<td>Google</td>
							<td class='center'>
								".r_userclass("google", $search_prefs['google'], "off", "public,guest,nobody,member,admin,classes")."
							</td>
							<td></td>
							<td></td>
						</tr>
				</tbody>
			</table>
		</fieldset>

	";

	$text .= "

			<fieldset id='core-search-configuration-comm'>
				<legend class='e-hideme'>".SEALAN_1."</legend>
				<table class='table adminlist'>
					<colgroup>
						<col style='width:55%' />
						<col style='width:45%' />
					</colgroup>
					<thead>
						<tr>
							<th>".SEALAN_18."</th>
							<th class='last'>".SEALAN_25."</th>
						</tr>
					</thead>
					<tbody>
	";

	foreach ($search_prefs['comments_handlers'] as $key => $value) 
	{
		$path = ($value['dir'] == 'core') ? e_HANDLER.'search/comments_'.$key.'.php' : e_PLUGIN.$value['dir'].'/search/search_comments.php';

		if($value['dir'] == 'download' && !e107::isInstalled($value['dir']))
		{
			continue;
		}

		if(is_readable($path))
		{
			require_once($path);
		}
		$text .= "
						<tr>
							<td>".vartrue($comments_title)."</td>
							<td>
								".r_userclass("comments_handlers[".$key."][class]", $search_prefs['comments_handlers'][$key]['class'], "off", "public,guest,nobody,member,admin,classes")."
							</td>
						</tr>
		";
		unset($comments_title);
	}
	$text .= "
				</tbody>
			</table>
			<div class='buttons-bar center'>
			".$frm->admin_button('update_main','no-value','update',LAN_UPDATE)."
			</div>
		</fieldset>
		</form>
	";

	$ns->tablerender(SEALAN_1, $mes->render().$text);
}


require_once("footer.php");

function search_adminmenu() 
{

	$query = e_QUERY;
	
	$action = vartrue($query,'main');

	$var['main']['text'] = SEALAN_41;
	$var['main']['link'] = e_SELF;

	$var['settings']['text'] = LAN_PREFS;
	$var['settings']['link'] = e_SELF."?settings";

		$icon  = e107::getParser()->toIcon('e-search-24');
		$caption = $icon."<span>".SEALAN_40."</span>";

	e107::getNav()->admin($caption, $action, $var);
}
?>