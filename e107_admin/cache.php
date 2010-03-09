<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Cache Administration Area
 *
 * $Source: /cvs_backup/e107_0.8/e107_admin/cache.php,v $
 * $Revision$
 * $Date$
 * $Author$
 *
*/
require_once("../class2.php");
if (!getperms("C"))
{
	header("location:".e_BASE."index.php");
	exit;
}

include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_'.e_PAGE);

$e_sub_cat = 'cache';

require_once("auth.php");
require_once(e_HANDLER."cache_handler.php");
require_once(e_HANDLER."form_handler.php");
require_once(e_HANDLER."message_handler.php");
$ec = new ecache;
$frm = new e_form();

$emessage = eMessage::getInstance();

if ($pref['cachestatus'] == '2')
{
	$pref['cachestatus'] = '1';
	save_prefs();
}

if(!is_writable(e_CACHE_CONTENT))
{
	$ns->tablerender(CACLAN_3, CACLAN_10."<br />(".$CACHE_DIRECTORY.")");
	require_once("footer.php");
	exit;
}

if (isset($_POST['submit_cache']))
{
	if ($pref['cachestatus'] != $_POST['cachestatus'] || $pref['syscachestatus'] != $_POST['syscachestatus'])
	{
		$pref['cachestatus'] = $_POST['cachestatus'] ? '1' : '0';
		$pref['syscachestatus'] = $_POST['syscachestatus'] ? '1' : '0';

		save_prefs();
		$admin_log->log_event('CACHE_01', $pref['syscachestatus'].', '.$pref['cachestatus'], E_LOG_INFORMATIVE,'');

		$ec->clear();
		$ec->clear_sys();

		$emessage->add(CACLAN_4, E_MESSAGE_SUCCESS);
	}
	else
	{
		$emessage->add(LAN_NO_CHANGE, E_MESSAGE_INFO);
	}
}

if (isset($_POST['empty_syscache']))
{
	$ec->clear_sys();
	$admin_log->log_event('CACHE_02', $pref['syscachestatus'].', '.$pref['cachestatus'], E_LOG_INFORMATIVE, '');
	$emessage->add(CACLAN_15, E_MESSAGE_SUCCESS);
}

if (isset($_POST['empty_cache']))
{
	$ec->clear();
	$admin_log->log_event('CACHE_03', $pref['syscachestatus'].', '.$pref['cachestatus'], E_LOG_INFORMATIVE, '');
	$emessage->add(CACLAN_6, E_MESSAGE_SUCCESS);
}



$syscache_files = glob(e_CACHE_CONTENT."S_*.*");
$cache_files = glob(e_CACHE_CONTENT."C_*.*");

$syscache_files_num = count($syscache_files);
$cache_files_num = count($cache_files);

$sys_count = CACLAN_17." ".$syscache_files_num." ".($syscache_files_num != 1 ? CACLAN_19 : CACLAN_18);
$nonsys_count = CACLAN_17." ".$cache_files_num." ".($cache_files_num != 1 ? CACLAN_19 : CACLAN_18);

$text = "
	<form method='post' action='".e_SELF."'>
		<fieldset id='core-cache-settings'>
			<legend class='e-hideme'>".CACLAN_3."</legend>
			<table cellpadding='0' cellspacing='0' class='adminlist'>
				<colgroup span='2'>
					<col style='width:80%' />
					<col style='width:20%' />
				</colgroup>
				<thead>
					<tr>
						<th><!-- --></th>
						<th class='center last'>".CACLAN_1."</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<strong>".CACLAN_11."</strong>: {$nonsys_count}
							<div class='field-help'>".CACLAN_13."</div>
						</td>
						<td class='center middle'>
							".$frm->radio('cachestatus', 1, ($pref['cachestatus']))."
							".$frm->label(LAN_ENABLED, 'cachestatus', 1)."&nbsp;&nbsp;
							".$frm->radio('cachestatus', 0, (!$pref['cachestatus']))."
							".$frm->label(LAN_DISABLED, 'cachestatus', 0)."
						</td>
					</tr>
					<tr>
						<td>
							<strong>".CACLAN_12."</strong>: {$sys_count}
							<div class='field-help'>".CACLAN_14."</div>
						</td>
						<td class='center middle'>
							".$frm->radio('syscachestatus', 1, ($pref['syscachestatus']))."
							".$frm->label(LAN_ENABLED, 'syscachestatus', 1)."&nbsp;&nbsp;
							".$frm->radio('syscachestatus', 0, (!$pref['syscachestatus']))."
							".$frm->label(LAN_DISABLED, 'syscachestatus', 0)."
						</td>
					</tr>
				</tbody>
			</table>
			<div class='buttons-bar left'>
				".$frm->admin_button('submit_cache', CACLAN_2, 'update f-right')."
				".$frm->admin_button('empty_cache', CACLAN_5, 'delete')."
				".$frm->admin_button('empty_syscache', CACLAN_16, 'delete')."
			</div>
		</fieldset>
	</form>";

$ns->tablerender(CACLAN_3, $emessage->render().$text);

require_once("footer.php");
?>