<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2008 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * URL Management
 *
 * $Source: /cvs_backup/e107_0.8/e107_admin/eurl.php,v $
 * $Revision: 1.9 $
 * $Date: 2009-08-28 16:11:00 $
 * $Author: marj_nl_fr $
*/

require_once('../class2.php');
if (!getperms('L'))
{
	header('location:'.e_BASE.'index.php');
	exit;
}

include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_'.e_PAGE);

$e_sub_cat = 'eurl';
require_once(e_ADMIN.'auth.php');
require_once(e_HANDLER."form_handler.php");
require_once(e_HANDLER."form_handler.php");
require_once(e_HANDLER."message_handler.php");

$frm = new e_form(); //new form handler
$emessage = &eMessage::getInstance();
$urlc = new admin_url_config();

if (isset($_POST['update']))
{
	//$res = $urlc->update();
	admin_update($urlc->update(), 'update', false, false, false);
	//$plug_message = $res ? LAN_UPDATED : ($res === 0 ? LAN_NO_CHANGE : LAN_UPDATED_FAILED);
	//$plug_message = "<div class='center clear'>".$plug_message."</div><br />";
}

//var_dump($pref['url_config'], $e107->url->getUrl('pm', 'main', array('f'=>'box', 'box'=>2)));

$urlc->renderPage();
require_once(e_ADMIN.'footer.php');

class admin_url_config {

	var $_frm;
	var $_plug;
	var $_api;

	function admin_url_config()
	{
		global $e107;
		require_once(e_HANDLER.'plugin_class.php');
		require_once(e_HANDLER.'file_class.php');
		require_once(e_HANDLER."form_handler.php");

		$this->_frm = new e_form();
		$this->_plug = new e107plugin();
		$this->_fl = new e_file();
		$this->_api = &$e107;
	}

	function renderPage()
	{
		global $emessage;
		$empty = "
							<tr>
								<td colspan='2'>".LAN_EURL_EMPTY."</td>
							</tr>
		";
		$text = "
			<form action='".e_SELF."' method='post' id='urlconfig-form'>
				<fieldset id='core-eurl-core'>
					<legend>".LAN_EURL_CORECONFIG."</legend>
					<table cellpadding='0' cellspacing='0' class='adminlist'>
						<colgroup span='2'>
							<col class='col-label' />
							<col class='col-control' />
						</colgroup>
						<tbody>
		";

		$tmp = $this->render_sections('core');

		if($tmp) $text .= $tmp;
		else $text .= $empty;

		$text .= "
						</tbody>
					</table>
				</fieldset>
				<fieldset id='core-eurl-plugin'>
					<legend>".LAN_EURL_PLUGCONFIG."</legend>
					<table cellpadding='0' cellspacing='0' class='adminlist'>
						<colgroup span='2'>
							<col class='col-label' />
							<col class='col-control' />
						</colgroup>
						<tbody>
		";

		$tmp = $this->render_sections('plugin');

		if($tmp) $text .= $tmp;
		else $text .= $empty;

		$text .= "
						</tbody>
					</table>
					<div class='buttons-bar center'>
						".$this->_frm->admin_button('update', LAN_UPDATE, 'update')."
					</div>
				</fieldset>
			</form>
		";

		$this->_api->ns->tablerender(PAGE_NAME, $emessage->render().$text);
	}

	function render_sections($id)
	{

		if($id == 'core')
		{
			$sections = $this->get_core_sections();
		} else
		{
			$sections = $this->_plug->getall(1);
		}

		$ret = '';
		foreach ($sections as $section)
		{
			if($id == 'core' && !is_readable(e_FILE.'e_url/core/'.$section['core_path'])) continue;
			elseif($id == 'plugin' && !is_readable(e_PLUGIN.$section['plugin_path'].'/e_url')) continue;
			$ret .= $this->render_section($id, $section);
		}

		return $ret;
	}

	function render_section($id, $section)
	{
		$this->normalize($id, $section);

		$text .= "
			<tr>
				<td class='label'>{$section['name']}</td>
				<td class='control'>
					".$this->render_section_radio($id, $section)."
		";
		$text .= "
				</td>
			</tr>
		";
		return $text;
	}

	function render_section_radio($id, $section)
	{
		global $pref;
		//DEFAULT
		$checked_def = varset($pref['url_config'][$section['path']]) ? '' : ' checked="checked"';
		$def = "
			<div class='field-spacer'>
				<input type='radio' class='radio' id='{$section['path']}-default' name='cprofile[{$section['path']}]' value='0'{$checked_def} /><label for='{$section['path']}-default'>".LAN_EURL_DEFAULT."</label>
			</div>
		";

		//CUSTOM - CENTRAL REPOSITORY
		$udefined_id = $id.'-custom:'.$section['path'];
		$udefined_path = e_FILE."e_url/custom/{$id}/{$section['path']}/";
		$need_save = false; $checked = false;
		$custom = '';
		if(is_readable($udefined_path))
		{
			//Search the central url config repository - one config to rull them all
			if($pref['url_config'][$section['path']])
			{
				$pref['url_config'][$section['path']] = $udefined_id;
				$need_save = true;
			}

			$checked = $pref['url_config'][$section['path']] == $udefined_id ? ' checked="checked"' : '';
			$custom = "
				<input type='radio' class='radio' id='{$section['path']}-custom' name='cprofile[{$section['path']}]' value='{$udefined_id}'{$checked} /><label for='{$section['path']}-custom'>".LAN_EURL_UDEFINED."</label>
				<a href='#{$section['path']}-custom-info' class='e-expandit' title='".LAN_EURL_INFOALT."'><img src='".e_IMAGE_ABS."admin_images/info_16.png' alt='' /></a>
				<div class='e-hideme' id='{$section['path']}-custom-info'>
				<div class='indent'>
					".LAN_EURL_UDEFINED_INFO."<br />
					<strong>".LAN_EURL_LOCATION."</strong> ".e_FILE_ABS."e_url/custom/{$id}/{$section['path']}/"."
				</div>
				</div>
			";
		}


		//CUSTOM PROFILES - PLUGINS ONLY
		$config_profiles = ''; $profile_id = '';
		if($id == 'plugin')
			$profile_path = e_PLUGIN."{$section['path']}/e_url/";
		else
			$profile_path = e_FILE."e_url/core/{$section['path']}/";

		$config_profiles_array = $this->get_plug_profiles($profile_path);
		//Search for custom url config released with the plugin
		if($config_profiles_array)
		{
			foreach ($config_profiles_array as $config_profile => $profile_info) {
				$profile_id = $id.'-profile:'.$config_profile;
				$checked_profile = $pref['url_config'][$section['path']] == $profile_id ? ' checked="checked"' : '';
				if($custom) $checked_profile = ' disabled="disabled"';
				$config_profiles .= "
					<input type='radio' class='radio' id='{$section['path']}-profile-{$config_profile}' name='cprofile[{$section['path']}]' value='{$profile_id}'{$checked_profile} /><label for='{$section['path']}-profile-{$config_profile}'>".LAN_EURL_PROFILE." [".varsettrue($profile_info['title'], $config_profile)."]</label>
					<a href='#{$section['path']}-profile-{$config_profile}-info' class='e-expandit' title='".LAN_EURL_INFOALT."'><img class='icon action' src='".e_IMAGE_ABS."admin_images/info_16.png' alt='' /></a>
					<div class='e-hideme' id='{$section['path']}-profile-{$config_profile}-info'>
						<div class='indent'>
							".(varsettrue($profile_info['title']) ? '<strong>'.$profile_info['title'].'</strong><br /><br />' : '')."
							".varsettrue($profile_info['description'], LAN_EURL_PROFILE_INFO)."<br /><br />
							<strong>".LAN_EURL_LOCATION."</strong> ".str_replace(array(e_PLUGIN, e_FILE), array(e_PLUGIN_ABS, e_FILE_ABS), $profile_path)."{$config_profile}/
						</div>
					</div>
				";
			}

		}

		$this->render_shutdown($need_save);

		return $def.$config_profiles.$custom;
	}

	function get_plug_profiles($path)
	{
		$tmp = $this->_fl->get_dirs($path, '', array('CVS', '.svn'));
		$ret = array();
		foreach ($tmp as $s) {
			$ret[$s] = $this->parse_config_xml($path.$s.'/profile.xml');
		}

		return $ret;
	}

	function parse_config_xml($path)
	{
		require_once(e_HANDLER.'xml_class.php');
		$xml = new xmlClass;
		$parsed = $xml->loadXMLfile($path, true, true);

		//Load Lan file if required
		if($parsed && varsettrue($parsed['adminLan'])) {
			include_lan($parsed['adminLan']);
		}
		return $parsed;
	}

	function render_shutdown($save)
	{
		global $pref, $emessage;
		if($save && !isset($_POST['update']))
		{
			if(save_prefs())
			{
				$emessage->add(LAN_EURL_AUTOSAVE);
			}

		}
	}

	function get_core_sections()
	{
		$core_def = array(
			'core' => 		array("core_name" => LAN_EURL_CORE_MAIN, 'core_path' => 'core'),
			'news' => 		array("core_name" => LAN_EURL_CORE_NEWS, 'core_path' => 'news'),
			'download' => 	array("core_name" => LAN_EURL_CORE_DOWNLOADS, 'core_path' => 'download'),
			'user' => 		array("core_name" => LAN_EURL_CORE_USERS, 'core_path' => 'user')
		);

		return $core_def;
	}

	function normalize($id, &$section)
	{
		$tmp = $section;
		foreach ($tmp as $k => $v)
		{
			$section[str_replace($id.'_', '', $k)] = $v;
			unset($section[$k]);
		}
	}

	function update()
	{
		global $pref;
		$pref['url_config'] = $_POST['cprofile'];
		return save_prefs();
	}
}

/*
function headerjs()
{

	$js = "
	<script type='text/javascript'>

	</script>";

	return $js;

}*/
?>