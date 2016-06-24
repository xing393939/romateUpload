<?php
include 'api_common.inc.php';

if (empty($_POST['username']) || empty($_POST['apikey'])) {
    ajaxReturn('', 'username,apikey can not be empty', 403);
}

//验证apikey，获取上传url
$url = SVC_PPTVYUN . '/api/picurlwithtoken';
$params = array(
    'username' => $_POST['username'],
    'apitk' => md5($_POST['apikey'] . $url),
);
$arr = json_decode(send_get($url . '?' . http_build_query($params)), 1);
if (empty($arr['data'])) {
    ajaxReturn('', 'apikey认证失败', 403);
}

//获取上传图片的url
$newUrl = 'http://ppyun.ugc.upload.pptv.com/php/uploadPic.php';
$uploadPicUrl = str_replace('http://api.grocery.pptv.com/upload_file.php', $newUrl, $arr['data']);
$uploadPicUrl = str_replace('http://api.grocery.ppqa.com/upload_file.php', $newUrl, $uploadPicUrl);
if (empty($_POST['uploadpic'])) $uploadPicUrl = '';

//算出登录的token
$session_data = array(
    'username' => $_POST['username'],
    'apikey' => $_POST['apikey'],
    'expire_time' => time() + 86400,
);
$token = urlencode(base64_encode(des_encrypt(http_build_query($session_data), $TRIPLEDES_KEY, $TRIPLEDES_IV)));

$data = array(
    'nextUrl' => "{$URL_PREFIX}/initUpload.php",
    'uploadPicUrl' => $uploadPicUrl,
    'token' => $token,
);
ajaxReturn($data, 'success', 0);
?>