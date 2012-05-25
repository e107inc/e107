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
			$( ".e-date" ).datepicker();
		});  
		
		// Tabs
		$(function() {
			$( "#tab-container" ).tabs();
		});	
		
		// Tabs
		$(function() {
			$( ".e-tabs" ).tabs();
		});	
		
		$(".e-multiselect").chosen();
		
		
		// Password
		$(function() {
			
			$("#password1").pwdMeter({
	            minLength: 6,
	            displayGeneratePassword: true,
	            generatePassText: "Generate",
	            randomPassLength: 12
	        });
			
        });
		
		// Decorate		
		$(".adminlist tr:even").addClass("even");
		$(".adminlist tr:odd").addClass("odd");
		$(".adminlist tr:first").addClass("first");
  		$(".adminlist tr:last").addClass("last");
				
		// Character Counter
		$("textarea").before("<p class=\"remainingCharacters\" id=\"" + $("textarea").attr("name")+ "-remainingCharacters\">&nbsp;</p>");
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
		
		// Modal Box - uses inline hidden content  // NEEDS work - see customize link for admin-infopanel. 
		
		$(".e-modal-iframe").click(function (e) {
			var id = $(this).attr("href");
				e.preventDefault();
                var page = $(this).attr("href")
                var pagetitle = $(this).attr("title")
                var $dialog = $("<div></div>")
                .html("<iframe style=\"border: 0px; \" src=\"" + page + "\" width=\"100%\" height=\"100%\"></iframe>")
                .dialog({
                    autoOpen: false,
                    modal: true,
                    width:800,
                    height: 700,
                    minWidth: 800,
                    minHeight: 700,
				 	maxHeight: 700,
                    title: pagetitle
                });
                $dialog.dialog("open");
		});
		
		
		// Admin Prefs Navigation
		
		 $(".plugin-navigation a").click(function () {
		 	$(".plugin-navigation a").each(function(index) {
    			var ot = $(this).attr("href");
				$(ot).hide();
				$(this).closest("li").removeClass("active");
				$(this).switchClass( "link-active", "link", 0 );
			});
	   		var id = $(this).attr("href");
			$(this).switchClass( "link", "link-active", 30 );
			$(this).closest("li").addClass("active");
			$(id).show({
				effect: "slide"
				});
		}); 
		
		
		
		
		
		
		// Sorting
		var fixHelper = function(e, ui) {
			ui.closest("tr").switchClass( "odd", "highlight-odd", 0 );
			ui.closest("tr").switchClass( "even", "highlight-even", 0 );
			ui.children().each(function() {
				$(this).width($(this).width());
			// 	$(this).closest("tr").switchClass( "odd", "highlight-odd", 0 );
			//	$(this).closest("tr").switchClass( "even", "highlight-even", 0 );
			});
			return ui;
		};
		
		$("#e-sort").sortable({
			helper: fixHelper,
			cursor: "move",
			opacity: 0.9,
			handle: ".e-sort",
			distance: 20,
			containment: "parent",
			stop: function(e,ui) {
			    var allItems = $(this).sortable("toArray");
			    var newSortValue = allItems.indexOf( $(ui.item).attr("id") );
			 //   alert($(ui.item).attr("id") + " was moved to index " + newSortValue);
			 	$(".highlight-even").switchClass( "highlight-even", "even", 600 );
				$(".highlight-odd").switchClass( "highlight-odd", "odd", 600 );   
			},
			
			update: function(event, ui) {         	
				var allItems = $(this).sortable("toArray");
			//	console.log(allItems);
				var neworder = allItems.indexOf( $(ui.item).attr("id") );
				var linkid = $(ui.item).attr("id"); 
			//	 $("td").removeClass("e-moving","slow"); 
			     	
				var script = $(".e-sort:first").attr("href");
			//	alert(script);
				$.ajax({
				  type: "POST",
				  url: script,
				  data: { all: allItems, linkid: linkid, neworder: neworder }
			//	  data: { linkid: linkid, neworder: neworder }
				}).done(function( msg ) {
				
				// alert("Posted: "+allItems+" Updated: "+ msg );
				});

 			}
			
		}).disableSelection();
		
		
		
		// Check-All
		$("input.toggle-all").click(function(evt){
			if($(this).is(":checked")){
				$("input[type=\"checkbox\"].checkbox").attr("checked", "checked");
			}
			else{
				$("input[type=\"checkbox\"].checkbox").removeAttr("checked");
			}
		});
		
		// highlight checked row
		$(".adminlist input[type=\"checkbox\"].checkbox").click(function(evt){
	
			if(this.checked)
			{
				$(this).closest("tr").switchClass( "odd", "highlight-odd", 0 );
				$(this).closest("tr").switchClass( "even", "highlight-even", 0 );
    		}
			else
			{
				$(this).closest("tr").switchClass( "highlight-even", "even", 300 );
				$(this).closest("tr").switchClass( "highlight-odd", "odd", 300 );
			}	
			
		});
		
			
		
	
		// Basic Delete Confirmation	
		$("input.delete").click(function(){
  			var answer = confirm($(this).attr("title")+ " ?");
  			return answer // answer is a boolean
		});
		
		$("e-confirm").click(function(){
  			var answer = confirm($(this).attr("title"));
  			return answer // answer is a boolean
		});    
		
		
		$(".e-dialog-close").live("click", function(){
			parent.$.colorbox.close()	
		}); 
		
		// Menu Manager Layout drop-down options
		$("#menuManagerSelect").change(function(){
			var link = $(this).val();
			$("#menu_iframe").attr("data",link);
			return false;		
		});
		
		
		$(".e-nav").click(function(){ // should be run before ajax. 
			
			var src = $(this).attr("data-src");
			var inc = parseInt($(this).attr("data-nav-inc"));
			var dir = $(this).attr("data-nav-dir");
			var tot = parseInt($(this).attr("data-nav-total"));
			var val = src.match(/from=(\d+)/);
			var amt = parseInt(val[1]);
			
			var oldVal = 'from='+ amt;
		
			var sub = amt - inc;
			var add = amt + inc;
			
			$(this).show();	
			
			if(add > tot)
			{
				add = amt;	
			}
				
			if(sub < 0)
			{
				sub = 0
			}
			
			if(dir == 'down')
			{
				var newVal = 'from='+ sub;
			}
			else
			{
				var newVal = 'from='+ add;	
			}
			
			
			src = src.replace(oldVal, newVal);
			$(".e-nav").attr("data-src",src);
	
		});
		
		$(".e-ajax").click(function(){
			
  			var id = $(this).attr("href");
  			var src = $(this).attr("data-src");
  			var effect = $(this).attr("data-effect");
  			
  			$(id).load(src + " ",function() {
    			// $(id).effect("slide");
			});
			
			
		});
				
		$(".e-shake" ).effect("shake","",100);
		
		
		$("select.filter").change(function() {
			$(this).closest("form").submit();
		});
		
		
		$("div.e-autocomplete").keyup(function() { //TODO. 
				
			
		});


	$(function() {
		
		//$(".e-menumanager-delete").live("click", function(e){
		$(".e-menumanager-delete").click(function(e){
			e.preventDefault();
			var area = 'remove';
			var remove = $(this).attr('id');
			var opt = remove.split('-');
			var hidem = "#block-" + opt[1] +'-' + opt[2];
			$(hidem).hide("slow");
			// alert(hidem);
			$.ajax({
				  type: "POST",
				  url: "menus.php?ajax_used=1",
				  data: { removeid: remove, area: area }
			//	  data: { linkid: linkid, neworder: neworder }
				}).done(function( msg ) {
					
				//	alert(msg );
				});		
			});
			
			$( ".column" ).sortable({
				connectWith: ".column",
				constain: 'table',
		//	stop: function(e,ui) {
		//	    var allItems = $(this).sortable("toArray");
		//	    var newSortValue = allItems.indexOf( $(ui.item).attr("id") );
		//	   // alert($(ui.item).attr("id") + " was moved to index " + newSortValue);
	
		//	},
			cursor: "move",
			opacity: 0.9,
			handle: ".portlet-header",
			distance: 20,
			remove: function(event, ui) {
               // ui.item.clone().appendTo(this);
               //  $(this).sortable('cancel');
           },
			stop: function(event, ui) {         	
				
				var linkid = $(ui.item).attr("id"); 
			    var area = $('#'+linkid).closest('.column').attr('id'); 
				var areaList = $('#'+linkid).closest('.column').sortable("toArray");
			    //  alert(areaList);
			    
			    $(ui.item).attr("id")
			    
			    var layout = $('#dbLayout').attr("value");
			    //	alert(layout);
			    	
			    var opt = linkid.split('-');
			    
			    if(area == 'remove')
			    {	// alert(area);
			    	var remove = linkid;
			    	areaList = '';
			    	$('#check-' + opt[1]).show('fast');
			    	$('#option-' + opt[1]).hide('fast');
			    	$('#status-' + opt[1]).text('remove');
			    }	
			    else
			    {	
			    	if($('#status-' + opt[1]).text() == 'insert' || $('#status-' + opt[1]).text() == 'update')
			    	{
			    		var stat = 'update';	
			    	}
			    	else
			    	{ 
			    		var stat = 'insert';
			    		
			    	}
			    	var aId = area.split('-');
			    	var newId =  linkid + '-' + aId[1];
			    	
			    	var remId = $('#'+linkid).find(".delete").attr('id') + aId[1];
			    	$('#'+linkid).find(".delete").attr('id',remId);
			    	var hidem = "block-" + opt[1] +'-' + aId[1];
			    	$('#'+linkid).attr('id',hidem);  	   	
	
			    	$('#check-' + opt[1]).hide('fast');
			    	$('#option-' + opt[1]).show('fast');
			    	$('#status-' + opt[1]).text(stat);	
			    }
			    		    
				$.ajax({
				  type: "POST",
				  url: "menus.php?ajax_used=1",
				  data: { removeid: remove, insert:linkid, mode: stat, list: areaList, area: area, layout: layout }
			//	  data: { linkid: linkid, neworder: neworder }
				}).done(function( msg ) {
				
				// alert(" Updated: "+ msg );
				});

 			}
		});

		$( ".portlet" ).addClass( "ui-widget ui-widget-content ui-helper-clearfix ui-corner-all" )
			.find( ".portlet-header" )
				.addClass( "ui-widget-header ui-corner-all" )
				.prepend( "<span class='ui-icon ui-icon-minusthick'></span>")
				.end()
			.find( ".portlet-content" );

		$( ".portlet-header .ui-icon" ).click(function() {
			$( this ).toggleClass( "ui-icon-minusthick" ).toggleClass( "ui-icon-plusthick" );
			$( this ).parents( ".portlet:first" ).find( ".portlet-content" ).toggle();
		});

		$( ".column" ).disableSelection();
		});









		$.fn.extend({
    	insertAtCaret: function(myValue) {
	        if (document.selection) {
	                this.focus();
	                sel = document.selection.createRange();
	                sel.text = myValue;
	                this.focus();
	        }
	        else if (this.selectionStart || this.selectionStart == '0') {
	            var startPos = this.selectionStart;
	            var endPos = this.selectionEnd;
	            var scrollTop = this.scrollTop;
	            this.value = this.value.substring(0, startPos)+myValue+this.value.substring(endPos,this.value.length);
	            this.focus();
	            this.selectionStart = startPos + myValue.length;
	            this.selectionEnd = startPos + myValue.length;
	            this.scrollTop = scrollTop;
		        } else {
		            this.value += myValue;
		            this.focus();
		        }
	    }
    	
  	
    	
})

				// Text-area AutoGrow
	//	$("textarea.e-autoheight").elastic();

		
		
})

// Legacy Stuff to be converted. 



// BC Expandit() function 
	
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
		

	var addinput = function(text) {
	
	// quick fix to prevent JS errors - proper match was done only for latin words
		var rep = text.match(/\=([^\]]*)\]/);
		var val = rep ? prompt(rep[1]) : prompt('http://');
	
		if(!val)
		{
			return;
		}
		var newtext = text.replace(rep[1], val);
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
 * @TODO - improve it more (0.8) - Prototype
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

