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
 * $Revision: 1.1 $
 * $Date: 2008-12-02 00:32:30 $
 * $Author: secretr $
*/

require_once('../class2.php');
/*if (!getperms('')) { FIXME
	header('location:'.e_BASE.'index.php');
	exit;
}*/

$e_sub_cat = 'eurl';
require_once(e_ADMIN.'auth.php');

$urlc = new admin_url_config();

if (isset($_POST['update']))
{
	$res = $urlc->update();
	$plug_message = $res ? LAN_UPDATED : ($res === 0 ? LAN_NO_CHANGE : LAN_UPDATED_FAILED);
	$plug_message = "<div class='center clear'>".$plug_message."</div><br />";
}

//var_dump($pref['url_config'], $e107->url->getUrl('pm', 'main', array('f'=>'box', 'box'=>2)));

$urlc->renderPage();
require_once(e_ADMIN.'footer.php');

class admin_url_config {
	
	var $_rs;
	var $_plug;
	var $_api;
	
	function admin_url_config() 
	{
		global $e107;
		require_once(e_HANDLER.'plugin_class.php');
		require_once(e_HANDLER.'file_class.php');
		require_once(e_HANDLER.'form_handler.php');
		$this->_rs = new form();
		$this->_plug = new e107plugin();
		$this->_fl = new e_file();
		$this->_api = &$e107;
	}

	function renderPage() {
		global $plug_message;
		$text = "<div class='center'>
		{$plug_message}
		<form action='".e_SELF."' method='post' id='urlconfig-form'>
		<table style='".ADMIN_WIDTH."' class='fborder admin-config'>
		";

		$text .= "
		<tbody>
		<tr>
			<td colspan='2' class='forumheader'>Configure Core URLs</td>
		</tr>";

		$text .= $this->render_sections('core');
		
		$text .= "
		<tr>
			<td colspan='2' class='forumheader'>Configure Plugin URLs</td>
		</tr>";

		$text .= $this->render_sections('plugin');

		$text .= "
		
		<tr>
			<td colspan='2' class='forumheader tfoot center'>".$this->_rs->form_button('submit', 'update', LAN_UPDATE)."</td>
		</tr>
		</tbody>
		</table>
		</form>
		</div>";

		$this->_api->ns->tablerender('Manage Site URLs', $text);
	}

	function render_sections($id) {
		
		if($id == 'core') {
			$sections = $this->get_core_sections();
		} else {
			$sections = $this->_plug->getall(1);
		}
		
		$ret = '';
		foreach ($sections as $section) {
			if($id == 'core' && !is_readable(e_FILE.'e_url/core/'.$section['core_path'])) continue;
			elseif($id == 'plugin' && !is_readable(e_PLUGIN.$section['plugin_path'].'/e_url')) continue;
			$ret .= $this->render_section($id, $section);
		}
		
		return $ret;
	}

	function render_section($id, $section) 
	{
		
		$this->normalize($id, $section); //var_dump($id, $section);
		$text .= "
			<tr>
				<td class='forumheader3' style='width: 30%'>{$section['name']}</td>
				<td class='forumheader3' style='width: 70%; white-space: nowrap'>
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
			<input type='radio' id='{$section['path']}-default' name='cprofile[{$section['path']}]' value='0'{$checked_def} />
			<label for='{$section['path']}-default'>Default</label>
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
				<div class='clear'><!-- --></div>
				<input type='radio' id='{$section['path']}-custom' name='cprofile[{$section['path']}]' value='{$udefined_id}'{$checked} />
				<label for='{$section['path']}-custom'>User Defined Config</label>
				<a href='#{$section['path']}-custom-info' class='e-expandit' title='Info'><img src='".e_IMAGE_ABS."admin_images/docs_16.png' alt='' /></a>
				<div class='e-hideme' id='{$section['path']}-custom-info'>
				<div class='indent'>
					User defined URL configuration - overrides (disables) all custom configuration profiles.<br />
					Remove the User defined configuration folder to enable the custom configuration profiles.<br />
					<strong>Location:</strong> ".e_FILE_ABS."e_url/custom/{$id}/{$section['path']}/"."
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
			foreach ($config_profiles_array as $config_profile) {
				$profile_id = $id.'-profile:'.$config_profile; 
				$checked_profile = $pref['url_config'][$section['path']] == $profile_id ? ' checked="checked"' : '';
				if($custom) $checked_profile = ' disabled="disabled"'; 
				$config_profiles .= "
					<div class='clear'><!-- --></div>
					<input type='radio' id='{$section['path']}-profile-{$config_profile}' name='cprofile[{$section['path']}]' value='{$profile_id}'{$checked_profile} />
					<label for='{$section['path']}-profile-{$config_profile}'>
						Config Profile [{$config_profile}] 
					</label>
					<a href='#{$section['path']}-profile-{$config_profile}-info' class='e-expandit' title='Info'><img src='".e_IMAGE_ABS."admin_images/docs_16.png' alt='' /></a>
					<div class='e-hideme' id='{$section['path']}-profile-{$config_profile}-info'>
						<div class='indent'>
							Under Construction - profile.xml<br />
							<strong>Location:</strong> ".str_replace(array(e_PLUGIN, e_FILE), array(e_PLUGIN_ABS, e_FILE_ABS), $profile_path)."{$config_profile}/
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
		$ret = $this->_fl->get_dirs($path, '', array('CVS', '.svn'));
		return $ret;
	}
	
	function render_shutdown($now)
	{
		global $pref;
		if($now && !isset($_POST['update']))
		{
			save_prefs();
		}
	}
	
	function get_core_sections() 
	{
		$core_def = array(
			'news' => 		array("core_name" => 'News', 'core_path' => 'news'),
			'downloads' => 	array("core_name" => 'Downloads', 'core_path' => 'downloads')
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


function headerjs()
{

	$js = "
	<script type='text/javascript'>

	</script>";

	return $js;
}
?>