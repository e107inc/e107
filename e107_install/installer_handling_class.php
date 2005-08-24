<?php

/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_install/installer_handling_class.php,v $
|     $Revision: 1.14 $
|     $Date: 2005-08-24 06:21:24 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/

class e_install {

	var $required_php = "4.3";

	var $paths;
	var $template;
	var $debug_info;
	var $e107;
	var $previous_steps;
	var $stage;
	var $post_data;

	function e_install() {
		$this->template = new SimpleTemplate();
		while (@ob_end_clean());
		global $e107;
		$this->e107 = $e107;
		if($_POST['previous_steps']) {
			$this->previous_steps = unserialize(base64_decode($_POST['previous_steps']));
			unset($_POST['previous_steps']);
		}
		$this->post_data = $_POST;
	}

	function raise_error($details){
		$this->debug_info[] = array (
		'info' => array (
		'details' => $details,
		'backtrace' => debug_backtrace()
		)
		);
	}

	function stage_1(){
		global $e_forms;
		$this->stage = 1;
		$this->get_lan_file();
		$this->template->SetTag("installation_heading", LANINS_001);
		$this->template->SetTag("stage_pre", LANINS_002);
		$this->template->SetTag("stage_num", LANINS_003);
		$this->template->SetTag("stage_title", LANINS_004);
		$e_forms->start_form("language_select", $_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING'] == "debug" ? "?debug" : ""));
		$e_forms->add_select_item("language", $this->get_languages(), "English");
		$this->finish_form();
		$e_forms->add_button("submit", LANINS_006);
		$this->template->SetTag("stage_content", "<div style='text-align: center;'><label for='language'>".LANINS_005."</label>\n<br /><br /><br />\n".$e_forms->return_form()."</div>");
	}

	function stage_2(){
		global $e_forms;
		$this->previous_steps['language'] = $_POST['language'];
		$this->stage = 2;
		$this->get_lan_file();
		$this->template->SetTag("installation_heading", LANINS_001);
		$this->template->SetTag("stage_pre", LANINS_002);
		$this->template->SetTag("stage_num", LANINS_007);
		$this->template->SetTag("stage_title", LANINS_008);
		$not_writable = $this->check_writable_perms();
		if(count($not_writable)) {
			$perms_pass = false;
			unset($perms_errors);
			foreach ($not_writable as $file)
			{
				$perms_errors .= (substr($file, -1) == "/" ? LANINS_010a : LANINS_010)."...<br /><b>{$file}</b><br />\n";
			}
			$perms_notes = LANINS_018;
		} else {
			$perms_pass = true;
			$perms_errors = "&nbsp;";
			$perms_notes = LANINS_017;
		}
		if(!function_exists("mysql_connect")) {
			$version_fail = true;
			$mysql_note = LANINS_011;
			$mysql_help = LANINS_012;
		} elseif (!mysql_get_server_info()) {
			$mysql_note = LANINS_011;
			$mysql_help = LANINS_013;
		} else {
			$mysql_note = mysql_get_server_info();
			$mysql_help = LANINS_017;
		}
		if(!function_exists("utf8_encode")) {
			$xml_installed = false;
		} else {
			$xml_installed = true;
		}

		$php_version = phpversion();
		if(version_compare($php_version, $this->required_php, ">=")) {
			$php_help = LANINS_017;
		} else {
			$php_help = LANINS_019;
		}
		$e_forms->start_form("versions", $_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING'] == "debug" ? "?debug" : ""));
		if(!$perms_pass) {
			$e_forms->add_hidden_data("language", $this->post_data['language']);
			$e_forms->add_button("retest_perms", LANINS_009);
			$this->stage = 1; // make the installer jump back a step
		} elseif ($perms_pass && !$version_fail && $xml_installed) {
			$e_forms->add_button("continue_install", LANINS_020);
		}
		$output = "
			<table style='width: 100%; margin-left: auto; margin-right: auto;'>
			  <tr>
			    <td style='width: 20%;'>".LANINS_014."</td>
			    <td style='width: 40%;'>{$perms_errors}</td>
			    <td style='width: 40%;'>{$perms_notes}</td>
			  </tr>
			  <tr>
			    <td>".LANINS_015."</td>
			    <td>{$php_version}</td>
			    <td>{$php_help}</td>
			  </tr>
			  <tr>
			    <td>".LANINS_016."</td>
			    <td>{$mysql_note}</td>
			    <td>{$mysql_help}</td>
			  </tr>
			  <tr>
			    <td>".LANINS_050."</td>
			    <td>".($xml_installed ? LANINS_051 : LANINS_052)."</td>
			    <td>".($xml_installed ? LANINS_017 : LANINS_053."<a href='http://php.net/manual/en/ref.xml.php' target='_blank'>php.net</a>".LANINS_054)."</td>
			  </tr>
			</table>\n<br /><br />\n\n";
		$this->finish_form();
		$this->template->SetTag("stage_content", $output.$e_forms->return_form());
	}

	function stage_3(){
		global $e_forms;
		$this->stage = 3;
		$this->get_lan_file();
		$this->template->SetTag("installation_heading", LANINS_001);
		$this->template->SetTag("stage_pre", LANINS_002);
		$this->template->SetTag("stage_num", LANINS_021);
		$this->template->SetTag("stage_title", LANINS_022);
		$page_info = nl2br(LANINS_023);
		$e_forms->start_form("versions", $_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING'] == "debug" ? "?debug" : ""));
		$output = "
			<br /><br />
			<div style='width: 100%; padding-left: auto; padding-right: auto;'>
			  <table cellspacing='0'>
			    <tr>
			      <td style='border-top: 1px solid #999;' class='row-border'><label for='server'>".LANINS_024."</label></td>
			      <td style='border-top: 1px solid #999;' class='row-border'><input class='tbox' type='text' id='server' name='server' size='40' value='localhost' maxlength='100' /></td>
				  <td style='width: 40%; border-top: 1px solid #999;' class='row-border'>".LANINS_030."</td>
			    </tr>
			    <tr>
			      <td class='row-border'><label for='name'>".LANINS_025."</label></td>
			      <td class='row-border'><input class='tbox' type='text' name='name' id='name' size='40' value='' maxlength='100' /></td>
				  <td class='row-border'>".LANINS_031."</td>
			    </tr>
			    <tr>
			      <td class='row-border'><label for='password'>".LANINS_026."</label></td>
			      <td class='row-border'><input class='tbox' type='password' name='password' size='40' id='password' value='' maxlength='100' /></td>
				  <td class='row-border'>".LANINS_032."</td>
			    </tr>
			    <tr>
			      <td class='row-border'><label for='db'>".LANINS_027."</label></td>
			      <td class='row-border'><input type='text' name='db' size='20' id='db' value='' maxlength='100' />
			      <label class='defaulttext'><input type='checkbox' name='createdb' value='1' />".LANINS_028."</label></td>
				  <td class='row-border'>".LANINS_033."</td>
			    </tr>
			    <tr>
			      <td class='row-border'><label for='prefix'>".LANINS_029."</label></td>
			      <td class='row-border'><input type='text' name='prefix' size='20' id='prefix' value='e107_'  maxlength='100' /></td>
				  <td class='row-border'>".LANINS_034."</td>
			    </tr>
			  </table>
			</div>
			<br /><br />\n";
		$e_forms->add_plain_html($output);
		$this->finish_form();
		$e_forms->add_button("submit", LANINS_035);
		$this->template->SetTag("stage_content", $page_info.$e_forms->return_form());
	}

	function stage_4(){
		global $e_forms;
		$this->stage = 4;
		$this->get_lan_file();
		$this->template->SetTag("installation_heading", LANINS_001);
		$this->template->SetTag("stage_pre", LANINS_002);
		$this->template->SetTag("stage_num", LANINS_036);
		$this->previous_steps['mysql']['server'] = $_POST['server'];
		$this->previous_steps['mysql']['user'] = $_POST['name'];
		$this->previous_steps['mysql']['password'] = $_POST['password'];
		$this->previous_steps['mysql']['db'] = $_POST['db'];
		$this->previous_steps['mysql']['createdb'] = $_POST['createdb'];
		$this->previous_steps['mysql']['prefix'] = $_POST['prefix'];
		if($this->previous_steps['mysql']['server'] == "" || $this->previous_steps['mysql']['user'] == "" | $this->previous_steps['mysql']['db'] == "") {
			$this->stage = 3;
			$this->template->SetTag("stage_num", LANINS_021);
			$e_forms->start_form("versions", $_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING'] == "debug" ? "?debug" : ""));
			$head = LANINS_039."<br /><br />\n";
			$output = "
			<br /><br />
			<div style='width: 100%; padding-left: auto; padding-right: auto;'>
			  <table cellspacing='0'>
			    <tr>
			      <td style='border-top: 1px solid #999;' class='row-border'><label for='server'>".LANINS_024."</label></td>
			      <td style='border-top: 1px solid #999;' class='row-border'><input class='tbox' type='text' id='server' name='server' size='40' value='{$this->previous_steps['mysql']['server']}' maxlength='100' /></td>
				  <td style='width: 40%; border-top: 1px solid #999;' class='row-border'>".LANINS_030."</td>
			    </tr>
			    <tr>
			      <td class='row-border'><label for='name'>".LANINS_025."</label></td>
			      <td class='row-border'><input class='tbox' type='text' name='name' id='name' size='40' value='{$this->previous_steps['mysql']['user']}' maxlength='100' /></td>
				  <td class='row-border'>".LANINS_031."</td>
			    </tr>
			    <tr>
			      <td class='row-border'><label for='password'>".LANINS_026."</label></td>
			      <td class='row-border'><input class='tbox' type='password' name='password' id='password' size='40' value='{$this->previous_steps['mysql']['password']}' maxlength='100' /></td>
				  <td class='row-border'>".LANINS_032."</td>
			    </tr>
			    <tr>
			      <td class='row-border'><label for='db'>".LANINS_027."</label></td>
			      <td class='row-border'><input type='text' name='db' id='db' size='20' value='{$this->previous_steps['mysql']['db']}' maxlength='100' />
			        <label class='defaulttext'><input type='checkbox' name='createdb'".($this->previous_steps['mysql']['createdb'] == 1 ? " checked='checked'" : "")." value='1'>".LANINS_028."</lebel></td>
				  <td class='row-border'>".LANINS_033."</td>
			    </tr>
			    <tr>
			      <td class='row-border'><label for='prefix'>".LANINS_029."</label></td>
			      <td class='row-border'><input type='text' name='prefix' id='prefix' size='20' value='{$this->previous_steps['mysql']['prefix']}'  maxlength='100' /></td>
				  <td class='row-border'>".LANINS_034."</td>
			    </tr>
			  </table>
			</div>
			<br /><br />\n";
			$e_forms->add_plain_html($output);
			$e_forms->add_button("submit", LANINS_035);
			$this->template->SetTag("stage_title", LANINS_040);
		} else {
			$success = true;
			$this->template->SetTag("stage_title", LANINS_037.($this->previous_steps['mysql']['createdb'] == 1 ? LANINS_038 : ""));
			if (!@mysql_connect($this->previous_steps['mysql']['server'], $this->previous_steps['mysql']['user'], $this->previous_steps['mysql']['password'])) {
				if($_SERVER['QUERY_STRING'] == "debug"){
					$this->raise_error("Couldn't connect to mysql server, error details: ".mysql_error());
				}
				$success = false;
				$page_content = LANINS_041;
			} else {
				$page_content = LANINS_042;
				if($this->previous_steps['mysql']['createdb'] == 1) {
					if (!mysql_query("CREATE DATABASE ".$this->previous_steps['mysql']['db'])) {
						if($_SERVER['QUERY_STRING'] == "debug"){
							$this->raise_error("Couldn't create database, error deatils: ".mysql_error());
						}
						$success = false;
						$page_content .= "<br /><br />".LANINS_043;
					} else {
						$page_content .= "<br /><br />".LANINS_044;
					}
				}
			}
			if($success){
				$e_forms->start_form("versions", $_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING'] == "debug" ? "?debug" : ""));
				$page_content .= "<br /><br />".LANINS_045."<br /><br />";
				$e_forms->add_button("submit", LANINS_035);
			}
			$head = $page_content;
		}
		$this->finish_form();
		$this->template->SetTag("stage_content", $head.$e_forms->return_form());
	}

	function stage_5(){
		global $e_forms;
		$this->stage = 5;
		$this->get_lan_file();
		$this->template->SetTag("installation_heading", LANINS_001);
		$this->template->SetTag("stage_pre", LANINS_002);
		$this->template->SetTag("stage_num", LANINS_046);
		$this->template->SetTag("stage_title", LANINS_047);
		$e_forms->start_form("admin_info", $_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING'] == "debug" ? "?debug" : ""));
		$output = "
			<div style='width: 100%; padding-left: auto; padding-right: auto;'>
			  <table cellspacing='0'>
			    <tr>
			      <td class='row-border'><label for='u_name'>".LANINS_072."</label></td>
			      <td class='row-border'><input class='tbox' type='text' name='u_name' id='u_name' size='30' value='".($this->previous_steps['admin']['user'] ? $this->previous_steps['admin']['user'] : "")."' maxlength='60' /></td>
				  <td class='row-border'>".LANINS_073."</td>
			    </tr>
			    <tr>
			      <td class='row-border'><label for='d_name'>".LANINS_074."</label></td>
			      <td class='row-border'><input class='tbox' type='text' name='d_name' id='d_name' size='30' value='".($this->previous_steps['admin']['display'] ? $this->previous_steps['admin']['display'] : "")."' maxlength='60' /></td>
				  <td class='row-border'>".LANINS_075."</td>
			    </tr>
			    <tr>
			      <td class='row-border'><label for='pass1'>".LANINS_076."</label></td>
			      <td class='row-border'><input type='password' name='pass1' size='30' id='pass1' value='' maxlength='60' /></td>
				  <td class='row-border'>".LANINS_077."</td>
			    </tr>
			    <tr>
			      <td class='row-border'><label for='pass2'>".LANINS_078."</label></td>
			      <td class='row-border'><input type='password' name='pass2' size='30' id='pass2' value='' maxlength='60' /></td>
				  <td class='row-border'>".LANINS_079."</td>
			    </tr>
			    <tr>
			      <td class='row-border'><label for='email'>".LANINS_080."</label></td>
			      <td class='row-border'><input type='text' name='email' size='30' id='email' value='".($this->previous_steps['admin']['email'] ? $this->previous_steps['admin']['email'] : LANINS_082)."' maxlength='100' /></td>
				  <td class='row-border'>".LANINS_081."</td>
			    </tr>
			  </table>
			</div>
			<br /><br />\n";
		$e_forms->add_plain_html($output);
		$this->finish_form();
		$e_forms->add_button("submit", LANINS_035);
		$this->template->SetTag("stage_content", $e_forms->return_form());
	}

	function stage_6(){
		global $e_forms;
		$this->get_lan_file();
		$this->stage = 6;

		$_POST['u_name'] = str_replace(array("'", '"'), "", $_POST['u_name']);
		$_POST['d_name'] = str_replace(array("'", '"'), "", $_POST['d_name']);

		$this->previous_steps['admin']['user'] = $_POST['u_name'];
		if ($_POST['d_name'] == "") {
			$this->previous_steps['admin']['display'] = $_POST['u_name'];
		} else {
			$this->previous_steps['admin']['display'] = $_POST['d_name'];
		}
		$this->previous_steps['admin']['email'] = $_POST['email'];
		$this->previous_steps['admin']['password'] = $_POST['pass1'];

		if($_POST['pass1'] != $_POST['pass2']) {
			$this->template->SetTag("installation_heading", LANINS_001);
			$this->template->SetTag("stage_num", LANINS_046);
			$this->template->SetTag("stage_pre", LANINS_002);
			$this->template->SetTag("stage_title", LANINS_047);
			$e_forms->start_form("admin_info", $_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING'] == "debug" ? "?debug" : ""));
			$page = LANINS_049."<br />".($_SERVER['QUERY_STRING'] == "debug" ? print_a($_POST, true) : "")."<br />";

			$this->finish_form(5);
			$e_forms->add_button("submit", LANINS_048);
		} else {

		$this->template->SetTag("installation_heading", LANINS_001);
		$this->template->SetTag("stage_pre", LANINS_002);
		$this->template->SetTag("stage_num", LANINS_056);
		$this->template->SetTag("stage_title", LANINS_055);

		$e_forms->start_form("confirmation", $_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING'] == "debug" ? "?debug" : ""));
		$page = nl2br(LANINS_057);
		$this->finish_form();
		$e_forms->add_button("submit", LANINS_035);
		}

		$this->template->SetTag("stage_content", $page.$e_forms->return_form());
	}

	function stage_7(){
		global $e_forms;
		$this->get_lan_file();

		$this->stage = 7;

		$this->template->SetTag("installation_heading", LANINS_001);
		$this->template->SetTag("stage_pre", LANINS_002);
		$this->template->SetTag("stage_num", LANINS_058);
		$this->template->SetTag("stage_title", LANINS_071);

		$config_file = "<?php

/*
+----------------------------------------------------+
|   e107 website system
|   e107_config.php
|
|   ©Steve Dunstan 2001-2002
|   http://e107.org
|   jalist@e107.org
|
|   Released under the terms and conditions of the
|   GNU General Public License (http://gnu.org).
+----------------------------------------------------+
This file has been generated by the installation script.
*/

\$mySQLserver    = \"{$this->previous_steps['mysql']['server']}\";
\$mySQLuser      = \"{$this->previous_steps['mysql']['user']}\";
\$mySQLpassword  = \"{$this->previous_steps['mysql']['password']}\";
\$mySQLdefaultdb = \"{$this->previous_steps['mysql']['db']}\";
\$mySQLprefix    = \"{$this->previous_steps['mysql']['prefix']}\";

\$ADMIN_DIRECTORY     = \"{$this->e107->e107_dirs['ADMIN_DIRECTORY']}\";
\$FILES_DIRECTORY     = \"{$this->e107->e107_dirs['FILES_DIRECTORY']}\";
\$IMAGES_DIRECTORY    = \"{$this->e107->e107_dirs['IMAGES_DIRECTORY']}\";
\$THEMES_DIRECTORY    = \"{$this->e107->e107_dirs['THEMES_DIRECTORY']}\";
\$PLUGINS_DIRECTORY   = \"{$this->e107->e107_dirs['PLUGINS_DIRECTORY']}\";
\$HANDLERS_DIRECTORY  = \"{$this->e107->e107_dirs['HANDLERS_DIRECTORY']}\";
\$LANGUAGES_DIRECTORY = \"{$this->e107->e107_dirs['LANGUAGES_DIRECTORY']}\";
\$HELP_DIRECTORY      = \"{$this->e107->e107_dirs['HELP_DIRECTORY']}\";
\$DOWNLOADS_DIRECTORY = \"{$this->e107->e107_dirs['DOWNLOADS_DIRECTORY']}\";

?>";

		$config_result = $this->write_config($config_file);
		$e_forms->start_form("confirmation", "index.php");
		if ($config_result) {
			$page = $config_result."<br />";
		} else {
			$errors = $this->create_tables();
			if ($errors == true) {
				$page = $errors."<br />";
			} else {
				$page = nl2br(LANINS_069)."<br />";
				$e_forms->add_button("submit", LANINS_035);
			}
		}
		$this->finish_form();
		$this->template->SetTag("stage_content", $page.$e_forms->return_form());
	}

	function get_lan_file(){
		$this->lan_file = "{$this->e107->e107_dirs['LANGUAGES_DIRECTORY']}{$this->previous_steps['language']}/lan_installer.php";
		if(is_readable($this->lan_file)){
			require($this->lan_file);
		} elseif(is_readable("{$this->e107->e107_dirs['LANGUAGES_DIRECTORY']}English/lan_installer.php")) {
			include("{$this->e107->e107_dirs['LANGUAGES_DIRECTORY']}English/lan_installer.php");
		} else {
			$this->raise_error("Fatal: Could not get valid language file for installation.");
		}
	}

	function get_languages() {
		$handle = opendir("{$this->e107->e107_dirs['LANGUAGES_DIRECTORY']}");
		while ($file = readdir($handle)) {
			if ($file != "." && $file != ".." && $file != "/" && $file != "CVS") {
				if(file_exists("./{$this->e107->e107_dirs['LANGUAGES_DIRECTORY']}{$file}/lan_installer.php")){
					$lanlist[] = $file;
				}
			}
		}
		closedir($handle);
		return $lanlist;
	}

	function finish_form($force_stage = false) {
		global $e_forms;
		if($this->previous_steps) {
			$e_forms->add_hidden_data("previous_steps", base64_encode(serialize($this->previous_steps)));
		}
		$e_forms->add_hidden_data("stage", ($force_stage ? $force_stage : ($this->stage + 1)));
	}

	function popup_info($info){
		global $installer_folder_name;
		return "&nbsp;&nbsp;<img src='".e_HTTP."{$installer_folder_name}images/info.png' alt='Info Image (Hover curser to read info)' title='{$info}' />";
	}

	function check_writable_perms(){
		$data = file_get_contents("./".$this->e107->e107_dirs['INSTALLER']."writable_file_list.txt");
		foreach ($this->e107->e107_dirs as $dir_name => $value) {
			$find[] = "{\${$dir_name}}";
			$replace[] = "./$value";
		}
		$data = str_replace($find, $replace, $data);
		$files = explode("|", trim($data));
		foreach ($files as $file) {
			if(!is_writable($file)) {
				$bad_files[] = str_replace("./", "", $file);
			}
		}
		return $bad_files;
	}

	function create_tables() {

		mysql_connect($this->previous_steps['mysql']['server'], $this->previous_steps['mysql']['user'], $this->previous_steps['mysql']['password']);
		mysql_select_db($this->previous_steps['mysql']['db']);


		$filename = "{$this->e107->e107_dirs['ADMIN_DIRECTORY']}sql/core_sql.php";
		$fd = fopen ($filename, "r");
		$sql_data = fread($fd, filesize($filename));
		fclose ($fd);

		if (!$sql_data) {
			return nl2br(LANINS_060)."<br /><br />";
		}

		preg_match_all("/create(.*?)myisam;/si", $sql_data, $result );

		foreach ($result[0] as $sql_table) {
			preg_match("/CREATE TABLE\s(.*?)\s\(/si", $sql_table, $match);
			$tablename = $match[1];
			preg_match_all("/create(.*?)myisam;/si", $sql_data, $result );
			$sql_table = preg_replace("/create table\s/si", "CREATE TABLE {$this->previous_steps['mysql']['prefix']}", $sql_table);
			if (!mysql_query($sql_table)) {
				return nl2br(LANINS_061);
			}
		}

		$welcome_message = "<b>".LANINS_062."</b><br /><br />";
		$welcome_message .= LANINS_063. "<br />".LANINS_064." <a href='e107_admin/admin.php'>".LANINS_065."</a>, ".LANINS_066;
		$welcome_message .= "\n\n[b]Support[/b]\ne107 Homepage: http://e107.org, ".LANINS_067."\nForums: http://e107.org/forum.php\n\n[b]Downloads[/b]\nPlugins: http://e107coders.org\nThemes: http://e107themes.org\n<br /><br />".LANINS_068."";

		$search = array("'", "'");
		$replace = array("&quot;", "&#39;");
		$welcome_message = str_replace($search, $replace, $welcome_message);
		$datestamp = time();

		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}news VALUES (0, 'Welcome to e107', '{$welcome_message}', '', '{$datestamp}', '0', '1', 1, 0, 0, 0, 0, '0', '', '', 0) ");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}news_category VALUES (0, 'Misc', 'icon5.png') ");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}links VALUES (0, 'Home', 'index.php', '', '', 1, 1, 0, 0, 0) ");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}links VALUES (0, 'Downloads', 'download.php', '', '', 1, 3, 0, 0, 0) ");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}links VALUES (0, 'Members', 'user.php', '', '', 1, 4, 0, 0, 0) ");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}links VALUES (0, 'Submit News', 'submitnews.php', '', '', 1, 5, 0, 0, 0) ");

		$e107['e107_author'] = "Steve Dunstan (jalist)";
		$e107['e107_url'] = "http://e107.org";
		$e107['e107_version'] = "v0.7CVS";
		$e107['e107_build'] = "";
		$e107['e107_datestamp'] = time();
		$tmp = serialize($e107);
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}core VALUES ('e107', '{$tmp}') ");

		$udirs = "admin/|plugins/|temp";
		$e_SELF = "http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
		$e_HTTP = preg_replace("#".$udirs."#i", "", substr($e_SELF, 0, strrpos($e_SELF, "/"))."/");
		
		$pref_language = isset($_POST['installlanguage']) ? $_POST['installlanguage'] : "English";

		if (file_exists($this->e107->e107_dirs['LANGUAGES_DIRECTORY'].$pref_language."/lan_prefs.php")) {
			include_once($this->e107->e107_dirs['LANGUAGES_DIRECTORY'].$pref_language."/lan_prefs.php");
		} else {
			include_once($this->e107->e107_dirs['LANGUAGES_DIRECTORY']."English/lan_prefs.php");
		}
		
		require_once("{$this->e107->e107_dirs['FILES_DIRECTORY']}def_e107_prefs.php");

		include_once("{$this->e107->e107_dirs['HANDLERS_DIRECTORY']}arraystorage_class.php");

		$tmp = ArrayData::WriteArray($pref);

		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}core VALUES ('SitePrefs', '{$tmp}')");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}core VALUES ('SitePrefs_Backup', '{$tmp}')");

		$emote = 'a:60:{i:0;a:1:{s:2:"&|";s:7:"cry.png";}i:1;a:1:{s:3:"&-|";s:7:"cry.png";}i:2;a:1:{s:3:"&o|";s:7:"cry.png";}i:3;a:1:{s:3:":((";s:7:"cry.png";}i:4;a:1:{s:3:"~:(";s:7:"mad.png";}i:5;a:1:{s:4:"~:o(";s:7:"mad.png";}i:6;a:1:{s:4:"~:-(";s:7:"mad.png";}i:7;a:1:{s:2:":)";s:9:"smile.png";}i:8;a:1:{s:3:":o)";s:9:"smile.png";}i:9;a:1:{s:3:":-)";s:9:"smile.png";}i:10;a:1:{s:2:":(";s:9:"frown.png";}i:11;a:1:{s:3:":o(";s:9:"frown.png";}i:12;a:1:{s:3:":-(";s:9:"frown.png";}i:13;a:1:{s:2:":D";s:8:"grin.png";}i:14;a:1:{s:3:":oD";s:8:"grin.png";}i:15;a:1:{s:3:":-D";s:8:"grin.png";}i:16;a:1:{s:2:":?";s:12:"confused.png";}i:17;a:1:{s:3:":o?";s:12:"confused.png";}i:18;a:1:{s:3:":-?";s:12:"confused.png";}i:19;a:1:{s:3:"%-6";s:11:"special.png";}i:20;a:1:{s:2:"x)";s:8:"dead.png";}i:21;a:1:{s:3:"xo)";s:8:"dead.png";}i:22;a:1:{s:3:"x-)";s:8:"dead.png";}i:23;a:1:{s:2:"x(";s:8:"dead.png";}i:24;a:1:{s:3:"xo(";s:8:"dead.png";}i:25;a:1:{s:3:"x-(";s:8:"dead.png";}i:26;a:1:{s:2:":@";s:7:"gah.png";}i:27;a:1:{s:3:":o@";s:7:"gah.png";}i:28;a:1:{s:3:":-@";s:7:"gah.png";}i:29;a:1:{s:2:":!";s:8:"idea.png";}i:30;a:1:{s:3:":o!";s:8:"idea.png";}i:31;a:1:{s:3:":-!";s:8:"idea.png";}i:32;a:1:{s:2:":|";s:11:"neutral.png";}i:33;a:1:{s:3:":o|";s:11:"neutral.png";}i:34;a:1:{s:3:":-|";s:11:"neutral.png";}i:35;a:1:{s:2:"?!";s:12:"question.png";}i:36;a:1:{s:2:"B)";s:12:"rolleyes.png";}i:37;a:1:{s:3:"Bo)";s:12:"rolleyes.png";}i:38;a:1:{s:3:"B-)";s:12:"rolleyes.png";}i:39;a:1:{s:2:"8)";s:10:"shades.png";}i:40;a:1:{s:3:"8o)";s:10:"shades.png";}i:41;a:1:{s:3:"8-)";s:10:"shades.png";}i:42;a:1:{s:2:":O";s:12:"suprised.png";}i:43;a:1:{s:3:":oO";s:12:"suprised.png";}i:44;a:1:{s:3:":-O";s:12:"suprised.png";}i:45;a:1:{s:2:":p";s:10:"tongue.png";}i:46;a:1:{s:3:":op";s:10:"tongue.png";}i:47;a:1:{s:3:":-p";s:10:"tongue.png";}i:48;a:1:{s:2:":P";s:10:"tongue.png";}i:49;a:1:{s:3:":oP";s:10:"tongue.png";}i:50;a:1:{s:3:":-P";s:10:"tongue.png";}i:51;a:1:{s:2:";)";s:8:"wink.png";}i:52;a:1:{s:3:";o)";s:8:"wink.png";}i:53;a:1:{s:3:";-)";s:8:"wink.png";}i:54;a:1:{s:4:"!ill";s:7:"ill.png";}i:55;a:1:{s:7:"!amazed";s:10:"amazed.png";}i:56;a:1:{s:4:"!cry";s:7:"cry.png";}i:57;a:1:{s:6:"!dodge";s:9:"dodge.png";}i:58;a:1:{s:6:"!alien";s:9:"alien.png";}i:59;a:1:{s:6:"!heart";s:9:"heart.png";}}';
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}core VALUES ('emote', '{$emote}') ");

		$menu_conf = 'a:23:{s:15:"comment_caption";s:15:"Latest Comments";s:15:"comment_display";s:2:"10";s:18:"comment_characters";s:2:"50";s:15:"comment_postfix";s:12:"[ more ... ]";s:13:"comment_title";i:0;s:15:"article_caption";s:8:"Articles";s:16:"articles_display";s:2:"10";s:17:"articles_mainlink";s:23:"Articles Front Page ...";s:21:"newforumposts_caption";s:18:"Latest Forum Posts";s:21:"newforumposts_display";s:2:"10";s:19:"forum_no_characters";s:2:"20";s:13:"forum_postfix";s:10:"[more ...]";s:11:"update_menu";s:20:"Update menu Settings";s:17:"forum_show_topics";s:1:"1";s:24:"newforumposts_characters";s:2:"50";s:21:"newforumposts_postfix";s:10:"[more ...]";s:19:"newforumposts_title";i:0;s:13:"clock_caption";s:11:"Date / Time";s:15:"reviews_caption";s:7:"Reviews";s:15:"reviews_display";s:2:"10";s:15:"reviews_parents";s:1:"1";s:16:"reviews_mainlink";s:21:"Review Front Page ...";s:16:"articles_parents";s:1:"1";}';
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}core VALUES ('menu_pref', '{$menu_conf}') ");

		preg_match("/^(.*?)($|-)/", mysql_get_server_info(), $mysql_version);
		if (version_compare($mysql_version[1], '4.0.1', '<')) {
			$search_prefs = 'a:11:{s:11:\"user_select\";s:1:\"1\";s:9:\"time_secs\";s:2:\"60\";s:13:\"time_restrict\";s:1:\"0\";s:8:\"selector\";i:2;s:9:\"relevance\";i:0;s:13:\"plug_handlers\";N;s:10:\"mysql_sort\";i:0;s:11:\"multisearch\";s:1:\"1\";s:6:\"google\";s:1:\"0\";s:13:\"core_handlers\";a:4:{s:4:\"news\";a:5:{s:5:\"class\";s:1:\"0\";s:9:\"pre_title\";s:1:\"0\";s:13:\"pre_title_alt\";s:0:\"\";s:5:\"chars\";s:3:\"150\";s:7:\"results\";s:2:\"10\";}s:8:\"comments\";a:5:{s:5:\"class\";s:1:\"0\";s:9:\"pre_title\";s:1:\"1\";s:13:\"pre_title_alt\";s:0:\"\";s:5:\"chars\";s:3:\"150\";s:7:\"results\";s:2:\"10\";}s:5:\"users\";a:5:{s:5:\"class\";s:1:\"0\";s:9:\"pre_title\";s:1:\"1\";s:13:\"pre_title_alt\";s:0:\"\";s:5:\"chars\";s:3:\"150\";s:7:\"results\";s:2:\"10\";}s:9:\"downloads\";a:5:{s:5:\"class\";s:1:\"0\";s:9:\"pre_title\";s:1:\"1\";s:13:\"pre_title_alt\";s:0:\"\";s:5:\"chars\";s:3:\"150\";s:7:\"results\";s:2:\"10\";}}s:17:\"comments_handlers\";a:2:{s:4:\"news\";a:3:{s:2:\"id\";i:0;s:3:\"dir\";s:4:\"core\";s:5:\"class\";s:1:\"0\";}s:8:\"download\";a:3:{s:2:\"id\";i:2;s:3:\"dir\";s:4:\"core\";s:5:\"class\";s:1:\"0\";}}}';
		} else {
			$search_prefs = 'a:11:{s:11:\"user_select\";s:1:\"1\";s:9:\"time_secs\";s:2:\"60\";s:13:\"time_restrict\";s:1:\"0\";s:8:\"selector\";i:2;s:9:\"relevance\";i:0;s:13:\"plug_handlers\";N;s:10:\"mysql_sort\";i:1;s:11:\"multisearch\";s:1:\"1\";s:6:\"google\";s:1:\"0\";s:13:\"core_handlers\";a:4:{s:4:\"news\";a:5:{s:5:\"class\";s:1:\"0\";s:9:\"pre_title\";s:1:\"0\";s:13:\"pre_title_alt\";s:0:\"\";s:5:\"chars\";s:3:\"150\";s:7:\"results\";s:2:\"10\";}s:8:\"comments\";a:5:{s:5:\"class\";s:1:\"0\";s:9:\"pre_title\";s:1:\"1\";s:13:\"pre_title_alt\";s:0:\"\";s:5:\"chars\";s:3:\"150\";s:7:\"results\";s:2:\"10\";}s:5:\"users\";a:5:{s:5:\"class\";s:1:\"0\";s:9:\"pre_title\";s:1:\"1\";s:13:\"pre_title_alt\";s:0:\"\";s:5:\"chars\";s:3:\"150\";s:7:\"results\";s:2:\"10\";}s:9:\"downloads\";a:5:{s:5:\"class\";s:1:\"0\";s:9:\"pre_title\";s:1:\"1\";s:13:\"pre_title_alt\";s:0:\"\";s:5:\"chars\";s:3:\"150\";s:7:\"results\";s:2:\"10\";}}s:17:\"comments_handlers\";a:2:{s:4:\"news\";a:3:{s:2:\"id\";i:0;s:3:\"dir\";s:4:\"core\";s:5:\"class\";s:1:\"0\";}s:8:\"download\";a:3:{s:2:\"id\";i:2;s:3:\"dir\";s:4:\"core\";s:5:\"class\";s:1:\"0\";}}}';
		}
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}core VALUES ('search_prefs', '{$search_prefs}') ");

		$notify_prefs = "array ('event' => array ('usersup' => array ('type' => 'off', 'class' => '253', 'email' => '',),'userveri' => array ( 'type' => 'off', 'class' => '253', 'email' => '', ), 'login' => array ( 'type' => 'off', 'class' => '253', 'email' => '', ), 'logout' => array ( 'type' => 'off', 'class' => '253', 'email' => '', ), 'flood' => array ( 'type' => 'off', 'class' => '253', 'email' => '', ), 'subnews' => array ( 'type' => 'off', 'class' => '253', 'email' => '', ), 'newspost' => array ( 'type' => 'off', 'class' => '253', 'email' => '', ), 'newsupd' => array ( 'type' => 'off', 'class' => '253', 'email' => '', ), 'newsdel' => array ( 'type' => 'off', 'class' => '253', 'email' => '', ), ), )";
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}core VALUES ('notify_prefs', '{$notify_prefs}') ");

		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}banner VALUES (0, 'e107', 'e107login', 'e107password', 'e107.jpg', 'http://e107.org', 0, 0, 0, 0, 0, 0, '', 'campaign_one') ");

		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}menus VALUES (0, 'login_menu', 1, 1, 0, '', 'login_menu')");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}menus VALUES (0, 'rss_menu', 2, 2, 0, '', 'rss_menu')");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}menus VALUES (0, 'online_menu', 2, 1, 0, '', 'online_menu')");


		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}userclass_classes VALUES (1, 'PRIVATEMENU', 'Grants access to private menu items')");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}userclass_classes VALUES (2, 'PRIVATEFORUM1', 'Example private forum class')");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}plugin VALUES (0, 'Integrity Check', '0.03', 'integrity_check', 1) ");

		// Create the admin user
		$userp = "1, '{$this->previous_steps['admin']['display']}', '{$this->previous_steps['admin']['user']}', '', '".md5($this->previous_steps['admin']['password'])."', '', '{$this->previous_steps['admin']['email']}', '', '', '', 0, ".time().", 0, 0, 0, 0, 0, 0, '{$ip}', 0, '', '', '', 0, 1, '', '', '0', '', ".time().", ''";
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}user VALUES ({$userp})" );
		mysql_close();

		return false;
	}

	function write_config($data) {
		$fp = @fopen("e107_config.php", "w");
		if (!@fwrite($fp, $data)) {
			@fclose ($fd);
			return nl2br(LANINS_070);
		}
		@fclose ($fd);
		return false;
	}
}

?>