<?php

/*
$fbPref = e107::getPlugPref('facebook');

 // print_a($fbPref);
// $appId = $fbPref['appId'];
echo " ";
$code = <<<EOT
$(function() {
	$('body').append('<div id="fb-root"></div>
	<div class="fb-login-button">Login with Facebook</div>
		
	<div 
        class="fb-registration" 
        data-fields="[{'name':'name'}, {'name':'email'},
          {'name':'favorite_car','description':'What is your favorite car?',
            'type':'text'}]" 
        data-redirect-uri="URL_TO_LOAD_AFTER_REGISTRATION"
      </div>
	');


	window.fbAsyncInit = function() {
	    FB.init({
			appId      : '{$fbPref['appId']}',
			status     : true, 
			cookie     : true,
			xfbml      : true,
			oauth      : true
	    		    	
		});
	};

	$.ajax({
	type: "GET",
	url: document.location.protocol + '//connect.facebook.net/en_US/all.js' ,
//	success: callback,
	dataType: "script",
	cache: true
	});
});
EOT;


$core = "";

$perms = "email";

$code = <<<EOT



EOT;


$link = "https://www.facebook.com/dialog/oauth?client_id={$fbPref['appId']}
&redirect_uri=".e_SELF."&scope={$perms}&response_type=token";
*/
// echo "<a href='{$link}'>Login with Facebook</a>";


// e107::js('inline',$code,'jquery',3);

?>