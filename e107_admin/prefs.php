<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Administration - Site Preferences
 *
 */

if(!empty($_POST) && !isset($_POST['e-token']))
{
	$_POST['e-token'] = '';
}
require_once (__DIR__."/../class2.php");

if(isset($_POST['newver']))
{
	e107::redirect("https://e107.org/index.php");
	exit();
}

if(!getperms("1"))
{
	e107::redirect('admin');
	exit();
}

e107::coreLan('prefs', true);

$e_sub_cat = 'prefs';
//e107::lan('core','mailout','admin');
e107::coreLan('mailout', true);


require_once (e_ADMIN."auth.php");

$e_userclass = e107::getUserClass(); 
require_once(e_HANDLER.'mailout_admin_class.php');		// Admin tasks handler
$ue = e107::getUserExt();
$core_pref = e107::getConfig();

if(!$core_pref->get('timezone'))
{
	$core_pref->set('timezone', 'UTC');
}

$frm = e107::getForm(false, true); //enable inner tabindex counter
$mes = e107::getMessage();
$tp = e107::getParser();

/*	RESET DISPLAY NAMES	*/
if(isset($_POST['submit_resetdisplaynames']))
{
	e107::getDb()->update('user', 'user_name=user_loginname');
	$mes->addInfo(PRFLAN_157);
}

//echo '<pre>';
//var_dump($core_pref->getPref());
//echo '</pre>';

if (isset($_POST['testemail'])) 
{
	sendTest();
}
		
	




/*	UPDATE PREFERENCES */
if(isset($_POST['updateprefs']))
{
	
	
	
	unset($_POST['updateprefs'], $_POST['sitelanguage']);
	


	$_POST['cookie_name'] = str_replace(array(" ", "."), "_", $_POST['cookie_name']);
	$_POST['cookie_name'] = preg_replace("#[^a-zA-Z0-9_]#", "", $_POST['cookie_name']);

	$_POST['siteurl'] = trim($_POST['siteurl']) ? trim($_POST['siteurl']) : SITEURL;
	$_POST['siteurl'] = substr($_POST['siteurl'], - 1) == "/" ? $_POST['siteurl'] : $_POST['siteurl']."/";

	// If email verification or Email/Password Login Method - email address is required!
	if (($_POST['user_reg_veri'] == 1 || $_POST['allowEmailLogin'] == 1) && $_POST['disable_emailcheck'])
	{
		$_POST['disable_emailcheck'] = 0;
		$mes->addError(PRFLAN_211);
    }


	if(!empty($_POST['passwordEncoding']) || !empty($_POST['ssl_enabled']))
	{
		$_POST['password_CHAP'] = 0; // disable chap unless using md5 without SSL.
	}

	// Table of range checking values - min and max for numerics. Only do the important ones
	$pref_limits = array('loginname_maxlength' => array('min' => 10, 'max' => 100, 'default' => 30),
					'displayname_maxlength' => array('min' => 5, 'max' => 100, 'default' => 15),
					'antiflood_timeout' => array('min' => 3, 'max' => 300, 'default' => 10),
					'signup_pass_len' => array('min' => 2, 'max' => 100, 'default' => 4)
					);

	$pref['post_html'] = intval($_POST['post_html']);			// This ensures the setting is reflected in set text


	$smtp_opts = array();

	if(!empty($_POST['smtp_options']))
	{

		switch (trim($_POST['smtp_options']))
		{
			case 'smtp_ssl' :
				$smtp_opts[] = 'secure=SSL';
				break;
			case 'smtp_tls' :
				$smtp_opts[] = 'secure=TLS';
				break;
			case 'smtp_pop3auth' :
				$smtp_opts[] = 'pop3auth';
				break;
		}

		if (!empty($_POST['smtp_keepalive']))
		{
			$smtp_opts[] = 'keepalive';
		}

		if (!empty($_POST['smtp_useVERP']))
		{
			$smtp_opts[] = 'useVERP';
		}

		$_POST['smtp_options'] = implode(',',$smtp_opts);

		unset($_POST['smtp_keepalive'],$_POST['smtp_useVERP']);

		// e107::getMessage()->addDebug(print_a($_POST['smtp_options'],true));
	}




	
	$_POST['membersonly_exceptions'] = explode("\n",$_POST['membersonly_exceptions']);

	// FIXME - automate - pref model & validation handler
	$prefChanges = array();
	$sessionRegenerate = false;
	foreach($_POST as $key => $value)
	{
		if(isset($pref_limits[$key]))
		{ // Its a numeric value to check
			if(is_numeric($value))
			{
				if($value < $pref_limits[$key]['min'])
				{
					$value = $pref_limits[$key]['min'];
					$mes->addWarning(str_replace(array('[x]','[y]'),array($key,$value),PRFLAN_213));
				}
				if($value > $pref_limits[$key]['max'])
				{
					$value = $pref_limits[$key]['max'];
					$mes->addWarning(str_replace(array('[x]','[y]'),array($key,$value),PRFLAN_212));
				}
			}
			else
			{
				$value = $pref_limits[$key]['default'];
			}
			$newValue = $value;
		}
		elseif('cookie_name' == $key && $core_pref->get($key) != $value)
		{
			// special case
			if(!preg_match('/^[\w\-]+$/', $value))
			{
				$newValue = e_COOKIE;
				$mes->addWarning(PRFLAN_219);
			}
			else 
			{
				$newValue = $value;
				$sessionRegenerate = true;
			}
		}
		else
		{
			$newValue = $tp->toDB($value);
		}
		
		$core_pref->update($key, $newValue);
	}


	$hasChanged = $core_pref->dataHasChanged();

	if($hasChanged)
	{
		// Need to clear cache in order to refresh library information.
		e107::getCache()->clearAll('system');


	}

	$core_pref->save(false);

	// special case, do session cleanup, logout, redirect to login screen
	if($sessionRegenerate)
	{
		// reset cookie
		cookie($core_pref->get('cookie_name'), $_COOKIE[e_COOKIE], (time() + 3600 * 24 * 30), e_HTTP, e107::getLanguage()->getCookieDomain());
		cookie(e_COOKIE, null, null);
		
		// regenerate session
		$s = $_SESSION;
		e107::getSession()->destroy();
		$session = new e_core_session(array('name' => $core_pref->get('cookie_name')));
		$_SESSION = $s;
	}

	// build favicon files.
	if(!empty($pref['sitebutton']) && $hasChanged)
	{
		$iconSizes = [32,16,48,192,167,180];
		$media = e107::getMedia();
		foreach($iconSizes as $size)
		{
			if($result = $media->resizeImage($pref['sitebutton'],'{e_MEDIA_ICON}/favicon.png', ['w'=>$size, 'h'=>$size, 'crop'=>true, 'overwrite'=>true]))
			{
				e107::getMessage()->addDebug("Created ".$result); // NO LAN. 
			}

		}
	}

}

if (e107::isInstalled('alt_auth'))
{
	$authlist[] = "e107";
	$handle = opendir(e_PLUGIN."alt_auth");
	while($file = readdir($handle))
	{
		if(preg_match("/^(.*)_auth\.php/", $file, $match))
		{
			$authlist[] = $match[1];
		}
	}
}



function sendTest()
{
	$log = e107::getLog();
	$mes = e107::getMessage();
	
	if(trim($_POST['testaddress']) == '')
	{
		$mes->add(LAN_MAILOUT_19, E_MESSAGE_ERROR);
		$subAction = 'error';
	}
	else
	{
		$mailheader_e107id = USERID;
		$pref = e107::pref();

		$add = ($pref['mailer']) ? " (".strtoupper($pref['mailer']).") " : ' (PHP)';

		if($pref['mailer'] == 'smtp')
		{
			$add .= "Port: ".varset($pref['smtp_port'],25);
			$add .= " - ".str_replace("secure=", "", $pref['smtp_options']);
		}


		$sendto = trim($_POST['testaddress']);
		
		
		$eml = array(); 
		
		$eml['email_subject']		= LAN_MAILOUT_113." ".$add;
		$eml['email_sender_email']	= null; 
		$eml['email_sender_name']	= null;
		$eml['email_replyto']		= null;
		$eml['email_replytonames']	= null; 
		$eml['send_html']			= true; 
		$eml['add_html_header'] 	= null; 
		$eml['email_body']			= str_replace("[br]", "<br>", LAN_MAILOUT_114);
		$eml['email_attach']		= null;
		$eml['template']			= 'default';
		$eml['e107_header']			= USERID;

		if (!e107::getEmail()->sendEmail($sendto, LAN_MAILOUT_189, $eml)) 
		{
			$mes->addError(($pref['mailer'] == 'smtp')  ? LAN_MAILOUT_67 : LAN_MAILOUT_106);
		} 
	//	if (!sendemail($sendto, LAN_MAILOUT_113." ".SITENAME.$add, str_replace("[br]", "\n", LAN_MAILOUT_114),LAN_MAILOUT_189)) 
	//	{
	//		$mes->addError(($pref['mailer'] == 'smtp')  ? LAN_MAILOUT_67 : LAN_MAILOUT_106);
	//	} 
		else 
		{
			$mes->addSuccess(LAN_MAILOUT_81. ' ('.$sendto.')');
			$log->add('MAIL_01',$sendto);
		}
	}

}

/*
if(e_QUERY == "u")
{
	$ns->tablerender("", "<div style='text-align:center'><b>".PRFLAN_106."</b></div>");
}
*/
$handle = opendir(e_ADMIN.'includes/');
while($file = readdir($handle))
{
	if($file != "." && $file != "..")
	{
		$file = str_replace(".php", "", $file);
		$adminlist[] = $file;
	}
}
closedir($handle);

$pref['membersonly_exceptions'] = implode("\n",$pref['membersonly_exceptions']);

$text = "
<div id='core-prefs'>
	<form method='post' action='".e_SELF."' autocomplete='off'>
	<input type='hidden' name='e-token' value='".defset('e_TOKEN')."' />
		<fieldset id='core-prefs-main'>
			<h4 class='caption'>".PRFLAN_53.SEP.PRFLAN_1."</h4>
			<table class='table adminform'>
				<colgroup>
					<col class='col-label' />
					<col class='col-control' />
				</colgroup>
				<tbody>
					<tr>
						<td><label for='sitename'>".PRFLAN_2."</label></td>
						<td>
							".$frm->text('sitename', $pref['sitename'], 100, 'required=1&size=xxlarge')."
						</td>
					</tr>
					<tr>
						<td><label for='siteurl'>".PRFLAN_3."</label>
						".($pref['siteurl'] == SITEURL ? "" : $frm->help(PRFLAN_159.": <strong>".SITEURL."</strong>"))."</td>
						<td>
							".$frm->text('siteurl', $pref['siteurl'], 150, 'size=xxlarge')."
						</td>
					</tr>
					<tr>
						<td><label for='redirectsiteurl'>".PRFLAN_134."</label>".$frm->help(PRFLAN_135)."</td>
						<td>";
						/*
							".$frm->radio('redirectsiteurl', 1, $pref['redirectsiteurl'], array('label'=>LAN_ENABLED))." 
							".$frm->radio('redirectsiteurl', 0, !$pref['redirectsiteurl'], array('label'=>LAN_DISABLED))."
						*/
						$text .= $frm->radio_switch('redirectsiteurl', $pref['redirectsiteurl'])."
						</td>
					</tr>
					<tr>
						<td><label for='sitebutton'>".PRFLAN_4."</label>".$frm->help(PRFLAN_225)."</td>
						<td>
";
/*
$parms = "name=sitebutton";
$parms .= "&path=".e_THEME.$pref['sitetheme']."/images/|".e_IMAGE;
$parms .= "&filter=0";
$parms .= "&fullpath=1";
$parms .= "&default=".urlencode($pref['sitebutton']);
//$parms .= "&width=128px";
//$parms .= "&height=128px";
$parms .= "&multiple=FALSE";
$parms .= "&label=-- No Image --";
$parms .= "&subdirs=1";
$parms .= "&tabindex=".$frm->getNext();


$text .= "<div class='field-section'>".$tp->parseTemplate("{IMAGESELECTOR={$parms}&scaction=select}")."</div>";
// $text .= "<div class='field-section'>".$frm->imagepicker('sitebutton',$pref['sitebutton'],'-- No Image --')."</div>";

//TODO make the preview update when image-picker is used.
$text .= "<div class='field-spacer'>".$tp->parseTemplate("{IMAGESELECTOR={$parms}&scaction=preview}")."</div>";

$sLogo = siteinfo_shortcodes::sc_logo();
*/

if(!empty($pref['sitebutton']) && strpos($pref['sitebutton'],'{')===false && file_exists(e_IMAGE.$pref['sitebutton']))
{
	$pref['sitebutton'] = '{e_IMAGE}'.$pref['sitebutton'];
}



$text .= $frm->imagepicker('sitebutton',$pref['sitebutton'],'','w=200&help='.PRFLAN_225); //todo  use 'LegacyPath' option instead of code above.

$text .= "
						</td>
					</tr>
					<tr>
						<td><label for='sitelogo'>".PRFLAN_214."</label>".$frm->help(PRFLAN_226)."</td>
						<td>".$frm->imagepicker('sitelogo',$pref['sitelogo'],'','w=200&help='.PRFLAN_226)."</td>
					</tr>
					<tr>
						<td><label for='sitetag'>".PRFLAN_5."</label>".$frm->help(PRFLAN_227)."</td>
						<td>
							".$frm->textarea('sitetag', $tp->toForm($pref['sitetag']), 3, 59, array('size'=>'xxlarge'))."
						</td>
					</tr>
					<tr>
						<td><label for='sitedescription'>".PRFLAN_6."</label>".$frm->help(PRFLAN_228)."</td>
						<td>
							".$frm->textarea('sitedescription', $tp->toForm($pref['sitedescription']), 3, 80, array('size'=>'xxlarge'))."
						</td>
					</tr>
					
					<tr>
						<td><label for='sitedisclaimer'>".PRFLAN_9."</label>".$frm->help(PRFLAN_229)."</td>
						<td>
							".$frm->textarea('sitedisclaimer',$tp->toForm( $pref['sitedisclaimer']), 3, 80, array('size'=>'xxlarge'))."
						</td>
					</tr>
				</tbody>
			</table>
			".pref_submit('main')."
		</fieldset>
";


// Email and Contact Information --------------

$text .= "<fieldset class='e-hideme' id='core-prefs-email'>
			<h4 class='caption'>".PRFLAN_53.SEP.PRFLAN_13."</h4>
			<table class='table adminform'>
				<colgroup>
					<col class='col-label' />
					<col class='col-control' />
				</colgroup>
				<tbody>
				<tr>
					<td><label for='siteadmin'>".PRFLAN_7."</label></td>
					<td>
						".$frm->text('siteadmin', SITEADMIN, 100, array('size'=>'xlarge'))."
					</td>
					</tr>
					<tr>
						<td><label for='siteadminemail'>".PRFLAN_8."</label></td>
						<td>
							".$frm->text('siteadminemail', SITEADMINEMAIL, 100, array('size'=>'xlarge'))."
						</td>
					</tr>
					<tr>
						<td><label for='replyto-name'>".PRFLAN_174."</label>".$frm->help(PRFLAN_175)."</td>
						<td>
							".$frm->text('replyto_name', $pref['replyto_name'], 100, array('size'=>'xlarge'))."
						</td>
					</tr>
					<tr>
						<td><label for='replyto-email'>".PRFLAN_176."</label>".$frm->help(PRFLAN_177)."</td>
						<td>
							".$frm->text('replyto_email', $pref['replyto_email'], 100, array('size'=>'xlarge'))."
						</td>
					</tr>
							
							
					<tr>
						<td><label for='testaddress'>".LAN_MAILOUT_110."</label><br /></td>
						<td class='form-inline'>".$frm->admin_button('testemail', LAN_MAILOUT_112,'other')."&nbsp;
							<input name='testaddress' id='testaddress' class='tbox form-control input-xxlarge' placeholder='user@yoursite.com' type='text' size='40' maxlength='80' value=\"".(varset($_POST['testaddress']) ? $_POST['testaddress'] : USEREMAIL)."\" />
						</td>
					</tr>
		
					<tr>
						<td style='vertical-align:top'><label for='mailer'>".PRFLAN_267."</label><br /></td>
						<td>";


				$text .= mailoutAdminClass::mailerPrefsTable($pref);


				$text .="</td>
				</tr>
			
			
				<tr>
					<td><label for='mail-sendstyle'>".LAN_MAILOUT_222."</label></td>
					<td>";
					
				$emFormat = array(
					'textonly' => LAN_MAILOUT_125,
					'texthtml' => LAN_MAILOUT_126,
					'texttheme' => LAN_MAILOUT_127
				);	
				$text .= $frm->select('mail_sendstyle', $emFormat, vartrue($pref['mail_sendstyle'])); 
				$text .= "
					</td>
				</tr>
					

					<tr>
						<td><label for='sitecontactinfo'>".PRFLAN_162."</label>".$frm->help(PRFLAN_163)."</td>
						<td>";

						if(!empty($pref['sitecontactinfo']))
						{
							$text .= $frm->textarea('sitecontactinfo', $pref['sitecontactinfo'], 6, 59, array('size'=>'xxlarge'));
						}
						else
						{
							$text .= "<table class='table table-bordered table-striped' style='margin:0; max-width:690px'>
								<colgroup>
								<col style='width:150px' />
								<col />
								</colgroup>
								<tr>";

							$text .= "<td>Organization</td>
									<td>".$frm->text('contact_info[organization]', varset($pref['contact_info']['organization']), 200, ['size'=>'block-level', 'placeholder'=>'eg. My Company Inc.'])."</td>
									</tr>";

								$text .= "<td>Address</td>
									<td>".$frm->textarea('contact_info[address]', varset($pref['contact_info']['address']), 2, 80, ['size'=>'block-level', 'placeholder'=>"eg. 123 Address St.\nCity, State, Zip/postal\nCountry"])."</td>
									</tr>";



							$contactFields = [
								'phone1'  => ['label'=>'Phone', 'placeholder'=>'+1-555-555-5555'],
								'phone2'  => ['label'=>'Phone', 'placeholder'=>'+1-444-444-4444'],
								'phone3'  => ['label'=>'Phone', 'placeholder'=>'+1-333-333-3333'],
								'fax'     => ['label'=>'Fax', 'placeholder'=>'+1-555-555-5555'],
								'email1'  => ['label'=>LAN_EMAIL, 'placeholder'=>'info@myorganization.com'],
								'email2'  => ['label'=>LAN_EMAIL, 'placeholder'=>'sales@myorganization.com'],
								'coordinates'  => ['label'=>"Map Coordinates", 'placeholder'=>'eg. 36.169941,-115.139832 or leave blank to use address'],
							];

							foreach($contactFields as $type => $var)
							{
								$text .= "<td>".$var['label']."</td>
									<td>".$frm->text('contact_info['.$type.']', varset($pref['contact_info'][$type]), 200, ['size'=>'block-level', 'placeholder'=>varset($var['placeholder'])])."</td>
									</tr>
									";
							}

							$text .= "<td>Hours</td>
									<td>".$frm->textarea('contact_info[hours]', varset($pref['contact_info']['hours']), 2, 80, ['size'=>'block-level', 'placeholder'=>"eg. Mon - Fri (9am-5pm)\nSat (closed)\nSun (closed)"])."</td>
									</tr>";

							$text .= "<td>Custom message</td>
									<td>".$frm->textarea('contact_info[message]', varset($pref['contact_info']['message']), 2, 80, ['size'=>'block-level', 'placeholder'=>"(Optional) Custom text message."])."</td>
									</tr>";

							$text .= "</table>";

						}

						$text .= "
						</td>
					</tr>
					<tr>
						<td><label for='sitecontacts'>".PRFLAN_168."</label>".$frm->help(PRFLAN_169)."</td>
						<td>
							".$e_userclass->uc_dropdown('sitecontacts', $pref['sitecontacts'], 'nobody,main,admin,classes', "tabindex='".$frm->getNext()."'")."
						</td>
					</tr>
					<tr>
						<td><label for='contact_visibility'>".PRFLAN_258."</label>".$frm->help(PRFLAN_274)."</td>
						<td>
							".$e_userclass->uc_dropdown('contact_visibility', varset( $pref['contact_visibility'],e_UC_PUBLIC), null, "tabindex='".$frm->getNext()."'")."
						</td>
					</tr>

					<tr>
						<td><label for='contact-filter'>".PRFLAN_270."</label>".$frm->help(PRFLAN_271)."</td>
						<td>
							".$frm->textarea('contact_filter', $pref['contact_filter'], 5, 59, array('size'=>'xxlarge'))."
						</td>
					</tr>



					<tr>
						<td><label for='contact-emailcopy'>".PRFLAN_164."</label>".$frm->help(PRFLAN_165)."</td>
						<td>";
						/*
							".$frm->radio('contact_emailcopy', 1, $pref['contact_emailcopy'])."
							".$frm->label(LAN_ENABLED, 'contact_emailcopy', 1)."&nbsp;&nbsp;
							".$frm->radio('contact_emailcopy', 0, !$pref['contact_emailcopy'])."
							".$frm->label(LAN_DISABLED, 'contact_emailcopy', 0)."

						*/
					$text .= $frm->radio_switch('contact_emailcopy', $pref['contact_emailcopy'])."



						</td>
					</tr>

						</tbody>
			</table>
			".pref_submit('email')."
		</fieldset>";


// GDPR Settings -----------------------------
$text .= "
		<fieldset class='e-hideme' id='core-prefs-gdpr'>
			<h4 class='caption'>".PRFLAN_53.SEP.PRFLAN_277."</h4>
			<table class='table adminform'>
				<colgroup>
					<col class='col-label' />
					<col class='col-control' />
				</colgroup>
				<tbody>
					<tr>
						<td><label for='gdpr-privacypolicy'>".PRFLAN_278."</label>".$frm->help(PRFLAN_279)."</td>
						<td>
							".$frm->text('gdpr_privacypolicy', $pref['gdpr_privacypolicy'], 200, array('size'=>'xxlarge'))."
						</td>
					</tr>

					<tr>
						<td><label for='gdpr-termsandconditions'>".PRFLAN_280."</label>".$frm->help(PRFLAN_279)."</td>
						<td>
							".$frm->text('gdpr_termsandconditions', $pref['gdpr_termsandconditions'], 200, array('size'=>'xxlarge'))."
						</td>
					</tr>

					<tr>
						<td><label>".LAN_DESCRIPTION."</label>".$frm->help(nl2br(PRFLAN_281))."</td>
						<td>
							</td>
					</tr>
				</tbody>
			</table>
			".pref_submit('display')."
		</fieldset>
";



$text .= "
		<fieldset class='e-hideme' id='core-prefs-display'>
			<h4 class='caption'>".PRFLAN_53.SEP.PRFLAN_13."</h4>
			<table class='table adminform'>
				<colgroup>
					<col class='col-label' />
					<col class='col-control' />
				</colgroup>
				<tbody>
					<tr>
						<td><label for='displaythemeinfo'>".PRFLAN_14."</label></td>
						<td>
							".$frm->radio_switch('displaythemeinfo', $pref['displaythemeinfo'])."
						</td>
					</tr>
					<tr>
						<td><label for='displayrendertime'>".PRFLAN_15."</label></td>
						<td>
							".$frm->radio_switch('displayrendertime', $pref['displayrendertime'])."
						</td>
					</tr>
					<tr>
						<td><label for='displaysql'>".PRFLAN_16."</label></td>
						<td>
							".$frm->radio_switch('displaysql', $pref['displaysql'])."
						</td>
					</tr>
	";
if(function_exists("memory_get_usage"))
{
	$text .= "
					<tr>
						<td><label for='display-memory-usage'>".PRFLAN_137."</label></td>
						<td>
							".$frm->radio_switch('display_memory_usage', $pref['display_memory_usage'])."
						</td>
					</tr>
	";
}
$text .= "
				</tbody>
			</table>
			".pref_submit('display')."
		</fieldset>
";


/**
 * @note Admin Display Options.
 */
$text .= "
		<fieldset class='e-hideme' id='core-prefs-admindisp'>
			<h4 class='caption'>".PRFLAN_53.SEP.PRFLAN_77."</h4>
			<table class='table adminform'>
				<colgroup>
					<col class='col-label' />
					<col class='col-control' />
				</colgroup>
				<tbody>
					<tr>
						<td><label for='admin-alerts-ok'>".PRFLAN_95."</label>".$frm->help(PRFLAN_96)."</td>
						<td>
							".$frm->radio_switch('admin_alerts_ok', $pref['admin_alerts_ok'])."
						</td>
					</tr>
					<tr>
						<td><label for='admin-alerts-uniquemenu'>".PRFLAN_97."</label>".$frm->help(PRFLAN_98)."</td>
						<td>
							".$frm->radio_switch('admin_alerts_uniquemenu', $pref['admin_alerts_uniquemenu'])."
						</td>
					</tr>";
					/*<tr>
						<td>".PRFLAN_199.$frm->help(PRFLAN_200)."</td>
						<td>
							".$frm->radio_switch('admin_slidedown_subs', $pref['admin_slidedown_subs'])."
						</td>
					</tr>*/
					$text .= "
					<tr>
						<td><label for='admin-separate-plugins'>".PRFLAN_204."</label>".$frm->help(PRFLAN_205)."</td>
						<td>
							".$frm->radio_switch('admin_separate_plugins', varset($pref['admin_separate_plugins']))."
						</td>
					</tr>
					<tr>
						<td><label for='admin-collapse-sidebar'>".PRFLAN_283."</label></td>
						<td>
							".$frm->radio_switch('admin_navbar_labels', varset($pref['admin_navbar_labels']))."
						</td>
					</tr>
					<tr>
						<td><label for='admin-collapse-sidebar'>".PRFLAN_284."</label></td>
						<td>
							".$frm->radio_switch('admin_collapse_sidebar', varset($pref['admin_collapse_sidebar']))."
						</td>
					</tr>
					<tr>
						<td><label for='admin-collapse-sidebar'>".PRFLAN_285."</label></td>
						<td>
						".$frm->radio_switch('admin_helptip', varset($pref['admin_helptip']))."
						</td>
					</tr>
					
					
					
				</tbody>
			</table>
			".pref_submit('admindisp')."
		</fieldset>

	";

// Date options.
$dateHelp = PRFLAN_25." <a target='_blank' href='http://www.php.net/manual/en/function.strftime.php' rel='external'>".PRFLAN_93."</a>";

$ga = e107::getDate();
$date1 = $ga->convert_date(time(), "short")."<br />".$dateHelp;
$date2 = $ga->convert_date(time(), "long")."<br />".$dateHelp;
$date3 = $ga->convert_date(time(), "forum")."<br />".$dateHelp;
//$core_pref$date4 = e107::getDate()->convert(time(),"input");
$date4 = $tp->toDate(time(),"input");


$text .= "
		<fieldset class='e-hideme' id='core-prefs-date'>
			<h4 class='caption'>".PRFLAN_53.SEP.PRFLAN_21."</h4>
			<table class='table adminform'>
				<colgroup>
					<col class='col-label' />
					<col class='col-control' />
				</colgroup>
				<tbody>
					<tr>
						<td><label for='shortdate'>".PRFLAN_22."</label>".$frm->help(PRFLAN_83.": ".$date1,'before')."</td>
						<td>
							".$frm->text('shortdate', $pref['shortdate'], 50)."
						</td>
					</tr>
					<tr>
						<td><label for='longdate'>".PRFLAN_23."</label>".$frm->help(PRFLAN_83.": ".$date2)."</td>
						<td>
							".$frm->text('longdate', $pref['longdate'], 50)."
						</td>
					</tr>
					<tr>
						<td><label for='forumdate'>".PRFLAN_24."</label>".$frm->help(PRFLAN_83.": ".$date3,'before')."</td>
						<td>
							".$frm->text('forumdate', $pref['forumdate'], 50)."
						</td>
					</tr>";
					
					
					
					$def = strtotime('December 21, 2012 3:45pm');

					$inputdate = e107::getDate()->dateFormats($def);
					$inputtime =  e107::getDate()->timeFormats($def);

					$text .= "
					<tr>
						<td><label for='inputdate'>".PRFLAN_230."</label></td>
						<td class='form-inline'>
							".$frm->select('inputdate',$inputdate, e107::getPref('inputdate'));
							
					$text .= $frm->select('inputtime',$inputtime, e107::getPref('inputtime'));
					
					$text .= "
						</td>
					</tr>";

$timeZones = systemTimeZones();
$timez = new DateTimeZone($pref['timezone']);
$datetime = new DateTime('now', $timez); // Get the current date and time

$text .= "
					<tr>
						<td><label for='timezone'>".PRFLAN_56."</label></td>
						<td class='form-inline'>
							".$frm->select('timezone', $timeZones, vartrue($pref['timezone'], 'UTC'),'size=xlarge')." <span style='padding-left:10px'>".$datetime->format('Y-m-d H:i:s')."</span>
						</td>
					</tr>
				</tbody>
			</table>
			".pref_submit('date')."
		</fieldset>
";


// =========== Registration Preferences. ==================



$elements = array(1=> PRFLAN_259, 2=> PRFLAN_260, 0=>LAN_DISABLED); 


$text .= "
		<fieldset class='e-hideme' id='core-prefs-registration'>
			<h4 class='caption'>".PRFLAN_53.SEP.PRFLAN_28."</h4>
			<table class='table adminform'>
				<colgroup>
					<col class='col-label' />
					<col class='col-control' />
				</colgroup>
				<tbody>
					<tr>
						<td><label for='user-reg'>".PRFLAN_224."</label>".$frm->help(PRFLAN_30)."</td>
						<td>
							".$frm->radio('user_reg', $elements, $pref['user_reg'])."
						</td>
					</tr>";


					$srch = array('[', ']');
					$repl = array("<a href='".e_ADMIN_ABS."notify.php'>", '</a>');

					$PRFLAN_154a = str_replace($srch,$repl, PRFLAN_154a);

					$text .= "
					<tr>
						<td><label for='user-reg-veri'>".PRFLAN_154."</label>".$frm->help($PRFLAN_154a)."</td>
						<td>
							".$frm->select_open('user_reg_veri', array('size'=>'xlarge'));
                            $veri_list = array(PRFLAN_152,PRFLAN_31,PRFLAN_153);

							foreach($veri_list as $v => $v_title)
							{
								$text .= $frm->option($v_title, $v, ($pref['user_reg_veri'] == $v));
							}



					$text .= "</select>
						</td>
					</tr>
					
					 <tr>
						<td><label for='allowemaillogin'>".PRFLAN_184."</label></td>
						<td>".$frm->select_open('allowEmailLogin', array('size'=>'xlarge'));
                     //   $login_list = array(PRFLAN_201,PRFLAN_202,PRFLAN_203);
                        $login_list = array(
	                        2 => PRFLAN_203,
	                        1 => PRFLAN_202,
	                        0 => PRFLAN_201
                        );
                        foreach($login_list as $l => $l_title)
						{
							$text .= $frm->option($l_title, $l, ($pref['allowEmailLogin'] == $l));
						}

					$text .= "
							</select></td>
					</tr>
					";

					/*
					 // Highly problematic.
					$text .= "
					<tr>
						<td><label for='signup-remote-emailcheck'>".PRFLAN_160."</label></td>
						<td>
							".$frm->radio_switch('signup_remote_emailcheck', $pref['signup_remote_emailcheck'])."
						</td>
					</tr>";

					*/
					$membersOnlyRedirectOptions = array( 'login'=>PRFLAN_264, 'splash'=>PRFLAN_265);

					$text .= "

					<tr>
						<td><label for='membersonly-enabled'>".PRFLAN_58."</label>".$frm->help(PRFLAN_59)."</td>
						<td>";

					$text .= $frm->radio_switch('membersonly_enabled', $pref['membersonly_enabled'])."
							<div class='e-expandit-container' >
							<div class='form-group clearfix' style='padding-top:10px'>
							".$frm->help(PRFLAN_266).
							$frm->select('membersonly_redirect',$membersOnlyRedirectOptions,$pref['membersonly_redirect'], array('size'=>'xxlarge'))."
							</div>
							<div class='form-group clearfix'>".$frm->help(PRFLAN_207).
							$frm->textarea('membersonly_exceptions', $pref['membersonly_exceptions'], 3, 1, 'size=xxlarge&placeholder='.PRFLAN_206)."
							</div>

							</div>
						</td>
					</tr>
              
               		<tr>
						<td><label for='autologinpostsignup'>".PRFLAN_197."</label>".$frm->help(PRFLAN_198)."</td>
						<td>
							".$frm->radio_switch('autologinpostsignup', $pref['autologinpostsignup'])."
						</td>
					</tr>

					<tr>
						<td><label for='displayname-maxlength'>".PRFLAN_158.":</label></td>
						<td>
							".$frm->number('displayname_maxlength', $pref['displayname_maxlength'], 3)."
						</td>
					</tr>
					<tr>
						<td><label for='loginname-maxlength'>".PRFLAN_172.":</label></td>
						<td>
							".$frm->number('loginname_maxlength', $pref['loginname_maxlength'], 3)."
						</td>
					</tr>
					<tr>
						<td><label for='signup-pass-len'>".CUSTSIG_16."</label></td>
						<td>
							".$frm->number('signup_pass_len', $pref['signup_pass_len'], 2)."
						</td>
					</tr>

					<tr>
						<td><label for='signup-maxip'>".PRFLAN_136."</label>".$frm->help(PRFLAN_78)."</td>
						<td>
							".$frm->number('signup_maxip', $pref['signup_maxip'], 3)."
						</td>
					</tr>

					
					</tbody>
					

			</table>
			".pref_submit('registration')."
		</fieldset>

	";
	

// Key registration 

	
	

// Signup options ===========================.

$prefOptionPassword = (isset($pref['signup_option_password'])) ? $pref['signup_option_password'] : 2;

$text .= "
		<fieldset class='e-hideme' id='core-prefs-signup'>
			<h4 class='caption'>".PRFLAN_53.SEP.PRFLAN_19."</h4>
			<table class='table adminform'>
				<colgroup>
					<col class='col-label' />
					<col class='col-control' />
				</colgroup>
				<tbody>
				<tr>
						<td>".PRFLAN_261."</td><td><table class='table table-striped table-condensed table-bordered' style='margin-bottom:0px'>
						<colgroup>
					<col class='col-label' />
					<col class='col-control' />
				</colgroup>
				<tr>
							<td><label>Email</label></td>
							<td>
								".$frm->radio('disable_emailcheck', 2, ($pref['disable_emailcheck']==2), array('label' => CUSTSIG_12, 'disabled'=>true))."
								".$frm->radio('disable_emailcheck', 1, (intval($pref['disable_emailcheck']) == 1), array('label' => CUSTSIG_14))."
								".$frm->radio('disable_emailcheck', 0, (intval($pref['disable_emailcheck']) == 0), array('label' => CUSTSIG_15))."

							</td>
						</tr>
						<tr>
							<td><label for='signup-option-password'>".LAN_PASSWORD."</label></td>
							<td>
								".$frm->radio('signup_option_password', 0, !$prefOptionPassword, array('label' => CUSTSIG_12))."
								".$frm->radio('signup_option_password', 1, ($prefOptionPassword == 1), array('label' => CUSTSIG_14, 'disabled'=>true))."
								".$frm->radio('signup_option_password', 2, ($prefOptionPassword == 2), array('label' => CUSTSIG_15))."

							</td>
						</tr>


						";
				
		$signup_option_names = array(
		//	"signup_option_loginname" 	=> "Login Name",

		"signup_option_realname" 		=> CUSTSIG_2,
		"signup_option_email_confirm" 	=> CUSTSIG_21,
		"signup_option_image" 			=> CUSTSIG_7,

		'signup_option_customtitle'		=> CUSTSIG_20,
		'signup_option_hideemail'		=> CUSTSIG_22,
		"signup_option_class" 			=> CUSTSIG_17,
		"signup_option_signature" 		=> CUSTSIG_6,
	);


	foreach($signup_option_names as $value => $key)
	{
		$label_value = str_replace('_', '-', $value);
		$text .= "
						<tr>
							<td><label for='".$label_value."'>".$key."</label></td>
							<td>
								".$frm->radio($value, 0, !$pref[$value], array('label' => CUSTSIG_12))."
								".$frm->radio($value, 1, ($pref[$value] == 1), array('label' => CUSTSIG_14))."
								".$frm->radio($value, 2, ($pref[$value] == 2), array('label' => CUSTSIG_15))."
							</td>
						</tr>
		";
	}			
				
				
				$text .= "


	<tr>
						<td><label for='user-reg-secureveri'>".PRFLAN_262."</label></td>
						<td>
							".$frm->radio_switch('user_reg_secureveri', $pref['user_reg_secureveri'], CUSTSIG_12, CUSTSIG_14)."
						</td>
					</tr>



</table>
						</td></tr>";

					$useCoppaHelp = PRFLAN_46." <a target='_blank' href='https://www.ftc.gov/business-guidance/resources/complying-coppa-frequently-asked-questions' rel='external'>".PRFLAN_94."</a>";

						$text .= "

					<tr>
						<td><label for='use-coppa'>".PRFLAN_45."</label>".$frm->help($useCoppaHelp)."</td>
						<td>
							".$frm->radio_switch('use_coppa', $pref['use_coppa'])."
						</td>
					</tr>";

/*
					<tr>
						<td><label for='disable-emailcheck'>".PRFLAN_167."</label></td>
						<td>
							". $pref['disable_emailcheck']."
						</td>
					</tr>*/

$text .= "
					<tr>
						<td><label for='signup-text'>".PRFLAN_126."</label></td>
						<td>
							".$frm->textarea('signup_text', $pref['signup_text'], 3, 80, array('size'=>'xxlarge'))."
						</td>
					</tr>

					<tr>
						<td><label for='signup-text-after'>".PRFLAN_140."</label></td>
						<td>
							".$frm->textarea('signup_text_after', $pref['signup_text_after'], 3, 80, array('size'=>'xxlarge'))."
						</td>
					</tr>";

					$predefinedloginnameHelp = "<div style='text-align:left'>".PRFLAN_193."<br />".str_replace("[br]","<br /> ",PRFLAN_194)."</div>";
					$text .= "				
					<tr>
						<td><label for='predefinedloginname'>".PRFLAN_192.":</label>".$frm->help($predefinedloginnameHelp,'before')."</td>
						<td>
							".$frm->text('predefinedLoginName', $pref['predefinedLoginName'], 50)."
						</td>
					</tr>



						<tr>
						<td><label for='signup-disallow-text'>".CUSTSIG_18."</label>".$frm->help(CUSTSIG_19)."</td>
						<td>
							".$frm->tags('signup_disallow_text', $pref['signup_disallow_text'], 500)."
						</td>
					</tr>

						<tr>
						<td><label for='displayname_class'>".PRFLAN_155.":</label></td>
						<td class='form-inline'>
							".$e_userclass->uc_dropdown('displayname_class', $pref['displayname_class'], 'nobody,member,admin,classes', "tabindex='".$frm->getNext()."'")."
							".$frm->admin_button('submit_resetdisplaynames', PRFLAN_156, 'delete')."
						</td>
					</tr>

					<tr>
						<td><label for='old_np'>".PRFLAN_124.":</label>".$frm->help(PRFLAN_125)."</td>
						<td>
							".$frm->radio_switch('old_np', $pref['old_np'])."
						</td>
					</tr>


";

/*
					<!--
					<tr>
						<td>".CUSTSIG_13."</td>
						<td>".CUSTSIG_14."</td>
					</tr>
					-->
*/



$text .= "
				</tbody>
			</table>
			".pref_submit('signup')."
		</fieldset>
";

// Custom Fields.


/* text render options */
$savePrefs = false;
if(!isset($pref['post_html']))
{
	$pref['post_html'] = '250';
	$savePrefs = true;
	//save_prefs();
}

// Make sure, the "post_script" setting is set and if not, set it to "No One" (255)
// This should close a possible security hole...
if(!isset($pref['post_script']))
{
	$pref['post_script'] = '255';
	$savePrefs = true;
	//save_prefs();
}
else
{
	// Make sure, that the pref is one of the allowed userclasses
	// Close possible security hole
	if (!array_key_exists($pref['post_script'], $e_userclass->uc_required_class_list('nobody,admin,main,classes,no-excludes', true)))
	{
		$pref['post_script'] = 255; //set to userclass "no one" if the old class isn't part of the list of allowed userclasses
		$savePrefs = true;
	}
}

if ($savePrefs) $core_pref->setPref($pref)->save(false, true);


	$text .= "
		<fieldset class='e-hideme' id='core-prefs-textpost'>
			<h4 class='caption'>".PRFLAN_53.SEP.PRFLAN_286."</h4>
			<table class='table adminform'>
				<colgroup>
					<col class='col-label' />
					<col class='col-control' />
				</colgroup>
				<tbody>
					<tr>
						<td><label for='make-clickable'>".PRFLAN_127.":</label>".$frm->help(PRFLAN_128)."</td>
						<td>
							".$frm->radio_switch('make_clickable', $pref['make_clickable'])."
						</td>
					</tr>";
					

				
				$text .= "
					<tr>
						<td><label for='link-replace'>".PRFLAN_102."?:</label>
						".$frm->help(PRFLAN_103)."
						</td>
						<td>
							
							".$frm->radio_switch('link_replace', $pref['link_replace'])."
							<div class='e-expandit-container'>
							<table class='table table-condensed table-bordered' style='margin:0; width:380px'>
							<tr>
								<td>URLs ".$frm->help(PRFLAN_105)."</td><td>".$frm->text('link_text', $pref['link_text'], 200, 'size=block-level&placeholder='.PRFLAN_104)."</td>
							</tr>
							<tr>
								<td>Emails ".$frm->help(PRFLAN_108)."</td><td>".$frm->text('email_text', $tp->post_toForm($pref['email_text']), 200, 'size=block-level&placeholder='.PRFLAN_107)."</td>
							</tr>
							</table>
														
							
							</div>
						</td>
					</tr>
			
					<tr >
						<td><label for='links-new-window'>".PRFLAN_145."?:</label>".$frm->help(PRFLAN_146)."</td>
						<td>
							".$frm->radio_switch('links_new_window', $pref['links_new_window'])."
					
						</td>
					</tr>
					
					
					<tr>
						<td><label for='profanity-filter'>".PRFLAN_40."</label>".$frm->help(PRFLAN_41)."</td>
						<td>
							".$frm->radio_switch('profanity_filter', $pref['profanity_filter'])."
						</td>
					</tr>

					<tr>
						<td><label for='profanity-replace'>".PRFLAN_42.":</label></td>
						<td>
							".$frm->text('profanity_replace', $pref['profanity_replace'], 20)."
						</td>
					</tr>
					<tr>
						<td><label for='profanity-words'>".PRFLAN_43.":</label>".$frm->help(PRFLAN_44)."</td>
						<td>
							".$frm->tags('profanity_words', $pref['profanity_words'], 250, array('maxItems'=>1000))."
						</td>
					</tr>
					
				
					<tr>
						<td><label for='main-wordwrap'>".PRFLAN_109.":</label>".$frm->help(PRFLAN_110)."</td>
						<td>
							".$frm->number('main_wordwrap', $pref['main_wordwrap'], 3)."
						</td>
					</tr>
					<tr>
						<td><label for='menu-wordwrap'>".PRFLAN_111.":</label>".$frm->help(PRFLAN_110)."</td>
						<td>
							".$frm->number('menu_wordwrap', $pref['menu_wordwrap'], 3)."
						</td>
					</tr>

					<tr>
						<td><label for='post-html'>".PRFLAN_116.":</label>".$frm->help(PRFLAN_117)."</td>
						<td>
							".$e_userclass->uc_dropdown('post_html', $pref['post_html'], 'nobody,public,member,admin,main,classes', "tabindex='".$frm->getNext()."'")."
						</td>
					</tr>

					<tr>
						<td><label for='post-script'>".PRFLAN_215.":</label>".$frm->help(PRFLAN_216)."</td>
						<td>
							".$e_userclass->uc_dropdown('post_script',$pref['post_script'],'nobody,admin,main,classes,no-excludes')."
						</td>
					</tr>
						<tr>
						<td><label for='inline-editing'>".PRFLAN_268.":</label>".$frm->help(PRFLAN_269)."</td>
						<td>
							".$frm->userclass('inline_editing',$pref['inline_editing'],'off','nobody,admin,main,classes,no-excludes')."
						</td>
					</tr>
					<tr>
						<td><label for='filter-script'>".PRFLAN_217.":</label>".$frm->help(PRFLAN_218)."</td>
						<td>
							".$frm->radio_switch('filter_script', varset($pref['filter_script'], 1))."
						</td>
					</tr>
					<tr>
						<td><label for='html-abuse'>".PRFLAN_220.":</label>".$frm->help(PRFLAN_221)."</td>
						<td>
							".$frm->radio_switch('html_abuse', varset($pref['html_abuse'], 1))."
						</td>
					</tr>
					<tr>
						<td><label for='wysiwyg'>".PRFLAN_122.":</label>".$frm->help(PRFLAN_123)."</td>
						<td>
							".$frm->radio_switch('wysiwyg', $pref['wysiwyg'])."
						</td>
					</tr>
					
					
";

if(file_exists(e_PLUGIN."geshi/geshi.php"))
{
	$usegeshiHelp = str_replace("[link]", "http://qbnz.com/highlighter/", PRFLAN_119);
	$text .= "
					<tr>
						<td><label for='usegeshi'>".PRFLAN_118."?:</label>".$frm->help($usegeshiHelp)."</td>
						<td>
							".$frm->radio_switch('useGeshi', $pref['useGeshi'])."
						</td>
					</tr>
					<tr>
						<td><label for='defaultlangeshi'>".PRFLAN_120."?:</label>".$frm->help(PRFLAN_121)."</td>
						<td>
							".$frm->text('defaultLanGeshi', ($pref['defaultLanGeshi'] ? $pref['defaultLanGeshi'] : "php"), 20)."
						</td>
					</tr>
	";
}
$text .= "
				</tbody>
			</table>
			".pref_submit('textpost')."
		</fieldset>
";

function multi_radio($name, $textsVals, $currentval = '')
{
	$ret = '';
	$gap = '';
	foreach($textsVals as $v => $t)
	{
		$sel = ($v == $currentval) ? " checked='checked'" : "";
		$ret .= $gap."<input type='radio' name='{$name}' value='{$v}' {$sel} /> ".$t."";
		$gap = "&nbsp;&nbsp;";
	}
	return $ret;
}

// Security Options. .
$hasGD = extension_loaded("gd");

$text .= "
		<fieldset class='e-hideme' id='core-prefs-security'>
			<h4 class='caption'>".PRFLAN_53.SEP.PRFLAN_47."</h4>
			<table class='table adminform'>
				<colgroup>
					<col class='col-label' />
					<col class='col-control' />
				</colgroup>
				<tbody>";


		$text .="
					<tr>
						<td><label for='ssl-enabled'>".PRFLAN_60."</label>".$frm->help(PRFLAN_61)."</td>

						<td>";

							if(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')     // Only allow if an SSL login has been made.
							{
								$text .= $frm->radio_switch('ssl_enabled', $pref['ssl_enabled']);
							}
							else
							{
								$text .= "<div class='label label-primary e-tip' title=\"".PRFLAN_61."\">".PRFLAN_275."</div>";
							}

						$text .= "
						</td>
					</tr>
			";

	// Secure Image/ Captcha
	$secureImage = array('signcode'=>PRFLAN_76, 'logcode'=>PRFLAN_81, "fpwcode"=>PRFLAN_138,'admincode'=>PRFLAN_222);
	
	foreach($secureImage as $key=>$label)
	{
		
		$label = str_replace($srch,$repl,$label);
		
		$text .= "<tr><td><label for='".$key."'>".$label."</label>".$frm->help(PRFLAN_223)."</td><td>";
		if($hasGD)
		{
			$text .= $frm->radio_switch($key, $pref[$key]);
		}
		else
		{
			$text .= PRFLAN_133;
		}
		
		$text .= "
		</td></tr>\n";
		
	}


/*


					
	$text .= "
					<tr>
						<td>".PRFLAN_81.": </td>
						<td>
";

if($hasGD)
{
	$text .= $frm->radio_switch('logcode', $pref['logcode']);
}
else
{
	$text .= PRFLAN_133;
}
$text .= "
						</td>
					</tr>
					<tr>
						<td>".PRFLAN_138.": </td>
						<td>
";
if($hasGD)
{
	$text .= $frm->radio_switch('fpwcode', $pref['fpwcode']);
}
else
{
	$text .= PRFLAN_133;
}

$text .= "
						</td>
					</tr>";
 * 
 
 */
    $text .= "

					<tr>
						<td><label for='disallowmultilogin'>".PRFLAN_129."</label>".$frm->help(PRFLAN_130)."</td>
						<td>
							".$frm->radio_switch('disallowMultiLogin', $pref['disallowMultiLogin'], LAN_YES, LAN_NO)."
						</td>
					</tr>

					<tr>
						<td><label for='user-tracking-cookie'>".PRFLAN_48."</label></td>
						<td >
							<div class='form-inline'>
							".$frm->radio('user_tracking', array('cookie' => PRFLAN_49, 'session' => PRFLAN_50), varset($pref['user_tracking']))."
						</div></td>
					</tr>
					
				
					<tr>
						<td><label for='cookie-name'>".PRFLAN_55."</label>".$frm->help(PRFLAN_263)."</td>
						<td >".$frm->text('cookie_name', varset($pref['cookie_name']), 20)."
						</td>
					</tr>

					<tr>
						<td><label for='session-lifetime'>".PRFLAN_272."</label>".$frm->help(PRFLAN_273)."</td>
						<td>
							".$frm->number('session_lifetime', varset($pref['session_lifetime']), 86400)."
						</td>
					</tr>
					";

//	if(e_DEVELOPER) // Experimental (translate terms after this check is removed)
    {
           //  $systemSaveMethod = ini_get('session.save_handler');
           //  $saveMethod = (!empty($systemSaveMethod)) ? $systemSaveMethod : 'files';
            // $systemSaveMethod => PRFLAN_276,
                $text .= "
	                <tr>
						<td><label for='session-save-method'>".PRFLAN_282."</label></td>
						<td class='form-inline'>
							".$frm->select('session_save_method', [ 'db'=>'Database', 'files'=>'Files'], varset($pref['session_save_method']))."
							
						</td>
					</tr>
                    ";
    }

    $text .= "
					<tr>
						<td><label for='passwordencoding'>".PRFLAN_188.":</label></td>

							";

						$pwdEncodeOpts = array();

						if(function_exists('password_verify')) // ie. php 5.5 or higher
						{
							$pwdEncodeOpts[3]	 = PRFLAN_276;

						}

						$pwdEncodeOpts[1] = PRFLAN_190;
						$pwdEncodeOpts[0] = PRFLAN_189;

						$text .= (isset($pwdEncodeOpts[3]) && $pref['passwordEncoding']!=3) ? "<td class='has-warning'>" : "<td>";
						$text .= $frm->select('passwordEncoding', $pwdEncodeOpts,  varset($pref['passwordEncoding'], 0));

				//	$text .= $frm->radio_switch('passwordEncoding', varset($pref['passwordEncoding'], 0), PRFLAN_190, PRFLAN_189);

						$text .= "
							<div class='field-help'></div>
						</td>
					</tr>
					<tr>";
					
					$CHAP_list = array(PRFLAN_180, PRFLAN_181, PRFLAN_182);
	
					$text .= "
						<td><label for='password-chap'>".PRFLAN_178."</label>".
						$frm->help(PRFLAN_183."<br />".PRFLAN_179)."</td>
						<td>";

						$CHAPopt = !empty($pref['ssl_enabled']) || !empty($pref['passwordEncoding']) ? array('disabled'=>1) : null;
						$text .=  $frm->select('password_CHAP',$CHAP_list,$pref['password_CHAP'], $CHAPopt );
						//."	".$frm->select_open('password_CHAP');
							
						//TODO - user tracking session name - visible only if Cookie is enabled (JS)

						$text .= "</td>
					</tr>
					
					<tr>
						<td><label for='antiflood1'>".PRFLAN_35."</label></td>
						<td>
							".$frm->radio_switch('antiflood1', $pref['antiflood1'])."
						</td>
					</tr>
					<tr>
						<td><label for='antiflood-timeout'>".PRFLAN_36."</label>".$frm->help(PRFLAN_38)."</td>
						<td>
							".$frm->number('antiflood_timeout', $pref['antiflood_timeout'], 3)."
						</td>
					</tr>
					<tr>
						<td><label for='autoban'>".PRFLAN_37."</label>".$frm->help(PRFLAN_91)."</td>
						<td>
							".$frm->select_open('autoban');

$autoban_list = array(
	PRFLAN_113,
	PRFLAN_144,
	PRFLAN_142,
	PRFLAN_143
);

foreach($autoban_list as $ab => $ab_title)
{
	$sel = ($pref['autoban'] == $ab) ? "selected='selected'" : "";
	$text .= "
								".$frm->option($ab_title, $ab, ($pref['autoban'] == $ab))."
	";
}

$text .= "
							</select>
					
						</td>
					</tr>
					<tr>
						<td><label for='failed-login-limit'>".PRFLAN_231."</label>".$frm->help(PRFLAN_232)."</td>
						<td>
							".$frm->number('failed_login_limit', varset($pref['failed_login_limit'],10), 3, array('max'=>10, 'min'=>0))."
				
						</td>
					</tr>
					<tr>
						<td><label for='adminpwordchange'>".PRFLAN_139."</label></td>
						<td>
							".$frm->radio_switch('adminpwordchange', $pref['adminpwordchange'])."
						</td>
					</tr>
				</tbody>
			</table>
			".pref_submit('security')."
		</fieldset>
";

$text .= "
		<fieldset class='e-hideme' id='core-prefs-comments'>
			<h4 class='caption'>".PRFLAN_53.SEP.PRFLAN_87."</h4>
			<table class='table adminform'>
				<colgroup>
					<col class='col-label' />
					<col class='col-control' />
				</colgroup>
				<tbody>
					<tr>
						<td>".PRFLAN_161.":</td>
						<td>
							".$frm->radio_switch('comments_disabled', $pref['comments_disabled'], LAN_YES, LAN_NO, array('inverse'=>1))."
						</td>
					</tr>
             		<tr>
						<td>".PRFLAN_32."".$frm->help(PRFLAN_33)."</td>
						<td>
							".$frm->radio_switch('anon_post', $pref['anon_post'], LAN_YES, LAN_NO)."
						</td>
					</tr>
					<tr>
						<td>".PRFLAN_89.": </td>
						<td>
							".$frm->radio_switch('comments_icon', $pref['comments_icon'], LAN_YES, LAN_NO)."
						</td>
					</tr>
					<tr>
						<td>".PRFLAN_88.": </td>
						<td>
							".$frm->radio_switch('nested_comments', $pref['nested_comments'], LAN_YES, LAN_NO)."
						</td>
					</tr>
					
					<tr>
						<td>".PRFLAN_90.": </td>
						<td>
							".$frm->radio_switch('allowCommentEdit', $pref['allowCommentEdit'], LAN_YES, LAN_NO)."
						</td>
					</tr>
					
					<tr>
						<td>".PRFLAN_166.": </td>
						<td>
							".$frm->radio_switch('comments_emoticons', $pref['comments_emoticons'], LAN_YES, LAN_NO)."
						</td>
					</tr>

					<tr>
						<td><label>".PRFLAN_233."</label>".$frm->help(PRFLAN_234)."</td>
						<td>
							".
							
							$frm->uc_select('comments_moderate', $pref['comments_moderate'],"nobody,guest,new,bots,public,member,admin,main,classes").
							"
							</td>
					</tr>
					<tr>
						<td>".PRFLAN_235."</td>
						<td>";
						
						$comment_sort = array(
							"desc"	=> PRFLAN_236, //default
							'asc'	=> PRFLAN_237
						);
					
					$text .= $frm->select('comments_sort',$comment_sort, $pref['comments_sort'], array('size'=>'xlarge'))."
						</td>
					</tr>
					
				</tbody>
			</table>

			<h4 class='caption'>".PRFLAN_209."</h4>
			<table class='table adminform'>
				<colgroup>
					<col class='col-label' />
					<col class='col-control' />
				</colgroup>
				<tbody>

					<tr>
						<td>".PRFLAN_208.":</td>
						<td>
							".$e_userclass->uc_dropdown('email_item_class',varset($pref['email_item_class'],e_UC_MEMBER),'nobody,admin,main,public,member,classes', "tabindex='".$frm->getNext()."'")."
						</td>
					</tr>

				</tbody>
			</table>
			".pref_submit('comments')."
		</fieldset>
	";
	
// File Uploads

	e107::includeLan(e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_upload.php");
	require_once(e_HANDLER."upload_handler.php"); 
	
	
	
	
	
	
	$text .= "
	<fieldset class='e-hideme' id='core-prefs-uploads'>
			<h4 class='caption'>".PRFLAN_53.SEP.PRFLAN_238."</h4>";
	
	
	$upload_max_filesize = ini_get('upload_max_filesize');
	$post_max_size = ini_get('post_max_size');
	
	$maxINI = min($upload_max_filesize,$post_max_size); 
	
	if($maxINI < $pref['upload_maxfilesize'])
	{
		$text .= "<div class='alert-block alert alert-danger'>";
		$text .= PRFLAN_239." ".$maxINI."</div>";
		$pref['upload_maxfilesize'] = $maxINI;
	}
	
	
	
			
	$text .= "
			<table class='table adminform'>
				<colgroup>
					<col class='col-label' />
					<col class='col-control' />
				</colgroup>
				<tbody>
	<tr>
	<td><label>".UPLLAN_25."</label>".$frm->help(UPLLAN_26)."</td>
	<td>".
	
	$frm->radio_switch('upload_enabled', $pref['upload_enabled'])
	."</td>
	</tr>

	<tr>
	<td><label>".UPLLAN_33."</label>".$frm->help(UPLLAN_34)."
	</td>
	<td>".
	$frm->text('upload_maxfilesize', $pref['upload_maxfilesize'], 10)
	 ."
	 </td>
	</tr>

	<tr>
	<td><label>".UPLLAN_37."</label>".$frm->help(UPLLAN_38)."</td>
	<td>".r_userclass("upload_class", $pref['upload_class'],"off","nobody,public,guest,member,admin,classes")."
	</td>
	</tr>
	<tr><td>".PRFLAN_240."<sup>**</sup></td>
	<td>";

	$fType = [e_UC_MEMBER => UC_LAN_3, e_UC_ADMIN => UC_LAN_5, e_UC_MAINADMIN => UC_LAN_6 ];
	$tabs = [];

	foreach($fType as $class => $label)
	{
		$tabs[$class] = array('caption' => $label, 'text' => filePermRender($class));
	}

	function filePermRender($class)
	{
		$fl = e107::getFile();
		$text = "<table class='table table-striped table-bordered' style='margin:0'>
		<tr><th>".LAN_TYPE."</th><th>".UPLLAN_33."</th></tr>";

			$data = $fl->getAllowedFileTypes($class);

			if(empty($data))
			{
				$data = $fl->getAllowedFileTypes(e_UC_MEMBER);
			}

			foreach($data as $k=>$v)
			{
				if(empty($k))
				{
					continue;
				}

				$text .= "<tr><td class='col-label'>".$k."</td>
				<td>".$fl->file_size_encode($v)."</td>
				</tr>";
			}

			$text .= "</table>";

			return $text;

	}



	$text .= e107::getForm()->tabs($tabs);
/*
	$text .= "

	<table class='table table-striped table-bordered'>
	<tr><th>".LAN_TYPE."</th><th>".UPLLAN_33."</th>
	";

	$fl = e107::getFile();
	$data = $fl->getAllowedFileTypes();

	foreach($data as $k=>$v)
	{
		$text .= "<tr><td>".$k."</td>
		<td>".$fl->file_size_encode($v)."</td>
		</tr>";	

		
	}
	// $text .= print_a($data,true);
	

	
	$text .= "</table>";
	*/
	$text .= "
	<div style='padding:15px 0'>".PRFLAN_241." <b>".str_replace("../",'',e_SYSTEM).e_READ_FILETYPES."</b></div>
	</td>
	
	
	</tbody>
		</table>
			".pref_submit('uploads');
			
			
			
	$text .= "
		</fieldset>";


$text .= "<fieldset class='e-hideme' id='core-prefs-javascript'>
<h4 class='caption'>".PRFLAN_53.SEP.PRFLAN_257 . "</h4>";

if(E107_DEBUG_LEVEL > 0)
{
	// TODO - remove these old JS settings completely!

	// Javascript Control
	$text .= "
			<h4>".PRFLAN_242 . "</h4>
			<table class='table adminform'>
				<colgroup>
					<col class='col-label' />
					<col class='col-control' />
				</colgroup>
				<tbody>";

	$js_options = array(
		'auto'  => PRFLAN_243, // load based on dependency
		'admin' => PRFLAN_244, // Always load in admin
		'front' => PRFLAN_245, // Always load in front-end
		'all'   => PRFLAN_246, // Always load in admin and front-end
		'none'  => PRFLAN_247  // disabled
	);

	$js_types = array(
		array('id' => 'jquery', 'name' => 'jQuery (local)'),
		array('id' => 'prototype', 'name' => 'Prototype (local)'),
	);

	foreach($js_types as $arr)
	{
		// $k = $arr['path'];
		$k = $arr['id'];
		$name = $arr['name'];
		$text .= "<tr>
				<td>" . $name . "</td>
				<td>" . $frm->radio("e_jslib_core[{$k}]", $js_options, $pref['e_jslib_core'][$k]) . "</td>
				</tr>";
	}

	$text .= "
					</tbody>
			</table>
			<table class='table adminform' style='margin-top: 20px'>
				<colgroup>
					<col class='col-label' />
					<col class='col-control' />
				</colgroup>
				<thead>
					<tr><th colspan='2'><span class='label label-warning'>DEPRECATED</span> Available only in DEBUG mode</th></tr>
				</thead>
				<tbody>
					<tr>
						<td><label>" . PRFLAN_248 . "</label>".$frm->help( PRFLAN_249 )."</td>
						<td>
							" . $frm->radio_switch('e_jslib_nocombine', $pref['e_jslib_nocombine'], LAN_YES, LAN_NO) . "
						</td>
					</tr>
					<tr>
						<td><label>" . PRFLAN_250 ."</label>".$frm->help( PRFLAN_251 )."</td>
						<td>
							" . $frm->radio_switch('e_jslib_gzip', $pref['e_jslib_gzip'], LAN_YES, LAN_NO) . "
						</td>
					</tr>
					<tr>
						<td><label>" . PRFLAN_252 . "</label>".$frm->help( PRFLAN_251 )."</td>
						<td>
							" . $frm->radio_switch('e_jslib_nocache', $pref['e_jslib_nocache'], LAN_YES, LAN_NO) . "
						</td>
					</tr>
					<tr>
						<td><label>" . PRFLAN_253 . "</label>".$frm->help( PRFLAN_251 )."</td>
						<td>
							" . $frm->radio_switch('e_jslib_nobcache', $pref['e_jslib_nobcache'], LAN_YES, LAN_NO) . "
						</td>
					</tr>
		";

	$text .= "</tbody></table>";
}
else
{
	$text .= "<div>";
	$text .= $frm->hidden('e_jslib_core[jquery]', 'all');
	$text .= $frm->hidden('e_jslib_core[prototype]', 'none');
	$text .= $frm->hidden('e_jslib_nocombine', 1);
	$text .= $frm->hidden('e_jslib_nocache', 1);
	$text .= $frm->hidden('e_jslib_nobcache', 1);
	$text .= $frm->hidden('e_jslib_gzip', 0);
	$text .= "</div>";
}

/**
 * @addtogroup CDN settings
 * @{
 */

// [e_LANGUAGEDIR]/[e_LANGUAGE]/lan_library_manager.php
e107::lan('core', 'library_manager');

$CDNproviders = array(
	'jsdelivr' => 'jsDelivr',
	'cdnjs' => 'cdnjs',
);

$text .= '
<h4 class="caption">' . LAN_LIBRARY_MANAGER_30 . '</h4>
<table class="table adminform">
	<colgroup>
		<col class="col-label"/>
		<col class="col-control"/>
	</colgroup>
	<tbody>
		<tr>
			<td>' . LAN_LIBRARY_MANAGER_31 . '</td>
			<td>
				' . $frm->radio("e_jslib_cdn", array(1 => LAN_YES, 0 => LAN_NO), varset($pref['e_jslib_cdn'], 1)) . '
			</td>
		</tr>
		<tr>
			<td>' . LAN_LIBRARY_MANAGER_32 . '</td>
			<td>
				' . $frm->select("e_jslib_cdn_provider", $CDNproviders, varset($pref['e_jslib_cdn_provider'], 'jsdelivr')) . '
			</td>
		</tr>
	</tbody>
</table>
';

// Submit button.
$text .= pref_submit('javascript');

/**
 * @} End of "addtogroup CDN settings".
 */


/**
 * @addtogroup Third-party libraries
 * @{
 */

$text .= '<h4 class="caption">' . LAN_LIBRARY_MANAGER_25 . '</h4>';
$text .= '<table class="table table-striped">';
$text .= '<thead>';
$text .= '<tr>';
$text .= '<th>' . LAN_LIBRARY_MANAGER_13 . '</th>';
$text .= '<th class="text-center">' . LAN_LIBRARY_MANAGER_21 . '</th>';
$text .= '<th>' . LAN_LIBRARY_MANAGER_29 . '</th>';
$text .= '<th class="text-center">' . LAN_VERSION . '</th>';
$text .= '<th class="text-center">' . LAN_STATUS . '</th>';
$text .= '<th>' . LAN_MESSAGE . '</th>';
$text .= '<th>' . LAN_MOREINFO . '</th>';
$text .= '</tr>';
$text .= '</thead>';
$text .= '<tbody>';

$lib = e107::getLibrary();
$libraries = $lib->info();

foreach($libraries as $machineName => $library)
{
	$details = $lib->detect($machineName);

	if(empty($details['name']))
	{
		continue;
	}

	$name = libraryGetName($machineName, $details);
	$provider = libraryGetProvider($details);
	$status = libraryGetStatus($details);
	$links = libraryGetLinks($details);

	$text .= '<tr>';
	$text .= '<td>' . $name . '</td>';
	$text .= '<td class="text-center">' . $provider . '</td>';
	$text .= '<td class="smalltext">' . varset($details['library_path']);
	$text .= !empty($details['path']) ? '/'. $details['path'] : '';
	$text .= '</td>';
	$text .= '<td class="text-center">' . varset($details['version']) . '</td>';
	$text .= '<td class="text-center">' . $status . '</td>';
	$text .= '<td>' . varset($details['error_message']) . '</td>';
	$text .= '<td>' . $links . '</td>';
	$text .= '</tr>';
}

if(empty($libraries))
{
	$text .= '<tr>';
	$text .= '<td colspan="6">' . LAN_NOT_FOUND . '</td>';
	$text .= '</tr>';
}

$text .= '</tbody>';
$text .= '</table>';
$text .= "</fieldset>";

/**
 * @} End of "addtogroup Third-party libraries".
 */


/**
 * @addtogroup Advanced Features
 * @{
 */

$text .= "
		<fieldset class='e-hideme' id='core-prefs-advanced'>
			<h4 class='caption'>".PRFLAN_53.SEP.PRFLAN_149."</h4>
			<table class='table adminform'>
				<colgroup>
					<col class='col-label' />
					<col class='col-control' />
				</colgroup>
				<tbody>
					<tr>
						<td>".PRFLAN_147.":".$frm->help(PRFLAN_148)."</td>
						<td>
							".$frm->radio_switch('developer', $pref['developer'])."
						</td>
					</tr>
					<tr>
						<td>".PRFLAN_196."".$frm->help(PRFLAN_196a." <strong>".e_LOG."</strong>")."</td>
						<td>
						".$frm->radio_switch('log_page_accesses', $pref['log_page_accesses'])."
						</td>
					</tr>
					<tr>
						<td>".PRFLAN_17."</td>
						<td>
							".$frm->radio_switch('compress_output', $pref['compress_output'])."
						</td>
					</tr>
";

$auth_dropdown = '';
if(!empty($authlist))
{
	$auth_dropdown = "\n".$frm->select_open('auth_method')."\n";
	foreach($authlist as $a)
	{
		$auth_dropdown .= $frm->option($a, $a, ($pref['auth_method'] == $a))."\n";
	}
	$auth_dropdown .= "</select>\n";
}
else
{
	$auth_dropdown = "<input type='hidden' name='auth_method' value='' />".PRFLAN_151;
	$pref['auth_method'] = "";
}

$text .= "
					<tr>
						<td>".PRFLAN_150."</td>
						<td>
							{$auth_dropdown}
						</td>
					</tr>
					<tr>
						<td>".PRFLAN_173."</td>
						<td>
							".$frm->radio_switch('check_updates', $pref['check_updates'])."
						</td>
					</tr>
				</tbody>
			</table>
			".pref_submit('advanced')."
		</fieldset>
	";

/**
 * @} End of "addtogroup Advanced Features".
 */


$text .= "
	</form>
</div>
";
//PRFLAN_53
$ns->tablerender(null, $mes->render().$text);

require_once(e_ADMIN."footer.php");

function pref_submit($post_id = '')
{
	global $frm;
	if($post_id) $post_id = '-'.$post_id;
	$text = "
		<div class='buttons-bar center'>";

	// ML
	/* if(e_MLANG == 1){
	//$text .="<input class='fcaption' type='submit' name='updateprefs' value='".PRFLAN_52."' />
	$but_typ = array(""); // empty = submit
	$but_nam = array("updateprefs"); // empty = autobutX with X autoincrement
	$but_val = array("updateprefs"); // empty = Submit
	$but_class = array("caption"); // empty = button
	$butjs = array(""); // empty = ""
	$buttitle = array(""); // empty = ""
	$text .= e107ml_adpanel(1,$but_typ,$but_nam,$but_val,$but_class,$butjs,$buttitle);
	}else{*/
	$text .= $frm->admin_button('updateprefs', PRFLAN_52, 'update', '', "id=updateprefs{$post_id}");
	// }
	$text .= "\n</div>";

	// END ML
	return $text;
}

function prefs_adminmenu()
{
	$var['core-prefs-header1']['header'] = LAN_BASIC_OPTIONS;

	$var['core-prefs-main']['text'] = PRFLAN_1;
	$var['core-prefs-main']['image_src'] = 'fa-home.glyph';

	$var['core-prefs-email']['text'] = PRFLAN_254;
	$var['core-prefs-email']['image_src'] = 'fa-envelope.glyph';

	$var['core-prefs-gdpr']['text'] = PRFLAN_277;
	$var['core-prefs-gdpr']['image_src'] = 'fa-cookie.glyph';

	$var['core-prefs-registration']['text'] = PRFLAN_28;
	$var['core-prefs-registration']['image_src'] = 'fas-user-plus.glyph';

	$var['core-prefs-signup']['text'] = PRFLAN_19;
	$var['core-prefs-signup']['image_src'] = 'fas-clipboard-list.glyph';
	
	$var['core-prefs-comments']['text'] = PRFLAN_210;
	$var['core-prefs-comments']['image_src'] = 'fa-comments.glyph';

	$var['core-prefs-uploads']['text'] = PRFLAN_255;
	$var['core-prefs-uploads']['image_src'] = 'file-upload.glyph';

	$var['core-prefs-header2']['header'] = PRFLAN_256;

	$var['core-prefs-display']['text'] = PRFLAN_13;
	$var['core-prefs-display']['image_src'] = 'fas-info-circle.glyph';

	$var['core-prefs-admindisp']['text'] = PRFLAN_77;
	$var['core-prefs-admindisp']['image_src'] = 'fa-dashboard.glyph';

	$var['core-prefs-textpost']['text'] = PRFLAN_286;
	$var['core-prefs-textpost']['image_src'] = 'fa-filter.glyph';

	$var['core-prefs-security']['text'] = PRFLAN_47;
	$var['core-prefs-security']['image_src'] = 'fas-shield-alt.glyph';

	$var['core-prefs-date']['text'] = PRFLAN_21;
	$var['core-prefs-date']['image_src'] = 'fa-calendar.glyph';

	$var['core-prefs-javascript']['text'] = PRFLAN_257;
	$var['core-prefs-javascript']['image_src'] = 'fab-js.glyph';

	$var['core-prefs-advanced']['text'] = PRFLAN_149;
	$var['core-prefs-advanced']['image_src'] = 'fa-cogs.glyph';

	$caption = "<span>".LAN_PREFS."</span>";

	$var['_extras_']['icon'] = e107::getParser()->toIcon('e-prefs-24');

	e107::getNav()->admin($caption.'--id--prev_nav', 'core-prefs-main', $var);
}

/**
 * @addtogroup Third-party libraries
 * @{
 */

/**
 * Helper function to get library's name.
 */
function libraryGetName($machineName, $details)
{
	$text = e107::getParser()->lanVars(LAN_LIBRARY_MANAGER_27, array($machineName));
	return '<span data-toggle="tooltip" data-bs-toggle="tooltip" data-placement="top" title="' . $text . '">' . $details['name'] . '</span>';
}

/**
 * Helper function to get links.
 */
function libraryGetLinks($details)
{
	$homepage = libraryGetHomepage($details);
	$download = libraryGetDownload($details);

	if ($homepage && $download)
	{
		return $homepage . ' | ' . $download;
	}

	if($homepage)
	{
		return $homepage;
	}

	if($download)
	{
		return $download;
	}
}

/**
 * Helper function to get homepage link.
 */
function libraryGetHomepage($details)
{
	if (empty($details['vendor_url']))
	{
		return false;
	}

	$href = $details['vendor_url'];
	$title = $details['name'];

	return '<a href="' . $href . '" title="' . $title . '" target="_blank">' . LAN_WEBSITE . '</a>';
}

/**
 * Helper function to get download link.
 */
function libraryGetDownload($details)
{
	if (empty($details['download_url']))
	{
		return false;
	}

	$href = $details['download_url'];
	$title = $details['name'];

	return '<a href="' . $href . '" title="' . $title . '" target="_blank">' . LAN_DOWNLOAD . '</a>';
}

/**
 * Helper function to get provider.
 */
function libraryGetProvider($details)
{
	$text = 'e107';
	$provider = LAN_CORE;

	if(varset($details['plugin'], false) == true)
	{
		$text = $details['plugin'];
		$provider = LAN_PLUGIN;
	}

	if(varset($details['theme'], false) == true)
	{
		$text = $details['theme'];
		$provider = LAN_THEME;
	}

	return '<span data-toggle="tooltip" data-bs-toggle="tooltip" data-placement="top" title="' . $text . '">' . $provider . '</span>';
}

/**
 * Helper function to get status.
 */
function libraryGetStatus($details)
{
	$tp = e107::getParser();

	if($details['installed'] == true)
	{
		$icon = $tp->toGlyph('fa-check');
		$text = LAN_OK;
		return '<span class="text-success" data-toggle="tooltip" data-bs-toggle="tooltip" data-placement="top" title="' . $text . '">' . $icon . '</span>';
	}

	$icon = $tp->toGlyph('fa-remove');
	$text = $details['error'];
	return '<span class="text-danger" data-toggle="tooltip" data-bs-toggle="tooltip" data-placement="top" title="' . $text . '">' . $icon . '</span>';
}

/**
 * @} End of "addtogroup Third-party libraries".
 */
