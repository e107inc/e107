//<?
$class = e107::getBB()->getClass('spoiler');

$spoiler_color = (defined("SPOILER_COLOR") ? SPOILER_COLOR : "#ff00ff");
return "<span class='{$class}' style='color:{$spoiler_color};background-color:{$spoiler_color}'>$code_text</span>";
