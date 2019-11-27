/**
@name jQuery pwdMeter 1.0.1
@author Shouvik Chatterjee (mailme@shouvik.net) Modified by e107 Inc. 
@date 31 Oct 2010
@modify 31 Dec 2010
@license Free for personal and commercial use as long as the author's name retains
 
*/
(function(jQuery){

jQuery.fn.pwdMeter = function(options){

	// FIXME options for all ID's (auto-generated from 'passwordBox' id - used as a base)
	options = jQuery.extend({
	
		minLength: 6,
		displayGeneratePassword: false,
		generatePassText: 'Generate',
		generatePassClass: 'GeneratePasswordLink',
		randomPassLength: 12,
        passwordBox: this
	
	}, options);

	var pwdObj = this;
	
//	$(pwdObj).after("<span class='progress progress-info span2' ><span class='bar' id=\"pwdMeter\" style='width:20%'></span></span>");

	return this.each(function(index){
	
		$(this).keyup(function(){
			evaluateMeter();
		});
		
		function evaluateMeter(){

			var passwordStrength   = 0;
			var password = $(options.passwordBox).val();
			
			// fix - when password is shown
			if($("#showPwdBox").length > 0) {
				if($('#showPwdBox').is(':visible')) {
					password = $('#showPwdBox').val();
					$(options.passwordBox).val(password);
				}
				else $("#showPwdBox").val(password);
			}
			
			if ((password.length >3) && (password.length <=5)) passwordStrength=1;
		
			if (password.length >= options.minLength) passwordStrength++;

			if ((password.match(/[A-Z]/)) ) passwordStrength++;
			
			if ((password.match(/[a-z]/)) ) passwordStrength++;

			if (password.match(/\d+/) && password.length > 5) passwordStrength++;

			if (password.match(/.[!,@,#,$,%,^,&,*,?,_,~,-,(,)]/))	passwordStrength++;

			if (password.length > 11) passwordStrength++;
		
			$('#pwdColor').removeClass();
			$('#pwdColor').addClass('progress');
			 $('#pwdMeter').css("width",'1%');
            $('#pwdMeter').removeClass("progress-bar-danger progress-bar-warning progress-bar-success");
			// $('#pwdMeter').removeStyle();
		
			switch(passwordStrength){
			case 1:
			  $('#pwdColor').addClass('progress-danger');
              $('#pwdMeter').addClass('progress-bar-danger'); //BS3
			  $('#pwdMeter').css("width",'10%');
			  $('#pwdStatus').text('Very Weak');
			  break;
			case 2:
			  $('#pwdColor').addClass('progress-danger');
                $('#pwdMeter').addClass('progress-bar-danger'); //BS3
			  $('#pwdMeter').css("width",'25%');
			  $('#pwdStatus').text('Weak');
			  
			  break;
			case 3:
			 	$('#pwdColor').addClass('progress-warning');
                $('#pwdMeter').addClass('progress-bar-warning'); //BS3
			    $('#pwdMeter').css("width",'30%');
				  $('#pwdStatus').text('Medium');
			  break;
			case 4:
				$('#pwdColor').addClass('progress-warning');
                $('#pwdMeter').addClass('progress-bar-warning'); //BS3
			    $('#pwdMeter').css("width",'50%');
			  $('#pwdStatus').text('Medium');
			  break;
			 case 5:
			  $('#pwdColor').addClass('progress-success');
              $('#pwdMeter').addClass('progress-bar-success'); //BS3
			  $('#pwdMeter').css("width",'75%');
			  $('#pwdStatus').text('Strong');
			  break;	
			case 6:
			  $('#pwdColor').addClass('progress-success');
               $('#pwdMeter').addClass('progress-bar-success'); //BS3
			  $('#pwdMeter').css("width",'100%');
			  $('#pwdStatus').text('Very Strong');
			  break;		  		  		  
			default:
			 $('#pwdMeter').css("width",'0px');
		//	  $('#pwdStatus').text('Strong');
			}		
		
		}
		
	
		if(options.displayGeneratePassword)
		{
		//	$('#pwdMeter').before('&nbsp;<a href="#" id="Spn_PasswordGenerator" class="'+options.generatePassClass+'">'+ options.generatePassText +'</a><br />');
			
			$(pwdObj).after('<input id="showPwdBox" type="text" class="'+ $(pwdObj).attr('class')  +'" style="display:none" size="'+ $(pwdObj).attr('size')  +'" maxlength="'+ $(pwdObj).attr('maxlength')  +'"  value="" />');
			
			$('#showPwdBox').keyup(function(){
				evaluateMeter();
			});
       		
		}
		
		$('#Spn_PasswordGenerator').click(function(event){
			event.preventDefault();
			var randomPassword = random_password();
			$('#Spn_NewPassword').text(randomPassword);
			$(options.passwordBox).val(randomPassword);
			if($('#showPwdBox').length>0) $('#showPwdBox').val(randomPassword);
			evaluateMeter();
		});
		
		$("#showPwd").click(function (event) {
			event.preventDefault();
			var id = $(pwdObj).attr("type");
			$(pwdObj).toggle();
			$("#showPwdBox").toggle();
			var text = $(this).text() == 'Show' ? 'Hide' : 'Show';
    		$(this).text(text);

		});
		
		function random_password() 
	    {
	    	var length = options.randomPassLength;	
	    	
		    var pchars = "abcdefghijklmnopqrstuvwxyz";
		    var pchars1 = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		    var pchars2 = "1234567890";
		    var pchars3 = "~!@#$%^&*-<>=_+,./?;':";
		     
		    ret_pass = "";
		    for(x=0;x<Math.abs(length/4);x++) {
		    i = Math.floor(Math.random() * pchars.length);
		    ret_pass += pchars.charAt(i);
		    }
		    for(x=0;x<Math.abs(length/4);x++) {
		    i = Math.floor(Math.random() * pchars1.length);
		    ret_pass += pchars1.charAt(i);
		    }
		    for(x=0;x<Math.abs(length/4);x++) {
		    i = Math.floor(Math.random() * pchars2.length);
		    ret_pass += pchars2.charAt(i);
		    }
		    for(x=0;x<Math.abs(length/4);x++) {
		    i = Math.floor(Math.random() * pchars3.length);
		    ret_pass += pchars3.charAt(i);
		    }
		    // shuffle the string a bit
		    var a = ret_pass.split(""),
		    n = a.length;
		    for(var i = n - 1; i > 0; i--) {
		    var j = Math.floor(Math.random() * (i + 1));
		    var tmp = a[i];
		    a[i] = a[j];
		    a[j] = tmp;
		    }
		    return a.join("");
	    }

	
		/*
		function random_password_old() {
			var allowed_chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz!?$?%^&*()_-+={[}]:;@~#|\<,>.?/";
			var allowed_upper = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZ";
			var allowed_lower = "abcdefghiklmnopqrstuvwxyz";
			var allower_symb  = "!?$?%^&*()_-+={[}]:;@~#|\<,>.?";
			var pwd_length = options.randomPassLength;
			var rnd_pwd = '';
			for (var i=0; i<pwd_length; i++) {
				var rnd_num = Math.floor(Math.random() * allowed_chars.length);
				rnd_pwd += allowed_chars.substring(rnd_num,rnd_num+1);
			}
			return rnd_pwd;
		}
		*/		
	
	});

}
/**
 * ALLWAYS add a semicolon at the end, otherwise 
 * it may cause issues when js is cached! 
 * see issue e107inc/e107#2265
 */
})(jQuery);
