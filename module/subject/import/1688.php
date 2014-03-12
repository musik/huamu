<?php
class O688 {
  function __construct(){
    if(!class_exists('Snoopy'))
      require DT_ROOT .'/extend/snoopy.class.php';
    if(!class_exists('nokogiri'))
      require DT_ROOT .'/extend/nokogiri.php';
    if(!class_exists('HtmlParserModel'))
      require DT_ROOT .'/extend/HtmlParserModel.php';
    $this->check_install();
  }
  function check_install(){
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
  function process($q){
    $ids = $this->search($q);
    var_dump($ids);
    foreach($ids as $id=>$title){
      if(!$this->fetch_log_exists('1688',$id)){
        $this->fetch($id);
      }
      break;
    }
  }
  function fetch($id){
    $url = "http://detail.1688.com/offer/$id.html";
    echo $url;
    $cl = new Snoopy;
    if($cl->fetch($url)){
      $html = iconv('GBK','UTF-8',$cl->results);
      $saw = new HtmlParserModel($html);
      try{
        $node = $saw->find('h1.d-title ',0);
        if(!$node){
          throw new Exception('title node not found');
        }
        $title = $node->getPlainText();
        $post = compact('title');
        $post = array_merge($post,$this->_parse_metas($saw));
        $post = array_merge($post,$this->_parse_price($saw));
        $post = array_merge($post,$this->_parse_contact($saw));
        var_dump($post);
      }catch (Exception $e){
        echo 'Caught Exception: ' .$e->getMessage(),"\n";
      }
    }else{
      echo 'fetch fail';
    }
  }
  function _parse_metas($saw){
    $metas = array();
    foreach($saw->find('.de-feature') as $f){
      $str = $f->getPlainText();
      if(!empty($str)){
        $metas[] = str_replace('：',':',$str);
      }
    }
    $metas = implode('||',$metas);
    return compact('metas');
  }
  function _parse_price($saw){
    $node = $saw->find('.unit-detail-price-amount tr',0);
    if(!$node)
      return array();
    $str= $node->attribute['data-range'];
    if(preg_match('/"begin":"(\d+)".+?"price":"([\d\.]+)"/',$str,$match)){
      $minamount = $match[1];
      $price = $match[2];
    }
    return compact('minamount','price');
  }
  function _parse_contact($saw){
    $node = $saw->find('a[data-tracelog=wp_widget_supplierinfo_compname]',0);
    if($node) $company = $node->getPlainText();
    $node = $saw->find('.contact-div a',0);
    if($node) $truename = $node->getPlainText();
    $node = $saw->find('.contact-div a[data-alitalk]',0);
    if($node){
       $str = $node->attribute['data-alitalk'];
       echo $str;
       if(preg_match('/id": "(.+?)"/',$str,$match))
         $ali = $match[1];
    }
    $node = $saw->find('.mobile-number',0);
    if($node) $mobile = $node->getPlainText();
    return compact('company','truename','ali','mobile');
  }
  function search($q){
    $url = "http://s.1688.com/selloffer/offer_search.htm?keywords=".urlencode(iconv('UTF-8','GBK',$q));
    echo $url;
    $cl = new Snoopy;
    if($cl->fetch($url)){
      $html = iconv('GBK','UTF-8',$cl->results);
      $saw = new HtmlParserModel($html);
      foreach($saw->find('a[offer-stat=title]') as $link){
        if(preg_match('/detail.1688.com\/offer\/(\d+).html/',$link->attribute['href'],$match)){
          $ids[$match[1]] = $link->getPlainText();
        }
      }
    }
    return($ids);
  }

}
