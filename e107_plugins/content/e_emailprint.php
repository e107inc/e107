<?php

function print_item($id)
{
		global $tp;
		$con = new convert;

		require_once(e_PLUGIN."content/handlers/content_class.php");
		$aa = new content;
		
		if(!is_object($sql)){ $sql = new db; }
		$sql -> db_Select($plugintable, "content_id, content_heading, content_subheading, content_text, content_author, content_parent, content_datestamp, content_class", "content_id='$id' ");
		$row = $sql -> db_Fetch();
		extract($row);

		if(!check_class($content_class)){
			header("location:".e_PLUGIN."content/content.php"); exit;
		}
		$content_heading = $tp -> toHTML($content_heading);
		$content_subheading = $tp -> toHTML($content_subheading);
		$content_text = ereg_replace("\{EMAILPRINT\}|\[newpage\]", "", $tp -> toHTML($content_text, TRUE));
		$authordetails = $aa -> getAuthor($content_author);
		$content_datestamp = $con -> convert_date($content_datestamp, "long");
		$tmp = explode(".",$content_parent);
		$type_id = ($tmp[0] == "0" ? $tmp[1] : $tmp[0]);

		$text = "
		<b>".$content_heading."</b>
		<br />
		".$content_subheading."
		<br />
		".$authordetails[1].", ".$content_datestamp."
		<br /><br />
		".$content_text."
		<br /><br /><hr />
		this content item is from ".SITENAME."
		<br />
		( http://".$_SERVER[HTTP_HOST].e_HTTP.e_PLUGIN."content/content.php?type.".$type_id.".content.".$content_id." )        
		";

		require_once(e_HANDLER.'bbcode_handler.php');
		$this->e_bb = new e_bbcode;
		$text = $this->e_bb->parseBBCodes($text, '');

		return $text;
}

function email_item($id)
{
	$plugintable = "pcontent";
	if(!is_object($sql)){ $sql = new db; }
	if($sql -> db_Select($plugintable, "content_id, content_heading, content_subheading, content_text, content_author, content_parent, content_datestamp, content_class", "content_id='$id' ")){
		while($row = $sql -> db_Fetch()){
		extract($row);
			$tmp = explode(".",$content_parent);
			$type_id = ($tmp[0] == "0" ? $tmp[1] : $tmp[0]);
			if(!check_class($content_class)){
				header("location:".e_PLUGIN."content/content.php?type.".$type_id); exit;
			}
			$message = SITEURL.e_PLUGIN."content/content.php?type.".$type_id.".content.".$id."\n\n".$content_heading."\n".$content_subheading."\n";
		}
		return $message;
	}
}


function print_item_pdf($id){

	function print_content_pdf($id)
	{
			//in this section you decide what to needs to be output to the pdf file
			//unfortunately using $tp causes problems, so don't use it (yet)
			$con = new convert;

			require_once(e_PLUGIN."content/handlers/content_class.php");
			$aa = new content;
			
			if(!is_object($sql)){ $sql = new db; }
			$sql -> db_Select($plugintable, "content_id, content_heading, content_subheading, content_text, content_author, content_parent, content_datestamp, content_class", "content_id='$id' ");
			$row = $sql -> db_Fetch();
			extract($row);

			if(!check_class($content_class)){
				header("location:".e_PLUGIN."content/content.php"); exit;
			}
			$authordetails = $aa -> getAuthor($content_author);
			$content_datestamp = $con -> convert_date($content_datestamp, "long");
			$tmp = explode(".",$content_parent);
			$type_id = ($tmp[0] == "0" ? $tmp[1] : $tmp[0]);

			$text = "
			<b>".$content_heading."</b><br />
			".$content_subheading."<br />
			".$authordetails[1].", ".$content_datestamp."<br />
			<br />
			".$content_text."<br />
			";

			//the following defines are processed in the document properties of the pdf file
			$creator = SITENAME;								//define creator
			$author	= $authordetails[1];						//define author
			$keywords = "";										//define keywords
			$subject = $content_subheading;						//define subject
			$title = $content_heading;							//define title

			//define url and logo to use in the header of the pdf file
			$url = SITEURL.$PLUGINS_DIRECTORY."content/content.php?type.".$type_id.".content.".$content_id;
			define('CONTENTPDFPAGEURL', $url);					//define page url to add in header
			define('CONTENTPDFLOGO', e_IMAGE."logo.png");		//define logo to add in header
			//global $tp;
			//$text = $tp -> toHTML($text, TRUE);
	
			//always return an array with the following data:
			return array($text, $creator, $author, $title, $subject, $keywords, $url);

	}



	//extend fpdf class from package with custom functions
	class PDF extends FPDF{

		//create a header; this will be added on each page
		function Header(){
			$this->SetY(15);
			$this->SetFont('Arial','I',8);
			$this->Image(CONTENTPDFLOGO, 10, 15, '', 0, '', '');
			$y = $this->GetY();
			$x = $this->GetX();
			$image_wh = Getimagesize(CONTENTPDFLOGO);
			$newx = $x + (($image_wh[0]/$this->k));
			$newy = (($image_wh[1]/$this->k));
			$this->SetX($newx);
			$x = $this->GetX();
			$cellwidth = 210-10-$newx;
			$this->SetFont('Arial','B',14);
			$this->Cell($cellwidth,8,SITENAME,0,2,'R');
			$this->SetFont('Arial','I',8);
			$this->Cell($cellwidth,8,CONTENTPDFPAGEURL,0,2,'R');
			$this->Cell($cellwidth,10,'Page '.$this->PageNo().'/{nb}',0,2,'R');
			$this->Line(10, $newy+15, 200, $newy+15);
			$this->Ln(20);
		}


		function WriteHTML($html)
		{
			//HTML parser
			$a=preg_split('/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE);
			foreach($a as $i=>$e)
			{
				if($i%2==0)
				{
					//Text
					if($this->HREF)
						$this->PutLink($this->HREF,$e);
					else
						$this->Write(5,$e);
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
						$this->OpenTag($tag,$attr);
					}
				}
			}
		}

		function OpenTag($tag,$attr)
		{
			//Opening tag
			if($tag=='B' or $tag=='I' or $tag=='U')
				$this->SetStyle($tag,true);
			if($tag=='A')
				$this->HREF=$attr['HREF'];
			if($tag=='BR')
				$this->Ln(5);
		}

		function CloseTag($tag)
		{
			//Closing tag
			if($tag=='B' or $tag=='I' or $tag=='U')
				$this->SetStyle($tag,false);
			if($tag=='A')
				$this->HREF='';
		}

		function SetStyle($tag,$enable)
		{
			//Modify style and select corresponding font
			$this->$tag+=($enable ? 1 : -1);
			$style='';
			foreach(array('B','I','U') as $s)
				if($this->$s>0)
					$style.=$s;
			$this->SetFont('',$style);
		}

		function PutLink($URL,$txt)
		{
			//Put a hyperlink
			$this->SetTextColor(0,0,255);
			$this->SetStyle('U',true);
			$this->Write(5,$txt,$URL);
			$this->SetStyle('U',false);
			$this->SetTextColor(0);
		}

	}

	//##### THIS IS THE ACTUAL PDF CREATION - DO NOT EDIT THIS ------------------------------------

	$pdf=new PDF();								//start new pdf class

	$text = print_content_pdf($id);				//get content from $id number
	$search = array('&#39;', '&#039;', '&#036;', '&quot;', '<br />', '\n');
	$replace = array("'", "'", '$', '"', '\n', '');
	$text[0] = str_replace($search, $replace, $text[0]);		//replace some in the text
	$text[3] = str_replace($search, $replace, $text[3]);		//replace some in the title

	//$text[0] = strip_tags($text[0], '<b></b><i></i><u></u>[b][/b][i][/i][u][/u]');


	$search[0] = "#\[b\](.*?)\[/b\]#si";
	$replace[0] = '<b>\1</b>';
	$search[1] = "#\[i\](.*?)\[/i\]#si";
	$replace[1] = '<i>\1</i>';
	$search[2] = "#\[u\](.*?)\[/u\]#si";
	$replace[2] = '<u>\1</u>';
	$text[0] = preg_replace($search, $replace, $text[0]);

	$pdf->AliasNbPages();						//calculate current page + number of pages
	$pdf->AddPage();							//start page
	$pdf->SetFont('Arial','',10);				//set font

	$pdf->WriteHTML($text[0]);					//write text
	//$pdf->Write('5', $text[0]);					//write text
	$pdf->SetCreator($text[1]);					//name of creator
	$pdf->SetAuthor($text[2]);					//name of author
	$pdf->SetTitle($text[3]);					//title
	$pdf->SetSubject($text[4]);					//subject
	$pdf->SetKeywords($text[5]);				//space seperated

	$file = $text[3].".pdf";					//name of the file
	$pdf->Output($file, 'D');					//Save PDF to file (D = output to download window)

	//##### ---------------------------------------------------------------------------------------

}


?>