<?php

if (!defined('e107_INIT')) { exit; }

// Moc - temporary solution until a global/common clone method is created which replaces duplicateHTML()
e107::js("footer-inline",  "

    $('#addoption').click(function () {
 
      $('#poll_answer').clone().appendTo('#pollsection').find('input[type=text]').val('');
 
    });
 ");

?>