<?php

if (!defined('e107_INIT'))
{ 
	require_once("../../class2.php");
}

if(!e107::isInstalled('metaweblog'))
{
 	exit();
}

echo "<?xml version=\"1.0\" encoding=\"utf-8\" ?>
<manifest xmlns=\"http://schemas.microsoft.com/wlw/manifest/weblog\">
  <options>
    <clientType>WordPress</clientType>
	<supportsKeywords>Yes</supportsKeywords>
	<supportsGetTags>Yes</supportsGetTags>
  </options>
  
  <weblog>
    <serviceName>e107</serviceName>
    <imageUrl>images/wlw/e107_icon_32.png</imageUrl>
    <watermarkImageUrl>images/wlw/e107_icon_32_wat.png</watermarkImageUrl>
    <homepageLinkText>View site</homepageLinkText>
    <adminLinkText>Dashboard</adminLinkText>
    <adminUrl>
      <![CDATA[". SITEURLBASE.e_ADMIN_ABS."
		]]>
    </adminUrl>
    <postEditingUrl>
      <![CDATA[ 
			".SITEURLBASE.e_ADMIN_ABS."newspost.php?create.edit."."{post-id}
		]]>
    </postEditingUrl>
  </weblog>

  <buttons>
    <button>
      <id>0</id>
      <text>Manage Comments</text>
      <imageUrl>images/wlw/comments_32.png</imageUrl>
      <clickUrl>
        <![CDATA[ 
				". SITEURLBASE.e_ADMIN_ABS."comment.php
			]]>
      </clickUrl>
    </button>

  </buttons>

</manifest>"

