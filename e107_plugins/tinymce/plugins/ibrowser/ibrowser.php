<?php
// ================================================
// tinymce PHP WYSIWYG editor control
// ================================================
// Image library dialog
// ================================================
// Developed: j-cons.com, mail@j-cons.com
// Copyright: j-cons (c)2004 All rights reserved.
// ------------------------------------------------
//                                   www.j-cons.com
// ================================================
// $Revision: 1.2 $Date: 2004/10/04
// ================================================
//
// $Source: /cvs_backup/e107_0.8/e107_plugins/tinymce/plugins/ibrowser/ibrowser.php,v $
// $Revision: 1.2 $
// $Date: 2007-08-09 19:09:56 $
// $Author: e107steved $
// +----------------------------------------------------------------------------+
// Major Re-work by CaMer0n


// unset $tinyMCE_imglib_include
require_once("../../../../class2.php");
if (!defined('e107_INIT')) { exit; }
unset($tinyMCE_imglib_include);

// include image library config settings
include 'config.php';

$request_uri = urldecode(empty($_POST['request_uri'])?(empty($_GET['request_uri'])?'':$_GET['request_uri']):$_POST['request_uri']);

// if set include file specified in $tinyMCE_imglib_include
if (!empty($tinyMCE_imglib_include))
{
  include $tinyMCE_imglib_include;
}


$imglib = isset($_POST['lib'])?$_POST['lib']:'';
if (empty($imglib) && isset($_GET['lib'])) $imglib = $_GET['lib'];

$value_found = false;
// callback function for preventing listing of non-library directory
function is_array_value($value, $key, $_imglib)
{
  global $value_found;
  if (is_array($value)) array_walk($value, 'is_array_value',$_imglib);
  if ($value == $_imglib){
    $value_found=true;
  }
}
array_walk($tinyMCE_imglibs, 'is_array_value',$imglib);

if (!$value_found || empty($imglib))
{
  $imglib = $tinyMCE_imglibs[0]['value'];
}
$lib_options = liboptions($tinyMCE_imglibs,'',$imglib);


$img = isset($_POST['imglist'])? $_POST['imglist']:'';

$preview = e_IMAGE."generic/blank.gif";

$errors = array();

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>{$lang_ibrowser_title}</title>
<script type="text/javaScript" src="../../tiny_mce_popup.js"></script>
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET ?>" />
<script type="text/javascript">
	// click ok - select picture or save changes
	function selectClick() {

	 //	if (validateParams()) {
			if (document.forms[0].src.value !='') {

				var src = document.forms[0].src.value;
				var alt = document.forms[0].alt.value;
		 		var border = document.forms[0].border.value;
		 		var width = document.forms[0].width.value;
		 		var height = document.forms[0].height.value;

				var margleft = document.forms[0].margin_left.value;
                var margright = document.forms[0].margin_right.value;
                var margtop = document.forms[0].margin_top.value;
                var margbottom = document.forms[0].margin_bottom.value;

		  		var cssfloat = document.forms[0].align.options[document.forms[0].align.selectedIndex].value;
				var css_style = "";

               	css_style = 'width:' + width + 'px; height:' + height + 'px; border:' + border + 'px solid black; ';
                css_style = (cssfloat) ? css_style + 'float: ' + cssfloat + '; ' : css_style;
				css_style = (margleft != 0) ? css_style + 'margin-left:' + margleft + 'px; ' : css_style;
				css_style = (margright != 0) ? css_style + 'margin-right:' + margright + 'px; ' : css_style;
				css_style = (margtop != 0) ? css_style + 'margin-top:' + margtop + 'px; ' : css_style;
				css_style = (margbottom != 0) ? css_style + 'margin-bottom:' + margbottom + 'px; ' : css_style;

				var html = '<img src=\''+ src +'\' alt=\''+ alt +'\'  style=\'' + css_style + '\'  />';

  //				alert(html);
			  	tinyMCE.execCommand('mceInsertContent',false,html);
		   		tinyMCEPopup.close();

			} else {
			alert(tinyMCE.getLang('lang_ibrowser_error')+ ' : '+ tinyMCE.getLang('lang_ibrowser_errornoimg'));}
      	}
  //	}

	// validate input values
	function validateParams() {
    	// check numeric values for attributes
    	if (isNaN(parseInt(document.getElementById("width").value)) && document.getElementById("width").value != '') {
 				alert(tinyMCE.getLang('lang_ibrowser_error')+ ' : '+ tinyMCE.getLang('lang_ibrowser_error_width_nan'));
 				document.getElementById("width").focus();
      		return false;}

    	if (isNaN(parseInt(document.getElementById("height").value)) && document.getElementById("height").value != '') {
 				alert(tinyMCE.getLang('lang_ibrowser_error')+ ' : '+ tinyMCE.getLang('lang_ibrowser_error_height_nan'));
      		document.getElementById("height").focus();
     		return false;}

    	if (isNaN(parseInt(document.getElementById("border").value)) && document.getElementById("border").value != '') {
			alert(tinyMCE.getLang('lang_ibrowser_error')+ ' : '+ tinyMCE.getLang('lang_ibrowser_error_border_nan'));
      		document.getElementById("border").focus();
      		return false;}

    	if (isNaN(parseInt(document.getElementById("hspace").value)) && document.getElementById("hspace").value != '') {
			alert(tinyMCE.getLang('lang_ibrowser_error')+ ' : '+ tinyMCE.getLang('lang_ibrowser_error_hspace_nan'));
			document.getElementById("hspace").focus();
      		return false;}

		if (isNaN(parseInt(document.getElementById("vspace").value)) && document.getElementById("vspace").value != '') {
			alert(tinyMCE.getLang('lang_ibrowser_error')+ ' : '+ tinyMCE.getLang('lang_ibrowser_error_vspace_nan'));
      		document.getElementById("vspace").focus();
      		return false;}

	return true;

	}

	// delete image
	function deleteClick()
	{
		if (document.libbrowser.imglist.selectedIndex>=0)
	  	{
			if (confirm(tinyMCE.getLang('lang_ibrowser_confirmdelete')))
			{
				document.libbrowser.lib_action.value = 'delete';
				document.libbrowser.submit();
			}
	  	}
	}

// set picture attributes on change
	function selectChange(obj)
	{
		var formObj = document.forms[0];
//		formObj.src.value = '<?php echo $tinyMCE_base_url.$imglib?>'+obj.options[obj.selectedIndex].value;
//		formObj.width.value = obj.options[obj.selectedIndex].img_width;
//		formObj.height.value = obj.options[obj.selectedIndex].img_height;
//		formObj.size.value = obj.options[obj.selectedIndex].f_size;
//		formObj.alt.value = obj.options[obj.selectedIndex].value;
		var splitvar = obj.options[obj.selectedIndex].value.split("|");
		formObj.src.value = '<?php echo $tinyMCE_base_url.$imglib?>'+splitvar[3];
		formObj.width.value = splitvar[0];
		formObj.height.value = splitvar[1];
		formObj.size.value = splitvar[2];
		formObj.alt.value = splitvar[3];

		owidth = eval(formObj.width.value);
		oheight = eval(formObj.height.value);
		updateStyle()
	}

	// init functions
	function init()
	{
		// if existing image (image properties)
		if (tinyMCE.getWindowArg('src') != '') {

			var formObj = document.forms[0];
			for (var i=0; i<document.forms[0].align.options.length; i++) {
				if (document.forms[0].align.options[i].value == tinyMCE.imgElement.style.cssFloat)
				document.forms[0].align.options.selectedIndex = i;
			}

			formObj.src.value = tinyMCE.getWindowArg('src');
			formObj.alt.value = tinyMCE.imgElement.alt;
			formObj.border.value = tinyMCE.imgElement.style.borderLeftWidth.replace('px','');
        	formObj.width.value = tinyMCE.imgElement.style.width.replace('px','');
			formObj.height.value = tinyMCE.imgElement.style.height.replace('px','');
			formObj.margin_left.value = tinyMCE.imgElement.style.marginLeft.replace('px','');
			formObj.margin_right.value = tinyMCE.imgElement.style.marginRight.replace('px','');
			formObj.margin_top.value = tinyMCE.imgElement.style.marginTop.replace('px','');
			formObj.margin_bottom.value = tinyMCE.imgElement.style.marginBottom.replace('px','');

			formObj.size.value = 'n/a';
			owidth = eval(formObj.width.value);
			oheight = eval(formObj.height.value);

			frameID = "imgpreview";
			//document.all(frameID).src = tinyMCE.getWindowArg('src');
			document.getElementById("imgpreview").src = tinyMCE.getWindowArg('src');
			updateStyle();
		}

		window.focus();
	}

	// updates style settings
	function updateStyle() {
	 //	if (validateParams()) {
		  //	alert('val=' + document.getElementById('wrap').style.marginLeft);


			document.getElementById('wrap').style.marginLeft = document.libbrowser.margin_left.value;
			document.getElementById('wrap').style.marginRight = document.libbrowser.margin_right.value;
			document.getElementById('wrap').style.marginTop = document.libbrowser.margin_top.value;
			document.getElementById('wrap').style.marginBottom = document.libbrowser.margin_bottom.value;
            document.getElementById('wrap').style.cssFloat = document.libbrowser.align.value;
			document.getElementById('wrap').style.borderWidth = document.libbrowser.border.value;
			document.getElementById('wrap').alt = document.libbrowser.alt.value;
	 //	}
	}

	// change picture dimensions
	var oheight; // original width
	var owidth;  // original height

	function changeDim(sel) {
		var formObj = document.forms[0];
		if (formObj.src.value!=''){
			f=oheight/owidth;
			if (sel==0){
				formObj.width.value = Math.round(formObj.height.value/f);
			} else {
				formObj.height.value= Math.round(formObj.width.value*f);}
		}
	}

	function resetDim() {
 		var formObj = document.forms[0];
		formObj.width.value = owidth;
		formObj.height.value = oheight;
	}
	function show_image(obj) {
		var formObj = document.forms[0];
		var splitvar = obj.options[obj.selectedIndex].value.split("|");
		formObj.src.value = splitvar[3];
	  // 	alert('<?php echo $tinyMCE_base_url.$imglib?>' + formObj.src.value);
		if (splitvar[3]) imgpreview.location.href = '<?php echo $tinyMCE_base_url.$imglib?>' + formObj.src.value;
	}
</script>
</head>
<body onLoad="init();">
<script type="text/javascript">
    window.name = 'imglibrary';
</script>
<form name="libbrowser" method="post" action="ibrowser.php?request_uri=<?php echo $_GET['request_uri']?>" enctype="multipart/form-data" target="imglibrary">
  <input type="hidden" name="request_uri" value="<?php echo urlencode($request_uri)?>" />
  <input type="hidden" name="lib_action" value="" />
  <fieldset style= "padding: 5 5 5 5; margin-top: -5px;">
  <legend>{$lang_ibrowser_img_sel}</legend>
  <table width="440" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td><table width="100%"  border="0" cellpadding="2" cellspacing="0">
          <tr>
            <td width="210"><strong>{$lang_ibrowser_library}:</strong></td>
            <td width="5">&nbsp;</td>
            <td width="210"><strong>{$lang_ibrowser_preview}:</strong></td>
          </tr>
          <tr>
            <td><select name="lib" size="1" style="width: 100%;" onChange="libbrowser.submit();">
                <?php echo $lib_options?>
              </select></td>
            <td>&nbsp;</td>
            <td width="210" rowspan="3" align="left" valign="top"><iframe name="imgpreview" id="imgpreview" class="previewWindow" src="<?php echo $preview?>" style="width: 100%; height: 100%;" scrolling="Auto" marginheight="0" marginwidth="0" frameborder="0"></iframe>
            </td>
          </tr>
          <tr>
            <td><strong>{$lang_ibrowser_images}:</strong></td>
            <td>&nbsp;</td>
          </tr>
          <tr>
            <td><?php
    if (!preg_match('#/$#', $_SERVER['DOCUMENT_ROOT'])){
    //  $_root = $_SERVER['DOCUMENT_ROOT'].'/';
		$_root = e_BASE;
   } else {
   //   $_root = $_SERVER['DOCUMENT_ROOT'];
      $_root = e_BASE;
	}
  //  $d = @dir($_root.$imglib);
  //	echo "<Br >dir=".$_root.$imglib;
	$d = @dir(e_BASE.$imglib);

  ?>
          <select name="imglist" size="15" style="width: 100%;"
    onChange="show_image(this);selectChange(this);" ondblclick="selectClick();">
            <?php
    	if ($d)
    {
	  $i = 0;
      while (false !== ($entry = $d->read())) {
        $ext = strtolower(substr(strrchr($entry,'.'), 1));
        if (is_file($_root.$imglib.$entry) && in_array($ext,$tinyMCE_valid_imgs))
        {
			$arr_tinyMCE_image_files[$i][file_name] = $entry;
			$i++;
        }
      }
      $d->close();
	  // sort the list of image filenames alphabetically.
	  sort($arr_tinyMCE_image_files);
	  for($k=0; $k<count($arr_tinyMCE_image_files); $k++){
      $entry = $arr_tinyMCE_image_files[$k][file_name];
	  $size = getimagesize($_root.$imglib.$entry);
	  $fsize = filesize($_root.$imglib.$entry);
   ?>
            <option  value="<?php echo $size[0]; ?>|<?php echo $size[1]; ?>|<?php echo filesize_h($fsize,2); ?>|<?php echo $entry?>" <?php echo ($entry == $img)?'selected':''?>><?php echo $entry?></option>
            <?php
	  }
    }
    else
    {
      $errors[] = '{$lang_ibrowser_errornodir}';
    }
  ?>
          </select></td>
            <td>&nbsp;</td>
          </tr>
          <tr>
            <td colspan="3"><table width="100%"  border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td width="40%"><?php if ($tinyMCE_img_delete_allowed) { ?>
                    <input type="button" value="{$lang_ibrowser_delete}" class="bt" onClick="deleteClick();" />
                    <?php } ?></td>
                  <td align="right"><input type="button" name="selectbt" value="{$lang_ibrowser_select}" class="bt" onClick="selectClick();" />
                    <input type="button" value="{$lang_ibrowser_cancel}" class="bt" onClick="window.close();" /></td>
                </tr>
              </table></td>
          </tr>
        </table></td>
    </tr>
  </table>
  </fieldset>
  <fieldset style= "padding: 5 5 5 5; margin-top: 10px;">
  <legend>{$lang_ibrowser_img_info}</legend>
  <table style='width:440px;border:0px' cellspacing="0" cellpadding="0">
    <tr>
      <td><table style='width:440px;border:0px' cellpadding="2" cellspacing="0">
          <tr>
            <td style='width:80px'>{$lang_ibrowser_src}:</td>
            <td colspan="5"><input name="src" type="text" id="src" value="" style="width: 100%;" /></td>
          </tr>
          <tr>
            <td>{$lang_ibrowser_alt}:</td>
            <td colspan="5"><input name="alt" type="text" id="alt" value="" style="width: 100%;" onChange="updateStyle()" /></td>
          </tr>
          <tr>
            <td>{$lang_ibrowser_align}:</td>
            <td colspan="3"><select name="align" style="width: 100%;" onChange="updateStyle()">
                <option value="">{$lang_insert_image_align_default}</option>
                <option value="left">{$lang_insert_image_align_left}</option>
                <option value="right">{$lang_insert_image_align_right}</option>
              </select></td>
            <td width="5">&nbsp;</td>
            <td rowspan="8" align="left" valign="top" style='width:210px;overflow:hidden'>
			<div id="stylepreview" style="padding:10px; width: 200px; height:100%; overflow:hidden; background-color:#ffffff; font-size:8px" class="previewWindow">
                <p><img id="wrap" src="images/textflow.gif" width="45" height="45" alt="" style="border:0px; float:right; margin-left:0px; margin-right:0px; margin-top:0px; margin-bottom:0px" />Lorem
                  ipsum, Dolor sit amet, consectetuer adipiscing loreum ipsum
                  edipiscing elit, sed diam nonummy nibh euismod tincidunt ut
                  laoreet dolore magna aliquam erat volutpat.Loreum ipsum edipiscing
                  elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore
                  magna aliquam erat volutpat. Ut wisi enim ad minim veniam,
                  quis nostrud exercitation ullamcorper suscipit. Lorem ipsum,
                  Dolor sit amet, consectetuer adipiscing loreum ipsum edipiscing
                  elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore
                  magna aliquam erat volutpat.</p>
              </div>
			</td>
          </tr>
          <tr>
            <td>{$lang_ibrowser_size}:</td>
            <td colspan="3"><input name="size" type="text" id="size" value="" readonly="true" style="width: 100%;" /></td>
            <td>&nbsp;</td>
          </tr>
          <tr>
            <td>{$lang_ibrowser_height}:</td>
            <td width="40"><input name="height" type="text" id="height" value="" size="5" maxlength="4" style="text-align: right;" onChange="changeDim(0)" /></td>
            <td width="25" rowspan="2" align="left" valign="middle"><a href="#" onClick="resetDim();" ><img src="images/constrain.gif" alt="{$lang_ibrowser_reset}" width="22" height="29" border="0"></a></td>
            <td rowspan="2">&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
          <tr>
            <td>{$lang_ibrowser_width}:</td>
            <td><input name="width" type="text" id="width" value="" size="5" maxlength="4" style="text-align: right;" onChange="changeDim(1)" /></td>
            <td>&nbsp;</td>
          </tr>
          <tr>
            <td>{$lang_ibrowser_border}:</td>
            <td colspan="3">
			<input name="border" type="text" id="border" value="0" size="5" maxlength="4" style="text-align: right;" onchange="updateStyle()" />px
			</td>
            <td>&nbsp;</td>
          </tr>
          <tr>
            <td>Margin-left:</td>
            <td colspan="3"><input name="margin_left" type="text" id="margin_left" value="0" size="5" maxlength="4" style="text-align: right;" onchange="updateStyle()" />px
			</td>
            <td>&nbsp;</td>
          </tr>
          <tr>
            <td>Margin-Right:</td>
            <td colspan="3"><input name="margin_right" type="text" id="margin_right" value="0" size="5" maxlength="4" style="text-align: right;" onchange="updateStyle()" />px
			</td>
            <td>&nbsp;</td>
          </tr>

          <tr>
            <td>Margin-Top:</td>
            <td colspan="3"><input name="margin_top" type="text" id="margin_top" value="0" size="5" maxlength="4" style="text-align: right;" onchange="updateStyle()" />px
			</td>
            <td>&nbsp;</td>
          </tr>
          <tr>
            <td>Margin-Bottom:</td>
            <td colspan="3"><input name="margin_bottom" type="text" id="margin_bottom" value="0" size="5" maxlength="4" style="text-align: right;" onchange="updateStyle()" />px
			</td>
            <td>&nbsp;</td>
          </tr>

        </table></td>
    </tr>
  </table>
  </fieldset>

</form>
</body>
</html>
<?php
function liboptions($arr, $prefix = '', $sel = '')
{
  $buf = '';
  foreach($arr as $lib) {
    $buf .= '<option value="'.$lib['value'].'"'.(($lib['value'] == $sel)?' selected':'').'>'.$prefix.$lib['text'].'</option>'."\n";
  }
  return $buf;
}


// Return the human readable size of a file
// @param int $size a file size
// @param int $dec a number of decimal places

function filesize_h($size, $dec = 1)
{
	$sizes = array('byte(s)', 'kb', 'mb', 'gb');
	$count = count($sizes);
	$i = 0;

	while ($size >= 1024 && ($i < $count - 1)) {
		$size /= 1024;
		$i++;
	}

	return round($size, $dec) . ' ' . $sizes[$i];
}

?>
