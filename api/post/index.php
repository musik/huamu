<?php
$key = 'd8f63kd0';
$status = 3;
define('DT_NONUSER', true);
define('DT_DEBUG', true);
require '../../common.inc.php';
require DT_ROOT.'/include/post.func.php';
require DT_ROOT.'/include/module.func.php';
if($DT_BOT) dhttp(403);
//$vars = get_defined_vars();
//var_dump(array_keys($vars));
//$vars['GLOBALS'] = false;
//var_export($vars);
require DT_ROOT."/api/post/functions.php";
mlog($_POST,0);
$action = $_GET['action'];
$moduleid = $_REQUEST['moduleid'];
$test = $_GET['test'];
switch($action){
case "new":
  $ap = new AutoPost($moduleid);
  if($test){
    include DT_ROOT.'/api/post/test/'. $moduleid.'.php';
  }
  if(!$post)
    $post = $_POST;
  $ap->post($post);
  break;
case "keyword_new":
  if($test){
    include DT_ROOT.'/api/post/test/keyword.php';
  }
  if(!$post)
    $post = $_POST;
  keyword_new($post['keywords'],$moduleid,$post['ali_cat']);
  break;
case 'keywords_rss':
  require DT_ROOT.'/include/keyword.class.php';
  $status = isset($kstatus) ? intval($kstatus) : 2;
  $condition = "status=$status";
  $pagesize = 10000;
  $offset = ($page - 1) * $pagesize;
  $do = new keyword();
  $lists = $do->get_list($condition, 'itemid desc');
  keywords_rss_1688($lists);
break;
case "cats":
  $cats = get_maincat(0,$moduleid);
  //echo "cid|name|url|moduleid<br />";
  foreach($cats as $cat){
    echo "$cat[catid]|$cat[catname]<br />";
  }
  break;
case "rss":
  $min = $_GET['min'] ? $_GET['min'] : 0;
  $cats = get_cats_by_order($moduleid,"listorder > $min");
  $op = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
  $op .= '<rss version="2.0">'."\n<channel>\n";
  //$op .= "\t<title>1688</title>\n";
  //$op .= "\t<link>http://www.ynlp.com</link>\n";
  $source = $_GET['source'];
  switch($source){
  case 'kjpd':
    foreach($cats as $cat){
      $op .= "\t<item>\n\t\t<link>http://kjpd.agri.gov.cn/usc/usc/InfoSearchBaseAction.do?method=secondarysearch&amp;KEYWORDS=".urlencode($cat[catname])."&amp;CATEGORY=TITLE</link>\n\t</item>\n";
      $links[] = "http://kjpd.agri.gov.cn/usc/usc/InfoSearchBaseAction.do?method=secondarysearch&KEYWORDS=".urlencode($cat[catname])."&CATEGORY=TITLE&pageSize=100";
    }
    break;
  default:
    $pcat = $_GET['pcat'];
    foreach($cats as $cat){
      if($cat['catname'] == '其它') continue;
      $link ="http://s.1688.com/selloffer/offer_search.htm?keywords=".urlencode(iconv("UTF-8","GBK",$cat[catname].$_GET['suffix']));
      if($pcat)
        $link .= "&amp;categoryId=$pcat";
      $op .= "<item><title>{$cat[catname]}</title><link>$link</link></item>\n";
      $links[] =$link;
    }
    break;
  }
  $op .= "\t</channel>\n</rss>";
  if(!$_GET['txt']) header('Content-Type: application/xml; charset=utf-8');
  echo $_GET['txt'] ? implode("\n",$links) : $op;
  break;
  default:
    break;
}
