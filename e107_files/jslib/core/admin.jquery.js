$(document).ready(function()
{
		// run tips on title attribute. 
		$(".e-tip").each(function() {
			var tip = $(this).attr('title');
			if(!tip)
			{
				return;
			}
			
			$(this).tipsy({opacity:1.0,fade:true});
			// $(this).css( 'cursor', 'pointer' )
		});
		
		
	
		// run tips on .field-help 
		$("input,textarea,select,label,.e-tip").each(function(c) {
					
			$(this).nextAll(".field-help").hide();
		//	alert('hello');
			$(this).tipsy({title: function() {
				var tip = $(this).nextAll(".field-help").text();
				 return tip; 
				},
				fade: true,
				html: true,
				gravity: 'w'  
			});
		
		});
	
	
			$(".e-radio-multi").each(function() {
		//	$(this).nextAll(".field-help").hide();
		//	$(this).nextAll(":input").tipsy({title: 'hello'});
			
		});
		
		$(".e-tags").tagit();
		
		
		$(".e-multiselect").chosen();
		
		

		// Decorate		
		$(".adminlist tr:even").addClass("even");
		$(".adminlist tr:odd").addClass("odd");
		$(".adminlist tr:first").addClass("first");
  		$(".adminlist tr:last").addClass("last");
				

		
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
		 	
		 //	alert(document.location.hash);
		 	
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
		
		
		// backend 
		$(".e-password-admin").pwdMeter({
	            minLength: 6,
	            displayGeneratePassword: true,
	            generatePassText: "Generate",
	            randomPassLength: 12
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
		
		
		// Check ALl Button
		$("#e-check-all").click(function(){
			$("input[type=\"checkbox\"]").attr("checked", "checked");
		});
		
		// Uncheck all button. 
		$("#e-uncheck-all").click(function(){
			$("input[type=\"checkbox\"]").removeAttr("checked");
		});
		
		
		
		// Check-All checkbox toggle
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
		$("input.delete,button.delete").click(function(){
  			var answer = confirm($(this).attr("data-confirm"));
  			return answer // answer is a boolean
		});
		
		$("e-confirm").click(function(){
  			var answer = confirm($(this).attr("title"));
  			return answer // answer is a boolean
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
		
	
				
		$(".e-shake" ).effect("shake",{times: 10, distance: 2},20);
		
		
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
	//	$( ".portlet" ).addClass( "ui-widget ui-widget-content ui-helper-clearfix ui-corner-all" )
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



