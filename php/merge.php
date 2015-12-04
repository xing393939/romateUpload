<?php
header('Access-Control-Allow-Origin:http://localhost');
header('Access-Control-Allow-Credentials:true');
header('Access-Control-Allow-Headers: X-Requested-With, X-File, X-File-Size, X-Index');
header('Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS');

function send_json_post($url,$post_data)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($post_data))
        );
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
function send_get($url)
    {
        $header = array('Expect:');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        $data = curl_exec( $ch );
        curl_close( $ch );
        return $data;
    }	
function send_post($url, array $post_data = array())
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT,10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
function delDirAndFile($dirName)
{
    if ($handle = opendir("$dirName")) {
        while (false !== ($item = readdir($handle))) {
            if ($item != "." && $item != "..") {
                if (is_dir("$dirName/$item")) {
                    delDirAndFile("$dirName/$item");
                } else {
                    @unlink("$dirName/$item");
                }
            }
        }
        closedir($handle);
        @rmdir($dirName);
    }
}
  
// name must be in proper format
if (!isset($_REQUEST['name'])) {
   die('Name required');
}
#if (!preg_match('/^[-a-z0-9_][-a-z0-9_.]*$/i', $_REQUEST['name'])) {
#    throw new Exception('Name error');
#}

// index must be set, and number
if (!isset($_REQUEST['index'])) {
    die('Index required');
}

if (!preg_match('/^[0-9]+$/', $_REQUEST['index'])) {
    die('Index error');
}
//生成的文件
$target = dirname(__DIR__) . "/uploads" . DIRECTORY_SEPARATOR . $_REQUEST['name'];
for ($i = 0; $i < $_REQUEST['index']; $i++) {
    //$slice .= " " . dirname(dirname(dirname(__DIR__)))  . "/upload" . DIRECTORY_SEPARATOR . $_REQUEST['name'] . DIRECTORY_SEPARATOR . $_REQUEST['name'] . '-' . $i;
    $slice = dirname(dirname(dirname(__DIR__))) . '/upload' . DIRECTORY_SEPARATOR . $_REQUEST['name'] . DIRECTORY_SEPARATOR . $_REQUEST['name'] . '-' . $i;
    file_put_contents($target, file_get_contents($slice), FILE_APPEND);
}
delDirAndFile(dirname(dirname(dirname(__DIR__))) . "/upload" . DIRECTORY_SEPARATOR . $_REQUEST['name']);

$string = "python " . dirname(__DIR__) . "/python" . DIRECTORY_SEPARATOR . "ppfeature" . DIRECTORY_SEPARATOR . "lixian_hash_pplive.py  " .$target;
	$descriptor_spec = array(
		0 => array("pipe", "r"),
		1 => array("pipe", "w"),
		2 => array("file", "error.log", "a")
	) ;

	$process = proc_open($string, $descriptor_spec, $pipes);

	if (is_resource($process)) {
 
		$output = stream_get_contents($pipes[1]);

		fclose($pipes[1]);
		fclose($pipes[0]);

		$return = proc_close($process);
		if ($return != 0) $output = "Error";
	}
	else $output = "Error";
	$output = str_replace(PHP_EOL, '', $output);
	$filesize = filesize($target);
	$features = $filesize."_".$output;
	$file_md5 = md5_file($target);

	//查询ppfeature对应的fid 如果FID没有 则生成fid
	/*
	*	online api.cloudplay.pptv.com
	*/
	$host = "http://api.cloudplay.pptv.com";
	$requestFid = $host . "fsvc" . DIRECTORY_SEPARATOR . "private" . DIRECTORY_SEPARATOR . "1" . DIRECTORY_SEPARATOR . "ppfeature" . DIRECTORY_SEPARATOR . $features . DIRECTORY_SEPARATOR . "fid";
	$rsFid = json_decode(send_get($requestFid),true);
	if($rsFid['err']==0){
		$fid = $rsFid['data'];
	}else{
		$creatFidUrl = $host . DIRECTORY_SEPARATOR . "fsvc" . DIRECTORY_SEPARATOR . "private" . DIRECTORY_SEPARATOR . "1" . DIRECTORY_SEPARATOR . "file?from=ppcloud";
		$dataRequest = array(
		'md5'=>$file_md5,
		'file_size'=>$filesize,
		'features' =>array('feature_pplive'=>$features),
		);
		$param = json_encode($dataRequest);
		$rsFid = json_decode(send_json_post($creatFidUrl,$param),true);
		if($rsFid['err']==0){
			$fid = $rsFid['data']['fid'];
		}else{
			die("fid error");
		}
	}
	$file_path = "http://{$_SERVER['SERVER_NAME']}/uploads/{$_REQUEST['name']}";
	// /fsvc/2/file/httpUpload?fid=?&filepath=?& feature_pplive=?&fromcp =？
    $uploadUrl = $host . DIRECTORY_SEPARATOR . "fsvc" . DIRECTORY_SEPARATOR . "2" . DIRECTORY_SEPARATOR . "file" . DIRECTORY_SEPARATOR . $fid . DIRECTORY_SEPARATOR ."httpUpload?filepath=".$file_path."&feature_pplive=".$features."&fromcp=ppcloud&fid=".$fid;
	$resUpload = json_decode(send_get($uploadUrl),true);
	$resUpload['fid'] = $fid;
	$resUpload['features'] = $features;
	$resUpload['file_path'] = $file_path;
	
	echo json_encode($resUpload);
