<?php
include 'api_common.inc.php';

//检查传参
if (empty($_POST['size']) || empty($_FILES['files'])) {
    die(json_encode(array(
        'err' => '403',
        'msg' => 'size or files empty',
        'data' => array(),
    )));
}

//获取ppFeature
$tmpArr = $_FILES['files']['tmp_name'];
$ppFeature = '';
foreach ($tmpArr as $tmp) {
    $ppFeature .= file_get_contents($tmp);
}

$arr = [
    'data' => [
        'ppFeature' => "{$_POST['size']}_" . sha1($ppFeature),
    ],
    'msg' => 'success',
    'err' => 0,
];
die(json_encode($arr));

