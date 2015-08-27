/**************************************************************/
/* Prepares the cv to be dynamically expandable/collapsible   */
/**************************************************************/
function pageNavList() {
	
	$('ul.page-nav').find('li:has(ul)').attr('title', 'Expand/Collapse');

    $('ul.page-nav').find('li:has(ul)')
    .click( function(event) {
  
        if (this == event.target) {
            $(this).toggleClass('expanded');
            $(this).children('ul').toggle('medium');
         
        }
        return false;
    })
    .addClass('collapsed')
    .children('ul').hide();

    //Create the button funtionality
    $('#page-nav-expand')
    .unbind('click')
    .click( function() {
        $('.collapsed').addClass('expanded');
        $('.collapsed').children().show('medium');
        
    });
    
    //FIXME - Collapses too many items, it should leave the primary <li> items visible. 
    $('#page-nav-collapse')
    .unbind('click')
    .click( function() {
        $('.collapsed').removeClass('expanded');
        $('.collapsed').children().hide('medium');
    });
    
}


/**************************************************************/
/* Functions to execute on loading the document               */
/**************************************************************/
$(document).ready( function() 
{
    pageNavList();
});