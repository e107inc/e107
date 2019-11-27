<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Mailout - template-related functions
 *
 * $URL: https://e107.svn.sourceforge.net/svnroot/e107/trunk/e107_0.8/e107_handlers/redirection_class.php $
 * $Id: redirection_class.php 11922 2010-10-27 11:31:18Z secretr $
 * $Revision: 11315 $
 *
 */

/**
 *	Various template-related mailout functions
 * 
 *	@package    e107
 *	@subpackage	e107_handlers
 *	@version 	$Id: mailout_admin_class.php 11315 2010-02-10 18:18:01Z secretr $;
 *
 *	Handles common aspects of template-based emails
 */


class e107MailTemplate
{
	public	$lastTemplateData = FALSE;	// Mailer template info - cache
	public	$mainBodyText = FALSE;		// Cache for body text
	public	$altBodyText = FALSE;		// Cache for alternate body text



	/**
	 *	Empty our template cache
	 *
	 *	@return none
	 */
	public function clearTemplateCache()
	{
		$this->lastTemplateData = FALSE;
	}


	/**
	 *	Set a template to be used.
	 *	Clears any cached data
	 *
	 *	@param array|string $newTemplate - if a string, the name of a template. (The internal name of the variable, not the associated name)
	 *						If an array, an existing template - must be in the correct format - no checking done
	 *
	 *	@return boolean TRUE if accepted, FALSE if rejected
	 */
	public function setNewTemplate($newTemplate)
	{
		$this->mainBodyText = FALSE;
		$this->altBodyText = FALSE;

		if (is_array($newTemplate))
		{
			$this->lastTemplateData = $newTemplate;
			return TRUE;
		}
		return $this->loadTemplateInfo($newTemplate);
	}



	/**
	 *	Given a template name, assembles the array of data required by sendTemplated() and saves in our cache
	 *
	 *	Template file name is 'email_template.php'
	 *	The template is first sought in the template file of the current theme directory, and data read as available.
	 *	If $extraFile is specified, that is searched next
	 *	Gaps are then filled in from the core template file.
	 *
	 *	@param string $templateName - name of required template
	 *	@param string $extraFile - optional path to additional template file (intended for plugins)
	 *			(This is read between the theme-specific file and the defaults)
	 *
	 *	@return boolean TRUE - template found and loaded. FALSE if not found.
	 *	If successful, we store an array in $lastTemplateData, with exactly six elements:
	 *		'template_name'
	 *		'email_overrides' - any override information (often an empty array)
	 *		'email_header' - any header information (usually loaded from the default)
	 *		'email_body'
	 *		'email_footer'
	 *		'email_plainText' - optional template for plain text part of email
	 */
	public function loadTemplateInfo($templateName, $extraFile = FALSE)
	{
		static $requiredFields = array ('email_overrides', 'email_header', 'email_body', 'email_footer', 'email_plainText');
		
		if (is_array($this->lastTemplateData))
		{
			if ($this->lastTemplateData['template_name'] == $templateName)
			{
				return $this->lastTemplateData;
			}
			$this->lastTemplateData = FALSE;		// Obviously a new template
		}

		$ret = array('email_overrides' => '', 'email_header' => '', 'email_body' => '', 'email_footer' => '', 'email_plainText' => '');
		if (!in_array($templateName, array('textonly', 'texthtml', 'texttheme')))
		{
			$found = 0;		// Count number of field definitions we've found
			
			$fileList = array(THEME.'templates/email_template.php');
			if ($extraFile)
			{
				$fileList[] = $extraFile;
			}
			$fileList[] = e_CORE.'templates/email_template.php';
			foreach ($fileList as $templateFileName )		// Override file, optional plugin file then defaults
			{

				if (($found < count($requiredFields)) && is_readable($templateFileName))
				{
					require_once($templateFileName);

					//$tVars = get_defined_vars();
					//if (isset($tVars['GLOBALS'])) unset($tVars['GLOBALS']);
					//print_a($tVars);

					if (isset($$templateName))
					{
						if (is_array($$templateName))
						{
							foreach ($requiredFields as $k)
							{
								if (!$ret[$k] && isset(${$templateName}[$k]))
								{
									$ret[$k] = ${$templateName}[$k];
									$found++;
								}
							}
						}
						else
						{
							$ret['email_body'] = $$templateName;		// Non-array just defines body of email
							$found++;
						}
					}
				}
			}

			// Now fill in the gaps from the defaults
			if ($found < count($requiredFields))
			{
				foreach ($requiredFields as $k)
				{
					$override = strtoupper($k);
					if (!$ret[$k] && isset($$override))
					{
						$ret[$k] = $$override;
						$found++;
					}
				}
			}
			if (($found == 0) || !$ret['email_body'])		// Pointless if we haven't defined a body
			{
				return FALSE;
			}
		}

		$this->lastTemplateData = $ret;
		$this->lastTemplateData['template_name'] = $templateName;		// Cache template
		return $this->lastTemplateData;		// Return this rather than $ret, so return is consistent with cached data
	}



	/**
	 * 	Creates email body text according to options, using the cached template information.
	 *	Caches body, and potentially alternate body
	 *
	 * 	@param $text string - text to process
	 * 	@param boolean $incImages - valid only with HTML and templated output: 
	 *					if true any 'absolute' format images are embedded in the source of the email.
	 *					if FALSE, absolute links are converted to URLs on the local server		
	 *
	 *	@return boolean TRUE for success, FALSE on error (no template defined)
	 */
	public function makeEmailBody($text, $incImages = TRUE)
	{
		if (!is_array( $this->lastTemplateData)) return FALSE;
		if (!isset($this->lastTemplateData['template_name'])) return FALSE;

		$tp = e107::getParser();
		
		//	textonly - generate plain text email
		//	texthtml - HTML format email, no theme info
		//	texttheme - HTML format email, including current theme stylesheet etc
		$format = $this->lastTemplateData['template_name'];
		if (!$format)
		{
			echo 'No format specified!';
			return FALSE;
		}

		if ($format == 'textonly')
		{	// Plain text email - strip bbcodes etc
			$temp = $tp->toHTML($text, TRUE, 'E_BODY_PLAIN');		// Decode bbcodes into HTML, plain text as far as possible etc
			$temp = stripslashes(strip_tags($temp));							// Have to do strip_tags() again in case bbcode added some
			$this->mainBodyText = $temp;
			$this->altBodyText = '';
			return TRUE;
		}

		$consts = $incImages ? ',consts_abs' : 'consts_full';			// If inline images, absolute constants so we can change them

		if (($format != 'texthtml') && ($format != 'texttheme'))
		{	// Specific theme - loaded already
			$mailHeader = $tp->parseTemplate($this->lastTemplateData['email_header'], TRUE);
			$mailBody = $tp->parseTemplate(str_replace('{BODY}', $text, $this->lastTemplateData['email_body']), TRUE);
			$mailFooter = $tp->parseTemplate($this->lastTemplateData['email_footer'], TRUE);

			$mailBody = $mailHeader.$mailBody.$mailFooter;
		}


		if (($format == 'texthtml') || ($format == 'texttheme'))
		{
			// HTML format email here, using hard-coded defaults
			$mailHeader = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">\n";
			$mailHeader .= "<html xmlns='http://www.w3.org/1999/xhtml' >\n";
			$mailHeader .= "<head><meta http-equiv='content-type' content='text/html; charset=utf-8' />\n";
			if ($format == 'texttheme') 
			{
				$styleFile = THEME.'emailstyle.css';
				if (!is_readable($styleFile)) { $styleFile = THEME."/style.css"; }
				$style_css = file_get_contents($styleFile);
				$mailHeader .= "<style>\n".$style_css."\n</style>";
			}
			$mailHeader .= "</head>\n";


			$mailBody = $mailHeader."<body>\n";
			if ($format == 'texttheme') 
			{
				$mailBody .= "<div style='padding:10px;width:97%'><div class='forumheader3'>\n";
				$mailBody .= $tp->toHTML($text, TRUE, 'E_BODY'.$consts)."</div></div></body></html>";
			}
			else
			{
				$mailBody .= $tp->toHTML($text, TRUE, 'E_BODY'.$consts)."</body></html>";
				$mailBody = str_replace("&quot;", '"', $mailBody);
			}

			$mailBody = stripslashes($mailBody);
		}


		if (!$incImages)
		{
			// Handle internally generated 'absolute' links - they need the full URL
			$mailBody = str_replace("src='".e_HTTP, "src='".SITEURL, $mailBody);
			$mailBody = str_replace('src="'.e_HTTP, 'src="'.SITEURL, $mailBody);
			$mailBody = str_replace("href='".e_HTTP, "src='".SITEURL, $mailBody);
			$mailBody = str_replace('href="'.e_HTTP, 'src="'.SITEURL, $mailBody);
		}

//		print_a($mailBody);
		$this->mainBodyText = $mailBody;
		$this->altBodyText = '';
		if ($this->lastTemplateData['email_plainText'])
		{
			$this->altBodyText = $tp->parseTemplate(str_replace('{BODY}', $text, $this->lastTemplateData['email_plainText']), TRUE);
		}
		return TRUE;
	}
}

?>
