<?php
include 'api_common.inc.php';
//$_POST['fid'] = des_decrypt(base64_decode(urldecode($_POST['fid'])), $TRIPLEDES_KEY, $TRIPLEDES_IV);

//检查传参
if (empty($_POST['fid']) || empty($_POST['ppfeature'])) {
    die(json_encode(array(
        'err' => '403',
        'msg' => 'fid or ppfeature empty',
        'data' => array(),
    )));
}

//获取上传进度
$url = API_CLOUDPLAY . "/1/file/{$_POST['fid']}/uploading?fromcp=private_cloud";
$json = send_get($url);
$return = json_decode($json, 1);
if (empty($return['data']) || empty($return['data']['fileSize'])) {
    die(json_encode(array(
        'err' => 500,
        'msg' => '公有云接口出错',
        'data' => array(),
    )));
}

//获取上传范围
$url = API_CLOUDPLAY . "/2/file/{$_POST['fid']}/action/uploadrange?fromcp=private_cloud&feature_pplive={$_POST['ppfeature']}&segs=1&asyncMD5=true";
if (!$IS_LOCAL) {
    $url .= '&inner=true';
}
$json = send_get($url);
$rangeRs = json_decode($json, 1);

//如果已经上传完成，提交md5和feature
if ($return['data']['fileSize'] == $return['data']['finished']) {
    //cli模式下异步提交最后的md5和feature
    $cacheDir = UPLOAD_DIR . "/cache/{$_POST['ppfeature']}";
    if (is_dir($cacheDir)) {
        $binPath = strpos(PHP_OS, 'WIN') === false ? '/usr/local/php5/bin/php' : 'D:\wamp\bin\php\php5.5.12\php.exe';
        $binPath = $IS_LOCAL[0] == 'localhost.idc.pplive.cn' ? '/usr/local/php5.5.27/bin/php' : $binPath;
        $phpPath = __DIR__ . "/api_cli_curl.php {$_POST['fid']} {$_POST['ppfeature']}";
        pclose(popen("{$binPath} {$phpPath} &", 'r'));
    }
    api_cache($_POST['ppfeature'], null);
} else {
    if (empty($rangeRs['data']) || empty($rangeRs['data']['ranges'])) {
        die(json_encode(array(
            'err' => 500,
            'msg' => '公有云接口出错',
            'data' => array(),
        )));
    }

    $ranges = $rangeRs['data']['ranges'][0];
    $return['data']['ranges'] = array(
        'start' => $ranges['start'],
        'end' => $ranges['end'] + 1,
    );
    $key = $ranges['start'] . '_' . ($ranges['end'] + 1);
    api_cache($_POST['ppfeature'], $key, $ranges);
}

$return['msg'] = 'success';
die(json_encode($return));
