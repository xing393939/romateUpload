<?php
include 'api_common.inc.php';
date_default_timezone_set('Asia/Shanghai');

//查看文件
if (isset($_GET['file'])) {
    die(file_get_contents($_GET['file']));
}

//查看md5
if (isset($_GET['md5'])) {
    die(md5_file($_GET['md5']));
}

$dir = isset($_GET['dir']) ? $_GET['dir'] : UPLOAD_DIR;
$open_dir = opendir($dir);
echo "<table border='1'>";
echo "<tr><th>name</th><th>size</th><th>type</th><th>filemtime</th></tr>";
$href = "api_debug.php?dir=" . dirname($dir);
echo "<tr><td colspan='4'><a href='$href'>back-to-parent-directory</a></tr>";
while ($fileName = readdir($open_dir)) {
    if ($fileName != "." && $fileName != "..") {
        $file = $dir . DIRECTORY_SEPARATOR . $fileName;
        if (filetype($file) == 'dir') {
            $href = "api_debug.php?dir={$file}";
            echo "<tr><td><a href='$href'>" . $fileName . "</a></td>";
        } else {
            echo "<tr><td>$fileName</td>";
        }
        echo "<td>" . filesize($file) . "</td>";
        echo "<td>" . filetype($file) . "</td>";
        echo "<td>" . date('Y-m-d H:i:s', filemtime($file)) . "</td></tr>";
    }
}
echo "</table>";
?>