<?php

//define ('PDF_DEBUG', TRUE);
define ('PDF_DEBUG', FALSE);

//extend fpdf class from package with custom functions
class e107PDF extends UFPDF{

	var $temp_counter = 2;			// Used for debug
	
	//variables of html parser
	var $B;
	var $I;
	var $U;
	var $HREF;
	var $CENTER='';
	var $ALIGN='';
	var $IMG;
	var $SRC;
	var $WIDTH;
	var $HEIGHT;
	var $fontList;
	var $issetfont;
	var $issetcolor;
	var $iminfo=array(0,0);

	function e107PDF($orientation='P',$unit='mm',$format='A4'){
		global $pdfpref;
		//Call parent constructor
		$this->UFPDF($orientation,$unit,$format);
		//Initialization
		$this->B=0;
		$this->I=0;
		$this->U=0;
		$this->BLOCKQUOTE='';
		$this->HREF='';
		$this->CENTER='';
		$this->ALIGN='';
		$this->IMG='';
		$this->SRC='';
		$this->WIDTH='';
		$this->HEIGHT='';
		$this->fontlist=array("arial","times","courier","helvetica","symbol");

		$this->issetfont=false;
		$this->issetcolor=false;
	}

	//default preferences if none present
	function getDefaultPDFPrefs(){
			$pdfpref['pdf_margin_left']				= '25';
			$pdfpref['pdf_margin_right']			= '15';
			$pdfpref['pdf_margin_top']				= '15';
			$pdfpref['pdf_font_family']				= 'arial';
			$pdfpref['pdf_font_size']				= '8';
			$pdfpref['pdf_font_size_sitename']		= '14';
			$pdfpref['pdf_font_size_page_url']		= '8';
			$pdfpref['pdf_font_size_page_number']	= '8';
			$pdfpref['pdf_show_logo']				= true;
			$pdfpref['pdf_show_sitename']			= false;
			$pdfpref['pdf_show_page_url']			= true;
			$pdfpref['pdf_show_page_number']		= true;
			$pdfpref['pdf_error_reporting']			= true;
			return $pdfpref;
	}
	//get preferences from db
	function getPDFPrefs(){
		global $sql, $eArrayStorage;

		if(!is_object($eArrayStorage)){
			e107_require_once(e_HANDLER.'arraystorage_class.php');
			$eArrayStorage = new ArrayData();
		}

		if(!is_object($sql)){ $sql = new db; }
		$num_rows = $sql -> db_Select("core", "*", "e107_name='pdf' ");
		if($num_rows == 0){
			$tmp = $this->getDefaultPDFPrefs();
			$tmp2 = $eArrayStorage->WriteArray($tmp);
			$sql -> db_Insert("core", "'pdf', '".$tmp2."' ");
			$sql -> db_Select("core", "*", "e107_name='pdf' ");
		}
		$row = $sql -> db_Fetch();
		$pdfpref = $eArrayStorage->ReadArray($row['e107_value']);
		return $pdfpref;
	}

	function toPDF($text){
		$search = array('&#39;', '&#039;', '&#036;', '&quot;');
		$replace = array("'", "'", '$', '"');
		$text = str_replace($search, $replace, $text);
		return $text;
	}

	function toPDFTitle($text){
		$search = array(":", "*", "?", '"', '<', '>', '|');
		$replace = array('-', '-', '-', '-', '-', '-', '-');
		$text = str_replace($search, $replace, $text);
		return $text;
	}

	/*
	The makePDF function does all the real parsing and composing
	input argument $text needs to be an array containing the following:
	$text = array($text, $creator, $author, $title, $subject, $keywords, $url);
	*/
	function makePDF($text){
		global $tp, $pdfpref;

		//call get preferences
		$pdfpref = $this->getPDFPrefs();

		//define logo and source pageurl (before the parser!)
		if(is_readable(THEME."images/logopdf.png")){
			$logo = THEME."images/logopdf.png";
		}else{
			$logo = e_IMAGE."logo.png";
		}
		define('PDFLOGO', $logo);					//define logo to add in header
		define('PDFPAGEURL', $text[6]);				//define page url to add in header

		//parse the data
		$text[3] = $this->toPDF($text[3]);					//replace some in the title
		$text[3] = $this->toPDFTitle($text[3]);			//replace some in the title
		foreach($text as $k=>$v){
			$text[$k] = $tp->toHTML($v, TRUE);
		}

		//set some variables
		$this->SetMargins($pdfpref['pdf_margin_left'],$pdfpref['pdf_margin_top'],$pdfpref['pdf_margin_right']);
		//$this->SetAutoPageBreak(true,25);

		//start creating the pdf and adding the data
		$this->AliasNbPages();						//calculate current page + number of pages
		$this->AddPage();							//start page
		$this->SetFont($pdfpref['pdf_font_family'],'',$pdfpref['pdf_font_size']);				//set font
		$this->WriteHTML($text[0], true);			//write text
		$this->SetCreator($text[1]);				//name of creator
		$this->SetAuthor($text[2]);					//name of author
		$this->SetTitle($text[3]);					//title
		$this->SetSubject($text[4]);				//subject
		$this->SetKeywords($text[5]);				//space/comma separated
		$file = $text[3].".pdf";					//name of the file
		$this->Output($file, 'D');					//Save PDF to file (D = output to download window)
		return;
	}


	//create a header; this will be added on each page
	function Header(){
		global $pdfpref;

		$this->SetY(15);
		$y0 = $this->GetY();
		if($pdfpref['pdf_show_logo']){
			$this->SetFont($pdfpref['pdf_font_family'],'',$pdfpref['pdf_font_size']);
			$this->PutImage(PDFLOGO, '1');
			$x1 = $this->GetX();
			$y1 = $this->GetY();

			$image_wh = getimagesize(PDFLOGO);
			$newx = $x1 + ($image_wh[0]/$this->k);
			$newy = ($image_wh[1]/$this->k);

			$a=$this->GetStringWidth(SITENAME);
			$b=$this->GetStringWidth(PDFPAGEURL);
			if($a>$b){$c=$a;}else{$c=$b;}
			if($x1+$newx+$c > 210){
				$this->SetX($this->lMargin);
				$this->SetY($y1+2);
			}else{
				if($pdfpref['pdf_show_sitename']){
					$m = 5;
				}
				if($pdfpref['pdf_show_page_url']){
					$m += 5;
				}
				if($pdfpref['pdf_show_page_number']){
					$m += 5;
				}
				$y = $this->GetY();
				$this->SetY($y-$m);
			}
		}
		$cellwidth	= 210-$this->lMargin-$this->rMargin;
		$align		= "R";
		if($pdfpref['pdf_show_sitename']){
			$this->SetFont($pdfpref['pdf_font_family'],'B',$pdfpref['pdf_font_size_sitename']);
			$this->Cell($cellwidth,5,SITENAME,0,1,$align);
		}
		if($pdfpref['pdf_show_page_url']){
			$this->SetFont($pdfpref['pdf_font_family'],'I',$pdfpref['pdf_font_size_page_url']);
			$this->Cell($cellwidth,5,PDFPAGEURL,0,1,$align,'',PDFPAGEURL);
		}
		if($pdfpref['pdf_show_page_number']){
			$this->SetFont($pdfpref['pdf_font_family'],'I',$pdfpref['pdf_font_size_page_number']);
			$this->Cell($cellwidth,5,PDF_LAN_19.' '.$this->PageNo().'/{nb}',0,1,$align);
		}
		$y = $this->GetY()+2;
		$this->Line($this->lMargin, $y, 210-$this->rMargin, $y);
		$this->Ln(10);
		$this->SetX($this->lMargin);
		$this->SetFont($pdfpref['pdf_font_family'],'',$pdfpref['pdf_font_size']);
	}

	function txtentities($html){
		$html = str_replace("\r\n", "\\n", $html);
		$html = str_replace("\r", "", $html);
		$trans_tbl = get_html_translation_table (HTML_ENTITIES);
		$trans_tbl = array_flip ($trans_tbl);
		return strtr ($html, $trans_tbl);
	}

	//function hex2dec
	//returns an associative array (keys: R,G,B) from
	//a hex html code (e.g. #3FE5AA)
	function hex2dec($couleur = "#000000"){
		$R = substr($couleur, 1, 2);
		$rouge = hexdec($R);
		$V = substr($couleur, 3, 2);
		$vert = hexdec($V);
		$B = substr($couleur, 5, 2);
		$bleu = hexdec($B);
		$tbl_couleur = array();
		$tbl_couleur['R']=$rouge;
		$tbl_couleur['G']=$vert;
		$tbl_couleur['B']=$bleu;
		return $tbl_couleur;
	}

	function WriteHTML($html,$scale)
	{
	  global $tp, $pdfpref;
	  global $admin_log;

		$search		= array("\n", "<br />", "<hr />", '&raquo;', '&ordm;', '&middot', '&trade;', '&copy;', '&euro;', '&#091;', '&amp;#091;', '&nbsp;', 'â€˜', 'â€™', ' />', '&#40;', '&#41;', '&#123;', '&#125;', '&#91;', '&#93;', '&#092;', '&#92;');
		$replace	= array(" ", "<br>", "<hr>", '»', 'º', '·', '™', '©', '', '[', '[', ' ', "'", "'", '>', '(', ')', '{', '}', '[',']', '\\\\', '\\\\' );
		//replace carriage returns by spaces, and some html variants
		$html=str_replace($search, $replace, $html);
		$a=preg_split('/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE); //explodes the string

	if (PDF_DEBUG) 
	{
	  $admin_log->e_log_event(10,'split_vars',"DEBUG","PDF Trace","Write text: : ".$tp->toHTML('[code]'.$html.'[/code]',TRUE),FALSE,LOG_TO_ROLLING);
	  $acc = array();
	  foreach ($a as $ef) { $acc[] = strlen($ef); }
	  $admin_log->e_log_event(10,'no_vars',"DEBUG","PDF Trace","Lengths:: ".implode(',',$acc),FALSE,LOG_TO_ROLLING);
	}
		foreach($a as $i=>$e)
		{
		  if ($this->temp_counter == 0)
		  {
//	if (PDF_DEBUG) $admin_log->e_log_event(10,'no_vars',"DEBUG","PDF Trace","Process chunk {$i}: ".$e,FALSE,LOG_TO_ROLLING);
		  }
			if($i%2==0)
			{
				//Text between tags
				if($this->HREF){
					$this->PutLink($this->HREF,$e);
					$this->HREF='';
				}elseif(0 && $this->IMG){		// This bit shouldn't happen now
					//correct url
					if(is_readable($this->SRC)){
						$file = trim($this->SRC);
						$pos=strrpos($file,'.');
						$type=substr($file,$pos+1);
						$type=strtolower($type);
						//for now only jpg, jpeg and png are supported
						if($type=='jpg' || $type=='jpeg' || $type=='png')
						{
						  if ((strpos($file,'http') !== 0) && (strpos($file,'www.') !== 0))
						  {  // Its a local file - possibly don't need to do anything at all!
						    $url = $tp->replaceConstants($file);
						  }
/*							Old path-related stuff confused things
							$url = str_replace("../", "", $this->SRC);
							$imgsearch = array(e_IMAGE, e_THEME, e_PLUGIN, e_FILE, e_HANDLER);
							//e_BASE and e_ADMIN are not taken into account !
							foreach($imgsearch as $p){
								$p = str_replace("../", "", $p);
								$l = strpos($url, $p);
								if ($l !== false) {
									$url = SITEURL.$url;
								}
							}  */
							$this->Ln();		// Newline with 'default' height to avoid overlaying text
							$this->PutImage($url,$scale);
							$this->Ln(2);
							$this->SetX($this->lMargin);
						}
					}
					$this->IMG='';
					$this->SRC='';
					$this->WIDTH='';
					$this->HEIGHT='';

				}elseif($this->CENTER){
					$this->Cell(0,5,$e,0,1,'C');
				}elseif($this->ALIGN == 'center'){
					$this->Cell(0,5,$e,0,1,'C');
				}elseif($this->ALIGN == 'right'){
					$this->Cell(0,5,$e,0,1,'R');
				}elseif($this->ALIGN == 'left'){
					$this->Cell(0,5,$e,0,1,'L');
				}
				elseif($this->BLOCKQUOTE == 'BLOCKQUOTE')
				{
					$this->SetFont('Courier','',11);
					$this->SetStyle('B',true);
					$this->SetStyle('I',true);
					$this->Cell(0,5,$e,1,1,'L');
					$this->SetStyle('B',false);
					$this->SetStyle('I',false);
					if ($this->issetcolor==true) 
					{
						$this->SetTextColor(0);
						$this->issetcolor=false;
					}
					$this->SetFont($pdfpref['pdf_font_family'],'',$pdfpref['pdf_font_size']);
				}
				else
				{
				  //	if (PDF_DEBUG) $admin_log->e_log_event(10,debug_backtrace(),"DEBUG","PDF Trace","Write block {$i}: ".$e,FALSE,LOG_TO_ROLLING);
					$this->Write(5,stripslashes($this->txtentities($e)));
				}
			}
			else
			{
				//Tag
				if($e{0}=='/'){
					$this->CloseTag(strtoupper(substr($e,1)));
				}else{
					//Extract attributes
					$a2=explode(' ',$e);
					$tag=strtoupper(array_shift($a2));
					$attr=array();
					foreach($a2 as $v){
						if(ereg('^([^=]*)=["\']?([^"\']*)["\']?$',$v,$a3)){
							$attr[strtoupper($a3[1])]=$a3[2];
						}
					}
					$this->OpenTag($tag,$attr,$scale);
				}
			}
		}
	}

	function OpenTag($tag,$attr,$scale)
	{
	  global $tp;
	  global $admin_log;
	  
		$tag = strtoupper($tag);
		//Opening tag

		switch($tag)
		{
			case 'STRONG':
				$this->SetStyle('B',true);
				break;
			case 'EM':
				$this->SetStyle('I',true);
				break;
			case 'B':
			case 'I':
			case 'U':
				$this->SetStyle($tag,true);
				break;
			case 'A':
				$this->HREF=$attr['HREF'];
				break;
			case 'P':
				$this->ALIGN=$attr['ALIGN'];
				break;
			case 'SPAN':
				if(isset($attr['STYLE'])){
					if($attr['STYLE'] == 'text-decoration:underline'){
						$this->SetStyle('U',true);
					}
					if(strstr($attr['STYLE'], 'color:')){
						$attr['COLOR'] = substr($attr['STYLE'],6);
						$coul=$this->hex2dec($attr['COLOR']);
						$this->SetTextColor($coul['R'],$coul['G'],$coul['B']);
						$this->issetcolor=true;
					}
					if(strstr($attr['STYLE'], 'font-size:')){
						$attr['FONTSIZE'] = intval(substr($attr['STYLE'],10));
						$this->SetFont('','',$attr['FONTSIZE']);
						$this->issetfont=true;
					}
					break;
				}
			case 'DIV':
				if($attr['STYLE'] == 'text-align:center'){
					$this->ALIGN='center';
				}
				if($attr['STYLE'] == 'text-align:left'){
					$this->ALIGN='left';
				}
				if($attr['STYLE'] == 'text-align:right'){
					$this->ALIGN='right';
				}
				if($attr['CLASS'] == 'indent'){
					// $this->BLOCKQUOTE='BLOCKQUOTE';
				}
				break;
			case 'IMG':
				if (PDF_DEBUG) $admin_log->e_log_event(10,debug_backtrace(),"DEBUG","PDF Trace","Image tag found: ".$attr['SRC'],FALSE,LOG_TO_ROLLING);
				$this->IMG=true;
				$this->SRC=$attr['SRC'];
				$this->WIDTH=$attr['WIDTH'];
				$this->HEIGHT=$attr['HEIGHT'];
				// Its a 'closed' tag - so need to process it immediately
				
				if(is_readable($this->SRC))
				{
				  $file = trim($this->SRC);
				  $pos=strrpos($file,'.');
				  $type=substr($file,$pos+1);
				  $type=strtolower($type);
					//for now only jpg, jpeg and png are supported
				  if($type=='jpg' || $type=='jpeg' || $type=='png')
				  {
					if ((strpos($file,'http') !== 0) && (strpos($file,'www.') !== 0))
					{  // Its a local file - possibly don't need to do anything at all!
					  $url = $tp->replaceConstants($file);
					}
					$this->Ln();		// Newline with 'default' height to avoid overlaying text
					$this->PutImage($url,$scale);
					$this->Ln(2);
					$this->SetX($this->lMargin);
				  }
				}
				$this->IMG='';		// Clear the parameters - stops further image-related processing
				$this->SRC='';
				$this->WIDTH='';
				$this->HEIGHT='';
				break;
			case 'TR':
				break;
			case 'TD':
				break;
			case 'CODE':
			case 'BLOCKQUOTE':
			case 'PRE':
                $this->Ln(5);
				$this->SetFont('Courier','',11);
				$this->issetcolor=true;
				$this->issetfont=true;
                $this->SetStyle('B',true);
                $this->SetStyle('I',true);
                break;
			case 'LI':
                $this->Write(5,'     » ');
                break;
			case 'BR':
				$this->Ln(5);
				break;
			case 'HR':
				if( $attr['WIDTH'] != '' ) $Width = $attr['WIDTH'];
				else $Width = $this->w - $this->lMargin-$this->rMargin;
				$this->Ln(2);
				$x = $this->GetX();
				$y = $this->GetY();
				$this->SetLineWidth(0.4);
				$this->Line($x,$y,$x+$Width,$y);
				$this->SetLineWidth(0.2);
				$this->Ln(2);
				break;
			case 'FONT':
				if (isset($attr['COLOR']) && $attr['COLOR']!='') {
					$coul=$this->hex2dec($attr['COLOR']);
					$this->SetTextColor($coul['R'],$coul['G'],$coul['B']);
					$this->issetcolor=true;
				}
				if (isset($attr['FACE']) && in_array(strtolower($attr['FACE']), $this->fontlist)) {
					$this->SetFont(strtolower($attr['FACE']));
					$this->issetfont=true;
				}
				break;
			case 'H1':
                $this->Ln(5);
                $this->SetFontSize(22);
				$this->issetfont=true;
                break;
            case 'H2':
                $this->Ln(5);
                $this->SetFontSize(18);
				$this->issetfont=true;
                $this->SetStyle('U',true);
                break;
            case 'H3':
                $this->Ln(5);
                $this->SetFontSize(16);
				$this->issetfont=true;
                $this->SetStyle('U',true);
                break;
            case 'H4':
                $this->Ln(5);
                $this->SetFontSize(14);
				$this->issetfont=true;
				$this->SetStyle('B',true);
                break;

		}
	}

	function CloseTag($tag){
		global $pdfpref;

		if ($this->issetcolor==true) {
			$this->SetTextColor(0);
		}
		if ($this->issetfont==true) {
			$this->SetFont($pdfpref['pdf_font_family'],'',$pdfpref['pdf_font_size']);
			$this->issetfont=false;
		}

		 $tag = strtoupper($tag);
		 //Closing tag
		if($tag=='SPAN'){
			$tag='U';
			if ($this->issetcolor==true) {
				$this->SetTextColor(0);
			}
			if ($this->issetfont==true) {
				$this->SetFont($pdfpref['pdf_font_family'],'',$pdfpref['pdf_font_size']);
				$this->issetfont=false;
			}
		}
		if($tag=='DIV'){
			$tag='DIV';
			$this->ALIGN='';
			$this->BLOCKQUOTE='';
		}
		if($tag=='STRONG'){
			$tag='B';
		}
		if($tag=='EM'){
			$tag='I';
		}
		if($tag=='B' or $tag=='I' or $tag=='U'){
			$this->SetStyle($tag,false);
		}
		if($tag=='A'){
			$this->HREF='';
		}
		if($tag=='P'){
			$this->ALIGN='';
		}
		if($tag=='IMG'){
			$this->IMG='';
			$this->SRC='';
			$this->WIDTH='';
			$this->HEIGHT='';
		}
		if($tag=='LI'){
			$this->Ln(5);
		}
		if($tag=='TD'){
			$this->Write(5,'    ');
		}
		if($tag=='TR' || $tag=='BLOCKQUOTE' || $tag=='CODE' || $tag=='PRE'){
			$this->SetStyle('B',false);
			$this->SetStyle('I',false);
			$this->Ln(5);
			if ($this->issetcolor==true) {
				$this->SetTextColor(0);
				$this->issetcolor=false;
			}
			if ($this->issetfont==true) {
				$this->SetFont($pdfpref['pdf_font_family'],'',$pdfpref['pdf_font_size']);
				$this->issetfont=false;
			}
        }
		if($tag=='FONT'){
			if ($this->issetcolor==true) {
				$this->SetTextColor(0);
				$this->issetcolor=false;
			}
			if ($this->issetfont==true) {
				$this->SetFont($pdfpref['pdf_font_family'],'',$pdfpref['pdf_font_size']);
				$this->issetfont=false;
			}
		}
		if ($tag=='H1' || $tag=='H2' || $tag=='H3' || $tag=='H4'){
			$this->H1='';
			$this->H2='';
			$this->H3='';
			$this->H4='';
			$this->SetStyle('B',false);
			$this->SetStyle('U',false);
			$this->Ln(5);
			if($this->issetfont==true){
				$this->SetFont($pdfpref['pdf_font_family'],'',$pdfpref['pdf_font_size']);
				$this->issetfont=false;
			}
        }
	}

	function SetStyle($tag,$enable){
		//Modify style and select corresponding font
		$this->$tag+=($enable ? 1 : -1);
		$style='';
		foreach(array('B','I','U') as $s)
			if($this->$s>0)
				$style.=$s;
		$this->SetFont('',$style);
	}

	function PutLink($URL,$txt){
		//remove leading 'http://'
		if(strpos($URL, "http://")!==false){
			$URL = substr($URL, strpos($URL, "http://")+strlen('http://') );
		}
		//Put a hyperlink
		$this->SetTextColor(0,0,255);
		$this->SetStyle('U',true);
		$this->Write(5,$txt,$URL);
		$this->SetStyle('U',false);
		$this->SetTextColor(0);
	}

	function px2mm($px){
		return $px*25.4/72;
	}

	//put the image in pdf with scaling...
	//width and height-options inside the IMG-Tag are ignored,
	//we get the image info directly from PHP...
	//$scale is the global scaling factor, passing through from WriteHTML()
	//(c)2004/03/12 by St@neCold
	function PutImage($url,$scale)
	{
	  global $admin_log;
	if (PDF_DEBUG) $admin_log->e_log_event(10,debug_backtrace(),"DEBUG","PDF Trace","Process image {$url}, scale ".$scale,FALSE,LOG_TO_ROLLING);
	
		if($scale<0) $scale=0;
		//$scale<=0: put NO image inside the pdf!
		if($scale>0)
		{
			$xsflag=0;
			$ysflag=0;
			$yhflag=0;
			$xscale=1;
			$yscale=1;
			//get image info
			$oposy=$this->GetY();
			$iminfo=@getimagesize($url);
			if($iminfo)
			{
				// Width and height of current drawing page
				$pw = $this->w - $this->lMargin - $this->rMargin;
				$ph = $this->h - $this->tMargin - $this->bMargin;
				
				$iw=$scale * $this->px2mm($iminfo[0]);
				$ih=$scale * $this->px2mm($iminfo[1]);
				$iw = ($iw)?$iw:1;		// Initial width
				$ih = ($ih)?$ih:1;		// Initial height
				$nw=$iw;				// New width
				$nh=$ih;				// New height
				//resizing in x-direction
				$xsflag=0;
//				if($iw>150)			// Dimensions in mm - so width of portrait A4
				if($iw>$pw)			// Dimensions in mm - so width of portrait A4
				{
//					$xscale=150 / $iw;
					$xscale=$pw / $iw;
					$yscale=$xscale;
					$nw=$xscale * $iw;
					$nh=$xscale * $ih;
					$xsflag=1;
				}
				//now eventually resizing in y-direction
				$ysflag=0;
//				if(($oposy+$nh)>250)	// See if will fit vertically on current page
				if(($oposy+$nh)>$ph)	// See if will fit vertically on current page
				{
//					$yscale=(250-$oposy)/$ih;
					$yscale=($ph-$oposy)/$ih;
					$nw=$yscale * $iw;
					$nh=$yscale * $ih;
					$ysflag=1;
				}
				//uups, if the scaling factor of resized image is < 0.33
				//remark: without(!) the global factor $scale!
				//that's hard -> on the next page please...
				$yhflag=0;
				if($yscale<0.33 and ($xsflag==1 or $ysflag==1))	
				{
					$nw=$xscale * $iw;
					$nh=$xscale * $ih;
					$ysflag==0;
					$xsflag==1;
					$yhflag=1;
				}
				if($yhflag==1) $this->AddPage();
				$oposy=$this->GetY();
				$this->Image($url, $this->GetX(), $this->GetY(), $nw, $nh);
				$this->SetY($oposy+$nh);
//	if (PDF_DEBUG) $admin_log->e_log_event(10,debug_backtrace(),"DEBUG","PDF Trace","Original Y={$oposy}, Initial height={$ih}, new height={$nh}. Set Y = ".($oposy+$nh)." after image output",FALSE,LOG_TO_ROLLING);
	if (PDF_DEBUG) $admin_log->e_log_event(10,debug_backtrace(),"DEBUG","PDF Trace","Page width: {$pw}, Height: {$ph}, Image new width={$nw}, new height={$nh}. Set Y = ".($oposy+$nh)." after image output",FALSE,LOG_TO_ROLLING);
/*
Original Y=76.166666666667, Initial height=119.23888888889, new height=101.4. Set Y = 177.56666666667 after image output */
				//if($yhflag==0 and $ysflag==1) $this->AddPage();
				if ($this->temp_counter > 0) $this->temp_counter--;
			}
		}
	}

}

?>
