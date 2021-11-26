<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2016 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

/*
Notes:
- array_flip method deprecated for delete item detection.
- using form handler is deprecated and present only for backwards compatibility.
- using generic terms like EDIT and DELETE in Language file is deprecated, use LAN_EDIT etc. instead.
- using terms like created, update, options etc..deprecated should use built in terms.
- generic admin icons used. ADMIN_ICON_EDIT etc.
- using $caption = "whatever", is unneccessary.
*/

require_once(__DIR__ . '/../../class2.php');

if(!getperms("P") || !e107::isInstalled('rss_menu'))
{
	e107::redirect('admin');
	exit;
}


e107::includeLan(e_PLUGIN . "rss_menu/languages/" . e_LANGUAGE . "_admin_rss_menu.php");


class rss_admin extends e_admin_dispatcher
{

	protected $modes = array(

		'main' => array(
			'controller' => 'rss_ui',
			'path'       => null,
			'ui'         => 'rss_form_ui',
			'uipath'     => null
		),

	);


	protected $adminMenu = array(

		'main/list'   => array('caption' => LAN_MANAGE, 'perm' => 'P'),
		'main/import' => array('caption' => LAN_IMPORT, 'perm' => 'P'),

		'main/prefs' => array('caption' => LAN_PREFS, 'perm' => 'P'),
		/*
		'main/custom'		=> array('caption'=> 'Custom Page', 'perm' => 'P')
	*/

	);

	protected $adminMenuAliases = array(
		'main/edit' => 'main/list'
	);

	protected $menuTitle = 'RSS';


	function init()
	{

		if(E107_DEBUG_LEVEL > 0)
		{
			$this->adminMenu['main/create'] = array('caption' => LAN_CREATE, 'perm' => 'P');
		}
	}
}


//TODO - Use this .. .				
class rss_ui extends e_admin_ui
{

	protected $pluginTitle = 'RSS';
	protected $pluginName = 'core';
	protected $table = 'rss';
	protected $pid = 'rss_id';
	protected $perPage = 10;

	protected $fields = array(
		'checkboxes'  => array('title' => '', 'type' => null, 'data' => false, 'width' => '5%', 'thclass' => 'center', 'forced' => '1', 'class' => 'center', 'toggle' => 'e-multiselect',),
		'rss_id'      => array('title' => LAN_ID, 'data' => 'int', 'width' => '5%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',),
		'rss_name'    => array('title' => LAN_TITLE, 'type' => 'text', 'data' => 'str', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',),
		'rss_path'    => array('title' => LAN_PLUGIN_FOLDER, 'type' => 'text', 'data' => 'str', 'readonly' => 1, 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',),
		'rss_url'     => array('title' => LAN_URL, 'type' => 'method', 'data' => 'str', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',),
		'rss_topicid' => array('title' => RSS_LAN_ADMIN_12, 'type' => 'text', 'data' => 'int', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',),

		'rss_text'      => array('title' => LAN_DESCRIPTION, 'type' => 'textarea', 'data' => 'str', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',),
		'rss_datestamp' => array('title' => LAN_DATESTAMP, 'type' => 'datestamp', 'data' => 'int', 'readonly' => true, 'width' => 'auto', 'filter' => true, 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',),
		'rss_class'     => array('title' => LAN_VISIBILITY, 'type' => 'dropdown', 'data' => 'int', 'width' => 'auto', 'batch' => true, 'filter' => true, 'help' => '', 'readParms' => '', 'writeParms' => array('optArray' => array(RSS_LAN_ADMIN_21, RSS_LAN_ADMIN_22, RSS_LAN_ADMIN_23), 'size' => 'xlarge'), 'class' => 'left', 'thclass' => 'left',),
		'rss_limit'     => array('title' => LAN_LIMIT, 'type' => 'number', 'data' => 'int', 'inline' => true, 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',),
		'options'       => array('title' => LAN_OPTIONS, 'type' => null, 'data' => '', 'width' => '10%', 'thclass' => 'center last', 'class' => 'center last', 'forced' => '1',),
	);

	protected $fieldpref = array('checkboxes', 'rss_name', 'rss_url', 'rss_topicid', 'rss_limit', 'rss_class', 'options');


	protected $prefs = array(
		'rss_othernews'     => array('title' => RSS_LAN_ADMIN_13, 'type' => 'boolean', 'data' => 'int'),
		'rss_summarydiz'    => array('title' => RSS_LAN_ADMIN_19, 'type' => 'boolean', 'data' => 'integer'),
		'rss_shownewsimage' => array('title' => RSS_LAN_ADMIN_33, 'type' => 'boolean', 'data' => 'int')
	);


	// optional
	public function init()
	{

		if(!empty($_POST['importid']))
		{
			$this->dbrssImport();
		}
	}


	function dbrssImport()
	{

		$sql = e107::getDb();
		$tp = e107::getParser();

		foreach($_POST['importid'] as $key => $value)
		{
			$rssVals = array();
			$rssVals['rss_topicid'] = $tp->toDB(varset($_POST['topic_id'][$key], ''));
			$rssVals['rss_url'] = $tp->toDB(varset($_POST['url'][$key], ''));
			$rssVals['rss_path'] = $tp->toDB(varset($_POST['path'][$key], ''));
			$rssVals['rss_name'] = $tp->toDB(varset($_POST['name'][$key], ''));
			$rssVals['rss_text'] = $tp->toDB(varset($_POST['text'][$key], ''));
			$rssVals['rss_datestamp'] = time();
			$rssVals['rss_class'] = intval(varset($_POST['class'][$key], '0'));
			$rssVals['rss_limit'] = intval(varset($_POST['limit'][$key], '0'));

			$sql->insert("rss", $rssVals);
			e107::getLog()->addArray($rssVals)->save('RSS_04');
			//	e107::getLog()->logArrayAll('RSS_04',$rssVals);
		}
		$message = count($_POST['importid']) . " " . RSS_LAN_ADMIN_18;

		return $message;
	}


	public function importPage()
	{

		// Import - put up the list of possible feeds to import

		$sql = e107::getDb();
		$ns = e107::getRender();
		$mes = e107::getMessage();
		$tp = e107::getParser();
		$frm = e107::getForm();

		global $i, $rss_shortcodes, $feed, $pref;

		require_once(e_PLUGIN . 'rss_menu/rss_shortcodes.php');
		$rss_shortcodes = e107::getScBatch('rss_menu', true);

		$RSS_ADMIN_IMPORT_HEADER = "
			<form action='" . e_SELF . "' id='imlistform' method='post' >
			<table class='table table-striped adminlist'>
			<thead>
			<tr>
				<th class='center' style='width:5%'>" . LAN_SELECT . "</td>
				<th>" . LAN_NAME . "</td>
				<th>" . LAN_PLUGIN_FOLDER . "</td>

				<th>" . LAN_URL . "</td>
				<th>" . RSS_LAN_ADMIN_12 . "</td>
			</tr>
			</thead><tbody>";

		$RSS_ADMIN_IMPORT_TABLE = "
			<tr>
				<td class='first center'>{RSS_ADMIN_IMPORT_CHECK}</td>
					<td>{RSS_ADMIN_IMPORT_NAME} - {RSS_ADMIN_IMPORT_TEXT}</td>
				<td>{RSS_ADMIN_IMPORT_PATH}</td>

				<td>{RSS_ADMIN_IMPORT_URL}</td>
				<td>{RSS_ADMIN_IMPORT_TOPICID}</td>
			</tr>";


		$RSS_ADMIN_IMPORT_FOOTER = "</tbody>
			</table>
			<div class='buttons-bar center'>
				" . $frm->admin_button('import_rss', LAN_ADD, 'submit') . "
			</div>
			</form>
			";


		$sqli = new db;
		$feedlist = array();

		// @see e107_plugins/news/e_rss.php

		// Comments
		$feed['name'] = LAN_COMMENTS;
		$feed['url'] = 'comments';
		$feed['topic_id'] = '';
		$feed['path'] = 'comments';
		$feed['text'] = RSS_PLUGIN_LAN_9;
		$feed['class'] = '0';
		$feed['limit'] = '9';
		$feedlist[] = $feed;

		// Plugin rss feed, using e_rss.php in plugin folder
		$plugin_feedlist = array();
		foreach($pref['e_rss_list'] as $val)
		{
			$eplug_rss_feed = array();
			if(is_readable(e_PLUGIN . $val . "/e_rss.php"))
			{
				require_once(e_PLUGIN . $val . "/e_rss.php");

				$className = $val . "_rss";
				$data = false;

				if(!$data = e107::callMethod($className, 'config'))
				{
					$data = $eplug_rss_feed;
				}

				foreach($data as $v)
				{
					$v['path'] = $val;
					array_push($plugin_feedlist, $v);
				}

			}
		}

		$feedlist = array_merge($feedlist, $plugin_feedlist);

//		print_a($feedlist);

		$render = false;
		$i = 0;
		$text = $RSS_ADMIN_IMPORT_HEADER;
		foreach($feedlist as $k => $feed)
		{
			$feed['topic_id'] = $tp->toDB($feed['topic_id']);
			$feed['url'] = $tp->toDB($feed['url']);

			// Check if feed is not yet present
			if(!$sql->select("rss", "*", "rss_path='" . $feed['path'] . "' AND rss_url='" . $feed['url'] . "' AND rss_topicid='" . $feed['topic_id'] . "' "))
			{
				$render = true;
				$rss_shortcodes->setVars($feed);
				$text .= $tp->parseTemplate($RSS_ADMIN_IMPORT_TABLE, false, $rss_shortcodes);
				$i++;
			}
		}

		$text .= $tp->parseTemplate($RSS_ADMIN_IMPORT_FOOTER, false, $rss_shortcodes);

		if(!$render)
		{
			e107::getMessage()->addWarning(RSS_LAN_ERROR_6);
		}
		else
		{

			return $text;
		}
	}


}


class rss_form_ui extends e_admin_form_ui
{


	// Custom Method/Function 
	function rss_url($curVal, $mode)
	{


		switch($mode)
		{
			case 'read': // List Page

				$type = $this->getController()->getListModel()->get('rss_type');
				$topic = $this->getController()->getListModel()->get('rss_topicid');

				$link = e107::url('rss_menu', 'rss', array('rss_type' => $type, 'rss_url' => $curVal, 'rss_topicid' => $topic));

				return "<a href='" . $link . "'>" . $curVal . "</a>";
				break;

			case 'write': // Edit Page
				$link = SITEURL . "feed/"; // e107::url('rss_menu','index').'/';

				return "<div class='form-inline'>" . $link . e107::getForm()->text('rss_url', $curVal, 255, 'size=small') . "/rss/{Topic id}</div>";
				break;

			case 'filter':
			case 'batch':
				return null;
				break;
		}
	}

}


new rss_admin();

require_once(e_ADMIN . "auth.php");
e107::getAdminUI()->runPage();

require_once(e_ADMIN . "footer.php");








