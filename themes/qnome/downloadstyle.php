<?


function downloadstyle($download_id, $download_name, $download_url, $download_description, $download_button, $download_category, $download_refer, $download_category_name, $download_category_description, $comment_total){

$text ="<div class=\"spacer\">$download_name<br /></div> ";

if($download_button !=""){
              
  $text .= "<div class=\"spacer\"><a href=\"download_view.php?img=".$download_id."\" target=\"\"><img src=\"$download_button\" alt=\"Image for $download_name\" width=\"100\" height=\"75\" /></a></div> ";
}

$text .= "<div class=\"spacer\"><br /> $download_description<br /></div>";


$text .=  "<div class=\"spacer\"> [ <a href=\"$PHP_SELF?id=$download_id\"><b>Download $download_name</b></a> ] [ Total: $download_refer ] [ <a href=\"download_comment.php?download_id=$download_id\">Comments: $comment_total</a> ]<br /><hr /></div>";


return $text;

}




?>


