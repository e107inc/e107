//USAGE:  {EXTENDED_ICON=<field_name>.<user_id>}
//EXAMPLE: {EXTENDED_ICON=user_gender.5}  will show the icon of the extended field user_gender for user #5
$parms = explode(".", $parm);
global $tp;
return $tp->parseTemplate("{USER_EXTENDED={$parms[0]}.icon.{$parms[1]}}");
