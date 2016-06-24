<?php
include 'api_common.inc.php';

$username = isset($_GET['username']) ? trim($_GET['username']) : '';
$apikey = isset($_GET['apikey']) ? trim($_GET['apikey']) : '';
if ($IS_LOCAL) {
    $username = 'scdddk@163.com';
    $apikey = 'FBBA0D39FA8F0A34814B1DDC24FCBB42';
}
$cb = isset($_GET['cb']) ? trim($_GET['cb']) : '';

$url = SVC_PPTVYUN . '/api/channelcategory/categorylist';
$params = array(
    'username' => $username,
    'apitk' => md5($apikey . $url),
);
$arr = json_decode(send_get($url . '?' . http_build_query($params)), 1);
if (empty($arr['data'])) {
    $return = json_encode(array(
        'err' => 403,
        'msg' => 'apikey认证失败',
        'data' => array(),
    ));
    die("{$cb}({$return})");
}
$categoryId = $arr['data'][0]['id'];

$url = SVC_PPTVYUN . '/api/channel/upload';
$postData = array(
    'username' => $username,
    'apitk' => md5($apikey . $url),
    'categoryid' => $categoryId,
    'name' => isset($_GET['channelname']) ? trim($_GET['channelname']) : '',
    'summary' => isset($_GET['summary']) ? trim($_GET['summary']) : '',
    'coverimg' => '',
    'length' => isset($_GET['size']) ? intval($_GET['size']) : 0,
    'ppfeature' => isset($_GET['ppfeature']) ? trim($_GET['ppfeature']) : '',
);

$arr = json_decode(send_json_post($url, json_encode($postData)), 1);
if (!empty($arr['data'])) {
    $fid = base64_encode(des_encrypt($arr['data']['fId'], $TRIPLEDES_KEY, $TRIPLEDES_IV));
    $arr['data'] = array(
        'channelWebId' => $arr['data']['channelWebId'],
        'fid' => urlencode($fid),
    );
}

$return = json_encode($arr);
die("{$cb}({$return})");
