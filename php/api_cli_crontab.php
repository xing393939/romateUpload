<?php
$IS_LOCAL = false;
preg_match("/(ITA-1312-3010|shnj\-b\-php\-187\-39|localhost\.idc\.pplive\.cn)/i", php_uname(), $IS_LOCAL);
define('UPLOAD_DIR', $IS_LOCAL ? dirname(__DIR__) . "/uploads" : '/home/pplive/storage');

function ll_dir($dir)
{
    $dir_handle = opendir($dir);
    $array = array();
    while ($file = readdir($dir_handle)) {
        if ($file != "." && $file != "..") {
            $array[] = array(
                'file' => "{$dir}/{$file}",
                'fileatime' => fileatime("{$dir}/{$file}"),
            );
        }
    }
    closedir($dir_handle);
    return $array;
}

function delDirAndFile($dirName)
{
    if (is_dir($dirName)) {
        $handle = @opendir("$dirName");
        while ($item = @readdir($handle)) {
            if ($item != "." && $item != "..") {
                delDirAndFile("$dirName/$item");
            }
        }
        @closedir($handle);
        @rmdir($dirName);
    } else {
        @unlink($dirName);
    }
}

//删除临时上传文件，临时图片
$arr = ll_dir(UPLOAD_DIR);
foreach ($arr as $row) {
    if ($row['file'] == UPLOAD_DIR . '/cache') continue;
    if ($row['fileatime'] < time() - 86400 * 3) {
        delDirAndFile($row['file']);
    }
}

//删除ppfeature的缓存
$dir = UPLOAD_DIR . '/cache';
$arr = ll_dir($dir);
foreach ($arr as $row) {
    if ($row['file'] == "{$dir}/session") continue;
    if ($row['fileatime'] < time() - 86400 * 3) {
        delDirAndFile($row['file']);
    }
}

//删除过期session
$dir = UPLOAD_DIR . '/cache/session';
$arr = ll_dir($dir);
foreach ($arr as $row) {
    if ($row['fileatime'] < time() - 86400) {
        delDirAndFile($row['file']);
    }
}