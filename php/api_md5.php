<?php
function send_post($url, array $post_data = array())
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}
$url = "http://api.cloudplay.pptv.com/fsvc/1/file/{$_POST['fid']}/md5?feature_pplive={$_POST['feature_pplive']}&md5={$_POST['md5']}";
$json = send_post($url);
$arr = json_decode($json, 1);
$arr['debug'] = $url;
die(json_encode($arr));
