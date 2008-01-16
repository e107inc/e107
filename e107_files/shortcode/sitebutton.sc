global $tp;
$path = ($_POST['sitebutton'] && $_POST['ajax_used']) ? $tp->replaceConstants($_POST['sitebutton']) : (strstr(SITEBUTTON, "http:") ? SITEBUTTON : e_IMAGE.SITEBUTTON);
return "<a href='".SITEURL."'><img src='".$path."' alt=\"".SITENAME."\" style='border: 0px; width: 88px; height: 31px' /></a>";
