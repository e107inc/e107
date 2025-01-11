//USAGE:  {EXTENDED_TEXT=<field_name>.<user_id>}
//EXAMPLE: {EXTENDED_TEXT=user_gender.5}  will show the text of the extended field user_gender for user #5

if(empty($parm) || !is_string($parm))
{
    return null;
}
$parms = explode(".", $parm);
global $tp;
return e107::getParser()->parseTemplate("{USER_EXTENDED={$parms[0]}.text.{$parms[1]}}");
