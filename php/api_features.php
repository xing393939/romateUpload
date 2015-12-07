<?php
function send_json_post($url, $post_data = array())
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($post_data))
    );
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}
$url = "http://api.cloudplay.pptv.com/fsvc/1/file/{$_POST['fid']}/features?feature_pplive={$_POST['feature_pplive']}";
$json = send_json_post($url, '{"feature_xunlei_cid":"'.$_POST['xunlei_cid'].'","feature_xunlei_gcid":"2'.$_POST['feature_pplive'].'"}');
$arr = json_decode($json, 1);
$arr['debug'] = $url;
die(json_encode($arr));
