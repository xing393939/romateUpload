<?php
function send_get($url)
{
    $header = array('Expect:');
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}
$url = "http://api.cloudplay.pptv.com/fsvc/2/file/{$_POST['fid']}/action/uploadrange?feature_pplive={$_POST['feature_pplive']}&segs=1";
$json = send_get($url);
$arr = json_decode($json, 1);
$arr['debug'] = $url;
die(json_encode($arr));
