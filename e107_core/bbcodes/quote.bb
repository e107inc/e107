//<?
$class = e107::getBB()->getClass('quote');
e107::includeLan(e_LANGUAGEDIR.e_LANGUAGE."/lan_parser_functions.php");

if(deftrue('BOOTSTRAP'))
{
    return '<blockquote>
      <p>'.$code_text.'</p>
      <small><cite title="'.$parm.'">'.$parm.'</cite></small>
    </blockquote>';
}

return "<div class='indent {$class}'><em>$parm ".LAN_WROTE."</em> ...<br />$code_text</div>";
