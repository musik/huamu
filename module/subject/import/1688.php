<?php
if(!class_exists('Snoopy'))
  require DT_ROOT .'/extend/snoopy.class.php';
if(!class_exists('nokogiri'))
  require DT_ROOT .'/extend/nokogiri.php';
if(!class_exists('HtmlParserModel'))
  require DT_ROOT .'/extend/HtmlParserModel.php';
install_1688();
function install_1688(){
  global $db;
  if(!$db->get_one("show tables like '".$db->pre."fetch_log'")){
    $sql = <<<EOT
CREATE TABLE `destoon_fetch_log` (
  `id` bigint(20) unsigned NOT NULL default '0',
  `context` varchar(10) NOT NULL,
  PRIMARY KEY  (`context`,`id`)
) COMMENT='采集记录';
EOT;
    $sql = str_replace('destoon_', $db->pre, $sql);
    $db->query($sql);
  };
}
function fetch_log_exists($context,$id){
  global $db;
  $db->get_one("select 1 from {$db->pre}fetch_log where context='$context' and id=$id");
}
function process_1688($q){
  $ids = search_1688($q);
  foreach($ids as $id){
    if(!fetch_log_exists('1688',$id)){
      fetch_1688($id);
    }
    break;
  }
}
function fetch_1688($id){
  $url = "http://detail.1688.com/offer/$id.html";
  $cl = new Snoopy;
  if($cl->fetch($url)){
    $html = iconv('GBK','UTF-8',$cl->results);
    $saw = new HtmlParserModel($html);
    $title = $saw->find('h1.d-title ',0)->getPlainText();//->toText();
    echo $title;
  }else{
    echo 'fetch fail';
  }
}
function search_1688($q){
  $url = "http://s.1688.com/selloffer/offer_search.htm?keywords=".urlencode(iconv('UTF-8','GBK',$q));
  $cl = new Snoopy;
  if($cl->fetch($url)){
    //$html = iconv('GBK','UTF-8',$cl->results);
    $saw = new nokogiri($cl->results);
    foreach($saw->get('#sw_mod_searchlist li h2.sm-offerShopwindow-title > a') as $link){
      if($link['href'] && preg_match('/detail.1688.com\/offer\/(\d+).html/',$link['href'],$match)){
        $ids[] = $match[1];
      }
    }
  }
  return($ids);
}
