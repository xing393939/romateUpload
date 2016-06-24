<?php
$_SERVER['REQUEST_METHOD'] = 'CLI';
include 'api_common.inc.php';

if (empty($_SERVER['argv'])) exit();

$fid = $_SERVER['argv'][1];
$ppFeature = $_SERVER['argv'][2];
$file_size_arr = explode('_', $ppFeature);
$file_size = $file_size_arr[0];
$fileArray = big_file_array($ppFeature);

$md5 = strtoupper(big_file_md5($fileArray));
$url = API_CLOUDPLAY . "/1/file/{$fid}/md5?fromcp=private_cloud&feature_pplive={$ppFeature}&md5={$md5}";
$json1 = send_post($url);

$xunlei_cid = big_file_cid($fileArray, $file_size);
$xunlei_gcid = big_file_gcid($fileArray, $file_size);

$url = API_CLOUDPLAY . "/1/file/{$fid}/features?fromcp=private_cloud&feature_pplive={$ppFeature}";
$json2 = send_json_post($url, '{"feature_xunlei_cid":"'.$xunlei_cid.'","feature_xunlei_gcid":"'.$xunlei_gcid.'"}');

//删除临时文件
$target = UPLOAD_DIR . "/{$ppFeature}";
delDirAndFile($target);

//必须，否则文件无法进入转码队列
$url = API_CLOUDPLAY . "/2/file/{$fid}/action/uploadrange?feature_pplive={$ppFeature}&segs=1";
sleep(30);
$json3 = send_get($url);
$arr3 = json_decode(send_get($url), 1);
if (empty($arr3['data']) || $arr3['data']['status'] == 50) {
    sleep(30);
    $json3 = send_get($url);
    $json3 = 'retry';
}

$phpPath = __DIR__ . '/0x0000.log';
file_put_contents($phpPath, " $md5 \n $xunlei_cid \n $xunlei_gcid \n $json1 \n $json2 \n $json3 \n ");