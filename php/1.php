<?php
$file_size = filesize('1.wmv');
$target = dirname(__DIR__) . '/uploads/gcid.rar';
@unlink($target);
$fp = fopen('1.wmv', 'r');
$data = fread($fp, 0x40000);
//$num = file_put_contents($target, $input);

$i = 1;
while ($data) {
    fseek($fp, 0x40000 * $i);
    $i ++;

    echo sha1($data), '<br>';
    file_put_contents($target, sha1($data, TRUE), FILE_APPEND);
    $data = fread($fp, 0x40000);
}
fclose($fp);
echo sha1_file($target);
?>