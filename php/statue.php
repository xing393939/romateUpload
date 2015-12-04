<?php
//文件基本信息
$filename   = $_REQUEST['name'];
 $filesize  = $_REQUEST['filesize'];
 $index     = $_REQUEST['index'];

 $path = DIRECTORY_SEPARATOR . "tmp" . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR . $filename . DIRECTORY_SEPARATOR ."statue.log";

if(file_exists($path)){
	$handle = fopen($path, 'r');
	$content = fread($handle,filesize($path));
	$returnData = explode(',', $content);
	 foreach ($returnData as $key => $value) {
	 	$array =  explode('|', $value);
	 	$json_data['data'][] = array('name'=>$filename,'index'=>$array['1'],'statue'=>$array['0'],'size'=>$array['2'],'client_path'=>$array['3']);
	 }
}
echo json_encode($json_data);