<?php
/***********************************************************************
** Title.........:        Thumbnail generator, with cache.
** Version.......:        1.0
** Author........:        Xiang Wei ZHUO <wei@zhuo.org>
** Filename......:        thumbs.php
** Last changed..:        1 Mar 2003
** Notes.........:        Configuration in config.inc.php

                    - if the thumbnail does not exists or the source
                                          file is newer, create a new thumbnail.

***********************************************************************/


require_once('config.inc.php');
require_once '../ImageEditor/Transform.php';

$img = $BASE_DIR.urldecode($_GET['img']);

if(is_file($img)) {
        make_thumbs(urldecode($_GET['img']));
}


function make_thumbs($img)
{
        global $BASE_DIR, $BASE_URL;


        $path_info = pathinfo($img);
        $path = $path_info['dirname']."/";
        $img_file = $path_info['basename'];

        $thumb = $path.'.'.$img_file;

        $img_info = getimagesize($BASE_DIR.$path.$img_file);
        $w = $img_info[0]; $h = $img_info[1];

        $nw = 96; $nh = 96;

        if($w <= $nw && $h <= $nh) {
                header('Location: '.$BASE_URL.$path.$img_file);
                exit();
        }

        if(is_file($BASE_DIR.$thumb)) {

                $t_mtime = filemtime($BASE_DIR.$thumb);
                $o_mtime = filemtime($BASE_DIR.$img);

                if($t_mtime > $o_mtime) {
                        //echo $BASE_URL.$path.'.'.$img_file;
                        header('Location: '.$BASE_URL.$path.'.'.$img_file);
                        exit();
                }
        }

        $img_thumbs = Image_Transform::factory(IMAGE_CLASS);
        $img_thumbs->load($BASE_DIR.$path.$img_file);


        if ($w > $h)
         $nh = unpercent(percent($nw, $w), $h);
    else if ($h > $w)
         $nw = unpercent(percent($nh, $h), $w);

        $img_thumbs->resize($nw, $nh);

        $img_thumbs->save($BASE_DIR.$thumb);
        $img_thumbs->free();

        chmod($BASE_DIR.$thumb, 0666);

        if(is_file($BASE_DIR.$thumb)) {
                //echo "Made:".$BASE_URL.$path.'.'.$img_file;
                header('Location: '.$BASE_URL.$path.'.'.$img_file);
                exit();
        }
}
function percent($p, $w)
    {
    return (real)(100 * ($p / $w));
    }

function unpercent($percent, $whole)
    {
    return (real)(($percent * $whole) / 100);
    }

?>