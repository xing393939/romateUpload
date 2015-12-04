<?php 
 /*
 proxy_pass nodejs;
 */
header('Access-Control-Allow-Origin:http://localhost');
header('Access-Control-Allow-Credentials:true');
header('Access-Control-Allow-Headers: X-Requested-With, X-File, X-File-Size, X-Index');
header('Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS');

//文件基本信息
$filename   = $_SERVER['HTTP_X_FILE'];
$filesize   = $_SERVER['HTTP_X_FILE_SIZE'];
$index      = $_SERVER['HTTP_X_INDEX'];
$fileChunk   = $_SERVER['HTTP_X_CHUNKSIZE'];
$client_path = $_REQUEST['file_path'];

// name must be in proper format
if (!isset($_SERVER['HTTP_X_FILE'])) {
    // throw new Exception('Name required');
      die('Name required');
}
// index must be set, and number
if (!isset($_SERVER['HTTP_X_INDEX'])) {
    // throw new Exception('Index required');
    die('Index required');
}
if (!preg_match('/^[0-9]+$/', $_SERVER['HTTP_X_INDEX'])) {
    // throw new Exception('Index error');
    die('Index error');
}

$path = dirname(dirname(dirname(__DIR__))) . '/upload' . DIRECTORY_SEPARATOR . $filename;

$log_path = $path . DIRECTORY_SEPARATOR . "statue.log";
if(!file_exists($path)){
    mkdir($path);
}

$target = $path. DIRECTORY_SEPARATOR . $filename . '-' . $index;
$input = fopen("php://input", "r");
$num = file_put_contents($target, $input);
