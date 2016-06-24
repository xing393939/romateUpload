<?php
include 'api_common.inc.php';

//$_GET['fid'] = des_decrypt(base64_decode($_GET['fid']), $TRIPLEDES_KEY, $TRIPLEDES_IV);

//检查传参
if (empty($_GET['fid']) || empty($_GET['ppfeature']) || !isset($_GET['start']) || empty($_GET['end'])) {
    die(json_encode(array(
        'err' => '403',
        'msg' => 'fid or ppfeature or start or end empty',
        'data' => array(),
    )));
}

//得到ppfeature、start，end对应的upload_url
$key = "{$_GET['start']}_{$_GET['end']}";
$ranges = api_cache($_GET['ppfeature'], $key);
if (empty($ranges)) {
    die(json_encode(array(
        'err' => 502,
        'msg' => '传参start、end不对',
        'data' => [$_GET, $_SERVER['REQUEST_URI'], $_SERVER['argv'], $_SERVER['SERVER_ADDR'], php_uname()],
    )));
}

//处理nginx接收到的文件
$target = UPLOAD_DIR . "/{$_GET['ppfeature']}";
@mkdir($target);
$target .= "/{$key}";
rename($_GET['upload_tmp_path'], $target);

//文件大小校验
if ($_GET['end'] - $_GET['start'] != filesize($target)) {
    die(json_encode(array(
        'err' => 502,
        'msg' => '文件不完整',
        'data' => [$_GET, $_SERVER['REQUEST_URI'], $_SERVER['argv'], $_SERVER['SERVER_ADDR'], php_uname()],
    )));
}

//传swift
$uploadId = send_put_file($ranges['upload_url'], $target);
if (!$uploadId) {
    die(json_encode(array(
        'err' => 500,
        'msg' => '传文件至公有云出错',
        'data' => array(),
    )));
}

//通知已完成
$md5 = strtoupper($uploadId);
$url = API_CLOUDPLAY . "/2/file/{$_GET['fid']}/action/uploaded?fromcp=private_cloud&range_md5={$md5}&bid={$ranges['bid']}&uploadid={$uploadId}";
$json = send_json_post($url, '');

$arr = json_decode($json, 1);
$arr['msg'] = 'success';
$arr['data'] = [$_GET, $_SERVER['REQUEST_URI'], $_SERVER['argv'], $_SERVER['SERVER_ADDR'], php_uname(), $md5];
die(json_encode($arr));
