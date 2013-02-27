<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Administration Area - Meta Tags
 *
 * $URL$
 * $Id$
 *
*/
require_once("../class2.php");
if (!getperms("T")) {
	header("location:".e_BASE."index.php");
	exit;
}

include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_'.e_PAGE);

$e_sub_cat = 'meta';
require_once("auth.php");

$mes = e107::getMessage();
$frm = e107::getForm();
$ns = e107::getRender();

if (isset($_POST['metasubmit']))
{
	$tmp = $pref['meta_tag'];
	$langs = explode(",",e_LANLIST);
	foreach($langs as $lan)
	{
		$meta_tag[$lan] = $tmp[$lan];
		$meta_diz[$lan] = $pref['meta_description'][$lan];
		$meta_keywords[$lan] = $pref['meta_keywords'][$lan];
		$meta_copyright[$lan] = $pref['meta_copyright'][$lan];
		$meta_author[$lan] = $pref['meta_author'][$lan];
	}

	$meta_tag[e_LANGUAGE] = strip_if_magic(chop($_POST['meta']));
	$meta_diz[e_LANGUAGE] = strip_if_magic(chop($_POST['meta_description']));
	$meta_keywords[e_LANGUAGE] = strip_if_magic(chop($_POST['meta_keywords']));
	$meta_copyright[e_LANGUAGE] = strip_if_magic(chop($_POST['meta_copyright']));
	$meta_author[e_LANGUAGE] = strip_if_magic(chop($_POST['meta_author']));

    $pref['meta_news_summary'] = intval($_POST['meta_news_summary']);
	$pref['meta_tag'] = $meta_tag;
	$pref['meta_description'] = $meta_diz;
	$pref['meta_keywords'] = $meta_keywords;
	$pref['meta_copyright'] = $meta_copyright;
	$pref['meta_author'] = $meta_author;

   /*
    if($pref['meta_tag'][e_LANGUAGE] == ""){
        unset($meta_tag[e_LANGUAGE]);
    }*/

	$admin_log->log_event('META_01', 'meta_news_summary=>'.$pref['meta_news_summary'].'[!br!]'.e_LANGUAGE, E_LOG_INFORMATIVE, '');
	save_prefs();
	//$mes->add(METLAN_1." (".e_LANGUAGE.")", E_MESSAGE_SUCCESS);
}

$meta 			= vartrue($pref['meta_tag']);
$meta_diz 		= vartrue($pref['meta_description']);
$meta_keywords 	= vartrue($pref['meta_keywords']);
$meta_copyright = vartrue($pref['meta_copyright']);
$meta_author 	= vartrue($pref['meta_author']);

$text = "
	<form method='post' action='".e_SELF."' id='dataform'>
		<fieldset id='core-meta-settings'>
			<legend class='e-hideme'>".METLAN_00." (".e_LANGUAGE.")"."</legend>
			<table class='table adminform'>
				<colgroup>
					<col class='col-label' />
					<col class='col-control' />
				</colgroup>
				<tbody>
					<tr>
						<td>".LAN_DESCRIPTION."</td>
						<td><textarea class='tbox textarea e-autoheight' id='meta_description' name='meta_description' cols='70' rows='4'>".$tp->toForm(varset($meta_diz[e_LANGUAGE]))."</textarea>	</td>
					</tr>
					<tr>
						<td>".LAN_KEYWORDS."</td>
						<td><textarea class='tbox textarea e-autoheight' id='meta_keywords' name='meta_keywords' cols='70' rows='4'>".$tp->toForm(varset($meta_keywords[e_LANGUAGE]))."</textarea></td>
					</tr>
					<tr>
						<td>".LAN_COPYRIGHT."</td>
						<td><input class='tbox input-text' size='70' type='text' name='meta_copyright' value=\"".varset($meta_copyright[e_LANGUAGE])."\" /></td>
					</tr>

					<tr>
						<td>".LAN_AUTHOR."</td>
						<td><input class='tbox input-text' size='70' type='text' name='meta_author' value=\"".varset($meta_author[e_LANGUAGE])."\" /></td>
					</tr>

					<tr>
						<td>".METLAN_1."</td>
						<td><textarea class='tbox textarea e-autoheight' id='meta' name='meta' cols='70' rows='10' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'>".str_replace("<","&lt;",$tp->toForm(varset($meta[e_LANGUAGE])))."</textarea><span class='field-help'>".METLAN_2."</span></td>
					</tr>
					<tr>
						<td>".METLAN_3."</td>
						<td>
							<div class='auto-toggle-area autocheck'>".
								$frm->checkbox('meta_news_summary',1, varset($pref['meta_news_summary']))."
							</div>
						</td>
					</tr>
				</tbody>
			</table>
			<div class='buttons-bar center'>".
				$frm->admin_button('metasubmit','no-value','update', LAN_UPDATE)."
			</div>
		</fieldset>
	</form>
";

$ns->tablerender(METLAN_00." (".e_LANGUAGE.")", $mes->render().$text);

require_once("footer.php");
/**
 * Handle page DOM within the page header
 *
 * @return string JS source
 */
function headerjs()
{
	$ret = "
		<script type='text/javascript'>
			var e107Admin = {}
			e107Admin.initRules = {
				'Helper': true,
				'AdminMenu': false
			}
		</script>
		<script type='text/javascript' src='".e_JS."core/admin.js'></script>
	";

	return $ret;
}
?>