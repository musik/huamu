<?php
defined('IN_DESTOON') or exit('Access Denied');
function extend_get_fields(){
  global $FD,$DT_PRE,$table;
  if(!isset($FD))
    $FD = cache_read('fields-'.substr($table, strlen($DT_PRE)).'.php');
  return $FD;
}
function subject_display_field($val,$field){
  if(!$val) return '--';
  if(function_exists("subject_display_".$field['name'])){
    return call_user_func_array("subject_display_".$field['name'],
      array($val));
  }
  if($field['display']){
      global $MOD;
      return "<a class='{$field[name]}' href='{$MOD[linkurl]}search.php?kw={$val}' rel='nofollow'>{$val}</a>";
  }
  return $val;
}
function subject_display_alias($val){
  global $MODULE;
  $val = str_replace(array('，','、'),',',$val);
  $arr = explode(',',$val);
  foreach($arr as $str){
    $arr1[] = "<a class='alias' href='{$MODULE[5][linkurl]}search.php?kw={$str}' rel='nofollow' target='_blank'>{$str}</a>";
  }
  $val = implode(" , ",$arr1);
  return $val;
}
function subject_check_db(){
  global $db,$table,$MODULE,$MOD;
  $rs = $db->get_list("show columns from $table");
  $mod_ids = explode(',',$MOD['module_index']);
  if($mod_ids){
    foreach($MODULE as $k=>$m){
      if(!in_array($k,$mod_ids)) continue;
      $key = $m['moduledir'] . "_cat_id";
      if(!array_key_exists($key,$rs)){
        $db->query("alter table $table add column $key int(10) default null");
      }
    }
  }
}
function subject_modules(){
  global $db,$table,$MODULE,$MOD;
  $SMODS = array();
  $mod_ids = explode(',',$MOD['module_index']);
  if($mod_ids){
    foreach($MODULE as $k=>$m){
      if(in_array($k,$mod_ids))
        $SMODS[$k] = $m;
    }
  }
  return $SMODS;
}
function subject_get_cat($condition = '1'){
  global $db;
  return $db->get_one("select * from {$db->pre}category where $condition");
}
function subject_listpages($CAT, $total, $page = 1, $perpage = 20, $step = 2) {
	global $DT, $MOD, $L,$link_current,$SMOD;
	if($total <= $perpage) return '';
	$items = $total;
	$total = ceil($total/$perpage);
	if($page < 1 || $page > $total) $page = 1;
	$home_url = $link_current;
	$demo_url = $link_current.'?page={destoon_page}';
	$pages = '';
	include DT_ROOT.'/api/pages.'.($DT['pages_mode'] ? 'sample' : 'default').'.php';
	return $pages;
}
?>
