$id = ($parm) ? $parm : USERID;
return "<a href='".e_HTTP."user.php?id.{$id}'>".defset('IMAGE_profile')."</a>";
