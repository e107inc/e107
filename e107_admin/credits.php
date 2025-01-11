<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Credits
 *
*/

require_once(__DIR__."/../class2.php");

$css = "body 				{ text-align: left; font-size:13px; line-height:1.5em; font-weight:normal; font-family:Arial, Helvetica, sans-serif; }
			p 					{ margin:0px 5px 10px 5px; }
		
			a					{ color:#F6931E; text-decoration:none; }
			a:hover				{ color:#fdce8a; text-decoration:none; }
			.bold				{ font-weight:bold; }
			.center				{ text-align:center; }
			.wrapper			{ width:600px;  margin:0px auto 0px 0px; padding-bottom:10px;  }
			.wrapper-middle		{ min-height:389px;  }
			.wrapper-text		{  }
			.logo				{ margin-bottom:20px }
			.credits-content	{ padding:20px 40px;}
        	.copyright			{  margin-top:30px}
      ";

e107::css('inline',$css);


require_once(e_ADMIN."auth.php");

require_once(__DIR__."/../credits.php");

require_once(e_ADMIN."footer.php");
