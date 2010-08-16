/* $Id$ */
// decode (just in case) and re-code sanitised string if debug and ADMIN
if(defsettrue('ADMIN') && defsettrue('E107_DEBUG_LEVEL'))
{
	return '<span class="sanitised"> SANITISED: '.htmlentities(html_entity_decode(rawurldecode($code_text), ENT_QUOTES, CHARSET), ENT_QUOTES, CHARSET).'  SANITISED END</span>';
}
return '<span class="sanitised">##'.LAN_SANITISED.'##</span>';