<?php
// ================================================
// tinymce PHP WYSIWYG editor control
// ================================================
// Configuration file
// ================================================
// Developed: j-cons.com, mail@j-cons.com
// Copyright: j-cons (c)2004 All rights reserved.
// ------------------------------------------------
//                                   www.j-cons.com
// ================================================
// v.1.0, 2004-10-04
// ================================================

// directory where tinymce files are located
// $tinyMCE_dir = "/e107_0.7/e107_handlers/tiny_mce/";

// base url for images
$tinyMCE_base_url = SITEURL;

// allowed extentions for uploaded image files
$tinyMCE_valid_imgs = array('gif', 'jpg', 'jpeg', 'png');

// allow upload in image library
$tinyMCE_upload_allowed = false;

// allow delete in image library
$tinyMCE_img_delete_allowed = false;

// image libraries
$tinyMCE_imglibs = array(
  array(
    'value'   => $IMAGES_DIRECTORY,
    'text'    => 'Images Root',
  ),
  array(
    'value'   => $IMAGES_DIRECTORY."newspost_images/",
    'text'    => 'Newspost Images',
  ),
  array(
    'value'   => $IMAGES_DIRECTORY."icons/",
    'text'    => 'Icons',
  ),
  array(
    'value'   => $IMAGES_DIRECTORY."banners/",
    'text'    => 'Banners',
  ),
  array(
    'value'   => $IMAGES_DIRECTORY."generic/",
    'text'    => 'Generic',
  ),
);
// file to include in img_library.php (useful for setting $tinyMCE_imglibs dynamically
// $tinyMCE_imglib_include = '';
?>