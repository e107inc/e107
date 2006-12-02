parse_str($parm);
$top = isset($top) ? $top : '-76px';
$left = isset($left) ? $left : '-49px';

return "<div style='position: relative; left: 0; top: 0'>
<img src='".THEME_ABS."images/cube.png' style='position: absolute; top: ".$top."; left: ".$left."; width: 96px; height: 108px; display: block' alt='' />
</div>";

// {CUBE=top=-76px&left=-49px}
// Register custom theme shortcodes

// $register_sc[] = 'CUBE'; // use as {CUBE} in your templates (e107_themes/your_theme/cube.sc)

