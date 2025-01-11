//USAGE:  {EXTENDED_ICON=<field_name>.<user_id>}
//EXAMPLE: {EXTENDED_ICON=user_gender.5}  will show the icon of the extended field user_gender for user #5
if(empty($parm) || !is_string($parm))
{
    return null;
}

$parms = explode(".", $parm);

return e107::getParser()->parseTemplate("{USER_EXTENDED={$parms[0]}.icon.{$parms[1]}}");
