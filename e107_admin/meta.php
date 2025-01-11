<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Administration Area - Meta Tags
 *
 *
*/
if(!empty($_POST) && !isset($_POST['e-token']))
{
	$_POST['e-token'] = '';
}
require_once(__DIR__ . '/../class2.php');

if(!getperms("T"))
{
	e107::redirect('admin');
	exit;
}

e107::coreLan('meta', true);


class meta_admin extends e_admin_dispatcher
{

	protected $modes = array(
		'main' => array(
			'controller' => 'meta_admin_ui',
			'path'       => null,
			'ui'         => 'e_admin_form_ui',
			'uipath'     => null
		)
	);


	protected $adminMenu = array(
		'main/meta' => array('caption' => LAN_MANAGE, 'perm' => '0', 'icon'=>'fas-list'),
		'main/prefs'=> array('caption' => "SEO", 'perm'=>'0', 'icon'=>'fas-cogs'),
	);

	protected $adminMenuAliases = array(//	'main/edit'	=> 'main/list'
	);

	protected $menuTitle = METLAN_00;

	protected $adminMenuIcon = 'e-meta-24';

}


class meta_admin_ui extends e_admin_ui
{

	protected $pluginTitle = METLAN_00;
	protected $pluginName = 'core';

	protected $prefs = array(
		'seo_title_limit' => array('title'=>METLAN_8, 'type'=>'number', 'data'=>'int', 'help'=>'', 'writeParms'=>['size'=>'large']),
		'seo_description_limit' => array('title'=>METLAN_9, 'type'=>'number', 'data'=>'int', 'help'=>'', 'writeParms'=>['size'=>'large']),
		'meta_news_summary' => array('title'=>METLAN_3, 'type'=>'boolean', 'data'=>'int'),
	);

	function init()
	{

		if(isset($_POST['metasubmit']))
		{
			$this->save();
		}
	}


	function save()
	{

		$fields = array(
			'meta_description',
			'meta_keywords',
			'meta_copyright',
			'meta_author',
			'meta_tag',
			'meta_bodystart',
			'meta_bodyend',
		);

		$cfg = e107::getConfig();

		foreach($fields as $key)
		{
			if(isset($_POST[$key]))
			{
				$cfg->setPref($key . '/' . e_LANGUAGE, trim($_POST[$key]));
			}
		}

		$cfg->save(true, true, true);
	}


	public function renderHelp()
	{

		$caption = LAN_HELP;
		$text = htmlentities(METLAN_7);

		return array('caption' => $caption, 'text' => $text);
	}


	public function metaPage()
	{

		$mes = e107::getMessage();
		$frm = $this->getUI();
		$ns = e107::getRender();
		$pref = e107::getPref();
		$tp = e107::getParser();

		$meta_diz           = vartrue($pref['meta_description'], array());
		$meta_keywords      = vartrue($pref['meta_keywords'], array());
		$meta_copyright     = vartrue($pref['meta_copyright'], array());
		$meta_author        = vartrue($pref['meta_author'], array());

		$customTagHead      = vartrue($pref['meta_tag'], array());
		$customTagBodyStart = vartrue($pref['meta_bodystart'], array());
		$customTagBodyEnd   = vartrue($pref['meta_bodyend'], array());


		$text = "
		<form method='post' action='" . e_SELF . "' id='dataform'>
		<fieldset id='core-meta-settings'>
			<legend class='e-hideme'>" . METLAN_00 . " (" . e_LANGUAGE . ")" . "</legend>
			<table class='table adminform'>
				<colgroup>
					<col class='col-label' />
					<col class='col-control' />
				</colgroup>
				<tbody>
					<tr>
						<td>" . LAN_DESCRIPTION . "</td>
						<td>";
		$text .= $frm->textarea('meta_description', $tp->toForm(varset($meta_diz[e_LANGUAGE])), 3, 80, array('size' => 'xxlarge'));
		//	$text .= "<textarea class='tbox textarea e-autoheight' id='meta_description' name='meta_description' cols='70' rows='4'>".$tp->toForm(varset($meta_diz[e_LANGUAGE]))."</textarea>";
		$text .= "</td>
					</tr>
					<tr>
						<td>" . LAN_KEYWORDS . "</td>
						<td>";
		$text .= $frm->tags('meta_keywords', $tp->toForm(varset($meta_keywords[e_LANGUAGE])));
		//	$text .= "<textarea class='tbox textarea e-autoheight' id='meta_keywords' name='meta_keywords' cols='70' rows='4'>".$tp->toForm(varset($meta_keywords[e_LANGUAGE]))."</textarea>";

		$text .= "</td>
					</tr>
					<tr>
						<td>" . LAN_COPYRIGHT . "</td>
						<td><input class='tbox form-control input-xxlarge' size='70' type='text' name='meta_copyright' value=\"" . varset($meta_copyright[e_LANGUAGE]) . "\" /></td>
					</tr>

					<tr>
						<td>" . LAN_AUTHOR . "</td>
						<td><input class='tbox form-control input-xxlarge' size='70' type='text' name='meta_author' value=\"" . varset($meta_author[e_LANGUAGE]) . "\" /></td>
					</tr>

					<tr>
						<td>" . $this->metaLabel(METLAN_4, '<head>') . "</td>
						<td>";
		$text .= $frm->textarea('meta_tag', str_replace("<", "&lt;", $tp->toForm(varset($customTagHead[e_LANGUAGE]))), 5, 100, array('size' => 'block-level', 'placeholder' => "eg. <script>Custom code.</script>"));

		$text .= "</td>
					</tr>
					<tr>
						<td>" . $this->metaLabel(METLAN_5, '<body>') . "</td>
						<td>";
		$text .= $frm->textarea('meta_bodystart', str_replace("<", "&lt;", $tp->toForm(varset($customTagBodyStart[e_LANGUAGE]))), 5, 100, array('size' => 'block-level', 'placeholder' => "eg. <script>Custom code.</script>"));

		$text .= "</td>
					</tr>
					<tr>
						<td>" . $this->metaLabel(METLAN_6, '</body>') . "</td>
						<td>";
		$text .= $frm->textarea('meta_bodyend', str_replace("<", "&lt;", $tp->toForm(varset($customTagBodyEnd[e_LANGUAGE]))), 5, 100, array('size' => 'block-level', 'placeholder' => "eg. <script>Custom code.</script>"));

		$text .= "</td>
					</tr>
				
					</tbody>
				</table>
				<div class='buttons-bar center'>" .
			$frm->admin_button('metasubmit', 'no-value', 'update', LAN_UPDATE) . "
				</div>
				<input type='hidden' name='e-token' value='" . defset('e_TOKEN') . "' />
			</fieldset>
		</form>
		";

		$caption = htmlentities(METLAN_00);
		$installedLangs = e107::getLanguage()->installed('count');

		if($installedLangs > 1)
		{
			$caption .= " (" . e_LANGUAGE . ")";
		}

		return $text;
		//	$ns->tablerender($caption, $mes->render() . $text);
	}


	function metaLabel($text, $small)
	{

		$small = htmlentities($small);

//	$text = str_replace(['(', ')'], ['<i>', '</i>'], $text);
		return e107::getParser()->lanVars($text, $small, true);
	}
}


new meta_admin();

$e_sub_cat = 'meta';

require_once('auth.php');

e107::getAdminUI()->runPage();


require_once("footer.php");

