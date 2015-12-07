<?php
$tmpArr = $_FILES['files']['tmp_name'];
$ppFeature = dirname(__DIR__) . '/uploads/ppFeature.rar';
@unlink($ppFeature);
foreach ($tmpArr as $tmp) {
    file_put_contents($ppFeature, file_get_contents($tmp), FILE_APPEND);
    //move_uploaded_file($tmp, basename($tmp));
}

$tmpArr = $_FILES['files2']['tmp_name'];
$xunlei_cid = dirname(__DIR__) . '/uploads/xunlei_cid.rar';
@unlink($xunlei_cid);
foreach ($tmpArr as $tmp) {
    file_put_contents($xunlei_cid, file_get_contents($tmp), FILE_APPEND);
    //move_uploaded_file($tmp, basename($tmp));
}

$arr = [
    'data' => [
        'ppFeature' => "{$_POST['size']}_" . sha1_file($ppFeature),
        'xunlei_cid' => "{$_POST['size']}_" . sha1_file($xunlei_cid),
    ],
    'err' => 0,
];
die(json_encode($arr));

