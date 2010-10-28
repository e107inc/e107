<?php
// search module for faq.

$search_info[$key]['qtype'] = "FAQs";    

if($results = $sql -> db_Select("faqs", "*", "faq_question REGEXP('".$query."') OR faq_answer REGEXP('".$query."') ORDER BY faq_id DESC ")){
        while($row = $sql -> db_Fetch()){
                extract($row);
                if(preg_match('/'.str_replace('/', '\\/', $query).'/i', $faq_question)){
                        $que = parsesearch($faq_question, $query);
                        $ans = substr($faq_answer, 0,70);
                        $text .= "<img src=\"".THEME."images/bullet2.gif\" alt=\"bullet\" /> <b><a href=\"".e_PLUGIN."faqs/faqs.php?cat.".$faq_parent.".".$faq_id."\">".$que."</a></b><br /><span class=\"smalltext\">Match found in faq question</span><br />".$ans."<br /><br />";
                }
                if(preg_match('/'.str_replace('/', '\\/', $query).'/i', $faq_answer)){
                        $resmain = parsesearch($faq_answer, $query);
                        $text .= "<img src=\"".THEME."images/bullet2.gif\" alt=\"bullet\" /> <b><a href=\"".e_PLUGIN."faqs/faqs.php?cat.".$faq_parent.".".$faq_id."\">".$faq_question."</a></b><br /><span class=\"smalltext\">Match found in faq answer</span><br />".$resmain."<br /><br />";
                }
        }
}else{
        $text .= "No matches.";
}
?>