$id = ($parm) ? $parm : USERID;
$image = (file_exists(THEME."forum/profile.png")) ? "<img src='".THEME."forum/profile.png' alt='' style='border:0' />" : "<img src='".e_PLUGIN."forum/images/profile.png' alt='' style='border:0' />";
return "<a href='".e_BASE."user.php?id.{$id}'>{$image}</a>";
