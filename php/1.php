<?php
$a =  sprintf('%u', (float) 0xFFFF);
var_dump($a);

$target = 'C:\Users\Public\Videos\Sample Videos\1.wmv';
$string = "C:\\Python27\\python.exe " . dirname(__DIR__) . "/python/ppfeature/lixian_hash_pplive.py  " . $target;
$descriptor_spec = array(
    0 => array("pipe", "r"),
    1 => array("pipe", "w"),
    2 => array("file", "error.log", "a")
);

$process = proc_open($string, $descriptor_spec, $pipes);

if (is_resource($process)) {

    $output = stream_get_contents($pipes[1]);

    fclose($pipes[1]);
    fclose($pipes[0]);

    $return = proc_close($process);
    if ($return != 0) $output = "Error";
} else $output = "Error";
$output = str_replace(PHP_EOL, '', $output);
$filesize = filesize($target);
$features = $filesize . "_" . $output;

var_dump($features);