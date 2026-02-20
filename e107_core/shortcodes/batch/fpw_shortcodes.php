<?php


if(!defined('e107_INIT'))
{
	exit;
}

e107::coreLan('fpw');

class fpw_shortcodes extends e_shortcode
{

	private $secImg;
	private $imageCode = false;

	function __construct()
	{

		parent::__construct();

		if(deftrue('USE_IMAGECODE'))
		{
			$this->imageCode = true;
			$this->secImg = e107::getSecureImg();
		}
	}

	function sc_fpw_username($parm = null) // used when email login is disabled
	{

		// return "<input class='tbox' type='text' name='username' size='40' value='' maxlength='100' />";
		return e107::getForm()->text('username'); // $frm->userpicker()?
	}

	function sc_fpw_useremail($parm = null)
	{

		// return '<input class="tbox form-control" type="text" name="email" size="40" value="" maxlength="100" placeholder="Email" required="required" type="email">';
		// return "<input class='tbox' type='text' name='email' size='40' value='' maxlength='100' />";
		return e107::getForm()->email('email', '', 200, array('placeholder' => 'Email', 'required' => 'required'));
	}

	function sc_fpw_submit($parm = null)
	{
		$options = array();
		$options['class'] = (!empty($parm['class'])) ? $parm['class'] : "btn submit btn-success";
		
		// return '<button type="submit" name="pwsubmit" class="button btn btn-primary btn-block reset">'.$label.'</button>';
		// return "<input class='button btn btn-primary btn-block' type='submit' name='pwsubmit' value='".$label."' />";
		$label = deftrue('LAN_FPW_102', LAN_SUBMIT);

		//return e107::getForm()->button('pwsubmit', $label);
		return e107::getForm()->button('pwsubmit', $label, 'submit', $label, $options);
	}

	function sc_fpw_captcha_lan($parm = null)
	{

		return LAN_ENTER_CODE;
	}

	function sc_fpw_captcha_hidden($parm = null)
	{

		return; // no longer required - included in renderInput();
	}

	/**
	 * @param string $parm
	 * @return mixed|null|string
	 */
	function sc_fpw_captcha_img($parm = '')
	{

		if($this->imageCode)
		{
			return $this->secImg->renderImage();
		}

		return null;
	}

	/**
	 * @param string $parm
	 * @return string|null
	 */
	function sc_fpw_captcha_input($parm = null)
	{

		if($this->imageCode)
		{
			return $this->secImg->renderInput();
		}

		return null;
	}

	function sc_fpw_logo($parm = '')
	{
		// Unused at the moment.
	}

	function sc_fpw_text($parm = null)
	{
		return deftrue('LAN_FPW_101', "Not to worry. Just enter your email address below and we'll send you an instruction email for recovery.");
	}
}
