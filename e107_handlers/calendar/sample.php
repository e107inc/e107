<?php
require_once('../../class2.php');
require_once(e_HANDLER."calendar/calendar_class.php");
$cal = new DHTML_Calendar(true);
function headerjs()
{
	global $cal;
	return $cal->load_files();
}
require_once(HEADERF);

echo "
<table style='border:2px solid'>
<tr>
<td>
";
echo $cal->make_input_field(
           // calendar options go here; see the documentation and/or calendar-setup.js
           array('firstDay'       => 1, // show Monday first
                 'showsTime'      => true,
                 'showOthers'     => true,
                 'ifFormat'       => '%Y/%m/%d %I:%M %P',
                 'weekNumbers'    => false,
                 'timeFormat'     => '12'),
           // field attributes go here
           array('style'       => 'color: #840; background-color: #ff8; border: 1px solid #000; text-align: center',
                 'name'        => 'date1',
                 'value'       => strftime('%Y/%m/%d %I:%M %P', strtotime('now'))));


echo "</td></tr><tr><td>";
unset($cal_options);
unset($cal_attrib);
$cal_options['firstDay'] = 0;
$cal_options['showsTime'] = false;
$cal_options['showOthers'] = false;
$cal_options['weekNumbers'] = true;
$cal_attrib['class'] = "tbox";
$cal_attrib['name'] = "date2";
$cal_attrib['value'] = "[select date]";
echo $cal->make_input_field($cal_options, $cal_attrib);
echo "
</td>
</tr>
</table>
";
require_once(FOOTERF);
                 
?>
