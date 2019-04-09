<?php

require 'general.php';

function escape_text($text)
{
    $text = str_replace('"', '\"', $text);
    $text = str_replace("\\\\", "\\\\\\\\\\\\\\\\", $text);
    $text = str_replace("'", "'\\\\\\\\\\\\\\''", $text);
    $text = str_replace("%", "\\\\\%", $text);
    $text = str_replace(".", "\\\\.", $text);
    $text = str_replace(",", "\\\\,", $text);
    $text = str_replace(":", "\\\\:", $text);
    return $text;
}

function get_zoompan_filter($animation, $seconds)
{
    $pp = 'on';
    $pm = "11*${seconds}-on-1";

    $filter = "zoompan=fps=11:d=11*${seconds}:s=700x700:";
    if ('pan-down-and-right' == $animation) {
        $filter .= "z=iw/(iw-11*${seconds}):x=${pp}:y=${pp}";
    } elseif ('pan-down-and-left' == $animation) {
        $filter .= "z=iw/(iw-11*${seconds}):x=${pm}:y=${pp}";
    } elseif ('pan-up-and-right' == $animation) {
        $filter .= "z=iw/(iw-11*${seconds}):x=${pp}:y=${pm}";
    } elseif ('pan-up-and-left' == $animation) {
        $filter .= "z=iw/(iw-11*${seconds}):x=${pm}:y=${pm}";
    } else {
        $filter .= 'z=(iw+trunc(on/2)*4)/iw:x=trunc(on/2)*2:y=trunc(on/2)*2';
    }

    return $filter;
}

function get_text_filter($text)
{
    return
        "drawtext=box=1:boxcolor=black@0.5:fontcolor=white:fontsize=28:text='".
        escape_text($text).
        "':y=h-160:boxborderw=15:x=15:fontfile='arialbd.ttf'";
}

function get_top_bar_text_filter($text)
{
    return
        'drawbox=w=in_w:h=80:c=black:t=fill,'.
        "drawtext=fontsize=32:x=(w-tw)/2:y=(80-th)/2:fontcolor=white:text='".
        escape_text($text)."':fontfile='arialbd.ttf'";
}

function get_bottom_bar_text_filter($text)
{
    return
        'drawbox=y=in_h-80:w=in_w:h=80:c=black:t=fill,'.
        "drawtext=fontsize=32:x=(w-tw)/2:y=h-80+(80-th)/2:fontcolor=white:text='".
        escape_text($text)."':fontfile='arialbd.ttf'";
}

function set_name_to_last($vid_name)
{
    global $filters;
    $filters[count($filters) - 1] .= $vid_name;
}

function remove_name_to_last($len)
{
    global $filters;
    $filters[count($filters) - 1] = substr($filters[count($filters) - 1], 0, strlen($filters[count($filters) - 1]) - $len);
}

function merge_videos_with_slide_effect($vid1, $len1, $vid2, $len2, $i)
{
    global $filters;

    $split1_1 = "[split${i}_1_1]";
    $split1_2 = "[split${i}_1_2]";
    $split2_1 = "[split${i}_2_1]";
    $split2_2 = "[split${i}_2_2]";
    array_push($filters, "${vid1}split${split1_1}${split1_2}");
    array_push($filters, "${vid2}split${split2_1}${split2_2}");

    $vid1_1 = "[vid${i}_1_1]";
    $vid1_2 = "[vid${i}_1_2]";
    $vid2_1 = "[vid${i}_2_1]";
    $vid2_2 = "[vid${i}_2_2]";
    $slide_overlay = "[slide_overlay_${i}]";
    array_push($filters, "${split1_1}trim=start_frame=0:end_frame=".($len1 - 5). ",setpts=PTS-STARTPTS". $vid1_1);
    array_push($filters, "${split1_2}trim=start_frame=".($len1 - 5 + 1).":end_frame=${len1},setpts=PTS-STARTPTS${vid1_2}");
    array_push($filters, "${split2_1}trim=start_frame=0:end_frame=4,setpts=PTS-STARTPTS${vid2_1}");
    array_push($filters, "${split2_2}trim=start_frame=5:end_frame=${len2},setpts=PTS-STARTPTS${vid2_2}");

    $direction = rand(1,4);
    if($direction == 1)
    {
        array_push($filters, $vid1_2.$vid2_1."overlay=x='W/5*n-W'".$slide_overlay);
    }
    else if($direction == 2)
    {
        array_push($filters, $vid1_2.$vid2_1."overlay=x='W-W/5*n'".$slide_overlay);
    }
    else if($direction == 3)
    {
        array_push($filters, $vid1_2.$vid2_1."overlay=y='H/5*n-H'".$slide_overlay);
    }
    else
    {
        array_push($filters, $vid1_2.$vid2_1."overlay=y='H-H/5*n'".$slide_overlay);
    }

    array_push($filters, $vid1_1.$slide_overlay.$vid2_2.'concat=n=3');
}

$param = json_decode($_POST['param']);

if (0 == count($param->images)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Please select at least a image',
    ]);
    die();
}

$inputs = [];
$filters = [];

$slide_len = 0;

for ($i = 0; $i < count($param->images); ++$i) {
    array_push($inputs, '-i "'.dirname(__FILE__).'/'.$param->images[$i]->src.'"');
    array_push(
        $filters,
        '['.$i.']'.get_zoompan_filter(
            $param->images[$i]->animation,
            $param->select_per_frame
    )
    );

    $image_vid = "[image_vid${i}]";
    if (count($param->images) == 1)
    {
        $image_vid = '';
    }
    else if (0 == $i) {
        $image_vid = '[slide_out_0]';
        $slide_len = 11 * $param->select_per_frame;
    }

    array_push($filters, get_text_filter($param->images[$i]->overlay_text).$image_vid);

    if ($i > 0) {
        merge_videos_with_slide_effect('[slide_out_'.($i - 1).']', $slide_len, $image_vid, 11 * $param->select_per_frame, $i);
        $slide_len += 11 * $param->select_per_frame - 5;
        if($i<count($param->images)-1)
        {
            set_name_to_last("[slide_out_{$i}]");
        }
    }
}

if ('' != $param->top_bar_text) {
    array_push($filters, get_top_bar_text_filter($param->top_bar_text));
}
if ('' != $param->bottom_bar_text) {
    array_push($filters, get_bottom_bar_text_filter($param->bottom_bar_text));
}

if ('' != $param->end_screen_text || $param->your_brand_name) {
    set_name_to_last('[total_images_vid]');

    array_push($filters, 'color=c=black:s=700x700:d=5:sar=72/72');
    if ('' != $param->end_screen_text) {
        $param->end_screen_text = escape_text($param->end_screen_text);
        array_push($filters, "drawtext=x=(w-tw)/2:y=h/2-th-28:fontsize=48:fontcolor=white:fontfile='arialbd.ttf':text='".$param->end_screen_text."'");
    }
    if ('' != $param->your_brand_name) {
        $param->your_brand_name = escape_text($param->your_brand_name);
        array_push($filters, "drawtext=x=(w-tw)/2:y=h/2:fontsize=32:fontcolor=white:fontfile='arialbd.ttf':text='".$param->your_brand_name."'");
    }

    set_name_to_last('[credit_vid]');

    array_push($filters, '[total_images_vid][credit_vid]concat=n=2');
}

set_name_to_last('[total_vid]');

$maps = '-map [total_vid]';
$filter_string = implode(',', $filters);

if ('Select audio file' != $param->select_sound) {
    array_push($inputs, '-i "'.dirname(__FILE__).'/audio/'.$param->select_sound.'"');
    $maps .= ' -map '.count($param->images);
}


$cmd = '/usr/local/bin/ffmpeg -y '.implode(' ', $inputs).' -filter_complex "'.$filter_string.'" '.$maps." -shortest -strict -2 ${user_id}/output_video.mp4 2>&1";

echo shell_exec($cmd);
echo "\r";
echo $cmd;

die();
