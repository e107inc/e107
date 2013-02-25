<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_admin/theme.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

require_once("../class2.php");
if (!getperms("1")) {
	header("location:".e_BASE."index.php");
	exit;
}

include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_'.e_PAGE);

$e_sub_cat = 'theme_manage';

e107::css("inline","
.hide						{ display: none }
.admin-theme-thumb			{ height:130px;overflow:hidden;border:1px solid black;margin-bottom:10px   }
.admin-theme-thumb:hover	{ opacity:0.4 }

.admin-theme-options		{ transition: opacity .20s ease-in-out;
							 -moz-transition: opacity .20s ease-in-out;
							 -webkit-transition: opacity .20s ease-in-out;
							 opacity:0; 
							 width:100%;
							 height:80px;
							 padding-top:50px;
							 white-space:nowrap;
							 background-color:black;
							 display:block;position:relative; text-align:center; vertical-align:middle; top:-141px;}

.admin-theme-options:hover	{ opacity:0.8; }

.admin-theme-title			{ font-size: 15px; overflow:hidden; padding-left:5px; white-space:no-wrap; width:200px; position:relative; top:-132px; }

.admin-theme-select			{border:1px dotted silver;background-color:#DDDDDD;float:left }

.admin-theme-select-active	{ background-color:red;float:left }

.admin-theme-cell			{ width:202px; height:160px; padding:10px; -moz-border-radius: 5px; border-radius: 5px; margin:5px}

.admin-theme-cell-default   { border:1px dotted silver; background-color:#DDDDDD }



.admin-theme-cell-site		{ background-color: #d9edf7;  border: 1px solid #bce8f1; }

.admin-theme-cell-admin	 	{ background-color:#FFFFD5; border: 1px solid #FFCC00; }


");


require_once(e_HANDLER."theme_handler.php");
$themec = new themeHandler;
if(e_AJAX_REQUEST)
{
	define('e_IFRAME',true);
}
	


if(e_AJAX_REQUEST)
{
	if(vartrue($_GET['src'])) // Process Theme Download. 
	{				
		$string =  base64_decode($_GET['src']);	
		parse_str($string,$p);
		
		if(vartrue($_GET['info']))
		{		
			echo $themec->renderThemeInfo($p);
		//	print_a($p);
			exit;
		}
				
		$remotefile = $p['url'];
			
		$localfile = md5($remotefile.time()).".zip";
		$status = "Downloading...";
		
		e107::getFile()->getRemoteFile($remotefile,$localfile);
		
		if(!file_exists(e_TEMP.$localfile))
		{
			$status = ADMIN_FALSE_ICON."<br /><a href='".$remotefile."'>Download Manually</a>";
			echo $status;
			exit;	
		}
	//	chmod(e_PLUGIN,0777);
		chmod(e_TEMP.$localfile,0755);
		
		require_once(e_HANDLER."pclzip.lib.php");
		$archive = new PclZip(e_TEMP.$localfile);
		$unarc = ($fileList = $archive -> extract(PCLZIP_OPT_PATH, e_THEME, PCLZIP_OPT_SET_CHMOD, 0755));
	//	chmod(e_PLUGIN,0755);
		$dir 		= basename($unarc[0]['filename']);
	//		chmod(e_UPLOAD.$localfile,0666);
	
	
	
		/* Cannot use this yet until 'folder' is included in feed. 
		if($dir != $p['plugin_folder'])
		{
			
			echo "<br />There is a problem with the data submitted by the author of the plugin.";
			echo "dir=".$dir;
			echo "<br />pfolder=".$p['plugin_folder'];
			exit;
		}	
		*/
			
		if($unarc[0]['folder'] ==1 && is_dir($unarc[0]['filename']))
		{
			$status = "Unzipping...";
			$dir 		= basename($unarc[0]['filename']);
			$plugPath	= preg_replace("/[^a-z0-9-\._]/", "-", strtolower($dir));	
			$status = ADMIN_TRUE_ICON;
			//unlink(e_UPLOAD.$localfile);
			
		}
		else 
		{
			// print_a($fileList);
			$status = ADMIN_FALSE_ICON."<br /><a href='".$remotefile."'>Download Manually</a>";
			//echo $archive->errorInfo(true);
			// $status = "There was a problem";	
			//unlink(e_UPLOAD.$localfile);
		}
		
		echo $status;
	//	@unlink(e_TEMP.$localfile);
	
	//	echo "file=".$file;
		exit;				
	}		
		
	
	$tm = (string) $_GET['id'];	
	$data = $themec->getThemeInfo($tm);
	echo $themec->renderThemeInfo($data);
	
	exit;	
}
else 
{
		require_once("auth.php");
	

		echo '

		 <div id="myModal" class="modal hide fade" tabindex="-1" role="dialog"  aria-hidden="true">
			    <div class="modal-header">
			    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			    &nbsp;
			    </div>
			    <div class="modal-body">
			    <p>Loading…</p>
			    </div>
			    <div class="modal-footer">
			    <a href="#" data-dismiss="modal" class="btn btn-primary">Close</a>
			    </div>
			    </div>';	
}				




$mode = varset($_GET['mode'],'main'); // (e_QUERY) ? e_QUERY :"main" ;

if(vartrue($_POST['selectadmin']))
{
	$mode = "admin";
}

if(vartrue($_POST['upload']))
{
	$mode = "choose";
}

if(vartrue($_POST['selectmain']) || varset($_POST['setUploadTheme']))
{
	$mode = "main";
}

if($mode == 'convert')
{
	new theme_builder;	
}
else 
{
	$themec -> showThemes($mode);	
}



// <a data-toggle="modal" href="'.e_SELF.'" data-target="#myModal" class="btn" >Launch demo modal</a>




require_once("footer.php");

function theme_adminmenu()
{
	//global $mode;
	
	$mode = varset($_GET['mode'],'main');
	
  // 	$e107 = &e107::getInstance();

		$var['main']['text'] = TPVLAN_33;
		$var['main']['link'] = e_SELF;

		$var['admin']['text'] = TPVLAN_34;
		$var['admin']['link'] = e_SELF."?mode=admin";

		$var['choose']['text'] = TPVLAN_51;
		$var['choose']['link'] = e_SELF."?mode=choose";
		
		$var['online']['text'] = "Find Themes";
		$var['online']['link'] = e_SELF."?mode=online";

		$var['upload']['text'] = TPVLAN_38;
		$var['upload']['link'] = e_SELF."?mode=upload";
		
		$var['convert']['text'] = "Convert";
		$var['convert']['link'] = e_SELF."?mode=convert";

      //  $selected = (e_QUERY) ? e_QUERY : "main";


		e107::getNav()->admin(TPVLAN_26, $mode, $var);
}

class theme_builder 
{
	var $themeName = "";
	var $remove = array();
	
		function __construct()
		{
			$this->themeName = $_GET['newtheme'];
			
			if(vartrue($_GET['step']) == 3)
			{	
				$this->step3();	
				return;
			}
			
			if(vartrue($_GET['step']) == 2)
			{
				$this->step2();	
			}
			else 
			{
				$this->step1();
			}
				
		}		
	
		function step1()
		{
			
			$fl = e107::getFile();
			$frm = e107::getForm();
			$ns = e107::getRender();
			$mes = e107::getMessage();
			
			$plugFolders = $fl->get_dirs(e_THEME);	
			foreach($plugFolders as $dir)
			{
				if(file_exists(e_THEME.$dir."/theme.xml") || $dir == 'templates')
				{
					continue;	
				}	
				$newDir[$dir] = $dir;
			}
			
			$mes->addInfo("This Wizard will build a theme.xml meta file for your theme.<br />
				Before you start: <ul>
						<li>Make sure your theme's directory is writable</b></li>
						<li>Select your theme's folder to begin.</li>
				</ul>
			");
			
			$text = $frm->open('createPlugin','get',e_SELF."?mode=convert");
			$text .= "<table class='table adminform'>
						<colgroup>
							<col class='col-label' />
							<col class='col-control' />
						</colgroup>
				<tr>
					<td>Select your theme's folder</td>
					<td>".$frm->selectbox("newtheme",$newDir)."</td>
				</tr>";
				
				/*
				$text .= "
				<tr>
					<td>Create Files</td>
					<td>".$frm->checkbox('createFiles',1,1)."</td>
				</tr>";
				*/
				
			$text .= "				
				</table>
				<div class='buttons-bar center'>
				".$frm->admin_button('step', 2,'other','Go')."
				</div>";
			
			$text .= $frm->close();
			
			$ns->tablerender(TPVLAN_26.SEP."Converter".SEP."Step 1", $mes->render() . $text);			
			
		}	

		function step2()
		{
			$ns = e107::getRender();
			$mes = e107::getMessage();
			$frm = e107::getForm();
			
			$data = array(
				'main' 			=> array('name','lang','version','date', 'compatibility'),
				'author' 		=> array('name','url'),
				'summary' 		=> array('summary'),
				'description' 	=> array('description'),
				'keywords' 		=> array('one','two'),
				'category'		=> array('category'),
				'copyright'		=> array('copyright')
		//		'adminLinks'	=> array('url','description','icon','iconSmall','primary'),
		//		'sitelinks'		=> array('url','description','icon','iconSmall')
			);			
					
			$legacyFile = e_THEME.$this->themeName."/theme.php";		
			if(file_exists($legacyFile))
			{
				$legacyData = file_get_contents($legacyFile);	
				
				$regex = '/\$([\w]*)\s*=\s*("|\')([\w @.\/:<\>,\'\[\] !()]*)("|\');/im';
				preg_match_all($regex, $legacyData, $matches);
				
				$leg = array();
				
				foreach($matches[1] as $i => $m)
				{
					$leg[$m] = strip_tags($matches[3][$i]);	
					if(substr($m,0,5) == 'theme' || $m == "CUSTOMPAGES")
					{
						$search[] = $matches[0][$i];		
					}					
				}
				
				$defaults = array(
					"main-name"					=> vartrue($leg['themename']),
					"author-name"				=> vartrue($leg['themeauthor']),
					"author-url"				=> vartrue($leg['themewebsite']),
					"description-description"	=> '',
					"summary-summary"			=> vartrue($leg['themeinfo']),
					"custompages"				=> vartrue($leg['CUSTOMPAGES']),
				);
				
				$search[] = "Steve Dunstan";
				$search[] = "jalist@e107.org";
				
				$_SESSION['themebulder-remove'] = $search;
				
				$mes->addInfo("Loading theme.php file");						
			}	
			
			$text = $frm->open('newtheme-step3','post', e_SELF.'?mode=convert&newtheme='.$this->themeName.'&step=3');
			$text .= "<table class='table adminlist'>";
			foreach($data as $key=>$val)
			{
				$text.= "<tr><td>$key</td><td>
				<div class='controls'>";
				foreach($val as $type)
				{
					$nm = $key.'-'.$type;
					$name = "xml[$nm]";	
					$size = (count($val)==1) ? 'span7' : 'span2';
					$text .= "<div class='{$size}'>".$this->xmlInput($name, $key."-". $type, vartrue($defaults[$nm]))."</div>";	
				}	
			
				$text .= "</div></td></tr>";
				
				
			}
			
			
			$text .= "</table>";
			$text .= "
			<div class='buttons-bar center'>"
			.$frm->hidden('newtheme', $this->themeName)
			.$frm->hidden('xml[custompages]', trim(vartrue($leg['CUSTOMPAGES'])))
			.$frm->admin_button('step', 3,'other','Generate')."
			</div>";
			
			$text .= $frm->close();

			$ns->tablerender(TPVLAN_26.SEP."Converter".SEP."Step 2", $mes->render() . $text);		
		}
					
				
		function step3()
		{
			$ns = e107::getRender();
			$mes = e107::getMessage();
			
		//	print_a($_POST);
			
			if($_POST['xml'])
			{
				$xmlText =	$this->createXml($_POST['xml']);
			}	
			
			$ns->tablerender("theme.xml", $mes->render(). "<pre>".$xmlText."</pre>");
			
			$legacyFile = e_THEME.$this->themeName."/theme.php";		
			if(file_exists($legacyFile))
			{
				$legacyData = file_get_contents($legacyFile);	
			}
			
			$legacyData = $this->cleanUp($legacyData);
			
			$output = nl2br(htmlentities($legacyData));
			
			// $legacyData = str_replace("\n\n\n","\n",$legacyData);
			
			$ns->tablerender("theme.php (updated)",  $output);
		}	


		function cleanUp($text)
		{
			$search = array();
			$replace = array();
		
			$search[0] 	= '$HEADER ';
			$replace[0]	= '$HEADER["default"] ';

			$search[1] 	= '$FOOTER ';
			$replace[1]	= '$FOOTER["default"] ';	
			
			// Early 0.6 and 0.7 Themes 

			$search[2] 	= '$CUSTOMHEADER ';
			$replace[2]	= '$HEADER["custom"] ';

			$search[3] 	= '$CUSTOMFOOTER ';
			$replace[3]	= '$FOOTER["custom"] ';
			
			//TODO Handle v1.x style themes. eg. $CUSTOMHEADER['something'];

			$text = str_replace($_SESSION['themebulder-remove'],"",$text);
					
			$text = str_replace($search, $replace, $text);
			
			return $text;	
		}


		function createXml($data)
		{
			$ns = e107::getRender();
			$mes = e107::getMessage();
			$tp = e107::getParser();
			
			foreach($data as $key=>$val)
			{
				$key = strtoupper(str_replace("-","_",$key));
				$newArray[$key] = $val;			
			}	
				
			if(vartrue($newArray['CUSTOMPAGES']))
			{
				$newArray['CUSTOMPAGES'] = trim($newArray['CUSTOMPAGES']);			
				$LAYOUTS = "<layout name='custom' title='Custom'>\n";
				$LAYOUTS .= "			<custompages>{CUSTOMPAGES}</custompages>\n";
				$LAYOUTS .= "		</layout>";
			}
			else
			{		
				$LAYOUTS = "";
			}
			

$template = <<<TEMPLATE
<?xml version="1.0" encoding="utf-8"?>
<e107Theme name="{MAIN_NAME}" lan="{MAIN_LANG}" version="{MAIN_VERSION}" date="{MAIN_DATE}" compatibility="{MAIN_COMPATIBILITY}" >
	<author name="{AUTHOR_NAME}" url="{AUTHOR_URL}" />
	<summary lan="">{SUMMARY_SUMMARY}</summary>
	<description lan="">{DESCRIPTION_DESCRIPTION}</description>
	<keywords>
		<word>{KEYWORDS_ONE}</word>
		<word>{KEYWORDS_TWO}</word>
	</keywords>
	<category>{CATEGORY_CATEGORY}</category>
	<copyright>{COPYRIGHT_COPYRIGHT}</copyright>
	<screenshots>
		<image>preview.jpg</image>
		<image>fullpreview.jpg</image>
	</screenshots>
	<layouts>
		<layout name='default' title='Default' default='true' />
		{LAYOUTS}
	</layouts>
</e107Theme>
TEMPLATE;

			
			$template = str_replace("{LAYOUTS}",$LAYOUTS, $template);
			
			$result = e107::getParser()->simpleParse($template, $newArray);
			$path = e_THEME.$this->themeName."/theme.xml";
			
			if(file_put_contents($path,$result))
			{
				$mes->addSuccess("Saved: ".$path);
			}
			else 
			{
				$mes->addError("Couldn't Save: ".$path);
			}
			
			$mes->addWarning("Please update your theme.php file with the data below");
			
			return  htmlentities($result);


		}
				
	
	
			function xmlInput($name, $info, $default='')
		{
			$frm = e107::getForm();	
			list($cat,$type) = explode("-",$info);
			
			$size 		= 30;
			$help		= '';
			
			switch ($info)
			{
				
				case 'main-name':
					$help 		= "The name of your theme. (Must be written in English)";
					$required 	= true;
					$pattern 	= "[A-Za-z ]*";
				break;
		
				case 'main-lang':
					$help 		= "If you have a language file, enter the LAN_XXX value for the theme's name";
					$required 	= false;
					$placeholder= " ";
					$pattern 	= "[A-Z0-9_]*";
				break;
				
				case 'main-date':
					$help 		= "Creation date of your theme";
					$required 	= true;
				break;
				
				case 'main-version':
					$default 	= '1.0';
					$required 	= true;
					$help 		= "The version of your theme. Format: x.x";
					$pattern	= "^[\d]{1,2}\.[\d]{1,2}$";
				break;

				case 'main-compatibility':
					$default 	= '2.0';
					$required 	= true;
					$help 		= "Compatible with this version of e107";
					$pattern	= "^[\d]{1,2}\.[\d]{1,2}$";
				break;
				
				case 'author-name':
					$default 	= (vartrue($default)) ? $default : USERNAME;
					$required 	= true;
					$help 		= "Author Name";
					$pattern	= "[A-Za-z \.0-9]*";
				break;
				
				case 'author-url':
					$required 	= true;
					$help 		= "Author Website Url";
				//	$pattern	= "https?://.+";
				break;
				
				//case 'main-installRequired':
				//	return "Installation required: ".$frm->radio_switch($name,'',LAN_YES, LAN_NO);
				//break;	
				
				case 'summary-summary':
					$help 		= "A short one-line description of the plugin. (!@#$%^&* characters not permitted) <br />(Must be written in English)";
					$required 	= true;
					$size 		= 100;
					$placeholder= " ";
					$pattern	= "[A-Za-z,() \.0-9]*";
				break;	
				
				case 'keywords-one':
				case 'keywords-two':
					$help 		= "Keyword/Tag for this theme<br />(Must be written in English)";
					$required 	= true;
					$size 		= 20;
					$placeholder= " ";
					$pattern 	= '^[a-z]*$';
				break;	
				
				case 'description-description':
					$help 		= "A full description of the theme<br />(Must be written in English)";
					$required 	= true;
					$size 		= 100;
					$placeholder = " ";
					$pattern	= "[A-Za-z \.0-9]*";
				break;
				
					
				case 'category-category':
					$help 		= "What category of theme is this?";
					$required 	= true;
					$size 		= 20;
				break;
						
				default:
					
				break;
			}

			$req = ($required == true) ? "&required=1" : "";	
			$placeholder = (varset($placeholder)) ? $placeholder : $type;
			$pat = ($pattern) ? "&pattern=".$pattern : "";
			
			switch ($type) 
			{
				case 'date':
					$text = $frm->datepicker($name, time(), 'dateformat=yyyy-mm-dd'.$req);		
				break;
				
				case 'description':
					$text = $frm->textarea($name,$default, 3, 100, $req);	// pattern not supported. 	
				break;
								
						
				case 'category':
										
				$allowedCategories = array(
					'generic', 'adult', 'blog', 'clan', 'children',
					'corporate', 'forum', 'gaming', 'gallery', 'news',
		 			'social', 'video', 'multimedia');	
					
				sort($allowedCategories);
				
					$text = $frm->selectbox($name, $allowedCategories,'','useValues=1&required=1', true);	
				break;
				
				
				default:
					$text = $frm->text($name, $default, $size, 'placeholder='.$placeholder . $req. $pat);	
				break;
			}
	
			
			$text .= ($help) ? "<span class='field-help'>".$help."</span>" : "";
			return $text;
			
		}		
}



?>