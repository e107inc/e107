<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2015 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * @file
 * Each import file has an identifier which must be the same for:
 * a) This file name - add '_class.php' to get the file name
 * b) The array index of certain variables.
 */

require_once('import_classes.php');

/**
 * Class drupal_import.
 */
class drupal_import extends base_import_class
{

	public $title = LAN_CONVERT_49;
	public $description = LAN_CONVERT_50;
	// array('users', 'news','page','links'); //XXX Modify to enable copyNewsData() etc.
	public $supported = array('users');
	public $mprefix = false;

	private $version = 7;
	private $baseUrl = null;
	private $basePath = '/';


	function init()
	{
		if (!empty($_POST['version']))
		{
			$this->version = $_POST['version'];
		}

		if (!empty($_POST['baseUrl']))
		{
			$this->baseUrl = $_POST['baseUrl'];
		}

		if (!empty($_POST['basePath']))
		{
			$this->basePath = $_POST['basePath'];
		}

		if (!empty($_POST))
		{
			e107::getMessage()->addDebug(print_a($_POST, true));
		}
	}


	/**
	 * Altering import form. We append additional form elements to it.
	 *
	 * @return array $frmElements
	 */
	function config()
	{
		$frm = e107::getForm();
		$frmElements = array();

		$versions = array(
			'6' => 'Drupal 6',
			'7' => 'Drupal 7',
			'8' => 'Drupal 8',
		);

		$dscVersion = LAN_CONVERT_51;
		$frmElements[] = array(
			'caption' => LAN_CONVERT_52,
			'html' => $frm->select('version', $versions, $this->version, 50, array(
					'required' => 1,
				)) . '<div class="field-help">' . $dscVersion . '</div>',
		);

		$dscBaseUrl = LAN_CONVERT_53;
		$frmElements[] = array(
			'caption' => LAN_CONVERT_54,
			'html' => $frm->text('baseUrl', $this->baseUrl, 50, array(
					'required' => 1,
				)) . '<div class="field-help">' . $dscBaseUrl . '</div>',
		);

		$dscBasePath = LAN_CONVERT_55;
		$frmElements[] = array(
			'caption' => LAN_CONVERT_56,
			'html' => $frm->text('basePath', $this->basePath, 50, array(
					'required' => 1,
				)) . '<div class="field-help">' . $dscBasePath . '</div>',
		);

		return $frmElements;
	}


	/**
	 * Set up a query for the specified task if we have a valid connection to
	 * Drupal database.
	 *
	 * @param string $task
	 *  Name of the current task.
	 *
	 * @param bool $blank_user
	 *  If $blank_user is true, certain cross-referencing user info is to be
	 *  zeroed.
	 *
	 * @return bool
	 *  Returns TRUE on success. false on error.
	 */
	function setupQuery($task, $blank_user = false)
	{
		$result = false;

		// Check the connection to Drupal database.
		if ($this->ourDB == null)
		{
			return $result;
		}

		// Set up a query for the specified task.
		switch ($task)
		{
			case 'users':
				$result = $this->_setupQueryUsers();
				$this->copyUserInfo = !$blank_user;
				break;

			case 'news':
				break;

			case 'page':
				break;

			case 'links':
				break;

			default:
				break;
		}

		if ($result === false)
		{
			return false;
		}

		$this->currentTask = $task;

		return true;
	}


	/**
	 * Helper function to setup a query for the 'users' task.
	 */
	function _setupQueryUsers()
	{
		switch ((int) $this->version)
		{
			case 6:
				$query = "SELECT * FROM " . $this->DBPrefix . "users AS u ";
				$query .= "WHERE u.status = 1 AND u.uid > 1 ";

				return $this->ourDB->gen($query);
				break;

			case 7:
				$fields = array(
					'u.*',
					'fm.uri',
					'public.value AS public_file_path',
				);

				$query = "SELECT " . implode(',', $fields) . " FROM " . $this->DBPrefix . "users AS u ";
				$query .= "LEFT JOIN " . $this->DBPrefix . "file_managed AS fm ON u.picture = fm.fid ";
				$query .= "LEFT JOIN " . $this->DBPrefix . "variable AS public ON public.name = 'file_public_path'";
				$query .= "WHERE u.status = 1 AND u.uid > 1 ";

				return $this->ourDB->gen($query);
				break;

			case 8:
				$fields = array(
					'ufd.*',
				);

				$query = "SELECT " . implode(',', $fields) . " FROM " . $this->DBPrefix . "users AS u ";
				$query .= "LEFT JOIN " . $this->DBPrefix . "users_field_data AS ufd ON u.uid = ufd.uid";
				$query .= "WHERE ufd.status = 1 AND ufd.uid > 1 ";

				return $this->ourDB->gen($query);
				break;

			default:
				return false;
				break;
		}
	}


	/**
	 * Copy data read from the DB into the record to be returned.
	 *
	 * @param array $target
	 *  Default e107 target values for e107_user table.
	 *
	 * @param array $source
	 *  Drupal table data.
	 *
	 * @return array $target
	 */
	function copyUserData(&$target, &$source)
	{
		if ($this->copyUserInfo)
		{
			$target['user_id'] = $source['uid'];
			$target['user_name'] = $source['name'];
			$target['user_loginname'] = $source['name'];
			$target['user_password'] = $source['pass'];
			$target['user_email'] = $source['mail'];
			$target['user_signature'] = $source['signature'];
			$target['user_join'] = (int) $source['created'];
			$target['user_lastvisit'] = (int) $source['login'];
			$target['user_timezone'] = $source['timezone'];
			$target['user_language'] = $source['language'];
			$user_image = $this->fileSaveAvatar($source);
			$target['user_image'] = $user_image;

			return $target;
		}
	}


	/**
	 * Example Copy News.
	 *
	 * @param array $target
	 *  Default e107 target values for e107_page table.
	 *
	 * @param array $source
	 *  Drupal table data.
	 *
	 * @return array $target
	 */
	function copyNewsData(&$target, &$source)
	{
		$target = array(
			'news_id' => 1,
			'news_title' => 'Welcome to e107',
			'news_sef' => 'welcome-to-e107',
			'news_body' => '[html]<p>Welcome to your new website!</p>[/html]',
			'news_extended' => '',
			'news_meta_keywords' => '',
			'news_meta_description' => '',
			'news_datestamp' => '1355612400', // time()
			'news_author' => 1,
			'news_category' => 1,
			'news_allow_comments' => 0,
			'news_start' => 0, // time()
			'news_end' => 0, // time()
			'news_class' => 0,
			'news_render_type' => 0,
			'news_comment_total' => 1,
			'news_summary' => 'summary text',
			'news_thumbnail' => '', // full path with {e_MEDIA_IMAGE} constant.
			'news_sticky' => 0
		);

		return $target;
	}


	/**
	 * Example copy e107 Page Table.
	 *
	 * @param array $target
	 *  Default e107 target values for e107_page table.
	 *
	 * @param array $source
	 *  Drupal table data.
	 *
	 * @return array $target
	 */
	function copyPageData(&$target, &$source)
	{
		$target = array(
			'page_id' => 1,
			'page_title' => 'string',
			'page_sef' => 'string',
			'page_chapter' => 0,
			'page_metakeys' => 'string',
			'page_metadscr' => '',
			'page_text' => '',
			'page_author' => 0, // e107 user_id
			'page_datestamp' => '1371420000', // time()
			'page_rating_flag' => 0,
			'page_comment_flag' => 0, // boolean
			'page_password' => '', // plain text
			'page_class' => 0, // e107 userclass
			'page_ip_restrict' => '',
			'page_template' => 'default',
			'page_order' => 0,
			'menu_name' => 'jumbotron-menu-1', // no spaces, all lowercase
			'menu_title' => 'string',
			'menu_text' => '',
			'menu_image' => '',
			'menu_icon' => '',
			'menu_template' => 'button',
			'menu_class' => 0,
			'menu_button_url' => '',
			'menu_button_text' => ''
		);

		return $target;
	}


	/**
	 * Save avatar picture from Drupal filesystem.
	 * a) create remote URL to stream file contents
	 * b) get remote file contents
	 * c) save file to e_AVATAR_UPLOAD
	 *
	 * @param array $row
	 *  Full database row contains details of user.
	 *
	 * @return string $local_path
	 *  Local path, where the file has been saved to, or empty string.
	 */
	function fileSaveAvatar($row)
	{
		// Set default return value.
		$local_path = '';

		switch ((int) $this->version) {
			case 6:
				$src_pth = $this->fileCreateUrl($row['picture'], "");
				break;

			case 7:
				$src_uri = $row['uri'];
				$src_pth = unserialize($row['public_file_path']);
				$src_pth = $this->fileCreateUrl($src_uri, $src_pth);
				break;

			case 8:
				// TODO: need to get user pictures url.
				return $local_path;
				break;
		}

		// If $src_pth is empty, we cannot save remote file, so return...
		if (!isset($src_pth) || empty($src_pth))
		{
			return $local_path;
		}

		// Try remote file to open for reading.
		if ($stream = fopen($src_pth, 'r'))
		{
			$file_contents = stream_get_contents($stream);
			fclose($stream);
		}
		else
		{
			return $local_path;
		}

		// If no contents, return...
		if (!$file_contents)
		{
			return $local_path;
		}

		// Get upload directory.
		$uploaddir = e_AVATAR_UPLOAD;
		$uploaddir = realpath($uploaddir);

		if (!is_dir($uploaddir))
		{
			return $local_path;
		}

		$tp = isset($tp) ? $tp : new e_parse();

		$base_name = basename($src_pth);
		$base_name = preg_replace("/[^\w\pL.-]/u", '', str_replace(' ', '_', str_replace('%20', '_', $tp->ustrtolower($base_name))));
		$file_name = 'ap_' . $tp->leadingZeros($row['uid'], 7) . '_' . $base_name;

		$uploaded = file_put_contents($uploaddir . '/' . $file_name, $file_contents);

		if ($uploaded === false)
		{
			return $local_path;
		}

		$local_path = '-upload-' . $file_name;

		return $local_path;
	}


	/**
	 * Creates a web-accessible URL for a stream to a Drupal local file.
	 *
	 * @param string $uri
	 *  The URI to a file for which we need an external URL.
	 *
	 * @param string $path
	 *  Base URL path (i.e., directory) of the Drupal installation.
	 *
	 * @return string
	 *  A string containing a URL that may be used to access the file.
	 */
	function fileCreateUrl($uri, $path)
	{
		if (empty($uri))
		{
			return "";
		}

		$base_url = $this->httpCheck($this->baseUrl);
		// Strip a slash from the end of URL.
		$base_url = rtrim($base_url, '/');
		// Strip slashes from the beginning and end of path.
		$base_path = trim($this->basePath, '/');
		// Append slashes to the beginning and end of path if it's not empty.
		$base_path = !empty($base_path) ? '/' . $base_path . '/' : '/';
		// Replace file schema with the real path.
		$file_path = str_replace("public://", $path . '/', $uri);

		$url = $base_url . $base_path . $file_path;

		return $url;
	}


	/**
	 * Check string contains 'http://' or 'https://' and prepend 'http://' if
	 * it's necessary.
	 *
	 * @param string $url
	 *  String of URL.
	 *
	 * @return string
	 *  String of URL.
	 */
	function httpCheck($url)
	{
		$return = $url;
		if ((!(substr($url, 0, 7) == 'http://')) && (!(substr($url, 0, 8) == 'https://')))
		{
			$return = 'http://' . $url;
		}
		return $return;
	}
}
