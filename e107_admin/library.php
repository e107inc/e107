<?php

/**
 * @file
 * Class installations to handle configuration forms on Admin UI.
 */

require_once('../class2.php');

if(!getperms("Z"))
{
	e107::redirect('admin');
	exit;
}

// [e_LANGUAGEDIR]/[e_LANGUAGE]/lan_library_manager.php
e107::lan('core', 'library_manager');


/**
 * Class library_admin.
 */
class library_admin extends e_admin_dispatcher
{

	/**
	 * @var array
	 */
	protected $modes = array(
		'list' => array(
			'controller' => 'library_list_ui',
			'path'       => null,
		),
	);

	/**
	 * @var array
	 */
	protected $adminMenu = array(
		'list/libraries' => array(
			'caption' => LAN_LIBRARY_MANAGER_12,
			'perm'    => 'P',
		),
	);

	/**
	 * @var string
	 */
	protected $menuTitle = LAN_LIBRARY_MANAGER_25;

}


/**
 * Class library_list_ui.
 */
class library_list_ui extends e_admin_ui
{

	/**
	 * @var string
	 */
	protected $pluginTitle = LAN_LIBRARY_MANAGER_25;


	/**
	 * List libraries.
	 */
	function librariesPage()
	{
		$tp = e107::getParser();
		$libraries = e107::library('info');

		$html = '<table width="100%" class="table table-striped" cellpadding="0" cellspacing="0">';
		$html .= '<thead>';
		$html .= '<tr>';
		$html .= '<th>' . LAN_LIBRARY_MANAGER_13 . '</th>';
		$html .= '<th>' . LAN_LIBRARY_MANAGER_21 . '</th>';
		$html .= '<th>' . LAN_LIBRARY_MANAGER_14 . '</th>';
		$html .= '<th>' . LAN_LIBRARY_MANAGER_18 . '</th>';
		$html .= '<th>' . LAN_LIBRARY_MANAGER_19 . '</th>';
		$html .= '<th></th>';
		$html .= '</tr>';
		$html .= '</thead>';
		$html .= '<tbody>';

		foreach($libraries as $machineName => $library)
		{
			$details = e107::library('detect', $machineName);

			if(empty($details['name']))
			{
				continue;
			}

			$provider = $this->getProvider($details);
			$status = $this->getStatus($details);
			$homepage = $this->getHomepage($details);
			$download = $this->getDownload($details);

			$html .= '<tr>';
			$html .= '<td>' . $details['name'] . '</td>';
			$html .= '<td>' . $provider . '</td>';
			$html .= '<td>' . $details['version'] . '</td>';
			$html .= '<td>' . $status . '</td>';
			$html .= '<td>' . $details['error_message'] . '</td>';
			$html .= '<td>' . $homepage . ' | ' . $download . '</td>';
			$html .= '</tr>';
		}

		$html .= '</tbody>';
		$html .= '</table>';

		return '<div class="table-responsive">' . $html . '</div>';
	}

	/**
	 * Helper function to get homepage link.
	 */
	private function getHomepage($details)
	{
		$href = $details['vendor_url'];
		$title = $details['name'];

		return '<a href="' . $href . '" title="' . $title . '" target="_blank">' . LAN_LIBRARY_MANAGER_15 . '</a>';
	}

	/**
	 * Helper function to get download link.
	 */
	private function getDownload($details)
	{
		$href = $details['download_url'];
		$title = $details['name'];

		return '<a href="' . $href . '" title="' . $title . '" target="_blank">' . LAN_LIBRARY_MANAGER_16 . '</a>';
	}

	/**
	 * Helper function to get provider.
	 */
	private function getProvider($details)
	{
		$provider = LAN_LIBRARY_MANAGER_24;

		if(varset($details['plugin'], false) == true)
		{
			$provider = LAN_LIBRARY_MANAGER_22;
		}

		if(varset($details['theme'], false) == true)
		{
			$provider = LAN_LIBRARY_MANAGER_23;
		}

		return $provider;
	}

	/**
	 * Helper function to get status.
	 */
	private function getStatus($details)
	{
		if($details['installed'] == true)
		{
			return '<span class="text-success">' . LAN_OK . '</span>';
		}

		return '<span class="text-danger">' . $details['error'] . '</span>';
	}

}


new library_admin();

require_once(e_ADMIN . "auth.php");
e107::getAdminUI()->runPage();
require_once(e_ADMIN . "footer.php");
exit;
