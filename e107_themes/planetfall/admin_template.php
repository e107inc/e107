<?php

// [prerenders]

$style = "leftmenu";
$prehelp = $tp -> parseTemplate('{ADMIN_HELP}');

$style = "rightmenu";
$pre_admin_menu = $tp -> parseTemplate('{ADMIN_MENU=pre}');
$preright = $tp -> parseTemplate('{ADMIN_STATUS=request}');
$preright .= $tp -> parseTemplate('{ADMIN_LATEST=request}');
$preright .= $tp -> parseTemplate('{ADMIN_PRESET}');
$preright .= $tp -> parseTemplate('{ADMIN_LOG=request}');
$style = "default";

// [admin button style]

//function show_admin_menu($title, $page, $e107_vars, $js = FALSE, $js_include = FALSE){
function show_admin_menu($title, $page, $e107_vars, $js = FALSE){
	global $ns;
	$text = "<table class='fborder' style='width: 100%'>";
	foreach (array_keys($e107_vars) as $act) {
		$t=str_replace(" ","&nbsp;",$e107_vars[$act]['text']);
		if (!$e107_vars[$act]['perm'] || getperms($e107_vars[$act]['perm'])) {
			$active = ($page == $act) ? "mainlevel-hilite" : "mainlevel";
			if ($js_include) {
				$on_click = $js_include;
			} else {
				$on_click = $js ? "showhideit('".$act."');" : "document.location='".$e107_vars[$act]['link']."'; disabled=true;";
			}
			$text .= "<tr><td style='border-bottom: 1px solid #000'><div >
			<div class='".$active."' onmouseover=\"eover(this, 'mainlevel-over')\" onmouseout=\"eover(this, '$active')\" onclick=\"".$on_click."\"	style='cursor:default;width: 98%; padding: 0px 0px 0px 12px; border-right: 0px'>
			&nbsp;".$t."</div>
			</div>
			</td></tr>";
		}
	}

	$text .= "</table>";
	if ($title=="") {
		return $text;
	}
	$ns -> tablerender($title,$text, array('id' => $title, 'style' => 'button_menu'));
}


// [layout]
// <img src='".THEME."images/logo.png' style='width: 170px; height: 71px; display: block;' alt='' />
$ADMIN_HEADER = "<table style='	border: solid 1px #9DA6B3;'>
<tr>
<td>
<img src=".THEME."images/admin_logo.jpg alt='' style='display:block;border:0px' />
</td>
<td class='top_section_mid' style='width: 100%'>
<div style='margin-bottom: 3px;vertical-align:top'>
{ADMIN_LOGGED}
<br />
{ADMIN_SEL_LAN}
{ADMIN_USERLAN}
</div>
</td>

<td class='top_section_right' style='padding: 0px 18px 0px 18px; width: 68px'>
<div style='height: 32px;'>
{ADMIN_ICON}
</div>
</td>
</tr>
</table>";

if (ADMIN) {
 	$ADMIN_HEADER .= "{ADMIN_ALT_NAV}";
} else {
	if (file_exists(THEME.'admin_nav.js')) {
	 	$ADMIN_HEADER .= "<script type='text/javascript' src='".THEME."admin_nav.js'></script>";
	} else {
		$ADMIN_HEADER .= "<script type='text/javascript' src='".e_FILE."admin_nav.js'></script>";
	}

	$ADMIN_HEADER .= "<div style='width: 100%'><table style='width:100%; border-collapse: collapse; border-spacing: 0px;'>
	<tr><td>
	<div class='menuBar' style='width:100%;'>
	&nbsp;
	</div>
	</td>
	</tr>
	</table></div>";
}

$ADMIN_HEADER .= "<table class='main_section' style='width:100%;margin-left:0px'>
<tr>
<td class='right_box' style='border-right: solid 1px #9DA6B3;width:150px;vertical-align:top;'>
<table style='width:100%; border-collapse: collapse; border-spacing: 0px;'>
<tr>
<td style='vertical-align:top;width:150px'>
{SETSTYLE=leftmenu}
{ADMIN_LANG}
{ADMIN_PWORD}
{ADMIN_MSG}
{ADMIN_PLUGINS}";

if (!ADMIN) {
	$style='leftmenu';
	$ADMIN_HEADER .= $ns -> tablerender('Welcome', '', '', TRUE);
	$style='default';
}

if ($prehelp!='') {
	$ADMIN_HEADER .= $prehelp;
} else {
	$ADMIN_HEADER .= "{ADMIN_SITEINFO}";
}

$ADMIN_HEADER .= "<br />
</td></tr></table>
</td>
<td style='padding:5px;vertical-align:top;width:auto'>
{SETSTYLE=default}
";

$ADMIN_FOOTER = "<br />
</td>";
// admin menu.
if ($pre_admin_menu || $preright) {
	$ADMIN_FOOTER .= "<td class='middle_box' style='width:180px;vertical-align:top'>
	<table style='width:100%; border-collapse: collapse; border-spacing: 0px;'>
	<tr>
	<td style='padding:5px'>
	{SETSTYLE=rightmenu}
	{ADMIN_MENU}
	".$preright."
	<br />
	</td></tr></table>
	</td>";
}

$ADMIN_FOOTER .= "</tr>
</table>
";
?>