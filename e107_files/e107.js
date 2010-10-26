<!--

/*
+ ----------------------------------------------------------------------------+
|	e107 website system - Javascript File.
|
|	$Source: /cvs_backup/e107_0.8/e107_files/e107.js,v $
|	$Revision$
|	$Date$
|	$Author$
+----------------------------------------------------------------------------+
*/

/*
 * NOTE: KEEP THIS AT THE TOP OF E107.JS!

  THIS FILE IS DEPRECATED - Please use e_js.php instead. 


 * localTime is recorded ASAP after page load; SyncWithServerTime is called at the END
 * of page processing. We want localTime and serverTime set in close chronological order.
 * Page Processing order is as follows:
 * A) All PHP code encountered sequentially in page
 * B) All Javascript code encountered sequentially in page NOT including function internals
 * So best proximity is achieved by setting
 * serverTime at END of php code, and localTime at START of js code.
 * NOTE: this method means reported times include transfer delay. It's slightly MORE accurate this way!
 * Final product: tdOffset cookie contains server-browser time difference in seconds,
 * independent of time zone. tzOffset contains browser time zone in minutes.
 */

var nowLocal = new Date();		/* time at very beginning of js execution */
var localTime = Math.floor(nowLocal.getTime()/1000);	/* time, in ms -- recorded at top of jscript */
/* NOTE: if serverDelta is needed for js functions, you must pull it from
 * the cookie (as calculated during a previous page load!)
 * The value calculated in SyncWithServerTime is not known until after the
 * entire page has been processed.
 */
function SyncWithServerTime(serverTime, path, domain)
{
	if (serverTime) 
	{
	  	/* update time difference cookie */
		var serverDelta=Math.floor(localTime-serverTime);
		if(!path) path = '/';
		if(!domain) domain = '';
		else domain = '; domain=' + domain;
	  	document.cookie = 'e107_tdOffset='+serverDelta+'; path='+path+domain;
	  	document.cookie = 'e107_tdSetTime='+(localTime-serverDelta)+'; path='+path+domain; /* server time when set */
	}

	var tzCookie = 'e107_tzOffset=';
//	if (document.cookie.indexOf(tzCookie) < 0) {
		/* set if not already set */
		var timezoneOffset = nowLocal.getTimezoneOffset(); /* client-to-GMT in minutes */
		document.cookie = tzCookie + timezoneOffset+'; path='+path+domain;
//	}
}
var ns6=0;
if(document.getElementById&&!document.all){ns6=1;}
var agtbrw=navigator.userAgent.toLowerCase();
var operaaa=(agtbrw.indexOf('opera')!=-1);
var head="display:''";
var folder='';

function expandit(curobj, hide) {
	if(document.getElementById(curobj)) {
  		folder=document.getElementById(curobj).style;
	} else {
		if(ns6==1||operaaa==true) {
			folder=curobj.nextSibling.nextSibling.style;
		} else {
			folder=document.all[curobj.sourceIndex+1].style;
		}
   }
	if(folder.display=="none")
	{
		folder.display="";
	} else {
		folder.display="none";
	}
	if(hide) {
		var hide_objects = hide.split(",");
		for(i=0; i<hide_objects.length; i++) {
			hide_objects[i]=hide_objects[i].replace(/^\s*(.*)/, "$1");
			hide_objects[i]=hide_objects[i].replace(/(.*?)\s*$/, "$1");
			if(document.getElementById(hide_objects[i])) {
				hidden=document.getElementById(hide_objects[i]).style;
				if(hidden.display=="") {
					hidden.display="none";
				}
			}
		}
	}
}

function urljump(url){
	top.window.location = url;
}

function setInner(id, txt) {
	document.getElementById(id).innerHTML = txt;
}

function jsconfirm(thetext){
		return confirm(thetext);
}

function insertext(str,tagid,display){
	document.getElementById(tagid).value = str;
	if(display){
		document.getElementById(display).style.display='none';
	}
}

function appendtext(str,tagid,display){
	document.getElementById(tagid).value += str;
	document.getElementById(tagid).focus();
	if(display){
		document.getElementById(display).style.display='none';
	}
}

function open_window(url,wth,hgt) {
	if('full' == wth){
		pwindow = window.open(url);
	} else {
		if (wth) {
			mywidth=wth;
		} else {
			mywidth=600;
		}

		if (hgt) {
			myheight=hgt;
		} else {
			myheight=400;
		}

		pwindow = window.open(url,'Name', 'top=100,left=100,resizable=yes,width='+mywidth+',height='+myheight+',scrollbars=yes,menubar=yes');
	}
	pwindow.focus();
}

function ejs_preload(ejs_path, ejs_imageString){
	var ejs_imageArray = ejs_imageString.split(',');
	for(ejs_loadall=0; ejs_loadall<ejs_imageArray.length; ejs_loadall++){
		var ejs_LoadedImage=new Image();
		ejs_LoadedImage.src=ejs_path + ejs_imageArray[ejs_loadall];
	}
}

function textCounter(field,cntfield) {
	cntfield.value = field.value.length;
}

function openwindow() {
	opener = window.open("htmlarea/index.php", "popup","top=50,left=100,resizable=no,width=670,height=520,scrollbars=no,menubar=no");
	opener.focus();
}

function closeWindow(form){
	if((window.opener!=null)&&(!window.opener.closed)){
		window.opener.location.reload();
	}
	if(window.opener!=null) {
		window.close();
	}else{setWinType(form);form.whatAction.value="Close";form.submit();}
}

function setCheckboxes(the_form, do_check, the_cb){
	var elts = (typeof(document.forms[the_form].elements[the_cb]) != 'undefined') ? document.forms[the_form].elements[the_cb] : document.forms[the_form].elements[the_cb];
	if(document.getElementById(the_form))
	{
		if(the_cb)
		{
			var elts =(typeof(document.getElementById(the_form).elements[the_cb]) != 'undefined') ? document.getElementById(the_form).elements[the_cb] : document.getElementById(the_form).elements[the_cb];
		}
		else
		{
			var elts = document.getElementById(the_form);
		}
	}
	
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
var e107_selectedInputArea;
var e107_selectedRange;
var e107_dupCounter = 1;

// From http://www.massless.org/mozedit/
function mozWrap(txtarea, open, close){
	var selLength = txtarea.textLength;
	var selStart = txtarea.selectionStart;
	var selEnd = txtarea.selectionEnd;
	if (selEnd == 1 || selEnd == 2) selEnd = selLength;
	var s1 = (txtarea.value).substring(0,selStart);
	var s2 = (txtarea.value).substring(selStart, selEnd);
	var s3 = (txtarea.value).substring(selEnd, selLength);
	txtarea.value = s1 + open + s2 + close + s3;
	return;
}

function mozSwap(txtarea, newtext){
	var selLength = txtarea.textLength;
	var selStart = txtarea.selectionStart;
	var selEnd = txtarea.selectionEnd;
	if (selEnd == 1 || selEnd == 2) selEnd = selLength;
	var s1 = (txtarea.value).substring(0,selStart);
	var s3 = (txtarea.value).substring(selEnd, selLength);
	txtarea.value = s1 + newtext + s3;
	return;
}

function storeCaret (textAr){
	e107_selectedInputArea = textAr;
	if (textAr.createTextRange){
		e107_selectedRange = document.selection.createRange().duplicate();
	}
}

function addtext(text, emote)
{
  var val = new Array(2);
	if (window.e107_selectedInputArea)
	{
		var ta = e107_selectedInputArea;
		if (emote != true)
		{  // Split if its a paired bbcode
			val = text.split('][');
			if (val[0] == text) val[1] = '';
		}
		else 
		{ 
		val = text; 
		}

		if ((clientVer >= 4) && is_ie && is_win)
		{
			theSelection = document.selection.createRange().text; /* wrap selected text */
			if (theSelection) 
			{
				if (emote != true)
				{
				  if (val[1] == '')
				  {  // Single piece of text
					document.selection.createRange().text = val[0];
				  }
				  else
				  {  // bbcode
					document.selection.createRange().text = val[0] +']' +  theSelection + '[' + val[1];
				  }
				} 
				else 
				{
					document.selection.createRange().text = val + theSelection;
				}
				ta.focus();
				theSelection = '';
				return;
			}

		}
		else 
		  if (ta.selectionEnd && (ta.selectionEnd - ta.selectionStart > 0))
		  { // Selected text here
			if (emote != true)
			{
			  if  (val[1] != '')
			  {  // BBCode to wrap
				mozWrap(ta, val[0] +']', '[' + val[1]); /* wrap selected text */
			  }
			  else
			  {  // Single piece of text to insert, and delete any selected text
				mozSwap(ta, text); /* wrap selected text */
			  }
			} 
			else 
			{
				mozWrap(ta, val, ''); /* wrap selected text */
			}
			return;
		  }
		text = ' ' + text + ' ';
		if (ta.createTextRange && e107_selectedRange) 
		{
			var caretPos = e107_selectedRange; /* IE */
			caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? caretPos.text + text + ' ' : caretPos.text + text;
		} 
		else 
		  if (ta.selectionStart || ta.selectionStart == '0') 
		  { /* Moz */
		   	var startPos = ta.selectionStart;
			var endPos = ta.selectionEnd;
			var charb4 = ta.value.charAt(endPos-1);
			ta.value = ta.value.substring(0, endPos)+ text + ta.value.substring(endPos);
		  } 
		  else 
		  {
			ta.value  += text;
		  }
		ta.focus();
	}
}

function help(helpstr,tagid){
	if(tagid){
		document.getElementById(tagid).value = helpstr;
	} else if(document.getElementById('dataform')) {
		document.getElementById('dataform').helpb.value = helpstr;
	}
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

function eover(object, over) {
	object.className = over;
}

function duplicateHTML(copy,paste,baseid){
		if(document.getElementById(copy)){

			e107_dupCounter++;
			var type = document.getElementById(copy).nodeName; // get the tag name of the source copy.

			var but = document.createElement('input');
			var br = document.createElement('br');

			but.type = 'button';
			but.value = 'x';
			but.className = 'button';
			but.onclick = function(){ this.parentNode.parentNode.removeChild(this.parentNode); };

			var destination = document.getElementById(paste);
			var source      = document.getElementById(copy).cloneNode(true);

			var newentry = document.createElement(type);

			newentry.appendChild(source);
			newentry.value='';
			newentry.appendChild(but);
			newentry.appendChild(br);
			if(baseid)
			{
				newid = baseid+e107_dupCounter;
				newentry.innerHTML = newentry.innerHTML.replace(new RegExp(baseid, 'g'), newid);
				newentry.id=newid;
			}

			destination.appendChild(newentry);
		}
}

function preview_image(src_val,img_path, not_found)
{
	var ta;
	var desti = src_val + '_prev';

	ta = document.getElementById(src_val).value;
	if(ta){
		document.getElementById(desti).src = img_path + ta;
	}else{
		document.getElementById(desti).src = not_found;
	}
	return;
}


//-->