<?php
header('Access-Control-Allow-Origin:http://localhost');
header('Access-Control-Allow-Credentials:true');
header('Access-Control-Allow-Headers: X-Requested-With, X-File, X-File-Size, X-Index');
header('Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS');

function send_post($url, array $post_data = array())
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
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
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
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
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    ob_start();
    $r = curl_exec($ch);
    curl_close($ch);
    $r = ob_get_contents();
    ob_clean();
    preg_match('/Etag: (\w+)/', $r, $match);
    $uploadId = isset($match[1]) ? $match[1] : '';
    return $uploadId;
}

function big_file_array($fid)
{
    $dir = dirname(__DIR__) . "/uploads/{$fid}";
    if (!is_dir($dir)) {
        return array();
    }
    $dir_handle = opendir($dir);
    $array = array();
    while ($file = readdir($dir_handle)) {
        if ($file != "." && $file != "..") {
            $arr = explode('_', $file);
            $array[$arr[0]] = ["{$file}", $arr[0], $arr[1]];
        }
    }
    ksort($array);
    return $array;
}

function big_file_read($fid, $start, $length)
{
    $return = '';
    $array = big_file_array($fid);
    $dir = dirname(__DIR__) . "/uploads/{$fid}";
    //var_dump($end, $start);
    foreach ($array as $fileArr) {
        if ($start >= $fileArr[1] && $start <= $fileArr[2]) {
            $fp = fopen("{$dir}/{$fileArr[0]}", 'r');
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

function big_file_md5($fid)
{
    $array = big_file_array($fid);
    $dir = dirname(__DIR__) . "/uploads/{$fid}";
    $ctx = hash_init('md5');
    foreach ($array as $fileArr) {
        hash_update_file($ctx, "{$dir}/{$fileArr[0]}");
    }
    return hash_final($ctx);
}

function big_file_cid($fid, $file_size)
{
    $xunlei_cid = '';
    $target = '';
    if ($file_size < 61440) {
        $xunlei_cid = big_file_read($fid, 0, $file_size);
    } else {
        $target .= big_file_read($fid, 0, 20480);
        $target .= big_file_read($fid, floor($file_size / 3), 20480);
        $target .= big_file_read($fid, floor($file_size - 20480), 20480);
        $xunlei_cid = "{$file_size}_" . sha1($target);
    }
    return $xunlei_cid;
}

function big_file_gcid($fid, $file_size)
{
    $xunlei_gcid = '';
    $target = '';
    $p_size = 0x40000;
    while ($file_size / $p_size > 0x200)
        $p_size = $p_size << 1;
    $data = big_file_read($fid, 0, $p_size);
    $i = 1;
    while ($data) {
        $target .= sha1($data, TRUE);
        $data = big_file_read($fid, $p_size * $i, $p_size);
        $i ++;
    }
    $xunlei_gcid = "{$file_size}_" . sha1($target);
    return $xunlei_gcid;
}