<?php
include 'api_common.inc.php';

//检查传参
if (empty($_POST['token']) || empty($_FILES['upload'])
    || empty($_GET['app']) || empty($_GET['tk'])
) {
    scriptReturn('', 'token,upload,app,tk can not be empty', 403);
}

//检查token
parse_str(des_decrypt(base64_decode(urldecode($_POST['token'])), $TRIPLEDES_KEY, $TRIPLEDES_IV), $session);
if (empty($session)) {
    scriptReturn('', 'token error', 403);
}

//检查是否是正常文件
if ($_FILES["upload"]["error"] > 0) {
    scriptReturn('', '未选择文件', 422);
}

//图片大小不能超过4M，像素宽高不能超过4000
if (filesize($_FILES['upload']['tmp_name']) > 4 * 1024 * 1024) {
    scriptReturn('', '图片大小不能超过4M', 422);
}
$imageInfo = getimagesize($_FILES['upload']['tmp_name']);
if (!$imageInfo || $imageInfo[0] > 4000 || $imageInfo[1] > 4000) {
    scriptReturn('', '图片像素宽高不能超过4000', 422);
}

//上传处理
$fileExt = pathinfo($_FILES['upload']['name'], PATHINFO_EXTENSION);
$target = UPLOAD_DIR . "/" . basename($_FILES['upload']['tmp_name']) . ".{$fileExt}";
//如果需要裁剪图片则裁剪，不需要直接保存
if (isset($_POST['x']) && isset($_POST['y']) && !empty($_POST['w']) && !empty($_POST['h']) && !empty($_POST['iw'])) {
    cropImage($target, $_FILES['upload']['tmp_name'], $_POST['x'], $_POST['y'], $_POST['w'], $_POST['h'], $_POST['iw']);
} else {
    move_uploaded_file($_FILES['upload']['tmp_name'], $target);
}

//传文件
$post_data = array(
    'upload' => "@{$target}",
);
$uploadUrl = "http://api.grocery.pptv.com/upload_file.php?app={$_GET['app']}&tk={$_GET['tk']}&prod={$_GET['prod']}";
$return = json_decode(send_post_pic($uploadUrl, $post_data), 1);
@unlink($target);
if (empty($return['data'])) {
    scriptReturn('', '公有云接口出错', 500);
}
scriptReturn($return['data'], 'success', 0);
?>