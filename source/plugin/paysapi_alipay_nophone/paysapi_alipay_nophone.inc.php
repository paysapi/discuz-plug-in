<?php
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

$paysapi = $_G['cache']['plugin']['paysapi_alipay_nophone'];

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

if (!$action || $_SERVER['REQUEST_METHOD'] != 'POST') {
    return;
}

$money = $_POST['money'];
if (!$money || $money < 0) {
    echo json_encode(array('code' => 400, 'msg' => '请输入正确的金额'));
    return;
}


function add_order($order_id,$money)
{
    global $_G, $paysapi;
    $data = array(
        'orderid' => $order_id,
        'status' => 1,
        'uid' => $_G['uid'],
        'amount' => $money  * $paysapi['integral_proportion'],
        'price' => $money ,
        'submitdate' => time(),
        'ip' => $_SERVER['REMOTE_ADDR'],
    );

    C::t('forum_order')->insert($data);
}

$order_id = date('YmdHis') . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);

$notify_url = trim($_G['siteurl'] . 'source/plugin/paysapi_alipay_nophone/notify.php');
$return_url = trim($_G['siteurl'] . 'home.php?mod=spacecp&ac=credit&op=base');

$istype = 6;
$price = $money;
$uid=  $paysapi['Uid'];
$token = $paysapi['Token'];
$goodsname = 'WP-' . $order_id;
$orderuid = "";
$key = md5($goodsname . $istype . $notify_url . $order_id . $orderuid . $price . $return_url . $token . $uid);
$url = "https://pay.bearsoftware.net.cn?key="
        .$key."&notify_url=".urlencode($notify_url)
        ."&orderid=".$order_id
        ."&orderuid=".$orderuid
        ."&return_url=".urlencode($return_url)
        ."&goodsname=".$goodsname
        ."&istype=".$istype
        ."&uid=".$uid
        ."&price=".$price;

add_order($order_id,$money);
echo json_encode(array('code' => 200, 'pay_url' => $url));