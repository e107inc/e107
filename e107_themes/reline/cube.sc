parse_str($parm);
$top = isset($top) ? $top : '-76px';
$left = isset($left) ? $left : '-49px';

return "<div style='position: relative; left: 0; top: 0'>
<img src='".THEME_ABS."images/cube.png' style='position: absolute; top: ".$top."; left: ".$left."; width: 96px; height: 108px; display: block' alt='' />
</div>";