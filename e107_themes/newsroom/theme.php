<?

$themename = "newsroom";
$themeversion = "1.0";
$themeauthor = "CaMer0n";
$themedate = "10th March 2005";
$themeinfo = "This theme displays some of the news features of 0.7<br />For best results, create news items with a thumbnail image of 65x50 pixels.<br />To place these items in the top right area, choose 'othernews2' for the rendertype of the news item.<br />'othernews' may be used for the simple bullet listing below it.<br />This theme may be used freely under the GPL license providing the 'e107 newsroom' image is replaced.";
$xhtmlcompliant = TRUE;
define("THEME_DISCLAIMER", "<br /><i>NewsRoom theme v1.0 by CaMer0n</i>");
define("IMODE", "dark");

// [layout]

$layout = "_default";

$HEADER = "
<div style='text-align:left'>
<div style='margin-left:0px;margin-right:auto;padding-left:3px;padding-right:3px;width:770px;background-color:#6A6A6A;border:1px solid black'>
<div style='width:770px;padding:0px;'>
<table cellpadding='0' cellspacing='0' style=\"background-image: url('".THEME."images/logo_bg.png');margin-bottom:0px;padding:0px;width:100%;\">
	<tr>
		<td style='padding-left:5px;'>
			<img src=\"".THEME."images/logo_text.png\" style='display:block' alt=\"newsroom\"  />
		</td>
		<td style='text-align:right;padding-right:4px'>

			<br />
			{CUSTOM=search}
		</td>
	</tr>
</table>
</div>
<div style='padding-right:3px;text-align:right;color:silver;display:block;width:770px;background-color:#000000;height:16px;border-bottom:3px solid #c00'>
{CUSTOM=clock}
</div>

<div style='padding-top:0px;text-align:left;margin-left:0px;margin-right:auto'>


<table style='margin-top:6px;margin-left:0px;width:770px;' cellpadding=\"0\" cellspacing=\"0\">
<tr>
	<td style='text-align:left;vertical-align:top;width:145px' rowspan='2'>
		<div style='margin-bottom:6px'>
		{SITELINKS=flat}
		</div>
		{SETSTYLE=leftmenu}
		{PLUGIN=login_menu}
		{MENU=1}
		{SETSTYLE=default}
	</td>

	<td style='vertical-align:top;padding-left:6px;padding-right:6px'>

";
$CUSTOMHEADER = $HEADER."<div>";
$HEADER .= "<div style='width:440px'> ";
$CUSTOMPAGES = "user.php usersettings.php forum_viewforum.php forum.php forum_viewtopic.php forum_post.php";

$FOOTER = "
		<div style='margin-top:6px'>
		{NEWS_CATEGORIES}
		</div>
		</div>

	</td>
	<td style='width:170px;vertical-align:top;'>
		{SETSTYLE=othernews}
		{PLUGIN=other_news_menu/other_news2_menu}
		{SETSTYLE=rightmenu}
		{MENU=2}
	</td>
</tr>";

$FOOTERBASE = "

<tr>
	<td colspan='2' style='vertical-align:top'>
		<table cellpadding='0' cellspacing='6' style='width:100%'>
		<tr>
			<td style='vertical-align:top;width:33%'>
				<!-- Powered by Menu Start -->
				{SETSTYLE=bottom}
				{PLUGIN=powered_by_menu}
				<!-- Powered by Menu Stop -->
			</td>
			<td style=\"vertical-align:top;width:33%\">
				<!-- Compliance Menu Start -->
				{PLUGIN=compliance_menu}
				<!-- Compliance Menu Stop -->
			</td>
			<td style='vertical-align:top;width:33%'>
				<!-- Compliance Menu Start -->
				{PLUGIN=sitebutton_menu}
				<!-- Compliance Menu Stop -->
			</td>
		</tr>
		</table>
	</td>
</tr>
</table>






<!-- FOOTER -->

<table style='margin-top:4px' width=\"770\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n
<tr>
<td style='width:770px;border-top:1px solid #999999'>

<div style='padding:5px;background-color:#333333;border-top:1px solid white'>
<table style='width:100%;' border=\"0\" cellpadding=\"3\" cellspacing=\"0\">
	<tr>
	<td>&nbsp;
	<a href='".e_BASE."' style='color:silver;'>".SITENAME."</a>
	</td>
	<td style='text-align:center'>
	{CUSTOM=language}
	</td>
	<td>
	<a href='http://www.e107.org' rel='external' style='color:silver;'>e107.org</a>
	</td>
	<td>
	<a href='http://www.e107coders.org' rel='external' style=\"color:silver;\">e107coders.org</a></td>
	<td>
	<a href='http://www.e107themes.org' rel='external' style=\"color:silver;\">e107themes.org</a></td>
	<td>
	<a href='http://www.e107styles.org'  style=\"color:silver;\">e107styles.org</a>
	</td>

	</tr>
</table>
</div>

<div style=\"border-top:2px solid white;background-image: url('".THEME."images/logo_bg.png');text-align:center\">
<table style='width:100%;'><tr>
<td style='vertical-align:top;padding:10px;width:50%;border-right:1px dotted silver'>
{SITEDISCLAIMER}
</td>
<td style='vertical-align:top;padding:10px;width:50%'>
{SITETAG}
</td>
</tr>
</table>
</div>



</td></tr></table>
</div></div></div>\n";
// this part just saves us duplicating the html in the footer section.
$CUSTOMFOOTER = "</div></td>
</tr>".$FOOTERBASE;

$FOOTER .= $FOOTERBASE;


$NEWSSTYLE = "
	<div class='bodytable' style='width:100%'>
	<table cellpadding='0' cellspacing='0' border='0' style='width:100%'>
	 <tr>
	 <td class='captionnews' style=\"padding-left:3px;font-weight:bold;font-size:22px;width:100%;\" >
	{NEWSTITLE}
	 </td>
	 </tr>
	</table>
	<table style='width:100%'>
	<tr>
	<td style='text-align:justify;padding:3px'>
	{NEWSBODY}
	{EXTENDED}
	<br />
	</td>

	</tr>
	</table>
	<table style='width:100%;border:0px' cellpadding='0' cellspacing='0' >
	<tr>
	<td style='padding:3px'>Posted on
	{NEWSDATE}
	 |
	{EMAILICON}
	{PRINTICON}
	{ADMINOPTIONS}

	</td>
	</tr>
	</table><br /></div>

";

// the style of the items in news.php?cat and news.php?all
$NEWSLISTSTYLE = "<table cellpadding='0' cellspacing='0' style='margin-bottom:3px;border-bottom:1px solid black;width:100%'>

	<tr><td style='padding:3px;vertical-align:top;width:65px'>
		{NEWSTHUMBNAIL}
	</td>
	<td style='padding:3px;text-align:left;vertical-align:top'>
	{NEWSTITLELINK}
	<br />
	{NEWSSUMMARY}
	</td>
	</tr>
	</table>";


// You can customize the news-category bullet listing here.

$NEWSCAT = "\n\n\n\n<!-- News Category -->\n\n\n\n
	<div style='padding:2px;padding-bottom:12px'>
	<div class='leftcap' style='vertical-align:center'>
	{NEWSCATICON}
	&nbsp;<span style='vertical-align:top;margin-top:3px'>
	{NEWSCATEGORY}
	</span>
	</div>
	<div style='width:100%;text-align:left;padding-top:2px'>
	{NEWSCAT_ITEM}
	</div>
	</div>
";

/*
$NEWSCAT_ITEM = "
        <div style='width:100%;height:14px;display:block'>
        <table style='width:100%'>
        <tr><td style='width:2px;vertical-align:middle'>&#8226;&nbsp;
        {NEWSCATICON}
        </td>
        <td style='text-align:left;height:10px'>{NEWSTITLELINK} </td></tr></table></div>
";*/




$OTHERNEWS_STYLE = "<div style='text-align:left;margin-bottom:3px'>
	<table style='width:100%'><tr><td style='color:black;text-align:left;width:10px;vertical-align:top'>
&#8226;</td><td style='text-align:left;vertical-align:top'>
	<div style='font-size:11px;'><b>{NEWSTITLELINK}</b></div></td></tr></table>
	</div>";


$OTHERNEWS2_STYLE = "
	<table cellpadding='0' cellspacing='0' style='border-bottom:1px solid black;width:100%;'>
	<tr><td class='caption2' colspan='2' style='padding:3px;text-decoration:none'>
	{NEWSCATEGORY}
	</td></tr>
	<tr><td style='padding:3px;vertical-align:top;background-color:#6A6A6A'>
	{NEWSTITLELINK}
	<br />
	{NEWSSUMMARY}
	</td>
	<td style='padding:3px;text-align:right;vertical-align:top;background-color:#6A6A6A'>
	{NEWSTHUMBNAIL}
	</td>
	</tr>
	</table>
";

// [News]
define("ICONSTYLE", "float: left; border:0");
define("COMMENTLINK", "Comments");
define("COMMENTOFFSTRING", "Commends Off");
define("PRE_EXTENDEDSTRING", "<span style='font-size:13px'> ");
define("EXTENDEDSTRING", "<br /><br /><b>FULL STORY</b>");
define("POST_EXTENDEDSTRING", "</span><br />");
define("ICONMAIL", "iconmail.png"); // Usable since e107v615
define("ICONPRINT", "iconprint.png"); // Usable since e107v615

// [News List by Category]
define("NEWSLIST_ITEMLINK","font-weight:bold");
define("NEWSLIST_THUMB","border:1px dotted black");
define("NEWSLIST_CATICON","border:1px solid white");

// [news categories]
define("NEWSCAT_CATLINK","font-weight:bold;font-size:12px;text-decoration:none;color:black");
define("NEWSCAT_ITEMLINK","font-size:12px;color:#fff");
define("NEWSCAT_STYLE","width:100%;margin-bottom:3px");
define("NEWSCAT_AMOUNT",3);
define("NEWSCAT_COLS",2);
define("NEWSCAT_THUMB","border:1px solid black");
// define("NEWSCAT_CATICON","border:1px solid red");

// [other news]
define("OTHERNEWS_CATLINK","text-decoration:none;");
define("OTHERNEWS_ITEMLINK","color:black;font-weight:bold;text-decoration:underline");
define("OTHERNEWS_LIMIT",3);

// [other news 2]
define("OTHERNEWS2_CATLINK","color:white;text-decoration:none;");
define("OTHERNEWS2_ITEMLINK","color:#cccccc;text-transform:uppercase;font-weight:bold;text-decoration:none;");
define("OTHERNEWS2_LIMIT",4);

// [linkstyle]

define(PRELINK, "<table style='width:145px;background-color:#eeeeee' border=\"0\" cellpadding=\"0\" cellspacing=\"0\" summary=\"Newsroom Navigation\">");
define(POSTLINK, "</table>");
define(LINKSTART, "<tr class=\"newsroomRow\">
	<td class=\"dent\">&nbsp;</td>
	<td class=\"newsroom\" onmouseover=\"this.style.backgroundColor='#6998CC';window.status='hithere'\" onmouseout=\"this.style.backgroundColor='#333333'\" >
	<div class=\"newsroomText\"><b>&nbsp;");

define(LINKSTART_HILITE, "<tr class=\"newsroomHiliteRow\">
	<td class=\"dent\">&nbsp;</td>
	<td class=\"newsroomHilite\" onmouseover=\"this.style.backgroundColor='#666666'\" onmouseout=\"this.style.backgroundColor='#c00'\" >
	<div class=\"newsroomText\"><b>&nbsp;");

define(LINKEND, "</b></div></td></tr>");
define(LINKDISPLAY, 1);
define(LINKALIGN, "left");


function tablestyle($caption, $text){
	global $style;

switch ($style) {
	case "leftmenu":
	echo "<div style='width:145px'>
	<table style='width:100%' cellpadding='0' cellspacing='0'>
	<tr>
	<td>
	<div class='leftcap'>
	$caption
	</div>
	<div class=\"forumheader3\" >$text</div>
	</td></tr></table></div>";
	break;

	case "rightmenu":
	echo "
	<div class='spacer' style='border-top:2px solid #CC0000' >
	<table style='width:100%;margin-left:0px' cellpadding='0' cellspacing='0'>
	<tr>
	<td class='caption'>".$caption."</td>
	</tr>
	</table>

	<table style='width:100%;margin-left:0px' cellpadding='0' cellspacing='0'>
	<tr>
	<td class='forumheader3' >".$text."</td>
	</tr>
	</table>
	</div>";
	break;

	case "highlight":
	echo "<div class='spacer'><div style='background-color:#D0DBE8;border:1px solid silver'>
	<div class='captionbar' style='text-align:left;text-transform:uppercase;white-space:nowrap'>".$caption."</div>
	<div style='padding:4px'>".$text."</div>
	</div></div>";
	break;

	case "othernews":
	echo "<div style='text-align:left;margin-left:0px'>
	<div style='padding:0px;text-align:left;margin-left:0px'>".$text."</div>
	</div>";
	break;

	case "bottom":
	echo "<div style='padding:3px;border-top:1px solid #CCCCCC;border-bottom:1px solid #CCCCCC'>
	<b style=\"font-size:12px;color: #c00;\">$caption</b>
	</div><div style='padding:3px;text-align:center'>$text</div>";
	break;

	default:
	echo "<div style='width:100%;vertical-align:top'>
	<table style='width:100%' cellpadding='0' cellspacing='0'>
	<tr >
	<td>
	<div class=\"caption2\" style='color:white;padding:3px'>
	$caption
	</div>
	<div class=\"forumheader3\" style='padding:6px'>$text</div>
	</td></tr></table></div>";

	break;
 }
}


// for testing the news parser.

/*$NEWSSTYLE = "
NEWSTITLE =
{NEWSTITLE}
<br />
<br />

NEWSBODY =
{NEWSBODY}
<br />
<br />
<br /><br />
NEWSICON = {NEWSICON}
<br />
<br />
<br />
<br />
NEWSHEADER =
{NEWSHEADER}
<br />
<br />
NEWSCATEGORY =
{NEWSCATEGORY}
<br />
<br />
NEWSAUTHOR =
{NEWSAUTHOR}
<br />
<br />

NEWSDATE =
{NEWSDATE}
<br />
<br />
NEWSCOMMENTS =
{NEWSCOMMENTS}
<br />
<br />
EMAILICON =
{EMAILICON}
<br />
<br />
PRINTICON =
{PRINTICON}
<br />
<br />
NEWSID =
{NEWSID}
<br />
<br />
ADMINOPTIONS =
{ADMINOPTIONS}
<br />
<br />
EXTENDED =
{EXTENDED}
<br />
<br />
CAPTIONCLASS =
{CAPTIONCLASS}
<br />
<br />
ADMINCAPTION =
{ADMINCAPTION}
<br />
<br />
ADMINBODY =
{ADMINBODY}
<br />
<br />
NEWSSUMMARY =
{NEWSSUMMARY}
<br />
<br />
NEWSTHUMBNAIL =
{NEWSTHUMBNAIL}
<br />
<br />
STICKYICON =
{STICKY_ICON}
<br />
<br />
NEWSTITLELINK =
{NEWSTITLELINK}
<br />
<br />


";
*/