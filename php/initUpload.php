<?php
include 'api_common.inc.php';

//检查传参
if (empty($_POST['token']) || empty($_FILES['files'])
    || empty($_POST['channelname']) || empty($_POST['size'])) {
    ajaxReturn('', 'token,files,channelname,size can not be empty', 403);
}

//检查token
parse_str(des_decrypt(base64_decode(urldecode($_POST['token'])), $TRIPLEDES_KEY, $TRIPLEDES_IV), $session);
if (empty($session)) {
    ajaxReturn('', 'token error', 403);
}

//获取ppFeature
$tmpArr = $_FILES['files']['tmp_name'];
$ppFeature = '';
foreach ($tmpArr as $tmp) {
    $ppFeature .= file_get_contents($tmp);
}
$ppFeature = "{$_POST['size']}_" . sha1($ppFeature);

//获取视频列表
$url = SVC_PPTVYUN . '/api/channelcategory/categorylist';
$params = array(
    'username' => $session['username'],
    'apitk' => md5($session['apikey'] . $url),
);
$arr = json_decode(send_get($url . '?' . http_build_query($params)), 1);
if (empty($arr['data'])) {
    ajaxReturn('', 'apikey认证失败', 403);
}
$categoryId = $arr['data'][0]['id'];
if (!empty($_POST['categoryid'])) {
    $categoryId = intval($_POST['categoryid']);
}

//创建视频
$url = SVC_PPTVYUN . '/api/channel/upload';
$postData = array(
    'username' => $session['username'],
    'apitk' => md5($session['apikey'] . $url),
    'categoryid' => $categoryId,
    'name' => isset($_POST['channelname']) ? trim($_POST['channelname']) : '',
    'summary' => isset($_POST['summary']) ? trim($_POST['summary']) : '',
    'coverimg' => isset($_POST['coverimg']) ? trim($_POST['coverimg']) : '',
    'length' => isset($_POST['size']) ? intval($_POST['size']) : 0,
    'ppfeature' => $ppFeature,
);
$arr = json_decode(send_json_post($url, json_encode($postData)), 1);
if (!empty($arr['data'])) {
    $fid = base64_encode(des_encrypt($arr['data']['fId'], $TRIPLEDES_KEY, $TRIPLEDES_IV));
    $arr['data'] = array(
        'nextUrl' => "{$URL_PREFIX}/getRange.php",
        'channelWebId' => $arr['data']['channelWebId'],
        "coverImage" => "http://grocery.pptv.com/lpic/ea7/a31/013/de8660322896b27530d31d82a0aa7bd0.jpg",
        'ppfeature' => $ppFeature,
        'fid' => urlencode($fid),
    );
}
ajaxReturn($arr['data'], $arr['msg'], 0);