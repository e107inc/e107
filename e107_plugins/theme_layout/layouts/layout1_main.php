$layouttext = "This layout renders a 3-column design with menu areas on the left and right, and 3 along the bottom of the screen.\n\n";

$HEADER = "
<div style='text-align:center'>
<table style='width:100%' cellspacing='3'><tr><td colspan='3' style='text-align:left'>
{LOGO}
<br />
{SITETAG}
</td></tr><tr> <td style='width:15%; vertical-align: top;'>
{SETSTYLE=leftmenu}
{SITELINKS=menu}
{MENU=1}
</td><td style='width:70%; vertical-align: top;'>
";

$FOOTER = "
</td><td style='width:15%; vertical-align:top'>
{MENU=2}
</td></tr>
<tr>
<td colspan='3' style='text-align:center'>
{SITEDISCLAIMER}
</td>
</tr>
</table>
<table style='width:60%'>
<tr>
<td style='width:33%; vertical-align:top'>
{MENU=3}
</td>
<td style='width:33%; vertical-align:top'>
{MENU=4}
</td>
<td style='width:33%; vertical-align:top'>
{MENU=5}
</td>
</tr>
</table></div>
";