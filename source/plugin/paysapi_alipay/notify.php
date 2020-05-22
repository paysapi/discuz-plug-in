<?php

require '../../class/class_core.php';
require '../../function/function_forum.php';
$discuz = C::app();
$discuz->init();
loadcache('plugin');

$paysapi = $_G['cache']['plugin']['paysapi_alipay'];

$token = $paysapi['Token'];
$paysapi_id = $_POST['paysapi_id'];
$orderid = $_POST['orderid'];
$price = $_POST['price'];
$realprice = $_POST['realprice'];
$orderuid = $_POST['orderuid'];
$key = $_POST['key'];

$temps = md5($orderId . $orderuid . $paysapi_id . $price . $realprice . $token);
// echo $token."|";
// echo $temps."|";
// echo $key."|";

//检查签名
if ($temps != $key) {
    echo 'SIGN ERROR';
    die();
}           


$order = DB::fetch_first("select * from " . DB::table('forum_order') . " where orderid='" . $orderid . "' and status=1");
if (!$order) {
    echo 'SUCCESS';
    die();
}

$data = array('status' => 2, 'confirmdate' => time());
$where = array('orderid' => $orderid);
DB::update('forum_order', $data, $where);

updatemembercount($order['uid'], array($_G['setting']['creditstrans'] => $order['amount']), true, '', 1, '', '积分充值');

notification_add($order['uid'], 'system', 'addfunds', array(
    'orderid' => $order['orderid'],
    'price' => $order['price'],
    'from_id' => 0,
    'from_idtype' => 'buycredit',
    'value' => $_G['setting']['extcredits'][$_G['setting']['creditstrans']]['title'] . ' ' . $order['amount'] . ' ' . $_G['setting']['extcredits'][$_G['setting']['creditstrans']]['unit'],
), 1);

echo 'SUCCESS';


