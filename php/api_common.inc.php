<?php
$URL_PREFIX = 'http://ppyun.ugc.upload.pptv.com/php';
$IS_LOCAL = false;
$TRIPLEDES_KEY = 'DAEFE3161F3578E0DFDFABD28C9E2F567A27EE5F2F8A2C9B';
$TRIPLEDES_IV = '0102030405060708';
preg_match("/(021ZJ1659|ITA-1312-3010|shnj\-b\-php\-187\-39|localhost\.idc\.pplive\.cn)/i", php_uname(), $IS_LOCAL);
define('UPLOAD_DIR', $IS_LOCAL ? dirname(__DIR__) . "/uploads" : '/home/pplive/storage');
define('SVC_PPTVYUN', $IS_LOCAL ? 'http://svc.pptvyun.ppqa.com/svc/v1' : 'http://svc.pptvyun.com/svc/v1');
define('API_CLOUDPLAY', $IS_LOCAL ? 'http://api.cloudplay.ppqa.com/fsvc' : 'http://api.cloudplay.pptv.com/fsvc');

header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Credentials:true');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
header('Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS');
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    die();
}

function send_post($url, array $post_data = array())
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 75);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

function send_json_post($url, $post_data = array())
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 75);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($post_data))
    );
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

function send_get($url)
{
    $header = array('Expect:');
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 75);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

function send_put_file($url, $fileName)
{
    $fp = fopen($fileName, 'r');
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_PUT, true);
    curl_setopt($ch, CURLOPT_INFILE, $fp);
    curl_setopt($ch, CURLOPT_INFILESIZE, filesize($fileName));
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 100);
    ob_start();
    $r = curl_exec($ch);
    curl_close($ch);
    $r = ob_get_contents();
    ob_clean();
    preg_match('/Etag: (\w+)/', $r, $match);
    $uploadId = isset($match[1]) ? $match[1] : '';
    return $uploadId;
}

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

function big_file_array($ppFeature)
{
    $dir = UPLOAD_DIR . "/{$ppFeature}";
    if (!is_dir($dir)) {
        return array();
    }
    $dir_handle = opendir($dir);
    $array = array();
    while ($file = readdir($dir_handle)) {
        if ($file != "." && $file != "..") {
            $arr = explode('_', $file);
            $array[$arr[0]] = ["{$dir}/{$file}", $arr[0], $arr[1]];
        }
    }
    closedir($dir_handle);
    ksort($array);
    return $array;
}

function big_file_read($fileArray, $start, $length)
{
    $return = '';
    foreach ($fileArray as $fileArr) {
        if ($start >= $fileArr[1] && $start <= $fileArr[2]) {
            $fp = fopen($fileArr[0], 'r');
            fseek($fp, $start - $fileArr[1]);
            $tmp = fread($fp, $length);
            fclose($fp);
            if ($tmp) {
                $return .= $tmp;
                $start += strlen($tmp);
                $length -= strlen($tmp);
                if ($length <= 0) break;
            }
        }
    }
    return $return;
}

function api_cache($ppFeature, $key, $value = null)
{
    $dir = UPLOAD_DIR . "/cache";
    @mkdir($dir);
    $dir .= "/{$ppFeature}";
    @mkdir($dir);
    if (null === $key) {
        delDirAndFile($dir);
        return true;
    }
    $cacheFile = "{$dir}/{$key}";
    if (null !== $value) {
        file_put_contents($cacheFile, serialize($value));
        return true;
    } else {
        $value = @file_get_contents($cacheFile);
        return unserialize($value);
    }
    return false;
}

function big_file_md5($fileArray)
{
    $ctx = hash_init('md5');
    foreach ($fileArray as $fileArr) {
        hash_update_file($ctx, $fileArr[0]);
    }
    return hash_final($ctx);
}

function big_file_cid($fileArray, $file_size)
{
    $xunlei_cid = '';
    $target = '';
    if ($file_size < 61440) {
        $xunlei_cid = big_file_read($fileArray, 0, $file_size);
    } else {
        $target .= big_file_read($fileArray, 0, 20480);
        $target .= big_file_read($fileArray, floor($file_size / 3), 20480);
        $target .= big_file_read($fileArray, floor($file_size - 20480), 20480);
        $xunlei_cid = "{$file_size}_" . sha1($target);
    }
    return $xunlei_cid;
}

function big_file_gcid($fileArray, $file_size)
{
    $xunlei_gcid = '';
    $target = '';
    $p_size = 0x40000;
    while ($file_size / $p_size > 0x200)
        $p_size = $p_size << 1;
    $data = big_file_read($fileArray, 0, $p_size);
    $i = 1;
    while ($data) {
        $target .= sha1($data, TRUE);
        $data = big_file_read($fileArray, $p_size * $i, $p_size);
        $i++;
    }
    $xunlei_gcid = "{$file_size}_" . sha1($target);
    return $xunlei_gcid;
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

function des_encrypt($input, $key, $iv)
{
    $key = pack('H48', $key);
    $iv = pack('H16', $iv);
    $srcdata = $input;
    $block_size = mcrypt_get_block_size('tripledes', 'ecb');
    $padding_char = $block_size - (strlen($input) % $block_size);
    $srcdata .= str_repeat(chr($padding_char), $padding_char);
    return mcrypt_encrypt(MCRYPT_3DES, $key, $srcdata, MCRYPT_MODE_CBC, $iv);
}

function des_decrypt($input, $key, $iv)
{
    $key = pack('H48', $key);
    $iv = pack('H16', $iv);
    $result = mcrypt_decrypt(MCRYPT_3DES, $key, $input, MCRYPT_MODE_CBC, $iv);
    $end = ord(substr($result, -1));
    $out = substr($result, 0, -$end);
    return $out;
}

//上传图片，避免Expect:100-continue
function send_post_pic($url, array $post_data = array())
{
    foreach ($post_data as $field => $value) {
        if (strpos($value, '@') === 0) {
            $file = substr($value, 1);
            $mimeType = mime_content_type($file);
            $fileObj = "@{$file};type={$mimeType}";
            if (class_exists('CURLFile')) {
                $fileObj = new CURLFile($file);
                $fileObj->setMimeType($mimeType);
                //var_dump($file, $mimeType);
                //exit();
            }
            $post_data[$field] = $fileObj;
        }
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 75);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Expect:"));
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

//返回script脚本
function scriptReturn($data, $msg, $err)
{
    $json = json_encode(array(
        'data' => $data,
        'msg' => $msg,
        'err' => $err,
    ));
    $html = "<!doctype html><html><head><meta charset=\"UTF-8\"><title>Default page</title></head><body><script>window.parent.postMessage('$json', \"*\");</script></body></html>";
    die($html);
}

//返回json
function ajaxReturn($data, $msg, $err)
{
    die(json_encode(array(
        'data' => $data,
        'msg' => $msg,
        'err' => $err,
    )));
}

/*
 * $filename 原图
 * $target   目标图
 * $x        起始点x坐标
 * $y        起始点y坐标
 * $w        裁剪的宽度
 * $h        裁剪的高度
 * $iw       缩放的宽度
 */
function cropImage($target, $filename, $x, $y, $w, $h, $iw)
{
    $imageInfo = getimagesize($filename);
    if (!$imageInfo) {
        return false;
    }

    //内存不够增加内存
    if (empty($imageInfo['channels'])) {
        $imageInfo['channels'] = 5;
    }
    $memoryNeeded = round(($imageInfo[0] * $imageInfo[1] * $imageInfo['bits'] * $imageInfo['channels'] / 8 + pow(2, 16)) * 1.8);
    $memoryLimit = (int) ini_get('memory_limit');
    if (function_exists('memory_get_usage') && memory_get_usage() + $memoryNeeded > $memoryLimit * pow(1024, 2)) {
        ini_set('memory_limit', $memoryLimit + ceil(((memory_get_usage() + $memoryNeeded) - $memoryLimit * pow(1024, 2)) / pow(1024, 2)) . 'M');
    }

    $srcWidth = $imageInfo[0];
    $srcHeight = $imageInfo[1];
    $scale = $srcWidth / $iw;

    //修正异常传参
    $x = max(0, $x);
    $y = max(0, $y);
    $w = min($w, $iw);
    $h = min($h, $srcHeight / $scale);
    if ($x + $w > $iw) {
        $x = 0;
    }
    if ($y + $h > $srcHeight / $scale) {
        $y = 0;
    }

    $src_w = round($w * $scale);
    $src_h = round($h * $scale);
    $src_x = round($x * $scale);
    $src_y = round($y * $scale);

    // 载入原图
    $createFun = 'ImageCreateFromjpeg';
    if ($imageInfo[2] == 1) {
        $createFun = 'ImageCreateFromgif';
    } elseif ($imageInfo[2] == 3) {
        $createFun = 'ImageCreateFrompng';
    }

    //目标图的实际宽高
    $w = $src_w;
    $h = $src_h;
    $dst_image = imagecreatetruecolor($w, $h);
    $src_image = $createFun($filename);
    imagecopyresampled($dst_image, $src_image, 0, 0, $src_x, $src_y, $w, $h, $src_w, $src_h);
    imagejpeg($dst_image, $target);
}