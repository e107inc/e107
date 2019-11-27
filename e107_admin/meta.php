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
require_once("../class2.php");

if (!getperms("T")) 
{
	e107::redirect('admin');
	exit;
}

e107::coreLan('meta', true);

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

	e107::getLog()->add('META_01', 'meta_news_summary=>'.$pref['meta_news_summary'].'[!br!]'.e_LANGUAGE, E_LOG_INFORMATIVE, '');
	save_prefs();
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
						<td>";
						$text .= $frm->textarea('meta_description',$tp->toForm($meta_diz[e_LANGUAGE]),3,80, array('size'=>'xxlarge'));
					//	$text .= "<textarea class='tbox textarea e-autoheight' id='meta_description' name='meta_description' cols='70' rows='4'>".$tp->toForm(varset($meta_diz[e_LANGUAGE]))."</textarea>";
						$text .= "</td>
					</tr>
					<tr>
						<td>".LAN_KEYWORDS."</td>
						<td>";
						$text .= $frm->tags('meta_keywords',$tp->toForm($meta_keywords[e_LANGUAGE]));
					//	$text .= "<textarea class='tbox textarea e-autoheight' id='meta_keywords' name='meta_keywords' cols='70' rows='4'>".$tp->toForm(varset($meta_keywords[e_LANGUAGE]))."</textarea>";
						
						$text .= "</td>
					</tr>
					<tr>
						<td>".LAN_COPYRIGHT."</td>
						<td><input class='tbox form-control input-xxlarge' size='70' type='text' name='meta_copyright' value=\"".$meta_copyright[e_LANGUAGE]."\" /></td>
					</tr>

					<tr>
						<td>".LAN_AUTHOR."</td>
						<td><input class='tbox form-control input-xxlarge' size='70' type='text' name='meta_author' value=\"".$meta_author[e_LANGUAGE]."\" /></td>
					</tr>

					<tr>
						<td>".METLAN_1."</td>
						<td>";
						$text .= $frm->textarea('meta',str_replace("<","&lt;",$tp->toForm($meta[e_LANGUAGE])),5,100,'size=block-level');
						
						$text .= "<span class='field-help'>".METLAN_2."</span>";
						
				//		$text .= "<textarea class='tbox textarea e-autoheight' id='meta' name='meta' cols='70' rows='10' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'>".str_replace("<","&lt;",$tp->toForm(varset($meta[e_LANGUAGE])))."</textarea><span class='field-help'>".METLAN_2."</span>";
						
						$text .= "</td>
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
			<input type='hidden' name='e-token' value='".e_TOKEN."' />
		</fieldset>
	</form>
";

$ns->tablerender(METLAN_00." (".e_LANGUAGE.")", $mes->render().$text);

require_once("footer.php");

?>