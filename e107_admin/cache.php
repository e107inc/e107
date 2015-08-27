<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2010 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Cache Administration Area
 *
 * $URL$
 * $Id$
 *
*/

/**
 *	Admin page - cache management
 *
 *	@package	e107
 *	@subpackage	admin
 *	@version 	$Id$;
 *  @author 	e107 Inc
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
$frm = e107::getForm();
$mes = e107::getMessage();

if (e107::getPref('cachestatus') == '2')
{
	e107::getConfig()->set('cachestatus', 1)
		->save(false);
}

if(!is_writable(e_CACHE_CONTENT))
{
	$mes->addError(CACLAN_10." (".e_CACHE.")");
	e107::getRender()->tablerender(CACLAN_3, $mes->render());
	require_once("footer.php");
	exit;
}

if (isset($_POST['submit_cache']))
{
	e107::getConfig()->set('cachestatus', intval($_POST['cachestatus']))
		->set('syscachestatus', intval($_POST['syscachestatus']))
		->save(false);
}

if (isset($_POST['trigger_empty_cache']))
{
	e107::getAdminLog()->logSuccess(CACLAN_6);
	switch ($_POST['option_clear_cache'])
	{
		case 'empty_contentcache':
			e107::getCache()->clearAll('content');
			e107::getAdminLog()->flushMessages(CACLAN_5);
		break;

		case 'empty_syscache':
			e107::getCache()->clearAll('system');
			e107::getAdminLog()->flushMessages(CACLAN_16);
		break;

		case 'empty_dbcache':
			e107::getCache()->clearAll('db');
			e107::getAdminLog()->flushMessages(CACLAN_24);
		break;

		case 'empty_imgcache':
			e107::getCache()->clearAll('image');
			e107::getAdminLog()->flushMessages(CACLAN_25);
		break;
		
		// used in standard page output and internal JS includes
		case 'empty_browsercache':
			e107::getCache()->clearAll('browser');
			e107::getAdminLog()->flushMessages(CACLAN_25);
		break;

		// all
		default:
			e107::getCache()->clearAll('content');
			e107::getCache()->clearAll('system');
			e107::getCache()->clearAll('db');
			e107::getCache()->clearAll('image');
			e107::getCache()->clearAll('browser');
			e107::getAdminLog()->flushMessages(CACLAN_26);
		break;
	}
}

$syscache_files = glob(e_CACHE_CONTENT.'S_*.*');
$cache_files = glob(e_CACHE_CONTENT.'C_*.*');
$imgcache_files = glob(e_CACHE_IMAGE.'*.cache.bin');
$dbcache_files = glob(e_CACHE_DB.'*.php');

$syscache_files_num = count($syscache_files);
$cache_files_num = count($cache_files);
$imgcache_files_num = count($imgcache_files);
$dbcache_files_num = count($dbcache_files);

$syscache_label = $syscache_files_num.' '.($syscache_files_num != 1 ? CACLAN_19 : CACLAN_18);
$contentcache_label = $cache_files_num.' '.($cache_files_num != 1 ? CACLAN_19 : CACLAN_18);
$imgcache_label = $imgcache_files_num.' '.($imgcache_files_num != 1 ? CACLAN_19 : CACLAN_18);
$dbcache_label = $dbcache_files_num.' '.($dbcache_files_num != 1 ? CACLAN_19 : CACLAN_18);

$text = "
	<form method='post' action='".e_SELF."'>
		<fieldset id='core-cache-settings'>
			<legend class='e-hideme'>".CACLAN_3."</legend>
			<table class='table adminlist'>
				<colgroup>
					<col style='width:60%' />
					<col style='width:20%' />
					<col style='width:20%' />
				</colgroup>
				<thead>
					<tr>
						<th><!-- --></th>
						<th class='left'>".CACLAN_17."</th>
						<th class='left last'>".CACLAN_1."</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<strong class='e-tip'>".CACLAN_11."</strong>
							<div class='field-help'>".CACLAN_13."</div>
						</td>
						<td>{$contentcache_label}</td>
						<td class='left middle'>
							".$frm->radio_switch('cachestatus', e107::getPref('cachestatus'))."
						</td>
					</tr>
					<tr>
						<td>
							<strong class='e-tip'>".CACLAN_12."</strong>
							<div class='field-help'>".CACLAN_14."</div>
						</td>
						<td>{$syscache_label}</td>
						<td class='left middle'>
							".$frm->radio_switch('syscachestatus', e107::getPref('syscachestatus'))."
						</td>
					</tr>
					<tr>
						<td>
							<strong class='e-tip'>".CACLAN_20."</strong>
							<div class='field-help'>".CACLAN_21."</div>
						</td>
						<td>{$dbcache_label}</td>
						<td class='left middle'>
							".LAN_ENABLED."
						</td>
					</tr>
					<tr>
						<td>
							<strong class='e-tip'>".CACLAN_22."</strong>
							<div class='field-help'>".CACLAN_23."</div>
						</td>
						<td>{$imgcache_label}</td>
						<td class='left middle'>
							".LAN_ENABLED."
						</td>
					</tr>
				</tbody>
			</table>
			<div class='buttons-bar form-inline'>
				<div class='f-right'>".$frm->admin_button('submit_cache', CACLAN_2, 'update')."</div>
				".$frm->select('option_clear_cache', array(
					'empty_all' => CACLAN_26,
					'empty_contentcache' => CACLAN_5,
					'empty_syscache' => CACLAN_16,
					'empty_dbcache' => CACLAN_24,
					'empty_imgcache' => CACLAN_25,
					'empty_browsercache' => CACLAN_27,
				))."
				".$frm->admin_button('trigger_empty_cache', LAN_DELETE, 'delete')."
			</div>
		</fieldset>
	</form>";

e107::getRender()->tablerender(CACLAN_3, $mes->render().$text);

require_once("footer.php");

?>