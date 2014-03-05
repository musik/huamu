<?php
if(!class_exists('Snoopy'))
  require DT_ROOT .'/extend/snoopy.class.php';
if(!class_exists('nokogiri'))
  require DT_ROOT .'/extend/nokogiri.php';
function process_1688($q){
  $ids = search_1688($q);
  foreach($ids as $id){
     
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
