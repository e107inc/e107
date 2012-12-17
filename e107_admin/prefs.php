<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2010 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Administration - Site Preferences
 *
 * $URL$
 * $Revision$
 * $Id$
 * $Author$
 */
require_once ("../class2.php");

if(isset($_POST['newver']))
{
	header("location:http://e107.org/index.php");
	exit();
}

if(! getperms("1"))
{
	header("location:".e_BASE."index.php");
	exit();
}

include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_'.e_PAGE);
$e_sub_cat = 'prefs';
e107::lan('core','mailout','admin');

//FIXME temporary fix - need universal solution. 
e107::js('inline',"
$(document).ready(function()
{	
	var hash = document.location.hash;
	if(hash)
	{
		$('.plugin-navigation a').each(function(index) {
    			var ot = $(this).attr('href');
				$(ot).hide();
				$(this).closest('li').removeClass('active');
				$(this).switchClass( 'link-active', 'link', 0 );
			});
		var button = '#eplug-nav-' + hash.replace('#', '') + '-prev-nav';
		$(hash).show();
		$(button).switchClass('link', 'link-active', 30 );
		$(button).closest('li').addClass('active');
	}
	else
	{
		$('#core-prefs-main').show();	
	}
	
	
		
});	
","jquery");


e107::js('inline',"
	function disp(type) 
	{
		if(type == 'smtp')
		{
			$('#smtp').show('slow');
			$('#sendmail').hide('slow');
			return;
		}

		if(type =='sendmail')
		{
            $('#smtp').hide('slow');
			$('#sendmail').show('slow');
			return;
		}

		$('#smtp').hide('slow');
		$('#sendmail').hide('slow');
	}
",'jquery');


require_once (e_ADMIN."auth.php");

$e_userclass = e107::getUserClass(); 
require_once (e_HANDLER."user_extended_class.php");
$ue = new e107_user_extended();
$core_pref = e107::getConfig();

if(!$core_pref->get('timezone'))
{
	$core_pref->set('timezone', 'GMT');
}

$frm = e107::getForm(false, true); //enable inner tabindex counter
$emessage = e107::getMessage();
$tp = e107::getParser();

/*	RESET DISPLAY NAMES	*/
if(isset($_POST['submit_resetdisplaynames']))
{
	e107::getDb()->db_Update('user', 'user_name=user_loginname');
	$emessage->add(PRFLAN_157);
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
		$emessage->add(PRFLAN_211, E_MESSAGE_ERROR);
    }

	// Table of range checking values - min and max for numerics. Only do the important ones
	$pref_limits = array('loginname_maxlength' => array('min' => 10, 'max' => 100, 'default' => 30),
					'displayname_maxlength' => array('min' => 5, 'max' => 100, 'default' => 15),
					'antiflood_timeout' => array('min' => 3, 'max' => 300, 'default' => 10),
					'signup_pass_len' => array('min' => 2, 'max' => 100, 'default' => 4)
					);

	$pref['post_html'] = intval($_POST['post_html']);			// This ensures the setting is reflected in set text
	
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
					$emessage->addWarning(str_replace(array('--FIELD--','--VALUE--'),array($key,$value),PRFLAN_213));
				}
				if($value > $pref_limits[$key]['max'])
				{
					$value = $pref_limits[$key]['max'];
					$emessage->addWarning(str_replace(array('--FIELD--','--VALUE--'),array($key,$value),PRFLAN_212));
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
				$emessage->addWarning(PRFLAN_219);
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
		/*if($newValue != $core_pref->get($key))
		{ // Changed value
			$core_pref->set($key, $newValue);
			$prefChanges[$key] = $newValue;
		}*/
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
}

if (plugInstalled('alt_auth'))
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
	$log = e107::getAdminLog();
	$mes = e107::getMessage();
	
	if(trim($_POST['testaddress']) == '')
	{
		$mes->add(LAN_MAILOUT_19, E_MESSAGE_ERROR);
		$subAction = 'error';
	}
	else
	{
		$mailheader_e107id = USERID;
		require_once(e_HANDLER.'mail.php');
		$add = ($pref['mailer']) ? " (".strtoupper($pref['mailer']).")" : ' (PHP)';
		$sendto = trim($_POST['testaddress']);
		if (!sendemail($sendto, LAN_MAILOUT_113." ".SITENAME.$add, LAN_MAILOUT_114,LAN_MAILOUT_189)) 
		{
			$mes->add(($pref['mailer'] == 'smtp')  ? LAN_MAILOUT_67 : LAN_MAILOUT_106, E_MESSAGE_ERROR);
		} 
		else 
		{
			$mes->add(LAN_MAILOUT_81. ' ('.$sendto.')', E_MESSAGE_SUCCESS);
			$log->log_event('MAIL_01',$sendto,E_LOG_INFORMATIVE,'');
		}
	}

}

/*
if(isset($message))
{
	$ns->tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

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
	<form class='admin-menu' method='post' action='".e_SELF."'>
		<fieldset class='e-hideme' id='core-prefs-main'>
			<legend>".PRFLAN_1."</legend>
			<table class='table adminform'>
				<colgroup>
					<col class='col-label' />
					<col class='col-control' />
				</colgroup>
				<tbody>
					<tr>
						<td>".PRFLAN_2."</td>
						<td>
							".$frm->text('sitename', $pref['sitename'], 100, 'required=1')."
						</td>
					</tr>
					<tr>
						<td>".PRFLAN_3."</td>
						<td>
							".$frm->text('siteurl', $pref['siteurl'], 150)."
							".($pref['siteurl'] == SITEURL ? "" : "<div class='smalltext'>( ".PRFLAN_159.": <strong>".SITEURL."</strong> )</div>")."
						</td>
					</tr>
					<tr>
						<td>".PRFLAN_134."</td>
						<td>
							".$frm->radio('redirectsiteurl', 1, $pref['redirectsiteurl']).$frm->label(LAN_ENABLED, 'redirectsiteurl', 1)."&nbsp;&nbsp;
							".$frm->radio('redirectsiteurl', 0, !$pref['redirectsiteurl']).$frm->label(LAN_DISABLED, 'redirectsiteurl', 0)."
							<div class='field-help'>".PRFLAN_135."</div>
						</td>
					</tr>
					<tr>
						<td>".PRFLAN_4."</td>
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

$text .= $frm->imagepicker('sitebutton',$pref['sitebutton'],'_common','help=Used by Facebook and others. Should be a square image.');

$text .= "
						</td>
					</tr>
					<tr>
						<td>".PRFLAN_214."</td>
						<td>".$frm->imagepicker('sitelogo',$pref['sitelogo'],'_common','help=Used by some themes as the header image on some pages.')."</td>
					</tr>
					<tr>
						<td>".PRFLAN_5."</td>
						<td>
							".$frm->textarea('sitetag', $pref['sitetag'], 3, 59)."
						</td>
					</tr>
					<tr>
						<td>".PRFLAN_6."</td>
						<td>
							".$frm->textarea('sitedescription', $pref['sitedescription'], 3, 80)."
						</td>
					</tr>
					
					<tr>
						<td>".PRFLAN_9."</td>
						<td>
							".$frm->textarea('sitedisclaimer', str_replace(array('<', '>', '"'), array('&lt;', '&gt;', '&quot;'), $pref['sitedisclaimer']), 3, 80)."
						</td>
					</tr>
				</tbody>
			</table>
			".pref_submit('main')."
		</fieldset>
";


// Email and Contact Information --------------

$text .= "<fieldset class='e-hideme' id='core-prefs-email'>
			<legend>".PRFLAN_13."</legend>
			<table class='table adminform'>
				<colgroup>
					<col class='col-label' />
					<col class='col-control' />
				</colgroup>
				<tbody>
				<tr>
					<td>".PRFLAN_7."</td>
					<td>
						".$frm->text('siteadmin', SITEADMIN, 100)."
					</td>
					</tr>
					<tr>
						<td>".PRFLAN_8."</td>
						<td>
							".$frm->text('siteadminemail', SITEADMINEMAIL, 100)."
						</td>
					</tr>
					<tr>
						<td>".PRFLAN_174."</td>
						<td>
							".$frm->text('replyto_name', $pref['replyto_name'], 100)."
							<div class='smalltext field-help'>".PRFLAN_175."</div>
						</td>
					</tr>
					<tr>
						<td>".PRFLAN_176."</td>
						<td>
							".$frm->text('replyto_email', $pref['replyto_email'], 100)."
							<div class='smalltext field-help'>".PRFLAN_177."</div>
						</td>
					</tr>
							
							
					<tr>
						<td>".LAN_MAILOUT_110."<br /></td>
						<td>".$frm->admin_button('testemail', LAN_MAILOUT_112,'other')."&nbsp;
							<input name='testaddress' class='tbox' placeholder='user@yoursite.com' type='text' size='40' maxlength='80' value=\"".(varset($_POST['testaddress']) ? $_POST['testaddress'] : USEREMAIL)."\" />
						</td>
					</tr>
		
					<tr>
						<td style='vertical-align:top'>".LAN_MAILOUT_115."<br /></td>
						<td>
						<select class='tbox' name='mailer' onchange='disp(this.value)'>\n";
						$mailers = array('php','smtp','sendmail');
						foreach($mailers as $opt)
						{
							$sel = ($pref['mailer'] == $opt) ? "selected='selected'" : '';
							$text .= "<option value='{$opt}' {$sel}>{$opt}</option>\n";
						}
						$text .="</select> <span class='field-help'>".LAN_MAILOUT_116."</span><br />";
		


			// SMTP. -------------->
			$smtp_opts = explode(',',varset($pref['smtp_options'],''));
			$smtpdisp = ($pref['mailer'] != 'smtp') ? "style='display:none;'" : '';
			$text .= "<div id='smtp' {$smtpdisp}>
			<table class='table adminlist' style='margin-right:auto;margin-left:0px;border:0px'>
			<colgroup>
				<col class='col-label' />
				<col class='col-control' />
			</colgroup>
			";
			$text .= "
			<tr>
				<td>".LAN_MAILOUT_87.":&nbsp;&nbsp;</td>
				<td>
				<input class='tbox' type='text' name='smtp_server' size='40' value='".vartrue($pref['smtp_server'])."' maxlength='50' />
				</td>
			</tr>
	
			<tr>
				<td>".LAN_MAILOUT_88.":&nbsp;(".LAN_OPTIONAL.")&nbsp;&nbsp;</td>
				<td style='width:50%;' >
				<input class='tbox' type='text' name='smtp_username' size='40' value=\"".vartrue($pref['smtp_username'])."\" maxlength='50' />
				</td>
			</tr>
	
			<tr>
				<td>".LAN_MAILOUT_89.":&nbsp;(".LAN_OPTIONAL.")&nbsp;&nbsp;</td>
				<td>
				<input class='tbox' type='password' name='smtp_password' size='40' value='".vartrue($pref['smtp_password'])."' maxlength='50' />
				</td>
			</tr>

			<tr>
				<td>".LAN_MAILOUT_90."</td><td>
				<select class='tbox' name='smtp_options'>\n
				<option value=''>".LAN_MAILOUT_96."</option>\n";
			$selected = (in_array('secure=SSL',$smtp_opts) ? " selected='selected'" : '');
			$text .= "<option value='smtp_ssl'{$selected}>".LAN_MAILOUT_92."</option>\n";
			$selected = (in_array('secure=TLS',$smtp_opts) ? " selected='selected'" : '');
			$text .= "<option value='smtp_tls'{$selected}>".LAN_MAILOUT_93."</option>\n";
			$selected = (in_array('pop3auth',$smtp_opts) ? " selected='selected'" : '');
			$text .= "<option value='smtp_pop3auth'{$selected}>".LAN_MAILOUT_91."</option>\n";
			$text .= "</select><span class='field-help'>".LAN_MAILOUT_94."</span></td></tr>";
		
			$text .= "<tr>
				<td>".LAN_MAILOUT_57."</td><td>
				";
			$checked = (varsettrue($pref['smtp_keepalive']) ) ? "checked='checked'" : '';
			$text .= "<input type='checkbox' name='smtp_keepalive' value='1' {$checked} />
				</td>
				</tr>";
		
			$checked = (in_array('useVERP',$smtp_opts) ? "checked='checked'" : "");
			$text .= "<tr>
				<td>".LAN_MAILOUT_95."</td><td>
				<input type='checkbox' name='smtp_useVERP' value='1' {$checked} />
				</td>
				</tr>
				</table></div>";

			/* FIXME - posting SENDMAIL path triggers Mod-Security rules. 
			// Sendmail. -------------->
				
				$text .= "<div id='sendmail' {$senddisp}><table style='margin-right:0px;margin-left:auto;border:0px'>";
				$text .= "
				<tr>
				<td>".LAN_MAILOUT_20.":&nbsp;&nbsp;</td>
				<td>
				<input class='tbox' type='text' name='sendmail' size='60' value=\"".(!$pref['sendmail'] ? "/usr/sbin/sendmail -t -i -r ".$pref['siteadminemail'] : $pref['sendmail'])."\" maxlength='80' />
				</td>
				</tr>
			
				</table></div>";
			*/
				$senddisp = (varset($pref['mailer']) != 'sendmail') ? "e-hideme" : '';
				$text .= "<div class='s-message info {$senddisp}' id='sendmail' >
							Not available in this release
						</div>";
						
						
				$text .="</td>
				</tr>
			
			
				<tr>
					<td>".LAN_MAILOUT_222."</td>
					<td>";
					
				$emFormat = array(
					'textonly' => LAN_MAILOUT_125,
					'texthtml' => LAN_MAILOUT_126,
					'texttheme' => LAN_MAILOUT_127
				);	
				$text .= $frm->selectbox('mail_sendstyle', $emFormat, vartrue($pref['mail_sendstyle'])); 
				$text .= "
					</td>
				</tr>
					

					<tr>
						<td>".PRFLAN_162."</td>
						<td>
							".$frm->textarea('sitecontactinfo', $pref['sitecontactinfo'], 6, 59)."
							<div class='smalltext field-help'>".PRFLAN_163."</div>
						</td>
					</tr>
					<tr>
						<td>".PRFLAN_168."</td>
						<td>
							".$e_userclass->uc_dropdown('sitecontacts', $pref['sitecontacts'], 'nobody,main,admin,classes', "tabindex='".$frm->getNext()."'")."
							<div class='smalltext field-help'>".PRFLAN_169."</div>
						</td>
					</tr>
					<tr>
						<td>".PRFLAN_164."</td>
						<td>
							".$frm->radio('contact_emailcopy', 1, $pref['contact_emailcopy'])."
							".$frm->label(LAN_ENABLED, 'contact_emailcopy', 1)."&nbsp;&nbsp;
							".$frm->radio('contact_emailcopy', 0, !$pref['contact_emailcopy'])."
							".$frm->label(LAN_DISABLED, 'contact_emailcopy', 0)."
							<div class='smalltext field-help'>".PRFLAN_165."</div>
						</td>
					</tr>
						</tbody>
			</table>
			".pref_submit('email')."
		</fieldset>";


$text .= "
		<fieldset class='e-hideme' id='core-prefs-display'>
			<legend>".PRFLAN_13."</legend>
			<table class='table adminform'>
				<colgroup>
					<col class='col-label' />
					<col class='col-control' />
				</colgroup>
				<tbody>
					<tr>
						<td>".PRFLAN_14." </td>
						<td>
							".$frm->radio_switch('displaythemeinfo', $pref['displaythemeinfo'])."
						</td>
					</tr>
					<tr>
						<td>".PRFLAN_15." </td>
						<td>
							".$frm->radio_switch('displayrendertime', $pref['displayrendertime'])."
						</td>
					</tr>
					<tr>
						<td>".PRFLAN_16." </td>
						<td>
							".$frm->radio_switch('displaysql', $pref['displaysql'])."
						</td>
					</tr>
	";
if(function_exists("memory_get_usage"))
{
	$text .= "
					<tr>
						<td>".PRFLAN_137." </td>
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

// Admin Display Areas
$text .= "
		<fieldset class='e-hideme' id='core-prefs-admindisp'>
			<legend>".PRFLAN_77."</legend>
			<table class='table adminform'>
				<colgroup>
					<col class='col-label' />
					<col class='col-control' />
				</colgroup>
				<tbody>
					<tr>
						<td>".PRFLAN_95."</td>
						<td>
							".$frm->radio_switch('admin_alerts_ok', $pref['admin_alerts_ok'])."
							<div class='smalltext field-help'>".PRFLAN_96."</div>
						</td>
					</tr>
					<tr>
						<td>".PRFLAN_97."</td>
						<td>
							".$frm->radio_switch('admin_alerts_uniquemenu', $pref['admin_alerts_uniquemenu'])."
							<div class='smalltext field-help'>".PRFLAN_98."</div>
						</td>
					</tr>
					<tr>
						<td>".PRFLAN_199."</td>
						<td>
							".$frm->radio_switch('admin_slidedown_subs', $pref['admin_slidedown_subs'])."
							<div class='smalltext field-help'>".PRFLAN_200."</div>
						</td>
					</tr>
					<tr>
						<td>".PRFLAN_204."</td>
						<td>
							".$frm->radio_switch('admin_separate_plugins', $pref['admin_separate_plugins'])."
							<div class='smalltext field-help'>".PRFLAN_205."</div>
						</td>
					</tr>
				</tbody>
			</table>
			".pref_submit('admindisp')."
		</fieldset>

	";

// Date options.
$ga = new convert();
$date1 = $ga->convert_date(time(), "short");
$date2 = $ga->convert_date(time(), "long");
$date3 = $ga->convert_date(time(), "forum");
$date4 = e107::getDate()->convert(time(),"input");

$text .= "
		<fieldset class='e-hideme' id='core-prefs-date'>
			<legend>".PRFLAN_21."</legend>
			<table class='table adminform'>
				<colgroup>
					<col class='col-label' />
					<col class='col-control' />
				</colgroup>
				<tbody>
					<tr>
						<td>".PRFLAN_22.": </td>
						<td>
							".$frm->text('shortdate', $pref['shortdate'], 50)."
							<div class='field-help'>".PRFLAN_83.": {$date1}</div>
						</td>
					</tr>
					<tr>
						<td>".PRFLAN_23.": </td>
						<td>
							".$frm->text('longdate', $pref['longdate'], 50)."
							<div class='field-help'>".PRFLAN_83.": {$date2}</div>
						</td>
					</tr>
					<tr>
						<td>".PRFLAN_24."</td>
						<td>
							".$frm->text('forumdate', $pref['forumdate'], 50)."
							<div class='field-help'>".PRFLAN_83.": {$date3}</div>
							<div class='field-help'>".PRFLAN_25." <a href='http://www.php.net/manual/en/function.strftime.php' rel='external'>".PRFLAN_93."</a></div>
						</td>
					</tr>";
					
					
					
					$def = strtotime('December 21, 2012 3:45pm');
					
					$inputdate = array( // TODO add more formats
						"%A, %d %B, %Y"	=> strftime("%A, %d %B, %Y",$def),
						"%A, %d %b, %Y"	=> strftime("%A, %d %b, %Y",$def),
						"%a, %d %B, %Y"	=> strftime("%a, %d %B, %Y",$def),
						"%a, %d %b, %Y"	=> strftime("%a, %d %b, %Y",$def),
						
						"%A, %B %d, %Y"	=> strftime("%A, %B %d, %Y",$def),
						"%A, %b %d, %Y"	=> strftime("%A, %b %d, %Y",$def),
						"%A, %b %d, %y"	=> strftime("%A, %b %d, %y",$def),
						
						"%B %d, %Y"		=> strftime("%B %d, %Y",$def),
						"%b %d, %Y"		=> strftime("%b %d, %Y",$def),
						"%b %d, %y"		=> strftime("%b %d, %y",$def),
						
						"%d %B, %Y"		=> strftime("%d %B, %Y",$def),
						"%d %b, %Y"		=> strftime("%d %b, %Y",$def),
						"%d %b, %y"		=> strftime("%d %b, %y",$def),
						
						"%Y-%m-%d"		=> strftime("%Y-%m-%d",$def),
						"%d-%m-%Y"		=> strftime("%d-%m-%Y",$def),
						"%m/%d/%Y"		=> strftime("%m/%d/%Y",$def)
					);
					
			
					$inputtime = array();
					
			
					
						 
					$inputtime["%I:%M %p"]	= strftime("%I:%M %p",$def);
					if(e107::getDate()->supported('P'))
					{	
						$inputtime["%I:%M %P"]	=  strftime("%I:%M %P",$def);
					}
					if(e107::getDate()->supported('l'))
					{
						$inputtime["%l:%M %p"]	= strftime("%l:%M %p",$def);
						$inputtime["%l:%M %P"]	= strftime("%l:%M %P",$def);	
					}
					
					$inputtime["%H:%M"]		= strftime("%H:%M",$def);
					$inputtime["%H:%M:%S"]	= strftime("%H:%M:%S",$def);
									
			
			
			
					
					
					$text .= "
					<tr>
						<td>Date/Time Input-Field format: </td>
						<td>
							".$frm->selectbox('inputdate',$inputdate, e107::getPref('inputdate'));
							
					$text .= $frm->selectbox('inputtime',$inputtime, e107::getPref('inputtime'));
					
					$text .= "
						</td>
					</tr>
					<tr>
						<td>".PRFLAN_26."</td>
						<td>
							".$frm->select_open('time_offset', 'class=tbox select time-offset');//use form handler because of the tabindex
$toffset = array("-12", "-11", "-10", "-9", "-8", "-7", "-6", "-5", "-4", "-3", "-2", "-1", "0", "+1", "+2", "+3", "+4", "+5", "+6", "+7", "+8", "+9", "+10", "+11", "+12", "+13", "+14", "+15", "+16");
if(! isset($pref['time_offset']))
{
	$pref['time_offset'] = "0";
}
foreach($toffset as $o)
{
	$text .= "
								".$frm->option($o, $o, ($o == $pref['time_offset']))."
	";
}
$text .= "
							</select>
							<div class='smalltext field-help'>".PRFLAN_27."</div>
						</td>
					</tr>
					<tr>
						<td>".PRFLAN_56.": </td>
						<td>
							".$frm->text('timezone', $pref['timezone'], 50)."
						</td>
					</tr>
				</tbody>
			</table>
			".pref_submit('date')."
		</fieldset>
";

// =========== Registration Preferences. ==================
$text .= "
		<fieldset class='e-hideme' id='core-prefs-registration'>
			<legend>".PRFLAN_28."</legend>
			<table class='table adminform'>
				<colgroup>
					<col class='col-label' />
					<col class='col-control' />
				</colgroup>
				<tbody>
					<tr>
						<td>".PRFLAN_29."</td>
						<td>
							".$frm->radio_switch('user_reg', $pref['user_reg'])."
							<div class='smalltext field-help'>".PRFLAN_30."</div>
						</td>
					</tr>


					<tr>
						<td>".PRFLAN_154."</td>
						<td>
							".$frm->select_open('user_reg_veri');
                            $veri_list = array(PRFLAN_152,PRFLAN_31,PRFLAN_153);

							foreach($veri_list as $v => $v_title)
							{
								$text .= $frm->option($v_title, $v, ($pref['user_reg_veri'] == $v));
							}

					$text .= "
							</select>
							<div class='field-help'>".PRFLAN_154a."</div>
						</td>
					</tr>
                    <tr>
						<td>".PRFLAN_184."</td>
						<td>".$frm->select_open('allowEmailLogin');
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
					<tr>
						<td>".PRFLAN_160."</td>
						<td>
							".$frm->radio_switch('signup_remote_emailcheck', $pref['signup_remote_emailcheck'])."
						</td>
					</tr>
					<tr>
						<td>".PRFLAN_167."</td>
						<td>
							".$frm->radio_switch('disable_emailcheck', $pref['disable_emailcheck'])."
						</td>
					</tr>

					<tr>
						<td>".PRFLAN_45."</td>
						<td>
							".$frm->radio_switch('use_coppa', $pref['use_coppa'])."
							<div class='field-help'>".PRFLAN_46." <a href='http://www.ftc.gov/privacy/coppafaqs.shtm'>".PRFLAN_94."</a></div>
						</td>
					</tr>
					<tr>
						<td>".PRFLAN_58."</td>
						<td>";
					
					$memDisp = !vartrue($pref['membersonly_enabled']) ? "e-hideme" : "";
						
					$text .= $frm->radio_switch('membersonly_enabled', $pref['membersonly_enabled'],'', '', 'class=e-expandit')."
							<div class='field-help'>".PRFLAN_59."</div>
							<div class='e-expandit-container {$memDisp}' style='padding-top:10px'>".
							$frm->textarea('membersonly_exceptions', $pref['membersonly_exceptions'], 3, 1, 'placeholder='.PRFLAN_206)."
							<div class='field-help'>".PRFLAN_207."</div>
							</div>
						</td>
					</tr>
              
               		<tr>
						<td>".PRFLAN_197.": </td>
						<td>
							".$frm->radio_switch('autologinpostsignup', $pref['autologinpostsignup'])."
							<div class='smalltext field-help'>".PRFLAN_198."</div>
						</td>
					</tr>


					<tr>
						<td>".PRFLAN_136."</td>
						<td>
							".$frm->number('signup_maxip', $pref['signup_maxip'], 3)."
							<div class='field-help'>".PRFLAN_78."</div>
						</td>
					</tr>

				
				</tbody>
			</table>
			".pref_submit('registration')."
		</fieldset>

	";
	
// Single/ Social  Login / / copied from hybridAuth config.php so it's easy to add more. 
// Used Below. 

$social_logins = array ( 
			// openid providers
			"OpenID" => array (
				"enabled" => true
			),

			"Yahoo" => array ( 
				"enabled" => true 
			),

			"AOL"  => array ( 
				"enabled" => true 
			),

			"Google" => array ( 
				"enabled" => true,
				"keys"    => array ( "id" => "", "secret" => "" ),
				"scope"   => ""
			),

			"Facebook" => array ( 
				"enabled" => true,
				"keys"    => array ( "id" => "", "secret" => "" ),

				// A comma-separated list of permissions you want to request from the user. See the Facebook docs for a full list of available permissions: http://developers.facebook.com/docs/reference/api/permissions.
				"scope"   => "", 

				// The display context to show the authentication page. Options are: page, popup, iframe, touch and wap. Read the Facebook docs for more details: http://developers.facebook.com/docs/reference/dialogs#display. Default: page
				"display" => "" 
			),

			"Twitter" => array ( 
				"enabled" => true,
				"keys"    => array ( "key" => "", "secret" => "" ) 
			),

			// windows live
			"Live" => array ( 
				"enabled" => true,
				"keys"    => array ( "id" => "", "secret" => "" ) 
			),

			"MySpace" => array ( 
				"enabled" => true,
				"keys"    => array ( "key" => "", "secret" => "" ) 
			),

			"LinkedIn" => array ( 
				"enabled" => true,
				"keys"    => array ( "key" => "", "secret" => "" ) 
			),

			"Foursquare" => array (
				"enabled" => true,
				"keys"    => array ( "id" => "", "secret" => "" ) 
			)
		);
 
 
// Key registration 
$social_external = array(
			"Facebook" 		=> "https://developers.facebook.com/apps",
			"Twitter"		=> "https://dev.twitter.com/apps/new",
			"Google"		=> "https://code.google.com/apis/console/",
			"Live"			=> "https://manage.dev.live.com/ApplicationOverview.aspx",
			"LinkedIn"		=> "https://www.linkedin.com/secure/developer",
			"Foursquare"	=> "https://www.foursquare.com/oauth/"
); 
 
 
$text .= "
		<fieldset class='e-hideme' id='core-prefs-sociallogin'>
					<legend>Social Login Options</legend>
					<div>Note: This section requires further testing</div>
					<table class='table adminform'>
						<colgroup>
							<col class='col-label' />
							<col class='col-control' />
						</colgroup>
						<tbody>
					<tr>
						<td>Enable Social Logins</td>
						<td>
							".$frm->radio_switch('social_login_active', $pref['social_login_active'])."
						</td>
					</tr>";
					
			if(!is_array($pref['social_login']))
			{
				$pref['social_login'] = array();	
			}
							
			foreach($social_logins as $prov=>$val)
			{
					
					$label = varset($social_external[$prov]) ? "<a class='e-tip' rel='external' title='Get a key from the provider' href='".$social_external[$prov]."'>".$prov."</a>" : $prov;
									
					$text .= "
					<tr>
						<td>".$label."</td>
						<td>
						";
					foreach($val as $k=>$v)
					{
						switch ($k) {
							case 'enabled':
								$eopt = array('class'=>'e-expandit');
								$text .= $frm->radio_switch('social_login['.$prov.'][enabled]', vartrue($pref['social_login'][$prov]['enabled']),'','',$eopt);
							break;
							
							case 'keys':
								// $cls = vartrue($pref['single_login'][$prov]['keys'][$tk]) ? "class='e-hideme'" : '';
								$sty = vartrue($pref['social_login'][$prov]['keys'][vartrue($tk)]) ? "" : "e-hideme";
								$text .= "<div class='e-expandit-container {$sty}' id='option-{$prov}' >";
								foreach($v as $tk=>$idk)
								{
									$opt['placeholder'] = $tk;
									$text .= "<br />".$frm->text('social_login['.$prov.'][keys]['.$tk.']', vartrue($pref['social_login'][$prov]['keys'][$tk]),100,$opt);								
								}	
								$text .= "</div>";
								
							break;
							
							case 'scope':
								$text .= $frm->hidden('social_login['.$prov.'][scope]','email');
							break;
							
							default:
								
							break;
						}	
					}				
				
				$text .= "</td>
					</tr>					
					";
			}		
					
	
	
$text .= "
				</tbody>
			</table>
			".pref_submit('sociallogin')."
		</fieldset>
";	
	
	

// Signup options ===========================.


$text .= "
		<fieldset class='e-hideme' id='core-prefs-signup'>
			<legend>".PRFLAN_19."</legend>
			<table class='table adminform'>
				<colgroup>
					<col class='col-label' />
					<col class='col-control' />
				</colgroup>
				<tbody>";
				
		$signup_option_names = array(
	//	"signup_option_loginname" 	=> "Login Name",
		"signup_option_email_confirm" 	=> "Email Confirmation",
		"signup_option_realname" 		=> CUSTSIG_2,
		"signup_option_signature" 		=> CUSTSIG_6,
		"signup_option_image" 			=> CUSTSIG_7,
		"signup_option_class" 			=> CUSTSIG_17,
		'signup_option_customtitle'		=> CUSTSIG_20,
		'signup_option_hideemail'		=> 'Option to hide email'
	);

	foreach($signup_option_names as $value => $key)
	{
		$text .= "
						<tr>
							<td>".$key."</td>
							<td>
								".$frm->radio($value, 0, !$pref[$value]).$frm->label(CUSTSIG_12, $value, 0)."&nbsp;&nbsp;
								".$frm->radio($value, 1, ($pref[$value] == 1)).$frm->label(CUSTSIG_14, $value, 1)."&nbsp;&nbsp;
								".$frm->radio($value, 2, ($pref[$value] == 2)).$frm->label(CUSTSIG_15, $value, 2)."
							</td>
						</tr>
		";
	}			
				
				
				$text .= "
					<tr>
						<td>".PRFLAN_126."</td>
						<td>
							".$frm->textarea('signup_text', $pref['signup_text'], 2, 1)."
						</td>
					</tr>

					<tr>
						<td>".PRFLAN_140."</td>
						<td>
							".$frm->textarea('signup_text_after', $pref['signup_text_after'], 2, 1)."
						</td>
					</tr>
					
				
					<tr>
						<td>".PRFLAN_192.":</td>
						<td>
							".$frm->text('predefinedLoginName', $pref['predefinedLoginName'], 50)."
							<div class='field-help'>".PRFLAN_193."</div>
							<div class='field-help'>".PRFLAN_194."</div>
						</td>
					</tr>
					<tr>
						<td>".PRFLAN_158.":</td>
						<td>
							".$frm->number('displayname_maxlength', $pref['displayname_maxlength'], 3)."
						</td>
					</tr>
					<tr>
						<td>".PRFLAN_172.":</td>
						<td>
							".$frm->number('loginname_maxlength', $pref['loginname_maxlength'], 3)."
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

if(!isset($pref['post_html']))
{
	$pref['post_html'] = '250';
	save_prefs();
}

$text .= "
		<fieldset class='e-hideme' id='core-prefs-textpost'>
			<legend>".PRFLAN_101."</legend>
			<table class='table adminform'>
				<colgroup>
					<col class='col-label' />
					<col class='col-control' />
				</colgroup>
				<tbody>
					<tr>
						<td>".PRFLAN_127.":</td>
						<td>
							".$frm->radio_switch('make_clickable', $pref['make_clickable'])."
							<div class='smalltext field-help'>".PRFLAN_128."</div>
						</td>
					</tr>";
					
				$replaceDisp = vartrue($pref['link_replace']) ? "" : "e-hideme";
				
				$text .= "
					<tr>
						<td>".PRFLAN_102."?:</td>
						<td>
							".$frm->radio_switch('link_replace', $pref['link_replace'],'', '', 'expandit=1')."
							<div class='smalltext field-help'>".PRFLAN_103."</div>
							<div class='e-expandit-container {$replaceDisp}'>
							".$frm->text('link_text', $pref['link_text'], 200, 'placeholder='.PRFLAN_104)."
							<div class='smalltext field-help'>".PRFLAN_105."</div>".
							$frm->text('email_text', $tp->post_toForm($pref['email_text']), 200, 'placeholder='.PRFLAN_107)."
							<div class='smalltext field-help'>".PRFLAN_108."</div>
							
							</div>
						</td>
					</tr>
			
					<tr >
						<td>".PRFLAN_145."?:</td>
						<td>
							".$frm->radio_switch('links_new_window', $pref['links_new_window'])."
							<div class='smalltext field-help'>".PRFLAN_146."</div>
						</td>
					</tr>
					
					
					<tr>
						<td>".PRFLAN_40."</td>
						<td>
							".$frm->radio_switch('profanity_filter', $pref['profanity_filter'])."
							<div class='smalltext field-help'>".PRFLAN_41."</div>
						</td>
					</tr>

					<tr>
						<td>".PRFLAN_42.":</td>
						<td>
							".$frm->text('profanity_replace', $pref['profanity_replace'], 20)."
						</td>
					</tr>
					<tr>
						<td>".PRFLAN_43.":</td>
						<td>
							".$frm->tags('profanity_words', $pref['profanity_words'])."
							<div class='field-help'>".PRFLAN_44."</div>
						</td>
					</tr>
					
				
					<tr>
						<td>".PRFLAN_109.":</td>
						<td>
							".$frm->number('main_wordwrap', $pref['main_wordwrap'], 3)."
							<div class='smalltext field-help'>".PRFLAN_110."</div>
						</td>
					</tr>
					<tr>
						<td>".PRFLAN_111.":</td>
						<td>
							".$frm->number('menu_wordwrap', $pref['menu_wordwrap'], 3)."
							<div class='smalltext field-help'>".PRFLAN_110."</div>
						</td>
					</tr>

					<tr>
						<td>".PRFLAN_116.":</td>
						<td>
							".$e_userclass->uc_dropdown('post_html', $pref['post_html'], 'nobody,public,member,admin,main,classes', "tabindex='".$frm->getNext()."'")."
							<div class='smalltext field-help'>".PRFLAN_117."</div>
						</td>
					</tr>

					<tr>
						<td>".PRFLAN_215.":</td>
						<td>
							".r_userclass('post_script',$pref['post_script'],'off','nobody,member,admin,main,classes')."
							<div class='smalltext field-help'>".PRFLAN_216."</div>
						</td>
					</tr>
					<tr>
						<td>".PRFLAN_217.":</td>
						<td>
							".$frm->radio_switch('filter_script', varset($pref['filter_script'], 1))."
							<div class='smalltext field-help'>".PRFLAN_218."</div>
						</td>
					</tr>
					<tr>
						<td>".PRFLAN_220.":</td>
						<td>
							".$frm->radio_switch('html_abuse', varset($pref['html_abuse'], 1))."
							<div class='smalltext field-help'>".PRFLAN_221."</div>
						</td>
					</tr>
					<tr>
						<td>".PRFLAN_122.":</td>
						<td>
							".$frm->radio_switch('wysiwyg', $pref['wysiwyg'])."
							<div class='smalltext field-help'>".PRFLAN_123."</div>
						</td>
					</tr>
					<tr>
						<td>".PRFLAN_124.":</td>
						<td>
							".$frm->radio_switch('old_np', $pref['old_np'])."
							<div class='smalltext field-help'>".PRFLAN_125."</div>
						</td>
					</tr>
					
";

if(file_exists(e_PLUGIN."geshi/geshi.php"))
{
	$text .= "
					<tr>
						<td>".PRFLAN_118."?:</div></td>
						<td>
							".$frm->radio_switch('useGeshi', $pref['useGeshi'])."
							<div class='smalltext field-help'>".PRFLAN_119."</div>
						</td>
					</tr>
					<tr>
						<td>".PRFLAN_120."?:</td>
						<td>
							".$frm->text('defaultLanGeshi', ($pref['defaultLanGeshi'] ? $pref['defaultLanGeshi'] : "php"), 20)."
							<div class='smalltext field-help'>".PRFLAN_121."</div>
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
		$ret .= $gap."<input type='radio' name='{$name}' value='{$v}'{$sel} /> ".$t."";
		$gap = "&nbsp;&nbsp;";
	}
	return $ret;
}

// Security Options. .
$hasGD = extension_loaded("gd");

$text .= "
		<fieldset class='e-hideme' id='core-prefs-security'>
			<legend>".PRFLAN_47."</legend>
			<table class='table adminform'>
				<colgroup>
					<col class='col-label' />
					<col class='col-control' />
				</colgroup>
				<tbody>
					<tr>
						<td>".PRFLAN_60."</td>

						<td>
							".$frm->radio_switch('ssl_enabled', $pref['ssl_enabled'])."
							<div class='field-help'>".PRFLAN_61."</div>
						</td>
					</tr>
					<tr>
						<td>".PRFLAN_76.": </td>
						<td>
";

if($hasGD)
{
	$text .= $frm->radio_switch('signcode', $pref['signcode']);
}
else
{
	$text .= PRFLAN_133;
}
$text .= "
						</td>
					</tr>
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
					</tr>
					<tr>
						<td>".PRFLAN_92.": </td>
						<td>
							".$frm->radio_switch('user_reg_secureveri', $pref['user_reg_secureveri'])."
						</td>
					</tr>

					<tr>
						<td>".PRFLAN_129.":</td>
						<td>
							".$frm->radio_switch('disallowMultiLogin', $pref['disallowMultiLogin'], LAN_YES, LAN_NO)."
							<div class='smalltext field-help'>".PRFLAN_130."</div>
						</td>
					</tr>

					<tr>
						<td>".PRFLAN_48.":</td>
						<td>
							<div class='field-spacer'>".$frm->radio_multi('user_tracking', array('cookie' => PRFLAN_49, 'session' => PRFLAN_50), $pref['user_tracking'])."</div>
							".PRFLAN_55.": <br />".$frm->text('cookie_name', $pref['cookie_name'], 20)."
						</td>
					</tr>
					
				
		
					<tr>
						<td>".CUSTSIG_18."</td>
						<td>
							".$frm->textarea('signup_disallow_text', $pref['signup_disallow_text'], 2, 1)."
							<div class='field-help'>".CUSTSIG_19."</div>
						</td>
					</tr>
					
						<tr>
						<td>".PRFLAN_155.":</td>
						<td>
							<div class='field-spacer'>".$e_userclass->uc_dropdown('displayname_class', $pref['displayname_class'], 'nobody,member,admin,classes', "tabindex='".$frm->getNext()."'")."</div>
							".$frm->admin_button('submit_resetdisplaynames', PRFLAN_156)."
						</td>
					</tr>
					
					
					
					<tr>
						<td>".CUSTSIG_16."</td>
						<td>
							".$frm->number('signup_pass_len', $pref['signup_pass_len'], 2)."
						</td>
					</tr>
					
					
					
					<tr>
						<td>".PRFLAN_188.":</td>
						<td>
							".$frm->radio_switch('passwordEncoding', varset($pref['passwordEncoding'], 0), PRFLAN_190, PRFLAN_189)."
							<div class='smalltext field-help'>".PRFLAN_191."</div>
						</td>
					</tr>
					<tr>";
					
					$CHAP_list = array(PRFLAN_180, PRFLAN_181, PRFLAN_182);
	
					$text .= "
						<td>".PRFLAN_178."</td>
						<td>".$frm->selectbox('password_CHAP',$CHAP_list,$pref['password_CHAP'] )."
							".$frm->select_open('password_CHAP');
							
						//TODO - user tracking session name - visible only if Cookie is enabled (JS)

						$text .= "
							<div class='smalltext field-help'>".PRFLAN_183."<br />".PRFLAN_179."</div>
						</td>
					</tr>
					
					<tr>
						<td>".PRFLAN_35.":</td>
						<td>
							".$frm->radio_switch('antiflood1', $pref['antiflood1'])."
						</td>
					</tr>
					<tr>
						<td>".PRFLAN_36.":</td>
						<td>
							".$frm->number('antiflood_timeout', $pref['antiflood_timeout'], 3)."
							<div class='smalltext field-help'>".PRFLAN_38."</div>
						</td>
					</tr>
					<tr>
						<td>".PRFLAN_37."</td>
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
							<div class='field-help'>".PRFLAN_91."</div>
						</td>
					</tr>
					<tr>
						<td>".PRFLAN_139.":</td>
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
			<legend>".PRFLAN_87."</legend>
			<table class='table adminform'>
				<colgroup>
					<col class='col-label' />
					<col class='col-control' />
				</colgroup>
				<tbody>
					<tr>
						<td>Allow users to post comments: </td>
						<td>
							".$frm->radio_switch('comments_disabled', $pref['comments_disabled'], LAN_NO, LAN_YES,array('reverse'=>1))."
						</td>
					</tr>
             		<tr>
						<td>".PRFLAN_32."</td>
						<td>
							".$frm->radio_switch('anon_post', $pref['anon_post'], LAN_YES, LAN_NO)."
							<div class='field-help'>".PRFLAN_33."</div>
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
						<td>Moderate Comments made by: </td>
						<td>
							".
							
							$frm->uc_select('comments_moderate', $pref['comments_moderate'],"nobody,guest,new,bots,public,admin,main,classes").
							"
							<div class='field-help'>Comments will require manual approval by an admin prior to being visible to other users</div>
						</td>
					</tr>
					<tr>
						<td>Comment Sorting: </td>
						<td>";
						
						$comment_sort = array(
							"desc"	=> "Most recent comments first", //default //TODO LAN
							'asc'	=> "Most recent comments last" 
						);
					
					$text .= $frm->selectbox('comments_sort',$comment_sort, $pref['comments_moderate'])."
						</td>
					</tr>
					
				</tbody>
			</table>

			<legend>".PRFLAN_209."</legend>
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

	include_lan(e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_upload.php");

	$text .= "
	<fieldset class='e-hideme' id='core-prefs-uploads'>
			<legend>File Uploading</legend>
			<table class='table adminform'>
				<colgroup>
					<col class='col-label' />
					<col class='col-control' />
				</colgroup>
				<tbody>
	<tr>
	<td>".UPLLAN_25."</td>
	<td>".
	
	$frm->radio_switch('upload_enabled', $pref['upload_enabled'], LAN_YES, LAN_NO)
	."
	<div class='field-help'>".UPLLAN_26."</div>
	</td>
	</tr>

	<tr>
	<td>".UPLLAN_33."<br />
	</td>
	<td>".
	$frm->text('upload_maxfilesize', $pref['upload_maxfilesize'], 10)
	 ."
	 <div class='field-help'>".UPLLAN_34." (upload_max_filesize = ".ini_get('upload_max_filesize').", post_max_size = ".ini_get('post_max_size')." )</div>
	</td>
	</tr>

	<tr>
	<td>".UPLLAN_37."</td>
	<td>".r_userclass("upload_class", $pref['upload_class'],"off","nobody,public,guest,member,admin,classes")."
	<div class='field-help'>".UPLLAN_38."</div>
	</td>
	</tr>
	</tbody>
		</table>
			".pref_submit('uploads')."
		</fieldset>";	
	
	
	
	
	
// Javascript Control
//TODO LANS
$text .= "
			<fieldset class='e-hideme' id='core-prefs-javascript'>
			<legend>Javascript Frameworks (for testing purposes only)</legend>
			<table class='table adminform'>
				<colgroup>
					<col class='col-label' />
					<col class='col-control' />
				</colgroup>
				<tbody>";
	
		$js_options = array(
			'auto'	=> 'Auto (on-demand)', 	// load based on dependency
			'admin'	=> 'Admin Area', 		// Always load in admin
			'front'	=> 'Front-End', 		// Always load in front-end
			'all'	=> "Both",				// Always load in admin and front-end
			'none'	=> 'Disabled' 			// disabled
		);	
	
		
		//TODO FIXME 
		// ie. e107 Core Minimum: JS similar to e107 v1.0 should be loaded "e_js.php" (no framwork dependency) 
		// with basic functions like SyncWithServerTime() and expandit(), externalLinks() etc. 

		
		$js_types = array(
			array('id'	=> 'prototype',		'name'=> 'Prototype (local)'),
			array('id'	=> 'jquery', 		'name'=> 'jQuery (local)'),		
 		);	
		
		//TODO // separate switch for CDN.. or automatic fall-back. 	
				
		
				
		foreach($js_types as $arr)
		{
			// $k = $arr['path'];
			$k = $arr['id'];
			$name = $arr['name'];
			$text .= "<tr>
				<td>".$name."</td>
				<td>".$frm->radio_multi("e_jslib_core[{$k}]",$js_options,$pref['e_jslib_core'][$k])."</td>
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
				<tbody>
					<tr>
						<td>Disable scripts consolidation</td>
						<td>
							".$frm->radio_switch('e_jslib_nocombine', $pref['e_jslib_nocombine'], LAN_YES, LAN_NO)."
							<div class='smalltext field-help'>If disabled, scripts will be loaded in one consolidated file</div>
						</td>
					</tr>
					<tr>
						<td>Enable consolidated scripts zlib compression:</td>
						<td>
							".$frm->radio_switch('e_jslib_gzip', $pref['e_jslib_gzip'], LAN_YES, LAN_NO)."
							<div class='smalltext field-help'>Used only when script consolidation is enabled</div>
						</td>
					</tr>
					<tr>
						<td>Disable consolidated scripts server cache:</td>
						<td>
							".$frm->radio_switch('e_jslib_nocache', $pref['e_jslib_nocache'], LAN_YES, LAN_NO)."
							<div class='smalltext field-help'>Used only when script consolidation is enabled</div>
						</td>
					</tr>
					<tr>
						<td>Disable consolidated scripts browser cache:</td>
						<td>
							".$frm->radio_switch('e_jslib_nobcache', $pref['e_jslib_nobcache'], LAN_YES, LAN_NO)."
							<div class='smalltext field-help'>Used only when script consolidation is enabled</div>
						</td>
					</tr>
		";	
					
		$text .= "
					</tbody></table>
			".pref_submit('javascript')."
					</fieldset>
					
					";
					
					
		e107::js('inline',"			
						\$\$('#e-jslib-nocombine', '#e-jslib-nocombine-1').invoke('observe', 'change', function(event) {
							var element = event.findElement('input'), check = !parseInt(element.value);
							eHandleJsForm(check);
							
						});
						
						var eHandleJsForm = function(enable) {
							var collection = \$w('e-jslib-gzip e-jslib-nocache e-jslib-nobcache');
							collection.each(function(id) {
								var method = enable ? 'enable' : 'disable';
								\$\$('#' + id, '#' + id + '-1').invoke(method);
							});
						};
						
						eHandleJsForm(".($pref['e_jslib_nocombine'] ? 'false' : 'true').");
					
					","prototype");
	
	

//Advanced Features
$text .= "
		<fieldset class='e-hideme' id='core-prefs-advanced'>
			<legend>".PRFLAN_149."</legend>
			<table class='table adminform'>
				<colgroup>
					<col class='col-label' />
					<col class='col-control' />
				</colgroup>
				<tbody>
					<tr>
						<td>".PRFLAN_147.":</td>
						<td>
							".$frm->radio_switch('developer', $pref['developer'])."
							<div class='smalltext field-help'>".PRFLAN_148."</div>
						</td>
					</tr>
					<tr>
						<td>".PRFLAN_196."</td>
						<td>
						".$frm->radio_switch('log_page_accesses', $pref['log_page_accesses'])."
						<div class='field-help'>".PRFLAN_196a." <strong>".e_LOG."</strong></div>
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
if($authlist)
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

// END Advanced Features


$text .= "
	</form>
</div>
";

$e107->ns->tablerender(PRFLAN_53, $emessage->render().$text);

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
	$var['core-prefs-main']['text'] = PRFLAN_1;
	$var['core-prefs-email']['text'] = "Email &amp; Contact Info.";
	$var['core-prefs-registration']['text'] = PRFLAN_28;
	$var['core-prefs-signup']['text'] = PRFLAN_19;
	$var['core-prefs-sociallogin']['text'] = "Social Logins";
	
	$var['core-prefs-comments']['text'] = PRFLAN_210;
	$var['core-prefs-uploads']['text'] = "File Uploading"; // TODO LAN
	
	$var['core-prefs-header1']['header'] = "Advanced Options";	
	
	$var['core-prefs-display']['text'] = PRFLAN_13;
	$var['core-prefs-admindisp']['text'] = PRFLAN_77;
	$var['core-prefs-textpost']['text'] = PRFLAN_101;
	$var['core-prefs-security']['text'] = PRFLAN_47;
	$var['core-prefs-date']['text'] = PRFLAN_21;	
	$var['core-prefs-javascript']['text'] = "Javascript Framework"; // TODO LAN
	$var['core-prefs-advanced']['text'] = PRFLAN_149;
	
	e107::getNav()->admin("Basic ".LAN_OPTIONS.'--id--prev_nav', 'core-prefs-main', $var);
}

