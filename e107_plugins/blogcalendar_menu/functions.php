<?php
/******************************************************************\   
 *                                                                *
 *  :: e107 blogcal addon ::                                      *                            
 *                                                                *   
 *  file:     functions.php                                       *   
 *  author:   Thomas Bouve                                        *   
 *  email:    crahan@gmx.net                                      *   
 *  Date:     2004-02-02                                          *     
 *                                                                *   
\******************************************************************/   

// format a date as yyyymmdd
function formatDate($year,$month,$day=""){  
    $date = $year;   
    $date .= (strlen($month)<2)?"0".$month:$month;  
    $date .= (strlen($day)<2 && $day != "")?"0".$day:$day;
    return $date;
}  
?>