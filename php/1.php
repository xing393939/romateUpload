<?php

//file_put_contents('1.wmv', '1', FILE_APPEND);
exit();

$now = time() * 1000;
$key = sha1("A6970547167342"."UZ"."5C3A931C-E572-4E7B-8EBC-A0FD03564FC3"."UZ".$now).".".$now;
$header = array(
    'X-APICloud-AppId: A6970547167342',
    "X-APICloud-AppKey: $key",
);
$data = array(
    'title' => 'abcd',
    'content' => 'content',
    'type' => '1',
    'platform' => '2',
    'groupName' => 'department',
    'userIds' => '1',
);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://p.apicloud.com/api/push/message');
curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
curl_close($ch);
exit($response);



include 'api_common.inc.php';
echo big_file_md5(64492), '<br>', md5_file('1.wmv');

$xunlei_cid = '';
$target = '';
$file_size = filesize('1.wmv');
if ($file_size < 61440) {
    $xunlei_cid = sha1_file('1.wmv');
} else {
    $fp = fopen('1.wmv', 'r');
    $target .= fread($fp, 20480);
    fseek($fp, (int) $file_size / 3);
    $target .= fread($fp, 20480);
    fseek($fp, (int) $file_size - 20480);
    $target .= fread($fp, 20480);
    fclose($fp);
    $xunlei_cid = "{$file_size}_" . sha1($target);
}
$xunlei_gcid = '';
$target = '';
$file_size = filesize('1.wmv');
$fp = fopen('1.wmv', 'r');
$data = fread($fp, 0x40000);
$i = 1;
while ($data) {
    fseek($fp, 0x40000 * $i);
    $i ++;
    $target .= sha1($data, TRUE);
    $data = fread($fp, 0x40000);
}
fclose($fp);
$xunlei_gcid = "{$file_size}_" . sha1($target);

var_dump([$xunlei_cid, $xunlei_gcid]);

$file_size = 26246049;
$_POST['fid'] = 64492;
$xunlei_cid = '';
$target = '';
if ($file_size < 61440) {
    $xunlei_cid = big_file_read($_POST['fid'], 0, $file_size);
} else {
    $target .= big_file_read($_POST['fid'], 0, 20480);
    $target .= big_file_read($_POST['fid'], floor($file_size / 3), 20480);
    $target .= big_file_read($_POST['fid'], floor($file_size - 20480), 20480);
    $xunlei_cid = "{$file_size}_" . sha1($target);
}
$xunlei_gcid = '';
$target = '';
$data = big_file_read($_POST['fid'], 0, 0x40000);
$i = 1;
while ($data) {
    $target .= sha1($data, TRUE);
    $data = big_file_read($_POST['fid'], 0x40000 * $i, 0x40000);
    $i ++;
}
$xunlei_gcid = "{$file_size}_" . sha1($target);
var_dump([$xunlei_cid, $xunlei_gcid]);

//file_put_contents('1.wmv', '1', FILE_APPEND);
?>