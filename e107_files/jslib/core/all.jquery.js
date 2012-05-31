$(document).ready(function()
{

    	 $(".e-hideme").hide();
    	 $(".e-expandit").show();
    			
       	$(".e-expandit").click(function () {
       		var id = $(this).attr("href");
			$(id).toggle("slow");
		}); 
		
		// Date
		$(function() {
					
			$("input.e-date").each(function() {
        		$(this).datepicker({
        			 dateFormat: $(this).attr("data-date-format"),
        			 ampm: $(this).attr("data-date-ampm")
        		 });    		 
    		});
    		
    		$("input.e-datetime").each(function() {
        		$(this).datetimepicker({
        			 dateFormat: $(this).attr("data-date-format"),
        			 timeFormat: $(this).attr("data-time-format"),
        			 ampm: $(this).attr("data-date-ampm")
        		 });    		 
    		});
    		
    		// Inline versions 
    		$("div.e-date").each(function() {
    			var id = $(this).attr("id");
        		var newid = id.replace("inline-", "");
        		$(this).datepicker({
        			dateFormat: $(this).attr("data-date-format"),
        			ampm: $(this).attr("data-date-ampm"),
        			defaultDate: $("#"+newid).val(),
        			onSelect: function(dateText, inst) {
				      $("#"+newid).val(dateText);
				   	}
				  
        		 });    		 
    		});
    		
    		$("div.e-datetime").each(function() {
    			var id = $(this).attr("id");
        		var newid = id.replace("inline-", "");
        		$(this).datetimepicker({
        			dateFormat: $(this).attr("data-date-format"),
        			ampm: $(this).attr("data-date-ampm"),
        			showButtonPanel: false,
           			onSelect: function(dateText, inst) {
				      $("#"+newid).val(dateText);
				   	}
        		 }); 
        		 $(this).datetimepicker('setDate', $("#"+newid).val());   		 
    		});
    		
    		
    		  		

		});  
		
		// Tabs
		$(function() {
			$( "#tab-container" ).tabs({cache: true});
		});	
		
		// Tabs
		$(function() {
			$( ".e-tabs" ).tabs();
		});	
		
		$('.e-tabs-add').on("click", function(){
			var url = $(this).attr('data-url');
			var count = parseInt($("#e-tab-count").val()) + 1; 	
			
			// alert(count);
			//return false;
			if($("#tab-container").tabs("add",url + '?iframe=1',"Page "+count))
			{
				$("#e-tab-count").val(count);	
			}
			
			return false;
		});
		
		$('.e-email').on('blur', function() {
			// alert('hello');
		  $(this).mailcheck({
		    
		    suggested: function(element, suggestion) {
		    	var id = $(element);
		    	var hint = $(element).next('div').attr('data-hint');
		    	var mes = hint.replace('[x]',suggestion.full);
		    	$(element).next('div').html(mes);
		    	$(element).next('div').show('slow');
		    },
		    empty: function(element) {
		      $(element).next('div').hide('slow');
		    }
	  		});
		});
		
		$('.e-password').on('keyup', function() {
			// var len = $(this).val().length;
			
			//data-minlength
		});
		
		
		
		
		
	//	var tabs = $('#tab-container').clone(true);
	//	$('#htmlEditor').append(tabs);


		
/*
		$("a.e-bb").click(function(){
			var add = $(this).attr('data-bbcode');
			var func = $(this).attr('data-function');
			var diz = $(this).attr('title');
			id = $(this).attr('href');
			var tmp = id.replace('#','');
			//alert(tmp);
			if(func == 'insert')
			{
				addtext(add,true);	
				return false;
			}
			if(func == 'input')
			{
				addinput(add,diz);
				return false;	
			}
			if(func == 'show')
			{
				$('#'+add).show('slow');
				// addinput(add,diz);
				return false;	
			}
			if(func == 'add')
			{
				addtext(add);	
				return false;	
			}


			return false;
		 	
		});
			
		
		$("select.e-bb").change(function(){
			var add = $(this).val();
		 	addtext(add);
		 	$(this).val('');
		 	return false;	
		});
		
		$(".e-bb").mouseover(function(){
			
			var id = $(this).attr('id');
			var diz = $(this).attr('title');
		//	alert(id);
			var tmp = id.split('--');
		//	alert('#'+tmp[0]);
		 	$('#'+tmp[0]).val(diz);
		 	return false;	
		});
		
		$(".e-bb").mouseout(function(){
			var id = $(this).attr('id');
			var tmp = id.split('--')			
		 	$('#'+tmp[0]).val('');
		 	return false;	
		});
		
	*/	
		
		
		
		
	//	$(".e-multiselect").chosen();
		
		
		// Password
		$(function() {
			
			$(".e-password-admin").pwdMeter({
	            minLength: 6,
	            displayGeneratePassword: true,
	            generatePassText: "Generate",
	            randomPassLength: 12
	        });
			
        });
					
		// Character Counter
	//	$("textarea").before("<p class=\"remainingCharacters\" id=\"" + $("textarea").attr("name")+ "-remainingCharacters\">&nbsp;</p>");
		$("textarea").keyup(function(){
    		
    	//	var max=$(this).attr("maxlength");
			var max = 100;
			var el = "#" + $(this).attr("name") + "-remainingCharacters";
    		var valLen=$(this).val().length;
    		$(el).text( valLen + " characters")
		});
		

		
		// Dialog
		$("a.e-dialog").colorbox({
			iframe:true,
			width:"60%",
			height:"70%",
			speed:100
		});
		
		$(".e-dialog-close").click(function () {
			parent.$.colorbox.close()
		}); 
		
		
		// Modal Box - uses inline hidden content 
		$(".e-modal").click(function () {
			var id = $(this).attr("href");
			$(id).dialog({
				 minWidth: 800,
				 maxHeight: 700,
				 modal: true
			 });
		});
	
		
				
		$(".e-shake" ).effect("shake","",100);
		
    	$('.e-rate').each(function() {
    		var path 		= $(this).attr("data-path");
			var script 		= $(this).attr("data-url");
			var score 		= $(this).attr("data-score");
			var readonly	= parseInt($(this).attr("data-readonly"));
			var tmp 		= $(this).attr('id');
			var hint		= $(this).attr("data-hint");
			var hintArray	= hint.split(',')
			var t	 		= tmp.split('-');
			var table 		= t[0];
			var id 			= t[1];
		
    		$('.e-rate').raty({
    			path		: path,
    			half  		: true,
    			score    	: score,
    			readOnly	: readonly,
    			hints		: hintArray,
    		//	starOff		: 'star_off_16.png',
  			//	starOn		: 'star_on_16.png',
  			//	starHalf  	: 'star_half_16.png',
  			//	cancelOff 	: 'cancel-off-big.png',
  			//	cancelOn  	: 'cancel-on-big.png',			
  			//	size      	: 16,
  				target     	: '#e-rate-'+tmp,	
  			//	targetType : 'number',
  				targetText : $('#e-rate-'+tmp).text(),			
    		//	cancel		: true,
    		//	css			: 'e-rate-star',
    			click: function(score, evt) {
        				$(this).find('img').unbind('click');
						$(this).find('img').unbind();
					$.ajax({
					  type: "POST",
					  url: script + "?ajax_used=1",
					  data: { table: table, id: id, score: score }
					}).done(function( msg ) {
						alert(msg);
						bla = msg.split('|');
											
						$('#e-rate-'+tmp).text(bla[0]);
						if(bla[1])
						{
							$('#e-rate-votes-'+tmp).text(bla[1]);	
						}
						
					});
				}
    		});
    	});
    
    	// $( ".field-help" ).tooltip();
	
		// Allow Tabs to be used inside textareas. 
		$( 'textarea' ).keypress( function( e ) {
	    if ( e.keyCode == 9 ) {
	        e.preventDefault();
	        $( this ).val( $( this ).val() + '\t' );
	    }
		});

		// Text-area AutoGrow
	//	$("textarea.e-autoheight").elastic();

		
		
})

// Legacy Stuff to be converted. 



// BC Expandit() function 

	var nowLocal = new Date();		/* time at very beginning of js execution */
	var localTime = Math.floor(nowLocal.getTime()/1000);	/* time, in ms -- recorded at top of jscript */

	
	function expandit(e) {
				
			var href = $(e).attr("href");
						
			if(href === "#" || href == "") 
			{
				idt = $(e).next("div");	
				$(idt).toggle("slow");
				return false;;			
			}
			
			var id = "#" + e; 		
			$(id).toggle("slow");
			return false;
	};
		

	var addinput = function(text,rep) {
	
	// quick fix to prevent JS errors - proper match was done only for latin words
		// var rep = text.match(/\=([^\]]*)\]/);
		// var rep = '';
		var val = rep ? prompt(rep) : prompt('http://');
	
		if(!val)
		{
			return;
		}
		var newtext = text.replace('*', val);
		emote = '';
	    addtext(newtext, emote);
	     return;
	}

		
		
		
	function SyncWithServerTime(serverTime,domain)
	{
		if (serverTime) 
		{
		  	/* update time difference cookie */
			var serverDelta=Math.floor(localTime-serverTime);
			
		  	document.cookie = 'e107_tdOffset='+serverDelta+'; path=/; domain= .'+domain;
		  	document.cookie = 'e107_tdSetTime='+(localTime-serverDelta)+'; path=/; domain=.'+domain; /* server time when set */
		}
	
		var tzCookie = 'e107_tzOffset=';
	//	if (document.cookie.indexOf(tzCookie) < 0) {
			/* set if not already set */
			var timezoneOffset = nowLocal.getTimezoneOffset(); /* client-to-GMT in minutes */
			document.cookie = tzCookie + timezoneOffset+'; path=/; domain=.'+domain;
	//	}
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

/* TODO: @SecretR - Object of removal
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
*/

	function storeCaret (textAr){
		e107_selectedInputArea = textAr;
		/* TODO: @SecretR - Object of removal - not needed anymore
		if (textAr.createTextRange){
			e107_selectedRange = document.selection.createRange().duplicate();
		}*/
	}

/**
 * New improved version - fixed scroll to top behaviour when inserting BBcodes
 * @TODO - improve it more (0.8) 
 */
	function addtext(text, emote) {
	
	if (!window.e107_selectedInputArea) {
		return; //[SecretR] TODO - alert the user 
	}

	var eField = e107_selectedInputArea;	
	var eSelection 	= false;  
	var tagOpen = '';
	var tagClose = '';
	
	if (emote != true) {  // Split if its a paired bbcode
		var tmp = text.split('][', 2);
		if (tmp[0] == text) {
			tagOpen = text;
		} else {
			tagOpen = tmp[0] + ']';
			tagClose = '[' + tmp[1];
		}
	} else { //Insert Emote
		tagOpen = text;
	}
		

	// Windows user  
	if (document.selection) {
	
		eSelection = document.selection.createRange().text;
		eField.focus();
		if (eSelection) {
			document.selection.createRange().text = tagOpen + eSelection + tagClose;
		} else {
			document.selection.createRange().text = tagOpen + tagClose;
		}
		
		eSelection = '';
		
		eField.blur();
		eField.focus();
		
		return;
	} 
	
	var scrollPos = eField.scrollTop;
	var selLength = eField.textLength;
	var selStart = eField.selectionStart;
	var selEnd = eField.selectionEnd; 
	
	if (selEnd <= 2 && typeof(selLength) != 'undefined' && (selStart != selEnd)) {
		selEnd = selLength;
	}
	
	var sel1 = (eField.value).substring(0,selStart);
	var sel2 = (eField.value).substring(selStart, selEnd);
	var sel3 = (eField.value).substring(selEnd, selLength);

	var newStart = selStart + tagOpen.length + sel2.length + tagClose.length;
	eField.value = sel1 + tagOpen + sel2 + tagClose + sel3;

	eField.focus();
	eField.selectionStart = newStart;
	eField.selectionEnd = newStart;
	eField.scrollTop = scrollPos;
	return;

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

var e107_dupCounter = 1;
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

