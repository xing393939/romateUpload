<?php
include 'api_common.inc.php';

date_default_timezone_set('Asia/Shanghai');
$username = isset($_GET['username']) ? trim($_GET['username']) : '';
$apikey = isset($_GET['apikey']) ? trim($_GET['apikey']) : '';
if ($IS_LOCAL) {
    $username = 'scdddk@163.com';
    $apikey = 'FBBA0D39FA8F0A34814B1DDC24FCBB42';
}
$cb = isset($_GET['cb']) ? trim($_GET['cb']) : '';

$url = SVC_PPTVYUN . '/api/channel/channellist';
$params = array(
    'username' => $username,
    'apitk' => md5($apikey . $url),
    'key' => isset($_GET['key']) ? trim($_GET['key']) : '',
    'pagenum' => isset($_GET['pagenum']) ? intval($_GET['pagenum']) : 1,
    'pagesize' => isset($_GET['pagesize']) ? intval($_GET['pagesize']) : 10,
);
$url .= '?' . http_build_query($params);
$arr = json_decode(send_get($url), 1);
if (!isset($arr['err']) || $arr['err'] != 0) {
    $return = json_encode(array(
        'err' => 403,
        'data' => array(),
        'msg' => 'apikey认证失败',
    ));
    die("{$cb}({$return})");
}

$list = array();
$statusArr = array(
    '0' => '创建',
    '99' => '重传中',
    '101' => '待压制',
    '102' => '压制中',
    '106' => '待审核',
    '107' => '审核中',
    '108' => '审核通过',
    '111' => '待分发',
    '112' => '分发中',
    '116' => '待回调',
    '150-179' => '压制失败',
    '180' =>    '审核不通过',
    '190' => '分发失败',
    '191' => '回调失败',
    '200' => '可播放',
    '210' => '入epg中',
    '211' => '入epg失败',
    '212' => '入epg成功'
);
foreach ($arr['data'] as $row) {
    $statusStr = '创建中';
    if (isset($statusArr[$row['transcodeStatus']])) {
        $statusStr = $statusArr[$row['transcodeStatus']];
    } elseif ($row['transcodeStatus'] >= 150 && $row['transcodeStatus'] <= 179) {
        $statusStr = '压制失败';
    }
    if (empty($row['coverImage'])) {
        $row['coverImage'] = 'http://grocery.pptv.com/lpic/ea7/a31/013/de8660322896b27530d31d82a0aa7bd0.jpg';
    }
    if (!empty($row['screenshot'])) {
        $row['coverImage'] = 'http://v.img.pplive.cn/sp160' . $row['screenshot'];
    }
    $list[] = array(
        'id' => $row['id'],
        'channelName' => $row['channelName'],
        'channelSummary' => $row['channelSummary'],
        'coverImage' => $row['coverImage'],
        'transcodeStatus' => $row['transcodeStatus'],
        'statusStr' => $statusStr,
        'createTime' => date('Y-m-d H:i:s', $row['createTime'] / 1000),
        'player_id' => $row['channelWebId'],
        'player_chid' => $row['channelId'],
        'player_cid' => $row['id'],
        'duration' => $row['duration'],
        'length' => $row['length'],
    );
}

$return = json_encode(array(
    'err' => 0,
    'data' => $list,
    'msg' => 'success',
    'totalnum' => $arr['totalnum'],
));
die("{$cb}({$return})");
