<?php

//extend fpdf class from package with custom functions
class e107PDF extends UFPDF{
	
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
		//Call parent constructor
		$this->UFPDF($orientation,$unit,$format);
		//Initialization
		$this->B=0;
		$this->I=0;
		$this->U=0;
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

	/*
	The makePDF function does all the real parsing and composing
	input argument $text needs to be an array containing the following:
	$text = array($text, $creator, $author, $title, $subject, $keywords, $url);
	*/
	function makePDF($text){
		global $tp;

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

		//parse the data
		$text[3] = toPDF($text[3]);					//replace some in the title
		$text[3] = toPDFTitle($text[3]);			//replace some in the title
		foreach($text as $k=>$v){
			$text[$k] = $tp->toHTML($v, TRUE);
		}

		//set some variables
		$margin['left']		= "25";
		$margin['right']	= "15";
		$margin['top']		= "15";
		$this->SetMargins($margin['left'],$margin['top'],$margin['right']);
		//$this->SetAutoPageBreak(true,25);

		//start creating the pdf and adding the data
		$this->AliasNbPages();						//calculate current page + number of pages
		$this->AddPage();							//start page
		$this->SetFont('Arial','',10);				//set font
		$this->WriteHTML($text[0], true);			//write text
		$this->SetCreator($text[1]);				//name of creator
		$this->SetAuthor($text[2]);					//name of author
		$this->SetTitle($text[3]);					//title
		$this->SetSubject($text[4]);				//subject
		$this->SetKeywords($text[5]);				//space/comma seperated
		$file = $text[3].".pdf";					//name of the file
		$this->Output($file, 'D');					//Save PDF to file (D = output to download window)
		return;
	}


	//create a header; this will be added on each page
	function Header(){
		/*
		$this->SetY(15);
		$this->SetFont('Arial','I',8);			
		$this->Image(CONTENTPDFLOGO, 10, 15, 0, 0, '', '');
		$y = $this->GetY();
		$x = $this->GetX();			
		$image_wh = getimagesize(CONTENTPDFLOGO);
		$newx = $x + ($image_wh[0]/$this->k);
		$newy = ($image_wh[1]/$this->k);
		$this->SetX($newx);
		$x = $this->GetX();
		$cellwidth = 210-10-$x;
		$this->SetFont('Arial','B',14);
		$this->Cell($cellwidth,8,SITENAME,0,2,'R');
		$this->SetFont('Arial','I',8);
		$this->Cell($cellwidth,8,CONTENTPDFPAGEURL,0,2,'R');
		$this->Cell($cellwidth,10,'Page '.$this->PageNo().'/{nb}',0,2,'R');
		$this->Line(10, $newy+20, 200, $newy+20);
		$this->Ln(20);
		*/
		
		$cellwidth	= 0;
		$align		= "L";
		
		$this->SetFont('Arial','B',14);
		$this->Cell($cellwidth,8,SITENAME,0,2,$align);
		$this->SetFont('Arial','I',8);
		$this->Cell($cellwidth,8,CONTENTPDFPAGEURL,0,2,$align);
		$this->Cell($cellwidth,8,'Page '.$this->PageNo().'/{nb}',0,2,$align);
		$x = $this->GetX();
		$y = $this->GetY();
		$this->Line($this->lMargin, $y, 210-$this->rMargin, $y);
		$this->Ln(20);
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

	function WriteHTML($html,$scale){
		$html=str_replace("<br />",'<br>',$html);
		$html=str_replace("\n",' ',$html); //replace carriage returns by spaces
		$a=preg_split('/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE); //explodes the string

		foreach($a as $i=>$e)
		{
			if($i%2==0)
			{
				//Text
				if($this->HREF)
					$this->PutLink($this->HREF,$e);
				elseif($this->IMG)
					$this->PutImage($this->SRC,$scale);
				elseif($this->CENTER)
					$this->Cell(0,5,$e,0,1,'C');
				elseif($this->ALIGN == 'center')
					$this->Cell(0,5,$e,0,1,'C');
				elseif($this->ALIGN == 'right')
					$this->Cell(0,5,$e,0,1,'R');
				elseif($this->ALIGN == 'left')
					$this->Cell(0,5,$e,0,1,'L');
				else
					$this->Write(5,stripslashes($this->txtentities($e)));
			}
			else
			{
				//Tag
				if($e{0}=='/')
					$this->CloseTag(strtoupper(substr($e,1)));
				else
				{
					//Extract attributes
					$a2=explode(' ',$e);
					$tag=strtoupper(array_shift($a2));
					$attr=array();
					foreach($a2 as $v)
						if(ereg('^([^=]*)=["\']?([^"\']*)["\']?$',$v,$a3))
							$attr[strtoupper($a3[1])]=$a3[2];
					$this->OpenTag($tag,$attr,$scale);
				}
			}
		}
	}

	function OpenTag($tag,$attr,$scale){
		$tag = strtoupper($tag);
		//Opening tag
		switch($tag){
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
					if($attr['STYLE'] == 'text-decoration: underline'){
						$this->SetStyle('U',true);
						break;
					}
					if(strstr($attr['STYLE'], 'color:')){
						$attr['COLOR'] = substr($attr['STYLE'],6);
						$coul=$this->hex2dec($attr['COLOR']);
						$this->SetTextColor($coul['R'],$coul['G'],$coul['B']);
						$this->issetcolor=true;
						break;
					}
					if(strstr($attr['STYLE'], 'font-size:')){
						$attr['FONTSIZE'] = intval(substr($attr['STYLE'],10));
						$this->SetFont('','',$attr['FONTSIZE']);
						$this->issetfont=true;
						break;
					}
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
					$this->Ln(5);
				}
				break;
			case 'IMG':
				$this->IMG=$attr['IMG'];
				$this->SRC=$attr['SRC'];
				$this->WIDTH=$attr['WIDTH'];
				$this->HEIGHT=$attr['HEIGHT'];
				$this->PutImage($attr[SRC],$scale);
				break;
			case 'TR':
			case 'BLOCKQUOTE':
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
				if (isset($attr['COLOR']) and $attr['COLOR']!='') {
					$coul=$this->hex2dec($attr['COLOR']);
					$this->SetTextColor($coul['R'],$coul['G'],$coul['B']);
					$this->issetcolor=true;
				}
				if (isset($attr['FACE']) and in_array(strtolower($attr['FACE']), $this->fontlist)) {
					$this->SetFont(strtolower($attr['FACE']));
					$this->issetfont=true;
				}
				break;
		}
	}

	function CloseTag($tag){
		 $tag = strtoupper($tag);
		 //Closing tag
		if($tag=='SPAN')
			$tag='U';
			$this->SetStyle($tag,false);
			if ($this->issetcolor==true) {
				$this->SetTextColor(0);
			}
			if ($this->issetfont==true) {
				$this->SetFont('arial');
				$this->issetfont=false;
			}
		if($tag=='DIV')
			$tag='DIV';
			$this->ALIGN='';
		if($tag=='STRONG')
			$tag='B';
		if($tag=='EM')
			$tag='I';
		if($tag=='B' or $tag=='I' or $tag=='U')
			$this->SetStyle($tag,false);
		if($tag=='A')
			$this->HREF='';
		if($tag=='P')
		$this->ALIGN='';
		if($tag=='IMG'){
			$this->IMG='';
			$this->SRC='';
			$this->WIDTH='';
			$this->HEIGHT='';
		}
		if($tag=='FONT'){
			if ($this->issetcolor==true) {
				$this->SetTextColor(0);
			}
			if ($this->issetfont) {
				$this->SetFont('arial');
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
		if($scale<0) $scale=0;
		//$scale<=0: put NO image inside the pdf!
		if($scale>0){
			$xsflag=0;
			$ysflag=0;
			$yhflag=0;
			$xscale=1;
			$yscale=1;
			//get image info
			$oposy=$this->GetY();
			//$url = str_replace(XOOPS_URL, XOOPS_ROOT_PATH, $url);
			$iminfo=@getimagesize($url);
			$iw=$scale * $this->px2mm($iminfo[0]);
			$ih=$scale * $this->px2mm($iminfo[1]);
			$iw = ($iw)?$iw:1;
			$ih = ($ih)?$ih:1;
			$nw=$iw;
			$nh=$ih;
			//resizing in x-direction
			$xsflag=0;
			if($iw>150)	{
				$xscale=150 / $iw;
				$yscale=$xscale;
				$nw=$xscale * $iw;
				$nh=$xscale * $ih;
				$xsflag=1;
			}
			//now eventually resizing in y-direction
			$ysflag=0;
			if(($oposy+$nh)>250){
				$yscale=(250-$oposy)/$ih;
				$nw=$yscale * $iw;
				$nh=$yscale * $ih;
				$ysflag=1;
			}
			//uups, if the scaling factor of resized image is < 0.33
			//remark: without(!) the global factor $scale!
			//that's hard -> on the next page please...
			$yhflag=0;
			if($yscale<0.33 and ($xsflag==1 or $ysflag==1))	{
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
			if($yhflag==0 and $ysflag==1) $this->AddPage();
		}
	}
}

?>