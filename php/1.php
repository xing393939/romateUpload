<?php
$target = 'C:\Users\Public\Videos\Sample Videos\1.wmv';
$target = 'C:\public.rar';
$tmpArr = $_FILES['files']['tmp_name'];
$target = 'public.rar';
@unlink($target);
foreach ($tmpArr as $tmp) {
    file_put_contents($target, file_get_contents($tmp), FILE_APPEND);
    //move_uploaded_file($tmp, basename($tmp));
    var_dump(sha1_file($target));
}
