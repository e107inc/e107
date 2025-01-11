<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2020 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */


$_BLANK_WRAPPER['default']['BLANK_TEST'] = "[ {---} ]";
$_BLANK_TEMPLATE['default'] = "<div>{BLANK_TEST}</div>";


$_BLANK_TEMPLATE['other'] = "<div>{BLANK_TEST}</div>";
$_BLANK_TEMPLATE['other'] = "<div>{BLANK_TEST}</div>";

/**
 * Custom Plugin email template 
 * @see https://github.com/e107inc/e107/issues/4919
 */
$_BLANK_TEMPLATE['email']['header'] = '<html lang="en"><body>';
$_BLANK_TEMPLATE['email']['body'] = "<div><span>{NAME}</span> <small>{DATE}</small></div><div>{BODY}</div>";
$_BLANK_TEMPLATE['email']['footer'] = '</body></html>';