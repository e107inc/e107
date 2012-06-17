

$(document).ready(function()
{
		$(":input").tipsy({gravity: 'w',fade: true});
		
		$(".e-tip").tipsy({gravity: 'sw',fade: true});
	
	
	
		$(".e-comment-submit").click(function(){
			
			var url = $(this).attr("data-target");
			var sort = $(this).attr("data-sort");
			var data = $("form#e-comment-form").serialize();
			var total = parseInt($("#e-comment-total").text());
	
			$.ajax({
			  type: 'POST',
			  url: url + '?ajax_used=1&mode=submit',
			  data: data,
			  success: function(html) {
			  	
			  	console.log(html);
			  	var a = $.parseJSON(html);
	
				$("#comment").val('');
				if(sort == 'desc')
				{
					$(a.html).prependTo('#comments-container').hide().slideDown(800);	
				}
				else
				{
					$(a.html).appendTo('#comments-container').hide().slideDown(800);
					alert('Thank you for commenting'); // possibly needed as the submission may go unoticed	by the user
				}  
				
				if(!a.error)
				{
					$("#e-comment-total").text(total + 1);	
				}
				else
				{
					alert(a.msg);	
				}
			  	return false;	
			  }
			});
			
			return false;

		});
		
		
		
		$(".e-comment-edit").live("click", function(){
			
			var url = $(this).attr("data-target");
			var sp = $(this).attr('id').split("-");	
			var id = "#comment-" + sp[3] + "-edit";
			
			if($('.e-comment-edit-save').length != 0) //prevent creating save button twice. 
			{
				return false;	
			}
					
			$(id).attr('contentEditable',true);
			$(id).after("<div class='e-comment-edit-save'><input data-target='"+url+"' id='e-comment-edit-save-"+sp[3]+"' class='button e-comment-edit-save' type='button' value='Save' /></div>");
			$('div.e-comment-edit-save').hide().fadeIn(800);
			$(id).addClass("e-comment-edit-active");
			$(id).focus();
			return false;		
		});
		
		
		$("input.e-comment-edit-save").live("click", function(){
			
			var url 	= $(this).attr("data-target");
			var sp 		= $(this).attr('id').split("-");	
			var id 		= "#comment-" + sp[4] + "-edit";
			var comment = $(id).text();
			
			$(id).attr('contentEditable',false);
			
		        $.ajax({
		            url: url + '?ajax_used=1&mode=edit',
		            type: 'POST',
		            data: {
		            	comment: comment,
		            	itemid: sp[4]
		            },
		            success:function (data) {
		            
		            	var a = $.parseJSON(data);
		            
		            	if(!a.error)
		            	{
		            	 	$("div.e-comment-edit-save")
		            	 	.hide()
		                    .addClass("e-comment-edit-success")
		                    .html(a.msg)
		                    .fadeIn('slow')
		                    .delay(1000)
		                    .fadeOut('slow');
		                    
						}
						else
						{
							 $("div.e-comment-edit-save")
		                    .addClass("e-comment-edit-error")
		                    .html(a.msg)
		                    .fadeIn('slow')
		                    .delay(1000)
		                    .fadeOut('slow');				
						}
		            	$(id).removeClass("e-comment-edit-active");
		            	
		            	setTimeout(function() {
						  $('div.e-comment-edit-save').remove();
						}, 2000);

		            //	.delay(1000);
		            //	alert(data);
		            	return;
		            }
		        });
		 
			
		});
		
		
		
		$(".e-comment-delete").live("click", function(){
			
			var url 	= $(this).attr("data-target");
			var sp 		= $(this).attr('id').split("-");	
			var id 		= "#comment-" + sp[3];
			var total 	= parseInt($("#e-comment-total").text());
	
			$.ajax({
			  type: 'POST',
			  url: url + '?ajax_used=1&mode=delete',
			  data: { itemid: sp[3] },
			  success: function(data) {
			  	var a = $.parseJSON(data);
			  
				if(!a.error)
				{
					$(id).hide('slow');
					$("#e-comment-total").text(total - 1);	
				}

			  }
			});
			
			return false;

		});
		
		$(".e-comment-approve").live("click", function(){
			
			var url 	= $(this).attr("data-target");
			var sp 		= $(this).attr('id').split("-");	
			var id 		= "#comment-status-" + sp[3];
	
			$.ajax({
			  type: 'POST',
			  url: url + '?ajax_used=1&mode=approve',
			  data: { itemid: sp[3] },
			  success: function(data) {
			  	
			  
			  	var a = $.parseJSON(data);
				
			  	
				if(!a.error)
				{		
					//TODO modify status of html on page. 	
					 $(id).text(a.html)
					 .fadeIn('slow')
					 .addClass('e-comment-edit-success'); //TODO another class?
				}
				else
				{
					alert(a.msg);	
				}
			  }
			});
			
			return false;

		});
		
		
		
		$(".e-rate-thumb").click(function(){
					
  			var src = $(this).attr("href");	
  			
  			var tmp = src.split('#');
  				id = tmp[1];
  				src = tmp[0];		
  			
  			$.ajax({
				type: "POST",
				url: src,
				data: { ajax_used: 1, mode: 'thumb' },
				dataType: "html",
				success: function(html) {
					
					if(html == '')
					{
						return false;	
					}
					
					var tmp = html.split('|');
  					up= tmp[0];
  					down = tmp[1];	
					
				    $('#'+id +'-up').text(up);
				    $('#'+id +'-down').text(down);
				    $(this).attr('title','Thanks for voting');
				    // alert('Thanks for liking');		
				}
			});
			
			return false; 	
		});

	
});