<?php
defined('IN_DESTOON') or exit('Access Denied');
include tpl('header');
if(!$mallid) show_menu($menus);
?>
<div class="tt">交易详情</div>
<table cellpadding="2" cellspacing="1" class="tb">
<tr>
<td class="tl">订单单号</td>
<td><?php echo $item['itemid'];?> <?php if($DT['trade']) { ?>(<?php echo $DT['trade_nm'];?>订单单号:<a href="https://lab.alipay.com/consume/queryTradeDetail.htm?tradeNo=<?php echo $item['trade_no'];?>" target="_blank" class="t"><?php echo $item['trade_no'];?></a>)<?php } ?></td>
</tr>
<tr>
<td class="tl">商品名称</td>
<td class="tr"><a href="<?php echo $item['linkurl'];?>" target="_blank" class="t"><?php echo $item['title'];?></a></td>
</tr>
<tr>
<td class="tl">商品图片</td>
<td class="tr"><a href="<?php echo $item['linkurl'];?>" target="_blank"><img src="<?php if($item['thumb']) { ?><?php echo $item['thumb'];?><?php } else { ?><?php echo DT_SKIN;?>image/nopic60.gif<?php } ?>" width="60" height="60"/></a></td>
</tr>
<tr>
<td class="tl">卖家</td>
<td><?php if($DT['im_web']) { ?><?php echo im_web($item['seller']);?>&nbsp;<?php } ?><a href="javascript:_user('<?php echo $item['seller'];?>');" class="t"><?php echo $item['seller'];?></a></td>
</tr>
<tr>
<td class="tl">买家</td>
<td><?php if($DT['im_web']) { ?><?php echo im_web($item['buyer']);?>&nbsp;<?php } ?><a href="javascript:_user('<?php echo $item['buyer'];?>');" class="t"><?php echo $item['buyer'];?></a></td>
</tr>
</table>
<div class="tt">物流信息</div>
<table cellpadding="2" cellspacing="1" class="tb">
<tr>
<td class="tl">邮编</td>
<td><?php echo $item['buyer_postcode'];?></td>
</tr>
<tr>
<td class="tl">地址</td>
<td><?php echo $item['buyer_address'];?></td>
</tr>
<tr>
<td class="tl">姓名</td>
<td><?php echo $item['buyer_name'];?></td>
</tr>
<tr>
<td class="tl">电话</td>
<td><?php echo $item['buyer_phone'];?></td>
</tr>
<tr>
<td class="tl">手机</td>
<td><?php echo $item['buyer_mobile'];?></td>
</tr>
<?php if($item['send_time']>0) { ?>
<tr>
<td class="tl">物流类型</td>
<td><?php echo $item['send_type'];?></td>
</tr>
<tr>
<td class="tl">物流号码</td>
<td><?php echo $item['send_no'];?><?php if($item['send_no']) { ?> &nbsp;<a href="<?php echo DT_PATH;?>api/express.php?e=<?php echo urlencode($item['send_type']);?>&n=<?php echo $item['send_no'];?>" target="_blank" class="t">[物流追踪]</a><?php } ?></td>
</tr>
<?php } ?>
<tr>
<td class="tl">备注说明</td>
<td><?php echo $item['note'];?></td>
</tr>
</table>
<div class="tt">价格信息</div>
<table cellpadding="2" cellspacing="1" class="tb">
<tr>
<td class="tl">单价</td>
<td>￥<?php echo $item['price'];?></td>
</tr>
<tr>
<td class="tl">数量</td>
<td><?php echo $item['number'];?></td>
</tr>
<?php if($item['fee']>0) { ?>
<tr>
<td class="tl"><?php echo $item['fee_name'];?></td>
<td>￥<?php echo $item['fee'];?></td>
</tr>
<?php } ?>
<tr>
<td class="tl">总额</td>
<td class="f_red">￥<?php echo $item['money'];?></td>
</tr>
</table>
<div class="tt">订单状态</div>
<table cellpadding="2" cellspacing="1" class="tb">
<tr>
<td class="tl">下单时间</td>
<td><?php echo $item['addtime'];?></td>
</tr>
<tr>
<td class="tl">更新时间</td>
<td><?php echo $item['updatetime'];?></td>
</tr>
<?php if($item['send_time']>0) { ?>
<tr>
<td class="tl">发货时间</td>
<td><?php echo $item['send_time'];?></td>
</tr>
<?php } ?>
<tr>
<td class="tl">交易状态</td>
<td><?php echo $_status[$item['status']];?></td>
</tr>
<?php if($item['buyer_reason']) { ?>
<tr>
<td class="tl">买家理由或证据</td>
<td><?php echo $item['buyer_reason'];?></td>
</tr>
<?php } ?>
<?php if($item['refund_reason']) { ?>
<tr>
<td class="tl">操作理由或证据</td>
<td><?php echo $item['refund_reason'];?></td>
</tr>
<tr>
<td class="tl">操作人</td>
<td><?php echo $item['editor'];?></td>
</tr>
<tr>
<td class="tl">操作时间</td>
<td><?php echo $item['updatetime'];?></td>
</tr>
<?php } ?>
</table>

<div class="tt">买家评价<a name="comment1"></a></div>
<table cellpadding="2" cellspacing="1" class="tb">
<?php if($cm['seller_star']) { ?>
<tr>
<td class="tl">买家评分</td>
<td>
<span class="f_r"><a href="#comment" onclick="Ds('c_edit');" class="t">[修改]</a></span>
<img src="<?php echo DT_PATH;?>file/image/star<?php echo $cm['seller_star'];?>.gif" width="36" height="12" alt="" align="absmiddle"/> <?php echo $STARS[$cm['seller_star']];?>
</td>
</tr>
<tr>
<td class="tl">买家评论</td>
<td><?php echo nl2br($cm['seller_comment']);?></td>
</tr>
<tr>
<td class="tl">评论时间</td>
<td class="px11"><?php echo timetodate($cm['seller_ctime'], 6);?></td>
</tr>
<?php if($cm['buyer_reply']) { ?>
<tr>
<td class="tl">卖家解释</td>
<td style="color:#D9251D;"><?php echo nl2br($cm['buyer_reply']);?></td>
</tr>
<tr>
<td class="tl">解释时间</td>
<td class="px11"><?php echo timetodate($cm['buyer_rtime'], 6);?></td>
</tr>
<?php } ?>
<?php } else { ?>
<tr>
<td class="tl">买家评论</td>
<td>暂未评论</td>
</tr>
<?php } ?>
</table>

<div class="tt">卖家评价<a name="comment2"></a></div>
<table cellpadding="2" cellspacing="1" class="tb">
<?php if($cm['buyer_star']) { ?>
<tr>
<td class="tl">卖家评分</td>
<td>
<span class="f_r"><a href="#comment" onclick="Ds('c_edit');" class="t">[修改]</a></span>
<img src="<?php echo DT_PATH;?>file/image/star<?php echo $cm['buyer_star'];?>.gif" width="36" height="12" alt="" align="absmiddle"/> <?php echo $STARS[$cm['buyer_star']];?>
</td>
</tr>
<tr>
<td class="tl">卖家评论</td>
<td><?php echo nl2br($cm['buyer_comment']);?></td>
</tr>
<tr>
<td class="tl">评论时间</td>
<td class="px11"><?php echo timetodate($cm['buyer_ctime'], 6);?></td>
</tr>
<?php if($cm['seller_reply']) { ?>
<tr>
<td class="tl">买家解释</td>
<td style="color:#D9251D;"><?php echo nl2br($cm['seller_reply']);?></td>
</tr>
<tr>
<td class="tl">解释时间</td>
<td class="px11"><?php echo timetodate($cm['seller_rtime'], 6);?></td>
</tr>
<?php } ?>
<?php } else { ?>
<tr>
<td class="tl">卖家评论</td>
<td>暂未评论</td>
</tr>
<?php } ?>
</table>

<div id="c_edit" style="display:none;">
<div class="tt">修改评价<a name="comment"></a></div>
<form method="post" action="?" id="dform" onsubmit="return check();">
<input type="hidden" name="moduleid" value="<?php echo $moduleid;?>"/>
<input type="hidden" name="file" value="<?php echo $file;?>"/>
<input type="hidden" name="action" value="comment"/>
<input type="hidden" name="mallid" value="<?php echo $mallid;?>"/>
<input type="hidden" name="itemid" value="<?php echo $itemid;?>"/>
<table cellpadding="2" cellspacing="1" class="tb">
<tr>
<td class="tl">买家评分</td>
<td>
<input type="radio" name="post[seller_star]" value="3"<?php echo $cm['seller_star'] == 3 ? ' checked' : '';?>/> 好评 
<input type="radio" name="post[seller_star]" value="2"<?php echo $cm['seller_star'] == 2 ? ' checked' : '';?>/> 中评 
<input type="radio" name="post[seller_star]" value="1"<?php echo $cm['seller_star'] == 1 ? ' checked' : '';?>/> 差评 
<input type="radio" name="post[seller_star]" value="0"<?php echo $cm['seller_star'] == 0 ? ' checked' : '';?>/> 待评
</td>
</tr>
<tr>
<td class="tl">买家评论</td>
<td><textarea name="post[seller_comment]" style="width:300px;height:60px;"><?php echo $cm['seller_comment'];?></textarea></td>
</tr>
<tr>
<td class="tl">评论时间</td>
<td><input type="text" style="width:150px;" name="post[seller_ctime]" value="<?php echo $cm['seller_ctime'] ? timetodate($cm['seller_ctime'], 6) : '';?>"/></td>
</tr>
<tr>
<td class="tl">卖家解释</td>
<td><textarea name="post[buyer_reply]" style="width:300px;height:60px;"><?php echo $cm['buyer_reply'];?></textarea></td>
</tr>
<tr>
<td class="tl">解释时间</td>
<td><input type="text" style="width:150px;" name="post[buyer_rtime]" value="<?php echo $cm['buyer_rtime'] ? timetodate($cm['buyer_rtime'], 6) : '';?>"/></td>
</tr>

<tr>
<td class="tl">卖家评分</td>
<td>
<input type="radio" name="post[buyer_star]" value="3"<?php echo $cm['buyer_star'] == 3 ? ' checked' : '';?>/> 好评 
<input type="radio" name="post[buyer_star]" value="2"<?php echo $cm['buyer_star'] == 2 ? ' checked' : '';?>/> 中评 
<input type="radio" name="post[buyer_star]" value="1"<?php echo $cm['buyer_star'] == 1 ? ' checked' : '';?>/> 差评 
<input type="radio" name="post[buyer_star]" value="0"<?php echo $cm['buyer_star'] == 0 ? ' checked' : '';?>/> 待评
</td>
</tr>
<tr>
<td class="tl">卖家评论</td>
<td><textarea name="post[buyer_comment]" style="width:300px;height:60px;"><?php echo $cm['buyer_comment'];?></textarea></td>
</tr>
<tr>
<td class="tl">评论时间</td>
<td><input type="text" style="width:150px;" name="post[buyer_ctime]" value="<?php echo $cm['buyer_ctime'] ? timetodate($cm['buyer_ctime'], 6) : '';?>"/></td>
</tr>
<tr>
<td class="tl">买家解释</td>
<td><textarea name="post[seller_reply]" style="width:300px;height:60px;"><?php echo $cm['seller_reply'];?></textarea></td>
</tr>
<tr>
<td class="tl">解释时间</td>
<td><input type="text" style="width:150px;" name="post[seller_rtime]" value="<?php echo $cm['seller_rtime'] ? timetodate($cm['seller_rtime'], 6) : '';?>"/></td>
</tr>
</table>
<div class="sbt"><input type="submit" name="submit" value=" 确 定 " class="btn"/>&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" value=" 返 回 " class="btn" onclick="history.back(-1);"/></div>
</form>
</div>
<script type="text/javascript">
function check() {
	return confirm('确定要修改该订单的评论吗？');
}
</script>
<script type="text/javascript">Menuon(1);</script>
<?php include tpl('footer');?>