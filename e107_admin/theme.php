<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

require_once("../class2.php");

if (!getperms("1"))
{
	e107::redirect('admin');
	exit;
}

e107::coreLan('theme', true);

$e_sub_cat = 'theme_manage';

e107::css("inline","
.block-text h2.caption 		{ text-align: right; margin-bottom: -30px; padding-right: 10px; }
.hide						{ display: none }



");


require_once(e_HANDLER."theme_handler.php");
$themec = new themeHandler;

// print_a($_GET);

$mode = varset($_GET['mode'],'main'); // (e_QUERY) ? e_QUERY :"main" ;


if(!empty($_GET['action']))
{
	define('e_IFRAME',true);
}

if(!empty($_GET['action']))
{
	require_once("auth.php");
	switch ($_GET['action']) 
	{
		case 'login':	
			$mp = $themec->getMarketplace();	
			echo $mp->renderLoginForm();
			exit;	
		break;

		/*
		case 'download':
			$string =  base64_decode($_GET['src']);	
			parse_str($string, $p);
			$mp = $themec->getMarketplace();
			$mp->generateAuthKey($e107SiteUsername, $e107SiteUserpass);
			// Server flush useless. It's ajax ready state 4, we can't flush (sadly) before that (at least not for all browsers) 
			echo "<pre>Connecting...\n"; flush();
			// download and flush
			$mp->download($p['id'], $p['mode'], $p['type']);
			echo "</pre>"; flush();
			exit;
		break;	
		*/

		case 'info':
			if(!empty($_GET['src']))
			{
				$string =  base64_decode($_GET['src']);
				parse_str($string,$p);
				$themeInfo = e107::getSession()->get('thememanager/online/'.intval($p['id']));
				echo $themec->renderThemeInfo($themeInfo);
			}
		break;
		
		case 'preview':
			// Theme Info Ajax 
			$tm = (string) $_GET['id'];	
			$data = $themec->getThemeInfo($tm);
			echo $themec->renderThemeInfo($data);
		//	exit;
		break;

	}
/*	
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
		
		e107::getFile()->download($remotefile,'theme');
		exit;
			
	}		
*/
	// Theme Info Ajax 
	// FIXME  addd action=preview to the url, remove this block
	if(!empty($_GET['id']))
	{
		$tm = (string) $_GET['id'];
		$data = $themec->getThemeInfo($tm);
		echo $themec->renderThemeInfo($data);
	}

	require_once(e_ADMIN."footer.php");
	exit;	

}
else 
{
		require_once("auth.php");
	
	/*
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
	 */	
}				







if($mode == 'download' && !empty($_GET['src']))
{
		define('e_IFRAME', true);
		$frm = e107::getForm();
		$mes = e107::getMessage();		
		$string =  base64_decode($_GET['src']);	
		parse_str($string, $data);

		if(!empty($data['price']))
		{
			e107::getRedirect()->go($data['url']);
			return true;
		}

		if(deftrue('e_DEBUG_MARKETPLACE'))
		{
			echo "<b>DEBUG MODE ACTIVE (no downloading)</b><br />";
			echo '$_GET: ';
			print_a($_GET);

			echo 'base64 decoded and parsed as $data:';
			print_a($data);
			return false;
		}

		
		$mp = $themec->getMarketplace();	
	 	$mes->addSuccess(TPVLAN_85);   

		if($mp->download($data['id'], $data['mode'], 'theme')) // download and unzip theme.
		{
			// Auto install?
		//	$text = e107::getPlugin()->install($data['plugin_folder']); 
		//	$mes->addInfo($text); 
			echo $mes->render('default', 'success');
			e107::getTheme()->clearCache();
		}
		else
		{
			echo $mes->addError('Unable to continue')->render('default', 'error'); 
		}
		
		echo $mes->render('default', 'debug'); 
	
}
elseif(!empty($_POST['selectadmin']))
{
	$mode = "admin";
}

if(!empty($_POST['upload']))
{
	$mode = "upload";
}
elseif(!empty($_POST['selectmain']) || isset($_POST['setUploadTheme']))
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
		
		$var['online']['text'] = TPVLAN_62;
		$var['online']['link'] = e_SELF."?mode=online";

		$var['upload']['text'] = TPVLAN_38;
		$var['upload']['link'] = e_SELF."?mode=upload";
		
		$var['convert']['text'] = ADLAN_CL_6;
		$var['convert']['link'] = e_SELF."?mode=convert";

      //  $selected = (e_QUERY) ? e_QUERY : "main";
		$icon  = e107::getParser()->toIcon('e-themes-24');
		$caption = $icon."<span>".TPVLAN_26."</span>";


		e107::getNav()->admin($caption, $mode, $var);
}

class theme_builder 
{
	var $themeName = "";
	var $remove = array();
	
		function __construct()
		{

			$ns = e107::getRender();
			$tp = e107::getParser();

			e107::getMessage()->addDebug("Disable debug to save generated files. ");


			$this->themeName = $tp->filter($_GET['newtheme'],'w');

			if(!empty($_GET['src']))
			{
				$this->themeSrc = $tp->filter($_GET['src'],'w');
				$this->copyTheme();
			/*	$src = $tp->filter($_GET['src'],'w');
				$name = $tp->filter($_GET['f']);
				$title = $tp->filter($_GET['t']);

				$this->copyTheme($src,$name,$title);*/

			}


			
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
				$ret = $this->step1();
				$ret2 = $this->copyThemeForm();

				$tabs = array(
					0 => array('caption'=>$ret['caption'], 'text'=>$ret['text']),
					1 => array('caption'=>$ret2['caption'], 'text'=>$ret2['text']),

				);

				$ns->tablerender(ADLAN_140.SEP.ADLAN_CL_6,e107::getForm()->tabs($tabs));
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
			
			$mes->addInfo(' '.TPVLAN_64.' <br />
       '.TPVLAN_65.' 
            <ul> 
						<li> '.TPVLAN_66.'</li>
						<li> '.TPVLAN_67.'</li>
				</ul>
	');
			
			$text = $frm->open('createPlugin','get',e_SELF."?mode=convert");
			$text .= "<table class='table adminform'>
						<colgroup>
							<col class='col-label' />
							<col class='col-control' />
						</colgroup>
						<tbody>
				<tr>
					<td> ".TPVLAN_68."</td>
					<td>".$frm->select("newtheme",$newDir)."</td>
				</tr>";
				
				/*
				$text .= "
				<tr>
					<td>Create Files</td>
					<td>".$frm->checkbox('createFiles',1,1)."</td>
				</tr>";
				*/
				
			$text .= "</tbody>
				</table>
				<div class='buttons-bar center'>
				".$frm->admin_button('step', 2,'other',LAN_GO)."
				</div>";
			
			$text .= $frm->close();

			return array('caption'=>TPVLAN_88, 'text'=>$mes->render() . $text);
			
			$ns->tablerender(TPVLAN_26.SEP.TPVLAN_88.SEP. TPVLAN_CONV_1, $mes->render() . $text);			
			
		}	

		function step2()
		{
			$ns = e107::getRender();
			$mes = e107::getMessage();
			$frm = e107::getForm();
			
		
	
			
			$data = array(
				'main' 			=> array('name','lang','version','date', 'compatibility'),
			    'author' 		=> array('name','url'),
				'summary'		=> array('summary'),
				'description' 	=> array('description'),

				'keywords' 		=> array('one','two'),
				'category'		=> array('category'),
				'livedemo'      => array('livedemo'),
				'copyright' 	=> array('copyright'),
				'stylesheets' 	=> array('stylesheets')
		//		'adminLinks'	=> array('url','description','icon','iconSmall','primary'),
		//		'sitelinks'		=> array('url','description','icon','iconSmall')
			);			
					
			$legacyFile = e_THEME.$this->themeName."/theme.php";		



			$newThemeXML = e_THEME.$this->themeName."/theme.xml";
			if(file_exists($newThemeXML))
			{
				$info = e107::getTheme()->getThemeInfo($this->themeName);

				e107::getDebug()->log($info);

				if($this->themeSrc) // New theme copied from another
				{
					$defaults = array(
						"main-name"				=> ucfirst($this->themeName),
						'category-category'     => vartrue($info['category']),
					);
				}
				else
				{
					$defaults = array(
						"main-name"					=> vartrue($info['name']),
						"main-date"                 => vartrue($info['date']),
						"main-version"				=> vartrue($info['version']),
						"author-name"				=> vartrue($info['author']),
						"author-url"				=> vartrue($info['website']),
						"description-description"	=> vartrue($info['description']),
						"summary-summary"			=> vartrue($info['summary']),
						'category-category'         => vartrue($info['category']),
				//		"custompages"				=> vartrue($leg['CUSTOMPAGES']),
					);
				}

				if(!empty($info['keywords']['word']))
				{
					$defaults['keywords-one'] = $info['keywords']['word'][0];
					$defaults['keywords-two'] = $info['keywords']['word'][1];
				}

			}
			elseif(file_exists($legacyFile))
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
			
			$text = $frm->open('newtheme-step3','post', e_SELF.'?mode=convert&src='.$this->themeSrc.'&newtheme='.$this->themeName.'&step=3');
			$text .= "<table class='table adminlist'>";
			foreach($data as $key=>$val)
			{
				$text.= "<tr><td>$key</td><td>
				<div class='controls'>";
				foreach($val as $type)
				{
					$nm = $key.'-'.$type;
					$name = "xml[$nm]";	
					$size = (count($val)==1) ? 'col-md-7' : 'col-md-2';
					$text .= "<div class='{$size}'>".$this->xmlInput($name, $key."-". $type, vartrue($defaults[$nm]))."</div>";	
				}	
			
				$text .= "</div></td></tr>";
				
				
			}
			
			
			$text .= "</table>";
			$text .= "
			<div class='buttons-bar center'>"
			.$frm->hidden('newtheme', $this->themeName);
			$text .= $frm->hidden('xml[custompages]', trim(vartrue($leg['CUSTOMPAGES'])))
			.$frm->admin_button('step', 3,'other',LAN_GENERATE)."
			</div>";
			
			$text .= $frm->close();

		//	return array('caption'=>TPVLAN_88.SEP. TPVLAN_CONV_2, 'text'=>$mes->render() . $text);

			$ns->tablerender(TPVLAN_26.SEP.ADLAN_CL_6.SEP. TPVLAN_CONV_2, $mes->render() . $text);
		}
					
				
		function step3()
		{
			$ns = e107::getRender();
			$mes = e107::getMessage();
			
		//	print_a($_POST);
			
			if(!empty($_POST['xml']))
			{
				$xmlText =	$this->createXml($_POST['xml']);
				$ns->tablerender("theme.xml", $mes->render(). "<pre>".$xmlText."</pre>");
			}


			$legacyFile = e_THEME.$this->themeName."/theme.php";		
			if(file_exists($legacyFile) && empty($this->themeSrc))
			{
				$legacyData = file_get_contents($legacyFile);
				$legacyData = e107::getTheme()->upgradeThemeCode($legacyData);

				$output = nl2br(htmlentities($legacyData));

				$ns->tablerender("theme.php (updated)",  $output);
			}
			

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
				
			if(!empty($newArray['CUSTOMPAGES']))
			{
				$newArray['CUSTOMPAGES'] = trim($newArray['CUSTOMPAGES']);			
				$LAYOUTS = "\n<layout name='custom' title='Custom'>\n";
				$LAYOUTS .= "			<custompages>{CUSTOMPAGES}</custompages>\n";
				$LAYOUTS .= "		</layout>";
			}
			else
			{		
				$LAYOUTS = "";
			}
			
			if(!empty($newArray['STYLESHEETS_STYLESHEETS']))
			{
				$STYLESHEETS = '';
				foreach($newArray['STYLESHEETS_STYLESHEETS'] as $val)
				{
					if(empty($val['file']))
					{
						continue;
					}

					$STYLESHEETS .= "\t\t<css file=\"".$val['file']."\" name=\"".$val['name']."\" />\n";	
				}

				if(!empty($STYLESHEETS))
				{
					$STYLESHEETS = "\n\t<stylesheets>\n".$STYLESHEETS."\t</stylesheets>";
				}

				unset($newArray['STYLESHEETS_STYLESHEETS']);
			}
			else 
			{
				$STYLESHEETS = "";
			}
			
			$newArray['STYLESHEETS'] = $STYLESHEETS; 
			
		//	print_a($newArray);
			

$template = <<<TEMPLATE
<?xml version="1.0" encoding="utf-8"?>
<e107Theme name="{MAIN_NAME}" lan="{MAIN_LANG}" version="{MAIN_VERSION}" date="{MAIN_DATE}" compatibility="{MAIN_COMPATIBILITY}" livedemo="{LIVEDEMO_LIVEDEMO}">
	<author name="{AUTHOR_NAME}" url="{AUTHOR_URL}" />
	<summary lan="">{SUMMARY_SUMMARY}</summary>
	<description lan="">{DESCRIPTION_DESCRIPTION}</description>
	<category>{CATEGORY_CATEGORY}</category>
	<keywords>
		<word>{KEYWORDS_ONE}</word>
		<word>{KEYWORDS_TWO}</word>
	</keywords>
	<copyright>{COPYRIGHT_COPYRIGHT}</copyright>
	<screenshots>
		<image>preview.jpg</image>
		<image>fullpreview.jpg</image>
	</screenshots>{STYLESHEETS}
	<layouts>
		<layout name='default' title='Default' default='true' />{LAYOUTS}	
	</layouts>
</e107Theme>
TEMPLATE;

			
			$template = str_replace("{LAYOUTS}",$LAYOUTS, $template);
			
			$result = e107::getParser()->simpleParse($template, $newArray);
			$path = e_THEME.$this->themeName."/theme.xml";
			
			
			if(E107_DEBUG_LEVEL > 0)
			{
				$mes->addDebug("Debug Mode active - no file saved. ");
				return  htmlentities($result);	
			}
			
			
			
			if(file_put_contents($path,$result))
			{
				$mes->addSuccess("Saved: ".$path);
			}
			else 
			{
				$mes->addError("Couldn't Save: ".$path);
			}
			

			
			return  htmlentities($result);


		}
				
	
	
		function xmlInput($name, $info, $default='')
		{
			$frm = e107::getForm();	
			list($cat,$type) = explode("-",$info);
			
			$size 		= 30;
			$help		= '';
			$sizex      = '';

			switch ($info)
			{
				
				case 'main-name':
					$help 		= TPVLAN_CONV_3;
					$required 	= true;
					$pattern 	= "[A-Za-z 0-9]*";
				break;
		
				case 'main-lang':
					$help 		= TPVLAN_CONV_4;
					$required 	= false;
					$placeholder= "LAN equivalent";
					$pattern 	= "[A-Z0-9_]*";
				break;
				
				case 'main-date':
					$help 		= TPVLAN_CONV_6;
					$required 	= true;
					$default = (empty($default)) ? time() : strtotime($default);
				break;
				
				case 'main-version':
					$default 	= '1.0';
					$required 	= true;
					$help 		= TPVLAN_CONV_5;
					$pattern	= "^[\d]{1,2}\.[\d]{1,2}$";
				break;

				case 'main-compatibility':
					$default 	= '2.0';
					$required 	= true;
					$help 		= TPVLAN_CONV_7;
					$pattern	= "^[\d]{1,2}\.[\d]{1,2}$";
				break;
				
				case 'author-name':
					$default 	= (vartrue($default)) ? $default : USERNAME;
					$required 	= true;
					$help 		= TPVLAN_CONV_8;
					$pattern	= "[A-Za-z \.0-9]*";
				break;
				
				case 'author-url':
					$required 	= true;
					$help 		= TPVLAN_CONV_9;
				//	$pattern	= "https?://.+";
				break;

				case 'livedemo-livedemo':
					$required 	= false;
					$help 		= TPVLAN_CONV_16;
					$placeholder= "http://demo-of-my-theme.com";
				//	$pattern	= "https?://.+";
				break;
				
				//case 'main-installRequired':
				//	return "Installation required: ".$frm->radio_switch($name,'',LAN_YES, LAN_NO);
				//break;	
				
				case 'summary-summary':
					$help 		= TPVLAN_CONV_10;
					$required 	= true;
					$size 		= 200;
					$placeholder= " ";
					$pattern	= "[A-Za-z,() \.0-9]*";
				break;	
				
				case 'keywords-one':
				case 'keywords-two':
					$help 		= TPVLAN_CONV_11;
					$required 	= true;
					$size 		= 20;
					$placeholder= " ";
					$pattern 	= '^[a-z]*$';
				break;	
				
				case 'description-description':
					$help 		= TPVLAN_CONV_12;
					$required 	= true;
					$size 		= 100;
					$placeholder = " ";
					$pattern	= "[A-Za-z \.0-9]*";
				break;
				
					
				case 'category-category':
					$help 		= TPVLAN_CONV_13;
					$required 	= true;
					$size 		= 20;
				break;
						
				default:
					
				break;
			}

			$req = ($required == true) ? "&required=1" : "";	
			$placeholder = (varset($placeholder)) ? $placeholder : $type;
			$pat = ($pattern) ? "&pattern=".$pattern : "";
			$text = '';

			switch ($type) 
			{
				
				case 'stylesheets':
					$fl = e107::getFile();
			
					$fl->setMode('full');
					$stylesheets = $fl->get_files(e_THEME.$this->themeName."/", "\.css", null, 1);
					foreach($stylesheets as $key=>$path)
					{
						$file = str_replace(e_THEME.$this->themeName."/",'',$path);
						$text .= "<div class='row-fluid'>";
						$text .= "<div class='controls'>";
						$text .= "<div class='col-md-3'>".$frm->checkbox($name.'['.$key.'][file]',$file, false, array('label'=>$file))."
						<div class='field-help'>".TPVLAN_CONV_14."</div></div>";
						$text .= "<div class='col-md-3'>".$frm->text($name.'['.$key.'][name]', $default, $size, 'placeholder='.$file . $req. $pat)."
						<div class='field-help'>".TPVLAN_CONV_15."</div></div>";
					//	$text .= "<div class='span2'>".$frm->checkbox('css['.$key.'][file]',$file, false, array('label'=>$file))."</div>";
					//	$text .= "<div class='span2'>".$frm->text('css['.$key.'][name]', $default, $size, 'placeholder='.$placeholder . $req. $pat)."</div>";	
						$text .= "</div>";
						$text .= "</div>";
					}
						
					
					return $text;
				break;
				
				
				case 'date':
					$text = $frm->datepicker($name, $default, 'format=yyyy-mm-dd'.$req.'&size=block-level');
				break;
				
				case 'description':
					$text = $frm->textarea($name,$default, 3, 100, $req.'&size=block-level');	// pattern not supported.
				break;
								
						
				case 'category':
										
				$allowedCategories = array(
					'generic', 'adult', 'blog', 'clan', 'children',
					'corporate', 'forum', 'gaming', 'gallery', 'news',
		 			'social', 'video', 'multimedia');	
					
				sort($allowedCategories);
				
					$text = $frm->select($name, $allowedCategories,$default,'useValues=1&required=1', true);
				break;
				
				
				default:
					$text = $frm->text($name, $default, $size, 'placeholder='.$placeholder . $req. $pat.'&size=block-level');
				break;
			}
	
			
			$text .= ($help) ? "<span class='field-help'>".$help."</span>" : "";
			return $text;
			
		}





		function copyThemeForm()
		{

			$frm = e107::getForm();

			$list = e107::getTheme()->clearCache()->getThemeList(); // (e_THEME);

			$folders = array_keys($list);

			$text = $frm->open('copytheme','get','theme.php?mode=convert');
			$text .= "<table class='table adminform'>
						<colgroup>
							<col class='col-label' />
							<col class='col-control' />
						</colgroup>
				<tr>
					<td>".TPVLAN_91."</td>
					<td>".$frm->select("src",$folders,'',array('useValues'=>1))."</td>
				</tr>

				<tr>
					<td>".TPVLAN_92."</td>
					<td>".$frm->text("newtheme",'',25, array('pattern'=>'[a-z_0-9]*', 'required'=>1))."</td>
				</tr>

				";

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
				".$frm->admin_button('step', 2,'success', LAN_CREATE)."
				</div>";




				$text .= $frm->close();


		//	$text = "Create a new theme based on ".e->select('copytheme',$list);


			return array('caption'=>LAN_CREATE, 'text'=>$text);

		}

		private function copyTheme()
		{
			if(empty($this->themeSrc) || empty($this->themeName) || is_dir(e_THEME.$this->themeName))
			{
				return false;
			}

			if(e107::getFile()->copy(e_THEME.$this->themeSrc, e_THEME.$this->themeName))
			{
				$newfiles = scandir(e_THEME.$this->themeName);

				foreach($newfiles as $file)
				{
					if(is_dir(e_THEME.$this->themeName.'/'.$file) || $file === '.' || $file === '..')
					{
						continue;
					}

					if(strpos($file,"admin_") === 0)
					{
						unlink(e_THEME.$this->themeName.'/'.$file);
					}



				}

			}

		}




}



?>
