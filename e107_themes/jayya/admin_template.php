<?php

// [prerenders]

$style = "leftmenu";
$preleft = $tp -> parseTemplate('{ADMIN_HELP}');
$preleft .= $tp -> parseTemplate('{ADMIN_PWORD}');
$preleft .= $tp -> parseTemplate('{ADMIN_MSG}');
$preleft .= $tp -> parseTemplate('{ADMIN_PLUGINS}');

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
			$arrow_icon = ($page == $act) ? E_16_NAV_ARROW_OVER : E_16_NAV_ARROW;
			if ($js_include) {
				$on_click = $js_include;
			} else {
				$on_click = $js ? "showhideit('".$act."');" : "document.location='".$e107_vars[$act]['link']."'; disabled=true;";
			}
			$text .= "<tr><td style='border-bottom: 1px solid #000'><div class='emenuBar'>
			<div class='menuButton' onmouseover=\"eover(this, 'menuButton_over')\" onmouseout=\"eover(this, 'menuButton')\" onclick=\"".$on_click."\"
			style='width: 98% !important; width: 100%; padding: 0px 0px 0px 2px; border-right: 0px'>
			<img src='".$arrow_icon."' style='width: 16px; height: 16px; vertical-align: middle' alt='' />&nbsp;".$t."</div>
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

$ADMIN_HEADER = "<table class='top_section'>
<tr>
<td class='top_section_left' style='width: 170px'>
<img src='".THEME."images/logo.png' style='width: 170px; height: 71px; display: block;' alt='' />
</td>
<td class='top_section_mid' style='width: 100%'>
<div style='margin-bottom: 3px;'>
{ADMIN_LOGGED}
{ADMIN_SEL_LAN}
{ADMIN_USERLAN}
</div>
{SITELINKS=flat}
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

$ADMIN_HEADER .= "<table class='main_section'>
<tr>
<td class='left_menu'>
<table style='width:100%; border-collapse: collapse; border-spacing: 0px;'>
<tr>
<td>
{SETSTYLE=leftmenu}
{ADMIN_LANG}";

if (!ADMIN) {
	$style='leftmenu';
	$ADMIN_HEADER .= $ns -> tablerender('Welcome', '', '', TRUE);
	$style='default';	
}

if ($preleft!='') {
	$ADMIN_HEADER .= $preleft;
} else {
	$ADMIN_HEADER .= "{ADMIN_SITEINFO}";
}

$ADMIN_HEADER .= "<br />
</td></tr></table>
</td>
<td class='default_menu'>
{SETSTYLE=default}
";

$ADMIN_FOOTER = "<br />
</td>";

if ($pre_admin_menu || $preright) {
	$ADMIN_FOOTER .= "<td class='right_menu'>
	<table style='width:100%; border-collapse: collapse; border-spacing: 0px;'>
	<tr>
	<td>
	{SETSTYLE=rightmenu}
	{ADMIN_MENU}
	".$preright."
	<br />
	</td></tr></table>
	</td>";
}

$ADMIN_FOOTER .= "</tr>
</table>
<div style='text-align:center'>
<br />
{SITEDISCLAIMER}
<br /><br />
</div>
";
?>
