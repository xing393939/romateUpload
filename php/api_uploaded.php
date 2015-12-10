<?php
include 'api_common.inc.php';

$target = dirname(__DIR__) . "/uploads/{$_GET['fid']}";
@mkdir($target);
$target .= "/{$_GET['start']}_{$_GET['end']}";
$input = fopen("php://input", "r");
$num = file_put_contents($target, $input);

//传swift
$uploadId = send_put_file($_GET['upload_url'], $target);

//通知已完成
$_GET['range_md5'] = strtoupper($uploadId);
$url = "http://api.cloudplay.pptv.com/fsvc/2/file/{$_GET['fid']}/action/uploaded?range_md5={$_GET['range_md5']}&bid={$_GET['bid']}&uploadid={$uploadId}";
//$url = "http://localhost/romateUpload/php/file.php";
$json = send_json_post($url, '');

$arr = json_decode($json, 1);
$arr['debug'] = $uploadId;
die(json_encode($arr));
