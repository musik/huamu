<?php
if(!class_exists('Snoopy'))
  require DT_ROOT .'/extend/snoopy.class.php';
if(!class_exists('nokogiri'))
  require DT_ROOT .'/extend/nokogiri.php';
function search_1688($q){
  $url = "http://s.1688.com/selloffer/offer_search.htm?keywords=".urlencode(iconv('UTF-8','GBK',$q));
  $cl = new Snoopy;
  if($cl->fetch($url)){
    //$html = iconv('GBK','UTF-8',$cl->results);
    $saw = new nokogiri($cl->results);
    foreach($saw->get('#sw_mod_searchlist li h2.sm-offerShopwindow-title > a') as $link){
      var_dump($link['href']);
    }
  }
}
