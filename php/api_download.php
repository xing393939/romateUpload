<?php
include 'api_common.inc.php';
$timestamp = time() * 1000;

//debug
if (!empty($_GET['fid'])) exit(urlencode(base64_encode(des_encrypt("{$_GET['fid']}|{$timestamp}0|username", $TRIPLEDES_KEY, $TRIPLEDES_IV))));

if (empty($_GET['encode_profile']) || empty($_GET['name']) || empty($_GET['username']) || empty($_GET['token'])) {
    exit('Missing encode_profile/name/username/token');
}

$encode_profile = trim($_GET['encode_profile']);
$name = trim($_GET['name']);
$encodeArr = explode('|', des_decrypt(base64_decode($encode_profile), $TRIPLEDES_KEY, $TRIPLEDES_IV));

if (sizeof($encodeArr) != 3 || $encodeArr[2] != $_GET['username'] || $encodeArr[1] < $timestamp) {
    exit('Unauthorized or out-of-date');
}

$url = API_CLOUDPLAY . "/private/2/file/{$encodeArr[0]}/download_url?download_inner=0&download_expire=864000";
$rs = json_decode(send_get($url), 1);
if (!empty($rs['data'])) {
    $rs['data'] = str_replace('http://sw3.pplive.cn', '', $rs['data']);
    $rs['data'] = str_replace('http://swift.pplive.cn', '', $rs['data']);

    if (strpos($_SERVER["HTTP_USER_AGENT"], "MSIE") || strpos($_SERVER["HTTP_USER_AGENT"], 'rv:11')) {
        $name = urlencode(urlencode($name));
    } else {
        $name = urlencode($name);
    }

    $rs['data'] = str_replace('.ppc?', ".ppc.{$name}.mp4?", $rs['data']);
    header("Location: {$rs['data']}");
}
?>