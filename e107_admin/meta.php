<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2008 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Administration Area - Meta Tags
 *
 * $Source: /cvs_backup/e107_0.8/e107_admin/meta.php,v $
 * $Revision: 1.4 $
 * $Date: 2008-12-15 13:42:25 $
 * $Author: secretr $
 *
*/
require_once("../class2.php");
if (!getperms("T")) {
	header("location:".e_BASE."index.php");
	exit;
}
$e_sub_cat = 'meta';
require_once("auth.php");
require_once(e_HANDLER."message_handler.php");
$emessage = &eMessage::getInstance();

$current_lang = ($sql->mySQLlanguage != "") ? $sql->mySQLlanguage : $pref['sitelanguage'];

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

	$meta_tag[$current_lang] = strip_if_magic(chop($_POST['meta']));
	$meta_diz[$current_lang] = strip_if_magic(chop($_POST['meta_description']));
	$meta_keywords[$current_lang] = strip_if_magic(chop($_POST['meta_keywords']));
	$meta_copyright[$current_lang] = strip_if_magic(chop($_POST['meta_copyright']));
	$meta_author[$current_lang] = strip_if_magic(chop($_POST['meta_author']));

    $pref['meta_news_summary'] = intval($_POST['meta_news_summary']);
	$pref['meta_tag'] = $meta_tag;
	$pref['meta_description'] = $meta_diz;
	$pref['meta_keywords'] = $meta_keywords;
	$pref['meta_copyright'] = $meta_copyright;
	$pref['meta_author'] = $meta_author;

   /*
    if($pref['meta_tag'][$current_lang] == ""){
        unset($meta_tag[$current_lang]);
    }*/

	$admin_log->log_event('META_01','meta_news_summary=>'.$pref['meta_news_summary'].'[!br!]'.$current_lang,E_LOG_INFORMATIVE,'');
	save_prefs();
	$emessage->add(METLAN_1." ({$current_lang})", E_MESSAGE_SUCCESS);
}

$meta = $pref['meta_tag'];
$meta_diz = $pref['meta_description'];
$meta_keywords = $pref['meta_keywords'];
$meta_copyright = $pref['meta_copyright'];
$meta_author = $pref['meta_author'];

$text = "
	<form method='post' action='".e_SELF."' id='dataform'>
		<fieldset id='core-meta-settings'>
			<legend class='e-hideme'>".METLAN_8." (".$current_lang.")"."</legend>
			<table cellpadding='0' cellspacing='0' class='adminform'>
				<colgroup span='2'>
					<col class='col-label' />
					<col class='col-control' />
				</colgroup>
				<tbody>
					<tr>
						<td class='label'>".METLAN_9."</td>
						<td class='control'>
							<textarea class='tbox textarea' title='meta_description' id='meta_description' name='meta_description' cols='70' rows='4'>".$tp->toForm($meta_diz[$current_lang])."</textarea>
						</td>
					</tr>
					<tr>
						<td class='label'>".METLAN_10."</td>
						<td class='control'>
							<textarea class='tbox textarea' title='meta_keywords' id='meta_keywords' name='meta_keywords' cols='70' rows='4'>".$tp->toForm($meta_keywords[$current_lang])."</textarea>
						</td>
					</tr>

					<tr>
						<td class='label'>".METLAN_11."</td>
						<td class='control'>
							<input class='tbox input-text' size='70' type='text' name='meta_copyright' value='".$meta_copyright[$current_lang]."' />
						</td>
					</tr>

					<tr>
						<td class='label'>".METLAN_13."</td>
						<td class='control'>
							<input class='tbox input-text' size='70' type='text' name='meta_author' value=\"".$meta_author[$current_lang]."\" />
						</td>
					</tr>

					<tr>
						<td class='label'>".METLAN_2."</td>
						<td class='control'>
							<textarea class='tbox textarea' title=\"eg. <meta name='author' content='your name' />\" id='meta' name='meta' cols='70'
							rows='10' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'>".str_replace("<","&lt;",$tp->toForm($meta[$current_lang]))."</textarea>
							<div class='smalltext field-help'>eg. &lt;meta name='author' content='your name' /&gt; </div>
						</td>
					</tr>

					<tr>
						<td class='label'>".METLAN_12."</td>
						<td class='control'>
							<input class='checkbox' type='checkbox' name='meta_news_summary' value='1'".($pref['meta_news_summary'] ? " checked='checked'" : '')." />
						</td>
					</tr>
				</tbody>
			</table>
			<div class='buttons-bar center'>
				<button class='create' type='submit' name='metasubmit' value='".METLAN_3."'><span>".METLAN_3."</span></button>
			</div>
		</fieldset>
	</form>

";

$ns -> tablerender(METLAN_8." (".$current_lang.")", $emessage->render().$text);

require_once("footer.php");

?>