<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Docs
 *
 *
*/
require_once(__DIR__ . '/../class2.php');
if(!ADMIN)
{
	e107::redirect();
	exit;
}

e107::coreLan('docs', true);

define('DOC_PATH', e_DOCS . e_LANGUAGE . '/');
define('DOC_PATH_ALT', e_DOCS . 'English/');

e107::css('inline', 'div.qitem { margin-top:20px }
						div.aitem { padding:10px 15px; }

');


class docs_admin extends e_admin_dispatcher
{

	protected $modes = array(

		'main' => array(
			'controller' => 'docs_ui',
			'path'       => null,
			'ui'         => 'docs_form_ui',
			'uipath'     => null
		),


	);

	protected $adminMenu = array();

	protected $adminMenuAliases = array();

	protected $menuTitle = LAN_DOCS;

	protected static $helpList = array();

	public static function getDocs()
	{

		return self::$helpList;
	}


	function init()
	{

		$fl = e107::getFile();

		$helplist_all = $fl->get_files(DOC_PATH_ALT);
		if(!is_dir(DOC_PATH) || DOC_PATH == DOC_PATH_ALT)
		{
			$helplist = $helplist_all;
		}
		else
		{
			$helplist = $fl->get_files(DOC_PATH);
		}

		sort($helplist);

		self::$helpList = $helplist;

		foreach($helplist as $key => $helpdata)
		{

			$id = 'doc-' . $key;
			$k = 'main/' . $id;

			$this->adminMenu[$k] = array('caption' => str_replace("_", " ", $helpdata['fname']), 'perm' => false, 'uri' => "#" . $id);
		}


	}
}


class docs_ui extends e_admin_ui
{

	public function Doc0Page()
	{

		$helplist = docs_admin::getDocs();

		$text = '';

		$iconQ = e107::getParser()->toGlyph('fa-question-circle');
		$iconA = " ";

		foreach($helplist as $key => $helpdata)
		{

			$filename = DOC_PATH . $helpdata['fname'];
			$filename_alt = DOC_PATH_ALT . vartrue($helpdata['fname']);

			if(is_readable($filename))
			{
				$tmp = file_get_contents($filename);
			}
			else
			{
				$tmp = file_get_contents($filename_alt);
			}

			$tmp = preg_replace('/Q\>(.*?)A>/si', "###QSTART###<div class='qitem'>" . $iconQ . "\\1</div>###QEND###", $tmp);
			$tmp = preg_replace('/###QEND###(.*?)###QSTART###/si', "<div class='aitem'>" . $iconA . "\\1</div>", $tmp);
			$tmp = str_replace(array('###QSTART###', '###QEND###'), array('', "<div class='aitem'>" . $iconA), $tmp) . "</div>";

			$id = 'doc-' . $key;

			$display = ($key === 0) ? "" : "style='display:none'";

			$text .= "
				<div class='docs-item' id='{$id}' {$display}>
					<h4>" . LAN_DOCS . SEP . str_replace("_", " ", $helpdata['fname']) . "</h4>
					{$tmp}

				</div>";

			// <div class='gotop'><a href='#docs-list' class='scroll-to'>".LAN_DOCS_GOTOP."</a></div>
		}


		return $text;

	}


}


class docs_form_ui extends e_admin_form_ui
{

}


new docs_admin();

require_once(e_ADMIN . "auth.php");

$data = e107::getAdminUI()->runPage('raw');

echo $data[1]; // just to remove the title.

require_once(e_ADMIN . "footer.php");
