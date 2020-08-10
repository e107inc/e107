<?php

if (!defined('e107_INIT')) { exit; }

// #647 - temporary solution until a global/common clone method is created which replaces duplicateHTML()
if(!e107::isInstalled('poll'))
{
	return;
}

if(e107::getPlugPref('forum', 'poll') != '255')
{
	$poll_active = true;
}

if(defset('e_CURRENT_PLUGIN') == "forum" && e107::isInstalled('poll') && $poll_active)
{
	e107::js("footer-inline",  "

	    $('#addoption').click(function () {
	      $('#poll_answer').clone().appendTo('#pollsection').find('input[type=text]').val('');
	    });
	");
}


