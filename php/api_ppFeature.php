<?php
include 'api_common.inc.php';

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
    'err' => 0,
];
die(json_encode($arr));

