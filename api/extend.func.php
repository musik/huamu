<?php
defined('IN_DESTOON') or exit('Access Denied');
#Your Functions
function pebug($arr,$exit = false){
    printf("<pre>%s</pre>",var_export($arr,true));
    if($exit) exit();
}
define('DEFAULT_CITY',false);
function to_pinyin($str,$type=null){
  if(!class_exists('Pinyin'))
    require DT_ROOT. '/extend/Pinyin/Pinyin.php';
  $arr = Pinyin::getPinyin($str,$type);
  return $arr[0];
}
//hacked start get_cat_by_dir
function get_cat_by_dir($catdir,$moduleid) {
	global $db;
	return $db->get_one("SELECT * FROM {$db->pre}category WHERE moduleid = $moduleid and catdir='$catdir'");
}
function pr($arr,$exit=false){
  if ($arr === false){
    echo 'false';
  }else{
    printf('<pre>%s</pre>',print_r($arr,true));
  }
  if($exit) exit();
}
function get_cats_for_detect($moduleid){
	global $db;
	$condition = "moduleid=$moduleid";
	$cat = array();
	$result = $db->query("SELECT catid,catname,parentid FROM {$db->pre}category WHERE $condition ORDER BY parentid desc,catid ASC", 'CACHE');
	while($r = $db->fetch_array($result)) {
    if($r['catname'] == '其它') continue;
		$cat[$r['catid']] = $r['catname'];
	}
	return $cat;
}

//ini_set("pcre.recursion_limit", "300000");
function detect_cat($post,$moduleid=5){
  if($post['catid']) return $post; 
  $cats = get_cats_for_detect($moduleid);
  $names =  str_replace('/','\/',(implode('|',array_unique(array_values($cats)))));

  $len = mb_strlen($names);
  while(mb_strlen($names) > 31550){
    $start = 0;
    $tmp = mb_substr($names,$start,31550);
    $limit = strrpos($tmp,'|');
    $tmp = mb_substr($names,$start,$limit);
    $names_arr[] = $tmp;
    $start += ($limit + 1);
    $names = mb_substr($names,$start);
  }
  $names_arr[] = $names;

  foreach($names_arr as $names){
    if(!preg_match('!'.$names.'!',$post['title'],$m)) continue;
    $catname = $m[0];
    $post['catid'] = array_search($catname,$cats);
    break;
  }
  return $post;
}
function bulk_fix_cats($last_id,$per = 100){
	global $db;
	$result = $db->query("SELECT title,itemid FROM {$db->pre}sell WHERE itemid < $last_id ORDER BY itemid desc limit $per");
	while($r = $db->fetch_array($result)) {
    $id = $r['itemid'];
    $r = detect_cat($r);
    if(array_key_exists('catid',$r)){
      $db->query("update {$db->pre}sell set catid = $r[catid] where itemid = $r[itemid]");
    }
	}
	return $id;
}
function update_cat_by_detect($id){
  global $db;
  $r = $db->get_one("select title from {$db->pre}sell where itemid = $id");
  $r = detect_cat($r);
  if(array_key_exists('catid',$r)){
    $db->query("update {$db->pre}sell set catid = $r[catid] where itemid = $id");
  }
}

//hacked detect_sells
function detect_items_for_cats($cats,$mid){
  $tb =get_table($mid);
  foreach($cats as $cat){
    detect_items_for_cat($cat,$mid,$tb);
  }
}
function detect_items_for_cat($cat,$mid,$table){
  global $db;
  $condition = "WHERE title like '%{$cat[catname]}%' and catid != {$cat[catid]}";
  $sql = "update {$table} set catid = {$cat[catid]} $condition";
  $db->query($sql);
  $condition = "catid = {$cat[catid]} and status = 3";
  $item = $db->count($table, $condition);
  $db->query("UPDATE {$db->pre}category SET item=$item WHERE catid=$cat[catid]");
}
//hacked end
?>
