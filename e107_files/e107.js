<!--

if(parent.frames[0])
{
parent.location.href = self.location.href;
}

if(document.getElementById&&!document.all){ns6=1;}else{ns6=0;}
var agtbrw=navigator.userAgent.toLowerCase();
var operaaa=(agtbrw.indexOf('opera')!=-1);
var head="display:''";
var folder='';
function expandit(curobj){
if(document.getElementById(curobj)){
  folder=document.getElementById(curobj).style;
  }else{

if(ns6==1||operaaa==true){
	folder=curobj.nextSibling.nextSibling.style;
}else{
	folder=document.all[curobj.sourceIndex+1].style;
}
   }
if (folder.display=="none"){folder.display="";}else{folder.display="none";}
}


function urljump(url){
	top.window.location = url; 
}

function open_window(url,type) {
	if('full' == type){
		pwindow = window.open(url);
	} else {
		pwindow = window.open(url,'Name', 'top=100,left=100,resizable=yes,width=600,height=400,scrollbars=yes,menubar=yes')
	}
}

function preloadimages(nbrpic,pic){
     myimages=new Image();
     myimages.src=pic;
}

function textCounter(field,cntfield) {
	cntfield.value = field.value.length;
}

function openwindow() {
	opener = window.open("htmlarea/index.php", "popup","top=50,left=100,resizable=no,width=670,height=520,scrollbars=no,menubar=no");            
}
function setCheckboxes(the_form, do_check){
	var elts = (typeof(document.forms[the_form].elements['perms[]']) != 'undefined') ? document.forms[the_form].elements['perms[]'] : document.forms[the_form].elements['perms[]'];
    var elts_cnt  = (typeof(elts.length) != 'undefined') ? elts.length : 0;
    if(elts_cnt){
		for(var i = 0; i < elts_cnt; i++){
			elts[i].checked = do_check;
        }
	}else{
		elts.checked        = do_check;
    }
	return true;
}
image1 = new Image(); image1.src = "../e107_images/generic/e107.gif";
image2 = new Image(); image2.src = "../e107_images/generic/hme.png";
image3 = new Image(); image3.src = "../e107_images/generic/location.png";
image4 = new Image(); image4.src = "../e107_images/generic/rname.png";

var ref=""+escape(top.document.referrer);
var colord = window.screen.colorDepth; 
var res = window.screen.width + "x" + window.screen.height;
var eself = document.location;

// From http://phpbb.com
var clientPC = navigator.userAgent.toLowerCase();
var clientVer = parseInt(navigator.appVersion);
var is_ie = ((clientPC.indexOf("msie") != -1) && (clientPC.indexOf("opera") == -1));
var is_nav = ((clientPC.indexOf('mozilla')!=-1) && (clientPC.indexOf('spoofer')==-1) && (clientPC.indexOf('compatible') == -1) && (clientPC.indexOf('opera')==-1) && (clientPC.indexOf('webtv')==-1) && (clientPC.indexOf('hotjava')==-1));
var is_moz = 0;
var is_win = ((clientPC.indexOf("win")!=-1) || (clientPC.indexOf("16bit") != -1));
var is_mac = (clientPC.indexOf("mac")!=-1);
var selectedInputArea;


// From http://www.massless.org/mozedit/
function mozWrap(txtarea, open, close){
	var selLength = txtarea.textLength;
	var selStart = txtarea.selectionStart;
	var selEnd = txtarea.selectionEnd;
	if (selEnd == 1 || selEnd == 2) selEnd = selLength;
	var s1 = (txtarea.value).substring(0,selStart);
	var s2 = (txtarea.value).substring(selStart, selEnd)
	var s3 = (txtarea.value).substring(selEnd, selLength);
	txtarea.value = s1 + open + s2 + close + s3;
	return;
}

function storeCaret (textAr){
	selectedInputArea = textAr;
	if (textAr.createTextRange){
		selectedRange = document.selection.createRange().duplicate();
	}
}

function addtext(text){
	if (window.selectedInputArea){
		var ta = selectedInputArea;
		val = text.split('][');
		if ((clientVer >= 4) && is_ie && is_win){
			theSelection = document.selection.createRange().text;
			if (theSelection) {
				document.selection.createRange().text = val[0] +']' +  theSelection + '[' + val[1];
				ta.focus();
				theSelection = '';
				return;
			}
		}else if (ta.selectionEnd && (ta.selectionEnd - ta.selectionStart > 0)){
			mozWrap(ta, val[0] +']', '[' + val[1]);
			return;
		}
		text = ' ' + text + ' ';
		if (ta.createTextRange && selectedRange) {
			var caretPos = selectedRange;
			caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? caretPos.text + text + ' ' : caretPos.text + text;
			ta.focus();
		} else {
			ta.value  += text;
			ta.focus();
		}
	}
}

function help(help){
	document.dataform.helpb.value = help;
}
function externalLinks() { 
 if (!document.getElementsByTagName) return; 
 var anchors = document.getElementsByTagName("a"); 
 for (var i=0; i<anchors.length; i++) { 
   var anchor = anchors[i]; 
   if (anchor.getAttribute("href") && 
       anchor.getAttribute("rel") == "external") 
     anchor.target = "_blank"; 
 } 
} 
window.onload = externalLinks;



//-->