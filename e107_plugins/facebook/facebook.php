<?php

require_once('../../class2.php');

require_once(HEADERF);
//TODO include_lan();

$text = '<fb:serverfbml style="'.USER_WIDTH.'">
	    <script type="text/fbml">
	      <fb:fbml>
	          <fb:request-form
	                    action="'.e_SELF.'"
	                    method="GET"
	                    invite="true"
	                    type="'.SITENAME.'"
	                     content="'.SITENAME.'
	                 <fb:req-choice url=\''.e_SELF.'\'
	                       label=\'Become a Member!\' />
	              ">
	 
	                    <fb:multi-friend-selector
	                    showborder="true"
	                    
	                    actiontext="Select the friends you want to invite.">
	        </fb:request-form>
	      </fb:fbml>
	 
	    </script>
	  </fb:serverfbml>';
	  
$ns->tablerender("Facebook Connect Invite Friends",$text);
	  
require_once(FOOTERF);

?>