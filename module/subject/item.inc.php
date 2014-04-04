<?php 
$itemid or $slug or dheader($MOD['linkurl']);
if(!check_group($_groupid, $MOD['group_show'])) include load('403.inc');
require DT_ROOT.'/module/'.$module.'/common.inc.php';
if($itemid)
  $item = $db->get_one("SELECT * FROM {$table} WHERE itemid=$itemid");
else if($slug)
  $item = $db->get_one("SELECT * FROM {$table} WHERE slug='$slug'");

if($item && ($_admin || $item['status'] > 2)) {
	if($item['islink']) dheader($item['linkurl']);
	if($MOD['show_html'] && is_file(DT_ROOT.'/'.$MOD['moduledir'].'/'.$item['linkurl'])) d301($MOD['linkurl'].$item['linkurl']);
	extract($item);
}else{
	include load('404.inc');
}
$CAT = get_cat($catid);
if(!check_group($_groupid, $CAT['group_show'])) include load('403.inc');
//if(in_array($action,array('show','home'))){
if($SMOD){
  $scat =subject_get_cat("moduleid = $SMOD[moduleid] and catdir = '{$slug}'");
}else{
  $content_table = content_table($moduleid, $itemid, $MOD['split'], $table_data);
  $t = $db->get_one("SELECT content FROM {$content_table} WHERE itemid=$itemid");
  $content = $t['content'];
  if($lazy) $content = img_lazy($content);
  if($MOD['keylink']) $content = keylink($content, $moduleid);
  $CP = $MOD['cat_property'] && $CAT['property'];
  if($CP) {
    require DT_ROOT.'/include/property.func.php';
    $options = property_option($catid);
    $values = property_value($moduleid, $itemid);
  }
}
  
$adddate = timetodate($addtime, 5);
$editdate = timetodate($edittime, 5);
$todate = $totime ? timetodate($totime, 3) : 0;
$thumbs = get_albums($item);
$albums =  get_albums($item, 1);
if(!class_exists('subject'))
  require(DT_ROOT.'/module/subject/subject.class.php');
//$sc = new subjectRel(21);
//$news = $sc->search($title);
//$sc = new subjectRel(5);
//$sells = $sc->search($title);
include DT_ROOT.'/include/update.inc.php';
$seo_file = 'show';
include DT_ROOT.'/include/seo.inc.php';
if($EXT['wap_enable']) $head_mobile = $EXT['wap_url'].'index.php?moduleid='.$moduleid.'&itemid='.$itemid.($page > 1 ? '&page='.$page : '');
$template = 'show';
if($MOD['template_show']) $template = $MOD['template_show'];
if($CAT['show_template']) $template = $CAT['show_template'];
if($item['template']) $template = $item['template'];
$view = 'show-' . $action;
$linkpre = $MOD['subdomain'] ? $linkurl . '/' : $linkurl;
$link_current = $linkpre . $SMOD['moduledir'] . '/';
