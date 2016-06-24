<?php
include 'api_common.inc.php';

$_GET['fid'] = des_decrypt(base64_decode($_GET['fid']), $TRIPLEDES_KEY, $TRIPLEDES_IV);

//检查传参
if (empty($_GET['token']) || empty($_GET['fid']) || empty($_GET['ppfeature']) || !isset($_GET['start']) || empty($_GET['end'])) {
    ajaxReturn('', 'token,fid,ppfeature,start can not be empty', 403);
}

//检查token
parse_str(des_decrypt(base64_decode($_GET['token']), $TRIPLEDES_KEY, $TRIPLEDES_IV), $session);
if (empty($session)) {
    ajaxReturn('', 'token error', 403);
}

//得到ppfeature、start，end对应的upload_url
$key = "{$_GET['start']}_{$_GET['end']}";
$ranges = api_cache($_GET['ppfeature'], $key);
if (empty($ranges)) {
    ajaxReturn('', '传参start、end不对', 502);
}

//先存本地
$target = UPLOAD_DIR . "/{$_GET['ppfeature']}";
@mkdir($target);
$target .= "/{$key}";
$input = fopen("php://input", "r");
@file_put_contents($target, $input);

//文件大小校验
if ($_GET['end'] - $_GET['start'] != filesize($target)) {
    ajaxReturn('', '文件不完整', 502);
}

//传swift
$uploadId = send_put_file($ranges['upload_url'], $target);
if (!$uploadId) {
    ajaxReturn('', '传文件至公有云出错', 500);
}

//通知已完成
$md5 = strtoupper($uploadId);
$url = API_CLOUDPLAY . "/2/file/{$_GET['fid']}/action/uploaded?fromcp=private_cloud&range_md5={$md5}&bid={$ranges['bid']}&uploadid={$uploadId}";
$arr = json_decode(send_json_post($url, ''), 1);
$arr['data'] = array(
    'nextUrl' => "{$URL_PREFIX}/getRange.php",
);
ajaxReturn($arr['data'], 'success', 0);
