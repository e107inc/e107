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

}