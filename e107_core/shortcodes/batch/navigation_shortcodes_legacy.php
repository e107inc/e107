<?php


	trait navigation_shortcodes_legacy
	{

		/**
		 * @deprecated
		 * @return string 'active' on the active link.
		 * @example {LINK_ACTIVE}
		 */
		function sc_link_active($parm = null)
		{
			trigger_error('<b>{LINK_ACTIVE} is deprecated</b> Use {NAV_LINK_ACTIVE} instead', E_USER_DEPRECATED); // NO LAN
			return $this->sc_nav_link_active($parm);
		}

		/**
		 * Return the primary_id number for the current link
		 * @deprecated
		 * @return integer
		 */
		function sc_link_id($parm = null)
		{
			trigger_error('<b>{LINK_ID} is deprecated</b> Use {NAV_LINK_ID} instead', E_USER_DEPRECATED); // NO LAN
			return $this->sc_nav_link_id($parm);
		}

		/**
		 * @deprecated
		 * @param null $parm
		 * @return int
		 */
		function sc_link_depth($parm = null)
		{
			trigger_error('<b>{LINK_DEPTH} is deprecated</b> Use {NAV_LINK_DEPTH} instead', E_USER_DEPRECATED); // NO LAN
			return $this->sc_nav_link_depth($parm);
		}


		/**
		 * Return the name of the current link
		 * @deprecated
		 * @return string
		 * @example {LINK_NAME}
		 */
		function sc_link_name($parm = null)
		{
			trigger_error('<b>{LINK_NAME} is deprecated</b> Use {NAV_LINK_NAME} instead', E_USER_DEPRECATED); // NO LAN
			return $this->sc_nav_link_name($parm);
		}


		/**
		 * Return the parent of the current link
		 * @deprecated
		 * @return integer
		 */
		function sc_link_parent($parm = null)
		{
			trigger_error('<b>{LINK_PARENT} is deprecated</b> Use {NAV_LINK_PARENT} instead', E_USER_DEPRECATED); // NO LAN
			return $this->sc_nav_link_parent($parm);
		}

		/**
		 * @deprecated
		 * @param null $parm
		 * @return mixed|string
		 */
		function sc_link_identifier($parm = null)
		{
			trigger_error('<b>{LINK_IDENTIFIER} is deprecated</b> Use {NAV_LINK_IDENTIFIER} instead', E_USER_DEPRECATED); // NO LAN
			return $this->sc_nav_link_identifier($parm);
		}

		/**
		 * Return the URL of the current link
		 * @deprecated
		 * @return string
		 */
		function sc_link_url($parm = null)
		{
			trigger_error('<b>{LINK_URL} is deprecated</b> Use {NAV_LINK_URL} instead', E_USER_DEPRECATED); // NO LAN
			return $this->sc_nav_link_url($parm);
		}


		/**
		 * Returns only the anchor target in the URL if one is found.
		 * @deprecated
		 * @param array $parm
		 * @return null|string
		 */
		function sc_link_target($parm = null)
		{
			trigger_error('<b>{LINK_TARGET} is deprecated</b> Use {NAV_LINK_TARGET} instead', E_USER_DEPRECATED); // NO LAN
			return $this->sc_nav_link_target($parm);
		}

		/**
		 * @deprecated
		 * @param null $parm
		 * @return string
		 */
		function sc_link_open($parm = null)
		{
			trigger_error('<b>{LINK_OPEN} is deprecated</b> Use {NAV_LINK_ICON} instead', E_USER_DEPRECATED); // NO LAN
			return $this->sc_nav_link_open($parm);
		}

		/**
		 * @deprecated - Use {LINK_ICON} instead.
		 */
		function sc_link_image($parm = null)
		{
			trigger_error('<b>{LINK_IMAGE} is deprecated</b> Use {NAV_LINK_ICON} instead', E_USER_DEPRECATED); // NO LAN
			return $this->sc_nav_link_icon($parm);
		}


		/**
		 * Return the link icon of the current link
		 * @deprecated
		 * @return string
		 */
		function sc_link_icon($parm = null)
		{
			trigger_error('<b>{LINK_ICON} is deprecated</b> Use {NAV_LINK_ICON} instead', E_USER_DEPRECATED); // NO LAN
			return $this->sc_nav_link_icon($parm);
		}


		/**
		 * Return the link description of the current link
		 * @deprecated
		 * @return string
		 */
		function sc_link_description($parm = null)
		{
			trigger_error('<b>{LINK_DESCRIPTION} is deprecated</b> Use {NAV_LINK_DESCRIPTION} instead', E_USER_DEPRECATED); // NO LAN
			return $this->sc_nav_link_description($parm);
		}


		/**
		 * Return the parsed sublinks of the current link
		 * @deprecated
		 * @return string
		 */
		function sc_link_sub($parm = null)
		{
			trigger_error('<b>{LINK_SUB} is deprecated</b> Use {NAV_LINK_SUB} instead', E_USER_DEPRECATED); // NO LAN
			return $this->sc_nav_link_sub($parm);
		}

		/**
		 * Return a generated anchor for the current link.
		 * @deprecated 
		 * @param unused
		 * @return    string - a generated anchor for the current link.
		 * @example {LINK_ANCHOR}
		 */
		function sc_link_anchor($parm = null)
		{
			trigger_error('<b>{LINK_ANCHOR} is deprecated</b> Use {NAV_LINK_ANCHOR} instead', E_USER_DEPRECATED); // NO LAN
			return $this->sc_nav_link_anchor($parm);
		}


	}