<?php

function xfile_put_contents($file, $string, $append = '') {
  $mode = $append == '' ? 'wb' : 'ab';
  $fp = @fopen($file, $mode) or exit("Can not open $file");
  flock($fp, LOCK_EX);
  $stringlen = @fwrite($fp, $string);
  flock($fp, LOCK_UN);
  @fclose($fp);
  return $stringlen;
}
function mlog($string,$append = true){
  if(!is_string($string))
    $string = var_export($string,true);
  $filename = DT_ROOT."/api/post/tmp.txt";
  xfile_put_contents($filename,$string,$append);
}
function keyword_new($str,$moduleid=5,$ali_cat=null){
  $arr = explode("\n",$str);
  $arr = array_unique($arr);
  $size = count($arr);
  $created = 0;
  foreach($arr as $name){
    $new = keyword_create(trim($name),0,$moduleid,$ali_cat);
    if($new) $created++;
  }
  echo "done: size: $size ; created: $created";
}
function keyword_create($kw, $items, $moduleid,$ali_cat=null) {
	global $db, $DT_TIME, $DT;
	if(strlen($kw) < 3 || mb_strlen($kw,'UTF-8') > 8 || strpos($kw, ' ') !== false || strpos($kw, '%') !== false) return;
	$kw = addslashes($kw);
	$r = $db->get_one("SELECT * FROM {$db->pre}keyword WHERE moduleid=$moduleid AND word='$kw'");
	if($r) return false;
  $letter = gb2py($kw);
  $status = 2;
  //echo("INSERT INTO {$db->pre}keyword (moduleid,word,keyword,letter,items,updatetime,total_search,month_search,week_search,today_search,status) VALUES ('$moduleid','$kw','$kw','$letter','$items','$DT_TIME','0','0','0','0','$status')");exit();
  $db->query("INSERT INTO {$db->pre}keyword (moduleid,word,keyword,letter,items,updatetime,total_search,month_search,week_search,today_search,status,ali_cat) VALUES ('$moduleid','$kw','$kw','$letter','$items','$DT_TIME','0','0','0','0','$status',$ali_cat)");
  return true;
}
function keywords_rss_1688($keywords){
  $op = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
  $op .= '<rss version="2.0">'."\n<channel>\n";
  foreach($keywords as $i=>$r){
    $link ="http://s.1688.com/selloffer/offer_search.htm?keywords=".urlencode(iconv("UTF-8","GBK",$r['word']));
    $op .= "<item><i>$i</i><link>$link</link></item>\n";
    //$op .= "<item><i>$i</i><title>{$r['word']}</title><link>$link</link></item>\n";
    $links[] =$link;
  }
  //$op .= "\t<pages><![CDATA[ $pages ]]</pages>\n</rss>";
  $op .= "\t</channel>\n</rss>";
  echo $op;
}

class AutoPost{
  function __construct($moduleid){
    $this->moduleid = $moduleid;
    $this->mod = cache_read("module-".$moduleid.'.php');
    $this->module = $this->mod['module'];
  }
  function post($data){
    global $cl;
    require DT_ROOT . "/module/{$this->module}/{$this->module}.class.php";
    $this->cl = new $this->module($this->moduleid); 
    $this->cl->table = get_table($this->moduleid);
    $this->cl->table_data = get_table($this->moduleid,1);
    $this->cl->table_search = str_replace('data_','search_',$this->cl->table_data);
    $data = $this->parse_data($data);
    if($this->cl->pass($data)){
      $post_fields = $data["post_fields"];
      if($post_fields) {
        require DT_ROOT.'/include/fields.func.php';
        fields_check($post_fields);
      }
      $var = $this->cl->add($data);
      if($post_fields) fields_update($post_fields, $this->cl->table, $this->cl->itemid);
      //$this->check_quote($data);
      if($data['keyword'])
        $this->update_keyword($data['keyword'],$this->cl->itemid);
      echo "done";
    }else{
      echo "error:";
      echo($this->cl->errmsg);
    }
  }
  function update_keyword($name,$sell_id){
    require DT_ROOT.'/include/keyword.class.php';
    $do = new keyword();
    $do->update_sells($name,$sell_id);
  }
  function check_quote($data){
    global $MODULE,$db;
    if(!$data['price'] or !$data['q'] or !$data['unit']) return;
    if(!$MODULE[7]) return;
    if($this->module != 'sell') return;
    if(false === strpos('棵株支',$data['unit'])) return;
    $data['unit'] = '株';
    $prod = $db->get_one("select * from {$db->pre}quote_product where title = '$data[q]'");
    if(!$prod){
      $prod = $this->create_prod($data);
    }
    if(!$prod) return;
    require DT_ROOT."/module/quote/price.class.php";
    $pc = new price();
    $data['pid'] = $prod['itemid'];
    global $P;
    $P = true;
    if(preg_match_all('/(株高|胸径|地径|蓬径|苗龄)：(\S+)/',$data['content'],$ms)){
      $data['note'] = implode(';',$ms[0]);
    }
    if(!$data['telephone'] && $data['mobile'])
      $data['telephone'] = $data['mobile'];
    if($pc->pass($data)){
      $pc->add($data);
    }else{
      echo $pc->errmsg;
    }
  }
  function create_prod($data){
    global $db;
    $catname = $data['catname'] ? $data['catname'] : $data['q'];
    $cat = $db->get_one("select catid from {$db->pre}category where moduleid = 7 and catname = '{$catname}'");
    if(!$cat) return;
    require DT_ROOT."/module/quote/product.class.php";
    $pc = new product();
    $arr = array(
      'title' => $data['q'],
      'unit'  => $data['unit'],
      'catid' => $cat['catid']
    );
    if($pc->pass($arr)){
      $itemid = $pc->add($arr);
      return $pc->get_one();
    }else{
      echo $pc->errmsg;
    }
  }
  function parse_data($data){
    global $db;
    unset($data["key"]);
    unset($data["moduleid"]);
    if(!$data['catid'] && $data['q']){
      $cat = $db->get_one("select catid from {$db->pre}category where moduleid = $this->moduleid and catname = '{$data[q]}'");
      if($cat) $data['catid'] = $cat['catid'];
     }
    if(method_exists($this,'parse_'.$this->module)){
      return call_user_func_array(array($this,'parse_'.$this->module),array($data));
    }
    return $data;
  }
  function parse_article($data){
    $this->check_exists($data);
    global $db;
    $data['tag'] = trim(str_replace(',',' ',$data['tag']));
    $data['status'] = 3;
    //pebug($data,1);
    return $data;
  }
  function check_exists($data,$key='fromurl'){
    global $db;
    $condition = $key . "= '".$data[$key]."'";
    $e = $db->get_one("select itemid from {$this->cl->table} where $condition");
    if($e)
      exit("error: $key 已存在");
  }

  function parse_title($data){
    $vars = array(
      '类型','品牌','加工产品种类','加工贸易形式',
      '材质','品名','品种'
    );
    $ns = array();
    if(preg_match_all('/<p>(.+?)：(.+?)<\/p>/',$data['content'],$matches,PREG_SET_ORDER)){
      foreach($matches as $m){
        if(in_array($m[1],$vars)){
          $ns[] = $m[2];
        }
      }
    }
    $title = implode('',$ns) . $data['areaname2'] . $data['title'];
    $exclude = ",|.(){}[]-_/\;'\":，。（）【】 ";
    $exclude = preg_split('/(?<!^)(?!$)/u',$exclude);
    $title = str_replace($exclude,'',$title);
    $title = mb_substr($title,0,30,'UTF-8');
    return $title;
  }
  function parse_thumb($data){
    if(!$data['thumb'] && $data['photo_url']){
      global $_userid;
      $_userid = 1;
      $data['photo_url'] = html_entity_decode($data['photo_url']);
      $data['thumb'] = save_remote("src=" . $data['photo_url']);
      $data['thumb'] = substr($data['thumb'],4,strlen($data['thumb']));
    }
    return $data;
  }
  function parse_sell($data){
    $data['title'] = $this->parse_title($data);
    $this->check_exists($data,'title');
    $data = $this->parse_thumb($data);
    global $db;
    $keys = explode(',','style,brand,tag,keyword,pptword,thumb,thumb1,thumb2,email,msn,qq,skype,linkurl,filepath,note');
    foreach($keys as $k){
      if(!array_key_exists($k,$data))
        $data[$k] = '';
    }
    $data['status'] = 3;
    if(!$data['areaid'] && $data['areaname'])
      $data['areaid'] = $this->detect_area($data["areaname2"] . " ". $data["areaname"]);
    if($data['q']){
      $cat = $db->get_one("select catid from {$db->pre}category where moduleid = $this->moduleid and catname = '{$data[q]}'");
      if($cat) $data['catid'] = $cat['catid'];
    }
    if($data['fromurl']){
      $post_fields['fromurl'] = $data['fromurl'];
      $data["post_fields"] = $post_fields;
    }
    if($data['keyword']){
      $data['n1'] = '关键词';
      $data['v1'] = $data['keyword'];
    }
    return $data; 
  }
  function detect_area($str){
    global $db,$DT_PRE;
    $arr = explode(" ",$str);
    $arr = array_unique($arr);
    foreach($arr as $str){
      $likes[] = "areaname like '%{$str}%'";
    }
    $conditions = implode(" or ",$likes);
    $r = $db->get_one("select * from {$DT_PRE}area where $conditions order by child asc");
    return $r['areaid'];
  }
  function parse_subject($data){
    global $DT_PRE,$DT;
    if(!$data['status'])
      $data['status'] = 2;
    $data['listorder'] = $data['vol'];
    $data['username'] = 'admin';
    if(!$data['introduce'] && $data['summary']){
      $data['introduce'] = $data['summary'];
      unset($data['summary']);
    }
    if(!$data['thumb'] && $data['photo']){
      if(preg_match('/^http/',$data['photo'])){
        global $_userid;
        $_userid = 1;
        $data['thumb'] = save_remote("src=" . $data['photo']);
        $data['thumb'] = substr($data['thumb'],4,strlen($data['thumb']));
       }else{
         if(preg_match('/src="(file.+?)"/',$data['photo'],$match)){
          $data['thumb'] = $DT['linkurl']. $match[1];
         }
       }
    }
    if(!$data['content'] && $data['introduce']){
      $data['content'] = $data['introduce'];
    }
    $post_fields = array();
    if($data["attrs"]){
      if(preg_match_all('/<p><strong>(.+?)：<\/strong>(.+?)<\/p>/',$data["attrs"],$matches)){
        $attrs = array_combine($matches[1],$matches[2]);
        $FD = cache_read('fields-'.substr($this->cl->table, strlen($DT_PRE)).'.php');
        if($FD){
          $post_fields = array();
          foreach($FD as $cf){
            if(array_key_exists($cf['title'],$attrs)){
              $post_fields[$cf['name']] = $attrs[$cf['title']];
            }
          }
        }
        //$data['content'] .= "<ul>" . preg_replace('/<p><strong>(.+?)：<\/strong>(.+?)<\/p>/','<li><em>\1</em>\2</li>',$data['attrs']) ."</ul>";
        //unset($data["attrs"]);
      }
    }
    $data["post_fields"] = $post_fields;
    return $data;
  }
}
