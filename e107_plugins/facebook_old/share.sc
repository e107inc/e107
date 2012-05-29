 /**
  * http://wiki.developers.facebook.com/index.php/Fb:share-button
  * 
  * $box_type = box_count, button_count, button, icon, or icon_link    
  * 
  *
  *
  *<fb:share-button class="meta" type="icon_link">
  <meta name="medium" content="blog"/>
  <meta name="title" content="Leonidas in All of Us"/>
  <meta name="video_type" content="application/x-shockwave-flash"/>
  <meta name="video_height" content="345"/>
  <meta name="video_width" content="473"/>
  <meta name="description" content="That's the lesson 300 teaches us."/>
  <link rel="image_src" href="http://9.content.collegehumor.com/d1/ch6/f/6/collegehumor.b38e345f621621dfa9de5456094735a0.jpg"/>
  <link rel="video_src" href="http://www.collegehumor.com/moogaloop/moogaloop.swf?clip_id=1757757&autoplay=true"/>
  <link rel="target_url" href="http://www.collegehumor.com/video:1757757"/>
 </fb:share-button>       
  */
  
$news_item = getcachedvars('current_news_item'); //get news id

$box_type = "box_count";

$share = "<fb:share-button href='".e_SELF."?item.".$news_item['news_id']."' type='{$box_type}'></fb:share-button>";

return $share;

