<?php


trait news_shortcodes_legacy
{
	public function sc_newsbody($parm=null)
	{
		trigger_error('<b>{NEWSBODY} is deprecated</b> Use {NEWS_BODY} instead', E_USER_DEPRECATED); // NO LAN
		return $this->sc_news_body($parm);
	}

	public function sc_newsauthor($parm=null)
	{
		trigger_error('<b>{NEWSAUTHOR} is deprecated</b> Use {NEWS_AUTHOR} instead', E_USER_DEPRECATED); // NO LAN
		return $this->sc_news_author($parm);
	}


	public function sc_newsavatar($parm=null)
	{
		trigger_error('<b>{NEWSAVATAR} is deprecated</b> Use {NEWS_AUTHOR_AVATAR} instead', E_USER_DEPRECATED); // NO LAN
		return $this->sc_news_author_avatar($parm);
	}

	/* use {NEWS_CATEGORY_ICON} instead */
	function sc_newscaticon($parm = array())
	{
		trigger_error('<b>{NEWSCATICON} is deprecated</b> Use {NEWS_CATEGORY_ICON} instead', E_USER_DEPRECATED); // NO LAN
		return $this->sc_news_category_icon($parm);
	}

	public function sc_newsurl($parm=null)
	{
		trigger_error('<b>{NEWSURL} is deprecated</b> Use {NEWS_URL} instead', E_USER_DEPRECATED); // NO LAN
		return $this->sc_news_url($parm);
	}

	public function sc_newstags($parm=null)
	{
		trigger_error('<b>{NEWSTAGS} is deprecated</b> Use {NEWS_TAGS} instead', E_USER_DEPRECATED); // NO LAN
		return $this->sc_news_tags($parm);
	}

	public function sc_newssummary($parm=null)
	{
		trigger_error('<b>{NEWSSUMMARY} is deprecated</b> Use {NEWS_SUMMARY} instead', E_USER_DEPRECATED); // NO LAN
		return $this->sc_news_summary($parm);
	}

	public function sc_newsmetadiz($parm=null)
	{
		trigger_error('<b>{NEWSMETADIZ} is deprecated</b> Use {NEWS_DESCRIPTION} instead', E_USER_DEPRECATED); // NO LAN
		return $this->sc_news_description($parm);
	}

	public function sc_newsdate($parm=null)
	{
		trigger_error('<b>{NEWSDATE} is deprecated</b> Use {NEWS_DATE} instead', E_USER_DEPRECATED); // NO LAN
		return $this->sc_news_date($parm);
	}

	public function sc_news_user_avatar($parm=null)
	{
		trigger_error('<b>{NEWS_USER_AVATAR} is deprecated</b> Use {NEWS_AUTHOR_AVATAR} instead', E_USER_DEPRECATED); // NO LAN
		return $this->sc_news_author_avatar($parm);
	}

	public function sc_newsrelated($parm=null)
	{
		trigger_error('<b>{NEWSRELATED} is deprecated</b> Use {NEWS_RELATED} instead', E_USER_DEPRECATED); // NO LAN
		return $this->sc_news_related($parm);
	}

	function sc_newsicon($parm=null)
	{
		trigger_error('<b>{NEWSICON} is deprecated</b> Use {NEWS_CATEGORY_ICON=url} instead', E_USER_DEPRECATED); // NO LAN
		return $this->sc_news_category_icon('url');
	}

	public function sc_newsid($parm=null)
	{
		trigger_error('<b>{NEWSID} is deprecated</b> Use {NEWS_ID} instead', E_USER_DEPRECATED); // NO LAN
		return $this->sc_news_id($parm);
	}

	public function sc_newsinfo($parm=null)
	{
		trigger_error('<b>{NEWSINFO} is deprecated</b> Use {NEWS_INFO} instead', E_USER_DEPRECATED); // NO LAN
		return $this->sc_news_info($parm);
	}

	public function sc_newstitlelink($parm=null)
	{
		trigger_error('<b>{NEWSTITLELINK} is deprecated</b> Use {NEWS_TITLE: link=1} instead', E_USER_DEPRECATED); // NO LAN
		return $this->newsTitleLink($parm);
	}

	public function sc_newstitle($parm=null)
	{
		trigger_error('<b>{NEWSTITLE} is deprecated</b> Use {NEWS_TITLE} instead', E_USER_DEPRECATED); // NO LAN
		return $this->newsTitle($parm);
	}

	public function sc_newsurltitle()
	{
		trigger_error('<b>{NEWSURLTITLE} is deprecated</b> Use {NEWS_TITLE: link=1} instead', E_USER_DEPRECATED); // NO LAN
		return $this->newsTitleLink();
	}

	public function sc_newsvideo($parm=null)
	{
		trigger_error('<b>{NEWSVIDEO} is deprecated</b> Use {NEWS_VIDEO} instead', E_USER_DEPRECATED); // NO LAN
		return $this->sc_news_video($parm);
	}

	public function sc_newsimage($parm = null)
	{
		trigger_error('<b>{NEWSIMAGE} is deprecated</b> Use {NEWS_IMAGE} instead', E_USER_DEPRECATED); // NO LAN
	    return $this->sc_news_image($parm);
	}

	public function sc_newsmedia($parm = null)
	{
		trigger_error('<b>{NEWSMEDIA} is deprecated</b> Use {NEWS_MEDIA} instead', E_USER_DEPRECATED); // NO LAN
	    return $this->sc_news_media($parm);
	}

	public function sc_newscommentcount($parm=null)
	{
		trigger_error('<b>{NEWSCOMMENTCOUNT} is deprecated</b> Use {NEWS_COMMENT_COUNT} instead', E_USER_DEPRECATED); // NO LAN
		return $this->sc_news_comment_count($parm);
	}

	public function sc_newsthumbnail($parm=null)
	{
		trigger_error('<b>{NEWSTHUMBNAIL} is deprecated</b> Use {NEWS_THUMBNAIL} instead', E_USER_DEPRECATED); // NO LAN
		return $this->sc_news_thumbnail($parm);
	}

}