<?php
$file_size = filesize('1.wmv');
$fp = fopen('1.wmv', 'r');
$input = fread($fp, 0x40000);
fclose($fp);

$target = dirname(__DIR__) . '/uploads/tmp.rar';
$num = file_put_contents($target, $input);

$sha1 = sha1_file($target);
for ($i = 1; $i <= ceil($file_size / 0x40000); $i ++) {
    echo $sha1, '<br>';
    $sha1 = sha1($sha1);
}
echo $sha1, '<br>';
?>