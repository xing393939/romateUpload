<?php
//fname为要下载的文件名
//$fpath为下载文件所在文件夹，默认是downlod
function download($path){

	$fp = fopen($path,'r');//只读方式打开
	$filesize = filesize($path);//文件大小

	//返回的文件(流形式)
	header("Content-type: application/octet-stream");
	//按照字节大小返回
	header("Accept-Ranges: bytes");
	//返回文件大小
	header("Accept-Length: $filesize");
	//这里客户端的弹出对话框，对应的文件名
	header("Content-Disposition: attachment; filename=test.mp4");
	//================重点====================
	ob_clean();
	flush();
	//=================重点===================
	//设置分流
	$buffer = 1024;
	//来个文件字节计数器
	$count = 0;
	while(!feof($fp)&&($filesize-$count>0)){
		$data = fread($fp,$buffer);
		$count += 1024;//计数
		echo $data;//传数据给浏览器端
	}

	fclose($fp);

}
download("http://sw3.pplive.cn/v1/AUTH_662aff6cd42e41e4be6332e483e88cce/video125/2006721/15156013-d37b71def481eda6b141428b6b218ab7.ppc?temp_url_sig=446377ff940acd6ae4b35513aa084bd71d913b0c&temp_url_expires=1450433016");
?>