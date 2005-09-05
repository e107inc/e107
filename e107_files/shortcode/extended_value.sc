//USAGE:  {EXTENDED_VALUE=<field_name>.<user_id>}
//EXAMPLE: {EXTENDED_VALUE=user_gender.5}  will show the value of the extended field user_gender for user #5
$parms = explode(".", $parm);
global $tp;
return $tp->parseTemplate("{EXTENDED={$parms[0]}.value.{$parms[1]}}");
