<?php
$TAGCLOUD_MENU_TEMPLATE = array();
          
$TAGCLOUD_MENU_TEMPLATE['default']['caption']       = '{TAGCLOUD_MENU_CAPTION}';
$TAGCLOUD_MENU_TEMPLATE['default']['start']       = '<div class="tagcloud-menu">';
$TAGCLOUD_MENU_TEMPLATE['default']['item']       = "<a class='tag' href='{TAG_URL}'><span class='size{TAG_SIZE}'>{TAG_NAME}</span></a>";
$TAGCLOUD_MENU_TEMPLATE['default']['end']       = '<div style="clear:both"></div></div>';
      
  /* example  for the same size tag
$TAGCLOUD_MENU_TEMPLATE['default']['caption']   = '{TAGCLOUD_MENU_CAPTION}';
$TAGCLOUD_MENU_TEMPLATE['default']['start']     = '<div class="tag-cloud">';
$TAGCLOUD_MENU_TEMPLATE['default']['item']      = '<span class="badge"><a href="{TAG_URL}">{TAG_NAME}</a> ({TAG_COUNT})</span>';
$TAGCLOUD_MENU_TEMPLATE['default']['end']       = '</div>';
 */