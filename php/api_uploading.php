<?php
include 'api_common.inc.php';

//提交md5
$_POST['md5'] = md5($_POST['feature_pplive']);
$url = "http://api.cloudplay.pptv.com/fsvc/1/file/{$_POST['fid']}/md5?feature_pplive={$_POST['feature_pplive']}&md5={$_POST['md5']}";
$json = send_post($url);

//提交feature
$url = "http://api.cloudplay.pptv.com/fsvc/1/file/{$_POST['fid']}/features?feature_pplive={$_POST['feature_pplive']}";
$json = send_json_post($url, "{\"feature_xunlei_cid\":\"{$_POST['feature_pplive']}\",\"feature_xunlei_gcid\":\"{$_POST['feature_pplive']}\"}");

//获取上传进度
$url = "http://api.cloudplay.pptv.com/fsvc/1/file/{$_POST['fid']}/uploading?fromcp=private_cloud";
$json = send_get($url);
$return = json_decode($json, 1);
if (empty($return['data']) || empty($return['data']['fileSize'])) {
    $return['data'] = [
        'fileSize' => 0,
        'finished' => 0,
        'ranges' => 0,
    ];
}

//如果已经上传完成，重新提交md5和feature
$file_size_arr = explode('_', $_POST['feature_pplive']);
$file_size = $file_size_arr[0];
if ($return['data']['fileSize'] == $return['data']['finished']) {
    $_POST['md5'] = strtoupper(big_file_md5($_POST['fid']));
    $url = "http://api.cloudplay.pptv.com/fsvc/1/file/{$_POST['fid']}/md5?feature_pplive={$_POST['feature_pplive']}&md5={$_POST['md5']}";
    $json = send_post($url);

    $xunlei_cid = big_file_cid($_POST['fid'], $file_size);
    $xunlei_gcid = big_file_gcid($_POST['fid'], $file_size);

    $url = "http://api.cloudplay.pptv.com/fsvc/1/file/{$_POST['fid']}/features?feature_pplive={$_POST['feature_pplive']}";
    $json = send_json_post($url, '{"feature_xunlei_cid":"'.$xunlei_cid.'","feature_xunlei_gcid":"'.$xunlei_gcid.'"}');
} else {
    $url = "http://api.cloudplay.pptv.com/fsvc/2/file/{$_POST['fid']}/action/uploadrange?feature_pplive={$_POST['feature_pplive']}&segs=1";
    $json = send_get($url);
    $rangeRs = json_decode($json, 1);
    if (!empty($rangeRs['data']['ranges'])) {
        $return['data']['ranges'] = $rangeRs['data']['ranges'][0];
        $return['data']['ranges']['end'] += 1;
    }
}

$return['debug'] = [$url];
die(json_encode($return));
