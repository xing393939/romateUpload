<?php
function send_json_post($url, $post_data)
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

function send_put_file($url, $fileName) {
    $fp = fopen($fileName, 'r');
    $ch = curl_init($url);
    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_PUT, true);
    curl_setopt($ch, CURLOPT_INFILE, $fp);
    curl_setopt($ch, CURLOPT_INFILESIZE, filesize($fileName));
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

$target = dirname(__DIR__) . '/uploads/tmp.rar';
$input = fopen("php://input", "r");
$num = file_put_contents($target, $input);

//传swift
$debug = send_put_file($_GET['upload_url'], $target);

//通知已完成
$_GET['range_md5'] = strtoupper($_GET['range_md5']);
$url = "http://api.cloudplay.pptv.com/fsvc/2/file/{$_GET['fid']}/action/uploaded?range_md5={$_GET['range_md5']}&bid={$_GET['bid']}";
//$url = "http://localhost/romateUpload/php/file.php";
$json = send_json_post($url, '');

$arr = json_decode($json, 1);
$arr['debug'] = $debug;
die(json_encode($arr));
