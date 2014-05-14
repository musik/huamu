<?php 
defined('IN_DESTOON') or exit('Access Denied');
if($DT_BOT || $_POST) dhttp(403);
require DT_ROOT.'/module/'.$module.'/common.inc.php';
if(!check_group($_groupid, $MOD['group_search'])) include load('403.inc');
require DT_ROOT.'/include/post.func.php';
require DT_ROOT.'/include/keyword.class.php';
include load('search.lang');
$do = new keyword();
$sorder  = array('结果排序方式', '总搜索量降序', '总搜索量升序', '本月搜索降序', '本月搜索升序', '本周搜索降序', '本周搜索升序', '今日搜索降序', '今日搜索升序', '信息数量降序', '信息数量升序', '更新时间降序', '更新时间升序');
$dorder  = array('itemid DESC', 'total_search DESC', 'total_search ASC', 'month_search DESC', 'month_search ASC', 'week_search DESC', 'week_search ASC', 'today_search DESC', 'today_search ASC', 'items DESC', 'items ASC', 'updatetime DESC', 'updatetime ASC');
isset($order) && isset($dorder[$order]) or $order = 0;
$order_select  = dselect($sorder, 'order', '', $order);
$status = isset($status) ? intval($status) : 3;
$condition = "status=$status";
if($keyword) $condition .= " AND keyword LIKE '%$keyword%'";
if($moduleid) $condition .= " AND moduleid=$moduleid";
$pagesize = 10;
$offset = ($page - 1) * $pagesize;
$lists = $do->get_list($condition, $dorder[$order]);
$pages = preg_replace('/\/\w+?\/ki-htm-page-1.html/','/ki',$pages);
$seo_file = 'ki';
include DT_ROOT.'/include/seo.inc.php';
$seo_title = "热门关键词".$seo_delimiter.$seo_sitename;
include template('ki', $module);
?>
