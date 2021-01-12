//this shortcode is deprecated due to a conflict with the news {EXTENDED} shortcode.
// Use USER_EXTENDED instead.

if(empty($parm))
{
    return null;
}

return e107::getParser()->parseTemplate("{USER_EXTENDED=$parm}");
