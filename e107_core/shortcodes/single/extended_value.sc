//USAGE:  {EXTENDED_VALUE=<field_name>.<user_id>}
//EXAMPLE: {EXTENDED_VALUE=user_gender.5}  will show the value of the extended field user_gender for user #5
if(empty($parm) || !is_string($parm))
{
    return null;
}

$parms = explode(".", $parm);
return e107::getParser()->parseTemplate("{USER_EXTENDED={$parms[0]}.value.{$parms[1]}}");
