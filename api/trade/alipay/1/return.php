<?php
$_DPOST = $_POST;
$_DGET = $_GET;
require '../../../../common.inc.php';
$_POST = $_DPOST;
$_GET = $_DGET;
require '../config.inc.php';
if($_GET['seller_email']) $aliapy_config['seller_email'] = $_GET['seller_email'];
#cache_write('ali/'.$api.'-return-server-'.date('Ymdhis').'.php', $_SERVER);
#cache_write('ali/'.$api.'-return-post-'.date('Ymdhis').'.php', $_POST);
#cache_write('ali/'.$api.'-return-get-'.date('Ymdhis').'.php', $_GET);
/* * 
 * 功能：支付宝页面跳转同步通知页面
 * 版本：3.2
 * 日期：2011-03-25
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。

 *************************页面功能说明*************************
 * 该页面可在本机电脑测试
 * 可放入HTML等美化页面的代码、商户业务逻辑程序代码
 * 该页面可以使用PHP开发工具调试，也可以使用写文本函数AlipayFunction.logResult，该函数已被默认关闭，见alipay_notify_class.php中的函数verifyReturn
 
 * WAIT_SELLER_SEND_GOODS(表示买家已在支付宝交易管理中产生了交易记录且付款成功，但卖家没有发货);
 */

//require_once("alipay.config.php");
require_once DT_ROOT.'/api/trade/alipay/1/pay/alipay_notify.class.php';

$alipayNotify = new AlipayNotify($aliapy_config);
$verify_result = $alipayNotify->verifyReturn();
if($verify_result) {//验证成功
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//请在这里加上商户的业务逻辑程序代码
	
	//――请根据您的业务逻辑来编写程序（以下代码仅作参考）――
    //获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表
    $out_trade_no	= $_GET['out_trade_no'];	//获取订单号
    $trade_no		= $_GET['trade_no'];		//获取支付宝交易号
    $total_fee		= $_GET['price'];			//获取总价格

	$itemid = $out_trade_no;
	$td = $db->get_one("SELECT * FROM {$DT_PRE}mall_order WHERE itemid=$itemid");
	$money = $td['amount'] + $td['fee'];
	if(!$td || $total_fee != $money) message('金额不符(Code:002)', $MODULE[2]['linkurl'].'trade.php?error=2');
	$seller = $td['seller'];
	$seller_email = $_GET['seller_email'];
	$buyer = $td['buyer'];
	$buyer_email = $_GET['buyer_email'];
	$mallid = $td['mallid'];
	$timenow = timetodate($DT_TIME, 3);
	$memberurl = $MODULE[2]['linkurl'];	
	include load('member.lang');

    if($_GET['trade_status'] == 'WAIT_SELLER_SEND_GOODS') {
		//判断该笔订单是否在商户网站中已经做过处理（可参考“集成教程”中“3.4返回数据处理”）
			//如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
			//如果有做过处理，不执行商户的业务程序
		if($td['status'] == 1) {
			$db->query("UPDATE {$DT_PRE}mall_order SET trade_no='$trade_no',status=2,updatetime=$DT_TIME WHERE itemid=$itemid");
			$db->query("UPDATE {$DT_PRE}member SET trade='$seller_email',vtrade=1 WHERE username='$seller' AND vtrade=0");
			$db->query("UPDATE {$DT_PRE}member SET trade='$buyer_email',vtrade=1 WHERE username='$buyer' AND vtrade=0");

			$myurl = userurl($td['buyer']);
			$_username = $td['seller'];
			//send message
			$touser = $td['seller'];
			$title = lang($L['trade_message_t2'], array($itemid));
			$url = $memberurl.'trade.php?itemid='.$itemid;
			$content = lang($L['trade_message_c2'], array($myurl, $_username, $timenow, $url));
			$content = ob_template('messager', 'mail');
			send_message($touser, $title, $content);

			message('订单付款成功，请等待卖家发货', $MODULE[2]['linkurl'].'trade.php?action=order&itemid='.$itemid);
		}
    } else if($_GET['trade_status'] == 'WAIT_BUYER_CONFIRM_GOODS') {
		if(isset($_GET['refund_status'])) {
			if($_GET['refund_status'] == 'WAIT_SELLER_AGREE' && $td['status'] == 3) {//买家申请退款 等待卖家同意
				$db->query("UPDATE {$DT_PRE}mall_order SET status=5,updatetime=$DT_TIME WHERE itemid=$itemid");
				message('申请退款成功，请等待卖家响应', $MODULE[2]['linkurl'].'trade.php?itemid='.$itemid);
			}
		}
		//卖家发货
		if($td['status'] == 2) {
			$db->query("UPDATE {$DT_PRE}mall_order SET status=3,updatetime=$DT_TIME WHERE itemid=$itemid");

			$myurl = userurl($td['seller']);
			$_username = $td['buyer'];
			//send message
			$touser = $td['buyer'];
			$title = lang($L['trade_message_t3'], array($itemid));
			$url = $memberurl.'trade.php?action=order&itemid='.$itemid;
			$content = lang($L['trade_message_c3'], array($myurl, $_username, $timenow, $url));
			$content = ob_template('messager', 'mail');
			send_message($touser, $title, $content);

			message('发货成功，请等待买家确认收货', $MODULE[2]['linkurl'].'trade.php?itemid='.$itemid);
		}
    } else if($_GET['trade_status'] == 'TRADE_FINISHED') {
		//判断该笔订单是否在商户网站中已经做过处理（可参考“集成教程”中“3.4返回数据处理”）
			//如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
			//如果有做过处理，不执行商户的业务程序
		if($td['status'] == 3) {
			$db->query("UPDATE {$DT_PRE}mall_order SET status=4,updatetime=$DT_TIME WHERE itemid=$itemid");
			//更新商品数据
			$db->query("UPDATE {$DT_PRE}mall SET orders=orders+1,sales=sales+$td[number],amount=amount-$td[number] WHERE itemid=$mallid");

			$myurl = userurl($td['buyer']);
			$_username = $td['seller'];			
			//send message
			$touser = $td['seller'];
			$title = lang($L['trade_message_t4'], array($itemid));
			$url = $memberurl.'trade.php?itemid='.$itemid;
			$content = lang($L['trade_message_c4'], array($myurl, $_username, $timenow, $url));
			$content = ob_template('messager', 'mail');
			send_message($touser, $title, $content);

			message('交易成功', $MODULE[2]['linkurl'].'trade.php?action=order&itemid='.$itemid);
		}
	} else if($_GET['trade_status'] == 'WAIT_BUYER_PAY') {
		message('订单创建成功，请尽快通过支付宝付款', $MODULE[2]['linkurl'].'trade.php?action=order&itemid='.$itemid);
    } else {
      //echo "trade_status=".$_GET['trade_status'];
    }
	
	message('验证成功(Code:000)', $MODULE[2]['linkurl'].'trade.php?error=0');
	//echo "验证成功<br />";
	//echo "trade_no=".$trade_no;

	//――请根据您的业务逻辑来编写程序（以上代码仅作参考）――
	
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}
else {
    //验证失败
    //如要调试，请看alipay_notify.php页面的return_verify函数，比对sign和mysign的值是否相等，或者检查$veryfy_result有没有返回true
    message('验证失败(Code:001)', $MODULE[2]['linkurl'].'trade.php?error=1');
}
?>